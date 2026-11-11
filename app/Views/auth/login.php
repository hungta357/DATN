<div class="container py-5 auth-page">
    <div class="text-center mb-4"><div class="cine-kicker">CINENOW MEMBERS</div><h1 class="fw-bold mb-2" style="color:#262116; letter-spacing:-.04em;">Chào mừng bạn trở lại</h1><p class="text-muted mb-0">Đăng nhập để tiếp tục hành trình điện ảnh của bạn.</p></div>
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card border-0 shadow-sm bg-white rounded-4 overflow-hidden auth-card">
                <div class="p-3 text-center text-white oswald-font fs-4 text-uppercase" style="background:linear-gradient(135deg,#292720,#171613); border-bottom:1px solid rgba(215,181,109,.4); letter-spacing:.08em;">
                    ĐĂNG NHẬP HỆ THỐNG
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="?route=auth/login">
<input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Email hoặc Tài khoản</label>
                            <input type="text" name="email" class="form-control" required autofocus placeholder="Nhập email hoặc tài khoản">
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold small">Mật khẩu</label>
                            <input type="password" name="password" class="form-control" required placeholder="Nhập mật khẩu">
                        </div>
                        <button type="submit" class="btn btn-beta-buy w-100 py-2">ĐĂNG NHẬP</button>
                    </form>
                    <hr>
                    <div class="small text-muted text-center">
                        Chưa có tài khoản? <a href="?route=auth/register" class="fw-bold" style="color: var(--beta-orange);">Đăng ký thành viên mới</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
