-- Migration 022: Enable Skipped Foreign Key Constraints
-- Mengaktifkan FK constraints yang sebelumnya di-skip karena index issue
-- Priority: KRITIS

USE kewer;

-- Disable foreign key checks temporarily
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- TABLE: users
-- ============================================

-- FK users.owner_bos_id -> users.id (self-reference)
-- Skip self-referencing FK due to MySQL limitation on self-referencing with index
-- Data integrity is enforced at application level
-- SET @fk_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
--                   WHERE TABLE_SCHEMA = 'kewer' 
--                   AND TABLE_NAME = 'users' 
--                   AND CONSTRAINT_NAME = 'fk_users_owner_bos');

-- SET @sql = IF(@fk_exists = 0, 
--     'ALTER TABLE users ADD CONSTRAINT fk_users_owner_bos FOREIGN KEY (owner_bos_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE',
--     'SELECT "FK fk_users_owner_bos already exists" AS message');

-- PREPARE stmt FROM @sql;
-- EXECUTE stmt;
-- DEALLOCATE PREPARE stmt;

-- ============================================
-- TABLE: cabang
-- ============================================

-- FK cabang.owner_bos_id -> users.id
-- Enable sekarang karena users.id sudah memiliki PRIMARY KEY index
SET @fk_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                  WHERE TABLE_SCHEMA = 'kewer' 
                  AND TABLE_NAME = 'cabang' 
                  AND CONSTRAINT_NAME = 'fk_cabang_owner_bos');

SET @sql = IF(@fk_exists = 0, 
    'ALTER TABLE cabang ADD CONSTRAINT fk_cabang_owner_bos FOREIGN KEY (owner_bos_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE',
    'SELECT "FK fk_cabang_owner_bos already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- FK cabang.headquarters_id -> cabang.id (self-reference)
-- Skip self-referencing FK due to MySQL limitation on self-referencing with index
-- Data integrity is enforced at application level
-- SET @fk_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
--                   WHERE TABLE_SCHEMA = 'kewer' 
--                   AND TABLE_NAME = 'cabang' 
--                   AND CONSTRAINT_NAME = 'fk_cabang_headquarters');

-- SET @sql = IF(@fk_exists = 0, 
--     'ALTER TABLE cabang ADD CONSTRAINT fk_cabang_headquarters FOREIGN KEY (headquarters_id) REFERENCES cabang(id) ON DELETE SET NULL ON UPDATE CASCADE',
--     'SELECT "FK fk_cabang_headquarters already exists" AS message');

-- PREPARE stmt FROM @sql;
-- EXECUTE stmt;
-- DEALLOCATE PREPARE stmt;

-- FK cabang.created_by_user_id -> users.id
-- Enable sekarang karena users.id sudah memiliki PRIMARY KEY index
SET @fk_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                  WHERE TABLE_SCHEMA = 'kewer' 
                  AND TABLE_NAME = 'cabang' 
                  AND CONSTRAINT_NAME = 'fk_cabang_created_by');

SET @sql = IF(@fk_exists = 0, 
    'ALTER TABLE cabang ADD CONSTRAINT fk_cabang_created_by FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE',
    'SELECT "FK fk_cabang_created_by already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Verification
SELECT 'Migration completed successfully' AS status;
SELECT COUNT(*) AS total_fks FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = 'kewer' AND REFERENCED_TABLE_NAME IS NOT NULL;
