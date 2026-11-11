<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="oswald-font fw-bold text-uppercase mb-0" style="color: var(--beta-blue);">
            <i class="bi bi-bell-fill text-warning me-2"></i>Thông Báo Của Tôi
        </h3>
        <a href="?route=notifications&mark_read=1" class="btn btn-outline-primary btn-sm">Đánh dấu đã đọc tất cả</a>
    </div>

    <?php if (empty($notifications)): ?>
        <div class="alert alert-info">Bạn chưa có thông báo nào.</div>
    <?php else: ?>
        <div class="list-group shadow-sm">
            <?php foreach ($notifications as $n): ?>
                <div class="list-group-item bg-white <?= empty($n['read_at']) ? 'border-start border-4 border-primary' : '' ?>">
                    <div class="d-flex justify-content-between">
                        <h6 class="fw-bold mb-1" style="color: var(--beta-blue);"><?= htmlspecialchars($n['title']) ?></h6>
                        <small class="text-muted"><?= htmlspecialchars($n['created_at'] ?? '') ?></small>
                    </div>
                    <p class="mb-1 small text-secondary"><?= htmlspecialchars($n['message']) ?></p>
                    <span class="badge <?= empty($n['read_at']) ? 'bg-warning text-dark' : 'bg-secondary' ?>">
                        <?= empty($n['read_at']) ? 'Mới' : 'Đã xem lúc ' . htmlspecialchars($n['read_at']) ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
