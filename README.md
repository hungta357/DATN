# CineNow — Website đặt vé xem phim tại rạp

Đồ án website đặt vé xem phim, xây dựng bằng **PHP 8+ theo mô hình MVC tự triển khai, PDO và MySQL 8.0**. Dự án hiện không sử dụng Laravel.

## 1. Công nghệ

- PHP 8.1 trở lên (khuyến nghị PHP 8.2/8.3)
- MySQL 8.0 hoặc tương thích
- PDO MySQL extension
- Bootstrap 5.3, Bootstrap Icons
- JavaScript thuần

## 2. Cài đặt trên máy cá nhân

1. Cài Laragon/XAMPP và bật Apache (nếu dùng) cùng MySQL.
2. Giải nén dự án, mở thư mục `DATN` bằng VS Code.
3. Sao chép `.env.example` thành `.env`.
4. Mở `.env`, chỉnh `DB_USERNAME` và `DB_PASSWORD` theo MySQL trên máy của bạn.
5. Mở Terminal tại thư mục `DATN` và chạy:

   ```bash
   php setup.php
   ```

   Lệnh này tạo database `cinema_complete`, tạo bảng, nạp dữ liệu phim/thể loại mẫu, poster nội bộ và tạo lịch chiếu demo cho 7 ngày tiếp theo. Nếu database đã tồn tại, lệnh sẽ bổ sung dữ liệu còn thiếu; không xóa đơn đặt vé hiện có. Chỉ chạy trên môi trường phát triển; file `setup.php` đã được giới hạn chạy từ Terminal, không chạy qua trình duyệt.

6. Khởi động web server:

   ```bash
   php -S localhost:8000
   ```

7. Mở trình duyệt tại `http://localhost:8000`.

Có thể chạy `run-server.bat` trên Windows để tự khởi tạo database và mở server. Nếu PHP không nằm ở đường dẫn Laragon được khai báo sẵn trong file BAT, hãy bảo đảm lệnh `php` đã có trong PATH.

## 3. Tài khoản mẫu

Sau khi chạy `php setup.php`:

| Vai trò | Email | Mật khẩu |
|---|---|---|
| Admin | `admin@cinema.vn` | `admin123` |
| Staff | `staff@cinema.vn` | `password` |
| Customer | `customer@cinema.vn` | `password` |

**Lưu ý:** Đây là tài khoản phục vụ chạy thử đồ án. Hãy đổi mật khẩu trước khi triển khai thật và không dùng các mật khẩu mẫu trên môi trường công khai.

## 4. Phân quyền

- **Admin:** Quản lý toàn bộ hệ thống.
- **Staff:** Xem lịch chiếu, xem danh sách đơn vé và thực hiện soát/xác nhận vé tại rạp. Staff không được truy cập các khu vực quản lý tài khoản, phim, phòng, suất chiếu, bắp nước hoặc mã giảm giá.
- **Customer:** Đăng ký, đăng nhập, tìm phim, xem lịch chiếu, đặt vé và quản lý vé cá nhân.

## 5. Các chức năng đang có trong source

- Trang chủ, danh sách phim, tìm kiếm phim, lọc theo thể loại, chi tiết phim.
- Bộ dữ liệu demo gồm 16 phim, nhiều thể loại và poster SVG được lưu ngay trong source để không phụ thuộc dịch vụ ảnh bên ngoài.
- Script setup tự bổ sung lịch chiếu cho 7 ngày tiếp theo (tối đa 2 phòng × 5 khung giờ/ngày) và sinh sơ đồ ghế cho từng suất; chạy lại không tạo trùng suất cùng phòng/giờ.
- Lịch chiếu, bảng giá, thông tin rạp và ưu đãi.
- Đăng ký, đăng nhập, phân quyền theo vai trò.
- Chọn ghế, tạo đơn đặt vé, thanh toán bằng phương thức đã cấu hình.
- Ví nội bộ, lịch sử đặt vé, vé điện tử và mã QR.
- Danh sách yêu thích, đánh giá phim, thông báo.
- Khu vực quản trị phim, phòng/ghế, suất chiếu, đơn vé, soát vé và bắp nước/mã giảm giá.

## 6. Ghi chú nghiệp vụ và giới hạn hiện tại

- Khi tạo suất chiếu, hệ thống sinh danh sách ghế cho suất chiếu.
- Quy trình giữ ghế sử dụng transaction và khóa bản ghi để hạn chế hai người đặt cùng ghế.
- Thời gian giữ ghế mặc định là 10 phút.
- Việc xác nhận thanh toán và phát hành vé được xử lý trong transaction.
- Các phương thức VNPay/MoMo trong database mới là cấu hình phương thức; **chưa được xem là tích hợp cổng thanh toán thật** nếu chưa có thông tin merchant, chữ ký và callback xác thực từ nhà cung cấp.
- Mã QR cần được kiểm thử cùng thiết bị/quy trình quét thực tế trước khi nghiệm thu.
- Nên kiểm thử lại toàn bộ luồng đặt vé, hết hạn giữ ghế, hoàn/hủy và soát vé trên database riêng trước khi demo.

## 7. Đưa source code lên GitHub

Mở Terminal tại thư mục `DATN`:

```bash
git init
git add .
git status
git commit -m "Initial commit - CineNow cinema booking"
git branch -M main
git remote add origin https://github.com/TEN_TAI_KHOAN/TEN_REPOSITORY.git
git push -u origin main
```

Thay URL mẫu bằng link repository GitHub của nhóm.

Nếu repository đã có remote `origin`, không chạy lại lệnh `git remote add origin`. Kiểm tra bằng:

```bash
git remote -v
```

Sau khi nhóm cập nhật code, lấy code mới:

```bash
git pull origin main
```

Đẩy thay đổi của bạn:

```bash
git add .
git commit -m "Improve cinema booking website"
git push origin main
```

Nếu làm trên nhánh riêng, thay `main` bằng tên nhánh của bạn.

## 8. Quy tắc an toàn khi đẩy GitHub

- Không đưa file `.env`, mật khẩu database, API key hoặc thông tin thanh toán thật lên GitHub.
- Không xóa file `.gitignore`.
- Chỉ đưa `.env.example` lên GitHub; mỗi thành viên tự tạo `.env` trên máy mình.
- Không commit dữ liệu khách hàng thật hoặc bản sao database có thông tin cá nhân.
- Trước khi commit, luôn kiểm tra `git status` và rà soát các file được staged.

## 9. Cấu trúc thư mục

```text
DATN/
├── app/
│   ├── Controllers/
│   ├── Core/
│   ├── Models/
│   └── Views/
├── config/
├── database/
│   ├── cinema_complete.sql
│   └── seed_sample_data.sql
├── public/
├── .env.example
├── .gitignore
├── index.php
├── setup.php
└── README.md
```
