<?php /** @var string $token */ ?>
<form method="post" action="<?= url('/login') ?>" autocomplete="off">
    <?= csrf_field() ?>

    <div class="mb-3">
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

    <div class="mb-3">
        <label class="form-label">Password</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" name="password" class="form-control <?= has_validation_error('password') ? 'is-invalid' : '' ?>" required>
        </div>
        <?php if ($error = validation_error('password')): ?>
            <div class="invalid-feedback d-block"><?= e($error) ?></div>
        <?php endif; ?>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="remember" value="1" id="rememberMe">
            <label class="form-check-label small" for="rememberMe">Remember me</label>
        </div>
        <a href="<?= url('/forgot-password') ?>" class="small text-decoration-none">Forgot password?</a>
    </div>

    <button type="submit" class="btn btn-primary w-100 py-2">
        <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
    </button>
</form>

<div class="text-center mt-4">
    <?php if ((bool)setting('security.register_enabled', true)): ?>
        <small class="text-muted">Don't have an account?
            <a href="<?= url('/register') ?>" class="text-decoration-none">Register</a>
        </small>
    <?php endif; ?>
</div>