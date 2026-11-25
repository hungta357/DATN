<?php
$statusLabels = [
    'open' => 'Đang mời thành viên',
    'pending_payment' => 'Chờ thanh toán',
    'paid' => 'Đã thanh toán',
    'expired' => 'Đã hết hạn',
];
$isOwner = !empty($currentMember['is_owner']);
$memberCanJoin = $group && !$isMember && $group['status'] === 'open' && (int) $group['member_count'] < (int) $group['member_limit'];
?>
<div class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h2 class="oswald-font fw-bold text-uppercase mb-1" style="color:var(--beta-blue)">Đặt vé nhóm</h2>
            <p class="text-muted mb-0">Mời bạn bè cùng chọn ghế và trả chung hoặc chia tiền.</p>
        </div>
        <a href="?route=booking/my" class="btn btn-outline-secondary"><i class="bi bi-ticket-perforated me-1"></i>Vé của tôi</a>
    </div>

    <?php if (!$group): ?>
        <div class="row g-4">
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white fw-bold">Tạo nhóm mới</div>
                    <div class="card-body">
                        <?php if (empty($shows)): ?>
                            <div class="alert alert-warning mb-0">Hiện chưa có suất chiếu tương lai để tạo nhóm.</div>
                        <?php else: ?>
                            <form method="POST" action="?route=booking/group/create">
                                <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                <div class="mb-3">
                                    <label class="form-label">Phim và suất chiếu</label>
                                    <select name="show_id" class="form-select" required>
                                        <option value="">Chọn suất chiếu</option>
                                        <?php foreach ($shows as $show): ?>
                                            <?php if ((int) $show['available_seats'] >= 2): ?>
                                                <option value="<?= (int) $show['id'] ?>">
                                                    <?= htmlspecialchars($show['movie_title'] . ' · ' . $show['room_name'] . ' · ' . date('H:i d/m/Y', strtotime($show['start_time'])), ENT_QUOTES, 'UTF-8') ?>
                                                </option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Số thành viên (bao gồm bạn)</label>
                                    <select name="member_limit" class="form-select" required>
                                        <?php foreach (range(2, 8) as $count): ?>
                                            <option value="<?= $count ?>"><?= $count ?> người</option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <fieldset class="mb-3">
                                    <legend class="form-label">Cách thanh toán</legend>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_mode" value="shared" id="group-shared" checked>
                                        <label class="form-check-label" for="group-shared">Người tạo trả toàn bộ</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_mode" value="split" id="group-split">
                                        <label class="form-check-label" for="group-split">Mỗi thành viên trả phần của mình bằng ví</label>
                                    </div>
                                </fieldset>
                                <div class="small text-muted mb-3">Nhóm mở trong 30 phút để mọi người tham gia và chọn mỗi người một ghế. Sau khi xác nhận, ghế được giữ 10 phút.</div>
                                <button class="btn btn-beta-buy w-100">Tạo nhóm và nhận mã mời</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white fw-bold">Nhóm của tôi</div>
                    <?php if (empty($groups)): ?>
                        <div class="card-body text-muted">Bạn chưa tham gia nhóm đặt vé nào.</div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($groups as $myGroup): ?>
                                <a class="list-group-item list-group-item-action py-3" href="?route=booking/group&amp;code=<?= urlencode($myGroup['invite_code']) ?>">
                                    <div class="d-flex justify-content-between gap-2">
                                        <div>
                                            <strong><?= htmlspecialchars($myGroup['movie_title'], ENT_QUOTES, 'UTF-8') ?></strong>
                                            <div class="small text-muted"><?= htmlspecialchars($myGroup['room_name'], ENT_QUOTES, 'UTF-8') ?> · <?= date('H:i d/m/Y', strtotime($myGroup['start_time'])) ?></div>
                                            <div class="small">Mã mời: <code><?= htmlspecialchars($myGroup['invite_code'], ENT_QUOTES, 'UTF-8') ?></code> · <?= (int) $myGroup['member_count'] ?>/<?= (int) $myGroup['member_limit'] ?> người</div>
                                        </div>
                                        <span class="badge <?= $myGroup['status'] === 'paid' ? 'bg-success' : ($myGroup['status'] === 'expired' ? 'bg-secondary' : 'bg-warning text-dark') ?> align-self-start"><?= htmlspecialchars($statusLabels[$myGroup['status']] ?? $myGroup['status'], ENT_QUOTES, 'UTF-8') ?></span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <span class="badge <?= $group['status'] === 'paid' ? 'bg-success' : ($group['status'] === 'expired' ? 'bg-secondary' : 'bg-warning text-dark') ?> mb-2"><?= htmlspecialchars($statusLabels[$group['status']] ?? $group['status'], ENT_QUOTES, 'UTF-8') ?></span>
                        <h3 class="oswald-font fw-bold mb-1"><?= htmlspecialchars($group['movie_title'], ENT_QUOTES, 'UTF-8') ?></h3>
                        <div><?= htmlspecialchars($group['room_name'], ENT_QUOTES, 'UTF-8') ?> · <?= date('H:i d/m/Y', strtotime($group['start_time'])) ?></div>
                        <div class="small text-muted mt-1">Tạo bởi <?= htmlspecialchars($group['creator_name'], ENT_QUOTES, 'UTF-8') ?> · Hạn nhóm <?= date('H:i d/m/Y', strtotime($group['expires_at'])) ?></div>
                    </div>
                    <div class="text-lg-end">
                        <div class="small text-muted">Mã mời</div>
                        <code class="fs-5"><?= htmlspecialchars($group['invite_code'], ENT_QUOTES, 'UTF-8') ?></code>
                        <div><a href="?route=booking/group&amp;code=<?= urlencode($group['invite_code']) ?>" class="small">Liên kết mời thành viên</a></div>
                    </div>
                </div>
                <?php if ($group['booking_status'] === 'pending' && $group['total_amount'] !== null): ?>
                    <div class="alert alert-info mt-3 mb-0">
                        Tổng tiền nhóm: <strong><?= number_format((float) $group['total_amount']) ?>đ</strong>
                        · Giữ ghế đến <?= $group['expires_at'] ? date('H:i d/m/Y', strtotime($group['expires_at'])) : '' ?>
                    </div>
                <?php elseif ($group['booking_status'] === 'confirmed'): ?>
                    <div class="alert alert-success mt-3 mb-0">Thanh toán hoàn tất. Vé điện tử đã được phát hành cho các thành viên.</div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($memberCanJoin): ?>
            <div class="alert alert-primary d-flex flex-wrap justify-content-between align-items-center gap-2">
                <span>Nhóm còn chỗ (<?= (int) $group['member_count'] ?>/<?= (int) $group['member_limit'] ?>). Tham gia để chọn ghế của bạn.</span>
                <form method="POST" action="?route=booking/group/join">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="invite_code" value="<?= htmlspecialchars($group['invite_code'], ENT_QUOTES, 'UTF-8') ?>">
                    <button class="btn btn-primary btn-sm">Tham gia nhóm</button>
                </form>
            </div>
        <?php elseif (!$isMember && $group['status'] === 'open'): ?>
            <div class="alert alert-secondary">Bạn cần đăng nhập bằng tài khoản thành viên nhóm hoặc nhóm đã đủ người.</div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white fw-bold">Thành viên & ghế</div>
                    <div class="list-group list-group-flush">
                        <?php foreach ($group['members'] as $groupMember): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center gap-2">
                                <div>
                                    <strong><?= htmlspecialchars($groupMember['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                                    <?php if ($groupMember['is_owner']): ?><span class="badge bg-primary ms-1">Người tạo</span><?php endif; ?>
                                    <div class="small text-muted">
                                        <?= $groupMember['seat_code'] ? 'Ghế ' . htmlspecialchars($groupMember['seat_code'], ENT_QUOTES, 'UTF-8') : 'Chưa chọn ghế' ?>
                                        <?php if ($group['payment_mode'] === 'split' && isset($groupMember['share_amount'])): ?>
                                            · <?= !empty($groupMember['contribution_status']) && $groupMember['contribution_status'] === 'paid' ? 'Đã thanh toán' : number_format((float) $groupMember['share_amount']) . 'đ' ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php if (!empty($groupMember['contribution_status']) && $groupMember['contribution_status'] === 'paid'): ?>
                                    <span class="badge bg-success">Đã trả</span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="card-body small text-muted">
                        Hình thức: <?= $group['payment_mode'] === 'split' ? 'Mỗi thành viên trả phần bằng ví nội bộ' : 'Người tạo thanh toán toàn bộ' ?>.
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <?php if ($isMember && $group['status'] === 'open'): ?>
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white fw-bold">Chọn ghế của bạn</div>
                        <div class="card-body">
                            <?php if (empty($availableSeats)): ?>
                                <div class="alert alert-warning mb-0">Không còn ghế trống phù hợp trong suất chiếu này.</div>
                            <?php else: ?>
                                <form method="POST" action="?route=booking/group/seat" class="row g-2 align-items-end">
                                    <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="group_id" value="<?= (int) $group['id'] ?>">
                                    <div class="col-md-8">
                                        <label class="form-label">Ghế còn trống</label>
                                        <select name="show_seat_id" class="form-select" required>
                                            <option value="">Chọn ghế</option>
                                            <?php foreach ($availableSeats as $seat): ?>
                                                <option value="<?= (int) $seat['id'] ?>" <?= (int) ($currentMember['show_seat_id'] ?? 0) === (int) $seat['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($seat['seat_code'] . ' · ' . $seat['seat_type_name'], ENT_QUOTES, 'UTF-8') ?> — <?= number_format((float) $seat['price']) ?>đ
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4"><button class="btn btn-outline-primary w-100">Lưu ghế</button></div>
                                </form>
                            <?php endif; ?>
                        </div>
                        <?php if ($isOwner): ?>
                            <div class="card-footer bg-white">
                                <div class="small text-muted mb-2">Cần đủ <?= (int) $group['member_limit'] ?> thành viên và mỗi người chọn một ghế.</div>
                                <?php if ((int) $group['member_count'] === (int) $group['member_limit'] && (int) $group['assigned_seats'] === (int) $group['member_limit']): ?>
                                    <form method="POST" action="?route=booking/group/finalize">
                                        <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="group_id" value="<?= (int) $group['id'] ?>">
                                        <button class="btn btn-beta-buy w-100">Xác nhận nhóm và giữ ghế 10 phút</button>
                                    </form>
                                <?php else: ?>
                                    <button class="btn btn-secondary w-100" disabled>Chờ đủ thành viên và ghế</button>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php elseif ($isMember && $group['status'] === 'pending_payment' && $group['payment_mode'] === 'split'): ?>
                    <?php if ($currentMember && $currentMember['contribution_status'] !== 'paid'): ?>
                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-header bg-white fw-bold">Thanh toán phần của bạn</div>
                            <div class="card-body">
                                <p>Phần cần trả: <strong class="text-danger"><?= number_format((float) ($currentMember['share_amount'] ?? 0)) ?>đ</strong></p>
                                <p>Số dư ví: <strong><?= number_format((float) ($wallet['balance'] ?? 0)) ?>đ</strong></p>
                                <a href="?route=wallet" class="small d-inline-block mb-3">Nạp tiền vào ví</a>
                                <form method="POST" action="?route=booking/group/pay-share">
                                    <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="group_id" value="<?= (int) $group['id'] ?>">
                                    <button class="btn btn-beta-buy w-100" <?= (float) ($wallet['balance'] ?? 0) < (float) ($currentMember['share_amount'] ?? 0) ? 'disabled' : '' ?>>Thanh toán bằng ví</button>
                                </form>
                                <div class="small text-muted mt-2">Khi tất cả thành viên hoàn tất phần tiền, hệ thống phát hành vé cho cả nhóm.</div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-success">Bạn đã thanh toán phần của mình. Đang chờ các thành viên còn lại.</div>
                    <?php endif; ?>
                <?php elseif ($isMember && $group['status'] === 'pending_payment' && $group['payment_mode'] === 'shared' && $isOwner): ?>
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body">
                            <p>Nhóm đã giữ ghế. Hoàn tất thanh toán chung trước khi hết hạn.</p>
                            <a class="btn btn-beta-buy w-100" href="?route=booking/checkout&amp;id=<?= (int) $group['booking_id'] ?>">Thanh toán đơn nhóm</a>
                        </div>
                    </div>
                <?php elseif ($isMember && $group['status'] === 'pending_payment'): ?>
                    <div class="alert alert-info">Người tạo nhóm đang hoàn tất khoản thanh toán chung.</div>
                <?php elseif ($isMember && $group['status'] === 'paid' && !empty($group['booking_id'])): ?>
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body">
                            <p>Thanh toán nhóm đã hoàn tất. Mở vé điện tử và mã QR tại đây.</p>
                            <a class="btn btn-success w-100" href="?route=booking/checkout&amp;id=<?= (int) $group['booking_id'] ?>">Xem vé nhóm</a>
                        </div>
                    </div>
                <?php elseif ($group['status'] === 'expired'): ?>
                    <div class="alert alert-secondary">Nhóm đã hết hạn. Bạn có thể tạo nhóm mới.</div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
