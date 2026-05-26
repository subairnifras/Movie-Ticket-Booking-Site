<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        echo json_encode(["success" => false, "message" => "Please enter both email and password."]);
        exit;
    }

    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];

            echo json_encode([
                "success" => true, 
                "message" => "Login Successful!",
                "role" => $user['role']
            ]);
            exit;
        } else {
            echo json_encode(["success" => false, "message" => "Invalid password."]);
            exit;
        }
    } else {
        echo json_encode(["success" => false, "message" => "User not found."]);
        exit;
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
    exit;
}
?>
