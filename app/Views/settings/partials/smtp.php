<?php /** @var array $smtp */ ?>
<form method="post" action="<?= url('/settings/smtp') ?>">
    <?= csrf_field() ?>

    <div class="form-check form-switch mb-4">
        <input class="form-check-input" type="checkbox" name="smtp.enabled" value="1" id="smtp-enabled" <?= !empty($smtp['enabled']) ? 'checked' : '' ?>>
        <label class="form-check-label" for="smtp-enabled">Enable SMTP mail sending</label>
        <div class="form-text">When disabled, outgoing emails are logged to <code>storage/mails</code> instead of being sent.</div>
    </div>

    <div class="row g-3">
        <div class="col-md-8">
            <label class="form-label">SMTP Host <span class="text-danger">*</span></label>
            <input type="text" name="smtp.host" class="form-control <?= has_validation_error('smtp.host') ? 'is-invalid' : '' ?>"
                   value="<?= e(old('smtp.host', $smtp['host'] ?? '')) ?>" required maxlength="190">
            <?php if ($error = validation_error('smtp.host')): ?><div class="invalid-feedback d-block"><?= e($error) ?></div><?php endif; ?>
        </div>
        <div class="col-md-4">
            <label class="form-label">Port <span class="text-danger">*</span></label>
            <input type="number" name="smtp.port" min="1" max="65535" class="form-control"
                   value="<?= e(old('smtp.port', $smtp['port'] ?? 587)) ?>" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Username</label>
            <input type="text" name="smtp.username" class="form-control" maxlength="190" value="<?= e(old('smtp.username', $smtp['username'] ?? '')) ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Password</label>
            <input type="password" name="smtp.password" class="form-control" autocomplete="new-password"
                   placeholder="<?= !empty($smtp['password']) ? '•••••••• (stored, leave blank to keep)' : 'Enter password' ?>">
            <div class="form-text">Stored encrypted (AES-256-CBC). Leave blank to keep the current password.</div>
        </div>
        <div class="col-md-4">
            <label class="form-label">Encryption</label>
            <select name="smtp.encryption" class="form-select">
                <option value="tls" <?= ($smtp['encryption'] ?? 'tls') === 'tls' ? 'selected' : '' ?>>TLS</option>
                <option value="ssl" <?= ($smtp['encryption'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                <option value="" <?= ($smtp['encryption'] ?? '') === '' ? 'selected' : '' ?>>None</option>
            </select>
        </div>
        <div class="col-md-8">
            <label class="form-label">From Email <span class="text-danger">*</span></label>
            <input type="email" name="smtp.from_email" class="form-control" required maxlength="190"
                   value="<?= e(old('smtp.from_email', $smtp['from_email'] ?? '')) ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">From Name <span class="text-danger">*</span></label>
            <input type="text" name="smtp.from_name" class="form-control" required maxlength="190"
                   value="<?= e(old('smtp.from_name', $smtp['from_name'] ?? '')) ?>">
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <button type="submit" name="action" value="test" class="btn btn-outline-secondary" formaction="<?= url('/settings/test-mail') ?>" formmethod="post">
            <i class="bi bi-envelope-check me-1"></i>Send Test Email
        </button>
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save SMTP Settings</button>
    </div>
</form>