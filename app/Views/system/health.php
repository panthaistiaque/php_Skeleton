<?php /** @var array $checks @var bool $allOk */ ?>
<div class="<?= $allOk ? 'alert alert-success' : 'alert alert-danger' ?> d-flex align-items-center">
    <i class="bi <?= $allOk ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' ?> me-2 fs-4"></i>
    <div>
        <strong><?= $allOk ? 'All systems operational.' : 'Some checks require attention.' ?></strong>
    </div>
</div>

<div class="row g-3">
    <?php foreach ($checks as $check): ?>
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-start gap-3">
                    <span class="check-icon <?= $check['ok'] ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' ?>">
                        <i class="bi <?= $check['ok'] ? 'bi-check-lg' : 'bi-x-lg' ?>"></i>
                    </span>
                    <div class="flex-grow-1">
                        <h6 class="mb-0"><?= e($check['label']) ?></h6>
                        <div class="small text-muted text-break"><?= e($check['detail']) ?></div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>