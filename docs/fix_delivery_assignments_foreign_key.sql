-- ============================================
-- FIX DELIVERY_ASSIGNMENTS FOREIGN KEY CONSTRAINT
-- ============================================
-- This script fixes the foreign key constraint that incorrectly references
-- the old 'drivers' table instead of 'users' table
-- 
-- Error: Cannot add or update a child row: a foreign key constraint fails
-- (`danicop`.`delivery_assignments`, CONSTRAINT `delivery_assignments_ibfk_2` 
-- FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`))

USE danicop;

-- Step 1: Drop the old foreign key constraint that references drivers table
-- First, let's check what constraints exist
SELECT 
    CONSTRAINT_NAME,
    TABLE_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'danicop'
    AND TABLE_NAME = 'delivery_assignments'
    AND COLUMN_NAME = 'driver_id';

-- Step 2: Drop the old foreign key constraint
-- Replace 'delivery_assignments_ibfk_2' with the actual constraint name from Step 1
ALTER TABLE delivery_assignments 
DROP FOREIGN KEY delivery_assignments_ibfk_2;

-- Step 3: Add the correct foreign key constraint that references users table
ALTER TABLE delivery_assignments 
ADD CONSTRAINT delivery_assignments_driver_id_fk 
FOREIGN KEY (driver_id) REFERENCES users(id) ON DELETE RESTRICT;

-- Step 4: Verify the constraint was updated correctly
SELECT 
    CONSTRAINT_NAME,
    TABLE_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'danicop'
    AND TABLE_NAME = 'delivery_assignments'
    AND COLUMN_NAME = 'driver_id';

-- Expected result: REFERENCED_TABLE_NAME should be 'users', not 'drivers'

