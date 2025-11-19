<?php
session_start();
include 'config.php';

// Only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Get order ID from URL
if (!isset($_GET['id'])) {
    header("Location: orders.php");
    exit();
}
$order_id = intval($_GET['id']);

// Mark order as viewed
$conn->query("UPDATE orders SET viewed = 1 WHERE order_id = $order_id");

// Fetch order details
$query = "SELECT o.order_id, u.name as customer, u.email, o.total_price, o.status, o.payment_status, o.order_date 
          FROM orders o 
          JOIN users u ON o.user_id = u.user_id
          WHERE o.order_id = $order_id";
$order_details = $conn->query($query)->fetch_assoc();

if (!$order_details) {
    header("Location: orders.php");
    exit();
}

// Fetch order items
$items_query = "SELECT m.name as medicine_name, oi.quantity, oi.price 
                FROM order_items oi 
                JOIN medicines m ON oi.medicine_id = m.medicine_id
                WHERE oi.order_id = $order_id";
$order_items = $conn->query($items_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - Online Pharmacy</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .header {
            background: rgba(255,255,255,0.9);
            backdrop-filter: blur(10px);
            padding: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            border-radius: 15px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            animation: slideDown 0.5s ease;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .header h1 {
            color: #2980B9;
            font-size: 28px;
            font-weight: 300;
        }

        .back-link {
            background: linear-gradient(90deg, #2980B9, #3498db);
            color: #fff;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .back-link:hover {
            background: linear-gradient(90deg, #1f5f8b, #2980B9);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(41,128,185,0.3);
        }

        /* Order Details Section */
        .details {
            background: rgba(255,255,255,0.9);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            animation: slideUp 0.5s ease;
            max-width: 900px;
            margin: 0 auto;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .details h2 {
            color: #2980B9;
            margin-bottom: 20px;
            font-size: 24px;
            text-align: center;
            border-bottom: 2px solid #2980B9;
            padding-bottom: 10px;
        }

        .order-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
            padding: 20px;
            background: rgba(41, 128, 185, 0.05);
            border-radius: 10px;
        }

        .order-info p {
            font-size: 16px;
            color: #2c3e50;
            margin: 0;
        }

        .order-info strong {
            color: #2980B9;
        }

        .status-pending { color: #f39c12; font-weight: bold; }
        .status-completed { color: #27ae60; font-weight: bold; }
        .status-shipped { color: #3498db; font-weight: bold; }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .items-table th, .items-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }

        .items-table th {
            background: linear-gradient(90deg, #2980B9, #3498db);
            color: #fff;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .items-table tr:hover {
            background: rgba(41, 128, 185, 0.05);
        }

        .items-table td {
            color: #2c3e50;
        }

        .grand-total {
            font-size: 18px;
            font-weight: bold;
            color: #27ae60;
            text-align: right;
            margin-top: 15px;
            padding-right: 12px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            body { padding: 10px; }
            .header { flex-direction: column; gap: 10px; text-align: center; }
            .details { padding: 20px; }
            .order-info { grid-template-columns: 1fr; }
            .items-table th, .items-table td { padding: 10px 5px; font-size: 14px; }
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>📋 Order Details</h1>
        <a href="orders.php" class="back-link">⬅ Back to Orders</a>
    </div>

    <div class="details">
        <h2>Order #<?php echo $order_details['order_id']; ?></h2>
        
        <div class="order-info">
            <p><strong>Customer:</strong> <?php echo htmlspecialchars($order_details['customer']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($order_details['email']); ?></p>
            <p><strong>Order Date:</strong> <?php echo date('M j, Y H:i', strtotime($order_details['order_date'])); ?></p>
            <p><strong>Status:</strong> <span class="status-<?php echo strtolower($order_details['status']); ?>"><?php echo $order_details['status']; ?></span></p>
            <p><strong>Payment Status:</strong> <?php echo $order_details['payment_status']; ?></p>
            <p><strong>Total Amount:</strong> $<span style="font-weight: bold; color: #27ae60;"><?php echo number_format($order_details['total_price'], 2); ?></span></p>
        </div>

        <h3>Order Items</h3>
        <table class="items-table">
            <thead>
                <tr>
                    <th>Medicine</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $calculated_total = 0;
                $has_items = false;
                while ($item = $order_items->fetch_assoc()) {
                    $has_items = true;
                    $subtotal = $item['price'] * $item['quantity'];
                    $calculated_total += $subtotal;
                    echo "<tr>
                            <td>" . htmlspecialchars($item['medicine_name']) . "</td>
                            <td>" . $item['quantity'] . "</td>
                            <td>$" . number_format($item['price'], 2) . "</td>
                            <td>$" . number_format($subtotal, 2) . "</td>
                          </tr>";
                }
                if (!$has_items) {
                    echo "<tr><td colspan='4' style='text-align: center; color: #7f8c8d; padding: 20px;'>No items in this order.</td></tr>";
                }
                ?>
            </tbody>
        </table>
        <?php if ($has_items): ?>
            <div class="grand-total">
                Grand Total: $<?php echo number_format($calculated_total, 2); ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Add subtle interactivity: Animate table rows on load
        document.addEventListener('DOMContentLoaded', function() {
            const rows = document.querySelectorAll('.items-table tbody tr');
            rows.forEach((row, index) => {
                row.style.animationDelay = (index * 0.05) + 's';
                row.style.animation = 'slideUp 0.3s ease forwards';
            });
        });
    </script>

</body>
</html>