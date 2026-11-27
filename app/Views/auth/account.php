<div class="container py-5 auth-page">
    <div class="text-center mb-4">
        <div class="cine-kicker">CINENOW MEMBERS</div>
        <h1 class="fw-bold mb-2" style="color:#262116; letter-spacing:-.04em;">Tài khoản của tôi</h1>
        <p class="text-muted mb-0">Quản lý thông tin cá nhân để thuận tiện khi đặt vé.</p>
    </div>
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card border-0 shadow-sm bg-white rounded-4 overflow-hidden auth-card">
                <div class="p-3 text-center text-white oswald-font fs-4 text-uppercase" style="background:linear-gradient(135deg,#292720,#171613); border-bottom:1px solid rgba(215,181,109,.4); letter-spacing:.08em;">
                    Thông tin thành viên
                </div>
                <div class="card-body p-4 p-md-5">
                    <?php if (empty($isEditing)): ?>
                        <dl class="row mb-4">
                            <dt class="col-sm-4 text-muted">Họ và tên</dt>
                            <dd class="col-sm-8 fw-semibold"><?= htmlspecialchars((string) ($account['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></dd>
                            <dt class="col-sm-4 text-muted">Email</dt>
                            <dd class="col-sm-8"><?= htmlspecialchars((string) ($account['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></dd>
                            <dt class="col-sm-4 text-muted">Số điện thoại</dt>
                            <dd class="col-sm-8"><?= htmlspecialchars((string) ($account['phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?: 'Chưa cập nhật' ?></dd>
                        </dl>
                        <a href="?route=auth/account&amp;edit=1" class="btn btn-beta-buy w-100 py-3 fw-semibold">THAY ĐỔI THÔNG TIN</a>
                    <?php else: ?>
                    <form method="POST" action="?route=auth/account">
                        <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        <div class="mb-3">
                            <label for="account-name" class="form-label fw-semibold">Họ và tên</label>
                            <input type="text" id="account-name" name="name" class="form-control" value="<?= htmlspecialchars((string) ($oldInput['name'] ?? $account['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" maxlength="150" autocomplete="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="account-email" class="form-label fw-semibold">Email</label>
                            <input type="email" id="account-email" name="email" class="form-control" value="<?= htmlspecialchars((string) ($oldInput['email'] ?? $account['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" maxlength="150" autocomplete="email" required>
                        </div>
                        <div class="mb-4">
                            <label for="account-phone" class="form-label fw-semibold">Số điện thoại <span class="text-muted fw-normal">(không bắt buộc)</span></label>
                            <input type="tel" id="account-phone" name="phone" class="form-control" value="<?= htmlspecialchars((string) ($oldInput['phone'] ?? $account['phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" autocomplete="tel" inputmode="tel">
                        </div>
                        <button type="submit" class="btn btn-beta-buy w-100 py-3 fw-semibold">LƯU THÔNG TIN</button>
                    </form>
                    <a href="?route=auth/account" class="btn btn-link w-100 mt-2 text-decoration-none">Quay lại thông tin tài khoản</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
