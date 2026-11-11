<div class="container py-4">
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="row g-0">
            <div class="col-lg-5" style="background: url('https://images.unsplash.com/photo-1517604931442-7e0c8ed2963c?w=900') center/cover no-repeat; min-height: 300px;">
            </div>
            <div class="col-lg-7 p-4 p-md-5">
                <span class="badge bg-primary mb-2 px-3 py-2 oswald-font">MÔ HÌNH 1 RẠP DUY NHẤT - NHIỀU PHÒNG CHIẾU</span>
                <h2 class="oswald-font fw-bold text-uppercase mb-3" style="color: var(--beta-blue);">
                    GIỚI THIỆU RẠP <?= htmlspecialchars(strtoupper($cinemaInfo['name'])) ?>
                </h2>
                <p class="text-secondary">
                    <?= nl2br(htmlspecialchars($cinemaInfo['description'] ?: 'Rạp chiếu phim hiện đại đạt chuẩn Hollywood với hệ thống phòng chiếu IMAX, VIP, Standard âm thanh vòm sống động và mức giá vé hợp lý dành cho giới trẻ, học sinh - sinh viên và gia đình.')) ?>
                </p>

                <div class="p-3 rounded-3 bg-light border mb-4">
                    <div class="mb-2"><i class="bi bi-geo-alt-fill text-danger me-2"></i><strong>Địa chỉ rạp:</strong> <?= htmlspecialchars($cinemaInfo['address']) ?></div>
                    <div class="mb-2"><i class="bi bi-telephone-fill text-primary me-2"></i><strong>Hotline đặt vé:</strong> <?= htmlspecialchars($cinemaInfo['phone'] ?: '1900 636807') ?></div>
                    <div><i class="bi bi-envelope-fill text-warning me-2"></i><strong>Email liên hệ:</strong> <?= htmlspecialchars($cinemaInfo['email'] ?: 'cskh@betacinema.vn') ?></div>
                </div>

                <h6 class="oswald-font fw-bold text-uppercase mb-2" style="color: var(--beta-orange);">Các Phòng Chiếu Đang Hoạt Động Tại Rạp:</h6>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($rooms as $r): ?>
                        <span class="badge bg-dark px-3 py-2">
                            <i class="bi bi-tv me-1 text-info"></i> <?= htmlspecialchars($r['name']) ?> (<?= (int) $r['capacity'] ?> ghế)
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

