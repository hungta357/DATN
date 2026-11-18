<div class="container py-5">
    <div class="text-center mb-4">
        <div class="cine-kicker">CineNow Members</div>
        <h1 class="fw-bold mb-2 text-white">Trở thành thành viên</h1>
        <p class="text-muted mb-0">Tạo tài khoản để đặt vé và lưu giữ những trải nghiệm yêu thích.</p>
    </div>
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card border-0 rounded-4 overflow-hidden">
                <div class="p-3 text-center fw-bold text-uppercase" style="background: linear-gradient(135deg, #1e2230, #12141c); border-bottom: 1px solid var(--border); letter-spacing: .06em; color: var(--accent);">
                    Đăng ký thành viên mới
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="?route=auth/register">
                        <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        <div class="mb-3">
                            <label class="form-label">Họ và tên *</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Số điện thoại</label>
                            <input type="text" name="phone" class="form-control">
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Mật khẩu (tối thiểu 8 ký tự) *</label>
                            <input type="password" name="password" class="form-control" minlength="8" required>
                        </div>
                        <button type="submit" class="btn-cine w-100 py-2">Đăng ký & tạo ví thành viên</button>
                    </form>
                    <hr style="border-color: var(--border); opacity: 1;">
                    <div class="small text-muted text-center">
                        Đã có tài khoản?
                        <a href="?route=auth/login" class="fw-bold" style="color: var(--accent);">Đăng nhập ngay</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
