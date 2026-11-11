<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm bg-white rounded-4 mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                    <span class="oswald-font fs-5 fw-bold" style="color: var(--beta-blue);">
                        <i class="bi bi-ticket-detailed text-danger me-1"></i> ĐƠN ĐẶT VÉ #<?= htmlspecialchars($booking['booking_code']) ?>
                    </span>
                    <span class="badge <?= $booking['status'] === 'confirmed' ? 'bg-success' : 'bg-warning text-dark' ?>">
                        <?= strtoupper($booking['status']) ?>
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
            <?php if ($booking['status'] === 'pending'): ?>
                <div class="card border-0 shadow-sm bg-white rounded-4 overflow-hidden">
                    <div class="p-3 text-white oswald-font fs-5 text-uppercase" style="background: var(--beta-gradient);">
                        <i class="bi bi-credit-card me-1"></i> CHỌN PHƯƠNG THỨC THANH TOÁN
                    </div>
                    <div class="card-body p-4">
                        <div class="alert alert-info small mb-3">
                            Số dư <strong>Ví Thành Viên Beta</strong> của bạn:
                            <strong class="text-danger"><?= number_format((float) $wallet['balance']) ?> <?= htmlspecialchars($wallet['currency']) ?></strong>
                            <a href="?route=wallet" class="ms-2 fw-bold">[Nạp thêm]</a>
                        </div>

                        <form method="POST" action="?route=booking/pay">
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

                            <button type="submit" class="btn btn-beta-buy w-100 py-2 mt-3">
                                <i class="bi bi-check2-circle me-1"></i> XÁC NHẬN THANH TOÁN & NHẬN VÉ QR
                            </button>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="card border-0 shadow-sm bg-white rounded-4 overflow-hidden">
                    <div class="card-header bg-success text-white oswald-font fs-5 text-uppercase">
                        <i class="bi bi-qr-code-scan me-1"></i> VÉ ĐIỆN TỬ QR CODE ĐÃ PHÁT HÀNH
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
            <?php endif; ?>
        </div>
    </div>
</div>
