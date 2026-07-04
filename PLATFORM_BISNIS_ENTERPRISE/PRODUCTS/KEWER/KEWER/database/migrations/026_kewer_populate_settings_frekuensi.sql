-- Migration 026: Populate setting_bunga & setting_denda with frekuensi_id
-- This ensures settings tables use the normalized frequency reference

USE kewer;

-- Disable foreign key checks
SET FOREIGN_KEY_CHECKS = 0;

-- Update setting_bunga to populate frekuensi_id from frekuensi enum
UPDATE setting_bunga SET frekuensi_id = 1 WHERE frekuensi = 'harian' AND (frekuensi_id IS NULL OR frekuensi_id = 0);
UPDATE setting_bunga SET frekuensi_id = 2 WHERE frekuensi = 'mingguan' AND (frekuensi_id IS NULL OR frekuensi_id = 0);
UPDATE setting_bunga SET frekuensi_id = 3 WHERE frekuensi = 'bulanan' AND (frekuensi_id IS NULL OR frekuensi_id = 0);

-- Update setting_denda to populate frekuensi_id from frekuensi enum
UPDATE setting_denda SET frekuensi_id = 1 WHERE frekuensi = 'harian' AND (frekuensi_id IS NULL OR frekuensi_id = 0);
UPDATE setting_denda SET frekuensi_id = 2 WHERE frekuensi = 'mingguan' AND (frekuensi_id IS NULL OR frekuensi_id = 0);
UPDATE setting_denda SET frekuensi_id = 3 WHERE frekuensi = 'bulanan' AND (frekuensi_id IS NULL OR frekuensi_id = 0);

-- Verification
SELECT 'Migration completed successfully' AS status;
SELECT COUNT(*) AS setting_bunga_updated FROM setting_bunga WHERE frekuensi_id IS NOT NULL;
SELECT COUNT(*) AS setting_denda_updated FROM setting_denda WHERE frekuensi_id IS NOT NULL;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;
