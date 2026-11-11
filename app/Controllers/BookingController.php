<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\BookingModel;
use App\Models\EngagementModel;
use App\Models\PaymentWalletModel;
use App\Models\ShowModel;

class BookingController extends BaseController
{
    public function selectSeats(): void
    {
        $user = $this->requireAuth();
        $showId = (int) ($_GET['show_id'] ?? 0);

        $showModel = new ShowModel();
        $engagementModel = new EngagementModel();

        $show = $showModel->findShowWithDetails($showId);
        if (!$show) {
            $this->setFlash('danger', 'Suất chiếu không tồn tại.');
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

        $showId = (int) ($_POST['show_id'] ?? 0);
        $showSeatIds = $_POST['show_seat_ids'] ?? [];
        $concessions = $_POST['concessions'] ?? [];
        $couponCode = trim($_POST['coupon_code'] ?? '');
        $note = trim($_POST['note'] ?? '');

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
        $bookingId = (int) ($_GET['id'] ?? 0);

        $bookingModel = new BookingModel();
        $paymentWalletModel = new PaymentWalletModel();

        $booking = $bookingModel->findBookingFull($bookingId);
        if (!$booking || (int) $booking['user_id'] !== (int) $user['id']) {
            $this->setFlash('danger', 'Không tìm thấy đơn đặt vé.');
            $this->redirect('?route=booking/my');
        }

        $paymentMethods = $paymentWalletModel->getActivePaymentMethods();
        $wallet = $paymentWalletModel->getOrCreateWallet((int) $user['id']);

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

        $bookingId = (int) ($_POST['booking_id'] ?? 0);
        $paymentMethodId = (int) ($_POST['payment_method_id'] ?? 1);

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
            $bookingModel->confirmBookingAndIssueTickets($bookingId, $paymentMethodId, $selectedCode);
            $this->setFlash('success', 'Thanh toán thành công! Vé điện tử và mã QR đã được phát hành.');
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
