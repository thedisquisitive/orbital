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

// Define valid columns for sorting
$valid_sort_columns = [
    'item_id' => 'items.item_id',
    'name' => 'items.name',
    'category_name' => 'categories.category_name',
    'quantity' => 'items.quantity',
    'minQuantity' => 'items.minQuantity',
    'cost' => 'items.cost',
    'price' => 'items.price',
    'location' => 'items.location',
    'vendor' => 'items.vendor'
];

// Get sorting parameters from the URL
$sort = isset($_GET['sort']) && array_key_exists($_GET['sort'], $valid_sort_columns) ? $_GET['sort'] : 'item_id';
$order = isset($_GET['order']) && in_array(strtoupper($_GET['order']), ['ASC', 'DESC']) ? strtoupper($_GET['order']) : 'ASC';

// Determine the opposite order for toggling
$opposite_order = $order === 'ASC' ? 'DESC' : 'ASC';

// Fetch inventory items from the database with sorting
$sql = "SELECT items.item_id, items.name, categories.category_name, items.quantity, items.minQuantity, items.cost, items.price, items.location, items.vendor 
        FROM items 
        JOIN categories ON items.category_id = categories.category_id 
        ORDER BY " . $valid_sort_columns[$sort] . " " . $order;

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
    <style>
        /* Additional styles for sorting indicators */
        th {
            position: relative;
            cursor: pointer;
        }

        th a {
            color: white;
            text-decoration: none;
            display: block;
        }

        th .sort-indicator {
            margin-left: 8px;
            font-size: 0.8em;
        }

        th.sorted-asc::after {
            content: " ▲";
        }

        th.sorted-desc::after {
            content: " ▼";
        }
    </style>
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
        
        <!-- Add Item Button (Visible to Admins and Technicians) -->
        <?php if ($role === 'admin' || $role === 'technician'): ?>
            <a href="add_item.php" class="btn">Add Item</a>
        <?php endif; ?>

        <!-- Manage Users Button (Visible to Admins) -->
        <?php if ($role === 'admin'): ?>
            <a href="manage_users.php" class="btn">Manage Users</a>
        <?php endif; ?>

        <a href="reorder_report.php" class="btn">Generate Report</a>

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
                        <?php
                        // Define column headers and their corresponding sort keys
                        $columns = [
                            'item_id' => 'Item ID',
                            'name' => 'Name',
                            'category_name' => 'Category',
                            'quantity' => 'Quantity',
                            'minQuantity' => 'Min Quantity',
                            'cost' => 'Cost ($)',
                            'price' => 'Price ($)',
                            'location' => 'Location',
                            'vendor' => 'Vendor'
                        ];

                        foreach ($columns as $key => $label):
                            // Determine the class for the sort indicator
                            $sort_class = '';
                            if ($sort === $key) {
                                $sort_class = $order === 'ASC' ? 'sorted-asc' : 'sorted-desc';
                            }
                        ?>
                            <th class="<?php echo $sort_class; ?>">
                                <a href="?sort=<?php echo urlencode($key); ?>&order=<?php echo $sort === $key ? $opposite_order : 'ASC'; ?>">
                                    <?php echo htmlspecialchars($label); ?>
                                </a>
                            </th>
                        <?php endforeach; ?>

                        <?php if ($role === 'admin' || $role === 'technician'): ?>
                            <th>Actions</th>
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
                            <?php if ($role === 'admin' || $role === 'technician'): ?>
                                <td>
                                    <a href="edit_item.php?id=<?php echo htmlspecialchars($item['item_id']); ?>" class="btn btn-edit">Edit</a>
                                    <?php if ($role === 'admin'): ?>
                                        <a href="delete_item.php?id=<?php echo htmlspecialchars($item['item_id']); ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this item?');">Delete</a>
                                    <?php endif; ?>
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
