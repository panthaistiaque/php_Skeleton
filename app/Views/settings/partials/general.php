<?php /** @var array $general */ ?>
<?php $timezones = [
    'UTC' => 'UTC', 'Africa/Cairo' => 'Africa/Cairo', 'Africa/Johannesburg' => 'Africa/Johannesburg',
    'America/New_York' => 'America/New York', 'America/Chicago' => 'America/Chicago', 'America/Denver' => 'America/Denver',
    'America/Los_Angeles' => 'America/Los Angeles', 'America/Sao_Paulo' => 'America/Sao Paulo', 'America/Toronto' => 'America/Toronto',
    'Asia/Dubai' => 'Asia/Dubai', 'Asia/Kolkata' => 'Asia/Kolkata', 'Asia/Singapore' => 'Asia/Singapore',
    'Asia/Tokyo' => 'Asia/Tokyo', 'Australia/Sydney' => 'Australia/Sydney', 'Europe/Berlin' => 'Europe/Berlin',
    'Europe/London' => 'Europe/London', 'Europe/Paris' => 'Europe/Paris', 'Pacific/Auckland' => 'Pacific/Auckland',
]; ?>
<form method="post" action="<?= url('/settings/general') ?>">
    <?= csrf_field() ?>

    <div class="mb-3">
        <label class="form-label">Timezone <span class="text-danger">*</span></label>
        <select name="general.timezone" class="form-select">
            <?php foreach ($timezones as $value => $label): ?>
                <option value="<?= e($value) ?>" <?= ($general['timezone'] ?? 'UTC') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Date Format <span class="text-danger">*</span></label>
            <input type="text" name="general.date_format" class="form-control" required value="<?= e(old('general.date_format', $general['date_format'] ?? 'Y-m-d')) ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Datetime Format <span class="text-danger">*</span></label>
            <input type="text" name="general.datetime_format" class="form-control" required value="<?= e(old('general.datetime_format', $general['datetime_format'] ?? 'Y-m-d H:i')) ?>">
        </div>
    </div>

    <div class="mb-4 mt-3">
        <label class="form-label">Records Per Page <span class="text-danger">*</span></label>
        <input type="number" name="general.records_per_page" class="form-control" style="max-width:180px;" min="5" max="200"
               value="<?= e(old('general.records_per_page', $general['records_per_page'] ?? 20)) ?>">
    </div>

    <div class="d-flex justify-content-end">
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save General Settings</button>
    </div>
</form>