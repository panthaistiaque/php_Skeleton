<?php /** @var array $users @var array $pagination @var array $filters @var array $roles @var array $departments */ ?>
<?php \App\Core\View::share('headerActions', user_can('users.create') ? '<a href="' . e(url('/users/create')) . '" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>New User</a>' : ''); ?>

<div class="card border-0 shadow-sm">
    <div class="card-body pb-3">
        <form method="get" action="<?= url('/users') ?>" class="row g-2 align-items-end">
            <div class="col-12 col-md-4">
                <label class="form-label small mb-1">Search</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Name or email" value="<?= e($filters['search']) ?>">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="active" <?= $filters['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $filters['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    <option value="pending" <?= $filters['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small mb-1">Role</label>
                <select name="role_id" class="form-select form-select-sm">
                    <option value="">All roles</option>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= (int)$role['id'] ?>" <?= (string)$filters['role_id'] === (string)$role['id'] ? 'selected' : '' ?>><?= e($role['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1">Department</label>
                <select name="department_id" class="form-select form-select-sm">
                    <option value="">All</option>
                    <?php foreach ($departments as $department): ?>
                        <option value="<?= (int)$department['id'] ?>" <?= (string)$filters['department_id'] === (string)$department['id'] ? 'selected' : '' ?>><?= e($department['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-1">
                <button class="btn btn-outline-primary btn-sm w-100" type="submit"><i class="bi bi-search"></i></button>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover table-striped align-middle mb-0">
            <thead class="table-light">
            <tr>
                <th class="ps-3">User</th>
                <th>Roles</th>
                <th>Department</th>
                <th>Designation</th>
                <th>Status</th>
                <th>Last Login</th>
                <th class="text-end pe-3">Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($users)): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">No users found.</td></tr>
            <?php else: foreach ($users as $user): ?>
                <tr>
                    <td class="ps-3">
                        <div class="fw-semibold"><?= e($user['name']) ?></div>
                        <div class="text-muted small"><?= e($user['email']) ?></div>
                    </td>
                    <td>
                        <?php
                        $userRoles = $user['roles'] ?? [];
                        if (is_string($userRoles)) {
                            $userRoles = array_filter(array_map('trim', explode(',', $userRoles)));
                        }
                        foreach ($userRoles as $role): ?>
                            <span class="badge bg-secondary-subtle text-secondary border"><?= e($role) ?></span>
                        <?php endforeach; ?>
                    </td>
                    <td class="small"><?= e($user['department_name'] ?? '—') ?></td>
                    <td class="small"><?= e($user['designation_name'] ?? '—') ?></td>
                    <td><?= status_badge((string)$user['status']) ?></td>
                    <td class="small text-muted"><?= $user['last_login_at'] ? e(time_ago($user['last_login_at'])) : '—' ?></td>
                    <td class="text-end pe-3">
                        <div class="btn-group btn-group-sm">
                            <a class="btn btn-outline-primary" href="<?= url('/users/' . (int)$user['id']) ?>" title="View"><i class="bi bi-eye"></i></a>
                            <?php if (user_can('users.edit')): ?>
                                <a class="btn btn-outline-secondary" href="<?= url('/users/' . (int)$user['id'] . '/edit') ?>" title="Edit"><i class="bi bi-pencil"></i></a>
                            <?php endif; ?>
                            <?php if (user_can('users.delete') && !\App\Models\User::isSuperAdmin((int)$user['id'])): ?>
                                <form method="post" action="<?= url('/users/' . (int)$user['id']) ?>" class="d-inline" onsubmit="return confirm('Delete this user? This cannot be undone.')">
                                    <?= csrf_field() ?>
                                    <?= method_field('DELETE') ?>
                                    <button class="btn btn-outline-danger" type="submit" title="Delete"><i class="bi bi-trash"></i></button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <?php if (!empty($users)): ?>
        <div class="card-footer bg-white d-flex justify-content-between align-items-center">
            <?php if (empty($pagination['last_page'])): ?>
                <small class="text-muted"><?= count($users) ?> user(s)</small>
            <?php else: ?>
                <small class="text-muted">Showing <?= count($users) ?> of <?= (int)$pagination['total'] ?> users</small>
            <?php endif; ?>
            <?php require VIEW_PATH . '/partials/pagination.php'; ?>
        </div>
    <?php endif; ?>
</div>