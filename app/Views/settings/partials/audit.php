<?php /** @var array $audit */ ?>
<form method="post" action="<?= url('/settings/audit') ?>">
    <?= csrf_field() ?>

    <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" name="audit.log_activities" value="1" id="aud-activity" <?= !empty($audit['log_activities']) ? 'checked' : '' ?>>
        <label class="form-check-label" for="aud-activity">Log user activities</label>
    </div>

    <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" name="audit.track_page_views" value="1" id="aud-pages" <?= !empty($audit['track_page_views']) ? 'checked' : '' ?>>
        <label class="form-check-label" for="aud-pages">Track page views</label>
    </div>

    <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" name="audit.log_security_events" value="1" id="aud-security" <?= !empty($audit['log_security_events']) ? 'checked' : '' ?>>
        <label class="form-check-label" for="aud-security">Log security events</label>
    </div>

    <div class="d-flex justify-content-end mt-4">
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save Audit Settings</button>
    </div>
</form>