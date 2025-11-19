<?php
session_start();
include 'config.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle Add Medicine
if (isset($_POST['add'])) {
    $name = $_POST['name'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $expiry = $_POST['expiry'];

    $sql = "INSERT INTO medicines (name, category, price, quantity_in_stock, expiry_date)
            VALUES ('$name', '$category', '$price', '$quantity', '$expiry')";
    $conn->query($sql);
    header("Location: medicines.php");
    exit();
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM medicines WHERE medicine_id=$id");
    header("Location: medicines.php");
    exit();
}

// Handle Update
if (isset($_POST['update'])) {
    $id = $_POST['medicine_id'];
    $name = $_POST['name'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $expiry = $_POST['expiry'];

    $sql = "UPDATE medicines 
            SET name='$name', category='$category', price='$price', quantity_in_stock='$quantity', expiry_date='$expiry'
            WHERE medicine_id=$id";
    $conn->query($sql);
    header("Location: medicines.php");
    exit();
}

// If edit button is clicked → fetch medicine
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $res = $conn->query("SELECT * FROM medicines WHERE medicine_id=$id");
    $edit_data = $res->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Medicines - Online Pharmacy</title>
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

        /* Form Card */
        .form-box {
            background: rgba(255,255,255,0.9);
            backdrop-filter: blur(10px);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            animation: slideUp 0.5s ease;
            max-width: 600px;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-box h2 {
            color: #2980B9;
            margin-bottom: 20px;
            font-size: 24px;
            text-align: center;
        }

        .form-box form {
            display: grid;
            gap: 15px;
        }

        .form-box input {
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        .form-box input:focus {
            outline: none;
            border-color: #2980B9;
            box-shadow: 0 0 0 3px rgba(41,128,185,0.1);
        }

        .form-box button {
            padding: 12px;
            background: linear-gradient(90deg, #2980B9, #3498db);
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .form-box button:hover {
            background: linear-gradient(90deg, #1f5f8b, #2980B9);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(41,128,185,0.3);
        }

        /* Table Section */
        .table-section {
            background: rgba(255,255,255,0.9);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            animation: slideUp 0.5s ease 0.2s both;
            overflow-x: auto;
        }

        .table-section h2 {
            margin-bottom: 20px;
            color: #2980B9;
            font-size: 24px;
        }

        table {
            width: 100%;
            min-width: 800px;
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

        /* Action Buttons */
        .action-btn {
            padding: 6px 12px;
            margin: 0 5px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 12px;
            text-decoration: none;
            display: inline-block;
        }

        .edit-btn {
            background: #3498db;
            color: #fff;
        }

        .edit-btn:hover {
            background: #2980B9;
            transform: translateY(-1px);
        }

        .delete-btn {
            background: #e74c3c;
            color: #fff;
        }

        .delete-btn:hover {
            background: #c0392b;
            transform: translateY(-1px);
        }

        /* Responsive */
        @media (max-width: 768px) {
            body { padding: 10px; }
            .header { flex-direction: column; gap: 10px; text-align: center; }
            .form-box { padding: 20px; }
            table { min-width: 600px; }
            table th, table td { padding: 10px 5px; font-size: 12px; }
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>💊 Manage Medicines</h1>
        <a href="dashboard.php" class="back-link">⬅ Back to Dashboard</a>
    </div>

    <!-- Add or Edit Medicine Form -->
    <div class="form-box">
        <?php if ($edit_data) { ?>
            <h2>Edit Medicine</h2>
            <form method="POST">
                <input type="hidden" name="medicine_id" value="<?php echo $edit_data['medicine_id']; ?>">
                <input type="text" name="name" value="<?php echo htmlspecialchars($edit_data['name']); ?>" placeholder="Medicine Name" required>
                <input type="text" name="category" value="<?php echo htmlspecialchars($edit_data['category']); ?>" placeholder="Category" required>
                <input type="number" step="0.01" name="price" value="<?php echo $edit_data['price']; ?>" placeholder="Price" required>
                <input type="number" name="quantity" value="<?php echo $edit_data['quantity_in_stock']; ?>" placeholder="Quantity in Stock" required>
                <input type="date" name="expiry" value="<?php echo $edit_data['expiry_date']; ?>" required>
                <button type="submit" name="update">Update Medicine</button>
            </form>
        <?php } else { ?>
            <h2>Add New Medicine</h2>
            <form method="POST">
                <input type="text" name="name" placeholder="Medicine Name" required>
                <input type="text" name="category" placeholder="Category" required>
                <input type="number" step="0.01" name="price" placeholder="Price" required>
                <input type="number" name="quantity" placeholder="Quantity in Stock" required>
                <input type="date" name="expiry" required>
                <button type="submit" name="add">Add Medicine</button>
            </form>
        <?php } ?>
    </div>

    <!-- Display Medicines Table -->
    <div class="table-section">
        <h2>Medicines List</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Expiry</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = $conn->query("SELECT * FROM medicines ORDER BY medicine_id DESC");
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>".$row['medicine_id']."</td>
                                <td>".htmlspecialchars($row['name'])."</td>
                                <td>".htmlspecialchars($row['category'])."</td>
                                <td>$".number_format($row['price'], 2)."</td>
                                <td>".$row['quantity_in_stock']."</td>
                                <td>".date('M j, Y', strtotime($row['expiry_date']))."</td>
                                <td>
                                    <a href='medicines.php?edit=".$row['medicine_id']."' class='action-btn edit-btn'>Edit</a>
                                    <a href='medicines.php?delete=".$row['medicine_id']."' class='action-btn delete-btn' onclick=\"return confirm('Are you sure you want to delete this medicine?');\">Delete</a>
                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='7' style='text-align: center; color: #7f8c8d; padding: 40px;'>No medicines found. Add one above!</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <script>
        // Add subtle interactivity: Animate table rows on load
        document.addEventListener('DOMContentLoaded', function() {
            const rows = document.querySelectorAll('table tbody tr');
            rows.forEach((row, index) => {
                row.style.animationDelay = (index * 0.05) + 's';
                row.style.animation = 'slideUp 0.3s ease forwards';
            });
        });

        // Form validation and focus effects (optional enhancement)
        const inputs = document.querySelectorAll('.form-box input');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
            });
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });
    </script>

</body>
</html>