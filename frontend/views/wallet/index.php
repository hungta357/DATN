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

                    <form method="POST" action="?route=wallet/deposit">
                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Số tiền nạp (VNĐ)</label>
                            <input type="number" name="amount" value="200000" min="10000" step="10000" class="form-control form-control-sm" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Ghi chú giao dịch</label>
                            <input type="text" name="note" value="Nạp tiền vào ví thành viên CineNow" class="form-control form-control-sm">
                        </div>
                        <button type="submit" class="btn btn-beta-buy w-100">
                            <i class="bi bi-plus-circle me-1"></i> NẠP TIỀN NGAY
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
                                        <tr>
                                            <td><code><?= htmlspecialchars($tx['transaction_code']) ?></code></td>
                                            <td><span class="badge bg-primary"><?= htmlspecialchars($tx['type']) ?></span></td>
                                            <td class="fw-bold <?= in_array($tx['type'], ['deposit', 'refund']) ? 'text-success' : 'text-danger' ?>">
                                                <?= in_array($tx['type'], ['deposit', 'refund']) ? '+' : '-' ?><?= number_format((float) $tx['amount']) ?>đ
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
