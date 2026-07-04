-- Migration 025: Populate frekuensi_id in angsuran table from pinjaman
-- This ensures backward compatibility for existing angsuran records

USE kewer;

-- Disable foreign key checks
SET FOREIGN_KEY_CHECKS = 0;

-- Update angsuran to populate frekuensi_id from pinjaman
UPDATE angsuran a
INNER JOIN pinjaman p ON a.pinjaman_id = p.id
SET a.frekuensi_id = p.frekuensi_id,
    a.frekuensi = p.frekuensi
WHERE a.frekuensi_id IS NULL OR a.frekuensi_id = 0;

-- Verification
SELECT 'Migration completed successfully' AS status;
SELECT COUNT(*) AS angsuran_updated FROM angsuran WHERE frekuensi_id IS NOT NULL;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;
