<div class="container py-5 auth-page">
    <div class="text-center mb-4">
        <div class="cine-kicker">CINENOW MEMBERS</div>
        <h1 class="fw-bold mb-2" style="color:#262116; letter-spacing:-.04em;">Trở thành thành viên</h1>
        <p class="text-muted mb-0">Tạo tài khoản để đặt vé và lưu giữ những trải nghiệm yêu thích.</p>
    </div>
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card border-0 shadow-sm bg-white rounded-4 overflow-hidden auth-card">
                <div class="p-3 text-center text-white oswald-font fs-4 text-uppercase" style="background:linear-gradient(135deg,#292720,#171613); border-bottom:1px solid rgba(215,181,109,.4); letter-spacing:.08em;">
                    TẠO TÀI KHOẢN MỚI
                </div>
                <div class="card-body p-4 p-md-5">
                    <form method="POST" action="?route=auth/register" data-password-confirm>
                        <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        <div class="mb-3">
                            <label for="register-name" class="form-label fw-semibold">Họ và tên <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                id="register-name"
                                name="name"
                                class="form-control"
                                value="<?= htmlspecialchars($oldInput['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                autocomplete="name"
                                maxlength="150"
                                required
                            >
                        </div>
                        <div class="mb-3">
                            <label for="register-email" class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                            <input
                                type="email"
                                id="register-email"
                                name="email"
                                class="form-control"
                                value="<?= htmlspecialchars($oldInput['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                autocomplete="email"
                                autocapitalize="none"
                                spellcheck="false"
                                maxlength="150"
                                required
                            >
                        </div>
                        <div class="mb-3">
                            <label for="register-phone" class="form-label fw-semibold">Số điện thoại <span class="text-muted fw-normal">(không bắt buộc)</span></label>
                            <input
                                type="tel"
                                id="register-phone"
                                name="phone"
                                class="form-control"
                                value="<?= htmlspecialchars($oldInput['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                autocomplete="tel"
                                inputmode="tel"
                                placeholder="Ví dụ: 0912345678"
                            >
                            <div class="form-text">Nhập từ 9 đến 15 chữ số; có thể nhập mã quốc gia +84.</div>
                        </div>
                        <div class="mb-3">
                            <label for="register-password" class="form-label fw-semibold">Mật khẩu <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input
                                    type="password"
                                    id="register-password"
                                    name="password"
                                    class="form-control"
                                    autocomplete="new-password"
                                    minlength="8"
                                    required
                                >
                                <button
                                    type="button"
                                    class="btn btn-outline-secondary"
                                    data-password-toggle
                                    data-password-label="mật khẩu"
                                    aria-controls="register-password"
                                    aria-label="Hiện mật khẩu"
                                    aria-pressed="false"
                                >Hiện</button>
                            </div>
                            <div class="form-text">Mật khẩu cần có ít nhất 8 ký tự.</div>
                        </div>
                        <div class="mb-4">
                            <label for="register-password-confirmation" class="form-label fw-semibold">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input
                                    type="password"
                                    id="register-password-confirmation"
                                    name="password_confirmation"
                                    class="form-control"
                                    autocomplete="new-password"
                                    required
                                >
                                <button
                                    type="button"
                                    class="btn btn-outline-secondary"
                                    data-password-toggle
                                    data-password-label="mật khẩu xác nhận"
                                    aria-controls="register-password-confirmation"
                                    aria-label="Hiện mật khẩu xác nhận"
                                    aria-pressed="false"
                                >Hiện</button>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-beta-buy w-100 py-3 fw-semibold">ĐĂNG KÝ & TẠO VÍ THÀNH VIÊN</button>
                    </form>
                    <hr class="my-4">
                    <p class="small text-muted text-center mb-0">
                        Đã có tài khoản?
                        <a href="?route=auth/login" class="fw-bold text-decoration-none" style="color: var(--beta-orange);">Đăng nhập</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
