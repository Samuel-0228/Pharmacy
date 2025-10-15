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
<html>
<head>
    <title>Manage Medicines</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; padding: 20px; }
        table { width: 100%; border-collapse: collapse; background: #fff; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: center; }
        th { background: green; color: white; }
        .form-box { background: #fff; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <h1>Manage Medicines</h1>
    <p><a href="dashboard.php">⬅ Back to Dashboard</a></p>

    <!-- Add or Edit Medicine Form -->
    <div class="form-box">
        <?php if ($edit_data) { ?>
            <h2>Edit Medicine</h2>
            <form method="POST">
                <input type="hidden" name="medicine_id" value="<?php echo $edit_data['medicine_id']; ?>">
                <input type="text" name="name" value="<?php echo $edit_data['name']; ?>" required>
                <input type="text" name="category" value="<?php echo $edit_data['category']; ?>" required>
                <input type="number" step="0.01" name="price" value="<?php echo $edit_data['price']; ?>" required>
                <input type="number" name="quantity" value="<?php echo $edit_data['quantity_in_stock']; ?>" required>
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
    <h2>Medicines List</h2>
    <table>
        <tr>
            <th>ID</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Expiry</th><th>Action</th>
        </tr>
        <?php
        $result = $conn->query("SELECT * FROM medicines");
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>".$row['medicine_id']."</td>
                    <td>".$row['name']."</td>
                    <td>".$row['category']."</td>
                    <td>".$row['price']."</td>
                    <td>".$row['quantity_in_stock']."</td>
                    <td>".$row['expiry_date']."</td>
                    <td>
                        <a href='medicines.php?edit=".$row['medicine_id']."'>Edit</a> |
                        <a href='medicines.php?delete=".$row['medicine_id']."' onclick=\"return confirm('Are you sure?');\">Delete</a>
                    </td>
                  </tr>";
        }
        ?>
    </table>
</body>
</html>
