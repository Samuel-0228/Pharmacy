<?php
session_start();
include 'config.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>



<!DOCTYPE html>
<html>
<head>
    <title>Sales Reports</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; padding: 20px; }
        table { width: 100%; border-collapse: collapse; background: #fff; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: center; }
        th { background: green; color: white; }
    </style>
</head>
<body>
    <h1>Sales Reports</h1>
    <p><a href="dashboard.php">⬅ Back to Dashboard</a></p>

    <!-- Daily Sales -->
    <h2>Daily Sales</h2>
    <table>
        <tr>
            <th>Date</th><th>Total Sales (Transactions)</th><th>Total Revenue</th>
        </tr>
        <?php
        $result = $conn->query("SELECT DATE(date) as date, COUNT(*) as sales_count, SUM(total_amount) as revenue 
                                FROM sales 
                                GROUP BY DATE(date)
                                ORDER BY DATE(date) DESC");
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>".$row['date']."</td>
                    <td>".$row['sales_count']."</td>
                    <td>".$row['revenue']."</td>
                  </tr>";
        }
        ?>
    </table>

    <!-- Top Medicines Sold -->
    <h2>Top Medicines Sold</h2>
    <table>
        <tr>
            <th>Medicine</th><th>Total Quantity Sold</th><th>Total Revenue</th>
        </tr>
        <?php
        $result2 = $conn->query("SELECT m.name, SUM(s.total_amount) as total_qty, SUM(s.total_amount) as total_rev
                                 FROM sales s 
                                 JOIN medicines m ON s.medicine_id = m.medicine_id
                                 GROUP BY m.name
                                 ORDER BY total_qty DESC");
        while ($row = $result2->fetch_assoc()) {
            echo "<tr>
                    <td>".$row['name']."</td>
                    <td>".$row['total_qty']."</td>
                    <td>".$row['total_rev']."</td>
                  </tr>";
        }
        ?>
    </table>
</body>
</html>
