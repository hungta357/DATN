<?php

session_start();

$config = require __DIR__ . '/config/config.php';
date_default_timezone_set($config['app']['timezone'] ?? 'Asia/Ho_Chi_Minh');

// PSR-4 style autoloader cho namespace App\
spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\BookingController;
use App\Controllers\HomeController;
use App\Controllers\WalletController;

$route = $_GET['route'] ?? 'home';

switch ($route) {
    // --- NHÁNH 1: AUTH / USERS ---
    case 'auth/login':
        (new AuthController())->login();
        break;
    case 'auth/register':
        (new AuthController())->register();
        break;
    case 'auth/logout':
        (new AuthController())->logout();
        break;

    // --- NHÁNH 2 & 7: TRANG CHỦ BETA CINEMA / LỊCH CHIẾU / GIÁ VÉ / ƯU ĐÃI / THÔNG TIN RẠP ---
    case 'home':
        (new HomeController())->index();
        break;
    case 'schedule':
        (new HomeController())->schedule();
        break;
    case 'prices':
        (new HomeController())->prices();
        break;
    case 'promotions':
        (new HomeController())->promotions();
        break;
    case 'about':
        (new HomeController())->about();
        break;
    case 'movie/detail':
        (new HomeController())->movieDetail();
        break;
    case 'movie/review':
        (new HomeController())->submitReview();
        break;
    case 'wishlist/toggle':
        (new HomeController())->toggleWishlist();
        break;
    case 'wishlist':
        (new HomeController())->wishlist();
        break;
    case 'notifications':
        (new HomeController())->notifications();
        break;

    // --- NHÁNH 3, 4, 5, 6, 7: SHOWS / SEATS / BOOKINGS / PAYMENTS / TICKETS ---
    case 'booking/seats':
        (new BookingController())->selectSeats();
        break;
    case 'booking/create':
        (new BookingController())->create();
        break;
    case 'booking/checkout':
        (new BookingController())->checkout();
        break;
    case 'booking/pay':
        (new BookingController())->pay();
        break;
    case 'booking/my':
        (new BookingController())->myBookings();
        break;

    // --- NHÁNH 6: WALLET (VÍ NỘI BỘ) ---
    case 'wallet':
        (new WalletController())->index();
        break;
    case 'wallet/deposit':
        (new WalletController())->deposit();
        break;

    // --- QUẢN TRỊ TỔNG HỢP 7 NHÁNH (ADMIN & STAFF) ---
    case 'admin':
        (new AdminController())->dashboard();
        break;
    case 'admin/cinema/save':
        (new AdminController())->saveCinemaInfo();
        break;
    case 'admin/user/status':
        (new AdminController())->updateUserStatus();
        break;
    case 'admin/movie/save':
        (new AdminController())->saveMovie();
        break;
    case 'admin/movie/status':
        (new AdminController())->updateMovieStatus();
        break;
    case 'admin/room/save':
        (new AdminController())->saveRoom();
        break;
    case 'admin/room/generate-seats':
        (new AdminController())->generateSeats();
        break;
    case 'admin/show/save':
        (new AdminController())->saveShow();
        break;
    case 'admin/ticket/checkin':
        (new AdminController())->checkInTicket();
        break;
    case 'admin/concession/save':
        (new AdminController())->saveConcession();
        break;
    case 'admin/coupon/save':
        (new AdminController())->saveCoupon();
        break;

    default:
        http_response_code(404);
        echo '404 - Route không tồn tại.';
        break;
}
