<?php /** @var int $status @var string $message */ $status = (int)($status ?? 500); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= (int)$status ?> - Server Error</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body class="error-body">
<div class="error-panel text-center">
    <div class="error-code">500</div>
    <h2>Something Went Wrong</h2>
    <p class="text-muted"><?= e($message) ?></p>
    <p class="small text-muted">The details have been recorded. If this keeps happening, contact your administrator.</p>
    <div class="mt-4">
        <a href="<?= e(url('/home')) ?>" class="btn btn-primary"><i class="bi bi-house me-1"></i>Go to Home</a>
    </div>
</div>
</body>
</html>