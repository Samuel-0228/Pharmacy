<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'customer') {
    header("Location: login.php");
    exit();
}

$message = "";

// Place Order → redirect to payment
if (isset($_POST['checkout']) && !empty($_SESSION['cart'])) {
    $total = 0;

    foreach ($_SESSION['cart'] as $id => $qty) {
        $res = $conn->query("SELECT price, quantity_in_stock, name FROM medicines WHERE medicine_id=$id");
        $med = $res->fetch_assoc();

        // Check stock
        if ($qty > $med['quantity_in_stock']) {
            $message = "⚠️ Not enough stock for " . $med['name'];
            header("Location: cart.php?error=" . urlencode($message));
            exit();
        }

        $total += $med['price'] * $qty;
    }

    // Store for payment
    $_SESSION['order_total'] = $total;
    $_SESSION['order_cart'] = $_SESSION['cart'];

    header("Location: payment.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Cart - Online Pharmacy</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            padding: 30px;
        }

        h1 {
            color: #2980B9;
            margin-bottom: 15px;
        }

        a {
            color: #2980B9;
            text-decoration: none;
            font-weight: bold;
            margin-right: 15px;
        }

        a:hover {
            text-decoration: underline;
        }

        .message {
            color: red;
            font-weight: bold;
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            margin-bottom: 20px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        th, td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #2980B9;
            color: #fff;
            font-size: 16px;
        }

        tr:nth-child(even) {
            background: #f9f9f9;
        }

        tr:hover {
            background: #eaf1fb;
        }

        strong {
            color: #2980B9;
        }

        button {
            padding: 12px 25px;
            background: #2980B9;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            background: #1f5f8b;
        }
    </style>
</head>
<body>
    <h1>🛒 Your Cart</h1>
    <p><a href="shop.php">⬅ Continue Shopping</a> | <a href="logout.php">Logout</a></p>

    <?php if (!empty($message)) echo "<p class='message'>$message</p>"; ?>

    <?php if (empty($_SESSION['cart'])) { ?>
        <p>Your cart is empty.</p>
    <?php } else { ?>
        <form method="POST">
            <table>
                <tr>
                    <th>Medicine</th>
                    <th>Quantity</th>
                    <th>Price ($)</th>
                    <th>Subtotal ($)</th>
                </tr>
                <?php
                $total = 0;
                foreach ($_SESSION['cart'] as $id => $qty) {
                    $res = $conn->query("SELECT name, price FROM medicines WHERE medicine_id=$id");
                    $med = $res->fetch_assoc();
                    $subtotal = $med['price'] * $qty;
                    $total += $subtotal;
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($med['name']); ?></td>
                        <td><?php echo $qty; ?></td>
                        <td><?php echo number_format($med['price'],2); ?></td>
                        <td><?php echo number_format($subtotal,2); ?></td>
                    </tr>
                <?php } ?>
                <tr>
                    <td colspan="3"><strong>Total</strong></td>
                    <td><strong><?php echo number_format($total,2); ?></strong></td>
                </tr>
            </table>
            <button type="submit" name="checkout">Place Order</button>
        </form>
    <?php } ?>
</body>
</html>
