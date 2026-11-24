<?php
$csrfToken = htmlspecialchars($_SESSION['_token'] ?? '', ENT_QUOTES, 'UTF-8');
$nowTs = time();
$upcomingShows = array_values(array_filter($shows ?? [], static function (array $sh) use ($nowTs): bool {
    return in_array($sh['status'] ?? '', ['scheduled', 'selling'], true) && strtotime($sh['start_time']) > $nowTs;
}));
$nowShowingMoviesCount = count(array_filter($movies ?? [], static fn(array $m): bool => ($m['status'] ?? '') === 'now_showing'));
?>
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
        <li class="nav-item"><a class="nav-link <?= $tab === 'incidents' ? 'active' : 'bg-white border text-dark' ?>" href="?route=admin&tab=incidents"><i class="bi bi-chat-square-text me-1"></i>Khiếu nại vé</a></li>
        <li class="nav-item"><a class="nav-link <?= $tab === 'shifts' ? 'active' : 'bg-white border text-dark' ?>" href="?route=admin&tab=shifts"><i class="bi bi-calendar2-check me-1"></i>Ca trực</a></li>
        <li class="nav-item"><a class="nav-link <?= $tab === 'bookings' ? 'active' : 'bg-white border text-dark' ?>" href="?route=admin&tab=bookings">Đơn vé & Soát vé QR</a></li>
    </ul>

    <?php if ($tab === 'overview'): ?>
        <?php $stats = $overviewStats ?? []; ?>
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm p-3 bg-white h-100">
                    <div class="text-muted small fw-semibold">Tổng doanh thu thực tế</div>
                    <h3 class="fw-bold text-success mb-1"><?= number_format((float) ($stats['total_revenue'] ?? 0)) ?>đ</h3>
                    <div class="small text-secondary">
                        Vé: <?= number_format((float) ($stats['ticket_revenue'] ?? 0)) ?>đ • Bắp nước: <?= number_format((float) ($stats['concession_revenue'] ?? 0)) ?>đ
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm p-3 bg-white h-100">
                    <div class="text-muted small fw-semibold">Phim đang chiếu</div>
                    <h3 class="fw-bold text-danger mb-1"><?= $nowShowingMoviesCount ?> / <?= count($movies) ?> phim</h3>
                    <div class="small text-secondary">
                        <a href="?route=admin&tab=movies" class="text-decoration-none">Quản lý danh sách phim →</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm p-3 bg-white h-100">
                    <div class="text-muted small fw-semibold">Suất chiếu đang mở bán</div>
                    <h3 class="fw-bold text-warning mb-1"><?= count($upcomingShows) ?> suất chiếu</h3>
                    <div class="small text-secondary">
                        Tại <?= count($rooms) ?> phòng chiếu • <a href="?route=admin&tab=shows" class="text-decoration-none">Xem lịch →</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm p-3 bg-white h-100">
                    <div class="text-muted small fw-semibold">Đơn đặt vé & Soát vé</div>
                    <h3 class="fw-bold text-primary mb-1"><?= (int) ($stats['confirmed_bookings'] ?? 0) ?> đơn thành công</h3>
                    <div class="small text-secondary">
                        Đã soát QR: <?= (int) ($stats['checked_in_tickets'] ?? 0) ?> / <?= (int) ($stats['total_tickets'] ?? 0) ?> vé
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm bg-white">
                    <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center py-3">
                        <span><i class="bi bi-calendar2-check text-primary me-1"></i> Suất Chiếu Đang Mở Bán Sắp Tới</span>
                        <a href="?route=admin&tab=shows" class="btn btn-sm btn-outline-primary">Quản lý suất chiếu</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0 align-middle small">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">Phim</th>
                                        <th>Phòng</th>
                                        <th>Giờ chiếu</th>
                                        <th>Giá vé</th>
                                        <th>Ghế trống</th>
                                        <th class="pe-3 text-end">Đặt vé</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($upcomingShows)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">
                                                Chưa có suất chiếu nào đang mở bán.
                                                <a href="?route=admin&tab=shows" class="fw-bold">Tạo suất chiếu ngay</a>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach (array_slice($upcomingShows, 0, 8) as $sh): ?>
                                            <tr>
                                                <td class="ps-3 fw-semibold"><?= htmlspecialchars($sh['title_vi'] ?: $sh['movie_title']) ?></td>
                                                <td><?= htmlspecialchars($sh['room_name']) ?></td>
                                                <td><span class="badge bg-light text-dark border"><?= date('H:i - d/m/Y', strtotime($sh['start_time'])) ?></span></td>
                                                <td class="text-danger fw-bold"><?= number_format((float) $sh['base_price']) ?>đ</td>
                                                <td><?= (int) $sh['available_seats'] ?> / <?= (int) $sh['total_seats'] ?></td>
                                                <td class="pe-3 text-end">
                                                    <a href="?route=booking/seats&show_id=<?= (int) $sh['id'] ?>" class="btn btn-sm btn-outline-primary">Sơ đồ ghế</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card border-0 shadow-sm bg-white">
                    <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center py-3">
                        <span><i class="bi bi-receipt text-success me-1"></i> Đơn Đặt Vé Mới Nhất</span>
                        <a href="?route=admin&tab=bookings" class="btn btn-sm btn-outline-success">Soát vé QR</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0 align-middle small">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">Mã đơn</th>
                                        <th>Khách hàng</th>
                                        <th>Tổng tiền</th>
                                        <th class="pe-3">Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($bookings)): ?>
                                        <tr><td colspan="4" class="text-center py-4 text-muted">Chưa có đơn đặt vé nào.</td></tr>
                                    <?php else: ?>
                                        <?php foreach (array_slice($bookings, 0, 8) as $b): ?>
                                            <tr>
                                                <td class="ps-3"><code><?= htmlspecialchars($b['booking_code']) ?></code></td>
                                                <td><?= htmlspecialchars($b['user_name']) ?><br><span class="text-muted"><?= htmlspecialchars($b['title_vi'] ?? $b['movie_title']) ?></span></td>
                                                <td class="text-danger fw-bold"><?= number_format((float) $b['total_amount']) ?>đ</td>
                                                <td class="pe-3">
                                                    <span class="badge <?= $b['status'] === 'confirmed' ? 'bg-success' : ($b['status'] === 'pending' ? 'bg-warning text-dark' : 'bg-secondary') ?>">
                                                        <?= htmlspecialchars($b['status']) ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php elseif ($tab === 'users'): ?>
        <div class="row g-4">
            <div class="col-md-5">
                <div class="card border-0 shadow-sm bg-white">
                    <div class="card-header bg-white fw-bold">Thông Tin Rạp Chiếu Phim</div>
                    <div class="card-body">
                        <form method="POST" action="?route=admin/cinema/save">
                            <input type="hidden" name="_token" value="<?= $csrfToken ?>">
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
                        <i class="bi bi-shield-lock-fill me-1"></i> Đổi Mật Khẩu Quản Trị Viên
                    </div>
                    <div class="card-body">
                        <form method="POST" action="?route=admin/password/change">
                            <input type="hidden" name="_token" value="<?= $csrfToken ?>">
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
                    <div class="card-header bg-white fw-bold">Danh Sách Tài Khoản Người Dùng</div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0 align-middle small">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">ID</th>
                                        <th>Họ tên / Email</th>
                                        <th>Vai trò</th>
                                        <th>Trạng thái</th>
                                        <th class="pe-3">Cập nhật quyền & TT</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $u): ?>
                                        <?php
                                        $userRoleCodes = array_filter(explode(',', (string) ($u['role_codes'] ?? '')));
                                        $primaryRole = in_array('admin', $userRoleCodes, true) ? 'admin' : (in_array('staff', $userRoleCodes, true) ? 'staff' : 'customer');
                                        ?>
                                        <tr>
                                            <td class="ps-3">#<?= (int) $u['id'] ?></td>
                                            <td><?= htmlspecialchars($u['name']) ?><br><span class="text-muted"><?= htmlspecialchars($u['email']) ?></span></td>
                                            <td><span class="badge bg-info text-dark"><?= htmlspecialchars($u['role_names'] ?: 'Khách hàng') ?></span></td>
                                            <td><span class="badge <?= $u['status'] === 'active' ? 'bg-success' : 'bg-danger' ?>"><?= htmlspecialchars($u['status']) ?></span></td>
                                            <td class="pe-3">
                                                <form method="POST" action="?route=admin/user/status" class="d-flex gap-1">
                                                    <input type="hidden" name="_token" value="<?= $csrfToken ?>">
                                                    <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
                                                    <select name="role" class="form-select form-select-sm" style="max-width: 115px;">
                                                        <option value="customer" <?= $primaryRole === 'customer' ? 'selected' : '' ?>>Khách hàng</option>
                                                        <option value="staff" <?= $primaryRole === 'staff' ? 'selected' : '' ?>>Nhân viên</option>
                                                        <option value="admin" <?= $primaryRole === 'admin' ? 'selected' : '' ?>>Admin</option>
                                                    </select>
                                                    <select name="status" class="form-select form-select-sm" style="max-width: 95px;">
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
        </div>

    <?php elseif ($tab === 'movies'): ?>
        <?php
        $editingMovie = $editMovie ?? null;
        $editGenreIds = $editingMovie['genre_ids'] ?? [];
        ?>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm bg-white">
                    <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
                        <span><?= $editingMovie ? 'Chỉnh Sửa Phim #' . (int) $editingMovie['id'] : 'Thêm Phim Mới' ?></span>
                        <?php if ($editingMovie): ?>
                            <a href="?route=admin&tab=movies" class="btn btn-sm btn-outline-secondary py-0">Hủy sửa</a>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="?route=admin/movie/save">
                            <input type="hidden" name="_token" value="<?= $csrfToken ?>">
                            <?php if ($editingMovie): ?>
                                <input type="hidden" name="movie_id" value="<?= (int) $editingMovie['id'] ?>">
                            <?php endif; ?>
                            <div class="mb-2">
                                <label class="form-label small">Tên phim (Gốc) *</label>
                                <input type="text" name="title" value="<?= htmlspecialchars($editingMovie['title'] ?? '') ?>" class="form-control form-control-sm" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Tên tiếng Việt</label>
                                <input type="text" name="title_vi" value="<?= htmlspecialchars($editingMovie['title_vi'] ?? '') ?>" class="form-control form-control-sm">
                            </div>
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <label class="form-label small">Thời lượng (phút)</label>
                                    <input type="number" name="runtime" value="<?= (int) ($editingMovie['runtime'] ?? 120) ?>" min="30" class="form-control form-control-sm">
                                </div>
                                <div class="col-6">
                                    <label class="form-label small">Giới hạn tuổi</label>
                                    <select name="age_rating" class="form-select form-select-sm">
                                        <?php $curAge = strtoupper($editingMovie['age_rating'] ?? 'T13'); ?>
                                        <?php foreach (['P', 'T13', 'T16', 'T18'] as $ageOpt): ?>
                                            <option value="<?= $ageOpt ?>" <?= $curAge === $ageOpt ? 'selected' : '' ?>><?= $ageOpt ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="row g-2 mb-2">
                                <div class="col-7">
                                    <label class="form-label small">Ngày khởi chiếu</label>
                                    <input type="date" name="release_date" value="<?= htmlspecialchars(!empty($editingMovie['release_date']) ? date('Y-m-d', strtotime($editingMovie['release_date'])) : date('Y-m-d')) ?>" class="form-control form-control-sm">
                                </div>
                                <div class="col-5">
                                    <label class="form-label small">Điểm IMDb</label>
                                    <input type="number" step="0.1" min="1" max="10" name="imdb_rating" value="<?= htmlspecialchars((string) ($editingMovie['imdb_rating'] ?? '8.0')) ?>" class="form-control form-control-sm">
                                </div>
                            </div>
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <label class="form-label small">Đạo diễn</label>
                                    <input type="text" name="director" value="<?= htmlspecialchars($editingMovie['director'] ?? '') ?>" class="form-control form-control-sm" placeholder="Tên đạo diễn">
                                </div>
                                <div class="col-6">
                                    <label class="form-label small">Diễn viên</label>
                                    <input type="text" name="cast_text" value="<?= htmlspecialchars($editingMovie['cast_text'] ?? '') ?>" class="form-control form-control-sm" placeholder="Diễn viên chính">
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Link ảnh Poster</label>
                                <input type="text" name="poster_path" list="posterSuggestions" value="<?= htmlspecialchars($editingMovie['poster_path'] ?? '') ?>" class="form-control form-control-sm" placeholder="https://... hoặc public/images/posters/...">
                                <datalist id="posterSuggestions">
                                    <option value="public/images/posters/dune-part-two.svg">
                                    <option value="public/images/posters/inside-out-2.svg">
                                    <option value="public/images/posters/avatar-fire-ash.svg">
                                    <option value="public/images/posters/spider-man.svg">
                                    <option value="public/images/posters/interstellar.svg">
                                    <option value="public/images/posters/kung-fu-panda.svg">
                                    <option value="public/images/posters/godzilla-kong.svg">
                                    <option value="public/images/posters/the-batman.svg">
                                    <option value="public/images/posters/moana-2.svg">
                                </datalist>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Link Trailer (YouTube Embed)</label>
                                <input type="text" name="trailer_url" value="<?= htmlspecialchars($editingMovie['trailer_url'] ?? '') ?>" class="form-control form-control-sm" placeholder="https://www.youtube.com/embed/...">
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Thể loại</label>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php foreach ($genres as $g): ?>
                                        <label class="small">
                                            <input type="checkbox" name="genre_ids[]" value="<?= (int) $g['id'] ?>" <?= in_array((int) $g['id'], $editGenreIds, true) ? 'checked' : '' ?>>
                                            <?= htmlspecialchars($g['name']) ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Trạng thái</label>
                                <?php $curStatus = $editingMovie['status'] ?? 'now_showing'; ?>
                                <select name="status" class="form-select form-select-sm">
                                    <option value="now_showing" <?= $curStatus === 'now_showing' ? 'selected' : '' ?>>Đang chiếu (now_showing)</option>
                                    <option value="coming_soon" <?= $curStatus === 'coming_soon' ? 'selected' : '' ?>>Sắp chiếu (coming_soon)</option>
                                    <option value="ended" <?= $curStatus === 'ended' ? 'selected' : '' ?>>Ngừng chiếu (ended)</option>
                                    <option value="hidden" <?= $curStatus === 'hidden' ? 'selected' : '' ?>>Ẩn (hidden)</option>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Tóm tắt nội dung</label>
                                <textarea name="overview" rows="2" class="form-control form-control-sm"><?= htmlspecialchars($editingMovie['overview'] ?? '') ?></textarea>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="auto_create_shows" value="1" id="autoCreateShowsCheck" checked>
                                <label class="form-check-label small" for="autoCreateShowsCheck">
                                    Tự động tạo suất chiếu mở bán nếu phim Đang chiếu
                                </label>
                            </div>
                            <button type="submit" class="btn btn-beta-buy btn-sm w-100">
                                <?= $editingMovie ? 'Lưu Cập Nhật Phim' : 'Thêm Phim Mới' ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card border-0 shadow-sm bg-white">
                    <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
                        <span>Danh Sách Phim (<?= count($movies) ?> phim)</span>
                        <a href="?route=admin&tab=shows" class="btn btn-sm btn-outline-primary">Xem lịch chiếu</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0 align-middle small">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">ID</th>
                                        <th>Phim</th>
                                        <th>Thời lượng</th>
                                        <th>Suất mở bán</th>
                                        <th>Đổi trạng thái</th>
                                        <th class="pe-3 text-end">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $futureShowsByMovieCount = [];
                                    foreach ($upcomingShows as $ush) {
                                        $mid = (int) $ush['movie_id'];
                                        $futureShowsByMovieCount[$mid] = ($futureShowsByMovieCount[$mid] ?? 0) + 1;
                                    }
                                    ?>
                                    <?php foreach ($movies as $m): ?>
                                        <?php $mOpenShows = $futureShowsByMovieCount[(int) $m['id']] ?? 0; ?>
                                        <tr>
                                            <td class="ps-3">#<?= (int) $m['id'] ?></td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <img src="<?= htmlspecialchars($m['poster_path'] ?: 'public/images/posters/dune-part-two.svg') ?>"
                                                         alt="Poster"
                                                         style="width:36px;height:52px;object-fit:cover;border-radius:5px;"
                                                         onerror="this.onerror=null;this.src='public/images/posters/dune-part-two.svg';">
                                                    <div>
                                                        <div class="fw-semibold"><?= htmlspecialchars($m['title']) ?></div>
                                                        <div class="text-primary"><?= htmlspecialchars($m['title_vi'] ?? '') ?></div>
                                                        <div class="text-muted" style="font-size:11px;"><?= htmlspecialchars($m['genre_names'] ?? 'Chưa gắn thể loại') ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= (int) $m['runtime'] ?>p<br><span class="badge bg-warning text-dark"><?= htmlspecialchars($m['age_rating'] ?? 'P') ?></span></td>
                                            <td>
                                                <?php if ($mOpenShows > 0): ?>
                                                    <span class="badge bg-success"><?= $mOpenShows ?> suất</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">0 suất</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <form method="POST" action="?route=admin/movie/status" class="d-flex gap-1">
                                                    <input type="hidden" name="_token" value="<?= $csrfToken ?>">
                                                    <input type="hidden" name="movie_id" value="<?= (int) $m['id'] ?>">
                                                    <select name="status" class="form-select form-select-sm" style="max-width:130px;">
                                                        <?php foreach (['now_showing' => 'Đang chiếu', 'coming_soon' => 'Sắp chiếu', 'ended' => 'Ngừng chiếu', 'hidden' => 'Ẩn'] as $st => $label): ?>
                                                            <option value="<?= $st ?>" <?= $m['status'] === $st ? 'selected' : '' ?>><?= $label ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <button class="btn btn-sm btn-outline-primary">Lưu</button>
                                                </form>
                                            </td>
                                            <td class="pe-3 text-end">
                                                <div class="d-inline-flex gap-1">
                                                    <a href="?route=admin&tab=movies&edit_id=<?= (int) $m['id'] ?>" class="btn btn-sm btn-outline-secondary">Sửa</a>
                                                    <form method="POST" action="?route=admin/movie/delete" onsubmit="return confirm('Bạn có chắc muốn xóa bộ phim này?');">
                                                        <input type="hidden" name="_token" value="<?= $csrfToken ?>">
                                                        <input type="hidden" name="movie_id" value="<?= (int) $m['id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">Xóa</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
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
                            <input type="hidden" name="_token" value="<?= $csrfToken ?>">
                            <div class="mb-2">
                                <label class="form-label small">Tên phòng chiếu *</label>
                                <input type="text" name="name" class="form-control form-control-sm" required placeholder="Phòng Chiếu 03 - Standard">
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Loại phòng</label>
                                <select name="room_type" class="form-select form-select-sm">
                                    <option value="standard">Standard (Tiêu chuẩn)</option>
                                    <option value="vip">VIP</option>
                                    <option value="imax">IMAX</option>
                                    <option value="four_d">4DX</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small">Mô tả</label>
                                <input type="text" name="description" class="form-control form-control-sm" placeholder="Âm thanh Dolby Atmos...">
                            </div>
                            <button type="submit" class="btn btn-beta-buy btn-sm w-100">Tạo Phòng & Sinh 27 Ghế Chuẩn</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card border-0 shadow-sm bg-white">
                    <div class="card-header bg-white fw-bold">Danh Sách Phòng Chiếu</div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0 align-middle small">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">ID</th>
                                        <th>Tên phòng</th>
                                        <th>Loại & Trạng thái</th>
                                        <th>Sức chứa</th>
                                        <th class="pe-3 text-end">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rooms as $r): ?>
                                        <tr>
                                            <td class="ps-3">#<?= (int) $r['id'] ?></td>
                                            <td>
                                                <div class="fw-bold"><?= htmlspecialchars($r['name']) ?></div>
                                                <div class="text-muted small"><?= htmlspecialchars($r['description'] ?? '') ?></div>
                                            </td>
                                            <td>
                                                <form method="POST" action="?route=admin/room/update" class="d-flex gap-1 align-items-center">
                                                    <input type="hidden" name="_token" value="<?= $csrfToken ?>">
                                                    <input type="hidden" name="room_id" value="<?= (int) $r['id'] ?>">
                                                    <select name="room_type" class="form-select form-select-sm" style="max-width:95px;">
                                                        <?php foreach (['standard', 'vip', 'imax', 'four_d'] as $rt): ?>
                                                            <option value="<?= $rt ?>" <?= $r['room_type'] === $rt ? 'selected' : '' ?>><?= strtoupper($rt) ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <select name="status" class="form-select form-select-sm" style="max-width:115px;">
                                                        <option value="active" <?= ($r['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Hoạt động</option>
                                                        <option value="maintenance" <?= ($r['status'] ?? '') === 'maintenance' ? 'selected' : '' ?>>Bảo trì</option>
                                                    </select>
                                                    <button class="btn btn-sm btn-outline-primary">Lưu</button>
                                                </form>
                                            </td>
                                            <td><span class="badge bg-light text-dark border"><?= (int) $r['capacity'] ?> ghế</span></td>
                                            <td class="pe-3 text-end">
                                                <div class="d-inline-flex gap-1">
                                                    <form method="POST" action="?route=admin/room/generate-seats">
                                                        <input type="hidden" name="_token" value="<?= $csrfToken ?>">
                                                        <input type="hidden" name="room_id" value="<?= (int) $r['id'] ?>">
                                                        <button class="btn btn-sm btn-outline-primary">Đồng bộ 27 ghế (A1..E3)</button>
                                                    </form>
                                                    <form method="POST" action="?route=admin/room/delete" onsubmit="return confirm('Xóa phòng chiếu này?');">
                                                        <input type="hidden" name="_token" value="<?= $csrfToken ?>">
                                                        <input type="hidden" name="room_id" value="<?= (int) $r['id'] ?>">
                                                        <button class="btn btn-sm btn-outline-danger">Xóa</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php elseif ($tab === 'shows'): ?>
        <?php $editingShow = $editShow ?? null; ?>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm bg-white">
                    <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
                        <span><?= $editingShow ? 'Cập Nhật Suất Chiếu #' . (int) $editingShow['id'] : 'Tạo Suất Chiếu Mới' ?></span>
                        <?php if ($editingShow): ?>
                            <a href="?route=admin&tab=shows" class="btn btn-sm btn-outline-secondary py-0">Hủy sửa</a>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="?route=<?= $editingShow ? 'admin/show/update' : 'admin/show/save' ?>">
                            <input type="hidden" name="_token" value="<?= $csrfToken ?>">
                            <?php if ($editingShow): ?>
                                <input type="hidden" name="show_id" value="<?= (int) $editingShow['id'] ?>">
                            <?php endif; ?>
                            <div class="mb-2">
                                <label class="form-label small">Chọn Phim *</label>
                                <select name="movie_id" class="form-select form-select-sm" required>
                                    <?php foreach ($movies as $m): ?>
                                        <option value="<?= (int) $m['id'] ?>" <?= ((int) ($editingShow['movie_id'] ?? 0) === (int) $m['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($m['title_vi'] ?: $m['title']) ?> (<?= (int) $m['runtime'] ?>p)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Chọn Phòng Chiếu *</label>
                                <select name="room_id" class="form-select form-select-sm" required>
                                    <?php foreach ($rooms as $r): ?>
                                        <option value="<?= (int) $r['id'] ?>" <?= ((int) ($editingShow['room_id'] ?? 0) === (int) $r['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($r['name']) ?> (<?= (int) $r['capacity'] ?> ghế)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Thời gian bắt đầu *</label>
                                <?php
                                $defaultStartVal = date('Y-m-d\TH:i', strtotime('+2 hours'));
                                if (!empty($editingShow['start_time'])) {
                                    $editTs = strtotime($editingShow['start_time']);
                                    $defaultStartVal = date('Y-m-d\TH:i', $editTs > $nowTs ? $editTs : strtotime('+2 hours'));
                                }
                                ?>
                                <input type="datetime-local" name="start_time" value="<?= $defaultStartVal ?>" class="form-control form-control-sm" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Giá vé cơ bản (VNĐ) *</label>
                                <input type="number" name="base_price" value="<?= (int) ($editingShow['base_price'] ?? 65000) ?>" step="5000" min="10000" class="form-control form-control-sm" required>
                            </div>
                            <?php if ($editingShow): ?>
                                <div class="mb-3">
                                    <label class="form-label small">Trạng thái suất chiếu</label>
                                    <select name="status" class="form-select form-select-sm">
                                        <?php foreach (['selling' => 'Đang mở bán (selling)', 'scheduled' => 'Lên lịch (scheduled)', 'closed' => 'Đóng bán (closed)', 'cancelled' => 'Đã hủy (cancelled)'] as $st => $lbl): ?>
                                            <option value="<?= $st ?>" <?= ($editingShow['status'] ?? '') === $st ? 'selected' : '' ?>><?= $lbl ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php else: ?>
                                <div class="mb-3"></div>
                            <?php endif; ?>
                            <button type="submit" class="btn btn-beta-buy btn-sm w-100">
                                <?= $editingShow ? 'Lưu Cập Nhật Suất Chiếu' : 'Tạo Suất Chiếu & Mở Bán Ghế' ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card border-0 shadow-sm bg-white">
                    <div class="card-header bg-white fw-bold d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <span>Danh Sách Suất Chiếu (<?= count($upcomingShows) ?> suất đang mở bán / <?= count($shows) ?> tổng)</span>
                        <form method="POST" action="?route=admin/show/auto-generate" class="m-0">
                            <input type="hidden" name="_token" value="<?= $csrfToken ?>">
                            <button type="submit" class="btn btn-sm btn-outline-success fw-semibold">
                                <i class="bi bi-magic me-1"></i>Tạo tự động lịch chiếu cho phim Đang chiếu
                            </button>
                        </form>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0 align-middle small">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">ID</th>
                                        <th>Phim</th>
                                        <th>Phòng</th>
                                        <th>Giờ chiếu</th>
                                        <th>Giá gốc</th>
                                        <th>Ghế trống</th>
                                        <th>Trạng thái</th>
                                        <th class="pe-3 text-end">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($shows as $sh): ?>
                                        <?php
                                        $shTs = strtotime($sh['start_time']);
                                        $isFuture = $shTs > $nowTs;
                                        $isOpen = in_array($sh['status'], ['scheduled', 'selling'], true) && $isFuture;
                                        ?>
                                        <tr>
                                            <td class="ps-3">#<?= (int) $sh['id'] ?></td>
                                            <td class="fw-semibold"><?= htmlspecialchars($sh['title_vi'] ?: $sh['movie_title']) ?></td>
                                            <td><?= htmlspecialchars($sh['room_name']) ?></td>
                                            <td><?= date('H:i d/m/Y', $shTs) ?></td>
                                            <td class="text-danger fw-bold"><?= number_format((float) $sh['base_price']) ?>đ</td>
                                            <td><?= (int) $sh['available_seats'] ?> / <?= (int) $sh['total_seats'] ?></td>
                                            <td>
                                                <?php if ($isOpen): ?>
                                                    <span class="badge bg-success">Đang mở bán</span>
                                                <?php elseif ($sh['status'] === 'cancelled'): ?>
                                                    <span class="badge bg-danger">Đã hủy</span>
                                                <?php elseif (!$isFuture): ?>
                                                    <span class="badge bg-secondary">Đã qua giờ</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning text-dark"><?= htmlspecialchars($sh['status']) ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="pe-3 text-end">
                                                <div class="d-inline-flex gap-1">
                                                    <?php if ($isOpen): ?>
                                                        <a href="?route=booking/seats&show_id=<?= (int) $sh['id'] ?>" class="btn btn-sm btn-outline-primary">Sơ đồ ghế</a>
                                                    <?php endif; ?>
                                                    <a href="?route=admin&tab=shows&edit_show_id=<?= (int) $sh['id'] ?>" class="btn btn-sm btn-outline-secondary">Sửa</a>
                                                    <form method="POST" action="?route=admin/show/delete" onsubmit="return confirm('Xóa/Hủy suất chiếu này?');">
                                                        <input type="hidden" name="_token" value="<?= $csrfToken ?>">
                                                        <input type="hidden" name="show_id" value="<?= (int) $sh['id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">Xóa</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
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

    <?php elseif ($tab === 'incidents'): ?>
        <?php
        $incidentCategories = ['ticket' => 'Vé / check-in', 'service' => 'Dịch vụ', 'safety' => 'An toàn', 'other' => 'Khác'];
        $incidentStatuses = ['open' => 'Mới tiếp nhận', 'in_review' => 'Đang xử lý', 'resolved' => 'Đã giải quyết', 'rejected' => 'Từ chối'];
        ?>
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h4 class="fw-bold mb-1"><i class="bi bi-chat-square-text me-2" style="color:var(--beta-blue)"></i>Quản lý sự cố & khiếu nại vé</h4>
                <div class="text-muted small">Theo dõi phản ánh gắn với vé, phân công nhân viên và lưu phản hồi.</div>
            </div>
            <span class="badge rounded-pill bg-light text-dark border"><?= count($incidentReports ?? []) ?> yêu cầu</span>
        </div>
        <?php if (empty($incidentReports)): ?>
            <div class="card p-4 text-center text-muted">Chưa có yêu cầu khiếu nại được phân công hoặc ghi nhận.</div>
        <?php endif; ?>
        <?php foreach ($incidentReports as $incident): ?>
            <div class="card mb-3">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <strong>#<?= (int) $incident['id'] ?> · <?= htmlspecialchars($incident['title'], ENT_QUOTES, 'UTF-8') ?></strong>
                        <div class="small text-muted">
                            <?= htmlspecialchars($incidentCategories[$incident['category']] ?? $incident['category'], ENT_QUOTES, 'UTF-8') ?>
                            <?php if (!empty($incident['ticket_code'])): ?> · Vé <code><?= htmlspecialchars($incident['ticket_code'], ENT_QUOTES, 'UTF-8') ?></code><?php endif; ?>
                            <?php if (!empty($incident['movie_title'])): ?> · <?= htmlspecialchars($incident['movie_title'], ENT_QUOTES, 'UTF-8') ?><?php endif; ?>
                        </div>
                    </div>
                    <span class="badge <?= $incident['status'] === 'resolved' ? 'bg-success' : ($incident['status'] === 'rejected' ? 'bg-secondary' : 'bg-warning text-dark') ?>">
                        <?= htmlspecialchars($incidentStatuses[$incident['status']] ?? $incident['status'], ENT_QUOTES, 'UTF-8') ?>
                    </span>
                </div>
                <div class="card-body">
                    <div class="row small text-muted mb-2">
                        <div class="col-md-6">Người gửi: <?= htmlspecialchars($incident['reporter_name'], ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="col-md-6">Phụ trách: <?= htmlspecialchars($incident['assignee_name'] ?? 'Chưa phân công', ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="col-12">Gửi lúc: <?= date('d/m/Y H:i', strtotime($incident['created_at'])) ?></div>
                    </div>
                    <p class="mb-3"><?= nl2br(htmlspecialchars($incident['description'], ENT_QUOTES, 'UTF-8')) ?></p>
                    <?php if (!empty($isAdmin)): ?>
                        <form method="POST" action="?route=admin/incidents/assign" class="row g-2 align-items-end border-top pt-3 mb-3">
                            <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="report_id" value="<?= (int) $incident['id'] ?>">
                            <div class="col-md-8">
                                <label class="form-label small">Phân công nhân viên</label>
                                <select name="staff_id" class="form-select form-select-sm" required>
                                    <option value="">Chọn nhân viên</option>
                                    <?php foreach ($incidentStaff as $staff): ?>
                                        <option value="<?= (int) $staff['id'] ?>" <?= (int) $incident['assigned_to'] === (int) $staff['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($staff['name'], ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4"><button class="btn btn-sm btn-outline-primary w-100">Lưu phân công</button></div>
                        </form>
                    <?php endif; ?>
                    <form method="POST" action="?route=admin/incidents/update" class="row g-2 border-top pt-3">
                        <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="report_id" value="<?= (int) $incident['id'] ?>">
                        <div class="col-md-3">
                            <label class="form-label small">Trạng thái</label>
                            <select name="status" class="form-select form-select-sm" required>
                                <?php foreach ($incidentStatuses as $value => $label): ?>
                                    <?php if (!empty($isAdmin) || in_array($value, ['in_review', 'resolved'], true)): ?>
                                        <option value="<?= $value ?>" <?= $incident['status'] === $value ? 'selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label small">Phản hồi / hướng xử lý</label>
                            <textarea name="resolution" rows="2" maxlength="5000" class="form-control form-control-sm"><?= htmlspecialchars($incident['resolution'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                        </div>
                        <div class="col-md-2 d-flex align-items-end"><button class="btn btn-sm btn-beta-buy w-100">Cập nhật</button></div>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>

    <?php elseif ($tab === 'shifts'): ?>
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h4 class="fw-bold mb-1"><i class="bi bi-calendar2-check me-2" style="color:var(--beta-blue)"></i>Quản lý ca trực & phân công nhân viên</h4>
                <div class="text-muted small">Mỗi nhân viên không thể nhận hai ca có thời gian chồng lấn.</div>
            </div>
            <span class="badge rounded-pill bg-light text-dark border"><?= count($shifts ?? []) ?> ca trực</span>
        </div>
        <?php if (!empty($isAdmin)): ?>
            <div class="card mb-4">
                <div class="card-header fw-bold"><i class="bi bi-person-plus me-1"></i>Tạo ca và phân công nhân viên</div>
                <div class="card-body">
                    <form method="POST" action="?route=admin/shifts/create" class="row g-3">
                        <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        <div class="col-md-4">
                            <label class="form-label small">Nhân viên *</label>
                            <select name="staff_id" class="form-select form-select-sm" required>
                                <option value="">Chọn nhân viên</option>
                                <?php foreach ($shiftStaff as $staff): ?>
                                    <option value="<?= (int) $staff['id'] ?>"><?= htmlspecialchars($staff['name'], ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Nhiệm vụ *</label>
                            <input type="text" name="duty" maxlength="80" class="form-control form-control-sm" required placeholder="Soát vé, quầy dịch vụ...">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Phòng (không bắt buộc)</label>
                            <select name="room_id" class="form-select form-select-sm">
                                <option value="">Khu vực chung</option>
                                <?php foreach ($shiftRooms as $room): ?>
                                    <option value="<?= (int) $room['id'] ?>"><?= htmlspecialchars($room['name'], ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4"><label class="form-label small">Bắt đầu *</label><input type="datetime-local" name="starts_at" min="<?= date('Y-m-d\TH:i') ?>" class="form-control form-control-sm" required></div>
                        <div class="col-md-4"><label class="form-label small">Kết thúc *</label><input type="datetime-local" name="ends_at" class="form-control form-control-sm" required></div>
                        <div class="col-md-4"><label class="form-label small">Ghi chú</label><input type="text" name="notes" maxlength="1000" class="form-control form-control-sm" placeholder="Thông tin bàn giao"></div>
                        <div class="col-12"><button class="btn btn-beta-buy btn-sm"><i class="bi bi-calendar-plus me-1"></i>Lưu ca trực</button></div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
        <div class="card">
            <div class="card-header fw-bold">Lịch ca trực</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>Nhân viên</th><th>Nhiệm vụ / Phòng</th><th>Thời gian</th><th>Ghi chú</th><th>Trạng thái</th><th>Thao tác</th></tr></thead>
                    <tbody>
                        <?php foreach ($shifts as $shift): ?>
                            <tr>
                                <td class="fw-semibold"><?= htmlspecialchars($shift['staff_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($shift['duty'], ENT_QUOTES, 'UTF-8') ?><br><span class="small text-muted"><?= htmlspecialchars($shift['room_name'] ?? 'Khu vực chung', ENT_QUOTES, 'UTF-8') ?></span></td>
                                <td class="small"><?= date('d/m/Y H:i', strtotime($shift['starts_at'])) ?><br>đến <?= date('d/m/Y H:i', strtotime($shift['ends_at'])) ?></td>
                                <td class="small"><?= htmlspecialchars($shift['notes'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><span class="badge <?= $shift['status'] === 'completed' ? 'bg-success' : ($shift['status'] === 'cancelled' ? 'bg-secondary' : 'bg-primary') ?>"><?= $shift['status'] === 'scheduled' ? 'Đã xếp lịch' : ($shift['status'] === 'completed' ? 'Hoàn thành' : 'Đã hủy') ?></span></td>
                                <td>
                                    <?php if ($shift['status'] === 'scheduled' && (!empty($isAdmin) || (int) $shift['staff_id'] === (int) ($_SESSION['user']['id'] ?? 0))): ?>
                                        <form method="POST" action="?route=admin/shifts/status" class="d-flex gap-1">
                                            <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="shift_id" value="<?= (int) $shift['id'] ?>">
                                            <?php if (!empty($isAdmin)): ?>
                                                <button name="status" value="cancelled" class="btn btn-sm btn-outline-danger">Hủy</button>
                                            <?php endif; ?>
                                            <button name="status" value="completed" class="btn btn-sm btn-outline-success">Hoàn thành</button>
                                        </form>
                                    <?php else: ?>—<?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($shifts)): ?><tr><td colspan="6" class="text-center text-muted py-4">Chưa có ca trực nào.</td></tr><?php endif; ?>
                    </tbody>
                </table>
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
        <?php if (!empty($scanAlerts)): ?>
            <div class="card border-warning mb-3">
                <div class="card-header bg-warning-subtle fw-bold text-dark">
                    <i class="bi bi-shield-exclamation me-1"></i>Cảnh báo bất thường khi soát vé
                    <span class="badge bg-warning text-dark ms-1"><?= count($scanAlerts) ?></span>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead><tr><th>Thời điểm</th><th>Mã vé</th><th>Nhân viên</th><th>Dấu hiệu</th><th>Chi tiết</th></tr></thead>
                        <tbody>
                            <?php foreach ($scanAlerts as $alert): ?>
                                <tr>
                                    <td class="small"><?= date('d/m/Y H:i:s', strtotime($alert['scanned_at'])) ?></td>
                                    <td><code><?= htmlspecialchars($alert['ticket_code'], ENT_QUOTES, 'UTF-8') ?></code></td>
                                    <td><?= htmlspecialchars($alert['staff_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><span class="badge bg-danger"><?= htmlspecialchars($alert['anomaly_type'], ENT_QUOTES, 'UTF-8') ?></span></td>
                                    <td class="small"><?= htmlspecialchars($alert['details'], ENT_QUOTES, 'UTF-8') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm bg-white">
                    <div class="card-header bg-success text-white fw-bold">
                        <i class="bi bi-qr-code-scan me-1"></i> Soát Vé Tại Rạp (Check-in Ticket)
                    </div>
                    <div class="card-body">
                        <form method="POST" action="?route=admin/ticket/checkin">
                            <input type="hidden" name="_token" value="<?= $csrfToken ?>">
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
                    <div class="card-header bg-white fw-bold">Danh Sách Đơn Đặt Vé & Mã Vé QR</div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0 align-middle small">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">Mã đơn</th>
                                        <th>Khách hàng</th>
                                        <th>Phim / Phòng / Suất</th>
                                        <th>Ghế & Mã vé QR</th>
                                        <th>Tổng tiền</th>
                                        <th class="pe-3">Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($bookings as $b): ?>
                                        <tr>
                                            <td class="ps-3">
                                                <code><?= htmlspecialchars($b['booking_code']) ?></code>
                                                <div class="text-muted" style="font-size:11px;"><?= date('H:i d/m/Y', strtotime($b['booking_time'])) ?></div>
                                            </td>
                                            <td>
                                                <div class="fw-semibold"><?= htmlspecialchars($b['user_name']) ?></div>
                                                <div class="text-muted" style="font-size:11px;"><?= htmlspecialchars($b['user_email'] ?? '') ?></div>
                                            </td>
                                            <td>
                                                <div class="fw-semibold"><?= htmlspecialchars($b['title_vi'] ?? $b['movie_title']) ?></div>
                                                <div class="text-muted"><?= htmlspecialchars($b['room_name']) ?> • <?= date('H:i d/m/Y', strtotime($b['start_time'])) ?></div>
                                            </td>
                                            <td>
                                                <?php if (empty($b['tickets'])): ?>
                                                    <span class="text-muted">Chưa có vé</span>
                                                <?php else: ?>
                                                    <div class="d-flex flex-column gap-1">
                                                        <?php foreach ($b['tickets'] as $tk): ?>
                                                            <div class="d-flex align-items-center gap-1 flex-wrap">
                                                                <span class="badge bg-light text-dark border">Ghế <?= htmlspecialchars($tk['seat_code']) ?></span>
                                                                <?php if (!empty($tk['ticket_code'])): ?>
                                                                    <code><?= htmlspecialchars($tk['ticket_code']) ?></code>
                                                                    <?php if ($tk['ticket_status'] === 'used'): ?>
                                                                        <span class="badge bg-secondary">Đã soát</span>
                                                                    <?php elseif ($tk['ticket_status'] === 'valid'): ?>
                                                                        <form method="POST" action="?route=admin/ticket/checkin" class="d-inline">
                                                                            <input type="hidden" name="_token" value="<?= $csrfToken ?>">
                                                                            <input type="hidden" name="ticket_code" value="<?= htmlspecialchars($tk['ticket_code']) ?>">
                                                                            <button type="submit" class="btn btn-success btn-sm py-0 px-2" style="font-size:11px;">Soát vé</button>
                                                                        </form>
                                                                    <?php endif; ?>
                                                                <?php endif; ?>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-danger fw-bold"><?= number_format((float) $b['total_amount']) ?>đ</td>
                                            <td class="pe-3">
                                                <span class="badge <?= $b['status'] === 'confirmed' ? 'bg-success' : ($b['status'] === 'pending' ? 'bg-warning text-dark' : 'bg-secondary') ?>">
                                                    <?= htmlspecialchars($b['status']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php elseif ($tab === 'concessions'): ?>
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm bg-white mb-3">
                    <div class="card-header bg-white fw-bold">Thêm Combo Bắp Nước</div>
                    <div class="card-body">
                        <form method="POST" action="?route=admin/concession/save" class="row g-2">
                            <input type="hidden" name="_token" value="<?= $csrfToken ?>">
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
                <table class="table table-bordered bg-white table-sm small align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Tên sản phẩm</th>
                            <th>Giá</th>
                            <th>Tồn kho</th>
                            <th class="text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($concessions as $c): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars($c['name']) ?></div>
                                    <div class="text-muted" style="font-size:11px;"><?= htmlspecialchars($c['description'] ?? '') ?></div>
                                </td>
                                <td class="text-danger fw-bold"><?= number_format((float) $c['price']) ?>đ</td>
                                <td><?= (int) $c['stock'] ?></td>
                                <td class="text-end">
                                    <form method="POST" action="?route=admin/concession/delete" onsubmit="return confirm('Xóa sản phẩm bắp nước này?');">
                                        <input type="hidden" name="_token" value="<?= $csrfToken ?>">
                                        <input type="hidden" name="concession_id" value="<?= (int) $c['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger py-0">Xóa</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="col-md-6">
                <div class="card border-0 shadow-sm bg-white mb-3">
                    <div class="card-header bg-white fw-bold">Thêm / Cập Nhật Mã Giảm Giá</div>
                    <div class="card-body">
                        <form method="POST" action="?route=admin/coupon/save" class="row g-2">
                            <input type="hidden" name="_token" value="<?= $csrfToken ?>">
                            <div class="col-md-4">
                                <input type="text" name="code" class="form-control form-control-sm" placeholder="Mã (VD: BETA50)" required>
                            </div>
                            <div class="col-md-8">
                                <input type="text" name="name" class="form-control form-control-sm" placeholder="Tên chương trình *" required>
                            </div>
                            <div class="col-md-4">
                                <select name="discount_type" class="form-select form-select-sm">
                                    <option value="fixed">Giảm tiền (VNĐ)</option>
                                    <option value="percent">Giảm theo %</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <input type="number" name="discount_value" class="form-control form-control-sm" placeholder="Giá trị giảm" value="25000" required>
                            </div>
                            <div class="col-md-4">
                                <input type="number" name="min_order_amount" class="form-control form-control-sm" placeholder="Đơn tối thiểu" value="100000">
                            </div>
                            <div class="col-md-8">
                                <input type="number" name="usage_limit" class="form-control form-control-sm" placeholder="Giới hạn lượt dùng" value="100">
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-beta-buy btn-sm w-100">Lưu Mã</button>
                            </div>
                        </form>
                    </div>
                </div>
                <table class="table table-bordered bg-white table-sm small align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Mã</th>
                            <th>Tên chương trình</th>
                            <th>Giảm</th>
                            <th>Đã dùng</th>
                            <th class="text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($coupons as $cp): ?>
                            <tr>
                                <td><code><?= htmlspecialchars($cp['code']) ?></code></td>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars($cp['name']) ?></div>
                                    <div class="text-muted" style="font-size:11px;">Đơn tối thiểu: <?= number_format((float) ($cp['min_order_amount'] ?? 0)) ?>đ</div>
                                </td>
                                <td class="text-success fw-bold"><?= $cp['discount_type'] === 'percent' ? (float) $cp['discount_value'] . '%' : number_format((float) $cp['discount_value']) . 'đ' ?></td>
                                <td><?= (int) $cp['used_count'] ?> / <?= htmlspecialchars((string) ($cp['usage_limit'] ?? '∞')) ?></td>
                                <td class="text-end">
                                    <form method="POST" action="?route=admin/coupon/delete" onsubmit="return confirm('Xóa mã giảm giá này?');">
                                        <input type="hidden" name="_token" value="<?= $csrfToken ?>">
                                        <input type="hidden" name="coupon_id" value="<?= (int) $cp['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger py-0">Xóa</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>
