-- =========================================================
-- DU LIEU MAU CHO HE THONG DAT VE XEM PHIM (cinema_complete)
-- Mat khau mac dinh cho cac tai khoan mau: password
-- =========================================================

USE cinema_complete;

-- Cap nhat thong tin 1 Rap chieu le duy nhat: CineNow
UPDATE cinema_info
SET name = 'CineNow',
    address = 'Tầng 3, Tòa nhà Imperial Plaza, 360 Giải Phóng, Hà Nội',
    phone = '1900 636807',
    email = 'cskh@cinenow.vn',
    description = 'CineNow - Rạp chiếu phim độc lập trẻ trung, hiện đại với hệ thống phòng chiếu tiêu chuẩn Hollywood và giá vé ưu đãi dành cho học sinh, sinh viên.',
    updated_at = NOW()
WHERE id = 1;

-- 1. Tai khoan mau (Admin mat khau rieng: admin123 | Staff & Customer mat khau: password)
INSERT IGNORE INTO users (id, name, email, phone, password, status, created_at, updated_at) VALUES
(1, 'Admin Cinema', 'admin@cinema.vn', '0901000001', '$2y$10$NjCgQCd/6Lakl6YWwjZ6RuawnsR2/HnmuNUKR1gc1j4gh0QYYiGbG', 'active', NOW(), NOW()),
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

INSERT IGNORE INTO movies (id, title, title_vi, slug, overview, poster_path, trailer_url, release_date, runtime, director, cast_text, language, country, age_rating, imdb_rating, status, created_at, updated_at) VALUES
(1, 'Dune: Part Two', 'Hành Tinh Cát: Phần Hai', 'dune-part-two', 'Hành trình trả thù của Paul Atreides cùng người Fremen chống lại những kẻ đã hủy hoại gia tộc anh.', 'public/images/posters/dune-part-two.svg', 'https://www.youtube.com/embed/Way9Dexny3w', CURDATE(), 166, 'Denis Villeneuve', 'Timothée Chalamet, Zendaya', 'Phụ đề Tiếng Việt', 'Mỹ', 'T16', 8.8, 'now_showing', NOW(), NOW()),
(2, 'Inside Out 2', 'Những Mảnh Ghép Cảm Xúc 2', 'inside-out-2', 'Riley bước vào tuổi dậy thì với sự xuất hiện của những cảm xúc hoàn toàn mới như Lo Âu, Ganh Tị.', 'public/images/posters/inside-out-2.svg', 'https://www.youtube.com/embed/LEjhY15eCx0', CURDATE(), 96, 'Kelsey Mann', 'Amy Poehler, Maya Hawke', 'Lồng tiếng / Phụ đề', 'Mỹ', 'P', 7.9, 'now_showing', NOW(), NOW()),
(3, 'Avatar: Fire and Ash', 'Avatar: Lửa Và Tro Tàn', 'avatar-fire-and-ash', 'Cuộc phiêu lưu tiếp theo của Jake Sully và Neytiri tại hành tinh Pandora.', 'public/images/posters/avatar-fire-ash.svg', 'https://www.youtube.com/embed/d9MyW72ELq0', DATE_ADD(CURDATE(), INTERVAL 14 DAY), 180, 'James Cameron', 'Sam Worthington, Zoe Saldana', 'Phụ đề Tiếng Việt', 'Mỹ', 'T13', 8.5, 'coming_soon', NOW(), NOW());

INSERT IGNORE INTO movie_genres (movie_id, genre_id) VALUES
(1, 1), (1, 2),
(2, 3),
(3, 1), (3, 2);

-- Cap nhat poster noi bo cho 3 phim mau cu neu database da duoc tao tu phien ban truoc.
UPDATE movies SET poster_path = 'public/images/posters/dune-part-two.svg' WHERE id = 1 AND slug = 'dune-part-two';
UPDATE movies SET poster_path = 'public/images/posters/inside-out-2.svg' WHERE id = 2 AND slug = 'inside-out-2';
UPDATE movies SET poster_path = 'public/images/posters/avatar-fire-ash.svg' WHERE id = 3 AND slug = 'avatar-fire-and-ash';



-- 2B. BO SUNG DANH MUC VA KHO PHIM MAU CHO WEBSITE DEMO
INSERT IGNORE INTO genres (id, name, slug, created_at, updated_at) VALUES
(6, 'Phiêu lưu', 'phieu-luu', NOW(), NOW()),
(7, 'Tâm lý', 'tam-ly', NOW(), NOW()),
(8, 'Hài', 'hai', NOW(), NOW()),
(9, 'Gia đình', 'gia-dinh', NOW(), NOW()),
(10, 'Viễn tưởng', 'vien-tuong', NOW(), NOW()),
(11, 'Hoạt hình 3D', 'hoat-hinh-3d', NOW(), NOW()),
(12, 'Giật gân', 'giat-gan', NOW(), NOW());

INSERT IGNORE INTO movies (id, title, title_vi, slug, overview, poster_path, trailer_url, release_date, runtime, director, cast_text, language, country, age_rating, imdb_rating, status, created_at, updated_at) VALUES
(4, 'Spider-Man: No Way Home', 'Người Nhện: Không Còn Nhà', 'spider-man-no-way-home', 'Danh tính Người Nhện bị tiết lộ, Peter Parker tìm đến Doctor Strange để sửa chữa mọi chuyện nhưng phép thuật mở ra những rạn nứt đa vũ trụ.', 'public/images/posters/spider-man.svg', 'https://www.youtube.com/embed/JfVOs4VSpmA', DATE_SUB(CURDATE(), INTERVAL 30 DAY), 148, 'Jon Watts', 'Tom Holland, Zendaya, Benedict Cumberbatch', 'Phụ đề Tiếng Việt', 'Mỹ', 'T13', 8.2, 'now_showing', NOW(), NOW()),
(5, 'Interstellar', 'Hố Đen Tử Thần', 'interstellar', 'Một nhóm phi hành gia vượt qua hố sâu vũ trụ để tìm kiếm mái nhà mới cho nhân loại, trong hành trình nơi tình thân vượt qua không gian và thời gian.', 'public/images/posters/interstellar.svg', 'https://www.youtube.com/embed/zSWdZVtXT7E', DATE_SUB(CURDATE(), INTERVAL 60 DAY), 169, 'Christopher Nolan', 'Matthew McConaughey, Anne Hathaway, Jessica Chastain', 'Phụ đề Tiếng Việt', 'Mỹ', 'T13', 8.7, 'now_showing', NOW(), NOW()),
(6, 'Kung Fu Panda 4', 'Kung Fu Panda 4', 'kung-fu-panda-4', 'Po bước vào thử thách mới khi phải tìm người kế nhiệm Thần Long Đại Hiệp và đối đầu phù thủy biến hình nguy hiểm.', 'public/images/posters/kung-fu-panda.svg', 'https://www.youtube.com/embed/_inKs4eeHiI', DATE_SUB(CURDATE(), INTERVAL 20 DAY), 94, 'Mike Mitchell', 'Jack Black, Awkwafina, Viola Davis', 'Lồng tiếng / Phụ đề', 'Mỹ', 'P', 6.3, 'now_showing', NOW(), NOW()),
(7, 'Godzilla x Kong: The New Empire', 'Godzilla x Kong: Đế Chế Mới', 'godzilla-x-kong', 'Godzilla và Kong buộc phải hợp sức trước mối đe dọa khổng lồ ẩn sâu trong lòng Trái Đất.', 'public/images/posters/godzilla-kong.svg', 'https://www.youtube.com/embed/lV1OOlGwExM', DATE_SUB(CURDATE(), INTERVAL 18 DAY), 115, 'Adam Wingard', 'Rebecca Hall, Brian Tyree Henry, Dan Stevens', 'Phụ đề Tiếng Việt', 'Mỹ', 'T13', 6.1, 'now_showing', NOW(), NOW()),
(8, 'A Quiet Place: Day One', 'Vùng Đất Câm Lặng: Ngày Đầu Tiên', 'a-quiet-place-day-one', 'Trong ngày đầu tiên thế giới sụp đổ, những người sống sót phải giữ im lặng tuyệt đối để tránh các sinh vật săn mồi bằng âm thanh.', 'public/images/posters/quiet-place.svg', 'https://www.youtube.com/embed/YPY7J-flzE8', DATE_SUB(CURDATE(), INTERVAL 12 DAY), 100, 'Michael Sarnoski', 'Lupita Nyong’o, Joseph Quinn', 'Phụ đề Tiếng Việt', 'Mỹ', 'T16', 6.3, 'now_showing', NOW(), NOW()),
(9, 'Despicable Me 4', 'Kẻ Trộm Mặt Trăng 4', 'despicable-me-4', 'Gru cùng gia đình chào đón thành viên mới, nhưng cuộc sống yên bình nhanh chóng bị đảo lộn bởi một kẻ thù cũ.', 'public/images/posters/despicable-me.svg', 'https://www.youtube.com/embed/qQlr9-rF32A', DATE_SUB(CURDATE(), INTERVAL 10 DAY), 94, 'Chris Renaud', 'Steve Carell, Kristen Wiig, Pierre Coffin', 'Lồng tiếng / Phụ đề', 'Mỹ', 'P', 6.2, 'now_showing', NOW(), NOW()),
(10, 'The Batman', 'Batman: Vạch Trần Sự Thật', 'the-batman', 'Batman lần theo dấu vết của một kẻ sát nhân bí ẩn, từng bước khám phá những bí mật đen tối của thành phố Gotham.', 'public/images/posters/the-batman.svg', 'https://www.youtube.com/embed/mqqft2x_Aa4', DATE_SUB(CURDATE(), INTERVAL 45 DAY), 176, 'Matt Reeves', 'Robert Pattinson, Zoë Kravitz, Paul Dano', 'Phụ đề Tiếng Việt', 'Mỹ', 'T16', 7.8, 'now_showing', NOW(), NOW()),
(11, 'Wicked', 'Wicked: Phù Thủy Xứ Oz', 'wicked', 'Câu chuyện về tình bạn khác thường giữa Elphaba và Glinda trước khi họ trở thành hai phù thủy nổi tiếng của xứ Oz.', 'public/images/posters/wicked.svg', 'https://www.youtube.com/embed/6COmYeLsz4c', DATE_SUB(CURDATE(), INTERVAL 8 DAY), 160, 'Jon M. Chu', 'Cynthia Erivo, Ariana Grande, Jonathan Bailey', 'Phụ đề Tiếng Việt', 'Mỹ', 'P', 7.6, 'now_showing', NOW(), NOW()),
(12, 'Moana 2', 'Hành Trình Của Moana 2', 'moana-2', 'Moana nhận được lời gọi từ tổ tiên và bắt đầu chuyến hải trình mới đến những vùng biển xa xôi đầy bí ẩn.', 'public/images/posters/moana-2.svg', 'https://www.youtube.com/embed/hDZ7y8RP5HE', DATE_SUB(CURDATE(), INTERVAL 5 DAY), 100, 'David Derrick Jr.', 'Auliʻi Cravalho, Dwayne Johnson', 'Lồng tiếng / Phụ đề', 'Mỹ', 'P', 6.8, 'now_showing', NOW(), NOW()),
(13, 'How to Train Your Dragon', 'Bí Kíp Luyện Rồng', 'how-to-train-your-dragon', 'Một chàng trai trẻ kết bạn với chú rồng hiếm gặp và thay đổi cách nhìn của cả bộ tộc về loài rồng.', 'public/images/posters/how-to-train-your-dragon.svg', 'https://www.youtube.com/embed/22w7z_lT6YM', DATE_ADD(CURDATE(), INTERVAL 14 DAY), 125, 'Dean DeBlois', 'Mason Thames, Nico Parker, Gerard Butler', 'Phụ đề Tiếng Việt', 'Mỹ', 'P', 7.8, 'coming_soon', NOW(), NOW()),
(14, 'Zootopia 2', 'Phi Vụ Động Trời 2', 'zootopia-2', 'Judy Hopps và Nick Wilde trở lại với một vụ án mới làm đảo lộn thành phố muôn loài.', 'public/images/posters/zootopia-2.svg', 'https://www.youtube.com/embed/BjkIOU5ob6s', DATE_ADD(CURDATE(), INTERVAL 21 DAY), 108, 'Jared Bush', 'Ginnifer Goodwin, Jason Bateman', 'Lồng tiếng / Phụ đề', 'Mỹ', 'P', 7.8, 'coming_soon', NOW(), NOW()),
(15, 'The Super Mario Bros. Movie', 'Anh Em Super Mario', 'super-mario-bros', 'Hai anh em thợ sửa ống nước lạc vào vương quốc kỳ diệu và cùng nhau chống lại thế lực của Bowser.', 'public/images/posters/super-mario.svg', 'https://www.youtube.com/embed/TnGl01FkMMo', DATE_ADD(CURDATE(), INTERVAL 28 DAY), 92, 'Aaron Horvath, Michael Jelenic', 'Chris Pratt, Anya Taylor-Joy, Charlie Day', 'Lồng tiếng / Phụ đề', 'Mỹ', 'P', 7.0, 'coming_soon', NOW(), NOW()),
(16, 'Fantastic Beasts', 'Sinh Vật Huyền Bí', 'fantastic-beasts', 'Nhà nghiên cứu Newt Scamander bước vào thế giới phép thuật với những sinh vật kỳ thú và bí mật chưa được khám phá.', 'public/images/posters/fantastic-beasts.svg', 'https://www.youtube.com/embed/ViuDsy7FU3w', DATE_ADD(CURDATE(), INTERVAL 35 DAY), 133, 'David Yates', 'Eddie Redmayne, Katherine Waterston', 'Phụ đề Tiếng Việt', 'Mỹ', 'T13', 7.2, 'coming_soon', NOW(), NOW());

INSERT IGNORE INTO movie_genres (movie_id, genre_id) VALUES
(4,1),(4,2),(5,2),(5,6),(5,7),(6,3),(6,8),(6,9),(7,1),(7,2),(7,6),
(8,4),(8,12),(9,3),(9,8),(9,9),(10,1),(10,7),(10,12),(11,5),(11,6),
(12,3),(12,6),(12,9),(13,1),(13,2),(13,6),(14,3),(14,8),(14,9),
(15,3),(15,8),(15,9),(16,6),(16,10);

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
