<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized access. Please login first."]);
    exit;
}

$user_id = $_SESSION['user_id'];
$showDate = $conn->real_escape_string($_POST['showDate'] ?? '');
$showTime = $conn->real_escape_string($_POST['showTime'] ?? '');
$seats = $conn->real_escape_string($_POST['seats'] ?? ''); // comma-separated seats
$totalAmount = floatval($_POST['totalAmount'] ?? 0);

if (empty($showDate) || empty($showTime) || empty($seats) || $totalAmount <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid booking details."]);
    exit;
}

// Save as pending until payment is confirmed
$sql = "INSERT INTO bookings (user_id, show_date, show_time, seats, total_amount, status) 
        VALUES ('$user_id', '$showDate', '$showTime', '$seats', '$totalAmount', 'pending')";

if ($conn->query($sql) === TRUE) {
    echo json_encode([
        "success" => true, 
        "booking_id" => $conn->insert_id,
        "message" => "Booking created successfully."
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Booking failed: " . $conn->error]);
}

$conn->close();
?>
