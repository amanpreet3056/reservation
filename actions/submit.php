<?php
include __DIR__ . '/../db.php';

// Capture form data
$restaurant = "BachstA?b'l"; // static for now
$people     = $_POST['people'];
$date       = $_POST['date'];
$time       = $_POST['time'];
$fname      = $_POST['fname'];
$lname      = $_POST['lname'];
$email      = $_POST['email'];
$phone      = $_POST['phone'];
$purpose    = $_POST['purpose'];
$message    = $_POST['message'];

// Save into database
$stmt = $conn->prepare("INSERT INTO reservations 
    (restaurant_name, people, date, time, fname, lname, email, phone, purpose, message) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sissssssss", $restaurant, $people, $date, $time, $fname, $lname, $email, $phone, $purpose, $message);

if ($stmt->execute()) {
    // Send confirmation email
    $to = $email;
    $subject = "Reservation Confirmation - $restaurant";
    $cancel_link = "https://yourdomain.com/reservation-system/actions/cancel.php?id=" . $conn->insert_id . "&token=$token";
    $update_link = "https://yourdomain.com/reservation-system/actions/update.php?id=" . $conn->insert_id . "&token=$token";

$body = "Hello $fname,\n\nThank you for your reservation.\n\n".
        "Details:\n".
        "Date: $date\nTime: $time\nGuests: $people\n\n".
        "If you need, you can:\n".
        "dY`% Cancel Booking: $cancel_link\n".
        "dY`% Update Booking: $update_link\n\n".
        "We look forward to serving you!\n\n$restaurant";

    $headers = "From: no-reply@" . $_SERVER['SERVER_NAME'];

    mail($to, $subject, $body, $headers);

    echo "<h2>Reservation Successful!</h2>";
    echo "<p>We??Tve sent a confirmation to <b>$email</b>.</p>";
    echo "<a href='../index.php'>Book another</a>";

} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
