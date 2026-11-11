<!DOCTYPE html>
<html lang="vi" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Cinema Booking') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background-color: #0f172a; color: #f8fafc; min-height: 100vh; display: flex; flex-direction: column; }
        .navbar { background-color: #1e293b !important; border-bottom: 1px solid #334155; }
        .card { background-color: #1e293b; border: 1px solid #334155; }
        .movie-poster { height: 340px; object-fit: cover; border-top-left-radius: .375rem; border-top-right-radius: .375rem; }
        .seat-box { width: 46px; height: 42px; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; margin: 4px; font-size: 0.8rem; font-weight: 600; cursor: pointer; user-select: none; border: 1px solid rgba(255,255,255,0.2); }
        .seat-available { background-color: #334155; color: #fff; }
        .seat-vip { background-color: #854d0e; color: #fef08a; border-color: #eab308; }
        .seat-selected { background-color: #e11d48 !important; color: #fff !important; border-color: #fb7185 !important; }
        .seat-held { background-color: #f97316; color: #fff; cursor: not-allowed; opacity: 0.75; }
        .seat-booked, .seat-blocked { background-color: #475569; color: #94a3b8; cursor: not-allowed; opacity: 0.45; text-decoration: line-through; }
        .screen-bar { height: 10px; background: linear-gradient(90deg, transparent, #38bdf8, transparent); border-radius: 50%; margin-bottom: 28px; box-shadow: 0 8px 20px rgba(56, 189, 248, 0.4); }
        footer { margin-top: auto; background-color: #1e293b; border-top: 1px solid #334155; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold text-danger" href="?route=home">
            <i class="bi bi-film me-1"></i> CINEMA COMPLETE
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="?route=home">Lịch Chiếu & Phim</a></li>
                <li class="nav-item"><a class="nav-link" href="?route=home&status=now_showing">Phim Đang Chiếu</a></li>
                <li class="nav-item"><a class="nav-link" href="?route=home&status=coming_soon">Phim Sắp Chiếu</a></li>
                <?php if (!empty($_SESSION['user'])): ?>
                    <li class="nav-item"><a class="nav-link" href="?route=booking/my"><i class="bi bi-ticket-perforated"></i> Vé Của Tôi</a></li>
                    <li class="nav-item"><a class="nav-link" href="?route=wallet"><i class="bi bi-wallet2"></i> Ví Nội Bộ</a></li>
                    <li class="nav-item"><a class="nav-link" href="?route=wishlist"><i class="bi bi-heart"></i> Yêu Thích</a></li>
                    <li class="nav-item"><a class="nav-link" href="?route=notifications"><i class="bi bi-bell"></i> Thông Báo</a></li>
                <?php endif; ?>
                <?php if (!empty($_SESSION['user']['roles']) && array_intersect(['admin', 'staff'], $_SESSION['user']['roles'])): ?>
                    <li class="nav-item">
                        <a class="nav-link text-warning fw-semibold" href="?route=admin">
                            <i class="bi bi-speedometer2"></i> Quản Trị (7 Nhánh DB)
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
            <div class="d-flex align-items-center gap-2">
                <?php if (!empty($_SESSION['user'])): ?>
                    <span class="text-light small me-2">
                        <i class="bi bi-person-circle text-info"></i>
                        <?= htmlspecialchars($_SESSION['user']['name']) ?>
                        <span class="badge bg-secondary"><?= htmlspecialchars(implode(', ', $_SESSION['user']['roles'] ?? ['customer'])) ?></span>
                    </span>
                    <a href="?route=auth/logout" class="btn btn-outline-light btn-sm">Đăng xuất</a>
                <?php else: ?>
                    <a href="?route=auth/login" class="btn btn-outline-light btn-sm">Đăng nhập</a>
                    <a href="?route=auth/register" class="btn btn-danger btn-sm">Đăng ký</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<main class="container py-4">
    <?php if (!empty($_SESSION['flash'])): ?>
        <div class="alert alert-<?= htmlspecialchars($_SESSION['flash']['type']) ?> alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['flash']['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <?= $content ?>
</main>

<footer class="py-3 text-center text-secondary small">
    <div class="container">
        Hệ thống Đặt Vé Xem Phim - Base Code PHP MVC chuẩn 7 Nhánh Database (<code>cinema_complete.sql</code>)
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

