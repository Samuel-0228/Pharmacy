<?php
session_start();
include 'config.php';

// If already logged in, redirect
if (isset($_SESSION['user_id'])) {
    // Customers go to shop, admins to dashboard
    if ($_SESSION['role'] == 'customer') {
        header("Location: shop.php");
    } else {
        header("Location: dashboard.php");
    }
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // secure hashing
    $role = "customer";

    // Check if email already exists
    $check = $conn->query("SELECT * FROM users WHERE email='$email'");
    if ($check->num_rows > 0) {
        $message = "⚠️ Email already registered. <a href='login.php'>Login here</a>";
    } else {
        $sql = "INSERT INTO users (name, email, password, role) 
                VALUES ('$name', '$email', '$password', '$role')";
        if ($conn->query($sql)) {
            $message = "✅ Registration successful! <a href='login.php'>Login here</a>";
        } else {
            $message = "❌ Error: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register - Online Pharmacy</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; padding: 50px; }
        form { background: #fff; padding: 20px; width: 350px; margin: auto; border-radius: 10px; }
        input { width: 100%; padding: 10px; margin: 8px 0; }
        button { width: 100%; padding: 10px; background: green; color: white; border: none; cursor: pointer; }
        button:hover { background: darkgreen; }
        .msg { margin-top: 10px; color: red; text-align: center; }
        h2 { text-align: center; }
    </style>
</head>
<body>
    <form method="POST">
        <h2>📝 Register</h2>
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Register</button>
        <p class="msg"><?php echo $message; ?></p>
        <p style="text-align:center;">Already have an account? <a href="login.php">Login here</a></p>
    </form>
</body>
</html>
