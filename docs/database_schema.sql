-- ============================================
-- Danicop Hardware Online - Database Schema
-- ============================================

-- Create Database
CREATE DATABASE IF NOT EXISTS hardware_online CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hardware_online;

-- ============================================
-- USERS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('superadmin', 'staff', 'customer') NOT NULL DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PRODUCTS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    image VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_stock (stock)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- ORDERS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    order_number VARCHAR(50) NOT NULL UNIQUE,
    total_amount DECIMAL(10, 2) NOT NULL,
    payment_method ENUM('cash_delivery', 'cash_pickup', 'gcash', 'paypal') NOT NULL DEFAULT 'cash_pickup',
    delivery_method ENUM('delivery', 'pickup') NOT NULL DEFAULT 'pickup',
    delivery_address TEXT DEFAULT NULL,
    contact_number VARCHAR(20) DEFAULT NULL,
    status ENUM('pending', 'confirmed', 'preparing', 'out_for_delivery', 'ready_for_pickup', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_order_number (order_number),
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- ORDER ITEMS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_order_id (order_id),
    INDEX idx_product_id (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- DELIVERY LOGS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS delivery_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    delivery_person VARCHAR(255) DEFAULT NULL,
    status_update TEXT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_order_id (order_id),
    INDEX idx_timestamp (timestamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SALES REPORTS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS sales_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL,
    total_sales DECIMAL(10, 2) NOT NULL DEFAULT 0,
    total_orders INT NOT NULL DEFAULT 0,
    best_seller VARCHAR(255) DEFAULT NULL,
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_date (date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- NOTIFICATIONS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('low_stock', 'order_update', 'new_order', 'system') NOT NULL,
    message TEXT NOT NULL,
    user_id INT DEFAULT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_is_read (is_read),
    INDEX idx_type (type),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- INSERT DEFAULT SUPER ADMIN
-- ============================================
-- Default password: admin123
-- Note: If password doesn't work, run reset_admin.php to generate correct hash
-- The hash below is a placeholder - use reset_admin.php to set the correct password
INSERT INTO users (name, email, password, role) VALUES
('Super Admin', 'admin@hardware.com', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy', 'superadmin')
ON DUPLICATE KEY UPDATE name=name;

-- ============================================
-- INSERT SAMPLE PRODUCTS
-- ============================================
INSERT INTO products (name, category, description, price, stock, image) VALUES
('Hammer', 'Tools', 'Heavy-duty steel hammer for construction work', 250.00, 50, 'hammer.jpg'),
('Screwdriver Set', 'Tools', 'Complete set of 10 screwdrivers in various sizes', 350.00, 30, 'screwdriver.jpg'),
('Nails (1kg)', 'Fasteners', 'Assorted nails for general construction', 120.00, 100, 'nails.jpg'),
('Paint Brush Set', 'Paint Supplies', 'Professional paint brush set with 5 brushes', 180.00, 40, 'paintbrush.jpg'),
('Drill Machine', 'Power Tools', 'Electric drill machine with variable speed', 2500.00, 15, 'drill.jpg'),
('Safety Helmet', 'Safety Equipment', 'Hard hat for construction safety', 350.00, 60, 'helmet.jpg'),
('Measuring Tape', 'Tools', '5-meter steel measuring tape', 150.00, 80, 'tape.jpg'),
('Pliers Set', 'Tools', 'Set of 3 pliers (regular, needle-nose, cutting)', 280.00, 35, 'pliers.jpg'),
('Paint (1L)', 'Paint Supplies', 'Premium interior/exterior paint', 450.00, 25, 'paint.jpg'),
('Wire Cutter', 'Tools', 'Heavy-duty wire cutting tool', 200.00, 45, 'wirecutter.jpg')
ON DUPLICATE KEY UPDATE name=name;

-- ============================================
-- END OF SCHEMA
-- ============================================

