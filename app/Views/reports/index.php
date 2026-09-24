<div class="row g-3">
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6><i class="bi bi-people text-primary me-2"></i>User Report</h6>
                <p class="text-muted small">All users with roles, status and last-login information. Supports status/date filters.</p>
                <form method="get" action="<?= url('/reports/export/users') ?>" class="row g-2">
                    <?php
                    $statusOptions = ['active', 'inactive', 'pending', 'suspended', 'deleted'];
                    ?>
                    <div class="col-6">
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All statuses</option>
                            <?php foreach ($statusOptions as $s): ?>
                                <option value="<?= e($s) ?>"><?= e(ucfirst($s)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6">
                        <input type="date" name="from" class="form-control form-control-sm" title="Created from">
                    </div>
                    <div class="col-6">
                        <input type="date" name="to" class="form-control form-control-sm" title="Created to">
                    </div>
                    <div class="col-6">
                        <button class="btn btn-sm btn-primary w-100" type="submit"><i class="bi bi-download me-1"></i>Export CSV</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6><i class="bi bi-door-open text-success me-2"></i>Login Report</h6>
                <p class="text-muted small">Successful and failed sign-in attempts with device, browser and IP details.</p>
                <form method="get" action="<?= url('/reports/export/logins') ?>" class="row g-2">
                    <div class="col-6">
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All statuses</option>
                            <?php foreach (['success', 'failed', 'lockout', 'logout'] as $s): ?>
                                <option value="<?= e($s) ?>"><?= e(ucfirst($s)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6">
                        <input type="date" name="from" class="form-control form-control-sm" title="From">
                    </div>
                    <div class="col-6">
                        <input type="date" name="to" class="form-control form-control-sm" title="To">
                    </div>
                    <div class="col-6">
                        <button class="btn btn-sm btn-success w-100" type="submit"><i class="bi bi-download me-1"></i>Export CSV</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6><i class="bi bi-clipboard-check text-warning me-2"></i>Activity Report</h6>
                <p class="text-muted small">User activity events with action, module, description and IP address details.</p>
                <form method="get" action="<?= url('/reports/export/activities') ?>" class="row g-2">
                    <div class="col-6">
                        <input type="text" name="action" class="form-control form-control-sm" placeholder="Action (optional)">
                    </div>
                    <div class="col-6">
                        <input type="date" name="from" class="form-control form-control-sm" title="From">
                    </div>
                    <div class="col-6">
                        <input type="date" name="to" class="form-control form-control-sm" title="To">
                    </div>
                    <div class="col-6">
                        <button class="btn btn-sm btn-warning text-dark w-100" type="submit"><i class="bi bi-download me-1"></i>Export CSV</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>