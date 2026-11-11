<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card border-0 shadow-sm bg-white rounded-4 overflow-hidden">
                <div class="p-3 text-center text-white oswald-font fs-4 text-uppercase" style="background: var(--beta-gradient);">
                    ĐĂNG NHẬP THÀNH VIÊN CINENOW
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="?route=auth/login" id="loginForm">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Email</label>
                            <input type="email" name="email" id="loginEmail" class="form-control" required placeholder="customer@cinema.vn">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Mật khẩu</label>
                            <input type="password" name="password" id="loginPassword" class="form-control" required placeholder="password">
                        </div>
                        <button type="submit" class="btn btn-beta-buy w-100 py-2">ĐĂNG NHẬP NGAY</button>
                    </form>
                    <hr>
                    <div class="small text-muted">
                        <p class="mb-2 fw-bold" style="color: var(--beta-blue);">Đăng nhập nhanh 1-Click (Tài khoản mẫu):</p>
                        <div class="d-grid gap-2 mb-3">
                            <button type="button" class="btn btn-outline-primary btn-sm text-start" onclick="quickLogin('customer@cinema.vn', 'password')">
                                <i class="bi bi-person-check-fill me-1"></i> <strong>Khách hàng:</strong> <code>customer@cinema.vn</code> (Có sẵn 500.000đ trong Ví)
                            </button>
                            <button type="button" class="btn btn-outline-success btn-sm text-start" onclick="quickLogin('staff@cinema.vn', 'password')">
                                <i class="bi bi-qr-code-scan me-1"></i> <strong>Nhân viên soát vé:</strong> <code>staff@cinema.vn</code>
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm text-start" onclick="quickLogin('admin@cinema.vn', 'password')">
                                <i class="bi bi-shield-lock-fill me-1"></i> <strong>Quản trị viên (Admin):</strong> <code>admin@cinema.vn</code>
                            </button>
                        </div>
                        Chưa có tài khoản? <a href="?route=auth/register" class="fw-bold" style="color: var(--beta-orange);">Đăng ký thành viên mới</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
function quickLogin(email, pass) {
    document.getElementById('loginEmail').value = email;
    document.getElementById('loginPassword').value = pass;
    document.getElementById('loginForm').submit();
}
</script>
