<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card border-0 shadow-sm bg-white rounded-4 overflow-hidden">
                <div class="p-3 text-center text-white oswald-font fs-4 text-uppercase" style="background: var(--beta-gradient);">
                    ĐĂNG KÝ THÀNH VIÊN MỚI
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="?route=auth/register">
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
                            <label class="form-label fw-semibold small">Mật khẩu (tối thiểu 6 ký tự) *</label>
                            <input type="password" name="password" class="form-control" minlength="6" required>
                        </div>
                        <button type="submit" class="btn btn-beta-buy w-100 py-2">ĐĂNG KÝ & TẠO VÍ THÀNH VIÊN</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
