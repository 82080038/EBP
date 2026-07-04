# Kewer Simulation Report

**Terakhir Diperbarui:** 2026-05-08

---

## Struktur Aplikasi
- **Model**: Single-office koperasi pinjaman modal pedagang
- **Arsitektur**: PHP 8 + MySQL (3 database: kewer, db_alamat_simple, db_orang)
- **Auth**: Session-based, 9 role levels + appOwner

## Role yang Aktif (Sesuai Database)

| Role | Username | Password | Level |
|------|----------|----------|-------|
| appOwner | appowner | AppOwner2024! | 0 |
| bos | patri | Kewer2024! | 1 |
| manager_pusat | mgr_pusat | Kewer2024! | 3 |
| manager_cabang | mgr_balige | Kewer2024! | 4 |
| admin_pusat | adm_pusat | Kewer2024! | 5 |
| admin_cabang | adm_cabang | Kewer2024! | 6 |
| petugas_pusat | ptr_pngr1 | Kewer2024! | 7 |
| petugas_cabang | ptr_blg1 | Kewer2024! | 8 |
| karyawan | krw_pngr | Kewer2024! | 9 |

## Flow Simulasi Harian

```
[Admin Pusat]  →  Input nasabah baru + pengajuan pinjaman
      ↓
[Bos/Manager]  →  Review dan approve pinjaman
      ↓
[Petugas]      →  Koleksi pembayaran angsuran di lapangan
      ↓
[Manager]      →  Review angsuran & operasional
      ↓
[Bos]          →  Review dashboard & laporan akhir hari
      ↓
[Karyawan]     →  Rekonsiliasi kas harian
      ↓
[AppOwner]     →  Monitoring platform (tidak akses data koperasi)
```

---

## Detail Simulasi per Role

### Bos (patri / Kewer2024!)

**Identitas:**
- Role: bos
- Hierarchy Level: 1
- Akses: Full access ke semua data dan fitur koperasi

**Aktivitas Harian:**
- Pagi: Cek dashboard, review pinjaman pending, approve/reject pinjaman
- Siang: Review laporan keuangan, pantau koleksi pembayaran petugas
- Sore: Review performa, cek audit log, kelola setting bunga

**Permissions:**
- dashboard.read, nasabah.read, manage_nasabah
- pinjaman.read, manage_pinjaman, pinjaman.approve
- angsuran.read, manage_pembayaran
- users.read, users.create, manage_users
- view_laporan, manage_pengeluaran, manage_kas_bon
- manage_bunga, view_settings, kas_petugas.read, kas_petugas.update
- manage_petugas, pinjaman.auto_confirm

**Quick Login:**
```
/login.php?test_login=true&username=patri&password=Kewer2024!
```

---

### Manager Pusat (mgr_pusat / Kewer2024!)

**Identitas:**
- Role: manager_pusat
- Hierarchy Level: 3
- Akses: Kontrol operasional, approve pinjaman, kelola staff

**Aktivitas Harian:**
- Review dan approve pinjaman
- Kelola staff dan petugas
- Monitor performa operasional

**Quick Login:**
```
/login.php?test_login=true&username=mgr_pusat&password=Kewer2024!
```

---

### Manager Cabang (mgr_balige / Kewer2024!)

**Identitas:**
- Role: manager_cabang
- Hierarchy Level: 4
- Akses: Operasional cabang, approve pinjaman

**Aktivitas Harian:**
- Operasional harian cabang
- Approve pinjaman cabang
- Monitor petugas cabang

**Quick Login:**
```
/login.php?test_login=true&username=mgr_balige&password=Kewer2024!
```

---

### Admin Pusat (adm_pusat / Kewer2024!)

**Identitas:**
- Role: admin_pusat
- Hierarchy Level: 5
- Akses: Input nasabah, pinjaman, angsuran, laporan

**Aktivitas Harian:**
- Input nasabah baru
- Input pengajuan pinjaman
- Input pembayaran angsuran
- Generate laporan

**Quick Login:**
```
/login.php?test_login=true&username=adm_pusat&password=Kewer2024!
```

---

### Admin Cabang (adm_cabang / Kewer2024!)

**Identitas:**
- Role: admin_cabang
- Hierarchy Level: 6
- Akses: Input nasabah, pinjaman, angsuran cabang

**Aktivitas Harian:**
- Input nasabah cabang
- Input pinjaman cabang
- Input pembayaran cabang

**Quick Login:**
```
/login.php?test_login=true&username=adm_cabang&password=Kewer2024!
```

---

### Petugas Pusat (ptr_pngr1 / Kewer2024!)

**Identitas:**
- Role: petugas_pusat
- Hierarchy Level: 7
- Akses: Koleksi angsuran lapangan, kas petugas

**Aktivitas Harian:**
- Koleksi pembayaran angsuran di lapangan
- Update kas petugas
- Input aktivitas lapangan

**Quick Login:**
```
/login.php?test_login=true&username=ptr_pngr1&password=Kewer2024!
```

---

### Petugas Cabang (ptr_blg1 / Kewer2024!)

**Identitas:**
- Role: petugas_cabang
- Hierarchy Level: 8
- Akses: Koleksi angsuran lapangan, aktivitas lapangan

**Aktivitas Harian:**
- Koleksi pembayaran angsuran cabang
- Update aktivitas lapangan

**Quick Login:**
```
/login.php?test_login=true&username=ptr_blg1&password=Kewer2024!
```

---

### Karyawan (krw_pngr / Kewer2024!)

**Identitas:**
- Role: karyawan
- Hierarchy Level: 9
- Akses: Dukungan administratif, rekonsiliasi kas

**Aktivitas Harian:**
- Rekonsiliasi kas harian
- Input pengeluaran
- Support administratif

**Quick Login:**
```
/login.php?test_login=true&username=krw_pngr&password=Kewer2024!
```

---

## Cara Menjalankan Simulasi

```bash
cd /opt/lampp/htdocs/kewer

# Jalankan setup dulu (tambah staff jika belum ada)
node simulation/run_simulation.js setup

# Jalankan simulasi 14 hari
node simulation/run_simulation.js sim

# Jalankan keduanya sekaligus
node simulation/run_simulation.js all

# Test login saja
node simulation/test_login.js
```

---

## Test Script

```bash
# Test payment methods feature
node tests/puppeteer-payment-methods.test.js

# Comprehensive test untuk appOwner
node tests/puppeteer-appowner-comprehensive.test.js

# Comprehensive test untuk bos
node tests/puppeteer-bos-comprehensive.test.js
```

Screenshots disimpan di `tests/screenshots/`

node simulation/run_simulation.js all

# Test login saja
node simulation/test_login.js
```

## File Simulasi

| File | Keterangan |
|------|-----------|
| `sim_helpers.js` | Helper functions: login, browser, wilayah, state |
| `sim_setup.js` | Setup awal: verifikasi bos + tambah staff |
| `sim_daily.js` | Simulasi aktivitas harian 14 hari per role |
| `run_simulation.js` | Entry point utama |
| `test_login.js` | Test login credentials |

## Catatan Penting
- Simulasi menggunakan `test_login=true` di `login.php` (hanya di APP_ENV=development)
- Password semua user koperasi: `Kewer2024!`
- Password appOwner: `AppOwner2024!`
- Single office structure — tidak ada multi-cabang
- kantor_id = 1 (hardcoded untuk struktur single office)
