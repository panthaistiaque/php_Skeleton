<?php $minLength = (int)setting('security.min_password_length', 8); ?>
<form method="post" action="<?= url('/register') ?>" autocomplete="off">
    <?= csrf_field() ?>

    <div class="mb-3">
        <label class="form-label">Full Name</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-person"></i></span>
            <input type="text" name="name" class="form-control <?= has_validation_error('name') ? 'is-invalid' : '' ?>" value="<?= e(old('name')) ?>" required>
        </div>
        <?php if ($error = validation_error('name')): ?>
            <div class="invalid-feedback d-block"><?= e($error) ?></div>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <label class="form-label">Email Address</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            <input type="email" name="email" class="form-control <?= has_validation_error('email') ? 'is-invalid' : '' ?>" value="<?= e(old('email')) ?>" required>
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
        <div class="form-text">Minimum <?= (int)$minLength ?> characters.</div>
        <?php if ($error = validation_error('password')): ?>
            <div class="invalid-feedback d-block"><?= e($error) ?></div>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <label class="form-label">Confirm Password</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
            <input type="password" name="password_confirmation" class="form-control" required>
        </div>
    </div>

    <div class="form-check mb-4">
        <input class="form-check-input" type="checkbox" name="terms" value="1" id="terms">
        <label class="form-check-label small" for="terms">
            I agree to the <a href="#" class="text-decoration-none">Terms &amp; Conditions</a>.
        </label>
        <?php if ($error = validation_error('terms')): ?>
            <div class="invalid-feedback d-block"><?= e($error) ?></div>
        <?php endif; ?>
    </div>

    <button type="submit" class="btn btn-primary w-100 py-2">
        <i class="bi bi-person-plus me-1"></i> Create Account
    </button>
</form>

<div class="text-center mt-4">
    <small class="text-muted">Already have an account?
        <a href="<?= url('/login') ?>" class="text-decoration-none">Sign In</a>
    </small>
</div>