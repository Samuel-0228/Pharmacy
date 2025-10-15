<?php
session_start();
include 'config.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if user exists
    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();

        $stored = $row['password'];

        // Determine if the stored password is hashed (bcrypt)
        if (strlen($stored) == 60 && preg_match('/^\$2[ayb]\$/', $stored)) {
            // New account with password_hash
            $login_ok = password_verify($password, $stored);
        } else {
            // Old account with md5
            $login_ok = ($stored === md5($password));
        }

        if ($login_ok) {
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['name'] = $row['name'];

            if ($row['role'] == 'admin') {
                header("Location: dashboard.php");
            } else {
                header("Location: shop.php");
            }
            exit();
        } else {
            $message = "❌ Invalid password.";
        }
    } else {
        $message = "❌ No account found with that email.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Online Pharmacy</title>
    <style>
        /* Reset */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Arial', sans-serif; }

        body {
            background: linear-gradient(135deg, #6DD5FA, #2980B9);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-container {
            background: #fff;
            padding: 40px 30px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            width: 350px;
            text-align: center;
        }

        h2 {
            margin-bottom: 25px;
            color: #2980B9;
        }

        input[type="email"], input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 6px;
            border: 1px solid #ccc;
            transition: all 0.3s ease;
        }

        input:focus {
            border-color: #2980B9;
            box-shadow: 0 0 5px rgba(41,128,185,0.5);
            outline: none;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #2980B9;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 10px;
            transition: all 0.3s ease;
        }

        button:hover {
            background: #1f5f8b;
        }

        .message {
            margin: 15px 0;
            color: red;
            font-size: 14px;
        }

        .register-link {
            margin-top: 10px;
            font-size: 14px;
        }

        .register-link a {
            color: #2980B9;
            text-decoration: none;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

    </style>
</head>
<body>
    <div class="login-container">
        <h2>🩺 Online Pharmacy Login</h2>

        <?php if($message != "") { echo "<p class='message'>$message</p>"; } ?>

        <form method="POST">
            <input type="email" name="email" placeholder="Email Address" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>

        <p class="register-link">
            Don't have an account? <a href="register.php">Register here</a>
        </p>
    </div>
</body>
</html>