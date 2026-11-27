<div class="container py-4">
    <div class="text-center mb-4">
        <h2 class="oswald-font fw-bold text-uppercase mb-1" style="color: var(--beta-blue);">
            LỊCH CHIẾU TẠI RẠP <?= htmlspecialchars(strtoupper($cinemaInfo['name'])) ?>
        </h2>
        <p class="text-muted small mb-0">
            <i class="bi bi-geo-alt-fill text-danger"></i> <?= htmlspecialchars($cinemaInfo['address']) ?>
        </p>
    </div>

    <!-- THANH CHON NGAY: cac the ngay nam ngang, giu nguyen mau CineNow -->
    <style>
        .cinenow-date-strip {
            display: flex;
            gap: 12px;
            overflow-x: auto;
            padding: 8px 4px 16px;
            margin: 0 auto 28px;
            max-width: 100%;
            scrollbar-width: thin;
            scrollbar-color: var(--beta-blue) #e8e5de;
        }
        .cinenow-date-card {
            flex: 0 0 76px;
            min-height: 82px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 5px;
            border: 1px solid rgba(120, 110, 90, .28);
            border-radius: 10px;
            background: #fff;
            color: inherit;
            text-decoration: none;
            transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
        }
        .cinenow-date-card:hover {
            border-color: var(--beta-blue);
            box-shadow: 0 5px 14px rgba(0,0,0,.08);
            transform: translateY(-2px);
            color: inherit;
        }
        .cinenow-date-card.is-active {
            border: 2px solid var(--beta-blue);
            background: #fff;
            box-shadow: 0 4px 14px rgba(0,0,0,.08);
        }
        .cinenow-date-weekday {
            font-size: 11px;
            font-weight: 700;
            color: #77736b;
            text-transform: uppercase;
            letter-spacing: .04em;
        }
        .cinenow-date-number {
            font-family: 'Oswald', sans-serif;
            font-size: 27px;
            line-height: 1;
            font-weight: 700;
            color: #292720;
        }
        .cinenow-date-card.is-active .cinenow-date-weekday,
        .cinenow-date-card.is-active .cinenow-date-number {
            color: var(--beta-blue);
        }
        .cinenow-date-month {
            font-size: 10px;
            color: #89857c;
        }
        @media (max-width: 575.98px) {
            .cinenow-date-strip { gap: 8px; }
            .cinenow-date-card { flex-basis: 68px; min-height: 76px; }
        }
    </style>

    <div class="cinenow-date-strip" aria-label="Chọn ngày chiếu">
        <?php
        $daysOfWeek = ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'];
        for ($i = 0; $i < 7; $i++):
            $ts = strtotime("+{$i} days");
            $dateVal = date('Y-m-d', $ts);
            $isActive = ($selectedDate === $dateVal);
        ?>
            <a href="?route=schedule&date=<?= htmlspecialchars($dateVal, ENT_QUOTES, 'UTF-8') ?>"
               class="cinenow-date-card <?= $isActive ? 'is-active' : '' ?>"
               aria-label="Chọn ngày <?= date('d/m/Y', $ts) ?>"
               aria-current="<?= $isActive ? 'date' : 'false' ?>">
                <span class="cinenow-date-weekday"><?= $daysOfWeek[(int) date('w', $ts)] ?></span>
                <span class="cinenow-date-number"><?= date('d', $ts) ?></span>
                <span class="cinenow-date-month">Tháng <?= date('m', $ts) ?></span>
            </a>
        <?php endfor; ?>
    </div>

    <!-- DANH SÁCH PHIM & KHUNG GIỜ CHIẾU TẠI RẠP -->
    <?php foreach ($movies as $movie): ?>
        <?php
        $movieShows = $showsByMovie[(int) $movie['id']] ?? [];
        $age = strtoupper($movie['age_rating'] ?: 'P');
        $ageClass = 'age-P';
        if (str_contains($age, '13')) $ageClass = 'age-T13';
        elseif (str_contains($age, '16')) $ageClass = 'age-T16';
        elseif (str_contains($age, '18')) $ageClass = 'age-T18';
        ?>
        <div class="card border-0 shadow-sm mb-4 p-3 p-md-4 bg-white rounded-4">
            <div class="row g-4 align-items-center">
                <div class="col-md-3 col-lg-2">
                    <a href="?route=movie/detail&id=<?= (int) $movie['id'] ?>" class="beta-poster-wrap d-block">
                        <span class="beta-age-badge <?= $ageClass ?>"><?= htmlspecialchars($age) ?></span>
                        <img src="<?= htmlspecialchars($movie['poster_path'] ?: 'public/images/posters/dune-part-two.svg') ?>"
                             alt="<?= htmlspecialchars($movie['title']) ?>"
                             onerror="this.onerror=null;this.src='public/images/posters/dune-part-two.svg';">
                    </a>
                </div>
                <div class="col-md-9 col-lg-10">
                    <h4 class="fw-bold mb-1">
                        <a href="?route=movie/detail&id=<?= (int) $movie['id'] ?>" class="text-decoration-none" style="color: var(--beta-blue);">
                            <?= htmlspecialchars($movie['title_vi'] ?: $movie['title']) ?>
                        </a>
                    </h4>
                    <div class="small text-muted mb-3">
                        <i class="bi bi-tags-fill text-primary me-1"></i> <?= htmlspecialchars($movie['genre_names'] ?: 'Điện ảnh') ?>
                        <span class="mx-2">|</span>
                        <i class="bi bi-clock-fill text-warning me-1"></i> <?= (int) $movie['runtime'] ?> phút
                    </div>

                    <div class="fw-bold small text-uppercase text-secondary mb-2">
                        2D PHỤ ĐỀ / LỒNG TIẾNG:
                    </div>

                    <?php if (empty($movieShows)): ?>
                        <div class="text-muted small fst-italic">Chưa có suất chiếu trong ngày đã chọn.</div>
                    <?php else: ?>
                        <div class="d-flex flex-wrap gap-3">
                            <?php foreach ($movieShows as $sh): ?>
                                <a href="?route=booking/seats&show_id=<?= (int) $sh['id'] ?>" class="beta-time-slot">
                                    <div class="slot-hour"><?= date('H:i', strtotime($sh['start_time'])) ?></div>
                                    <div class="small text-primary fw-semibold"><?= date('d/m', strtotime($sh['start_time'])) ?> • <?= htmlspecialchars($sh['room_name']) ?></div>
                                    <div class="slot-seats"><?= (int) ($sh['available_seats'] ?? 0) ?> ghế trống</div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

