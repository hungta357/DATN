<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Việc khởi tạo dữ liệu chỉ được phép chạy từ Terminal trên máy phát triển.');
}

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

    $pdoApp = \App\Core\Database::getConnection();

    // 4b. Migration: thêm cột is_early nếu DB cũ chưa có
    $colCheck = $pdoApp->query("SHOW COLUMNS FROM shows LIKE 'is_early'");
    if ($colCheck->rowCount() === 0) {
        $pdoApp->exec("ALTER TABLE shows ADD COLUMN is_early TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = Suat chieu som' AFTER base_price");
        $pdoApp->exec("ALTER TABLE shows ADD INDEX idx_shows_is_early (is_early, start_time)");
        // Đánh dấu các suất sáng đã có
        $pdoApp->exec("UPDATE shows SET is_early = 1 WHERE HOUR(start_time) < 12");
        if (PHP_SAPI === 'cli') {
            echo "[OK] Da them cot shows.is_early va danh dau suat som.\n";
        }
    }

    $roomModel = new \App\Models\RoomModel();
    $roomModel->generateDefaultSeatsForRoom(1);
    $roomModel->generateDefaultSeatsForRoom(2);

    $showModel = new \App\Models\ShowModel();
    $appCfg = require __DIR__ . '/config/config.php';
    $earlyHour = (int) ($appCfg['app']['early_show_before_hour'] ?? 12);
    $earlyDiscount = (int) ($appCfg['app']['early_show_discount_percent'] ?? 20);
    $defaultBase = (float) ($appCfg['app']['default_base_price'] ?? 75000);

    $activeMovies = $pdoApp->query("SELECT id, runtime FROM movies WHERE status = 'now_showing' ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
    $activeRooms = $pdoApp->query("SELECT id FROM rooms WHERE status = 'active' ORDER BY id ASC LIMIT 2")->fetchAll(PDO::FETCH_ASSOC);

    // Tao lich chieu 7 ngay tiep theo, 5 khung gio/phong/ngay. Bo qua neu da ton tai.
    // Slot 09:00 = suất chiếu sớm (giá giảm).
    if (!empty($activeMovies) && !empty($activeRooms)) {
        $slots = ['09:00:00', '12:30:00', '16:00:00', '19:30:00', '22:30:00'];
        $movieCount = count($activeMovies);
        foreach ($activeRooms as $roomIndex => $room) {
            foreach (range(1, 7) as $dayOffset) {
                foreach ($slots as $slotIndex => $slot) {
                    $movie = $activeMovies[(($dayOffset - 1) * count($activeRooms) * count($slots) + $roomIndex * count($slots) + $slotIndex) % $movieCount];
                    $startAt = date('Y-m-d', strtotime("+{$dayOffset} day")) . ' ' . $slot;
                    $check = $pdoApp->prepare('SELECT id FROM shows WHERE room_id = :room_id AND start_time = :start_time LIMIT 1');
                    $check->execute(['room_id' => (int) $room['id'], 'start_time' => $startAt]);
                    if ($check->fetchColumn()) {
                        continue;
                    }
                    $runtime = max(80, (int) ($movie['runtime'] ?? 120));
                    $endAt = date('Y-m-d H:i:s', strtotime($startAt . " +{$runtime} minutes"));
                    $hour = (int) substr($slot, 0, 2);
                    $isEarly = $hour < $earlyHour ? 1 : 0;
                    $stdPrice = $roomIndex === 0 ? 65000.0 : $defaultBase;
                    $basePrice = $isEarly
                        ? \App\Models\ShowModel::suggestEarlyBasePrice($stdPrice, $earlyDiscount)
                        : $stdPrice;
                    $showModel->createShowWithSeats([
                        'movie_id' => (int) $movie['id'],
                        'room_id' => (int) $room['id'],
                        'start_time' => $startAt,
                        'end_time' => $endAt,
                        'base_price' => $basePrice,
                        'is_early' => $isEarly,
                        'status' => 'selling',
                    ]);
                }
            }
        }
    }


    if (PHP_SAPI === 'cli') {
        echo "[OK] Database cinema_complete va du lieu mau da san sang!\n";
    } else {
        echo "Khởi tạo Database cinema_complete và dữ liệu mẫu thành công! <a href='index.php'>Vào trang chủ</a>";
    }
} catch (Throwable $e) {
    echo "Lỗi khởi tạo Database: " . $e->getMessage() . "\n";
}
