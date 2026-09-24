<?php /** @var array $security */ ?>
<form method="post" action="<?= url('/settings/security') ?>">
    <?= csrf_field() ?>

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Minimum Password Length <span class="text-danger">*</span></label>
            <input type="number" name="security.min_password_length" min="6" max="64" class="form-control"
                   value="<?= e(old('security.min_password_length', $security['min_password_length'] ?? 8)) ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Max Failed Attempts (lockout) <span class="text-danger">*</span></label>
            <input type="number" name="security.max_failed_attempts" min="3" max="20" class="form-control"
                   value="<?= e(old('security.max_failed_attempts', $security['max_failed_attempts'] ?? 5)) ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Lockout Duration (minutes) <span class="text-danger">*</span></label>
            <input type="number" name="security.lockout_minutes" min="1" max="1440" class="form-control"
                   value="<?= e(old('security.lockout_minutes', $security['lockout_minutes'] ?? 15)) ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Session Timeout (minutes) <span class="text-danger">*</span></label>
            <input type="number" name="security.session_timeout" min="5" max="1440" class="form-control"
                   value="<?= e(old('security.session_timeout', $security['session_timeout'] ?? 120)) ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Password Expiry (days, 0 = never) <span class="text-danger">*</span></label>
            <input type="number" name="security.password_expiry_days" min="0" max="365" class="form-control"
                   value="<?= e(old('security.password_expiry_days', $security['password_expiry_days'] ?? 0)) ?>">
        </div>
    </div>

    <div class="mt-4">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="security.require_verification" value="1" id="sv-verify" <?= !empty($security['require_verification']) ? 'checked' : '' ?>>
            <label class="form-check-label" for="sv-verify">Require email verification on registration</label>
        </div>
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="security.register_enabled" value="1" id="sv-register" <?= !empty($security['register_enabled']) ? 'checked' : '' ?>>
            <label class="form-check-label" for="sv-register">Allow public registration</label>
        </div>
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="security.remember_me" value="1" id="sv-remember" <?= !empty($security['remember_me']) ? 'checked' : '' ?>>
            <label class="form-check-label" for="sv-remember">Allow "remember me" persistent sessions</label>
        </div>
    </div>

    <div class="d-flex justify-content-end mt-4">
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save Security Settings</button>
    </div>
</form>