<!-- Lấy mốc thời gian hết hạn và Kiểm tra hết hạn & trạng thái hoạt động để tạo đồng hồ -->
<?php
$expiresAtRaw = $booking['expires_at']
    ?? ($booking['seats'][0]['hold_expires_at'] ?? '');

if (empty($expiresAtRaw) && !empty($booking['booking_time'])) {
    $expiresAtRaw = date('Y-m-d H:i:s', strtotime($booking['booking_time']) + 600);
}

$isExpiredByTime = !empty($expiresAtRaw) && strtotime($expiresAtRaw) <= time();
$isPendingActive = ($booking['status'] === 'pending') && !$isExpiredByTime;
?>


<div class="container py-4">
    <?php if ($booking['status'] === 'pending'): ?>
        <div id="hold-countdown-banner"
             class="alert <?= $isExpiredByTime ? 'alert-secondary' : 'alert-danger' ?> d-flex flex-wrap align-items-center justify-content-between gap-2 rounded-4 shadow-sm mb-4 px-4 py-3"
             style="border-left: 6px solid #dc3545;">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-alarm-fill fs-4 text-danger"></i>
                <div>
                    <div class="fw-bold text-danger">THỜI GIAN GIỮ GHẾ CÒN LẠI</div>
                    <div class="small text-dark">Vui lòng hoàn tất thanh toán trước khi hết giờ để tránh bị hủy giữ chỗ.</div>
                </div>
            </div>
            <div class="text-end">
                <span id="countdown-timer"
                      class="badge bg-danger text-white fs-4 px-3 py-2 font-monospace"
                      data-expires-at="<?= htmlspecialchars($expiresAtRaw, ENT_QUOTES, 'UTF-8') ?>">
                    <?= $isExpiredByTime ? '00:00' : '10:00' ?>
                </span>
            </div>
        </div>
    <?php endif; ?>

    <!-- Giao diện khung đếm ngược -->
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm bg-white rounded-4 mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                    <span class="oswald-font fs-5 fw-bold" style="color: var(--beta-blue);">
                        <i class="bi bi-ticket-detailed text-danger me-1"></i> ĐƠN ĐẶT VÉ #<?= htmlspecialchars($booking['booking_code']) ?>
                    </span>
                    <span class="badge <?= $booking['status'] === 'confirmed' ? 'bg-success' : ($isPendingActive ? 'bg-warning text-dark' : 'bg-secondary') ?>">
                        <?= $isExpiredByTime && $booking['status'] === 'pending' ? 'EXPIRED' : strtoupper($booking['status']) ?>
                    </span>
                </div>
                <div class="card-body p-4">
                    <h4 class="fw-bold mb-1" style="color: var(--beta-blue);"><?= htmlspecialchars($booking['movie_title']) ?></h4>
                    <p class="small text-muted mb-3">
                        Phòng chiếu: <strong><?= htmlspecialchars($booking['room_name']) ?></strong> |
                        Suất chiếu: <strong class="text-primary"><?= date('H:i - d/m/Y', strtotime($booking['start_time'])) ?></strong>
                    </p>

                    <h6 class="fw-bold small text-uppercase text-secondary">Danh sách ghế đã chọn</h6>
                    <ul class="list-group mb-3">
                        <?php foreach ($booking['seats'] as $s): ?>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Ghế <strong><?= htmlspecialchars($s['seat_code']) ?></strong> (<?= htmlspecialchars($s['seat_type_name']) ?>)</span>
                                <span class="fw-semibold"><?= number_format((float) $s['seat_price']) ?>đ</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <?php if (!empty($booking['concessions'])): ?>
                        <h6 class="fw-bold small text-uppercase text-secondary">Combo Bắp Nước</h6>
                        <ul class="list-group mb-3">
                            <?php foreach ($booking['concessions'] as $c): ?>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span><?= htmlspecialchars($c['concession_name']) ?> x <?= (int) $c['quantity'] ?></span>
                                    <span class="fw-semibold"><?= number_format((float) $c['line_total']) ?>đ</span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                    <div class="border-top pt-3 small">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Tiền ghế:</span>
                            <span><?= number_format((float) $booking['subtotal']) ?>đ</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Tiền bắp nước:</span>
                            <span><?= number_format((float) $booking['concession_total']) ?>đ</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1 text-success fw-semibold">
                            <span>Giảm giá Voucher/Coupon:</span>
                            <span>-<?= number_format((float) $booking['discount_amount']) ?>đ</span>
                        </div>
                        <div class="d-flex justify-content-between fs-5 fw-bold text-danger mt-2">
                            <span>TỔNG THANH TOÁN:</span>
                            <span><?= number_format((float) $booking['total_amount']) ?> VNĐ</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <?php if ($isPendingActive): ?>
                <div class="card border-0 shadow-sm bg-white rounded-4 overflow-hidden">
                    <div class="p-3 text-white oswald-font fs-5 text-uppercase" style="background: var(--beta-gradient);">
                        <i class="bi bi-credit-card me-1"></i> CHỌN PHƯƠNG THỨC THANH TOÁN
                    </div>
                    <div class="card-body p-4">
                        <div class="alert alert-info small mb-3">
                            Số dư <strong>Ví Thành Viên CineNow</strong> của bạn:
                            <strong class="text-danger"><?= number_format((float) $wallet['balance']) ?> <?= htmlspecialchars($wallet['currency']) ?></strong>
                            <a href="?route=wallet" class="ms-2 fw-bold">[Nạp thêm]</a>
                        </div>

                        <form id="checkout-payment-form" method="POST" action="?route=booking/pay">
                            <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="booking_id" value="<?= (int) $booking['id'] ?>">
                            <?php foreach ($paymentMethods as $idx => $pm): ?>
                                <div class="form-check mb-2 p-3 border rounded-3 bg-light">
                                    <input class="form-check-input ms-1" type="radio"
                                           name="payment_method_id"
                                           id="pm_<?= (int) $pm['id'] ?>"
                                           value="<?= (int) $pm['id'] ?>"
                                           <?= $idx === 0 ? 'checked' : '' ?>>
                                    <label class="form-check-label ms-2" for="pm_<?= (int) $pm['id'] ?>">
                                        <strong style="color: var(--beta-blue);"><?= htmlspecialchars($pm['name']) ?></strong> (<code><?= htmlspecialchars($pm['code']) ?></code>)
                                        <div class="small text-muted"><?= htmlspecialchars($pm['description'] ?? '') ?></div>
                                    </label>
                                </div>
                            <?php endforeach; ?>

                            <button id="btn-pay-submit" type="submit" class="btn btn-beta-buy w-100 py-2 mt-3">
                                <i class="bi bi-check2-circle me-1"></i> XÁC NHẬN THANH TOÁN & NHẬN VÉ QR
                            </button>
                        </form>
                    </div>
                </div>
            <?php elseif ($booking['status'] === 'confirmed'): ?>
                <div class="card border-0 shadow-sm bg-white rounded-4 overflow-hidden">
                    <div class="card-header bg-success text-white oswald-font fs-5 text-uppercase d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-qr-code-scan me-1"></i> VÉ ĐIỆN TỬ QR CODE ĐÃ PHÁT HÀNH</span>
                        <button type="button" class="btn btn-sm btn-light text-dark fw-bold" onclick="window.print()">
                            <i class="bi bi-printer-fill me-1"></i> In vé
                        </button>
                    </div>
                    <div class="card-body p-4">
                        <?php foreach ($booking['seats'] as $s): ?>
                            <div class="p-3 mb-3 border rounded-3 text-center bg-light">
                                <div class="fw-bold fs-5" style="color: var(--beta-blue);">Ghế <?= htmlspecialchars($s['seat_code']) ?> — <?= htmlspecialchars($s['seat_type_name']) ?></div>
                                <div class="small mb-2">Mã vé: <code><?= htmlspecialchars($s['ticket_code'] ?? '') ?></code></div>
                                <?php if (!empty($s['qr_code'])): ?>
                                    <img src="<?= htmlspecialchars($s['qr_code']) ?>" alt="QR Code" class="bg-white p-2 border rounded mb-2" style="width:145px;height:145px;">
                                <?php endif; ?>
                                <div>
                                    <span class="badge <?= ($s['ticket_status'] === 'valid') ? 'bg-success' : 'bg-secondary' ?>">
                                        Trạng thái vé: <?= htmlspecialchars($s['ticket_status'] ?? 'valid') ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="card border-0 shadow-sm bg-white rounded-4">
                    <div class="card-body p-4 text-center">
                        <div class="display-6 text-secondary mb-2"><i class="bi bi-clock-history"></i></div>
                        <h5 class="fw-bold">Đơn đặt vé đã <?= htmlspecialchars($booking['status'] === 'expired' ? 'hết hạn' : 'đóng') ?></h5>
                        <p class="text-muted mb-4">Ghế đã được trả lại để khách khác có thể đặt. Vui lòng chọn một suất chiếu khác.</p>
                        <a href="?route=schedule" class="btn btn-beta-buy">XEM LỊCH CHIẾU</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Tạo bộ đếm ngược 10 phút thanh toán bằng JS -->
<?php if ($booking['status'] === 'pending'): ?>
<script>
(function () {
    const timerEl = document.getElementById('countdown-timer');
    const bannerEl = document.getElementById('hold-countdown-banner');
    const submitBtn = document.getElementById('btn-pay-submit');
    if (!timerEl) return;

    const rawExpires = (timerEl.dataset.expiresAt || '').trim();
    const targetMs = rawExpires
        ? new Date(rawExpires.replace(' ', 'T')).getTime()
        : (Date.now() + 10 * 60 * 1000);

    function tick() {
        const diffSec = Math.floor((targetMs - Date.now()) / 1000);
        if (diffSec <= 0) {
            timerEl.textContent = '00:00';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.classList.add('disabled', 'opacity-50');
            }
            if (bannerEl) {
                bannerEl.classList.remove('alert-danger');
                bannerEl.classList.add('alert-secondary');
                bannerEl.innerHTML = `
                    <div class="d-flex align-items-center gap-2 text-danger fw-bold">
                        <i class="bi bi-x-circle-fill fs-5"></i>
                        <span>Đã hết thời gian giữ ghế! Vui lòng quay lại trang chọn ghế để đặt lại.</span>
                    </div>
                    <a href="?route=schedule" class="btn btn-sm btn-danger fw-bold">Chọn suất chiếu khác</a>
                `;
            }
            clearInterval(timerId);
            return;
        }

        const mins = Math.floor(diffSec / 60);
        const secs = diffSec % 60;
        timerEl.textContent = String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
    }

    tick();
    const timerId = setInterval(tick, 1000);
})();
</script>
<?php endif; ?>