<?php
$e = static fn($value): string => htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
$featuredMovie = $movies[0] ?? null;
$featuredBackdrop = $featuredMovie['backdrop_path'] ?? '';
if (!$featuredBackdrop) {
    $featuredBackdrop = 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?auto=format&fit=crop&w=2000&q=85';
}
$heroTitle = $featuredMovie ? ($featuredMovie['title_vi'] ?: $featuredMovie['title']) : 'Chạm đến thế giới điện ảnh';
?>

<style>
    .cine-home { --cine-ink:#101322; --cine-muted:#8b90a3; --cine-primary:#b58b48; --cine-accent:#ffb547; color:#202338; }
    .cine-hero { min-height:590px; position:relative; overflow:hidden; isolation:isolate; display:flex; align-items:center; background:#171612; }
    .cine-hero::before { content:""; position:absolute; inset:0; z-index:-2; background-image:url('<?= $e($featuredBackdrop) ?>'); background-size:cover; background-position:center 35%; transform:scale(1.02); }
    .cine-hero::after { content:""; position:absolute; inset:0; z-index:-1; background:linear-gradient(90deg,rgba(13,13,12,.97) 0%,rgba(18,17,14,.84) 43%,rgba(18,17,14,.20) 100%),linear-gradient(0deg,rgba(13,13,12,.72),transparent 52%); }
    .cine-hero-content { padding-top:100px; padding-bottom:100px; max-width:780px; }
    .cine-eyebrow { display:inline-flex; align-items:center; gap:9px; color:#e8cc8f; font-size:.78rem; font-weight:800; letter-spacing:.16em; text-transform:uppercase; }
    .cine-hero h1 { color:#fff; font-family:'Be Vietnam Pro',sans-serif; font-size:clamp(2.5rem,5.5vw,5rem); line-height:1.08; font-weight:900; letter-spacing:-.045em; margin:18px 0; }
    .cine-hero p { color:rgba(255,255,255,.78); max-width:610px; font-size:1.05rem; line-height:1.8; }
    .cine-btn-primary { display:inline-flex; align-items:center; justify-content:center; gap:9px; border:0; border-radius:999px; padding:14px 25px; background:linear-gradient(135deg,#d9bd7b,#8e6a32); color:#211b10; text-decoration:none; font-weight:800; box-shadow:0 12px 30px rgba(174,132,58,.24); transition:.2s; }
    .cine-btn-primary:hover { color:#fff; transform:translateY(-2px); box-shadow:0 16px 34px rgba(174,132,58,.32); }
    .cine-btn-ghost { display:inline-flex; align-items:center; gap:8px; border:1px solid rgba(231,211,165,.55); border-radius:999px; padding:12px 19px; color:#fff; text-decoration:none; font-weight:700; transition:.2s; }
    .cine-btn-ghost:hover { background:#fff; color:#17192b; }
    .cine-benefits { position:relative; margin-top:-1px; background:#171a2d; color:#fff; }
    .cine-benefit { display:flex; align-items:center; gap:13px; padding:20px 12px; }
    .cine-benefit-icon { width:43px; height:43px; flex:0 0 43px; display:grid; place-items:center; border-radius:13px; color:#e4c987; background:rgba(215,181,109,.13); font-size:1.2rem; }
    .cine-benefit strong { display:block; font-size:.9rem; }
    .cine-benefit small { color:#a9adc0; }
    .cine-section { padding:68px 0; }
    .cine-section-heading { display:flex; justify-content:space-between; align-items:end; gap:20px; margin-bottom:25px; }
    .cine-kicker { color:var(--cine-primary); font-size:.75rem; font-weight:900; letter-spacing:.14em; text-transform:uppercase; }
    .cine-section-title { color:var(--cine-ink); font-size:clamp(1.65rem,3vw,2.25rem); font-weight:900; letter-spacing:-.04em; margin:5px 0 0; }
    .cine-filter { background:#fff; border:1px solid #e8e9f1; border-radius:16px; padding:16px; box-shadow:0 10px 35px rgba(26,30,60,.045); margin-bottom:27px; }
    .cine-filter .form-control,.cine-filter .form-select { min-height:46px; border-color:#e4ddcf; border-radius:10px; box-shadow:none; }
    .cine-filter .form-control:focus,.cine-filter .form-select:focus { border-color:#c6a15b; box-shadow:0 0 0 3px rgba(103,88,232,.1); }
    .cine-tabs { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:25px; }
    .cine-tab { border:1px solid #e4ddcf; border-radius:999px; padding:9px 16px; color:#5e6277; background:#fff; text-decoration:none; font-size:.88rem; font-weight:800; transition:.2s; }
    .cine-tab:hover,.cine-tab.active { color:#fff; background:#b58b48; border-color:#b58b48; }
    .cine-movie-card { height:100%; overflow:hidden; border:1px solid #ececf3; border-radius:17px; background:#fff; box-shadow:0 7px 24px rgba(25,28,55,.055); transition:transform .25s,box-shadow .25s; }
    .cine-movie-card:hover { transform:translateY(-6px); box-shadow:0 18px 38px rgba(25,28,55,.13); }
    .cine-poster { position:relative; display:block; aspect-ratio:2/2.85; overflow:hidden; background:#e9eaf1; }
    .cine-poster img { width:100%; height:100%; object-fit:cover; transition:transform .45s; }
    .cine-movie-card:hover .cine-poster img { transform:scale(1.055); }
    .cine-age { position:absolute; left:12px; top:12px; padding:5px 9px; border-radius:7px; background:#e54b5d; color:#fff; font-size:.72rem; font-weight:900; box-shadow:0 4px 12px #0003; }
    .cine-rating { position:absolute; right:11px; top:11px; padding:5px 8px; border-radius:7px; color:#191a26; background:#ffc45e; font-size:.76rem; font-weight:900; }
    .cine-card-body { padding:15px; display:flex; flex-direction:column; min-height:190px; }
    .cine-movie-title { color:#202338; text-decoration:none; font-size:1rem; line-height:1.45; font-weight:900; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; min-height:46px; }
    .cine-movie-title:hover { color:var(--cine-primary); }
    .cine-meta { color:#85899c; font-size:.78rem; line-height:1.7; }
    .cine-card-actions { display:flex; gap:8px; margin-top:auto; padding-top:13px; }
    .cine-card-actions .btn { flex:1; border-radius:9px; font-size:.78rem; font-weight:800; padding:9px 7px; }
    .cine-buy { background:#b58b48; color:#fff; border:1px solid #b58b48; }
    .cine-buy:hover { background:#8e6a32; color:#fff; }
    .cine-detail { color:#5d6073; border:1px solid #e5e6ef; }
    .cine-detail:hover { color:#8e6a32; border-color:#c6a15b; }
    .cine-empty { padding:55px 20px; border:1px dashed #d7d9e5; border-radius:18px; text-align:center; background:#fff; }
    .cine-time-slot { display:inline-flex; flex-direction:column; min-width:105px; padding:10px 13px; border:1px solid #e4e5ee; border-radius:11px; text-decoration:none; color:#25283b; background:#f8f8fc; transition:.2s; }
    .cine-time-slot:hover { color:#fff; background:#b58b48; border-color:#b58b48; }
    .cine-time-slot strong { font-size:1.1rem; }
    .cine-time-slot small { font-size:.72rem; opacity:.8; }
    .cine-modal .modal-content { border:0; border-radius:18px; overflow:hidden; }
    .cine-modal .modal-header { color:#fff; background:#1d1c19; border:0; }
    @media(max-width:767.98px) { .cine-hero{min-height:480px}.cine-hero-content{padding-top:72px;padding-bottom:72px}.cine-hero::after{background:linear-gradient(90deg,rgba(13,13,12,.94),rgba(13,13,12,.62)),linear-gradient(0deg,rgba(9,12,27,.5),transparent)}.cine-section{padding:38px 0}.cine-section-heading{align-items:start;flex-direction:column}.cine-card-body{padding:12px;min-height:185px}.cine-movie-title{font-size:.9rem}.cine-card-actions{flex-direction:column}.cine-benefit{padding:13px 5px}.cine-benefit small{font-size:.7rem} }
</style>

<div class="cine-home">
    <section class="cine-hero">
        <div class="container">
            <div class="cine-hero-content">
                <span class="cine-eyebrow"><i class="bi bi-stars"></i> Trải nghiệm điện ảnh khác biệt</span>
                <h1><?= $e($heroTitle) ?></h1>
                <p>Đặt vé trực tuyến nhanh chóng, chủ động chọn vị trí ghế yêu thích và tận hưởng những khoảnh khắc điện ảnh tuyệt vời tại <strong><?= $e($cinemaInfo['name'] ?? 'rạp phim') ?></strong>.</p>
                <div class="d-flex flex-wrap gap-3 mt-4">
                    <a href="#movie-list" class="cine-btn-primary"><i class="bi bi-ticket-perforated-fill"></i> Khám phá phim</a>
                    <a href="?route=schedule" class="cine-btn-ghost"><i class="bi bi-calendar3"></i> Xem lịch chiếu</a>
                </div>
                <?php if ($featuredMovie): ?>
                    <div class="mt-4 text-white-50 small"><i class="bi bi-film me-1"></i> Gợi ý nổi bật: <?= $e($heroTitle) ?></div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="cine-benefits">
        <div class="container">
            <div class="row g-0">
                <div class="col-6 col-lg-3"><div class="cine-benefit"><span class="cine-benefit-icon"><i class="bi bi-phone"></i></span><div><strong>Đặt vé online</strong><small>Chọn vé mọi lúc</small></div></div></div>
                <div class="col-6 col-lg-3"><div class="cine-benefit"><span class="cine-benefit-icon"><i class="bi bi-grid-3x3-gap"></i></span><div><strong>Chọn ghế yêu thích</strong><small>Sơ đồ ghế trực quan</small></div></div></div>
                <div class="col-6 col-lg-3"><div class="cine-benefit"><span class="cine-benefit-icon"><i class="bi bi-qr-code-scan"></i></span><div><strong>Vé điện tử QR</strong><small>Check-in thuận tiện</small></div></div></div>
                <div class="col-6 col-lg-3"><div class="cine-benefit"><span class="cine-benefit-icon"><i class="bi bi-shield-check"></i></span><div><strong>Thanh toán an toàn</strong><small>Thông tin rõ ràng</small></div></div></div>
            </div>
        </div>
    </section>

    <section class="cine-section" id="movie-list">
        <div class="container">
            <div class="cine-section-heading">
                <div><div class="cine-kicker">THE CINEMA COLLECTION</div><h2 class="cine-section-title">Những bộ phim không thể bỏ lỡ</h2></div>
                <a href="?route=schedule" class="text-decoration-none fw-bold" style="color:#b58b48">Lịch chiếu <i class="bi bi-arrow-right"></i></a>
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
                <?php $filterQuery = ($keyword ?? '') !== '' ? '&q=' . rawurlencode($keyword) : ''; $genreQuery = !empty($selectedGenreId) ? '&genre_id=' . (int) $selectedGenreId : ''; ?>
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
                    <?php foreach ($movies as $movie): ?>
                        <?php
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
                                        <?php if ($movie['status'] === 'now_showing'): ?>
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
                                <div class="modal-header"><div><div class="small text-white-50">CHỌN SUẤT CHIẾU</div><h5 class="modal-title fw-bold"><?= $e($movie['title_vi'] ?: $movie['title']) ?></h5></div><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Đóng"></button></div>
                                <div class="modal-body p-4">
                                    <div class="mb-3"><strong><?= $e($cinemaInfo['name'] ?? 'Rạp chiếu phim') ?></strong><div class="small text-muted"><?= $e($cinemaInfo['address'] ?? '') ?></div></div>
                                    <?php if (empty($movieShows)): ?>
                                        <div class="alert alert-warning mb-0">Hiện chưa có suất chiếu khả dụng cho phim này. Vui lòng quay lại sau.</div>
                                    <?php else: ?>
                                        <div class="d-flex flex-wrap gap-2">
                                            <?php foreach ($movieShows as $show): ?>
                                                <a class="cine-time-slot" href="?route=booking/seats&show_id=<?= (int) $show['id'] ?>"><strong><?= date('H:i', strtotime($show['start_time'])) ?></strong><small><?= date('d/m', strtotime($show['start_time'])) ?> · <?= $e($show['room_name']) ?></small><small><?= (int) ($show['available_seats'] ?? 0) ?> ghế trống</small></a>
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
</div>
