# Base Code PHP MVC - Hệ Thống Đặt Vé Xem Phim (`cinema_complete`)

Bộ base code được xây dựng theo mô hình **PHP MVC (PDO + MySQL 8.0)** bám sát toàn bộ **7 nhánh bảng và 5 lưu ý nghiệp vụ** trong file [`database/cinema_complete.sql`](./database/cinema_complete.sql).

## Cấu trúc Phân chia `backend/` (PHP) và `frontend/` (React)

- **`backend/` (PHP Backend & REST API):**
  - `backend/Core/`: `Database.php`, `BaseModel.php`, `BaseController.php`
  - `backend/Models/`: `UserModel.php`, `MovieModel.php`, `RoomModel.php`, `ShowModel.php`, `BookingModel.php`, `PaymentWalletModel.php`, `EngagementModel.php`
  - `backend/Controllers/`: `ApiController.php`, `AdminController.php`, `AuthController.php`, `HomeController.php`, `BookingController.php`, `WalletController.php`
- **`frontend/` (React 18 Components & Giao diện):**
  - `frontend/react/`: `AdminAnalyticsApp.js` (Thống kê chi tiết Admin), `CustomerMovieApp.js` (Khám phá phim & đặt vé nhanh), `SeatCouponApp.js` (Chọn ghế & kiểm tra mã giảm giá realtime)
  - `frontend/views/`: Giao diện khách hàng (`layouts/main.php`, `home/`, `booking/`, `auth/`, `wallet/`) và giao diện Quản trị riêng biệt (`layouts/admin.php`, `admin/dashboard.php`)

## Cấu trúc 7 Nhánh Chính trong Code

| Nhánh Database | Các bảng trong MySQL | Model xử lý | Controller xử lý |
| :--- | :--- | :--- | :--- |
| **1. Roles / Users / Cinema Info** | `roles`, `users`, `user_roles`, `cinema_info` | `backend/Models/UserModel.php` | `AuthController`, `AdminController` |
| **2. Movies / Genres** | `movies`, `genres`, `movie_genres` | `backend/Models/MovieModel.php` | `HomeController`, `AdminController`, `ApiController` |
| **3. Rooms / Seats / Seat Types** | `rooms`, `seat_types`, `seats` | `backend/Models/RoomModel.php` | `AdminController` |
| **4. Shows / Show Seats Inventory** | `shows`, `show_seats` | `backend/Models/ShowModel.php` | `BookingController`, `AdminController` |
| **5. Bookings / Details / Tickets** | `bookings`, `booking_details`, `tickets` | `backend/Models/BookingModel.php` | `BookingController`, `AdminController`, `ApiController` |
| **6. Payments / Methods / Wallets** | `payment_methods`, `payments`, `wallets`, `wallet_transactions` | `backend/Models/PaymentWalletModel.php` | `BookingController`, `WalletController` |
| **7. Concessions / Coupons / Reviews / Wishlists / Notifications** | `concessions`, `booking_concessions`, `coupons`, `coupon_usages`, `reviews`, `wishlists`, `notifications` | `backend/Models/EngagementModel.php` | `HomeController`, `BookingController`, `AdminController`, `ApiController` |

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

### Tài khoản mặc định
- **Admin (Quản trị viên):** `admin@cinema.vn` (hoặc `admin`) / Mật khẩu riêng: `admin123` (khi đăng nhập tại `?route=auth/login` sẽ tự động chuyển vào trang Quản trị riêng `?route=admin`)
- **Staff (Nhân viên soát vé):** `staff@cinema.vn` / Mật khẩu: `password`
- **Customer (Khách hàng):** `customer@cinema.vn` / Mật khẩu: `password`

