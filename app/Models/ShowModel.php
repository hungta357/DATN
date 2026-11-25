<?php

namespace App\Models;

use App\Core\BaseModel;
use Exception;

/**
 * NHÁNH 4: SHOWTIMES / SEAT INVENTORY FOR EACH SHOW
 * Bảng: shows, show_seats
 * Lưu ý nghiệp vụ #1: Khi tạo show, tạo các dòng show_seats từ danh sách seats của room.
 * Lưu ý nghiệp vụ #2: Khi giữ ghế, dùng DB transaction + SELECT ... FOR UPDATE,
 *                     chỉ giữ nếu status='available'; đặt hold_expires_at.
 */
class ShowModel extends BaseModel
{
    protected string $table = 'shows';

    public function getShowsByMovie(int $movieId): array
    {
        $sql = "SELECT sh.*, r.name AS room_name, r.room_type,
                       (SELECT COUNT(*) FROM show_seats ss
                        WHERE ss.show_id = sh.id
                          AND (ss.status = 'available' OR (ss.status = 'held' AND ss.hold_expires_at <= NOW()))) AS available_seats
                FROM shows sh
                INNER JOIN rooms r ON r.id = sh.room_id
                WHERE sh.movie_id = :movie_id
                  AND sh.status IN ('scheduled', 'selling')
                  AND sh.start_time > NOW()
                ORDER BY sh.start_time ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['movie_id' => $movieId]);
        return $stmt->fetchAll();
    }

    /** Lấy các suất chiếu tương lai, có thể lọc theo một ngày cụ thể. */
    public function getSellingShowsGroupedByMovie(?string $date = null): array
    {
        $whereDate = '';
        $params = [];
        if ($date !== null && $date !== '') {
            $dateObject = \DateTimeImmutable::createFromFormat('!Y-m-d', $date);
            if (!$dateObject || $dateObject->format('Y-m-d') !== $date) {
                return [];
            }
            $whereDate = ' AND sh.start_time >= :date_start AND sh.start_time < :date_end ';
            $params['date_start'] = $dateObject->format('Y-m-d H:i:s');
            $params['date_end'] = $dateObject->modify('+1 day')->format('Y-m-d H:i:s');
        }

        $sql = "SELECT sh.*, r.name AS room_name, r.room_type,
                       (SELECT COUNT(*) FROM show_seats ss
                        WHERE ss.show_id = sh.id
                          AND (ss.status = 'available' OR (ss.status = 'held' AND ss.hold_expires_at <= NOW()))) AS available_seats
                FROM shows sh
                INNER JOIN rooms r ON r.id = sh.room_id
                WHERE sh.status IN ('scheduled', 'selling')
                  AND sh.start_time > NOW() {$whereDate}
                ORDER BY sh.start_time ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        $grouped = [];
        foreach ($rows as $row) {
            $grouped[(int) $row['movie_id']][] = $row;
        }
        return $grouped;
    }

    public function getAllShowsWithDetails(): array
    {
        $sql = "SELECT sh.*, m.title AS movie_title, m.title_vi, r.name AS room_name,
                       (SELECT COUNT(*) FROM show_seats ss WHERE ss.show_id = sh.id AND ss.status = 'available') AS available_seats,
                       (SELECT COUNT(*) FROM show_seats ss WHERE ss.show_id = sh.id) AS total_seats
                FROM shows sh
                INNER JOIN movies m ON m.id = sh.movie_id
                INNER JOIN rooms r ON r.id = sh.room_id
                ORDER BY sh.start_time DESC";
        return $this->db->query($sql)->fetchAll();
    }

    public function findShowWithDetails(int $showId): ?array
    {
        $sql = "SELECT sh.*, m.title AS movie_title, m.title_vi, m.poster_path, m.runtime, m.age_rating,
                       r.name AS room_name, r.room_type
                FROM shows sh
                INNER JOIN movies m ON m.id = sh.movie_id
                INNER JOIN rooms r ON r.id = sh.room_id
                WHERE sh.id = :id
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $showId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Nghiệp vụ #1: Tạo suất chiếu (show) và sinh tự động toàn bộ show_seats từ seats của room
     * Giá mỗi ghế = base_price của show + surcharge của seat_type
     */
    public function createShowWithSeats(array $data): int
    {
        $movieId = (int) ($data['movie_id'] ?? 0);
        $roomId = (int) ($data['room_id'] ?? 0);
        $startTime = (string) ($data['start_time'] ?? '');
        $endTime = (string) ($data['end_time'] ?? '');
        $basePrice = (float) ($data['base_price'] ?? 0);
        $startTimestamp = strtotime($startTime);
        $endTimestamp = strtotime($endTime);

        if ($movieId <= 0 || $roomId <= 0 || $basePrice <= 0) {
            throw new Exception('Vui lòng chọn phim, phòng chiếu và giá vé hợp lệ.');
        }
        if ($startTimestamp === false || $endTimestamp === false || $startTimestamp <= time() || $endTimestamp <= $startTimestamp) {
            throw new Exception('Thời gian chiếu phải ở tương lai và giờ kết thúc phải sau giờ bắt đầu.');
        }

        $this->db->beginTransaction();
        try {
            $movieStmt = $this->db->prepare("SELECT id, runtime, status FROM movies WHERE id = :id FOR UPDATE");
            $movieStmt->execute(['id' => $movieId]);
            $movie = $movieStmt->fetch();
            if (!$movie || $movie['status'] === 'hidden' || $movie['status'] === 'ended') {
                throw new Exception('Phim không tồn tại hoặc không được phép xếp lịch chiếu.');
            }

            $roomStmt = $this->db->prepare("SELECT id, status FROM rooms WHERE id = :id FOR UPDATE");
            $roomStmt->execute(['id' => $roomId]);
            $room = $roomStmt->fetch();
            if (!$room || $room['status'] !== 'active') {
                throw new Exception('Phòng chiếu không tồn tại hoặc đang ngừng hoạt động.');
            }

            $overlapStmt = $this->db->prepare("SELECT id FROM shows
                WHERE room_id = :room_id AND status <> 'cancelled'
                  AND start_time < :end_time AND end_time > :start_time
                LIMIT 1 FOR UPDATE");
            $overlapStmt->execute([
                'room_id' => $roomId,
                'start_time' => $startTime,
                'end_time' => $endTime,
            ]);
            if ($overlapStmt->fetchColumn()) {
                throw new Exception('Phòng chiếu đã có suất chiếu khác trùng hoặc giao thời gian.');
            }

            $showId = $this->create([
                'movie_id' => $movieId,
                'room_id' => $roomId,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'base_price' => $basePrice,
                'status' => $data['status'] ?? 'selling',
            ]);

            $roomModel = new RoomModel();
            if (empty($roomModel->getRoomSeats($roomId))) {
                $roomModel->generateDefaultSeatsForRoom($roomId);
            }

            $insertSeats = $this->db->prepare("INSERT INTO show_seats
                (show_id, seat_id, price, status, created_at, updated_at)
                SELECT :show_id, s.id, (:base_price + st.surcharge),
                       CASE WHEN s.status = 'active' THEN 'available' ELSE 'blocked' END,
                       NOW(), NOW()
                FROM seats s INNER JOIN seat_types st ON st.id = s.seat_type_id
                WHERE s.room_id = :room_id");
            $insertSeats->execute([
                'show_id' => $showId,
                'base_price' => $basePrice,
                'room_id' => $roomId,
            ]);

            if ($insertSeats->rowCount() === 0) {
                throw new Exception('Phòng chiếu chưa có sơ đồ ghế hợp lệ; không thể tạo suất chiếu.');
            }

            $this->db->commit();
            return $showId;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Giải phóng các ghế đang giữ (held) đã quá hạn hold_expires_at
     */
    public function releaseExpiredHeldSeats(?int $showId = null): int
    {
        $sql = "UPDATE show_seats
                SET status = 'available', held_by_user_id = NULL, hold_expires_at = NULL, updated_at = NOW()
                WHERE status = 'held' AND hold_expires_at IS NOT NULL AND hold_expires_at < NOW()";
        $params = [];
        if ($showId !== null) {
            $sql .= " AND show_id = :show_id";
            $params['show_id'] = $showId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    public function getShowSeats(int $showId): array
    {
        $this->releaseExpiredHeldSeats($showId);

        $sql = "SELECT ss.*, s.seat_code, s.row_label, s.seat_number, st.name AS seat_type_name
                FROM show_seats ss
                INNER JOIN seats s ON s.id = ss.seat_id
                INNER JOIN seat_types st ON st.id = s.seat_type_id
                WHERE ss.show_id = :show_id
                ORDER BY s.row_label ASC, s.seat_number ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['show_id' => $showId]);
        return $stmt->fetchAll();
    }

    /**
     * Nghiệp vụ #2: Khi giữ ghế, dùng DB transaction + SELECT ... FOR UPDATE,
     * chỉ giữ nếu status='available' (hoặc đang được giữ bởi chính user đó); đặt hold_expires_at.
     */
    public function holdSeats(int $showId, array $showSeatIds, int $userId, int $holdMinutes = 10): array
    {
        if (empty($showSeatIds)) {
            throw new Exception('Vui lòng chọn ít nhất 1 ghế.');
        }

        $this->releaseExpiredHeldSeats($showId);

        $this->db->beginTransaction();
        try {
            $placeholders = implode(',', array_fill(0, count($showSeatIds), '?'));
            $sql = "SELECT ss.*, s.seat_code
                    FROM show_seats ss
                    INNER JOIN seats s ON s.id = ss.seat_id
                    WHERE ss.show_id = ? AND ss.id IN ({$placeholders})
                    FOR UPDATE";

            $params = array_merge([$showId], array_map('intval', $showSeatIds));
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $seats = $stmt->fetchAll();

            if (count($seats) !== count($showSeatIds)) {
                throw new Exception('Một số ghế không hợp lệ hoặc không thuộc suất chiếu này.');
            }

            foreach ($seats as $seat) {
                $isHeldByCurrentUser = ($seat['status'] === 'held' && (int) $seat['held_by_user_id'] === $userId);
                if ($seat['status'] !== 'available' && !$isHeldByCurrentUser) {
                    throw new Exception("Ghế {$seat['seat_code']} đã có người chọn hoặc không còn trống.");
                }
            }

            $expiresAt = date('Y-m-d H:i:s', time() + ($holdMinutes * 60));
            $sqlUpdate = "UPDATE show_seats
                          SET status = 'held',
                              held_by_user_id = ?,
                              hold_expires_at = ?,
                              updated_at = NOW()
                          WHERE show_id = ? AND id IN ({$placeholders})";

            $updateParams = array_merge([$userId, $expiresAt, $showId], array_map('intval', $showSeatIds));
            $stmtUpdate = $this->db->prepare($sqlUpdate);
            $stmtUpdate->execute($updateParams);

            $this->db->commit();
            return $seats;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}

