-- ============================================
-- STORE LOCATIONS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS store_locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    address TEXT NOT NULL,
    latitude DECIMAL(10, 8) DEFAULT NULL,
    longitude DECIMAL(11, 8) DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    email VARCHAR(255) DEFAULT NULL,
    hours TEXT DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default location
INSERT INTO store_locations (name, address, phone, hours, is_active) VALUES
('Main Store', '123 Hardware Street, City, Philippines', '(02) 1234-5678', 'Mon-Sat: 8:00 AM - 6:00 PM\nSun: 9:00 AM - 4:00 PM', 1)
ON DUPLICATE KEY UPDATE name=name;

