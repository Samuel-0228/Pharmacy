<?php
session_start();
include 'config.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle Sale
if (isset($_POST['sell'])) {
    $medicine_id = $_POST['medicine_id'];
    $quantity = $_POST['quantity'];
    $date = date("Y-m-d H:i:s");

    // Get medicine details
    $res = $conn->query("SELECT * FROM medicines WHERE medicine_id=$medicine_id");
    $med = $res->fetch_assoc();

    if ($quantity > 0 && $quantity <= $med['quantity_in_stock']) {
        $total = $med['price'] * $quantity;

        // Insert into sales table
        $sql = "INSERT INTO sales (medicine_id, total_amount, total_price, date)
                VALUES ('$medicine_id', '$quantity', '$total', '$date')";
        $conn->query($sql);

        // Update stock
        $new_stock = $med['quantity_in_stock'] - $quantity;
        $conn->query("UPDATE medicines SET quantity_in_stock=$new_stock WHERE medicine_id=$medicine_id");

        $message = "✅ Sale recorded successfully!";
    } else {
        $message = "❌ Not enough stock!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Record Sales</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; padding: 20px; }
        table { width: 100%; border-collapse: collapse; background: #fff; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: center; }
        th { background: green; color: white; }
        .form-box { background: #fff; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .msg { margin: 10px 0; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Sales Module</h1>
    <p><a href="dashboard.php">⬅ Back to Dashboard</a></p>

    <!-- Show messages -->
    <?php if (!empty($message)) { echo "<p class='msg'>$message</p>"; } ?>

    <!-- Record Sale Form -->
    <div class="form-box">
        <h2>Record a Sale</h2>
        <form method="POST">
            <label>Medicine:</label>
            <select name="medicine_id" required>
                <option value="">-- Select Medicine --</option>
                <?php
                $res = $conn->query("SELECT * FROM medicines WHERE quantity_in_stock > 0");
                while ($row = $res->fetch_assoc()) {
                    echo "<option value='".$row['medicine_id']."'>".$row['name']." (Stock: ".$row['quantity_in_stock'].")</option>";
                }
                ?>
            </select>
            <input type="number" name="quantity" placeholder="Quantity" required>
            <button type="submit" name="sell">Record Sale</button>
        </form>
    </div>

    <!-- Sales History -->
    <h2>Sales History</h2>
    <table>
        <tr>
            <th>ID</th><th>Medicine</th><th>Quantity</th><th>Total Price</th><th>Date</th>
        </tr>
        <?php
        $result = $conn->query("SELECT s.sale_id, m.name, s.total_amount, s.total_price, s.date 
                                FROM sales s JOIN medicines m ON s.medicine_id = m.medicine_id 
                                ORDER BY s.date DESC");
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>".$row['sale_id']."</td>
                    <td>".$row['name']."</td>
                    <td>".$row['total_amount']."</td>
                    <td>".$row['total_price']."</td>
                    <td>".$row['date']."</td>
                  </tr>";
        }
        ?>
    </table>
</body>
</html>
