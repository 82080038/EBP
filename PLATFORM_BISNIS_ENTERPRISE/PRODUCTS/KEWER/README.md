# Kewer — Sistem Pinjaman Modal Pedagang

Aplikasi web berbasis PHP untuk mengelola pinjaman modal pedagang koperasi pasar / bank keliling, dengan fitur manajemen nasabah, pinjaman, angsuran harian/mingguan/bulanan, dan pembayaran.

[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net/)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-purple.svg)](https://getbootstrap.com/)
[![MariaDB](https://img.shields.io/badge/MariaDB-10.4-orange.svg)](https://mariadb.org/)

---

## Model Bisnis

Koperasi pasar / bank keliling meminjamkan uang ke pedagang pasar dan UMKM dengan skema:
- **Angsuran harian** — petugas kutip tiap hari kerja (tenor 30–100 hari)
- **Angsuran mingguan** — petugas kutip tiap minggu (tenor 4–52 minggu)
- **Angsuran bulanan** — tenor 1–12 bulan

Petugas keliling mengutip angsuran langsung ke lokasi nasabah (pasar, lapak, rumah).

---

## Arsitektur

- **Backend:** PHP 8.2, MySQL/MariaDB
- **Frontend:** Bootstrap 5.3, DataTable.js, SweetAlert2, Select2, Flatpickr
- **Database:** 3 database terpisah (lihat di bawah)
- **Auth:** Session-based, 9 role levels + appOwner
- **Platform:** Multi-tenant — appOwner mengelola platform, setiap Bos punya koperasi sendiri
- **Server:** XAMPP (Apache 2.4, MariaDB 10.4, PHP 8.2) di Linux

---

## 3-Database Architecture

### 1. `kewer` — Database Utama (64 tabel + 3 view)
- Transaksi koperasi: users, nasabah, pinjaman, angsuran, pembayaran, cabang
- Platform: billing, usage, AI advisor, koperasi_activities
- **Koneksi:** `$conn` / `query()`

### 2. `db_alamat` — Referensi Lokasi Nasional (24 tabel)
- Provinsi (38), Kabupaten (541), Kecamatan (7,938), Desa (80,937) — Nasional
- Data geospasial: GPS, boundaries, POI, infrastructure
- **Koneksi:** `$conn_alamat` / `query_alamat()`

### 3. `db_orang` — Identitas Orang (20 tabel)
- Data orang (people) + alamat (addresses) + master data + relasi + audit
- Master data: agama, jenis_kelamin, golongan_darah, status_perkawinan, suku, pekerjaan, jenis_alamat, jenis_identitas
- Multiple: phone numbers, emails, documents, family relations
- Audit trail untuk tracking perubahan data
- Soft delete dengan kolom deleted_at
- Referensi lokasi → `db_alamat.provinces/regencies/districts/villages`
- **Koneksi:** `$conn_orang` / `query_orang()`
- Cross-DB: `users.db_orang_person_id`, `nasabah.db_orang_user_id`, `cabang.db_orang_person_id`

---

## Role Hierarchy

| Level | Role | Username | Keterangan |
|-------|------|----------|-----------|
| 0 | appOwner | appowner | Platform owner — billing, approvals, AI advisor |
| 1 | bos | patri | Pemilik koperasi — akses penuh operasional |
| 3 | manager_pusat | mgr_pusat | Kontrol operasional, approve pinjaman |
| 4 | manager_cabang | mgr_balige | Operasional cabang, approve pinjaman |
| 5 | admin_pusat | adm_pusat | Input nasabah, pinjaman, angsuran |
| 7 | petugas_pusat | ptr_pngr1 | Koleksi pembayaran lapangan |
| 8 | petugas_cabang | ptr_blg1 | Koleksi pembayaran lapangan cabang |
| 9 | karyawan | krw_pngr | Dukungan administratif, rekonsiliasi kas |

> **Catatan:** Multi-cabang aktif — Cabang Pusat (Pematangsiantar, id=1) + Cabang Balige (id=2).

---

## Default Credentials

| Role | Username | Password |
|------|----------|----------|
| appOwner | appowner | AppOwner2024! |
| bos | patri | Kewer2024! |
| manager_pusat | mgr_pusat | Kewer2024! |
| manager_cabang | mgr_balige | Kewer2024! |
| admin_pusat | adm_pusat | Kewer2024! |
| petugas_pusat | ptr_pngr1 | Kewer2024! |
| petugas_cabang | ptr_blg1 | Kewer2024! |
| karyawan | krw_pngr | Kewer2024! |

> Quick login tersedia di halaman login (development mode `APP_ENV=development`).

---

## Fitur Utama

### Platform (appOwner)
- Approve/suspend koperasi bos
- Usage tracking & billing management
- AI advisor per koperasi
- Platform-wide audit logs

### Operasional Koperasi
- **Nasabah:** CRUD, upload KTP, OCR KTP (Tesseract), blacklist, family risk, credit scoring
- **Pinjaman:** Pengajuan, approval workflow, harian/mingguan/bulanan, auto-confirm, auto-approval
- **Angsuran:** Jadwal otomatis, denda keterlambatan, cetak kartu angsuran
- **Pembayaran:** Input tunai, GPS tracking, kwitansi, rekonsiliasi kas harian
- **Petugas Lapangan:** GPS tracking, kunjungan, foto, aktivitas lapangan, kas petugas
- **Kas & Keuangan:** Pengeluaran, kas bon, rekonsiliasi kas
- **Laporan:** Dashboard analytics, keuangan, kinerja pinjaman, nasabah, audit trail, geographic analysis
- **Users:** RBAC granular, delegated permissions
- **Multi-branch Sync:** Data synchronization antar cabang dengan conflict resolution
- **Webhook System:** Third-party API integration dengan event triggers

### Integrasi
- WhatsApp (Twilio, Wablas, Fonnte)
- PDF export (DomPDF)
- OCR KTP (Tesseract)
- Email notifications (SMTP)
- Chart.js untuk dashboard analytics
- Webhook system untuk external integrations

---

## Instalasi

### Prerequisites
- PHP 8.0+ (`mysqli`, `gd`, `mbstring`, `curl`, `zip`)
- MySQL/MariaDB 5.7+
- XAMPP (Linux/Windows) atau Apache + MySQL

### 1. Clone Repository
```bash
git clone https://github.com/82080038/kewer.git /opt/lampp/htdocs/kewer
```

### 2. Setup Database
```bash
# Buat 3 database
/opt/lampp/bin/mysql -u root -proot --socket=/opt/lampp/var/mysql/mysql.sock -e "
  CREATE DATABASE IF NOT EXISTS kewer;
  CREATE DATABASE IF NOT EXISTS db_alamat;
  CREATE DATABASE IF NOT EXISTS db_orang;
"

# Import SQL
/opt/lampp/bin/mysql -u root -proot kewer           < database/kewer.sql
/opt/lampp/bin/mysql -u root -proot db_alamat      < database/db_alamat.sql
/opt/lampp/bin/mysql -u root -proot db_orang        < database/db_orang.sql

# Atau gunakan export terbaru
/opt/lampp/bin/mysql -u root -proot --socket=/opt/lampp/var/mysql/mysql.sock kewer      < database/kewer_export.sql
/opt/lampp/bin/mysql -u root -proot --socket=/opt/lampp/var/mysql/mysql.sock db_alamat < database/db_alamat_export.sql
/opt/lampp/bin/mysql -u root -proot --socket=/opt/lampp/var/mysql/mysql.sock db_orang   < database/db_orang_export.sql

# Fresh install (reset ke kondisi awal)
/opt/lampp/bin/mysql -u root -proot --socket=/opt/lampp/var/mysql/mysql.sock kewer < scripts/fresh_install.sql
```

### 3. Konfigurasi
Edit `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_NAME', 'kewer');
// Socket path untuk XAMPP Linux
define('DB_SOCKET', '/opt/lampp/var/mysql/mysql.sock');
```

Buat file `.env`:
```env
APP_ENV=development
APP_URL=http://localhost/kewer

# WhatsApp (opsional)
WA_ENABLED=false
WA_PROVIDER=fonnte

# Email (opsional)
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
```

### 4. Install Dependencies
```bash
# PHP (PDF export, OCR)
composer install

# Node.js (simulasi Puppeteer)
npm install
```

### 5. Akses Aplikasi
```
http://localhost/kewer/login.php
```

---

## Migrations

Migration tersedia di `database/migrations/`:
- `015_kewer_ref_frekuensi_angsuran.sql` - Normalisasi frekuensi angsuran
- `017_kewer_foreign_keys.sql` - Foreign key constraints
- `020_kewer_penagihan_system.sql` - Sistem penagihan
- `024_kewer_drop_old_frekuensi_columns.sql` - Hapus enum columns
- `025_kewer_populate_angsuran_frekuensi.sql` - Populate angsuran frekuensi_id
- `026_kewer_populate_settings_frekuensi.sql` - Populate settings frekuensi_id
- `027_kewer_insert_default_settings_frekuensi.sql` - Default settings frekuensi
- `028_add_credit_scoring_columns.sql` - Credit scoring system
- `029_add_gps_tracking_tables.sql` - GPS tracking
- `030_add_sync_tables.sql` - Multi-branch sync
- `031_add_webhook_tables.sql` - Webhook system
- `032_add_new_permissions.sql` - New permissions untuk batch features

Migration sudah dijalankan pada v2.4.0. Untuk fresh install, gunakan:
```bash
/opt/lampp/bin/mysql -u root -proot --socket=/opt/lampp/var/mysql/mysql.sock kewer < database/kewer.sql
```

Untuk menjalankan migration batch features (v2.4.0):
```bash
/opt/lampp/bin/mysql -u root -proot --socket=/opt/lampp/var/mysql/mysql.sock kewer < database/migrations/028_add_credit_scoring_columns.sql
/opt/lampp/bin/mysql -u root -proot --socket=/opt/lampp/var/mysql/mysql.sock kewer < database/migrations/029_add_gps_tracking_tables.sql
/opt/lampp/bin/mysql -u root -proot --socket=/opt/lampp/var/mysql/mysql.sock kewer < database/migrations/030_add_sync_tables.sql
/opt/lampp/bin/mysql -u root -proot --socket=/opt/lampp/var/mysql/mysql.sock kewer < database/migrations/031_add_webhook_tables.sql
/opt/lampp/bin/mysql -u root -proot --socket=/opt/lampp/var/mysql/mysql.sock kewer < database/migrations/032_add_new_permissions.sql
```

---

## Cron Job (Windows Task Scheduler)

File scheduled task: `cron_daily_tasks.php`

### Setup Windows Task Scheduler
1. Buka Task Scheduler (`Win + R` → `taskschd.msc`)
2. Create Basic Task: "Kewer Daily Tasks"
3. Trigger: Daily (00:00)
4. Action: Start a program
   - Program: `C:\xampp\php\php.exe`
   - Arguments: `C:\xampp\htdocs\kewer\cron_daily_tasks.php`
   - Start in: `C:\xampp\htdocs\kewer`

### Fungsi Daily Tasks
- Auto-create penagihan untuk angsuran jatuh tempo
- Hitung denda harian
- Update kolektibilitas nasabah
- Tag pinjaman macet
- Kirim notifikasi (jika dikonfigurasi)

Lihat `docs/cron_job_setup.md` untuk detail lengkap.

---

## Simulasi Multi-Role (Puppeteer)

```bash
# Setup: verifikasi bos login + tambah staff
node simulation/run_simulation.js setup

# Simulasi 14 hari × semua role
node simulation/run_simulation.js sim

# Keduanya sekaligus
node simulation/run_simulation.js all

# Test login saja
node simulation/test_login.js
```

Flow simulasi harian:
```
Admin Pusat → input nasabah & pinjaman
Bos/Manager → approve pinjaman
Petugas     → koleksi angsuran di lapangan
Manager     → review operasional
Bos         → dashboard & laporan akhir hari
Karyawan    → rekonsiliasi kas
AppOwner    → monitoring platform
```

---

## API

### Base URL
```
http://localhost/kewer/api/
```

### Endpoints Utama
| Method | Endpoint | Keterangan |
|--------|----------|-----------|
| GET/POST/PUT/DELETE | `/api/nasabah.php` | Manajemen nasabah |
| GET/POST/PUT | `/api/pinjaman.php` | Manajemen pinjaman |
| GET/POST/PUT | `/api/angsuran.php` | Jadwal angsuran |
| GET/POST | `/api/pembayaran.php` | Input pembayaran |
| GET/POST | `/api/kas_petugas.php` | Kas lapangan |
| GET | `/api/dashboard.php` | Statistik dashboard |
| GET/POST/PUT/DELETE | `/api/users.php` | Manajemen user |
| GET/POST/PUT/DELETE | `/api/roles.php` | Role & permissions |

---

## Troubleshooting

### MySQL tidak konek
```bash
/opt/lampp/bin/mysql -u root -proot --socket=/opt/lampp/var/mysql/mysql.sock
```

### XAMPP start
```bash
sudo /opt/lampp/lampp start
sudo /opt/lampp/lampp status
```

### Log error
```bash
tail -f /opt/lampp/logs/error_log
tail -f /opt/lampp/var/mysql/*.err
```

---

## Changelog

### v2.4.0 (2026-05-08) - Batch Implementation Selesai
- ✅ **Integrasi ref_frekuensi_angsuran** - Normalisasi frekuensi angsuran (harian, mingguan, bulanan)
  - Tabel `ref_frekuensi_angsuran` dengan ID: 1 (Harian, max 100 hari), 2 (Mingguan, max 52 minggu), 3 (Bulanan, max 36 bulan)
  - 20 file diperbarui untuk support frekuensi_id
  - Setting bunga & denda per frekuensi
  - Migration 015, 025, 026, 027, 024 (drop enum columns)
  - Helper functions: getFrequencyLabel(), getFrequencyId(), getFrequencyCode(), getMaxTenor()
- ✅ **Sistem Penagihan** - Penagihan system integration
  - Tabel penagihan, penagihan_log, ref_jenis_penagihan
  - API penagihan dan UI penagihan
  - autoCreatePenagihanOverdue() function
  - cron_daily_tasks.php untuk scheduled tasks
- ✅ **Advanced Dashboard Analytics** - Dashboard analytics API dengan Chart.js
  - API endpoint: api/dashboard_analytics.php
  - Service: src/Cache/CacheManager.php
  - Helper: includes/chart_helper.php
  - Real-time metrics, per-cabang comparison, trend analysis
  - File-based caching untuk performance
- ✅ **Credit Scoring System** - Rule-based scoring engine dengan auto-approval
  - API endpoint: api/credit_scoring.php
  - Service: src/CreditScoring/ScoringEngine.php
  - Database: credit_scoring_logs, nasabah (credit_score, risk_level), pinjaman (auto_approved)
  - Auto-approval untuk low-risk nasabah
- ✅ **GPS Tracking** - Geolocation tracking untuk petugas lapangan
  - API endpoint: api/visits.php
  - Service: src/Geo/GPSTracker.php
  - Pages: pages/petugas/kunjungan.php
  - Database: visits, mobile_devices, pembayaran (GPS columns), cabang (GPS columns)
  - Geofencing, distance calculation, visit logging
- ✅ **Audit Trail UI** - Audit log viewer dengan filtering dan export
  - Pages: pages/audit/index.php
  - API endpoint: api/audit_log.php
  - Filterable by user, action, table, date range
  - Export to CSV
- ✅ **Geographic Analysis** - Radius search, demographic analysis, heatmap
  - API endpoint: api/geographic_analysis.php
  - Service: src/Geo/GPSTracker.php
  - Area classification (urban vs rural)
- ✅ **Multi-branch Sync** - Data synchronization service
  - Service: src/Sync/DataSyncService.php
  - Database: sync_logs, sync_conflicts
  - Conflict detection dan resolution
- ✅ **Webhook System** - Third-party API integration
  - Pages: pages/settings/webhooks.php
  - API endpoint: api/webhooks.php
  - Service: src/Webhook/WebhookService.php
  - Helper: includes/webhook_trigger.php
  - Database: webhooks, webhook_logs, webhook_deliveries, external_api_logs, api_keys
  - Event triggers: pinjaman.approved, pinjaman.rejected, pembayaran.received, dll
- ✅ **Cleanup** - Hapus backward compatibility code & test files
  - Hapus fallback ke frekuensi enum (kolom sudah di-drop)
  - Hapus file test sementara
  - Hapus file migration runner (sudah dijalankan)
  - Update README.md & dokumentasi

### v2.3.1 (2026-05-06)
- ✅ Feature Flags System — dynamic toggle semua fitur baru
- ✅ Layout consistency fix — semua halaman pakai shared `sidebar.php`
- ✅ SQL column bugfixes (cetak_kwitansi, slip_harian, dashboard)
- ✅ 8 appOwner pages (+ features.php)
- ✅ Cleanup: hapus file temp/debug, consolidasi SQL ke `database/`
- ✅ E2E test: 189 tests × 9 roles — 0 failures

### v2.3.0 (2026-05-05)
- ✅ Kolektibilitas OJK, Cron harian, WA Pengingat
- ✅ Target Petugas, Slip Harian, Export Laporan
- ✅ GPS Pembayaran, PWA, 2FA TOTP
- ✅ Simulasi pinjaman real-time

### v2.1.0 (2026-05-03)
- ✅ Single office structure refactor (hapus multi-branch, `kantor_id = 1`)
- ✅ Role JSON files sync dengan database (hapus superadmin, manager, tambah appOwner)
- ✅ Simulasi Puppeteer diperbarui sesuai role & flow aplikasi
- ✅ Credentials semua user koperasi: `Kewer2024!`
- ✅ `loginUser()` default password `Kewer2024!`

### v2.0.0 (2026-05-02)
- ✅ 3-Database architecture (kewer, db_alamat_simple, db_orang)
- ✅ appOwner platform layer (billing, usage, AI advisor)
- ✅ 7 appOwner pages
- ✅ Cross-DB links: users/nasabah/cabang → db_orang.people

### v1.2.0 (2026-04-30)
- ✅ Simulasi multi-role Puppeteer (13 role × 14 hari)
- ✅ WhatsApp integration (Twilio, Wablas, Fonnte)
- ✅ PDF export (DomPDF), OCR KTP (Tesseract)

### v1.0.0 (2026-04-14)
- Initial release

---

**Repository:** https://github.com/82080038/kewer  
**Developed by:** 82080038
