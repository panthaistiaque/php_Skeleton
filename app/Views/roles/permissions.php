<?php /** @var array $role @var array $permissions @var array $assigned */ ?>
<form method="post" action="<?= url('/roles/' . (int)$role['id'] . '/permissions') ?>">
    <?= csrf_field() ?>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body d-flex justify-content-between align-items-center py-3">
            <div>
                <h6 class="mb-0"><?= e($role['name']) ?>
                    <?php if (!empty($role['is_system'])): ?><span class="badge bg-warning-subtle text-dark border">System</span><?php endif; ?>
                </h6>
                <small class="text-muted"><?= e($role['description'] ?? '') ?></small>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-secondary" type="button" id="checkAll"><i class="bi bi-check2-square me-1"></i>Check All Modules</button>
                <button class="btn btn-sm btn-outline-danger" type="button" id="uncheckAll"><i class="bi bi-square me-1"></i>Uncheck All</button>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <?php if (empty($permissions)): ?>
            <div class="col-12"><div class="card border-0 shadow-sm"><div class="card-body text-muted text-center py-4">No permissions defined yet.</div></div></div>
        <?php else: foreach ($permissions as $module => $modulePermissions): ?>
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                        <span class="fw-semibold text-capitalize"><?= e($module) ?></span>
                        <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none module-toggle" data-module="<?= e($module) ?>">toggle all</button>
                    </div>
                    <div class="card-body pt-2" style="max-height:260px;overflow-y:auto;">
                        <?php foreach ($modulePermissions as $permission): ?>
                            <div class="form-check">
                                <input class="form-check-input permission" type="checkbox" name="permissions[]" value="<?= (int)$permission['id'] ?>"
                                       id="perm-<?= (int)$permission['id'] ?>" data-module="<?= e($module) ?>"
                                       <?= in_array((int)$permission['id'], array_map('intval', $assigned), true) ? 'checked' : '' ?>>
                                <label class="form-check-label small" for="perm-<?= (int)$permission['id'] ?>" title="<?= e($permission['description'] ?? '') ?>">
                                    <code><?= e($permission['slug']) ?></code>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; endif; ?>
    </div>

    <div class="mt-4 d-flex justify-content-between gap-2">
        <a href="<?= url('/roles') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to Roles</a>
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save Permissions</button>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const checkAll = document.getElementById('checkAll');
        const uncheckAll = document.getElementById('uncheckAll');
        const perms = () => Array.from(document.querySelectorAll('.permission'));

        if (checkAll) checkAll.addEventListener('click', () => perms().forEach(i => i.checked = true));
        if (uncheckAll) uncheckAll.addEventListener('click', () => perms().forEach(i => i.checked = false));

        document.querySelectorAll('.module-toggle').forEach(btn => {
            btn.addEventListener('click', () => {
                const module = btn.dataset.module;
                const boxes = perms().filter(i => i.dataset.module === module);
                const allOn = boxes.every(i => i.checked);
                boxes.forEach(i => i.checked = !allOn);
            });
        });
    });
</script>