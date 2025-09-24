<?php
include __DIR__ . '/../db.php';

$id    = $_GET['id'];
$token = $_GET['token'];

$stmt = $conn->prepare("SELECT * FROM reservations WHERE id=? AND token=? AND status='active'");
$stmt->bind_param("is", $id, $token);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $update = $conn->prepare("UPDATE reservations SET status='cancelled' WHERE id=?");
        $update->bind_param("i", $id);
        $update->execute();

        // Email admin
        $adminEmail = "admin@yourdomain.com";
        $subject = "Reservation Cancelled - ".$row['restaurant_name'];
        $body = "Reservation #$id for ".$row['fname']." ".$row['lname']." has been CANCELLED.";
        mail($adminEmail, $subject, $body);

        echo "<h2>Booking Cancelled</h2>";
        exit;
    }
    ?>
    <h2>Cancel Reservation</h2>
    <p>Reservation for <b><?php echo $row['fname']." ".$row['lname']; ?></b></p>
    <p>Date: <?php echo $row['date']; ?>, Time: <?php echo $row['time']; ?></p>
    <form method="post">
        <button type="submit">Cancel Booking</button>
    </form>
    <?php
} else {
    echo "Invalid or already cancelled reservation.";
}
?>
