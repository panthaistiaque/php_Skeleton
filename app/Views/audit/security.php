<?php /** @var array $rows @var array $filters */ ?>
<div class="card border-0 shadow-sm">
    <div class="card-body pb-3">
        <form method="get" action="<?= url('/audit/security') ?>" class="row g-2 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label small mb-1">Search</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Description" value="<?= e($filters['search']) ?>">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1">Severity</label>
                <select name="severity" class="form-select form-select-sm">
                    <option value="">All</option>
                    <?php foreach (['info' => 'Info', 'warning' => 'Warning', 'critical' => 'Critical'] as $value => $label): ?>
                        <option value="<?= e($value) ?>" <?= $filters['severity'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1">From</label>
                <input type="date" name="from" class="form-control form-control-sm" value="<?= e($filters['from']) ?>">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1">To</label>
                <input type="date" name="to" class="form-control form-control-sm" value="<?= e($filters['to']) ?>">
            </div>
            <div class="col-6 col-md-3">
                <button class="btn btn-outline-primary btn-sm" type="submit"><i class="bi bi-search me-1"></i>Filter</button>
                <a class="btn btn-outline-secondary btn-sm" href="<?= url('/audit/security') ?>">Reset</a>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover table-striped align-middle mb-0">
            <thead class="table-light">
            <tr>
                <th class="ps-3">Severity</th>
                <th>Type</th>
                <th>Description</th>
                <th>IP</th>
                <th class="text-end pe-3">Time</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($rows['items'])): ?>
                <tr><td colspan="5" class="text-center text-muted py-4">No security events.</td></tr>
            <?php else: foreach ($rows['items'] as $row): ?>
                <tr>
                    <td class="ps-3"><?= status_badge((string)$row['severity']) ?></td>
                    <td><code class="small"><?= e($row['event_type']) ?></code></td>
                    <td class="small"><?= e($row['description'] ?? '') ?></td>
                    <td class="small text-muted"><?= e($row['ip_address'] ?? '') ?></td>
                    <td class="small text-muted text-end pe-3"><?= e(time_ago($row['created_at'])) ?></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <?php if (!empty($rows['last_page']) && $rows['last_page'] > 1): ?>
        <div class="card-footer bg-white d-flex justify-content-between align-items-center">
            <small class="text-muted"><?= (int)$rows['total'] ?> records</small>
            <?php require VIEW_PATH . '/partials/pagination.php'; ?>
        </div>
    <?php endif; ?>
</div>