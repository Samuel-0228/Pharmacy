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
    <title>...</title>
    <link rel="stylesheet" href="css/style.css">
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