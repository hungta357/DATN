<div class="container py-4">
    <div class="text-center mb-4">
        <h2 class="oswald-font fw-bold text-uppercase mb-1" style="color: var(--beta-blue);">
            LỊCH CHIẾU TẠI RẠP <?= htmlspecialchars(strtoupper($cinemaInfo['name'])) ?>
        </h2>
        <p class="text-muted small mb-0">
            <i class="bi bi-geo-alt-fill text-danger"></i> <?= htmlspecialchars($cinemaInfo['address']) ?>
        </p>
    </div>

    <!-- THANH CHỌN NGÀY KIỂU BETA CINEMAS -->
    <div class="d-flex justify-content-center flex-wrap gap-2 mb-4 pb-3 border-bottom">
        <a href="?route=schedule"
           class="btn <?= empty($selectedDate) ? 'btn-beta-buy' : 'btn-outline-secondary' ?> px-3 py-2">
            Tất cả ngày
        </a>
        <?php
        $daysOfWeek = ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'];
        for ($i = 0; $i < 6; $i++):
            $ts = strtotime("+{$i} days");
            $dateVal = date('Y-m-d', $ts);
            $dayLabel = date('d/m', $ts) . ' - ' . $daysOfWeek[(int) date('w', $ts)];
        ?>
            <a href="?route=schedule&date=<?= $dateVal ?>"
               class="btn <?= ($selectedDate === $dateVal) ? 'btn-beta-buy' : 'btn-outline-secondary' ?> px-3 py-2 oswald-font">
                <?= $dayLabel ?>
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
                        <img src="<?= htmlspecialchars($movie['poster_path'] ?: 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=600') ?>"
                             alt="<?= htmlspecialchars($movie['title']) ?>">
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
                                    <div class="slot-seats"><?= (int) ($sh['available_seats'] ?? 20) ?> ghế trống</div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

