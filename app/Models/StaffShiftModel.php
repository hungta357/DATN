<?php

namespace App\Models;

use App\Core\BaseModel;

class StaffShiftModel extends BaseModel
{
    protected string $table = 'staff_shifts';

    /**
     * Lấy lịch ca kèm tên nhân viên và phòng; nhân viên chỉ được nhận lịch của mình,
     * Admin nhận toàn bộ để quản lý và phân công.
     */
    public function getShifts(int $userId, bool $isAdmin): array
    {
        $where = $isAdmin ? '' : 'WHERE ss.staff_id = :staff_id';
        $stmt = $this->db->prepare("
            SELECT ss.*, u.name AS staff_name, r.name AS room_name, a.name AS assigner_name
            FROM staff_shifts ss
            INNER JOIN users u ON u.id = ss.staff_id
            INNER JOIN users a ON a.id = ss.assigned_by
            LEFT JOIN rooms r ON r.id = ss.room_id
            {$where}
            ORDER BY ss.starts_at DESC, u.name
        ");
        $stmt->execute($isAdmin ? [] : ['staff_id' => $userId]);
        return $stmt->fetchAll();
    }

    /**
     * Cung cấp nhân viên đang hoạt động và phòng chiếu đang dùng được cho biểu mẫu
     * phân công của Admin.
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

    /**
     * Tạo ca trực và khóa dòng tài khoản nhân viên trước khi kiểm tra chồng giờ,
     * tránh hai yêu cầu đồng thời phân công trùng lịch cho cùng một người.
     */
    public function createShift(
        int $staffId,
        int $assignedBy,
        ?int $roomId,
        string $startsAt,
        string $endsAt,
        string $duty,
        string $notes
    ): int {
        if (strtotime($startsAt) === false || strtotime($endsAt) === false || strtotime($endsAt) <= strtotime($startsAt)) {
            throw new \InvalidArgumentException('Thời gian bắt đầu và kết thúc ca trực không hợp lệ.');
        }
        if (strtotime($endsAt) - strtotime($startsAt) > 16 * 60 * 60) {
            throw new \InvalidArgumentException('Một ca trực không được dài quá 16 giờ.');
        }

        $this->db->beginTransaction();
        try {
            $stmtStaff = $this->db->prepare("
                SELECT u.id
                FROM users u
                INNER JOIN user_roles ur ON ur.user_id = u.id
                INNER JOIN roles r ON r.id = ur.role_id
                WHERE u.id = :id AND u.status = 'active' AND r.name = 'staff'
                LIMIT 1
                FOR UPDATE
            ");
            $stmtStaff->execute(['id' => $staffId]);
            if (!$stmtStaff->fetchColumn()) {
                throw new \InvalidArgumentException('Nhân viên không tồn tại hoặc không hoạt động.');
            }

            if ($roomId !== null) {
                $stmtRoom = $this->db->prepare("SELECT id FROM rooms WHERE id = :id AND status = 'active'");
                $stmtRoom->execute(['id' => $roomId]);
                if (!$stmtRoom->fetchColumn()) {
                    throw new \InvalidArgumentException('Phòng chiếu không tồn tại hoặc đang ngừng hoạt động.');
                }
            }

            $stmtOverlap = $this->db->prepare("
                SELECT id
                FROM staff_shifts
                WHERE staff_id = :staff_id
                  AND status = 'scheduled'
                  AND starts_at < :ends_at
                  AND ends_at > :starts_at
                LIMIT 1
            ");
            $stmtOverlap->execute([
                'staff_id' => $staffId,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
            ]);
            if ($stmtOverlap->fetchColumn()) {
                throw new \InvalidArgumentException('Nhân viên đã có ca trực bị trùng thời gian.');
            }

            $shiftId = $this->create([
                'staff_id' => $staffId,
                'assigned_by' => $assignedBy,
                'room_id' => $roomId,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'duty' => $duty,
                'notes' => $notes !== '' ? $notes : null,
                'status' => 'scheduled',
            ]);
            $this->db->commit();
            return $shiftId;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Cập nhật trạng thái ca; nhân viên chỉ được hoàn thành ca của mình, Admin có
     * thể đánh dấu hoàn thành hoặc hủy ca bất kỳ.
     */
    public function updateStatus(int $shiftId, int $actorId, bool $isAdmin, string $status): void
    {
        $allowedStatuses = $isAdmin ? ['completed', 'cancelled'] : ['completed'];
        if (!in_array($status, $allowedStatuses, true)) {
            throw new \InvalidArgumentException('Trạng thái ca trực không hợp lệ.');
        }

        $condition = $isAdmin
            ? 'id = :id AND status = \'scheduled\''
            : 'id = :id AND staff_id = :staff_id AND status = \'scheduled\'';
        $params = ['id' => $shiftId, 'status' => $status];
        if (!$isAdmin) {
            $params['staff_id'] = $actorId;
        }
        $stmt = $this->db->prepare("
            UPDATE staff_shifts
            SET status = :status, updated_at = NOW()
            WHERE {$condition}
        ");
        $stmt->execute($params);
        if ($stmt->rowCount() !== 1) {
            throw new \InvalidArgumentException('Không tìm thấy ca đang xếp lịch hoặc bạn không có quyền cập nhật.');
        }
    }

    /**
     * Trả về phòng đang hoạt động để chọn địa điểm làm việc cho ca trực.
     */
    public function getActiveRooms(): array
    {
        return $this->db->query("SELECT id, name FROM rooms WHERE status = 'active' ORDER BY name")->fetchAll();
    }
}
