-- Migration: Payment Methods Support
-- Date: 2026-05-07
-- Description: Add support for multiple payment types (bank, ewallet, qris, virtual_account, mobile_banking)

-- Add new columns to platform_bank_accounts table
ALTER TABLE `platform_bank_accounts`
ADD COLUMN `tipe_pembayaran` ENUM('bank', 'ewallet', 'qris', 'virtual_account', 'mobile_banking') NOT NULL DEFAULT 'bank' AFTER `nama_bank`,
ADD COLUMN `nomor_hp` VARCHAR(20) DEFAULT NULL AFTER `nomor_rekening`,
ADD COLUMN `qris_code` TEXT DEFAULT NULL AFTER `nomor_hp`,
ADD COLUMN `keterangan` TEXT DEFAULT NULL AFTER `qris_code`;

-- Update existing bank accounts to have tipe_pembayaran = 'bank'
UPDATE `platform_bank_accounts` SET `tipe_pembayaran` = 'bank' WHERE `tipe_pembayaran` IS NULL OR `tipe_pembayaran` = '';

-- Insert sample payment methods
INSERT INTO `platform_bank_accounts` (`nama_bank`, `nomor_rekening`, `nama_pemilik`, `cabang`, `tipe_pembayaran`, `nomor_hp`, `qris_code`, `keterangan`, `is_primary`, `created_at`) VALUES
('BCA', '1234567890', 'Koperasi Kewer', 'Jakarta', 'bank', NULL, NULL, 'Rekening utama untuk pembayaran', 1, NOW()),
('DANA', NULL, NULL, NULL, 'ewallet', '081234567890', NULL, 'E-wallet DANA', 0, NOW()),
('QRIS', NULL, NULL, NULL, 'qris', NULL, 'ID123456789012345678901234567890', 'QR Code untuk pembayaran', 0, NOW()),
('Sea Bank', NULL, NULL, NULL, 'virtual_account', NULL, NULL, 'Virtual Account Sea Bank', 0, NOW()),
('Mobile Banking', NULL, NULL, NULL, 'mobile_banking', '081234567891', NULL, 'Mobile Banking BCA', 0, NOW());

-- Create API endpoint for searching people data
-- (This is a PHP file, not SQL, but documented here for reference)
-- File: api/search_people.php
-- Purpose: Search people in db_orang by KTP or phone number
