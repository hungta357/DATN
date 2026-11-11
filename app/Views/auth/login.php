<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card border-0 shadow-sm bg-white rounded-4 overflow-hidden">
                <div class="p-3 text-center text-white oswald-font fs-4 text-uppercase" style="background: var(--beta-gradient);">
                    ĐĂNG NHẬP THÀNH VIÊN BETA
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="?route=auth/login">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Email</label>
                            <input type="email" name="email" class="form-control" required placeholder="customer@cinema.vn">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Mật khẩu</label>
                            <input type="password" name="password" class="form-control" required placeholder="password">
                        </div>
                        <button type="submit" class="btn btn-beta-buy w-100 py-2">ĐĂNG NHẬP NGAY</button>
                    </form>
                    <hr>
                    <div class="small text-muted">
                        <p class="mb-1 fw-bold" style="color: var(--beta-blue);">Tài khoản mẫu (sau khi chạy <code>setup.php</code>):</p>
                        <ul class="mb-2 ps-3">
                            <li>Admin: <code>admin@cinema.vn</code> / Mật khẩu: <code>password</code></li>
                            <li>Staff: <code>staff@cinema.vn</code> / Mật khẩu: <code>password</code></li>
                            <li>Khách: <code>customer@cinema.vn</code> / Mật khẩu: <code>password</code></li>
                        </ul>
                        Chưa có tài khoản? <a href="?route=auth/register" class="fw-bold" style="color: var(--beta-orange);">Đăng ký thành viên mới</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
