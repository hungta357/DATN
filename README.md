# Base Code PHP MVC - Hệ Thống Đặt Vé Xem Phim (`cinema_complete`)

Bộ base code được xây dựng theo mô hình **PHP MVC (PDO + MySQL 8.0)** bám sát toàn bộ **7 nhánh bảng và 5 lưu ý nghiệp vụ** trong file [`database/cinema_complete.sql`](./database/cinema_complete.sql).

## Cấu trúc 7 Nhánh Chính trong Code

| Nhánh Database | Các bảng trong MySQL | Model xử lý | Controller xử lý |
| :--- | :--- | :--- | :--- |
| **1. Roles / Users / Cinema Info** | `roles`, `users`, `user_roles`, `cinema_info` | `app/Models/UserModel.php` | `AuthController`, `AdminController` |
| **2. Movies / Genres** | `movies`, `genres`, `movie_genres` | `app/Models/MovieModel.php` | `HomeController`, `AdminController` |
| **3. Rooms / Seats / Seat Types** | `rooms`, `seat_types`, `seats` | `app/Models/RoomModel.php` | `AdminController` |
| **4. Shows / Show Seats Inventory** | `shows`, `show_seats` | `app/Models/ShowModel.php` | `BookingController`, `AdminController` |
| **5. Bookings / Details / Tickets** | `bookings`, `booking_details`, `tickets` | `app/Models/BookingModel.php` | `BookingController`, `AdminController` |
| **6. Payments / Methods / Wallets** | `payment_methods`, `payments`, `wallets`, `wallet_transactions` | `app/Models/PaymentWalletModel.php` | `BookingController`, `WalletController` |
| **7. Concessions / Coupons / Reviews / Wishlists / Notifications** | `concessions`, `booking_concessions`, `coupons`, `coupon_usages`, `reviews`, `wishlists`, `notifications` | `app/Models/EngagementModel.php` | `HomeController`, `BookingController`, `AdminController` |

---

## Các Lưu Ý Nghiệp Vụ Đã Được Cài Đặt Sẵn

1. **Tự động sinh `show_seats` khi tạo `shows`:** Phương thức `ShowModel::createShowWithSeats()` copy toàn bộ ghế từ `seats` của `room` sang `show_seats` với giá `base_price + seat_types.surcharge`.
2. **Giữ ghế an toàn chống trùng lặp:** Phương thức `ShowModel::holdSeats()` sử dụng `DB Transaction + SELECT ... FOR UPDATE`, kiểm tra `status = 'available'`, gán `hold_expires_at` (10 phút) và tự động giải phóng ghế quá hạn.
3. **Thanh toán & phát hành vé trong 1 DB Transaction:** Phương thức `BookingModel::confirmBookingAndIssueTickets()` cập nhật `bookings`, `show_seats`, trừ số dư `wallets` (nếu chọn ví nội bộ), lưu `payments`, sinh mã vé QR trong bảng `tickets` và gửi `notifications` trong cùng 1 transaction.
4. **Không xóa cứng dữ liệu giao dịch:** Tài khoản, Phim, Suất chiếu được quản lý bằng trường `status`.

---

## Hướng dẫn chạy nhanh

1. **Cấu hình kết nối MySQL:** Kiểm tra thông tin trong [`config/config.php`](./config/config.php) (`DB_USERNAME`, `DB_PASSWORD`).
2. **Khởi tạo Database & dữ liệu mẫu:**
   - Cách 1 (Tự động): Chạy lệnh `php setup.php` hoặc truy cập `http://localhost:8000/setup.php` để tự động tạo DB `cinema_complete`, nạp dữ liệu mẫu, sinh ghế và suất chiếu.
   - Cách 2 (Thủ công): Import [`database/cinema_complete.sql`](./database/cinema_complete.sql) và [`database/seed_sample_data.sql`](./database/seed_sample_data.sql) vào MySQL / phpMyAdmin.
3. **Chạy Web Server:**
   ```bash
   php -S localhost:8000
   ```
   Sau đó mở trình duyệt tại `http://localhost:8000`.

### Tài khoản mẫu (sau khi chạy `setup.php` hoặc `seed_sample_data.sql`)
- **Admin:** `admin@cinema.vn` / Mật khẩu: `password`
- **Staff (Soát vé):** `staff@cinema.vn` / Mật khẩu: `password`
- **Customer (Khách hàng - có sẵn 500.000đ trong Ví nội bộ):** `customer@cinema.vn` / Mật khẩu: `password`

