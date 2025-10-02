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
$errorMessage = null;

if (is_post()) {
    $name = trim((string) ($_POST['restaurant_name'] ?? ''));
    $email = trim((string) ($_POST['contact_email'] ?? ''));
    $phone = trim((string) ($_POST['contact_phone'] ?? ''));
    $address = trim((string) ($_POST['restaurant_address'] ?? ''));

    if ($name === '') {
        $errorMessage = 'Restaurant name is required.';
    } else {
        $settingService->set('restaurant.name', $name, 'restaurant');
        $settingService->set('restaurant.contact_email', $email, 'restaurant');
        $settingService->set('restaurant.phone', $phone, 'restaurant');
        $settingService->set('restaurant.address', $address, 'restaurant');

        flash('settings_success', 'Restaurant settings saved.');
        redirect('admin/settings/restaurant.php');
    }
}

$restaurantName = $settingService->get('restaurant.name', config('app.name', "BachstA?b'l"));
$contactEmail = $settingService->get('restaurant.contact_email', '');
$contactPhone = $settingService->get('restaurant.phone', '');
$restaurantAddress = $settingService->get('restaurant.address', '');

$pageTitle = 'Settings';
$activeNav = 'settings';
$activeSubnav = 'restaurant';

include __DIR__ . '/../partials/header.php';
?>
<div class="form-card">
    <h2>Restaurant Details</h2>
    <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <?php if ($errorMessage): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <form method="post">
        <div class="form-grid">
            <div>
                <label for="restaurant_name">Restaurant Name</label>
                <input type="text" name="restaurant_name" id="restaurant_name" value="<?php echo htmlspecialchars($restaurantName, ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
            <div>
                <label for="contact_email">Contact Email</label>
                <input type="email" name="contact_email" id="contact_email" value="<?php echo htmlspecialchars($contactEmail, ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div>
                <label for="contact_phone">Contact Phone</label>
                <input type="text" name="contact_phone" id="contact_phone" value="<?php echo htmlspecialchars($contactPhone, ENT_QUOTES, 'UTF-8'); ?>">
            </div>
        </div>
        <div>
            <label for="restaurant_address">Address</label>
            <textarea name="restaurant_address" id="restaurant_address" rows="3"><?php echo htmlspecialchars($restaurantAddress, ENT_QUOTES, 'UTF-8'); ?></textarea>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Save changes</button>
        </div>
    </form>
</div>
<?php include __DIR__ . '/../partials/footer.php';