<?php
session_start();
include 'config.php';

// Only customers
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'customer') {
    header("Location: login.php");
    exit();
}

// Check if cart and total exist
if (!isset($_SESSION['order_cart']) || !isset($_SESSION['order_total']) || empty($_SESSION['order_cart'])) {
    echo "<p style='color:red; font-weight:bold;'>⚠️ No order found. Please add items to your cart first.</p>";
    echo "<p><a href='shop.php'>Back to Shop</a></p>";
    exit();
}

$cart = $_SESSION['order_cart'];
$total = $_SESSION['order_total'];

// Process payment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['pay'])) {
    $user_id = $_SESSION['user_id'];

    // Insert order
    $conn->query("INSERT INTO orders (user_id, total_price, status, payment_status, order_date)
                  VALUES ($user_id, $total, 'Pending', 'Paid', NOW())");
    $order_id = $conn->insert_id;

    // Insert order items and update stock
    foreach ($cart as $med_id => $qty) {
        $res = $conn->query("SELECT price, quantity_in_stock, name FROM medicines WHERE medicine_id=$med_id");
        $med = $res->fetch_assoc();
        $price = $med['price'];

        $conn->query("INSERT INTO order_items (order_id, medicine_id, quantity, price)
                      VALUES ($order_id, $med_id, $qty, $price)");

        // Update stock
        $new_stock = $med['quantity_in_stock'] - $qty;
        $conn->query("UPDATE medicines SET quantity_in_stock=$new_stock WHERE medicine_id=$med_id");
    }

    // Clear cart session
    unset($_SESSION['cart']);
    unset($_SESSION['order_cart']);
    unset($_SESSION['order_total']);

    $success = true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment - Online Pharmacy</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            padding: 30px;
        }

        .container {
            max-width: 600px;
            margin: auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            text-align: center;
        }

        h1 {
            color: #2980B9;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #2980B9;
            color: #fff;
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

        .success-message {
            color: green;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .back-link {
            display: inline-block;
            margin-top: 15px;
            text-decoration: none;
            color: #2980B9;
            font-weight: bold;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Payment</h1>

        <?php if(isset($success) && $success) { ?>
            <p class="success-message">✅ Payment successful! Your order has been placed.</p>
            <a href="shop.php" class="back-link">Back to Shop</a>
        <?php } else { ?>
            <table>
                <tr>
                    <th>Medicine</th>
                    <th>Quantity</th>
                    <th>Price ($)</th>
                    <th>Subtotal ($)</th>
                </tr>
                <?php foreach($cart as $med_id => $qty) {
                    $res = $conn->query("SELECT name, price FROM medicines WHERE medicine_id=$med_id");
                    $med = $res->fetch_assoc();
                    $subtotal = $med['price'] * $qty;
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($med['name']); ?></td>
                        <td><?php echo $qty; ?></td>
                        <td><?php echo number_format($med['price'],2); ?></td>
                        <td><?php echo number_format($subtotal,2); ?></td>
                    </tr>
                <?php } ?>
                <tr>
                    <th colspan="3">Total</th>
                    <th><?php echo number_format($total,2); ?></th>
                </tr>
            </table>

            <form method="POST">
                <button type="submit" name="pay">Pay Now</button>
            </form>
        <?php } ?>
    </div>
</body>
</html>
