<?php
/**
 * Tao 10 suat chieu demo cho CineNow.
 * Chay mot lan tu thu muc goc DATN_GIT: php tao_10_suat_chieu.php
 * Script khong xoa/sua show cu; tu dong tao show_seats qua ShowModel.
 */

declare(strict_types=1);

$config = require __DIR__ . '/config/config.php';
date_default_timezone_set($config['app']['timezone'] ?? 'Asia/Ho_Chi_Minh');

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/app/';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }
    $relativeClass = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

use App\Core\Database;
use App\Models\ShowModel;

try {
    $db = Database::getConnection();
    $db->exec("SET time_zone = '+07:00'");

    // Chan chay lap: neu da co bat ky show tuong lai trong 7 ngay thi dung.
    $check = $db->prepare("SELECT COUNT(*) FROM shows
        WHERE status IN ('scheduled','selling')
          AND start_time >= NOW()
          AND start_time < DATE_ADD(NOW(), INTERVAL 7 DAY)");
    $check->execute();
    $existingUpcoming = (int) $check->fetchColumn();
    if ($existingUpcoming > 0) {
        throw new RuntimeException("Da co {$existingUpcoming} suat chieu tuong lai trong 7 ngay. Script dung de tranh tao trung. Hay kiem tra lich hien co truoc.");
    }

    $rooms = $db->query("SELECT id, name FROM rooms WHERE status = 'active' ORDER BY id ASC LIMIT 2")->fetchAll(PDO::FETCH_ASSOC);
    if (count($rooms) < 2) {
        throw new RuntimeException('Can it nhat 2 phong chieu dang hoat dong de tao 10 suat demo.');
    }

    $movies = $db->query("SELECT id, title, title_vi, runtime
        FROM movies
        WHERE status = 'now_showing' AND runtime IS NOT NULL AND runtime > 0
        ORDER BY imdb_rating DESC, id ASC
        LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
    if (count($movies) < 10) {
        throw new RuntimeException('Hien khong co du 10 phim co status now_showing va runtime hop le. Khong tao du lieu nao.');
    }

    // Nam khung gio, cach nhau du de ket thuc phim va don phong.
    $timeSlots = ['09:00:00', '12:30:00', '16:00:00', '19:30:00', '22:45:00'];
    $targetDate = (new DateTimeImmutable('tomorrow'))->format('Y-m-d');
    $showModel = new ShowModel();
    $created = [];
    $movieIndex = 0;

    // 2 phong x 5 khung gio = 10 suat; neu slot bi trung lich thi bo qua.
    foreach ($rooms as $room) {
        foreach ($timeSlots as $time) {
            if ($movieIndex >= 10) {
                break 2;
            }

            $movie = $movies[$movieIndex];
            $start = new DateTimeImmutable($targetDate . ' ' . $time);
            $end = $start->modify('+' . (int) $movie['runtime'] . ' minutes');
            if ($start <= new DateTimeImmutable('now')) {
                continue;
            }

            // Kiem tra ca suat cu khong bi huy; giu khoang don phong 15 phut.
            $conflict = $db->prepare("SELECT id FROM shows
                WHERE room_id = :room_id
                  AND status <> 'cancelled'
                  AND start_time < :buffered_end
                  AND end_time > :buffered_start
                LIMIT 1");
            $bufferedStart = $start->modify('-15 minutes')->format('Y-m-d H:i:s');
            $bufferedEnd = $end->modify('+15 minutes')->format('Y-m-d H:i:s');
            $conflict->execute([
                'room_id' => (int) $room['id'],
                'buffered_start' => $bufferedStart,
                'buffered_end' => $bufferedEnd,
            ]);
            if ($conflict->fetchColumn()) {
                continue;
            }

            $showId = $showModel->createShowWithSeats([
                'movie_id' => (int) $movie['id'],
                'room_id' => (int) $room['id'],
                'start_time' => $start->format('Y-m-d H:i:s'),
                'end_time' => $end->format('Y-m-d H:i:s'),
                'base_price' => 65000,
                'status' => 'selling',
            ]);

            $seatCountStmt = $db->prepare("SELECT COUNT(*) FROM show_seats WHERE show_id = :show_id AND status = 'available'");
            $seatCountStmt->execute(['show_id' => $showId]);
            $created[] = [
                'id' => $showId,
                'movie' => $movie['title_vi'] ?: $movie['title'],
                'room' => $room['name'],
                'start' => $start->format('d/m/Y H:i'),
                'seats' => (int) $seatCountStmt->fetchColumn(),
            ];
            $movieIndex++;
        }
    }

    if (count($created) !== 10) {
        // Khong xoa cac show da tao; thong bao de nguoi dung kiem tra truoc khi chay lai.
        throw new RuntimeException('Moi tao duoc ' . count($created) . '/10 suat. Cac suat da tao van duoc giu nguyen; hay kiem tra bang shows truoc khi chay tiep.');
    }

    echo "\nDA TAO THANH CONG 10 SUAT CHIEU DEMO\n";
    foreach ($created as $row) {
        echo sprintf("#%d | %s | %s | %s | %d ghe trong\n", $row['id'], $row['movie'], $row['room'], $row['start'], $row['seats']);
    }
    echo "\nMo trang lich chieu: http://localhost:8000/?route=schedule\n";
} catch (Throwable $e) {
    fwrite(STDERR, "KHONG HOAN TAT: " . $e->getMessage() . "\n");
    exit(1);
}
