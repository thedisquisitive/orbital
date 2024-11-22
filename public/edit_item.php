<?php
// Start the session
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Include the database connection
include_once '../includes/db_connect.php';

// Initialize variables
$item_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$name = $category_id = $quantity = $minQuantity = $cost = $price = $location = $vendor = "";
$errors = [];

// Fetch categories for the dropdown
$categories = [];
$sql = "SELECT category_id, category_name FROM categories ORDER BY category_name ASC";
$result = $conn->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
} else {
    $errors[] = "Error fetching categories: " . $conn->error;
}

// Fetch existing item data
if ($item_id > 0) {
    $stmt = $conn->prepare("SELECT name, category_id, quantity, minQuantity, cost, price, location, vendor FROM items WHERE item_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $item_id);
        $stmt->execute();
        $stmt->bind_result($name, $category_id, $quantity, $minQuantity, $cost, $price, $location, $vendor);
        if (!$stmt->fetch()) {
            $errors[] = "Item not found.";
        }
        $stmt->close();
    } else {
        $errors[] = "Database error: " . $conn->error;
    }
} else {
    $errors[] = "Invalid item ID.";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize form data
    $name = trim($_POST['name']);
    $category_id = intval($_POST['category_id']);
    $quantity = intval($_POST['quantity']);
    $minQuantity = intval($_POST['minQuantity']);
    $cost = floatval($_POST['cost']);
    $price = floatval($_POST['price']);
    $location = trim($_POST['location']);
    $vendor = trim($_POST['vendor']);

    // Validate inputs
    if (empty($name)) {
        $errors[] = "Item name is required.";
    }
    if ($category_id <= 0) {
        $errors[] = "Please select a valid category.";
    }
    if ($quantity < 0) {
        $errors[] = "Quantity cannot be negative.";
    }
    if ($minQuantity < 0) {
        $errors[] = "Minimum quantity cannot be negative.";
    }
    if ($cost < 0) {
        $errors[] = "Cost cannot be negative.";
    }
    if ($price < 0) {
        $errors[] = "Price cannot be negative.";
    }
    if (empty($location)) {
        $errors[] = "Location is required.";
    }
    if (empty($vendor)) {
        $errors[] = "Vendor is required.";
    }

    // If no errors, update the item in the database
    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE items SET name = ?, category_id = ?, quantity = ?, minQuantity = ?, cost = ?, price = ?, location = ?, vendor = ? WHERE item_id = ?");
        if ($stmt) {
            $stmt->bind_param("siiidddsi", $name, $category_id, $quantity, $minQuantity, $cost, $price, $location, $vendor, $item_id);
            if ($stmt->execute()) {
                // Redirect to dashboard after successful update
                header("Location: dashboard.php");
                exit();
            } else {
                $errors[] = "Error updating item: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $errors[] = "Database error: " . $conn->error;
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Edit Item - Inventory Management System</title>
    <link rel="stylesheet" href="../css/item.css" />
</head>
<body>
    <div class="container">
        <h2>Edit Inventory Item</h2>

        <!-- Display Errors -->
        <?php if (!empty($errors)): ?>
            <div class="error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Edit Item Form -->
        <?php if ($item_id > 0 && empty($errors) || $_SERVER['REQUEST_METHOD'] === 'POST'): ?>
            <form method="POST" action="edit_item.php?id=<?php echo htmlspecialchars($item_id); ?>">
                <div class="form-group">
                    <label for="name">Item Name:</label>
                    <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($name); ?>" required>
                </div>

                <div class="form-group">
                    <label for="category_id">Category:</label>
                    <select name="category_id" id="category_id" required>
                        <option value="">-- Select Category --</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo htmlspecialchars($category['category_id']); ?>" <?php echo ($category_id == $category['category_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['category_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="quantity">Quantity:</label>
                    <input type="number" name="quantity" id="quantity" value="<?php echo htmlspecialchars($quantity); ?>" min="0" required>
                </div>

                <div class="form-group">
                    <label for="minQuantity">Minimum Quantity:</label>
                    <input type="number" name="minQuantity" id="minQuantity" value="<?php echo htmlspecialchars($minQuantity); ?>" min="0" required>
                </div>

                <div class="form-group">
                    <label for="cost">Cost ($):</label>
                    <input type="number" step="0.01" name="cost" id="cost" value="<?php echo htmlspecialchars($cost); ?>" min="0" required>
                </div>

                <div class="form-group">
                    <label for="price">Price ($):</label>
                    <input type="number" step="0.01" name="price" id="price" value="<?php echo htmlspecialchars($price); ?>" min="0" required>
                </div>

                <div class="form-group">
                    <label for="location">Location:</label>
                    <input type="text" name="location" id="location" value="<?php echo htmlspecialchars($location); ?>" required>
                </div>

                <div class="form-group">
                    <label for="vendor">Vendor:</label>
                    <input type="text" name="vendor" id="vendor" value="<?php echo htmlspecialchars($vendor); ?>" required>
                </div>

                <button type="submit" class="submit-btn">Update Item</button>
            </form>
        <?php endif; ?>

        <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
    </div>
</body>
</html>
