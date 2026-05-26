<?php
include 'db.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $showDate = $conn->real_escape_string($_POST['showDate'] ?? '');
    $showTime = $conn->real_escape_string($_POST['showTime'] ?? '');

    // Return seats for active bookings (not cancelled)
    $sql = "SELECT seats FROM bookings WHERE show_date='$showDate' AND show_time='$showTime' AND status != 'cancelled'";
    $result = $conn->query($sql);

    $bookedSeats = [];
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            // Split seats e.g. "A1, A2" by comma
            $seatsArray = array_map('trim', explode(",", $row['seats']));
            $bookedSeats = array_merge($bookedSeats, $seatsArray);
        }
    }

    // Return unique seats list
    echo json_encode(array_values(array_unique($bookedSeats)));
} else {
    echo json_encode([]);
}
$conn->close();
?>
