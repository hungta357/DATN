<h4 class="fw-bold mb-3"><i class="bi bi-heart-fill text-danger me-2"></i>Danh Sách Phim Yêu Thích (Wishlists)</h4>
<?php if (empty($movies)): ?>
    <div class="alert alert-secondary">Bạn chưa thêm bộ phim nào vào danh sách yêu thích.</div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($movies as $movie): ?>
            <div class="col-md-4">
                <div class="card p-3 d-flex flex-row align-items-center gap-3">
                    <img src="<?= htmlspecialchars($movie['poster_path'] ?: 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=300') ?>"
                         style="width:70px;height:100px;object-fit:cover;" class="rounded">
                    <div>
                        <h6 class="fw-bold mb-1"><?= htmlspecialchars($movie['title']) ?></h6>
                        <div class="small text-secondary mb-2">Đã lưu: <?= htmlspecialchars($movie['wishlisted_at']) ?></div>
                        <a href="?route=movie/detail&id=<?= (int) $movie['id'] ?>" class="btn btn-sm btn-danger">Xem chi tiết</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
