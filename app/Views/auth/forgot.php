<form method="post" action="<?= url('/forgot-password') ?>">
    <?= csrf_field() ?>

    <p class="text-muted small mb-4">
        Enter your account email address. If it exists, we will send you a secure link to reset your password.
    </p>

    <div class="mb-4">
        <label class="form-label">Email Address</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            <input type="email" name="email" class="form-control <?= has_validation_error('email') ? 'is-invalid' : '' ?>"
                   value="<?= e(old('email')) ?>" required autofocus>
        </div>
        <?php if ($error = validation_error('email')): ?>
            <div class="invalid-feedback d-block"><?= e($error) ?></div>
        <?php endif; ?>
    </div>

    <button type="submit" class="btn btn-primary w-100 py-2">
        <i class="bi bi-envelope-arrow-up me-1"></i> Send Reset Link
    </button>
</form>

<div class="text-center mt-4">
    <small class="text-muted">Remembered it?
        <a href="<?= url('/login') ?>" class="text-decoration-none">Back to Sign In</a>
    </small>
</div>