-- Add latitude and longitude columns to orders table for delivery addresses
ALTER TABLE orders 
ADD COLUMN delivery_latitude DECIMAL(10, 8) DEFAULT NULL AFTER delivery_address,
ADD COLUMN delivery_longitude DECIMAL(11, 8) DEFAULT NULL AFTER delivery_latitude;

