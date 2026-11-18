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
    <title><?= htmlspecialchars($pageTitle ?? $headerCinema['name'] ?? 'CineNow') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --bg: #0b0c10;
            --bg-soft: #12141c;
            --surface: #171a24;
            --surface-2: #1e2230;
            --border: rgba(255,255,255,0.08);
            --text: #f4f6fb;
            --muted: #9aa3b5;
            --primary: #ff4d6d;
            --primary-soft: rgba(255,77,109,0.15);
            --accent: #ffd166;
            --accent-soft: rgba(255,209,102,0.14);
            --success: #2dd4a8;
            --danger: #ff5c7a;
            --radius: 16px;
            --shadow: 0 18px 40px rgba(0,0,0,0.35);
            --gradient: linear-gradient(135deg, #ff4d6d 0%, #ff7a59 50%, #ffd166 100%);
            --nav-height: 72px;
        }

        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: var(--bg);
            color: var(--text);
            font-family: 'Be Vietnam Pro', system-ui, -apple-system, sans-serif;
            line-height: 1.6;
        }

        h1,h2,h3,h4,h5,.display-font {
            font-family: 'Outfit', 'Be Vietnam Pro', sans-serif;
            letter-spacing: -0.02em;
        }

        a { color: inherit; text-decoration: none; transition: color .2s ease, opacity .2s ease; }
        a:hover { color: var(--accent); }

        /* ========== TOPBAR ========== */
        .cine-topbar {
            background: #07080c;
            border-bottom: 1px solid var(--border);
            font-size: 12.5px;
            color: var(--muted);
            padding: 8px 0;
        }
        .cine-topbar a { color: #c9d0e0; }
        .cine-topbar a:hover { color: var(--accent); }

        /* ========== NAVBAR ========== */
        .cine-navbar {
            position: sticky;
            top: 0;
            z-index: 1030;
            background: rgba(11,12,16,0.88);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border);
            min-height: var(--nav-height);
        }
        .cine-navbar .navbar-brand {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            font-weight: 800;
            font-size: 1.25rem;
            color: #fff !important;
        }
        .cine-logo {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: var(--gradient);
            display: grid;
            place-items: center;
            color: #1a0f0a;
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 1.15rem;
            box-shadow: 0 8px 20px rgba(255,77,109,0.35);
        }
        .cine-nav-link {
            color: #c8d0e0 !important;
            font-weight: 600;
            font-size: 0.92rem;
            padding: 0.55rem 0.9rem !important;
            border-radius: 999px;
            transition: all .2s ease;
        }
        .cine-nav-link:hover,
        .cine-nav-link.active {
            color: #fff !important;
            background: rgba(255,255,255,0.06);
        }
        .cine-nav-link.active {
            background: var(--primary-soft);
            color: #ff8fa3 !important;
        }
        .cine-search .form-control {
            background: var(--surface);
            border: 1px solid var(--border);
            color: #fff;
            border-radius: 999px 0 0 999px;
            min-height: 40px;
            padding-left: 16px;
        }
        .cine-search .form-control::placeholder { color: #7d879c; }
        .cine-search .form-control:focus {
            background: var(--surface-2);
            border-color: rgba(255,77,109,0.45);
            box-shadow: 0 0 0 3px var(--primary-soft);
            color: #fff;
        }
        .cine-search .btn {
            border-radius: 0 999px 999px 0;
            border: 1px solid var(--border);
            border-left: 0;
            background: var(--surface);
            color: var(--accent);
            min-height: 40px;
            padding: 0 16px;
        }
        .cine-search .btn:hover { background: var(--primary); color: #fff; border-color: var(--primary); }

        .btn-cine {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: var(--gradient);
            color: #1a0f0a !important;
            font-weight: 700;
            border: none;
            border-radius: 999px;
            padding: 10px 20px;
            box-shadow: 0 10px 24px rgba(255,77,109,0.28);
            transition: transform .2s ease, box-shadow .2s ease, filter .2s ease;
        }
        .btn-cine:hover {
            transform: translateY(-2px);
            filter: brightness(1.05);
            box-shadow: 0 14px 28px rgba(255,77,109,0.38);
            color: #1a0f0a !important;
        }
        .btn-cine-outline {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 1px solid rgba(255,255,255,0.18);
            color: #fff !important;
            background: transparent;
            border-radius: 999px;
            padding: 10px 18px;
            font-weight: 600;
            transition: all .2s ease;
        }
        .btn-cine-outline:hover {
            background: rgba(255,255,255,0.08);
            border-color: rgba(255,255,255,0.3);
            color: #fff !important;
        }

        /* ========== MAIN CONTENT ========== */
        main {
            flex: 1 0 auto;
        }
        main > .container,
        main > .container-fluid {
            padding-top: 28px;
            padding-bottom: 48px;
        }

        /* Cards & surfaces */
        .card, .cine-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            color: var(--text);
            box-shadow: var(--shadow);
        }
        .card-header {
            background: var(--surface-2) !important;
            border-bottom: 1px solid var(--border) !important;
            color: var(--text);
        }
        .card-body { color: var(--text); }

        /* Forms */
        .form-control, .form-select {
            background: var(--surface-2);
            border: 1px solid var(--border);
            color: #fff;
            border-radius: 12px;
            min-height: 44px;
        }
        .form-control:focus, .form-select:focus {
            background: #222736;
            border-color: rgba(255,77,109,0.5);
            box-shadow: 0 0 0 3px var(--primary-soft);
            color: #fff;
        }
        .form-control::placeholder { color: #7d879c; }
        .form-label { color: #c9d0e0; font-weight: 500; }
        .form-text, .text-muted { color: var(--muted) !important; }

        /* Tables */
        .table {
            --bs-table-bg: transparent;
            --bs-table-color: var(--text);
            --bs-table-border-color: var(--border);
            --bs-table-striped-bg: rgba(255,255,255,0.02);
            --bs-table-hover-bg: rgba(255,255,255,0.04);
            color: var(--text);
        }
        .table thead th {
            background: var(--surface-2);
            color: #c9d0e0;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            border-bottom-color: var(--border);
        }

        /* Alerts */
        .alert {
            border-radius: 14px;
            border: 1px solid transparent;
        }
        .alert-success { background: rgba(45,212,168,0.12); border-color: rgba(45,212,168,0.25); color: #9af0d4; }
        .alert-danger, .alert-warning { background: rgba(255,77,109,0.12); border-color: rgba(255,77,109,0.25); color: #ffb3c1; }
        .alert-info { background: rgba(56,189,248,0.12); border-color: rgba(56,189,248,0.25); color: #a5e0ff; }

        /* Badges */
        .badge { font-weight: 700; letter-spacing: 0.02em; }
        .age-P { background: #10b981 !important; }
        .age-T13 { background: #f59e0b !important; }
        .age-T16 { background: #f97316 !important; }
        .age-T18 { background: #ef4444 !important; }

        /* Movie cards (shared) */
        .cine-movie-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 18px;
            overflow: hidden;
            height: 100%;
            transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
        }
        .cine-movie-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 22px 40px rgba(0,0,0,0.45);
            border-color: rgba(255,77,109,0.35);
        }
        .cine-poster {
            position: relative;
            display: block;
            aspect-ratio: 2 / 3;
            overflow: hidden;
            background: #1a1d28;
        }
        .cine-poster img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform .4s ease;
        }
        .cine-movie-card:hover .cine-poster img { transform: scale(1.06); }
        .cine-age {
            position: absolute;
            top: 12px;
            left: 12px;
            padding: 4px 9px;
            border-radius: 8px;
            font-size: 0.72rem;
            font-weight: 800;
            color: #fff;
            z-index: 2;
            box-shadow: 0 4px 12px rgba(0,0,0,0.35);
        }
        .cine-movie-title {
            font-weight: 700;
            font-size: 1rem;
            color: #fff;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            margin-bottom: 4px;
        }
        .cine-movie-meta { color: var(--muted); font-size: 0.85rem; }

        /* Time slots */
        .beta-time-slot, .cine-time-slot {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-width: 92px;
            padding: 10px 14px;
            border-radius: 12px;
            background: var(--surface-2);
            border: 1px solid var(--border);
            color: #fff;
            transition: all .2s ease;
            text-decoration: none;
        }
        .beta-time-slot:hover, .cine-time-slot:hover {
            background: var(--primary-soft);
            border-color: rgba(255,77,109,0.45);
            color: #fff;
            transform: translateY(-2px);
        }
        .beta-time-slot .slot-hour, .cine-time-slot .slot-hour {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        .beta-time-slot .slot-seats, .cine-time-slot .slot-seats {
            font-size: 0.72rem;
            color: var(--muted);
        }
        .badge-early {
            display: inline-block;
            font-size: 0.62rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            padding: 2px 6px;
            border-radius: 6px;
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            color: #1a1205;
            line-height: 1.2;
        }
        .beta-time-slot.slot-early, .cine-time-slot.slot-early {
            border-color: rgba(251, 191, 36, 0.45);
            background: rgba(251, 191, 36, 0.08);
        }

        /* Seats */
        .seat-box {
            width: 42px;
            height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            margin: 3px;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
            user-select: none;
            border: none;
            transition: transform .15s ease, box-shadow .15s ease;
        }
        .seat-box:hover { transform: scale(1.08); }
        .seat-available { background: #3a4158; color: #e8ecf8; }
        .seat-vip { background: linear-gradient(135deg, #f59e0b, #d97706); color: #1a1205; }
        .seat-couple { background: linear-gradient(135deg, #ec4899, #db2777); color: #fff; width: 90px; }
        .seat-selected { background: var(--primary) !important; color: #fff !important; box-shadow: 0 0 0 2px #ffb3c1; }
        .seat-held { background: #38bdf8; color: #0c1929; cursor: not-allowed; opacity: 0.8; }
        .seat-booked, .seat-blocked { background: #4b5563; color: #9ca3af; cursor: not-allowed; opacity: 0.7; }
        .screen-bar {
            height: 12px;
            background: linear-gradient(180deg, rgba(255,77,109,0.7) 0%, transparent 100%);
            border-top: 3px solid var(--primary);
            border-radius: 50% 50% 0 0 / 100% 100% 0 0;
            margin: 0 auto 28px;
            max-width: 70%;
        }

        /* Tabs */
        .cine-tabs {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 24px;
        }
        .cine-tab {
            border: 1px solid var(--border);
            border-radius: 999px;
            padding: 9px 18px;
            color: var(--muted);
            background: var(--surface);
            font-size: 0.9rem;
            font-weight: 600;
            transition: all .2s ease;
        }
        .cine-tab:hover, .cine-tab.active {
            color: #fff;
            background: var(--primary);
            border-color: var(--primary);
        }

        /* Section headings */
        .cine-section { padding: 56px 0; }
        .cine-kicker {
            color: var(--accent);
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            margin-bottom: 6px;
        }
        .cine-section-title {
            font-size: clamp(1.5rem, 3vw, 2rem);
            font-weight: 800;
            color: #fff;
            margin: 0;
        }

        /* Footer */
        .cine-footer {
            margin-top: auto;
            background: #07080c;
            border-top: 1px solid var(--border);
            color: var(--muted);
            padding: 48px 0 32px;
        }
        .cine-footer h5, .cine-footer h6 {
            color: #fff;
            font-weight: 700;
            margin-bottom: 16px;
        }
        .cine-footer a { color: #b0b8cc; }
        .cine-footer a:hover { color: var(--accent); }
        .cine-footer .footer-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 16px 18px;
        }

        /* Utility overrides for Bootstrap light classes used in views */
        .bg-light, .bg-white { background: var(--surface) !important; color: var(--text) !important; }
        .text-dark { color: var(--text) !important; }
        .text-secondary { color: var(--muted) !important; }
        .border { border-color: var(--border) !important; }
        .border-bottom { border-bottom-color: var(--border) !important; }
        .shadow-sm, .shadow { box-shadow: var(--shadow) !important; }
        .btn-primary {
            background: var(--primary);
            border-color: var(--primary);
            font-weight: 600;
        }
        .btn-primary:hover {
            background: #e11d48;
            border-color: #e11d48;
        }
        .btn-outline-primary {
            color: var(--primary);
            border-color: rgba(255,77,109,0.5);
        }
        .btn-outline-primary:hover {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
        }
        .btn-secondary {
            background: var(--surface-2);
            border-color: var(--border);
            color: #fff;
        }
        .breadcrumb { --bs-breadcrumb-divider-color: var(--muted); }
        .breadcrumb-item a { color: var(--accent); }
        .breadcrumb-item.active { color: var(--muted); }
        .modal-content {
            background: var(--surface);
            border: 1px solid var(--border);
            color: var(--text);
        }
        .modal-header, .modal-footer { border-color: var(--border); }
        .list-group-item {
            background: var(--surface);
            border-color: var(--border);
            color: var(--text);
        }
        .dropdown-menu {
            background: var(--surface-2);
            border: 1px solid var(--border);
        }
        .dropdown-item { color: #c9d0e0; }
        .dropdown-item:hover { background: rgba(255,255,255,0.06); color: #fff; }
        .nav-pills .nav-link { color: var(--muted); }
        .nav-pills .nav-link.active { background: var(--primary); color: #fff; }

        /* Responsive tweaks */
        @media (max-width: 991.98px) {
            .cine-navbar .navbar-collapse {
                background: var(--bg-soft);
                border: 1px solid var(--border);
                border-radius: 16px;
                padding: 12px;
                margin-top: 10px;
            }
            .cine-search { margin-top: 10px; }
        }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 10px; height: 10px; }
        ::-webkit-scrollbar-track { background: #0b0c10; }
        ::-webkit-scrollbar-thumb { background: #2a3142; border-radius: 999px; }
        ::-webkit-scrollbar-thumb:hover { background: #3a445a; }
    </style>
</head>
<body>
    <!-- Top bar -->
    <div class="cine-topbar">
        <div class="container d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex flex-wrap gap-3">
                <span><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($headerCinema['address'] ?? 'Rạp chiếu phim') ?></span>
                <span><i class="bi bi-telephone me-1"></i><?= htmlspecialchars($headerCinema['phone'] ?: '1900 636807') ?></span>
            </div>
            <div class="d-flex flex-wrap gap-3">
                <?php if (!empty($_SESSION['user'])): ?>
                    <span class="text-white-50">Xin chào, <strong class="text-white"><?= htmlspecialchars($_SESSION['user']['full_name'] ?? $_SESSION['user']['email'] ?? 'Bạn') ?></strong></span>
                    <?php if (!empty(array_intersect(['admin','staff'], $_SESSION['user']['roles'] ?? []))): ?>
                        <a href="?route=admin"><i class="bi bi-speedometer2 me-1"></i>Quản trị</a>
                    <?php endif; ?>
                    <a href="?route=wallet"><i class="bi bi-wallet2 me-1"></i>Ví của tôi</a>
                    <a href="?route=booking/my"><i class="bi bi-ticket-perforated me-1"></i>Vé của tôi</a>
                    <a href="?route=auth/logout"><i class="bi bi-box-arrow-right me-1"></i>Đăng xuất</a>
                <?php else: ?>
                    <a href="?route=auth/login"><i class="bi bi-person me-1"></i>Đăng nhập</a>
                    <a href="?route=auth/register"><i class="bi bi-person-plus me-1"></i>Đăng ký</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Main Navbar -->
    <nav class="navbar navbar-expand-lg cine-navbar">
        <div class="container">
            <a class="navbar-brand" href="?route=home">
                <span class="cine-logo">C</span>
                <span>CineNow</span>
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#cineMainNav" aria-controls="cineMainNav" aria-expanded="false" aria-label="Toggle navigation">
                <i class="bi bi-list text-white fs-3"></i>
            </button>

            <div class="collapse navbar-collapse" id="cineMainNav">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0 gap-lg-1">
                    <li class="nav-item">
                        <a class="nav-link cine-nav-link <?= $currentRoute === 'home' ? 'active' : '' ?>" href="?route=home">Phim</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link cine-nav-link <?= $currentRoute === 'schedule' ? 'active' : '' ?>" href="?route=schedule">Lịch chiếu</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link cine-nav-link <?= $currentRoute === 'prices' ? 'active' : '' ?>" href="?route=prices">Giá vé</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link cine-nav-link <?= $currentRoute === 'promotions' ? 'active' : '' ?>" href="?route=promotions">Ưu đãi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link cine-nav-link <?= $currentRoute === 'about' ? 'active' : '' ?>" href="?route=about">Rạp chiếu</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link cine-nav-link <?= in_array($currentRoute, ['wallet', 'booking/my']) ? 'active' : '' ?>" href="?route=wallet">Thành viên</a>
                    </li>
                </ul>

                <form method="GET" action="index.php" class="d-flex cine-search">
                    <input type="hidden" name="route" value="home">
                    <div class="input-group">
                        <input type="text" name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" class="form-control" placeholder="Tìm phim...">
                        <button class="btn" type="submit"><i class="bi bi-search"></i></button>
                    </div>
                </form>
            </div>
        </div>
    </nav>

    <?php if (!empty($_SESSION['flash'])): ?>
        <div class="container mt-3">
            <div class="alert alert-<?= htmlspecialchars($_SESSION['flash']['type']) ?> alert-dismissible fade show mb-0">
                <?= htmlspecialchars($_SESSION['flash']['message']) ?>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
            </div>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <main>
        <?= $content ?>
    </main>

    <footer class="cine-footer">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-5">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <span class="cine-logo" style="width:40px;height:40px;font-size:1rem;">C</span>
                        <h5 class="mb-0"><?= htmlspecialchars(strtoupper($headerCinema['name'] ?? 'CineNow')) ?></h5>
                    </div>
                    <p class="mb-2"><i class="bi bi-geo-alt-fill me-2 text-danger"></i><?= htmlspecialchars($headerCinema['address'] ?? '') ?></p>
                    <p class="mb-0">
                        <i class="bi bi-telephone-fill me-2" style="color:var(--primary)"></i><?= htmlspecialchars($headerCinema['phone'] ?: '1900 636807') ?>
                        <span class="mx-2">|</span>
                        <i class="bi bi-envelope-fill me-2" style="color:var(--primary)"></i><?= htmlspecialchars($headerCinema['email'] ?: 'cskh@cinenow.vn') ?>
                    </p>
                </div>
                <div class="col-md-4 col-lg-3">
                    <h6>Danh mục</h6>
                    <div class="d-flex flex-column gap-2">
                        <a href="?route=schedule">Lịch chiếu theo ngày</a>
                        <a href="?route=home">Phim đang chiếu</a>
                        <a href="?route=prices">Bảng giá vé</a>
                        <a href="?route=promotions">Ưu đãi & Combo</a>
                        <a href="?route=about">Giới thiệu rạp</a>
                    </div>
                </div>
                <div class="col-md-4 col-lg-4">
                    <div class="footer-card">
                        <div class="small text-uppercase mb-1" style="color:var(--muted);letter-spacing:.06em;">Đặt vé trực tuyến</div>
                        <div class="fs-5 fw-bold mb-1" style="color:var(--accent);">Giữ ghế nhanh 10 phút</div>
                        <div class="small">Thanh toán Ví nội bộ · VNPay · MoMo</div>
                    </div>
                </div>
            </div>
            <hr class="my-4" style="border-color:var(--border);opacity:1;">
            <div class="d-flex flex-wrap justify-content-between gap-2 small">
                <span>© <?= date('Y') ?> CineNow — Đồ án tốt nghiệp</span>
                <span>Thiết kế giao diện hiện đại · Dark theme</span>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
