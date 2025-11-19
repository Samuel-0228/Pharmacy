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
    $query = "SELECT o.order_id, u.name as customer, o.total_price, o.status, o.payment_status, o.order_date 
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Management - Online Pharmacy</title>
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

        /* Orders Table Section */
        .table-section {
            background: rgba(255,255,255,0.9);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            animation: slideUp 0.5s ease;
            overflow-x: auto;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .table-section h2 {
            margin-bottom: 20px;
            color: #2980B9;
            font-size: 24px;
        }

        table {
            width: 100%;
            min-width: 700px;
            border-collapse: collapse;
            background: transparent;
        }

        table th, table td {
            padding: 15px 10px;
            text-align: left;
            border-bottom: 1px solid rgba(0,0,0,0.1);
            font-size: 14px;
        }

        table th {
            background: linear-gradient(90deg, #2980B9, #3498db);
            color: #fff;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        table tr {
            transition: all 0.3s ease;
            cursor: pointer;
        }

        table tr:hover {
            background: rgba(41, 128, 185, 0.1);
            transform: scale(1.01);
        }

        table td {
            color: #2c3e50;
        }

        .status-pending { color: #f39c12; font-weight: bold; }
        .status-completed { color: #27ae60; font-weight: bold; }
        .status-shipped { color: #3498db; font-weight: bold; }

        .view-btn {
            padding: 8px 16px;
            background: linear-gradient(90deg, #2980B9, #3498db);
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
        }

        .view-btn:hover {
            background: linear-gradient(90deg, #1f5f8b, #2980B9);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(41,128,185,0.3);
        }

        /* Details Section */
        .details {
            background: rgba(255,255,255,0.9);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            animation: slideUp 0.5s ease 0.2s both;
            max-width: 800px;
        }

        .details h2 {
            color: #2980B9;
            margin-bottom: 20px;
            font-size: 24px;
            text-align: center;
            border-bottom: 2px solid #2980B9;
            padding-bottom: 10px;
        }

        .details p {
            font-size: 16px;
            margin-bottom: 10px;
            color: #2c3e50;
        }

        .details h3 {
            color: #2980B9;
            margin: 20px 0 10px 0;
            font-size: 20px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
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
        }

        .items-table tr:hover {
            background: rgba(41, 128, 185, 0.05);
        }

        .grand-total {
            font-size: 18px;
            font-weight: bold;
            color: #27ae60;
            text-align: right;
            margin-top: 10px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            body { padding: 10px; }
            .header { flex-direction: column; gap: 10px; text-align: center; }
            .table-section, .details { padding: 20px; }
            table { min-width: 500px; }
            table th, table td { padding: 10px 5px; font-size: 12px; }
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>📦 Orders Management</h1>
        <a href="dashboard.php" class="back-link">⬅ Back to Dashboard</a>
    </div>

    <!-- All Orders Table -->
    <div class="table-section">
        <h2>All Orders</h2>
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = $conn->query("SELECT o.order_id, u.name, o.total_price, o.status, o.payment_status, o.order_date 
                                        FROM orders o 
                                        JOIN users u ON o.user_id = u.user_id
                                        ORDER BY o.order_date DESC");
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $status_class = 'status-' . strtolower($row['status']);
                        echo "<tr>
                                <td>#" . $row['order_id'] . "</td>
                                <td>" . htmlspecialchars($row['name']) . "</td>
                                <td>$" . number_format($row['total_price'], 2) . "</td>
                                <td class='$status_class'>" . $row['status'] . "</td>
                                <td>" . $row['payment_status'] . "</td>
                                <td>" . date('M j, Y', strtotime($row['order_date'])) . "</td>
                                <td><a href='orders.php?order_id=" . $row['order_id'] . "' class='view-btn'>View Details</a></td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='7' style='text-align: center; color: #7f8c8d; padding: 40px;'>No orders found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <?php if ($order_details) { ?>
        <div class="details">
            <h2>Order #<?php echo $order_details['order_id']; ?> Details</h2>
            <p><strong>Customer:</strong> <?php echo htmlspecialchars($order_details['customer']); ?></p>
            <p><strong>Date:</strong> <?php echo date('M j, Y H:i', strtotime($order_details['order_date'])); ?></p>
            <p><strong>Status:</strong> <span class="status-<?php echo strtolower($order_details['status']); ?>"><?php echo $order_details['status']; ?></span></p>
            <p><strong>Payment Status:</strong> <?php echo $order_details['payment_status']; ?></p>
            <p><strong>Total:</strong> $<span class="grand-total"><?php echo number_format($order_details['total_price'], 2); ?></span></p>

            <h3>Ordered Items</h3>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Medicine</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $grand_total = 0;
                    $has_items = false;
                    while ($item = $order_items->fetch_assoc()) {
                        $has_items = true;
                        $subtotal = $item['price'] * $item['quantity'];
                        $grand_total += $subtotal;
                        echo "<tr>
                                <td>" . htmlspecialchars($item['name']) . "</td>
                                <td>" . $item['quantity'] . "</td>
                                <td>$" . number_format($item['price'], 2) . "</td>
                                <td>$" . number_format($subtotal, 2) . "</td>
                              </tr>";
                    }
                    if (!$has_items) {
                        echo "<tr><td colspan='4' style='text-align: center; color: #7f8c8d;'>No items in this order.</td></tr>";
                    }
                    ?>
                </tbody>
                <?php if ($has_items): ?>
                    <tfoot>
                        <tr>
                            <td colspan="3" style="text-align: right; font-weight: bold;">Grand Total</td>
                            <td style="font-weight: bold; color: #27ae60;">$<?php echo number_format($grand_total, 2); ?></td>
                        </tr>
                    </tfoot>
                <?php endif; ?>
            </table>
        </div>
    <?php } ?>

    <script>
        // Add subtle interactivity: Animate table rows on load
        document.addEventListener('DOMContentLoaded', function() {
            const rows = document.querySelectorAll('table tbody tr');
            rows.forEach((row, index) => {
                row.style.animationDelay = (index * 0.05) + 's';
                row.style.animation = 'slideUp 0.3s ease forwards';
            });
        });
    </script>

</body>
</html>