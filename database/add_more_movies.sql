-- ============================================================
-- Bổ sung phim mới (id 27-40) + thể loại
-- Chạy trên database cinema_complete đã có sẵn:
--   mysql -u root -p cinema_complete < database/add_more_movies.sql
-- hoặc dán vào phpMyAdmin / MySQL Workbench.
-- Sau đó chạy lại: php setup.php  (để tạo suất chiếu cho phim now_showing)
-- ============================================================

USE cinema_complete;

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

SELECT id, title_vi, status, age_rating FROM movies WHERE id BETWEEN 27 AND 40 ORDER BY id;
