<?php
$host = "localhost";
$user = "root"; // default in XAMPP
$pass = "";     // default in XAMPP
$dbname = "login_system";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$showDate = $_POST['showDate'];
$showTime = $_POST['showTime'];
$seats = $_POST['seats'];  // This will be like "A1, A2, B3"
$totalAmount = $_POST['totalAmount'];

$sql = "INSERT INTO bookings (showDate, showTime, seats, totalAmount) 
        VALUES ('$showDate', '$showTime', '$seats', '$totalAmount')";

if ($conn->query($sql) === TRUE) {
    echo "success";
} else {
    echo "Booking failed: " . $conn->error;
}

$conn->close();
?>
