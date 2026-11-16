<?php
$currentTab = $tab ?? ($_GET['tab'] ?? 'overview');
$adminUser = $_SESSION['user'] ?? ['name' => 'Admin', 'email' => 'admin@cinema.vn', 'roles' => ['admin']];
$cinemaName = $cinemaInfo['name'] ?? 'CineNow';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Hệ Thống Quản Trị - ' . $cinemaName) ?></title>
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
            --admin-sidebar-bg: #0f172a;
            --admin-sidebar-hover: #1e293b;
            --admin-accent: #38bdf8;
        }
        body {
            font-family: 'Be Vietnam Pro', sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
            min-height: 100vh;
            margin: 0;
        }
        h1, h2, h3, h4, .oswald-font {
            font-family: 'Oswald', sans-serif;
        }
        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }
        /* SIDEBAR QUẢN TRỊ RIÊNG BIỆT */
        .admin-sidebar {
            width: 270px;
            background-color: var(--admin-sidebar-bg);
            color: #f8fafc;
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            box-shadow: 4px 0 15px rgba(0, 0, 0, 0.12);
        }
        .admin-brand {
            padding: 20px 22px;
            border-bottom: 1px solid rgba(148, 163, 184, 0.15);
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: #fff;
        }
        .admin-brand-badge {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            background: linear-gradient(135deg, #0284c7 0%, #f36523 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Oswald', sans-serif;
            font-size: 22px;
            font-weight: 700;
            color: #fff;
        }
        .admin-nav-list {
            list-style: none;
            padding: 16px 12px;
            margin: 0;
            flex-grow: 1;
        }
        .admin-nav-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #64748b;
            font-weight: 700;
            padding: 8px 12px 6px;
        }
        .admin-nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 14px;
            border-radius: 10px;
            color: #cbd5e1;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 4px;
            transition: all 0.2s;
        }
        .admin-nav-link i {
            font-size: 17px;
            width: 22px;
            text-align: center;
        }
        .admin-nav-link:hover {
            background-color: var(--admin-sidebar-hover);
            color: #ffffff;
        }
        .admin-nav-link.active {
            background: linear-gradient(135deg, #03599d 0%, #0284c7 100%);
            color: #ffffff;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.35);
        }
        .admin-sidebar-footer {
            padding: 16px;
            border-top: 1px solid rgba(148, 163, 184, 0.15);
            background: rgba(2, 6, 23, 0.4);
        }
        /* KHU VỰC NỘI DUNG CHÍNH */
        .admin-main {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }
        .admin-topbar {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 14px 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .admin-content {
            padding: 24px 28px;
            flex-grow: 1;
        }
        .btn-beta-buy {
            background-image: var(--beta-gradient);
            background-size: 200% auto;
            color: #ffffff !important;
            font-family: 'Oswald', sans-serif;
            font-size: 15px;
            font-weight: 600;
            text-transform: uppercase;
            border: none;
            border-radius: 6px;
            padding: 8px 16px;
            transition: 0.3s;
        }
        .btn-beta-buy:hover {
            background-position: right center;
        }
        @media (max-width: 991.98px) {
            .admin-wrapper {
                flex-direction: column;
            }
            .admin-sidebar {
                width: 100%;
            }
        }

        /* CineNow Premium Admin */
        :root {
            --beta-blue:#c6a15b; --beta-cyan:#e2c98f; --beta-orange:#e2b96f;
            --beta-gradient:linear-gradient(135deg,#d9bd7b 0%,#b68b43 55%,#8e6a32 100%);
            --admin-sidebar-bg:#191815; --admin-sidebar-hover:#2b2923; --admin-accent:#d7b56d;
        }
        body { background:#f5f3ee; color:#28251e; }
        .admin-sidebar { background:linear-gradient(180deg,#1c1b17 0%,#121210 100%); border-right:1px solid rgba(215,181,109,.18); box-shadow:8px 0 28px rgba(25,22,16,.08); }
        .admin-brand { border-bottom-color:rgba(215,181,109,.2); padding:23px 22px; }
        .admin-brand-badge { background:linear-gradient(145deg,#e4ca8c,#9d7638); color:#171511; border-radius:13px; box-shadow:0 7px 22px rgba(215,181,109,.2); }
        .admin-nav-label { color:#a99978; letter-spacing:1.35px; }
        .admin-nav-link { border:1px solid transparent; border-radius:11px; padding:12px 14px; color:#d2cbbd; }
        .admin-nav-link:hover { background:rgba(215,181,109,.1); color:#f5e7c6; border-color:rgba(215,181,109,.12); }
        .admin-nav-link.active { background:linear-gradient(135deg,rgba(215,181,109,.24),rgba(142,106,50,.22)); color:#f2d99f; border-color:rgba(215,181,109,.3); box-shadow:inset 3px 0 #d7b56d,0 8px 20px rgba(0,0,0,.12); }
        .admin-sidebar-footer { border-top-color:rgba(215,181,109,.2); background:rgba(0,0,0,.2); }
        .admin-topbar { background:rgba(255,254,250,.94); border-bottom-color:#e8e1d3; box-shadow:0 5px 22px rgba(45,37,23,.04); padding:16px 30px; }
        .admin-content { padding:30px; }
        .admin-content .card { border:1px solid #ebe4d7 !important; border-radius:17px !important; box-shadow:0 10px 30px rgba(41,33,20,.055) !important; overflow:hidden; }
        .admin-content .card-header { background:#fbfaf6 !important; border-bottom:1px solid #eee7da; }
        .admin-content .table thead th { background:#f1ede4; color:#695633; border-bottom-color:#e5dccb; font-size:.76rem; text-transform:uppercase; letter-spacing:.045em; }
        .admin-content .table td { border-color:#eee9df; }
        .admin-content .form-control,.admin-content .form-select { border-radius:9px; border-color:#e3dccf; min-height:40px; }
        .admin-content .form-control:focus,.admin-content .form-select:focus { border-color:#c6a15b; box-shadow:0 0 0 3px rgba(198,161,91,.14); }
        .btn-beta-buy { background:var(--beta-gradient); color:#211b10 !important; border:1px solid #c4a15e; border-radius:9px; font-family:'Be Vietnam Pro',sans-serif; font-weight:800; box-shadow:0 6px 16px rgba(174,132,58,.17); }
        .btn-beta-buy:hover { filter:brightness(1.07); transform:translateY(-1px); }
        .admin-topbar .btn-outline-secondary { border-color:#ded5c5; color:#6d6048; }
        .admin-topbar .btn-outline-secondary:hover { background:#292720; color:#f2d99f; border-color:#292720; }
        @media(max-width:991.98px) { .admin-content{padding:20px 15px}.admin-topbar{padding:13px 16px} }
    </style>
</head>
<body>

<div class="admin-wrapper">
    <!-- SIDEBAR QUẢN TRỊ VIÊN -->
    <aside class="admin-sidebar">
        <a href="?route=admin" class="admin-brand">
            <span class="admin-brand-badge"><i class="bi bi-shield-check"></i></span>
            <div>
                <div class="oswald-font fw-bold fs-5 lh-1 text-uppercase"><?= htmlspecialchars($cinemaName) ?> ADMIN</div>
                <div class="text-info" style="font-size: 11px;">Hệ Thống Quản Trị Riêng</div>
            </div>
        </a>

        <ul class="admin-nav-list">
            <?php if (!empty($isAdmin)): ?>
            <li class="admin-nav-label">Bảng điều khiển</li>
            <li>
                <a href="?route=admin&tab=overview" class="admin-nav-link <?= $currentTab === 'overview' ? 'active' : '' ?>">
                    <i class="bi bi-speedometer2"></i> Tổng quan hệ thống
                </a>
            </li>

            <li class="admin-nav-label mt-2">Quản lý 7 Nhánh Nghiệp vụ</li>
            <li>
                <a href="?route=admin&tab=users" class="admin-nav-link <?= $currentTab === 'users' ? 'active' : '' ?>">
                    <i class="bi bi-people-fill"></i> 1. Rạp, Tài khoản & Admin
                </a>
            </li>
            <li>
                <a href="?route=admin&tab=movies" class="admin-nav-link <?= $currentTab === 'movies' ? 'active' : '' ?>">
                    <i class="bi bi-film"></i> 2. Phim & Thể loại
                </a>
            </li>
            <li>
                <a href="?route=admin&tab=rooms" class="admin-nav-link <?= $currentTab === 'rooms' ? 'active' : '' ?>">
                    <i class="bi bi-grid-3x3-gap-fill"></i> 3. Phòng chiếu & Sơ đồ ghế
                </a>
            </li>
            <li>
                <a href="?route=admin&tab=shows" class="admin-nav-link <?= $currentTab === 'shows' ? 'active' : '' ?>">
                    <i class="bi bi-calendar2-week-fill"></i> 4. Lịch & Suất chiếu
                </a>
            </li>
            <?php endif; ?>
            <?php if (!empty($isStaff) && empty($isAdmin)): ?>
                <li class="admin-nav-label">Nghiệp vụ nhân viên</li>
                <li><a href="?route=schedule" class="admin-nav-link"><i class="bi bi-calendar3"></i> Xem lịch chiếu</a></li>
            <?php endif; ?>
            <li>
                <a href="?route=admin&tab=bookings" class="admin-nav-link <?= $currentTab === 'bookings' ? 'active' : '' ?>">
                    <i class="bi bi-qr-code-scan"></i> 5-6. Đơn vé & Soát vé QR
                </a>
            </li>
            <?php if (!empty($isAdmin)): ?>
            <li>
                <a href="?route=admin&tab=concessions" class="admin-nav-link <?= $currentTab === 'concessions' ? 'active' : '' ?>">
                    <i class="bi bi-cup-straw"></i> 7. Bắp nước & Mã giảm giá
                </a>
            </li>
            <?php endif; ?>
        </ul>

        <div class="admin-sidebar-footer">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="small text-truncate">
                    <div class="fw-bold text-white"><?= htmlspecialchars($adminUser['name']) ?></div>
                    <div class="text-secondary" style="font-size: 12px;"><?= htmlspecialchars($adminUser['email']) ?></div>
                </div>
                <span class="badge <?= !empty($isStaff) && empty($isAdmin) ? 'bg-info text-dark' : 'bg-danger' ?> text-uppercase"><?= !empty($isStaff) && empty($isAdmin) ? 'STAFF' : 'ADMIN' ?></span>
            </div>
            <div class="d-grid gap-2 mt-2">
                <a href="?route=home" target="_blank" class="btn btn-outline-info btn-sm">
                    <i class="bi bi-box-arrow-up-right me-1"></i>Xem Trang Khách Hàng
                </a>
                <a href="?route=admin/logout" class="btn btn-danger btn-sm">
                    <i class="bi bi-power me-1"></i>Đăng Xuất Quản Trị
                </a>
            </div>
        </div>
    </aside>

    <!-- KHU VỰC NỘI DUNG QUẢN TRỊ CHÍNH -->
    <div class="admin-main">
        <header class="admin-topbar">
            <div>
                <h5 class="oswald-font fw-bold text-uppercase mb-0" style="color: var(--beta-blue);">
                    <i class="bi bi-sliders text-danger me-2"></i>KHU VỰC QUẢN TRỊ — <?= htmlspecialchars(strtoupper($cinemaName)) ?>
                </h5>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2">
                    <i class="bi bi-person-check-fill me-1"></i>Đang đăng nhập: <strong><?= htmlspecialchars($adminUser['name']) ?></strong>
                </span>
                <a href="?route=admin/logout" class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-box-arrow-right me-1"></i>Thoát Admin
                </a>
            </div>
        </header>

        <div class="admin-content">
            <?php if (!empty($_SESSION['flash'])): ?>
                <div class="alert alert-<?= htmlspecialchars($_SESSION['flash']['type']) ?> alert-dismissible fade show shadow-sm mb-4">
                    <?= htmlspecialchars($_SESSION['flash']['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['flash']); ?>
            <?php endif; ?>

            <?= $content ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

