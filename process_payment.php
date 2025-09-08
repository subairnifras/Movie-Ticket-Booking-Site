<?php
// Database connection settings
$host = "localhost";   // XAMPP runs on localhost
$user = "root";        // default XAMPP user
$pass = "";            // default is blank
$db   = "payments_db"; // create this database in phpMyAdmin

// Connect to database
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Collect form data
$cardName   = $_POST['cardName'];
$cardNumber = $_POST['cardNumber'];
$expiryDate = $_POST['expiryDate'];
$cvv        = $_POST['cvv'];

// Insert into database
$sql = "INSERT INTO payments (card_name, card_number, expiry_date, cvv) 
        VALUES ('$cardName', '$cardNumber', '$expiryDate', '$cvv')";

if ($conn->query($sql) === TRUE) {
    echo "<h2>Payment Successful!</h2><p>Thank you, $cardName.</p>";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
