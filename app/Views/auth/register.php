<div class="container py-5 auth-page">
    <div class="text-center mb-4"><div class="cine-kicker">CINENOW MEMBERS</div><h1 class="fw-bold mb-2" style="color:#262116; letter-spacing:-.04em;">Trở thành thành viên</h1><p class="text-muted mb-0">Tạo tài khoản để đặt vé và lưu giữ những trải nghiệm yêu thích.</p></div>
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card border-0 shadow-sm bg-white rounded-4 overflow-hidden auth-card">
                <div class="p-3 text-center text-white oswald-font fs-4 text-uppercase" style="background:linear-gradient(135deg,#292720,#171613); border-bottom:1px solid rgba(215,181,109,.4); letter-spacing:.08em;">
                    ĐĂNG KÝ THÀNH VIÊN MỚI
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="?route=auth/register">
<input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Họ và tên *</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Email *</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Số điện thoại</label>
                            <input type="text" name="phone" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Mật khẩu (tối thiểu 8 ký tự) *</label>
                            <input type="password" name="password" class="form-control" minlength="8" required>
                        </div>
                        <button type="submit" class="btn btn-beta-buy w-100 py-2">ĐĂNG KÝ & TẠO VÍ THÀNH VIÊN</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
