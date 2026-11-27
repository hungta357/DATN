<?php
$categoryLabels = [
    'ticket' => 'Vé / check-in',
    'service' => 'Chất lượng dịch vụ',
    'safety' => 'An toàn tại rạp',
    'other' => 'Khác',
];
$incidentStatusLabels = [
    'open' => 'Mới tiếp nhận',
    'in_review' => 'Đang xử lý',
    'resolved' => 'Đã giải quyết',
    'rejected' => 'Không đủ điều kiện',
];
?>
<div class="container py-4">
    <div class="p-4 p-md-5 mb-4 rounded-4 text-white" style="background:linear-gradient(125deg,#064f87,#1687c5);">
        <div class="row align-items-center g-3">
            <div class="col-md-8">
                <span class="badge rounded-pill bg-light text-primary mb-2">CINENOW · HỖ TRỢ KHÁCH HÀNG</span>
                <h1 class="oswald-font text-uppercase mb-2">Khiếu nại vé & hỗ trợ</h1>
                <p class="mb-0 opacity-75">Gửi yêu cầu gắn với vé đã thanh toán để đội ngũ rạp kiểm tra và phản hồi.</p>
            </div>
            <div class="col-md-4 text-md-end">
                <a class="btn btn-light text-primary fw-bold" href="?route=booking/my">
                    <i class="bi bi-ticket-perforated me-1"></i>Vé của tôi
                </a>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white fw-bold py-3"><i class="bi bi-chat-square-text text-primary me-2"></i>Tạo yêu cầu mới</div>
                <div class="card-body">
                    <?php if (empty($tickets)): ?>
                        <div class="alert alert-info mb-0">
                            Chưa có vé đã thanh toán để gửi khiếu nại.
                            <?php if (!empty($selectedBookingId)): ?>
                                <a href="?route=incidents">Xem các vé khác</a>.
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <form method="POST" action="?route=incidents/create">
                            <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            <div class="mb-3">
                                <label class="form-label">Vé cần hỗ trợ *</label>
                                <select name="ticket_code" class="form-select" required>
                                    <option value="">Chọn vé</option>
                                    <?php foreach ($tickets as $ticket): ?>
                                        <option value="<?= htmlspecialchars($ticket['ticket_code'], ENT_QUOTES, 'UTF-8') ?>">
                                            <?= htmlspecialchars($ticket['movie_title'] . ' · ' . $ticket['seat_code'] . ' · ' . date('d/m/Y H:i', strtotime($ticket['start_time'])) . ' · ' . $ticket['ticket_code'], ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Danh mục</label>
                                <select name="category" class="form-select">
                                    <?php foreach ($categoryLabels as $value => $label): ?>
                                        <option value="<?= $value ?>"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tiêu đề *</label>
                                <input type="text" name="title" class="form-control" maxlength="180" required placeholder="Ví dụ: Vé không quét được tại cổng">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Mô tả chi tiết *</label>
                                <textarea name="description" rows="5" class="form-control" maxlength="5000" required placeholder="Mô tả vấn đề, thời điểm và thông tin liên quan..."></textarea>
                            </div>
                            <button class="btn btn-beta-buy w-100"><i class="bi bi-send me-1"></i>Gửi yêu cầu</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white fw-bold py-3"><i class="bi bi-inboxes text-primary me-2"></i>Các yêu cầu đã gửi</div>
                <div class="card-body">
                    <?php if (empty($reports)): ?>
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-chat-square-dots fs-1 d-block mb-2"></i>
                            Bạn chưa gửi yêu cầu hỗ trợ nào.
                        </div>
                    <?php else: ?>
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($reports as $report): ?>
                                <article class="border rounded-3 p-3">
                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                        <div>
                                            <h6 class="fw-bold mb-1"><?= htmlspecialchars($report['title'], ENT_QUOTES, 'UTF-8') ?></h6>
                                            <div class="small text-muted">
                                                <?= htmlspecialchars($categoryLabels[$report['category']] ?? $report['category'], ENT_QUOTES, 'UTF-8') ?>
                                                <?php if (!empty($report['ticket_code'])): ?>
                                                    · Vé <code><?= htmlspecialchars($report['ticket_code'], ENT_QUOTES, 'UTF-8') ?></code>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <span class="badge <?= $report['status'] === 'resolved' ? 'bg-success' : ($report['status'] === 'rejected' ? 'bg-secondary' : 'bg-warning text-dark') ?>">
                                            <?= htmlspecialchars($incidentStatusLabels[$report['status']] ?? $report['status'], ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </div>
                                    <p class="mb-2"><?= nl2br(htmlspecialchars($report['description'], ENT_QUOTES, 'UTF-8')) ?></p>
                                    <?php if (!empty($report['resolution'])): ?>
                                        <div class="rounded-3 bg-light p-3 small">
                                            <strong>Phản hồi từ rạp:</strong><br>
                                            <?= nl2br(htmlspecialchars($report['resolution'], ENT_QUOTES, 'UTF-8')) ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="small text-muted mt-2">
                                        <?= date('d/m/Y H:i', strtotime($report['created_at'])) ?>
                                        · Phụ trách: <?= htmlspecialchars($report['assignee_name'] ?? 'Chưa phân công', ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
