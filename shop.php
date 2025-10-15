<?php
session_start();
include 'config.php';

// Only customers can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'customer') {
    header("Location: login.php");
    exit();
}

// Handle Add to Cart
if (isset($_POST['add_to_cart'])) {
    $id = intval($_POST['medicine_id']);
    $qty = intval($_POST['quantity']);

    if (!isset($_SESSION['cart'])) { $_SESSION['cart'] = []; }
    if (!isset($_SESSION['cart'][$id])) { $_SESSION['cart'][$id] = 0; }

    $_SESSION['cart'][$id] += $qty;  // Add chosen quantity
    header("Location: shop.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Online Pharmacy - Shop</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            padding: 20px;
        }

        h1 {
            color: #2980B9;
            margin-bottom: 10px;
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

        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            margin-top: 20px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        th, td {
            padding: 12px;
            text-align: center;
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

        input[type="number"] {
            width: 60px;
            padding: 5px;
            border-radius: 6px;
            border: 1px solid #ccc;
            text-align: center;
        }

        button {
            background: #2980B9;
            color: #fff;
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            background: #1f5f8b;
        }

        span.out-of-stock {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h1>🩺 Welcome to the Online Pharmacy</h1>
    <p><a href="cart.php">🛒 View Cart</a> | <a href="logout.php">Logout</a></p>

    <table>
        <tr>
            <th>Medicine</th>
            <th>Price ($)</th>
            <th>Stock</th>
            <th>Action</th>
        </tr>
        <?php
        $result = $conn->query("SELECT * FROM medicines");
        while ($row = $result->fetch_assoc()) {
        ?>
            <tr>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo number_format($row['price'], 2); ?></td>
                <td><?php echo $row['quantity_in_stock']; ?></td>
                <td>
                    <?php if ($row['quantity_in_stock'] > 0) { ?>
                        <form method="POST" action="shop.php">
                            <input type="hidden" name="medicine_id" value="<?php echo $row['medicine_id']; ?>">
                            <input type="number" name="quantity" value="1" min="1" max="<?php echo $row['quantity_in_stock']; ?>" required>
                            <button type="submit" name="add_to_cart">Add to Cart</button>
                        </form>
                    <?php } else { ?>
                        <span class="out-of-stock">Out of Stock</span>
                    <?php } ?>
                </td>
            </tr>
        <?php } ?>
    </table>
</body>
</html>
