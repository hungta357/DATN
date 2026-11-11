-- =========================================================
-- CINEMA BOOKING DATABASE - Laravel 12 / PHP MVC + MySQL 8.0
-- Mo hinh: mot rap duy nhat, co the co nhieu phong chieu
-- Tao database moi de tranh ghi de database cu.
-- =========================================================

CREATE DATABASE IF NOT EXISTS cinema_complete
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cinema_complete;

-- 1. ROLES / USERS
CREATE TABLE roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    display_name VARCHAR(100) NOT NULL,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB;

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(20) NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    avatar VARCHAR(500) NULL,
    email_verified_at TIMESTAMP NULL,
    status ENUM('active','locked','pending') NOT NULL DEFAULT 'active',
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_users_status (status)
) ENGINE=InnoDB;

CREATE TABLE user_roles (
    user_id BIGINT UNSIGNED NOT NULL,
    role_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    PRIMARY KEY (user_id, role_id),
    CONSTRAINT fk_user_roles_user FOREIGN KEY (user_id) REFERENCES users(id)
      ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_user_roles_role FOREIGN KEY (role_id) REFERENCES roles(id)
      ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- Thong tin cua mot rap duy nhat. Chi can mot dong du lieu.
CREATE TABLE cinema_info (
    id TINYINT UNSIGNED PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    address VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NULL,
    email VARCHAR(150) NULL,
    logo VARCHAR(500) NULL,
    description TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB;

-- 2. MOVIES / GENRES
CREATE TABLE movies (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    title_vi VARCHAR(255) NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    overview TEXT NULL,
    poster_path VARCHAR(500) NULL,
    backdrop_path VARCHAR(500) NULL,
    trailer_url VARCHAR(500) NULL,
    release_date DATE NULL,
    runtime SMALLINT UNSIGNED NULL COMMENT 'Thoi luong phim tinh bang phut',
    director VARCHAR(255) NULL,
    cast_text TEXT NULL,
    language VARCHAR(100) NULL,
    country VARCHAR(100) NULL,
    age_rating VARCHAR(20) NULL,
    imdb_rating DECIMAL(3,1) NULL,
    status ENUM('coming_soon','now_showing','ended','hidden') NOT NULL DEFAULT 'coming_soon',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_movies_status_release (status, release_date),
    FULLTEXT INDEX ft_movies_title_overview (title, title_vi, overview)
) ENGINE=InnoDB;

CREATE TABLE genres (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    slug VARCHAR(120) NOT NULL UNIQUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB;

CREATE TABLE movie_genres (
    movie_id BIGINT UNSIGNED NOT NULL,
    genre_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (movie_id, genre_id),
    CONSTRAINT fk_movie_genres_movie FOREIGN KEY (movie_id) REFERENCES movies(id)
      ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_movie_genres_genre FOREIGN KEY (genre_id) REFERENCES genres(id)
      ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- 3. ROOMS / SEATS
CREATE TABLE rooms (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    room_type ENUM('standard','vip','imax','four_d','other') NOT NULL DEFAULT 'standard',
    capacity SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    status ENUM('active','maintenance','inactive') NOT NULL DEFAULT 'active',
    description VARCHAR(500) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB;

CREATE TABLE seat_types (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL UNIQUE,
    surcharge DECIMAL(12,2) NOT NULL DEFAULT 0,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB;

CREATE TABLE seats (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    room_id BIGINT UNSIGNED NOT NULL,
    seat_type_id BIGINT UNSIGNED NOT NULL,
    seat_code VARCHAR(10) NOT NULL,
    row_label VARCHAR(5) NOT NULL,
    seat_number SMALLINT UNSIGNED NOT NULL,
    status ENUM('active','blocked','maintenance') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY uq_room_seat_code (room_id, seat_code),
    UNIQUE KEY uq_room_row_number (room_id, row_label, seat_number),
    CONSTRAINT fk_seats_room FOREIGN KEY (room_id) REFERENCES rooms(id)
      ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_seats_type FOREIGN KEY (seat_type_id) REFERENCES seat_types(id)
      ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 4. SHOWTIMES / SEAT INVENTORY FOR EACH SHOW
CREATE TABLE shows (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    movie_id BIGINT UNSIGNED NOT NULL,
    room_id BIGINT UNSIGNED NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    base_price DECIMAL(12,2) NOT NULL,
    status ENUM('scheduled','selling','cancelled','completed') NOT NULL DEFAULT 'scheduled',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_shows_movie FOREIGN KEY (movie_id) REFERENCES movies(id)
      ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_shows_room FOREIGN KEY (room_id) REFERENCES rooms(id)
      ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_shows_start_status (start_time, status),
    INDEX idx_shows_movie_start (movie_id, start_time),
    INDEX idx_shows_room_start (room_id, start_time)
) ENGINE=InnoDB;

-- Moi ghe trong moi suat chieu co mot dong trang thai va gia rieng.
CREATE TABLE show_seats (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    show_id BIGINT UNSIGNED NOT NULL,
    seat_id BIGINT UNSIGNED NOT NULL,
    price DECIMAL(12,2) NOT NULL,
    status ENUM('available','held','booked','blocked') NOT NULL DEFAULT 'available',
    held_by_user_id BIGINT UNSIGNED NULL,
    hold_expires_at DATETIME NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY uq_show_seat (show_id, seat_id),
    INDEX idx_show_seats_status (show_id, status),
    INDEX idx_show_seats_hold_expiry (status, hold_expires_at),
    CONSTRAINT fk_show_seats_show FOREIGN KEY (show_id) REFERENCES shows(id)
      ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_show_seats_seat FOREIGN KEY (seat_id) REFERENCES seats(id)
      ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_show_seats_user FOREIGN KEY (held_by_user_id) REFERENCES users(id)
      ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

-- 5. BOOKINGS / BOOKING DETAILS / TICKETS
CREATE TABLE bookings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_code VARCHAR(40) NOT NULL UNIQUE,
    user_id BIGINT UNSIGNED NOT NULL,
    show_id BIGINT UNSIGNED NOT NULL,
    coupon_id BIGINT UNSIGNED NULL,
    booking_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    subtotal DECIMAL(12,2) NOT NULL DEFAULT 0,
    discount_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    concession_total DECIMAL(12,2) NOT NULL DEFAULT 0,
    total_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    status ENUM('pending','confirmed','cancelled','expired','refunded') NOT NULL DEFAULT 'pending',
    note VARCHAR(500) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_bookings_user FOREIGN KEY (user_id) REFERENCES users(id)
      ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_bookings_show FOREIGN KEY (show_id) REFERENCES shows(id)
      ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_bookings_user_time (user_id, booking_time),
    INDEX idx_bookings_status (status)
) ENGINE=InnoDB;

CREATE TABLE booking_details (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id BIGINT UNSIGNED NOT NULL,
    show_seat_id BIGINT UNSIGNED NOT NULL,
    seat_price DECIMAL(12,2) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY uq_booking_show_seat (show_seat_id),
    CONSTRAINT fk_booking_details_booking FOREIGN KEY (booking_id) REFERENCES bookings(id)
      ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_booking_details_show_seat FOREIGN KEY (show_seat_id) REFERENCES show_seats(id)
      ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE tickets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_detail_id BIGINT UNSIGNED NOT NULL UNIQUE,
    ticket_code VARCHAR(60) NOT NULL UNIQUE,
    qr_code VARCHAR(500) NULL,
    status ENUM('valid','used','cancelled','refunded') NOT NULL DEFAULT 'valid',
    checked_in_at DATETIME NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_tickets_booking_detail FOREIGN KEY (booking_detail_id) REFERENCES booking_details(id)
      ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 6. PAYMENTS / PAYMENT METHODS / WALLET
CREATE TABLE payment_methods (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    code VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(255) NULL,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB;

CREATE TABLE payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id BIGINT UNSIGNED NOT NULL,
    payment_method_id BIGINT UNSIGNED NOT NULL,
    transaction_code VARCHAR(150) NULL UNIQUE,
    amount DECIMAL(12,2) NOT NULL,
    status ENUM('pending','success','failed','cancelled','refunded') NOT NULL DEFAULT 'pending',
    paid_at DATETIME NULL,
    gateway_response JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_payments_booking FOREIGN KEY (booking_id) REFERENCES bookings(id)
      ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_payments_method FOREIGN KEY (payment_method_id) REFERENCES payment_methods(id)
      ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_payments_booking_status (booking_id, status)
) ENGINE=InnoDB;

CREATE TABLE wallets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL UNIQUE,
    balance DECIMAL(12,2) NOT NULL DEFAULT 0,
    currency CHAR(3) NOT NULL DEFAULT 'VND',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_wallets_user FOREIGN KEY (user_id) REFERENCES users(id)
      ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE wallet_transactions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    wallet_id BIGINT UNSIGNED NOT NULL,
    booking_id BIGINT UNSIGNED NULL,
    type ENUM('deposit','payment','refund','adjustment') NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    balance_after DECIMAL(12,2) NOT NULL,
    transaction_code VARCHAR(150) NOT NULL UNIQUE,
    note VARCHAR(500) NULL,
    status ENUM('pending','success','failed') NOT NULL DEFAULT 'success',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_wallet_transactions_wallet FOREIGN KEY (wallet_id) REFERENCES wallets(id)
      ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_wallet_transactions_booking FOREIGN KEY (booking_id) REFERENCES bookings(id)
      ON UPDATE CASCADE ON DELETE SET NULL,
    INDEX idx_wallet_transactions_wallet_date (wallet_id, created_at)
) ENGINE=InnoDB;

-- 7. CONCESSIONS / COUPONS / REVIEWS / WISHLISTS / NOTIFICATIONS
CREATE TABLE concessions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT NULL,
    image_url VARCHAR(500) NULL,
    price DECIMAL(12,2) NOT NULL,
    category VARCHAR(80) NULL,
    stock INT UNSIGNED NOT NULL DEFAULT 0,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_concessions_status_sort (status, sort_order)
) ENGINE=InnoDB;

CREATE TABLE booking_concessions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id BIGINT UNSIGNED NOT NULL,
    concession_id BIGINT UNSIGNED NOT NULL,
    quantity SMALLINT UNSIGNED NOT NULL,
    unit_price DECIMAL(12,2) NOT NULL,
    line_total DECIMAL(12,2) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_booking_concessions_booking FOREIGN KEY (booking_id) REFERENCES bookings(id)
      ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_booking_concessions_concession FOREIGN KEY (concession_id) REFERENCES concessions(id)
      ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_booking_concessions_booking (booking_id)
) ENGINE=InnoDB;

CREATE TABLE coupons (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    description VARCHAR(500) NULL,
    discount_type ENUM('fixed','percent') NOT NULL,
    discount_value DECIMAL(12,2) NOT NULL,
    min_order_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    max_discount_amount DECIMAL(12,2) NULL,
    usage_limit INT UNSIGNED NULL,
    used_count INT UNSIGNED NOT NULL DEFAULT 0,
    starts_at DATETIME NULL,
    expires_at DATETIME NULL,
    status ENUM('active','inactive','expired') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_coupons_status_dates (status, starts_at, expires_at)
) ENGINE=InnoDB;

ALTER TABLE bookings
    ADD CONSTRAINT fk_bookings_coupon FOREIGN KEY (coupon_id) REFERENCES coupons(id)
      ON UPDATE CASCADE ON DELETE SET NULL;

CREATE TABLE coupon_usages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    coupon_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    booking_id BIGINT UNSIGNED NOT NULL UNIQUE,
    discount_amount DECIMAL(12,2) NOT NULL,
    used_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_coupon_usages_coupon FOREIGN KEY (coupon_id) REFERENCES coupons(id)
      ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_coupon_usages_user FOREIGN KEY (user_id) REFERENCES users(id)
      ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_coupon_usages_booking FOREIGN KEY (booking_id) REFERENCES bookings(id)
      ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_coupon_usages_user (user_id, coupon_id)
) ENGINE=InnoDB;

CREATE TABLE reviews (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    movie_id BIGINT UNSIGNED NOT NULL,
    booking_id BIGINT UNSIGNED NULL,
    rating TINYINT UNSIGNED NOT NULL,
    comment TEXT NULL,
    status ENUM('pending','approved','hidden') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY uq_user_movie_review (user_id, movie_id),
    CONSTRAINT fk_reviews_user FOREIGN KEY (user_id) REFERENCES users(id)
      ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_reviews_movie FOREIGN KEY (movie_id) REFERENCES movies(id)
      ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_reviews_booking FOREIGN KEY (booking_id) REFERENCES bookings(id)
      ON UPDATE CASCADE ON DELETE SET NULL,
    INDEX idx_reviews_movie_status (movie_id, status)
) ENGINE=InnoDB;

CREATE TABLE wishlists (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    movie_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY uq_wishlist_user_movie (user_id, movie_id),
    CONSTRAINT fk_wishlists_user FOREIGN KEY (user_id) REFERENCES users(id)
      ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_wishlists_movie FOREIGN KEY (movie_id) REFERENCES movies(id)
      ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    type VARCHAR(50) NOT NULL DEFAULT 'system',
    read_at DATETIME NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id)
      ON UPDATE CASCADE ON DELETE CASCADE,
    INDEX idx_notifications_user_read (user_id, read_at)
) ENGINE=InnoDB;

-- Du lieu khoi tao co ban
INSERT IGNORE INTO roles (name, display_name, description) VALUES
('admin', 'Quan tri vien', 'Quan ly toan bo he thong'),
('staff', 'Nhan vien', 'Ho tro van hanh rap va soat ve'),
('customer', 'Khach hang', 'Dat ve va su dung dich vu');

INSERT IGNORE INTO seat_types (name, surcharge, description) VALUES
('Standard', 0, 'Ghe thuong'),
('VIP', 20000, 'Ghe VIP'),
('Couple', 50000, 'Ghe doi');

INSERT IGNORE INTO payment_methods (name, code, description) VALUES
('Tien mat tai quay', 'CASH', 'Thanh toan truc tiep tai quay'),
('VNPay', 'VNPAY', 'Thanh toan qua cong VNPay'),
('MoMo', 'MOMO', 'Thanh toan qua vi MoMo'),
('Vi noi bo', 'WALLET', 'Thanh toan bang so du vi');

INSERT IGNORE INTO cinema_info (id, name, address, phone, email)
VALUES (1, 'Cinema', 'Cap nhat dia chi rap cua ban', NULL, NULL);
