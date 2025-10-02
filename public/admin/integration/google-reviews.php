<?php
declare(strict_types=1);

use App\Repositories\SettingRepository;
use App\Services\SettingService;

require __DIR__ . '/../init.php';

if (($currentAdminRole ?? current_admin_role()) !== 'super_admin') {
    redirect('admin/dashboard.php');
}

$settingService = new SettingService(new SettingRepository(db()));
$successMessage = flash('integration_success');

if (is_post()) {
    $placeId = trim((string) ($_POST['place_id'] ?? ''));
    $apiKey = trim((string) ($_POST['api_key'] ?? ''));
    $widgetCode = trim((string) ($_POST['widget_code'] ?? ''));

    $settingService->set('integration.google_reviews.place_id', $placeId, 'integration_google_reviews');
    $settingService->set('integration.google_reviews.api_key', $apiKey, 'integration_google_reviews');
    $settingService->set('integration.google_reviews.widget_code', $widgetCode, 'integration_google_reviews');

    flash('integration_success', 'Google Reviews integration saved.');
    redirect('admin/integration/google-reviews.php');
}

$placeId = $settingService->get('integration.google_reviews.place_id', '');
$apiKey = $settingService->get('integration.google_reviews.api_key', '');
$widgetCode = $settingService->get('integration.google_reviews.widget_code', '');

$pageTitle = 'Integration';
$activeNav = 'integration';
$activeSubnav = null;

include __DIR__ . '/../partials/header.php';
?>
<div class="form-card">
    <h2>Google Reviews</h2>
    <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <p>Configure Google Reviews to embed live guest feedback on your public site.</p>
    <form method="post">
        <div class="form-grid">
            <div>
                <label for="place_id">Google Place ID</label>
                <input type="text" name="place_id" id="place_id" value="<?php echo htmlspecialchars($placeId, ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div>
                <label for="api_key">Google API Key</label>
                <input type="text" name="api_key" id="api_key" value="<?php echo htmlspecialchars($apiKey, ENT_QUOTES, 'UTF-8'); ?>">
            </div>
        </div>
        <div>
            <label for="widget_code">Widget Embed Code</label>
            <textarea name="widget_code" id="widget_code" rows="5" placeholder="Paste any widget embed snippet here"><?php echo htmlspecialchars($widgetCode, ENT_QUOTES, 'UTF-8'); ?></textarea>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Save integration</button>
        </div>
    </form>
</div>
<?php include __DIR__ . '/../partials/footer.php';