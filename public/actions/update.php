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
    echo 'Invalid or cancelled reservation.';
    exit;
}

if (is_post()) {
    $newDate = trim((string) ($_POST['date'] ?? ''));
    $newTime = trim((string) ($_POST['time'] ?? ''));

    if ($newDate === '' || $newTime === '') {
        echo '<p>Please provide both date and time.</p>';
    } else {
        if ($service->updateDateTime($id, $newDate, $newTime)) {
            $adminEmail = setting('notification.admin_email', config('auth.default_super_admin.email', 'admin@yourdomain.com'));
            $subject = 'Reservation Updated - ' . $reservation['restaurant_name'];
            $body = 'Reservation #' . $id . ' for ' . $reservation['fname'] . ' ' . $reservation['lname'] . " was UPDATED.\n" .
                    'New Date: ' . $newDate . "\nNew Time: " . $newTime;
            @mail($adminEmail, $subject, $body);

            echo '<h2>Booking Updated</h2>';
            exit;
        }

        echo '<p>Unable to update booking right now.</p>';
    }
}
?>
<h2>Update Reservation</h2>
<form method="post">
    <label>Date</label>
    <input type="date" name="date" value="<?php echo htmlspecialchars($reservation['reservation_date'], ENT_QUOTES, 'UTF-8'); ?>" required>

    <label>Time</label>
    <input type="text" name="time" value="<?php echo htmlspecialchars($reservation['reservation_time'], ENT_QUOTES, 'UTF-8'); ?>" required>

    <button type="submit">Update Booking</button>
</form>