<?php
declare(strict_types=1);

use App\Repositories\GuestRepository;
use App\Repositories\ReservationRepository;
use App\Repositories\TableRepository;
use App\Services\ReservationService;

require __DIR__ . '/../../app/bootstrap.php';

if (!is_post()) {
    http_response_code(405);
    echo '<h2>Method Not Allowed</h2>';
    exit;
}

$input = filter_input_array(INPUT_POST, [
    'people'   => FILTER_SANITIZE_NUMBER_INT,
    'date'     => FILTER_UNSAFE_RAW,
    'time'     => FILTER_UNSAFE_RAW,
    'fname'    => FILTER_UNSAFE_RAW,
    'lname'    => FILTER_UNSAFE_RAW,
    'email'    => FILTER_SANITIZE_EMAIL,
    'phone'    => FILTER_UNSAFE_RAW,
    'purpose'  => FILTER_UNSAFE_RAW,
    'message'  => FILTER_UNSAFE_RAW,
    'table_id' => FILTER_SANITIZE_NUMBER_INT,
], false);

if (!$input) {
    http_response_code(400);
    echo '<h2>Invalid submission.</h2>';
    exit;
}

$required = ['people', 'date', 'time', 'fname', 'lname', 'email', 'phone'];
foreach ($required as $field) {
    if (empty($input[$field])) {
        http_response_code(422);
        echo '<h2>Missing required information.</h2>';
        exit;
    }
}

$people  = (int) $input['people'];
$date    = trim((string) $input['date']);
$time    = trim((string) $input['time']);
$fname   = trim((string) $input['fname']);
$lname   = trim((string) $input['lname']);
$email   = trim((string) $input['email']);
$phone   = trim((string) $input['phone']);
$purpose = trim((string) ($input['purpose'] ?? ''));
$message = trim((string) ($input['message'] ?? ''));
$tableId = $input['table_id'] !== null && $input['table_id'] !== '' ? (int) $input['table_id'] : null;

$restaurantName = setting('restaurant.name', config('app.name', "BachstA?b'l"));

$service = new ReservationService(
    new ReservationRepository(db()),
    new GuestRepository(db()),
    new TableRepository(db())
);

try {
    $reservation = $service->create([
        'restaurant_name'   => $restaurantName,
        'people'            => $people,
        'reservation_date'  => $date,
        'reservation_time'  => $time,
        'fname'             => $fname,
        'lname'             => $lname,
        'email'             => $email,
        'phone'             => $phone,
        'purpose'           => $purpose,
        'message'           => $message,
        'table_id'          => $tableId,
    ]);
} catch (\Throwable $e) {
    http_response_code(500);
    echo '<h2>Could not save your reservation.</h2>';
    exit;
}

[$subject, $body] = $service->buildConfirmationEmail($reservation);

$headers = 'From: no-reply@' . ($_SERVER['SERVER_NAME'] ?? 'reservation.local');

@mail($email, $subject, $body, $headers);

$safeEmail = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');

$bookAnotherUrl = htmlspecialchars(url('index.php'), ENT_QUOTES, 'UTF-8');

echo '<h2>Reservation Successful!</h2>';
echo '<p>We\'ve sent a confirmation to <b>' . $safeEmail . '</b>.</p>';
echo '<a href="' . $bookAnotherUrl . '">Book another</a>';