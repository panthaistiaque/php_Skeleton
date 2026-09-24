<?php /** @var string $content */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title><?= e(($pageTitle ?? 'Authentication') . ' | Skeleton App') ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body class="auth-body">

<div class="auth-wrapper">
    <div class="auth-card card shadow-lg border-0">
        <div class="card-body p-5">
            <div class="text-center mb-4">
                <i class="bi bi-boxes auth-logo"></i>
                <h3 class="mb-0 mt-2"><?= e(setting('app.name', 'Skeleton App')) ?></h3>
                <p class="text-muted small mb-0"><?= e($pageTitle ?? '') ?></p>
            </div>

            <?= render_flash_alerts() ?>

            <?= $content ?>
        </div>
        <div class="card-footer text-center bg-light py-3 small text-muted">
            &copy; <?= date('Y') ?> <?= e(setting('app.name', 'Skeleton App')) ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>