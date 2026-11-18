<div class="container py-4">
    <h3 class="oswald-font fw-bold text-uppercase mb-3" style="color: var(--beta-blue);">
        <i class="bi bi-heart-fill text-danger me-2"></i>Danh Sách Phim Yêu Thích
    </h3>
    <?php if (empty($movies)): ?>
        <div class="alert alert-info">Bạn chưa thêm bộ phim nào vào danh sách yêu thích.</div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($movies as $movie): ?>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm p-3 d-flex flex-row align-items-center gap-3 bg-white rounded-4">
                        <img src="<?= htmlspecialchars($movie['poster_path'] ?: 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=300') ?>"
                             style="width:75px;height:105px;object-fit:cover;" class="rounded-3">
                        <div>
                            <h6 class="fw-bold mb-1" style="color: var(--beta-blue);"><?= htmlspecialchars($movie['title_vi'] ?: $movie['title']) ?></h6>
                            <div class="small text-muted mb-2">Đã lưu: <?= htmlspecialchars($movie['wishlisted_at']) ?></div>
                            <a href="?route=movie/detail&id=<?= (int) $movie['id'] ?>" class="btn btn-sm btn-beta-buy">Xem lịch chiếu</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
