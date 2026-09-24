<?php /** @var array $designations */ ?>
<?php \App\Core\View::share('headerActions', user_can('designations.manage') ? '<a href="' . e(url('/designations/create')) . '" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>New Designation</a>' : ''); ?>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
            <tr>
                <th class="ps-3">Name</th>
                <th>Department</th>
                <th>Description</th>
                <th>Status</th>
                <th class="text-end pe-3">Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($designations)): ?>
                <tr><td colspan="5" class="text-center text-muted py-4">No designations yet.</td></tr>
            <?php else: foreach ($designations as $designation): ?>
                <tr>
                    <td class="ps-3 fw-semibold"><?= e($designation['name']) ?></td>
                    <td class="small"><?= e($designation['department_name'] ?? '—') ?></td>
                    <td class="small text-muted"><?= e($designation['description'] ?? '—') ?></td>
                    <td><?= status_badge((string)$designation['status']) ?></td>
                    <td class="text-end pe-3">
                        <?php if (user_can('designations.manage')): ?>
                            <div class="btn-group btn-group-sm">
                                <a class="btn btn-outline-secondary" href="<?= url('/designations/' . (int)$designation['id'] . '/edit') ?>" title="Edit"><i class="bi bi-pencil"></i></a>
                                <form method="post" action="<?= url('/designations/' . (int)$designation['id']) ?>" class="d-inline" onsubmit="return confirm('Delete this designation?')">
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

    <?php if (!empty($designations)): ?>
        <div class="card-footer bg-white d-flex justify-content-between align-items-center">
            <small class="text-muted">Showing <?= count($designations) ?> of <?= (int)$pagination['total'] ?> designations</small>
            <?php require VIEW_PATH . '/partials/pagination.php'; ?>
        </div>
    <?php endif; ?>
</div>