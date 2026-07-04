-- Migration: Normalisasi db_orang dengan tabel-tabel master
-- Tanggal: 2026-05-07
-- Deskripsi: Membuat tabel master untuk normalisasi data identitas orang Indonesia

USE db_orang;

-- Drop tables if they exist (for clean migration)
DROP TABLE IF EXISTS ref_agama;
DROP TABLE IF EXISTS ref_jenis_kelamin;
DROP TABLE IF EXISTS ref_golongan_darah;
DROP TABLE IF EXISTS ref_status_perkawinan;
DROP TABLE IF EXISTS ref_suku;
DROP TABLE IF EXISTS ref_pekerjaan;

-- Tabel Master Agama
CREATE TABLE ref_agama (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(50) NOT NULL UNIQUE,
    kode VARCHAR(20) NOT NULL UNIQUE,
    urutan INT NOT NULL DEFAULT 0,
    is_aktif TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Referensi agama resmi di Indonesia';

-- Insert data agama (6 agama resmi di Indonesia)
INSERT INTO ref_agama (nama, kode, urutan) VALUES
('Islam', 'ISLAM', 1),
('Kristen Protestan', 'KRISTEN_PROTESTAN', 2),
('Katolik', 'KATOLIK', 3),
('Hindu', 'HINDU', 4),
('Buddha', 'BUDDHA', 5),
('Konghucu', 'KONGHUCU', 6);

-- Tabel Master Jenis Kelamin
CREATE TABLE IF NOT EXISTS ref_jenis_kelamin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(20) NOT NULL UNIQUE,
    kode VARCHAR(10) NOT NULL UNIQUE,
    urutan INT NOT NULL DEFAULT 0,
    is_aktif TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Referensi jenis kelamin';

-- Insert data jenis kelamin
INSERT INTO ref_jenis_kelamin (nama, kode, urutan) VALUES
('Laki-laki', 'L', 1),
('Perempuan', 'P', 2);

-- Tabel Master Golongan Darah
CREATE TABLE IF NOT EXISTS ref_golongan_darah (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(10) NOT NULL UNIQUE,
    kode VARCHAR(5) NOT NULL UNIQUE,
    urutan INT NOT NULL DEFAULT 0,
    is_aktif TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Referensi golongan darah';

-- Insert data golongan darah
INSERT INTO ref_golongan_darah (nama, kode, urutan) VALUES
('A', 'A', 1),
('B', 'B', 2),
('AB', 'AB', 3),
('O', 'O', 4);

-- Tabel Master Status Perkawinan
CREATE TABLE IF NOT EXISTS ref_status_perkawinan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(50) NOT NULL UNIQUE,
    kode VARCHAR(20) NOT NULL UNIQUE,
    urutan INT NOT NULL DEFAULT 0,
    is_aktif TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Referensi status perkawinan sesuai standar KTP';

-- Insert data status perkawinan
INSERT INTO ref_status_perkawinan (nama, kode, urutan) VALUES
('Belum Kawin', 'BELUM_KAWIN', 1),
('Kawin', 'KAWIN', 2),
('Cerai Hidup', 'CERAI_HIDUP', 3),
('Cerai Mati', 'CERAI_MATI', 4);

-- Tabel Master Suku (suku-suku utama di Indonesia)
CREATE TABLE IF NOT EXISTS ref_suku (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    provinsi VARCHAR(50) NULL,
    urutan INT NOT NULL DEFAULT 0,
    is_aktif TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY (nama, provinsi)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Referensi suku bangsa di Indonesia';

-- Insert data suku-suku utama (top 20 suku terbanyak)
INSERT INTO ref_suku (nama, provinsi, urutan) VALUES
('Jawa', 'Jawa Tengah', 1),
('Sunda', 'Jawa Barat', 2),
('Batak', 'Sumatera Utara', 3),
('Minangkabau', 'Sumatera Barat', 4),
('Bugis', 'Sulawesi Selatan', 5),
('Madura', 'Jawa Timur', 6),
('Betawi', 'DKI Jakarta', 7),
('Bali', 'Bali', 8),
('Banjar', 'Kalimantan Selatan', 9),
('Aceh', 'Aceh', 10),
('Dayak', 'Kalimantan Tengah', 11),
('Sasak', 'Nusa Tenggara Barat', 12),
('Makassar', 'Sulawesi Selatan', 13),
('Papua', 'Papua', 14),
('Tionghoa', NULL, 15),
('Arab', NULL, 16),
('Minahasa', 'Sulawesi Utara', 17),
('Gorontalo', 'Gorontalo', 18),
('Nias', 'Sumatera Utara', 19),
('Melayu', 'Riau', 20);

-- Tabel Master Pekerjaan (opsional)
CREATE TABLE IF NOT EXISTS ref_pekerjaan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL UNIQUE,
    kategori VARCHAR(50) NULL,
    urutan INT NOT NULL DEFAULT 0,
    is_aktif TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Referensi pekerjaan';

-- Insert data pekerjaan umum
INSERT INTO ref_pekerjaan (nama, kategori, urutan) VALUES
('Pedagang', 'Wiraswasta', 1),
('Petani', 'Pertanian', 2),
('Nelayan', 'Perikanan', 3),
('Buruh', 'Buruh', 4),
('PNS', 'Pemerintahan', 5),
('Wiraswasta', 'Wiraswasta', 6),
('Karyawan Swasta', 'Swasta', 7),
('Ibu Rumah Tangga', 'Rumah Tangga', 8),
('Pelajar/Mahasiswa', 'Pendidikan', 9),
('Lainnya', NULL, 99);

-- Update tabel people untuk menggunakan referensi ke tabel master
ALTER TABLE people
ADD COLUMN agama_id INT NULL AFTER agama,
ADD COLUMN jenis_kelamin_id INT NULL AFTER jenis_kelamin,
ADD COLUMN golongan_darah_id INT NULL AFTER pekerjaan,
ADD COLUMN suku_id INT NULL AFTER golongan_darah_id,
ADD COLUMN status_perkawinan_id INT NULL AFTER suku_id,
ADD COLUMN pekerjaan_id INT NULL AFTER status_perkawinan_id,
ADD FOREIGN KEY (agama_id) REFERENCES ref_agama(id) ON DELETE SET NULL,
ADD FOREIGN KEY (jenis_kelamin_id) REFERENCES ref_jenis_kelamin(id) ON DELETE SET NULL,
ADD FOREIGN KEY (golongan_darah_id) REFERENCES ref_golongan_darah(id) ON DELETE SET NULL,
ADD FOREIGN KEY (suku_id) REFERENCES ref_suku(id) ON DELETE SET NULL,
ADD FOREIGN KEY (status_perkawinan_id) REFERENCES ref_status_perkawinan(id) ON DELETE SET NULL,
ADD FOREIGN KEY (pekerjaan_id) REFERENCES ref_pekerjaan(id) ON DELETE SET NULL,
ADD INDEX idx_agama_id (agama_id),
ADD INDEX idx_jenis_kelamin_id (jenis_kelamin_id),
ADD INDEX idx_golongan_darah_id (golongan_darah_id),
ADD INDEX idx_suku_id (suku_id),
ADD INDEX idx_status_perkawinan_id (status_perkawinan_id),
ADD INDEX idx_pekerjaan_id (pekerjaan_id);

-- Migrasi data dari kolom lama ke kolom baru
UPDATE people p
LEFT JOIN ref_agama ra ON p.agama = ra.nama
SET p.agama_id = ra.id
WHERE p.agama IS NOT NULL;

UPDATE people p
LEFT JOIN ref_jenis_kelamin rjk ON p.jenis_kelamin = rjk.kode
SET p.jenis_kelamin_id = rjk.id
WHERE p.jenis_kelamin IS NOT NULL;

-- (Opsional) Set default values jika data kosong
-- UPDATE people SET agama_id = 1 WHERE agama_id IS NULL;
