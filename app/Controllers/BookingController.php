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

        $booking = $bookingModel->findBookingFull($bookingId);
        if (!$booking || (int) $booking['user_id'] !== (int) $user['id']) {
            $this->setFlash('danger', 'Đơn hàng không hợp lệ.');
            $this->redirect('?route=booking/my');
        }

        try {
            $bookingModel->confirmBookingAndIssueTickets($bookingId, $paymentMethodId);
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

