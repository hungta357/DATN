<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow">
            <div class="card-body p-4">
                <h4 class="fw-bold mb-3 text-center"><i class="bi bi-person-plus text-danger"></i> Đăng Ký Khách Hàng</h4>
                <form method="POST" action="?route=auth/register">
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
                    <div class="mb-3">
                        <label class="form-label">Mật khẩu (tối thiểu 6 ký tự) *</label>
                        <input type="password" name="password" class="form-control" minlength="6" required>
                    </div>
                    <button type="submit" class="btn btn-danger w-100">Tạo tài khoản & Khởi tạo Ví nội bộ</button>
                </form>
            </div>
        </div>
    </div>
</div>
