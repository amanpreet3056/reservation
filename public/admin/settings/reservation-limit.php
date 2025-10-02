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
    $perSlot = (int) ($_POST['per_slot'] ?? 10);
    $perDay = (int) ($_POST['per_day'] ?? 50);

    $settingService->set('reservation.limit.per_slot', (string) max(1, $perSlot), 'reservation');
    $settingService->set('reservation.limit.per_day', (string) max(1, $perDay), 'reservation');

    flash('settings_success', 'Reservation limits saved.');
    redirect('admin/settings/reservation-limit.php');
}

$perSlotLimit = (int) $settingService->get('reservation.limit.per_slot', 10);
$perDayLimit = (int) $settingService->get('reservation.limit.per_day', 50);

$pageTitle = 'Settings';
$activeNav = 'settings';
$activeSubnav = 'limit';

include __DIR__ . '/../partials/header.php';
?>
<div class="form-card">
    <h2>Reservation Limits</h2>
    <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <form method="post">
        <div class="form-grid">
            <div>
                <label for="per_slot">Per Time Slot</label>
                <input type="number" name="per_slot" id="per_slot" min="1" value="<?php echo $perSlotLimit; ?>">
            </div>
            <div>
                <label for="per_day">Per Day</label>
                <input type="number" name="per_day" id="per_day" min="1" value="<?php echo $perDayLimit; ?>">
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Save limits</button>
        </div>
    </form>
</div>
<?php include __DIR__ . '/../partials/footer.php';