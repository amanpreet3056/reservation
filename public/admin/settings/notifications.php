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
    $emailEnabled = isset($_POST['email_enabled']) ? '1' : '0';
    $smsEnabled = isset($_POST['sms_enabled']) ? '1' : '0';
    $adminEmail = trim((string) ($_POST['admin_email'] ?? ''));
    $smsNumber = trim((string) ($_POST['sms_number'] ?? ''));

    $settingService->set('notification.email_enabled', $emailEnabled, 'notification');
    $settingService->set('notification.sms_enabled', $smsEnabled, 'notification');
    $settingService->set('notification.admin_email', $adminEmail, 'notification');
    $settingService->set('notification.sms_number', $smsNumber, 'notification');

    flash('settings_success', 'Notification preferences saved.');
    redirect('admin/settings/notifications.php');
}

$emailEnabled = $settingService->get('notification.email_enabled', '1');
$smsEnabled = $settingService->get('notification.sms_enabled', '0');
$adminEmail = $settingService->get('notification.admin_email', config('auth.default_super_admin.email', 'admin@yourdomain.com'));
$smsNumber = $settingService->get('notification.sms_number', '');

$pageTitle = 'Settings';
$activeNav = 'settings';
$activeSubnav = 'notification';

include __DIR__ . '/../partials/header.php';
?>
<div class="form-card">
    <h2>Notifications</h2>
    <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <form method="post">
        <div>
            <label style="display:flex; align-items:center; gap:10px; margin-bottom:12px;">
                <input type="checkbox" name="email_enabled" <?php echo ($emailEnabled === '1') ? 'checked' : ''; ?>>
                Email confirmations for guests
            </label>
            <label style="display:flex; align-items:center; gap:10px; margin-bottom:20px;">
                <input type="checkbox" name="sms_enabled" <?php echo ($smsEnabled === '1') ? 'checked' : ''; ?>>
                SMS alerts for the team
            </label>
        </div>
        <div class="form-grid">
            <div>
                <label for="admin_email">Admin Notification Email</label>
                <input type="email" name="admin_email" id="admin_email" value="<?php echo htmlspecialchars($adminEmail, ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div>
                <label for="sms_number">SMS Notification Number</label>
                <input type="text" name="sms_number" id="sms_number" value="<?php echo htmlspecialchars($smsNumber, ENT_QUOTES, 'UTF-8'); ?>" placeholder="+49...">
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Save notifications</button>
        </div>
    </form>
</div>
<?php include __DIR__ . '/../partials/footer.php';