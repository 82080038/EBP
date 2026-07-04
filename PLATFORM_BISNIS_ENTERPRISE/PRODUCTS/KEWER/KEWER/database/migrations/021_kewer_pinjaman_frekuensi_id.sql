-- Migration 021: Add frekuensi_id to pinjaman table
-- Menambahkan kolom frekuensi_id ke tabel pinjaman untuk integrasi dengan ref_frekuensi_angsuran
-- Priority: KRITIS

USE kewer;

-- Disable foreign key checks
SET FOREIGN_KEY_CHECKS = 0;

-- 1. Tambah kolom frekuensi_id ke pinjaman jika belum ada
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                   WHERE TABLE_SCHEMA = 'kewer' 
                   AND TABLE_NAME = 'pinjaman' 
                   AND COLUMN_NAME = 'frekuensi_id');

SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE pinjaman ADD COLUMN frekuensi_id INT NULL AFTER cabang_id',
    'SELECT "Column frekuensi_id already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2. Migrasi data dari enum frekuensi ke frekuensi_id
UPDATE pinjaman SET frekuensi_id = 1 WHERE frekuensi = 'harian';
UPDATE pinjaman SET frekuensi_id = 2 WHERE frekuensi = 'mingguan';
UPDATE pinjaman SET frekuensi_id = 3 WHERE frekuensi = 'bulanan';

-- 3. Tambah foreign key
SET @fk_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                  WHERE TABLE_SCHEMA = 'kewer' 
                  AND TABLE_NAME = 'pinjaman' 
                  AND CONSTRAINT_NAME = 'fk_pinjaman_frekuensi');

SET @sql = IF(@fk_exists = 0, 
    'ALTER TABLE pinjaman ADD CONSTRAINT fk_pinjaman_frekuensi FOREIGN KEY (frekuensi_id) REFERENCES ref_frekuensi_angsuran(id) ON DELETE RESTRICT ON UPDATE CASCADE',
    'SELECT "FK fk_pinjaman_frekuensi already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Verification
SELECT 'Migration completed successfully' AS status;
SELECT COUNT(*) AS total_pinjaman FROM pinjaman;
SELECT COUNT(*) AS pinjaman_with_frekuensi_id FROM pinjaman WHERE frekuensi_id IS NOT NULL;
SELECT COUNT(*) AS pinjaman_with_old_frekuensi FROM pinjaman WHERE frekuensi IS NOT NULL;
