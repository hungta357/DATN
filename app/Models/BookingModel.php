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
}
