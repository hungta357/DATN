<?php

return [
    'app' => [
        'name' => 'Cinema Booking System',
        'base_url' => 'http://localhost:8000',
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

