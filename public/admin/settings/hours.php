<?php
declare(strict_types=1);

use App\Repositories\SettingRepository;
use App\Services\SettingService;

require __DIR__ . '/../init.php';

if (!in_array(($currentAdminRole ?? current_admin_role()), ['super_admin', 'admin'], true)) {
    redirect('admin/dashboard.php');
}

$settingService = new SettingService(new SettingRepository(db()));
$successMessage = flash('settings_success');

if (is_post()) {
    $open = trim((string) ($_POST['opening_time'] ?? '08:00'));
    $close = trim((string) ($_POST['closing_time'] ?? '22:00'));
    $timezone = trim((string) ($_POST['timezone'] ?? 'Europe/Berlin'));
    $closedDays = trim((string) ($_POST['closed_days'] ?? ''));

    $settingService->set('hours.open', $open, 'hours');
    $settingService->set('hours.close', $close, 'hours');
    $settingService->set('hours.timezone', $timezone, 'hours');
    $settingService->set('hours.closed_days', $closedDays, 'hours');

    flash('settings_success', 'Opening hours saved.');
    redirect('admin/settings/hours.php');
}

$openingTime = $settingService->get('hours.open', '08:00');
$closingTime = $settingService->get('hours.close', '22:00');
$timezone = $settingService->get('hours.timezone', date_default_timezone_get());
$closedDays = $settingService->get('hours.closed_days', '');

$pageTitle = 'Settings';
$activeNav = 'settings';
$activeSubnav = 'hours';

include __DIR__ . '/../partials/header.php';
?>
<div class="form-card">
    <h2>Hours</h2>
    <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <form method="post">
        <div class="form-grid">
            <div>
                <label for="opening_time">Opening Time</label>
                <input type="time" name="opening_time" id="opening_time" value="<?php echo htmlspecialchars($openingTime, ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div>
                <label for="closing_time">Closing Time</label>
                <input type="time" name="closing_time" id="closing_time" value="<?php echo htmlspecialchars($closingTime, ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div>
                <label for="timezone">Timezone</label>
                <input type="text" name="timezone" id="timezone" value="<?php echo htmlspecialchars($timezone, ENT_QUOTES, 'UTF-8'); ?>">
            </div>
        </div>
        <div>
            <label for="closed_days">Closed Days</label>
            <textarea name="closed_days" id="closed_days" rows="3" placeholder="e.g. Monday, Tuesday"><?php echo htmlspecialchars($closedDays, ENT_QUOTES, 'UTF-8'); ?></textarea>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Save hours</button>
        </div>
    </form>
</div>
<?php include __DIR__ . '/../partials/footer.php';