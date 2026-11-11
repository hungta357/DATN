<?php

namespace App\Models;

use App\Core\BaseModel;

/**
 * NHÁNH 3: ROOMS / SEATS / SEAT TYPES
 * Bảng: rooms, seat_types, seats
 */
class RoomModel extends BaseModel
{
    protected string $table = 'rooms';

    public function getAllSeatTypes(): array
    {
        return $this->db->query("SELECT * FROM seat_types ORDER BY surcharge ASC")->fetchAll();
    }

    public function getRoomSeats(int $roomId): array
    {
        $sql = "SELECT s.*, st.name AS seat_type_name, st.surcharge
                FROM seats s
                INNER JOIN seat_types st ON st.id = s.seat_type_id
                WHERE s.room_id = :room_id
                ORDER BY s.row_label ASC, s.seat_number ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['room_id' => $roomId]);
        return $stmt->fetchAll();
    }

    /**
     * Tự động khởi tạo sơ đồ ghế tiêu chuẩn cho phòng chiếu mới
     * Hàng A-B: Standard (id=1), Hàng C-D: VIP (id=2), Hàng E: Couple (id=3)
     */
    public function generateDefaultSeatsForRoom(int $roomId, array $rows = ['A', 'B', 'C', 'D', 'E'], int $seatsPerRow = 6): int
    {
        $count = 0;
        $sql = "INSERT IGNORE INTO seats (room_id, seat_type_id, seat_code, row_label, seat_number, status, created_at, updated_at)
                VALUES (:room_id, :seat_type_id, :seat_code, :row_label, :seat_number, 'active', NOW(), NOW())";
        $stmt = $this->db->prepare($sql);

        foreach ($rows as $index => $rowLabel) {
            // Hàng A-B: Ghế thường (1), Hàng C-D: Ghế VIP (2), Hàng E: Ghế đôi Couple (3)
            if ($index < 2) {
                $seatTypeId = 1;
                $maxNum = $seatsPerRow;
            } elseif ($index < 4) {
                $seatTypeId = 2;
                $maxNum = $seatsPerRow;
            } else {
                $seatTypeId = 3;
                $maxNum = max(2, (int) floor($seatsPerRow / 2)); // Hàng ghế đôi có số lượng bằng nửa hàng thường
            }

            for ($num = 1; $num <= $maxNum; $num++) {
                $seatCode = $rowLabel . $num;
                $stmt->execute([
                    'room_id' => $roomId,
                    'seat_type_id' => $seatTypeId,
                    'seat_code' => $seatCode,
                    'row_label' => $rowLabel,
                    'seat_number' => $num,
                ]);
                $count += $stmt->rowCount();
            }
        }

        // Cập nhật lại capacity của phòng
        $stmtCap = $this->db->prepare("UPDATE rooms SET capacity = (SELECT COUNT(*) FROM seats WHERE room_id = :rid1 AND status = 'active'), updated_at = NOW() WHERE id = :rid2");
        $stmtCap->execute(['rid1' => $roomId, 'rid2' => $roomId]);

        return $count;
    }
}

