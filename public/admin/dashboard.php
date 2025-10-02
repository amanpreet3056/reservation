<?php
declare(strict_types=1);

use App\Repositories\GuestRepository;
use App\Repositories\ReservationRepository;
use App\Repositories\TableRepository;
use App\Services\ReservationService;
use App\Services\TableService;

require __DIR__ . '/init.php';

$reservationRepository = new ReservationRepository(db());
$guestRepository = new GuestRepository(db());
$tableRepository = new TableRepository(db());

$reservationService = new ReservationService($reservationRepository, $guestRepository, $tableRepository);
$tableService = new TableService($tableRepository);

$totalReservations = $reservationService->countAll();
$upcomingReservations = $reservationService->countUpcoming();
$totalTables = $tableService->count();
$totalGuests = $guestRepository->count();
$recentReservations = $reservationService->recent(10);

$pageTitle = 'Dashboard';
$activeNav = 'dashboard';
$activeSubnav = null;

include __DIR__ . '/partials/header.php';
?>
<div class="cards-grid">
    <div class="card">
        <h3>Total Reservations</h3>
        <p class="metric"><?php echo number_format($totalReservations); ?></p>
    </div>
    <div class="card">
        <h3>Upcoming Reservations</h3>
        <p class="metric"><?php echo number_format($upcomingReservations); ?></p>
    </div>
    <div class="card">
        <h3>Registered Guests</h3>
        <p class="metric"><?php echo number_format($totalGuests); ?></p>
    </div>
    <div class="card">
        <h3>Tables</h3>
        <p class="metric"><?php echo number_format($totalTables); ?></p>
    </div>
</div>

<div class="table-wrapper">
    <table>
        <thead>
        <tr>
            <th>#</th>
            <th>Guest</th>
            <th>Email</th>
            <th>Date</th>
            <th>Time</th>
            <th>Guests</th>
            <th>Table</th>
            <th>Status</th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($recentReservations)): ?>
            <tr>
                <td colspan="8">No reservations yet.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($recentReservations as $reservation): ?>
                <tr>
                    <td><?php echo (int) $reservation['id']; ?></td>
                    <td><?php echo htmlspecialchars($reservation['fname'] . ' ' . $reservation['lname'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($reservation['email'], ENT_QUOTES, 'UTF-8'); ?></td>
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