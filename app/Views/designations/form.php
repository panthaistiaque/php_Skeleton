<?php /** @var array|null $designation @var array $departments */ $isEdit = $designation !== null; ?>
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="post" action="<?= $isEdit ? e(url('/designations/' . (int)$designation['id'])) : e(url('/designations')) ?>">
            <?= csrf_field() ?>
            <?php if ($isEdit): ?><?= method_field('PUT') ?><?php endif; ?>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Designation Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control <?= has_validation_error('name') ? 'is-invalid' : '' ?>"
                           value="<?= e(old('name', $designation['name'] ?? '')) ?>" required>
                    <?php if ($error = validation_error('name')): ?><div class="invalid-feedback d-block"><?= e($error) ?></div><?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Department</label>
                    <select name="department_id" class="form-select">
                        <option value="">— None —</option>
                        <?php foreach ($departments as $department): ?>
                            <option value="<?= (int)$department['id'] ?>" <?= (string)old('department_id', $designation['department_id'] ?? '') === (string)$department['id'] ? 'selected' : '' ?>>
                                <?= e($department['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="mt-3">
                <label class="form-label">Description</label>
                <textarea name="description" rows="3" class="form-control" maxlength="500"><?= e(old('description', $designation['description'] ?? '')) ?></textarea>
            </div>

            <div class="mt-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select" style="max-width:240px;">
                    <option value="active" <?= (old('status', $designation['status'] ?? 'active')) === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= (old('status', $designation['status'] ?? '')) === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>

            <div class="mt-4 d-flex justify-content-end gap-2">
                <a href="<?= url('/designations') ?>" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i><?= $isEdit ? 'Update Designation' : 'Create Designation' ?></button>
            </div>
        </form>
    </div>
</div>