<?php
$e = static fn($value): string => htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
$heroMovies = $heroMovies ?? [];
$comingSoonMovies = $comingSoonMovies ?? [];
$activeCoupons = $activeCoupons ?? [];
$concessions = $concessions ?? [];
$todayShowsByMovie = $todayShowsByMovie ?? [];
$showsByMovie = $showsByMovie ?? [];
$rooms = $rooms ?? [];
$cinemaName = $cinemaInfo['name'] ?? 'CineNow';
?>

<style>
    .cine-home { --cine-ink:#101322; --cine-muted:#8b90a3; --cine-primary:#b58b48; --cine-accent:#ffb547; color:#202338; }
    /* ===== HERO SLIDER ===== */
    .cine-hero-slider { position:relative; overflow:hidden; background:#12110f; }
    .cine-hero-slide { min-height:560px; position:relative; display:flex; align-items:center; }
    .cine-hero-content { position:relative; z-index:2; padding:90px 0 80px; max-width:720px; }
    .cine-eyebrow { display:inline-flex; align-items:center; gap:9px; color:#e8cc8f; font-size:.78rem; font-weight:800; letter-spacing:.16em; text-transform:uppercase; }
    .cine-hero-slide h1 { color:#fff; font-family:'Be Vietnam Pro',sans-serif; font-size:clamp(2.2rem,5vw,4.2rem); line-height:1.1; font-weight:900; letter-spacing:-.04em; margin:16px 0; }
    .cine-hero-slide p { color:rgba(255,255,255,.78); max-width:560px; font-size:1.02rem; line-height:1.75; }
    .cine-btn-primary { display:inline-flex; align-items:center; justify-content:center; gap:9px; border:0; border-radius:999px; padding:13px 24px; background:linear-gradient(135deg,#d9bd7b,#8e6a32); color:#211b10; text-decoration:none; font-weight:800; box-shadow:0 12px 30px rgba(174,132,58,.24); transition:.2s; }
    .cine-btn-primary:hover { color:#fff; transform:translateY(-2px); box-shadow:0 16px 34px rgba(174,132,58,.32); }
    .cine-btn-ghost { display:inline-flex; align-items:center; gap:8px; border:1px solid rgba(231,211,165,.55); border-radius:999px; padding:12px 18px; color:#fff; text-decoration:none; font-weight:700; transition:.2s; background:transparent; cursor:pointer; }
    .cine-btn-ghost:hover { background:#fff; color:#17192b; }
    .cine-hero-slider .carousel-indicators { bottom:22px; z-index:3; }
    .cine-hero-slider .carousel-indicators [data-bs-target] { width:10px; height:10px; border-radius:50%; background:#d9bd7b; opacity:.45; }
    .cine-hero-slider .carousel-indicators .active { opacity:1; }
    .cine-hero-slider .carousel-control-prev, .cine-hero-slider .carousel-control-next { z-index:3; width:6%; }

    /* ===== BENEFITS ===== */
    .cine-benefits { background:#171a2d; color:#fff; }
    .cine-benefit { display:flex; align-items:center; gap:13px; padding:20px 12px; }
    .cine-benefit-icon { width:43px; height:43px; flex:0 0 43px; display:grid; place-items:center; border-radius:13px; color:#e4c987; background:rgba(215,181,109,.13); font-size:1.2rem; }
    .cine-benefit strong { display:block; font-size:.9rem; }
    .cine-benefit small { color:#a9adc0; }

    /* ===== SECTION COMMON ===== */
    .cine-section { padding:64px 0; }
    .cine-section-alt { background:#f3f1ec; }
    .cine-section-heading { display:flex; justify-content:space-between; align-items:end; gap:20px; margin-bottom:28px; flex-wrap:wrap; }
    .cine-kicker { color:var(--cine-primary); font-size:.75rem; font-weight:900; letter-spacing:.14em; text-transform:uppercase; }
    .cine-section-title { color:var(--cine-ink); font-size:clamp(1.55rem,3vw,2.15rem); font-weight:900; letter-spacing:-.04em; margin:5px 0 0; }

    /* ===== PROMO / COUPON ===== */
    .cine-promo-card { border:0; border-radius:18px; overflow:hidden; background:#fff; box-shadow:0 8px 28px rgba(25,28,55,.07); height:100%; transition:transform .25s,box-shadow .25s; }
    .cine-promo-card:hover { transform:translateY(-4px); box-shadow:0 14px 36px rgba(25,28,55,.12); }
    .cine-promo-code { font-family:monospace; font-weight:800; font-size:1.15rem; color:#c0392b; background:#fdf2f0; border:1px dashed #e8a598; border-radius:10px; padding:8px 14px; display:inline-block; }
    .cine-combo-price { color:#c0392b; font-weight:900; font-size:1.2rem; }

    /* ===== FILTER + TABS ===== */
    .cine-filter { background:#fff; border:1px solid #e8e9f1; border-radius:16px; padding:16px; box-shadow:0 10px 35px rgba(26,30,60,.045); margin-bottom:22px; }
    .cine-filter .form-control,.cine-filter .form-select { min-height:46px; border-color:#e4ddcf; border-radius:10px; box-shadow:none; }
    .cine-filter .form-control:focus,.cine-filter .form-select:focus { border-color:#c6a15b; box-shadow:0 0 0 3px rgba(182,139,72,.15); }
    .cine-tabs { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:24px; }
    .cine-tab { border:1px solid #e4ddcf; border-radius:999px; padding:9px 16px; color:#5e6277; background:#fff; text-decoration:none; font-size:.88rem; font-weight:800; transition:.2s; }
    .cine-tab:hover,.cine-tab.active { color:#fff; background:#b58b48; border-color:#b58b48; }

    /* ===== MOVIE CARD ===== */
    .cine-movie-card { height:100%; overflow:hidden; border:1px solid #ececf3; border-radius:17px; background:#fff; box-shadow:0 7px 24px rgba(25,28,55,.055); transition:transform .25s,box-shadow .25s; }
    .cine-movie-card:hover { transform:translateY(-6px); box-shadow:0 18px 38px rgba(25,28,55,.13); }
    .cine-poster { position:relative; display:block; aspect-ratio:2/2.85; overflow:hidden; background:#e9eaf1; }
    .cine-poster img { width:100%; height:100%; object-fit:cover; transition:transform .45s; }
    .cine-movie-card:hover .cine-poster img { transform:scale(1.055); }
    .cine-age { position:absolute; left:12px; top:12px; padding:5px 9px; border-radius:7px; color:#fff; font-size:.72rem; font-weight:900; box-shadow:0 4px 12px #0003; }
    .cine-rating { position:absolute; right:11px; top:11px; padding:5px 8px; border-radius:7px; color:#191a26; background:#ffc45e; font-size:.76rem; font-weight:900; }
    .cine-card-body { padding:15px; display:flex; flex-direction:column; min-height:175px; }
    .cine-movie-title { color:#202338; text-decoration:none; font-size:1rem; line-height:1.45; font-weight:900; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; min-height:46px; }
    .cine-movie-title:hover { color:var(--cine-primary); }
    .cine-meta { color:#85899c; font-size:.78rem; line-height:1.7; }
    .cine-card-actions { display:flex; gap:8px; margin-top:auto; padding-top:12px; }
    .cine-card-actions .btn { flex:1; border-radius:9px; font-size:.78rem; font-weight:800; padding:9px 7px; }
    .cine-buy { background:#b58b48; color:#fff; border:1px solid #b58b48; }
    .cine-buy:hover { background:#8e6a32; color:#fff; }
    .cine-detail { color:#5d6073; border:1px solid #e5e6ef; background:#fff; }
    .cine-detail:hover { color:#8e6a32; border-color:#c6a15b; }
    .cine-empty { padding:55px 20px; border:1px dashed #d7d9e5; border-radius:18px; text-align:center; background:#fff; }
    .cine-time-slot { display:inline-flex; flex-direction:column; min-width:105px; padding:10px 13px; border:1px solid #e4e5ee; border-radius:11px; text-decoration:none; color:#25283b; background:#f8f8fc; transition:.2s; }
    .cine-time-slot:hover { color:#fff; background:#b58b48; border-color:#b58b48; }
    .cine-time-slot strong { font-size:1.1rem; }
    .cine-time-slot small { font-size:.72rem; opacity:.8; }
    .cine-modal .modal-content { border:0; border-radius:18px; overflow:hidden; }
    .cine-modal .modal-header { color:#fff; background:#1d1c19; border:0; }

    /* ===== COMING SOON HORIZONTAL ===== */
    .cine-coming-scroll { display:flex; gap:18px; overflow-x:auto; padding-bottom:12px; scroll-snap-type:x mandatory; -webkit-overflow-scrolling:touch; }
    .cine-coming-scroll::-webkit-scrollbar { height:6px; }
    .cine-coming-scroll::-webkit-scrollbar-thumb { background:#c6a15b; border-radius:4px; }
    .cine-coming-item { flex:0 0 180px; scroll-snap-align:start; }
    .cine-coming-item .cine-movie-card { min-height:auto; }
    .cine-coming-item .cine-card-body { min-height:110px; }

    /* ===== TODAY SCHEDULE ===== */
    .cine-today-row { background:#fff; border:1px solid #ececf3; border-radius:16px; padding:18px 20px; margin-bottom:14px; box-shadow:0 6px 20px rgba(25,28,55,.04); }
    .cine-today-poster { width:64px; height:96px; object-fit:cover; border-radius:10px; }

    /* ===== ABOUT TEASER ===== */
    .cine-about-teaser { background:linear-gradient(135deg,#1a1916 0%,#2a271f 100%); color:#fff; border-radius:22px; overflow:hidden; }
    .cine-about-teaser .cine-btn-primary { margin-top:8px; }

    @media(max-width:767.98px) {
        .cine-hero-slide{min-height:460px}
        .cine-hero-content{padding:70px 0 60px}
        .cine-section{padding:40px 0}
        .cine-section-heading{align-items:start;flex-direction:column}
        .cine-card-body{padding:12px;min-height:165px}
        .cine-movie-title{font-size:.9rem}
        .cine-card-actions{flex-direction:column}
        .cine-benefit{padding:13px 5px}
        .cine-coming-item{flex:0 0 150px}
    }
</style>

<div class="cine-home">

    <!-- =========================================================
         1. HERO SLIDER – Banner phim nổi bật + CTA + trailer
         ========================================================= -->
    <section class="cine-hero-slider">
        <?php if (!empty($heroMovies)): ?>
            <div id="cineHeroCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5500">
                <div class="carousel-indicators">
                    <?php foreach ($heroMovies as $hi => $hm): ?>
                        <button type="button" data-bs-target="#cineHeroCarousel" data-bs-slide-to="<?= $hi ?>" class="<?= $hi === 0 ? 'active' : '' ?>" aria-label="Slide <?= $hi + 1 ?>"></button>
                    <?php endforeach; ?>
                </div>
                <div class="carousel-inner">
                    <?php foreach ($heroMovies as $hi => $hm):
                        $bg = $hm['backdrop_path'] ?: ($hm['poster_path'] ?: 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?auto=format&fit=crop&w=2000&q=85');
                        $title = $hm['title_vi'] ?: $hm['title'];
                    ?>
                        <div class="carousel-item <?= $hi === 0 ? 'active' : '' ?>">
                            <div class="cine-hero-slide">
                                <div style="position:absolute;inset:0;z-index:0;background-image:url('<?= $e($bg) ?>');background-size:cover;background-position:center 30%;"></div>
                                <div style="position:absolute;inset:0;z-index:1;background:linear-gradient(90deg,rgba(12,11,10,.96) 0%,rgba(16,15,13,.78) 45%,rgba(16,15,13,.25) 100%),linear-gradient(0deg,rgba(12,11,10,.75),transparent 55%);"></div>
                                <div class="container">
                                    <div class="cine-hero-content">
                                        <span class="cine-eyebrow"><i class="bi bi-stars"></i> Phim đang chiếu tại <?= $e($cinemaName) ?></span>
                                        <h1><?= $e($title) ?></h1>
                                        <p><?= $e(mb_strimwidth(strip_tags($hm['description'] ?? $hm['overview'] ?? 'Đặt vé trực tuyến, chọn ghế yêu thích và tận hưởng trải nghiệm điện ảnh đỉnh cao.'), 0, 160, '...')) ?></p>
                                        <div class="d-flex flex-wrap gap-3 mt-4">
                                            <a href="?route=movie/detail&id=<?= (int) $hm['id'] ?>" class="cine-btn-primary"><i class="bi bi-ticket-perforated-fill"></i> Đặt vé ngay</a>
                                            <?php if (!empty($hm['trailer_url'])): ?>
                                                <button type="button" class="cine-btn-ghost" data-bs-toggle="modal" data-bs-target="#heroTrailerModal<?= (int) $hm['id'] ?>">
                                                    <i class="bi bi-play-circle"></i> Xem trailer
                                                </button>
                                            <?php else: ?>
                                                <a href="?route=schedule" class="cine-btn-ghost"><i class="bi bi-calendar3"></i> Lịch chiếu</a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#cineHeroCarousel" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
                <button class="carousel-control-next" type="button" data-bs-target="#cineHeroCarousel" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
            </div>

            <?php foreach ($heroMovies as $hm): if (empty($hm['trailer_url'])) continue; ?>
                <div class="modal fade" id="heroTrailerModal<?= (int) $hm['id'] ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content bg-dark">
                            <div class="modal-header border-0">
                                <h5 class="modal-title text-white"><?= $e($hm['title_vi'] ?: $hm['title']) ?> – Trailer</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body p-0">
                                <div class="ratio ratio-16x9">
                                    <iframe src="<?= $e($hm['trailer_url']) ?>" title="Trailer" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="cine-hero-slide">
                <div style="position:absolute;inset:0;z-index:0;background-image:url('https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?auto=format&fit=crop&w=2000&q=85');background-size:cover;background-position:center;"></div>
                <div style="position:absolute;inset:0;z-index:1;background:linear-gradient(90deg,rgba(12,11,10,.96),rgba(16,15,13,.5));"></div>
                <div class="container">
                    <div class="cine-hero-content">
                        <span class="cine-eyebrow"><i class="bi bi-stars"></i> Trải nghiệm điện ảnh khác biệt</span>
                        <h1>Chạm đến thế giới điện ảnh</h1>
                        <p>Đặt vé trực tuyến nhanh chóng tại <?= $e($cinemaName) ?>.</p>
                        <a href="#movie-list" class="cine-btn-primary mt-3"><i class="bi bi-ticket-perforated-fill"></i> Khám phá phim</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </section>

    <!-- =========================================================
         2. THANH LỢI ÍCH (Benefits)
         ========================================================= -->
    <section class="cine-benefits">
        <div class="container">
            <div class="row g-0">
                <div class="col-6 col-lg-3"><div class="cine-benefit"><span class="cine-benefit-icon"><i class="bi bi-phone"></i></span><div><strong>Đặt vé online</strong><small>Chọn vé mọi lúc</small></div></div></div>
                <div class="col-6 col-lg-3"><div class="cine-benefit"><span class="cine-benefit-icon"><i class="bi bi-grid-3x3-gap"></i></span><div><strong>Chọn ghế yêu thích</strong><small>Sơ đồ ghế trực quan</small></div></div></div>
                <div class="col-6 col-lg-3"><div class="cine-benefit"><span class="cine-benefit-icon"><i class="bi bi-qr-code-scan"></i></span><div><strong>Vé điện tử QR</strong><small>Check-in thuận tiện</small></div></div></div>
                <div class="col-6 col-lg-3"><div class="cine-benefit"><span class="cine-benefit-icon"><i class="bi bi-shield-check"></i></span><div><strong>Thanh toán an toàn</strong><small>Ví nội bộ & rõ ràng</small></div></div></div>
            </div>
        </div>
    </section>

    <!-- =========================================================
         3. ƯU ĐÃI & COMBO NỔI BẬT
         ========================================================= -->
    <?php if (!empty($activeCoupons) || !empty($concessions)): ?>
    <section class="cine-section cine-section-alt" id="promotions-home">
        <div class="container">
            <div class="cine-section-heading">
                <div>
                    <div class="cine-kicker">ƯU ĐÃI ĐẶC BIỆT</div>
                    <h2 class="cine-section-title">Mã giảm giá & Combo bắp nước</h2>
                </div>
                <a href="?route=promotions" class="text-decoration-none fw-bold" style="color:#b58b48">Xem tất cả <i class="bi bi-arrow-right"></i></a>
            </div>

            <div class="row g-4">
                <?php
                $promoCoupons = array_slice($activeCoupons, 0, 2);
                foreach ($promoCoupons as $cp):
                    $discountLabel = ($cp['discount_type'] ?? '') === 'percent'
                        ? (int) $cp['discount_value'] . '%'
                        : number_format((float) $cp['discount_value']) . 'đ';
                ?>
                    <div class="col-md-6">
                        <div class="cine-promo-card p-4 d-flex justify-content-between align-items-center gap-3">
                            <div>
                                <span class="badge mb-2" style="background:#c0392b">MÃ GIẢM GIÁ</span>
                                <h5 class="fw-bold mb-1" style="color:var(--cine-ink)"><?= $e($cp['name']) ?></h5>
                                <p class="small text-muted mb-2 mb-md-3"><?= $e($cp['description'] ?? 'Áp dụng khi đặt vé online') ?></p>
                                <div class="small text-secondary">Giảm <strong><?= $e($discountLabel) ?></strong>
                                    <?php if (!empty($cp['min_order_amount'])): ?> · Đơn từ <?= number_format((float) $cp['min_order_amount']) ?>đ<?php endif; ?>
                                </div>
                            </div>
                            <div class="text-center flex-shrink-0">
                                <div class="small text-muted mb-1">Nhập mã</div>
                                <div class="cine-promo-code"><?= $e($cp['code']) ?></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php
                $promoCombos = array_slice($concessions, 0, 3);
                foreach ($promoCombos as $con):
                ?>
                    <div class="col-md-4">
                        <div class="cine-promo-card p-4 h-100 d-flex flex-column">
                            <span class="badge bg-warning text-dark align-self-start mb-2"><?= $e($con['category'] ?: 'Combo') ?></span>
                            <h5 class="fw-bold mb-2" style="color:var(--cine-ink)"><?= $e($con['name']) ?></h5>
                            <p class="small text-muted mb-3 flex-grow-1"><?= $e($con['description'] ?? '') ?></p>
                            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                <span class="cine-combo-price"><?= number_format((float) $con['price']) ?>đ</span>
                                <a href="?route=schedule" class="btn btn-sm cine-buy">Chọn kèm vé</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- =========================================================
         4. PHIM ĐANG CHIẾU / LỌC / TÌM KIẾM (danh sách chính)
         ========================================================= -->
    <section class="cine-section" id="movie-list">
        <div class="container">
            <div class="cine-section-heading">
                <div>
                    <div class="cine-kicker">THE CINEMA COLLECTION</div>
                    <h2 class="cine-section-title">Những bộ phim không thể bỏ lỡ</h2>
                </div>
                <a href="?route=schedule" class="text-decoration-none fw-bold" style="color:#b58b48">Lịch chiếu đầy đủ <i class="bi bi-arrow-right"></i></a>
            </div>

            <form class="cine-filter" method="GET" action="">
                <input type="hidden" name="route" value="home">
                <input type="hidden" name="status" value="<?= $e($currentStatus) ?>">
                <div class="row g-2 align-items-center">
                    <div class="col-12 col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input class="form-control border-start-0" type="search" name="q" value="<?= $e($keyword ?? '') ?>" placeholder="Tìm tên phim bạn muốn xem...">
                        </div>
                    </div>
                    <div class="col-8 col-md-4">
                        <select class="form-select" name="genre_id">
                            <option value="">Tất cả thể loại</option>
                            <?php foreach (($genres ?? []) as $genre): ?>
                                <option value="<?= (int) $genre['id'] ?>" <?= (int) ($selectedGenreId ?? 0) === (int) $genre['id'] ? 'selected' : '' ?>><?= $e($genre['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-4 col-md-2 d-grid"><button class="btn cine-btn-primary" type="submit"><i class="bi bi-funnel"></i> Lọc phim</button></div>
                </div>
            </form>

            <nav class="cine-tabs" aria-label="Lọc trạng thái phim">
                <?php
                $filterQuery = ($keyword ?? '') !== '' ? '&q=' . rawurlencode($keyword) : '';
                $genreQuery = !empty($selectedGenreId) ? '&genre_id=' . (int) $selectedGenreId : '';
                ?>
                <a class="cine-tab <?= $currentStatus === 'now_showing' ? 'active' : '' ?>" href="?route=home&status=now_showing<?= $filterQuery . $genreQuery ?>">Đang chiếu</a>
                <a class="cine-tab <?= $currentStatus === 'coming_soon' ? 'active' : '' ?>" href="?route=home&status=coming_soon<?= $filterQuery . $genreQuery ?>">Sắp chiếu</a>
                <a class="cine-tab <?= $currentStatus === 'all' ? 'active' : '' ?>" href="?route=home&status=all<?= $filterQuery . $genreQuery ?>">Tất cả phim</a>
            </nav>

            <?php if (empty($movies)): ?>
                <div class="cine-empty">
                    <div class="display-4 text-muted mb-3"><i class="bi bi-camera-reels"></i></div>
                    <h4 class="fw-bold">Chưa tìm thấy bộ phim phù hợp</h4>
                    <p class="text-muted mb-3">Bạn hãy thử từ khóa khác hoặc thay đổi bộ lọc nhé.</p>
                    <a href="?route=home" class="cine-btn-primary">Xóa bộ lọc</a>
                </div>
            <?php else: ?>
                <div class="row g-3 g-lg-4">
                    <?php foreach ($movies as $movie):
                        $age = strtoupper($movie['age_rating'] ?: 'P');
                        $ageClass = str_contains($age, '18') ? 'background:#d92d45' : (str_contains($age, '16') ? 'background:#ed7138' : (str_contains($age, '13') ? 'background:#e6a12e' : 'background:#1b9b69'));
                        $movieShows = $showsByMovie[(int) $movie['id']] ?? [];
                        $poster = $movie['poster_path'] ?: 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?auto=format&fit=crop&w=700&q=80';
                    ?>
                        <div class="col-6 col-md-4 col-lg-3">
                            <article class="cine-movie-card">
                                <a class="cine-poster" href="?route=movie/detail&id=<?= (int) $movie['id'] ?>">
                                    <span class="cine-age" style="<?= $ageClass ?>"><?= $e($age) ?></span>
                                    <?php if (!empty($movie['imdb_rating'])): ?><span class="cine-rating"><i class="bi bi-star-fill me-1"></i><?= number_format((float) $movie['imdb_rating'], 1) ?></span><?php endif; ?>
                                    <img src="<?= $e($poster) ?>" alt="Poster <?= $e($movie['title']) ?>" loading="lazy" onerror="this.onerror=null;this.src='https://placehold.co/600x900/171a2d/ffffff?text=Cinema';">
                                </a>
                                <div class="cine-card-body">
                                    <a class="cine-movie-title" href="?route=movie/detail&id=<?= (int) $movie['id'] ?>"><?= $e($movie['title_vi'] ?: $movie['title']) ?></a>
                                    <div class="cine-meta mt-2"><i class="bi bi-tags me-1"></i><?= $e($movie['genre_names'] ?: 'Đang cập nhật thể loại') ?></div>
                                    <div class="cine-meta"><i class="bi bi-clock me-1"></i><?= (int) ($movie['runtime'] ?? 0) ?> phút<?php if (!empty($movie['release_date'])): ?> <span class="mx-1">•</span><?= date('d/m/Y', strtotime($movie['release_date'])) ?><?php endif; ?></div>
                                    <div class="cine-card-actions">
                                        <?php if (($movie['status'] ?? '') === 'now_showing'): ?>
                                            <button class="btn cine-buy" type="button" data-bs-toggle="modal" data-bs-target="#cineShowModal<?= (int) $movie['id'] ?>"><i class="bi bi-ticket-perforated me-1"></i> Mua vé</button>
                                        <?php else: ?>
                                            <a class="btn cine-detail" href="?route=movie/detail&id=<?= (int) $movie['id'] ?>">Xem chi tiết</a>
                                        <?php endif; ?>
                                        <a class="btn cine-detail" href="?route=movie/detail&id=<?= (int) $movie['id'] ?>" aria-label="Chi tiết phim"><i class="bi bi-arrow-up-right"></i></a>
                                    </div>
                                </div>
                            </article>
                        </div>

                        <div class="modal fade cine-modal" id="cineShowModal<?= (int) $movie['id'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content">
                                <div class="modal-header">
                                    <div>
                                        <div class="small text-white-50">CHỌN SUẤT CHIẾU</div>
                                        <h5 class="modal-title fw-bold"><?= $e($movie['title_vi'] ?: $movie['title']) ?></h5>
                                    </div>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Đóng"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <div class="mb-3">
                                        <strong><?= $e($cinemaName) ?></strong>
                                        <div class="small text-muted"><?= $e($cinemaInfo['address'] ?? '') ?></div>
                                    </div>
                                    <?php if (empty($movieShows)): ?>
                                        <div class="alert alert-warning mb-0">Hiện chưa có suất chiếu khả dụng cho phim này. Vui lòng quay lại sau.</div>
                                    <?php else: ?>
                                        <div class="d-flex flex-wrap gap-2">
                                            <?php foreach ($movieShows as $show): ?>
                                                <a class="cine-time-slot" href="?route=booking/seats&show_id=<?= (int) $show['id'] ?>">
                                                    <strong><?= date('H:i', strtotime($show['start_time'])) ?></strong>
                                                    <small><?= date('d/m', strtotime($show['start_time'])) ?> · <?= $e($show['room_name']) ?></small>
                                                    <small><?= (int) ($show['available_seats'] ?? 0) ?> ghế trống</small>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- =========================================================
         5. PHIM SẮP CHIẾU (carousel ngang riêng)
         ========================================================= -->
    <?php if (!empty($comingSoonMovies) && ($currentStatus ?? '') !== 'coming_soon'): ?>
    <section class="cine-section cine-section-alt" id="coming-soon">
        <div class="container">
            <div class="cine-section-heading">
                <div>
                    <div class="cine-kicker">COMING SOON</div>
                    <h2 class="cine-section-title">Phim sắp chiếu</h2>
                </div>
                <a href="?route=home&status=coming_soon" class="text-decoration-none fw-bold" style="color:#b58b48">Xem tất cả <i class="bi bi-arrow-right"></i></a>
            </div>

            <div class="cine-coming-scroll">
                <?php foreach ($comingSoonMovies as $movie):
                    $age = strtoupper($movie['age_rating'] ?: 'P');
                    $ageClass = str_contains($age, '18') ? 'background:#d92d45' : (str_contains($age, '16') ? 'background:#ed7138' : (str_contains($age, '13') ? 'background:#e6a12e' : 'background:#1b9b69'));
                    $poster = $movie['poster_path'] ?: 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?auto=format&fit=crop&w=700&q=80';
                ?>
                    <div class="cine-coming-item">
                        <article class="cine-movie-card">
                            <a class="cine-poster" href="?route=movie/detail&id=<?= (int) $movie['id'] ?>">
                                <span class="cine-age" style="<?= $ageClass ?>"><?= $e($age) ?></span>
                                <img src="<?= $e($poster) ?>" alt="<?= $e($movie['title']) ?>" loading="lazy" onerror="this.onerror=null;this.src='https://placehold.co/600x900/171a2d/ffffff?text=Coming';">
                            </a>
                            <div class="cine-card-body">
                                <a class="cine-movie-title" href="?route=movie/detail&id=<?= (int) $movie['id'] ?>"><?= $e($movie['title_vi'] ?: $movie['title']) ?></a>
                                <div class="cine-meta mt-1">
                                    <?php if (!empty($movie['release_date'])): ?>
                                        <i class="bi bi-calendar-event me-1"></i><?= date('d/m/Y', strtotime($movie['release_date'])) ?>
                                    <?php else: ?>
                                        Sắp ra mắt
                                    <?php endif; ?>
                                </div>
                                <div class="cine-card-actions">
                                    <a class="btn cine-detail w-100" href="?route=movie/detail&id=<?= (int) $movie['id'] ?>">Chi tiết</a>
                                </div>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- =========================================================
         6. LỊCH CHIẾU NHANH HÔM NAY
         ========================================================= -->
    <?php if (!empty($todayShowsByMovie)): ?>
    <section class="cine-section" id="today-schedule">
        <div class="container">
            <div class="cine-section-heading">
                <div>
                    <div class="cine-kicker">HÔM NAY · <?= date('d/m/Y') ?></div>
                    <h2 class="cine-section-title">Lịch chiếu nhanh hôm nay</h2>
                </div>
                <a href="?route=schedule&date=<?= date('Y-m-d') ?>" class="text-decoration-none fw-bold" style="color:#b58b48">Xem đầy đủ <i class="bi bi-arrow-right"></i></a>
            </div>

            <?php
            $movieMap = [];
            foreach (($nowShowingMovies ?? $movies) as $m) {
                $movieMap[(int) $m['id']] = $m;
            }
            $shown = 0;
            foreach ($todayShowsByMovie as $movieId => $shows):
                if ($shown >= 6) break;
                $m = $movieMap[(int) $movieId] ?? null;
                if (!$m) continue;
                $poster = $m['poster_path'] ?: 'https://placehold.co/120x180/171a2d/ffffff?text=Film';
                $shown++;
            ?>
                <div class="cine-today-row">
                    <div class="d-flex flex-wrap gap-3 align-items-start">
                        <a href="?route=movie/detail&id=<?= (int) $movieId ?>">
                            <img class="cine-today-poster" src="<?= $e($poster) ?>" alt="<?= $e($m['title'] ?? '') ?>" loading="lazy">
                        </a>
                        <div class="flex-grow-1">
                            <a href="?route=movie/detail&id=<?= (int) $movieId ?>" class="cine-movie-title d-inline-block mb-1" style="min-height:auto">
                                <?= $e($m['title_vi'] ?: $m['title']) ?>
                            </a>
                            <div class="cine-meta mb-2">
                                <i class="bi bi-clock me-1"></i><?= (int) ($m['runtime'] ?? 0) ?> phút
                                <?php if (!empty($m['genre_names'])): ?> · <?= $e($m['genre_names']) ?><?php endif; ?>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach (array_slice($shows, 0, 8) as $show): ?>
                                    <a class="cine-time-slot" href="?route=booking/seats&show_id=<?= (int) $show['id'] ?>">
                                        <strong><?= date('H:i', strtotime($show['start_time'])) ?></strong>
                                        <small><?= $e($show['room_name']) ?></small>
                                        <small><?= (int) ($show['available_seats'] ?? 0) ?> ghế</small>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- =========================================================
         7. GIỚI THIỆU RẠP NGẮN (teaser)
         ========================================================= -->
    <section class="cine-section cine-section-alt">
        <div class="container">
            <div class="cine-about-teaser">
                <div class="row g-0 align-items-center">
                    <div class="col-lg-7 p-4 p-md-5">
                        <div class="cine-kicker" style="color:#e8cc8f">VỀ CHÚNG TÔI</div>
                        <h2 class="cine-section-title text-white mb-3">Rạp <?= $e($cinemaName) ?></h2>
                        <p class="mb-3" style="color:rgba(255,255,255,.78);line-height:1.75">
                            <?= $e(mb_strimwidth(strip_tags($cinemaInfo['description'] ?? 'Rạp chiếu phim hiện đại với hệ thống phòng chiếu chất lượng, âm thanh sống động và dịch vụ đặt vé online tiện lợi.'), 0, 220, '...')) ?>
                        </p>
                        <ul class="list-unstyled mb-4" style="color:rgba(255,255,255,.85)">
                            <?php if (!empty($cinemaInfo['address'])): ?>
                                <li class="mb-2"><i class="bi bi-geo-alt-fill me-2" style="color:#e8cc8f"></i><?= $e($cinemaInfo['address']) ?></li>
                            <?php endif; ?>
                            <?php if (!empty($cinemaInfo['phone'])): ?>
                                <li class="mb-2"><i class="bi bi-telephone-fill me-2" style="color:#e8cc8f"></i>Hotline: <?= $e($cinemaInfo['phone']) ?></li>
                            <?php endif; ?>
                            <?php if (!empty($rooms)): ?>
                                <li class="mb-2"><i class="bi bi-tv me-2" style="color:#e8cc8f"></i><?= count($rooms) ?> phòng chiếu đang hoạt động</li>
                            <?php endif; ?>
                        </ul>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="?route=about" class="cine-btn-primary"><i class="bi bi-info-circle"></i> Thông tin rạp</a>
                            <a href="?route=contact" class="cine-btn-ghost"><i class="bi bi-chat-dots"></i> Liên hệ</a>
                        </div>
                    </div>
                    <div class="col-lg-5 d-none d-lg-block" style="min-height:320px;background:url('https://images.unsplash.com/photo-1517604931442-7e0c8ed2963c?w=900') center/cover no-repeat;"></div>
                </div>
            </div>
        </div>
    </section>

</div>