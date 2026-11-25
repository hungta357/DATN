<?php

// Doc .env neu co (khong can cai them thu vien). Bien moi truong he thong duoc uu tien.
$envFile = dirname(__DIR__) . '/.env';
if (is_file($envFile) && is_readable($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = array_map('trim', explode('=', $line, 2));
        if ($key !== '' && getenv($key) === false) {
            $value = trim($value, "\\\"'");
            putenv($key . '=' . $value);
        }
    }
}

return [
    'app' => [
        'name' => 'CineNow',
        'base_url' => getenv('APP_URL') ?: 'http://localhost:8000',
        'timezone' => 'Asia/Ho_Chi_Minh',
        'hold_seat_minutes' => 10, // Thời gian giữ ghế (phút)
    ],
    'db' => [
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'port' => getenv('DB_PORT') ?: '3306',
        'database' => getenv('DB_DATABASE') ?: 'cinema_complete',
        'username' => getenv('DB_USERNAME') ?: 'root',
        'password' => getenv('DB_PASSWORD') ?: '',
        'charset' => 'utf8mb4',
    ],
];

