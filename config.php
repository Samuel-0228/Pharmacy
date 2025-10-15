<?php
// Database configuration
$host = "localhost";      // Server
$user = "root";           // Default XAMPP MySQL user
$pass = "";               // Leave empty if no password
$db   = "pharmacy_system"; // Database name you created

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// echo "Database connected successfully!";
?>
