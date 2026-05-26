<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized access. Please login first."]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $bookingId = intval($_POST['bookingId'] ?? 0);
    $cardName = $conn->real_escape_string(trim($_POST['cardName'] ?? ''));
    $cardNumber = $conn->real_escape_string(trim($_POST['cardNumber'] ?? ''));
    $expiryDate = $conn->real_escape_string(trim($_POST['expiryDate'] ?? ''));
    $cvv = $conn->real_escape_string(trim($_POST['cvv'] ?? ''));

    if ($bookingId <= 0 || empty($cardName) || empty($cardNumber) || empty($expiryDate) || empty($cvv)) {
        echo json_encode(["success" => false, "message" => "All payment fields are required."]);
        exit;
    }

    // Verify booking belongs to this user
    $user_id = $_SESSION['user_id'];
    $bookingCheck = $conn->query("SELECT id FROM bookings WHERE id = $bookingId AND user_id = $user_id");
    
    if ($bookingCheck->num_rows == 0) {
        echo json_encode(["success" => false, "message" => "Booking not found or access denied."]);
        exit;
    }

    // Start Transaction
    $conn->begin_transaction();

    try {
        // 1. Save payment info
        $sqlPayment = "INSERT INTO payments (booking_id, card_name, card_number, expiry_date, cvv) 
                       VALUES ($bookingId, '$cardName', '$cardNumber', '$expiryDate', '$cvv')";
        
        if (!$conn->query($sqlPayment)) {
            throw new Exception("Failed to insert payment record: " . $conn->error);
        }

        // 2. Set booking status to paid
        $sqlUpdateBooking = "UPDATE bookings SET status = 'paid' WHERE id = $bookingId";
        
        if (!$conn->query($sqlUpdateBooking)) {
            throw new Exception("Failed to update booking status: " . $conn->error);
        }

        $conn->commit();
        echo json_encode(["success" => true, "message" => "Payment successful!"]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
}

$conn->close();
?>
