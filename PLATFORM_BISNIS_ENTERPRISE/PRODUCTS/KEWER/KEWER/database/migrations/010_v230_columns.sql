-- Migration 010: Kolom-kolom baru v2.3.0
-- Upgrade dari v2.2.x — jalankan sekali sebelum deploy v2.3.x
-- Aman dijalankan berulang (ALTER IGNORE / IF NOT EXISTS via stored block)

-- ── pembayaran: GPS koordinat ────────────────────────────────────
ALTER TABLE `pembayaran`
    ADD COLUMN IF NOT EXISTS `lat`         DECIMAL(10,7) NULL COMMENT 'GPS latitude'   AFTER `keterangan`,
    ADD COLUMN IF NOT EXISTS `lng`         DECIMAL(10,7) NULL COMMENT 'GPS longitude'  AFTER `lat`,
    ADD COLUMN IF NOT EXISTS `akurasi_gps` SMALLINT      NULL COMMENT 'meters'         AFTER `lng`;

-- ── pinjaman: kolektibilitas OJK ─────────────────────────────────
ALTER TABLE `pinjaman`
    ADD COLUMN IF NOT EXISTS `kolektibilitas`  TINYINT(1) DEFAULT 1 COMMENT '1=Lancar,2=DPK,3=KurangLancar,4=Diragukan,5=Macet' AFTER `status`,
    ADD COLUMN IF NOT EXISTS `hari_tunggakan`  INT        DEFAULT 0 AFTER `kolektibilitas`;

-- ── users: 2FA TOTP ──────────────────────────────────────────────
ALTER TABLE `users`
    ADD COLUMN IF NOT EXISTS `totp_secret`      VARCHAR(64) NULL                       AFTER `password`,
    ADD COLUMN IF NOT EXISTS `totp_enabled`     TINYINT(1)  NOT NULL DEFAULT 0         AFTER `totp_secret`,
    ADD COLUMN IF NOT EXISTS `totp_verified_at` DATETIME    NULL                       AFTER `totp_enabled`,
    ADD COLUMN IF NOT EXISTS `phone_2fa`        VARCHAR(20) NULL COMMENT 'No HP untuk OTP SMS (opsional)' AFTER `totp_verified_at`;

-- ── target_petugas (tabel baru) ───────────────────────────────────
CREATE TABLE IF NOT EXISTS `target_petugas` (
    `id`                     INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `cabang_id`              INT(11)         NOT NULL,
    `petugas_id`             INT(11)         NOT NULL,
    `bulan`                  VARCHAR(7)      NOT NULL COMMENT 'YYYY-MM',
    `target_kutipan`         DECIMAL(15,2)   DEFAULT 0,
    `target_nasabah_baru`    INT(11)         DEFAULT 0,
    `target_pinjaman_baru`   INT(11)         DEFAULT 0,
    `target_collection_rate` DECIMAL(5,2)    DEFAULT 90.00,
    `catatan`                TEXT            NULL,
    `dibuat_oleh`            INT(11)         NULL,
    `created_at`             TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`             TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_target_petugas_bulan` (`petugas_id`, `bulan`),
    KEY `idx_cabang_bulan` (`cabang_id`, `bulan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── wa_log (tabel baru) ───────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `wa_log` (
    `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `cabang_id`   INT(11)       NULL,
    `nasabah_id`  INT(11)       NULL,
    `pinjaman_id` INT(11)       NULL,
    `phone`       VARCHAR(20)   NOT NULL,
    `template`    VARCHAR(64)   NULL,
    `pesan`       TEXT          NOT NULL,
    `status`      ENUM('sent','failed','pending') NOT NULL DEFAULT 'pending',
    `provider`    VARCHAR(32)   NULL DEFAULT 'fonnte',
    `response`    TEXT          NULL,
    `sent_at`     DATETIME      NULL,
    `created_at`  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_nasabah` (`nasabah_id`),
    KEY `idx_pinjaman` (`pinjaman_id`),
    KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── platform_features — delegate ke migration 009 ────────────────
-- Sudah ditangani di migrations/009_platform_features.sql
-- Tidak perlu duplikasi di sini.
