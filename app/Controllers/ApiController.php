<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\BookingModel;
use App\Models\EngagementModel;
use App\Models\MovieModel;
use App\Models\ShowModel;
use App\Models\TicketSecurityModel;
use App\Models\UserModel;

/**
 * REST API Controller kết nối Backend PHP với Frontend React & JS
 */
class ApiController extends BaseController
{
    /**
     * GET ?route=api/movies
     * Trả về danh sách phim, thể loại và lịch chiếu mở bán cho React Movie Explorer
     */
    public function movies(): void
    {
        $movieModel = new MovieModel();
        $showModel = new ShowModel();
        $userModel = new UserModel();

        $movies = $movieModel->getMovies(null, null);
        $genres = $movieModel->getAllGenres();
        $showsByMovie = $showModel->getSellingShowsGroupedByMovie();
        $cinemaInfo = $userModel->getCinemaInfo();

        $this->json([
            'success' => true,
            'cinema' => $cinemaInfo,
            'genres' => $genres,
            'movies' => $movies,
            'showsByMovie' => $showsByMovie,
        ]);
    }

    /**
     * POST ?route=api/coupon/check
     * Kiểm tra mã giảm giá realtime trên giao diện chọn ghế
     */
    public function checkCoupon(): void
    {
        $input = json_decode(file_get_contents('php://input') ?: '', true);
        if (!is_array($input)) {
            $input = $_POST;
        }
        $code = strtoupper(trim((string) ($input['code'] ?? '')));
        $orderAmount = (float) ($input['order_amount'] ?? 0);

        if ($code === '') {
            $this->json(['success' => false, 'message' => 'Vui lòng nhập mã giảm giá.'], 400);
        }

        try {
            $engagementModel = new EngagementModel();
            $result = $engagementModel->validateCoupon($code, $orderAmount);
            $this->json([
                'success' => true,
                'code' => $result['coupon']['code'],
                'name' => $result['coupon']['name'],
                'discount_amount' => (float) $result['discount_amount'],
                'message' => 'Áp dụng mã ' . $result['coupon']['code'] . ' thành công! Giảm ' . number_format((float) $result['discount_amount']) . 'đ',
            ]);
        } catch (\Throwable $e) {
            $this->json([
                'success' => false,
                'discount_amount' => 0,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * GET ?route=api/admin/stats
     * Trả về dữ liệu thống kê chi tiết (khách xem, doanh thu, phim, phòng, thanh toán...) cho Admin Dashboard
     */
    public function adminStats(): void
    {
        if (empty($_SESSION['user']['roles']) || empty(array_intersect(['admin', 'staff'], $_SESSION['user']['roles']))) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $bookingModel = new BookingModel();
        $analytics = $bookingModel->getDetailedAnalytics();

        $this->json([
            'success' => true,
            'data' => $analytics,
        ]);
    }

    /**
     * POST ?route=api/admin/checkin
     * Soát vé QR realtime trong trang Admin
     */
    public function adminCheckIn(): void
    {
        if (empty($_SESSION['user']['roles']) || empty(array_intersect(['admin', 'staff'], $_SESSION['user']['roles']))) {
            $this->json(['success' => false, 'message' => 'Không có quyền thực hiện.'], 403);
        }

        $input = json_decode(file_get_contents('php://input') ?: '', true);
        if (!is_array($input)) {
            $input = $_POST;
        }
        $ticketCode = trim((string) ($input['ticket_code'] ?? ''));

        if ($ticketCode === '') {
            $this->json(['success' => false, 'message' => 'Vui lòng nhập mã vé QR.'], 400);
        }

        try {
            $staffId = (int) ($_SESSION['user']['id'] ?? 0);
            $ticket = (new TicketSecurityModel())->checkInTicketByCode($ticketCode, $staffId);
            $message = "Soát vé thành công! Mã vé: {$ticket['ticket_code']} | Phim: {$ticket['movie_title']} | Phòng: {$ticket['room_name']} | Ghế: {$ticket['seat_code']}";
            if (!empty($ticket['scan_anomaly'])) {
                $message .= ' Cảnh báo: ' . $ticket['scan_anomaly'] . '. Vui lòng kiểm tra thông tin suất chiếu.';
            }
            $this->json([
                'success' => true,
                'ticket' => $ticket,
                'message' => $message,
            ]);
        } catch (\Throwable $e) {
            $this->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * GET ?route=frontend/js&file=...
     * Phục vụ các file React Component tách riêng trong thư mục frontend/react/
     */
    public function serveFrontendJs(): void
    {
        $allowedFiles = [
            'AdminAnalyticsApp.js',
            'CustomerMovieApp.js',
            'SeatCouponApp.js',
        ];
        $file = basename((string) ($_GET['file'] ?? ''));
        if (!in_array($file, $allowedFiles, true)) {
            http_response_code(404);
            header('Content-Type: application/javascript; charset=utf-8');
            echo '// File not found';
            exit;
        }

        $filePath = __DIR__ . '/../../frontend/react/' . $file;
        if (!file_exists($filePath)) {
            http_response_code(404);
            header('Content-Type: application/javascript; charset=utf-8');
            echo '// File missing';
            exit;
        }

        header('Content-Type: application/javascript; charset=utf-8');
        header('Cache-Control: no-cache');
        readfile($filePath);
        exit;
    }
}
