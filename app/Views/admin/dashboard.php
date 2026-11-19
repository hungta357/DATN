<?php
$csrfToken = htmlspecialchars($_SESSION['_token'] ?? '', ENT_QUOTES, 'UTF-8');
$nowTs = time();
$upcomingShows = array_values(array_filter($shows ?? [], static function (array $sh) use ($nowTs): bool {
    return in_array($sh['status'] ?? '', ['scheduled', 'selling'], true) && strtotime($sh['start_time']) > $nowTs;
}));
$nowShowingMoviesCount = count(array_filter($movies ?? [], static fn(array $m): bool => ($m['status'] ?? '') === 'now_showing'));
?>
<div>
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

    <?php elseif ($tab === 'bookings'): ?>
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
