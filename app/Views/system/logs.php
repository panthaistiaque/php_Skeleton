<?php /** @var array $logs @var string $logPath */ ?>
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="bi bi-journal-text me-2"></i>Application Logs <span class="text-muted small">(last <?= count($logs) ?> lines)</span></h6>
        <small class="text-muted"><?= e($logPath) ?></small>
    </div>
    <div class="card-body p-0">
        <pre class="log-view mb-0 p-3"><?php foreach ($logs as $line): ?><?= e($line) ?>
<?php endforeach; ?></pre>
    </div>
</div>