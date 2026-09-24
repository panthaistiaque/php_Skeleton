<?php /** @var array $roles */ ?>
<?php \App\Core\View::share('headerActions', user_can('roles.create') ? '<a href="' . e(url('/roles/create')) . '" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>New Role</a>' : ''); ?>

<div class="row g-3">
    <?php if (empty($roles)): ?>
        <div class="col-12"><div class="card border-0 shadow-sm"><div class="card-body text-center text-muted py-4">No roles found.</div></div></div>
    <?php else: foreach ($roles as $role): ?>
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="mb-0"><?= e($role['name']) ?>
                                <?php if (!empty($role['is_system'])): ?><span class="badge bg-warning-subtle text-dark border align-middle">System</span><?php endif; ?>
                            </h5>
                            <small class="text-muted"><?= e($role['slug']) ?></small>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-primary-subtle text-primary"><?= (int)$role['user_count'] ?> users</span>
                            <div class="mt-1">
                                <span class="badge bg-info-subtle text-info"><i class="bi bi-shield-check me-1"></i><?= (int)$role['permission_count'] ?> permissions</span>
                            </div>
                            <div class="mt-1"><?= status_badge((string)$role['status']) ?></div>
                        </div>
                    </div>

                    <p class="small text-muted mt-2 mb-3 flex-grow-1"><?= e($role['description'] ?? 'No description provided.') ?></p>

                    <div class="btn-group btn-group-sm w-100">
                        <?php if (user_can('roles.permissions')): ?>
                            <a class="btn btn-outline-primary" href="<?= url('/roles/' . (int)$role['id'] . '/permissions') ?>"><i class="bi bi-shield-check me-1"></i>Permissions</a>
                        <?php endif; ?>
                        <?php if (user_can('roles.edit')): ?>
                            <a class="btn btn-outline-secondary" href="<?= url('/roles/' . (int)$role['id'] . '/edit') ?>"><i class="bi bi-pencil me-1"></i>Edit</a>
                        <?php endif; ?>
                        <?php if (user_can('roles.delete') && empty($role['is_system'])): ?>
                            <form method="post" action="<?= url('/roles/' . (int)$role['id']) ?>" class="d-inline" onsubmit="return confirm('Delete this role? This cannot be undone.')">
                                <?= csrf_field() ?>
                                <?= method_field('DELETE') ?>
                                <button class="btn btn-outline-danger" type="submit"><i class="bi bi-trash"></i></button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; endif; ?>

    <?php if (!empty($roles)): ?>
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center py-2">
                    <small class="text-muted">Showing <?= count($roles) ?> of <?= (int)$pagination['total'] ?> roles</small>
                    <?php require VIEW_PATH . '/partials/pagination.php'; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>