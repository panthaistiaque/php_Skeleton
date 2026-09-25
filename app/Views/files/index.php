<?php /** @var array $files @var array $categories @var array $uploaders @var array $filters @var array $pagination @var array $appUser */ ?>
<?php if (user_can('files.upload')): ?>
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <h6 class="small text-uppercase text-muted mb-3">Upload a file</h6>
        <form method="post" action="<?= url('/files/upload') ?>" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Category</label>
                    <input type="text" name="category" class="form-control" list="upload-categories"
                           placeholder="e.g. Contracts, Reports, Invoices" maxlength="120"
                           value="<?= e(old('category', $filters['category'] ?: '')) ?>">
                    <datalist id="upload-categories">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= e($cat['category']) ?>"></option>
                        <?php endforeach; ?>
                    </datalist>
                    <div class="form-text">Optional &mdash; pick an existing one or type a new one.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">File <span class="text-danger">*</span></label>
                    <input type="file" name="upload" class="form-control" required
                           accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx,.xls,.xlsx,.csv,.txt,.zip,.json,.sql">
                    <div class="form-text">Allowed: images, PDF, Word, Excel, CSV, TXT, ZIP, JSON, SQL &middot; max 10 MB.</div>
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-end gap-2">
                <button class="btn btn-primary" type="submit"><i class="bi bi-upload me-1"></i>Upload File</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-body pb-3">
        <form method="get" action="<?= url('/files') ?>" class="row g-2 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label small mb-1">Category</label>
                <select name="category" class="form-select form-select-sm">
                    <option value="">All categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= e($cat['category']) ?>" <?= $filters['category'] === $cat['category'] ? 'selected' : '' ?>>
                            <?= e($cat['category']) ?> (<?= (int)$cat['total'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1">Uploaded From</label>
                <input type="text" name="date_from" class="form-control form-control-sm date-range-filter"
                       placeholder="e.g. 24 Sep 2026" maxlength="11" autocomplete="off"
                       value="<?= e($filters['date_from']) ?>">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1">Uploaded To</label>
                <input type="text" name="date_to" class="form-control form-control-sm date-range-filter"
                       placeholder="e.g. 24 Sep 2026" maxlength="11" autocomplete="off"
                       value="<?= e($filters['date_to']) ?>">
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label small mb-1">Uploaded By</label>
                <select name="user_id" class="form-select form-select-sm">
                    <option value="0">Anyone</option>
                    <?php foreach ($uploaders as $user): ?>
                        <option value="<?= (int)$user['id'] ?>" <?= (int)$filters['user_id'] === (int)$user['id'] ? 'selected' : '' ?>>
                            <?= e($user['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-2 d-flex gap-2">
                <button class="btn btn-outline-primary btn-sm flex-fill" type="submit"><i class="bi bi-funnel me-1"></i>Filter</button>
                <a class="btn btn-outline-secondary btn-sm flex-fill" href="<?= url('/files') ?>">Reset</a>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
            <tr>
                <th class="ps-3">File</th>
                <th>Category</th>
                <th>Type</th>
                <th>Size</th>
                <th>Uploaded By</th>
                <th>Uploaded</th>
                <th class="text-end pe-3">Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($files)): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">No files found.</td></tr>
            <?php else: foreach ($files as $file): ?>
                <tr>
                    <td class="ps-3">
                        <i class="bi bi-file-earmark-<?= e(in_array($file['extension'], ['jpg', 'jpeg', 'png', 'gif', 'webp'], true) ? 'image' : (in_array($file['extension'], ['pdf', 'doc', 'docx'], true) ? 'text' : 'arrow')) ?> me-2 text-muted"></i>
                        <span class="fw-semibold small"><?= e($file['original_name']) ?></span>
                    </td>
                    <td class="small">
                        <?= !empty($file['category']) ? '<span class="badge bg-primary-subtle text-primary border"><i class="bi bi-tag me-1"></i>' . e($file['category']) . '</span>' : '<span class="text-muted">—</span>' ?>
                    </td>
                    <td class="small"><span class="badge bg-light border text-dark"><?= e(strtoupper($file['extension'] ?? '')) ?></span></td>
                    <td class="small text-muted"><?= e(bytes_to_human((int)$file['size'])) ?></td>
                    <td class="small">
                        <?= e($file['user_name'] ?? '—') ?>
                        <?php if ((int)($file['user_id'] ?? 0) === (int)($appUser['id'] ?? 0)): ?>
                            <span class="badge bg-secondary-subtle text-secondary border">you</span>
                        <?php endif; ?>
                    </td>
                    <td class="small text-muted"><?= e(format_datetime($file['created_at'])) ?></td>
                    <td class="text-end pe-3">
                        <div class="btn-group btn-group-sm">
                            <a class="btn btn-outline-primary" href="<?= url('/files/' . (int)$file['id'] . '/download') ?>"><i class="bi bi-download me-1"></i>Download</a>
                            <?php if (user_can('files.delete') && ((int)($file['user_id'] ?? 0) === (int)($appUser['id'] ?? 0) || \App\Models\User::isSuperAdmin((int)($appUser['id'] ?? 0)))): ?>
                                <form method="post" action="<?= url('/files/' . (int)$file['id']) ?>" class="d-inline" onsubmit="return confirm('Delete this file permanently? Only the uploader can delete.')">
                                    <?= csrf_field() ?>
                                    <?= method_field('DELETE') ?>
                                    <button class="btn btn-outline-danger" type="submit" title="Delete"><i class="bi bi-trash"></i></button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <?php if (!empty($files)): ?>
        <div class="card-footer bg-white d-flex justify-content-between align-items-center">
            <small class="text-muted">Showing <?= count($files) ?> of <?= (int)$pagination['total'] ?> files</small>
            <?php $paginationQuery = ['category' => $filters['category'] ?: null, 'date_from' => $filters['date_from'] ?: null, 'date_to' => $filters['date_to'] ?: null, 'user_id' => $filters['user_id'] ?: null]; require VIEW_PATH . '/partials/pagination.php'; unset($paginationQuery); ?>
        </div>
    <?php endif; ?>
</div>