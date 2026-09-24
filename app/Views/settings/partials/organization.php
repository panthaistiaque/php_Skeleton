<?php /** @var array $org */ ?>
<form method="post" action="<?= url('/settings/organization') ?>">
    <?= csrf_field() ?>

    <div class="mb-3">
        <label class="form-label">Organization Name <span class="text-danger">*</span></label>
        <input type="text" name="organization.name" class="form-control <?= has_validation_error('organization.name') ? 'is-invalid' : '' ?>"
               value="<?= e(old('organization.name', $org['name'] ?? '')) ?>" required maxlength="150">
        <?php if ($error = validation_error('organization.name')): ?><div class="invalid-feedback d-block"><?= e($error) ?></div><?php endif; ?>
    </div>

    <div class="mb-3">
        <label class="form-label">Address</label>
        <textarea name="organization.address" rows="2" class="form-control" maxlength="500"><?= e(old('organization.address', $org['address'] ?? '')) ?></textarea>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <label class="form-label">Phone</label>
            <input type="text" name="organization.phone" class="form-control" maxlength="30" value="<?= e(old('organization.phone', $org['phone'] ?? '')) ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Email</label>
            <input type="email" name="organization.email" class="form-control <?= has_validation_error('organization.email') ? 'is-invalid' : '' ?>"
                   maxlength="190" value="<?= e(old('organization.email', $org['email'] ?? '')) ?>">
            <?php if ($error = validation_error('organization.email')): ?><div class="invalid-feedback d-block"><?= e($error) ?></div><?php endif; ?>
        </div>
    </div>

    <div class="mb-4">
        <label class="form-label">Website</label>
        <input type="url" name="organization.website" class="form-control" maxlength="190" value="<?= e(old('organization.website', $org['website'] ?? '')) ?>">
    </div>

    <div class="d-flex justify-content-end">
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save Organization Settings</button>
    </div>
</form>