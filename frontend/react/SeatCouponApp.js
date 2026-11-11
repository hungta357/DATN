/**
 * FRONTEND MODULE: XỬ LÝ SƠ ĐỒ GHẾ, COMBO BẮP NƯỚC & KIỂM TRA MÃ GIẢM GIÁ REALTIME
 * Kết nối trực tiếp với PHP Backend API (?route=api/coupon/check)
 */
(function () {
    document.addEventListener('DOMContentLoaded', function () {
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

        if (!selectedSeatList) return;

        let currentDiscount = 0;

        function getOrderBeforeDiscount() {
            let seatSum = 0;
            seatCheckboxes.forEach(cb => {
                if (cb.checked) seatSum += parseFloat(cb.dataset.price || 0);
            });
            let conSum = 0;
            concessionInputs.forEach(inp => {
                const qty = parseInt(inp.value || '0', 10);
                if (qty > 0) conSum += qty * parseFloat(inp.dataset.price || 0);
            });
            return { seatSum, conSum, totalBefore: seatSum + conSum };
        }

        function recalc() {
            let codes = [];
            seatCheckboxes.forEach(cb => {
                const label = cb.closest('.seat-box');
                if (cb.checked) {
                    label.classList.add('seat-selected');
                    codes.push(cb.dataset.code);
                } else {
                    label.classList.remove('seat-selected');
                }
            });

            const { seatSum, conSum, totalBefore } = getOrderBeforeDiscount();
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

        seatCheckboxes.forEach(cb => cb.addEventListener('change', recalc));
        concessionInputs.forEach(inp => inp.addEventListener('input', recalc));
    });
})();

