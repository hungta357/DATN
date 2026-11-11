<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\EngagementModel;
use App\Models\MovieModel;
use App\Models\ShowModel;
use App\Models\UserModel;

class HomeController extends BaseController
{
    public function index(): void
    {
        $movieModel = new MovieModel();
        $userModel = new UserModel();

        $status = $_GET['status'] ?? null;
        $keyword = trim($_GET['q'] ?? '');

        $movies = $movieModel->getMovies($status ?: null, $keyword ?: null);
        $cinemaInfo = $userModel->getCinemaInfo();

        $this->view('home/index', [
            'pageTitle' => $cinemaInfo['name'] . ' - Đặt vé xem phim trực tuyến',
            'movies' => $movies,
            'cinemaInfo' => $cinemaInfo,
            'currentStatus' => $status,
            'keyword' => $keyword,
        ]);
    }

    public function movieDetail(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $movieModel = new MovieModel();
        $showModel = new ShowModel();
        $engagementModel = new EngagementModel();

        $movie = $movieModel->findWithDetails($id);
        if (!$movie) {
            $this->setFlash('danger', 'Không tìm thấy bộ phim.');
            $this->redirect('?route=home');
        }

        $shows = $showModel->getShowsByMovie($id);
        $reviews = $engagementModel->getMovieReviews($id);

        $this->view('home/movie_detail', [
            'pageTitle' => $movie['title'],
            'movie' => $movie,
            'shows' => $shows,
            'reviews' => $reviews,
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

