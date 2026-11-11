-- =========================================================
-- DU LIEU MAU CHO HE THONG DAT VE XEM PHIM (cinema_complete)
-- Mat khau mac dinh cho cac tai khoan mau: password
-- =========================================================

USE cinema_complete;

-- Cap nhat thong tin 1 Rap chieu le duy nhat theo phong cach Beta Cinema
UPDATE cinema_info
SET name = 'Beta Cinema Giải Phóng',
    address = 'Tầng 3, Tòa nhà Imperial Plaza, 360 Giải Phóng, Hà Nội',
    phone = '1900 636807',
    email = 'cskh@betacinema.vn',
    description = 'Rạp chiếu phim độc lập phong cách Beta Cinema trẻ trung, hiện đại với hệ thống 4 phòng chiếu tiêu chuẩn Hollywood và giá vé ưu đãi dành cho học sinh, sinh viên.',
    updated_at = NOW()
WHERE id = 1;

-- 1. Tai khoan mau (password = password -> bcrypt hash)
INSERT IGNORE INTO users (id, name, email, phone, password, status, created_at, updated_at) VALUES
(1, 'Admin Cinema', 'admin@cinema.vn', '0901000001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active', NOW(), NOW()),
(2, 'Nhan Vien Soat Ve', 'staff@cinema.vn', '0901000002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active', NOW(), NOW()),
(3, 'Nguyen Van Khach', 'customer@cinema.vn', '0901000003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active', NOW(), NOW());

INSERT IGNORE INTO user_roles (user_id, role_id, created_at) VALUES
(1, 1, NOW()),
(2, 2, NOW()),
(3, 3, NOW());

INSERT IGNORE INTO wallets (id, user_id, balance, currency, created_at, updated_at) VALUES
(1, 1, 1000000, 'VND', NOW(), NOW()),
(2, 2, 200000, 'VND', NOW(), NOW()),
(3, 3, 500000, 'VND', NOW(), NOW());

-- 2. The loai & Phim mau
INSERT IGNORE INTO genres (id, name, slug, created_at, updated_at) VALUES
(1, 'Hành động', 'hanh-dong', NOW(), NOW()),
(2, 'Khoa học viễn tưởng', 'khoa-hoc-vien-tuong', NOW(), NOW()),
(3, 'Hoạt hình', 'hoat-hinh', NOW(), NOW()),
(4, 'Kinh dị', 'kinh-di', NOW(), NOW()),
(5, 'Tình cảm', 'tinh-cam', NOW(), NOW());

INSERT IGNORE INTO movies (id, title, title_vi, slug, overview, poster_path, release_date, runtime, director, cast_text, language, country, age_rating, imdb_rating, status, created_at, updated_at) VALUES
(1, 'Dune: Part Two', 'Hành Tinh Cát: Phần Hai', 'dune-part-two', 'Hành trình trả thù của Paul Atreides cùng người Fremen chống lại những kẻ đã hủy hoại gia tộc anh.', 'https://images.unsplash.com/photo-1534447677768-be436bb09401?w=600', CURDATE(), 166, 'Denis Villeneuve', 'Timothée Chalamet, Zendaya', 'Phụ đề Tiếng Việt', 'Mỹ', 'T16', 8.8, 'now_showing', NOW(), NOW()),
(2, 'Inside Out 2', 'Những Mảnh Ghép Cảm Xúc 2', 'inside-out-2', 'Riley bước vào tuổi dậy thì với sự xuất hiện của những cảm xúc hoàn toàn mới như Lo Âu, Ganh Tị.', 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?w=600', CURDATE(), 96, 'Kelsey Mann', 'Amy Poehler, Maya Hawke', 'Lồng tiếng / Phụ đề', 'Mỹ', 'P', 7.9, 'now_showing', NOW(), NOW()),
(3, 'Avatar: Fire and Ash', 'Avatar: Lửa Và Tro Tàn', 'avatar-fire-and-ash', 'Cuộc phiêu lưu tiếp theo của Jake Sully và Neytiri tại hành tinh Pandora.', 'https://images.unsplash.com/photo-1509198397868-475647b2a1e5?w=600', DATE_ADD(CURDATE(), INTERVAL 14 DAY), 180, 'James Cameron', 'Sam Worthington, Zoe Saldana', 'Phụ đề Tiếng Việt', 'Mỹ', 'T13', 8.5, 'coming_soon', NOW(), NOW());

INSERT IGNORE INTO movie_genres (movie_id, genre_id) VALUES
(1, 1), (1, 2),
(2, 3),
(3, 1), (3, 2);

-- 3. Phong chieu
INSERT IGNORE INTO rooms (id, name, room_type, capacity, status, description, created_at, updated_at) VALUES
(1, 'Phòng Chiếu 01 - IMAX', 'imax', 24, 'active', 'Màn hình IMAX Laser âm thanh vòm 12 kênh', NOW(), NOW()),
(2, 'Phòng Chiếu 02 - VIP', 'vip', 18, 'active', 'Ghế da ngả lưng cao cấp', NOW(), NOW());

-- 4. Combo bap nuoc & Coupon
INSERT IGNORE INTO concessions (id, name, description, price, category, stock, status, sort_order, created_at, updated_at) VALUES
(1, 'Combo Solo (1 Bắp lớn + 1 Nước lớn)', 'Bắp rang bơ vị phô mai/caramel + Pepsi 32oz', 79000, 'Combo', 100, 'active', 1, NOW(), NOW()),
(2, 'Combo Couple (1 Bắp lớn + 2 Nước lớn)', 'Tiết kiệm hơn khi đi 2 người', 109000, 'Combo', 100, 'active', 2, NOW(), NOW()),
(3, 'Bắp Phô Mai Đặc Biệt', 'Bắp rang phủ bột phô mai thơm lừng', 55000, 'Popcorn', 80, 'active', 3, NOW(), NOW());

INSERT IGNORE INTO coupons (id, code, name, description, discount_type, discount_value, min_order_amount, max_discount_amount, usage_limit, used_count, starts_at, expires_at, status, created_at, updated_at) VALUES
(1, 'WELCOME20', 'Giảm 20% cho đơn từ 100K', 'Giảm tối đa 50.000đ', 'percent', 20, 100000, 50000, 100, 0, DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_ADD(NOW(), INTERVAL 60 DAY), 'active', NOW(), NOW()),
(2, 'GIAM30K', 'Giảm thẳng 30.000đ', 'Áp dụng cho đơn từ 150.000đ', 'fixed', 30000, 150000, NULL, 50, 0, DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_ADD(NOW(), INTERVAL 60 DAY), 'active', NOW(), NOW());
