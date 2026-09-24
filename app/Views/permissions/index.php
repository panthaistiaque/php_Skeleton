<?php /** @var array $permissions */ ?>
<?php \App\Core\View::share('headerActions', user_can('permissions.create') ? '<a href="' . e(url('/permissions/create')) . '" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>New Permission</a>' : ''); ?>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
            <tr>
                <th class="ps-3">Permission</th>
                <th>Module</th>
                <th>Description</th>
                <th class="text-end pe-3">Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($permissions)): ?>
                <tr><td colspan="4" class="text-center text-muted py-4">No permissions defined yet.</td></tr>
            <?php else: foreach ($permissions as $permission): ?>
                <tr>
                    <td class="ps-3"><code><?= e($permission['slug']) ?></code></td>
                    <td><span class="badge bg-light text-dark border text-capitalize"><?= e($permission['module']) ?></span></td>
                    <td class="small text-muted"><?= e($permission['description'] ?? '—') ?></td>
                    <td class="text-end pe-3">
                        <?php if (user_can('permissions.edit')): ?>
                            <a class="btn btn-sm btn-outline-secondary" href="<?= url('/permissions/' . (int)$permission['id'] . '/edit') ?>" title="Edit"><i class="bi bi-pencil"></i></a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <?php if (!empty($permissions)): ?>
        <div class="card-footer bg-white d-flex justify-content-between align-items-center">
            <small class="text-muted">Showing <?= count($permissions) ?> of <?= (int)$pagination['total'] ?> permissions</small>
            <?php require VIEW_PATH . '/partials/pagination.php'; ?>
        </div>
    <?php endif; ?>
</div>