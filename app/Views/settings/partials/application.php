<?php /** @var array $app */ ?>
<form method="post" action="<?= url('/settings/application') ?>">
    <?= csrf_field() ?>

    <div class="mb-3">
        <label class="form-label">Application Name <span class="text-danger">*</span></label>
        <input type="text" name="app.name" class="form-control <?= has_validation_error('app.name') ? 'is-invalid' : '' ?>"
               value="<?= e(old('app.name', $app['name'] ?? '')) ?>" required maxlength="120">
        <?php if ($error = validation_error('app.name')): ?><div class="invalid-feedback d-block"><?= e($error) ?></div><?php endif; ?>
        <div class="form-text">Used in the navigation bar, page titles and emails.</div>
    </div>

    <div class="mb-4">
        <label class="form-label">Logo Text</label>
        <input type="text" name="app.logo_text" class="form-control" maxlength="60" value="<?= e(old('app.logo_text', $app['logo_text'] ?? '')) ?>">
    </div>

    <div class="d-flex justify-content-end">
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save Application Settings</button>
    </div>
</form>