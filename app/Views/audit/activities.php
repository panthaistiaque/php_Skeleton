<?php /** @var array $rows @var array $filters */ ?>
<?php \App\Core\View::share('headerActions', '<a href="' . e(url('/reports/export/activities')) . '" class="btn btn-outline-secondary btn-sm"><i class="bi bi-download me-1"></i>Export CSV</a>'); ?>

<div class="card border-0 shadow-sm">
    <div class="card-body pb-3">
        <form method="get" action="<?= url('/audit/activities') ?>" class="row g-2 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label small mb-1">Search</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Description" value="<?= e($filters['search']) ?>">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1">Action</label>
                <input type="text" name="action" class="form-control form-control-sm" placeholder="e.g. user_created" value="<?= e($filters['action']) ?>">
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
                <a class="btn btn-outline-secondary btn-sm" href="<?= url('/audit/activities') ?>">Reset</a>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover table-striped align-middle mb-0">
            <thead class="table-light">
            <tr>
                <th class="ps-3">User</th>
                <th>Action</th>
                <th>Description</th>
                <th>Module</th>
                <th>IP</th>
                <th class="text-end pe-3">Time</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($rows['items'])): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">No activity recorded.</td></tr>
            <?php else: foreach ($rows['items'] as $row): ?>
                <tr>
                    <td class="ps-3">
                        <?php if (!empty($row['user_id'])): ?>
                            <a href="<?= url('/audit/work/' . (int)$row['user_id']) ?>" class="text-decoration-none"><?= e($row['user_name'] ?? '') ?></a>
                        <?php else: ?>
                            <span class="text-muted">System</span>
                        <?php endif; ?>
                    </td>
                    <td><code class="small"><?= e($row['action']) ?></code></td>
                    <td class="small"><?= e($row['description'] ?? '') ?></td>
                    <td><?= $row['module'] ? '<span class="badge bg-light text-dark border text-capitalize">' . e($row['module']) . '</span>' : '—' ?></td>
                    <td class="small text-muted"><?= e($row['ip_address'] ?? '') ?></td>
                    <td class="small text-muted text-end pe-3"><?= e(format_datetime($row['created_at'])) ?></td>
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