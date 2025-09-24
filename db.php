<?php
$host = "localhost";     // database host
$user = "your_db_user";  // database username
$pass = "your_db_pass";  // database password
$db   = "reservation_system"; // database name

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
