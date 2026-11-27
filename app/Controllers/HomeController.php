<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\EngagementModel;
use App\Models\MovieModel;
use App\Models\RoomModel;
use App\Models\ShowModel;
use App\Models\UserModel;

class HomeController extends BaseController
{
    public function index(): void
    {
        $movieModel = new MovieModel();
        $userModel = new UserModel();
        $showModel = new ShowModel();
        $engagementModel = new EngagementModel();
        $roomModel = new RoomModel();

        // Mặc định hiển thị tab PHIM ĐANG CHIẾU
        $status = $_GET['status'] ?? 'now_showing';
        if ($status === 'all') {
            $status = null;
        }
        $keyword = trim($_GET['q'] ?? '');
        $genreId = (int) ($_GET['genre_id'] ?? 0);
        $genreId = $genreId > 0 ? $genreId : null;
        $genres = $movieModel->getAllGenres();

        // Danh sách phim theo bộ lọc (tab + search + thể loại)
        $movies = $movieModel->getMovies($status, $keyword ?: null, $genreId);

        // Dữ liệu bổ sung cho các section trang chủ
        $nowShowingMovies = $movieModel->getMovies('now_showing');
        $comingSoonMovies = $movieModel->getMovies('coming_soon');
        $cinemaInfo = $userModel->getCinemaInfo();
        $showsByMovie = $showModel->getSellingShowsGroupedByMovie();

        // Lịch chiếu nhanh hôm nay
        $today = date('Y-m-d');
        $todayShowsByMovie = $showModel->getSellingShowsGroupedByMovie($today);

        // Ưu đãi: coupon còn hiệu lực + combo bắp nước đang bán
        $allCoupons = $engagementModel->getAllCoupons();
        $activeCoupons = array_values(array_filter($allCoupons, static function (array $cp): bool {
            if (($cp['status'] ?? '') !== 'active') {
                return false;
            }
            if (!empty($cp['expires_at']) && strtotime($cp['expires_at']) < time()) {
                return false;
            }
            if (!empty($cp['starts_at']) && strtotime($cp['starts_at']) > time()) {
                return false;
            }
            return true;
        }));
        $concessions = $engagementModel->getActiveConcessions();
        $rooms = $roomModel->all('id ASC');

        // Hero slider: lấy tối đa 3 phim đang chiếu có backdrop hoặc poster
        $heroMovies = array_slice($nowShowingMovies, 0, 3);

        $this->view('home/index', [
            'pageTitle' => $cinemaInfo['name'] . ' - Hệ thống rạp chiếu phim',
            'movies' => $movies,
            'nowShowingMovies' => $nowShowingMovies,
            'comingSoonMovies' => $comingSoonMovies,
            'heroMovies' => $heroMovies,
            'showsByMovie' => $showsByMovie,
            'todayShowsByMovie' => $todayShowsByMovie,
            'activeCoupons' => $activeCoupons,
            'concessions' => $concessions,
            'rooms' => $rooms,
            'cinemaInfo' => $cinemaInfo,
            'currentStatus' => $status ?? 'all',
            'keyword' => $keyword,
            'genres' => $genres,
            'selectedGenreId' => $genreId,
        ]);
    }

    /**
     * Trang LỊCH CHIẾU THEO NGÀY (Beta Cinemas style - cho 1 rạp duy nhất)
     * Hiển thị hôm nay + 7 ngày; hỗ trợ suất chiếu sớm.
     */
    public function schedule(): void
    {
        $movieModel = new MovieModel();
        $showModel = new ShowModel();
        $userModel = new UserModel();

        $selectedDate = $_GET['date'] ?? '';
        $showsByMovie = $showModel->getSellingShowsGroupedByMovie($selectedDate ?: null);
        $movies = method_exists($movieModel, 'getMoviesForSchedule')
            ? $movieModel->getMoviesForSchedule(array_keys($showsByMovie))
            : $movieModel->getMovies('now_showing');
        $cinemaInfo = $userModel->getCinemaInfo();

        $this->view('home/schedule', [
            'pageTitle' => 'Lịch chiếu theo ngày - ' . $cinemaInfo['name'],
            'movies' => $movies,
            'showsByMovie' => $showsByMovie,
            'selectedDate' => $selectedDate,
            'cinemaInfo' => $cinemaInfo,
        ]);
    }

    /**
     * Trang GIÁ VÉ của rạp
     */
    public function prices(): void
    {
        $roomModel = new RoomModel();
        $userModel = new UserModel();

        $cinemaInfo = $userModel->getCinemaInfo();
        $seatTypes = $roomModel->getAllSeatTypes();
        $rooms = $roomModel->all('id ASC');

        $this->view('home/prices', [
            'pageTitle' => 'Bảng giá vé - ' . $cinemaInfo['name'],
            'cinemaInfo' => $cinemaInfo,
            'seatTypes' => $seatTypes,
            'rooms' => $rooms,
        ]);
    }

    /**
     * Trang TIN MỚI VÀ ƯU ĐÃI (Hiển thị Coupons & Combo Bắp Nước của rạp)
     */
    public function promotions(): void
    {
        $engagementModel = new EngagementModel();
        $userModel = new UserModel();

        $cinemaInfo = $userModel->getCinemaInfo();
        $coupons = $engagementModel->getAllCoupons();
        $concessions = $engagementModel->getActiveConcessions();

        $this->view('home/promotions', [
            'pageTitle' => 'Tin mới và Ưu đãi - ' . $cinemaInfo['name'],
            'cinemaInfo' => $cinemaInfo,
            'coupons' => $coupons,
            'concessions' => $concessions,
        ]);
    }

    /**
     * Trang THÔNG TIN RẠP (Mô hình 1 rạp chiếu lẻ duy nhất với nhiều phòng chiếu)
     */
    public function about(): void
    {
        $userModel = new UserModel();
        $roomModel = new RoomModel();

        $cinemaInfo = $userModel->getCinemaInfo();
        $rooms = $roomModel->all('id ASC');

        $this->view('home/about', [
            'pageTitle' => 'Thông tin rạp - ' . $cinemaInfo['name'],
            'cinemaInfo' => $cinemaInfo,
            'rooms' => $rooms,
        ]);
    }

    public function contact(): void
    {
        $userModel = new UserModel();
        $this->view('home/contact', [
            'pageTitle' => 'Liên hệ và góp ý - ' . $userModel->getCinemaInfo()['name'],
            'cinemaInfo' => $userModel->getCinemaInfo(),
        ]);
    }

    public function submitContact(): void
    {
        $name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $subject === '' || mb_strlen($message) < 10) {
            $this->setFlash('danger', 'Vui lòng nhập đầy đủ thông tin hợp lệ. Nội dung góp ý tối thiểu 10 ký tự.');
            $this->redirect('?route=contact');
        }
        $user = $_SESSION['user'] ?? null;
        (new EngagementModel())->createContactMessage([
            'user_id' => $user['id'] ?? null,
            'full_name' => $name,
            'email' => $email,
            'phone' => $phone,
            'subject' => $subject,
            'message' => $message,
        ]);
        $this->setFlash('success', 'Cảm ơn bạn! Góp ý đã được gửi đến bộ phận chăm sóc khách hàng.');
        $this->redirect('?route=contact');
    }

    public function movieDetail(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $movieModel = new MovieModel();
        $showModel = new ShowModel();
        $engagementModel = new EngagementModel();
        $userModel = new UserModel();

        $movie = $movieModel->findWithDetails($id);
        if (!$movie) {
            $this->setFlash('danger', 'Không tìm thấy bộ phim.');
            $this->redirect('?route=home');
        }

        $reviewPage = max(1, (int) ($_GET['review_page'] ?? 1));
        $shows = $showModel->getShowsByMovie($id);

        // Hỗ trợ cả getMovieReviews cũ (trả về array) và mới (phân trang)
        $reviewResult = $engagementModel->getMovieReviews($id, $reviewPage, 5);
        if (isset($reviewResult['items'])) {
            $reviews = $reviewResult['items'];
            $reviewPagination = $reviewResult;
        } else {
            $reviews = $reviewResult;
            $reviewPagination = [
                'items' => $reviews,
                'total' => count($reviews),
                'page' => 1,
                'per_page' => count($reviews),
                'total_pages' => 1,
            ];
        }

        $userReview = null;
        if (!empty($_SESSION['user']['id']) && method_exists($engagementModel, 'getUserReviewForMovie')) {
            $userReview = $engagementModel->getUserReviewForMovie((int) $_SESSION['user']['id'], $id);
        }

        $cinemaInfo = $userModel->getCinemaInfo();

        $this->view('home/movie_detail', [
            'pageTitle' => $movie['title'] . ' - ' . $cinemaInfo['name'],
            'movie' => $movie,
            'shows' => $shows,
            'reviews' => $reviews,
            'reviewPagination' => $reviewPagination,
            'userReview' => $userReview,
            'cinemaInfo' => $cinemaInfo,
        ]);
    }

    public function submitReview(): void
    {
        $user = $this->requireAuth();
        $movieId = (int) ($_POST['movie_id'] ?? 0);
        $rating = (int) ($_POST['rating'] ?? 10);
        $comment = trim($_POST['comment'] ?? '');

        if ($movieId <= 0 || $comment === '') {
            $this->setFlash('danger', 'Vui lòng nhập nội dung đánh giá.');
            $this->redirect('?route=movie/detail&id=' . $movieId);
        }

        $engagementModel = new EngagementModel();
        $result = $engagementModel->saveReview((int) $user['id'], $movieId, $rating, $comment);

        if ($result === 'updated') {
            $this->setFlash('success', 'Đã cập nhật đánh giá của bạn (mỗi tài khoản chỉ 1 đánh giá / phim).');
        } else {
            $this->setFlash('success', 'Cảm ơn bạn đã gửi đánh giá bộ phim!');
        }
        $this->redirect('?route=movie/detail&id=' . $movieId);
    }

    public function deleteReview(): void
    {
        $user = $this->requireAuth();
        $reviewId = (int) ($_POST['review_id'] ?? 0);
        $movieId = (int) ($_POST['movie_id'] ?? 0);

        $engagementModel = new EngagementModel();
        $ok = method_exists($engagementModel, 'deleteReview')
            ? $engagementModel->deleteReview((int) $user['id'], $reviewId)
            : false;

        if ($ok) {
            $this->setFlash('success', 'Đã xóa đánh giá của bạn.');
        } else {
            $this->setFlash('danger', 'Không thể xóa đánh giá (chỉ được xóa đánh giá của chính bạn).');
        }
        $this->redirect('?route=movie/detail&id=' . $movieId);
    }

    public function toggleWishlist(): void
    {
        $user = $this->requireAuth();
        $movieId = (int) ($_POST['movie_id'] ?? $_GET['movie_id'] ?? 0);

        $engagementModel = new EngagementModel();
        $added = $engagementModel->toggleWishlist((int) $user['id'], $movieId);

        $this->setFlash('info', $added ? 'Đã thêm phim vào danh sách yêu thích!' : 'Đã xóa phim khỏi danh sách yêu thích.');
        $this->redirect('?route=movie/detail&id=' . $movieId);
    }

    public function wishlist(): void
    {
        $user = $this->requireAuth();
        $engagementModel = new EngagementModel();
        $movies = $engagementModel->getUserWishlist((int) $user['id']);

        $this->view('home/wishlist', [
            'pageTitle' => 'Phim yêu thích của tôi',
            'movies' => $movies,
        ]);
    }

    /**
     * Thông báo khách hàng: phân trang, đánh dấu 1 / tất cả đã đọc
     */
    public function notifications(): void
    {
        $user = $this->requireAuth();
        $engagementModel = new EngagementModel();
        $userId = (int) $user['id'];

        if (isset($_GET['mark_read'])) {
            $engagementModel->markAllNotificationsRead($userId);
            $this->setFlash('success', 'Đã đánh dấu đọc tất cả thông báo.');
            $this->redirect('?route=notifications');
        }

        if (isset($_GET['read_id']) && method_exists($engagementModel, 'markNotificationRead')) {
            $engagementModel->markNotificationRead($userId, (int) $_GET['read_id']);
            $this->redirect('?route=notifications&page=' . max(1, (int) ($_GET['page'] ?? 1)));
        }

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $result = $engagementModel->getUserNotifications($userId, $page, 10);

        // Hỗ trợ cả method cũ (trả về list) và mới (có phân trang)
        if (isset($result['items'])) {
            $notifications = $result['items'];
            $pagination = $result;
        } else {
            $notifications = $result;
            $pagination = [
                'items' => $notifications,
                'total' => count($notifications),
                'page' => 1,
                'per_page' => count($notifications),
                'total_pages' => 1,
            ];
        }

        $unreadCount = method_exists($engagementModel, 'countUnreadNotifications')
            ? $engagementModel->countUnreadNotifications($userId)
            : 0;

        $this->view('home/notifications', [
            'pageTitle' => 'Thông báo của tôi',
            'notifications' => $notifications,
            'pagination' => $pagination,
            'unreadCount' => $unreadCount,
        ]);
    }
}