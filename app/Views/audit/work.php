<?php /** @var array $user @var array $timeline @var array $logins */ ?>
<div class="row g-3">
    <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="avatar-xl rounded-circle mx-auto d-flex align-items-center justify-content-center bg-primary-subtle text-primary mb-3">
                    <?= e(strtoupper(mb_substr((string)$user['name'], 0, 1))) ?>
                </div>
                <h6 class="mb-0"><?= e($user['name']) ?></h6>
                <div class="text-muted small"><?= e($user['email']) ?></div>
                <div class="mt-2"><?= status_badge((string)$user['status']) ?></div>
            </div>
            <ul class="list-group list-group-flush small">
                <li class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Activity Events</span>
                    <span class="fw-semibold"><?= count($timeline) ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Successful Logins</span>
                    <span class="fw-semibold"><?= count($logins) ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Member Since</span>
                    <span><?= e(format_date($user['created_at'])) ?></span>
                </li>
            </ul>
        </div>
    </div>

    <div class="col-12 col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><h6 class="mb-0">Work Timeline</h6></div>
            <div class="card-body p-0">
                <?php if (empty($timeline)): ?>
                    <div class="text-muted small p-3">No tracked activity yet.</div>
                <?php else: ?>
                    <ul class="timeline list-unstyled mb-0">
                        <?php foreach ($timeline as $event): ?>
                            <li class="timeline-item">
                                <div class="timeline-dot"></div>
                                <div class="timeline-content">
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-semibold small"><?= e($event['description'] ?? $event['action']) ?></span>
                                        <span class="small text-muted text-nowrap ms-2"><?= e(time_ago($event['created_at'])) ?></span>
                                    </div>
                                    <div class="small text-muted">
                                        <code><?= e($event['action']) ?></code>
                                        <?php if (!empty($event['url'])): ?>
                                            <span class="ms-1">· <?= e($event['url']) ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($event['ip_address'])): ?>
                                            <span class="ms-1">· <?= e($event['ip_address']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>