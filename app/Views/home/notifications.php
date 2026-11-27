<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h3 class="oswald-font fw-bold text-uppercase mb-0" style="color: var(--beta-blue);">
            <i class="bi bi-bell-fill text-warning me-2"></i>Thông Báo Của Tôi
            <?php if (!empty($unreadCount)): ?>
                <span class="badge bg-danger align-middle"><?= (int) $unreadCount ?> chưa đọc</span>
            <?php endif; ?>
        </h3>
        <a href="?route=notifications&mark_read=1" class="btn btn-outline-primary btn-sm">Đánh dấu đã đọc tất cả</a>
    </div>

    <?php
    $pagination = $pagination ?? ['page' => 1, 'total_pages' => 1, 'total' => 0];
    $currentPage = (int) ($pagination['page'] ?? 1);
    $totalPages = (int) ($pagination['total_pages'] ?? 1);
    $totalItems = (int) ($pagination['total'] ?? 0);
    ?>

    <?php if (empty($notifications)): ?>
        <div class="alert alert-info">Bạn chưa có thông báo nào.</div>
    <?php else: ?>
        <p class="small text-muted mb-2">Tổng <?= $totalItems ?> thông báo · Trang <?= $currentPage ?>/<?= $totalPages ?></p>
        <div class="list-group shadow-sm">
            <?php foreach ($notifications as $n): ?>
                <div class="list-group-item bg-white <?= empty($n['read_at']) ? 'border-start border-4 border-primary' : '' ?>">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between flex-wrap gap-1">
                                <h6 class="fw-bold mb-1" style="color: var(--beta-blue);"><?= htmlspecialchars($n['title']) ?></h6>
                                <small class="text-muted"><?= htmlspecialchars($n['created_at'] ?? '') ?></small>
                            </div>
                            <p class="mb-1 small text-secondary"><?= htmlspecialchars($n['message']) ?></p>
                            <span class="badge <?= empty($n['read_at']) ? 'bg-warning text-dark' : 'bg-secondary' ?>">
                                <?= empty($n['read_at']) ? 'Mới' : 'Đã xem lúc ' . htmlspecialchars($n['read_at']) ?>
                            </span>
                            <?php if (!empty($n['type'])): ?>
                                <span class="badge bg-light text-dark border"><?= htmlspecialchars($n['type']) ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if (empty($n['read_at'])): ?>
                            <a href="?route=notifications&read_id=<?= (int) $n['id'] ?>&page=<?= $currentPage ?>"
                               class="btn btn-sm btn-outline-primary text-nowrap">Đánh dấu đã đọc</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
            <nav class="mt-3" aria-label="Phân trang thông báo">
                <ul class="pagination pagination-sm justify-content-center mb-0">
                    <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?route=notifications&page=<?= max(1, $currentPage - 1) ?>">Trước</a>
                    </li>
                    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                        <li class="page-item <?= $p === $currentPage ? 'active' : '' ?>">
                            <a class="page-link" href="?route=notifications&page=<?= $p ?>"><?= $p ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="?route=notifications&page=<?= min($totalPages, $currentPage + 1) ?>">Sau</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>
