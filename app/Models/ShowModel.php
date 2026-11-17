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

    /**
     * Lấy tất cả suất chiếu đang mở bán nhóm theo movie_id (phục vụ popup MUA VÉ nhanh & trang Lịch chiếu theo ngày kiểu Beta Cinema)
     */
    public function getSellingShowsGroupedByMovie(?string $date = null): array
    {
        $whereDate = '';
        $params = [];
        if ($date) {
            $dateStart = \DateTimeImmutable::createFromFormat('!Y-m-d', $date);
            if (!$dateStart || $dateStart->format('Y-m-d') !== $date) {
                return [];
            }

            $whereDate = ' AND sh.start_time >= :date_start AND sh.start_time < :date_end ';
            $params['date_start'] = $dateStart->format('Y-m-d H:i:s');
            $params['date_end'] = $dateStart->modify('+1 day')->format('Y-m-d H:i:s');
        }

        $sql = "SELECT sh.*, r.name AS room_name, r.room_type,
                       (SELECT COUNT(*) FROM show_seats ss
                        WHERE ss.show_id = sh.id
                          AND (ss.status = 'available' OR (ss.status = 'held' AND ss.hold_expires_at <= NOW()))) AS available_seats
                FROM shows sh
                INNER JOIN rooms r ON r.id = sh.room_id
                WHERE sh.status IN ('scheduled', 'selling') {$whereDate}
                  AND sh.start_time > NOW()
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
                       (SELECT COUNT(*) FROM show_seats ss
                        WHERE ss.show_id = sh.id
                          AND (ss.status = 'available' OR (ss.status = 'held' AND ss.hold_expires_at <= NOW()))) AS available_seats,
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
        $startTime = $data['start_time'] ?? null;
        $endTime = $data['end_time'] ?? null;
        $startTimestamp = is_string($startTime) ? strtotime($startTime) : false;
        $endTimestamp = is_string($endTime) ? strtotime($endTime) : false;
        if ($startTimestamp === false || $endTimestamp === false || $startTimestamp <= time() || $endTimestamp <= $startTimestamp) {
            throw new Exception('Thời gian suất chiếu không hợp lệ.');
        }

        $roomId = (int) ($data['room_id'] ?? 0);
        $basePrice = (float) ($data['base_price'] ?? 0);
        if ($roomId <= 0 || $basePrice <= 0) {
            throw new Exception('Phòng chiếu và giá vé phải hợp lệ.');
        }

        $this->db->beginTransaction();
        try {
            $stmtRoom = $this->db->prepare('SELECT id FROM rooms WHERE id = :room_id FOR UPDATE');
            $stmtRoom->execute(['room_id' => $roomId]);
            if (!$stmtRoom->fetchColumn()) {
                throw new Exception('Phòng chiếu không tồn tại.');
            }

            $stmtOverlap = $this->db->prepare("
                SELECT id
                FROM shows
                WHERE room_id = :room_id
                  AND status <> 'cancelled'
                  AND start_time < :end_time
                  AND end_time > :start_time
                LIMIT 1
                FOR UPDATE
            ");
            $stmtOverlap->execute([
                'room_id' => $roomId,
                'start_time' => $startTime,
                'end_time' => $endTime,
            ]);
            if ($stmtOverlap->fetchColumn()) {
                throw new Exception('Phòng chiếu đã có suất khác trong khoảng thời gian này.');
            }

            $showId = $this->create([
                'movie_id' => (int) $data['movie_id'],
                'room_id' => $roomId,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'base_price' => $basePrice,
                'status' => $data['status'] ?? 'selling',
            ]);

            // Nếu phòng chưa có ghế thì tự khởi tạo sơ đồ ghế mặc định
            $roomModel = new RoomModel();
            $roomSeats = $roomModel->getRoomSeats($roomId);
            if (empty($roomSeats)) {
                $roomModel->generateDefaultSeatsForRoom($roomId);
            }

            // Copy toàn bộ ghế của phòng sang bảng show_seats
            $sqlInsertSeats = "
                INSERT INTO show_seats (show_id, seat_id, price, status, created_at, updated_at)
                SELECT :show_id, s.id, (:base_price + st.surcharge),
                       CASE WHEN s.status = 'active' THEN 'available' ELSE 'blocked' END,
                       NOW(), NOW()
                FROM seats s
                INNER JOIN seat_types st ON st.id = s.seat_type_id
                WHERE s.room_id = :room_id
            ";
            $stmt = $this->db->prepare($sqlInsertSeats);
            $stmt->execute([
                'show_id' => $showId,
                'base_price' => $basePrice,
                'room_id' => $roomId,
            ]);

            $this->db->commit();
            return $showId;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Giải phóng các ghế đang giữ (held) đã quá hạn hold_expires_at
     */
    public function releaseExpiredHeldSeats(?int $showId = null): int
    {
        $ownsTransaction = !$this->db->inTransaction();
        if ($ownsTransaction) {
            $this->db->beginTransaction();
        }

        try {
            $expiredBookingsSql = "SELECT b.id
                                   FROM bookings b
                                   WHERE b.status = 'pending'
                                     AND EXISTS (
                                         SELECT 1
                                         FROM booking_details bd
                                         INNER JOIN show_seats ss ON ss.id = bd.show_seat_id
                                         WHERE bd.booking_id = b.id
                                           AND (
                                               ss.status <> 'held'
                                               OR ss.held_by_user_id IS NULL
                                               OR ss.held_by_user_id <> b.user_id
                                               OR ss.hold_expires_at IS NULL
                                               OR ss.hold_expires_at <= NOW()
                                           )";
            $expiredBookingsParams = [];
            if ($showId !== null) {
                $expiredBookingsSql .= " AND ss.show_id = :expired_show_id";
                $expiredBookingsParams['expired_show_id'] = $showId;
            }
            $expiredBookingsSql .= ') FOR UPDATE';

            $stmtExpiredBookings = $this->db->prepare($expiredBookingsSql);
            $stmtExpiredBookings->execute($expiredBookingsParams);
            $expiredBookingIds = array_values(array_unique(array_map(
                'intval',
                $stmtExpiredBookings->fetchAll(\PDO::FETCH_COLUMN)
            )));

            if ($expiredBookingIds) {
                $placeholders = implode(',', array_fill(0, count($expiredBookingIds), '?'));
                $this->db->prepare("
                    UPDATE bookings
                    SET status = 'expired', updated_at = NOW()
                    WHERE status = 'pending' AND id IN ({$placeholders})
                ")->execute($expiredBookingIds);

                $this->db->prepare("
                    UPDATE concessions c
                    INNER JOIN (
                        SELECT concession_id, SUM(quantity) AS quantity
                        FROM booking_concessions
                        WHERE booking_id IN ({$placeholders})
                        GROUP BY concession_id
                    ) expired_items ON expired_items.concession_id = c.id
                    SET c.stock = c.stock + expired_items.quantity, c.updated_at = NOW()
                ")->execute($expiredBookingIds);

                $this->db->prepare("
                    UPDATE coupons c
                    INNER JOIN (
                        SELECT coupon_id, COUNT(*) AS usage_count
                        FROM coupon_usages
                        WHERE booking_id IN ({$placeholders})
                        GROUP BY coupon_id
                    ) expired_usage ON expired_usage.coupon_id = c.id
                    SET c.used_count = GREATEST(c.used_count - expired_usage.usage_count, 0),
                        c.updated_at = NOW()
                ")->execute($expiredBookingIds);

                $this->db->prepare("
                    DELETE FROM coupon_usages
                    WHERE booking_id IN ({$placeholders})
                ")->execute($expiredBookingIds);
            }

            $sql = "UPDATE show_seats
                    SET status = 'available', held_by_user_id = NULL, hold_expires_at = NULL, updated_at = NOW()
                    WHERE status = 'held' AND hold_expires_at IS NOT NULL AND hold_expires_at <= NOW()";
            $params = [];
            if ($showId !== null) {
                $sql .= " AND show_id = :show_id";
                $params['show_id'] = $showId;
            }
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $releasedCount = $stmt->rowCount();

            if ($ownsTransaction) {
                $this->db->commit();
            }
            return $releasedCount;
        } catch (\Throwable $e) {
            if ($ownsTransaction && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
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
        $showSeatIds = array_values(array_unique(array_filter(
            array_map(
                static function ($seatId): int {
                    if (!is_string($seatId) && !is_int($seatId)) {
                        return 0;
                    }
                    $validatedId = filter_var($seatId, FILTER_VALIDATE_INT);
                    return $validatedId === false ? 0 : (int) $validatedId;
                },
                $showSeatIds
            ),
            static fn (int $seatId): bool => $seatId > 0
        )));

        if (!$showSeatIds) {
            throw new Exception('Vui lòng chọn ít nhất 1 ghế.');
        }

        $this->releaseExpiredHeldSeats($showId);

        $this->db->beginTransaction();
        try {
            $stmtShow = $this->db->prepare("
                SELECT id
                FROM shows
                WHERE id = :show_id
                  AND status IN ('scheduled', 'selling')
                  AND start_time > NOW()
                FOR UPDATE
            ");
            $stmtShow->execute(['show_id' => $showId]);
            if (!$stmtShow->fetchColumn()) {
                throw new Exception('Suất chiếu không còn mở bán hoặc đã bắt đầu.');
            }

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
                if ($seat['status'] !== 'available') {
                    throw new Exception("Ghế {$seat['seat_code']} đã có người chọn hoặc không còn trống.");
                }
            }

            $holdMinutes = max(1, min(30, $holdMinutes));
            $sqlUpdate = "UPDATE show_seats
                          SET status = 'held',
                              held_by_user_id = ?,
                              hold_expires_at = DATE_ADD(NOW(), INTERVAL {$holdMinutes} MINUTE),
                              updated_at = NOW()
                          WHERE show_id = ? AND id IN ({$placeholders})";

            $updateParams = array_merge([$userId, $showId], array_map('intval', $showSeatIds));
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

