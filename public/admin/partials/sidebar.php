<?php
declare(strict_types=1);

$adminRole = $currentAdminRole ?? current_admin_role();
$canManageSettings = in_array($adminRole, ['super_admin', 'admin'], true);
$canSeeIntegration = $adminRole === 'super_admin';
?>
<aside class="sidebar">
    <div class="brand">
        <span>BR</span>
        <div>Bistro Admin</div>
    </div>
    <nav>
        <ul>
            <li class="<?php echo ($activeNav ?? '') === 'dashboard' ? 'active' : ''; ?>">
                <a href="<?php echo htmlspecialchars(admin_url('dashboard.php'), ENT_QUOTES, 'UTF-8'); ?>">Dashboard</a>
            </li>
            <li class="<?php echo ($activeNav ?? '') === 'reservations' ? 'active' : ''; ?>">
                <a href="<?php echo htmlspecialchars(admin_url('reservations.php'), ENT_QUOTES, 'UTF-8'); ?>">Reservations</a>
            </li>
            <li class="<?php echo ($activeNav ?? '') === 'tables' ? 'active' : ''; ?>">
                <a href="<?php echo htmlspecialchars(admin_url('table-plan.php'), ENT_QUOTES, 'UTF-8'); ?>">Table Plan</a>
            </li>
            <li class="<?php echo ($activeNav ?? '') === 'guests' ? 'active' : ''; ?>">
                <a href="<?php echo htmlspecialchars(admin_url('guests.php'), ENT_QUOTES, 'UTF-8'); ?>">Guests</a>
            </li>
            <?php if ($canManageSettings): ?>
            <li class="<?php echo ($activeNav ?? '') === 'settings' ? 'active' : ''; ?>">
                <a href="<?php echo htmlspecialchars(admin_url('settings/restaurant.php'), ENT_QUOTES, 'UTF-8'); ?>">Settings</a>
                <ul class="sub-nav">
                    <li class="<?php echo ($activeSubnav ?? '') === 'restaurant' ? 'active' : ''; ?>">
                        <a href="<?php echo htmlspecialchars(admin_url('settings/restaurant.php'), ENT_QUOTES, 'UTF-8'); ?>">Restaurant Name</a>
                    </li>
                    <li class="<?php echo ($activeSubnav ?? '') === 'tables' ? 'active' : ''; ?>">
                        <a href="<?php echo htmlspecialchars(admin_url('settings/tables.php'), ENT_QUOTES, 'UTF-8'); ?>">Tables</a>
                    </li>
                    <li class="<?php echo ($activeSubnav ?? '') === 'hours' ? 'active' : ''; ?>">
                        <a href="<?php echo htmlspecialchars(admin_url('settings/hours.php'), ENT_QUOTES, 'UTF-8'); ?>">Hours</a>
                    </li>
                    <li class="<?php echo ($activeSubnav ?? '') === 'limit' ? 'active' : ''; ?>">
                        <a href="<?php echo htmlspecialchars(admin_url('settings/reservation-limit.php'), ENT_QUOTES, 'UTF-8'); ?>">Reservation Limit</a>
                    </li>
                    <li class="<?php echo ($activeSubnav ?? '') === 'notification' ? 'active' : ''; ?>">
                        <a href="<?php echo htmlspecialchars(admin_url('settings/notifications.php'), ENT_QUOTES, 'UTF-8'); ?>">Notification</a>
                    </li>
                    <li class="<?php echo ($activeSubnav ?? '') === 'account' ? 'active' : ''; ?>">
                        <a href="<?php echo htmlspecialchars(admin_url('settings/account.php'), ENT_QUOTES, 'UTF-8'); ?>">Account</a>
                    </li>
                </ul>
            </li>
            <?php endif; ?>
            <?php if ($canSeeIntegration): ?>
            <li class="<?php echo ($activeNav ?? '') === 'integration' ? 'active' : ''; ?>">
                <a href="<?php echo htmlspecialchars(admin_url('integration/google-reviews.php'), ENT_QUOTES, 'UTF-8'); ?>">Integration</a>
            </li>
            <?php endif; ?>
            <li>
                <a href="<?php echo htmlspecialchars(admin_url('logout.php'), ENT_QUOTES, 'UTF-8'); ?>">Sign out</a>
            </li>
        </ul>
    </nav>
</aside>