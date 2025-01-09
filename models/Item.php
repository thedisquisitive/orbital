<?php
class Item {
    private $conn;
    private $table = "items";

    // Public properties matching your DB columns
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

    // READ: Fetch all items
    public function read() {
        $query = "SELECT items.*, categories.category_name 
                  FROM " . $this->table . "
                  LEFT JOIN categories ON items.category_id = categories.category_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // CREATE: Insert a new item
    public function create() {
        $query = "INSERT INTO " . $this->table . "
                  SET name = ?, 
                      category_id = ?, 
                      quantity = ?, 
                      minQuantity = ?, 
                      cost = ?, 
                      price = ?, 
                      location = ?, 
                      vendor = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            "siiiddss",
            $this->name,
            $this->category_id,
            $this->quantity,
            $this->minQuantity,
            $this->cost,
            $this->price,
            $this->location,
            $this->vendor
        );
        return $stmt->execute();
    }

    // UPDATE: Update existing item
    public function update() {
        $query = "UPDATE " . $this->table . "
                  SET name = ?, 
                      category_id = ?, 
                      quantity = ?, 
                      minQuantity = ?, 
                      cost = ?, 
                      price = ?, 
                      location = ?, 
                      vendor = ?
                  WHERE item_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            "siiiddssi",
            $this->name,
            $this->category_id,
            $this->quantity,
            $this->minQuantity,
            $this->cost,
            $this->price,
            $this->location,
            $this->vendor,
            $this->item_id
        );
        return $stmt->execute();
    }

    // DELETE: Remove an item
    public function delete() {
        $query = "DELETE FROM " . $this->table . " 
                  WHERE item_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $this->item_id);
        return $stmt->execute();
    }
}
