<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Pharmacy System</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; margin: 0; padding: 0; }
        header { background: green; color: white; padding: 15px; }
        nav a { color: white; margin: 0 10px; text-decoration: none; font-weight: bold; }
        nav a:hover { text-decoration: underline; }
        main { padding: 20px; }
    </style>
</head>
<body>
    <header>
        <h1>Pharmacy System</h1>
        <nav>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') { ?>
                <a href="dashboard.php">Dashboard</a>
                <a href="medicines.php">Medicines</a>
                <a href="sales.php">Sales</a>
                <a href="reports.php">Reports</a>
            <?php } elseif (isset($_SESSION['role']) && $_SESSION['role'] == 'customer') { ?>
                <a href="shop.php">Shop</a>
                <a href="cart.php">Cart</a>
            <?php } ?>
            <?php if (isset($_SESSION['user_id'])) { ?>
                <a href="logout.php">Logout</a>
            <?php } else { ?>
                <a href="login.php">Login</a>
            <?php } ?>
        </nav>
    </header>
    <main>
