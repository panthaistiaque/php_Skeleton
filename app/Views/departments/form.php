<?php /** @var array|null $department */ $isEdit = $department !== null; ?>
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="post" action="<?= $isEdit ? e(url('/departments/' . (int)$department['id'])) : e(url('/departments')) ?>">
            <?= csrf_field() ?>
            <?php if ($isEdit): ?><?= method_field('PUT') ?><?php endif; ?>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Department Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control <?= has_validation_error('name') ? 'is-invalid' : '' ?>"
                           value="<?= e(old('name', $department['name'] ?? '')) ?>" required>
                    <?php if ($error = validation_error('name')): ?><div class="invalid-feedback d-block"><?= e($error) ?></div><?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Code</label>
                    <input type="text" name="code" class="form-control" maxlength="30" value="<?= e(old('code', $department['code'] ?? '')) ?>">
                </div>
            </div>

            <div class="mt-3">
                <label class="form-label">Description</label>
                <textarea name="description" rows="3" class="form-control" maxlength="500"><?= e(old('description', $department['description'] ?? '')) ?></textarea>
            </div>

            <div class="mt-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select" style="max-width:240px;">
                    <option value="active" <?= (old('status', $department['status'] ?? 'active')) === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= (old('status', $department['status'] ?? '')) === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
                <?php if ($error = validation_error('status')): ?><div class="invalid-feedback d-block"><?= e($error) ?></div><?php endif; ?>
            </div>

            <div class="mt-4 d-flex justify-content-end gap-2">
                <a href="<?= url('/departments') ?>" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i><?= $isEdit ? 'Update Department' : 'Create Department' ?></button>
            </div>
        </form>
    </div>
</div>