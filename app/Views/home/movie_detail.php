<div class="row g-4">
    <div class="col-md-4 col-lg-3">
        <img src="<?= htmlspecialchars($movie['poster_path'] ?: 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=600') ?>"
             class="img-fluid rounded shadow w-100" alt="<?= htmlspecialchars($movie['title']) ?>">
        <form method="POST" action="?route=wishlist/toggle" class="mt-3">
            <input type="hidden" name="movie_id" value="<?= (int) $movie['id'] ?>">
            <button type="submit" class="btn btn-outline-danger w-100 btn-sm">
                <i class="bi bi-heart-fill me-1"></i> Thêm / Bỏ Yêu Thích (Wishlist)
            </button>
        </form>
    </div>

    <div class="col-md-8 col-lg-9">
        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
            <span class="badge bg-danger fs-6"><?= htmlspecialchars($movie['age_rating'] ?: 'P') ?></span>
            <span class="badge bg-warning text-dark fs-6"><i class="bi bi-star-fill"></i> IMDb <?= htmlspecialchars((string) ($movie['imdb_rating'] ?: '8.5')) ?></span>
            <span class="badge bg-secondary"><?= htmlspecialchars($movie['status']) ?></span>
        </div>
        <h2 class="fw-bold mb-1"><?= htmlspecialchars($movie['title']) ?></h2>
        <?php if (!empty($movie['title_vi'])): ?>
            <h5 class="text-info mb-3"><?= htmlspecialchars($movie['title_vi']) ?></h5>
        <?php endif; ?>

        <p class="text-light"><?= nl2br(htmlspecialchars($movie['overview'] ?? '')) ?></p>

        <div class="row small text-secondary mb-4">
            <div class="col-sm-6"><strong>Thể loại:</strong> <?= htmlspecialchars($movie['genre_names'] ?: 'Chưa phân loại') ?></div>
            <div class="col-sm-6"><strong>Thời lượng:</strong> <?= (int) $movie['runtime'] ?> phút</div>
            <div class="col-sm-6"><strong>Đạo diễn:</strong> <?= htmlspecialchars($movie['director'] ?: 'Đang cập nhật') ?></div>
            <div class="col-sm-6"><strong>Khởi chiếu:</strong> <?= htmlspecialchars($movie['release_date'] ?: '') ?></div>
        </div>

        <div class="card mb-4">
            <div class="card-header fw-bold text-warning">
                <i class="bi bi-calendar2-week me-1"></i> Suất Chiếu Đang Mở Bán (Nhánh 4: Shows)
            </div>
            <div class="card-body">
                <?php if (empty($shows)): ?>
                    <p class="text-secondary mb-0">Hiện chưa có suất chiếu nào cho phim này. Admin có thể tạo suất chiếu trong trang Quản trị.</p>
                <?php else: ?>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($shows as $sh): ?>
                            <a href="?route=booking/seats&show_id=<?= (int) $sh['id'] ?>" class="btn btn-outline-info text-start">
                                <div class="fw-bold"><?= date('H:i - d/m/Y', strtotime($sh['start_time'])) ?></div>
                                <div class="small text-light"><?= htmlspecialchars($sh['room_name']) ?> (<?= strtoupper($sh['room_type']) ?>)</div>
                                <div class="small text-warning">Giá từ: <?= number_format((float) $sh['base_price']) ?>đ</div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Đánh giá phim (Nhánh 7: Reviews) -->
        <div class="card">
            <div class="card-header fw-bold">
                <i class="bi bi-chat-square-quote me-1 text-info"></i> Đánh Giá Từ Khán Giả (Nhánh 7: Reviews)
            </div>
            <div class="card-body">
                <?php if (!empty($_SESSION['user'])): ?>
                    <form method="POST" action="?route=movie/review" class="mb-4 pb-3 border-bottom border-secondary">
                        <input type="hidden" name="movie_id" value="<?= (int) $movie['id'] ?>">
                        <div class="row g-2">
                            <div class="col-md-3">
                                <label class="form-label small">Điểm (1-10)</label>
                                <select name="rating" class="form-select form-select-sm">
                                    <?php for ($i = 10; $i >= 1; $i--): ?>
                                        <option value="<?= $i ?>"><?= $i ?> / 10 sao</option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-7">
                                <label class="form-label small">Nhận xét của bạn</label>
                                <input type="text" name="comment" class="form-control form-control-sm" required placeholder="Chia sẻ cảm nhận về bộ phim...">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-info btn-sm w-100">Gửi</button>
                            </div>
                        </div>
                    </form>
                <?php endif; ?>

                <?php if (empty($reviews)): ?>
                    <p class="text-secondary small mb-0">Chưa có đánh giá nào cho bộ phim này.</p>
                <?php else: ?>
                    <?php foreach ($reviews as $rv): ?>
                        <div class="mb-2 pb-2 border-bottom border-secondary">
                            <div class="d-flex justify-content-between">
                                <strong><?= htmlspecialchars($rv['user_name']) ?></strong>
                                <span class="badge bg-warning text-dark">★ <?= (int) $rv['rating'] ?>/10</span>
                            </div>
                            <div class="small text-light"><?= htmlspecialchars($rv['comment'] ?? '') ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

