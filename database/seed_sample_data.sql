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

-- 2C. BO SUNG THEM PHIM DEMO (17-26) DE TIM KIEM / DAT VE
INSERT IGNORE INTO movies (id, title, title_vi, slug, overview, poster_path, trailer_url, release_date, runtime, director, cast_text, language, country, age_rating, imdb_rating, status, created_at, updated_at) VALUES
(17, 'Oppenheimer', 'Oppenheimer', 'oppenheimer', 'Câu chuyện về J. Robert Oppenheimer và dự án Manhattan - cuộc đua chế tạo bom nguyên tử đã thay đổi lịch sử thế giới.', 'public/images/posters/oppenheimer.svg', 'https://www.youtube.com/embed/uYPbbksJxIg', DATE_SUB(CURDATE(), INTERVAL 25 DAY), 180, 'Christopher Nolan', 'Cillian Murphy, Emily Blunt, Robert Downey Jr.', 'Phụ đề Tiếng Việt', 'Mỹ', 'T16', 8.3, 'now_showing', NOW(), NOW()),
(18, 'Joker', 'Joker: Kẻ Điên Cuồng', 'joker', 'Arthur Fleck - một nghệ sĩ hài thất bại - dần trở thành biểu tượng tội phạm Joker sau chuỗi biến cố đau thương tại Gotham.', 'public/images/posters/joker.svg', 'https://www.youtube.com/embed/zAGVQLHvwOY', DATE_SUB(CURDATE(), INTERVAL 40 DAY), 122, 'Todd Phillips', 'Joaquin Phoenix, Robert De Niro', 'Phụ đề Tiếng Việt', 'Mỹ', 'T18', 8.4, 'now_showing', NOW(), NOW()),
(19, 'Top Gun: Maverick', 'Top Gun: Maverick', 'top-gun-maverick', 'Sau hơn 30 năm phục vụ, phi công huyền thoại Pete Maverick Mitchell quay lại để huấn luyện thế hệ phi công trẻ cho nhiệm vụ bất khả thi.', 'public/images/posters/top-gun-maverick.svg', 'https://www.youtube.com/embed/giXco2jaZ_4', DATE_SUB(CURDATE(), INTERVAL 15 DAY), 130, 'Joseph Kosinski', 'Tom Cruise, Miles Teller, Jennifer Connelly', 'Phụ đề Tiếng Việt', 'Mỹ', 'T13', 8.2, 'now_showing', NOW(), NOW()),
(20, 'Frozen II', 'Nữ Hoàng Băng Giá 2', 'frozen-2', 'Elsa và Anna cùng bạn bè lên đường khám phá nguồn gốc sức mạnh của Elsa và bí mật về vương quốc phía Bắc.', 'public/images/posters/frozen-2.svg', 'https://www.youtube.com/embed/Zi4LMpSDccc', DATE_SUB(CURDATE(), INTERVAL 7 DAY), 103, 'Chris Buck, Jennifer Lee', 'Kristen Bell, Idina Menzel, Josh Gad', 'Lồng tiếng / Phụ đề', 'Mỹ', 'P', 6.8, 'now_showing', NOW(), NOW()),
(21, 'John Wick: Chapter 4', 'John Wick: Chapter 4', 'john-wick-4', 'John Wick đối đầu với High Table trên mọi châu lục trong cuộc chiến sinh tồn đầy bạo lực và mãnh liệt.', 'public/images/posters/john-wick-4.svg', 'https://www.youtube.com/embed/qEVUtrk8_B4', DATE_SUB(CURDATE(), INTERVAL 22 DAY), 169, 'Chad Stahelski', 'Keanu Reeves, Donnie Yen, Bill Skarsgård', 'Phụ đề Tiếng Việt', 'Mỹ', 'T18', 7.7, 'now_showing', NOW(), NOW()),
(22, 'Barbie', 'Barbie', 'barbie', 'Barbie rời thế giới màu hồng hoàn hảo để bước vào thế giới thực và khám phá ý nghĩa của việc trở thành chính mình.', 'public/images/posters/barbie.svg', 'https://www.youtube.com/embed/pBk4NYhWNMM', DATE_SUB(CURDATE(), INTERVAL 14 DAY), 114, 'Greta Gerwig', 'Margot Robbie, Ryan Gosling, America Ferrera', 'Phụ đề Tiếng Việt', 'Mỹ', 'T13', 6.9, 'now_showing', NOW(), NOW()),
(23, 'Mission: Impossible – Dead Reckoning', 'Nhiệm Vụ Bất Khả Thi: Nghiệp Báo Phần Một', 'mission-impossible-dead-reckoning', 'Ethan Hunt và đội IMF truy lùng một vũ khí AI nguy hiểm có thể kiểm soát toàn bộ mạng lưới thông tin thế giới.', 'public/images/posters/mission-impossible.svg', 'https://www.youtube.com/embed/avz06PDqDbM', DATE_SUB(CURDATE(), INTERVAL 9 DAY), 163, 'Christopher McQuarrie', 'Tom Cruise, Hayley Atwell, Rebecca Ferguson', 'Phụ đề Tiếng Việt', 'Mỹ', 'T13', 7.7, 'now_showing', NOW(), NOW()),
(24, 'Elemental', 'Xứ Sở Các Nguyên Tố', 'elemental', 'Trong thành phố nơi lửa, nước, đất và không khí sống chung, một cô gái lửa và chàng trai nước khám phá rằng họ có thể hợp nhất.', 'public/images/posters/elemental.svg', 'https://www.youtube.com/embed/hXzcyx9V0xw', DATE_ADD(CURDATE(), INTERVAL 10 DAY), 109, 'Peter Sohn', 'Leah Lewis, Mamoudou Athie', 'Lồng tiếng / Phụ đề', 'Mỹ', 'P', 7.0, 'coming_soon', NOW(), NOW()),
(25, 'The Marvels', 'The Marvels', 'the-marvels', 'Captain Marvel, Ms. Marvel và Monica Rambeau vô tình liên kết sức mạnh và phải hợp tác để cứu vũ trụ.', 'public/images/posters/the-marvels.svg', 'https://www.youtube.com/embed/wS_qbDztgVY', DATE_ADD(CURDATE(), INTERVAL 18 DAY), 105, 'Nia DaCosta', 'Brie Larson, Teyonah Parris, Iman Vellani', 'Phụ đề Tiếng Việt', 'Mỹ', 'T13', 5.5, 'coming_soon', NOW(), NOW()),
(26, 'Wonka', 'Wonka', 'wonka', 'Câu chuyện về Willy Wonka thời trẻ - hành trình từ chàng trai mơ mộng đến ông chủ nhà máy sô-cô-la huyền thoại.', 'public/images/posters/wonka.svg', 'https://www.youtube.com/embed/otNh9bTjXWg', DATE_ADD(CURDATE(), INTERVAL 12 DAY), 116, 'Paul King', 'Timothée Chalamet, Calah Lane, Hugh Grant', 'Phụ đề Tiếng Việt', 'Mỹ', 'P', 7.0, 'coming_soon', NOW(), NOW());

INSERT IGNORE INTO movie_genres (movie_id, genre_id) VALUES
(17,7),(17,2),
(18,7),(18,12),
(19,1),(19,6),
(20,3),(20,9),(20,6),
(21,1),(21,12),
(22,8),(22,5),
(23,1),(23,6),
(24,3),(24,9),(24,5),
(25,1),(25,2),(25,6),
(26,8),(26,9),(26,6);

-- ===== BỔ SUNG PHIM MỚI (tìm kiếm / đặt vé) =====
INSERT IGNORE INTO movies (id, title, title_vi, slug, overview, poster_path, trailer_url, release_date, runtime, director, cast_text, language, country, age_rating, imdb_rating, status, created_at, updated_at) VALUES
(27, 'Deadpool & Wolverine', 'Deadpool & Wolverine', 'deadpool-wolverine', 'Deadpool kéo Wolverine vào một cuộc phiêu lưu hỗn loạn xuyên đa vũ trụ để cứu thế giới của mình.', 'public/images/posters/deadpool-wolverine.svg', 'https://www.youtube.com/embed/73_1biulkYk', DATE_SUB(CURDATE(), INTERVAL 5 DAY), 128, 'Shawn Levy', 'Ryan Reynolds, Hugh Jackman, Emma Corrin', 'Phụ đề Tiếng Việt', 'Mỹ', 'T18', 8.0, 'now_showing', NOW(), NOW()),
(28, 'Twisters', 'Twisters: Vùng Đất Quỷ Dữ', 'twisters', 'Một nhóm săn bão trở lại vùng Trung Tây Mỹ đối mặt với những cơn lốc xoáy cực mạnh.', 'public/images/posters/twisters.svg', 'https://www.youtube.com/embed/wdok0FRwX18', DATE_SUB(CURDATE(), INTERVAL 3 DAY), 122, 'Lee Isaac Chung', 'Daisy Edgar-Jones, Glen Powell, Anthony Ramos', 'Phụ đề Tiếng Việt', 'Mỹ', 'T13', 6.8, 'now_showing', NOW(), NOW()),
(29, 'Furiosa: A Mad Max Saga', 'Furiosa: Câu Chuyện Từ Max Điên', 'furiosa', 'Câu chuyện nguồn gốc của Furiosa trước khi gặp Max, hành trình sinh tồn giữa vùng đất hoang tàn.', 'public/images/posters/furiosa.svg', 'https://www.youtube.com/embed/XHhAG-YLdk8', DATE_SUB(CURDATE(), INTERVAL 12 DAY), 148, 'George Miller', 'Anya Taylor-Joy, Chris Hemsworth', 'Phụ đề Tiếng Việt', 'Úc', 'T18', 7.6, 'now_showing', NOW(), NOW()),
(30, 'Kingdom of the Planet of the Apes', 'Hành Tinh Khỉ: Vương Quốc Mới', 'kingdom-apes', 'Nhiều thế hệ sau Caesar, một chàng vượn trẻ bắt đầu hành trình định hình tương lai của cả loài người và loài khỉ.', 'public/images/posters/kingdom-apes.svg', 'https://www.youtube.com/embed/XtFI7SNtVpY', DATE_SUB(CURDATE(), INTERVAL 15 DAY), 145, 'Wes Ball', 'Owen Teague, Freya Allan, Kevin Durand', 'Phụ đề Tiếng Việt', 'Mỹ', 'T13', 7.0, 'now_showing', NOW(), NOW()),
(31, 'Civil War', 'Nội Chiến', 'civil-war', 'Nhóm phóng viên chiến trường xuyên qua nước Mỹ đang nội chiến để ghi lại câu chuyện cuối cùng.', 'public/images/posters/civil-war.svg', 'https://www.youtube.com/embed/aDyQxtg0V2w', DATE_SUB(CURDATE(), INTERVAL 20 DAY), 109, 'Alex Garland', 'Kirsten Dunst, Wagner Moura, Cailee Spaeny', 'Phụ đề Tiếng Việt', 'Mỹ', 'T18', 7.1, 'now_showing', NOW(), NOW()),
(32, 'The Wild Robot', 'Robot Hoang Dã', 'the-wild-robot', 'Robot Roz bị đắm tàu trên đảo hoang và học cách sống cùng các loài động vật, trở thành người mẹ bất đắc dĩ.', 'public/images/posters/wild-robot.svg', 'https://www.youtube.com/embed/67BvD8p9pR8', DATE_SUB(CURDATE(), INTERVAL 2 DAY), 102, 'Chris Sanders', 'Lupita Nyongo, Pedro Pascal, Kit Connor', 'Lồng tiếng / Phụ đề', 'Mỹ', 'P', 8.3, 'now_showing', NOW(), NOW()),
(33, 'Venom: The Last Dance', 'Venom: Vũ Điệu Cuối', 'venom-last-dance', 'Eddie và Venom buộc phải chạy trốn khi bị cả hai thế giới truy đuổi, đối mặt lựa chọn cuối cùng.', 'public/images/posters/venom.svg', 'https://www.youtube.com/embed/__2bjWbetsA', DATE_SUB(CURDATE(), INTERVAL 6 DAY), 109, 'Kelly Marcel', 'Tom Hardy, Chiwetel Ejiofor, Juno Temple', 'Phụ đề Tiếng Việt', 'Mỹ', 'T16', 6.2, 'now_showing', NOW(), NOW()),
(34, 'Alien: Romulus', 'Quái Vật Không Gian: Romulus', 'alien-romulus', 'Nhóm thanh niên khám phá trạm không gian bỏ hoang và đánh thức nỗi kinh hoàng kinh điển của vũ trụ.', 'public/images/posters/alien-romulus.svg', 'https://www.youtube.com/embed/x0DUoF7DiE0', DATE_SUB(CURDATE(), INTERVAL 10 DAY), 119, 'Fede Alvarez', 'Cailee Spaeny, David Jonsson, Archie Renaux', 'Phụ đề Tiếng Việt', 'Mỹ', 'T18', 7.2, 'now_showing', NOW(), NOW()),
(35, 'Beetlejuice Beetlejuice', 'Beetlejuice Beetlejuice', 'beetlejuice-2', 'Gia đình Deetz trở lại thế giới bên kia và vô tình triệu hồi lại tinh linh tinh nghịch Beetlejuice.', 'public/images/posters/beetlejuice.svg', 'https://www.youtube.com/embed/CoZqL9N6Rx4', DATE_SUB(CURDATE(), INTERVAL 4 DAY), 105, 'Tim Burton', 'Michael Keaton, Winona Ryder, Jenna Ortega', 'Phụ đề Tiếng Việt', 'Mỹ', 'T13', 7.0, 'now_showing', NOW(), NOW()),
(36, 'Smile 2', 'Nụ Cười 2', 'smile-2', 'Ngôi sao nhạc pop Skye Riley bắt đầu trải nghiệm những hiện tượng đáng sợ khi chuẩn bị cho chuyến lưu diễn lớn.', 'public/images/posters/smile-2.svg', 'https://www.youtube.com/embed/0YRTBSnb5k0', DATE_SUB(CURDATE(), INTERVAL 7 DAY), 127, 'Parker Finn', 'Naomi Scott, Rosemarie DeWitt', 'Phụ đề Tiếng Việt', 'Mỹ', 'T18', 6.8, 'now_showing', NOW(), NOW()),
(37, 'Gladiator II', 'Đấu Sĩ II', 'gladiator-2', 'Nhiều năm sau cái chết của Maximus, Lucius buộc phải bước vào đấu trường và đối mặt với những thế lực mới của La Mã.', 'public/images/posters/gladiator-2.svg', 'https://www.youtube.com/embed/4rgYUipGJNo', DATE_ADD(CURDATE(), INTERVAL 5 DAY), 148, 'Ridley Scott', 'Paul Mescal, Denzel Washington, Pedro Pascal', 'Phụ đề Tiếng Việt', 'Mỹ', 'T16', 7.0, 'coming_soon', NOW(), NOW()),
(38, 'Mufasa: The Lion King', 'Mufasa: Vua Sư Tử', 'mufasa', 'Câu chuyện nguồn gốc về Mufasa - hành trình từ chú sư tử mồ côi đến vị vua huyền thoại của Pride Rock.', 'public/images/posters/mufasa.svg', 'https://www.youtube.com/embed/o17xYaFbkwA', DATE_ADD(CURDATE(), INTERVAL 25 DAY), 120, 'Barry Jenkins', 'Aaron Pierre, Kelvin Harrison Jr., Tiffany Boone', 'Lồng tiếng / Phụ đề', 'Mỹ', 'P', 7.1, 'coming_soon', NOW(), NOW()),
(39, 'Sonic the Hedgehog 3', 'Nhím Sonic 3', 'sonic-3', 'Sonic, Knuckles và Tails đối đầu với Shadow - một nhân vật mới mạnh mẽ với sức mạnh đáng gờm.', 'public/images/posters/sonic-3.svg', 'https://www.youtube.com/embed/qSu6i2iFMO0', DATE_ADD(CURDATE(), INTERVAL 30 DAY), 110, 'Jeff Fowler', 'Jim Carrey, Ben Schwartz, Keanu Reeves', 'Lồng tiếng / Phụ đề', 'Mỹ', 'P', 7.0, 'coming_soon', NOW(), NOW()),
(40, 'Paddington in Peru', 'Paddington ở Peru', 'paddington-peru', 'Gia đình Brown đưa Paddington trở về Peru để tìm người dì yêu quý và bắt đầu chuyến phiêu lưu bất ngờ.', 'public/images/posters/paddington.svg', 'https://www.youtube.com/embed/hngNP9uxo3g', DATE_ADD(CURDATE(), INTERVAL 14 DAY), 106, 'Dougal Wilson', 'Ben Whishaw, Hugh Bonneville, Emily Mortimer', 'Lồng tiếng / Phụ đề', 'Anh', 'P', 6.9, 'coming_soon', NOW(), NOW());

INSERT IGNORE INTO movie_genres (movie_id, genre_id) VALUES
(27,1),(27,8),
(28,1),(28,6),
(29,1),(29,6),
(30,1),(30,2),(30,6),
(31,1),(31,7),
(32,3),(32,9),(32,6),
(33,1),(33,2),
(34,4),(34,2),
(35,8),(35,4),
(36,4),(36,12),
(37,1),(37,6),
(38,3),(38,9),(38,6),
(39,3),(39,1),(39,6),
(40,3),(40,9),(40,8);

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
