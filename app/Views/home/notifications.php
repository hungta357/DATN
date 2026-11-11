<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0"><i class="bi bi-bell-fill text-warning me-2"></i>Thông Báo Của Tôi (Notifications)</h4>
    <a href="?route=notifications&mark_read=1" class="btn btn-outline-info btn-sm">Đánh dấu đã đọc tất cả</a>
</div>

<?php if (empty($notifications)): ?>
    <div class="alert alert-secondary">Bạn chưa có thông báo nào.</div>
<?php else: ?>
    <div class="list-group">
        <?php foreach ($notifications as $n): ?>
            <div class="list-group-item bg-dark text-light border-secondary <?= empty($n['read_at']) ? 'border-start border-4 border-warning' : '' ?>">
                <div class="d-flex justify-content-between">
                    <h6 class="fw-bold mb-1 text-info"><?= htmlspecialchars($n['title']) ?></h6>
                    <small class="text-secondary"><?= htmlspecialchars($n['created_at'] ?? '') ?></small>
                </div>
                <p class="mb-1 small"><?= htmlspecialchars($n['message']) ?></p>
                <span class="badge <?= empty($n['read_at']) ? 'bg-warning text-dark' : 'bg-secondary' ?>">
                    <?= empty($n['read_at']) ? 'Chưa đọc' : 'Đã đọc lúc ' . htmlspecialchars($n['read_at']) ?>
                </span>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
