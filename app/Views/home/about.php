<div class="container py-4">
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="row g-0">
            <div class="col-lg-5" style="background: url('https://images.unsplash.com/photo-1517604931442-7e0c8ed2963c?w=900') center/cover no-repeat; min-height: 300px;">
            </div>
            <div class="col-lg-7 p-4 p-md-5">
                <span class="badge bg-primary mb-2 px-3 py-2 oswald-font">MÔ HÌNH 1 RẠP DUY NHẤT - NHIỀU PHÒNG CHIẾU</span>
                <?php if (!empty($cinemaInfo['logo'])): ?><img src="<?= htmlspecialchars($cinemaInfo['logo']) ?>" alt="Logo <?= htmlspecialchars($cinemaInfo['name']) ?>" style="max-width:150px;max-height:75px;object-fit:contain" class="mb-3"><?php endif; ?>
                <h2 class="oswald-font fw-bold text-uppercase mb-3" style="color: var(--beta-blue);">
                    GIỚI THIỆU RẠP <?= htmlspecialchars(strtoupper($cinemaInfo['name'])) ?>
                </h2>
                <p class="text-secondary">
                    <?= nl2br(htmlspecialchars($cinemaInfo['description'] ?: 'Rạp chiếu phim hiện đại đạt chuẩn Hollywood với hệ thống phòng chiếu IMAX, VIP, Standard âm thanh vòm sống động và mức giá vé hợp lý dành cho giới trẻ, học sinh - sinh viên và gia đình.')) ?>
                </p>

                <div class="p-3 rounded-3 bg-light border mb-4">
                    <div class="mb-2"><i class="bi bi-geo-alt-fill text-danger me-2"></i><strong>Địa chỉ rạp:</strong> <?= htmlspecialchars($cinemaInfo['address']) ?></div>
                    <div class="mb-2"><i class="bi bi-telephone-fill text-primary me-2"></i><strong>Hotline đặt vé:</strong> <?= htmlspecialchars($cinemaInfo['phone'] ?: '1900 636807') ?></div>
                    <div class="mb-2"><i class="bi bi-envelope-fill text-warning me-2"></i><strong>Email liên hệ:</strong> <?= htmlspecialchars($cinemaInfo['email'] ?: 'Chưa cập nhật') ?></div>
                    <div class="mb-2"><i class="bi bi-clock-fill text-success me-2"></i><strong>Giờ hoạt động:</strong> <?= htmlspecialchars($cinemaInfo['opening_hours'] ?? 'Theo lịch chiếu') ?></div>
                    <?php if (!empty($cinemaInfo['website'])): ?><div class="mb-2"><i class="bi bi-globe me-2"></i><strong>Website:</strong> <a href="<?= htmlspecialchars($cinemaInfo['website']) ?>" target="_blank" rel="noopener"> <?= htmlspecialchars($cinemaInfo['website']) ?></a></div><?php endif; ?>
                </div>

                <?php if (!empty($cinemaInfo['facilities'])): ?><div class="mb-4"><h6 class="fw-bold">TIỆN ÍCH TẠI RẠP</h6><p class="text-secondary mb-0"><?= nl2br(htmlspecialchars($cinemaInfo['facilities'])) ?></p></div><?php endif; ?>
                <?php if (!empty($cinemaInfo['map_url'])): ?><a class="btn btn-outline-primary mb-4" href="<?= htmlspecialchars($cinemaInfo['map_url']) ?>" target="_blank" rel="noopener"><i class="bi bi-map me-1"></i> Xem bản đồ đường đi</a><?php endif; ?>

                <h6 class="oswald-font fw-bold text-uppercase mb-2" style="color: var(--beta-orange);">Các Phòng Chiếu Đang Hoạt Động Tại Rạp:</h6>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($rooms as $r): ?>
                        <span class="badge bg-dark px-3 py-2">
                            <i class="bi bi-tv me-1 text-info"></i> <?= htmlspecialchars($r['name']) ?> (<?= (int) $r['capacity'] ?> ghế)
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <?php
    $stats = $cinemaReviewStats ?? [];
    $cinemaReviews = $cinemaReviews ?? [];
    $userCinemaReview = $userCinemaReview ?? null;
    $cinemaReviewPagination = $cinemaReviewPagination ?? ['page' => 1, 'total_pages' => 1, 'total' => 0];
    $currentUserId = (int) ($_SESSION['user']['id'] ?? 0);

    $scoreOptions = static function (int $selected = 10): void {
        for ($i = 10; $i >= 1; $i--) {
            $sel = $i === $selected ? ' selected' : '';
            echo "<option value=\"{$i}\"{$sel}>★ {$i}/10</option>";
        }
    };
    ?>

    <div id="cinema-reviews" class="card border-0 shadow-sm rounded-4 bg-white p-4 p-md-5 mt-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <h3 class="oswald-font fw-bold text-uppercase mb-0" style="color: var(--beta-blue);">
                <i class="bi bi-star-fill text-warning me-2"></i>Đánh giá rạp & cơ sở vật chất
            </h3>
            <?php if (!empty($stats['total_reviews'])): ?>
                <div class="text-end">
                    <div class="fs-4 fw-bold" style="color: var(--beta-orange);">
                        ★ <?= number_format((float) ($stats['avg_overall'] ?? 0), 1) ?>/10
                    </div>
                    <div class="small text-muted"><?= (int) $stats['total_reviews'] ?> lượt đánh giá</div>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($stats['total_reviews'])): ?>
            <div class="row g-3 mb-4">
                <?php
                $bars = [
                    'Cơ sở vật chất' => $stats['avg_facility'] ?? 0,
                    'Vệ sinh' => $stats['avg_cleanliness'] ?? 0,
                    'Âm thanh - Hình ảnh' => $stats['avg_sound'] ?? 0,
                    'Nhân viên - Dịch vụ' => $stats['avg_service'] ?? 0,
                ];
                foreach ($bars as $label => $avg):
                    $pct = max(0, min(100, ((float) $avg) * 10));
                ?>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between small mb-1">
                            <span><?= htmlspecialchars($label) ?></span>
                            <strong><?= number_format((float) $avg, 1) ?>/10</strong>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-warning" style="width: <?= $pct ?>%;"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($_SESSION['user'])): ?>
            <?php if ($userCinemaReview): ?>
                <div class="alert alert-light border mb-3">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div>
                            <div class="small text-muted mb-1">Đánh giá của bạn (mỗi tài khoản chỉ 1 lần)</div>
                            <strong style="color: var(--beta-blue);"><?= htmlspecialchars($userCinemaReview['user_name'] ?? $_SESSION['user']['name']) ?></strong>
                            <span class="badge bg-warning text-dark ms-1">Tổng ★ <?= (int) $userCinemaReview['rating_overall'] ?>/10</span>
                            <div class="small text-secondary mt-1">
                                CSVC <?= (int) $userCinemaReview['rating_facility'] ?> ·
                                VS <?= (int) $userCinemaReview['rating_cleanliness'] ?> ·
                                AV <?= (int) $userCinemaReview['rating_sound'] ?> ·
                                DV <?= (int) $userCinemaReview['rating_service'] ?>
                            </div>
                            <div class="mt-1"><?= htmlspecialchars($userCinemaReview['comment'] ?? '') ?></div>
                        </div>
                        <form method="POST" action="?route=cinema/review/delete" onsubmit="return confirm('Xóa đánh giá rạp của bạn?');">
                            <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="review_id" value="<?= (int) $userCinemaReview['id'] ?>">
                            <button type="submit" class="btn btn-outline-danger btn-sm">Xóa</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            <form method="POST" action="?route=cinema/review" class="border rounded-3 p-3 mb-4 bg-light">
                <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                <h6 class="fw-bold mb-3">
                    <?= $userCinemaReview ? 'Cập nhật đánh giá rạp' : 'Gửi đánh giá rạp & cơ sở vật chất' ?>
                </h6>
                <div class="row g-2 mb-2">
                    <div class="col-6 col-md-4 col-lg">
                        <label class="form-label small mb-0">Tổng thể</label>
                        <select name="rating_overall" class="form-select form-select-sm">
                            <?php $scoreOptions((int) ($userCinemaReview['rating_overall'] ?? 10)); ?>
                        </select>
                    </div>
                    <div class="col-6 col-md-4 col-lg">
                        <label class="form-label small mb-0">Cơ sở vật chất</label>
                        <select name="rating_facility" class="form-select form-select-sm">
                            <?php $scoreOptions((int) ($userCinemaReview['rating_facility'] ?? 10)); ?>
                        </select>
                    </div>
                    <div class="col-6 col-md-4 col-lg">
                        <label class="form-label small mb-0">Vệ sinh</label>
                        <select name="rating_cleanliness" class="form-select form-select-sm">
                            <?php $scoreOptions((int) ($userCinemaReview['rating_cleanliness'] ?? 10)); ?>
                        </select>
                    </div>
                    <div class="col-6 col-md-4 col-lg">
                        <label class="form-label small mb-0">Âm thanh - Hình ảnh</label>
                        <select name="rating_sound" class="form-select form-select-sm">
                            <?php $scoreOptions((int) ($userCinemaReview['rating_sound'] ?? 10)); ?>
                        </select>
                    </div>
                    <div class="col-6 col-md-4 col-lg">
                        <label class="form-label small mb-0">Nhân viên - Dịch vụ</label>
                        <select name="rating_service" class="form-select form-select-sm">
                            <?php $scoreOptions((int) ($userCinemaReview['rating_service'] ?? 10)); ?>
                        </select>
                    </div>
                </div>
                <div class="mb-2">
                    <label class="form-label small mb-0">Nhận xét</label>
                    <textarea name="comment" class="form-control form-control-sm" rows="2" required
                              placeholder="Chia sẻ trải nghiệm về rạp, ghế ngồi, vệ sinh, nhân viên..."><?= htmlspecialchars($userCinemaReview['comment'] ?? '') ?></textarea>
                </div>
                <button type="submit" class="btn btn-beta-buy btn-sm">
                    <?= $userCinemaReview ? 'Cập nhật đánh giá' : 'Gửi đánh giá' ?>
                </button>
            </form>
        <?php else: ?>
            <div class="alert alert-info">
                <a href="?route=auth/login">Đăng nhập</a> để gửi đánh giá rạp và cơ sở vật chất.
            </div>
        <?php endif; ?>

        <?php if (empty($cinemaReviews)): ?>
            <p class="text-muted small mb-0">Chưa có đánh giá nào. Hãy là người đầu tiên nhận xét về rạp!</p>
        <?php else: ?>
            <?php foreach ($cinemaReviews as $rv): ?>
                <div class="mb-3 pb-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div>
                            <strong style="color: var(--beta-blue);"><?= htmlspecialchars($rv['user_name']) ?></strong>
                            <span class="badge bg-warning text-dark ms-1">Tổng ★ <?= (int) $rv['rating_overall'] ?>/10</span>
                            <div class="small text-muted mt-1">
                                CSVC <?= (int) $rv['rating_facility'] ?>/10 ·
                                Vệ sinh <?= (int) $rv['rating_cleanliness'] ?>/10 ·
                                AV <?= (int) $rv['rating_sound'] ?>/10 ·
                                Dịch vụ <?= (int) $rv['rating_service'] ?>/10
                            </div>
                            <div class="small text-secondary mt-1"><?= htmlspecialchars($rv['comment'] ?? '') ?></div>
                            <div class="small text-muted"><?= htmlspecialchars($rv['created_at'] ?? '') ?></div>
                        </div>
                        <?php if ($currentUserId > 0 && (int) $rv['user_id'] === $currentUserId): ?>
                            <form method="POST" action="?route=cinema/review/delete" onsubmit="return confirm('Xóa đánh giá rạp của bạn?');">
                                <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="review_id" value="<?= (int) $rv['id'] ?>">
                                <button type="submit" class="btn btn-link btn-sm text-danger p-0">Xóa</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (($cinemaReviewPagination['total_pages'] ?? 1) > 1): ?>
                <nav class="mt-2" aria-label="Phân trang đánh giá rạp">
                    <ul class="pagination pagination-sm justify-content-center mb-0">
                        <?php
                        $cp = (int) $cinemaReviewPagination['page'];
                        $ctp = (int) $cinemaReviewPagination['total_pages'];
                        for ($p = 1; $p <= $ctp; $p++):
                        ?>
                            <li class="page-item <?= $p === $cp ? 'active' : '' ?>">
                                <a class="page-link" href="?route=about&page=<?= $p ?>#cinema-reviews"><?= $p ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
