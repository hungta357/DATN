<div class="row g-4">
    <div class="col-lg-7">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="bi bi-ticket-detailed text-danger me-1"></i> Đơn Đặt Vé #<?= htmlspecialchars($booking['booking_code']) ?></span>
                <span class="badge <?= $booking['status'] === 'confirmed' ? 'bg-success' : 'bg-warning text-dark' ?>">
                    <?= strtoupper($booking['status']) ?>
                </span>
            </div>
            <div class="card-body">
                <h5 class="fw-bold text-info mb-1"><?= htmlspecialchars($booking['movie_title']) ?></h5>
                <p class="small text-secondary mb-3">
                    Phòng chiếu: <strong><?= htmlspecialchars($booking['room_name']) ?></strong> |
                    Suất chiếu: <strong><?= date('H:i - d/m/Y', strtotime($booking['start_time'])) ?></strong>
                </p>

                <h6 class="fw-bold small text-uppercase text-secondary">Danh sách ghế (Booking Details)</h6>
                <ul class="list-group mb-3">
                    <?php foreach ($booking['seats'] as $s): ?>
                        <li class="list-group-item bg-dark text-light border-secondary d-flex justify-content-between">
                            <span>Ghế <strong><?= htmlspecialchars($s['seat_code']) ?></strong> (<?= htmlspecialchars($s['seat_type_name']) ?>)</span>
                            <span><?= number_format((float) $s['seat_price']) ?>đ</span>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <?php if (!empty($booking['concessions'])): ?>
                    <h6 class="fw-bold small text-uppercase text-secondary">Bắp nước đi kèm (Booking Concessions)</h6>
                    <ul class="list-group mb-3">
                        <?php foreach ($booking['concessions'] as $c): ?>
                            <li class="list-group-item bg-dark text-light border-secondary d-flex justify-content-between">
                                <span><?= htmlspecialchars($c['concession_name']) ?> x <?= (int) $c['quantity'] ?></span>
                                <span><?= number_format((float) $c['line_total']) ?>đ</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <div class="border-top border-secondary pt-3 small">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Tiền ghế (Subtotal):</span>
                        <span><?= number_format((float) $booking['subtotal']) ?>đ</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span>Tiền bắp nước (Concession Total):</span>
                        <span><?= number_format((float) $booking['concession_total']) ?>đ</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1 text-success">
                        <span>Giảm giá Coupon (Discount):</span>
                        <span>-<?= number_format((float) $booking['discount_amount']) ?>đ</span>
                    </div>
                    <div class="d-flex justify-content-between fs-5 fw-bold text-danger mt-2">
                        <span>Tổng thanh toán:</span>
                        <span><?= number_format((float) $booking['total_amount']) ?>đ</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <?php if ($booking['status'] === 'pending'): ?>
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark fw-bold">
                    <i class="bi bi-credit-card me-1"></i> Chọn Phương Thức Thanh Toán (Nhánh 6)
                </div>
                <div class="card-body">
                    <div class="alert alert-dark border-secondary small mb-3">
                        Số dư Ví nội bộ hiện tại của bạn:
                        <strong class="text-info"><?= number_format((float) $wallet['balance']) ?> <?= htmlspecialchars($wallet['currency']) ?></strong>
                        <a href="?route=wallet" class="ms-2 text-warning">[Nạp thêm]</a>
                    </div>

                    <form method="POST" action="?route=booking/pay">
                        <input type="hidden" name="booking_id" value="<?= (int) $booking['id'] ?>">
                        <?php foreach ($paymentMethods as $idx => $pm): ?>
                            <div class="form-check mb-2 p-2 border border-secondary rounded">
                                <input class="form-check-input ms-1" type="radio"
                                       name="payment_method_id"
                                       id="pm_<?= (int) $pm['id'] ?>"
                                       value="<?= (int) $pm['id'] ?>"
                                       <?= $idx === 0 ? 'checked' : '' ?>>
                                <label class="form-check-label ms-2" for="pm_<?= (int) $pm['id'] ?>">
                                    <strong><?= htmlspecialchars($pm['name']) ?></strong> (<code><?= htmlspecialchars($pm['code']) ?></code>)
                                    <div class="small text-secondary"><?= htmlspecialchars($pm['description'] ?? '') ?></div>
                                </label>
                            </div>
                        <?php endforeach; ?>

                        <button type="submit" class="btn btn-success w-100 fw-bold mt-3">
                            <i class="bi bi-check2-circle me-1"></i> Xác Nhận Thanh Toán & Xuất Vé QR
                        </button>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="card border-success">
                <div class="card-header bg-success text-white fw-bold">
                    <i class="bi bi-qr-code-scan me-1"></i> Vé Điện Tử Đã Phát Hành (Nhánh 5: Tickets)
                </div>
                <div class="card-body">
                    <?php foreach ($booking['seats'] as $s): ?>
                        <div class="p-3 mb-3 border border-secondary rounded text-center bg-dark">
                            <div class="fw-bold text-warning mb-1">Ghế <?= htmlspecialchars($s['seat_code']) ?> - <?= htmlspecialchars($s['seat_type_name']) ?></div>
                            <div class="small mb-2">Mã vé: <code><?= htmlspecialchars($s['ticket_code'] ?? '') ?></code></div>
                            <?php if (!empty($s['qr_code'])): ?>
                                <img src="<?= htmlspecialchars($s['qr_code']) ?>" alt="QR Code" class="bg-white p-2 rounded mb-2" style="width:140px;height:140px;">
                            <?php endif; ?>
                            <div>
                                <span class="badge <?= ($s['ticket_status'] === 'valid') ? 'bg-info text-dark' : 'bg-secondary' ?>">
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
