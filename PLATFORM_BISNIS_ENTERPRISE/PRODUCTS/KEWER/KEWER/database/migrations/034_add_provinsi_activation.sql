-- Migration 034: Add provinsi_activation table
-- Date: 2026-05-10
-- Description: Tabel untuk appOwner mengelola provinsi yang aktif untuk pendaftaran koperasi

CREATE TABLE IF NOT EXISTS provinsi_activation (
    id INT AUTO_INCREMENT PRIMARY KEY,
    province_id VARCHAR(20) NOT NULL UNIQUE COMMENT 'ID provinsi (mengacu ke db_alamat.province)',
    province_name VARCHAR(100) NOT NULL COMMENT 'Nama provinsi',
    is_active TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = aktif untuk pendaftaran, 0 = non-aktif',
    activated_by INT NULL COMMENT 'User ID yang mengaktifkan (FK ke users.id)',
    activated_at TIMESTAMP NULL COMMENT 'Waktu diaktifkan',
    deactivated_at TIMESTAMP NULL COMMENT 'Waktu dinonaktifkan terakhir',
    notes TEXT NULL COMMENT 'Catatan',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_is_active (is_active),
    KEY idx_activated_by (activated_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
COMMENT='Status aktivasi provinsi untuk pendaftaran koperasi (dikelola appOwner)';

-- Seed default: aktifkan Sumatera Utara (sesuai memory aplikasi fokus Sumut)
INSERT INTO provinsi_activation (province_id, province_name, is_active, activated_at, notes)
VALUES ('12', 'SUMATERA UTARA', 1, CURRENT_TIMESTAMP, 'Default aktif')
ON DUPLICATE KEY UPDATE province_name = VALUES(province_name);
