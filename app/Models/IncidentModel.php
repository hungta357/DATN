<?php

namespace App\Models;

use App\Core\BaseModel;

class IncidentModel extends BaseModel
{
    protected string $table = 'incident_reports';

    /**
     * Lấy danh sách vé đã thanh toán của khách để biểu mẫu chỉ cho phép khiếu nại
     * vé thuộc chính tài khoản đó; có thể giới hạn theo đơn được chọn trên trang vé.
     */
    public function getCustomerTickets(int $userId, ?int $bookingId = null): array
    {
        $sql = "
            SELECT t.id AS ticket_id, t.ticket_code, t.status AS ticket_status,
                   b.id AS booking_id, m.title AS movie_title, r.name AS room_name,
                   sh.start_time, s.seat_code
            FROM tickets t
            INNER JOIN booking_details bd ON bd.id = t.booking_detail_id
            INNER JOIN bookings b ON b.id = bd.booking_id
            INNER JOIN show_seats ss ON ss.id = bd.show_seat_id
            INNER JOIN shows sh ON sh.id = b.show_id
            INNER JOIN movies m ON m.id = sh.movie_id
            INNER JOIN rooms r ON r.id = sh.room_id
            INNER JOIN seats s ON s.id = ss.seat_id
            WHERE b.user_id = :user_id
              AND b.status IN ('confirmed', 'cancelled', 'refunded')
              AND t.status IN ('valid', 'used', 'cancelled', 'refunded')
        ";
        $params = ['user_id' => $userId];
        if ($bookingId !== null) {
            $sql .= ' AND b.id = :booking_id';
            $params['booking_id'] = $bookingId;
        }
        $sql .= ' ORDER BY sh.start_time DESC, t.id DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Lấy khiếu nại để khách chỉ xem báo cáo của mình, nhân viên xem phần được giao,
     * còn Admin được xem toàn bộ hồ sơ kèm thông tin vé và người xử lý.
     */
    public function getReports(int $userId, bool $isAdmin, bool $isStaff = false): array
    {
        $conditions = [];
        $params = [];
        if (!$isAdmin && $isStaff) {
            $conditions[] = 'ir.assigned_to = :assigned_to';
            $params['assigned_to'] = $userId;
        } elseif (!$isAdmin) {
            $conditions[] = 'ir.reported_by = :reported_by';
            $params['reported_by'] = $userId;
        }
        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
        $stmt = $this->db->prepare("
            SELECT ir.*, t.ticket_code, reporter.name AS reporter_name,
                   assignee.name AS assignee_name, m.title AS movie_title
            FROM incident_reports ir
            LEFT JOIN tickets t ON t.id = ir.ticket_id
            INNER JOIN users reporter ON reporter.id = ir.reported_by
            LEFT JOIN users assignee ON assignee.id = ir.assigned_to
            LEFT JOIN booking_details bd ON bd.id = t.booking_detail_id
            LEFT JOIN bookings b ON b.id = bd.booking_id
            LEFT JOIN shows sh ON sh.id = b.show_id
            LEFT JOIN movies m ON m.id = sh.movie_id
            {$where}
            ORDER BY FIELD(ir.status, 'open', 'in_review', 'resolved', 'rejected'),
                     ir.created_at DESC
        ");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Tạo yêu cầu khiếu nại sau khi xác minh mã vé thuộc một đơn đã thanh toán
     * của người gửi; câu lệnh chuẩn bị và transaction bảo vệ dữ liệu khỏi giả mạo.
     */
    public function createReport(int $userId, string $ticketCode, string $category, string $title, string $description): int
    {
        if (!in_array($category, ['ticket', 'service', 'safety', 'other'], true)) {
            throw new \InvalidArgumentException('Loại khiếu nại không hợp lệ.');
        }
        $stmtTicket = $this->db->prepare("
            SELECT t.id
            FROM tickets t
            INNER JOIN booking_details bd ON bd.id = t.booking_detail_id
            INNER JOIN bookings b ON b.id = bd.booking_id
            WHERE t.ticket_code = :ticket_code
              AND b.user_id = :user_id
              AND b.status IN ('confirmed', 'cancelled', 'refunded')
              AND t.status IN ('valid', 'used', 'cancelled', 'refunded')
            LIMIT 1
        ");
        $stmtTicket->execute(['ticket_code' => $ticketCode, 'user_id' => $userId]);
        $ticketId = $stmtTicket->fetchColumn();
        if ($ticketId === false) {
            throw new \InvalidArgumentException('Không tìm thấy vé hợp lệ thuộc tài khoản của bạn.');
        }

        return $this->create([
            'ticket_id' => (int) $ticketId,
            'reported_by' => $userId,
            'category' => $category,
            'title' => $title,
            'description' => $description,
            'status' => 'open',
            'resolution' => null,
        ]);
    }

    /**
     * Phân công hồ sơ cho nhân viên đang hoạt động và chuyển trạng thái sang đang
     * xem xét trong cùng transaction để tránh lưu phân công dang dở.
     */
    public function assignReport(int $reportId, int $staffId): void
    {
        $this->db->beginTransaction();
        try {
            $stmtStaff = $this->db->prepare("
                SELECT u.id
                FROM users u
                INNER JOIN user_roles ur ON ur.user_id = u.id
                INNER JOIN roles r ON r.id = ur.role_id
                WHERE u.id = :id AND u.status = 'active' AND r.name = 'staff'
                LIMIT 1
            ");
            $stmtStaff->execute(['id' => $staffId]);
            if (!$stmtStaff->fetchColumn()) {
                throw new \InvalidArgumentException('Chỉ có thể phân công cho nhân viên đang hoạt động.');
            }

            $stmtExists = $this->db->prepare('SELECT id FROM incident_reports WHERE id = :id FOR UPDATE');
            $stmtExists->execute(['id' => $reportId]);
            if (!$stmtExists->fetchColumn()) {
                throw new \InvalidArgumentException('Không tìm thấy khiếu nại cần phân công.');
            }

            $stmtReport = $this->db->prepare("
                UPDATE incident_reports
                SET assigned_to = :staff_id,
                    status = IF(status IN ('resolved', 'rejected'), status, 'in_review'),
                    updated_at = NOW()
                WHERE id = :id
            ");
            $stmtReport->execute(['staff_id' => $staffId, 'id' => $reportId]);
            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Cập nhật trạng thái và phản hồi; nhân viên chỉ được thay đổi hồ sơ được giao,
     * trong khi Admin có thể xử lý mọi trạng thái.
     */
    public function updateReport(int $reportId, int $actorId, bool $isAdmin, string $status, string $resolution): void
    {
        $allowedStatuses = $isAdmin ? ['open', 'in_review', 'resolved', 'rejected'] : ['in_review', 'resolved'];
        if (!in_array($status, $allowedStatuses, true)) {
            throw new \InvalidArgumentException('Trạng thái xử lý không hợp lệ.');
        }

        $this->db->beginTransaction();
        try {
            $conditions = $isAdmin ? 'id = :id' : 'id = :id AND assigned_to = :actor_id';
            $params = ['id' => $reportId];
            if (!$isAdmin) {
                $params['actor_id'] = $actorId;
            }
            $stmtExists = $this->db->prepare("
                SELECT id FROM incident_reports WHERE {$conditions} FOR UPDATE
            ");
            $stmtExists->execute($params);
            if (!$stmtExists->fetchColumn()) {
                throw new \InvalidArgumentException('Không tìm thấy khiếu nại hoặc bạn không được phân công xử lý.');
            }

            $stmt = $this->db->prepare("
                UPDATE incident_reports
                SET status = :status, resolution = :resolution, updated_at = NOW()
                WHERE id = :id
            ");
            $stmt->execute([
                'status' => $status,
                'resolution' => $resolution !== '' ? $resolution : null,
                'id' => $reportId,
            ]);
            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Trả về danh sách nhân viên đang hoạt động để Admin chọn người phụ trách.
     */
    public function getAssignableStaff(): array
    {
        return $this->db->query("
            SELECT DISTINCT u.id, u.name, u.email
            FROM users u
            INNER JOIN user_roles ur ON ur.user_id = u.id
            INNER JOIN roles r ON r.id = ur.role_id
            WHERE u.status = 'active' AND r.name = 'staff'
            ORDER BY u.name
        ")->fetchAll();
    }
}
