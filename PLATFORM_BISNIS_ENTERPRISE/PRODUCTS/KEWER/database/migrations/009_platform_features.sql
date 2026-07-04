-- Migration 009: Platform Feature Flags
-- Dikelola hanya oleh appOwner via /pages/app_owner/features.php
-- Semua fitur DEFAULT OFF (kecuali yg sudah ada sebelum v2.3.0)

CREATE TABLE IF NOT EXISTS platform_features (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    feature_key VARCHAR(64)  NOT NULL UNIQUE COMMENT 'identifier unik fitur',
    label       VARCHAR(128) NOT NULL COMMENT 'nama tampilan',
    description TEXT         NULL,
    category    VARCHAR(32)  NOT NULL DEFAULT 'general' COMMENT 'wa|auth|pwa|laporan|lapangan|system',
    is_enabled  TINYINT(1)   NOT NULL DEFAULT 0,
    changed_by  INT UNSIGNED NULL,
    changed_at  DATETIME     NULL,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed: semua fitur v2.3.0 — default OFF
INSERT INTO platform_features (feature_key, label, description, category, is_enabled) VALUES
('wa_pengingat_auto','Pengingat Otomatis',     'Cron harian kirim notifikasi pengingat H-3, H-1 dan H-0 jatuh tempo ke nasabah.',            'wa',      0),
('two_factor_auth',  '2FA Login (TOTP)',           'Autentikasi dua faktor untuk role bos/manager menggunakan Google Authenticator.', 'auth',    0),
('pwa',              'PWA (Progressive Web App)', 'Service worker, manifest, install-to-homescreen, offline fallback.',              'pwa',     0),
('gps_pembayaran',   'GPS pada Pembayaran',        'Rekam koordinat GPS saat petugas mencatat pembayaran di lapangan.',              'lapangan', 0),
('export_laporan',   'Export Laporan (CSV/PDF)',   'Tombol export di halaman laporan. PDF butuh library dompdf.',                    'laporan', 0),
('target_petugas',   'Target Kinerja Petugas',     'Set target bulanan kutipan/nasabah per petugas, tampil progress bar di kinerja.','lapangan', 0),
('slip_harian',      'Slip Harian Petugas',        'Petugas bisa cetak rekap kutipan harian mereka sendiri.',                       'lapangan', 0),
('kolektibilitas',   'Kolektibilitas OJK (1-5)',   'Badge dan update otomatis level kolektibilitas pinjaman per standar OJK.',       'lapangan', 0),
('cron_harian',      'Cron Job Harian',            'Jalankan autoTandaiMacet, hitungDenda, dan notifikasi jatuh tempo tiap pagi.',  'system',  0),
('simulasi_pinjaman','Simulasi Pinjaman Real-time','Preview angsuran dan jadwal amortisasi saat input pinjaman baru.',               'lapangan', 1)
ON DUPLICATE KEY UPDATE label = VALUES(label), description = VALUES(description);
