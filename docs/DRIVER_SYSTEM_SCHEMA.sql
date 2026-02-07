-- ============================================
-- Complete Driver System SQL Schema
-- ============================================
-- Updated: Drivers are users with role='driver' (no separate drivers table)
-- Run this SQL to set up the complete driver/delivery system

USE danicop;

-- ============================================
-- STEP 1: UPDATE USERS TABLE TO SUPPORT DRIVER ROLE
-- ============================================
-- Add 'driver' to the role ENUM
ALTER TABLE users MODIFY COLUMN role ENUM('superadmin', 'staff', 'driver', 'customer') NOT NULL DEFAULT 'customer';

-- ============================================
-- STEP 2: CREATE DELIVERY ASSIGNMENTS TABLE
-- ============================================
-- Links orders to drivers (users with role='driver')
CREATE TABLE IF NOT EXISTS delivery_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    driver_id INT NOT NULL COMMENT 'References users.id where role=''driver''',
    assigned_by INT NOT NULL COMMENT 'User who assigned (staff/admin)',
    status ENUM('assigned', 'picked_up', 'delivering', 'delivered', 'failed') NOT NULL DEFAULT 'assigned',
    notes TEXT DEFAULT NULL COMMENT 'Optional notes for driver',
    delivery_started_at DATETIME DEFAULT NULL COMMENT 'When status changed to delivering',
    delivery_completed_at DATETIME DEFAULT NULL COMMENT 'When status changed to delivered',
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
-- STEP 3: CREATE DELIVERY HISTORY TABLE
-- ============================================
-- Complete audit trail of all delivery actions
CREATE TABLE IF NOT EXISTS delivery_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assignment_id INT NOT NULL COMMENT 'References delivery_assignments.id',
    action VARCHAR(100) NOT NULL COMMENT 'assigned, status_update, reassigned',
    previous_status VARCHAR(50) DEFAULT NULL COMMENT 'Previous delivery status',
    new_status VARCHAR(50) DEFAULT NULL COMMENT 'New delivery status',
    notes TEXT DEFAULT NULL COMMENT 'Additional notes',
    updated_by INT NOT NULL COMMENT 'User who made the change',
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (assignment_id) REFERENCES delivery_assignments(id) ON DELETE CASCADE,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_assignment_id (assignment_id),
    INDEX idx_timestamp (timestamp),
    INDEX idx_action (action)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- STEP 4: VERIFY TABLES CREATED
-- ============================================
-- Check that all tables exist
SELECT 
    'delivery_assignments' as table_name,
    COUNT(*) as row_count
FROM information_schema.tables 
WHERE table_schema = 'danicop' AND table_name = 'delivery_assignments'
UNION ALL
SELECT 
    'delivery_history' as table_name,
    COUNT(*) as row_count
FROM information_schema.tables 
WHERE table_schema = 'danicop' AND table_name = 'delivery_history';

-- ============================================
-- STEP 5: VERIFY USERS TABLE ROLE ENUM
-- ============================================
-- Check that 'driver' is in the role ENUM
SELECT 
    COLUMN_TYPE 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = 'danicop' 
    AND TABLE_NAME = 'users' 
    AND COLUMN_NAME = 'role';

-- ============================================
-- NOTES
-- ============================================
-- 1. Drivers are users with role='driver' (no separate drivers table)
-- 2. To add a driver: INSERT INTO users (name, email, password, role, email_verified) 
--    VALUES ('Driver Name', 'driver@email.com', '$2y$10$...', 'driver', 1);
-- 3. delivery_assignments.driver_id references users.id (where role='driver')
-- 4. All delivery actions are logged in delivery_history
-- 5. Driver status is managed through delivery_assignments, not user table

-- ============================================
-- END OF SCHEMA
-- ============================================

