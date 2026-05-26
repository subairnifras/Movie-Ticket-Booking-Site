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
        
        $isValidPassword = false;

        if (password_verify($password, $user['password'])) {
            $isValidPassword = true;
        } elseif ($password === $user['password']) {
            // Legacy plaintext password support: verify and migrate to hashed password.
            $isValidPassword = true;
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $updateSql = "UPDATE users SET password='$newHash' WHERE id=" . intval($user['id']);
            $conn->query($updateSql);
        }

        if ($isValidPassword) {
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
        }

        echo json_encode(["success" => false, "message" => "Invalid password."]);
        exit;
    } else {
        echo json_encode(["success" => false, "message" => "User not found."]);
        exit;
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
    exit;
}
?>
