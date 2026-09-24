<?php /** @var array $user @var array $userRoles @var array $apiTokens */ ?>
<div class="row g-3">
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body text-center">
                <div class="avatar-xl rounded-circle mx-auto d-flex align-items-center justify-content-center bg-primary-subtle text-primary mb-3">
                    <?= e(strtoupper(mb_substr((string)$user['name'], 0, 1))) ?>
                </div>
                <h5 class="mb-0"><?= e($user['name']) ?></h5>
                <div class="text-muted small"><?= e($user['email']) ?></div>
                <div class="mt-2"><?= status_badge((string)$user['status']) ?></div>

                <div class="mt-3 d-flex flex-wrap gap-1 justify-content-center">
                    <?php foreach ($userRoles as $userRole): ?>
                        <span class="badge bg-secondary-subtle text-secondary border"><?= e($userRole['name']) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><h6 class="mb-0">Security Status</h6></div>
            <ul class="list-group list-group-flush small">
                <li class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Email Verified</span>
                    <?= $user['email_verified_at'] ? '<span class="text-success"><i class="bi bi-check-circle me-1"></i>Yes</span>' : '<span class="text-danger"><i class="bi bi-x-circle me-1"></i>No</span>' ?>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Password Changed</span>
                    <span><?= $user['password_changed_at'] ? e(time_ago($user['password_changed_at'])) : '—' ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Last Login</span>
                    <span><?= $user['last_login_at'] ? e(time_ago($user['last_login_at'])) : '—' ?></span>
                </li>
            </ul>
        </div>
    </div>

    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white"><h6 class="mb-0">Update Profile</h6></div>
            <div class="card-body">
                <form method="post" action="<?= url('/profile') ?>" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control <?= has_validation_error('name') ? 'is-invalid' : '' ?>"
                               value="<?= e(old('name', $user['name'])) ?>" required>
                        <?php if ($error = validation_error('name')): ?><div class="invalid-feedback d-block"><?= e($error) ?></div><?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" value="<?= e($user['email']) ?>" disabled>
                        <div class="form-text">Email is managed by an administrator.</div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Avatar</label>
                        <input type="file" name="avatar" class="form-control" accept="image/jpeg,image/png,image/gif,image/webp">
                        <div class="form-text">JPEG, PNG, GIF or WEBP image.</div>
                    </div>
                    <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg me-1"></i>Save Profile</button>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white"><h6 class="mb-0">Change Password</h6></div>
            <div class="card-body">
                <form method="post" action="<?= url('/profile/password') ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Current Password <span class="text-danger">*</span></label>
                        <input type="password" name="current_password" class="form-control <?= has_validation_error('current_password') ? 'is-invalid' : '' ?>" required>
                        <?php if ($error = validation_error('current_password')): ?><div class="invalid-feedback d-block"><?= e($error) ?></div><?php endif; ?>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">New Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control <?= has_validation_error('password') ? 'is-invalid' : '' ?>" required>
                            <div class="form-text">Minimum <?= (int)setting('security.min_password_length', 8) ?> characters.</div>
                            <?php if ($error = validation_error('password')): ?><div class="invalid-feedback d-block"><?= e($error) ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                    </div>
                    <button class="btn btn-primary mt-3" type="submit"><i class="bi bi-key me-1"></i>Change Password</button>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><h6 class="mb-0">API Tokens</h6></div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                    <tr>
                        <th class="ps-3">Token</th>
                        <th>Created</th>
                        <th>Last Used</th>
                        <th>Expires</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($apiTokens)): ?>
                        <tr><td colspan="4" class="text-center text-muted small py-3">No API tokens generated yet.</td></tr>
                    <?php else: foreach ($apiTokens as $token): ?>
                        <tr>
                            <td class="ps-3"><code class="small"><?= e($token['name'] ?? '') ?>...</code></td>
                            <td class="small text-muted"><?= e(time_ago($token['created_at'])) ?></td>
                            <td class="small text-muted"><?= $token['last_used_at'] ? e(time_ago($token['last_used_at'])) : '—' ?></td>
                            <td class="small text-muted"><?= $token['expires_at'] ? e(time_ago($token['expires_at'])) : 'never' ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>