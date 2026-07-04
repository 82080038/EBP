-- Migration 023: Add Explicit Index on users.id for FK Constraints
-- Menambahkan index eksplisit pada users.id untuk mendukung self-referencing FK
-- Priority: KRITIS

USE kewer;

-- Cek apakah index idx_users_id sudah ada
SET @index_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
                     WHERE TABLE_SCHEMA = 'kewer' 
                     AND TABLE_NAME = 'users' 
                     AND INDEX_NAME = 'idx_users_id');

SET @sql = IF(@index_exists = 0, 
    'CREATE INDEX idx_users_id ON users(id)',
    'SELECT "Index idx_users_id already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Cek apakah index idx_cabang_id sudah ada
SET @index_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
                     WHERE TABLE_SCHEMA = 'kewer' 
                     AND TABLE_NAME = 'cabang' 
                     AND INDEX_NAME = 'idx_cabang_id');

SET @sql = IF(@index_exists = 0, 
    'CREATE INDEX idx_cabang_id ON cabang(id)',
    'SELECT "Index idx_cabang_id already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verification
SELECT 'Migration completed successfully' AS status;
SELECT INDEX_NAME, COLUMN_NAME FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = 'kewer' AND TABLE_NAME = 'users' AND INDEX_NAME = 'idx_users_id';
SELECT INDEX_NAME, COLUMN_NAME FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = 'kewer' AND TABLE_NAME = 'cabang' AND INDEX_NAME = 'idx_cabang_id';
