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
     */
    public function schedule(): void
    {
        $movieModel = new MovieModel();
        $showModel = new ShowModel();
        $userModel = new UserModel();

        $selectedDate = $_GET['date'] ?? '';
        $movies = $movieModel->getMovies('now_showing');
        $showsByMovie = $showModel->getSellingShowsGroupedByMovie($selectedDate ?: null);
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
            'user_id' => $user['id'] ?? null, 'full_name' => $name, 'email' => $email,
            'phone' => $phone, 'subject' => $subject, 'message' => $message,
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

        $shows = $showModel->getShowsByMovie($id);
        $reviews = $engagementModel->getMovieReviews($id);
        $cinemaInfo = $userModel->getCinemaInfo();

        $this->view('home/movie_detail', [
            'pageTitle' => $movie['title'] . ' - ' . $cinemaInfo['name'],
            'movie' => $movie,
            'shows' => $shows,
            'reviews' => $reviews,
            'cinemaInfo' => $cinemaInfo,
        ]);
    }

    public function submitReview(): void
    {
        $user = $this->requireAuth();
        $movieId = (int) ($_POST['movie_id'] ?? 0);
        $rating = (int) ($_POST['rating'] ?? 10);
        $comment = trim($_POST['comment'] ?? '');

        $engagementModel = new EngagementModel();
        $engagementModel->saveReview((int) $user['id'], $movieId, $rating, $comment);

        $this->setFlash('success', 'Cảm ơn bạn đã gửi đánh giá bộ phim!');
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

    public function notifications(): void
    {
        $user = $this->requireAuth();
        $engagementModel = new EngagementModel();

        if (isset($_GET['mark_read'])) {
            $engagementModel->markAllNotificationsRead((int) $user['id']);
            $this->setFlash('success', 'Đã đánh dấu đọc tất cả thông báo.');
            $this->redirect('?route=notifications');
        }

        $notifications = $engagementModel->getUserNotifications((int) $user['id']);
        $this->view('home/notifications', [
            'pageTitle' => 'Thông báo của tôi',
            'notifications' => $notifications,
        ]);
    }
}
