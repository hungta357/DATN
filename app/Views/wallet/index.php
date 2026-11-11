<div class="row g-4">
    <div class="col-md-4">
        <div class="card border-info">
            <div class="card-header bg-info text-dark fw-bold">
                <i class="bi bi-wallet2 me-1"></i> Ví Nội Bộ (Nhánh 6: Wallets)
            </div>
            <div class="card-body">
                <div class="text-secondary small">Số dư khả dụng</div>
                <h2 class="fw-bold text-warning mb-3"><?= number_format((float) $wallet['balance']) ?> <?= htmlspecialchars($wallet['currency']) ?></h2>

                <form method="POST" action="?route=wallet/deposit">
                    <div class="mb-2">
                        <label class="form-label small">Số tiền nạp (VNĐ)</label>
                        <input type="number" name="amount" value="200000" min="10000" step="10000" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Ghi chú giao dịch</label>
                        <input type="text" name="note" value="Nạp tiền vào ví nội bộ" class="form-control form-control-sm">
                    </div>
                    <button type="submit" class="btn btn-info btn-sm w-100 fw-bold">
                        <i class="bi bi-plus-circle me-1"></i> Nạp Tiền Ngay
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header fw-bold">
                <i class="bi bi-clock-history me-1"></i> Lịch Sử Giao Dịch Ví (Wallet Transactions)
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-dark table-striped mb-0 small align-middle">
                        <thead>
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
                                <tr><td colspan="6" class="text-center text-secondary py-3">Chưa có giao dịch nào.</td></tr>
                            <?php else: ?>
                                <?php foreach ($transactions as $tx): ?>
                                    <tr>
                                        <td><code><?= htmlspecialchars($tx['transaction_code']) ?></code></td>
                                        <td><span class="badge bg-secondary"><?= htmlspecialchars($tx['type']) ?></span></td>
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
