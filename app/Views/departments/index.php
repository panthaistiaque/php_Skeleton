<?php /** @var array $departments */ ?>
<?php \App\Core\View::share('headerActions', user_can('departments.manage') ? '<a href="' . e(url('/departments/create')) . '" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>New Department</a>' : ''); ?>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
            <tr>
                <th class="ps-3">Name</th>
                <th>Code</th>
                <th>Description</th>
                <th>Status</th>
                <th class="text-end pe-3">Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($departments)): ?>
                <tr><td colspan="5" class="text-center text-muted py-4">No departments yet.</td></tr>
            <?php else: foreach ($departments as $department): ?>
                <tr>
                    <td class="ps-3 fw-semibold"><?= e($department['name']) ?></td>
                    <td class="small"><?= e($department['code'] ?? '—') ?></td>
                    <td class="small text-muted"><?= e($department['description'] ?? '—') ?></td>
                    <td><?= status_badge((string)$department['status']) ?></td>
                    <td class="text-end pe-3">
                        <?php if (user_can('departments.manage')): ?>
                            <div class="btn-group btn-group-sm">
                                <a class="btn btn-outline-secondary" href="<?= url('/departments/' . (int)$department['id'] . '/edit') ?>" title="Edit"><i class="bi bi-pencil"></i></a>
                                <form method="post" action="<?= url('/departments/' . (int)$department['id']) ?>" class="d-inline" onsubmit="return confirm('Delete this department?')">
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

    <?php if (!empty($departments)): ?>
        <div class="card-footer bg-white d-flex justify-content-between align-items-center">
            <small class="text-muted">Showing <?= count($departments) ?> of <?= (int)$pagination['total'] ?> departments</small>
            <?php require VIEW_PATH . '/partials/pagination.php'; ?>
        </div>
    <?php endif; ?>
</div>