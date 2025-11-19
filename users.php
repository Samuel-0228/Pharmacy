<?php
session_start();
include 'config.php';

// Only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Handle Add User
if (isset($_POST['add'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash password
    $role = $_POST['role'];

    $sql = "INSERT INTO users (name, email, password, role)
            VALUES ('$name', '$email', '$password', '$role')";
    $conn->query($sql);
    header("Location: users.php");
    exit();
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM users WHERE user_id=$id");
    header("Location: users.php");
    exit();
}

// Handle Update
if (isset($_POST['update'])) {
    $id = $_POST['user_id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;
    
    $sql = "UPDATE users 
            SET name='$name', email='$email', role='$role'";
    if ($password) {
        $sql .= ", password='$password'";
    }
    $sql .= " WHERE user_id=$id";
    $conn->query($sql);
    header("Location: users.php");
    exit();
}

// If edit button is clicked → fetch user
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $res = $conn->query("SELECT * FROM users WHERE user_id=$id");
    $edit_data = $res->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Online Pharmacy</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <div class="header">
        <h1>👥 Manage Users</h1>
        <a href="dashboard.php" class="back-link">⬅ Back to Dashboard</a>
    </div>

    <!-- Add or Edit User Form -->
    <div class="form-box">
        <?php if ($edit_data) { ?>
            <h2>Edit User</h2>
            <form method="POST">
                <input type="hidden" name="user_id" value="<?php echo $edit_data['user_id']; ?>">
                <input type="text" name="name" value="<?php echo htmlspecialchars($edit_data['name']); ?>" placeholder="Full Name" required>
                <input type="email" name="email" value="<?php echo htmlspecialchars($edit_data['email']); ?>" placeholder="Email" required>
                <input type="password" name="password" placeholder="New Password (leave blank to keep current)">
                <select name="role" required>
                    <option value="admin" <?php echo ($edit_data['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                    <option value="cashier" <?php echo ($edit_data['role'] == 'cashier') ? 'selected' : ''; ?>>Cashier</option>
                    <option value="customer" <?php echo ($edit_data['role'] == 'customer') ? 'selected' : ''; ?>>Customer</option>
                </select>
                <button type="submit" name="update">Update User</button>
            </form>
        <?php } else { ?>
            <h2>Add New User</h2>
            <form method="POST">
                <input type="text" name="name" placeholder="Full Name" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required minlength="6">
                <select name="role" required>
                    <option value="">Select Role</option>
                    <option value="admin">Admin</option>
                    <option value="cashier">Cashier</option>
                    <option value="customer">Customer</option>
                </select>
                <button type="submit" name="add">Add User</button>
            </form>
        <?php } ?>
    </div>

    <!-- Display Users Table -->
    <div class="table-section">
        <h2>Users List</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = $conn->query("SELECT * FROM users ORDER BY user_id DESC");
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>".$row['user_id']."</td>
                                <td>".htmlspecialchars($row['name'])."</td>
                                <td>".htmlspecialchars($row['email'])."</td>
                                <td>".$row['role']."</td>
                                <td>
                                    <a href='users.php?edit=".$row['user_id']."' class='action-btn edit-btn'>Edit</a>
                                    <a href='users.php?delete=".$row['user_id']."' class='action-btn delete-btn' onclick=\"return confirm('Are you sure you want to delete this user?');\">Delete</a>
                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='5' style='text-align: center; color: #7f8c8d; padding: 40px;'>No users found. Add one above!</td></tr>";
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
    </script>

</body>
</html>