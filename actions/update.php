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
        $newDate = $_POST['date'];
        $newTime = $_POST['time'];

        $update = $conn->prepare("UPDATE reservations SET date=?, time=? WHERE id=?");
        $update->bind_param("ssi", $newDate, $newTime, $id);
        $update->execute();

        // Email admin
        $adminEmail = "admin@yourdomain.com";
        $subject = "Reservation Updated - ".$row['restaurant_name'];
        $body = "Reservation #$id for ".$row['fname']." ".$row['lname']." was UPDATED.\n".
                "New Date: $newDate\nNew Time: $newTime";
        mail($adminEmail, $subject, $body);

        echo "<h2>Booking Updated</h2>";
        exit;
    }
    ?>
    <h2>Update Reservation</h2>
    <form method="post">
        <label>Date</label>
        <input type="date" name="date" value="<?php echo $row['date']; ?>" required>

        <label>Time</label>
        <input type="text" name="time" value="<?php echo $row['time']; ?>" required>

        <button type="submit">Update Booking</button>
    </form>
    <?php
} else {
    echo "Invalid or cancelled reservation.";
}
?>
