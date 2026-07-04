# Kewer Application Analysis

> **Terakhir diperbarui**: 10 Mei 2026 (Client-Side Rendering Conversion Selesai)
> **Versi Aplikasi**: v2.5.0 (Client-Side JSON API Rendering + Interoperable API)

## Overview
**Project Name**: Kewer - Koperasi Warga Ekonomi Rakyat
**Type**: Sistem Manajemen Pinjaman untuk Koperasi Pasar / Bank Keliling
**Business Model**: Usaha pinjaman pribadi — meminjamkan uang ke pedagang pasar/UMKM dengan angsuran harian, mingguan, atau bulanan. Petugas keliling mengutip angsuran langsung ke lokasi nasabah.
**Target Users**: Pedagang pasar, warung, pelaku UMKM informal
**Repository**: https://github.com/82080038/kewer.git

> **PENTING**: Ini BUKAN koperasi simpan pinjam formal. Tidak ada modul simpanan, SHU, atau laporan SAK EP. Fokus utama adalah pencairan pinjaman dan kutipan angsuran lapangan.
> **ARsitektur Baru**: Aplikasi sekarang menggunakan client-side rendering dengan jQuery dan JSON API untuk interoperabilitas dengan aplikasi lain.

## Business Model
- **Target**: Market vendors (pedagang pasar) needing micro-loans
- **Loan Types**: Daily (harian), Weekly (mingguan), Monthly (bulanan) repayment frequencies
- **Interest**: Fixed percentage per month (typically 2-5%)
- **Collection**: Field officers (petugas) collect payments from customers in the field
- **Platform**: Multi-tenant — setiap Bos punya koperasi sendiri, appOwner mengelola platform
- **Nasabah Portal**: Self-service portal untuk nasabah (dashboard, profil, pengajuan pinjaman/simpanan)
- **Penagihan System**: Manajemen penagihan dengan pengingat otomatis
- **API Interoperability**: RESTful JSON API untuk integrasi dengan aplikasi lain (v2.5.0)

## Architecture
- **Backend**: PHP 8.2 with MySQL/MariaDB
- **Frontend**: Bootstrap 5.3 + jQuery + Vanilla JavaScript + Chart.js (Client-Side Rendering)
- **API Layer**: RESTful JSON API dengan standardized response format (v2.5.0)
- **Database**: 3 database terpisah (kewer, db_alamat, db_orang)
- **Authentication**: Session-based with role-based permissions + API token authentication
- **Platform**: appOwner layer (billing, usage tracking, AI advisor, province activation)
- **Audit Trail**: CRUD operations logged to audit_log table
- **Feature Flags**: Dynamic feature toggle system (v2.3.1)
- **Cron Jobs**: Automated daily tasks (v2.4.0)
- **Koperasi Isolation**: Multi-tenant support dengan data isolation per koperasi (v2.4.0)
- **Advanced Analytics**: Dashboard analytics API dengan Chart.js integration (v2.4.0)
- **Credit Scoring**: Rule-based scoring engine dengan auto-approval (v2.4.0)
- **GPS Tracking**: Geolocation tracking untuk petugas lapangan (v2.4.0)
- **Geographic Analysis**: Radius search, demographic analysis, heatmap (v2.4.0)
- **Multi-branch Sync**: Data synchronization service untuk cabang (v2.4.0)
- **Webhook System**: Third-party API integration dengan webhook triggers (v2.4.0)
- **Global API Helper**: Standardized AJAX calls via KewerAPI object (v2.5.0)
- **Client-Side Rendering**: Dynamic HTML rendering using JavaScript template literals (v2.5.0)

## Client-Side Rendering Architecture (v2.5.0)

### Frontend Stack
- **jQuery**: DOM manipulation and AJAX calls
- **Bootstrap 5.3**: UI framework
- **Bootstrap Icons**: Icon library
- **DataTables.js**: Advanced table functionality
- **SweetAlert2**: Beautiful alert dialogs
- **Select2**: Enhanced dropdowns
- **Flatpickr**: Modern date picker
- **Chart.js**: Data visualization

### Global JavaScript Files
- **includes/js/api.js**: Global API helper (KewerAPI object) with standardized AJAX calls
- **includes/js/app.js**: Master app JavaScript for page initialization
- **includes/js/page-converter.js**: Guide for converting pages to client-side rendering

### API Response Format
All API endpoints return standardized JSON response:
```json
{
  "success": true|false,
  "data": { ... },
  "error": "Error message if failed",
  "stats": { ... }  // Optional statistics
}
```

### Page Conversion Pattern
**IMPORTANT**: When modifying or creating pages, ALWAYS use client-side rendering pattern. Do NOT use PHP server-side rendering.

**Conversion Steps:**
1. Remove all PHP queries and server-side data fetching from the page
2. Replace PHP-rendered HTML with loading spinners (`<div class="spinner-border spinner-border-sm" role="status"></div>`)
3. Add JavaScript functions to fetch data via KewerAPI or $.ajax()
4. Implement dynamic HTML rendering using JavaScript template literals (backticks)
5. Maintain role-based access control via API/backend (not in PHP)
6. Use standardized AJAX error handling with showAlert()

**JavaScript Structure Pattern:**
```javascript
$(document).ready(function() {
    loadData();
    setupForms();
});

function loadData() {
    window.KewerAPI.getData().done(response => {
        if (response.success) {
            renderData(response.data);
        }
    });
}

function renderData(data) {
    const html = `<!-- template literal HTML -->`;
    $('#container').html(html);
}

function setupForms() {
    $('#formId').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize();
        $.ajax({
            url: 'api/endpoint.php',
            method: 'POST',
            data: formData,
            success: function() {
                showAlert('Success message');
                loadData();
            },
            error: function(xhr) {
                const error = xhr.responseJSON?.error || 'Error message';
                showAlert(error, 'danger');
            }
        });
    });
}

function showAlert(message, type = 'success') {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    $('#alert-container').html(alertHtml);
}
```

**API Endpoint Pattern:**
All API endpoints must return standardized JSON:
```json
{
  "success": true|false,
  "data": { ... },
  "error": "Error message if failed"
}
```

**KewerAPI Usage:**
- `window.KewerAPI.getCurrentUser()` - Get current user data
- `window.KewerAPI.getData(params)` - Generic GET request
- `window.KewerAPI.postData(data)` - Generic POST request
- `window.KewerAPI.putData(id, data)` - Generic PUT request
- `window.KewerAPI.deleteData(params)` - Generic DELETE request

**Direct AJAX Usage:**
For custom endpoints not in KewerAPI:
```javascript
$.ajax({
    url: 'api/endpoint.php',
    method: 'GET',
    data: { action: 'specific_action', param: value },
    success: function(response) {
        if (response.success) {
            // handle data
        }
    },
    error: function() {
        showAlert('Error message', 'danger');
    }
});
```

### Converted Pages (v2.5.0)
- dashboard.php
- nasabah/index.php
- pinjaman/index.php
- angsuran/index.php
- pembayaran/index.php
- cabang/index.php
- petugas/index.php
- laporan/index.php
- audit/index.php
- pengeluaran/index.php
- kas_bon/index.php
- kas_petugas/index.php
- setting_bunga/index.php
- permissions/index.php
- users/index.php
- app_owner/settings.php (accordion dropdown + client-side rendering)

---

## 3-Database Architecture

```
┌─────────────────────────────────┐
│  kewer (72+ tabel + 1 view)     │  Transaksi koperasi, users, billing, usage
│  $conn → query()                │
│  ├── users, permissions, roles  │  Auth & RBAC
│  ├── nasabah, pinjaman          │  Bisnis inti
│  ├── angsuran, pembayaran       │  Cicilan & pembayaran
│  ├── jurnal, akun               │  Akuntansi
│  ├── billing_plans, invoices    │  Platform (appOwner)
│  ├── usage_log, ai_advice       │  Usage tracking & AI
│  ├── koperasi_activities        │  Log aktivitas koperasi
│  ├── petugas_daerah_tugas       │  Daerah tugas petugas
│  ├── penagihan, penagihan_log   │  Penagihan system (v2.4.0)
│  ├── ref_frekuensi_angsuran    │  Frekuensi angsuran reference (v2.4.0)
│  ├── ref_produk_pinjaman       │  Produk pinjaman catalog (v2.4.0)
│  ├── pinjaman_jaminan          │  Jaminan pinjaman (v2.4.0)
│  ├── credit_scoring_logs        │  Credit scoring audit (v2.4.0)
│  ├── visits, mobile_devices     │  GPS tracking (v2.4.0)
│  ├── sync_logs, sync_conflicts  │  Multi-branch sync (v2.4.0)
│  ├── webhooks, webhook_logs    │  Webhook system (v2.4.0)
│  ├── webhook_deliveries        │  Webhook deliveries (v2.4.0)
│  ├── external_api_logs, api_keys│  External API (v2.4.0)
│  └── dashboard_cache            │  Analytics cache (v2.4.0)
└────────┬────────────────────────┘
         │ province_id, regency_id, district_id, village_id
         ▼
┌─────────────────────────────────┐
│  db_alamat (24 tabel)           │  Referensi lokasi nasional + geospasial
│  $conn_alamat → query_alamat()  │
│  ├── provinces → regencies      │  38 prov, 541 kab, 7,938 kec, 80,937 desa
│  │   → districts → villages     │
│  ├── GPS, boundaries, metadata  │  Geospasial
│  └── POI, infrastructure       │  Data tambahan
└─────────────────────────────────┘
         ▲ cross-DB JOIN
┌─────────────────────────────────┐
│  db_orang (19 tabel)            │  Identitas orang + alamat + master data + relasi + audit
│  $conn_orang → query_orang()    │
│  ├── people                     │  Data orang (link ke kewer.users/nasabah)
│  ├── addresses                  │  Alamat per orang (ref lokasi → db_alamat)
│  ├── people_phones              │  Multiple phone numbers per orang
│  ├── people_emails              │  Multiple emails per orang
│  ├── people_documents           │  Dokumen identitas per orang
│  ├── family_relations           │  Relasi keluarga per orang
│  ├── people_audit_log           │  Audit trail perubahan data orang
│  ├── ref_agama                  │  Master agama (6 agama resmi Indonesia)
│  ├── ref_jenis_kelamin          │  Master jenis kelamin
│  ├── ref_golongan_darah         │  Master golongan darah
│  ├── ref_status_perkawinan      │  Master status perkawinan
│  ├── ref_suku                   │  Master suku bangsa (20 suku utama)
│  ├── ref_pekerjaan              │  Master pekerjaan
│  ├── ref_jenis_alamat           │  Master jenis alamat (rumah, kantor, kos, dll)
│  ├── ref_jenis_identitas        │  Master jenis identitas (KTP, SIM, Paspor, dll)
│  ├── ref_jenis_telepon          │  Master jenis telepon
│  ├── ref_jenis_email            │  Master jenis email
│  ├── ref_jenis_gelar            │  Master gelar
│  ├── ref_jenis_relasi           │  Master jenis relasi keluarga
│  └── ref_jenis_properti         │  Master jenis properti
└─────────────────────────────────┘
```

### Cross-DB Links
- `kewer.users.db_orang_person_id` → `db_orang.people.id`
- `kewer.nasabah.db_orang_user_id` → `db_orang.people.id`
- `kewer.cabang.db_orang_person_id` → `db_orang.people.id`
- `db_orang.addresses` → `db_alamat.provinces/regencies/districts/villages`
- Models (Nasabah.php, Cabang.php) use `LEFT JOIN db_alamat.provinces` etc.

### v2.5.0 Database Additions
- `penagihan` - Penagihan management system (v2.4.0)
- `penagihan_log` - Log aktivitas penagihan (v2.4.0)
- `ref_frekuensi_angsuran` - Reference table untuk frekuensi angsuran (menggantikan enum) (v2.4.0)
- `ref_produk_pinjaman` - Catalog produk pinjaman (v2.4.0)
- `ref_jenis_penagihan` - Jenis penagihan reference (v2.4.0)
- `pinjaman_jaminan` - Jaminan pinjaman (v2.4.0)
- `v_penagihan_hari_ini` - View untuk penagihan hari ini (v2.4.0)

## API Interoperability (v2.5.0)

### RESTful API Design
All endpoints follow RESTful conventions:
- **GET**: Retrieve data
- **POST**: Create new resource
- **PUT**: Update existing resource
- **DELETE**: Remove resource

### Authentication
- **Session-based**: For web application
- **API Token**: For external integrations (Bearer token: kewer-api-token-2024)

### Standard Response Format
```json
{
  "success": true|false,
  "data": { ... },
  "error": "Error message if failed",
  "stats": { ... },
  "pagination": {
    "page": 1,
    "limit": 25,
    "total": 100
  }
}
```

### Available API Endpoints
- `/api/dashboard` - Dashboard statistics
- `/api/nasabah` - CRUD nasabah
- `/api/pinjaman` - CRUD pinjaman
- `/api/angsuran` - CRUD angsuran
- `/api/pembayaran` - CRUD pembayaran
- `/api/cabang` - CRUD cabang
- `/api/users` - CRUD users
- `/api/petugas` - CRUD petugas
- `/api/laporan` - Generate reports
- `/api/audit` - Audit logs
- `/api/pengeluaran` - CRUD pengeluaran
- `/api/kas_bon` - CRUD kas bon
- `/api/kas_petugas` - CRUD kas petugas
- `/api/setting_bunga` - CRUD setting bunga
- `/api/permissions` - Manage permissions

### Integration with External Applications
The API is designed to be interoperable with:
- Mobile applications (React Native, Flutter)
- Third-party systems
- Webhooks for event notifications
- Export/Import data endpoints

---

## Role Hierarchy (9 Levels + appOwner + nasabah)

```
appOwner          — Platform owner (billing, usage, AI advisor, approvals, province activation)
  └── Credentials: appowner / AppOwner2024!

Level 1: Bos (Pemilik Usaha)
  └── Wajib punya kantor pusat, delegasi permission, kelola cabang
Level 2: Manager Pusat — koordinasi lintas cabang
Level 3: Manager Cabang — operasi cabang tunggal
Level 4: Admin Pusat — administrasi lintas cabang
Level 5: Admin Cabang — administrasi cabang tunggal
Level 6: Petugas Pusat — kutipan lapangan lintas cabang
Level 7: Petugas Cabang — kutipan lapangan cabang tunggal
Level 8: Karyawan — berdasarkan delegated permissions dari bos
Level 9: Nasabah — Self-service portal (v2.4.0)
  └── Dashboard, profil, pengajuan pinjaman/simpanan, pembayaran, angsuran
```

### Test Users (password: Kewer2024!)
- **patri** (bos, cabang 1), mgr_pusat, mgr_pangururan, mgr_balige
- **adm_pusat**, adm_pangururan, adm_balige
- **ptr_pngr1** (petugas_pusat), ptr_pngr2 (petugas_cabang), ptr_blg1
- **krw_pngr** (karyawan), krw_blg

### 2 Branches
- Cabang 1: Kantor Pusat Pangururan (HQ)
- Cabang 2: Cabang Balige

---

## Critical Business Requirements (Koperasi Pasar Model)

### 🔴 KRITIS - Inti Bisnis (COMPLETED)

1. **Frekuensi Angsuran** ✅ — Harian, mingguan, bulanan (migrasi ke frekuensi_id di v2.4.0)
2. **Denda Otomatis** ✅ — Grace period, auto-calc, waive by manager
3. **Blacklist UI** ✅ — Kelola blacklist, validasi pinjaman aktif
4. **Penagihan System** ✅ — Manajemen penagihan dengan pengingat otomatis (v2.4.0)
5. **Nasabah Portal** ✅ — Self-service portal untuk nasabah (v2.4.0)

### 🟡 SEDANG - Operasional Lapangan (PARTIAL)

6. Cetak Kwitansi & Kartu Angsuran — PARTIAL (cetak_kwitansi.php)
7. Notifikasi WhatsApp — PARTIAL (api/wa_notifikasi.php exists, feature flag ready)
8. Rute Harian Petugas — PARTIAL (pages/rute_harian exists)
9. Dashboard Kinerja Petugas — PARTIAL (pages/kinerja exists)
10. Pengelolaan Jaminan — PARTIAL (pages/jaminan exists, pinjaman_jaminan table added)

### 🟢 RENDAH - Tata Kelola (PENDING)

11. Audit Trail UI
12. Pinjaman Top-Up
13. Laporan Laba Rugi Sederhana
14. Credit Scoring Sederhana
15. Integrasi Pembayaran Digital (pembayaran_elektronik.php exists but basic)
16. Export/Import Data (api/export.php exists, src/Export/Exporter.php exists)
17. Advanced Dashboard Analytics

## Database Schema — kewer (67 tables + 1 view)

### Operational Tables
| # | Tabel | Fungsi |
|---|-------|--------|
| 1 | users | Auth, role, cabang_id, db_orang_person_id |
| 2 | cabang | Kantor pusat & cabang, db_orang_person_id |
| 3 | nasabah | Data nasabah, db_orang_user_id/address_id |
| 4 | pinjaman | Pengajuan & tracking pinjaman |
| 5 | pinjaman_jaminan | Jaminan pinjaman (v2.4.0) |
| 6 | angsuran | Jadwal cicilan |
| 7 | pembayaran | Riwayat pembayaran |
| 8 | settings | Konfigurasi sistem |
| 9 | audit_log | Activity logging |
| 10 | family_risk | Penilaian risiko keluarga |
| 11 | nasabah_family_link | Relasi keluarga nasabah |
| 12 | kas_bon | Kasbon karyawan |
| 13 | kas_bon_potongan | Potongan kasbon |
| 14 | kas_petugas | Kas petugas lapangan |
| 15 | kas_petugas_setoran | Setoran kas petugas |
| 16 | pengeluaran | Pengeluaran operasional |
| 17 | loan_risk_log | Tracking risiko pinjaman |
| 18 | field_officer_activities | Aktivitas petugas + daerah_province/regency/district/village_id |
| 19 | daily_cash_reconciliation | Rekonsiliasi kas harian |
| 20 | consolidated_reports | Laporan konsolidasi |
| 21 | transaksi_log | Log transaksi |
| 22 | blacklist_log | Log blacklist nasabah |
| 23 | nasabah_orang_mapping | Mapping nasabah ↔ db_orang |
| 24 | ahli_waris | Data ahli waris nasabah |
| 25 | jurnal_kas | Jurnal kas |
| 26 | kelebihan_bayar | Kelebihan pembayaran |
| 27 | pembayaran_offline_queue | Antrian pembayaran offline |
| 28 | pengganti_petugas | Pengganti petugas |
| 29 | restrukturisasi | Restrukturisasi pinjaman |
| 30 | riwayat_skor_kredit | Riwayat skor kredit nasabah |
| 31 | write_off | Write-off pinjaman bermasalah |
| 32 | notifikasi | Notifikasi sistem |
| 33 | penagihan | Manajemen penagihan (v2.4.0) |
| 34 | penagihan_log | Log aktivitas penagihan (v2.4.0) |

### Organizational & Auth Tables
| # | Tabel | Fungsi |
|---|-------|--------|
| 23 | permissions | Semua permission tersedia |
| 24 | role_permissions | Permission per role |
| 25 | user_permissions | Override permission per user |
| 26 | permission_audit_log | Audit perubahan permission |
| 27 | bos_registrations | Registrasi bos + province/regency/district/village_id |
| 28 | delegated_permissions | Delegasi permission dari bos |
| 29 | koperasi_activities | Log aktivitas koperasi (16 kategori + JSON) |
| 30 | petugas_daerah_tugas | Daerah tugas petugas (district/village level) |

### Platform Tables (appOwner)
| # | Tabel | Fungsi |
|---|-------|--------|
| 31 | billing_plans | 5 paket billing |
| 32 | koperasi_billing | Assign paket ke bos |
| 33 | koperasi_invoices | Invoice bulanan |
| 34 | usage_log | Log per-request |
| 35 | usage_daily_summary | Aggregate harian |
| 36 | ai_advice | AI advisor per koperasi |

### Accounting Tables
| # | Tabel | Fungsi |
|---|-------|--------|
| 37 | akun | Chart of accounts |
| 38 | jurnal | Jurnal entries |
| 39 | jurnal_detail | Detail jurnal |
| 40 | labarugi | Laba rugi |
| 41 | neraca | Neraca |
| 42 | neraca_saldo | Neraca saldo |

### Reference Tables
| # | Tabel | Fungsi |
|---|-------|--------|
| 43 | ref_jaminan_tipe | Tipe jaminan |
| 44 | ref_jenis_usaha | Jenis usaha |
| 45 | ref_kategori_pengeluaran | Kategori pengeluaran |
| 46 | ref_metode_pembayaran | Metode pembayaran |
| 47 | ref_roles | Daftar role |
| 48 | ref_status_pinjaman | Status pinjaman |
| 49 | ref_frekuensi_angsuran | Frekuensi angsuran reference (v2.4.0) |
| 50 | ref_produk_pinjaman | Produk pinjaman catalog (v2.4.0) |
| 51 | ref_jenis_penagihan | Jenis penagihan reference (v2.4.0) |
| 52 | setting_bunga | Setting bunga |
| — | auto_confirm_settings | Auto-confirm pinjaman |
| — | denda_settings | Setting denda |
| — | setting_denda | Setting denda per cabang |
| 53 | platform_features | Feature flags system (v2.3.1) |
| 54 | target_petugas | Target petugas lapangan (v2.3.1) |
| 55 | wa_log | Log WA notifikasi (v2.3.1) |

### New Tables (v2.4.0 Batch Implementation)
| # | Tabel | Fungsi |
|---|-------|--------|
| 56 | credit_scoring_logs | Credit scoring audit trail |
| 57 | visits | Petugas field visit log dengan GPS |
| 58 | mobile_devices | Mobile device registration |
| 59 | sync_logs | Multi-branch sync operation logs |
| 60 | sync_conflicts | Sync conflict resolution |
| 61 | webhooks | Webhook configuration |
| 62 | webhook_logs | Webhook trigger logs |
| 63 | webhook_deliveries | Webhook delivery queue |
| 64 | external_api_logs | External API call logs |
| 65 | api_keys | API key management |
| 66 | dashboard_cache | Analytics data cache |

### Views (1)
- v_penagihan_hari_ini - View untuk penagihan hari ini (v2.4.0)

---

## v2.4.0 New Features

### Nasabah Portal (Self-Service)
- **Pages**: dashboard, profil, pengajuan_pinjaman, pengajuan_simpanan, pinjaman, angsuran, pembayaran, data_keluarga
- **API**: api/nasabah_portal.php
- **Features**:
  - Dashboard dengan ringkasan pinjaman & angsuran
  - Profil management dengan data keluarga
  - Pengajuan pinjaman online
  - Pengajuan simpanan
  - Riwayat angsuran & pembayaran
  - Integration dengan db_orang untuk data keluarga

### Penagihan System
- **Pages**: penagihan/index.php
- **API**: api/penagihan.php, api/pengingat_penagihan.php
- **Database**: penagihan, penagihan_log, ref_jenis_penagihan, v_penagihan_hari_ini
- **Features**:
  - Manajemen penagihan nasabah
  - Pengingat otomatis (WA/SMS)
  - Jenis penagihan (JATUH_TEMPO, OVERDUE, BLACKLIST, FOLLOW_UP)
  - Log aktivitas penagihan

### Advanced Dashboard Analytics (Batch Implementation)
- **API**: api/dashboard_analytics.php
- **Service**: src/Cache/CacheManager.php
- **Helper**: includes/chart_helper.php
- **Features**:
  - Real-time metrics (total pinjaman aktif, collection rate, NPL ratio)
  - Per-cabang performance comparison
  - Trend analysis (mingguan/bulanan/tahunan)
  - Top 10 nasabah analysis
  - File-based caching untuk performance
  - Chart.js integration untuk visualisasi

### Credit Scoring System (Batch Implementation)
- **API**: api/credit_scoring.php
- **Service**: src/CreditScoring/ScoringEngine.php
- **Database**: credit_scoring_logs, nasabah (credit_score, risk_level), pinjaman (auto_approved)
- **Features**:
  - Rule-based scoring engine (payment history, demographics, location, family risk, duration)
  - Auto-approval untuk low-risk nasabah
  - Risk score (0-100) untuk setiap nasabah
  - Audit trail untuk scoring history
  - Batch score calculation untuk semua nasabah

### GPS Tracking (Batch Implementation)
- **API**: api/visits.php
- **Service**: src/Geo/GPSTracker.php
- **Pages**: pages/petugas/kunjungan.php
- **Database**: visits, mobile_devices, pembayaran (GPS columns), cabang (GPS columns)
- **Features**:
  - GPS capture saat pembayaran
  - Geofencing untuk validasi lokasi
  - Visit logging dengan GPS data
  - Haversine formula untuk distance calculation
  - GPS accuracy level determination

### Audit Trail UI (Batch Implementation)
- **Pages**: pages/audit/index.php
- **API**: api/audit_log.php
- **Database**: audit_log (already exists)
- **Features**:
  - Filterable log viewer (by user, action, table, date range)
  - Export audit log to CSV
  - Search functionality
  - Statistics on audit activity

### Geographic Analysis (Batch Implementation)
- **API**: api/geographic_analysis.php
- **Service**: src/Geo/GPSTracker.php
- **Features**:
  - Radius-based nasabah search
  - Demographic analysis per area
  - Risk scoring by location
  - Heatmap data untuk nasabah distribution
  - Area classification (urban vs rural)

### Multi-branch Sync (Batch Implementation)
- **Service**: src/Sync/DataSyncService.php
- **Database**: sync_logs, sync_conflicts
- **Features**:
  - Data synchronization antar cabang
  - Conflict detection dan resolution
  - Sync operation logging
  - Full dan incremental sync support

### Webhook System (Batch Implementation)
- **Pages**: pages/settings/webhooks.php
- **API**: api/webhooks.php
- **Service**: src/Webhook/WebhookService.php
- **Helper**: includes/webhook_trigger.php
- **Database**: webhooks, webhook_logs, webhook_deliveries, external_api_logs, api_keys
- **Features**:
  - Webhook configuration management
  - Event triggers (pinjaman.approved, pinjaman.rejected, pembayaran.received, dll)
  - HMAC signature untuk security
  - Retry logic dengan exponential backoff
  - Webhook delivery logging
  - Integration di pinjaman dan pembayaran logic
  - Tracking status penagihan
  - Log aktivitas penagihan

### Cron Jobs
- **File**: cron_daily_tasks.php
- **Features**:
  - Auto-calculate denda
  - Update status pinjaman overdue
  - Generate pengingat penagihan
  - Daily summary reports

### Koperasi Isolation
- **Helper**: includes/koperasi_isolation.php
- **Features**:
  - Data isolation per koperasi (bos)
  - Cross-koperasi operations API
  - Multi-tenant support

### Province Activation
- **Pages**: pages/app_owner/provinsi_activation.php
- **API**: api/provinsi_activation.php
- **Features**:
  - Aktivasi provinsi untuk rollout
  - Per-province configuration

### Enhanced Reference Tables
- **ref_frekuensi_angsuran** — Menggantikan enum frekuensi (v2.4.0)
  - ID 1: Harian (max 100 hari)
  - ID 2: Mingguan (max 52 minggu)
  - ID 3: Bulanan (max 36 bulan)
- **ref_produk_pinjaman** — Catalog produk pinjaman
- **pinjaman_jaminan** — Enhanced jaminan management

### v2.3.1 Feature Flags System (Still Active)
| Feature Key | Category | Default | Description |
|-------------|----------|---------|-------------|
| wa_notifikasi | wa | OFF | WhatsApp notifikasi via Fonnte API |
| wa_pengingat_auto | wa | OFF | Auto reminder WA (cron harian) |
| two_factor_auth | auth | OFF | 2FA TOTP untuk login |
| pwa | pwa | OFF | Progressive Web App support |
| gps_pembayaran | lapangan | OFF | GPS tracking pembayaran lapangan |
| export_laporan | laporan | OFF | Export laporan CSV/PDF |
| target_petugas | lapangan | OFF | Target kinerja petugas |
| slip_harian | lapangan | OFF | Slip harian petugas |
| kolektibilitas | lapangan | OFF | Kolektibilitas OJK 1-5 |
| cron_harian | system | OFF | Cron job harian otomatis |
| simulasi_pinjaman | lapangan | ON | Simulasi pinjaman real-time |

---

## Application Structure

### API Endpoints (38+ files)
| File | Fungsi |
|------|--------|
| alamat.php | Cascade dropdown lokasi (db_alamat) |
| accounting.php | Akuntansi (jurnal, ledger, neraca) |
| angsuran.php | CRUD angsuran |
| auth.php | Login/logout API |
| auth_2fa.php | 2FA TOTP (v2.3.1) |
| auto_confirm_settings.php | Auto-confirm pinjaman |
| bos_registration.php | Registrasi & approval bos |
| branch_managers.php | Assign manager ke cabang |
| cabang.php | CRUD cabang |
| cross_koperasi.php | Cross-koperasi operations (v2.4.0) |
| cron_daily_tasks.php | Cron job harian (v2.4.0) |
| daily_cash_reconciliation.php | Rekonsiliasi kas |
| dashboard.php | Statistik dashboard |
| delegated_permissions.php | Delegasi permission |
| export.php | Export laporan CSV/PDF (v2.3.1) |
| family_risk.php | Penilaian risiko keluarga |
| feature_flags.php | Feature flags management (v2.3.1) |
| field_officer_activities.php | Aktivitas petugas |
| kas_bon.php | Kasbon karyawan |
| kas_petugas.php | Kas petugas |
| kas_petugas_setoran.php | Setoran petugas |
| nasabah.php | CRUD nasabah |
| nasabah_blacklist.php | Blacklist nasabah |
| nasabah_portal.php | API portal nasabah (v2.4.0) |
| ocr.php | OCR KTP |
| pembayaran.php | CRUD pembayaran |
| pembayaran_elektronik.php | Pembayaran elektronik (v2.4.0) |
| pengeluaran.php | CRUD pengeluaran |
| pengingat_penagihan.php | Pengingat penagihan (v2.4.0) |
| penagihan.php | Manajemen penagihan (v2.4.0) |
| petugas_tugas_pengajuan.php | Pengajuan tugas petugas (v2.4.0) |
| pinjaman.php | CRUD pinjaman |
| provinsi_activation.php | Aktivasi provinsi (v2.4.0) |
| roles.php | Manajemen role & permission |
| search_people.php | Search people di db_orang (v2.4.0) |
| setting_bunga.php | Setting bunga |
| target_petugas.php | Target petugas (v2.3.1) |
| wa_notifikasi.php | WA notifikasi via Fonnte (v2.3.1) |
| business.php | Business logic operations |
| transfer_karyawan.php | Transfer karyawan antar cabang |

### Includes (28 files)
| File | Fungsi |
|------|--------|
| functions.php | Core business logic |
| people_helper.php | CRUD db_orang.people + addresses |
| alamat_helper.php | Dropdown lokasi (query_alamat) |
| address_helper.php | Format alamat |
| accounting_helper.php | Helper akuntansi |
| bunga_calculator.php | Kalkulator bunga |
| usage_tracker.php | Track API/render per koperasi |
| csrf.php | CSRF protection |
| validation.php | Input validation |
| error_handler.php | Error handling |
| database_class.php | Database wrapper |
| datatable_helper.php | DataTable.js |
| sweetalert_helper.php | SweetAlert2 |
| select2_helper.php | Select2 |
| flatpickr_helper.php | Flatpickr |
| family_risk.php | Logika risiko keluarga |
| kas_bon.php | Logika kasbon |
| kas_petugas.php | Logika kas petugas |
| pengeluaran.php | Logika pengeluaran |
| ocr_ktp.php | OCR KTP |
| jwt_auth.php | JWT auth |
| jwt_helper.php | JWT helper |
| auto_confirm.php | Auto-confirm logic |
| sidebar.php | Dynamic sidebar rendering |
| feature_flags.php | Feature flags helper (v2.3.1) |
| wa_notifikasi.php | WA notification via Fonnte (v2.3.1) |
| business_logic.php | Business logic operations |
| koperasi_isolation.php | Multi-tenant isolation (v2.4.0) |

### Page Modules (26 directories)
angsuran, app_owner, audit, auto_confirm, bos, cabang, cash_reconciliation, family_risk, field_activities, jaminan, kas_bon, kas_petugas, kinerja, laporan, nasabah, notifikasi, pembayaran, pengeluaran, penagihan, permissions, petugas, pinjaman, rute_harian, setting_bunga, superadmin, users

### appOwner Pages (9)
dashboard, approvals, koperasi, billing, usage, ai_advisor, settings, features, provinsi_activation (v2.4.0)

### Nasabah Portal Pages (7) - v2.4.0
dashboard, profil, pengajuan_pinjaman, pengajuan_simpanan, pinjaman, angsuran, pembayaran, data_keluarga

---

## Page Layout Pattern

Semua halaman (kecuali compact/standalone views) menggunakan layout konsisten:

```html
<div class="main-container">
    <?php require_once BASE_PATH . '/includes/sidebar.php'; ?>
    <main class="content-area">
        <!-- page content -->
    </main>
</div>
```

- **sidebar.php** menyediakan: navbar (fixed top), sidebar navigasi (role-based), dan CSS layout
- **Standalone pages** (compact/mobile): `bayar_compact.php`, `index_compact.php`, `blacklist_compact.php`, `transaksi.php`, `riwayat_harian.php`, `gabungan.php`, `setup_headquarters.php`

---

## Technology Stack

- **Backend**: PHP 8.2, MySQL/MariaDB, MySQLi prepared statements
- **Frontend**: Bootstrap 5.3, DataTable.js 1.13.6, SweetAlert2 v11, Select2 4.1.0, Flatpickr 4.6.13
- **Testing**: F2E (PHP), E2E (Puppeteer), Bash curl scripts
- **Server**: XAMPP on Linux (Apache 2.4.58, MariaDB 10.4.32, PHP 8.2.12)
- **WA Provider**: Fonnte API (v2.3.1)
- **PWA**: Service Worker + Manifest (v2.3.1)

## Security Features
- SQL injection prevention (prepared statements)
- CSRF protection (semua forms)
- Session timeout (2 jam)
- Rate limiting API (60 req/min)
- Input validation layer
- Role-based access control
- Password hashing (bcrypt)
- Error logging

## Configuration
- **Host**: localhost
- **Path**: /opt/lampp/htdocs/kewer
- **MySQL**: root / root
- **SUDO**: 8208
- **WA Token**: WA_TOKEN (di .env, untuk Fonnte API)
- **WA Provider**: fonnte (default)

## Database Files (database/)
- `kewer.sql` — Full export database kewer (67 tabel + 1 view + data)
- `db_alamat.sql` — Export db_alamat (24 tabel + data nasional dengan geospasial)
- `db_orang.sql` — Export db_orang (19 tabel + geospasial nasional)
- `migrations/` — Migration scripts (012-027 untuk v2.4.0)

---

## Bugfix History (v2.4.0)

| File | Issue | Fix |
|------|-------|-----|
| `api/dashboard.php` | Undefined `$cabang_id` | Replaced with `$kantor_id` |
| `api/auto_confirm_settings.php` | `hasPermission()` undefined | Added `require functions.php` |
| `pages/petugas/slip_harian.php` | Unknown column `a.ke` | Changed to `a.no_angsuran` |
| `pages/angsuran/cetak_kwitansi.php` | Unknown column `c.nama`, `byr.dibayar_oleh` | Fixed to `c.nama_cabang`, `byr.petugas_id`, `byr.cara_bayar` |
| `pages/pinjaman/tambah.php` | Undefined `$error`/`$success` | Initialized before POST block |
| 7 pages | Double navbar (sidebar.php + inline nav) | Removed inline navbar |
| 12 pages | Hardcoded mini sidebar | Replaced with shared `sidebar.php` |
| **v2.4.0 Migration** | Frekuensi enum → frekuensi_id | Migration 021-027 |
| **v2.4.0 Migration** | Added penagihan system tables | Migration 020 |
| **v2.4.0 Migration** | Added nasabah portal tables | Migration 015-019 |

---

## Current Gaps & Improvement Areas

### 🔴 High Priority Gaps
1. **WA Notifikasi Full Implementation** - API exists but not fully integrated with penagihan system
2. **Export/Import Data** - Basic export exists, need full Excel/CSV import with validation
3. **Advanced Dashboard Analytics** - Basic dashboard exists, need real-time charts and metrics

### 🟡 Medium Priority Gaps
4. **Credit Scoring System** - Not implemented (planned in ROADMAP.md)
5. **Payment Gateway Integration** - Basic pembayaran_elektronik.php exists, need full QRIS/VA/e-wallet
6. **GPS Tracking for Field Officers** - Feature flag exists but not implemented
7. **Audit Trail UI** - audit_log table exists but no UI

### 🟢 Low Priority Gaps
8. **PWA Support** - Feature flag exists but not implemented
9. **2FA TOTP** - Feature flag exists but not fully implemented
10. **Advanced Reporting** - PDF generation, scheduled reports
