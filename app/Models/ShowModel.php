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
        $rows = $stmt->fetchAll();

        if (empty($rows)) {
            $stmtMovie = $this->db->prepare("SELECT status FROM movies WHERE id = :id LIMIT 1");
            $stmtMovie->execute(['id' => $movieId]);
            $movieStatus = $stmtMovie->fetchColumn();
            if ($movieStatus === 'now_showing') {
                $created = $this->ensureUpcomingShowsForMovie($movieId, 65000, 3);
                if ($created > 0) {
                    $stmt->execute(['movie_id' => $movieId]);
                    $rows = $stmt->fetchAll();
                }
            }
        }

        return $rows;
    }

    /**
     * Lấy tất cả suất chiếu đang mở bán nhóm theo movie_id (phục vụ popup MUA VÉ nhanh & trang Lịch chiếu theo ngày kiểu Beta Cinema)
     * Hỗ trợ xuất chiếu sớm: suất của phim coming_soon vẫn hiện khi đến đúng ngày.
     */
    public function getSellingShowsGroupedByMovie(?string $date = null): array
    {
        // Tự chuyển phim sắp chiếu → đang chiếu khi đã đến ngày phát hành
        $movieModel = new MovieModel();
        $promotedIds = $movieModel->promoteMoviesDueForRelease();
        foreach ($promotedIds as $pid) {
            $this->ensureUpcomingShowsForMovie((int) $pid, 65000, 3);
        }

        // Đảm bảo các phim đang chiếu đều có suất chiếu mở bán trong 7 ngày tới
        $stmtMissing = $this->db->query("
            SELECT m.id
            FROM movies m
            WHERE m.status = 'now_showing'
              AND NOT EXISTS (
                  SELECT 1 FROM shows sh
                  WHERE sh.movie_id = m.id
                    AND sh.status IN ('scheduled', 'selling')
                    AND sh.start_time > NOW()
              )
        ");
        $missingMovieIds = $stmtMissing->fetchAll(\PDO::FETCH_COLUMN);
        foreach ($missingMovieIds as $mid) {
            $this->ensureUpcomingShowsForMovie((int) $mid, 65000, 3);
        }

        // Suất đã lên lịch (scheduled) trong cửa sổ hôm nay + 7 ngày → mở bán (selling)
        $this->activateScheduledShowsInWindow(7);

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

    /**
     * Xuất chiếu sớm: khi suất (status=scheduled) bước vào cửa sổ hôm nay + $days ngày,
     * tự chuyển sang selling để khách đặt vé được ngay.
     */
    public function activateScheduledShowsInWindow(int $days = 7): int
    {
        $days = max(1, min(14, $days));
        $windowEnd = date('Y-m-d H:i:s', strtotime("+{$days} days"));
        $sql = "UPDATE shows
                SET status = 'selling', updated_at = NOW()
                WHERE status = 'scheduled'
                  AND start_time > NOW()
                  AND start_time <= :window_end";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['window_end' => $windowEnd]);
        return $stmt->rowCount();
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
     * Cập nhật thông tin suất chiếu (giờ chiếu, phòng, giá vé, trạng thái) và đồng bộ giá ghế trống
     */
    public function updateShowWithSeats(int $showId, array $data): void
    {
        $existing = $this->find($showId);
        if (!$existing) {
            throw new Exception('Suất chiếu không tồn tại.');
        }

        $movieId = (int) ($data['movie_id'] ?? $existing['movie_id']);
        $roomId = (int) ($data['room_id'] ?? $existing['room_id']);
        $basePrice = (float) ($data['base_price'] ?? $existing['base_price']);
        $status = (string) ($data['status'] ?? $existing['status']);
        $startTime = $data['start_time'] ?? $existing['start_time'];
        $endTime = $data['end_time'] ?? $existing['end_time'];

        $startTimestamp = is_string($startTime) ? strtotime($startTime) : false;
        $endTimestamp = is_string($endTime) ? strtotime($endTime) : false;
        if ($startTimestamp === false || $endTimestamp === false || $endTimestamp <= $startTimestamp) {
            throw new Exception('Thời gian suất chiếu không hợp lệ.');
        }
        if ($roomId <= 0 || $basePrice <= 0) {
            throw new Exception('Phòng chiếu và giá vé phải hợp lệ.');
        }

        $this->db->beginTransaction();
        try {
            if ($status !== 'cancelled') {
                $stmtOverlap = $this->db->prepare("
                    SELECT id
                    FROM shows
                    WHERE room_id = :room_id
                      AND id <> :show_id
                      AND status <> 'cancelled'
                      AND start_time < :end_time
                      AND end_time > :start_time
                    LIMIT 1
                    FOR UPDATE
                ");
                $stmtOverlap->execute([
                    'room_id' => $roomId,
                    'show_id' => $showId,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                ]);
                if ($stmtOverlap->fetchColumn()) {
                    throw new Exception('Phòng chiếu đã có suất khác trùng trong khoảng thời gian này.');
                }
            }

            $this->update($showId, [
                'movie_id' => $movieId,
                'room_id' => $roomId,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'base_price' => $basePrice,
                'status' => $status,
            ]);

            // Đảm bảo phòng có sơ đồ ghế và show_seats
            $roomModel = new RoomModel();
            if (empty($roomModel->getRoomSeats($roomId))) {
                $roomModel->generateDefaultSeatsForRoom($roomId);
            }

            $stmtInsertMissing = $this->db->prepare("
                INSERT IGNORE INTO show_seats (show_id, seat_id, price, status, created_at, updated_at)
                SELECT :show_id, s.id, (:base_price + st.surcharge),
                       CASE WHEN s.status = 'active' THEN 'available' ELSE 'blocked' END,
                       NOW(), NOW()
                FROM seats s
                INNER JOIN seat_types st ON st.id = s.seat_type_id
                WHERE s.room_id = :room_id
            ");
            $stmtInsertMissing->execute([
                'show_id' => $showId,
                'base_price' => $basePrice,
                'room_id' => $roomId,
            ]);

            // Cập nhật lại giá cho các ghế còn trống
            $stmtUpdatePrices = $this->db->prepare("
                UPDATE show_seats ss
                INNER JOIN seats s ON s.id = ss.seat_id
                INNER JOIN seat_types st ON st.id = s.seat_type_id
                SET ss.price = (:base_price + st.surcharge), ss.updated_at = NOW()
                WHERE ss.show_id = :show_id AND ss.status = 'available'
            ");
            $stmtUpdatePrices->execute([
                'base_price' => $basePrice,
                'show_id' => $showId,
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
     * Xóa suất chiếu nếu chưa có đơn đặt thành công, hoặc chuyển sang trạng thái đã hủy
     */
    public function deleteOrCancelShow(int $showId): string
    {
        $stmtCheck = $this->db->prepare("SELECT COUNT(*) FROM bookings WHERE show_id = :id AND status IN ('confirmed', 'pending')");
        $stmtCheck->execute(['id' => $showId]);
        $activeBookings = (int) $stmtCheck->fetchColumn();

        if ($activeBookings > 0) {
            $this->update($showId, ['status' => 'cancelled']);
            return 'cancelled';
        }

        $this->db->beginTransaction();
        try {
            $this->db->prepare("DELETE FROM bookings WHERE show_id = :id")->execute(['id' => $showId]);
            $this->db->prepare("DELETE FROM show_seats WHERE show_id = :id")->execute(['id' => $showId]);
            $this->db->prepare("DELETE FROM shows WHERE id = :id")->execute(['id' => $showId]);
            $this->db->commit();
            return 'deleted';
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $this->update($showId, ['status' => 'cancelled']);
            return 'cancelled';
        }
    }

    /**
     * Đảm bảo một bộ phim đang chiếu có ít nhất $minShows suất chiếu mở bán trong tương lai
     */
    public function ensureUpcomingShowsForMovie(int $movieId, float $basePrice = 65000, int $minShows = 3): int
    {
        $stmtMovie = $this->db->prepare("SELECT id, runtime, status FROM movies WHERE id = :id LIMIT 1");
        $stmtMovie->execute(['id' => $movieId]);
        $movie = $stmtMovie->fetch();
        if (!$movie) {
            return 0;
        }

        $stmtCount = $this->db->prepare("
            SELECT COUNT(*) FROM shows
            WHERE movie_id = :mid
              AND status IN ('scheduled', 'selling')
              AND start_time > NOW()
        ");
        $stmtCount->execute(['mid' => $movieId]);
        $existingFutureCount = (int) $stmtCount->fetchColumn();

        if ($existingFutureCount >= $minShows) {
            return 0;
        }

        $rooms = $this->db->query("SELECT id FROM rooms WHERE status = 'active' ORDER BY id ASC")->fetchAll();
        if (empty($rooms)) {
            $roomModel = new RoomModel();
            $newRoomId = $roomModel->create([
                'name' => 'Phòng Chiếu 01 - Standard',
                'room_type' => 'standard',
                'capacity' => 0,
                'status' => 'active',
                'description' => 'Phòng chiếu tiêu chuẩn',
            ]);
            $roomModel->generateDefaultSeatsForRoom($newRoomId);
            $rooms = [['id' => $newRoomId]];
        }

        $runtime = max(90, (int) ($movie['runtime'] ?? 120));
        $slots = ['09:30:00', '13:00:00', '16:30:00', '19:30:00', '22:15:00'];
        $created = 0;
        $needed = $minShows - $existingFutureCount;

        $stmtOverlap = $this->db->prepare("
            SELECT id FROM shows
            WHERE room_id = :room_id
              AND status <> 'cancelled'
              AND start_time < :end_time
              AND end_time > :start_time
            LIMIT 1
        ");

        for ($dayOffset = 0; $dayOffset <= 6 && $created < $needed; $dayOffset++) {
            $dateStr = date('Y-m-d', strtotime("+{$dayOffset} days"));
            foreach ($slots as $slot) {
                if ($created >= $needed) {
                    break;
                }
                $startAt = $dateStr . ' ' . $slot;
                $startTs = strtotime($startAt);
                if ($startTs === false || $startTs <= time() + 1800) {
                    continue;
                }
                $endAt = date('Y-m-d H:i:s', $startTs + ($runtime * 60));

                foreach ($rooms as $rIdx => $room) {
                    $roomId = (int) $room['id'];
                    $stmtOverlap->execute([
                        'room_id' => $roomId,
                        'start_time' => $startAt,
                        'end_time' => $endAt,
                    ]);
                    if ($stmtOverlap->fetchColumn()) {
                        continue;
                    }

                    try {
                        $this->createShowWithSeats([
                            'movie_id' => $movieId,
                            'room_id' => $roomId,
                            'start_time' => $startAt,
                            'end_time' => $endAt,
                            'base_price' => $basePrice + ($rIdx % 2 === 1 ? 10000 : 0),
                            'status' => 'selling',
                        ]);
                        $created++;
                        break;
                    } catch (\Throwable $e) {
                        continue;
                    }
                }
            }
        }

        return $created;
    }

    /**
     * Tự động tạo lịch chiếu mở bán cho tất cả phim Đang chiếu (now_showing) chưa có suất chiếu tương lai
     */
    public function autoGenerateShowsForNowShowingMovies(int $minShowsPerMovie = 3): int
    {
        $movies = $this->db->query("SELECT id FROM movies WHERE status = 'now_showing' ORDER BY id ASC")->fetchAll();
        $totalCreated = 0;
        foreach ($movies as $m) {
            $totalCreated += $this->ensureUpcomingShowsForMovie((int) $m['id'], 65000, $minShowsPerMovie);
        }
        return $totalCreated;
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

