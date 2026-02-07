-- ============================================
-- Delivery Management Module - Database Schema
-- ============================================
-- Run this SQL to add delivery management tables to your existing database

USE danicop;

-- ============================================
-- NOTE: Drivers are now users with role='driver'
-- No separate drivers table needed
-- ============================================

-- ============================================
-- 1. DELIVERY ASSIGNMENTS TABLE
-- ============================================
-- Note: driver_id references users.id where role='driver'
CREATE TABLE IF NOT EXISTS delivery_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    driver_id INT NOT NULL,
    assigned_by INT NOT NULL,
    status ENUM('assigned', 'picked_up', 'delivering', 'delivered', 'failed') NOT NULL DEFAULT 'assigned',
    notes TEXT DEFAULT NULL,
    delivery_started_at DATETIME DEFAULT NULL,
    delivery_completed_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (driver_id) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_order_id (order_id),
    INDEX idx_driver_id (driver_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    UNIQUE KEY unique_active_order (order_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 2. DELIVERY HISTORY TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS delivery_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assignment_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    previous_status VARCHAR(50) DEFAULT NULL,
    new_status VARCHAR(50) DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    updated_by INT NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (assignment_id) REFERENCES delivery_assignments(id) ON DELETE CASCADE,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_assignment_id (assignment_id),
    INDEX idx_timestamp (timestamp),
    INDEX idx_action (action)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 3. UPDATE USERS TABLE TO SUPPORT DRIVER ROLE
-- ============================================
-- Add 'driver' to the role ENUM
ALTER TABLE users MODIFY COLUMN role ENUM('superadmin', 'staff', 'driver', 'customer') NOT NULL DEFAULT 'customer';

-- ============================================
-- END OF DELIVERY MODULE SCHEMA
-- ============================================

