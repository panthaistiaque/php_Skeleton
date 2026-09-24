<?php /** @var int $status @var string $message */ $status = (int)($status ?? 403); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= (int)$status ?> - Forbidden</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body class="error-body">
<div class="error-panel text-center">
    <div class="error-code">403</div>
    <h2>Access Denied</h2>
    <p class="text-muted"><?= e($message) ?></p>
    <div class="mt-4">
        <a href="javascript:history.back()" class="btn btn-outline-secondary me-2"><i class="bi bi-arrow-left me-1"></i>Go Back</a>
        <a href="<?= e(url('/home')) ?>" class="btn btn-primary me-2"><i class="bi bi-house me-1"></i>Go to Home</a>
        <form method="post" action="<?= e(url('/logout')) ?>" class="d-inline">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-outline-danger"><i class="bi bi-box-arrow-right me-1"></i>Logout</button>
        </form>
    </div>
    <p class="small text-muted mt-3 mb-0">Ask an administrator to grant you access to the requested area.</p>
</div>
</body>
</html>