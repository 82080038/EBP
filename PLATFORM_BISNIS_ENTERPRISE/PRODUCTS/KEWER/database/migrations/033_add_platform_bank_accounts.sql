-- Migration 033: Add platform_bank_accounts table
-- Date: 8 Mei 2026
-- Description: Table untuk menyimpan rekening bank platform untuk pembayaran

CREATE TABLE IF NOT EXISTS platform_bank_accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bank_name VARCHAR(100) NOT NULL COMMENT 'Nama bank',
    account_number VARCHAR(50) NOT NULL COMMENT 'Nomor rekening',
    account_name VARCHAR(100) NOT NULL COMMENT 'Nama pemilik rekening',
    tipe_pembayaran ENUM('bank', 'mobile_banking', 'ewallet', 'qris', 'virtual_account') DEFAULT 'bank' COMMENT 'Tipe pembayaran',
    nomor_rekening VARCHAR(50) COMMENT 'Nomor rekening (untuk bank, mobile_banking, virtual_account)',
    nomor_hp VARCHAR(20) COMMENT 'Nomor HP (untuk ewallet)',
    nama_pemilik VARCHAR(100) COMMENT 'Nama pemilik rekening',
    cabang VARCHAR(100) COMMENT 'Nama cabang bank',
    bank_code VARCHAR(10) COMMENT 'Kode bank (misal: 014 untuk BCA)',
    branch VARCHAR(100) COMMENT 'Nama cabang bank (deprecated, use cabang)',
    is_primary BOOLEAN DEFAULT FALSE COMMENT 'Apakah rekening utama',
    is_active BOOLEAN DEFAULT TRUE COMMENT 'Status aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Tanggal dibuat',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Tanggal diupdate',
    INDEX idx_primary_active (is_primary, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Rekening bank platform untuk pembayaran';

-- Insert default bank account
INSERT INTO platform_bank_accounts (bank_name, account_number, account_name, tipe_pembayaran, nomor_rekening, nama_pemilik, cabang, bank_code, branch, is_primary, is_active)
VALUES ('BCA', '1234567890', 'Kewer Platform', 'bank', '1234567890', 'Kewer Platform', 'Jakarta Pusat', '014', 'Jakarta Pusat', 1, 1)
ON DUPLICATE KEY UPDATE account_name = VALUES(account_name);
