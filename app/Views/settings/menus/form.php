<?php /** @var array|null $menu @var array $parents */ $isEdit = $menu !== null; ?>
<div class="card border-0 shadow-sm" style="max-width:760px;">
    <div class="card-body">
        <form method="post" action="<?= $isEdit ? e(url('/settings/menus/' . (int)$menu['id'])) : e(url('/settings/menus')) ?>">
            <?= csrf_field() ?>
            <?php if ($isEdit): ?><?= method_field('PUT') ?><?php endif; ?>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control <?= has_validation_error('title') ? 'is-invalid' : '' ?>"
                           value="<?= e(old('title', $menu['title'] ?? '')) ?>" required maxlength="120">
                    <?php if ($error = validation_error('title')): ?><div class="invalid-feedback d-block"><?= e($error) ?></div><?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Slug <span class="text-danger">*</span></label>
                    <input type="text" name="slug" class="form-control <?= has_validation_error('slug') ? 'is-invalid' : '' ?>"
                           value="<?= e(old('slug', $menu['slug'] ?? '')) ?>" required maxlength="120">
                    <?php if ($error = validation_error('slug')): ?><div class="invalid-feedback d-block"><?= e($error) ?></div><?php endif; ?>
                </div>
            </div>

            <div class="row g-3 mt-1">
                <div class="col-md-6">
                    <label class="form-label">Parent</label>
                    <select name="parent_id" class="form-select">
                        <option value="">— Top level —</option>
                        <?php foreach ($parents as $parent): ?>
                            <option value="<?= (int)$parent['id'] ?>" <?= (string)old('parent_id', $menu['parent_id'] ?? '') === (string)$parent['id'] ? 'selected' : '' ?>>
                                <?= e($parent['title']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Route</label>
                    <input type="text" name="route" class="form-control" maxlength="190" value="<?= e(old('route', $menu['route'] ?? '')) ?>">
                    <div class="form-text">Internal app path, e.g. <code>/users</code>. Leave empty for a menu group.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Icon (Bootstrap Icons class)</label>
                    <input type="text" name="icon" class="form-control" maxlength="60" value="<?= e(old('icon', $menu['icon'] ?? '')) ?>">
                    <div class="form-text">e.g. <code>bi-people</code></div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Required Permission</label>
                    <input type="text" name="permission" class="form-control" maxlength="120" value="<?= e(old('permission', $menu['permission'] ?? '')) ?>">
                    <div class="form-text">e.g. <code>users.view</code>. Leave empty to show to all authenticated users.</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Module <span class="text-danger">*</span></label>
                    <input type="text" name="module" class="form-control" required maxlength="60" value="<?= e(old('module', $menu['module'] ?? '')) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Display Order</label>
                    <input type="number" name="sort_order" class="form-control" value="<?= e(old('sort_order', $menu['sort_order'] ?? 0)) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" <?= (old('status', $menu['status'] ?? 'active')) === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= (old('status', $menu['status'] ?? '')) === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-end gap-2">
                <a href="<?= url('/settings/menus') ?>" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i><?= $isEdit ? 'Update Menu Item' : 'Create Menu Item' ?></button>
            </div>
        </form>
    </div>
</div>