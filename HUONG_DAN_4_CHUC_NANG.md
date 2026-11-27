# CineNow - 4 chức năng được giao

## Các file chính
- Chi tiết phim: `app/Controllers/HomeController.php` (`movieDetail`), `app/Models/MovieModel.php`, `app/Views/home/movie_detail.php`.
- Lịch chiếu: `app/Controllers/HomeController.php` (`schedule`), `app/Models/ShowModel.php`, `app/Views/home/schedule.php`.
- Chọn ghế: `app/Controllers/BookingController.php` (`selectSeats`), `app/Models/ShowModel.php` (`getShowSeats`, `holdSeats`), `app/Views/booking/select_seats.php`.
- Đặt vé: `app/Controllers/BookingController.php` (`create`, `checkout`, `pay`), `app/Models/BookingModel.php`, `app/Views/booking/checkout.php`.

## Cài đặt
1. Giải nén dự án vào `C:\laragon\www\DATN` (hoặc thư mục web server bạn đang dùng).
2. Giữ file cấu hình `.env` của dự án; đối chiếu với `.env.example` để đặt host, port, database, user và password MySQL đúng máy.
3. Database nhóm đang dùng là `cinema_complete`. Không import lại file SQL tổng vào database này.
4. Chỉ import file `CINENOW_DATA_BO_SUNG_LICH_CHIEU.sql` được gửi riêng; file này chỉ thêm dữ liệu vào `shows` và `show_seats`, có kiểm tra chống thêm trùng khi chạy lại.
5. Bật Apache/MySQL trong Laragon và mở `http://localhost/DATN/` hoặc URL dự án đang cấu hình.

## Luồng kiểm tra
1. Trang chủ -> mở một phim đang chiếu -> kiểm tra thông tin và thể loại.
2. Mở Lịch chiếu -> chọn ngày -> chọn suất chiếu.
3. Chọn một hoặc nhiều ghế -> thay đổi combo -> thử coupon `WELCOME20` hoặc `GIAM30K` (đơn hàng phải đạt điều kiện).
4. Tạo đơn -> kiểm tra đơn pending -> thanh toán bằng phương thức có sẵn -> kiểm tra vé QR trong trang checkout.

## Ghi chú
- Mã nguồn hiện có xử lý giữ ghế 10 phút, kiểm tra tồn kho combo, tính lại coupon ở backend, tạo booking, ghi nhận payment và phát hành ticket. Frontend không được xem là nguồn quyết định giá cuối cùng; backend luôn tính lại.
- Các phương thức thanh toán trong bản hiện tại là luồng mô phỏng/ghi nhận trong database; chưa cấu hình tích hợp cổng thanh toán thật thì không xem là giao dịch tiền thật.
- Không sửa/xóa bảng hoặc dữ liệu nhóm. Hãy sao lưu database trước khi chạy bất kỳ SQL nào.
