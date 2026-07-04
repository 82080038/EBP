-- Migration 017: Tambah Foreign Key Constraints
-- Menambahkan foreign key constraints untuk menjaga integritas data
-- Priority: KRITIS

USE kewer;

-- Disable foreign key checks temporarily
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- TABLE: users
-- ============================================

-- FK users.cabang_id -> cabang.id
SET @fk_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                  WHERE TABLE_SCHEMA = 'kewer' 
                  AND TABLE_NAME = 'users' 
                  AND CONSTRAINT_NAME = 'fk_users_cabang');

SET @sql = IF(@fk_exists = 0, 
    'ALTER TABLE users ADD CONSTRAINT fk_users_cabang FOREIGN KEY (cabang_id) REFERENCES cabang(id) ON DELETE SET NULL ON UPDATE CASCADE',
    'SELECT "FK fk_users_cabang already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- FK users.owner_bos_id -> users.id (self-reference)
-- Skip this for now as it requires special handling for self-referencing
-- SET @fk_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
--                   WHERE TABLE_SCHEMA = 'kewer' 
--                   AND TABLE_NAME = 'users' 
--                   AND CONSTRAINT_NAME = 'fk_users_owner_bos');

-- SET @sql = IF(@fk_exists = 0, 
--     'ALTER TABLE users ADD CONSTRAINT fk_users_owner_bos FOREIGN KEY (owner_bos_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE',
--     'SELECT "FK fk_users_owner_bos already exists" AS message');

-- PREPARE stmt FROM @sql;
-- EXECUTE stmt;
-- DEALLOCATE PREPARE stmt;

-- FK users.db_orang_person_id -> db_orang.people.id
SET @fk_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                  WHERE TABLE_SCHEMA = 'kewer' 
                  AND TABLE_NAME = 'users' 
                  AND CONSTRAINT_NAME = 'fk_users_db_orang_person');

SET @sql = IF(@fk_exists = 0, 
    'ALTER TABLE users ADD CONSTRAINT fk_users_db_orang_person FOREIGN KEY (db_orang_person_id) REFERENCES db_orang.people(id) ON DELETE SET NULL ON UPDATE CASCADE',
    'SELECT "FK fk_users_db_orang_person already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================
-- TABLE: cabang
-- ============================================

-- FK cabang.owner_bos_id -> users.id
-- Skip for now due to index issue on users.id
-- SET @fk_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
--                   WHERE TABLE_SCHEMA = 'kewer' 
--                   AND TABLE_NAME = 'cabang' 
--                   AND CONSTRAINT_NAME = 'fk_cabang_owner_bos');

-- SET @sql = IF(@fk_exists = 0, 
--     'ALTER TABLE cabang ADD CONSTRAINT fk_cabang_owner_bos FOREIGN KEY (owner_bos_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE',
--     'SELECT "FK fk_cabang_owner_bos already exists" AS message');

-- PREPARE stmt FROM @sql;
-- EXECUTE stmt;
-- DEALLOCATE PREPARE stmt;

-- FK cabang.headquarters_id -> cabang.id (self-reference)
-- Skip for now due to self-referencing index issue
-- SET @fk_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
--                   WHERE TABLE_SCHEMA = 'kewer' 
--                   AND TABLE_NAME = 'cabang' 
--                   AND CONSTRAINT_NAME = 'fk_cabang_headquarters');

-- SET @sql = IF(@fk_exists = 0, 
--     'ALTER TABLE cabang ADD CONSTRAINT fk_cabang_headquarters FOREIGN KEY (headquarters_id) REFERENCES cabang(id) ON DELETE SET NULL ON UPDATE CASCADE',
--     'SELECT "FK fk_cabang_headquarters already exists" AS message');

-- PREPARE stmt FROM @sql;
-- EXECUTE stmt;
-- DEALLOCATE PREPARE stmt;

-- FK cabang.created_by_user_id -> users.id
-- Skip for now due to index issue on users.id
-- SET @fk_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
--                   WHERE TABLE_SCHEMA = 'kewer' 
--                   AND TABLE_NAME = 'cabang' 
--                   AND CONSTRAINT_NAME = 'fk_cabang_created_by');

-- SET @sql = IF(@fk_exists = 0, 
--     'ALTER TABLE cabang ADD CONSTRAINT fk_cabang_created_by FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE',
--     'SELECT "FK fk_cabang_created_by already exists" AS message');

-- PREPARE stmt FROM @sql;
-- EXECUTE stmt;
-- DEALLOCATE PREPARE stmt;

-- FK cabang.db_orang_person_id -> db_orang.people.id
SET @fk_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                  WHERE TABLE_SCHEMA = 'kewer' 
                  AND TABLE_NAME = 'cabang' 
                  AND CONSTRAINT_NAME = 'fk_cabang_db_orang_person');

SET @sql = IF(@fk_exists = 0, 
    'ALTER TABLE cabang ADD CONSTRAINT fk_cabang_db_orang_person FOREIGN KEY (db_orang_person_id) REFERENCES db_orang.people(id) ON DELETE SET NULL ON UPDATE CASCADE',
    'SELECT "FK fk_cabang_db_orang_person already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================
-- TABLE: nasabah
-- ============================================

-- FK nasabah.cabang_id -> cabang.id
SET @fk_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                  WHERE TABLE_SCHEMA = 'kewer' 
                  AND TABLE_NAME = 'nasabah' 
                  AND CONSTRAINT_NAME = 'fk_nasabah_cabang');

SET @sql = IF(@fk_exists = 0, 
    'ALTER TABLE nasabah ADD CONSTRAINT fk_nasabah_cabang FOREIGN KEY (cabang_id) REFERENCES cabang(id) ON DELETE SET NULL ON UPDATE CASCADE',
    'SELECT "FK fk_nasabah_cabang already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- FK nasabah.owner_bos_id -> users.id
-- Skip for now due to index issue on users.id
-- SET @fk_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
--                   WHERE TABLE_SCHEMA = 'kewer' 
--                   AND TABLE_NAME = 'nasabah' 
--                   AND CONSTRAINT_NAME = 'fk_nasabah_owner_bos');

-- SET @sql = IF(@fk_exists = 0, 
--     'ALTER TABLE nasabah ADD CONSTRAINT fk_nasabah_owner_bos FOREIGN KEY (owner_bos_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE',
--     'SELECT "FK fk_nasabah_owner_bos already exists" AS message');

-- PREPARE stmt FROM @sql;
-- EXECUTE stmt;
-- DEALLOCATE PREPARE stmt;

-- FK nasabah.referensi_nasabah_id -> nasabah.id (self-reference)
SET @fk_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                  WHERE TABLE_SCHEMA = 'kewer' 
                  AND TABLE_NAME = 'nasabah' 
                  AND CONSTRAINT_NAME = 'fk_nasabah_referensi');

SET @sql = IF(@fk_exists = 0, 
    'ALTER TABLE nasabah ADD CONSTRAINT fk_nasabah_referensi FOREIGN KEY (referensi_nasabah_id) REFERENCES nasabah(id) ON DELETE SET NULL ON UPDATE CASCADE',
    'SELECT "FK fk_nasabah_referensi already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- FK nasabah.db_orang_user_id -> db_orang.people.id
SET @fk_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                  WHERE TABLE_SCHEMA = 'kewer' 
                  AND TABLE_NAME = 'nasabah' 
                  AND CONSTRAINT_NAME = 'fk_nasabah_db_orang_user');

SET @sql = IF(@fk_exists = 0, 
    'ALTER TABLE nasabah ADD CONSTRAINT fk_nasabah_db_orang_user FOREIGN KEY (db_orang_user_id) REFERENCES db_orang.people(id) ON DELETE SET NULL ON UPDATE CASCADE',
    'SELECT "FK fk_nasabah_db_orang_user already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- FK nasabah.db_orang_address_id -> db_orang.addresses.id
SET @fk_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                  WHERE TABLE_SCHEMA = 'kewer' 
                  AND TABLE_NAME = 'nasabah' 
                  AND CONSTRAINT_NAME = 'fk_nasabah_db_orang_address');

SET @sql = IF(@fk_exists = 0, 
    'ALTER TABLE nasabah ADD CONSTRAINT fk_nasabah_db_orang_address FOREIGN KEY (db_orang_address_id) REFERENCES db_orang.addresses(id) ON DELETE SET NULL ON UPDATE CASCADE',
    'SELECT "FK fk_nasabah_db_orang_address already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- FK nasabah.penjamin_id -> users.id
-- Skip for now due to index issue on users.id
-- SET @fk_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
--                   WHERE TABLE_SCHEMA = 'kewer' 
--                   AND TABLE_NAME = 'nasabah' 
--                   AND CONSTRAINT_NAME = 'fk_nasabah_penjamin');

-- SET @sql = IF(@fk_exists = 0, 
--     'ALTER TABLE nasabah ADD CONSTRAINT fk_nasabah_penjamin FOREIGN KEY (penjamin_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE',
--     'SELECT "FK fk_nasabah_penjamin already exists" AS message');

-- PREPARE stmt FROM @sql;
-- EXECUTE stmt;
-- DEALLOCATE PREPARE stmt;

-- ============================================
-- TABLE: pinjaman
-- ============================================

-- FK pinjaman.nasabah_id -> nasabah.id
SET @fk_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                  WHERE TABLE_SCHEMA = 'kewer' 
                  AND TABLE_NAME = 'pinjaman' 
                  AND CONSTRAINT_NAME = 'fk_pinjaman_nasabah');

SET @sql = IF(@fk_exists = 0, 
    'ALTER TABLE pinjaman ADD CONSTRAINT fk_pinjaman_nasabah FOREIGN KEY (nasabah_id) REFERENCES nasabah(id) ON DELETE RESTRICT ON UPDATE CASCADE',
    'SELECT "FK fk_pinjaman_nasabah already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- FK pinjaman.petugas_id -> users.id
-- Skip for now due to index issue on users.id
-- SET @fk_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
--                   WHERE TABLE_SCHEMA = 'kewer' 
--                   AND TABLE_NAME = 'pinjaman' 
--                   AND CONSTRAINT_NAME = 'fk_pinjaman_petugas');

-- SET @sql = IF(@fk_exists = 0, 
--     'ALTER TABLE pinjaman ADD CONSTRAINT fk_pinjaman_petugas FOREIGN KEY (petugas_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE',
--     'SELECT "FK fk_pinjaman_petugas already exists" AS message');

-- PREPARE stmt FROM @sql;
-- EXECUTE stmt;
-- DEALLOCATE PREPARE stmt;

-- FK pinjaman.approved_by -> users.id
-- Skip for now due to index issue on users.id
-- SET @fk_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
--                   WHERE TABLE_SCHEMA = 'kewer' 
--                   AND TABLE_NAME = 'pinjaman' 
--                   AND CONSTRAINT_NAME = 'fk_pinjaman_approved_by');

-- SET @sql = IF(@fk_exists = 0, 
--     'ALTER TABLE pinjaman ADD CONSTRAINT fk_pinjaman_approved_by FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE',
--     'SELECT "FK fk_pinjaman_approved_by already exists" AS message');

-- PREPARE stmt FROM @sql;
-- EXECUTE stmt;
-- DEALLOCATE PREPARE stmt;

-- FK pinjaman.rejected_by -> users.id
-- Skip for now due to index issue on users.id
-- SET @fk_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
--                   WHERE TABLE_SCHEMA = 'kewer' 
--                   AND TABLE_NAME = 'pinjaman' 
--                   AND CONSTRAINT_NAME = 'fk_pinjaman_rejected_by');

-- SET @sql = IF(@fk_exists = 0, 
--     'ALTER TABLE pinjaman ADD CONSTRAINT fk_pinjaman_rejected_by FOREIGN KEY (rejected_by) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE',
--     'SELECT "FK fk_pinjaman_rejected_by already exists" AS message');

-- PREPARE stmt FROM @sql;
-- EXECUTE stmt;
-- DEALLOCATE PREPARE stmt;

-- FK pinjaman.override_oleh -> users.id
-- Skip for now due to index issue on users.id
-- SET @fk_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
--                   WHERE TABLE_SCHEMA = 'kewer' 
--                   AND TABLE_NAME = 'pinjaman' 
--                   AND CONSTRAINT_NAME = 'fk_pinjaman_override_oleh');

-- SET @sql = IF(@fk_exists = 0, 
--     'ALTER TABLE pinjaman ADD CONSTRAINT fk_pinjaman_override_oleh FOREIGN KEY (override_oleh) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE',
--     'SELECT "FK fk_pinjaman_override_oleh already exists" AS message');

-- PREPARE stmt FROM @sql;
-- EXECUTE stmt;
-- DEALLOCATE PREPARE stmt;

-- FK pinjaman.auto_confirmed_by -> users.id
-- Skip for now due to index issue on users.id
-- SET @fk_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
--                   WHERE TABLE_SCHEMA = 'kewer' 
--                   AND TABLE_NAME = 'pinjaman' 
--                   AND CONSTRAINT_NAME = 'fk_pinjaman_auto_confirmed_by');

-- SET @sql = IF(@fk_exists = 0, 
--     'ALTER TABLE pinjaman ADD CONSTRAINT fk_pinjaman_auto_confirmed_by FOREIGN KEY (auto_confirmed_by) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE',
--     'SELECT "FK fk_pinjaman_auto_confirmed_by already exists" AS message');

-- PREPARE stmt FROM @sql;
-- EXECUTE stmt;
-- DEALLOCATE PREPARE stmt;

-- ============================================
-- TABLE: angsuran
-- ============================================

-- FK angsuran.pinjaman_id -> pinjaman.id
SET @fk_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                  WHERE TABLE_SCHEMA = 'kewer' 
                  AND TABLE_NAME = 'angsuran' 
                  AND CONSTRAINT_NAME = 'fk_angsuran_pinjaman');

SET @sql = IF(@fk_exists = 0, 
    'ALTER TABLE angsuran ADD CONSTRAINT fk_angsuran_pinjaman FOREIGN KEY (pinjaman_id) REFERENCES pinjaman(id) ON DELETE CASCADE ON UPDATE CASCADE',
    'SELECT "FK fk_angsuran_pinjaman already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- FK angsuran.petugas_id -> users.id
-- Skip for now due to index issue on users.id
-- SET @fk_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
--                   WHERE TABLE_SCHEMA = 'kewer' 
--                   AND TABLE_NAME = 'angsuran' 
--                   AND CONSTRAINT_NAME = 'fk_angsuran_petugas');

-- SET @sql = IF(@fk_exists = 0, 
--     'ALTER TABLE angsuran ADD CONSTRAINT fk_angsuran_petugas FOREIGN KEY (petugas_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE',
--     'SELECT "FK fk_angsuran_petugas already exists" AS message');

-- PREPARE stmt FROM @sql;
-- EXECUTE stmt;
-- DEALLOCATE PREPARE stmt;

-- FK angsuran.denda_waived_by -> users.id
-- Skip for now due to index issue on users.id
-- SET @fk_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
--                   WHERE TABLE_SCHEMA = 'kewer' 
--                   AND TABLE_NAME = 'angsuran' 
--                   AND CONSTRAINT_NAME = 'fk_angsuran_denda_waived_by');

-- SET @sql = IF(@fk_exists = 0, 
--     'ALTER TABLE angsuran ADD CONSTRAINT fk_angsuran_denda_waived_by FOREIGN KEY (denda_waived_by) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE',
--     'SELECT "FK fk_angsuran_denda_waived_by already exists" AS message');

-- PREPARE stmt FROM @sql;
-- EXECUTE stmt;
-- DEALLOCATE PREPARE stmt;

-- ============================================
-- TABLE: pembayaran
-- ============================================

-- FK pembayaran.pinjaman_id -> pinjaman.id
SET @fk_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                  WHERE TABLE_SCHEMA = 'kewer' 
                  AND TABLE_NAME = 'pembayaran' 
                  AND CONSTRAINT_NAME = 'fk_pembayaran_pinjaman');

SET @sql = IF(@fk_exists = 0, 
    'ALTER TABLE pembayaran ADD CONSTRAINT fk_pembayaran_pinjaman FOREIGN KEY (pinjaman_id) REFERENCES pinjaman(id) ON DELETE RESTRICT ON UPDATE CASCADE',
    'SELECT "FK fk_pembayaran_pinjaman already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- FK pembayaran.angsuran_id -> angsuran.id
-- Skip for now due to constraint options issue
-- SET @fk_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
--                   WHERE TABLE_SCHEMA = 'kewer' 
--                   AND TABLE_NAME = 'pembayaran' 
--                   AND CONSTRAINT_NAME = 'fk_pembayaran_angsuran');

-- SET @sql = IF(@fk_exists = 0, 
--     'ALTER TABLE pembayaran ADD CONSTRAINT fk_pembayaran_angsuran FOREIGN KEY (angsuran_id) REFERENCES angsuran(id) ON DELETE SET NULL ON UPDATE CASCADE',
--     'SELECT "FK fk_pembayaran_angsuran already exists" AS message');

-- PREPARE stmt FROM @sql;
-- EXECUTE stmt;
-- DEALLOCATE PREPARE stmt;

-- FK pembayaran.petugas_id -> users.id
-- Skip for now due to index issue on users.id
-- SET @fk_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
--                   WHERE TABLE_SCHEMA = 'kewer' 
--                   AND TABLE_NAME = 'pembayaran' 
--                   AND CONSTRAINT_NAME = 'fk_pembayaran_petugas');

-- SET @sql = IF(@fk_exists = 0, 
--     'ALTER TABLE pembayaran ADD CONSTRAINT fk_pembayaran_petugas FOREIGN KEY (petugas_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE',
--     'SELECT "FK fk_pembayaran_petugas already exists" AS message');

-- PREPARE stmt FROM @sql;
-- EXECUTE stmt;
-- DEALLOCATE PREPARE stmt;

-- FK pembayaran.petugas_pengganti_id -> users.id
-- Skip for now due to index issue on users.id
-- SET @fk_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
--                   WHERE TABLE_SCHEMA = 'kewer' 
--                   AND TABLE_NAME = 'pembayaran' 
--                   AND CONSTRAINT_NAME = 'fk_pembayaran_petugas_pengganti');

-- SET @sql = IF(@fk_exists = 0, 
--     'ALTER TABLE pembayaran ADD CONSTRAINT fk_pembayaran_petugas_pengganti FOREIGN KEY (petugas_pengganti_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE',
--     'SELECT "FK fk_pembayaran_petugas_pengganti already exists" AS message');

-- PREPARE stmt FROM @sql;
-- EXECUTE stmt;
-- DEALLOCATE PREPARE stmt;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Verification
SELECT 'Migration completed successfully' AS status;
SELECT COUNT(*) AS total_fk_added FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
WHERE TABLE_SCHEMA = 'kewer' 
AND CONSTRAINT_NAME LIKE 'fk_%';
