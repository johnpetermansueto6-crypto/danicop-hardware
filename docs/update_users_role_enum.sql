-- ============================================
-- Update Users Table to Support Driver Role
-- ============================================
-- Run this SQL to add 'driver' to the role ENUM

USE danicop;

ALTER TABLE users MODIFY COLUMN role ENUM('superadmin', 'staff', 'driver', 'customer') NOT NULL DEFAULT 'customer';

