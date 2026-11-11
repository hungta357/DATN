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

        // Mặc định hiển thị tab PHIM ĐANG CHIẾU giống Beta Cinemas
        $status = $_GET['status'] ?? 'now_showing';
        if ($status === 'all') {
            $status = null;
        }
        $keyword = trim($_GET['q'] ?? '');

        $movies = $movieModel->getMovies($status, $keyword ?: null);
        $allMovies = $movieModel->getMovies(null, null);
        $genres = $movieModel->getAllGenres();
        $cinemaInfo = $userModel->getCinemaInfo();
        $showsByMovie = $showModel->getSellingShowsGroupedByMovie();

        $this->view('home/index', [
            'pageTitle' => $cinemaInfo['name'] . ' - Hệ thống rạp chiếu phim',
            'movies' => $movies,
            'allMovies' => $allMovies,
            'genres' => $genres,
            'showsByMovie' => $showsByMovie,
            'cinemaInfo' => $cinemaInfo,
            'currentStatus' => $status ?? 'all',
            'keyword' => $keyword,
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
