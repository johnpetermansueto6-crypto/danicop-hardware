-- ============================================
-- ADD DELIVERY PROOF IMAGE COLUMN
-- ============================================
-- This adds a column to store proof of delivery photos
-- Drivers can upload a photo of the customer when marking delivery as "delivered"

USE danicop;

-- Add delivery_proof_image column to delivery_assignments table
ALTER TABLE delivery_assignments
ADD COLUMN delivery_proof_image VARCHAR(255) DEFAULT NULL COMMENT 'Path to proof of delivery photo' AFTER delivery_completed_at;

