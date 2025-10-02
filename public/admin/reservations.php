<?php
declare(strict_types=1);

use App\Repositories\GuestRepository;
use App\Repositories\ReservationRepository;
use App\Repositories\TableRepository;
use App\Services\ReservationService;

require __DIR__ . '/init.php';

$reservationRepository = new ReservationRepository(db());
$guestRepository = new GuestRepository(db());
$tableRepository = new TableRepository(db());

$reservationService = new ReservationService($reservationRepository, $guestRepository, $tableRepository);
$reservations = $reservationService->all(200);

$pageTitle = 'Reservations';
$activeNav = 'reservations';
$activeSubnav = null;

include __DIR__ . '/partials/header.php';
?>
<div class="table-wrapper">
    <table>
        <thead>
        <tr>
            <th>#</th>
            <th>Guest</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Date</th>
            <th>Time</th>
            <th>Guests</th>
            <th>Table</th>
            <th>Status</th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($reservations)): ?>
            <tr>
                <td colspan="9">No reservations recorded yet.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($reservations as $reservation): ?>
                <tr>
                    <td><?php echo (int) $reservation['id']; ?></td>
                    <td><?php echo htmlspecialchars($reservation['fname'] . ' ' . $reservation['lname'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($reservation['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($reservation['phone'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($reservation['reservation_date'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($reservation['reservation_time'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo (int) $reservation['people']; ?></td>
                    <td><?php echo htmlspecialchars($reservation['table_name'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <?php
                        $status = $reservation['status'];
                        $statusClass = match ($status) {
                            'active' => 'success',
                            'completed' => 'warning',
                            default => 'danger',
                        };
                        ?>
                        <span class="status-pill <?php echo $statusClass; ?>">
                            <?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php include __DIR__ . '/partials/footer.php';