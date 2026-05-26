<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string(trim($_POST['name']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $password = $_POST['password'];

    // Basic server-side validation
    if (empty($name) || empty($email) || empty($password)) {
        echo "<script>alert('Please fill in all fields.'); window.history.back();</script>";
        exit;
    }

    // Check if email already exists
    $checkEmail = $conn->query("SELECT id FROM users WHERE email='$email'");
    if ($checkEmail->num_rows > 0) {
        echo "<script>alert('Email is already registered. Please sign in.'); window.location='login.html';</script>";
        exit;
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$passwordHash', 'user')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Account created successfully! Please sign in.'); window.location='login.html';</script>";
    } else {
        $error = $conn->real_escape_string($conn->error);
        echo "<script>alert('Registration failed: $error'); window.history.back();</script>";
    }
}
?>
