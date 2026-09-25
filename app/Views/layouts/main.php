<?php /** @var string $content */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title><?= e(($pageTitle ?? '') . ' | ' . ($appSettings['name'] ?? 'Skeleton App')) ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <?php if (!empty($pageUsesDatePicker)): ?>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-datepicker@1.10.0/dist/css/bootstrap-datepicker.min.css" rel="stylesheet">
    <?php endif; ?>
</head>
<body class="app-body">

<nav class="navbar navbar-expand-lg navbar-dark app-navbar shadow-sm sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= url('/home') ?>">
            <i class="bi bi-boxes"></i>
            <span><?= e($appSettings['name'] ?? 'Skeleton App') ?></span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#appNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="appNav">
            <ul class="navbar-nav ms-auto align-items-center gap-lg-2">
                <li class="nav-item dropdown">
                    <a class="nav-link position-relative" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-bell"></i>
                        <?php if (($unreadNotifications ?? 0) > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notif-badge">
                                <?= (int)$unreadNotifications ?>
                            </span>
                        <?php endif; ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end notif-dropdown">
                        <div class="dropdown-header d-flex justify-content-between align-items-center">
                            <span>Notifications</span>
                            <form method="post" action="<?= url('/notifications/read-all') ?>">
                                <?= csrf_field() ?>
                                <button class="btn btn-link btn-sm p-0 text-decoration-none">Mark all read</button>
                            </form>
                        </div>
                        <div class="dropdown-item text-muted small py-2 px-3">You have <?= (int)$unreadNotifications ?> unread notification(s).</div>
                        <a class="dropdown-item border-top" href="<?= url('/notifications') ?>">View all notifications</a>
                    </div>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                        <?php if (!empty($appUser['avatar'])): ?>
                            <span class="rounded-circle user-avatar user-avatar-fallback"><?= e(strtoupper(mb_substr((string)$appUser['name'], 0, 1))) ?></span>
                        <?php else: ?>
                            <span class="rounded-circle user-avatar user-avatar-fallback"><?= e(strtoupper(mb_substr((string)$appUser['name'], 0, 1))) ?></span>
                        <?php endif; ?>
                        <span class="d-none d-xl-inline"><?= e($appUser['name']) ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?= url('/profile') ?>"><i class="bi bi-person me-2"></i>My Profile</a></li>
                        <li><a class="dropdown-item" href="<?= url('/audit/work/' . (int)$appUser['id']) ?>"><i class="bi bi-clock-history me-2"></i>My Activity</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="post" action="<?= url('/logout') ?>">
                                <?= csrf_field() ?>
                                <button class="dropdown-item text-danger" type="submit"><i class="bi bi-box-arrow-right me-2"></i>Sign Out</button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="app-layout">
    <aside class="app-sidebar" id="appSidebar">
        <nav class="sidebar-nav">
            <ul class="nav flex-column">
                <?php foreach (($sidebarMenus ?? []) as $menu): $item = $menu['item']; ?>
                    <?php if (!empty($menu['children'])): ?>
                        <?php
                        $menuOpen = false;
                        foreach (($menu['children'] ?? []) as $child) {
                            if (isset($child['route']) && $child['route'] !== '' && str_starts_with((string)($currentPath ?? ''), (string)$child['route'])) {
                                $menuOpen = true;
                                break;
                            }
                        }
                        ?>
                        <li class="nav-item">
                            <a class="nav-link sidebar-toggle <?= $menuOpen ? 'active' : 'collapsed' ?>" data-bs-toggle="collapse" data-bs-target="#menu-<?= e($item['slug']) ?>" aria-expanded="<?= $menuOpen ? 'true' : 'false' ?>">
                                <i class="bi <?= e($item['icon'] ?? 'bi-circle') ?>"></i>
                                <span><?= e($item['title']) ?></span>
                                <i class="bi bi-chevron-down ms-auto sidebar-arrow"></i>
                            </a>
                            <div class="collapse <?= $menuOpen ? 'show' : '' ?>" id="menu-<?= e($item['slug']) ?>">
                                <ul class="nav flex-column sub-nav">
                                    <?php foreach ($menu['children'] as $child): ?>
                                        <li class="nav-item">
                                            <a class="nav-link sub-link <?= isset($child['route']) && $child['route'] !== '' && str_starts_with((string)($currentPath ?? ''), (string)$child['route']) ? 'active' : '' ?>"
                                               href="<?= e(url((string)$child['route'])) ?>">
                                                <i class="bi bi-dot"></i><?= e($child['title']) ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link <?= isset($item['route']) && $item['route'] !== '' && str_starts_with((string)($currentPath ?? ''), (string)$item['route']) ? 'active' : '' ?>"
                               href="<?= e(url((string)$item['route'])) ?>">
                                <i class="bi <?= e($item['icon'] ?? 'bi-circle') ?>"></i>
                                <span><?= e($item['title']) ?></span>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </nav>
    </aside>

    <main class="app-main">
        <div class="container-fluid py-4">
            <?php if (!empty($pageTitle)): ?>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="page-title mb-0"><?= e($pageTitle) ?></h4>
                    <div><?= $headerActions ?? '' ?></div>
                </div>
            <?php endif; ?>

            <?= render_flash_alerts() ?>

            <?= $content ?>
        </div>

        <footer class="app-footer text-center small">
            &copy; <?= date('Y') ?> <?= e($appSettings['name'] ?? 'Skeleton App') ?> — built with <b>PHP Skeleton</b>
        </footer>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset('js/app.js') ?>"></script>
<?php if (!empty($pageUsesDatePicker)): ?>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-datepicker@1.10.0/dist/js/bootstrap-datepicker.min.js"></script>
    <script>
        $('.date-range-filter').datepicker({
            format: 'd M yyyy',
            autoclose: true,
            todayHighlight: true
        });
    </script>
<?php endif; ?>
</body>
</html>