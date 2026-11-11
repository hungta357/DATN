<?php
/**
 * Script tự động khởi tạo Database `cinema_complete`, nạp dữ liệu mẫu,
 * sinh sơ đồ ghế cho phòng chiếu và tạo suất chiếu mẫu.
 * Có thể chạy lại nhiều lần (Idempotent) mà không báo lỗi trùng bảng.
 */

$config = require __DIR__ . '/config/config.php';
$dbConf = $config['db'];

try {
    $pdo = new PDO(
        sprintf('mysql:host=%s;port=%s;charset=%s', $dbConf['host'], $dbConf['port'], $dbConf['charset']),
        $dbConf['username'],
        $dbConf['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // 1. Tạo database nếu chưa có
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbConf['database']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `{$dbConf['database']}`");

    // 2. Kiểm tra xem bảng roles đã tồn tại chưa; nếu chưa thì mới chạy cinema_complete.sql
    $stmtCheck = $pdo->query("SHOW TABLES LIKE 'roles'");
    if ($stmtCheck->rowCount() === 0) {
        $schemaSql = file_get_contents(__DIR__ . '/database/cinema_complete.sql');
        $pdo->exec($schemaSql);
    } else {
        // Đảm bảo dữ liệu cơ bản của cinema_complete.sql luôn có mặt
        $pdo->exec("
            INSERT IGNORE INTO roles (id, name, display_name, description) VALUES
            (1, 'admin', 'Quan tri vien', 'Quan ly toan bo he thong'),
            (2, 'staff', 'Nhan vien', 'Ho tro van hanh rap va soat ve'),
            (3, 'customer', 'Khach hang', 'Dat ve va su dung dich vu');

            INSERT IGNORE INTO seat_types (id, name, surcharge, description) VALUES
            (1, 'Standard', 0, 'Ghe thuong'),
            (2, 'VIP', 20000, 'Ghe VIP'),
            (3, 'Couple', 50000, 'Ghe doi');

            INSERT IGNORE INTO payment_methods (id, name, code, description) VALUES
            (1, 'Tien mat tai quay', 'CASH', 'Thanh toan truc tiep tai quay'),
            (2, 'VNPay', 'VNPAY', 'Thanh toan qua cong VNPay'),
            (3, 'MoMo', 'MOMO', 'Thanh toan qua vi MoMo'),
            (4, 'Vi noi bo', 'WALLET', 'Thanh toan bang so du vi');

            INSERT IGNORE INTO cinema_info (id, name, address, phone, email)
            VALUES (1, 'CineNow', 'Tầng 3, Tòa nhà Imperial Plaza, 360 Giải Phóng, Hà Nội', '1900 636807', 'cskh@cinenow.vn');
        ");
    }

    // 3. Nạp dữ liệu mẫu từ seed_sample_data.sql
    $seedSql = file_get_contents(__DIR__ . '/database/seed_sample_data.sql');
    $pdo->exec($seedSql);

    // 4. Autoload models để tạo ghế mẫu & suất chiếu mẫu
    require_once __DIR__ . '/app/Core/Database.php';
    require_once __DIR__ . '/app/Core/BaseModel.php';
    require_once __DIR__ . '/app/Models/RoomModel.php';
    require_once __DIR__ . '/app/Models/ShowModel.php';

    $roomModel = new \App\Models\RoomModel();
    $roomModel->generateDefaultSeatsForRoom(1);
    $roomModel->generateDefaultSeatsForRoom(2);

    $showModel = new \App\Models\ShowModel();
    $existingShows = $showModel->all();
    if (empty($existingShows)) {
        $showModel->createShowWithSeats([
            'movie_id' => 1,
            'room_id' => 1,
            'start_time' => date('Y-m-d H:i:s', strtotime('+2 hours')),
            'end_time' => date('Y-m-d H:i:s', strtotime('+5 hours')),
            'base_price' => 60000,
            'status' => 'selling',
        ]);
        $showModel->createShowWithSeats([
            'movie_id' => 1,
            'room_id' => 2,
            'start_time' => date('Y-m-d H:i:s', strtotime('+5 hours')),
            'end_time' => date('Y-m-d H:i:s', strtotime('+8 hours')),
            'base_price' => 70000,
            'status' => 'selling',
        ]);
        $showModel->createShowWithSeats([
            'movie_id' => 2,
            'room_id' => 2,
            'start_time' => date('Y-m-d H:i:s', strtotime('+3 hours')),
            'end_time' => date('Y-m-d H:i:s', strtotime('+5 hours')),
            'base_price' => 55000,
            'status' => 'selling',
        ]);
    }

    if (PHP_SAPI === 'cli') {
        echo "[OK] Database cinema_complete va du lieu mau da san sang!\n";
    } else {
        echo "Khởi tạo Database cinema_complete và dữ liệu mẫu thành công! <a href='index.php'>Vào trang chủ</a>";
    }
} catch (Throwable $e) {
    echo "Lỗi khởi tạo Database: " . $e->getMessage() . "\n";
}
