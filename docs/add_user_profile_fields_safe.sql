-- ============================================
-- ADD IMPORTANT PROFILE FIELDS TO USERS TABLE (SAFE VERSION)
-- ============================================
-- This version checks for existing columns before adding
-- Run via: utils/add_user_profile_fields.php (recommended)
-- Or execute each statement individually after checking

-- Add address field (for customer delivery addresses)
-- Check first: SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'hardware_online' AND TABLE_NAME = 'users' AND COLUMN_NAME = 'address';
ALTER TABLE users
ADD COLUMN address TEXT DEFAULT NULL AFTER phone;

-- Add profile picture field
ALTER TABLE users
ADD COLUMN profile_picture VARCHAR(255) DEFAULT NULL AFTER phone;

-- Add updated_at timestamp for tracking changes
ALTER TABLE users
ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

-- Add date of birth (optional, for age verification or marketing)
ALTER TABLE users
ADD COLUMN date_of_birth DATE DEFAULT NULL AFTER address;

-- Add gender (optional, for demographics)
ALTER TABLE users
ADD COLUMN gender ENUM('male', 'female', 'other', 'prefer_not_to_say') DEFAULT NULL AFTER date_of_birth;

-- Add city/province/zipcode for better address management
ALTER TABLE users
ADD COLUMN city VARCHAR(100) DEFAULT NULL AFTER address;

ALTER TABLE users
ADD COLUMN province VARCHAR(100) DEFAULT NULL AFTER city;

ALTER TABLE users
ADD COLUMN zipcode VARCHAR(10) DEFAULT NULL AFTER province;

-- Add emergency contact fields (useful for delivery drivers)
ALTER TABLE users
ADD COLUMN emergency_contact_name VARCHAR(255) DEFAULT NULL AFTER zipcode;

ALTER TABLE users
ADD COLUMN emergency_contact_phone VARCHAR(20) DEFAULT NULL AFTER emergency_contact_name;

-- Add status field (active, inactive, suspended)
ALTER TABLE users
ADD COLUMN status ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active' AFTER role;

-- Add last login tracking
ALTER TABLE users
ADD COLUMN last_login DATETIME DEFAULT NULL AFTER updated_at;

-- Create indexes for better query performance (only if they don't exist)
CREATE INDEX idx_status ON users (status);
CREATE INDEX idx_city ON users (city);
CREATE INDEX idx_province ON users (province);

