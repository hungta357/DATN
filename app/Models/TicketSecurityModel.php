<?php

namespace App\Models;

use App\Core\BaseModel;

class TicketSecurityModel extends BaseModel
{
    protected string $table = 'ticket_scan_logs';

    /**
     * Soát vé trong transaction, khóa dòng vé để ngăn quét đồng thời; mọi lần từ
     * chối đều được ghi log sau rollback để lịch sử cảnh báo không bị mất.
     */
    public function checkInTicketByCode(string $ticketCode, int $staffId): array
    {
        $ticketCode = trim($ticketCode);
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("
                SELECT t.*, bd.booking_id, s.seat_code, b.booking_code, b.status AS booking_status,
                       m.title AS movie_title, sh.start_time, sh.end_time, r.name AS room_name
                FROM tickets t
                INNER JOIN booking_details bd ON bd.id = t.booking_detail_id
                INNER JOIN bookings b ON b.id = bd.booking_id
                INNER JOIN show_seats ss ON ss.id = bd.show_seat_id
                INNER JOIN seats s ON s.id = ss.seat_id
                INNER JOIN shows sh ON sh.id = b.show_id
                INNER JOIN movies m ON m.id = sh.movie_id
                INNER JOIN rooms r ON r.id = sh.room_id
                WHERE t.ticket_code = :code
                LIMIT 1
                FOR UPDATE
            ");
            $stmt->execute(['code' => $ticketCode]);
            $ticket = $stmt->fetch();

            if (!$ticket) {
                $this->rejectScan($staffId, null, $ticketCode, 'unknown_ticket', 'Mã vé không tồn tại trong hệ thống.');
            }
            if ($ticket['status'] === 'used') {
                $this->rejectScan(
                    $staffId,
                    (int) $ticket['id'],
                    $ticketCode,
                    'duplicate_scan',
                    "Vé {$ticketCode} đã được sử dụng lúc {$ticket['checked_in_at']}."
                );
            }
            if ($ticket['status'] !== 'valid' || $ticket['booking_status'] !== 'confirmed') {
                $this->rejectScan(
                    $staffId,
                    (int) $ticket['id'],
                    $ticketCode,
                    'invalid_ticket',
                    "Vé {$ticketCode} không hợp lệ hoặc đơn đặt vé chưa thanh toán."
                );
            }

            $stmtUpdate = $this->db->prepare("
                UPDATE tickets
                SET status = 'used', checked_in_at = NOW(), updated_at = NOW()
                WHERE id = :id AND status = 'valid'
            ");
            $stmtUpdate->execute(['id' => $ticket['id']]);
            if ($stmtUpdate->rowCount() !== 1) {
                $this->rejectScan(
                    $staffId,
                    (int) $ticket['id'],
                    $ticketCode,
                    'concurrent_scan',
                    'Vé vừa được soát bởi một nhân viên khác. Vui lòng kiểm tra lại.'
                );
            }

            $outsideShowWindow = strtotime($ticket['start_time']) > time() + 60 * 60
                || strtotime($ticket['end_time']) < time();
            $anomalyType = $outsideShowWindow ? 'outside_show_window' : null;
            $details = $outsideShowWindow
                ? 'Vé được quét ngoài khung thời gian suất chiếu; cần kiểm tra lại tại quầy.'
                : 'Soát vé thành công.';
            $anomalyType = $this->recordScan(
                $staffId,
                (int) $ticket['id'],
                $ticketCode,
                $outsideShowWindow ? 'anomaly' : 'accepted',
                $anomalyType,
                $details
            );
            $this->db->commit();

            $ticket['status'] = 'used';
            $ticket['checked_in_at'] = date('Y-m-d H:i:s');
            $ticket['scan_anomaly'] = $anomalyType;
            return $ticket;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Ghi nhận các cảnh báo gần đây để Admin và nhân viên ca trực có thể xử lý
     * những lượt quét vé trùng, sai mã hoặc ngoài thời gian suất chiếu.
     */
    public function getAnomalyLogs(int $limit = 30): array
    {
        $limit = max(1, min($limit, 100));
        $stmt = $this->db->query("
            SELECT l.*, u.name AS staff_name
            FROM ticket_scan_logs l
            INNER JOIN users u ON u.id = l.staff_id
            WHERE l.anomaly_type IS NOT NULL
            ORDER BY l.scanned_at DESC, l.id DESC
            LIMIT {$limit}
        ");
        return $stmt->fetchAll();
    }

    /**
     * Kết thúc transaction soát vé, lưu một lần quét bất thường độc lập rồi báo
     * lỗi cho controller; vì vậy những lần thất bại vẫn hiện trong danh sách cảnh báo.
     */
    private function rejectScan(int $staffId, ?int $ticketId, string $ticketCode, string $anomalyType, string $message): void
    {
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }
        $this->recordScan($staffId, $ticketId, $ticketCode, 'anomaly', $anomalyType, $message);
        throw new \RuntimeException($message);
    }

    /**
     * Lưu log và gắn cảnh báo quét lỗi lặp lại khi cùng nhân viên có ít nhất ba
     * lần quét thất bại trong vòng năm phút.
     */
    private function recordScan(
        int $staffId,
        ?int $ticketId,
        string $ticketCode,
        string $outcome,
        ?string $anomalyType,
        string $details
    ): ?string {
        if ($outcome !== 'accepted') {
            $stmtCount = $this->db->prepare("
                SELECT COUNT(*)
                FROM ticket_scan_logs
                WHERE staff_id = :staff_id
                  AND anomaly_type IN (
                      'unknown_ticket', 'invalid_ticket', 'duplicate_scan',
                      'concurrent_scan', 'repeated_failed_scans'
                  )
                  AND scanned_at >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
            ");
            $stmtCount->execute(['staff_id' => $staffId]);
            if (
                (int) $stmtCount->fetchColumn() >= 2 &&
                in_array($anomalyType, ['unknown_ticket', 'invalid_ticket'], true)
            ) {
                $anomalyType = 'repeated_failed_scans';
                $outcome = 'anomaly';
                $details = 'Phát hiện ít nhất ba lần quét vé thất bại trong vòng năm phút.';
            }
        }

        $stmt = $this->db->prepare("
            INSERT INTO ticket_scan_logs
                (ticket_id, staff_id, ticket_code, outcome, anomaly_type, details, scanned_at, created_at)
            VALUES
                (:ticket_id, :staff_id, :ticket_code, :outcome, :anomaly_type, :details, NOW(), NOW())
        ");
        $stmt->execute([
            'ticket_id' => $ticketId,
            'staff_id' => $staffId,
            'ticket_code' => substr($ticketCode, 0, 255),
            'outcome' => $outcome,
            'anomaly_type' => $anomalyType,
            'details' => substr($details, 0, 500),
        ]);
        return $anomalyType;
    }
}
