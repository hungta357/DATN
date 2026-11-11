<!-- HERO BANNER SLIDER GIỐNG BETA CINEMAS -->
<div id="betaHeroCarousel" class="carousel slide mb-4 shadow-sm" data-bs-ride="carousel">
    <div class="carousel-indicators">
        <button type="button" data-bs-target="#betaHeroCarousel" data-bs-slide-to="0" class="active"></button>
        <button type="button" data-bs-target="#betaHeroCarousel" data-bs-slide-to="1"></button>
    </div>
    <div class="carousel-inner">
        <div class="carousel-item active">
            <div class="position-relative" style="height: 390px; background: linear-gradient(90deg, #021b35 0%, #03599d 55%, #111 100%);">
                <img src="https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=1600"
                     class="w-100 h-100" style="object-fit: cover; opacity: 0.35;" alt="Banner 1">
                <div class="carousel-caption text-start d-flex flex-column justify-content-center h-100" style="top:0; bottom:0;">
                    <span class="badge bg-warning text-dark align-self-start mb-2 px-3 py-2 oswald-font fs-6">RẠP PHIM ĐỘC LẬP • ÂM THANH DOLBY ATMOS</span>
                    <h1 class="display-5 fw-bold text-white oswald-font text-uppercase mb-2">
                        TRẢI NGHIỆM ĐIỆN ẢNH ĐỈNH CAO TẠI <?= htmlspecialchars(strtoupper($cinemaInfo['name'])) ?>
                    </h1>
                    <p class="lead text-light mb-3 d-none d-md-block">
                        Đặt vé online chọn ghế đẹp ngay lập tức – Nhập mã <strong>WELCOME20</strong> giảm ngay 20% khi mua kèm Combo Bắp Nước!
                    </p>
                    <div>
                        <a href="?route=schedule" class="btn btn-beta-buy btn-lg px-4 me-2">
                            <i class="bi bi-ticket-perforated-fill me-1"></i> XEM LỊCH CHIẾU HÔM NAY
                        </a>
                        <a href="?route=promotions" class="btn btn-outline-light btn-lg oswald-font">
                            ƯU ĐÃI & COMBO
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="carousel-item">
            <div class="position-relative" style="height: 390px; background: linear-gradient(90deg, #3b0d0c 0%, #f36523 70%, #111 100%);">
                <img src="https://images.unsplash.com/photo-1517604931442-7e0c8ed2963c?w=1600"
                     class="w-100 h-100" style="object-fit: cover; opacity: 0.35;" alt="Banner 2">
                <div class="carousel-caption text-start d-flex flex-column justify-content-center h-100" style="top:0; bottom:0;">
                    <span class="badge bg-light text-danger align-self-start mb-2 px-3 py-2 oswald-font fs-6">ĐẶC QUYỀN THÀNH VIÊN CINENOW</span>
                    <h1 class="display-5 fw-bold text-white oswald-font text-uppercase mb-2">
                        THANH TOÁN VÍ NỘI BỘ • NHẬN VÉ QR CODE TRONG 3 GIÂY
                    </h1>
                    <p class="lead text-light mb-3 d-none d-md-block">
                        Không cần xếp hàng chờ đợi tại quầy – Quét mã QR trực tiếp tại cửa phòng chiếu!
                    </p>
                    <div>
                        <a href="?route=wallet" class="btn btn-light text-danger fw-bold btn-lg oswald-font px-4">
                            <i class="bi bi-wallet2 me-1"></i> NẠP VÍ THÀNH VIÊN NGAY
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#betaHeroCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon"></span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#betaHeroCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon"></span>
    </button>
</div>

<div class="container">
    <!-- 3 TAB LỚN GIỐNG HỆT BETA CINEMAS -->
    <div class="beta-movie-tabs">
        <a href="?route=home&status=coming_soon" class="beta-movie-tab <?= ($currentStatus === 'coming_soon') ? 'active' : '' ?>">
            PHIM SẮP CHIẾU
        </a>
        <a href="?route=home&status=now_showing" class="beta-movie-tab <?= ($currentStatus === 'now_showing') ? 'active' : '' ?>">
            PHIM ĐANG CHIẾU
        </a>
        <a href="?route=home&status=all" class="beta-movie-tab <?= ($currentStatus === 'all') ? 'active' : '' ?>">
            TẤT CẢ PHIM TẠI RẠP
        </a>
    </div>

    <?php if (empty($movies)): ?>
        <div class="alert alert-info text-center py-4">
            Hiện chưa có phim nào trong mục này. Bạn có thể chuyển sang tab khác hoặc thêm phim trong trang Quản trị.
        </div>
    <?php else: ?>
        <!-- LƯỚI 4 CỘT PHIM PHONG CÁCH BETA CINEMAS -->
        <div class="row g-4">
            <?php foreach ($movies as $movie): ?>
                <?php
                $age = strtoupper($movie['age_rating'] ?: 'P');
                $ageClass = 'age-P';
                if (str_contains($age, '13')) $ageClass = 'age-T13';
                elseif (str_contains($age, '16')) $ageClass = 'age-T16';
                elseif (str_contains($age, '18')) $ageClass = 'age-T18';

                $movieShows = $showsByMovie[(int) $movie['id']] ?? [];
                ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="card beta-movie-card h-100">
                        <a href="?route=movie/detail&id=<?= (int) $movie['id'] ?>" class="beta-poster-wrap d-block mb-3">
                            <span class="beta-age-badge <?= $ageClass ?>"><?= htmlspecialchars($age) ?></span>
                            <img src="<?= htmlspecialchars($movie['poster_path'] ?: 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=600') ?>"
                                 alt="<?= htmlspecialchars($movie['title']) ?>">
                        </a>

                        <div class="d-flex flex-column flex-grow-1">
                            <a href="?route=movie/detail&id=<?= (int) $movie['id'] ?>" class="beta-movie-title mb-1" title="<?= htmlspecialchars($movie['title']) ?>">
                                <?= htmlspecialchars($movie['title_vi'] ?: $movie['title']) ?>
                            </a>
                            <div class="small text-muted mb-1">
                                <strong>Thể loại:</strong> <?= htmlspecialchars($movie['genre_names'] ?: 'Điện ảnh') ?>
                            </div>
                            <div class="small text-muted mb-3">
                                <strong>Thời lượng:</strong> <?= (int) $movie['runtime'] ?> phút
                                <?php if (!empty($movie['release_date'])): ?>
                                    | <strong>KC:</strong> <?= date('d/m/Y', strtotime($movie['release_date'])) ?>
                                <?php endif; ?>
                            </div>

                            <div class="mt-auto">
                                <?php if ($movie['status'] === 'now_showing'): ?>
                                    <button type="button"
                                            class="btn btn-beta-buy w-100"
                                            data-bs-toggle="modal"
                                            data-bs-target="#quickBuyModal_<?= (int) $movie['id'] ?>">
                                        <i class="bi bi-ticket-perforated-fill me-1"></i> MUA VÉ
                                    </button>
                                <?php else: ?>
                                    <a href="?route=movie/detail&id=<?= (int) $movie['id'] ?>" class="btn btn-outline-primary w-100 oswald-font fw-bold">
                                        XEM CHI TIẾT PHIM
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- MODAL CHỌN SUẤT CHIẾU NHANH KIỂU BETA CINEMAS KHI BẤM "MUA VÉ" -->
                <div class="modal fade" id="quickBuyModal_<?= (int) $movie['id'] ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content border-0 shadow">
                            <div class="modal-header" style="background: var(--beta-gradient); color: #fff;">
                                <h5 class="modal-title oswald-font text-uppercase">
                                    <i class="bi bi-film me-2"></i>LỊCH CHIẾU - <?= htmlspecialchars($movie['title_vi'] ?: $movie['title']) ?>
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body p-4">
                                <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom">
                                    <div>
                                        <span class="oswald-font fs-5 fw-bold" style="color: var(--beta-blue);">
                                            <i class="bi bi-Pin-map-fill text-danger me-1"></i>RẠP <?= htmlspecialchars(strtoupper($cinemaInfo['name'])) ?>
                                        </span>
                                        <div class="small text-muted"><?= htmlspecialchars($cinemaInfo['address']) ?></div>
                                    </div>
                                    <a href="?route=movie/detail&id=<?= (int) $movie['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                        Xem nội dung & Đánh giá phim
                                    </a>
                                </div>

                                <?php if (empty($movieShows)): ?>
                                    <div class="alert alert-warning mb-0">
                                        Hiện tại rạp chưa mở suất chiếu mới cho phim này. Vui lòng quay lại sau!
                                    </div>
                                <?php else: ?>
                                    <h6 class="fw-bold text-uppercase small text-secondary mb-3">
                                        2D Phụ đề / Lồng tiếng — Chọn giờ chiếu để vào sơ đồ ghế:
                                    </h6>
                                    <div class="d-flex flex-wrap gap-3">
                                        <?php foreach ($movieShows as $sh): ?>
                                            <a href="?route=booking/seats&show_id=<?= (int) $sh['id'] ?>" class="beta-time-slot">
                                                <div class="slot-hour"><?= date('H:i', strtotime($sh['start_time'])) ?></div>
                                                <div class="small text-primary fw-semibold"><?= date('d/m', strtotime($sh['start_time'])) ?> • <?= htmlspecialchars($sh['room_name']) ?></div>
                                                <div class="slot-seats"><?= (int) ($sh['available_seats'] ?? 20) ?> ghế trống</div>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
