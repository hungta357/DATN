<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\BookingModel;
use App\Models\EngagementModel;
use App\Models\PaymentWalletModel;
use App\Models\ShowModel;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Nếu dùng Composer thì bật dòng này:
require_once __DIR__ . '/../../vendor/autoload.php';

class BookingController extends BaseController
{
    public function selectSeats(): void
    {
        $user = $this->requireAuth();
        $showIdInput = $_GET['show_id'] ?? null;
        $showId = is_string($showIdInput) || is_int($showIdInput)
            ? filter_var($showIdInput, FILTER_VALIDATE_INT)
            : false;
        $showId = $showId === false ? 0 : (int) $showId;

        $showModel = new ShowModel();
        $engagementModel = new EngagementModel();

        $show = $showModel->findShowWithDetails($showId);
        if (
            !$show ||
            !in_array($show['status'], ['scheduled', 'selling'], true) ||
            strtotime($show['start_time']) <= time()
        ) {
            $this->setFlash('danger', 'Suất chiếu không tồn tại, đã bắt đầu hoặc đã đóng bán.');
            $this->redirect('?route=home');
        }

        $showSeats = $showModel->getShowSeats($showId);
        $concessions = $engagementModel->getActiveConcessions();
        $coupons = $engagementModel->getAllCoupons();

        $this->view('booking/select_seats', [
            'pageTitle' => 'Chọn ghế - ' . $show['movie_title'],
            'show' => $show,
            'showSeats' => $showSeats,
            'concessions' => $concessions,
            'coupons' => $coupons,
            'user' => $user,
        ]);
    }

    public function create(): void
    {
        $user = $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?route=home');
        }

        $showIdInput = $_POST['show_id'] ?? null;
        $showId = is_string($showIdInput) || is_int($showIdInput)
            ? filter_var($showIdInput, FILTER_VALIDATE_INT)
            : false;
        $showId = $showId === false ? 0 : (int) $showId;
        $showSeatIds = $_POST['show_seat_ids'] ?? [];
        $showSeatIds = is_array($showSeatIds) ? $showSeatIds : [];
        $concessions = $_POST['concessions'] ?? [];
        $concessions = is_array($concessions) ? $concessions : [];
        $couponInput = $_POST['coupon_code'] ?? '';
        $noteInput = $_POST['note'] ?? '';
        $couponCode = is_string($couponInput) ? trim($couponInput) : '';
        $note = is_string($noteInput) ? trim($noteInput) : '';

        try {
            $bookingModel = new BookingModel();
            $booking = $bookingModel->createPendingBooking(
                (int) $user['id'],
                $showId,
                $showSeatIds,
                $concessions,
                $couponCode ?: null,
                $note ?: null
            );

            $this->setFlash('success', 'Đã giữ ghế trong 10 phút! Vui lòng hoàn tất thanh toán.');
            $this->redirect('?route=booking/checkout&id=' . $booking['id']);
        } catch (\Throwable $e) {
            $this->setFlash('danger', $e->getMessage());
            $this->redirect('?route=booking/seats&show_id=' . $showId);
        }
    }

    public function checkout(): void
    {
        $user = $this->requireAuth();
        $bookingIdInput = $_GET['id'] ?? null;
        $bookingId = is_string($bookingIdInput) || is_int($bookingIdInput)
            ? filter_var($bookingIdInput, FILTER_VALIDATE_INT)
            : false;
        $bookingId = $bookingId === false ? 0 : (int) $bookingId;

        $bookingModel = new BookingModel();
        $paymentWalletModel = new PaymentWalletModel();

        $booking = $bookingModel->findBookingFull($bookingId);
        if (!$booking || (int) $booking['user_id'] !== (int) $user['id']) {
            $this->setFlash('danger', 'Không tìm thấy đơn đặt vé.');
            $this->redirect('?route=booking/my');
        }

        if ($booking['status'] === 'pending') {
            (new ShowModel())->releaseExpiredHeldSeats((int) $booking['show_id']);
            $booking = $bookingModel->findBookingFull($bookingId);
        }

        $paymentMethods = $booking['status'] === 'pending'
            ? $paymentWalletModel->getActivePaymentMethods()
            : [];
        $wallet = $booking['status'] === 'pending'
            ? $paymentWalletModel->getOrCreateWallet((int) $user['id'])
            : null;

        $this->view('booking/checkout', [
            'pageTitle' => 'Thanh toán & Vé #' . $booking['booking_code'],
            'booking' => $booking,
            'paymentMethods' => $paymentMethods,
            'wallet' => $wallet,
        ]);
    }

    public function pay(): void
    {
        $user = $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?route=booking/my');
        }

        $bookingIdInput = $_POST['booking_id'] ?? null;
        $bookingId = is_string($bookingIdInput) || is_int($bookingIdInput)
            ? filter_var($bookingIdInput, FILTER_VALIDATE_INT)
            : false;
        $bookingId = $bookingId === false ? 0 : (int) $bookingId;
        
        $paymentMethodInput = $_POST['payment_method_id'] ?? null;
        $paymentMethodId = is_string($paymentMethodInput) || is_int($paymentMethodInput)
            ? filter_var($paymentMethodInput, FILTER_VALIDATE_INT)
            : false;
        $paymentMethodId = $paymentMethodId === false ? 0 : (int) $paymentMethodId;

        $bookingModel = new BookingModel();
        $paymentWalletModel = new PaymentWalletModel();

        $booking = $bookingModel->findBookingFull($bookingId);
        if (!$booking || (int) $booking['user_id'] !== (int) $user['id']) {
            $this->setFlash('danger', 'Đơn hàng không hợp lệ.');
            $this->redirect('?route=booking/my');
        }

        $methods = $paymentWalletModel->getActivePaymentMethods();
        $selectedCode = 'CASH';
        foreach ($methods as $m) {
            if ((int) $m['id'] === $paymentMethodId) {
                $selectedCode = $m['code'];
                break;
            }
        }

        try {
            // 1. Xác nhận đơn hàng và phát hành vé
            $bookingModel->confirmBookingAndIssueTickets($bookingId, $paymentMethodId);

            // 2. Lấy lại toàn bộ thông tin mới nhất sau khi đã phát hành vé để đổ vào email
            $updatedBooking = $bookingModel->findBookingFull($bookingId);

            // 3. Tích hợp gửi mail tự động qua PHPMailer
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'tuanp201100@gmail.com'; 
                $mail->Password   = 'hflx pofr utyh wqld';    
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;
                $mail->Timeout    = 4;
                $mail->CharSet    = 'UTF-8';

                $customerName = $user['name'] ?? $user['full_name'] ?? 'Khách hàng';
                $mail->setFrom('tuanp201100@gmail.com', 'CineNow Cinema');
                $mail->addAddress($user['email'], $customerName);

                $orderCode  = $updatedBooking['booking_code'] ?? '';
                $movieTitle = $updatedBooking['movie_title'] ?? '';
                $roomName   = $updatedBooking['room_name'] ?? '';
                $showTime   = $updatedBooking['start_time'] ?? '';
                
                $ticketCode = $orderCode;
                $seatsText = '';
                if (!empty($updatedBooking['seats'])) {
                    $seatArr = [];
                    foreach ($updatedBooking['seats'] as $t) {
                        $seatArr[] = ($t['seat_code'] ?? $t['seat_number'] ?? '') . ' (' . ($t['seat_type_name'] ?? 'Standard') . ')';
                        $ticketCode = $t['ticket_code'] ?? $ticketCode;
                    }
                    $seatsText = implode(', ', $seatArr);
                }

                $concessionText = 'Không có';
                if (!empty($updatedBooking['concessions'])) {
                    $conArr = [];
                    foreach ($updatedBooking['concessions'] as $c) {
                        $conArr[] = ($c['concession_name'] ?? $c['name'] ?? 'Combo') . ' x ' . ($c['quantity'] ?? 1);
                    }
                    $concessionText = implode(', ', $conArr);
                }

                $totalAmount = number_format($updatedBooking['total_amount'] ?? 0, 0, ',', '.') . ' đ';
                $qrCodeUrl   = "https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=" . urlencode($ticketCode);

                $mail->isHTML(true);
                $mail->Subject = '[CineNow] Xác nhận đặt vé thành công - Đơn hàng #' . $orderCode;
                $mail->Body    = "
                    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden;'>
                        <div style='background-color: #0d6efd; color: white; padding: 20px; text-align: center;'>
                            <h2 style='margin: 0; font-size: 22px;'>VÉ ĐIỆN TỬ CINENOW</h2>
                            <p style='margin: 5px 0 0 0; font-size: 14px;'>Rạp chiếu phim độc lập</p>
                        </div>
                        <div style='padding: 20px; background-color: #fafafa;'>
                            <p>Xin chào <strong>" . htmlspecialchars($customerName, ENT_QUOTES, 'UTF-8') . "</strong>,</p>
                            <p>Cảm ơn bạn đã đặt vé tại CineNow. Giao dịch thanh toán của bạn đã được xác nhận thành công.</p>
                            
                            <table style='width: 100%; border-collapse: collapse; margin: 15px 0; background: #fff; border-radius: 6px;'>
                                <tr><td style='padding: 10px; border-bottom: 1px solid #eee;'><strong>Mã đơn hàng:</strong></td><td style='padding: 10px; border-bottom: 1px solid #eee; color: #0d6efd;'>#{$orderCode}</td></tr>
                                <tr><td style='padding: 10px; border-bottom: 1px solid #eee;'><strong>Phim:</strong></td><td style='padding: 10px; border-bottom: 1px solid #eee;'><strong>{$movieTitle}</strong></td></tr>
                                <tr><td style='padding: 10px; border-bottom: 1px solid #eee;'><strong>Phòng chiếu:</strong></td><td style='padding: 10px; border-bottom: 1px solid #eee;'>{$roomName}</td></tr>
                                <tr><td style='padding: 10px; border-bottom: 1px solid #eee;'><strong>Suất chiếu:</strong></td><td style='padding: 10px; border-bottom: 1px solid #eee;'>{$showTime}</td></tr>
                                <tr><td style='padding: 10px; border-bottom: 1px solid #eee;'><strong>Ghế đã chọn:</strong></td><td style='padding: 10px; border-bottom: 1px solid #eee; color: #d63384; font-weight: bold;'>{$seatsText}</td></tr>
                                <tr><td style='padding: 10px; border-bottom: 1px solid #eee;'><strong>Combo bắp nước:</strong></td><td style='padding: 10px; border-bottom: 1px solid #eee;'>{$concessionText}</td></tr>
                                <tr><td style='padding: 10px;'><strong>Tổng thanh toán:</strong></td><td style='padding: 10px; color: #dc3545; font-weight: bold; font-size: 16px;'>{$totalAmount}</td></tr>
                            </table>

                            <div style='text-align: center; margin-top: 25px; background: #fff; padding: 15px; border-radius: 6px; border: 1px dashed #ccc;'>
                                <p style='margin-bottom: 10px; font-weight: bold; color: #333;'>Mã QR Check-in tại quầy:</p>
                                <img src='{$qrCodeUrl}' alt='QR Code Vé' style='border: 4px solid #fff; box-shadow: 0 0 5px rgba(0,0,0,0.1);' />
                                <p style='color: #666; font-size: 12px; margin-top: 8px;'>Mã vé: <strong>{$ticketCode}</strong></p>
                            </div>
                            <p style='font-size: 13px; color: #777; text-align: center; margin-top: 20px;'>Vui lòng đến rạp trước giờ chiếu 15 phút để check-in. Chúc bạn xem phim vui vẻ!</p>
                        </div>
                    </div>
                ";
                $mail->send();
            } catch (\Exception $mailEx) {
                error_log("Email sending failed: " . $mailEx->getMessage());
            }

            $this->setFlash('success', 'Thanh toán thành công! Vé điện tử, mã QR đã được phát hành và gửi về email của bạn.');
            $this->redirect('?route=booking/checkout&id=' . $bookingId);
        } catch (\Throwable $e) {
            $this->setFlash('danger', 'Lỗi thanh toán: ' . $e->getMessage());
            $this->redirect('?route=booking/checkout&id=' . $bookingId);
        }
    }

    public function myBookings(): void
    {
        $user = $this->requireAuth();
        $bookingModel = new BookingModel();
        $bookings = $bookingModel->getUserBookings((int) $user['id']);

        $this->view('booking/my_bookings', [
            'pageTitle' => 'Vé đã đặt của tôi',
            'bookings' => $bookings,
        ]);
    }
}