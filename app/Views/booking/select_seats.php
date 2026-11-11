<div class="mb-3">
    <a href="?route=movie/detail&id=<?= (int) $show['movie_id'] ?>" class="text-decoration-none text-secondary small">
        <i class="bi bi-arrow-left"></i> Quay lại thông tin phim
    </a>
    <h3 class="fw-bold mt-1 mb-0"><?= htmlspecialchars($show['movie_title']) ?></h3>
    <div class="text-info small">
        Phòng: <strong><?= htmlspecialchars($show['room_name']) ?></strong> |
        Suất chiếu: <strong><?= date('H:i - d/m/Y', strtotime($show['start_time'])) ?></strong>
    </div>
</div>

<form method="POST" action="?route=booking/create" id="bookingForm">
    <input type="hidden" name="show_id" value="<?= (int) $show['id'] ?>">

    <div class="row g-4">
        <!-- Cột trái: Sơ đồ ghế (Nhánh 3 & 4: seats + show_seats) -->
        <div class="col-lg-8">
            <div class="card p-4 text-center">
                <div class="text-secondary small text-uppercase mb-1">Màn hình chiếu (Screen)</div>
                <div class="screen-bar"></div>

                <?php
                $groupedRows = [];
                foreach ($showSeats as $ss) {
                    $groupedRows[$ss['row_label']][] = $ss;
                }
                ?>

                <?php if (empty($groupedRows)): ?>
                    <div class="alert alert-warning">Suất chiếu này chưa có danh sách ghế.</div>
                <?php else: ?>
                    <?php foreach ($groupedRows as $rowLabel => $seatsInRow): ?>
                        <div class="d-flex justify-content-center align-items-center mb-1">
                            <span class="me-2 fw-bold text-secondary" style="width:24px;"><?= htmlspecialchars($rowLabel) ?></span>
                            <?php foreach ($seatsInRow as $seat): ?>
                                <?php
                                $status = $seat['status'];
                                $isSelectable = ($status === 'available') || ($status === 'held' && (int) $seat['held_by_user_id'] === (int) $user['id']);
                                $cssClass = 'seat-available';
                                if (!$isSelectable) {
                                    $cssClass = ($status === 'held') ? 'seat-held' : 'seat-booked';
                                } elseif (stripos($seat['seat_type_name'], 'VIP') !== false || stripos($seat['seat_type_name'], 'Couple') !== false) {
                                    $cssClass = 'seat-vip';
                                }
                                ?>
                                <label class="seat-box <?= $cssClass ?>"
                                       title="<?= htmlspecialchars($seat['seat_code'] . ' (' . $seat['seat_type_name'] . ') - ' . number_format((float) $seat['price']) . 'đ') ?>">
                                    <?php if ($isSelectable): ?>
                                        <input type="checkbox"
                                               name="show_seat_ids[]"
                                               value="<?= (int) $seat['id'] ?>"
                                               data-code="<?= htmlspecialchars($seat['seat_code']) ?>"
                                               data-price="<?= (float) $seat['price'] ?>"
                                               class="d-none seat-checkbox">
                                    <?php endif; ?>
                                    <?= htmlspecialchars($seat['seat_code']) ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <div class="d-flex justify-content-center flex-wrap gap-3 mt-4 pt-3 border-top border-secondary small">
                    <span><span class="seat-box seat-available" style="width:20px;height:20px;cursor:default;"></span> Ghế Thường</span>
                    <span><span class="seat-box seat-vip" style="width:20px;height:20px;cursor:default;"></span> Ghế VIP (+phụ thu)</span>
                    <span><span class="seat-box seat-selected" style="width:20px;height:20px;cursor:default;"></span> Đang chọn</span>
                    <span><span class="seat-box seat-held" style="width:20px;height:20px;cursor:default;"></span> Đang được giữ (10p)</span>
                    <span><span class="seat-box seat-booked" style="width:20px;height:20px;cursor:default;"></span> Đã đặt</span>
                </div>
            </div>

            <!-- Chọn Combo Bắp Nước (Nhánh 7: Concessions) -->
            <div class="card mt-4">
                <div class="card-header fw-bold text-warning">
                    <i class="bi bi-cup-straw me-1"></i> Chọn Combo Bắp Nước (Nhánh 7: Concessions)
                </div>
                <div class="card-body">
                    <?php if (empty($concessions)): ?>
                        <p class="text-secondary small mb-0">Hiện chưa có sản phẩm bắp nước.</p>
                    <?php else: ?>
                        <div class="row g-3">
                            <?php foreach ($concessions as $con): ?>
                                <div class="col-md-6">
                                    <div class="p-2 border border-secondary rounded d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-semibold small"><?= htmlspecialchars($con['name']) ?></div>
                                            <div class="text-warning small fw-bold"><?= number_format((float) $con['price']) ?>đ</div>
                                        </div>
                                        <div style="width: 75px;">
                                            <input type="number"
                                                   name="concessions[<?= (int) $con['id'] ?>]"
                                                   value="0" min="0" max="<?= (int) $con['stock'] ?>"
                                                   data-price="<?= (float) $con['price'] ?>"
                                                   class="form-control form-control-sm text-center concession-input">
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Cột phải: Tóm tắt đơn & Mã giảm giá (Nhánh 5 & 7) -->
        <div class="col-lg-4">
            <div class="card sticky-top" style="top: 80px;">
                <div class="card-header fw-bold bg-danger text-white">
                    <i class="bi bi-receipt me-1"></i> Tóm Tắt Đặt Vé
                </div>
                <div class="card-body">
                    <p class="mb-2 small">Ghế đã chọn: <strong id="selectedSeatList" class="text-warning">Chưa chọn</strong></p>
                    <p class="mb-2 small">Tiền ghế: <strong id="seatSubtotalText">0đ</strong></p>
                    <p class="mb-3 small">Tiền bắp nước: <strong id="concessionTotalText">0đ</strong></p>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Mã giảm giá (Coupon)</label>
                        <input type="text" name="coupon_code" class="form-control form-control-sm" placeholder="VD: WELCOME20 hoặc GIAM30K">
                        <?php if (!empty($coupons)): ?>
                            <div class="form-text text-info small">
                                Mã khả dụng:
                                <?php foreach ($coupons as $cp): ?>
                                    <code><?= htmlspecialchars($cp['code']) ?></code>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small">Ghi chú đơn hàng</label>
                        <input type="text" name="note" class="form-control form-control-sm" placeholder="Ghi chú thêm (nếu có)...">
                    </div>

                    <hr>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fw-bold">Tạm tính:</span>
                        <span class="fs-4 fw-bold text-danger" id="grandTotalText">0đ</span>
                    </div>

                    <button type="submit" class="btn btn-danger w-100 fw-bold">
                        <i class="bi bi-lock-fill me-1"></i> Giữ Ghế (FOR UPDATE) & Tiếp Tục
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const seatCheckboxes = document.querySelectorAll('.seat-checkbox');
    const concessionInputs = document.querySelectorAll('.concession-input');
    const selectedSeatList = document.getElementById('selectedSeatList');
    const seatSubtotalText = document.getElementById('seatSubtotalText');
    const concessionTotalText = document.getElementById('concessionTotalText');
    const grandTotalText = document.getElementById('grandTotalText');

    function recalc() {
        let codes = [];
        let seatSum = 0;
        seatCheckboxes.forEach(cb => {
            const label = cb.closest('.seat-box');
            if (cb.checked) {
                label.classList.add('seat-selected');
                codes.push(cb.dataset.code);
                seatSum += parseFloat(cb.dataset.price || 0);
            } else {
                label.classList.remove('seat-selected');
            }
        });

        let conSum = 0;
        concessionInputs.forEach(inp => {
            const qty = parseInt(inp.value || '0', 10);
            if (qty > 0) {
                conSum += qty * parseFloat(inp.dataset.price || 0);
            }
        });

        selectedSeatList.textContent = codes.length ? codes.join(', ') : 'Chưa chọn';
        seatSubtotalText.textContent = seatSum.toLocaleString('vi-VN') + 'đ';
        concessionTotalText.textContent = conSum.toLocaleString('vi-VN') + 'đ';
        grandTotalText.textContent = (seatSum + conSum).toLocaleString('vi-VN') + 'đ';
    }

    seatCheckboxes.forEach(cb => cb.addEventListener('change', recalc));
    concessionInputs.forEach(inp => inp.addEventListener('input', recalc));
});
</script>
