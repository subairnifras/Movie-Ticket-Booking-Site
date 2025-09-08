<?php
$host = "localhost"; 
$user = "root"; // default XAMPP user
$pass = ""; // default password is empty
$dbname = "login_system";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}
?>
