<?php
session_start();
include 'config.php';

// Only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Mark new orders as viewed
$conn->query("UPDATE orders SET viewed = 1 WHERE viewed = 0");

// Check if admin clicked "view details"
$order_details = null;
if (isset($_GET['order_id'])) {
    $order_id = intval($_GET['order_id']);
    $query = "SELECT o.order_id, u.name as customer, o.total_price, o.status, o.order_date 
              FROM orders o 
              JOIN users u ON o.user_id = u.user_id
              WHERE o.order_id = $order_id";
    $order_details = $conn->query($query)->fetch_assoc();

    $items_query = "SELECT m.name, oi.quantity, oi.price 
                    FROM order_items oi 
                    JOIN medicines m ON oi.medicine_id = m.medicine_id
                    WHERE oi.order_id = $order_id";
    $order_items = $conn->query($items_query);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Orders Management</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; padding: 20px; }
        table { width: 100%; border-collapse: collapse; background: #fff; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: center; }
        th { background: green; color: white; }
        .details { background: #fff; padding: 15px; border: 1px solid #ccc; margin-top: 20px; }
    </style>
</head>
<body>
    <h1>Orders Management</h1>
    <p><a href="dashboard.php">⬅ Back to Dashboard</a></p>

    <h2>All Orders</h2>
    <table>
        <tr>
            <th>Order ID</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th><th>Action</th>
        </tr>
        <?php
        $result = $conn->query("SELECT o.order_id, u.name, o.total_price, o.status, o.order_date 
                                FROM orders o 
                                JOIN users u ON o.user_id = u.user_id
                                ORDER BY o.order_date DESC");
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>".$row['order_id']."</td>
                    <td>".$row['name']."</td>
                    <td>".$row['total_price']."</td>
                    <td>".$row['status']."</td>
                    <td>".$row['order_date']."</td>
                    <td><a href='orders.php?order_id=".$row['order_id']."'>View Details</a></td>
                  </tr>";
        }
        ?>
    </table>

    <?php if ($order_details) { ?>
        <div class="details">
            <h2>Order #<?php echo $order_details['order_id']; ?> Details</h2>
            <p><strong>Customer:</strong> <?php echo $order_details['customer']; ?></p>
            <p><strong>Date:</strong> <?php echo $order_details['order_date']; ?></p>
            <p><strong>Status:</strong> <?php echo $order_details['status']; ?></p>
            <p><strong>Total:</strong> <?php echo $order_details['total_price']; ?></p>

            <h3>Ordered Items</h3>
            <table>
                <tr>
                    <th>Medicine</th><th>Quantity</th><th>Price</th><th>Subtotal</th>
                </tr>
                <?php
                $grand_total = 0;
                while ($item = $order_items->fetch_assoc()) {
                    $subtotal = $item['price'] * $item['quantity'];
                    $grand_total += $subtotal;
                    echo "<tr>
                            <td>".$item['name']."</td>
                            <td>".$item['quantity']."</td>
                            <td>".$item['price']."</td>
                            <td>".$subtotal."</td>
                          </tr>";
                }
                echo "<tr>
                        <td colspan='3'><strong>Grand Total</strong></td>
                        <td><strong>$grand_total</strong></td>
                      </tr>";
                ?>
            </table>
        </div>
    <?php } ?>
</body>
</html>
