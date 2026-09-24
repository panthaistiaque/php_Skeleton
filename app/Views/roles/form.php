<?php /** @var array|null $role */ $isEdit = $role !== null; ?>
<div class="card border-0 shadow-sm" style="max-width:720px;">
    <div class="card-body">
        <form method="post" action="<?= $isEdit ? e(url('/roles/' . (int)$role['id'])) : e(url('/roles')) ?>">
            <?= csrf_field() ?>
            <?php if ($isEdit): ?><?= method_field('PUT') ?><?php endif; ?>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Role Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control <?= has_validation_error('name') ? 'is-invalid' : '' ?>"
                           value="<?= e(old('name', $role['name'] ?? '')) ?>" required>
                    <?php if ($error = validation_error('name')): ?><div class="invalid-feedback d-block"><?= e($error) ?></div><?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Slug <span class="text-danger">*</span></label>
                    <input type="text" name="slug" class="form-control <?= has_validation_error('slug') ? 'is-invalid' : '' ?>"
                           value="<?= e(old('slug', $role['slug'] ?? '')) ?>" <?= $isEdit && !empty($role['is_system']) ? 'disabled title="Slug cannot be changed for system roles"' : '' ?> required>
                    <div class="form-text">Lowercase letters, numbers, dashes. e.g. <code>content-editor</code></div>
                    <?php if ($error = validation_error('slug')): ?><div class="invalid-feedback d-block"><?= e($error) ?></div><?php endif; ?>
                </div>
            </div>

            <div class="mt-3">
                <label class="form-label">Description</label>
                <textarea name="description" rows="3" class="form-control" maxlength="500"><?= e(old('description', $role['description'] ?? '')) ?></textarea>
            </div>

            <div class="mt-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select" style="max-width:240px;" <?= $isEdit && !empty($role['is_system']) ? 'disabled title="Status is fixed for system roles"' : '' ?>>
                    <option value="active" <?= (old('status', $role['status'] ?? 'active')) === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= (old('status', $role['status'] ?? '')) === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
                <?php if ($isEdit && !empty($role['is_system'])): ?>
                    <input type="hidden" name="slug" value="<?= e($role['slug']) ?>">
                    <input type="hidden" name="status" value="active">
                    <div class="form-text text-warning">This is a system role. Slug and status are managed by the application.</div>
                <?php endif; ?>
            </div>

            <div class="mt-4 d-flex justify-content-end gap-2">
                <a href="<?= url('/roles') ?>" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i><?= $isEdit ? 'Update Role' : 'Create Role' ?></button>
            </div>
        </form>
    </div>
</div>