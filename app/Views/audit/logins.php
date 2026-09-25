<?php /** @var array $rows @var array $filters @var bool $failedOnly */ $failedOnly = $failedOnly ?? false; ?>
<?php \App\Core\View::share('headerActions', '<a href="' . e(url($failedOnly ? '/audit/logins' : '/audit/failed')) . '" class="btn btn-outline-secondary btn-sm"><i class="bi bi-' . ($failedOnly ? 'door-open' : 'x-circle') . ' me-1"></i>' . ($failedOnly ? 'All Logins' : 'Failed Only') . '</a>'); ?>

<div class="card border-0 shadow-sm">
    <div class="card-body pb-3">
        <form method="get" action="<?= e(url($failedOnly ? '/audit/failed' : '/audit/logins')) ?>" class="row g-2 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label small mb-1">Search</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Name or email or IP" value="<?= e($filters['search']) ?>">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All</option>
                    <?php foreach (['success' => 'Success', 'failed' => 'Failed', 'lockout' => 'Lockout', 'logout' => 'Logout'] as $value => $label): ?>
                        <option value="<?= e($value) ?>" <?= $filters['status'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1">From</label>
                <input type="text" name="from" class="form-control form-control-sm date-range-filter"
                       placeholder="e.g. 24 Sep 2026" maxlength="11" autocomplete="off" value="<?= e($filters['from']) ?>">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1">To</label>
                <input type="text" name="to" class="form-control form-control-sm date-range-filter"
                       placeholder="e.g. 24 Sep 2026" maxlength="11" autocomplete="off" value="<?= e($filters['to']) ?>">
            </div>
            <div class="col-6 col-md-3">
                <button class="btn btn-outline-primary btn-sm" type="submit"><i class="bi bi-search me-1"></i>Filter</button>
                <a class="btn btn-outline-secondary btn-sm" href="<?= e(url($failedOnly ? '/audit/failed' : '/audit/logins')) ?>">Reset</a>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover table-striped align-middle mb-0">
            <thead class="table-light">
            <tr>
                <th class="ps-3">User</th>
                <th>IP Address</th>
                <th>Device</th>
                <th>Status</th>
                <th>Reason</th>
                <th class="text-end pe-3">Time</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($rows['items'])): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">No entries found.</td></tr>
            <?php else: foreach ($rows['items'] as $row): ?>
                <tr>
                    <td class="ps-3">
                        <?php if (!empty($row['user_id']) && user_can('audit.view')): ?>
                            <a href="<?= url('/audit/work/' . (int)$row['user_id']) ?>" class="fw-semibold text-decoration-none"><?= e($row['user_name'] ?? 'Unknown') ?></a>
                        <?php else: ?>
                            <span class="fw-semibold"><?= e($row['user_name'] ?? '') ?></span>
                        <?php endif; ?>
                        <div class="text-muted small"><?= e($row['email'] ?? '') ?></div>
                    </td>
                    <td class="small"><?= e($row['ip_address'] ?? '—') ?></td>
                    <td class="small text-muted">
                        <?php if (!empty($row['device_type'])): ?><i class="bi bi-<?= e($row['device_type'] === 'mobile' ? 'phone' : 'laptop') ?> me-1"></i><?php endif; ?>
                        <?= e($row['browser'] ?? '') ?><?= !empty($row['platform']) ? ' · ' . e($row['platform']) : '' ?>
                    </td>
                    <td><?= status_badge((string)$row['status']) ?></td>
                    <td class="small text-muted"><?= e($row['reason'] ?? '—') ?></td>
                    <td class="small text-muted text-end pe-3"><?= e(format_datetime($row['created_at'] ?? null)) ?></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <?php if (!empty($rows['last_page']) && $rows['last_page'] > 1): ?>
        <div class="card-footer bg-white d-flex justify-content-between align-items-center">
            <small class="text-muted">Showing <?= count($rows['items']) ?> of <?= (int)$rows['total'] ?> records</small>
            <?php $pagination = $rows; require VIEW_PATH . '/partials/pagination.php'; unset($pagination); ?>
        </div>
    <?php endif; ?>
</div>