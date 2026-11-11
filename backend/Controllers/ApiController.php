<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\BookingModel;
use App\Models\EngagementModel;
use App\Models\MovieModel;
use App\Models\ShowModel;
use App\Models\UserModel;

/**
 * REST API Controller kết nối Backend PHP với Frontend React
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
     * Kiểm tra mã giảm giá realtime trên giao diện chọn ghế React
     */
    public function checkCoupon(): void
    {
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $code = strtoupper(trim($input['code'] ?? ''));
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
     * Trả về dữ liệu thống kê chi tiết (khách xem, doanh thu, phim, phòng, thanh toán...) cho React Admin Dashboard
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
     * Soát vé QR realtime bằng React trong trang Admin
     */
    public function adminCheckIn(): void
    {
        if (empty($_SESSION['user']['roles']) || empty(array_intersect(['admin', 'staff'], $_SESSION['user']['roles']))) {
            $this->json(['success' => false, 'message' => 'Không có quyền thực hiện.'], 403);
        }

        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $ticketCode = trim($input['ticket_code'] ?? '');

        if ($ticketCode === '') {
            $this->json(['success' => false, 'message' => 'Vui lòng nhập mã vé QR.'], 400);
        }

        try {
            $bookingModel = new BookingModel();
            $ticket = $bookingModel->checkInTicketByCode($ticketCode);
            $this->json([
                'success' => true,
                'ticket' => $ticket,
                'message' => "Soát vé thành công! Mã vé: {$ticket['ticket_code']} | Phim: {$ticket['movie_title']} | Phòng: {$ticket['room_name']} | Ghế: {$ticket['seat_code']}",
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
     * Đảm bảo không bao giờ lỗi đường dẫn 404 dù chạy từ thư mục gốc hay /public
     */
    public function serveFrontendJs(): void
    {
        $allowedFiles = [
            'AdminAnalyticsApp.js',
            'CustomerMovieApp.js',
            'SeatCouponApp.js',
        ];
        $file = basename($_GET['file'] ?? '');
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

