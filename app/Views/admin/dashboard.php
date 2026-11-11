<div>
    <ul class="nav nav-pills mb-4 gap-2">
        <li class="nav-item"><a class="nav-link <?= $tab === 'overview' ? 'active' : 'bg-white border text-dark' ?>" href="?route=admin&tab=overview">Tổng quan</a></li>
        <li class="nav-item"><a class="nav-link <?= $tab === 'users' ? 'active' : 'bg-white border text-dark' ?>" href="?route=admin&tab=users">1. Rạp, Users & Mật khẩu Admin</a></li>
        <li class="nav-item"><a class="nav-link <?= $tab === 'movies' ? 'active' : 'bg-white border text-dark' ?>" href="?route=admin&tab=movies">2. Phim & Thể loại</a></li>
        <li class="nav-item"><a class="nav-link <?= $tab === 'rooms' ? 'active' : 'bg-white border text-dark' ?>" href="?route=admin&tab=rooms">3. Phòng & Ghế</a></li>
        <li class="nav-item"><a class="nav-link <?= $tab === 'shows' ? 'active' : 'bg-white border text-dark' ?>" href="?route=admin&tab=shows">4. Suất chiếu (Shows)</a></li>
        <li class="nav-item"><a class="nav-link <?= $tab === 'bookings' ? 'active' : 'bg-white border text-dark' ?>" href="?route=admin&tab=bookings">5-6. Đơn vé & Soát vé QR</a></li>
        <li class="nav-item"><a class="nav-link <?= $tab === 'concessions' ? 'active' : 'bg-white border text-dark' ?>" href="?route=admin&tab=concessions">7. Bắp nước & Coupon</a></li>
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

    <?php elseif ($tab === 'bookings'): ?>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm bg-white">
                    <div class="card-header bg-success text-white fw-bold">
                        <i class="bi bi-qr-code-scan me-1"></i> Soát Vé Tại Rạp (Check-in Ticket)
                    </div>
                    <div class="card-body">
                        <form method="POST" action="?route=admin/ticket/checkin">
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
