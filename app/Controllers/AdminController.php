<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\BookingModel;
use App\Models\EngagementModel;
use App\Models\MovieModel;
use App\Models\RoomModel;
use App\Models\ShowModel;
use App\Models\UserModel;

/**
 * QUẢN TRỊ HỆ THỐNG TỔNG HỢP TẤT CẢ 7 NHÁNH TRONG DATABASE
 */
class AdminController extends BaseController
{
    public function dashboard(): void
    {
        $currentUser = $this->requireRole(['admin', 'staff']);
        $isAdmin = in_array('admin', $currentUser['roles'] ?? [], true);
        $isStaff = !$isAdmin && in_array('staff', $currentUser['roles'] ?? [], true);

        $userModel = new UserModel();
        $movieModel = new MovieModel();
        $roomModel = new RoomModel();
        $showModel = new ShowModel();
        $bookingModel = new BookingModel();
        $engagementModel = new EngagementModel();

        $tab = $_GET['tab'] ?? ($isStaff ? 'bookings' : 'overview');

        // Staff chỉ được vào quầy soát vé trong trang quản trị.
        // Lịch chiếu được xem tại trang công khai ?route=schedule.
        if ($isStaff && $tab !== 'bookings') {
            $this->redirect('?route=admin&tab=bookings');
        }

        $this->view('admin/dashboard', [
            'pageTitle' => 'Hệ Thống Quản Trị Rạp Chiếu Phim - CineNow Admin',
            'tab' => $tab,
            'isAdmin' => $isAdmin,
            'isStaff' => $isStaff,
            'cinemaInfo' => $userModel->getCinemaInfo(),
            // Không tải dữ liệu quản trị nhạy cảm khi tài khoản là Staff.
            'users' => $isAdmin ? $userModel->getAllUsersWithRoles() : [],
            'movies' => $isAdmin ? $movieModel->all('id DESC') : [],
            'genres' => $isAdmin ? $movieModel->getAllGenres() : [],
            'rooms' => $isAdmin ? $roomModel->all('id ASC') : [],
            'seatTypes' => $isAdmin ? $roomModel->getAllSeatTypes() : [],
            'shows' => $isAdmin ? $showModel->getAllShowsWithDetails() : [],
            'bookings' => $bookingModel->getAllBookings(),
            'concessions' => $isAdmin ? $engagementModel->getAllConcessions() : [],
            'coupons' => $isAdmin ? $engagementModel->getAllCoupons() : [],
        ], 'layouts/admin');
    }

    public function changeAdminPassword(): void
    {
        $admin = $this->requireRole(['admin']);
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (strlen($newPassword) < 6) {
            $this->setFlash('danger', 'Mật khẩu mới của Admin phải có ít nhất 6 ký tự.');
            $this->redirect('?route=admin&tab=users');
        }

        if ($newPassword !== $confirmPassword) {
            $this->setFlash('danger', 'Xác nhận mật khẩu mới không khớp.');
            $this->redirect('?route=admin&tab=users');
        }

        $userModel = new UserModel();
        $dbAdmin = $userModel->find((int) $admin['id']);
        if (!$dbAdmin || !password_verify($currentPassword, $dbAdmin['password'])) {
            $this->setFlash('danger', 'Mật khẩu hiện tại không chính xác.');
            $this->redirect('?route=admin&tab=users');
        }

        $userModel->updateAdminPassword((int) $admin['id'], $newPassword);
        $this->setFlash('success', 'Đã cập nhật mật khẩu Quản trị viên thành công!');
        $this->redirect('?route=admin&tab=users');
    }

    // --- NHÁNH 1: CẬP NHẬT CINEMA INFO & USER STATUS ---
    public function saveCinemaInfo(): void
    {
        $this->requireRole(['admin']);
        $userModel = new UserModel();
        $userModel->updateCinemaInfo($_POST);
        $this->setFlash('success', 'Đã cập nhật thông tin rạp chiếu phim.');
        $this->redirect('?route=admin&tab=users');
    }

    public function updateUserStatus(): void
    {
        $this->requireRole(['admin']);
        $userId = (int) ($_POST['user_id'] ?? 0);
        $status = $_POST['status'] ?? 'active';
        $userModel = new UserModel();
        $userModel->update($userId, ['status' => $status]);
        $this->setFlash('success', 'Đã cập nhật trạng thái tài khoản #' . $userId);
        $this->redirect('?route=admin&tab=users');
    }

    // --- NHÁNH 2: THÊM / CẬP NHẬT PHIM ---
    public function saveMovie(): void
    {
        $this->requireRole(['admin']);
        $movieModel = new MovieModel();

        $title = trim($_POST['title'] ?? '');
        $slug = trim($_POST['slug'] ?? '') ?: strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $title)) . '-' . rand(10, 99);

        $movieId = $movieModel->create([
            'title' => $title,
            'title_vi' => trim($_POST['title_vi'] ?? '') ?: null,
            'slug' => $slug,
            'overview' => trim($_POST['overview'] ?? '') ?: null,
            'poster_path' => trim($_POST['poster_path'] ?? '') ?: null,
            'release_date' => $_POST['release_date'] ?: date('Y-m-d'),
            'runtime' => (int) ($_POST['runtime'] ?? 120),
            'director' => trim($_POST['director'] ?? '') ?: null,
            'age_rating' => trim($_POST['age_rating'] ?? 'P'),
            'imdb_rating' => (float) ($_POST['imdb_rating'] ?? 8.0),
            'status' => $_POST['status'] ?? 'now_showing',
        ]);

        if (!empty($_POST['genre_ids']) && is_array($_POST['genre_ids'])) {
            $movieModel->syncGenres($movieId, $_POST['genre_ids']);
        }

        $this->setFlash('success', 'Đã thêm bộ phim mới thành công!');
        $this->redirect('?route=admin&tab=movies');
    }

    public function updateMovieStatus(): void
    {
        $this->requireRole(['admin']);
        $movieId = (int) ($_POST['movie_id'] ?? 0);
        $status = $_POST['status'] ?? 'now_showing';

        $movieModel = new MovieModel();
        $movieModel->update($movieId, ['status' => $status]);
        $this->setFlash('success', 'Đã cập nhật trạng thái phim #' . $movieId . ' (Không xóa cứng theo đúng quy tắc DB).');
        $this->redirect('?route=admin&tab=movies');
    }

    // --- NHÁNH 3: THÊM PHÒNG CHIẾU & KHỞI TẠO SƠ ĐỒ GHẾ ---
    public function saveRoom(): void
    {
        $this->requireRole(['admin']);
        $roomModel = new RoomModel();

        $roomId = $roomModel->create([
            'name' => trim($_POST['name'] ?? 'Phòng mới'),
            'room_type' => $_POST['room_type'] ?? 'standard',
            'capacity' => 0,
            'status' => 'active',
            'description' => trim($_POST['description'] ?? ''),
        ]);

        $createdSeats = $roomModel->generateDefaultSeatsForRoom($roomId);
        $this->setFlash('success', "Đã tạo phòng chiếu và sinh tự động {$createdSeats} ghế!");
        $this->redirect('?route=admin&tab=rooms');
    }

    public function generateSeats(): void
    {
        $this->requireRole(['admin']);
        $roomId = (int) ($_POST['room_id'] ?? 0);
        $roomModel = new RoomModel();
        $created = $roomModel->generateDefaultSeatsForRoom($roomId);
        $this->setFlash('success', "Đã khởi tạo bổ sung {$created} ghế cho phòng #{$roomId}.");
        $this->redirect('?route=admin&tab=rooms');
    }

    // --- NHÁNH 4: TẠO SUẤT CHIẾU (SHOW) & TỰ ĐỘNG SINH SHOW_SEATS ---
    public function saveShow(): void
    {
        $this->requireRole(['admin']);
        $showModel = new ShowModel();

        try {
            $startTime = date('Y-m-d H:i:s', strtotime($_POST['start_time']));
            $endTime = date('Y-m-d H:i:s', strtotime($_POST['start_time'] . ' +2 hours'));

            $showId = $showModel->createShowWithSeats([
                'movie_id' => (int) $_POST['movie_id'],
                'room_id' => (int) $_POST['room_id'],
                'start_time' => $startTime,
                'end_time' => $endTime,
                'base_price' => (float) ($_POST['base_price'] ?? 80000),
                'status' => 'selling',
            ]);
            $this->setFlash('success', "Đã tạo suất chiếu #{$showId} và tự động khởi tạo danh sách ghế (show_seats)!");
        } catch (\Throwable $e) {
            $this->setFlash('danger', 'Lỗi tạo suất chiếu: ' . $e->getMessage());
        }

        $this->redirect('?route=admin&tab=shows');
    }

    // --- NHÁNH 5: SOÁT VÉ (CHECK-IN TICKET QR CODE) ---
    public function checkInTicket(): void
    {
        $this->requireRole(['admin', 'staff']);
        $ticketCode = trim($_POST['ticket_code'] ?? '');

        try {
            $bookingModel = new BookingModel();
            $ticket = $bookingModel->checkInTicketByCode($ticketCode);
            $this->setFlash('success', "Soát vé thành công! Mã vé: {$ticket['ticket_code']} | Phim: {$ticket['movie_title']} | Phòng: {$ticket['room_name']} | Ghế: {$ticket['seat_code']}");
        } catch (\Throwable $e) {
            $this->setFlash('danger', $e->getMessage());
        }

        $this->redirect('?route=admin&tab=bookings');
    }

    // --- NHÁNH 7: BẮP NƯỚC (CONCESSIONS) & MÃ GIẢM GIÁ (COUPONS) ---
    public function saveConcession(): void
    {
        $this->requireRole(['admin']);
        $engagementModel = new EngagementModel();
        $engagementModel->create([
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'price' => (float) ($_POST['price'] ?? 50000),
            'category' => trim($_POST['category'] ?? 'Combo'),
            'stock' => (int) ($_POST['stock'] ?? 100),
            'status' => 'active',
            'sort_order' => 1,
        ]);
        $this->setFlash('success', 'Đã thêm sản phẩm bắp nước mới!');
        $this->redirect('?route=admin&tab=concessions');
    }

    public function saveCoupon(): void
    {
        $this->requireRole(['admin']);
        $engagementModel = new EngagementModel();
        $db = $engagementModel->getDb();
        $stmt = $db->prepare("
            INSERT INTO coupons (code, name, discount_type, discount_value, min_order_amount, usage_limit, status, created_at, updated_at)
            VALUES (:code, :name, :type, :val, :min_amt, :limit_cnt, 'active', NOW(), NOW())
        ");
        $stmt->execute([
            'code' => strtoupper(trim($_POST['code'] ?? '')),
            'name' => trim($_POST['name'] ?? ''),
            'type' => $_POST['discount_type'] ?? 'fixed',
            'val' => (float) ($_POST['discount_value'] ?? 20000),
            'min_amt' => (float) ($_POST['min_order_amount'] ?? 0),
            'limit_cnt' => (int) ($_POST['usage_limit'] ?? 100),
        ]);
        $this->setFlash('success', 'Đã tạo mã giảm giá mới!');
        $this->redirect('?route=admin&tab=concessions');
    }
}

