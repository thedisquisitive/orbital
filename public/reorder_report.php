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
$items_to_reorder = [];
$error_message = "";

// Fetch items that need to be reordered
$sql = "SELECT items.item_id, items.name, categories.category_name, items.quantity, items.minQuantity, items.vendor 
        FROM items 
        JOIN categories ON items.category_id = categories.category_id 
        WHERE items.quantity <= items.minQuantity 
        ORDER BY items.item_id ASC";

$result = $conn->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Calculate Quantity to Order
        $quantity_to_order = $row['minQuantity'] - $row['quantity'];
        $quantity_to_order = max($quantity_to_order, 0);
        $row['quantity_to_order'] = $quantity_to_order;
        $items_to_reorder[] = $row;
    }
} else {
    $error_message = "Error fetching items to reorder: " . $conn->error;
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reorder Report - Inventory Management System</title>
    <link rel="stylesheet" href="../css/report.css" />
</head>
<body>
    <div class="header">
        <div class="welcome">
            Welcome! (Role: <strong><?php echo htmlspecialchars($role); ?></strong>)
        </div>
        <form action="logout.php" method="POST">
            <button type="submit" class="logout-btn">Logout</button>
        </form>
    </div>

    <div class="content">
        <!-- Print Header -->
        <div class="print-header" style="display: none;">
            <h2>Reorder Report</h2>
            <p>Date: <?php echo date('Y-m-d'); ?></p>
        </div>

        <h2>Reorder Report</h2>

        <!-- Display Error Message if Any -->
        <?php if (!empty($error_message)): ?>
            <div style="color: red; margin-bottom: 20px;">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <!-- Reorder Items Table -->
        <?php if (!empty($items_to_reorder)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Item ID</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Current Quantity</th>
                        <th>Minimum Quantity</th>
                        <th>Quantity to Order</th>
                        <th>Vendor</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $row_count = 0;
                    foreach ($items_to_reorder as $item):
                        $row_count++;
                        $page_break_class = ($row_count % 25 == 0) ? 'page-break' : '';
                    ?>
                        <tr class="<?php echo $page_break_class; ?>">
                            <td><?php echo htmlspecialchars($item['item_id']); ?></td>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td><?php echo htmlspecialchars($item['category_name']); ?></td>
                            <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                            <td><?php echo htmlspecialchars($item['minQuantity']); ?></td>
                            <td><?php echo htmlspecialchars($item['quantity_to_order']); ?></td>
                            <td><?php echo htmlspecialchars($item['vendor']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Provide a print button -->
            <button onclick="window.print();" class="btn">Print Report</button>

        <?php else: ?>
            <p>All inventory levels are above the minimum required. No items need to be reordered at this time.</p>
        <?php endif; ?>

        <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
    </div>
</body>
</html>
