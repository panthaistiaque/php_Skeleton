<?php
/**
 * User form partial. Used by users/create and users/edit.
 * @var bool $isEdit @var array|null $user @var array $userRoleIds
 * @var array $departments @var array $designations @var array $roles
 * @var int $minPasswordLength
 */
$isEdit = $isEdit ?? false;
$user = $user ?? null;
$userRoleIds = $userRoleIds ?? [];
?>
<form method="post" action="<?= $isEdit ? e(url('/users/' . (int)$user['id'])) : e(url('/users')) ?>">
    <?= csrf_field() ?>
    <?php if ($isEdit): ?><?= method_field('PUT') ?><?php endif; ?>

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Full Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control <?= has_validation_error('name') ? 'is-invalid' : '' ?>"
                   value="<?= e(old('name', $user['name'] ?? '')) ?>" required>
            <?php if ($error = validation_error('name')): ?><div class="invalid-feedback d-block"><?= e($error) ?></div><?php endif; ?>
        </div>
        <div class="col-md-6">
            <label class="form-label">Email <span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control <?= has_validation_error('email') ? 'is-invalid' : '' ?>"
                   value="<?= e(old('email', $user['email'] ?? '')) ?>" required>
            <?php if ($error = validation_error('email')): ?><div class="invalid-feedback d-block"><?= e($error) ?></div><?php endif; ?>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-md-4">
            <label class="form-label">Department</label>
            <select name="department_id" class="form-select <?= has_validation_error('department_id') ? 'is-invalid' : '' ?>">
                <option value="">— None —</option>
                <?php foreach ($departments as $department): ?>
                    <option value="<?= (int)$department['id'] ?>"
                        <?= (string)old('department_id', $user['department_id'] ?? '') === (string)$department['id'] ? 'selected' : '' ?>>
                        <?= e($department['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Designation</label>
            <select name="designation_id" class="form-select <?= has_validation_error('designation_id') ? 'is-invalid' : '' ?>">
                <option value="">— None —</option>
                <?php foreach ($designations as $designation): ?>
                    <option value="<?= (int)$designation['id'] ?>"
                        <?= (string)old('designation_id', $user['designation_id'] ?? '') === (string)$designation['id'] ? 'selected' : '' ?>>
                        <?= e($designation['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="active" <?= (old('status', $user['status'] ?? 'active')) === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= (old('status', $user['status'] ?? '')) === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                <option value="pending" <?= (old('status', $user['status'] ?? '')) === 'pending' ? 'selected' : '' ?>>Pending</option>
            </select>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-md-6">
            <label class="form-label">Password <?= $isEdit ? '' : '<span class="text-danger">*</span>' ?></label>
            <input type="password" name="password" class="form-control <?= has_validation_error('password') ? 'is-invalid' : '' ?>"
                   autocomplete="new-password" <?= $isEdit ? '' : 'required' ?>>
            <div class="form-text">
                <?= $isEdit ? 'Leave blank to keep the current password.' : 'Temporary password. It will be included in the welcome email.' ?>
                Minimum <?= (int)$minPasswordLength ?> characters.
            </div>
            <?php if ($error = validation_error('password')): ?><div class="invalid-feedback d-block"><?= e($error) ?></div><?php endif; ?>
        </div>
        <div class="col-md-6">
            <label class="form-label">Roles</label>
            <div class="border rounded p-2 <?= has_validation_error('roles') ? 'border-danger' : '' ?>" style="max-height:190px;overflow-y:auto;">
                <?php foreach ($roles as $role): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="roles[]" value="<?= (int)$role['id'] ?>"
                               id="role-<?= (int)$role['id'] ?>" <?= in_array((int)$role['id'], array_map('intval', $userRoleIds), true) ? 'checked' : '' ?>>
                        <label class="form-check-label small" for="role-<?= (int)$role['id'] ?>">
                            <?= e($role['name']) ?>
                            <?php if (!empty($role['is_system'])): ?><span class="badge bg-warning-subtle text-dark border ms-1">System</span><?php endif; ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if ($error = validation_error('roles')): ?><div class="invalid-feedback d-block"><?= e($error) ?></div><?php endif; ?>
        </div>
    </div>

    <div class="mt-4 d-flex justify-content-end gap-2">
        <a href="<?= url('/users') ?>" class="btn btn-outline-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i><?= $isEdit ? 'Update User' : 'Create User' ?></button>
    </div>
</form>