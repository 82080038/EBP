-- Migration 015: Normalisasi Frekuensi Angsuran
-- Tabel master untuk frekuensi angsuran (harian, mingguan, bulanan)
-- Menggantikan enum hardcoded di angsuran, setting_bunga, setting_denda

USE kewer;

-- Disable foreign key checks
SET FOREIGN_KEY_CHECKS = 0;

-- 1. Buat tabel ref_frekuensi_angsuran
CREATE TABLE IF NOT EXISTS ref_frekuensi_angsuran (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kode VARCHAR(20) UNIQUE NOT NULL,
    nama VARCHAR(50) NOT NULL,
    hari_per_periode INT NOT NULL COMMENT 'Jumlah hari dalam satu periode',
    tenor_default INT NOT NULL COMMENT 'Tenor default untuk frekuensi ini',
    tenor_min INT NOT NULL COMMENT 'Tenor minimum',
    tenor_max INT NOT NULL COMMENT 'Tenor maximum',
    urutan_tampil INT DEFAULT 0,
    status ENUM('aktif','nonaktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_urutan (urutan_tampil)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Insert data frekuensi
INSERT INTO ref_frekuensi_angsuran (kode, nama, hari_per_periode, tenor_default, tenor_min, tenor_max, urutan_tampil) VALUES
('HARIAN', 'Harian', 1, 30, 1, 100, 1),
('MINGGUAN', 'Mingguan', 7, 12, 1, 52, 2),
('BULANAN', 'Bulanan', 30, 12, 1, 36, 3)
ON DUPLICATE KEY UPDATE 
    nama = VALUES(nama),
    hari_per_periode = VALUES(hari_per_periode),
    tenor_default = VALUES(tenor_default),
    tenor_min = VALUES(tenor_min),
    tenor_max = VALUES(tenor_max);

-- 3. Update setting_bunga untuk menggunakan foreign key
-- Tambah kolom frekuensi_id jika belum ada
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                   WHERE TABLE_SCHEMA = 'kewer' 
                   AND TABLE_NAME = 'setting_bunga' 
                   AND COLUMN_NAME = 'frekuensi_id');

SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE setting_bunga ADD COLUMN frekuensi_id INT NULL AFTER id',
    'SELECT "Column frekuensi_id already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Migrasi data dari enum ke frekuensi_id
UPDATE setting_bunga SET frekuensi_id = 1 WHERE frekuensi = 'harian';
UPDATE setting_bunga SET frekuensi_id = 2 WHERE frekuensi = 'mingguan';
UPDATE setting_bunga SET frekuensi_id = 3 WHERE frekuensi = 'bulanan';

-- Tambah foreign key
ALTER TABLE setting_bunga ADD CONSTRAINT fk_setting_bunga_frekuensi 
    FOREIGN KEY (frekuensi_id) REFERENCES ref_frekuensi_angsuran(id) ON DELETE RESTRICT ON UPDATE CASCADE;

-- 4. Update setting_denda untuk menggunakan foreign key
-- Tambah kolom frekuensi_id jika belum ada
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                   WHERE TABLE_SCHEMA = 'kewer' 
                   AND TABLE_NAME = 'setting_denda' 
                   AND COLUMN_NAME = 'frekuensi_id');

SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE setting_denda ADD COLUMN frekuensi_id INT NULL AFTER id',
    'SELECT "Column frekuensi_id already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Migrasi data dari enum ke frekuensi_id
UPDATE setting_denda SET frekuensi_id = 1 WHERE frekuensi = 'harian';
UPDATE setting_denda SET frekuensi_id = 2 WHERE frekuensi = 'mingguan';
UPDATE setting_denda SET frekuensi_id = 3 WHERE frekuensi = 'bulanan';

-- Tambah foreign key
ALTER TABLE setting_denda ADD CONSTRAINT fk_setting_denda_frekuensi 
    FOREIGN KEY (frekuensi_id) REFERENCES ref_frekuensi_angsuran(id) ON DELETE RESTRICT ON UPDATE CASCADE;

-- 5. Update angsuran untuk menggunakan foreign key
-- Tambah kolom frekuensi_id jika belum ada
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                   WHERE TABLE_SCHEMA = 'kewer' 
                   AND TABLE_NAME = 'angsuran' 
                   AND COLUMN_NAME = 'frekuensi_id');

SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE angsuran ADD COLUMN frekuensi_id INT NULL AFTER id',
    'SELECT "Column frekuensi_id already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Migrasi data dari enum ke frekuensi_id
UPDATE angsuran SET frekuensi_id = 1 WHERE frekuensi = 'harian';
UPDATE angsuran SET frekuensi_id = 2 WHERE frekuensi = 'mingguan';
UPDATE angsuran SET frekuensi_id = 3 WHERE frekuensi = 'bulanan';

-- Tambah foreign key
ALTER TABLE angsuran ADD CONSTRAINT fk_angsuran_frekuensi 
    FOREIGN KEY (frekuensi_id) REFERENCES ref_frekuensi_angsuran(id) ON DELETE RESTRICT ON UPDATE CASCADE;

-- 6. (Opsional) Hapus kolom frekuensi enum dari tabel-tabel setelah migration berhasil
-- Uncomment baris berikut jika sudah yakin data sudah migrasi dengan benar
-- ALTER TABLE setting_bunga DROP COLUMN frekuensi;
-- ALTER TABLE setting_denda DROP COLUMN frekuensi;
-- ALTER TABLE angsuran DROP COLUMN frekuensi;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Verification
SELECT 'Migration completed successfully' AS status;
SELECT COUNT(*) AS total_frekuensi FROM ref_frekuensi_angsuran;
SELECT COUNT(*) AS setting_bunga_updated FROM setting_bunga WHERE frekuensi_id IS NOT NULL;
SELECT COUNT(*) AS setting_denda_updated FROM setting_denda WHERE frekuensi_id IS NOT NULL;
SELECT COUNT(*) AS angsuran_updated FROM angsuran WHERE frekuensi_id IS NOT NULL;
