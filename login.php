<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            echo "<script>
                alert('Login Successful!');
                window.location='index.html';
            </script>";
        } else {
            echo "<script>alert('Invalid Password'); window.location='login.html';</script>";
        }
    } else {
        echo "<script>alert('User not found'); window.location='login.html';</script>";
    }
}
?>
