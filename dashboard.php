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
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            height: 100%;
            background: linear-gradient(180deg, #2980B9 0%, #1f5f8b 100%);
            color: #fff;
            display: flex;
            flex-direction: column;
            padding-top: 20px;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            transition: width 0.3s ease;
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 1.5em;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .sidebar a {
            color: #fff;
            text-decoration: none;
            padding: 15px 25px;
            display: block;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .sidebar a::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(255,255,255,0.1);
            transition: left 0.3s ease;
        }

        .sidebar a:hover::before {
            left: 0;
        }

        .sidebar a:hover {
            background: rgba(255,255,255,0.1);
            transform: translateX(5px);
        }

        /* Main content */
        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: margin-left 0.3s ease;
            max-width: calc(100vw - 250px);
        }

        .topbar {
            background: rgba(255,255,255,0.9);
            backdrop-filter: blur(10px);
            padding: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 15px;
            margin-bottom: 20px;
            animation: slideDown 0.5s ease;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .topbar h1 {
            font-size: 28px;
            color: #2980B9;
            font-weight: 300;
        }

        .notification {
            background: #e74c3c;
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: bold;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .topbar span {
            font-size: 14px;
            color: #7f8c8d;
        }

        /* Stats Cards */
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .card {
            background: rgba(255,255,255,0.9);
            backdrop-filter: blur(10px);
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #2980B9, #3498db);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 35px rgba(0,0,0,0.15);
        }

        .card h3 {
            color: #7f8c8d;
            margin-bottom: 10px;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .card p {
            font-size: 32px;
            font-weight: bold;
            color: #2980B9;
            margin: 0;
        }

        /* Recent Orders Table */
        .orders-section {
            background: rgba(255,255,255,0.9);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            margin-top: 30px;
            animation: slideUp 0.5s ease;
            overflow-x: auto;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .orders-section h2 {
            margin-bottom: 20px;
            color: #2980B9;
            font-size: 24px;
        }

        table {
            width: 100%;
            min-width: 800px; /* Ensures table has a minimum width for PC fit */
            border-collapse: collapse;
            background: transparent;
        }

        table th, table td {
            padding: 12px 8px; /* Reduced padding for better fit */
            text-align: left;
            border-bottom: 1px solid rgba(0,0,0,0.1);
            font-size: 14px; /* Slightly smaller font for compactness */
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

        button {
            padding: 8px 12px; /* Slightly smaller button */
            background: linear-gradient(90deg, #2980B9, #3498db);
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            font-size: 12px;
        }

        button:hover {
            background: linear-gradient(90deg, #1f5f8b, #2980B9);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(41,128,185,0.3);
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .main-content {
                max-width: calc(100vw - 250px);
                padding: 15px;
            }
            table th, table td {
                padding: 10px 6px;
                font-size: 13px;
            }
        }

        @media (max-width: 768px) {
            .sidebar { width: 100%; height: auto; }
            .main-content { margin-left: 0; max-width: 100%; }
            .cards { grid-template-columns: 1fr; }
            table { min-width: 600px; }
        }
    </style>
</head>
<body>

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
        <div class="orders-section">
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
                                <td><?php echo $order['order_id']; ?></td>
                                <td><?php echo htmlspecialchars($order['customer']); ?></td>
                                <td>$<?php echo number_format($order['total_price'], 2); ?></td>
                                <td class="status-<?php echo strtolower($order['status']); ?>"><?php echo $order['status']; ?></td>
                                <td><?php echo $order['payment_status']; ?></td>
                                <td><?php echo date('Y-m-d', strtotime($order['order_date'])); ?></td>
                                <td><button onclick="viewOrder(<?php echo $order['order_id']; ?>)">View</button></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: #7f8c8d;">No recent orders found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function viewOrder(id) {
            // Interactive: Open modal or redirect to order details page
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
        });
    </script>

</body>
</html>