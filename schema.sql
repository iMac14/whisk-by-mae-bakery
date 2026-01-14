-- Create database
CREATE DATABASE IF NOT EXISTS whiskbymae;
USE whiskbymae;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    password VARCHAR(255) NOT NULL,
    user_type ENUM('customer', 'admin') DEFAULT 'customer',
    reset_token VARCHAR(255),
    reset_expires DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'accepted', 'done') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Create order_items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Insert admin user
INSERT INTO users (name, email, phone, address, password, user_type) VALUES
('Admin', 'admin@whiskbymae.com', '1234567890', 'Admin Address', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert sample products
INSERT INTO products (name, price, image) VALUES
('Chocolate Cake', 25.99, 'product_6964dc5edf7307.51727745.jpg'),
('Vanilla Cupcake', 5.99, 'product_6964de0f8e0c68.51697886.jpg'),
('Strawberry Tart', 15.50, 'product_6964de05f1a4a9.73847653.png'),
('Blueberry Muffin', 4.99, 'product_6964e3f189f722.06991162.jpg'),
('Red Velvet Cake', 30.00, 'product_6964ea46685520.00570752.png'),
('Lemon Bars', 12.99, 'product_6964ecd2e29da0.38117036.png'),
('Cookies Assortment', 18.50, 'product_6964f82826f636.74432615.png');
