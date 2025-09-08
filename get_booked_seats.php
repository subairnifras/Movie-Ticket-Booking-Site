<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $showDate = $conn->real_escape_string($_POST['showDate']);
    $showTime = $conn->real_escape_string($_POST['showTime']);

    $sql = "SELECT seats FROM bookings WHERE show_date='$showDate' AND show_time='$showTime'";
    $result = $conn->query($sql);

    $bookedSeats = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $seatsArray = explode(", ", $row['seats']);
            $bookedSeats = array_merge($bookedSeats, $seatsArray);
        }
    }

    echo json_encode($bookedSeats);
}
?>
