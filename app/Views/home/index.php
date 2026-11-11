<div class="p-4 mb-4 rounded-3" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); border: 1px solid #334155;">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h2 class="fw-bold text-white mb-1"><?= htmlspecialchars($cinemaInfo['name']) ?></h2>
            <p class="text-secondary mb-2">
                <i class="bi bi-geo-alt text-danger"></i> <?= htmlspecialchars($cinemaInfo['address']) ?>
                <?php if (!empty($cinemaInfo['phone'])): ?>
                    | <i class="bi bi-telephone text-info"></i> <?= htmlspecialchars($cinemaInfo['phone']) ?>
                <?php endif; ?>
            </p>
            <p class="small text-light mb-0">Trải nghiệm đặt vé nhanh chóng: Giữ ghế realtime (<code>SELECT ... FOR UPDATE</code>), Bắp nước, Coupon, Ví điện tử & Vé QR Code.</p>
        </div>
        <div class="col-md-4 mt-3 mt-md-0">
            <form method="GET" action="index.php" class="d-flex gap-2">
                <input type="hidden" name="route" value="home">
                <input type="text" name="q" value="<?= htmlspecialchars($keyword ?? '') ?>" class="form-control form-control-sm" placeholder="Tìm tên phim...">
                <button class="btn btn-danger btn-sm" type="submit"><i class="bi bi-search"></i></button>
            </form>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0"><i class="bi bi-collection-play text-danger me-2"></i>Danh Sách Phim</h4>
    <div class="btn-group btn-group-sm">
        <a href="?route=home" class="btn <?= empty($currentStatus) ? 'btn-danger' : 'btn-outline-secondary' ?>">Tất cả</a>
        <a href="?route=home&status=now_showing" class="btn <?= ($currentStatus === 'now_showing') ? 'btn-danger' : 'btn-outline-secondary' ?>">Đang chiếu</a>
        <a href="?route=home&status=coming_soon" class="btn <?= ($currentStatus === 'coming_soon') ? 'btn-danger' : 'btn-outline-secondary' ?>">Sắp chiếu</a>
    </div>
</div>

<?php if (empty($movies)): ?>
    <div class="alert alert-secondary">Chưa có bộ phim nào. Hãy đăng nhập quyền Admin để thêm phim hoặc import <code>database/seed_sample_data.sql</code>.</div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($movies as $movie): ?>
            <div class="col-sm-6 col-md-4 col-lg-3">
                <div class="card h-100 shadow-sm">
                    <img src="<?= htmlspecialchars($movie['poster_path'] ?: 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=600') ?>"
                         class="movie-poster w-100" alt="<?= htmlspecialchars($movie['title']) ?>">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-danger"><?= htmlspecialchars($movie['age_rating'] ?: 'P') ?></span>
                            <span class="badge bg-warning text-dark"><i class="bi bi-star-fill"></i> <?= htmlspecialchars((string) ($movie['imdb_rating'] ?: '8.0')) ?></span>
                        </div>
                        <h5 class="card-title fw-bold mb-1"><?= htmlspecialchars($movie['title']) ?></h5>
                        <?php if (!empty($movie['title_vi'])): ?>
                            <div class="text-info small mb-2"><?= htmlspecialchars($movie['title_vi']) ?></div>
                        <?php endif; ?>
                        <p class="text-secondary small mb-3">
                            <i class="bi bi-tags"></i> <?= htmlspecialchars($movie['genre_names'] ?: 'Đang cập nhật') ?><br>
                            <i class="bi bi-clock"></i> <?= (int) $movie['runtime'] ?> phút
                        </p>
                        <div class="mt-auto">
                            <a href="?route=movie/detail&id=<?= (int) $movie['id'] ?>" class="btn btn-danger w-100 btn-sm">
                                <i class="bi bi-ticket-detailed me-1"></i> Xem Lịch Chiếu & Đặt Vé
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

