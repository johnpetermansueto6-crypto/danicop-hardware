-- ============================================
-- ADD NEW SUPERADMIN USER
-- ============================================
-- 
-- IMPORTANT: Replace the password hash below with a proper bcrypt hash
-- You can generate one using PHP: password_hash('your_password', PASSWORD_DEFAULT)
-- Or use the utility script: utils/create_superadmin.php
--
-- ============================================

-- Option 1: Insert new superadmin (replace values)
-- Replace 'Your Name', 'admin@example.com', and the password hash
INSERT INTO users (name, email, password, role, email_verified) 
VALUES (
    'Super Admin', 
    'admin@hardware.com', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',  -- Password: admin123
    'superadmin',
    1
)
ON DUPLICATE KEY UPDATE 
    name = VALUES(name),
    password = VALUES(password),
    role = 'superadmin';

-- Option 2: Update existing user to superadmin
-- UPDATE users SET role = 'superadmin' WHERE email = 'user@example.com';

-- Option 3: Insert with custom values (replace these)
-- INSERT INTO users (name, email, password, role, email_verified) 
-- VALUES (
--     'Your Admin Name',                    -- Replace with admin name
--     'your-admin@email.com',              -- Replace with admin email
--     '$2y$10$YOUR_PASSWORD_HASH_HERE',    -- Replace with bcrypt hash
--     'superadmin',
--     1
-- );

-- ============================================
-- HOW TO GENERATE PASSWORD HASH:
-- ============================================
-- 
-- Method 1: Using PHP (recommended)
-- Run this in PHP:
-- <?php
-- echo password_hash('your_password_here', PASSWORD_DEFAULT);
-- ?>
--
-- Method 2: Use the utility script
-- Visit: http://localhost/hardware/utils/create_superadmin.php
--
-- Method 3: Online bcrypt generator (less secure, use with caution)
-- https://bcrypt-generator.com/
--
-- ============================================
-- DEFAULT ADMIN CREDENTIALS (if using Option 1):
-- ============================================
-- Email: admin@hardware.com
-- Password: admin123
-- 
-- ⚠️ CHANGE THE DEFAULT PASSWORD IMMEDIATELY AFTER FIRST LOGIN!
-- ============================================

