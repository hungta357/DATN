<?php
$walletUserId = (int) ($wallet['user_id'] ?? ($_SESSION['user']['id'] ?? 0));
$defaultAmount = 35000;
$defaultAddInfo = 'NAPVI' . $walletUserId;

// Cấu hình tài khoản ngân hàng nhận chuyển khoản VietQR (thay bằng thông tin của bạn)
$bankCode = 'MB';                 // Ví dụ: MB, VCB, TCB, ACB, ICB...
$bankName = 'MB Bank';            // Tên hiển thị ngân hàng
$accountNo = '0123456789';        // Số tài khoản nhận tiền
$accountName = 'CINENOW';         // Tên chủ tài khoản
?>
<div class="container py-4">
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-white rounded-4 overflow-hidden">
                <div class="p-3 text-white oswald-font fs-5 text-uppercase" style="background: var(--beta-gradient);">
                    <i class="bi bi-wallet2 me-1"></i> VÍ THÀNH VIÊN CINENOW
                </div>
                <div class="card-body p-4">
                    <div class="text-muted small">Số dư khả dụng</div>
                    <h2 class="fw-bold text-danger mb-3"><?= number_format((float) $wallet['balance']) ?> <?= htmlspecialchars($wallet['currency']) ?></h2>

                    <form method="POST" action="?route=wallet/deposit" id="walletDepositForm">
                        <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="gateway_ref" id="gatewayRefInput" value="">
                        <input type="hidden" name="gateway_verified" id="gatewayVerifiedInput" value="0">

                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Số tiền nạp (VNĐ)</label>
                            <input type="number" name="amount" id="depositAmount" value="35000" min="10000" step="1000" class="form-control form-control-sm" required>
                            <div class="d-flex gap-1 flex-wrap mt-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2 quick-amount" data-amount="100000">100k</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2 quick-amount" data-amount="200000">200k</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2 quick-amount" data-amount="500000">500k</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2 quick-amount" data-amount="1000000">1.000k</button>
                            </div>
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Phương thức thanh toán online</label>
                            <select name="payment_channel" id="paymentChannel" class="form-select form-select-sm" required>
                                <option value="VIETQR">Chuyển khoản Ngân hàng (Mã QR VietQR)</option>
                                <option value="VNPAY">Cổng thanh toán VNPAY</option>
                                <option value="MOMO">Ví điện tử MoMo</option>
                            </select>
                        </div>

                        <!-- Khung QR & xác nhận giao dịch cho cả Ngân hàng / VNPAY / MoMo -->
                        <div id="paymentBox" class="p-3 mb-3 rounded-3 border bg-light text-center">
                            <div class="small fw-semibold text-dark mb-2" id="qrHeading">Quét mã QR Ngân hàng để chuyển khoản</div>

                            <img id="paymentQrImage"
                                 src="https://img.vietqr.io/image/<?= urlencode($bankCode) ?>-<?= urlencode($accountNo) ?>-compact2.png?amount=<?= $defaultAmount ?>&addInfo=<?= urlencode($defaultAddInfo) ?>&accountName=<?= urlencode($accountName) ?>"
                                 alt="Mã QR thanh toán"
                                 class="img-fluid rounded border bg-white p-1"
                                 style="max-width: 185px;">

                            <div id="bankInfoBox" class="mt-2 pt-2 border-top text-start small">
                                <div>Ngân hàng: <strong><?= htmlspecialchars($bankName) ?> (<?= htmlspecialchars($bankCode) ?>)</strong></div>
                                <div>STK: <strong id="bankAccText"><?= htmlspecialchars($accountNo) ?></strong></div>
                                <div>Chủ TK: <strong><?= htmlspecialchars($accountName) ?></strong></div>
                                <div>Nội dung CK: <strong class="text-danger" id="transferMemoText"><?= htmlspecialchars($defaultAddInfo) ?></strong></div>
                            </div>

                            <div id="gatewayStepBox" class="mt-2 pt-2 border-top text-start small">
                                <div class="mb-1">Mã tham chiếu: <code id="gatewayRefText">-</code></div>
                                <div class="mb-1">
                                    Hiệu lực giao dịch: <strong class="text-danger" id="txCountdownText">15:00</strong>
                                </div>
                                <div class="mb-2">
                                    Trạng thái:
                                    <span class="badge bg-warning text-dark" id="gatewayStatusBadge">Chờ thanh toán</span>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="simulateGatewayBtn">
                                        <i class="bi bi-check2-circle me-1"></i> <span id="verifyBtnLabel">Kiểm tra & xác nhận đã chuyển khoản</span>
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm d-none" id="renewTxBtn">
                                        <i class="bi bi-arrow-clockwise me-1"></i> Tạo mã giao dịch mới (15 phút)
                                    </button>
                                </div>

                                <div class="text-success fw-semibold mt-2 d-none" id="gatewaySuccessMsg">
                                    <i class="bi bi-check-circle-fill me-1"></i> <span id="successTextDetail">Hệ thống đã ghi nhận tiền vào, có thể nạp vào ví!</span>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Ghi chú giao dịch</label>
                            <input type="text" name="note" id="depositNote" value="Nạp tiền vào ví thành viên CineNow" class="form-control form-control-sm">
                        </div>

                        <button type="submit" class="btn btn-beta-buy w-100" id="submitDepositBtn" disabled>
                            <i class="bi bi-lock-fill me-1" id="submitBtnIcon"></i> <span id="submitBtnText">XÁC NHẬN NẠP VÀO VÍ</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card border-0 shadow-sm bg-white rounded-4 overflow-hidden">
                <div class="card-header bg-white py-3 fw-bold oswald-font fs-5 text-uppercase" style="color: var(--beta-blue);">
                    <i class="bi bi-clock-history me-1"></i> Lịch Sử Giao Dịch Ví Thành Viên
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0 small align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Mã GD</th>
                                    <th>Loại</th>
                                    <th>Số Tiền</th>
                                    <th>Số Dư Sau GD</th>
                                    <th>Nội Dung</th>
                                    <th>Thời Gian</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($transactions)): ?>
                                    <tr><td colspan="6" class="text-center text-muted py-4">Chưa có giao dịch nào.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($transactions as $tx): ?>
                                        <?php
                                        $isCredit = in_array($tx['type'], ['deposit', 'refund'], true);
                                        $badgeClass = match ($tx['type']) {
                                            'deposit' => 'bg-success',
                                            'refund' => 'bg-info text-dark',
                                            'payment', 'withdraw', 'purchase' => 'bg-danger',
                                            default => 'bg-secondary',
                                        };
                                        ?>
                                        <tr>
                                            <td><code><?= htmlspecialchars($tx['transaction_code']) ?></code></td>
                                            <td><span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($tx['type']) ?></span></td>
                                            <td class="fw-bold <?= $isCredit ? 'text-success' : 'text-danger' ?>">
                                                <?= $isCredit ? '+' : '-' ?><?= number_format((float) $tx['amount']) ?>đ
                                            </td>
                                            <td><?= number_format((float) $tx['balance_after']) ?>đ</td>
                                            <td><?= htmlspecialchars($tx['note'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($tx['created_at'] ?? '') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const EXPIRY_SECONDS = 15 * 60; // 15 phút

    const form = document.getElementById('walletDepositForm');
    const amountInput = document.getElementById('depositAmount');
    const channelSelect = document.getElementById('paymentChannel');
    const qrHeading = document.getElementById('qrHeading');
    const qrImage = document.getElementById('paymentQrImage');
    const bankInfoBox = document.getElementById('bankInfoBox');
    const transferMemoText = document.getElementById('transferMemoText');
    const gatewayRefText = document.getElementById('gatewayRefText');
    const gatewayRefInput = document.getElementById('gatewayRefInput');
    const gatewayVerifiedInput = document.getElementById('gatewayVerifiedInput');
    const gatewayStatusBadge = document.getElementById('gatewayStatusBadge');
    const countdownText = document.getElementById('txCountdownText');
    const simulateGatewayBtn = document.getElementById('simulateGatewayBtn');
    const renewTxBtn = document.getElementById('renewTxBtn');
    const verifyBtnLabel = document.getElementById('verifyBtnLabel');
    const gatewaySuccessMsg = document.getElementById('gatewaySuccessMsg');
    const successTextDetail = document.getElementById('successTextDetail');
    const submitDepositBtn = document.getElementById('submitDepositBtn');
    const submitBtnIcon = document.getElementById('submitBtnIcon');
    const quickButtons = document.querySelectorAll('.quick-amount');

    const userId = <?= $walletUserId ?>;
    const bankCode = <?= json_encode($bankCode) ?>;
    const accountNo = <?= json_encode($accountNo) ?>;
    const accountName = <?= json_encode($accountName) ?>;

    let remainingSeconds = EXPIRY_SECONDS;
    let countdownTimer = null;

    function buildRefCode(channel) {
        const prefix = channel === 'VIETQR' ? 'BNK' : (channel === 'VNPAY' ? 'VNP' : 'MM');
        return `${prefix}${Date.now().toString().slice(-8)}${userId}`;
    }

    function formatCountdown(sec) {
        const safeSec = Math.max(0, sec);
        const mins = Math.floor(safeSec / 60);
        const secs = safeSec % 60;
        return `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
    }

    function lockSubmitButton(locked) {
        submitDepositBtn.disabled = locked;
        if (locked) {
            submitDepositBtn.classList.add('opacity-50');
            submitBtnIcon.className = 'bi bi-lock-fill me-1';
        } else {
            submitDepositBtn.classList.remove('opacity-50');
            submitBtnIcon.className = 'bi bi-unlock-fill me-1';
        }
    }

    function startCountdown() {
        clearInterval(countdownTimer);
        remainingSeconds = EXPIRY_SECONDS;

        if (countdownText) {
            countdownText.textContent = formatCountdown(remainingSeconds);
            countdownText.classList.remove('text-muted');
            countdownText.classList.add('text-danger');
        }
        renewTxBtn?.classList.add('d-none');

        countdownTimer = setInterval(() => {
            remainingSeconds -= 1;
            if (countdownText) {
                countdownText.textContent = formatCountdown(remainingSeconds);
            }

            if (remainingSeconds <= 0) {
                clearInterval(countdownTimer);
                gatewayVerifiedInput.value = '0';
                simulateGatewayBtn.disabled = true;
                lockSubmitButton(true);
                gatewayStatusBadge.className = 'badge bg-danger';
                gatewayStatusBadge.textContent = 'Mã GD đã hết hạn (15 phút)';
                gatewaySuccessMsg.classList.add('d-none');
                renewTxBtn?.classList.remove('d-none');
            }
        }, 1000);
    }

    function resetVerification(channel) {
        gatewayVerifiedInput.value = '0';
        const ref = buildRefCode(channel);
        gatewayRefInput.value = ref;
        gatewayRefText.textContent = ref;

        gatewayStatusBadge.className = 'badge bg-warning text-dark';
        gatewayStatusBadge.textContent = channel === 'VIETQR'
            ? 'Chờ quét QR & chuyển khoản'
            : 'Chờ xác nhận từ cổng thanh toán';

        gatewaySuccessMsg.classList.add('d-none');
        simulateGatewayBtn.disabled = false;

        if (channel === 'VIETQR') {
            verifyBtnLabel.textContent = 'Kiểm tra & xác nhận đã chuyển khoản QR';
        } else if (channel === 'VNPAY') {
            verifyBtnLabel.textContent = 'Mô phỏng thanh toán & nhận xác nhận VNPAY';
        } else {
            verifyBtnLabel.textContent = 'Mô phỏng thanh toán & nhận xác nhận MoMo';
        }

        lockSubmitButton(true);
        startCountdown();
    }

    function refreshPaymentUI() {
        const rawAmount = parseInt(amountInput?.value || '35000', 10);
        const amount = Number.isNaN(rawAmount) ? 35000 : Math.max(10000, rawAmount);
        const channel = channelSelect?.value || 'VIETQR';
        const memo = `NAPVI${userId}`;

        if (transferMemoText) {
            transferMemoText.textContent = memo;
        }

        if (channel === 'VIETQR') {
            qrHeading.textContent = 'Quét mã QR Ngân hàng để chuyển khoản';
            bankInfoBox.classList.remove('d-none');
            qrImage.src = `https://img.vietqr.io/image/${encodeURIComponent(bankCode)}-${encodeURIComponent(accountNo)}-compact2.png?amount=${amount}&addInfo=${encodeURIComponent(memo)}&accountName=${encodeURIComponent(accountName)}`;
        } else if (channel === 'VNPAY') {
            qrHeading.textContent = 'Quét mã / Xác nhận qua cổng VNPAY';
            bankInfoBox.classList.add('d-none');
            qrImage.src = `https://img.vietqr.io/image/${encodeURIComponent(bankCode)}-${encodeURIComponent(accountNo)}-compact2.png?amount=${amount}&addInfo=${encodeURIComponent('VNPAY_' + memo)}&accountName=${encodeURIComponent(accountName)}`;
        } else {
            qrHeading.textContent = 'Quét mã / Xác nhận qua Ví MoMo';
            bankInfoBox.classList.add('d-none');
            qrImage.src = `https://img.vietqr.io/image/${encodeURIComponent(bankCode)}-${encodeURIComponent(accountNo)}-compact2.png?amount=${amount}&addInfo=${encodeURIComponent('MOMO_' + memo)}&accountName=${encodeURIComponent(accountName)}`;
        }
    }

    simulateGatewayBtn?.addEventListener('click', function () {
        if (remainingSeconds <= 0) {
            alert('Mã giao dịch đã hết hạn sau 15 phút. Vui lòng tạo mã giao dịch mới.');
            return;
        }

        const channel = channelSelect?.value || 'VIETQR';
        simulateGatewayBtn.disabled = true;
        gatewayStatusBadge.className = 'badge bg-info text-dark';
        gatewayStatusBadge.textContent = 'Đang kiểm tra giao dịch...';

        setTimeout(() => {
            if (remainingSeconds <= 0) {
                return;
            }
            gatewayVerifiedInput.value = '1';
            gatewayStatusBadge.className = 'badge bg-success';
            gatewayStatusBadge.textContent = 'Giao dịch thành công';

            successTextDetail.textContent = channel === 'VIETQR'
                ? 'Đã ghi nhận chuyển khoản QR thành công! Bạn có thể nạp tiền vào ví.'
                : 'Cổng thanh toán đã xác nhận thành công! Bạn có thể nạp tiền vào ví.';

            gatewaySuccessMsg.classList.remove('d-none');
            lockSubmitButton(false);
        }, 700);
    });

    renewTxBtn?.addEventListener('click', function () {
        resetVerification(channelSelect?.value || 'VIETQR');
        refreshPaymentUI();
    });

    form?.addEventListener('submit', function (e) {
        if (remainingSeconds <= 0) {
            e.preventDefault();
            alert('Phiên giao dịch đã hết hạn sau 15 phút. Vui lòng tạo mã giao dịch mới.');
            return;
        }
        if (gatewayVerifiedInput.value !== '1') {
            e.preventDefault();
            alert('Vui lòng quét mã và bấm xác nhận giao dịch thành công trước khi nạp tiền vào ví.');
        }
    });

    quickButtons.forEach((btn) => {
        btn.addEventListener('click', function () {
            if (amountInput) {
                amountInput.value = this.dataset.amount;
                resetVerification(channelSelect?.value || 'VIETQR');
                refreshPaymentUI();
            }
        });
    });

    channelSelect?.addEventListener('change', function () {
        resetVerification(this.value);
        refreshPaymentUI();
    });

    amountInput?.addEventListener('input', function () {
        resetVerification(channelSelect?.value || 'VIETQR');
        refreshPaymentUI();
    });

    resetVerification(channelSelect?.value || 'VIETQR');
    refreshPaymentUI();
});
</script>