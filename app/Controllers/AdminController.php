<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\BookingModel;
use App\Models\EngagementModel;
use App\Models\GroupBookingModel;
use App\Models\IncidentModel;
use App\Models\MovieModel;
use App\Models\MaintenanceModel;
use App\Models\RoomModel;
use App\Models\ShowModel;
use App\Models\StaffShiftModel;
use App\Models\TicketSecurityModel;
use App\Models\UserModel;

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

        // Staff chỉ được vào các phân hệ nghiệp vụ được cấp quyền.
        if ($isStaff && !in_array($tab, ['bookings', 'maintenance', 'incidents', 'shifts'], true)) {
            $this->redirect('?route=admin&tab=bookings');
        }

        $maintenanceModel = new MaintenanceModel();
        $groupBookingModel = new GroupBookingModel();
        $isMaintenanceTab = $tab === 'maintenance';
        $isIncidentTab = $tab === 'incidents';
        $isShiftTab = $tab === 'shifts';
        $isBookingTab = $tab === 'bookings';
        $incidentModel = new IncidentModel();
        $shiftModel = new StaffShiftModel();
        $ticketSecurityModel = new TicketSecurityModel();

        // Đảm bảo các bộ phim đang chiếu đều có suất chiếu mở bán để khách có thể đặt vé ngay
        if ($isAdmin) {
            $showModel->getSellingShowsGroupedByMovie();
        }

        $editMovie = null;
        if ($isAdmin && $tab === 'movies' && !empty($_GET['edit_id'])) {
            $editMovie = $movieModel->findWithDetails((int) $_GET['edit_id']);
            if ($editMovie) {
                $stmtG = $movieModel->getDb()->prepare("SELECT genre_id FROM movie_genres WHERE movie_id = :mid");
                $stmtG->execute(['mid' => (int) $editMovie['id']]);
                $editMovie['genre_ids'] = array_map('intval', $stmtG->fetchAll(\PDO::FETCH_COLUMN));
            }
        }

        $editShow = null;
        if ($isAdmin && $tab === 'shows' && !empty($_GET['edit_show_id'])) {
            $editShow = $showModel->findShowWithDetails((int) $_GET['edit_show_id']);
        }

        $this->view('admin/dashboard', [
            'pageTitle' => 'Hệ Thống Quản Trị Rạp Chiếu Phim - CineNow Admin',
            'tab' => $tab,
            'isAdmin' => $isAdmin,
            'isStaff' => $isStaff,
            'cinemaInfo' => $userModel->getCinemaInfo(),
            'overviewStats' => $isAdmin ? $bookingModel->getOverviewStats() : [],
            'analytics' => $isAdmin ? $bookingModel->getDetailedAnalytics() : [],
            'users' => $isAdmin ? $userModel->getAllUsersWithRoles() : [],
            'movies' => $isAdmin ? $movieModel->getAllMoviesWithGenres() : [],
            'editMovie' => $editMovie,
            'genres' => $isAdmin ? $movieModel->getAllGenres() : [],
            'rooms' => $isAdmin ? $roomModel->all('id ASC') : [],
            'seatTypes' => $isAdmin ? $roomModel->getAllSeatTypes() : [],
            'shows' => $isAdmin ? $showModel->getAllShowsWithDetails() : [],
            'editShow' => $editShow,
            'bookings' => $bookingModel->getAllBookings(),
            'concessions' => $isAdmin ? $engagementModel->getAllConcessions() : [],
            'coupons' => $isAdmin ? $engagementModel->getAllCoupons() : [],
            'maintenanceReports' => $isMaintenanceTab ? $maintenanceModel->getReports(
                (int) $currentUser['id'],
                $isAdmin,
                [
                    'room_id' => isset($_GET['maintenance_room']) && is_scalar($_GET['maintenance_room']) ? (string) $_GET['maintenance_room'] : '',
                    'severity' => isset($_GET['maintenance_severity']) && is_string($_GET['maintenance_severity']) ? $_GET['maintenance_severity'] : '',
                    'status' => isset($_GET['maintenance_status']) && is_string($_GET['maintenance_status']) ? $_GET['maintenance_status'] : '',
                ]
            ) : [],
            'maintenanceCategories' => $isMaintenanceTab ? $maintenanceModel->getCategories() : [],
            'maintenanceRooms' => $isMaintenanceTab ? $maintenanceModel->getRooms() : [],
            'maintenanceSeats' => $isMaintenanceTab ? $maintenanceModel->getSeats() : [],
            'maintenanceStaff' => $isMaintenanceTab && $isAdmin ? $maintenanceModel->getAssignableStaff() : [],
            'maintenanceStats' => $isMaintenanceTab && $isAdmin ? $maintenanceModel->getMonthlyStats() : [],
            'groupBookings' => $tab === 'groups' && $isAdmin ? $groupBookingModel->getAllGroupsForAdmin() : [],
            // Nhân viên chỉ nhận các khiếu nại được phân công; Admin xem toàn bộ.
            'incidentReports' => $isIncidentTab ? $incidentModel->getReports(
                (int) $currentUser['id'],
                $isAdmin,
                $isStaff
            ) : [],
            'incidentStaff' => $isIncidentTab && $isAdmin ? $incidentModel->getAssignableStaff() : [],
            'shifts' => $isShiftTab ? $shiftModel->getShifts((int) $currentUser['id'], $isAdmin) : [],
            'shiftStaff' => $isShiftTab && $isAdmin ? $shiftModel->getAssignableStaff() : [],
            'shiftRooms' => $isShiftTab && $isAdmin ? $shiftModel->getActiveRooms() : [],
            'scanAlerts' => $isBookingTab ? $ticketSecurityModel->getAnomalyLogs() : [],
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
        $currentAdmin = $this->requireRole(['admin']);
        $userId = (int) ($_POST['user_id'] ?? 0);
        $status = in_array($_POST['status'] ?? '', ['active', 'locked'], true) ? $_POST['status'] : 'active';
        $role = trim($_POST['role'] ?? '');

        if ($userId <= 0) {
            $this->setFlash('danger', 'Tài khoản không hợp lệ.');
            $this->redirect('?route=admin&tab=users');
        }

        if ($userId === (int) ($currentAdmin['id'] ?? 0) && ($status === 'locked' || ($role !== '' && $role !== 'admin'))) {
            $this->setFlash('warning', 'Không thể tự khóa hoặc hạ quyền tài khoản Quản trị viên đang đăng nhập.');
            $this->redirect('?route=admin&tab=users');
        }

        $userModel = new UserModel();
        $userModel->update($userId, ['status' => $status]);
        if ($role !== '') {
            $userModel->updateUserRole($userId, $role);
        }

        $this->setFlash('success', 'Đã cập nhật tài khoản #' . $userId . ' thành công.');
        $this->redirect('?route=admin&tab=users');
    }

    public function saveMovie(): void
    {
        $this->requireRole(['admin']);
        $movieModel = new MovieModel();
        $showModel = new ShowModel();

        $movieId = (int) ($_POST['movie_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        if ($title === '') {
            $this->setFlash('danger', 'Vui lòng nhập tên bộ phim.');
            $this->redirect('?route=admin&tab=movies');
        }

        $baseSlug = strtolower(trim((string) preg_replace('/[^a-zA-Z0-9]+/', '-', $title), '-'));
        if ($baseSlug === '') {
            $baseSlug = 'movie';
        }
        $slug = trim($_POST['slug'] ?? '') ?: ($baseSlug . '-' . rand(100, 999));

        $posterPath = trim($_POST['poster_path'] ?? '');
        if ($posterPath !== '' && !str_starts_with($posterPath, 'http://') && !str_starts_with($posterPath, 'https://') && !str_starts_with($posterPath, 'public/') && !is_file(__DIR__ . '/../../' . ltrim($posterPath, '/'))) {
            $posterPath = 'public/images/posters/dune-part-two.svg';
        }

        $releaseDate = trim($_POST['release_date'] ?? '');
        if ($releaseDate === '' || strtotime($releaseDate) === false) {
            $releaseDate = date('Y-m-d');
        }

        $status = in_array($_POST['status'] ?? '', ['now_showing', 'coming_soon', 'ended', 'hidden'], true)
            ? $_POST['status']
            : 'now_showing';

        $payload = [
            'title' => $title,
            'title_vi' => trim($_POST['title_vi'] ?? '') ?: null,
            'overview' => trim($_POST['overview'] ?? '') ?: null,
            'poster_path' => $posterPath ?: 'public/images/posters/dune-part-two.svg',
            'trailer_url' => trim($_POST['trailer_url'] ?? '') ?: null,
            'release_date' => $releaseDate,
            'runtime' => max(30, (int) ($_POST['runtime'] ?? 120)),
            'director' => trim($_POST['director'] ?? '') ?: null,
            'cast_text' => trim($_POST['cast_text'] ?? '') ?: null,
            'age_rating' => trim($_POST['age_rating'] ?? 'T13') ?: 'T13',
            'imdb_rating' => max(1.0, min(10.0, (float) ($_POST['imdb_rating'] ?? 8.0))),
            'status' => $status,
        ];

        if ($movieId > 0 && $movieModel->find($movieId)) {
            $movieModel->update($movieId, $payload);
            $actionText = 'Đã cập nhật thông tin phim';
        } else {
            $payload['slug'] = $slug;
            $movieId = $movieModel->create($payload);
            $actionText = 'Đã thêm bộ phim mới';
        }

        $genreIds = (!empty($_POST['genre_ids']) && is_array($_POST['genre_ids'])) ? $_POST['genre_ids'] : [];
        $movieModel->syncGenres($movieId, $genreIds);

        $createdShows = 0;
        if ($status === 'now_showing' && !empty($_POST['auto_create_shows'])) {
            $createdShows = $showModel->ensureUpcomingShowsForMovie($movieId, 65000, 3);
        } elseif ($status === 'now_showing') {
            $createdShows = $showModel->ensureUpcomingShowsForMovie($movieId, 65000, 1);
        }

        $extraMsg = $createdShows > 0 ? " và tự động mở bán {$createdShows} suất chiếu sắp tới!" : '!';
        $this->setFlash('success', $actionText . $extraMsg);
        $this->redirect('?route=admin&tab=movies');
    }

    public function updateMovieStatus(): void
    {
        $this->requireRole(['admin']);
        $movieId = (int) ($_POST['movie_id'] ?? 0);
        $status = in_array($_POST['status'] ?? '', ['now_showing', 'coming_soon', 'ended', 'hidden'], true)
            ? $_POST['status']
            : 'now_showing';

        $movieModel = new MovieModel();
        $movieModel->update($movieId, ['status' => $status]);

        $extra = '';
        if ($status === 'now_showing') {
            $showModel = new ShowModel();
            $created = $showModel->ensureUpcomingShowsForMovie($movieId, 65000, 3);
            if ($created > 0) {
                $extra = " Đã tự động tạo {$created} suất chiếu mở bán cho phim.";
            }
        }

        $this->setFlash('success', "Đã cập nhật trạng thái phim #{$movieId} sang '{$status}'." . $extra);
        $this->redirect('?route=admin&tab=movies');
    }

    public function deleteMovie(): void
    {
        $this->requireRole(['admin']);
        $movieId = (int) ($_POST['movie_id'] ?? 0);
        if ($movieId <= 0) {
            $this->redirect('?route=admin&tab=movies');
        }

        $movieModel = new MovieModel();
        $result = $movieModel->deleteOrHideMovie($movieId);
        if ($result === 'deleted') {
            $this->setFlash('success', "Đã xóa bộ phim #{$movieId}.");
        } else {
            $this->setFlash('info', "Phim #{$movieId} đã có đơn đặt vé nên được chuyển sang trạng thái ẩn (hidden).");
        }
        $this->redirect('?route=admin&tab=movies');
    }

    public function saveRoom(): void
    {
        $this->requireRole(['admin']);
        $roomModel = new RoomModel();

        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            $this->setFlash('danger', 'Vui lòng nhập tên phòng chiếu.');
            $this->redirect('?route=admin&tab=rooms');
        }

        try {
            $roomId = $roomModel->create([
                'name' => $name,
                'room_type' => in_array($_POST['room_type'] ?? '', ['standard', 'vip', 'imax', 'four_d'], true) ? $_POST['room_type'] : 'standard',
                'capacity' => 0,
                'status' => 'active',
                'description' => trim($_POST['description'] ?? ''),
            ]);

            $createdSeats = $roomModel->generateDefaultSeatsForRoom($roomId);
            $this->setFlash('success', "Đã tạo phòng chiếu '{$name}' và khởi tạo {$createdSeats} ghế tiêu chuẩn!");
        } catch (\Throwable $e) {
            $this->setFlash('danger', 'Không thể tạo phòng chiếu (có thể trùng tên phòng): ' . $e->getMessage());
        }
        $this->redirect('?route=admin&tab=rooms');
    }

    public function updateRoom(): void
    {
        $this->requireRole(['admin']);
        $roomId = (int) ($_POST['room_id'] ?? 0);
        if ($roomId <= 0) {
            $this->redirect('?route=admin&tab=rooms');
        }

        $roomModel = new RoomModel();
        $status = in_array($_POST['status'] ?? '', ['active', 'maintenance'], true) ? $_POST['status'] : 'active';
        $roomType = in_array($_POST['room_type'] ?? '', ['standard', 'vip', 'imax', 'four_d'], true) ? $_POST['room_type'] : 'standard';
        $name = trim($_POST['name'] ?? '');

        $updateData = [
            'room_type' => $roomType,
            'status' => $status,
        ];
        if ($name !== '') {
            $updateData['name'] = $name;
        }
        if (isset($_POST['description'])) {
            $updateData['description'] = trim($_POST['description']);
        }

        $roomModel->update($roomId, $updateData);
        $this->setFlash('success', "Đã cập nhật phòng chiếu #{$roomId}.");
        $this->redirect('?route=admin&tab=rooms');
    }

    public function deleteRoom(): void
    {
        $this->requireRole(['admin']);
        $roomId = (int) ($_POST['room_id'] ?? 0);
        if ($roomId <= 0) {
            $this->redirect('?route=admin&tab=rooms');
        }

        $roomModel = new RoomModel();
        $res = $roomModel->deleteOrDisableRoom($roomId);
        if ($res === 'deleted') {
            $this->setFlash('success', "Đã xóa phòng chiếu #{$roomId}.");
        } else {
            $this->setFlash('info', "Phòng #{$roomId} đã có lịch chiếu nên được chuyển sang trạng thái bảo trì (maintenance).");
        }
        $this->redirect('?route=admin&tab=rooms');
    }

    public function generateSeats(): void
    {
        $this->requireRole(['admin']);
        $roomId = (int) ($_POST['room_id'] ?? 0);
        $roomModel = new RoomModel();
        $created = $roomModel->generateDefaultSeatsForRoom($roomId);
        if ($created > 0) {
            $this->setFlash('success', "Đã khởi tạo bổ sung {$created} ghế cho phòng #{$roomId}.");
        } else {
            $this->setFlash('info', "Phòng #{$roomId} đã có đầy đủ sơ đồ 27 ghế tiêu chuẩn (A1..E3) và đã đồng bộ với các suất chiếu.");
        }
        $this->redirect('?route=admin&tab=rooms');
    }

    public function saveShow(): void
    {
        $this->requireRole(['admin']);
        $showModel = new ShowModel();
        $movieModel = new MovieModel();

        try {
            $startTimeInput = $_POST['start_time'] ?? null;
            $movieIdInput = $_POST['movie_id'] ?? null;
            $roomIdInput = $_POST['room_id'] ?? null;
            $basePriceInput = $_POST['base_price'] ?? 65000;
            $startTimestamp = is_string($startTimeInput) ? strtotime($startTimeInput) : false;
            $movieId = is_string($movieIdInput) || is_int($movieIdInput)
                ? filter_var($movieIdInput, FILTER_VALIDATE_INT)
                : false;
            $roomId = is_string($roomIdInput) || is_int($roomIdInput)
                ? filter_var($roomIdInput, FILTER_VALIDATE_INT)
                : false;

            if (
                $startTimestamp === false ||
                $startTimestamp <= time() ||
                $movieId === false ||
                $movieId <= 0 ||
                $roomId === false ||
                $roomId <= 0 ||
                !is_numeric($basePriceInput) ||
                (float) $basePriceInput <= 0
            ) {
                throw new \InvalidArgumentException('Vui lòng chọn phim, phòng, thời gian tương lai và giá vé hợp lệ.');
            }

            $movie = $movieModel->find((int) $movieId);
            $runtimeMinutes = max(60, (int) ($movie['runtime'] ?? 120));

            $startTime = date('Y-m-d H:i:s', $startTimestamp);
            $endTime = date('Y-m-d H:i:s', $startTimestamp + ($runtimeMinutes * 60));

            $showId = $showModel->createShowWithSeats([
                'movie_id' => (int) $movieId,
                'room_id' => (int) $roomId,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'base_price' => (float) $basePriceInput,
                'status' => 'selling',
            ]);

            // Nếu phim đang ở trạng thái sắp chiếu/đã kết thúc thì tự động bật sang đang chiếu để khách đặt vé được ngay
            if ($movie && $movie['status'] !== 'now_showing') {
                $movieModel->update((int) $movieId, ['status' => 'now_showing']);
            }

            $this->setFlash('success', "Đã tạo suất chiếu #{$showId} và mở bán ghế thành công!");
        } catch (\Throwable $e) {
            $this->setFlash('danger', 'Lỗi tạo suất chiếu: ' . $e->getMessage());
        }

        $this->redirect('?route=admin&tab=shows');
    }

    public function updateShow(): void
    {
        $this->requireRole(['admin']);
        $showId = (int) ($_POST['show_id'] ?? 0);
        if ($showId <= 0) {
            $this->redirect('?route=admin&tab=shows');
        }

        $showModel = new ShowModel();
        $movieModel = new MovieModel();

        try {
            $existing = $showModel->find($showId);
            if (!$existing) {
                throw new \InvalidArgumentException('Không tìm thấy suất chiếu.');
            }

            $movieId = (int) ($_POST['movie_id'] ?? $existing['movie_id']);
            $roomId = (int) ($_POST['room_id'] ?? $existing['room_id']);
            $basePrice = (float) ($_POST['base_price'] ?? $existing['base_price']);
            $status = in_array($_POST['status'] ?? '', ['scheduled', 'selling', 'completed', 'cancelled'], true)
                ? $_POST['status']
                : (($_POST['status'] ?? '') === 'closed' ? 'completed' : $existing['status']);

            $startTimeInput = $_POST['start_time'] ?? $existing['start_time'];
            $startTimestamp = is_string($startTimeInput) ? strtotime($startTimeInput) : false;
            if ($startTimestamp === false) {
                throw new \InvalidArgumentException('Thời gian bắt đầu không hợp lệ.');
            }

            $movie = $movieModel->find($movieId);
            $runtimeMinutes = max(60, (int) ($movie['runtime'] ?? 120));
            $startTime = date('Y-m-d H:i:s', $startTimestamp);
            $endTime = date('Y-m-d H:i:s', $startTimestamp + ($runtimeMinutes * 60));

            $showModel->updateShowWithSeats($showId, [
                'movie_id' => $movieId,
                'room_id' => $roomId,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'base_price' => $basePrice,
                'status' => $status,
            ]);

            $this->setFlash('success', "Đã cập nhật suất chiếu #{$showId} thành công!");
        } catch (\Throwable $e) {
            $this->setFlash('danger', 'Lỗi cập nhật suất chiếu: ' . $e->getMessage());
        }

        $this->redirect('?route=admin&tab=shows');
    }

    public function deleteShow(): void
    {
        $this->requireRole(['admin']);
        $showId = (int) ($_POST['show_id'] ?? 0);
        if ($showId <= 0) {
            $this->redirect('?route=admin&tab=shows');
        }

        $showModel = new ShowModel();
        $res = $showModel->deleteOrCancelShow($showId);
        if ($res === 'deleted') {
            $this->setFlash('success', "Đã xóa suất chiếu #{$showId}.");
        } else {
            $this->setFlash('info', "Suất chiếu #{$showId} đã có đơn đặt vé nên được chuyển sang trạng thái Đã hủy (cancelled).");
        }
        $this->redirect('?route=admin&tab=shows');
    }

    public function autoGenerateShows(): void
    {
        $this->requireRole(['admin']);
        $showModel = new ShowModel();
        $created = $showModel->autoGenerateShowsForNowShowingMovies(4);
        if ($created > 0) {
            $this->setFlash('success', "Đã tự động tạo bổ sung {$created} suất chiếu mở bán cho các bộ phim Đang chiếu!");
        } else {
            $this->setFlash('info', 'Tất cả các bộ phim Đang chiếu đều đã có đủ suất chiếu mở bán.');
        }
        $this->redirect('?route=admin&tab=shows');
    }

    public function checkInTicket(): void
    {
        (new TicketSecurityController())->checkIn();
    }

    public function saveConcession(): void
    {
        $this->requireRole(['admin']);
        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            $this->setFlash('danger', 'Vui lòng nhập tên sản phẩm bắp nước.');
            $this->redirect('?route=admin&tab=concessions');
        }

        $engagementModel = new EngagementModel();
        $engagementModel->create([
            'name' => $name,
            'description' => trim($_POST['description'] ?? ''),
            'price' => max(0, (float) ($_POST['price'] ?? 50000)),
            'category' => trim($_POST['category'] ?? 'Combo') ?: 'Combo',
            'stock' => max(0, (int) ($_POST['stock'] ?? 100)),
            'status' => 'active',
            'sort_order' => 1,
        ]);
        $this->setFlash('success', 'Đã thêm sản phẩm bắp nước mới!');
        $this->redirect('?route=admin&tab=concessions');
    }

    public function deleteConcession(): void
    {
        $this->requireRole(['admin']);
        $id = (int) ($_POST['concession_id'] ?? 0);
        if ($id > 0) {
            $engagementModel = new EngagementModel();
            try {
                $db = $engagementModel->getDb();
                $stmtUsed = $db->prepare("SELECT COUNT(*) FROM booking_concessions WHERE concession_id = :id");
                $stmtUsed->execute(['id' => $id]);
                if ((int) $stmtUsed->fetchColumn() > 0) {
                    $engagementModel->update($id, ['status' => 'inactive', 'stock' => 0]);
                    $this->setFlash('info', 'Đã ngừng bán combo bắp nước #' . $id);
                } else {
                    $db->prepare("DELETE FROM concessions WHERE id = :id")->execute(['id' => $id]);
                    $this->setFlash('success', 'Đã xóa combo bắp nước #' . $id);
                }
            } catch (\Throwable $e) {
                $this->setFlash('danger', 'Không thể xóa sản phẩm: ' . $e->getMessage());
            }
        }
        $this->redirect('?route=admin&tab=concessions');
    }

    public function saveCoupon(): void
    {
        $this->requireRole(['admin']);
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $name = trim($_POST['name'] ?? '');

        if ($code === '' || $name === '') {
            $this->setFlash('danger', 'Vui lòng nhập đầy đủ mã giảm giá và tên chương trình.');
            $this->redirect('?route=admin&tab=concessions');
        }

        try {
            $engagementModel = new EngagementModel();
            $db = $engagementModel->getDb();
            $stmt = $db->prepare("
                INSERT INTO coupons (code, name, discount_type, discount_value, min_order_amount, usage_limit, status, created_at, updated_at)
                VALUES (:code, :name, :type, :val, :min_amt, :limit_cnt, 'active', NOW(), NOW())
                ON DUPLICATE KEY UPDATE
                    name = VALUES(name),
                    discount_type = VALUES(discount_type),
                    discount_value = VALUES(discount_value),
                    min_order_amount = VALUES(min_order_amount),
                    usage_limit = VALUES(usage_limit),
                    status = 'active',
                    updated_at = NOW()
            ");
            $stmt->execute([
                'code' => $code,
                'name' => $name,
                'type' => in_array($_POST['discount_type'] ?? '', ['fixed', 'percent'], true) ? $_POST['discount_type'] : 'fixed',
                'val' => max(0, (float) ($_POST['discount_value'] ?? 20000)),
                'min_amt' => max(0, (float) ($_POST['min_order_amount'] ?? 0)),
                'limit_cnt' => max(1, (int) ($_POST['usage_limit'] ?? 100)),
            ]);
            $this->setFlash('success', "Đã lưu mã giảm giá {$code} thành công!");
        } catch (\Throwable $e) {
            $this->setFlash('danger', 'Lỗi lưu mã giảm giá: ' . $e->getMessage());
        }
        $this->redirect('?route=admin&tab=concessions');
    }

    public function deleteCoupon(): void
    {
        $this->requireRole(['admin']);
        $id = (int) ($_POST['coupon_id'] ?? 0);
        if ($id > 0) {
            $engagementModel = new EngagementModel();
            $db = $engagementModel->getDb();
            try {
                $stmtUsed = $db->prepare("SELECT COUNT(*) FROM bookings WHERE coupon_id = :id");
                $stmtUsed->execute(['id' => $id]);
                if ((int) $stmtUsed->fetchColumn() > 0) {
                    $db->prepare("UPDATE coupons SET status = 'inactive', updated_at = NOW() WHERE id = :id")->execute(['id' => $id]);
                    $this->setFlash('info', 'Mã giảm giá đã phát sinh đơn hàng nên được chuyển sang Ngừng hoạt động.');
                } else {
                    $db->prepare("DELETE FROM coupon_usages WHERE coupon_id = :id")->execute(['id' => $id]);
                    $db->prepare("DELETE FROM coupons WHERE id = :id")->execute(['id' => $id]);
                    $this->setFlash('success', 'Đã xóa mã giảm giá.');
                }
            } catch (\Throwable $e) {
                $this->setFlash('danger', 'Không thể xóa mã giảm giá: ' . $e->getMessage());
            }
        }
        $this->redirect('?route=admin&tab=concessions');
    }
}
