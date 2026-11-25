<div class="container py-5 auth-page">
    <div class="text-center mb-4">
        <div class="cine-kicker">CINENOW MEMBERS</div>
        <h1 class="fw-bold mb-2" style="color:#262116; letter-spacing:-.04em;">Chào mừng bạn trở lại</h1>
        <p class="text-muted mb-0">Đăng nhập để tiếp tục hành trình điện ảnh của bạn.</p>
    </div>
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">
            <div class="card border-0 shadow-sm bg-white rounded-4 overflow-hidden auth-card">
                <div class="p-3 text-center text-white oswald-font fs-4 text-uppercase" style="background:linear-gradient(135deg,#292720,#171613); border-bottom:1px solid rgba(215,181,109,.4); letter-spacing:.08em;">
                    ĐĂNG NHẬP TÀI KHOẢN
                </div>
                <div class="card-body p-4 p-md-5">
                    <form method="POST" action="?route=auth/login">
                        <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        <div class="mb-4">
                            <label for="login-email" class="form-label fw-semibold">Email hoặc tài khoản admin</label>
                            <input
                                type="text"
                                id="login-email"
                                name="email"
                                class="form-control form-control-lg"
                                value="<?= htmlspecialchars($oldInput['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                autocomplete="username"
                                autocapitalize="none"
                                spellcheck="false"
                                required
                                autofocus
                                placeholder="name@example.com"
                            >
                        </div>
                        <div class="mb-4">
                            <label for="login-password" class="form-label fw-semibold">Mật khẩu</label>
                            <div class="input-group input-group-lg">
                                <input
                                    type="password"
                                    id="login-password"
                                    name="password"
                                    class="form-control"
                                    autocomplete="current-password"
                                    required
                                    placeholder="Nhập mật khẩu"
                                >
                                <button
                                    type="button"
                                    class="btn btn-outline-secondary"
                                    data-password-toggle
                                    data-password-label="mật khẩu"
                                    aria-controls="login-password"
                                    aria-label="Hiện mật khẩu"
                                    aria-pressed="false"
                                >Hiện</button>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-beta-buy w-100 py-3 fw-semibold">ĐĂNG NHẬP</button>
                    </form>
                    <hr class="my-4">
                    <p class="small text-muted text-center mb-0">
                        Chưa có tài khoản?
                        <a href="?route=auth/register" class="fw-bold text-decoration-none" style="color: var(--beta-orange);">Đăng ký thành viên</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
