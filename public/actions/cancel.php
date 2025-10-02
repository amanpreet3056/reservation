<?php
declare(strict_types=1);

use App\Repositories\GuestRepository;
use App\Repositories\ReservationRepository;
use App\Repositories\TableRepository;
use App\Services\ReservationService;

require __DIR__ . '/../../app/bootstrap.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$token = filter_input(INPUT_GET, 'token', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

if (!$id || !$token) {
    echo 'Invalid reservation link.';
    exit;
}

$service = new ReservationService(
    new ReservationRepository(db()),
    new GuestRepository(db()),
    new TableRepository(db())
);
$reservation = $service->findActiveByToken($id, (string) $token);

if (!$reservation) {
    echo 'Invalid or already cancelled reservation.';
    exit;
}

if (is_post()) {
    if ($service->cancel($id)) {
        $adminEmail = setting('notification.admin_email', config('auth.default_super_admin.email', 'admin@yourdomain.com'));
        $subject = 'Reservation Cancelled - ' . $reservation['restaurant_name'];
        $body = 'Reservation #' . $id . ' for ' . $reservation['fname'] . ' ' . $reservation['lname'] . ' has been CANCELLED.';
        @mail($adminEmail, $subject, $body);

        echo '<h2>Booking Cancelled</h2>';
        exit;
    }

    echo '<h2>Unable to cancel booking right now.</h2>';
    exit;
}
?>
<h2>Cancel Reservation</h2>
<p>Reservation for <b><?php echo htmlspecialchars($reservation['fname'] . ' ' . $reservation['lname'], ENT_QUOTES, 'UTF-8'); ?></b></p>
<p>Date: <?php echo htmlspecialchars($reservation['reservation_date'], ENT_QUOTES, 'UTF-8'); ?>, Time: <?php echo htmlspecialchars($reservation['reservation_time'], ENT_QUOTES, 'UTF-8'); ?></p>
<form method="post">
    <button type="submit">Cancel Booking</button>
</form>