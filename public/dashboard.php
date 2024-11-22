<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    // User is not authenticated
    header("Location: login.php");
    exit();
}

// Retrieve user information from the session
$username = $_SESSION['username'];
$role = $_SESSION['role'];

// Include the database connection
include_once '../includes/db_connect.php';

// Initialize variables
$items = [];
$error_message = "";

// Fetch inventory items from the database
$sql = "SELECT items.item_id, items.name, categories.category_name, items.quantity, items.minQuantity, items.cost, items.price, items.location, items.vendor 
        FROM items 
        JOIN categories ON items.category_id = categories.category_id 
        ORDER BY items.item_id ASC";

$result = $conn->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
} else {
    $error_message = "Error fetching items: " . $conn->error;
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Inventory Management System</title>
    <link rel="stylesheet" href="../css/dashboard.css" />
</head>
<body>
    <div class="header">
        <div class="welcome">
            Welcome, <strong><?php echo htmlspecialchars($username); ?></strong>! (Role: <strong><?php echo htmlspecialchars($role); ?></strong>)
        </div>
        <form action="logout.php" method="POST">
            <button type="submit" class="logout-btn">Logout</button>
        </form>
    </div>

    <div class="content">
        <h2>Inventory Items</h2>
        
        <!-- Add Item Button (Visible to Admins) -->
        <?php if ($role === 'admin'): ?>
            <a href="add_item.php" class="btn">Add New Item</a>
        <?php endif; ?>

        <!-- Display Error Message if Any -->
        <?php if (!empty($error_message)): ?>
            <div style="color: red; margin-bottom: 20px;">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <!-- Inventory Items Table -->
        <?php if (!empty($items)): ?>
            <table>
    <thead>
        <tr>
            <th scope="col">Item ID</th>
            <th scope="col">Name</th>
            <th scope="col">Category</th>
            <th scope="col">Quantity</th>
            <th scope="col">Min Quantity</th>
            <th scope="col">Cost ($)</th>
            <th scope="col">Price ($)</th>
            <th scope="col">Location</th>
            <th scope="col">Vendor</th>
            <?php if ($role === 'admin'): ?>
                <th scope="col">Actions</th>
            <?php endif; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($items as $item): ?>
            <tr>
                <td><?php echo htmlspecialchars($item['item_id']); ?></td>
                <td><?php echo htmlspecialchars($item['name']); ?></td>
                <td><?php echo htmlspecialchars($item['category_name']); ?></td>
                <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                <td><?php echo htmlspecialchars($item['minQuantity']); ?></td>
                <td><?php echo htmlspecialchars(number_format($item['cost'], 2)); ?></td>
                <td><?php echo htmlspecialchars(number_format($item['price'], 2)); ?></td>
                <td><?php echo htmlspecialchars($item['location']); ?></td>
                <td><?php echo htmlspecialchars($item['vendor']); ?></td>
                <?php if ($role === 'admin'): ?>
                    <td>
                        <a href="edit_item.php?id=<?php echo htmlspecialchars($item['item_id']); ?>" class="btn btn-edit">Edit</a>
                        <a href="delete_item.php?id=<?php echo htmlspecialchars($item['item_id']); ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this item?');">Delete</a>
                    </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

        <?php else: ?>
            <p>No inventory items found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
