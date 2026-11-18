<?php
$appCfg = require dirname(__DIR__, 3) . '/config/config.php';
$earlyHour = (int) ($appCfg['app']['early_show_before_hour'] ?? 12);
$earlyDiscount = (int) ($appCfg['app']['early_show_discount_percent'] ?? 20);
$defaultBase = (float) ($appCfg['app']['default_base_price'] ?? 75000);
$earlyBase = \App\Models\ShowModel::suggestEarlyBasePrice($defaultBase, $earlyDiscount);
?>
<div class="container py-4">
    <div class="text-center mb-4">
        <h2 class="oswald-font fw-bold text-uppercase mb-1" style="color: var(--beta-blue);">
            BẢNG GIÁ VÉ TẠI <?= htmlspecialchars(strtoupper($cinemaInfo['name'])) ?>
        </h2>
        <p class="text-muted small">Áp dụng cho tất cả các phòng chiếu tại rạp</p>
    </div>

    <div class="card border-0 shadow-sm p-4 mb-4 bg-white rounded-4">
        <h5 class="oswald-font fw-bold text-uppercase mb-3" style="color: var(--beta-orange);">
            <i class="bi bi-sunrise me-2"></i>0. Suất chiếu sớm (ưu đãi buổi sáng)
        </h5>
        <p class="mb-3">
            Suất bắt đầu <strong>trước <?= $earlyHour ?>:00</strong> được đánh dấu
            <span class="badge bg-warning text-dark">Suất sớm</span>
            và áp dụng giá vé cơ bản giảm khoảng <strong><?= $earlyDiscount ?>%</strong> so với suất thường.
        </p>
        <div class="table-responsive">
            <table class="table table-bordered align-middle text-center mb-0">
                <thead class="table-warning">
                    <tr>
                        <th>Loại suất</th>
                        <th>Khung giờ</th>
                        <th>Giá vé cơ bản gợi ý</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="badge bg-warning text-dark">Suất sớm</span></td>
                        <td>Trước <?= $earlyHour ?>:00</td>
                        <td class="fw-bold text-danger"><?= number_format($earlyBase) ?> VNĐ</td>
                    </tr>
                    <tr>
                        <td><span class="badge bg-secondary">Suất thường</span></td>
                        <td>Từ <?= $earlyHour ?>:00 trở đi</td>
                        <td class="fw-bold"><?= number_format($defaultBase) ?> VNĐ</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="small text-muted mt-2 mb-0">
            Giá cuối cùng trên ghế = giá cơ bản suất chiếu + phụ thu hạng ghế (VIP / Couple…). Có thể chỉnh trong Admin khi tạo suất.
        </p>
    </div>

    <div class="card border-0 shadow-sm p-4 mb-4 bg-white rounded-4">
        <h5 class="oswald-font fw-bold text-uppercase mb-3" style="color: var(--beta-orange);">
            <i class="bi bi-ticket-detailed me-2"></i>1. Phụ Thu Theo Hạng Ghế (Bảng <code>seat_types</code>)
        </h5>
        <div class="table-responsive">
            <table class="table table-bordered align-middle text-center">
                <thead class="table-primary">
                    <tr>
                        <th>Hạng Ghế</th>
                        <th>Phụ Thu Cộng Thêm (So với giá gốc suất chiếu)</th>
                        <th>Mô Tả</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($seatTypes as $st): ?>
                        <tr>
                            <td class="fw-bold fs-5" style="color: var(--beta-blue);"><?= htmlspecialchars($st['name']) ?></td>
                            <td class="fw-bold text-danger">
                                <?= (float) $st['surcharge'] > 0 ? '+' . number_format((float) $st['surcharge']) . ' VNĐ' : '0 VNĐ (Giá tiêu chuẩn)' ?>
                            </td>
                            <td><?= htmlspecialchars($st['description'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card border-0 shadow-sm p-4 bg-white rounded-4">
        <h5 class="oswald-font fw-bold text-uppercase mb-3" style="color: var(--beta-blue);">
            <i class="bi bi-camera-reels me-2"></i>2. Hệ Thống Phòng Chiếu Tại Rạp (Bảng <code>rooms</code>)
        </h5>
        <div class="row g-3">
            <?php foreach ($rooms as $r): ?>
                <div class="col-md-6">
                    <div class="p-3 border rounded-3 bg-light">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold fs-5" style="color: var(--beta-blue);"><?= htmlspecialchars($r['name']) ?></span>
                            <span class="badge bg-primary text-uppercase"><?= htmlspecialchars($r['room_type']) ?></span>
                        </div>
                        <div class="small text-muted mb-1">Sức chứa: <strong><?= (int) $r['capacity'] ?> ghế</strong></div>
                        <div class="small"><?= htmlspecialchars($r['description'] ?: 'Phòng chiếu tiêu chuẩn âm thanh sống động.') ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

