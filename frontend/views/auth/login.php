<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card border-0 shadow-sm bg-white rounded-4 overflow-hidden">
                <div class="p-3 text-center text-white oswald-font fs-4 text-uppercase" style="background: var(--beta-gradient);">
                    ĐĂNG NHẬP HỆ THỐNG
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="?route=auth/login">
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
