<?php /** @var int $status @var string $message */ $status = (int)($status ?? 429); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= (int)$status ?> - Too Many Requests</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body class="error-body">
<div class="error-panel text-center">
    <div class="error-code">429</div>
    <h2>Too Many Requests</h2>
    <p class="text-muted"><?= e($message) ?></p>
    <p class="small text-muted">You have sent too many requests in a short period. Please wait a moment and try again.</p>
</div>
</body>
</html>