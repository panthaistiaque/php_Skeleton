<?php /** @var string $tab @var array $app @var array $org @var array $general @var array $smtp @var array $security @var array $audit */ ?>
<ul class="nav nav-pills nav-fill mb-4 gap-1">
    <?php
    $tabs = [
        'general'      => ['label' => 'General', 'icon' => 'bi-sliders'],
        'application'  => ['label' => 'Application', 'icon' => 'bi-window'],
        'organization' => ['label' => 'Organization', 'icon' => 'bi-building'],
        'security'     => ['label' => 'Security', 'icon' => 'bi-shield-lock'],
        'smtp'         => ['label' => 'SMTP', 'icon' => 'bi-envelope-paper'],
        'audit'        => ['label' => 'Audit', 'icon' => 'bi-clipboard-check'],
        'menus'        => ['label' => 'Menus', 'icon' => 'bi-list-ul'],
    ];
    foreach ($tabs as $key => $info): ?>
        <li class="nav-item">
            <a class="nav-link <?= $tab === $key ? 'active' : '' ?>" href="<?= e(url($key === 'menus' ? '/settings/menus' : '/settings?tab=' . $key)) ?>">
                <i class="bi <?= e($info['icon']) ?> me-1"></i><?= e($info['label']) ?>
            </a>
        </li>
    <?php endforeach; ?>
</ul>

<?php if ($tab === 'application' || $tab === 'general' || $tab === 'organization' || $tab === 'security' || $tab === 'smtp' || $tab === 'audit'): ?>
<div class="card border-0 shadow-sm" style="max-width:820px;">
    <div class="card-header bg-white"><h6 class="mb-0"><?= e($tabs[$tab]['label']) ?> Settings</h6></div>
    <div class="card-body">
        <?php require VIEW_PATH . '/settings/partials/' . $tab . '.php'; ?>
    </div>
</div>
<?php endif; ?>