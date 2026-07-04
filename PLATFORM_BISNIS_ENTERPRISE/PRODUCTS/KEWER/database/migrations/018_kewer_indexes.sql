-- Migration 018: Tambah Indexes untuk Optimasi Query
-- Menambahkan indexes untuk query yang sering dilakukan
-- Priority: SEDANG

USE kewer;

-- ============================================
-- TABLE: users
-- ============================================

-- Index untuk query berdasarkan cabang
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
                  WHERE TABLE_SCHEMA = 'kewer' 
                  AND TABLE_NAME = 'users' 
                  AND INDEX_NAME = 'idx_users_cabang');

SET @sql = IF(@idx_exists = 0, 
    'CREATE INDEX idx_users_cabang ON users(cabang_id)',
    'SELECT "Index idx_users_cabang already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Index untuk query berdasarkan owner_bos
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
                  WHERE TABLE_SCHEMA = 'kewer' 
                  AND TABLE_NAME = 'users' 
                  AND INDEX_NAME = 'idx_users_owner_bos');

SET @sql = IF(@idx_exists = 0, 
    'CREATE INDEX idx_users_owner_bos ON users(owner_bos_id)',
    'SELECT "Index idx_users_owner_bos already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Composite index untuk role dan status
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
                  WHERE TABLE_SCHEMA = 'kewer' 
                  AND TABLE_NAME = 'users' 
                  AND INDEX_NAME = 'idx_users_role_status');

SET @sql = IF(@idx_exists = 0, 
    'CREATE INDEX idx_users_role_status ON users(role, status)',
    'SELECT "Index idx_users_role_status already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================
-- TABLE: nasabah
-- ============================================

-- Index untuk query berdasarkan cabang
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
                  WHERE TABLE_SCHEMA = 'kewer' 
                  AND TABLE_NAME = 'nasabah' 
                  AND INDEX_NAME = 'idx_nasabah_cabang');

SET @sql = IF(@idx_exists = 0, 
    'CREATE INDEX idx_nasabah_cabang ON nasabah(cabang_id)',
    'SELECT "Index idx_nasabah_cabang already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Index untuk query berdasarkan owner_bos
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
                  WHERE TABLE_SCHEMA = 'kewer' 
                  AND TABLE_NAME = 'nasabah' 
                  AND INDEX_NAME = 'idx_nasabah_owner_bos');

SET @sql = IF(@idx_exists = 0, 
    'CREATE INDEX idx_nasabah_owner_bos ON nasabah(owner_bos_id)',
    'SELECT "Index idx_nasabah_owner_bos already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Index untuk query berdasarkan status
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
                  WHERE TABLE_SCHEMA = 'kewer' 
                  AND TABLE_NAME = 'nasabah' 
                  AND INDEX_NAME = 'idx_nasabah_status');

SET @sql = IF(@idx_exists = 0, 
    'CREATE INDEX idx_nasabah_status ON nasabah(status)',
    'SELECT "Index idx_nasabah_status already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Index untuk query berdasarkan platform_blacklist
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
                  WHERE TABLE_SCHEMA = 'kewer' 
                  AND TABLE_NAME = 'nasabah' 
                  AND INDEX_NAME = 'idx_nasabah_platform_blacklist');

SET @sql = IF(@idx_exists = 0, 
    'CREATE INDEX idx_nasabah_platform_blacklist ON nasabah(platform_blacklist)',
    'SELECT "Index idx_nasabah_platform_blacklist already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================
-- TABLE: pinjaman
-- ============================================

-- Index untuk query berdasarkan nasabah
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
                  WHERE TABLE_SCHEMA = 'kewer' 
                  AND TABLE_NAME = 'pinjaman' 
                  AND INDEX_NAME = 'idx_pinjaman_nasabah');

SET @sql = IF(@idx_exists = 0, 
    'CREATE INDEX idx_pinjaman_nasabah ON pinjaman(nasabah_id)',
    'SELECT "Index idx_pinjaman_nasabah already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Index untuk query berdasarkan status
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
                  WHERE TABLE_SCHEMA = 'kewer' 
                  AND TABLE_NAME = 'pinjaman' 
                  AND INDEX_NAME = 'idx_pinjaman_status');

SET @sql = IF(@idx_exists = 0, 
    'CREATE INDEX idx_pinjaman_status ON pinjaman(status)',
    'SELECT "Index idx_pinjaman_status already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Index untuk query berdasarkan petugas
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
                  WHERE TABLE_SCHEMA = 'kewer' 
                  AND TABLE_NAME = 'pinjaman' 
                  AND INDEX_NAME = 'idx_pinjaman_petugas');

SET @sql = IF(@idx_exists = 0, 
    'CREATE INDEX idx_pinjaman_petugas ON pinjaman(petugas_id)',
    'SELECT "Index idx_pinjaman_petugas already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Index untuk query berdasarkan tanggal jatuh tempo
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
                  WHERE TABLE_SCHEMA = 'kewer' 
                  AND TABLE_NAME = 'pinjaman' 
                  AND INDEX_NAME = 'idx_pinjaman_tanggal_jatuh_tempo');

SET @sql = IF(@idx_exists = 0, 
    'CREATE INDEX idx_pinjaman_tanggal_jatuh_tempo ON pinjaman(tanggal_jatuh_tempo)',
    'SELECT "Index idx_pinjaman_tanggal_jatuh_tempo already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Composite index untuk status dan tanggal jatuh tempo (penting untuk query jatuh tempo)
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
                  WHERE TABLE_SCHEMA = 'kewer' 
                  AND TABLE_NAME = 'pinjaman' 
                  AND INDEX_NAME = 'idx_pinjaman_status_jatuh_tempo');

SET @sql = IF(@idx_exists = 0, 
    'CREATE INDEX idx_pinjaman_status_jatuh_tempo ON pinjaman(status, tanggal_jatuh_tempo)',
    'SELECT "Index idx_pinjaman_status_jatuh_tempo already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================
-- TABLE: angsuran
-- ============================================

-- Index untuk query berdasarkan pinjaman
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
                  WHERE TABLE_SCHEMA = 'kewer' 
                  AND TABLE_NAME = 'angsuran' 
                  AND INDEX_NAME = 'idx_angsuran_pinjaman');

SET @sql = IF(@idx_exists = 0, 
    'CREATE INDEX idx_angsuran_pinjaman ON angsuran(pinjaman_id)',
    'SELECT "Index idx_angsuran_pinjaman already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Index untuk query berdasarkan status
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
                  WHERE TABLE_SCHEMA = 'kewer' 
                  AND TABLE_NAME = 'angsuran' 
                  AND INDEX_NAME = 'idx_angsuran_status');

SET @sql = IF(@idx_exists = 0, 
    'CREATE INDEX idx_angsuran_status ON angsuran(status)',
    'SELECT "Index idx_angsuran_status already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Index untuk query berdasarkan jatuh tempo
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
                  WHERE TABLE_SCHEMA = 'kewer' 
                  AND TABLE_NAME = 'angsuran' 
                  AND INDEX_NAME = 'idx_angsuran_jatuh_tempo');

SET @sql = IF(@idx_exists = 0, 
    'CREATE INDEX idx_angsuran_jatuh_tempo ON angsuran(jatuh_tempo)',
    'SELECT "Index idx_angsuran_jatuh_tempo already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Composite index untuk status dan jatuh tempo (penting untuk query jatuh tempo)
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
                  WHERE TABLE_SCHEMA = 'kewer' 
                  AND TABLE_NAME = 'angsuran' 
                  AND INDEX_NAME = 'idx_angsuran_status_jatuh_tempo');

SET @sql = IF(@idx_exists = 0, 
    'CREATE INDEX idx_angsuran_status_jatuh_tempo ON angsuran(status, jatuh_tempo)',
    'SELECT "Index idx_angsuran_status_jatuh_tempo already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================
-- TABLE: pembayaran
-- ============================================

-- Index untuk query berdasarkan pinjaman
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
                  WHERE TABLE_SCHEMA = 'kewer' 
                  AND TABLE_NAME = 'pembayaran' 
                  AND INDEX_NAME = 'idx_pembayaran_pinjaman');

SET @sql = IF(@idx_exists = 0, 
    'CREATE INDEX idx_pembayaran_pinjaman ON pembayaran(pinjaman_id)',
    'SELECT "Index idx_pembayaran_pinjaman already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Index untuk query berdasarkan angsuran
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
                  WHERE TABLE_SCHEMA = 'kewer' 
                  AND TABLE_NAME = 'pembayaran' 
                  AND INDEX_NAME = 'idx_pembayaran_angsuran');

SET @sql = IF(@idx_exists = 0, 
    'CREATE INDEX idx_pembayaran_angsuran ON pembayaran(angsuran_id)',
    'SELECT "Index idx_pembayaran_angsuran already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Index untuk query berdasarkan tanggal bayar
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
                  WHERE TABLE_SCHEMA = 'kewer' 
                  AND TABLE_NAME = 'pembayaran' 
                  AND INDEX_NAME = 'idx_pembayaran_tanggal');

SET @sql = IF(@idx_exists = 0, 
    'CREATE INDEX idx_pembayaran_tanggal ON pembayaran(tanggal_bayar)',
    'SELECT "Index idx_pembayaran_tanggal already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Index untuk query berdasarkan petugas
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
                  WHERE TABLE_SCHEMA = 'kewer' 
                  AND TABLE_NAME = 'pembayaran' 
                  AND INDEX_NAME = 'idx_pembayaran_petugas');

SET @sql = IF(@idx_exists = 0, 
    'CREATE INDEX idx_pembayaran_petugas ON pembayaran(petugas_id)',
    'SELECT "Index idx_pembayaran_petugas already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================
-- TABLE: cabang
-- ============================================

-- Index untuk query berdasarkan owner_bos
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
                  WHERE TABLE_SCHEMA = 'kewer' 
                  AND TABLE_NAME = 'cabang' 
                  AND INDEX_NAME = 'idx_cabang_owner_bos');

SET @sql = IF(@idx_exists = 0, 
    'CREATE INDEX idx_cabang_owner_bos ON cabang(owner_bos_id)',
    'SELECT "Index idx_cabang_owner_bos already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================
-- TABLE: kas_bon
-- ============================================

-- Index untuk query berdasarkan karyawan
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
                  WHERE TABLE_SCHEMA = 'kewer' 
                  AND TABLE_NAME = 'kas_bon' 
                  AND INDEX_NAME = 'idx_kasbon_karyawan');

SET @sql = IF(@idx_exists = 0, 
    'CREATE INDEX idx_kasbon_karyawan ON kas_bon(karyawan_id)',
    'SELECT "Index idx_kasbon_karyawan already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Index untuk query berdasarkan tanggal pengajuan
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
                  WHERE TABLE_SCHEMA = 'kewer' 
                  AND TABLE_NAME = 'kas_bon' 
                  AND INDEX_NAME = 'idx_kasbon_tanggal_pengajuan');

SET @sql = IF(@idx_exists = 0, 
    'CREATE INDEX idx_kasbon_tanggal_pengajuan ON kas_bon(tanggal_pengajuan)',
    'SELECT "Index idx_kasbon_tanggal_pengajuan already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================
-- TABLE: pengeluaran
-- ============================================

-- Index untuk query berdasarkan tanggal
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
                  WHERE TABLE_SCHEMA = 'kewer' 
                  AND TABLE_NAME = 'pengeluaran' 
                  AND INDEX_NAME = 'idx_pengeluaran_tanggal');

SET @sql = IF(@idx_exists = 0, 
    'CREATE INDEX idx_pengeluaran_tanggal ON pengeluaran(tanggal)',
    'SELECT "Index idx_pengeluaran_tanggal already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================
-- TABLE: jurnal
-- ============================================

-- Index untuk query berdasarkan tanggal jurnal
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
                  WHERE TABLE_SCHEMA = 'kewer' 
                  AND TABLE_NAME = 'jurnal' 
                  AND INDEX_NAME = 'idx_jurnal_tanggal');

SET @sql = IF(@idx_exists = 0, 
    'CREATE INDEX idx_jurnal_tanggal ON jurnal(tanggal_jurnal)',
    'SELECT "Index idx_jurnal_tanggal already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verification
SELECT 'Migration completed successfully' AS status;
SELECT COUNT(*) AS total_indexes_added FROM INFORMATION_SCHEMA.STATISTICS 
WHERE TABLE_SCHEMA = 'kewer' 
AND INDEX_NAME LIKE 'idx_%';
