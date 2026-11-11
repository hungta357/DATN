<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow">
            <div class="card-body p-4">
                <h4 class="fw-bold mb-3 text-center"><i class="bi bi-box-arrow-in-right text-danger"></i> Đăng Nhập</h4>
                <form method="POST" action="?route=auth/login">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required placeholder="admin@cinema.vn">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mật khẩu</label>
                        <input type="password" name="password" class="form-control" required placeholder="password">
                    </div>
                    <button type="submit" class="btn btn-danger w-100">Đăng nhập</button>
                </form>
                <hr>
                <div class="small text-secondary">
                    <p class="mb-1 fw-semibold text-light">Tài khoản mẫu (sau khi chạy <code>seed_sample_data.sql</code>):</p>
                    <ul class="mb-2 ps-3">
                        <li>Admin: <code>admin@cinema.vn</code> / Mật khẩu: <code>password</code></li>
                        <li>Staff: <code>staff@cinema.vn</code> / Mật khẩu: <code>password</code></li>
                        <li>Khách: <code>customer@cinema.vn</code> / Mật khẩu: <code>password</code></li>
                    </ul>
                    Chưa có tài khoản? <a href="?route=auth/register" class="text-danger">Đăng ký ngay</a>
                </div>
            </div>
        </div>
    </div>
</div>
