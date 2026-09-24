<?php /** @var array $notifications */ ?>
<?php \App\Core\View::share('headerActions', '
<form method="post" action="' . e(url('/notifications/read-all')) . '" class="d-inline">
    ' . csrf_field() . '
    <button class="btn btn-outline-secondary btn-sm"><i class="bi bi-check2-all me-1"></i>Mark All Read</button>
</form>'); ?>

<div class="card border-0 shadow-sm">
    <div class="list-group list-group-flush">
        <?php if (empty($notifications)): ?>
            <div class="list-group-item text-center text-muted py-5">
                <i class="bi bi-bell-slash d-block fs-2 mb-2"></i>
                You're all caught up — no notifications.
            </div>
        <?php else: foreach ($notifications as $notification): ?>
            <div class="list-group-item py-3 <?= empty($notification['is_read']) ? 'list-group-item-primary-subtle' : '' ?>">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fw-semibold"><?= e($notification['title']) ?>
                            <?php if (empty($notification['is_read'])): ?>
                                <span class="badge bg-primary ms-1">New</span>
                            <?php endif; ?>
                        </div>
                        <div class="small text-muted"><?= e($notification['message'] ?? '') ?></div>
                        <div class="small text-muted mt-1"><?= e($notification['type'] ?? '') ?> &middot; <?= e(time_ago($notification['created_at'])) ?></div>
                    </div>
                    <?php if (empty($notification['is_read'])): ?>
                        <form method="post" action="<?= url('/notifications/' . (int)$notification['id'] . '/read') ?>" class="ms-3">
                            <?= csrf_field() ?>
                            <button class="btn btn-sm btn-outline-secondary" type="submit"><i class="bi bi-check2"></i></button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; endif; ?>
    </div>

    <?php if (!empty($notifications)): ?>
        <div class="card-footer bg-white d-flex justify-content-between align-items-center">
            <small class="text-muted">Showing <?= count($notifications) ?> of <?= (int)$pagination['total'] ?> notifications</small>
            <?php require VIEW_PATH . '/partials/pagination.php'; ?>
        </div>
    <?php endif; ?>
</div>