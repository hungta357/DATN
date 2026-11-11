<div class="container py-4">
    <div class="text-center mb-4">
        <h2 class="oswald-font fw-bold text-uppercase mb-1" style="color: var(--beta-blue);">
            TIN MỚI & ƯU ĐÃI ĐẶC BIỆT TẠI <?= htmlspecialchars(strtoupper($cinemaInfo['name'])) ?>
        </h2>
        <p class="text-muted small">Săn mã giảm giá (Coupon) và thưởng thức Combo Bắp Nước giá siêu hời tại rạp</p>
    </div>

    <h4 class="oswald-font fw-bold text-uppercase mb-3" style="color: var(--beta-orange);">
        <i class="bi bi-percent me-1"></i> Mã Giảm Giá Đang Áp Dụng (Nhánh 7: Coupons)
    </h4>
    <div class="row g-4 mb-5">
        <?php foreach ($coupons as $cp): ?>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden">
                    <div class="card-body p-4 d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-danger mb-2">MÃ KHUYẾN MÃI</span>
                            <h5 class="fw-bold mb-1" style="color: var(--beta-blue);"><?= htmlspecialchars($cp['name']) ?></h5>
                            <p class="small text-muted mb-2"><?= htmlspecialchars($cp['description'] ?? '') ?></p>
                            <div class="small text-secondary">
                                Đơn tối thiểu: <strong><?= number_format((float) $cp['min_order_amount']) ?>đ</strong>
                            </div>
                        </div>
                        <div class="text-center ps-3 border-start">
                            <div class="small text-muted mb-1">Nhập mã khi đặt vé</div>
                            <div class="px-3 py-2 rounded bg-light border fw-bold fs-5 text-danger font-monospace">
                                <?= htmlspecialchars($cp['code']) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <h4 class="oswald-font fw-bold text-uppercase mb-3" style="color: var(--beta-blue);">
        <i class="bi bi-cup-straw me-1"></i> Combo Bắp Nước Tại Quầy (Nhánh 7: Concessions)
    </h4>
    <div class="row g-4">
        <?php foreach ($concessions as $con): ?>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 rounded-4">
                    <div class="card-body p-4 d-flex flex-column">
                        <span class="badge bg-warning text-dark align-self-start mb-2"><?= htmlspecialchars($con['category'] ?: 'Combo') ?></span>
                        <h5 class="fw-bold mb-2" style="color: var(--beta-blue);"><?= htmlspecialchars($con['name']) ?></h5>
                        <p class="small text-muted mb-3"><?= htmlspecialchars($con['description'] ?? '') ?></p>
                        <div class="mt-auto d-flex justify-content-between align-items-center pt-3 border-top">
                            <span class="fs-5 fw-bold text-danger"><?= number_format((float) $con['price']) ?> VNĐ</span>
                            <a href="?route=schedule" class="btn btn-sm btn-beta-buy">Chọn kèm vé</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
