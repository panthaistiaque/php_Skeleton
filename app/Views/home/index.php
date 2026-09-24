<?php /** @var array $appUser @var array $homeUser @var array $homeRoles */ ?>
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex flex-wrap align-items-center gap-3">
                    <div class="flex-shrink-0">
                        <span class="rounded-circle user-avatar user-avatar-fallback" style="width:56px;height:56px;font-size:1.4rem;">
                            <?= e(strtoupper(mb_substr((string)$appUser['name'], 0, 1))) ?>
                        </span>
                    </div>
                    <div class="flex-grow-1">
                        <h4 class="mb-1">Welcome back, <?= e($appUser['name']) ?>!</h4>
                        <div class="text-muted small">
                            Today is <?= e(format_date(date('Y-m-d H:i:s'))) ?>.
                            <?php if (!empty($appUser['last_login_at'])): ?>
                                Last login: <?= e(format_datetime($appUser['last_login_at'])) ?>.
                            <?php endif; ?>
                            <span class="badge text-bg-success ms-1">Account Active</span>
                        </div>
                        <div class="mt-2">
                            <span class="text-muted small">Your roles:</span>
                            <?php if (empty($homeRoles)): ?>
                                <span class="badge text-bg-secondary">No roles assigned</span>
                            <?php else: foreach ($homeRoles as $role): ?>
                                <a href="<?= e(url('/profile')) ?>" class="badge text-bg-light border border-secondary-subtle text-dark text-decoration-none me-1">
                                    <i class="bi bi-shield me-1"></i><?= e($role['name']) ?>
                                </a>
                            <?php endforeach; endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <?php if (user_can('dashboard.view')): ?>
        <div class="col-12 col-sm-6 col-lg-3">
            <a class="card card-link border-0 shadow-sm h-100 text-decoration-none p-0" href="<?= url('/dashboard') ?>">
                <div class="card-body text-center">
                    <span class="stat-icon bg-primary-subtle text-primary mb-3"><i class="bi bi-grid"></i></span>
                    <h6 class="mb-1">Dashboard</h6>
                    <p class="small text-muted mb-0">Stats &amp; analytics</p>
                </div>
            </a>
        </div>
    <?php endif; ?>
    <?php if (user_can('users.view')): ?>
        <div class="col-12 col-sm-6 col-lg-3">
            <a class="card card-link border-0 shadow-sm h-100 text-decoration-none p-0" href="<?= url('/users') ?>">
                <div class="card-body text-center">
                    <span class="stat-icon bg-success-subtle text-success mb-3"><i class="bi bi-people"></i></span>
                    <h6 class="mb-1">Users</h6>
                    <p class="small text-muted mb-0">Manage accounts</p>
                </div>
            </a>
        </div>
    <?php endif; ?>
    <?php if (user_can('roles.view')): ?>
        <div class="col-12 col-sm-6 col-lg-3">
            <a class="card card-link border-0 shadow-sm h-100 text-decoration-none p-0" href="<?= url('/roles') ?>">
                <div class="card-body text-center">
                    <span class="stat-icon bg-warning-subtle text-warning mb-3"><i class="bi bi-shield-check"></i></span>
                    <h6 class="mb-1">Roles</h6>
                    <p class="small text-muted mb-0">Roles &amp; permissions</p>
                </div>
            </a>
        </div>
    <?php endif; ?>
    <div class="col-12 col-sm-6 col-lg-3">
        <a class="card card-link border-0 shadow-sm h-100 text-decoration-none p-0" href="<?= url('/profile') ?>">
            <div class="card-body text-center">
                <span class="stat-icon bg-info-subtle text-info mb-3"><i class="bi bi-person"></i></span>
                <h6 class="mb-1">My Profile</h6>
                <p class="small text-muted mb-0">Account &amp; security</p>
            </div>
        </a>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-person-vcard me-2"></i>Account Details</h6>
            </div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Full name</span>
                    <span class="fw-semibold"><?= e($appUser['name']) ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Email</span>
                    <span class="fw-semibold"><?= e($appUser['email']) ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Member since</span>
                    <span class="fw-semibold"><?= e(format_date($appUser['created_at'] ?? date('Y-m-d H:i:s'))) ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Last login</span>
                    <span class="fw-semibold"><?= $appUser['last_login_at'] ? e(format_datetime($appUser['last_login_at'])) : '—' ?></span>
                </li>
            </ul>
        </div>
    </div>
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-lightning-charge me-2"></i>Quick Actions</h6>
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
                    <div class="col-6"><a class="btn btn-outline-secondary w-100" href="<?= url('/profile') ?>"><i class="bi bi-gear me-1"></i>Profile Settings</a></div>
                    <div class="col-6"><a class="btn btn-outline-secondary w-100" href="<?= url('/notifications') ?>"><i class="bi bi-bell me-1"></i>Notifications</a></div>
                </div>
            </div>
        </div>
    </div>
</div>