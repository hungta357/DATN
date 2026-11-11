<?php
$currentRoute = $_GET['route'] ?? 'home';
$userModelHeader = new \App\Models\UserModel();
$headerCinema = $cinemaInfo ?? $userModelHeader->getCinemaInfo();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? $headerCinema['name']) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&family=Oswald:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --beta-blue: #03599d;
            --beta-cyan: #258dcf;
            --beta-orange: #f36523;
            --beta-gradient: linear-gradient(to right, #0a64ad 0%, #258dcf 51%, #3db1f3 100%);
        }
        body {
            background-color: #f8f9fa;
            color: #333333;
            font-family: 'Be Vietnam Pro', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        h1, h2, h3, h4, .oswald-font {
            font-family: 'Oswald', sans-serif;
        }
        /* Top black bar giống Beta Cinemas (dành cho 1 rạp chiếu lẻ) */
        .beta-topbar {
            background-color: #111111;
            color: #ffffff;
            font-size: 13px;
            padding: 6px 0;
        }
        .beta-topbar a {
            color: #ffffff;
            text-decoration: none;
            transition: color 0.2s;
        }
        .beta-topbar a:hover {
            color: #3db1f3;
        }
        /* Main Navbar Beta Cinemas */
        .beta-navbar {
            background-color: #ffffff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            padding: 12px 0;
        }
        .beta-logo-box {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
        .beta-logo-mark {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: linear-gradient(135deg, #03599d 0%, #3db1f3 55%, #f36523 100%);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-family: 'Oswald', sans-serif;
            font-size: 24px;
            font-weight: 700;
            box-shadow: 0 4px 10px rgba(3, 89, 157, 0.25);
        }
        .beta-nav-link {
            font-family: 'Oswald', sans-serif;
            font-size: 17px;
            font-weight: 600;
            text-transform: uppercase;
            color: #222222 !important;
            padding: 8px 16px !important;
            letter-spacing: 0.3px;
            transition: color 0.2s;
        }
        .beta-nav-link:hover, .beta-nav-link.active {
            color: var(--beta-blue) !important;
        }
        /* Beta Cinema Tabs */
        .beta-movie-tabs {
            border-bottom: 1px solid #dee2e6;
            display: flex;
            justify-content: center;
            gap: 36px;
            margin-bottom: 30px;
        }
        .beta-movie-tab {
            font-family: 'Oswald', sans-serif;
            font-size: 24px;
            font-weight: 600;
            text-transform: uppercase;
            color: #555555;
            text-decoration: none;
            padding-bottom: 12px;
            position: relative;
            transition: color 0.2s;
        }
        .beta-movie-tab:hover, .beta-movie-tab.active {
            color: var(--beta-blue);
        }
        .beta-movie-tab.active::after {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            bottom: -2px;
            height: 4px;
            background: var(--beta-gradient);
            border-radius: 4px;
        }
        /* Movie Card Beta Style */
        .beta-movie-card {
            background: transparent;
            border: none;
            transition: transform 0.25s ease;
        }
        .beta-movie-card:hover {
            transform: translateY(-4px);
        }
        .beta-poster-wrap {
            position: relative;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 6px 18px rgba(0,0,0,0.12);
            aspect-ratio: 2 / 3;
            background-color: #e9ecef;
        }
        .beta-poster-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.35s ease;
        }
        .beta-poster-wrap:hover img {
            transform: scale(1.05);
        }
        .beta-age-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            padding: 3px 10px;
            border-radius: 6px;
            font-family: 'Oswald', sans-serif;
            font-weight: 700;
            font-size: 14px;
            color: #fff;
            z-index: 2;
            box-shadow: 0 2px 6px rgba(0,0,0,0.3);
        }
        .age-P { background-color: #00a651; }
        .age-T13 { background-color: #f5a623; }
        .age-T16 { background-color: #f36523; }
        .age-T18 { background-color: #ed1c24; }
        .beta-movie-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--beta-blue);
            text-decoration: none;
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .beta-movie-title:hover {
            color: var(--beta-orange);
        }
        .btn-beta-buy {
            background-image: var(--beta-gradient);
            background-size: 200% auto;
            color: #ffffff !important;
            font-family: 'Oswald', sans-serif;
            font-size: 16px;
            font-weight: 600;
            text-transform: uppercase;
            border: none;
            border-radius: 6px;
            padding: 8px 16px;
            position: relative;
            transition: 0.3s;
            box-shadow: 0 4px 10px rgba(37, 141, 207, 0.3);
        }
        .btn-beta-buy:hover {
            background-position: right center;
            transform: translateY(-1px);
        }
        /* Time slot button kiểu Beta Cinema */
        .beta-time-slot {
            display: inline-block;
            background-color: #f1f3f5;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 6px 14px;
            text-align: center;
            text-decoration: none;
            color: #222;
            transition: all 0.2s;
            min-width: 96px;
        }
        .beta-time-slot:hover {
            background-color: var(--beta-blue);
            border-color: var(--beta-blue);
            color: #fff;
        }
        .beta-time-slot:hover .slot-seats {
            color: #e0f2fe !important;
        }
        .beta-time-slot .slot-hour {
            font-family: 'Oswald', sans-serif;
            font-size: 16px;
            font-weight: 600;
        }
        .beta-time-slot .slot-seats {
            font-size: 11px;
            color: #6c757d;
        }
        /* Sơ đồ ghế phong cách Beta Cinema */
        .seat-box {
            width: 44px;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            margin: 4px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            user-select: none;
            border: none;
            transition: transform 0.15s;
        }
        .seat-box:hover { transform: scale(1.08); }
        .seat-available { background-color: #babbc3; color: #222; }
        .seat-vip { background-color: #f7b500; color: #fff; }
        .seat-couple { background-color: #e83e8c; color: #fff; width: 96px; }
        .seat-selected { background-color: #03599d !important; color: #fff !important; box-shadow: 0 0 0 2px #3db1f3; }
        .seat-held { background-color: #3db1f3; color: #fff; cursor: not-allowed; opacity: 0.7; }
        .seat-booked, .seat-blocked { background-color: #df2029; color: #fff; cursor: not-allowed; opacity: 0.65; }
        .screen-bar {
            height: 14px;
            background: linear-gradient(180deg, #03599d 0%, rgba(3,89,157,0.05) 100%);
            border-top: 4px solid #03599d;
            border-radius: 50% 50% 0 0 / 100% 100% 0 0;
            margin-bottom: 30px;
        }
        /* Footer Beta Cinemas */
        .beta-footer {
            margin-top: auto;
            background-color: #ffffff;
            border-top: 3px solid var(--beta-blue);
            color: #444;
        }

        /* ===== CINENOW PREMIUM DESIGN SYSTEM ===== */
        :root {
            --beta-blue: #c6a15b;
            --beta-cyan: #e2c98f;
            --beta-orange: #e2b96f;
            --beta-gradient: linear-gradient(135deg, #d9bd7b 0%, #b68b43 55%, #8e6a32 100%);
            --premium-ink: #171717;
            --premium-panel: #22211f;
            --premium-gold: #d7b56d;
            --premium-cream: #f7f5f0;
        }
        html { scroll-behavior: smooth; }
        body { background: var(--premium-cream); color: #25231f; }
        .beta-topbar { background:#11110f; color:#c9c4b9; padding:9px 0; font-size:11px; letter-spacing:.025em; }
        .beta-topbar a { color:#e5dfd2; transition:color .2s ease; }
        .beta-topbar a:hover { color:var(--premium-gold); }
        .beta-navbar { background:rgba(22,22,20,.97); box-shadow:0 8px 30px rgba(0,0,0,.16); padding:15px 0; border-bottom:1px solid rgba(215,181,109,.22); }
        .beta-logo-mark { border-radius:13px; background:linear-gradient(145deg,#e4ca8c,#9d7638); color:#171511; box-shadow:0 7px 22px rgba(215,181,109,.22); }
        .beta-nav-link { color:#e9e4d9 !important; font-family:'Be Vietnam Pro',sans-serif; font-size:11px; font-weight:800; letter-spacing:.075em; padding:12px 11px !important; }
        .beta-nav-link:hover,.beta-nav-link.active { color:var(--premium-gold) !important; }
        .beta-navbar .navbar-toggler { border-color:rgba(255,255,255,.24); }
        .beta-navbar .navbar-toggler-icon { filter:invert(1); }
        .beta-navbar .input-group { border:1px solid rgba(215,181,109,.3); border-radius:999px; overflow:hidden; }
        .beta-navbar .form-control { background:#2a2926; color:#fff; border:0; min-height:38px; padding-left:15px; }
        .beta-navbar .form-control::placeholder { color:#aaa397; }
        .beta-navbar .btn-outline-primary { color:var(--premium-gold); border:0; background:#2a2926; padding:0 14px; }
        .beta-navbar .btn-outline-primary:hover { color:#171511; background:var(--premium-gold); }
        .beta-footer { background:#171715; border-top:1px solid rgba(215,181,109,.38); color:#c9c4b9; padding-top:48px !important; padding-bottom:38px !important; }
        .beta-footer h5,.beta-footer h6 { color:#e4c987 !important; }
        .beta-footer .text-muted,.beta-footer .text-secondary { color:#aaa397 !important; }
        .beta-footer a { color:#c9c4b9 !important; transition:color .2s; }
        .beta-footer a:hover { color:var(--premium-gold) !important; }
        .beta-footer .bg-light { background:#24231f !important; border-color:rgba(215,181,109,.2) !important; }
        .beta-footer .bg-light .text-secondary { color:#c9c4b9 !important; }
        .btn-beta-buy,.cine-btn-primary,.cine-buy { background:var(--beta-gradient) !important; color:#211b10 !important; border:1px solid #c4a15e !important; box-shadow:0 8px 22px rgba(174,132,58,.2); font-weight:900; }
        .btn-beta-buy:hover,.cine-btn-primary:hover,.cine-buy:hover { filter:brightness(1.08); color:#17130c !important; transform:translateY(-2px); }
        .beta-movie-tab.active::after { background:var(--beta-gradient); }
        .beta-movie-title,.cine-movie-title { color:#29251e; }
        .beta-movie-title:hover,.cine-movie-title:hover { color:#a47b38; }
        .beta-poster-wrap,.cine-poster { box-shadow:0 14px 32px rgba(30,26,18,.16); }
        .seat-selected { background:#b58a43 !important; box-shadow:0 0 0 2px #e6cf9e !important; }
        .screen-bar { background:linear-gradient(180deg,#d4b36e 0%,rgba(212,179,110,.04) 100%); border-top-color:#c39b52; }
        main { flex:1 0 auto; }
        main > .container { padding-top:30px; padding-bottom:38px; }
        main .card { border:1px solid rgba(72,61,40,.08) !important; border-radius:18px !important; box-shadow:0 12px 35px rgba(36,31,21,.07) !important; }
        main .card-header { background:#fbfaf7 !important; }
        main .form-control,main .form-select { border-radius:10px; border-color:#e5dfd3; min-height:43px; }
        main .form-control:focus,main .form-select:focus { border-color:#c6a15b; box-shadow:0 0 0 3px rgba(198,161,91,.15); }
        main .breadcrumb a { color:#9b773d; }
        main .table thead th { background:#f2eee5; color:#5a4a2c; border-bottom-color:#e4dac7; font-size:.78rem; letter-spacing:.04em; text-transform:uppercase; }
        main .table td { border-color:#eee9df; }
        .cine-home { --cine-ink:#211e18; --cine-muted:#8b8478; --cine-primary:#b58b48; --cine-accent:#e5c987; color:#25231f; }
        .cine-hero { min-height:590px; background:#171612; }
        .cine-hero::after { background:linear-gradient(90deg,rgba(13,13,12,.97) 0%,rgba(18,17,14,.84) 42%,rgba(18,17,14,.2) 100%),linear-gradient(0deg,rgba(13,13,12,.72),transparent 52%); }
        .cine-hero-content { max-width:780px; padding-top:100px; padding-bottom:100px; }
        .cine-eyebrow { color:#e8cc8f; letter-spacing:.19em; }
        .cine-hero h1 { font-size:clamp(2.5rem,5.5vw,5rem); text-shadow:0 5px 30px rgba(0,0,0,.35); }
        .cine-btn-primary { border-radius:999px; padding:14px 25px; background:var(--beta-gradient) !important; color:#211b10 !important; }
        .cine-btn-ghost { border-radius:999px; border-color:rgba(231,211,165,.55); }
        .cine-btn-ghost:hover { background:#e5c987; border-color:#e5c987; color:#201b12; }
        .cine-benefits { background:#1d1c19; border-top:1px solid rgba(215,181,109,.24); border-bottom:1px solid rgba(215,181,109,.18); }
        .cine-benefit-icon { color:#e4c987; background:rgba(215,181,109,.12); border:1px solid rgba(215,181,109,.17); }
        .cine-section { padding:68px 0; }
        .cine-kicker { color:#a47b38; }
        .cine-section-title { color:#211e18; }
        .cine-filter { border-color:#e9e2d5; border-radius:18px; box-shadow:0 14px 40px rgba(44,36,22,.06); }
        .cine-tab { border-color:#e4ddcf; color:#6e675a; }
        .cine-tab:hover,.cine-tab.active { color:#211b10; background:var(--beta-gradient); border-color:#c6a15b; }
        .cine-movie-card { border-color:#eae4d9; border-radius:18px; box-shadow:0 12px 32px rgba(35,29,19,.07); }
        .cine-movie-card:hover { box-shadow:0 22px 44px rgba(35,29,19,.15); }
        .cine-rating { background:#e9d39f; color:#332817; }
        .cine-detail { border-color:#ded5c5; color:#655a46; }
        .cine-detail:hover { color:#8c682f; border-color:#c6a15b; }
        .cine-time-slot:hover { background:#b58b48; border-color:#b58b48; color:#fff; }
        .cine-modal .modal-header { background:#1d1c19; border-bottom:1px solid rgba(215,181,109,.3); }
        .cine-empty { border-color:#ded5c5; background:#fffdfa; }
        .auth-premium-note { color:#8b7b5e; }
        @media(max-width:767.98px) { .cine-hero{min-height:480px}.cine-hero-content{padding-top:72px;padding-bottom:72px}.beta-topbar .container{gap:8px}.beta-topbar .container>div:first-child{max-width:100%}.beta-nav-link{padding:9px 4px !important}.cine-section{padding:44px 0} }
    </style>
</head>
<body>

<!-- TOP BAR: Hiển thị cố định thông tin 1 Rạp chiếu lẻ duy nhất + Tài khoản -->
<div class="beta-topbar">
    <div class="container d-flex flex-wrap justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-3">
            <span>
                <i class="bi bi-geo-alt-fill text-warning me-1"></i>
                <strong><?= htmlspecialchars($headerCinema['name']) ?></strong> — <?= htmlspecialchars($headerCinema['address']) ?>
            </span>
            <?php if (!empty($headerCinema['phone'])): ?>
                <span class="d-none d-md-inline text-secondary">|</span>
                <span class="d-none d-md-inline">
                    <i class="bi bi-telephone-fill text-info me-1"></i>Hotline: <strong><?= htmlspecialchars($headerCinema['phone']) ?></strong>
                </span>
            <?php endif; ?>
        </div>
        <div class="d-flex align-items-center gap-3">
            <?php if (!empty($_SESSION['user'])): ?>
                <a href="?route=booking/my"><i class="bi bi-ticket-perforated me-1"></i>Vé của tôi</a>
                <span>|</span>
                <a href="?route=wallet"><i class="bi bi-wallet2 me-1"></i>Ví CineNow</a>
                <span>|</span>
                <a href="?route=wishlist"><i class="bi bi-heart me-1"></i>Yêu thích</a>
                <span>|</span>
                <a href="?route=notifications"><i class="bi bi-bell me-1"></i>Thông báo</a>
                <span>|</span>
                <span class="text-info fw-semibold"><?= htmlspecialchars($_SESSION['user']['name']) ?></span>
                <a href="?route=auth/logout" class="text-danger ms-1">[Thoát]</a>
            <?php else: ?>
                <a href="?route=auth/login"><i class="bi bi-person-circle me-1"></i>Đăng nhập</a>
                <span>|</span>
                <a href="?route=auth/register"><i class="bi bi-person-plus me-1"></i>Đăng ký thành viên</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- MAIN NAVBAR KIỂU BETA CINEMAS (Không có dropdown chọn tỉnh/thành vì là rạp chiếu lẻ) -->
<nav class="navbar navbar-expand-lg beta-navbar sticky-top">
    <div class="container">
        <a class="beta-logo-box me-4" href="?route=home">
            <span class="beta-logo-mark">C</span>
            <div>
                <div class="oswald-font fw-bold fs-4 lh-1" style="color: var(--beta-blue); letter-spacing: 0.5px;">
                    <?= htmlspecialchars(strtoupper($headerCinema['name'])) ?>
                </div>
                <div class="text-uppercase fw-semibold" style="font-size: 10px; color: var(--beta-orange); letter-spacing: 1.5px;">
                    Rạp Chiếu Phim Độc Lập
                </div>
            </div>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#betaMainNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="betaMainNav">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link beta-nav-link <?= $currentRoute === 'schedule' ? 'active' : '' ?>" href="?route=schedule">
                        Lịch Chiếu Theo Ngày
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link beta-nav-link <?= $currentRoute === 'home' ? 'active' : '' ?>" href="?route=home">
                        Phim
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link beta-nav-link <?= $currentRoute === 'about' ? 'active' : '' ?>" href="?route=about">
                        Thông Tin Rạp
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link beta-nav-link <?= $currentRoute === 'prices' ? 'active' : '' ?>" href="?route=prices">
                        Giá Vé
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link beta-nav-link <?= $currentRoute === 'promotions' ? 'active' : '' ?>" href="?route=promotions">
                        Tin Mới Và Ưu Đãi
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link beta-nav-link <?= in_array($currentRoute, ['wallet', 'booking/my']) ? 'active' : '' ?>" href="?route=wallet">
                        Thành Viên
                    </a>
                </li>
            </ul>

            <form method="GET" action="index.php" class="d-flex">
                <input type="hidden" name="route" value="home">
                <div class="input-group input-group-sm">
                    <input type="text" name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" class="form-control" placeholder="Tìm phim tại rạp...">
                    <button class="btn btn-outline-primary" type="submit"><i class="bi bi-search"></i></button>
                </div>
            </form>
        </div>
    </div>
</nav>

<?php if (!empty($_SESSION['flash'])): ?>
    <div class="container mt-3">
        <div class="alert alert-<?= htmlspecialchars($_SESSION['flash']['type']) ?> alert-dismissible fade show shadow-sm mb-0">
            <?= htmlspecialchars($_SESSION['flash']['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>

<main class="pb-5">
    <?= $content ?>
</main>

<footer class="beta-footer py-4">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-md-5">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="beta-logo-mark" style="width:34px;height:34px;font-size:18px;">C</span>
                    <h5 class="oswald-font fw-bold mb-0" style="color: var(--beta-blue);">
                        <?= htmlspecialchars(strtoupper($headerCinema['name'])) ?>
                    </h5>
                </div>
                <p class="small text-muted mb-1"><i class="bi bi-geo-alt-fill text-danger me-1"></i> Địa chỉ: <?= htmlspecialchars($headerCinema['address']) ?></p>
                <p class="small text-muted mb-0">
                    <i class="bi bi-telephone-fill text-primary me-1"></i> Hotline: <?= htmlspecialchars($headerCinema['phone'] ?: '1900 636807') ?> |
                    <i class="bi bi-envelope-fill text-primary ms-2 me-1"></i> <?= htmlspecialchars($headerCinema['email'] ?: 'cskh@cinenow.vn') ?>
                </p>
            </div>
            <div class="col-md-4">
                <h6 class="oswald-font text-uppercase fw-bold mb-2" style="color: var(--beta-blue);">Danh Mục Nhanh</h6>
                <div class="row small">
                    <div class="col-6">
                        <a href="?route=schedule" class="text-decoration-none text-secondary d-block mb-1">» Lịch chiếu theo ngày</a>
                        <a href="?route=home" class="text-decoration-none text-secondary d-block mb-1">» Phim đang chiếu</a>
                        <a href="?route=prices" class="text-decoration-none text-secondary d-block mb-1">» Bảng giá vé rạp</a>
                    </div>
                    <div class="col-6">
                        <a href="?route=promotions" class="text-decoration-none text-secondary d-block mb-1">» Ưu đãi & Combo</a>
                        <a href="?route=about" class="text-decoration-none text-secondary d-block mb-1">» Giới thiệu phòng chiếu</a>
                        <a href="?route=wallet" class="text-decoration-none text-secondary d-block mb-1">» Ví thành viên</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3 text-md-end">
                <div class="p-3 rounded-3 bg-light border">
                    <div class="oswald-font fw-bold text-uppercase small text-secondary">Đặt vé trực tuyến</div>
                    <div class="fs-5 fw-bold" style="color: var(--beta-orange);">Giữ ghế nhanh 10 phút</div>
                    <div class="small text-muted">Thanh toán Ví nội bộ / VNPay / MoMo</div>
                </div>
            </div>
        </div>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
