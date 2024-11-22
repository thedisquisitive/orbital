<?php
include_once '../includes/headers.php';
include_once '../includes/db_connect.php';
include_once '../models/Item.php';

// Authentication (implement your auth logic)
include_once 'auth.php';
if (!isAuthenticated()) {
    http_response_code(401);
    echo json_encode(["message" => "Unauthorized"]);
    exit;
}

$item = new Item($conn);
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            // Get single item
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
            // Get all items
            $result = $item->read();
            $items = [];
            while ($row = $result->fetch_assoc()) {
                $items[] = $row;
            }
            echo json_encode($items);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->name) && !empty($data->category_id)) {
            $item->name = $data->name;
            $item->category_id = $data->category_id;
            $item->quantity = $data->quantity;
            $item->minQuantity = $data->minQuantity;
            $item->cost = $data->cost;
            $item->price = $data->price;
            $item->location = $data->location;
            $item->vendor = $data->vendor;

            if ($item->create()) {
                http_response_code(201);
                echo json_encode(["message" => "Item created"]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "Unable to create item"]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Incomplete data"]);
        }
        break;

    // Implement PUT and DELETE methods similarly

    default:
        http_response_code(405);
        echo json_encode(["message" => "Method not allowed"]);
        break;
}

$conn->close();
?>
