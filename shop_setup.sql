-- Gym Management System - Shop Section Database Setup
USE gym_management;

-- Shop Categories Table
CREATE TABLE IF NOT EXISTS shop_categories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Shop Products Table
CREATE TABLE IF NOT EXISTS shop_products (
    product_id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    stock_quantity INT NOT NULL DEFAULT 0,
    image VARCHAR(255),
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES shop_categories(category_id) ON DELETE RESTRICT
);

-- Shop Orders Table
CREATE TABLE IF NOT EXISTS shop_orders (
    order_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('Pending', 'Paid', 'Shipped', 'Delivered', 'Cancelled') DEFAULT 'Pending',
    shipping_address TEXT NOT NULL,
    phone VARCHAR(20) NOT NULL,
    payment_method ENUM('Cash on Delivery', 'Online Transfer') DEFAULT 'Cash on Delivery',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Shop Order Items Table
CREATE TABLE IF NOT EXISTS shop_order_items (
    order_item_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES shop_orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES shop_products(product_id) ON DELETE CASCADE
);

-- Insert Sample Categories
INSERT INTO shop_categories (name, description) VALUES
('Supplements', 'Protein powders, pre-workouts, and vitamins.'),
('Drinks', 'Energy drinks, hydration, and shakes.'),
('Apparel', 'Gym clothing and accessories.'),
('Equipment', 'Lifting belts, gloves, and small workout gear.')
ON DUPLICATE KEY UPDATE name=name;
