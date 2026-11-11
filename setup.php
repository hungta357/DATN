<?php
/**
 * Script tự động khởi tạo Database `cinema_complete`, nạp dữ liệu mẫu,
 * sinh sơ đồ ghế cho phòng chiếu và tạo suất chiếu mẫu.
 * Chạy bằng lệnh: php setup.php hoặc mở http://localhost:8000/setup.php
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

    $schemaSql = file_get_contents(__DIR__ . '/database/cinema_complete.sql');
    $pdo->exec($schemaSql);

    $seedSql = file_get_contents(__DIR__ . '/database/seed_sample_data.sql');
    $pdo->exec($seedSql);

    // Autoload models để tạo ghế mẫu & suất chiếu mẫu
    require __DIR__ . '/app/Core/Database.php';
    require __DIR__ . '/app/Core/BaseModel.php';
    require __DIR__ . '/app/Models/RoomModel.php';
    require __DIR__ . '/app/Models/ShowModel.php';

    $roomModel = new \App\Models\RoomModel();
    $roomModel->generateDefaultSeatsForRoom(1);
    $roomModel->generateDefaultSeatsForRoom(2);

    $showModel = new \App\Models\ShowModel();
    $existingShows = $showModel->all();
    if (empty($existingShows)) {
        $showModel->createShowWithSeats([
            'movie_id' => 1,
            'room_id' => 1,
            'start_time' => date('Y-m-d H:i:s', strtotime('+3 hours')),
            'end_time' => date('Y-m-d H:i:s', strtotime('+6 hours')),
            'base_price' => 90000,
            'status' => 'selling',
        ]);
        $showModel->createShowWithSeats([
            'movie_id' => 2,
            'room_id' => 2,
            'start_time' => date('Y-m-d H:i:s', strtotime('+4 hours')),
            'end_time' => date('Y-m-d H:i:s', strtotime('+6 hours')),
            'base_price' => 80000,
            'status' => 'selling',
        ]);
    }

    echo "Khởi tạo Database cinema_complete và dữ liệu mẫu thành công! <a href='index.php'>Vào trang chủ</a>";
} catch (Throwable $e) {
    echo "Lỗi khởi tạo Database: " . $e->getMessage();
}
