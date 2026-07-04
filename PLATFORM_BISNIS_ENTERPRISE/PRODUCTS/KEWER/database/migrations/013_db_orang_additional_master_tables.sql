-- Migration: Tambah tabel master untuk jenis alamat dan jenis identitas
-- Tanggal: 2026-05-07
-- Deskripsi: Normalisasi jenis alamat dan jenis nomor identitas di db_orang

USE db_orang;

-- Drop tables if they exist (for clean migration)
DROP TABLE IF EXISTS ref_jenis_alamat;
DROP TABLE IF EXISTS ref_jenis_identitas;

-- Tabel Master Jenis Alamat
CREATE TABLE ref_jenis_alamat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(50) NOT NULL UNIQUE,
    kode VARCHAR(20) NOT NULL UNIQUE,
    urutan INT NOT NULL DEFAULT 0,
    is_aktif TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Referensi jenis alamat';

-- Insert data jenis alamat
INSERT INTO ref_jenis_alamat (nama, kode, urutan) VALUES
('Rumah', 'RUMAH', 1),
('Kantor', 'KANTOR', 2),
('Kos', 'KOS', 3),
('Apartemen', 'APARTEMEN', 4),
('Kontrakan', 'KONTRAKAN', 5),
('Mess', 'MESS', 6),
('Toko', 'TOKO', 7),
('Gudang', 'GUDANG', 8),
('Ruko', 'RUKO', 9),
('Lainnya', 'LAINNYA', 99);

-- Tabel Master Jenis Identitas
CREATE TABLE ref_jenis_identitas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(50) NOT NULL UNIQUE,
    kode VARCHAR(20) NOT NULL UNIQUE,
    panjang_nomor INT NULL COMMENT 'Panjang standar nomor identitas',
    urutan INT NOT NULL DEFAULT 0,
    is_aktif TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Referensi jenis dokumen identitas';

-- Insert data jenis identitas
INSERT INTO ref_jenis_identitas (nama, kode, panjang_nomor, urutan) VALUES
('KTP', 'KTP', 16, 1),
('SIM', 'SIM', 12, 2),
('Paspor', 'PASPOR', 8, 3),
('KK', 'KK', 16, 4),
('NPWP', 'NPWP', 15, 5),
('KITAS', 'KITAS', NULL, 6),
('KITAP', 'KITAP', NULL, 7),
('Lainnya', 'LAINNYA', NULL, 99);

-- Update tabel addresses untuk menggunakan referensi ke jenis alamat
ALTER TABLE addresses
ADD COLUMN jenis_alamat_id INT NULL AFTER label,
ADD FOREIGN KEY (jenis_alamat_id) REFERENCES ref_jenis_alamat(id) ON DELETE SET NULL,
ADD INDEX idx_jenis_alamat_id (jenis_alamat_id);

-- Migrasi data dari label ke jenis_alamat_id
UPDATE addresses a
LEFT JOIN ref_jenis_alamat rja ON a.label = rja.nama
SET a.jenis_alamat_id = rja.id
WHERE a.label IS NOT NULL;

-- Set default jika label kosong atau tidak match
UPDATE addresses SET jenis_alamat_id = 1 WHERE jenis_alamat_id IS NULL;

-- Update tabel people untuk menambah kolom jenis identitas
ALTER TABLE people
ADD COLUMN jenis_identitas_id INT NULL AFTER ktp,
ADD COLUMN nomor_identitas VARCHAR(50) NULL AFTER jenis_identitas_id,
ADD FOREIGN KEY (jenis_identitas_id) REFERENCES ref_jenis_identitas(id) ON DELETE SET NULL,
ADD INDEX idx_jenis_identitas_id (jenis_identitas_id),
ADD INDEX idx_nomor_identitas (nomor_identitas);

-- Migrasi data dari ktp ke nomor_identitas
UPDATE people
SET nomor_identitas = ktp,
    jenis_identitas_id = 1 -- Default KTP
WHERE ktp IS NOT NULL;

-- Tambah constraint unique untuk nomor_identitas per jenis_identitas
ALTER TABLE people
ADD UNIQUE INDEX idx_unique_identitas (jenis_identitas_id, nomor_identitas);
