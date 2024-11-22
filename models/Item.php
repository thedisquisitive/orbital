<?php
class Item {
    private $conn;
    private $table = "items";

    public $item_id;
    public $name;
    public $category_id;
    public $quantity;
    public $minQuantity;
    public $cost;
    public $price;
    public $location;
    public $vendor;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Read all items
    public function read() {
        $query = "SELECT items.*, categories.category_name FROM " . $this->table . "
                  LEFT JOIN categories ON items.category_id = categories.category_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Create item
    public function create() {
        $query = "INSERT INTO " . $this->table . "
                  SET name=?, category_id=?, quantity=?, minQuantity=?, cost=?, price=?, location=?, vendor=?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("siiiddss", $this->name, $this->category_id, $this->quantity, $this->minQuantity, $this->cost, $this->price, $this->location, $this->vendor);
        return $stmt->execute();
    }

    // Additional methods for update, delete, etc.
}
?>
