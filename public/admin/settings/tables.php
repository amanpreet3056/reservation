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
    $autoAssign = isset($_POST['auto_assign']) ? '1' : '0';
    $maxParty = (int) ($_POST['max_party_size'] ?? 6);
    $buffer = (int) ($_POST['buffer_minutes'] ?? 15);

    $settingService->set('tables.auto_assign', $autoAssign, 'tables');
    $settingService->set('tables.max_party_size', (string) max(1, $maxParty), 'tables');
    $settingService->set('tables.buffer_minutes', (string) max(0, $buffer), 'tables');

    flash('settings_success', 'Table preferences saved.');
    redirect('admin/settings/tables.php');
}

$autoAssign = $settingService->get('tables.auto_assign', '1');
$maxPartySize = (int) $settingService->get('tables.max_party_size', 6);
$bufferMinutes = (int) $settingService->get('tables.buffer_minutes', 15);

$pageTitle = 'Settings';
$activeNav = 'settings';
$activeSubnav = 'tables';

include __DIR__ . '/../partials/header.php';
?>
<div class="form-card">
    <h2>Table Preferences</h2>
    <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <form method="post">
        <div class="form-grid">
            <div>
                <label for="max_party_size">Maximum Party Size</label>
                <input type="number" name="max_party_size" id="max_party_size" min="1" value="<?php echo $maxPartySize; ?>">
            </div>
            <div>
                <label for="buffer_minutes">Turnover Buffer (minutes)</label>
                <input type="number" name="buffer_minutes" id="buffer_minutes" min="0" value="<?php echo $bufferMinutes; ?>">
            </div>
            <div>
                <label>&nbsp;</label>
                <label style="display:flex; align-items:center; gap:8px;">
                    <input type="checkbox" name="auto_assign" <?php echo ($autoAssign === '1') ? 'checked' : ''; ?>>
                    Auto assign tables for new reservations
                </label>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Save preferences</button>
        </div>
    </form>
</div>
<?php include __DIR__ . '/../partials/footer.php';