<?php /** @var array $backups */ ?>
<?php \App\Core\View::share('headerActions', '
<form method="post" action="' . e(url('/system/backups')) . '" class="d-inline">
    ' . csrf_field() . '
    <button class="btn btn-primary btn-sm"><i class="bi bi-cloud-arrow-down me-1"></i>Create Backup</button>
</form>'); ?>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
            <tr>
                <th class="ps-3">File</th>
                <th>Size</th>
                <th>Created</th>
                <th class="text-end pe-3">Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($backups)): ?>
                <tr><td colspan="4" class="text-center text-muted py-4">No backups yet. Click "Create Backup" to generate one.</td></tr>
            <?php else: foreach ($backups as $backup): ?>
                <tr>
                    <td class="ps-3"><code><?= e($backup['file']) ?></code></td>
                    <td class="small text-muted"><?= e(bytes_to_human((int)$backup['size'])) ?></td>
                    <td class="small text-muted"><?= e(time_ago($backup['date'])) ?></td>
                    <td class="text-end pe-3">
                        <div class="btn-group btn-group-sm">
                            <a class="btn btn-outline-primary" href="<?= url('/system/backups/' . rawurlencode($backup['file']) . '/download') ?>"><i class="bi bi-download me-1"></i>Download</a>
                            <form method="post" action="<?= url('/system/backups/' . rawurlencode($backup['file'])) ?>" class="d-inline" onsubmit="return confirm('Delete this backup file?')">
                                <?= csrf_field() ?>
                                <?= method_field('DELETE') ?>
                                <button class="btn btn-outline-danger" type="submit" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>