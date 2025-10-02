<?php
declare(strict_types=1);

use App\Repositories\GuestRepository;

require __DIR__ . '/init.php';

$guestRepository = new GuestRepository(db());
$guests = $guestRepository->allWithStats(200);

$pageTitle = 'Guests';
$activeNav = 'guests';
$activeSubnav = null;

include __DIR__ . '/partials/header.php';
?>
<div class="table-wrapper">
    <table>
        <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Reservations</th>
            <th>Last Visit</th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($guests)): ?>
            <tr>
                <td colspan="5">No guests stored yet.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($guests as $guest): ?>
                <tr>
                    <td><?php echo htmlspecialchars(($guest['first_name'] ?? '') . ' ' . ($guest['last_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($guest['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($guest['phone'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo (int) ($guest['reservations_count'] ?? 0); ?></td>
                    <td><?php echo htmlspecialchars($guest['last_reservation_date'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php include __DIR__ . '/partials/footer.php';