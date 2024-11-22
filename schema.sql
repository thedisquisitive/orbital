-- Create the database
CREATE DATABASE IF NOT EXISTS inventory_db;
USE inventory_db;

-- Create the 'users' table
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'technician') NOT NULL
);

-- Create the 'categories' table
CREATE TABLE IF NOT EXISTS categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE
);

-- Create the 'items' table with 'cost', 'price', and 'minQuantity'
CREATE TABLE IF NOT EXISTS items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    minQuantity INT NOT NULL DEFAULT 0,
    cost DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    location VARCHAR(100),
    vendor VARCHAR(100),
    FOREIGN KEY (category_id) REFERENCES categories(category_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

-- Optional: Insert default admin user (password is 'admin123' hashed)
INSERT INTO users (username, password, role) VALUES
('admin', '$2y$10$e0NRXqEuP4Uj6FQn6aXxFOpOqPFeQSkC0K/BZs.3btErkDmULkE8G', 'admin');

-- Optional: Insert sample categories
INSERT INTO categories (category_name) VALUES
('Hardware'),
('Software'),
('Peripherals');

-- Optional: Insert sample items with 'cost', 'price', and 'minQuantity'
INSERT INTO items (name, category_id, quantity, minQuantity, cost, price, location, vendor) VALUES
('Viewsonic 24" Monitor', 1, 2, 3, 95, 179.00, 'Storage Rack A', 'Dell Inc.'),
('Windows 10 Pro License', 2, 10, 3, 100.00, 150.00, 'Shop Cab A', 'Microsoft'),
('Logitech MK120 KB/M Combo', 3, 5, 5, 15.00, 25.00, 'Shelf B2', 'Logitech');
