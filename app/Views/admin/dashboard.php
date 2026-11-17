<div>
    <ul class="nav nav-pills mb-4 gap-2">
        <?php if (!empty($isAdmin)): ?>
            <li class="nav-item"><a class="nav-link <?= $tab === 'overview' ? 'active' : 'bg-white border text-dark' ?>" href="?route=admin&tab=overview">Tổng quan</a></li>
            <li class="nav-item"><a class="nav-link <?= $tab === 'users' ? 'active' : 'bg-white border text-dark' ?>" href="?route=admin&tab=users">1. Rạp, Users & Mật khẩu Admin</a></li>
            <li class="nav-item"><a class="nav-link <?= $tab === 'movies' ? 'active' : 'bg-white border text-dark' ?>" href="?route=admin&tab=movies">2. Phim & Thể loại</a></li>
            <li class="nav-item"><a class="nav-link <?= $tab === 'rooms' ? 'active' : 'bg-white border text-dark' ?>" href="?route=admin&tab=rooms">3. Phòng & Ghế</a></li>
            <li class="nav-item"><a class="nav-link <?= $tab === 'shows' ? 'active' : 'bg-white border text-dark' ?>" href="?route=admin&tab=shows">4. Suất chiếu (Shows)</a></li>
            <li class="nav-item"><a class="nav-link <?= $tab === 'concessions' ? 'active' : 'bg-white border text-dark' ?>" href="?route=admin&tab=concessions">7. Bắp nước & Coupon</a></li>
            <li class="nav-item"><a class="nav-link <?= $tab === 'groups' ? 'active' : 'bg-white border text-dark' ?>" href="?route=admin&tab=groups"><i class="bi bi-people me-1"></i>Đặt vé nhóm</a></li>
        <?php elseif (!empty($isStaff)): ?>
            <li class="nav-item"><a class="nav-link bg-white border text-dark" href="?route=schedule"><i class="bi bi-calendar3 me-1"></i>Xem lịch chiếu</a></li>
        <?php endif; ?>
        <li class="nav-item"><a class="nav-link <?= $tab === 'maintenance' ? 'active' : 'bg-white border text-dark' ?>" href="?route=admin&tab=maintenance"><i class="bi bi-tools me-1"></i>Sự cố & bảo trì</a></li>
        <li class="nav-item"><a class="nav-link <?= $tab === 'bookings' ? 'active' : 'bg-white border text-dark' ?>" href="?route=admin&tab=bookings">Đơn vé & Soát vé QR</a></li>
    </ul>

    <?php if ($tab === 'overview'): ?>
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm p-3 bg-white">
                    <div class="text-muted small">Nhánh 1: Người dùng</div>
                    <h3 class="fw-bold text-primary mb-0"><?= count($users) ?> tài khoản</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm p-3 bg-white">
                    <div class="text-muted small">Nhánh 2: Phim</div>
                    <h3 class="fw-bold text-danger mb-0"><?= count($movies) ?> bộ phim</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm p-3 bg-white">
                    <div class="text-muted small">Nhánh 3 & 4: Phòng / Suất chiếu</div>
                    <h3 class="fw-bold text-warning mb-0"><?= count($rooms) ?> phòng / <?= count($shows) ?> suất</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm p-3 bg-white">
                    <div class="text-muted small">Nhánh 5 & 6: Đơn đặt vé</div>
                    <h3 class="fw-bold text-success mb-0"><?= count($bookings) ?> đơn</h3>
                </div>
            </div>
        </div>
        <div class="card border-0 shadow-sm p-4 bg-white">
            <h6 class="fw-bold" style="color: var(--beta-blue);">Các quy tắc nghiệp vụ cốt lõi từ <code>cinema_complete.sql</code> đã được cài đặt sẵn:</h6>
            <ul class="small mb-0">
                <li><strong>Quy tắc 1:</strong> Khi tạo Suất chiếu (<code>shows</code>), hệ thống tự động tạo đầy đủ các dòng <code>show_seats</code> từ danh sách <code>seats</code> của phòng cộng phụ thu <code>seat_types.surcharge</code>.</li>
                <li><strong>Quy tắc 2:</strong> Khi khách giữ ghế, sử dụng <code>DB Transaction + SELECT ... FOR UPDATE</code>, kiểm tra <code>status = 'available'</code> và gán <code>hold_expires_at</code> (10 phút).</li>
                <li><strong>Quy tắc 3:</strong> Khi thanh toán thành công, cập nhật <code>bookings</code>, <code>show_seats</code>, trừ ví (nếu dùng <code>WALLET</code>) và phát hành <code>tickets</code> trong cùng một DB Transaction.</li>
                <li><strong>Quy tắc 4:</strong> Không xóa cứng dữ liệu đã phát sinh giao dịch (Users/Movies/Shows), chỉ chuyển trạng thái <code>status</code>.</li>
            </ul>
        </div>

    <?php elseif ($tab === 'users'): ?>
        <div class="row g-4">
            <div class="col-md-5">
                <div class="card border-0 shadow-sm bg-white">
                    <div class="card-header bg-white fw-bold">Thông Tin Rạp Chiếu Lẻ Duy Nhất (Bảng <code>cinema_info</code>)</div>
                    <div class="card-body">
                        <form method="POST" action="?route=admin/cinema/save">
<input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            <div class="mb-2">
                                <label class="form-label small">Tên rạp</label>
                                <input type="text" name="name" value="<?= htmlspecialchars($cinemaInfo['name']) ?>" class="form-control form-control-sm" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Địa chỉ</label>
                                <input type="text" name="address" value="<?= htmlspecialchars($cinemaInfo['address']) ?>" class="form-control form-control-sm" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Điện thoại Hotline</label>
                                <input type="text" name="phone" value="<?= htmlspecialchars($cinemaInfo['phone'] ?? '') ?>" class="form-control form-control-sm">
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Email</label>
                                <input type="email" name="email" value="<?= htmlspecialchars($cinemaInfo['email'] ?? '') ?>" class="form-control form-control-sm">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small">Giới thiệu rạp</label>
                                <textarea name="description" rows="3" class="form-control form-control-sm"><?= htmlspecialchars($cinemaInfo['description'] ?? '') ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-beta-buy btn-sm w-100">Lưu Thông Tin Rạp</button>
                        </form>
                    </div>
                </div>

                <div class="card border-0 shadow-sm bg-white mt-3">
                    <div class="card-header bg-white fw-bold text-danger">
                        <i class="bi bi-shield-lock-fill me-1"></i> Đổi Mật Khẩu Dành Riêng Cho Quản Trị Viên
                    </div>
                    <div class="card-body">
                        <form method="POST" action="?route=admin/password/change">
<input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            <div class="mb-2">
                                <label class="form-label small">Mật khẩu Admin hiện tại</label>
                                <input type="password" name="current_password" class="form-control form-control-sm" required placeholder="Nhập mật khẩu hiện tại">
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Mật khẩu Admin mới (tối thiểu 6 ký tự)</label>
                                <input type="password" name="new_password" class="form-control form-control-sm" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small">Nhập lại mật khẩu mới</label>
                                <input type="password" name="confirm_password" class="form-control form-control-sm" required>
                            </div>
                            <button type="submit" class="btn btn-danger btn-sm w-100 fw-bold">Cập Nhật Mật Khẩu Admin</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-7">
                <div class="card border-0 shadow-sm bg-white">
                    <div class="card-header bg-white fw-bold">Danh Sách Tài Khoản (Bảng <code>users</code>, <code>roles</code>)</div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0 align-middle small">
                            <thead class="table-light">
                                <tr><th>ID</th><th>Họ tên / Email</th><th>Vai trò</th><th>Trạng thái</th><th>Đổi TT</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $u): ?>
                                    <tr>
                                        <td>#<?= (int) $u['id'] ?></td>
                                        <td><?= htmlspecialchars($u['name']) ?><br><span class="text-muted"><?= htmlspecialchars($u['email']) ?></span></td>
                                        <td><span class="badge bg-info text-dark"><?= htmlspecialchars($u['role_names'] ?: 'customer') ?></span></td>
                                        <td><span class="badge <?= $u['status'] === 'active' ? 'bg-success' : 'bg-danger' ?>"><?= htmlspecialchars($u['status']) ?></span></td>
                                        <td>
                                            <form method="POST" action="?route=admin/user/status" class="d-flex gap-1">
<input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
                                                <select name="status" class="form-select form-select-sm">
                                                    <option value="active" <?= $u['status'] === 'active' ? 'selected' : '' ?>>active</option>
                                                    <option value="locked" <?= $u['status'] === 'locked' ? 'selected' : '' ?>>locked</option>
                                                </select>
                                                <button class="btn btn-sm btn-outline-primary">Lưu</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    <?php elseif ($tab === 'movies'): ?>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm bg-white">
                    <div class="card-header bg-white fw-bold">Thêm Phim Mới (Bảng <code>movies</code>)</div>
                    <div class="card-body">
                        <form method="POST" action="?route=admin/movie/save">
<input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            <div class="mb-2">
                                <label class="form-label small">Tên phim (Gốc) *</label>
                                <input type="text" name="title" class="form-control form-control-sm" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Tên tiếng Việt</label>
                                <input type="text" name="title_vi" class="form-control form-control-sm">
                            </div>
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <label class="form-label small">Thời lượng (phút)</label>
                                    <input type="number" name="runtime" value="120" class="form-control form-control-sm">
                                </div>
                                <div class="col-6">
                                    <label class="form-label small">Giới hạn tuổi</label>
                                    <input type="text" name="age_rating" value="T13" class="form-control form-control-sm">
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Link ảnh Poster</label>
                                <input type="text" name="poster_path" class="form-control form-control-sm" placeholder="https://...">
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Thể loại</label>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php foreach ($genres as $g): ?>
                                        <label class="small"><input type="checkbox" name="genre_ids[]" value="<?= (int) $g['id'] ?>"> <?= htmlspecialchars($g['name']) ?></label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Trạng thái</label>
                                <select name="status" class="form-select form-select-sm">
                                    <option value="now_showing">now_showing (Đang chiếu)</option>
                                    <option value="coming_soon">coming_soon (Sắp chiếu)</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small">Tóm tắt nội dung</label>
                                <textarea name="overview" rows="2" class="form-control form-control-sm"></textarea>
                            </div>
                            <button type="submit" class="btn btn-beta-buy btn-sm w-100">Thêm Phim</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card border-0 shadow-sm bg-white">
                    <div class="card-header bg-white fw-bold">Danh Sách Phim (Ưu tiên đổi <code>status</code> thay vì xóa cứng)</div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0 align-middle small">
                            <thead class="table-light">
                                <tr><th>ID</th><th>Tên phim</th><th>Thời lượng</th><th>Trạng thái</th><th>Cập nhật Status</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($movies as $m): ?>
                                    <tr>
                                        <td>#<?= (int) $m['id'] ?></td>
                                        <td class="fw-semibold"><?= htmlspecialchars($m['title']) ?><br><span class="text-primary"><?= htmlspecialchars($m['title_vi'] ?? '') ?></span></td>
                                        <td><?= (int) $m['runtime'] ?>p</td>
                                        <td><span class="badge bg-secondary"><?= htmlspecialchars($m['status']) ?></span></td>
                                        <td>
                                            <form method="POST" action="?route=admin/movie/status" class="d-flex gap-1">
<input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="movie_id" value="<?= (int) $m['id'] ?>">
                                                <select name="status" class="form-select form-select-sm">
                                                    <?php foreach (['now_showing', 'coming_soon', 'ended', 'hidden'] as $st): ?>
                                                        <option value="<?= $st ?>" <?= $m['status'] === $st ? 'selected' : '' ?>><?= $st ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <button class="btn btn-sm btn-outline-primary">Lưu</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    <?php elseif ($tab === 'rooms'): ?>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm bg-white">
                    <div class="card-header bg-white fw-bold">Tạo Phòng Chiếu & Sơ Đồ Ghế Tự Động</div>
                    <div class="card-body">
                        <form method="POST" action="?route=admin/room/save">
<input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            <div class="mb-2">
                                <label class="form-label small">Tên phòng chiếu *</label>
                                <input type="text" name="name" class="form-control form-control-sm" required placeholder="Phòng 03 - Standard">
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Loại phòng</label>
                                <select name="room_type" class="form-select form-select-sm">
                                    <option value="standard">standard</option>
                                    <option value="vip">vip</option>
                                    <option value="imax">imax</option>
                                    <option value="four_d">four_d</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small">Mô tả</label>
                                <input type="text" name="description" class="form-control form-control-sm">
                            </div>
                            <button type="submit" class="btn btn-beta-buy btn-sm w-100">Tạo Phòng & Sinh 24 Ghế Mẫu</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card border-0 shadow-sm bg-white">
                    <div class="card-header bg-white fw-bold">Danh Sách Phòng Chiếu (Bảng <code>rooms</code> & <code>seats</code>)</div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0 align-middle small">
                            <thead class="table-light">
                                <tr><th>ID</th><th>Tên phòng</th><th>Loại</th><th>Sức chứa</th><th>Khởi tạo ghế</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rooms as $r): ?>
                                    <tr>
                                        <td>#<?= (int) $r['id'] ?></td>
                                        <td class="fw-bold"><?= htmlspecialchars($r['name']) ?></td>
                                        <td><span class="badge bg-info text-dark"><?= strtoupper($r['room_type']) ?></span></td>
                                        <td><?= (int) $r['capacity'] ?> ghế</td>
                                        <td>
                                            <form method="POST" action="?route=admin/room/generate-seats">
<input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="room_id" value="<?= (int) $r['id'] ?>">
                                                <button class="btn btn-sm btn-outline-primary">Sinh sơ đồ ghế chuẩn (A1..D6)</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    <?php elseif ($tab === 'shows'): ?>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm bg-white">
                    <div class="card-header bg-white fw-bold">Tạo Suất Chiếu Mới (Tự động sinh <code>show_seats</code>)</div>
                    <div class="card-body">
                        <form method="POST" action="?route=admin/show/save">
<input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            <div class="mb-2">
                                <label class="form-label small">Chọn Phim *</label>
                                <select name="movie_id" class="form-select form-select-sm" required>
                                    <?php foreach ($movies as $m): ?>
                                        <option value="<?= (int) $m['id'] ?>"><?= htmlspecialchars($m['title']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Chọn Phòng Chiếu *</label>
                                <select name="room_id" class="form-select form-select-sm" required>
                                    <?php foreach ($rooms as $r): ?>
                                        <option value="<?= (int) $r['id'] ?>"><?= htmlspecialchars($r['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Thời gian bắt đầu *</label>
                                <input type="datetime-local" name="start_time" value="<?= date('Y-m-d\TH:i', strtotime('+2 hours')) ?>" class="form-control form-control-sm" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small">Giá vé cơ bản (VNĐ) *</label>
                                <input type="number" name="base_price" value="55000" step="5000" class="form-control form-control-sm" required>
                            </div>
                            <button type="submit" class="btn btn-beta-buy btn-sm w-100">Tạo Suất Chiếu & Sinh Ghế</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card border-0 shadow-sm bg-white">
                    <div class="card-header bg-white fw-bold">Danh Sách Suất Chiếu (Bảng <code>shows</code> & <code>show_seats</code>)</div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0 align-middle small">
                            <thead class="table-light">
                                <tr><th>ID</th><th>Phim</th><th>Phòng</th><th>Giờ chiếu</th><th>Giá gốc</th><th>Ghế trống</th><th>Đặt thử</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($shows as $sh): ?>
                                    <tr>
                                        <td>#<?= (int) $sh['id'] ?></td>
                                        <td class="fw-semibold"><?= htmlspecialchars($sh['movie_title']) ?></td>
                                        <td><?= htmlspecialchars($sh['room_name']) ?></td>
                                        <td><?= date('H:i d/m/Y', strtotime($sh['start_time'])) ?></td>
                                        <td class="text-danger fw-bold"><?= number_format((float) $sh['base_price']) ?>đ</td>
                                        <td><?= (int) $sh['available_seats'] ?> / <?= (int) $sh['total_seats'] ?></td>
                                        <td><a href="?route=booking/seats&show_id=<?= (int) $sh['id'] ?>" class="btn btn-sm btn-outline-primary">Sơ đồ ghế</a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    <?php elseif ($tab === 'groups'): ?>
        <div class="card border-0 shadow-sm bg-white">
            <div class="card-header bg-white fw-bold"><i class="bi bi-people me-1"></i>Quản lý đặt vé nhóm</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead><tr><th>Mã nhóm</th><th>Phim / Suất chiếu</th><th>Người tạo & thành viên</th><th>Thanh toán</th><th>Trạng thái</th><th>Hạn</th></tr></thead>
                        <tbody>
                            <?php foreach ($groupBookings as $groupBooking): ?>
                                <tr>
                                    <td><code><?= htmlspecialchars($groupBooking['invite_code'], ENT_QUOTES, 'UTF-8') ?></code><br><span class="small text-muted"><?= htmlspecialchars($groupBooking['payment_mode'] === 'split' ? 'Chia tiền' : 'Trả chung', ENT_QUOTES, 'UTF-8') ?></span></td>
                                    <td><?= htmlspecialchars($groupBooking['movie_title'], ENT_QUOTES, 'UTF-8') ?><br><span class="small text-muted"><?= htmlspecialchars($groupBooking['room_name'], ENT_QUOTES, 'UTF-8') ?> · <?= date('H:i d/m/Y', strtotime($groupBooking['start_time'])) ?></span></td>
                                    <td><?= htmlspecialchars($groupBooking['creator_name'], ENT_QUOTES, 'UTF-8') ?><br><span class="small text-muted"><?= (int) $groupBooking['member_count'] ?>/<?= (int) $groupBooking['member_limit'] ?>: <?= htmlspecialchars($groupBooking['member_names'], ENT_QUOTES, 'UTF-8') ?></span></td>
                                    <td>
                                        <?= $groupBooking['total_amount'] !== null ? number_format((float) $groupBooking['total_amount']) . 'đ' : 'Chưa xác nhận ghế' ?>
                                        <?php if ($groupBooking['payment_mode'] === 'split' && $groupBooking['status'] === 'pending_payment'): ?>
                                            <br><span class="small text-muted"><?= (int) $groupBooking['paid_count'] ?>/<?= (int) $groupBooking['member_limit'] ?> đã trả</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge <?= $groupBooking['status'] === 'paid' ? 'bg-success' : ($groupBooking['status'] === 'expired' ? 'bg-secondary' : 'bg-warning text-dark') ?>"><?= htmlspecialchars($groupBooking['status'], ENT_QUOTES, 'UTF-8') ?></span></td>
                                    <td class="small"><?= date('d/m/Y H:i', strtotime($groupBooking['expires_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($groupBookings)): ?>
                                <tr><td colspan="6" class="text-center text-muted py-4">Chưa có đơn đặt vé nhóm.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    <?php elseif ($tab === 'maintenance'): ?>
        <?php
        $maintenanceRoomFilter = isset($_GET['maintenance_room']) && is_scalar($_GET['maintenance_room']) ? (string) $_GET['maintenance_room'] : '';
        $maintenanceSeverityFilter = isset($_GET['maintenance_severity']) && is_string($_GET['maintenance_severity']) ? $_GET['maintenance_severity'] : '';
        $maintenanceStatusFilter = isset($_GET['maintenance_status']) && is_string($_GET['maintenance_status']) ? $_GET['maintenance_status'] : '';
        $statusLabels = [
            'reported' => 'Mới báo cáo',
            'assigned' => 'Đã phân công',
            'in_progress' => 'Đang xử lý',
            'pending_confirmation' => 'Chờ Admin xác nhận',
            'completed' => 'Hoàn thành',
        ];
        $severityLabels = ['low' => 'Thấp', 'medium' => 'Trung bình', 'high' => 'Cao', 'critical' => 'Nghiêm trọng'];
        ?>
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm bg-white">
                    <div class="card-header bg-white fw-bold"><i class="bi bi-exclamation-diamond me-1"></i>Ghi nhận sự cố</div>
                    <div class="card-body">
                        <form method="POST" action="?route=admin/maintenance/create" enctype="multipart/form-data">
                            <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            <div class="mb-2">
                                <label class="form-label small">Loại sự cố *</label>
                                <select name="category_id" class="form-select form-select-sm" required>
                                    <option value="">Chọn loại sự cố</option>
                                    <?php foreach ($maintenanceCategories as $category): ?>
                                        <option value="<?= (int) $category['id'] ?>"><?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Phòng chiếu *</label>
                                <select name="room_id" id="maintenance-room" class="form-select form-select-sm" required>
                                    <option value="">Chọn phòng</option>
                                    <?php foreach ($maintenanceRooms as $room): ?>
                                        <option value="<?= (int) $room['id'] ?>"><?= htmlspecialchars($room['name'], ENT_QUOTES, 'UTF-8') ?><?= $room['status'] === 'maintenance' ? ' (đang bảo trì)' : '' ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Ghế (không bắt buộc)</label>
                                <select name="seat_id" id="maintenance-seat" class="form-select form-select-sm">
                                    <option value="">Sự cố cấp phòng / thiết bị</option>
                                    <?php foreach ($maintenanceSeats as $seat): ?>
                                        <option value="<?= (int) $seat['id'] ?>" data-room="<?= (int) $seat['room_id'] ?>">
                                            <?= htmlspecialchars($seat['room_name'] . ' — ' . $seat['seat_code'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($seat['status'], ENT_QUOTES, 'UTF-8') ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Mô tả tình trạng *</label>
                                <textarea name="description" rows="3" class="form-control form-control-sm" required maxlength="5000"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small">Ảnh minh chứng (JPG, PNG, WEBP; tối đa 5 MB)</label>
                                <input type="file" name="evidence" accept="image/jpeg,image/png,image/webp" class="form-control form-control-sm">
                            </div>
                            <button type="submit" class="btn btn-beta-buy btn-sm w-100">Gửi báo cáo sự cố</button>
                        </form>
                    </div>
                </div>
                <div class="alert alert-info small mt-3 mb-0">
                    Khi báo cáo ghế, ghế sẽ tự chuyển sang trạng thái bảo trì. Admin đánh giá mức độ và phân công; sự cố nghiêm trọng cấp phòng sẽ tạm ngừng phòng cho đến khi xác nhận khắc phục.
                </div>
            </div>
            <div class="col-lg-8">
                <?php if (!empty($isAdmin)): ?>
                    <div class="card border-0 shadow-sm bg-white mb-3">
                        <div class="card-header bg-white fw-bold">Thống kê sự cố theo tháng (12 tháng gần nhất)</div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm mb-0 align-middle">
                                    <thead><tr><th>Tháng</th><th>Tổng</th><th>Đang xử lý</th><th>Hoàn thành</th><th>Nghiêm trọng</th></tr></thead>
                                    <tbody>
                                        <?php foreach ($maintenanceStats as $stat): ?>
                                            <tr>
                                                <td><?= htmlspecialchars(date('m/Y', strtotime($stat['month'] . '-01')), ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= (int) $stat['total'] ?></td>
                                                <td><?= (int) $stat['open'] ?></td>
                                                <td><?= (int) $stat['completed'] ?></td>
                                                <td><?= (int) $stat['critical'] ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($maintenanceStats)): ?>
                                            <tr><td colspan="5" class="text-center text-muted py-3">Chưa có dữ liệu sự cố.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="card border-0 shadow-sm bg-white mb-3">
                    <div class="card-body">
                        <form method="GET" class="row g-2 align-items-end">
                            <input type="hidden" name="route" value="admin">
                            <input type="hidden" name="tab" value="maintenance">
                            <div class="col-md-4">
                                <label class="form-label small">Phòng chiếu</label>
                                <select name="maintenance_room" class="form-select form-select-sm">
                                    <option value="">Tất cả phòng</option>
                                    <?php foreach ($maintenanceRooms as $room): ?>
                                        <option value="<?= (int) $room['id'] ?>" <?= $maintenanceRoomFilter === (string) $room['id'] ? 'selected' : '' ?>><?= htmlspecialchars($room['name'], ENT_QUOTES, 'UTF-8') ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Mức độ</label>
                                <select name="maintenance_severity" class="form-select form-select-sm">
                                    <option value="">Tất cả</option>
                                    <?php foreach ($severityLabels as $value => $label): ?>
                                        <option value="<?= $value ?>" <?= $maintenanceSeverityFilter === $value ? 'selected' : '' ?>><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Trạng thái</label>
                                <select name="maintenance_status" class="form-select form-select-sm">
                                    <option value="">Tất cả</option>
                                    <?php foreach ($statusLabels as $value => $label): ?>
                                        <option value="<?= $value ?>" <?= $maintenanceStatusFilter === $value ? 'selected' : '' ?>><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex gap-1">
                                <button class="btn btn-beta-buy btn-sm flex-grow-1">Lọc</button>
                                <a class="btn btn-outline-secondary btn-sm" href="?route=admin&amp;tab=maintenance">Xóa</a>
                            </div>
                        </form>
                    </div>
                </div>

                <?php if (empty($maintenanceReports)): ?>
                    <div class="card border-0 shadow-sm bg-white p-4 text-center text-muted">Chưa có báo cáo sự cố phù hợp.</div>
                <?php endif; ?>
                <?php foreach ($maintenanceReports as $report): ?>
                    <div class="card border-0 shadow-sm bg-white mb-3">
                        <div class="card-header bg-white d-flex justify-content-between align-items-start gap-2">
                            <div>
                                <strong><?= htmlspecialchars($report['report_code'], ENT_QUOTES, 'UTF-8') ?></strong>
                                <span class="text-muted ms-2"><?= htmlspecialchars($report['category_name'], ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                            <span class="badge <?= $report['severity'] === 'critical' ? 'bg-danger' : ($report['severity'] === 'high' ? 'bg-warning text-dark' : 'bg-secondary') ?>">
                                <?= htmlspecialchars($severityLabels[$report['severity']] ?? $report['severity'], ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="row g-2 small mb-3">
                                <div class="col-md-6"><strong>Phòng:</strong> <?= htmlspecialchars($report['room_name'], ENT_QUOTES, 'UTF-8') ?><?= $report['seat_code'] ? ' — Ghế ' . htmlspecialchars($report['seat_code'], ENT_QUOTES, 'UTF-8') : '' ?></div>
                                <div class="col-md-6"><strong>Trạng thái:</strong> <?= htmlspecialchars($statusLabels[$report['status']] ?? $report['status'], ENT_QUOTES, 'UTF-8') ?></div>
                                <div class="col-md-6"><strong>Báo cáo bởi:</strong> <?= htmlspecialchars($report['reporter_name'], ENT_QUOTES, 'UTF-8') ?></div>
                                <div class="col-md-6"><strong>Phụ trách:</strong> <?= htmlspecialchars($report['assignee_name'] ?? 'Chưa phân công', ENT_QUOTES, 'UTF-8') ?></div>
                                <div class="col-md-6"><strong>Thời hạn:</strong> <?= $report['due_at'] ? date('d/m/Y H:i', strtotime($report['due_at'])) : 'Chưa đặt' ?></div>
                                <div class="col-md-6"><strong>Tạo lúc:</strong> <?= date('d/m/Y H:i', strtotime($report['created_at'])) ?></div>
                            </div>
                            <p class="mb-2"><?= nl2br(htmlspecialchars($report['description'], ENT_QUOTES, 'UTF-8')) ?></p>
                            <?php if (!empty($report['resolution'])): ?>
                                <p class="mb-2 small"><strong>Kết quả xử lý:</strong> <?= nl2br(htmlspecialchars($report['resolution'], ENT_QUOTES, 'UTF-8')) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($report['attachments'])): ?>
                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    <?php foreach ($report['attachments'] as $attachment): ?>
                                        <a href="<?= htmlspecialchars($attachment['file_path'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-image me-1"></i><?= htmlspecialchars($attachment['original_name'], ENT_QUOTES, 'UTF-8') ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($isAdmin) && $report['status'] !== 'completed'): ?>
                                <form method="POST" action="?route=admin/maintenance/assign" class="row g-2 border-top pt-3 mb-3">
                                    <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="report_id" value="<?= (int) $report['id'] ?>">
                                    <div class="col-md-4">
                                        <label class="form-label small">Phân công nhân viên</label>
                                        <select name="staff_id" class="form-select form-select-sm" required>
                                            <option value="">Chọn nhân viên</option>
                                            <?php foreach ($maintenanceStaff as $staff): ?>
                                                <option value="<?= (int) $staff['id'] ?>" <?= (int) $report['assigned_to'] === (int) $staff['id'] ? 'selected' : '' ?>><?= htmlspecialchars($staff['name'], ENT_QUOTES, 'UTF-8') ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small">Mức độ</label>
                                        <select name="severity" class="form-select form-select-sm">
                                            <?php foreach ($severityLabels as $value => $label): ?>
                                                <option value="<?= $value ?>" <?= $report['severity'] === $value ? 'selected' : '' ?>><?= $label ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small">Hạn xử lý</label>
                                        <input type="datetime-local" name="due_at" class="form-control form-control-sm" value="<?= $report['due_at'] ? date('Y-m-d\TH:i', strtotime($report['due_at'])) : '' ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small">Ghi chú phân công</label>
                                        <input type="text" name="note" class="form-control form-control-sm" maxlength="1000">
                                    </div>
                                    <div class="col-12"><button class="btn btn-sm btn-outline-primary">Lưu phân công</button></div>
                                </form>
                            <?php endif; ?>

                            <?php if (empty($isAdmin) && (int) $report['assigned_to'] === (int) ($_SESSION['user']['id'] ?? 0) && in_array($report['status'], ['assigned', 'in_progress'], true)): ?>
                                <form method="POST" action="?route=admin/maintenance/progress" enctype="multipart/form-data" class="row g-2 border-top pt-3 mb-3">
                                    <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="report_id" value="<?= (int) $report['id'] ?>">
                                    <div class="col-md-8">
                                        <label class="form-label small">Cập nhật tiến độ / kết quả *</label>
                                        <textarea name="note" rows="2" class="form-control form-control-sm" required maxlength="5000"></textarea>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small">Ảnh minh chứng (tùy chọn)</label>
                                        <input type="file" name="evidence" accept="image/jpeg,image/png,image/webp" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-12 d-flex gap-2">
                                        <button class="btn btn-sm btn-outline-primary" name="submit_for_confirmation" value="0">Lưu tiến độ</button>
                                        <button class="btn btn-sm btn-success" name="submit_for_confirmation" value="1">Gửi Admin xác nhận hoàn thành</button>
                                    </div>
                                </form>
                            <?php endif; ?>

                            <?php if (!empty($isAdmin) && $report['status'] === 'pending_confirmation'): ?>
                                <form method="POST" action="?route=admin/maintenance/confirm" class="border-top pt-3 mb-3">
                                    <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="report_id" value="<?= (int) $report['id'] ?>">
                                    <label class="form-label small">Ghi chú xác nhận (tùy chọn)</label>
                                    <div class="d-flex gap-2">
                                        <input type="text" name="note" class="form-control form-control-sm" maxlength="1000">
                                        <button class="btn btn-sm btn-success text-nowrap">Xác nhận hoàn thành</button>
                                    </div>
                                </form>
                            <?php endif; ?>

                            <details class="small">
                                <summary class="text-primary">Lịch sử xử lý (<?= count($report['history']) ?>)</summary>
                                <ul class="mt-2 mb-0">
                                    <?php foreach ($report['history'] as $history): ?>
                                        <li><strong><?= htmlspecialchars($history['actor_name'], ENT_QUOTES, 'UTF-8') ?></strong> — <?= date('d/m/Y H:i', strtotime($history['created_at'])) ?>: <?= nl2br(htmlspecialchars($history['note'], ENT_QUOTES, 'UTF-8')) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </details>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <script>
            (function () {
                var room = document.getElementById('maintenance-room');
                var seat = document.getElementById('maintenance-seat');
                if (!room || !seat) return;
                function filterSeats() {
                    var roomId = room.value;
                    Array.prototype.forEach.call(seat.options, function (option) {
                        if (!option.value) return;
                        var visible = roomId !== '' && option.getAttribute('data-room') === roomId;
                        option.hidden = !visible;
                        option.disabled = !visible;
                        if (!visible && option.selected) seat.value = '';
                    });
                }
                room.addEventListener('change', filterSeats);
                filterSeats();
            }());
        </script>

    <?php elseif ($tab === 'bookings'): ?>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm bg-white">
                    <div class="card-header bg-success text-white fw-bold">
                        <i class="bi bi-qr-code-scan me-1"></i> Soát Vé Tại Rạp (Check-in Ticket)
                    </div>
                    <div class="card-body">
                        <form method="POST" action="?route=admin/ticket/checkin">
<input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            <div class="mb-2">
                                <label class="form-label small">Nhập Mã Vé (<code>ticket_code</code>)</label>
                                <input type="text" name="ticket_code" class="form-control form-control-sm" required placeholder="VD: TK-BK2026...-1">
                            </div>
                            <button type="submit" class="btn btn-success btn-sm w-100 fw-bold">Xác Nhận Soát Vé (Check-in)</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card border-0 shadow-sm bg-white">
                    <div class="card-header bg-white fw-bold">Toàn Bộ Đơn Đặt Vé (Bảng <code>bookings</code>)</div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0 align-middle small">
                            <thead class="table-light">
                                <tr><th>Mã đơn</th><th>Khách hàng</th><th>Phim / Phòng</th><th>Tổng tiền</th><th>Trạng thái</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bookings as $b): ?>
                                    <tr>
                                        <td><code><?= htmlspecialchars($b['booking_code']) ?></code></td>
                                        <td><?= htmlspecialchars($b['user_name']) ?></td>
                                        <td><?= htmlspecialchars($b['movie_title']) ?><br><span class="text-muted"><?= htmlspecialchars($b['room_name']) ?></span></td>
                                        <td class="text-danger fw-bold"><?= number_format((float) $b['total_amount']) ?>đ</td>
                                        <td><span class="badge <?= $b['status'] === 'confirmed' ? 'bg-success' : 'bg-warning text-dark' ?>"><?= htmlspecialchars($b['status']) ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    <?php elseif ($tab === 'concessions'): ?>
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm bg-white mb-3">
                    <div class="card-header bg-white fw-bold">Thêm Combo Bắp Nước (Bảng <code>concessions</code>)</div>
                    <div class="card-body">
                        <form method="POST" action="?route=admin/concession/save" class="row g-2">
<input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            <div class="col-md-6">
                                <input type="text" name="name" class="form-control form-control-sm" placeholder="Tên combo *" required>
                            </div>
                            <div class="col-md-3">
                                <input type="number" name="price" class="form-control form-control-sm" placeholder="Giá *" value="69000" required>
                            </div>
                            <div class="col-md-3">
                                <input type="number" name="stock" class="form-control form-control-sm" placeholder="Tồn kho" value="100" required>
                            </div>
                            <div class="col-md-9">
                                <input type="text" name="description" class="form-control form-control-sm" placeholder="Mô tả sản phẩm">
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-beta-buy btn-sm w-100">Thêm</button>
                            </div>
                        </form>
                    </div>
                </div>
                <table class="table table-bordered bg-white table-sm small">
                    <thead class="table-light"><tr><th>Tên sản phẩm</th><th>Giá</th><th>Tồn kho</th></tr></thead>
                    <tbody>
                        <?php foreach ($concessions as $c): ?>
                            <tr>
                                <td><?= htmlspecialchars($c['name']) ?></td>
                                <td class="text-danger fw-bold"><?= number_format((float) $c['price']) ?>đ</td>
                                <td><?= (int) $c['stock'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="col-md-6">
                <div class="card border-0 shadow-sm bg-white mb-3">
                    <div class="card-header bg-white fw-bold">Thêm Mã Giảm Giá (Bảng <code>coupons</code>)</div>
                    <div class="card-body">
                        <form method="POST" action="?route=admin/coupon/save" class="row g-2">
<input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            <div class="col-md-4">
                                <input type="text" name="code" class="form-control form-control-sm" placeholder="Mã (VD: BETA50)" required>
                            </div>
                            <div class="col-md-8">
                                <input type="text" name="name" class="form-control form-control-sm" placeholder="Tên chương trình *" required>
                            </div>
                            <div class="col-md-4">
                                <select name="discount_type" class="form-select form-select-sm">
                                    <option value="fixed">Giảm tiền (fixed)</option>
                                    <option value="percent">Giảm % (percent)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <input type="number" name="discount_value" class="form-control form-control-sm" placeholder="Giá trị giảm" value="25000" required>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-beta-buy btn-sm w-100">Tạo Mã</button>
                            </div>
                        </form>
                    </div>
                </div>
                <table class="table table-bordered bg-white table-sm small">
                    <thead class="table-light"><tr><th>Mã</th><th>Tên</th><th>Giảm</th><th>Đã dùng</th></tr></thead>
                    <tbody>
                        <?php foreach ($coupons as $cp): ?>
                            <tr>
                                <td><code><?= htmlspecialchars($cp['code']) ?></code></td>
                                <td><?= htmlspecialchars($cp['name']) ?></td>
                                <td><?= $cp['discount_type'] === 'percent' ? (float) $cp['discount_value'] . '%' : number_format((float) $cp['discount_value']) . 'đ' ?></td>
                                <td><?= (int) $cp['used_count'] ?> / <?= htmlspecialchars((string) ($cp['usage_limit'] ?? '∞')) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>
