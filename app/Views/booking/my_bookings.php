<div class="container py-4">
    <h3 class="oswald-font fw-bold text-uppercase mb-3" style="color: var(--beta-blue);">
        <i class="bi bi-ticket-perforated text-danger me-2"></i>Hành Trình Điện Ảnh & Vé Đã Đặt
    </h3>

    <?php if (empty($bookings)): ?>
        <div class="alert alert-info">Bạn chưa có đơn đặt vé nào tại rạp.</div>
    <?php else: ?>
        <div class="card border-0 shadow-sm bg-white rounded-4 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-primary">
                        <tr>
                            <th>Mã Đơn</th>
                            <th>Phim</th>
                            <th>Phòng & Suất Chiếu</th>
                            <th>Tổng Tiền</th>
                            <th>Trạng Thái</th>
                            <th>Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $b): ?>
                            <tr>
                                <td><code><?= htmlspecialchars($b['booking_code']) ?></code></td>
                                <td class="fw-bold" style="color: var(--beta-blue);"><?= htmlspecialchars($b['movie_title']) ?></td>
                                <td class="small">
                                    <?= htmlspecialchars($b['room_name']) ?><br>
                                    <span class="text-primary fw-semibold"><?= date('H:i - d/m/Y', strtotime($b['start_time'])) ?></span>
                                </td>
                                <td class="fw-bold text-danger"><?= number_format((float) $b['total_amount']) ?>đ</td>
                                <td>
                                    <span class="badge <?= $b['status'] === 'confirmed' ? 'bg-success' : ($b['status'] === 'pending' ? 'bg-warning text-dark' : 'bg-secondary') ?>">
                                        <?= htmlspecialchars($b['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="?route=booking/checkout&id=<?= (int) $b['id'] ?>" class="btn btn-sm btn-beta-buy">
                                        <?= $b['status'] === 'pending' ? 'Thanh toán ngay' : ($b['status'] === 'confirmed' ? 'Xem Vé QR' : 'Xem chi tiết') ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>
