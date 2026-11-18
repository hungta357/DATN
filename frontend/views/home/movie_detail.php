<?php
$age = strtoupper($movie['age_rating'] ?: 'P');
$ageClass = 'age-P';
if (str_contains($age, '13')) $ageClass = 'age-T13';
elseif (str_contains($age, '16')) $ageClass = 'age-T16';
elseif (str_contains($age, '18')) $ageClass = 'age-T18';
?>
<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-3 small">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="?route=home" class="text-decoration-none">Trang chủ</a></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($movie['title_vi'] ?: $movie['title']) ?></li>
        </ol>
    </nav>

    <div class="card border-0 shadow-sm p-4 mb-4 bg-white rounded-4">
        <div class="row g-4">
            <div class="col-md-4 col-lg-3">
                <div class="beta-poster-wrap">
                    <span class="beta-age-badge <?= $ageClass ?>"><?= htmlspecialchars($age) ?></span>
                    <img src="<?= htmlspecialchars($movie['poster_path'] ?: 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=600') ?>"
                         alt="<?= htmlspecialchars($movie['title']) ?>">
                </div>
                <?php if (!empty($movie['trailer_url'])): ?>
                    <button type="button" class="btn btn-warning w-100 btn-sm fw-bold mt-2 text-dark" data-bs-toggle="modal" data-bs-target="#trailerModal">
                        <i class="bi bi-play-circle-fill me-1"></i> Xem Trailer Phim
                    </button>
                <?php endif; ?>
                <form method="POST" action="?route=wishlist/toggle" class="mt-2">
                    <input type="hidden" name="movie_id" value="<?= (int) $movie['id'] ?>">
                    <button type="submit" class="btn btn-outline-danger w-100 btn-sm fw-semibold">
                        <i class="bi bi-heart-fill me-1"></i> Lưu vào Phim Yêu Thích
                    </button>
                </form>
            </div>

            <div class="col-md-8 col-lg-9">
                <h2 class="oswald-font fw-bold text-uppercase mb-1" style="color: var(--beta-blue);">
                    <?= htmlspecialchars($movie['title_vi'] ?: $movie['title']) ?>
                </h2>
                <div class="text-muted mb-3"><?= htmlspecialchars($movie['title']) ?></div>

                <p class="text-secondary"><?= nl2br(htmlspecialchars($movie['overview'] ?? '')) ?></p>

                <div class="row g-2 small mb-4 p-3 rounded-3 bg-light border">
                    <div class="col-sm-6"><strong>Đạo diễn:</strong> <?= htmlspecialchars($movie['director'] ?: 'Đang cập nhật') ?></div>
                    <div class="col-sm-6"><strong>Diễn viên:</strong> <?= htmlspecialchars($movie['cast_text'] ?: 'Đang cập nhật') ?></div>
                    <div class="col-sm-6"><strong>Thể loại:</strong> <?= htmlspecialchars($movie['genre_names'] ?: 'Điện ảnh') ?></div>
                    <div class="col-sm-6"><strong>Thời lượng:</strong> <?= (int) $movie['runtime'] ?> phút</div>
                    <div class="col-sm-6"><strong>Ngôn ngữ:</strong> <?= htmlspecialchars($movie['language'] ?: 'Phụ đề Tiếng Việt') ?></div>
                    <div class="col-sm-6"><strong>Ngày khởi chiếu:</strong> <?= !empty($movie['release_date']) ? date('d/m/Y', strtotime($movie['release_date'])) : '' ?></div>
                </div>

                <!-- LỊCH CHIẾU CỦA PHIM TẠI RẠP ĐƠN LẺ -->
                <div class="border-top pt-3">
                    <h5 class="oswald-font fw-bold text-uppercase mb-3" style="color: var(--beta-blue);">
                        <i class="bi bi-calendar2-check text-danger me-1"></i> LỊCH CHIẾU TẠI <?= htmlspecialchars(strtoupper($cinemaInfo['name'] ?? 'RẠP')) ?>
                    </h5>
                    <?php if (empty($shows)): ?>
                        <div class="alert alert-warning mb-0">Hiện chưa có suất chiếu nào đang mở bán cho bộ phim này.</div>
                    <?php else: ?>
                        <div class="d-flex flex-wrap gap-3">
                            <?php foreach ($shows as $sh): ?>
                                <a href="?route=booking/seats&show_id=<?= (int) $sh['id'] ?>" class="beta-time-slot">
                                    <div class="slot-hour"><?= date('H:i', strtotime($sh['start_time'])) ?></div>
                                    <div class="small text-primary fw-semibold"><?= date('d/m/Y', strtotime($sh['start_time'])) ?> • <?= htmlspecialchars($sh['room_name']) ?></div>
                                    <div class="slot-seats"><?= (int) ($sh['available_seats'] ?? 20) ?> ghế trống • <?= number_format((float) $sh['base_price']) ?>đ</div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- BÌNH LUẬN & ĐÁNH GIÁ KHÁN GIẢ -->
    <div class="card border-0 shadow-sm p-4 bg-white rounded-4">
        <h5 class="oswald-font fw-bold text-uppercase mb-3" style="color: var(--beta-blue);">
            <i class="bi bi-chat-dots-fill text-warning me-1"></i> ĐÁNH GIÁ TỪ KHÁN GIẢ TẠI RẠP
        </h5>
        <?php if (!empty($_SESSION['user'])): ?>
            <form method="POST" action="?route=movie/review" class="mb-4 pb-3 border-bottom">
                <input type="hidden" name="movie_id" value="<?= (int) $movie['id'] ?>">
                <div class="row g-2">
                    <div class="col-md-3">
                        <select name="rating" class="form-select form-select-sm">
                            <?php for ($i = 10; $i >= 1; $i--): ?>
                                <option value="<?= $i ?>">★ <?= $i ?> / 10 điểm</option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-7">
                        <input type="text" name="comment" class="form-control form-control-sm" required placeholder="Nhập cảm nhận của bạn về bộ phim...">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-beta-buy btn-sm w-100">Gửi đánh giá</button>
                    </div>
                </div>
            </form>
        <?php endif; ?>

        <?php if (empty($reviews)): ?>
            <p class="text-muted small mb-0">Chưa có đánh giá nào. Hãy là người đầu tiên nhận xét bộ phim này!</p>
        <?php else: ?>
            <?php foreach ($reviews as $rv): ?>
                <div class="mb-2 pb-2 border-bottom">
                    <div class="d-flex justify-content-between">
                        <strong style="color: var(--beta-blue);"><?= htmlspecialchars($rv['user_name']) ?></strong>
                        <span class="badge bg-warning text-dark">★ <?= (int) $rv['rating'] ?>/10</span>
                    </div>
                    <div class="small text-secondary"><?= htmlspecialchars($rv['comment'] ?? '') ?></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($movie['trailer_url'])): ?>
    <!-- MODAL XEM TRAILER YOUTUBE -->
    <div class="modal fade" id="trailerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content bg-dark border-secondary">
                <div class="modal-header border-secondary text-white py-2">
                    <h6 class="modal-title oswald-font text-uppercase mb-0">Trailer: <?= htmlspecialchars($movie['title_vi'] ?: $movie['title']) ?></h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="ratio ratio-16x9">
                        <iframe src="<?= htmlspecialchars($movie['trailer_url']) ?>" title="Movie Trailer" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
