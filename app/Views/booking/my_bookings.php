<h4 class="fw-bold mb-3"><i class="bi bi-ticket-perforated text-danger me-2"></i>Lịch Sử Đặt Vé Của Tôi</h4>

<?php if (empty($bookings)): ?>
    <div class="alert alert-secondary">Bạn chưa có đơn đặt vé nào.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle">
            <thead>
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
                        <td class="fw-semibold"><?= htmlspecialchars($b['movie_title']) ?></td>
                        <td class="small">
                            <?= htmlspecialchars($b['room_name']) ?><br>
                            <span class="text-info"><?= date('H:i - d/m/Y', strtotime($b['start_time'])) ?></span>
                        </td>
                        <td class="fw-bold text-warning"><?= number_format((float) $b['total_amount']) ?>đ</td>
                        <td>
                            <span class="badge <?= $b['status'] === 'confirmed' ? 'bg-success' : 'bg-warning text-dark' ?>">
                                <?= htmlspecialchars($b['status']) ?>
                            </span>
                        </td>
                        <td>
                            <a href="?route=booking/checkout&id=<?= (int) $b['id'] ?>" class="btn btn-sm btn-outline-info">
                                <?= $b['status'] === 'pending' ? 'Thanh toán ngay' : 'Xem Vé QR' ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

