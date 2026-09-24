<?php /** @var array|null $permission */ $isEdit = $permission !== null; ?>
<div class="card border-0 shadow-sm" style="max-width:720px;">
    <div class="card-body">
        <form method="post" action="<?= $isEdit ? e(url('/permissions/' . (int)$permission['id'])) : e(url('/permissions')) ?>">
            <?= csrf_field() ?>
            <?php if ($isEdit): ?><?= method_field('PUT') ?><?php endif; ?>

            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control <?= has_validation_error('name') ? 'is-invalid' : '' ?>"
                           value="<?= e(old('name', $permission['name'] ?? '')) ?>" required>
                    <?php if ($error = validation_error('name')): ?><div class="invalid-feedback d-block"><?= e($error) ?></div><?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Slug <span class="text-danger">*</span></label>
                    <input type="text" name="slug" class="form-control <?= has_validation_error('slug') ? 'is-invalid' : '' ?>"
                           value="<?= e(old('slug', $permission['slug'] ?? '')) ?>" required>
                    <div class="form-text">e.g. <code>reports.export</code></div>
                    <?php if ($error = validation_error('slug')): ?><div class="invalid-feedback d-block"><?= e($error) ?></div><?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Module <span class="text-danger">*</span></label>
                    <input type="text" name="module" class="form-control <?= has_validation_error('module') ? 'is-invalid' : '' ?>"
                           value="<?= e(old('module', $permission['module'] ?? '')) ?>" required>
                    <div class="form-text">e.g. <code>reports</code></div>
                    <?php if ($error = validation_error('module')): ?><div class="invalid-feedback d-block"><?= e($error) ?></div><?php endif; ?>
                </div>
            </div>

            <div class="mt-3">
                <label class="form-label">Description</label>
                <textarea name="description" rows="3" class="form-control" maxlength="500"><?= e(old('description', $permission['description'] ?? '')) ?></textarea>
            </div>

            <div class="mt-4 d-flex justify-content-end gap-2">
                <a href="<?= url('/permissions') ?>" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i><?= $isEdit ? 'Update Permission' : 'Create Permission' ?></button>
            </div>
        </form>
    </div>
</div>