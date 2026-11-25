<div class="container py-5">
    <div class="row g-4 align-items-stretch">
        <div class="col-lg-5">
            <div class="h-100 rounded-4 p-4 p-lg-5 text-white" style="background:linear-gradient(145deg,#14130f,#40331e)">
                <span class="badge rounded-pill mb-3" style="background:#b68b43">CHĂM SÓC KHÁCH HÀNG</span>
                <h1 class="fw-bold mb-3">Chúng tôi luôn lắng nghe</h1>
                <p class="text-white-50">Gửi câu hỏi, góp ý hoặc phản ánh để <?= htmlspecialchars($cinemaInfo['name']) ?> phục vụ bạn tốt hơn.</p>
                <hr class="border-secondary my-4">
                <p><i class="bi bi-geo-alt me-2"></i><?= htmlspecialchars($cinemaInfo['address']) ?></p>
                <p><i class="bi bi-telephone me-2"></i><?= htmlspecialchars($cinemaInfo['phone'] ?: 'Chưa cập nhật') ?></p>
                <p><i class="bi bi-envelope me-2"></i><?= htmlspecialchars($cinemaInfo['email'] ?: 'Chưa cập nhật') ?></p>
                <p><i class="bi bi-clock me-2"></i><?= htmlspecialchars($cinemaInfo['opening_hours'] ?? 'Theo lịch hoạt động của rạp') ?></p>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body p-4 p-lg-5">
                <h2 class="h3 fw-bold mb-1">Gửi liên hệ / góp ý</h2><p class="text-secondary mb-4">Thông tin có dấu * là bắt buộc.</p>
                <form method="POST" action="?route=contact/submit">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['_token'] ?? '') ?>">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Họ và tên *</label><input class="form-control" name="full_name" required maxlength="150" value="<?= htmlspecialchars($_SESSION['user']['name'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label">Email *</label><input class="form-control" type="email" name="email" required maxlength="150" value="<?= htmlspecialchars($_SESSION['user']['email'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label">Số điện thoại</label><input class="form-control" name="phone" maxlength="20"></div>
                        <div class="col-md-6"><label class="form-label">Chủ đề *</label><select class="form-select" name="subject" required><option value="">-- Chọn chủ đề --</option><option>Hỗ trợ đặt vé</option><option>Thanh toán và hoàn tiền</option><option>Chất lượng dịch vụ</option><option>Góp ý / đề xuất</option><option>Khác</option></select></div>
                        <div class="col-12"><label class="form-label">Nội dung *</label><textarea class="form-control" name="message" rows="5" minlength="10" maxlength="5000" required placeholder="Bạn cần chúng tôi hỗ trợ điều gì?"></textarea></div>
                        <div class="col-12"><button class="btn btn-dark px-4 py-2" type="submit">Gửi góp ý <i class="bi bi-send ms-1"></i></button></div>
                    </div>
                </form>
            </div></div>
        </div>
    </div>
</div>
