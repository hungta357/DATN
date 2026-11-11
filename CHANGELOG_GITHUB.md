# Các thay đổi trong bản bàn giao GitHub

## Giao diện và trải nghiệm
- Giữ trang chủ đã được làm mới theo phong cách điện ảnh hiện đại.
- Giữ giao diện responsive và các thành phần thẻ phim, banner, nút mua vé đã chỉnh.

## Bảo mật và phân quyền
- Thêm CSRF token cho các form POST trong Views và kiểm tra token tại front controller.
- Bật cookie session HttpOnly, SameSite=Lax, Secure khi chạy HTTPS và strict session mode.
- Đổi session ID sau khi đăng nhập/đăng xuất.
- Giới hạn Staff ở chức năng xem lịch chiếu và xử lý đơn vé/soát vé.
- Chỉ Admin được quản lý phim, suất chiếu, bắp nước, tài khoản, phòng và mã giảm giá.
- Giới hạn `setup.php` chỉ chạy từ Terminal, tránh khởi tạo dữ liệu qua HTTP.

## Cấu hình và GitHub
- Thêm `.env.example` và hỗ trợ đọc `.env` không cần cài thư viện ngoài.
- Thêm `.gitignore` để loại trừ `.env` và file tạm.
- Đồng bộ `database/cinema_complete.sql` với file SQL người dùng cung cấp.
- Viết lại README hướng dẫn cài đặt, tài khoản mẫu và lệnh Git.

## Cần kiểm thử thêm
- Thanh toán VNPay/MoMo thật chưa được cấu hình/kiểm chứng.
- Cần kiểm thử thực tế luồng giữ ghế, hết hạn giữ ghế, thanh toán, phát hành QR và soát vé trên máy chạy MySQL của nhóm.

## Bổ sung kho phim và lịch chiếu demo
- Mở rộng dữ liệu mẫu lên 16 bộ phim, 12 thể loại và liên kết phim-thể loại.
- Thêm 16 poster SVG tự lưu trong `public/images/posters`, không phụ thuộc máy chủ ảnh bên ngoài.
- Cập nhật `setup.php` để tự bổ sung lịch chiếu cho 7 ngày tiếp theo ở tối đa 2 phòng, 5 khung giờ mỗi ngày/phòng; tự tạo bản ghi ghế cho từng suất.
- Cơ chế bổ sung lịch chiếu kiểm tra phòng và giờ bắt đầu để tránh tạo trùng khi chạy setup nhiều lần.

## Sửa lỗi đăng ký và đăng nhập (bản cập nhật)
- Chuẩn hóa email khi đăng ký và đăng nhập để tránh khác biệt chữ hoa/chữ thường.
- Chỉ khởi tạo tài khoản Admin mặc định khi đăng nhập bằng Admin, không chạy thao tác này cho mọi tài khoản Customer.
- Bảo đảm role `customer` tồn tại và bắt buộc gán role cho tài khoản mới.
- Mã hóa mật khẩu bằng `PASSWORD_DEFAULT`, xác minh hash đã lưu trước khi báo đăng ký thành công.
- Kiểm tra lại tài khoản vừa tạo sau khi lưu; ghi lỗi kỹ thuật vào error log thay vì hiển thị lỗi SQL cho người dùng.

## Giao diện CineNow Premium
- Đồng bộ giao diện khách hàng theo phong cách rạp chiếu phim cao cấp, tông than đen và vàng champagne.
- Nâng cấp navbar, thanh thông tin, footer, nút hành động, form, bảng, thẻ nội dung và hiệu ứng hover.
- Làm mới màu sắc trang chủ, hero banner, bộ lọc và thẻ phim; tinh chỉnh tiêu đề trang đăng nhập/đăng ký.
- Không thay đổi controller, route, database, quy trình đặt vé hoặc phân quyền.
- Đồng bộ thêm giao diện quản trị Admin/Staff theo cùng hệ màu cao cấp, tinh chỉnh sidebar, thanh điều hướng, bảng dữ liệu và biểu mẫu.
