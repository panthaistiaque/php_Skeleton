<?php /** @var array $user @var array $userRoles @var array $allRoles @var array $activities @var array $logins @var array $departments @var array $designations */ ?>
<div class="row g-3">
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body text-center">
                <div class="avatar-xl rounded-circle mx-auto d-flex align-items-center justify-content-center bg-primary-subtle text-primary mb-3">
                    <?= e(strtoupper(mb_substr((string)$user['name'], 0, 1))) ?>
                </div>
                <h5 class="mb-0"><?= e($user['name']) ?></h5>
                <div class="text-muted small"><?= e($user['email']) ?></div>
                <div class="mt-2"><?= status_badge((string)$user['status']) ?></div>

                <div class="mt-4 d-flex gap-2 justify-content-center flex-wrap">
                    <?php if (user_can('users.edit')): ?>
                        <a href="<?= url('/users/' . (int)$user['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil me-1"></i>Edit</a>
                    <?php endif; ?>
                    <?php if (user_can('audit.view')): ?>
                        <a href="<?= url('/audit/work/' . (int)$user['id']) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-clock-history me-1"></i>Work Timeline</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><h6 class="mb-0">Details</h6></div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Department</span>
                    <span><?= e($user['department_name'] ?? '—') ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Designation</span>
                    <span><?= e($user['designation_name'] ?? '—') ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Joined</span>
                    <span><?= e(format_date($user['created_at'])) ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Last Login</span>
                    <span><?= $user['last_login_at'] ? e(time_ago($user['last_login_at'])) : '—' ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Email Verified</span>
                    <span><?= $user['email_verified_at'] ? '<i class="bi bi-check-circle text-success"></i>' : '<i class="bi bi-x-circle text-danger"></i>' ?></span>
                </li>
            </ul>
        </div>
    </div>

    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Roles &amp; Permissions</h6>
                <?php if (user_can('users.roles')): ?>
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#roleForm"><i class="bi bi-shield-plus me-1"></i>Manage Roles</button>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (empty($userRoles)): ?>
                    <div class="text-muted small">No roles assigned.</div>
                <?php else: ?>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <?php foreach ($userRoles as $userRole): ?>
                            <span class="badge bg-primary-subtle text-primary border px-3 py-2 fs-6"><?= e($userRole['name']) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <ul class="list-unstyled d-flex flex-wrap gap-1 mb-0">
                        <?php foreach (array_column($userRoles, 'permissions') as $permissionList): foreach ((array)$permissionList as $permission): ?>
                            <li class="badge bg-light text-dark border"><?= e(is_string($permission) ? $permission : ($permission['name'] ?? '')) ?></li>
                        <?php endforeach; endforeach; ?>
                    </ul>
                <?php endif; ?>

                <div class="collapse mt-3" id="roleForm">
                    <form method="post" action="<?= url('/users/' . (int)$user['id'] . '/roles') ?>" class="border-top pt-3">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label small">Assign Roles</label>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($allRoles as $role): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="roles[]" value="<?= (int)$role['id'] ?>" id="show-role-<?= (int)$role['id'] ?>"
                                               <?= in_array((int)$role['id'], array_column($userRoles, 'id'), true) ? 'checked' : '' ?>>
                                        <label class="form-check-label small me-2" for="show-role-<?= (int)$role['id'] ?>"><?= e($role['name']) ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <button class="btn btn-primary btn-sm" type="submit"><i class="bi bi-check-lg me-1"></i>Save Roles</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white"><h6 class="mb-0">Recent Login History</h6></div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <tbody>
                    <?php if (empty($logins)): ?>
                        <tr><td class="text-muted small p-3">No logins recorded.</td></tr>
                    <?php else: foreach ($logins as $login): ?>
                        <tr>
                            <td class="ps-3"><?= status_badge((string)$login['status']) ?></td>
                            <td class="small text-muted"><?= e($login['ip_address'] ?? '') ?> &middot; <?= e($login['user_agent'] ?? '') ?></td>
                            <td class="small text-muted text-end pe-3"><?= e(time_ago($login['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><h6 class="mb-0">Recent Activity</h6></div>
            <div class="list-group list-group-flush">
                <?php if (empty($activities)): ?>
                    <div class="list-group-item text-muted small">No activity yet.</div>
                <?php else: foreach ($activities as $activity): ?>
                    <div class="list-group-item py-2">
                        <div class="d-flex justify-content-between">
                            <span class="small fw-semibold"><?= e($activity['description'] ?? $activity['action']) ?></span>
                            <span class="small text-muted text-nowrap ms-2"><?= e(time_ago($activity['created_at'])) ?></span>
                        </div>
                        <div class="small text-muted"><i class="bi bi-hdd me-1"></i><?= e($activity['ip_address'] ?? '') ?></div>
                    </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </div>
</div>