-- Migration 019: Loan Product Configuration
-- Tabel master untuk konfigurasi produk pinjaman yang berbeda
-- Mendukung multiple loan products dengan konfigurasi berbeda
-- Priority: KRITIS

USE kewer;

-- Disable foreign key checks
SET FOREIGN_KEY_CHECKS = 0;

-- 1. Buat tabel ref_produk_pinjaman
CREATE TABLE IF NOT EXISTS ref_produk_pinjaman (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kode VARCHAR(20) UNIQUE NOT NULL,
    nama VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    frekuensi_id INT NOT NULL COMMENT 'Foreign key ke ref_frekuensi_angsuran',
    tenor_min INT NOT NULL COMMENT 'Tenor minimum dalam periode',
    tenor_max INT NOT NULL COMMENT 'Tenor maximum dalam periode',
    jumlah_min DECIMAL(15,2) NOT NULL COMMENT 'Jumlah pinjaman minimum',
    jumlah_max DECIMAL(15,2) NOT NULL COMMENT 'Jumlah pinjaman maximum',
    bunga_default DECIMAL(5,2) NOT NULL COMMENT 'Bunga default dalam persen',
    bunga_min DECIMAL(5,2) COMMENT 'Bunga minimum dalam persen',
    bunga_max DECIMAL(5,2) COMMENT 'Bunga maximum dalam persen',
    biaya_admin DECIMAL(15,2) DEFAULT 0 COMMENT 'Biaya administrasi nominal',
    biaya_provisi DECIMAL(5,2) DEFAULT 0 COMMENT 'Biaya provisi dalam persen',
    asuransi_wajib TINYINT(1) DEFAULT 0 COMMENT 'Apakah asuransi wajib',
    jaminan_wajib TINYINT(1) DEFAULT 0 COMMENT 'Apakah jaminan wajib',
    jaminan_tipe_id INT COMMENT 'Tipe jaminan yang wajib',
    status ENUM('aktif','nonaktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (frekuensi_id) REFERENCES ref_frekuensi_angsuran(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (jaminan_tipe_id) REFERENCES ref_jaminan_tipe(id) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_frekuensi_id (frekuensi_id),
    INDEX idx_status (status),
    INDEX idx_jaminan_tipe_id (jaminan_tipe_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Insert data produk pinjaman default
-- Produk harian (sesuai industri KSP: Rp500rb - Rp5jt, max 100 hari)
INSERT INTO ref_produk_pinjaman (kode, nama, deskripsi, frekuensi_id, tenor_min, tenor_max, jumlah_min, jumlah_max, bunga_default, bunga_min, bunga_max, biaya_admin, biaya_provisi, jaminan_wajib) VALUES
('PIN_HARIAN', 'Pinjaman Harian', 'Pinjaman dengan angsuran harian untuk pedagang pasar', 1, 1, 100, 500000, 5000000, 1.5, 1.0, 2.5, 5000, 0, 0),
('PIN_HARIAN_JAMINAN', 'Pinjaman Harian dengan Jaminan', 'Pinjaman harian dengan jaminan untuk plafon lebih tinggi', 1, 1, 100, 500000, 10000000, 1.2, 0.8, 2.0, 10000, 0, 1)
ON DUPLICATE KEY UPDATE 
    nama = VALUES(nama),
    deskripsi = VALUES(deskripsi);

-- Produk mingguan (sesuai industri: Rp300rb - Rp10jt, max 52 minggu)
INSERT INTO ref_produk_pinjaman (kode, nama, deskripsi, frekuensi_id, tenor_min, tenor_max, jumlah_min, jumlah_max, bunga_default, bunga_min, bunga_max, biaya_admin, biaya_provisi, jaminan_wajib) VALUES
('PIN_MINGGUAN', 'Pinjaman Mingguan', 'Pinjaman dengan angsuran mingguan', 2, 1, 52, 300000, 10000000, 1.0, 0.5, 1.5, 10000, 0, 0),
('PIN_MINGGUAN_KEMAS', 'Pinjaman Mingguan Kemas', 'Pinjaman mingguan tenor 11 minggu (Koperasi Sentra Dana style)', 2, 11, 11, 300000, 3500000, 1.0, 0.5, 1.5, 10000, 0, 0),
('PIN_MINGGUAN_ASA', 'Pinjaman Mingguan ASA', 'Pinjaman mingguan tenor 8 minggu (Koperasi ASA style)', 2, 8, 8, 300000, 3500000, 1.0, 0.5, 1.5, 10000, 0, 0)
ON DUPLICATE KEY UPDATE 
    nama = VALUES(nama),
    deskripsi = VALUES(deskripsi);

-- Produk bulanan (sesuai industri: Rp500rb - Rp50jt, max 36 bulan)
INSERT INTO ref_produk_pinjaman (kode, nama, deskripsi, frekuensi_id, tenor_min, tenor_max, jumlah_min, jumlah_max, bunga_default, bunga_min, bunga_max, biaya_admin, biaya_provisi, asuransi_wajib, jaminan_wajib) VALUES
('PIN_BULANAN', 'Pinjaman Bulanan', 'Pinjaman dengan angsuran bulanan', 3, 1, 36, 500000, 50000000, 0.5, 0.3, 1.0, 25000, 1, 1, 1)
ON DUPLICATE KEY UPDATE 
    nama = VALUES(nama),
    deskripsi = VALUES(deskripsi);

-- 3. Update tabel pinjaman untuk menambah kolom produk_pinjaman_id
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                   WHERE TABLE_SCHEMA = 'kewer' 
                   AND TABLE_NAME = 'pinjaman' 
                   AND COLUMN_NAME = 'produk_pinjaman_id');

SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE pinjaman ADD COLUMN produk_pinjaman_id INT NULL AFTER cabang_id',
    'SELECT "Column produk_pinjaman_id already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 4. Migrasi data pinjaman yang sudah ada ke produk default
-- Default: pinjaman bulanan -> PINJAMAN_BULANAN (id 5 based on insert order)
UPDATE pinjaman SET produk_pinjaman_id = 5 WHERE frekuensi = 'bulanan' AND produk_pinjaman_id IS NULL;
UPDATE pinjaman SET produk_pinjaman_id = 1 WHERE frekuensi = 'harian' AND produk_pinjaman_id IS NULL;
UPDATE pinjaman SET produk_pinjaman_id = 3 WHERE frekuensi = 'mingguan' AND produk_pinjaman_id IS NULL;

-- 5. Tambah foreign key
SET @fk_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                  WHERE TABLE_SCHEMA = 'kewer' 
                  AND TABLE_NAME = 'pinjaman' 
                  AND CONSTRAINT_NAME = 'fk_pinjaman_produk');

SET @sql = IF(@fk_exists = 0, 
    'ALTER TABLE pinjaman ADD CONSTRAINT fk_pinjaman_produk FOREIGN KEY (produk_pinjaman_id) REFERENCES ref_produk_pinjaman(id) ON DELETE RESTRICT ON UPDATE CASCADE',
    'SELECT "FK fk_pinjaman_produk already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Verification
SELECT 'Migration completed successfully' AS status;
SELECT COUNT(*) AS total_produk FROM ref_produk_pinjaman;
SELECT COUNT(*) AS pinjaman_updated FROM pinjaman WHERE produk_pinjaman_id IS NOT NULL;
SELECT * FROM ref_produk_pinjaman ORDER BY id;
