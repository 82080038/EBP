-- Migration 020: Collection Management System
-- Sistem penagihan untuk tracking collection dan recovery
-- Priority: KRITIS

USE kewer;

-- Disable foreign key checks
SET FOREIGN_KEY_CHECKS = 0;

-- 1. Buat tabel penagihan
CREATE TABLE IF NOT EXISTS penagihan (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    pinjaman_id INT NOT NULL,
    angsuran_id INT,
    jenis ENUM('jatuh_tempo','telat','macet','follow_up') NOT NULL DEFAULT 'jatuh_tempo',
    status ENUM('pending','dalam_proses','berhasil','gagal','diabaikan') DEFAULT 'pending',
    tanggal_jatuh_tempo DATE NOT NULL,
    tanggal_penagihan DATE,
    petugas_id INT,
    hasil TEXT,
    tindakan TEXT,
    catatan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pinjaman_id) REFERENCES pinjaman(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (angsuran_id) REFERENCES angsuran(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (petugas_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_pinjaman_status (pinjaman_id, status),
    INDEX idx_petugas_tanggal (petugas_id, tanggal_penagihan),
    INDEX idx_jenis_status (jenis, status),
    INDEX idx_tanggal_jatuh_tempo (tanggal_jatuh_tempo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Buat tabel penagihan_log untuk audit trail
CREATE TABLE IF NOT EXISTS penagihan_log (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    penagihan_id BIGINT NOT NULL,
    aksi VARCHAR(100) NOT NULL,
    hasil TEXT,
    petugas_id INT,
    tanggal TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (penagihan_id) REFERENCES penagihan(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (petugas_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_penagihan_id (penagihan_id),
    INDEX idx_petugas_tanggal (petugas_id, tanggal)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Buat tabel ref_jenis_penagihan untuk jenis penagihan
CREATE TABLE IF NOT EXISTS ref_jenis_penagihan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kode VARCHAR(20) UNIQUE NOT NULL,
    nama VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    urutan_tampil INT DEFAULT 0,
    status ENUM('aktif','nonaktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_urutan (urutan_tampil)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Insert data ref_jenis_penagihan default
INSERT INTO ref_jenis_penagihan (kode, nama, deskripsi, urutan_tampil) VALUES
('JATUH_TEMPO', 'Jatuh Tempo', 'Penagihan rutin saat jatuh tempo', 1),
('TELAT_1_7', 'Telat 1-7 Hari', 'Penagihan untuk keterlambatan 1-7 hari', 2),
('TELAT_8_14', 'Telat 8-14 Hari', 'Penagihan untuk keterlambatan 8-14 hari', 3),
('TELAT_15_30', 'Telat 15-30 Hari', 'Penagihan untuk keterlambatan 15-30 hari', 4),
('TELAT_30_PLUS', 'Telat 30+ Hari', 'Penagihan untuk keterlambatan lebih dari 30 hari', 5),
('MACET', 'Macet', 'Penagihan untuk pinjaman yang macet', 6),
('FOLLOW_UP', 'Follow Up', 'Follow up setelah penagihan', 7)
ON DUPLICATE KEY UPDATE 
    nama = VALUES(nama),
    deskripsi = VALUES(deskripsi);

-- 5. Tambah kolom jenis_penagihan_id ke tabel penagihan
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                   WHERE TABLE_SCHEMA = 'kewer' 
                   AND TABLE_NAME = 'penagihan' 
                   AND COLUMN_NAME = 'jenis_penagihan_id');

SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE penagihan ADD COLUMN jenis_penagihan_id INT NULL AFTER jenis',
    'SELECT "Column jenis_penagihan_id already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 6. Migrasi data enum ke jenis_penagihan_id
UPDATE penagihan SET jenis_penagihan_id = 1 WHERE jenis = 'jatuh_tempo';
UPDATE penagihan SET jenis_penagihan_id = 5 WHERE jenis = 'telat';
UPDATE penagihan SET jenis_penagihan_id = 6 WHERE jenis = 'macet';
UPDATE penagihan SET jenis_penagihan_id = 7 WHERE jenis = 'follow_up';

-- 7. Tambah foreign key
SET @fk_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                  WHERE TABLE_SCHEMA = 'kewer' 
                  AND TABLE_NAME = 'penagihan' 
                  AND CONSTRAINT_NAME = 'fk_penagihan_jenis');

SET @sql = IF(@fk_exists = 0, 
    'ALTER TABLE penagihan ADD CONSTRAINT fk_penagihan_jenis FOREIGN KEY (jenis_penagihan_id) REFERENCES ref_jenis_penagihan(id) ON DELETE RESTRICT ON UPDATE CASCADE',
    'SELECT "FK fk_penagihan_jenis already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 8. Buat view untuk penagihan hari ini
CREATE OR REPLACE VIEW v_penagihan_hari_ini AS
SELECT 
    p.id,
    p.pinjaman_id,
    n.kode_nasabah,
    n.nama AS nama_nasabah,
    n.telp,
    n.alamat,
    n.province_id,
    n.regency_id,
    n.district_id,
    n.village_id,
    a.no_angsuran,
    a.jatuh_tempo,
    a.total_angsuran,
    a.total_bayar,
    a.status AS status_angsuran,
    p.jenis,
    p.status AS status_penagihan,
    p.petugas_id,
    u.nama AS nama_petugas,
    p.catatan
FROM penagihan p
JOIN pinjaman pin ON p.pinjaman_id = pin.id
JOIN nasabah n ON pin.nasabah_id = n.id
LEFT JOIN angsuran a ON p.angsuran_id = a.id
LEFT JOIN users u ON p.petugas_id = u.id
WHERE p.status IN ('pending', 'dalam_proses')
ORDER BY p.tanggal_jatuh_tempo ASC;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Verification
SELECT 'Migration completed successfully' AS status;
SELECT COUNT(*) AS total_jenis_penagihan FROM ref_jenis_penagihan;
SELECT COUNT(*) AS total_penagihan FROM penagihan;
SELECT * FROM ref_jenis_penagihan ORDER BY urutan_tampil;
