<?php
declare(strict_types=1);

use App\Repositories\AdminRepository;
use App\Services\AdminService;

require __DIR__ . '/../init.php';

$adminRepository = new AdminRepository(db());
$adminService = new AdminService($adminRepository);

$successMessage = flash('account_success');
$errorMessage = flash('account_error');

$currentAdmin = $adminRepository->findById((int) current_admin_id());

if (is_post() && $currentAdmin) {
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirm = (string) ($_POST['password_confirmation'] ?? '');

    try {
        $adminService->updateProfile((int) $currentAdmin['id'], $name, $email);
        $adminService->updatePassword((int) $currentAdmin['id'], $password, $confirm);

        $_SESSION['admin_name'] = $name;
        flash('account_success', 'Account details updated.');
        redirect('admin/settings/account.php');
    } catch (\InvalidArgumentException $e) {
        $errorMessage = $e->getMessage();
    } catch (\Throwable $e) {
        $errorMessage = 'Unable to update your account right now.';
    }
}

if ($currentAdmin) {
    $currentName = $currentAdmin['name'] ?? '';
    $currentEmail = $currentAdmin['email'] ?? '';
    $currentRole = $currentAdmin['role'] ?? '';
} else {
    $currentName = $currentAdminName ?? '';
    $currentEmail = '';
    $currentRole = current_admin_role() ?? '';
}

$pageTitle = 'Settings';
$activeNav = 'settings';
$activeSubnav = 'account';

include __DIR__ . '/../partials/header.php';
?>
<div class="form-card">
    <h2>Account</h2>
    <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <?php if (!empty($errorMessage)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <form method="post">
        <div class="form-grid">
            <div>
                <label for="name">Display Name</label>
                <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($currentName, ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
            <div>
                <label for="email">Email</label>
                <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($currentEmail, ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
            <div>
                <label>Role</label>
                <input type="text" value="<?php echo htmlspecialchars(ucfirst($currentRole), ENT_QUOTES, 'UTF-8'); ?>" disabled>
            </div>
        </div>
        <div class="form-grid">
            <div>
                <label for="password">New Password</label>
                <input type="password" name="password" id="password" placeholder="Leave blank to keep current password">
            </div>
            <div>
                <label for="password_confirmation">Confirm Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation">
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Save account</button>
        </div>
    </form>
</div>
<?php include __DIR__ . '/../partials/footer.php';