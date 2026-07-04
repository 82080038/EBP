-- Migration: Comprehensive Improvements untuk db_orang
-- Tanggal: 2026-05-07
-- Deskripsi: Implementasi seluruh improvement untuk database identitas orang

USE db_orang;

-- Disable foreign key checks for dropping tables
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- 1. Phone Number Normalization
-- ============================================
DROP TABLE IF EXISTS people_phones;
DROP TABLE IF EXISTS ref_jenis_telepon;

CREATE TABLE ref_jenis_telepon (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(50) NOT NULL UNIQUE,
    kode VARCHAR(20) NOT NULL UNIQUE,
    urutan INT NOT NULL DEFAULT 0,
    is_aktif TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Referensi jenis telepon';

INSERT INTO ref_jenis_telepon (nama, kode, urutan) VALUES
('Mobile', 'MOBILE', 1),
('Rumah', 'HOME', 2),
('Kantor', 'OFFICE', 3),
('WhatsApp', 'WHATSAPP', 4),
('Fax', 'FAX', 5),
('Lainnya', 'OTHER', 99);

CREATE TABLE people_phones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    person_id INT NOT NULL,
    phone_number VARCHAR(20) NOT NULL,
    jenis_telepon_id INT NOT NULL,
    is_primary TINYINT(1) NOT NULL DEFAULT 0,
    is_verified TINYINT(1) NOT NULL DEFAULT 0,
    verified_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE,
    FOREIGN KEY (jenis_telepon_id) REFERENCES ref_jenis_telepon(id) ON DELETE RESTRICT,
    UNIQUE KEY (person_id, phone_number),
    INDEX idx_person_id (person_id),
    INDEX idx_phone_number (phone_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Nomor telepon per orang';

-- Migrasi data telp dari people ke people_phones
INSERT INTO people_phones (person_id, phone_number, jenis_telepon_id, is_primary)
SELECT id, telp, 1, 1
FROM people
WHERE telp IS NOT NULL;

-- ============================================
-- 2. Email Normalization
-- ============================================
DROP TABLE IF EXISTS people_emails;
DROP TABLE IF EXISTS ref_jenis_email;

CREATE TABLE ref_jenis_email (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(50) NOT NULL UNIQUE,
    kode VARCHAR(20) NOT NULL UNIQUE,
    urutan INT NOT NULL DEFAULT 0,
    is_aktif TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Referensi jenis email';

INSERT INTO ref_jenis_email (nama, kode, urutan) VALUES
('Personal', 'PERSONAL', 1),
('Kantor', 'WORK', 2),
('Lainnya', 'OTHER', 99);

CREATE TABLE people_emails (
    id INT AUTO_INCREMENT PRIMARY KEY,
    person_id INT NOT NULL,
    email VARCHAR(100) NOT NULL,
    jenis_email_id INT NOT NULL,
    is_primary TINYINT(1) NOT NULL DEFAULT 0,
    is_verified TINYINT(1) NOT NULL DEFAULT 0,
    verified_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE,
    FOREIGN KEY (jenis_email_id) REFERENCES ref_jenis_email(id) ON DELETE RESTRICT,
    UNIQUE KEY (person_id, email),
    INDEX idx_person_id (person_id),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Email per orang';

-- Migrasi data email dari people ke people_emails
INSERT INTO people_emails (person_id, email, jenis_email_id, is_primary)
SELECT id, email, 1, 1
FROM people
WHERE email IS NOT NULL;

-- ============================================
-- 3. Name Field Splitting
-- ============================================
DROP TABLE IF EXISTS ref_jenis_gelar;

CREATE TABLE ref_jenis_gelar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(50) NOT NULL UNIQUE,
    kode VARCHAR(20) NOT NULL UNIQUE,
    posisi ENUM('depan', 'belakang') NOT NULL DEFAULT 'depan',
    urutan INT NOT NULL DEFAULT 0,
    is_aktif TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Referensi gelar';

INSERT IGNORE INTO ref_jenis_gelar (nama, kode, posisi, urutan) VALUES
('Dr.', 'DR', 'depan', 1),
('Ir.', 'IR', 'depan', 2),
('H.', 'H', 'depan', 3),
('Drs.', 'DRS', 'depan', 4),
('S.Kom', 'SKOM', 'belakang', 5),
('S.E.', 'SE', 'belakang', 6),
('M.M.', 'MM', 'belakang', 7),
('M.Si.', 'MSI', 'belakang', 8),
('Lainnya', 'OTHER', 'depan', 99);

-- Add columns if not exist
SET @dbname = DATABASE();
SET @tablename = 'people';
SET @columnname = 'gelar_id';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
    (table_schema = @dbname)
    AND (table_name = @tablename)
    AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' INT NULL AFTER nama, ADD FOREIGN KEY (', @columnname, ') REFERENCES ref_jenis_gelar(id) ON DELETE SET NULL, ADD INDEX idx_', @columnname, ' (', @columnname, ')')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @columnname = 'nama_depan';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
    (table_schema = @dbname)
    AND (table_name = @tablename)
    AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' VARCHAR(100) NULL AFTER gelar_id, ADD INDEX idx_', @columnname, ' (', @columnname, ')')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @columnname = 'nama_tengah';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
    (table_schema = @dbname)
    AND (table_name = @tablename)
    AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' VARCHAR(100) NULL AFTER nama_depan')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @columnname = 'nama_belakang';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
    (table_schema = @dbname)
    AND (table_name = @tablename)
    AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' VARCHAR(100) NULL AFTER nama_tengah, ADD INDEX idx_', @columnname, ' (', @columnname, ')')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @columnname = 'nama_lengkap';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
    (table_schema = @dbname)
    AND (table_name = @tablename)
    AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' VARCHAR(255) NULL AFTER nama_belakang')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Migrasi data nama ke kolom baru (sederhana split berdasarkan spasi)
UPDATE people
SET nama_depan = SUBSTRING_INDEX(nama, ' ', 1),
    nama_belakang = CASE 
        WHEN LENGTH(nama) - LENGTH(REPLACE(nama, ' ', '')) >= 2 
        THEN SUBSTRING_INDEX(SUBSTRING_INDEX(nama, ' ', -2), ' ', -1)
        ELSE NULL
    END,
    nama_lengkap = nama
WHERE nama IS NOT NULL AND nama_depan IS NULL;

-- Create trigger untuk update nama_lengkap otomatis
DROP TRIGGER IF EXISTS trg_people_nama_update;
DELIMITER //
CREATE TRIGGER trg_people_nama_update
BEFORE INSERT ON people
FOR EACH ROW
BEGIN
    IF NEW.nama_lengkap IS NULL THEN
        SET NEW.nama_lengkap = CONCAT(
            COALESCE((SELECT nama FROM ref_jenis_gelar WHERE id = NEW.gelar_id), ''), 
            ' ', 
            COALESCE(NEW.nama_depan, ''), 
            ' ', 
            COALESCE(NEW.nama_tengah, ''), 
            ' ', 
            COALESCE(NEW.nama_belakang, '')
        );
    END IF;
END//
DELIMITER ;

DROP TRIGGER IF EXISTS trg_people_nama_update_before;
DELIMITER //
CREATE TRIGGER trg_people_nama_update_before
BEFORE UPDATE ON people
FOR EACH ROW
BEGIN
    IF NEW.nama_depan <> OLD.nama_depan OR NEW.nama_tengah <> OLD.nama_tengah OR NEW.nama_belakang <> OLD.nama_belakang OR NEW.gelar_id <> OLD.gelar_id THEN
        SET NEW.nama_lengkap = CONCAT(
            COALESCE((SELECT nama FROM ref_jenis_gelar WHERE id = NEW.gelar_id), ''), 
            ' ', 
            COALESCE(NEW.nama_depan, ''), 
            ' ', 
            COALESCE(NEW.nama_tengah, ''), 
            ' ', 
            COALESCE(NEW.nama_belakang, '')
        );
    END IF;
END//
DELIMITER ;

-- ============================================
-- 4. Document Management
-- ============================================
DROP TABLE IF EXISTS people_documents;

CREATE TABLE people_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    person_id INT NOT NULL,
    jenis_identitas_id INT NOT NULL,
    nomor_dokumen VARCHAR(50) NOT NULL,
    file_path VARCHAR(255) NULL,
    tanggal_ekspedisi DATE NULL,
    tanggal_kadaluarsa DATE NULL,
    is_verified TINYINT(1) NOT NULL DEFAULT 0,
    verified_at TIMESTAMP NULL,
    verified_by INT NULL,
    catatan TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE,
    FOREIGN KEY (jenis_identitas_id) REFERENCES ref_jenis_identitas(id) ON DELETE RESTRICT,
    FOREIGN KEY (verified_by) REFERENCES people(id) ON DELETE SET NULL,
    UNIQUE KEY (person_id, jenis_identitas_id, nomor_dokumen),
    INDEX idx_person_id (person_id),
    INDEX idx_nomor_dokumen (nomor_dokumen)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Dokumen identitas per orang';

-- Migrasi data KTP dari people ke people_documents
INSERT INTO people_documents (person_id, jenis_identitas_id, nomor_dokumen, file_path, is_verified)
SELECT id, jenis_identitas_id, nomor_identitas, foto_ktp, 1
FROM people
WHERE nomor_identitas IS NOT NULL;

-- ============================================
-- 5. Family Relations
-- ============================================
DROP TABLE IF EXISTS family_relations;
DROP TABLE IF EXISTS ref_jenis_relasi;

CREATE TABLE ref_jenis_relasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(50) NOT NULL UNIQUE,
    kode VARCHAR(20) NOT NULL UNIQUE,
    urutan INT NOT NULL DEFAULT 0,
    is_aktif TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Referensi jenis relasi keluarga';

INSERT INTO ref_jenis_relasi (nama, kode, urutan) VALUES
('Ayah', 'FATHER', 1),
('Ibu', 'MOTHER', 2),
('Suami', 'HUSBAND', 3),
('Istri', 'WIFE', 4),
('Anak', 'CHILD', 5),
('Saudara Kandung', 'SIBLING', 6),
('Kakek', 'GRANDFATHER', 7),
('Nenek', 'GRANDMOTHER', 8),
('Paman', 'UNCLE', 9),
('Bibi', 'AUNT', 10),
('Keponakan', 'NEPHEW', 11),
('Cucu', 'GRANDCHILD', 12),
('Lainnya', 'OTHER', 99);

CREATE TABLE family_relations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    person_id INT NOT NULL,
    relative_person_id INT NOT NULL,
    relationship_type_id INT NOT NULL,
    is_primary TINYINT(1) NOT NULL DEFAULT 0,
    catatan TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE,
    FOREIGN KEY (relative_person_id) REFERENCES people(id) ON DELETE CASCADE,
    FOREIGN KEY (relationship_type_id) REFERENCES ref_jenis_relasi(id) ON DELETE RESTRICT,
    INDEX idx_person_id (person_id),
    INDEX idx_relative_person_id (relative_person_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Relasi keluarga per orang';

-- ============================================
-- 6. Address Enhancement
-- ============================================
DROP TABLE IF EXISTS ref_jenis_properti;

CREATE TABLE ref_jenis_properti (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(50) NOT NULL UNIQUE,
    kode VARCHAR(20) NOT NULL UNIQUE,
    urutan INT NOT NULL DEFAULT 0,
    is_aktif TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Referensi jenis properti';

INSERT IGNORE INTO ref_jenis_properti (nama, kode, urutan) VALUES
('Rumah Tinggal', 'RUMAH_TINGGAL', 1),
('Ruko', 'RUKO', 2),
('Apartemen', 'APARTEMEN', 3),
('Tanah', 'TANAH', 4),
('Kos', 'KOS', 5),
('Kontrakan', 'KONTRAKAN', 6),
('Gudang', 'GUDANG', 7),
('Toko', 'TOKO', 8),
('Kantor', 'KANTOR', 9),
('Lainnya', 'OTHER', 99);

-- Add columns to addresses if not exist
SET @tablename = 'addresses';

SET @columnname = 'jenis_properti_id';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
    (table_schema = @dbname)
    AND (table_name = @tablename)
    AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' INT NULL AFTER jenis_alamat_id, ADD FOREIGN KEY (', @columnname, ') REFERENCES ref_jenis_properti(id) ON DELETE SET NULL, ADD INDEX idx_', @columnname, ' (', @columnname, ')')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @columnname = 'nama_gedung';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
    (table_schema = @dbname)
    AND (table_name = @tablename)
    AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' VARCHAR(100) NULL AFTER street_address')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @columnname = 'nomor_unit';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
    (table_schema = @dbname)
    AND (table_name = @tablename)
    AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' VARCHAR(20) NULL AFTER house_number')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- ============================================
-- 7. Audit Trail
-- ============================================
DROP TABLE IF EXISTS people_audit_log;

CREATE TABLE people_audit_log (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    person_id INT NOT NULL,
    action ENUM('INSERT', 'UPDATE', 'DELETE') NOT NULL,
    old_values JSON NULL,
    new_values JSON NULL,
    changed_by INT NULL,
    changed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES people(id) ON DELETE SET NULL,
    INDEX idx_person_id (person_id),
    INDEX idx_action (action),
    INDEX idx_changed_at (changed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Audit trail perubahan data orang';

-- ============================================
-- 8. Soft Delete
-- ============================================
SET @tablename = 'people';
SET @columnname = 'deleted_at';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
    (table_schema = @dbname)
    AND (table_name = @tablename)
    AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' TIMESTAMP NULL AFTER updated_at, ADD INDEX idx_', @columnname, ' (', @columnname, ')')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @tablename = 'addresses';
SET @columnname = 'deleted_at';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
    (table_schema = @dbname)
    AND (table_name = @tablename)
    AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' TIMESTAMP NULL AFTER updated_at, ADD INDEX idx_', @columnname, ' (', @columnname, ')')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- ============================================
-- 9. Data Validation Constraints
-- ============================================
-- Drop constraints if they exist before adding
ALTER TABLE people DROP CONSTRAINT IF EXISTS chk_tanggal_lahir;
ALTER TABLE people DROP CONSTRAINT IF EXISTS chk_nomor_identitas;
ALTER TABLE people_phones DROP CONSTRAINT IF EXISTS chk_phone_number;
ALTER TABLE people_emails DROP CONSTRAINT IF EXISTS chk_email;

ALTER TABLE people
ADD CONSTRAINT chk_tanggal_lahir CHECK (tanggal_lahir IS NULL OR tanggal_lahir BETWEEN '1900-01-01' AND '2100-12-31'),
ADD CONSTRAINT chk_nomor_identitas CHECK (nomor_identitas IS NULL OR nomor_identitas REGEXP '^[0-9]{16}$');

-- Skip phone number constraint for now - will add with proper validation later
-- ALTER TABLE people_phones
-- ADD CONSTRAINT chk_phone_number CHECK (phone_number REGEXP '^\\+?[0-9]{10,15}$');

-- Skip email constraint for now - will add with proper validation later
-- ALTER TABLE people_emails
-- ADD CONSTRAINT chk_email CHECK (email REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\\.[A-Za-z]{2,}$');

-- ============================================
-- 10. Index Optimization
-- ============================================
-- Composite indexes untuk query umum - add if not exist
SET @tablename = 'people';
SET @indexname = 'idx_ktp';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
    (table_schema = @dbname)
    AND (table_name = @tablename)
    AND (index_name = @indexname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD INDEX ', @indexname, ' (ktp)')
));
PREPARE addIndexIfNotExists FROM @preparedStatement;
EXECUTE addIndexIfNotExists;
DEALLOCATE PREPARE addIndexIfNotExists;

SET @indexname = 'idx_nama_tanggal_lahir';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
    (table_schema = @dbname)
    AND (table_name = @tablename)
    AND (index_name = @indexname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD INDEX ', @indexname, ' (nama_depan, tanggal_lahir)')
));
PREPARE addIndexIfNotExists FROM @preparedStatement;
EXECUTE addIndexIfNotExists;
DEALLOCATE PREPARE addIndexIfNotExists;

SET @indexname = 'idx_fulltext_nama';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
    (table_schema = @dbname)
    AND (table_name = @tablename)
    AND (index_name = @indexname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD INDEX ', @indexname, ' (nama, nama_depan, nama_belakang)')
));
PREPARE addIndexIfNotExists FROM @preparedStatement;
EXECUTE addIndexIfNotExists;
DEALLOCATE PREPARE addIndexIfNotExists;

SET @tablename = 'addresses';
SET @indexname = 'idx_person_village';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
    (table_schema = @dbname)
    AND (table_name = @tablename)
    AND (index_name = @indexname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD INDEX ', @indexname, ' (person_id, village_id)')
));
PREPARE addIndexIfNotExists FROM @preparedStatement;
EXECUTE addIndexIfNotExists;
DEALLOCATE PREPARE addIndexIfNotExists;

SET @indexname = 'idx_is_primary';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
    (table_schema = @dbname)
    AND (table_name = @tablename)
    AND (index_name = @indexname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD INDEX ', @indexname, ' (is_primary)')
));
PREPARE addIndexIfNotExists FROM @preparedStatement;
EXECUTE addIndexIfNotExists;
DEALLOCATE PREPARE addIndexIfNotExists;

SET @tablename = 'people_documents';
SET @indexname = 'idx_person_jenis';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
    (table_schema = @dbname)
    AND (table_name = @tablename)
    AND (index_name = @indexname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD INDEX ', @indexname, ' (person_id, jenis_identitas_id)')
));
PREPARE addIndexIfNotExists FROM @preparedStatement;
EXECUTE addIndexIfNotExists;
DEALLOCATE PREPARE addIndexIfNotExists;

SET @tablename = 'family_relations';
SET @indexname = 'idx_person_relasi';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
    (table_schema = @dbname)
    AND (table_name = @tablename)
    AND (index_name = @indexname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD INDEX ', @indexname, ' (person_id, relationship_type_id)')
));
PREPARE addIndexIfNotExists FROM @preparedStatement;
EXECUTE addIndexIfNotExists;
DEALLOCATE PREPARE addIndexIfNotExists;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;
