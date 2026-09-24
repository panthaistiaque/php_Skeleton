<?php /** @var array $menus */ ?>
<?php \App\Core\View::share('headerActions', user_can('menus.manage') ? '<a href="' . e(url('/settings/menus/create')) . '" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>New Menu Item</a>' : ''); ?>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
            <tr>
                <th class="ps-3">Menu</th>
                <th>Parent</th>
                <th>Route</th>
                <th>Module</th>
                <th>Permission</th>
                <th>Order</th>
                <th>Status</th>
                <th class="text-end pe-3">Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($menus)): ?>
                <tr><td colspan="8" class="text-center text-muted py-4">No menu items defined.</td></tr>
            <?php else: foreach ($menus as $menu): ?>
                <tr>
                    <td class="ps-3">
                        <i class="bi <?= e($menu['icon'] ?? 'bi-dot') ?> me-1 text-muted"></i>
                        <span class="fw-semibold"><?= e($menu['title']) ?></span>
                    </td>
                    <td class="small"><?= e($menu['parent_title'] ?? '—') ?></td>
                    <td class="small"><code><?= e($menu['route'] ?? '—') ?></code></td>
                    <td class="small"><?= e($menu['module']) ?></td>
                    <td class="small text-muted"><?= e($menu['permission'] ?? '—') ?></td>
                    <td class="small"><?= (int)$menu['sort_order'] ?></td>
                    <td><?= status_badge((string)$menu['status']) ?></td>
                    <td class="text-end pe-3">
                        <?php if (user_can('menus.manage')): ?>
                            <div class="btn-group btn-group-sm">
                                <a class="btn btn-outline-secondary" href="<?= url('/settings/menus/' . (int)$menu['id'] . '/edit') ?>" title="Edit"><i class="bi bi-pencil"></i></a>
                                <form method="post" action="<?= url('/settings/menus/' . (int)$menu['id']) ?>" class="d-inline" onsubmit="return confirm('Delete this menu item?')">
                                    <?= csrf_field() ?>
                                    <?= method_field('DELETE') ?>
                                    <button class="btn btn-outline-danger" type="submit" title="Delete"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <?php if (!empty($menus)): ?>
        <div class="card-footer bg-white d-flex justify-content-between align-items-center">
            <small class="text-muted">Showing <?= count($menus) ?> of <?= (int)$pagination['total'] ?> menu items</small>
            <?php require VIEW_PATH . '/partials/pagination.php'; ?>
        </div>
    <?php endif; ?>
</div>