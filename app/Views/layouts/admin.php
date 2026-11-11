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
            <li>
                <a href="?route=admin&tab=bookings" class="admin-nav-link <?= $currentTab === 'bookings' ? 'active' : '' ?>">
                    <i class="bi bi-qr-code-scan"></i> 5-6. Đơn vé & Soát vé QR
                </a>
            </li>
            <li>
                <a href="?route=admin&tab=concessions" class="admin-nav-link <?= $currentTab === 'concessions' ? 'active' : '' ?>">
                    <i class="bi bi-cup-straw"></i> 7. Bắp nước & Mã giảm giá
                </a>
            </li>
        </ul>

        <div class="admin-sidebar-footer">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="small text-truncate">
                    <div class="fw-bold text-white"><?= htmlspecialchars($adminUser['name']) ?></div>
                    <div class="text-secondary" style="font-size: 12px;"><?= htmlspecialchars($adminUser['email']) ?></div>
                </div>
                <span class="badge bg-danger text-uppercase">ADMIN</span>
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
                    <i class="bi bi-sliders text-danger me-2"></i>TRANG QUẢN TRỊ VIÊN — <?= htmlspecialchars(strtoupper($cinemaName)) ?>
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

