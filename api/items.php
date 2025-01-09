<?php
// items.php

// Common headers for JSON output (and optional CORS)
include_once '../includes/headers.php';
include_once '../includes/db_connect.php';

// Include your Item model
include_once '../models/Item.php';

// Include authentication script
include_once 'auth.php';
if (!isAuthenticated()) {
    http_response_code(401);
    echo json_encode(["message" => "Unauthorized"]);
    exit;
}

// Instantiate the Item model
$item = new Item($conn);

// Determine the HTTP request method
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            // Get a single item
            $item->item_id = intval($_GET['id']);
            $stmt = $conn->prepare("SELECT * FROM items WHERE item_id = ?");
            $stmt->bind_param("i", $item->item_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $item_data = $result->fetch_assoc();

            if ($item_data) {
                echo json_encode($item_data);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Item not found"]);
            }

        } else {
            // Get all items using the model's read() method
            $result = $item->read();
            $items = [];
            while ($row = $result->fetch_assoc()) {
                $items[] = $row;
            }
            echo json_encode($items);
        }
        break;

    case 'POST':
        // Create a new item
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->name) && !empty($data->category_id)) {
            $item->name        = $data->name;
            $item->category_id = $data->category_id;
            $item->quantity    = isset($data->quantity)    ? $data->quantity    : 0;
            $item->minQuantity = isset($data->minQuantity) ? $data->minQuantity : 0;
            $item->cost        = isset($data->cost)        ? $data->cost        : 0.0;
            $item->price       = isset($data->price)       ? $data->price       : 0.0;
            $item->location    = isset($data->location)    ? $data->location    : '';
            $item->vendor      = isset($data->vendor)      ? $data->vendor      : '';

            if ($item->create()) {
                http_response_code(201);
                echo json_encode(["message" => "Item created successfully"]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "Unable to create item"]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Incomplete data"]);
        }
        break;

    case 'PUT':
        // Update an existing item
        if (isset($_GET['id'])) {
            $item->item_id = intval($_GET['id']);
            $data = json_decode(file_get_contents("php://input"));

            // Check if the item exists first (optional, but recommended)
            // Or the model's update() could also handle the existence check
            $stmt = $conn->prepare("SELECT item_id FROM items WHERE item_id = ?");
            $stmt->bind_param("i", $item->item_id);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows === 0) {
                http_response_code(404);
                echo json_encode(["message" => "Item not found"]);
                $stmt->close();
                break;
            }
            $stmt->close();

            // Map updated fields from request body
            if (isset($data->name))        $item->name        = $data->name;
            if (isset($data->category_id)) $item->category_id = $data->category_id;
            if (isset($data->quantity))    $item->quantity    = $data->quantity;
            if (isset($data->minQuantity)) $item->minQuantity = $data->minQuantity;
            if (isset($data->cost))        $item->cost        = $data->cost;
            if (isset($data->price))       $item->price       = $data->price;
            if (isset($data->location))    $item->location    = $data->location;
            if (isset($data->vendor))      $item->vendor      = $data->vendor;

            // Call the model's update method
            if ($item->update()) {
                echo json_encode(["message" => "Item updated successfully"]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Error updating item"]);
            }

        } else {
            http_response_code(400);
            echo json_encode(["message" => "Item ID is required for update"]);
        }
        break;

    case 'DELETE':
        // Delete an existing item
        if (isset($_GET['id'])) {
            $item->item_id = intval($_GET['id']);

            // Check if the item exists first (optional)
            $stmt = $conn->prepare("SELECT item_id FROM items WHERE item_id = ?");
            $stmt->bind_param("i", $item->item_id);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows === 0) {
                http_response_code(404);
                echo json_encode(["message" => "Item not found"]);
                $stmt->close();
                break;
            }
            $stmt->close();

            // Call the model's delete method
            if ($item->delete()) {
                echo json_encode(["message" => "Item deleted successfully"]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Error deleting item"]);
            }

        } else {
            http_response_code(400);
            echo json_encode(["message" => "Item ID is required for delete"]);
        }
        break;

    default:
        // Method not allowed
        http_response_code(405);
        echo json_encode(["message" => "Method not allowed"]);
        break;
}

$conn->close();
?>
