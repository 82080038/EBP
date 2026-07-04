-- Migration 024: Drop Old Frekuensi Enum Columns
-- Menghapus kolom frekuensi enum yang sudah tidak diperlukan setelah migrasi ke ref_frekuensi_angsuran
-- Priority: KRITIS
-- WARNING: Jalankan ini HANYA setelah yakin integrasi ref_frekuensi_angsuran berfungsi dengan baik

USE kewer;

-- Disable foreign key checks temporarily
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- TABLE: pinjaman
-- ============================================

-- Drop frekuensi enum column (frekuensi_id sudah ada)
SET @column_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                      WHERE TABLE_SCHEMA = 'kewer' 
                      AND TABLE_NAME = 'pinjaman' 
                      AND COLUMN_NAME = 'frekuensi');

SET @sql = IF(@column_exists > 0, 
    'ALTER TABLE pinjaman DROP COLUMN frekuensi',
    'SELECT "Column frekuensi in pinjaman does not exist" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================
-- TABLE: setting_bunga
-- ============================================

-- Drop frekuensi enum column (frekuensi_id sudah ada)
SET @column_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                      WHERE TABLE_SCHEMA = 'kewer' 
                      AND TABLE_NAME = 'setting_bunga' 
                      AND COLUMN_NAME = 'frekuensi');

SET @sql = IF(@column_exists > 0, 
    'ALTER TABLE setting_bunga DROP COLUMN frekuensi',
    'SELECT "Column frekuensi in setting_bunga does not exist" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================
-- TABLE: setting_denda
-- ============================================

-- Drop frekuensi enum column (frekuensi_id sudah ada)
SET @column_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                      WHERE TABLE_SCHEMA = 'kewer' 
                      AND TABLE_NAME = 'setting_denda' 
                      AND COLUMN_NAME = 'frekuensi');

SET @sql = IF(@column_exists > 0, 
    'ALTER TABLE setting_denda DROP COLUMN frekuensi',
    'SELECT "Column frekuensi in setting_denda does not exist" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================
-- TABLE: angsuran
-- ============================================

-- Drop frekuensi enum column (frekuensi_id sudah ada)
SET @column_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                      WHERE TABLE_SCHEMA = 'kewer' 
                      AND TABLE_NAME = 'angsuran' 
                      AND COLUMN_NAME = 'frekuensi');

SET @sql = IF(@column_exists > 0, 
    'ALTER TABLE angsuran DROP COLUMN frekuensi',
    'SELECT "Column frekuensi in angsuran does not exist" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Verification
SELECT 'Migration completed successfully' AS status;

-- Check remaining frekuensi columns
SELECT TABLE_NAME, COLUMN_NAME, DATA_TYPE 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'kewer' 
AND COLUMN_NAME = 'frekuensi'
AND TABLE_NAME IN ('pinjaman', 'setting_bunga', 'setting_denda', 'angsuran');
