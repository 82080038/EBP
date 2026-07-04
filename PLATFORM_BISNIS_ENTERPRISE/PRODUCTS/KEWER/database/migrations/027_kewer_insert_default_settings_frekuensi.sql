-- Migration 027: Insert default settings for setting_bunga & setting_denda
-- Insert default settings for harian, mingguan, and bulanan with frekuensi_id

USE kewer;

-- Disable foreign key checks
SET FOREIGN_KEY_CHECKS = 0;

-- Insert default bunga settings
-- Harian: lower interest rate for short-term loans
INSERT INTO setting_bunga (jenis_pinjaman, frekuensi, frekuensi_id, tenor_min, tenor_max, bunga_default, bunga_min, bunga_max, created_at, updated_at)
VALUES ('umum', 'harian', 1, 1, 100, 0.5, 0.3, 1.0, NOW(), NOW())
ON DUPLICATE KEY UPDATE frekuensi_id = VALUES(frekuensi_id);

-- Mingguan: medium interest rate
INSERT INTO setting_bunga (jenis_pinjaman, frekuensi, frekuensi_id, tenor_min, tenor_max, bunga_default, bunga_min, bunga_max, created_at, updated_at)
VALUES ('umum', 'mingguan', 2, 1, 52, 1.0, 0.5, 2.0, NOW(), NOW())
ON DUPLICATE KEY UPDATE frekuensi_id = VALUES(frekuensi_id);

-- Bulanan: higher interest rate for long-term loans
INSERT INTO setting_bunga (jenis_pinjaman, frekuensi, frekuensi_id, tenor_min, tenor_max, bunga_default, bunga_min, bunga_max, created_at, updated_at)
VALUES ('umum', 'bulanan', 3, 1, 36, 2.0, 1.0, 3.0, NOW(), NOW())
ON DUPLICATE KEY UPDATE frekuensi_id = VALUES(frekuensi_id);

-- Insert default denda settings
-- Harian: higher daily penalty for short-term loans
INSERT INTO setting_denda (cabang_id, frekuensi, frekuensi_id, tipe_denda, nilai_denda, denda_maksimal, grace_period, bisa_waive, created_at, updated_at)
VALUES (NULL, 'harian', 1, 'persentase', 0.1, 100000, 0, 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE frekuensi_id = VALUES(frekuensi_id);

-- Mingguan: medium daily penalty
INSERT INTO setting_denda (cabang_id, frekuensi, frekuensi_id, tipe_denda, nilai_denda, denda_maksimal, grace_period, bisa_waive, created_at, updated_at)
VALUES (NULL, 'mingguan', 2, 'persentase', 0.05, 200000, 3, 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE frekuensi_id = VALUES(frekuensi_id);

-- Bulanan: lower daily penalty with longer grace period
INSERT INTO setting_denda (cabang_id, frekuensi, frekuensi_id, tipe_denda, nilai_denda, denda_maksimal, grace_period, bisa_waive, created_at, updated_at)
VALUES (NULL, 'bulanan', 3, 'persentase', 0.03, 500000, 7, 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE frekuensi_id = VALUES(frekuensi_id);

-- Verification
SELECT 'Migration completed successfully' AS status;
SELECT COUNT(*) AS setting_bunga_inserted FROM setting_bunga WHERE frekuensi_id IS NOT NULL;
SELECT COUNT(*) AS setting_denda_inserted FROM setting_denda WHERE frekuensi_id IS NOT NULL;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;
