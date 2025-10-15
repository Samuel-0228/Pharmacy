<?php
session_start();
include 'config.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<?php
if ($_SESSION['role'] == 'admin') {
    $res = $conn->query("SELECT COUNT(*) as new_orders FROM orders WHERE viewed = 0");
    $row = $res->fetch_assoc();
    $new_orders = $row['new_orders'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Online Pharmacy</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family: 'Arial', sans-serif; }

        body {
            background: #f4f6f8;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 220px;
            height: 100%;
            background: #2980B9;
            color: #fff;
            display: flex;
            flex-direction: column;
            padding-top: 20px;
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 30px;
        }

        .sidebar a {
            color: #fff;
            text-decoration: none;
            padding: 15px 20px;
            display: block;
            transition: 0.3s;
        }

        .sidebar a:hover {
            background: #1f5f8b;
        }

        /* Main content */
        .main-content {
            margin-left: 220px;
            padding: 20px;
        }

        .topbar {
            background: #fff;
            padding: 15px 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 8px;
        }

        .topbar h1 {
            font-size: 20px;
            color: #2980B9;
        }

        .cards {
            display: flex;
            flex-wrap: wrap;
            margin-top: 20px;
            gap: 20px;
        }

        .card {
            background: #fff;
            flex: 1 1 200px;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .card h3 {
            color: #2980B9;
            margin-bottom: 10px;
        }

        .card p {
            font-size: 24px;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
        }

        table th, table td {
            padding: 12px 15px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        table th {
            background: #2980B9;
            color: #fff;
        }

        table tr:hover {
            background: #f1f1f1;
        }

        button {
            padding: 8px 12px;
            background: #2980B9;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            background: #1f5f8b;
        }

    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Admin Panel</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="medicines.php">Manage Medicines</a>
        <a href="orders.php">Manage Orders</a>
        <a href="users.php">Manage Users</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="main-content">
        <div class="topbar">
            <h1>Welcome, Admin</h1>
            <span>🕒 <?php echo date("Y-m-d H:i"); ?></span>
        </div>

        <!-- Stats Cards -->
        <div class="cards">
            <div class="card">
                <h3>Total Medicines</h3>
                <p>120</p>
            </div>
            <div class="card">
                <h3>Total Orders</h3>
                <p>85</p>
            </div>
            <div class="card">
                <h3>Total Customers</h3>
                <p>45</p>
            </div>
            <div class="card">
                <h3>Revenue</h3>
                <p>$12,500</p>
            </div>
        </div>

        <!-- Recent Orders Table -->
        <h2 style="margin-top: 30px; color: #2980B9;">Recent Orders</h2>
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
                <tr>
                    <td>101</td>
                    <td>John Doe</td>
                    <td>$150</td>
                    <td>Pending</td>
                    <td>Paid</td>
                    <td>2025-09-28</td>
                    <td><button>View</button></td>
                </tr>
                <tr>
                    <td>102</td>
                    <td>Jane Smith</td>
                    <td>$220</td>
                    <td>Completed</td>
                    <td>Paid</td>
                    <td>2025-09-27</td>
                    <td><button>View</button></td>
                </tr>
                <!-- More rows will come dynamically with PHP -->
            </tbody>
        </table>
    </div>

</body>
</html>
