-- ============================================
-- ADD GOOGLE AUTH & PROFILE FIELDS TO USERS
-- ============================================

ALTER TABLE users
ADD COLUMN google_id VARCHAR(255) DEFAULT NULL AFTER email,
ADD COLUMN auth_provider ENUM('local','google') NOT NULL DEFAULT 'local' AFTER password,
ADD COLUMN profile_completed TINYINT(1) NOT NULL DEFAULT 0 AFTER auth_provider,
ADD COLUMN phone VARCHAR(20) DEFAULT NULL AFTER role;

-- Optional index for faster lookup by google_id
CREATE INDEX idx_google_id ON users (google_id);


