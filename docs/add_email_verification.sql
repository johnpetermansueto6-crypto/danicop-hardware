-- ============================================
-- ADD EMAIL VERIFICATION FIELDS TO USERS TABLE
-- ============================================

ALTER TABLE users
ADD COLUMN email_verified TINYINT(1) NOT NULL DEFAULT 0 AFTER email,
ADD COLUMN verification_code VARCHAR(10) DEFAULT NULL AFTER email_verified,
ADD COLUMN verification_expires DATETIME DEFAULT NULL AFTER verification_code;


