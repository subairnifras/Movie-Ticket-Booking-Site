<?php
// Database setup script for Movie Booking Site
$host = "localhost";
$user = "root";
$pass = "";

// 1. Connect to MySQL server (without specifying DB)
$conn = new mysqli($host, $user, $pass);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected successfully to MySQL server.<br>";

// 2. Create Database
$sql = "CREATE DATABASE IF NOT EXISTS movie_booking_db";
if ($conn->query($sql) === TRUE) {
    echo "Database 'movie_booking_db' verified/created successfully.<br>";
} else {
    die("Error creating database: " . $conn->error);
}

// 3. Select Database
$conn->select_db("movie_booking_db");

// 4. Create Tables
$usersTable = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;";

$bookingsTable = "CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    show_date DATE NOT NULL,
    show_time VARCHAR(50) NOT NULL,
    seats VARCHAR(255) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;";

$paymentsTable = "CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    card_name VARCHAR(100) NOT NULL,
    card_number VARCHAR(20) NOT NULL,
    expiry_date VARCHAR(10) NOT NULL,
    cvv VARCHAR(5) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
) ENGINE=InnoDB;";

if ($conn->query($usersTable) === TRUE) {
    echo "Table 'users' verified/created successfully.<br>";
} else {
    echo "Error creating users table: " . $conn->error . "<br>";
}

if ($conn->query($bookingsTable) === TRUE) {
    echo "Table 'bookings' verified/created successfully.<br>";
} else {
    echo "Error creating bookings table: " . $conn->error . "<br>";
}

if ($conn->query($paymentsTable) === TRUE) {
    echo "Table 'payments' verified/created successfully.<br>";
} else {
    echo "Error creating payments table: " . $conn->error . "<br>";
}

// 5. Seed default admin account
$adminEmail = "admin@movies.com";
$adminCheck = $conn->query("SELECT id FROM users WHERE email = '$adminEmail'");
if ($adminCheck->num_rows == 0) {
    // Password: admin123
    $adminPassHash = '$2y$10$8xG5Wm.N2WOvNK9KfW41Eu55O9kv24KKbTKRPvq0UdDr1IY97eWzq';
    $insertAdmin = "INSERT INTO users (name, email, password, role) VALUES ('Admin User', '$adminEmail', '$adminPassHash', 'admin')";
    if ($conn->query($insertAdmin) === TRUE) {
        echo "Default admin account created: <b>$adminEmail</b> / <b>admin123</b><br>";
    } else {
        echo "Error seeding admin account: " . $conn->error . "<br>";
    }
} else {
    echo "Admin account already exists.<br>";
}

// 6. Seed default test user account
$userEmail = "user@movies.com";
$userCheck = $conn->query("SELECT id FROM users WHERE email = '$userEmail'");
if ($userCheck->num_rows == 0) {
    // Password: user123
    $userPassHash = '$2y$10$XzKum1vmDg6RVNx435JVIu.jQsUHHBgJ6A8y.62QIPInCafY/riES';
    $insertUser = "INSERT INTO users (name, email, password, role) VALUES ('Test User', '$userEmail', '$userPassHash', 'user')";
    if ($conn->query($insertUser) === TRUE) {
        echo "Default user account created: <b>$userEmail</b> / <b>user123</b><br>";
    } else {
        echo "Error seeding user account: " . $conn->error . "<br>";
    }
} else {
    echo "Test user account already exists.<br>";
}

$conn->close();
echo "<b>Setup completed!</b>";
?>
