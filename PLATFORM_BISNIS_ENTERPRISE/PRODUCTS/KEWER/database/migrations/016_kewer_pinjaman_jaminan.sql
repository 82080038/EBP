-- Migration 016: Normalisasi Jaminan Pinjaman
-- Tabel pinjaman_jaminan untuk tracking multiple jaminan per pinjaman
-- Menggantikan field jaminan di tabel pinjaman

USE kewer;

-- Disable foreign key checks
SET FOREIGN_KEY_CHECKS = 0;

-- 1. Buat tabel pinjaman_jaminan
CREATE TABLE IF NOT EXISTS pinjaman_jaminan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pinjaman_id INT NOT NULL,
    jaminan_tipe_id INT NOT NULL,
    deskripsi TEXT,
    nilai_taksiran DECIMAL(12,0) DEFAULT 0,
    nomor_dokumen VARCHAR(50),
    file_dokumen VARCHAR(255),
    status ENUM('aktif','dilepas','terjual','hilang') DEFAULT 'aktif',
    tanggal_dilepas DATE,
    dilepas_oleh INT,
    catatan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pinjaman_id) REFERENCES pinjaman(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (jaminan_tipe_id) REFERENCES ref_jaminan_tipe(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (dilepas_oleh) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_pinjaman_id (pinjaman_id),
    INDEX idx_jaminan_tipe_id (jaminan_tipe_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Migrasi data dari pinjaman ke pinjaman_jaminan
-- Migrasi hanya untuk pinjaman yang memiliki jaminan (bukan 'tanpa')
INSERT INTO pinjaman_jaminan (pinjaman_id, jaminan_tipe_id, deskripsi, nilai_taksiran, nomor_dokumen, file_dokumen, status, catatan)
SELECT 
    p.id AS pinjaman_id,
    CASE p.jaminan_tipe
        WHEN 'bpkb' THEN (SELECT id FROM ref_jaminan_tipe WHERE tipe_kode = 'BPKB' LIMIT 1)
        WHEN 'shm' THEN (SELECT id FROM ref_jaminan_tipe WHERE tipe_kode = 'SHM' LIMIT 1)
        WHEN 'ajb' THEN (SELECT id FROM ref_jaminan_tipe WHERE tipe_kode = 'AJB' LIMIT 1)
        WHEN 'tabungan' THEN (SELECT id FROM ref_jaminan_tipe WHERE tipe_kode = 'TABUNGAN' LIMIT 1)
        ELSE 1
    END AS jaminan_tipe_id,
    p.jaminan AS deskripsi,
    p.jaminan_nilai AS nilai_taksiran,
    NULL AS nomor_dokumen,
    p.jaminan_dokumen AS file_dokumen,
    p.jaminan_status AS status,
    CONCAT('Migrasi dari pinjaman.id=', p.id) AS catatan
FROM pinjaman p
WHERE p.jaminan_tipe IS NOT NULL 
    AND p.jaminan_tipe != 'tanpa'
    AND p.jaminan IS NOT NULL
    AND p.jaminan != '';

-- 3. (Opsional) Set pinjaman.jaminan_tipe dan pinjaman.jaminan_status ke NULL setelah migrasi
-- Uncomment baris berikut jika sudah yakin data sudah migrasi dengan benar
-- UPDATE pinjaman SET jaminan_tipe = NULL, jaminan = NULL, jaminan_nilai = NULL, jaminan_dokumen = NULL, jaminan_status = 'aktif';

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Verification
SELECT 'Migration completed successfully' AS status;
SELECT COUNT(*) AS total_jaminan_migrated FROM pinjaman_jaminan;
SELECT COUNT(*) AS pinjaman_dengan_jaminan FROM pinjaman WHERE jaminan IS NOT NULL AND jaminan != 'tanpa';
