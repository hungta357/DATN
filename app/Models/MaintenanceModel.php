<?php

namespace App\Models;

use App\Core\BaseModel;

class MaintenanceModel extends BaseModel
{
    protected string $table = 'maintenance_reports';
    private static bool $schemaEnsured = false;

    public function __construct()
    {
        parent::__construct();
        $this->ensureSchema();
    }

    private function ensureSchema(): void
    {
        if (self::$schemaEnsured) {
            return;
        }
        self::$schemaEnsured = true;
        try {
            $sqlFile = dirname(__DIR__, 2) . '/database/maintenance_feature.sql';
            if (is_file($sqlFile)) {
                $sql = file_get_contents($sqlFile);
                if ($sql !== false) {
                    $this->db->exec($sql);
                }
            }
        } catch (\Throwable $e) {
            error_log('[MaintenanceModel::ensureSchema] ' . $e->getMessage());
        }
    }

    public function getCategories(): array
    {
        return $this->db->query('SELECT id, name FROM maintenance_categories ORDER BY name')->fetchAll();
    }

    public function getRooms(): array
    {
        return $this->db->query('SELECT id, name, status FROM rooms ORDER BY name')->fetchAll();
    }

    public function getSeats(): array
    {
        return $this->db->query("
            SELECT s.id, s.room_id, s.seat_code, s.status, r.name AS room_name
            FROM seats s
            INNER JOIN rooms r ON r.id = s.room_id
            ORDER BY r.name, s.row_label, s.seat_number
        ")->fetchAll();
    }

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

    public function getMonthlyStats(): array
    {
        return $this->db->query("
            SELECT DATE_FORMAT(created_at, '%Y-%m') AS month,
                   COUNT(*) AS total,
                   SUM(status = 'completed') AS completed,
                   SUM(status <> 'completed') AS open,
                   SUM(severity = 'critical') AS critical
            FROM maintenance_reports
            WHERE created_at >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 11 MONTH), '%Y-%m-01')
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month DESC
            LIMIT 12
        ")->fetchAll();
    }

    public function getReports(int $userId, bool $isAdmin, array $filters = []): array
    {
        $conditions = [];
        $params = [];

        if (!$isAdmin) {
            $conditions[] = '(mr.reported_by = :reported_by OR mr.assigned_to = :assigned_to)';
            $params['reported_by'] = $userId;
            $params['assigned_to'] = $userId;
        }
        foreach (['room_id', 'severity', 'status'] as $filter) {
            if (isset($filters[$filter]) && $filters[$filter] !== '') {
                $conditions[] = 'mr.' . $filter . ' = :' . $filter;
                $params[$filter] = $filters[$filter];
            }
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
        $stmt = $this->db->prepare("
            SELECT mr.*, mc.name AS category_name, rm.name AS room_name,
                   s.seat_code, reporter.name AS reporter_name,
                   assignee.name AS assignee_name
            FROM maintenance_reports mr
            INNER JOIN maintenance_categories mc ON mc.id = mr.category_id
            INNER JOIN rooms rm ON rm.id = mr.room_id
            LEFT JOIN seats s ON s.id = mr.seat_id
            INNER JOIN users reporter ON reporter.id = mr.reported_by
            LEFT JOIN users assignee ON assignee.id = mr.assigned_to
            {$where}
            ORDER BY FIELD(mr.status, 'pending_confirmation', 'in_progress', 'assigned', 'reported', 'completed'),
                     mr.created_at DESC
        ");
        $stmt->execute($params);
        $reports = $stmt->fetchAll();

        if ($reports) {
            $ids = array_column($reports, 'id');
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmtAttachments = $this->db->prepare("
                SELECT report_id, file_path, original_name
                FROM maintenance_attachments
                WHERE report_id IN ({$placeholders})
                ORDER BY id
            ");
            $stmtAttachments->execute($ids);
            $attachmentsByReport = [];
            foreach ($stmtAttachments->fetchAll() as $attachment) {
                $attachmentsByReport[$attachment['report_id']][] = $attachment;
            }

            $stmtHistory = $this->db->prepare("
                SELECT mh.report_id, mh.action, mh.note, mh.created_at, u.name AS actor_name
                FROM maintenance_histories mh
                INNER JOIN users u ON u.id = mh.actor_id
                WHERE mh.report_id IN ({$placeholders})
                ORDER BY mh.id DESC
            ");
            $stmtHistory->execute($ids);
            $historyByReport = [];
            foreach ($stmtHistory->fetchAll() as $history) {
                $historyByReport[$history['report_id']][] = $history;
            }

            foreach ($reports as &$report) {
                $report['attachments'] = $attachmentsByReport[$report['id']] ?? [];
                $report['history'] = $historyByReport[$report['id']] ?? [];
            }
            unset($report);
        }

        return $reports;
    }

    public function createReport(array $data, int $actorId, ?array $attachment = null): int
    {
        $this->db->beginTransaction();
        try {
            $roomId = (int) $data['room_id'];
            $seatId = !empty($data['seat_id']) ? (int) $data['seat_id'] : null;
            $stmtRoom = $this->db->prepare('SELECT id, status FROM rooms WHERE id = :id FOR UPDATE');
            $stmtRoom->execute(['id' => $roomId]);
            $room = $stmtRoom->fetch();
            if (!$room) {
                throw new \InvalidArgumentException('Phòng chiếu không tồn tại.');
            }

            $seatAutoMaintenance = false;
            if ($seatId !== null) {
                $stmtSeat = $this->db->prepare('SELECT id, room_id, status FROM seats WHERE id = :id FOR UPDATE');
                $stmtSeat->execute(['id' => $seatId]);
                $seat = $stmtSeat->fetch();
                if (!$seat || (int) $seat['room_id'] !== $roomId) {
                    throw new \InvalidArgumentException('Ghế không thuộc phòng chiếu đã chọn.');
                }
                if ($seat['status'] === 'blocked') {
                    throw new \InvalidArgumentException('Ghế đang bị khóa, không thể ghi nhận thêm sự cố bảo trì.');
                }
                if ($seat['status'] === 'active') {
                    $this->db->prepare("UPDATE seats SET status = 'maintenance', updated_at = NOW() WHERE id = :id")
                        ->execute(['id' => $seatId]);
                    $this->updateRoomCapacity($roomId);
                    $this->db->prepare("
                        UPDATE show_seats ss
                        INNER JOIN shows sh ON sh.id = ss.show_id
                        SET ss.status = 'blocked', ss.held_by_user_id = NULL, ss.hold_expires_at = NULL, ss.updated_at = NOW()
                        WHERE ss.seat_id = :seat_id AND ss.status IN ('available', 'held')
                          AND sh.status IN ('scheduled', 'selling')
                    ")->execute(['seat_id' => $seatId]);
                    $seatAutoMaintenance = true;
                } else {
                    $existingSeatReport = $this->db->prepare("
                        SELECT 1 FROM maintenance_reports
                        WHERE seat_id = :seat_id AND seat_auto_maintenance = 1 AND status <> 'completed'
                        LIMIT 1
                    ");
                    $existingSeatReport->execute(['seat_id' => $seatId]);
                    $seatAutoMaintenance = (bool) $existingSeatReport->fetchColumn();
                }
            }

            $stmt = $this->db->prepare("
                INSERT INTO maintenance_reports
                    (report_code, category_id, room_id, seat_id, reported_by, severity, status,
                     description, seat_auto_maintenance, room_auto_maintenance, created_at, updated_at)
                VALUES (NULL, :category_id, :room_id, :seat_id, :reported_by, :severity, 'reported',
                        :description, :seat_auto_maintenance, 0, NOW(), NOW())
            ");
            $stmt->execute([
                'category_id' => (int) $data['category_id'],
                'room_id' => $roomId,
                'seat_id' => $seatId,
                'reported_by' => $actorId,
                'severity' => $data['severity'],
                'description' => $data['description'],
                'seat_auto_maintenance' => $seatAutoMaintenance ? 1 : 0,
            ]);
            $reportId = (int) $this->db->lastInsertId();
            $reportCode = 'MT-' . date('ymd') . '-' . str_pad((string) $reportId, 6, '0', STR_PAD_LEFT);
            $this->db->prepare('UPDATE maintenance_reports SET report_code = :code WHERE id = :id')
                ->execute(['code' => $reportCode, 'id' => $reportId]);

            $this->addHistory($reportId, $actorId, 'created', 'Đã tạo báo cáo sự cố.');
            $this->saveAttachment($reportId, $actorId, $attachment);
            $this->db->commit();
            return $reportId;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function assignReport(int $reportId, int $staffId, string $severity, ?string $dueAt, int $actorId, string $note): void
    {
        if (!in_array($severity, ['low', 'medium', 'high', 'critical'], true)) {
            throw new \InvalidArgumentException('Mức độ sự cố không hợp lệ.');
        }
        if ($dueAt !== null && strtotime($dueAt) === false) {
            throw new \InvalidArgumentException('Thời hạn xử lý không hợp lệ.');
        }

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("
                SELECT mr.*, r.status AS room_status, u.status AS staff_status
                FROM maintenance_reports mr
                INNER JOIN rooms r ON r.id = mr.room_id
                INNER JOIN users u ON u.id = :staff_id
                WHERE mr.id = :id
                FOR UPDATE
            ");
            $stmt->execute(['id' => $reportId, 'staff_id' => $staffId]);
            $report = $stmt->fetch();
            if (!$report || $report['status'] === 'completed') {
                throw new \InvalidArgumentException('Không tìm thấy báo cáo đang cần xử lý.');
            }
            if ($report['staff_status'] !== 'active') {
                throw new \InvalidArgumentException('Nhân viên được chọn không hoạt động.');
            }
            $staffRole = $this->db->prepare("
                SELECT 1 FROM user_roles ur INNER JOIN roles r ON r.id = ur.role_id
                WHERE ur.user_id = :id AND r.name = 'staff' LIMIT 1
            ");
            $staffRole->execute(['id' => $staffId]);
            if (!$staffRole->fetchColumn()) {
                throw new \InvalidArgumentException('Chỉ có thể phân công cho tài khoản nhân viên.');
            }

            $roomAutoMaintenance = (bool) $report['room_auto_maintenance'];
            if ($severity === 'critical' && $report['seat_id'] === null) {
                if ($report['room_status'] === 'active') {
                    $this->db->prepare("UPDATE rooms SET status = 'maintenance', updated_at = NOW() WHERE id = :id")
                        ->execute(['id' => $report['room_id']]);
                    $this->db->prepare("
                        UPDATE show_seats ss
                        INNER JOIN shows sh ON sh.id = ss.show_id
                        SET ss.status = 'blocked', ss.held_by_user_id = NULL, ss.hold_expires_at = NULL, ss.updated_at = NOW()
                        WHERE sh.room_id = :room_id AND ss.status IN ('available', 'held')
                          AND sh.status IN ('scheduled', 'selling')
                    ")->execute(['room_id' => $report['room_id']]);
                    $roomAutoMaintenance = true;
                } elseif (!$roomAutoMaintenance) {
                    $existingRoomReport = $this->db->prepare("
                        SELECT 1 FROM maintenance_reports
                        WHERE room_id = :room_id AND room_auto_maintenance = 1
                          AND status <> 'completed' AND id <> :report_id
                        LIMIT 1
                    ");
                    $existingRoomReport->execute(['room_id' => $report['room_id'], 'report_id' => $reportId]);
                    $roomAutoMaintenance = (bool) $existingRoomReport->fetchColumn();
                }
            }
            $this->db->prepare("
                UPDATE maintenance_reports
                SET assigned_to = :assigned_to, severity = :severity, due_at = :due_at,
                    status = 'assigned', room_auto_maintenance = :room_auto_maintenance, updated_at = NOW()
                WHERE id = :id
            ")->execute([
                'assigned_to' => $staffId,
                'severity' => $severity,
                'due_at' => $dueAt,
                'room_auto_maintenance' => $roomAutoMaintenance ? 1 : 0,
                'id' => $reportId,
            ]);
            $this->addHistory($reportId, $actorId, 'assigned', trim($note) ?: 'Đã phân công nhân viên xử lý.');
            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function updateProgress(int $reportId, int $staffId, bool $submitForConfirmation, string $note, ?array $attachment = null, bool $isAdmin = false): void
    {
        if (trim($note) === '') {
            throw new \InvalidArgumentException('Vui lòng nhập nội dung cập nhật.');
        }
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare('SELECT * FROM maintenance_reports WHERE id = :id FOR UPDATE');
            $stmt->execute(['id' => $reportId]);
            $report = $stmt->fetch();
            $canUpdate = $report && (
                ($isAdmin && in_array($report['status'], ['reported', 'assigned', 'in_progress'], true)) ||
                ((int) $report['assigned_to'] === $staffId && in_array($report['status'], ['assigned', 'in_progress'], true))
            );
            if (!$canUpdate) {
                throw new \InvalidArgumentException('Bạn không được phân công xử lý báo cáo này hoặc báo cáo đã chuyển trạng thái.');
            }

            $newStatus = $submitForConfirmation ? 'pending_confirmation' : 'in_progress';
            $this->db->prepare("
                UPDATE maintenance_reports
                SET status = :status, resolution = :resolution, updated_at = NOW()
                WHERE id = :id
            ")->execute([
                'status' => $newStatus,
                'resolution' => $submitForConfirmation ? trim($note) : $report['resolution'],
                'id' => $reportId,
            ]);
            $this->addHistory(
                $reportId,
                $staffId,
                $submitForConfirmation ? 'submitted' : 'progress',
                trim($note)
            );
            $this->saveAttachment($reportId, $staffId, $attachment);
            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function confirmReport(int $reportId, int $adminId, string $note): void
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare('SELECT * FROM maintenance_reports WHERE id = :id FOR UPDATE');
            $stmt->execute(['id' => $reportId]);
            $report = $stmt->fetch();
            if (!$report || $report['status'] !== 'pending_confirmation') {
                throw new \InvalidArgumentException('Chỉ báo cáo đã gửi chờ xác nhận mới được hoàn tất.');
            }

            $this->db->prepare("
                UPDATE maintenance_reports SET status = 'completed', updated_at = NOW() WHERE id = :id
            ")->execute(['id' => $reportId]);
            $this->addHistory($reportId, $adminId, 'confirmed', trim($note) ?: 'Admin xác nhận đã hoàn thành.');

            if ($report['seat_id'] !== null && (bool) $report['seat_auto_maintenance']) {
                $otherOpenSeatReport = $this->db->prepare("
                    SELECT 1 FROM maintenance_reports
                    WHERE seat_id = :seat_id AND id <> :id AND status <> 'completed' LIMIT 1
                ");
                $otherOpenSeatReport->execute(['seat_id' => $report['seat_id'], 'id' => $reportId]);
                if (!$otherOpenSeatReport->fetchColumn()) {
                    $this->db->prepare("
                        UPDATE seats SET status = 'active', updated_at = NOW()
                        WHERE id = :id AND status = 'maintenance'
                    ")->execute(['id' => $report['seat_id']]);
                    $this->db->prepare("
                        UPDATE show_seats ss
                        INNER JOIN shows sh ON sh.id = ss.show_id
                        INNER JOIN seats s ON s.id = ss.seat_id
                        INNER JOIN rooms r ON r.id = sh.room_id
                        SET ss.status = 'available', ss.updated_at = NOW()
                        WHERE ss.seat_id = :seat_id AND ss.status = 'blocked'
                          AND s.status = 'active' AND r.status = 'active' AND sh.status IN ('scheduled', 'selling')
                          AND sh.start_time > NOW()
                    ")->execute(['seat_id' => $report['seat_id']]);
                    $this->updateRoomCapacity((int) $report['room_id']);
                }
            }

            if ((bool) $report['room_auto_maintenance']) {
                $otherOpenRoomReport = $this->db->prepare("
                    SELECT 1 FROM maintenance_reports
                    WHERE room_id = :room_id AND id <> :id AND room_auto_maintenance = 1
                      AND status <> 'completed' LIMIT 1
                ");
                $otherOpenRoomReport->execute(['room_id' => $report['room_id'], 'id' => $reportId]);
                if (!$otherOpenRoomReport->fetchColumn()) {
                    $this->db->prepare("
                        UPDATE rooms SET status = 'active', updated_at = NOW()
                        WHERE id = :id AND status = 'maintenance'
                    ")->execute(['id' => $report['room_id']]);
                    $this->db->prepare("
                        UPDATE show_seats ss
                        INNER JOIN shows sh ON sh.id = ss.show_id
                        INNER JOIN seats s ON s.id = ss.seat_id
                        SET ss.status = 'available', ss.updated_at = NOW()
                        WHERE sh.room_id = :room_id AND ss.status = 'blocked'
                          AND s.status = 'active' AND sh.status IN ('scheduled', 'selling')
                          AND sh.start_time > NOW()
                    ")->execute(['room_id' => $report['room_id']]);
                }
            }

            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    private function addHistory(int $reportId, int $actorId, string $action, string $note): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO maintenance_histories (report_id, actor_id, action, note, created_at)
            VALUES (:report_id, :actor_id, :action, :note, NOW())
        ");
        $stmt->execute([
            'report_id' => $reportId,
            'actor_id' => $actorId,
            'action' => $action,
            'note' => $note,
        ]);
    }

    private function updateRoomCapacity(int $roomId): void
    {
        $this->db->prepare("
            UPDATE rooms
            SET capacity = (SELECT COUNT(*) FROM seats WHERE room_id = :room_id AND status = 'active'),
                updated_at = NOW()
            WHERE id = :id
        ")->execute(['room_id' => $roomId, 'id' => $roomId]);
    }

    private function saveAttachment(int $reportId, int $actorId, ?array $attachment): void
    {
        if ($attachment === null) {
            return;
        }
        $stmt = $this->db->prepare("
            INSERT INTO maintenance_attachments
                (report_id, uploaded_by, file_path, original_name, mime_type, size_bytes, created_at)
            VALUES (:report_id, :uploaded_by, :file_path, :original_name, :mime_type, :size_bytes, NOW())
        ");
        $stmt->execute([
            'report_id' => $reportId,
            'uploaded_by' => $actorId,
            'file_path' => $attachment['file_path'],
            'original_name' => $attachment['original_name'],
            'mime_type' => $attachment['mime_type'],
            'size_bytes' => $attachment['size_bytes'],
        ]);
    }
}
