<?php /** @var array $stats @var array $trend @var array $recentActivities @var array $recentLogins */ ?>
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1">Total Users</h6>
                        <h3 class="mb-0"><?= (int)$stats['total_users'] ?></h3>
                    </div>
                    <span class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-people"></i></span>
                </div>
                <p class="text-muted small mb-0 mt-2"><?= (int)$stats['new_users_month'] ?> new this month</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1">Active Users</h6>
                        <h3 class="mb-0"><?= (int)$stats['active_users'] ?></h3>
                    </div>
                    <span class="stat-icon bg-success-subtle text-success"><i class="bi bi-person-check"></i></span>
                </div>
                <p class="text-muted small mb-0 mt-2"><?= (int)$stats['pending_users'] ?> pending verification</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1">Logins Today</h6>
                        <h3 class="mb-0"><?= (int)$stats['logins_today'] ?></h3>
                    </div>
                    <span class="stat-icon bg-info-subtle text-info"><i class="bi bi-door-open"></i></span>
                </div>
                <p class="text-muted small mb-0 mt-2">successful sign-ins</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1">Active Roles</h6>
                        <h3 class="mb-0"><?= (int)$stats['active_roles'] ?></h3>
                    </div>
                    <span class="stat-icon bg-warning-subtle text-warning"><i class="bi bi-shield-check"></i></span>
                </div>
                <p class="text-muted small mb-0 mt-2">role based access</p>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-graph-up me-2"></i>Login &amp; Registration Trend (14 days)</h6>
            </div>
            <div class="card-body">
                <canvas id="trendChart" height="120"></canvas>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-person-lines-fill me-2"></i>Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <?php if (user_can('users.create')): ?>
                        <div class="col-6"><a class="btn btn-outline-primary w-100" href="<?= url('/users/create') ?>"><i class="bi bi-person-plus me-1"></i>New User</a></div>
                    <?php endif; ?>
                    <?php if (user_can('roles.create')): ?>
                        <div class="col-6"><a class="btn btn-outline-primary w-100" href="<?= url('/roles/create') ?>"><i class="bi bi-shield-plus me-1"></i>New Role</a></div>
                    <?php endif; ?>
                    <?php if (user_can('reports.view')): ?>
                        <div class="col-6"><a class="btn btn-outline-primary w-100" href="<?= url('/reports') ?>"><i class="bi bi-file-earmark-bar-graph me-1"></i>Reports</a></div>
                    <?php endif; ?>
                    <?php if (user_can('files.upload')): ?>
                        <div class="col-6"><a class="btn btn-outline-primary w-100" href="<?= url('/files') ?>"><i class="bi bi-upload me-1"></i>Upload File</a></div>
                    <?php endif; ?>
                    <?php if (user_can('settings.view')): ?>
                        <div class="col-6"><a class="btn btn-outline-secondary w-100 mt-0" href="<?= url('/settings') ?>"><i class="bi bi-gear me-1"></i>Settings</a></div>
                    <?php endif; ?>
                    <?php if (user_can('system.manage')): ?>
                        <div class="col-6"><a class="btn btn-outline-secondary w-100 mt-0" href="<?= url('/system/health') ?>"><i class="bi bi-heart-pulse me-1"></i>Health</a></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-door-open me-2"></i>Latest Logins</h6>
                <?php if (user_can('audit.view')): ?>
                    <a href="<?= url('/audit/logins') ?>" class="small text-decoration-none">View all</a>
                <?php endif; ?>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <tbody>
                    <?php if (empty($recentLogins)): ?>
                        <tr><td class="text-muted small p-3">No logins recorded yet.</td></tr>
                    <?php else: foreach ($recentLogins as $login): ?>
                        <tr>
                            <td class="ps-3">
                                <span class="fw-semibold small"><?= e($login['user_name'] ?? $login['email'] ?? 'Unknown') ?></span>
                                <div class="text-muted small"><?= e($login['ip_address'] ?? '') ?></div>
                            </td>
                            <td><?= status_badge((string)$login['status']) ?></td>
                            <td class="text-end pe-3 text-muted small"><?= e(time_ago($login['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if (!empty($recentLogins)): ?>
                <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                    <small class="text-muted">Showing <?= count($recentLogins) ?> of <?= (int)$loginsPagination['total'] ?> logins<span class="d-none d-sm-inline"> &middot; 5 per page</span></small>
                    <?php $pagination = $loginsPagination; $paginationKey = null; require VIEW_PATH . '/partials/pagination.php'; unset($pagination, $paginationKey); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-receipt me-2"></i>Recent Activities</h6>
                <?php if (user_can('audit.view')): ?>
                    <a href="<?= url('/audit/activities') ?>" class="small text-decoration-none">View all</a>
                <?php endif; ?>
            </div>
            <div class="list-group list-group-flush">
                <?php if (empty($recentActivities)): ?>
                    <div class="list-group-item text-muted small">No activity recorded yet.</div>
                <?php else: foreach ($recentActivities as $activity): ?>
                    <div class="list-group-item py-2">
                        <div class="d-flex justify-content-between">
                            <span class="small text-truncate fw-semibold"><?= e($activity['description'] ?? $activity['action']) ?></span>
                        </div>
                        <div class="small text-muted">
                            <?= e($activity['user_name'] ?? 'System') ?> &middot; <?= e(format_datetime($activity['created_at'])) ?>
                        </div>
                    </div>
                <?php endforeach; endif; ?>
            </div>
            <?php if (!empty($recentActivities)): ?>
                <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                    <small class="text-muted">Showing <?= count($recentActivities) ?> of <?= (int)$activitiesPagination['total'] ?> activities<span class="d-none d-sm-inline"> &middot; 5 per page</span></small>
                    <?php $pagination = $activitiesPagination; $paginationKey = 'activities_page'; require VIEW_PATH . '/partials/pagination.php'; unset($pagination, $paginationKey); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
    (function () {
        const labels = <?= json_encode($trend['labels']) ?>;
        new Chart(document.getElementById('trendChart'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    { label: 'Logins', data: <?= json_encode($trend['logins']) ?>, borderColor: '#0d6efd', backgroundColor: 'rgba(13,110,253,.10)', fill: true, tension: .35 },
                    { label: 'Registrations', data: <?= json_encode($trend['registrations']) ?>, borderColor: '#198754', backgroundColor: 'rgba(25,135,84,.10)', fill: true, tension: .35 }
                ]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });
    })();
</script>