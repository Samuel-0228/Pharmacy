<?php
session_start();
include 'config.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch dynamic stats (assuming database tables: medicines, orders, users)
if ($_SESSION['role'] == 'admin') {
    // New orders count
    $res = $conn->query("SELECT COUNT(*) as new_orders FROM orders WHERE viewed = 0");
    $row = $res->fetch_assoc();
    $new_orders = $row['new_orders'];

    // Total Medicines
    $total_medicines_res = $conn->query("SELECT COUNT(*) as count FROM medicines");
    $total_medicines = $total_medicines_res->fetch_assoc()['count'];

    // Total Orders
    $total_orders_res = $conn->query("SELECT COUNT(*) as count FROM orders");
    $total_orders = $total_orders_res->fetch_assoc()['count'];

    // Total Customers (only role = 'customer')
    $total_customers_res = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'customer'");
    $total_customers = $total_customers_res->fetch_assoc()['count'];

    // Revenue (sum of totals for completed/paid orders)
    $revenue_res = $conn->query("SELECT SUM(total_price) as revenue FROM orders WHERE status = 'Completed' AND payment_status = 'Paid'");
    $revenue = $revenue_res->fetch_assoc()['revenue'] ?? 0;
    $revenue_formatted = '$' . number_format($revenue, 0);

    // Recent Orders (last 5)
    $recent_orders_res = $conn->query("
        SELECT o.order_id, u.name as customer, o.total_price, o.status, o.payment_status, o.order_date 
        FROM orders o 
        JOIN users u ON o.user_id = u.user_id 
        ORDER BY o.order_date DESC 
        LIMIT 5
    ");
    $recent_orders = $recent_orders_res->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Online Pharmacy</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="dashboard-page">

    <div class="sidebar">
        <h2>Admin Panel</h2>
        <a href="dashboard.php">📊 Dashboard</a>
        <a href="medicines.php">💊 Manage Medicines</a>
        <a href="orders.php">📦 Manage Orders</a>
        <a href="users.php">👥 Manage Users</a>
        <a href="logout.php">🚪 Logout</a>
    </div>

    <div class="main-content">
        <div class="topbar">
            <div>
                <h1>Welcome, Admin</h1>
                <?php if ($new_orders > 0): ?>
                    <div class="notification">New Orders: <?php echo $new_orders; ?></div>
                <?php endif; ?>
            </div>
            <span>🕒 <?php echo date("Y-m-d H:i"); ?></span>
        </div>

        <!-- Stats Cards -->
        <div class="cards">
            <div class="card">
                <h3>Total Medicines</h3>
                <p><?php echo $total_medicines ?? 0; ?></p>
            </div>
            <div class="card">
                <h3>Total Orders</h3>
                <p><?php echo $total_orders ?? 0; ?></p>
            </div>
            <div class="card">
                <h3>Total Customers</h3>
                <p><?php echo $total_customers ?? 0; ?></p>
            </div>
            <div class="card">
                <h3>Revenue</h3>
                <p><?php echo $revenue_formatted; ?></p>
            </div>
        </div>

        <!-- Recent Orders Table -->
        <div class="table-section">
            <h2>Recent Orders</h2>
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
                    <?php if (isset($recent_orders) && count($recent_orders) > 0): ?>
                        <?php foreach ($recent_orders as $order): ?>
                            <tr>
                                <td>#<?php echo $order['order_id']; ?></td>
                                <td><?php echo htmlspecialchars($order['customer']); ?></td>
                                <td>$<?php echo number_format($order['total_price'], 2); ?></td>
                                <td class="status-<?php echo strtolower($order['status']); ?>"><?php echo $order['status']; ?></td>
                                <td><?php echo $order['payment_status']; ?></td>
                                <td><?php echo date('M j, Y', strtotime($order['order_date'])); ?></td>
                                <td><a href="order_details.php?id=<?php echo $order['order_id']; ?>" class="view-btn">View</a></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: #7f8c8d; padding: 40px;">No recent orders found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Interactive: View order
        function viewOrder(id) {
            if (confirm('View order details for ID ' + id + '?')) {
                window.location.href = 'order_details.php?id=' + id;
            }
        }

        // Add subtle interactivity: Animate cards on load
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.card');
            cards.forEach((card, index) => {
                card.style.animationDelay = (index * 0.1) + 's';
                card.style.animation = 'slideDown 0.5s ease forwards';
            });

            const rows = document.querySelectorAll('table tbody tr');
            rows.forEach((row, index) => {
                row.style.animationDelay = (index * 0.05) + 's';
                row.style.animation = 'slideUp 0.3s ease forwards';
            });
        });
    </script>

</body>
</html>