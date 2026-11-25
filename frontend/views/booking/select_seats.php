<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-3 small">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="?route=home" class="text-decoration-none">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="?route=movie/detail&id=<?= (int) $show['movie_id'] ?>" class="text-decoration-none"><?= htmlspecialchars($show['movie_title']) ?></a></li>
            <li class="breadcrumb-item active">Chọn ghế & Combo</li>
        </ol>
    </nav>

    <form method="POST" action="?route=booking/create" id="bookingForm">
<input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="show_id" value="<?= (int) $show['id'] ?>">

        <div class="row g-4">
            <!-- CỘT TRÁI: SƠ ĐỒ GHẾ PHONG CÁCH BETA CINEMAS -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm p-4 text-center bg-white rounded-4">
                    <h5 class="oswald-font fw-bold text-uppercase mb-3" style="color: var(--beta-blue);">
                        SƠ ĐỒ GHẾ — <?= htmlspecialchars(strtoupper($show['room_name'])) ?>
                    </h5>
                    <div class="text-muted small text-uppercase fw-semibold mb-1">MÀN HÌNH CHIẾU (SCREEN)</div>
                    <div class="screen-bar"></div>

                    <?php
                    $groupedRows = [];
                    $hasOwnHeldSeats = false;
                    foreach ($showSeats as $ss) {
                        $groupedRows[$ss['row_label']][] = $ss;
                        if ($ss['status'] === 'held' && (int) $ss['held_by_user_id'] === (int) $user['id']) {
                            $hasOwnHeldSeats = true;
                        }
                    }
                    ?>

                    <?php if ($hasOwnHeldSeats): ?>
                        <div class="alert alert-info text-start">
                            Bạn đang có ghế được giữ trong một đơn chờ thanh toán. Bạn có thể chọn lại các ghế đó bên dưới hoặc
                            <a href="?route=booking/my" class="alert-link">Mở đơn đặt vé</a> để thanh toán đơn cũ.
                        </div>
                    <?php endif; ?>

                    <?php if (empty($groupedRows)): ?>
                        <div class="alert alert-warning">Suất chiếu này chưa có sơ đồ ghế.</div>
                    <?php else: ?>
                        <?php foreach ($groupedRows as $rowLabel => $seatsInRow): ?>
                            <div class="d-flex justify-content-center align-items-center mb-1">
                                <span class="me-2 fw-bold text-secondary" style="width:24px;"><?= htmlspecialchars($rowLabel) ?></span>
                                <?php foreach ($seatsInRow as $seat): ?>
                                    <?php
                                    $status = $seat['status'];
                                    $isOwnHeld = ($status === 'held' && (int) $seat['held_by_user_id'] === (int) $user['id']);
                                    $isSelectable = ($status === 'available') || $isOwnHeld;
                                    $cssClass = 'seat-available';
                                    if (!$isSelectable) {
                                        $cssClass = ($status === 'held') ? 'seat-held' : 'seat-booked';
                                    } elseif (stripos($seat['seat_type_name'], 'Couple') !== false) {
                                        $cssClass = 'seat-couple';
                                    } elseif (stripos($seat['seat_type_name'], 'VIP') !== false) {
                                        $cssClass = 'seat-vip';
                                    }
                                    if (stripos($seat['seat_type_name'], 'Couple') !== false && !$isSelectable) {
                                        $cssClass .= ' seat-couple';
                                    }
                                    $seatTitle = $seat['seat_code'] . ' (' . $seat['seat_type_name'] . ') - ' . number_format((float) $seat['price']) . 'đ';
                                    if ($isOwnHeld) {
                                        $seatTitle .= ' - Đang được giữ bởi bạn (có thể chọn lại)';
                                    }
                                    ?>
                                    <label class="seat-box <?= $cssClass ?>"
                                           title="<?= htmlspecialchars($seatTitle) ?>">
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

                    <div class="d-flex justify-content-center flex-wrap gap-3 mt-4 pt-3 border-top small">
                        <span><span class="seat-box seat-available" style="width:20px;height:20px;cursor:default;"></span> Ghế thường</span>
                        <span><span class="seat-box seat-vip" style="width:20px;height:20px;cursor:default;"></span> Ghế VIP</span>
                        <span><span class="seat-box seat-couple" style="width:32px;height:20px;cursor:default;"></span> Ghế đôi (Couple)</span>
                        <span><span class="seat-box seat-selected" style="width:20px;height:20px;cursor:default;"></span> Ghế đang chọn</span>
                        <span><span class="seat-box seat-held" style="width:20px;height:20px;cursor:default;"></span> Ghế đang được giữ</span>
                        <span><span class="seat-box seat-booked" style="width:20px;height:20px;cursor:default;"></span> Ghế đã bán</span>
                    </div>
                </div>

                <!-- CHỌN COMBO BẮP NƯỚC BETA -->
                <div class="card border-0 shadow-sm mt-4 p-4 bg-white rounded-4">
                    <h5 class="oswald-font fw-bold text-uppercase mb-3" style="color: var(--beta-blue);">
                        <i class="bi bi-cup-straw text-warning me-1"></i> CHỌN COMBO BẮP NƯỚC TIẾT KIỆM
                    </h5>
                    <div class="row g-3">
                        <?php foreach ($concessions as $con): ?>
                            <div class="col-md-6">
                                <div class="p-3 border rounded-3 d-flex justify-content-between align-items-center bg-light">
                                    <div>
                                        <div class="fw-bold small" style="color: var(--beta-blue);"><?= htmlspecialchars($con['name']) ?></div>
                                        <div class="text-danger small fw-bold"><?= number_format((float) $con['price']) ?> VNĐ</div>
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
                </div>
            </div>

            <!-- CỘT PHẢI: CUỐNG VÉ TÓM TẮT KIỂU BETA CINEMAS -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm sticky-top bg-white rounded-4 overflow-hidden" style="top: 85px;">
                    <div class="p-3 text-white oswald-font fs-5 text-uppercase" style="background: var(--beta-gradient);">
                        <i class="bi bi-ticket-perforated-fill me-1"></i> THÔNG TIN VÉ ĐẶT
                    </div>
                    <div class="card-body p-4">
                        <div class="d-flex gap-3 mb-3 pb-3 border-bottom">
                            <img src="<?= htmlspecialchars($show['poster_path'] ?: 'public/images/posters/dune-part-two.svg') ?>"
                                 style="width:70px;height:100px;object-fit:cover;" class="rounded-3"
                                 onerror="this.onerror=null;this.src='public/images/posters/dune-part-two.svg';">
                            <div>
                                <h6 class="fw-bold mb-1" style="color: var(--beta-blue);"><?= htmlspecialchars($show['title_vi'] ?: $show['movie_title']) ?></h6>
                                <span class="badge bg-warning text-dark mb-1"><?= htmlspecialchars($show['age_rating'] ?: 'P') ?></span>
                                <div class="small text-muted">2D Phụ đề • <?= (int) $show['runtime'] ?> phút</div>
                            </div>
                        </div>

                        <div class="small mb-3 pb-3 border-bottom" style="border-style: dashed !important;">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Phòng chiếu:</span>
                                <strong><?= htmlspecialchars($show['room_name']) ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Suất chiếu:</span>
                                <strong class="text-primary"><?= date('H:i - d/m/Y', strtotime($show['start_time'])) ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Ghế đã chọn:</span>
                                <strong id="selectedSeatList" class="text-danger">Chưa chọn</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Tiền ghế:</span>
                                <strong id="seatSubtotalText">0đ</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Combo bắp nước:</span>
                                <strong id="concessionTotalText">0đ</strong>
                            </div>
                            <div class="d-flex justify-content-between text-success" id="discountRow" style="display:none !important;">
                                <span>Giảm giá Coupon:</span>
                                <strong id="discountAmountText">-0đ</strong>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Mã giảm giá (Voucher / Coupon)</label>
                            <div class="input-group input-group-sm">
                                <input type="text" name="coupon_code" id="couponCodeInput" class="form-control" placeholder="Nhập WELCOME20 hoặc GIAM30K">
                                <button type="button" class="btn btn-outline-primary fw-semibold" id="btnCheckCoupon">Áp dụng</button>
                            </div>
                            <div id="couponFeedback" class="small mt-1"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small">Ghi chú</label>
                            <input type="text" name="note" class="form-control form-control-sm" placeholder="Ghi chú thêm...">
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3 pt-2 border-top">
                            <span class="oswald-font fs-5 text-uppercase">TỔNG TIỀN:</span>
                            <span class="fs-4 fw-bold text-danger" id="grandTotalText">0đ</span>
                        </div>

                        <button type="submit" class="btn btn-beta-buy w-100 py-2">
                            TIẾP TỤC THANH TOÁN <i class="bi bi-arrow-right-circle ms-1"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const bookingForm = document.getElementById('bookingForm');
    const seatCheckboxes = document.querySelectorAll('.seat-checkbox');
    const concessionInputs = document.querySelectorAll('.concession-input');
    const selectedSeatList = document.getElementById('selectedSeatList');
    const seatSubtotalText = document.getElementById('seatSubtotalText');
    const concessionTotalText = document.getElementById('concessionTotalText');
    const grandTotalText = document.getElementById('grandTotalText');
    const couponCodeInput = document.getElementById('couponCodeInput');
    const btnCheckCoupon = document.getElementById('btnCheckCoupon');
    const couponFeedback = document.getElementById('couponFeedback');
    const discountRow = document.getElementById('discountRow');
    const discountAmountText = document.getElementById('discountAmountText');

    let currentDiscount = 0;

    function getOrderBeforeDiscount() {
        let seatSum = 0;
        let codes = [];
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

        return { codes, seatSum, conSum, totalBefore: seatSum + conSum };
    }

    function recalc() {
        const { codes, seatSum, conSum, totalBefore } = getOrderBeforeDiscount();
        const finalTotal = Math.max(0, totalBefore - currentDiscount);

        selectedSeatList.textContent = codes.length ? codes.join(', ') : 'Chưa chọn';
        seatSubtotalText.textContent = seatSum.toLocaleString('vi-VN') + 'đ';
        concessionTotalText.textContent = conSum.toLocaleString('vi-VN') + 'đ';

        if (discountRow && discountAmountText) {
            if (currentDiscount > 0) {
                discountRow.style.setProperty('display', 'flex', 'important');
                discountAmountText.textContent = '-' + currentDiscount.toLocaleString('vi-VN') + 'đ';
            } else {
                discountRow.style.setProperty('display', 'none', 'important');
            }
        }

        grandTotalText.textContent = finalTotal.toLocaleString('vi-VN') + 'đ';
    }

    if (btnCheckCoupon && couponCodeInput) {
        btnCheckCoupon.addEventListener('click', function () {
            const code = couponCodeInput.value.trim();
            const { totalBefore } = getOrderBeforeDiscount();
            if (!code) {
                currentDiscount = 0;
                couponFeedback.className = 'small mt-1 text-danger';
                couponFeedback.textContent = 'Vui lòng nhập mã giảm giá.';
                recalc();
                return;
            }

            btnCheckCoupon.disabled = true;
            fetch('?route=api/coupon/check', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ code: code, order_amount: totalBefore })
            })
                .then(res => res.json())
                .then(json => {
                    if (json.success) {
                        currentDiscount = parseFloat(json.discount_amount || 0);
                        couponFeedback.className = 'small mt-1 text-success fw-semibold';
                        couponFeedback.textContent = json.message;
                    } else {
                        currentDiscount = 0;
                        couponFeedback.className = 'small mt-1 text-danger';
                        couponFeedback.textContent = json.message || 'Mã không hợp lệ.';
                    }
                    recalc();
                })
                .catch(() => {
                    couponFeedback.className = 'small mt-1 text-danger';
                    couponFeedback.textContent = 'Không thể kiểm tra mã lúc này.';
                })
                .finally(() => {
                    btnCheckCoupon.disabled = false;
                });
        });
    }

    if (bookingForm) {
        bookingForm.addEventListener('submit', function (e) {
            const { codes } = getOrderBeforeDiscount();
            if (codes.length === 0) {
                e.preventDefault();
                alert('Vui lòng chọn ít nhất 1 ghế trên sơ đồ trước khi tiếp tục thanh toán.');
            }
        });
    }

    seatCheckboxes.forEach(cb => cb.addEventListener('change', recalc));
    concessionInputs.forEach(inp => inp.addEventListener('input', recalc));
});
</script>
