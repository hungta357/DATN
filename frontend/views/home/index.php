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
    <!-- REACT 18 FRONTEND MOUNT ROOT (frontend/react/CustomerMovieApp.js) -->
    <div id="react-movie-explorer-root">
        <div class="beta-movie-tabs">
            <a href="?route=home&status=coming_soon" class="beta-movie-tab <?= ($currentStatus === 'coming_soon') ? 'active' : '' ?>">PHIM SẮP CHIẾU</a>
            <a href="?route=home&status=now_showing" class="beta-movie-tab <?= ($currentStatus === 'now_showing') ? 'active' : '' ?>">PHIM ĐANG CHIẾU</a>
            <a href="?route=home&status=all" class="beta-movie-tab <?= ($currentStatus === 'all') ? 'active' : '' ?>">TẤT CẢ PHIM TẠI RẠP</a>
        </div>
        <div class="row g-4">
            <?php foreach ($movies as $movie): ?>
                <?php
                $age = strtoupper($movie['age_rating'] ?: 'P');
                $ageClass = 'age-P';
                if (str_contains($age, '13')) $ageClass = 'age-T13';
                elseif (str_contains($age, '16')) $ageClass = 'age-T16';
                elseif (str_contains($age, '18')) $ageClass = 'age-T18';
                ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="card beta-movie-card h-100">
                        <a href="?route=movie/detail&id=<?= (int) $movie['id'] ?>" class="beta-poster-wrap d-block mb-3">
                            <span class="beta-age-badge <?= $ageClass ?>"><?= htmlspecialchars($age) ?></span>
                            <img src="<?= htmlspecialchars($movie['poster_path'] ?: 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=600') ?>"
                                 alt="<?= htmlspecialchars($movie['title']) ?>">
                        </a>
                        <div class="d-flex flex-column flex-grow-1">
                            <a href="?route=movie/detail&id=<?= (int) $movie['id'] ?>" class="beta-movie-title mb-1">
                                <?= htmlspecialchars($movie['title_vi'] ?: $movie['title']) ?>
                            </a>
                            <div class="small text-muted mb-1"><strong>Thể loại:</strong> <?= htmlspecialchars($movie['genre_names'] ?: 'Điện ảnh') ?></div>
                            <div class="small text-muted mb-3"><strong>Thời lượng:</strong> <?= (int) $movie['runtime'] ?> phút</div>
                            <div class="mt-auto">
                                <a href="?route=movie/detail&id=<?= (int) $movie['id'] ?>" class="btn btn-beta-buy w-100">MUA VÉ</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
window.__HOME_INITIAL_DATA__ = <?= json_encode([
    'movies' => $allMovies ?? $movies ?? [],
    'genres' => $genres ?? [],
    'showsByMovie' => $showsByMovie ?? [],
    'cinemaInfo' => $cinemaInfo ?? [],
    'currentStatus' => $currentStatus ?? 'now_showing',
    'keyword' => $keyword ?? '',
], JSON_UNESCAPED_UNICODE) ?>;
</script>
<script src="?route=frontend/js&file=CustomerMovieApp.js"></script>
