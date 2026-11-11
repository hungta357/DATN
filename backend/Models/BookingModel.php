<?php

namespace App\Models;

use App\Core\BaseModel;
use Exception;

/**
 * NHÁNH 5: BOOKINGS / BOOKING DETAILS / TICKETS
 * Bảng: bookings, booking_details, tickets
 * Lưu ý nghiệp vụ #3: Khi thanh toán thành công, cập nhật booking, show_seats và tạo tickets
 *                     trong cùng một DB transaction.
 */
class BookingModel extends BaseModel
{
    protected string $table = 'bookings';

    /**
     * Tạo đơn đặt vé (trạng thái pending) sau khi giữ ghế + chọn bắp nước + áp mã giảm giá
     */
    public function createPendingBooking(
        int $userId,
        int $showId,
        array $showSeatIds,
        array $concessionItems = [],
        ?string $couponCode = null,
        ?string $note = null
    ): array {
        $showModel = new ShowModel();
        // Bước 1: Giữ ghế bằng SELECT ... FOR UPDATE
        $heldSeats = $showModel->holdSeats($showId, $showSeatIds, $userId, 10);

        $this->db->beginTransaction();
        try {
            $subtotal = 0;
            foreach ($heldSeats as $seat) {
                $subtotal += (float) $seat['price'];
            }

            // Tính tiền combo bắp nước (concessions)
            $concessionTotal = 0;
            $validConcessions = [];
            if (!empty($concessionItems)) {
                $stmtCon = $this->db->prepare("SELECT * FROM concessions WHERE id = :id AND status = 'active' FOR UPDATE");
                foreach ($concessionItems as $conId => $qty) {
                    $qty = (int) $qty;
                    if ($qty <= 0) {
                        continue;
                    }
                    $stmtCon->execute(['id' => (int) $conId]);
                    $con = $stmtCon->fetch();
                    if ($con) {
                        if ((int) $con['stock'] < $qty) {
                            throw new Exception("Sản phẩm '{$con['name']}' không đủ tồn kho.");
                        }
                        $lineTotal = (float) $con['price'] * $qty;
                        $concessionTotal += $lineTotal;
                        $validConcessions[] = [
                            'concession_id' => (int) $con['id'],
                            'quantity' => $qty,
                            'unit_price' => (float) $con['price'],
                            'line_total' => $lineTotal,
                        ];
                    }
                }
            }

            // Kiểm tra và tính giảm giá coupon
            $discountAmount = 0;
            $couponId = null;
            $orderBeforeDiscount = $subtotal + $concessionTotal;

            if (!empty($couponCode)) {
                $engagementModel = new EngagementModel();
                $couponResult = $engagementModel->validateCoupon(trim($couponCode), $orderBeforeDiscount);
                $couponId = (int) $couponResult['coupon']['id'];
                $discountAmount = (float) $couponResult['discount_amount'];
            }

            $totalAmount = max(0, $orderBeforeDiscount - $discountAmount);
            $bookingCode = 'BK' . date('YmdHis') . rand(100, 999);

            $bookingId = $this->create([
                'booking_code' => $bookingCode,
                'user_id' => $userId,
                'show_id' => $showId,
                'coupon_id' => $couponId,
                'booking_time' => date('Y-m-d H:i:s'),
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'concession_total' => $concessionTotal,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'note' => $note,
            ]);

            // Lưu booking_details cho từng ghế
            $stmtDetail = $this->db->prepare("
                INSERT INTO booking_details (booking_id, show_seat_id, seat_price, created_at, updated_at)
                VALUES (:booking_id, :show_seat_id, :seat_price, NOW(), NOW())
            ");
            foreach ($heldSeats as $seat) {
                // Nếu có booking_details cũ của ghế bị hủy/hết hạn trước đó thì xóa trước khi tạo mới
                $this->db->prepare("DELETE bd FROM booking_details bd INNER JOIN bookings b ON b.id = bd.booking_id WHERE bd.show_seat_id = :ssid AND b.status IN ('cancelled', 'expired')")->execute(['ssid' => $seat['id']]);

                $stmtDetail->execute([
                    'booking_id' => $bookingId,
                    'show_seat_id' => $seat['id'],
                    'seat_price' => $seat['price'],
                ]);
            }

            // Lưu booking_concessions và trừ tồn kho
            if (!empty($validConcessions)) {
                $stmtBc = $this->db->prepare("
                    INSERT INTO booking_concessions (booking_id, concession_id, quantity, unit_price, line_total, created_at, updated_at)
                    VALUES (:booking_id, :concession_id, :quantity, :unit_price, :line_total, NOW(), NOW())
                ");
                $stmtStock = $this->db->prepare("UPDATE concessions SET stock = stock - :qty, updated_at = NOW() WHERE id = :id");
                foreach ($validConcessions as $vc) {
                    $stmtBc->execute([
                        'booking_id' => $bookingId,
                        'concession_id' => $vc['concession_id'],
                        'quantity' => $vc['quantity'],
                        'unit_price' => $vc['unit_price'],
                        'line_total' => $vc['line_total'],
                    ]);
                    $stmtStock->execute(['qty' => $vc['quantity'], 'id' => $vc['concession_id']]);
                }
            }

            // Lưu coupon_usages nếu có dùng mã giảm giá
            if ($couponId !== null && $discountAmount > 0) {
                $stmtCu = $this->db->prepare("
                    INSERT INTO coupon_usages (coupon_id, user_id, booking_id, discount_amount, used_at, created_at, updated_at)
                    VALUES (:coupon_id, :user_id, :booking_id, :discount_amount, NOW(), NOW(), NOW())
                ");
                $stmtCu->execute([
                    'coupon_id' => $couponId,
                    'user_id' => $userId,
                    'booking_id' => $bookingId,
                    'discount_amount' => $discountAmount,
                ]);

                $this->db->prepare("UPDATE coupons SET used_count = used_count + 1, updated_at = NOW() WHERE id = :id")
                         ->execute(['id' => $couponId]);
            }

            $this->db->commit();
            return $this->findBookingFull($bookingId);
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Nghiệp vụ #3: Khi thanh toán thành công, cập nhật booking, show_seats và tạo tickets
     * trong cùng một DB transaction.
     */
    public function confirmBookingAndIssueTickets(int $bookingId, int $paymentMethodId, string $paymentCode = 'CASH'): array
    {
        $this->db->beginTransaction();
        try {
            $stmtBooking = $this->db->prepare("SELECT * FROM bookings WHERE id = :id FOR UPDATE");
            $stmtBooking->execute(['id' => $bookingId]);
            $booking = $stmtBooking->fetch();

            if (!$booking) {
                throw new Exception('Không tìm thấy đơn đặt vé.');
            }
            if ($booking['status'] === 'confirmed') {
                $this->db->commit();
                return $this->findBookingFull($bookingId);
            }
            if ($booking['status'] !== 'pending') {
                throw new Exception('Đơn đặt vé không ở trạng thái chờ thanh toán.');
            }

            $amount = (float) $booking['total_amount'];
            $txCode = 'PAY' . date('YmdHis') . rand(1000, 9999);

            // Nếu thanh toán bằng Ví nội bộ (WALLET), kiểm tra & trừ số dư ví
            if ($paymentCode === 'WALLET') {
                $stmtWallet = $this->db->prepare("SELECT * FROM wallets WHERE user_id = :uid FOR UPDATE");
                $stmtWallet->execute(['uid' => $booking['user_id']]);
                $wallet = $stmtWallet->fetch();

                if (!$wallet || (float) $wallet['balance'] < $amount) {
                    throw new Exception('Số dư Ví nội bộ không đủ để thanh toán đơn này.');
                }

                $balanceAfter = (float) $wallet['balance'] - $amount;
                $this->db->prepare("UPDATE wallets SET balance = :bal, updated_at = NOW() WHERE id = :wid")
                         ->execute(['bal' => $balanceAfter, 'wid' => $wallet['id']]);

                $this->db->prepare("
                    INSERT INTO wallet_transactions (wallet_id, booking_id, type, amount, balance_after, transaction_code, note, status, created_at, updated_at)
                    VALUES (:wid, :bid, 'payment', :amount, :bal_after, :tx_code, :note, 'success', NOW(), NOW())
                ")->execute([
                    'wid' => $wallet['id'],
                    'bid' => $bookingId,
                    'amount' => $amount,
                    'bal_after' => $balanceAfter,
                    'tx_code' => 'WL' . date('YmdHis') . rand(100, 999),
                    'note' => 'Thanh toán đơn đặt vé #' . $booking['booking_code'],
                ]);
            }

            // 1. Tạo bản ghi thanh toán (payments)
            $stmtPay = $this->db->prepare("
                INSERT INTO payments (booking_id, payment_method_id, transaction_code, amount, status, paid_at, gateway_response, created_at, updated_at)
                VALUES (:bid, :pmid, :tx, :amount, 'success', NOW(), :gw, NOW(), NOW())
            ");
            $stmtPay->execute([
                'bid' => $bookingId,
                'pmid' => $paymentMethodId,
                'tx' => $txCode,
                'amount' => $amount,
                'gw' => json_encode(['provider' => $paymentCode, 'message' => 'Paid successfully']),
            ]);

            // 2. Cập nhật trạng thái booking -> confirmed
            $this->db->prepare("UPDATE bookings SET status = 'confirmed', updated_at = NOW() WHERE id = :id")
                     ->execute(['id' => $bookingId]);

            // 3. Cập nhật trạng thái show_seats -> booked và tạo vé (tickets) cho từng ghế
            $stmtDetails = $this->db->prepare("SELECT * FROM booking_details WHERE booking_id = :bid");
            $stmtDetails->execute(['bid' => $bookingId]);
            $details = $stmtDetails->fetchAll();

            $stmtUpdateSeat = $this->db->prepare("
                UPDATE show_seats
                SET status = 'booked', held_by_user_id = NULL, hold_expires_at = NULL, updated_at = NOW()
                WHERE id = :ssid
            ");
            $stmtTicket = $this->db->prepare("
                INSERT IGNORE INTO tickets (booking_detail_id, ticket_code, qr_code, status, created_at, updated_at)
                VALUES (:bd_id, :ticket_code, :qr_code, 'valid', NOW(), NOW())
            ");

            foreach ($details as $detail) {
                $stmtUpdateSeat->execute(['ssid' => $detail['show_seat_id']]);
                $ticketCode = 'TK-' . $booking['booking_code'] . '-' . $detail['id'];
                $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=' . urlencode($ticketCode);
                $stmtTicket->execute([
                    'bd_id' => $detail['id'],
                    'ticket_code' => $ticketCode,
                    'qr_code' => $qrUrl,
                ]);
            }

            // 4. Tạo thông báo (notifications) cho khách hàng
            $this->db->prepare("
                INSERT INTO notifications (user_id, title, message, type, created_at, updated_at)
                VALUES (:uid, :title, :msg, 'booking', NOW(), NOW())
            ")->execute([
                'uid' => $booking['user_id'],
                'title' => 'Đặt vé thành công #' . $booking['booking_code'],
                'msg' => 'Đơn đặt vé ' . $booking['booking_code'] . ' đã được thanh toán thành công. Chúc bạn xem phim vui vẻ!',
            ]);

            $this->db->commit();
            return $this->findBookingFull($bookingId);
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function findBookingFull(int $bookingId): ?array
    {
        $sql = "SELECT b.*, u.name AS user_name, u.email AS user_email, u.phone AS user_phone,
                       sh.start_time, sh.end_time, m.title AS movie_title, m.title_vi, m.poster_path,
                       r.name AS room_name
                FROM bookings b
                INNER JOIN users u ON u.id = b.user_id
                INNER JOIN shows sh ON sh.id = b.show_id
                INNER JOIN movies m ON m.id = sh.movie_id
                INNER JOIN rooms r ON r.id = sh.room_id
                WHERE b.id = :id
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $bookingId]);
        $booking = $stmt->fetch();
        if (!$booking) {
            return null;
        }

        // Lấy danh sách ghế & vé
        $sqlSeats = "SELECT bd.*, s.seat_code, s.row_label, s.seat_number, st.name AS seat_type_name,
                            t.ticket_code, t.qr_code, t.status AS ticket_status, t.checked_in_at
                     FROM booking_details bd
                     INNER JOIN show_seats ss ON ss.id = bd.show_seat_id
                     INNER JOIN seats s ON s.id = ss.seat_id
                     INNER JOIN seat_types st ON st.id = s.seat_type_id
                     LEFT JOIN tickets t ON t.booking_detail_id = bd.id
                     WHERE bd.booking_id = :bid";
        $stmtS = $this->db->prepare($sqlSeats);
        $stmtS->execute(['bid' => $bookingId]);
        $booking['seats'] = $stmtS->fetchAll();

        // Lấy danh sách combo bắp nước
        $sqlCon = "SELECT bc.*, c.name AS concession_name
                   FROM booking_concessions bc
                   INNER JOIN concessions c ON c.id = bc.concession_id
                   WHERE bc.booking_id = :bid";
        $stmtC = $this->db->prepare($sqlCon);
        $stmtC->execute(['bid' => $bookingId]);
        $booking['concessions'] = $stmtC->fetchAll();

        return $booking;
    }

    public function getUserBookings(int $userId): array
    {
        $sql = "SELECT b.*, sh.start_time, m.title AS movie_title, m.title_vi, r.name AS room_name
                FROM bookings b
                INNER JOIN shows sh ON sh.id = b.show_id
                INNER JOIN movies m ON m.id = sh.movie_id
                INNER JOIN rooms r ON r.id = sh.room_id
                WHERE b.user_id = :uid
                ORDER BY b.booking_time DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchAll();
    }

    public function getAllBookings(): array
    {
        $sql = "SELECT b.*, u.name AS user_name, u.email AS user_email,
                       sh.start_time, m.title AS movie_title, r.name AS room_name
                FROM bookings b
                INNER JOIN users u ON u.id = b.user_id
                INNER JOIN shows sh ON sh.id = b.show_id
                INNER JOIN movies m ON m.id = sh.movie_id
                INNER JOIN rooms r ON r.id = sh.room_id
                ORDER BY b.booking_time DESC";
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Soát vé (Check-in ticket) dành cho Staff / Admin
     */
    public function checkInTicketByCode(string $ticketCode): array
    {
        $stmt = $this->db->prepare("
            SELECT t.*, bd.booking_id, s.seat_code, b.booking_code, m.title AS movie_title, sh.start_time, r.name AS room_name
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
        ");
        $stmt->execute(['code' => trim($ticketCode)]);
        $ticket = $stmt->fetch();

        if (!$ticket) {
            throw new Exception('Mã vé không tồn tại trong hệ thống.');
        }
        if ($ticket['status'] === 'used') {
            throw new Exception("Vé {$ticketCode} đã được sử dụng lúc {$ticket['checked_in_at']}.");
        }
        if ($ticket['status'] !== 'valid') {
            throw new Exception("Vé {$ticketCode} không hợp lệ (Trạng thái: {$ticket['status']}).");
        }

        $this->db->prepare("UPDATE tickets SET status = 'used', checked_in_at = NOW(), updated_at = NOW() WHERE id = :id")
                 ->execute(['id' => $ticket['id']]);

        $ticket['status'] = 'used';
        $ticket['checked_in_at'] = date('Y-m-d H:i:s');
        return $ticket;
    }

    /**
     * THỐNG KÊ CHI TIẾT TOÀN HỆ THỐNG DÀNH CHO ADMIN (REACT ANALYTICS DASHBOARD)
     * Tương thích 100% với MySQL 8.0 ONLY_FULL_GROUP_BY
     */
    public function getDetailedAnalytics(): array
    {
        $safeQueryRow = function (string $sql, array $default = []): array {
            try {
                $res = $this->db->query($sql)->fetch();
                return $res ?: $default;
            } catch (\Throwable $e) {
                return $default;
            }
        };

        $safeQueryAll = function (string $sql): array {
            try {
                return $this->db->query($sql)->fetchAll() ?: [];
            } catch (\Throwable $e) {
                return [];
            }
        };

        // 1. Tổng hợp doanh thu & đơn vé
        $summary = $safeQueryRow("
            SELECT 
                COUNT(*) AS total_bookings,
                COALESCE(SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END), 0) AS confirmed_bookings,
                COALESCE(SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END), 0) AS pending_bookings,
                COALESCE(SUM(CASE WHEN status IN ('cancelled', 'expired', 'refunded') THEN 1 ELSE 0 END), 0) AS cancelled_bookings,
                COALESCE(SUM(CASE WHEN status = 'confirmed' THEN total_amount ELSE 0 END), 0) AS total_revenue,
                COALESCE(SUM(CASE WHEN status = 'confirmed' THEN subtotal ELSE 0 END), 0) AS ticket_revenue,
                COALESCE(SUM(CASE WHEN status = 'confirmed' THEN concession_total ELSE 0 END), 0) AS concession_revenue,
                COALESCE(SUM(CASE WHEN status = 'confirmed' THEN discount_amount ELSE 0 END), 0) AS total_discount
            FROM bookings
        ");

        // Tổng số vé bán ra (lượng khách xem) & số vé đã check-in tại rạp
        $ticketStats = $safeQueryRow("
            SELECT 
                COUNT(bd.id) AS total_viewers,
                COALESCE(SUM(CASE WHEN t.status = 'used' THEN 1 ELSE 0 END), 0) AS checked_in_tickets
            FROM booking_details bd
            INNER JOIN bookings b ON b.id = bd.booking_id AND b.status = 'confirmed'
            LEFT JOIN tickets t ON t.booking_detail_id = bd.id
        ");

        // Tỷ lệ lấp đầy ghế toàn rạp
        $seatOcc = $safeQueryRow("
            SELECT 
                COUNT(*) AS total_show_seats,
                COALESCE(SUM(CASE WHEN status = 'booked' THEN 1 ELSE 0 END), 0) AS booked_seats,
                COALESCE(SUM(CASE WHEN status = 'held' THEN 1 ELSE 0 END), 0) AS held_seats
            FROM show_seats
        ");
        $totalShowSeats = (int) ($seatOcc['total_show_seats'] ?? 0);
        $bookedSeats = (int) ($seatOcc['booked_seats'] ?? 0);
        $occupancyRate = $totalShowSeats > 0 ? round(($bookedSeats / $totalShowSeats) * 100, 1) : 0;

        // Tổng số khách hàng & tổng số dư ví
        $uwStats = $safeQueryRow("
            SELECT 
                (SELECT COUNT(*) FROM users) AS total_users,
                COALESCE((SELECT SUM(balance) FROM wallets), 0) AS total_wallet_balance
        ");

        // 2. Doanh thu & lượng khách theo ngày (Tương thích ONLY_FULL_GROUP_BY)
        $dailyRows = array_reverse($safeQueryAll("
            SELECT 
                d.report_date,
                d.bookings_count,
                d.total_revenue,
                d.ticket_revenue,
                d.concession_revenue,
                COALESCE(v.viewers_count, 0) AS viewers_count
            FROM (
                SELECT 
                    DATE(booking_time) AS report_date,
                    COUNT(id) AS bookings_count,
                    COALESCE(SUM(CASE WHEN status = 'confirmed' THEN total_amount ELSE 0 END), 0) AS total_revenue,
                    COALESCE(SUM(CASE WHEN status = 'confirmed' THEN subtotal ELSE 0 END), 0) AS ticket_revenue,
                    COALESCE(SUM(CASE WHEN status = 'confirmed' THEN concession_total ELSE 0 END), 0) AS concession_revenue
                FROM bookings
                GROUP BY DATE(booking_time)
            ) d
            LEFT JOIN (
                SELECT 
                    DATE(b2.booking_time) AS report_date,
                    COUNT(bd2.id) AS viewers_count
                FROM booking_details bd2
                INNER JOIN bookings b2 ON b2.id = bd2.booking_id
                WHERE b2.status = 'confirmed'
                GROUP BY DATE(b2.booking_time)
            ) v ON v.report_date = d.report_date
            ORDER BY d.report_date DESC
            LIMIT 14
        "));

        // 3. Thống kê chi tiết theo từng Bộ Phim
        $movieStats = $safeQueryAll("
            SELECT 
                m.id,
                m.title,
                m.title_vi,
                m.poster_path,
                m.status,
                m.imdb_rating,
                (SELECT COUNT(*) FROM shows sh WHERE sh.movie_id = m.id) AS show_count,
                COALESCE((
                    SELECT COUNT(bd.id)
                    FROM booking_details bd
                    INNER JOIN bookings b ON b.id = bd.booking_id AND b.status = 'confirmed'
                    INNER JOIN shows sh2 ON sh2.id = b.show_id
                    WHERE sh2.movie_id = m.id
                ), 0) AS tickets_sold,
                COALESCE((
                    SELECT COUNT(t.id)
                    FROM tickets t
                    INNER JOIN booking_details bd ON bd.id = t.booking_detail_id
                    INNER JOIN bookings b ON b.id = bd.booking_id AND b.status = 'confirmed'
                    INNER JOIN shows sh2 ON sh2.id = b.show_id
                    WHERE sh2.movie_id = m.id AND t.status = 'used'
                ), 0) AS checked_in_count,
                COALESCE((
                    SELECT COUNT(ss.id)
                    FROM show_seats ss
                    INNER JOIN shows sh3 ON sh3.id = ss.show_id
                    WHERE sh3.movie_id = m.id
                ), 0) AS total_capacity,
                COALESCE((
                    SELECT SUM(b.subtotal)
                    FROM bookings b
                    INNER JOIN shows sh4 ON sh4.id = b.show_id
                    WHERE sh4.movie_id = m.id AND b.status = 'confirmed'
                ), 0) AS ticket_revenue,
                COALESCE((
                    SELECT SUM(b.total_amount)
                    FROM bookings b
                    INNER JOIN shows sh5 ON sh5.id = b.show_id
                    WHERE sh5.movie_id = m.id AND b.status = 'confirmed'
                ), 0) AS total_revenue
            FROM movies m
            ORDER BY total_revenue DESC, tickets_sold DESC, m.id DESC
        ");
        foreach ($movieStats as &$ms) {
            $cap = (int) ($ms['total_capacity'] ?? 0);
            $sold = (int) ($ms['tickets_sold'] ?? 0);
            $ms['occupancy_rate'] = $cap > 0 ? round(($sold / $cap) * 100, 1) : 0;
        }
        unset($ms);

        // 4. Thống kê theo Phòng chiếu
        $roomStats = $safeQueryAll("
            SELECT 
                r.id,
                r.name,
                r.room_type,
                r.capacity,
                (SELECT COUNT(*) FROM shows sh WHERE sh.room_id = r.id) AS show_count,
                COALESCE((
                    SELECT COUNT(bd.id)
                    FROM booking_details bd
                    INNER JOIN bookings b ON b.id = bd.booking_id AND b.status = 'confirmed'
                    INNER JOIN shows sh2 ON sh2.id = b.show_id
                    WHERE sh2.room_id = r.id
                ), 0) AS tickets_sold,
                COALESCE((
                    SELECT COUNT(ss.id)
                    FROM show_seats ss
                    INNER JOIN shows sh3 ON sh3.id = ss.show_id
                    WHERE sh3.room_id = r.id
                ), 0) AS total_show_seats,
                COALESCE((
                    SELECT SUM(b.total_amount)
                    FROM bookings b
                    INNER JOIN shows sh4 ON sh4.id = b.show_id
                    WHERE sh4.room_id = r.id AND b.status = 'confirmed'
                ), 0) AS revenue
            FROM rooms r
            ORDER BY revenue DESC, r.id ASC
        ");
        foreach ($roomStats as &$rs) {
            $totSeats = (int) ($rs['total_show_seats'] ?? 0);
            $sold = (int) ($rs['tickets_sold'] ?? 0);
            $rs['occupancy_rate'] = $totSeats > 0 ? round(($sold / $totSeats) * 100, 1) : 0;
        }
        unset($rs);

        // 5. Thống kê theo Phương thức Thanh toán
        $paymentStats = $safeQueryAll("
            SELECT 
                pm.id,
                pm.name AS method_name,
                pm.code AS method_code,
                (SELECT COUNT(*) FROM payments p WHERE p.payment_method_id = pm.id AND p.status = 'success') AS tx_count,
                COALESCE((SELECT SUM(p2.amount) FROM payments p2 WHERE p2.payment_method_id = pm.id AND p2.status = 'success'), 0) AS total_amount
            FROM payment_methods pm
            ORDER BY total_amount DESC, pm.id ASC
        ");

        // 6. Thống kê Combo Bắp nước bán chạy
        $concessionStats = $safeQueryAll("
            SELECT 
                c.id,
                c.name,
                c.category,
                c.price,
                c.stock,
                COALESCE((
                    SELECT SUM(bc.quantity)
                    FROM booking_concessions bc
                    INNER JOIN bookings b ON b.id = bc.booking_id AND b.status = 'confirmed'
                    WHERE bc.concession_id = c.id
                ), 0) AS sold_qty,
                COALESCE((
                    SELECT SUM(bc.line_total)
                    FROM booking_concessions bc
                    INNER JOIN bookings b ON b.id = bc.booking_id AND b.status = 'confirmed'
                    WHERE bc.concession_id = c.id
                ), 0) AS revenue
            FROM concessions c
            ORDER BY sold_qty DESC, revenue DESC
        ");

        // 7. Top Khách hàng đặt vé & chi tiêu cao nhất
        $topCustomers = $safeQueryAll("
            SELECT 
                u.id,
                u.name,
                u.email,
                u.phone,
                COALESCE((SELECT w.balance FROM wallets w WHERE w.user_id = u.id LIMIT 1), 0) AS wallet_balance,
                (SELECT COUNT(*) FROM bookings b WHERE b.user_id = u.id AND b.status = 'confirmed') AS bookings_count,
                COALESCE((
                    SELECT COUNT(bd.id)
                    FROM booking_details bd
                    INNER JOIN bookings b2 ON b2.id = bd.booking_id
                    WHERE b2.user_id = u.id AND b2.status = 'confirmed'
                ), 0) AS tickets_bought,
                COALESCE((
                    SELECT SUM(b3.total_amount)
                    FROM bookings b3
                    WHERE b3.user_id = u.id AND b3.status = 'confirmed'
                ), 0) AS total_spent
            FROM users u
            ORDER BY total_spent DESC, tickets_bought DESC
            LIMIT 10
        ");

        return [
            'generated_at' => date('H:i:s d/m/Y'),
            'summary' => [
                'total_revenue' => (float) ($summary['total_revenue'] ?? 0),
                'ticket_revenue' => (float) ($summary['ticket_revenue'] ?? 0),
                'concession_revenue' => (float) ($summary['concession_revenue'] ?? 0),
                'total_discount' => (float) ($summary['total_discount'] ?? 0),
                'total_bookings' => (int) ($summary['total_bookings'] ?? 0),
                'confirmed_bookings' => (int) ($summary['confirmed_bookings'] ?? 0),
                'pending_bookings' => (int) ($summary['pending_bookings'] ?? 0),
                'cancelled_bookings' => (int) ($summary['cancelled_bookings'] ?? 0),
                'total_viewers' => (int) ($ticketStats['total_viewers'] ?? 0),
                'checked_in_tickets' => (int) ($ticketStats['checked_in_tickets'] ?? 0),
                'total_show_seats' => $totalShowSeats,
                'booked_seats' => $bookedSeats,
                'held_seats' => (int) ($seatOcc['held_seats'] ?? 0),
                'occupancy_rate' => $occupancyRate,
                'total_users' => (int) ($uwStats['total_users'] ?? 0),
                'total_wallet_balance' => (float) ($uwStats['total_wallet_balance'] ?? 0),
            ],
            'daily_revenue' => $dailyRows,
            'movie_stats' => $movieStats,
            'room_stats' => $roomStats,
            'payment_stats' => $paymentStats,
            'concession_stats' => $concessionStats,
            'top_customers' => $topCustomers,
        ];
    }
}

