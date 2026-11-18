<?php
$e = static fn($value): string => htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
$featuredMovie = $movies[0] ?? null;
$featuredBackdrop = $featuredMovie['backdrop_path'] ?? '';
if (!$featuredBackdrop) {
    $featuredBackdrop = 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?auto=format&fit=crop&w=2000&q=85';
}
$heroTitle = $featuredMovie ? ($featuredMovie['title_vi'] ?: $featuredMovie['title']) : 'Chạm đến thế giới điện ảnh';
$currentStatus = $currentStatus ?? 'now_showing';
?>

<style>
    .cine-hero {
        position: relative;
        min-height: 560px;
        display: flex;
        align-items: center;
        overflow: hidden;
        isolation: isolate;
        background: #0b0c10;
    }
    .cine-hero::before {
        content: "";
        position: absolute;
        inset: 0;
        z-index: -2;
        background-image: url('<?= $e($featuredBackdrop) ?>');
        background-size: cover;
        background-position: center 30%;
        transform: scale(1.03);
    }
    .cine-hero::after {
        content: "";
        position: absolute;
        inset: 0;
        z-index: -1;
        background:
            linear-gradient(105deg, rgba(11,12,16,0.97) 0%, rgba(11,12,16,0.82) 42%, rgba(11,12,16,0.25) 100%),
            linear-gradient(0deg, rgba(11,12,16,0.9) 0%, transparent 55%);
    }
    .cine-hero-content { max-width: 720px; padding: 90px 0 80px; }
    .cine-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--accent);
        font-size: 0.78rem;
        font-weight: 800;
        letter-spacing: 0.16em;
        text-transform: uppercase;
        margin-bottom: 14px;
    }
    .cine-hero h1 {
        color: #fff;
        font-family: 'Outfit', 'Be Vietnam Pro', sans-serif;
        font-size: clamp(2.4rem, 5vw, 4.2rem);
        line-height: 1.08;
        font-weight: 800;
        margin: 0 0 16px;
        letter-spacing: -0.03em;
    }
    .cine-hero p {
        color: rgba(255,255,255,0.78);
        max-width: 560px;
        font-size: 1.05rem;
        line-height: 1.75;
        margin-bottom: 28px;
    }
    .cine-hero-actions { display: flex; flex-wrap: wrap; gap: 12px; }

    .cine-benefits {
        background: var(--bg-soft);
        border-top: 1px solid var(--border);
        border-bottom: 1px solid var(--border);
        padding: 18px 0;
    }
    .cine-benefit {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 8px 0;
    }
    .cine-benefit-icon {
        width: 44px;
        height: 44px;
        flex: 0 0 44px;
        display: grid;
        place-items: center;
        border-radius: 12px;
        color: var(--accent);
        background: var(--accent-soft);
        font-size: 1.15rem;
        border: 1px solid rgba(255,209,102,0.15);
    }
    .cine-benefit strong { display: block; font-size: 0.92rem; color: #fff; }
    .cine-benefit small { color: var(--muted); font-size: 0.82rem; }

    .cine-filter-bar {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 18px;
        padding: 18px;
        margin-bottom: 24px;
    }
    .cine-empty {
        text-align: center;
        padding: 64px 20px;
        background: var(--surface);
        border: 1px dashed var(--border);
        border-radius: 18px;
    }
    .cine-movie-body { padding: 14px 16px 16px; }
    .cine-buy {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        background: var(--gradient);
        color: #1a0f0a !important;
        font-weight: 700;
        font-size: 0.88rem;
        border: none;
        border-radius: 999px;
        padding: 8px 14px;
        text-decoration: none;
        transition: transform .2s ease, filter .2s ease;
    }
    .cine-buy:hover { transform: translateY(-1px); filter: brightness(1.05); color: #1a0f0a !important; }
    .cine-detail {
        width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        border: 1px solid var(--border);
        color: #fff;
        background: transparent;
        transition: all .2s ease;
    }
    .cine-detail:hover { background: rgba(255,255,255,0.08); border-color: rgba(255,255,255,0.25); color: #fff; }

    .cine-time-slot {
        display: inline-flex;
        flex-direction: column;
        gap: 2px;
        min-width: 110px;
        padding: 12px 14px;
        border-radius: 12px;
        background: var(--surface-2);
        border: 1px solid var(--border);
        color: #fff;
        text-decoration: none;
        transition: all .2s ease;
    }
    .cine-time-slot:hover {
        background: var(--primary-soft);
        border-color: rgba(255,77,109,0.45);
        color: #fff;
        transform: translateY(-2px);
    }
    .cine-time-slot strong { font-family: 'Outfit', sans-serif; font-size: 1.05rem; }
    .cine-time-slot small { color: var(--muted); font-size: 0.75rem; }
</style>

<div class="cine-home">
    <!-- Hero -->
    <section class="cine-hero">
        <div class="container">
            <div class="cine-hero-content">
                <div class="cine-eyebrow">
                    <i class="bi bi-stars"></i> Trải nghiệm điện ảnh cao cấp
                </div>
                <h1><?= $e($heroTitle) ?></h1>
                <p>
                    <?php if ($featuredMovie): ?>
                        <?= $e(mb_strimwidth($featuredMovie['overview'] ?? $featuredMovie['description'] ?? 'Khám phá suất chiếu mới nhất, chọn ghế yêu thích và đặt vé chỉ trong vài bước.', 0, 160, '...')) ?>
                    <?php else: ?>
                        Đặt vé xem phim nhanh chóng, giữ ghế thông minh và thanh toán tiện lợi tại CineNow.
                    <?php endif; ?>
                </p>
                <div class="cine-hero-actions">
                    <?php if ($featuredMovie): ?>
                        <a class="btn-cine" href="?route=movie/detail&id=<?= (int) $featuredMovie['id'] ?>">
                            <i class="bi bi-ticket-perforated"></i> Đặt vé ngay
                        </a>
                        <a class="btn-cine-outline" href="?route=schedule">
                            <i class="bi bi-calendar3"></i> Xem lịch chiếu
                        </a>
                    <?php else: ?>
                        <a class="btn-cine" href="?route=schedule"><i class="bi bi-calendar3"></i> Xem lịch chiếu</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits -->
    <section class="cine-benefits">
        <div class="container">
            <div class="row g-2">
                <div class="col-md-4">
                    <div class="cine-benefit">
                        <div class="cine-benefit-icon"><i class="bi bi-lightning-charge-fill"></i></div>
                        <div>
                            <strong>Giữ ghế nhanh 10 phút</strong>
                            <small>Không lo mất chỗ khi đang thanh toán</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="cine-benefit">
                        <div class="cine-benefit-icon"><i class="bi bi-phone"></i></div>
                        <div>
                            <strong>Vé điện tử & QR</strong>
                            <small>Quét mã vào rạp tiện lợi</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="cine-benefit">
                        <div class="cine-benefit-icon"><i class="bi bi-wallet2"></i></div>
                        <div>
                            <strong>Ví thành viên</strong>
                            <small>Thanh toán nội bộ, ưu đãi riêng</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Movies -->
    <section class="cine-section">
        <div class="container">
            <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
                <div>
                    <div class="cine-kicker">Danh sách phim</div>
                    <h2 class="cine-section-title">Phim đang chiếu & sắp ra mắt</h2>
                </div>
            </div>

            <!-- Filter -->
            <form method="GET" action="index.php" class="cine-filter-bar">
                <input type="hidden" name="route" value="home">
                <input type="hidden" name="status" value="<?= $e($currentStatus) ?>">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label">Tìm kiếm</label>
                        <input type="text" name="q" class="form-control" value="<?= $e($keyword ?? '') ?>" placeholder="Tên phim, diễn viên...">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Thể loại</label>
                        <select class="form-select" name="genre_id">
                            <option value="">Tất cả thể loại</option>
                            <?php foreach (($genres ?? []) as $genre): ?>
                                <option value="<?= (int) $genre['id'] ?>" <?= (int) ($selectedGenreId ?? 0) === (int) $genre['id'] ? 'selected' : '' ?>>
                                    <?= $e($genre['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 d-grid">
                        <button class="btn-cine" type="submit"><i class="bi bi-funnel"></i> Lọc phim</button>
                    </div>
                </div>
            </form>

            <!-- Status tabs -->
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
                    <div class="display-5 mb-3" style="color:var(--muted)"><i class="bi bi-camera-reels"></i></div>
                    <h4 class="fw-bold text-white">Chưa tìm thấy bộ phim phù hợp</h4>
                    <p class="text-muted mb-4">Thử từ khóa khác hoặc thay đổi bộ lọc nhé.</p>
                    <a href="?route=home" class="btn-cine">Xóa bộ lọc</a>
                </div>
            <?php else: ?>
                <div class="row g-3 g-lg-4">
                    <?php foreach ($movies as $movie): ?>
                        <?php
                        $age = strtoupper($movie['age_rating'] ?: 'P');
                        $ageClass = 'age-P';
                        if (str_contains($age, '18')) $ageClass = 'age-T18';
                        elseif (str_contains($age, '16')) $ageClass = 'age-T16';
                        elseif (str_contains($age, '13')) $ageClass = 'age-T13';

                        $poster = $movie['poster_path'] ?? '';
                        if ($poster && !str_starts_with($poster, 'http') && !str_starts_with($poster, '/')) {
                            $poster = 'public/images/posters/' . $poster;
                        } elseif ($poster && str_starts_with($poster, '/')) {
                            $poster = ltrim($poster, '/');
                        }
                        if (!$poster) {
                            $poster = 'https://via.placeholder.com/400x600/1e2230/9aa3b5?text=No+Poster';
                        }

                        $movieShows = $movie['shows'] ?? [];
                        ?>
                        <div class="col-6 col-md-4 col-lg-3">
                            <article class="cine-movie-card">
                                <a class="cine-poster" href="?route=movie/detail&id=<?= (int) $movie['id'] ?>">
                                    <span class="cine-age <?= $ageClass ?>"><?= $e($age) ?></span>
                                    <img src="<?= $e($poster) ?>" alt="<?= $e($movie['title_vi'] ?: $movie['title']) ?>" loading="lazy">
                                </a>
                                <div class="cine-movie-body">
                                    <h3 class="cine-movie-title">
                                        <a href="?route=movie/detail&id=<?= (int) $movie['id'] ?>"><?= $e($movie['title_vi'] ?: $movie['title']) ?></a>
                                    </h3>
                                    <div class="cine-movie-meta mb-3">
                                        <?= $e($movie['duration'] ? $movie['duration'] . ' phút' : '') ?>
                                        <?php if (!empty($movie['genre_names'])): ?>
                                            · <?= $e(is_array($movie['genre_names']) ? implode(', ', array_slice($movie['genre_names'], 0, 2)) : $movie['genre_names']) ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <?php if (!empty($movieShows)): ?>
                                            <button type="button" class="cine-buy flex-grow-1" data-bs-toggle="modal" data-bs-target="#cineShowModal<?= (int) $movie['id'] ?>">
                                                <i class="bi bi-ticket-perforated"></i> Mua vé
                                            </button>
                                        <?php else: ?>
                                            <a class="cine-buy flex-grow-1" href="?route=movie/detail&id=<?= (int) $movie['id'] ?>">Chi tiết</a>
                                        <?php endif; ?>
                                        <a class="cine-detail" href="?route=movie/detail&id=<?= (int) $movie['id'] ?>" aria-label="Chi tiết">
                                            <i class="bi bi-arrow-up-right"></i>
                                        </a>
                                    </div>
                                </div>
                            </article>
                        </div>

                        <!-- Show modal -->
                        <div class="modal fade" id="cineShowModal<?= (int) $movie['id'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header border-0 pb-0">
                                        <div>
                                            <div class="small" style="color:var(--muted)">CHỌN SUẤT CHIẾU</div>
                                            <h5 class="modal-title fw-bold"><?= $e($movie['title_vi'] ?: $movie['title']) ?></h5>
                                        </div>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Đóng"></button>
                                    </div>
                                    <div class="modal-body pt-3">
                                        <div class="mb-3">
                                            <strong><?= $e($cinemaInfo['name'] ?? 'Rạp chiếu phim') ?></strong>
                                            <div class="small text-muted"><?= $e($cinemaInfo['address'] ?? '') ?></div>
                                        </div>
                                        <?php if (empty($movieShows)): ?>
                                            <div class="alert alert-warning mb-0">Hiện chưa có suất chiếu khả dụng cho phim này.</div>
                                        <?php else: ?>
                                            <div class="d-flex flex-wrap gap-2">
                                                <?php foreach ($movieShows as $show): ?>
                                                    <?php $isEarly = \App\Models\ShowModel::isEarlyShow($show); ?>
                                                    <a class="cine-time-slot <?= $isEarly ? 'slot-early' : '' ?>" href="?route=booking/seats&show_id=<?= (int) $show['id'] ?>">
                                                        <strong>
                                                            <?= date('H:i', strtotime($show['start_time'])) ?>
                                                            <?php if ($isEarly): ?><span class="badge-early">Sớm</span><?php endif; ?>
                                                        </strong>
                                                        <small><?= date('d/m', strtotime($show['start_time'])) ?> · <?= $e($show['room_name'] ?? '') ?></small>
                                                        <small><?= (int) ($show['available_seats'] ?? 0) ?> ghế · <?= number_format((float) ($show['base_price'] ?? 0)) ?>đ</small>
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
    </section>
</div>
