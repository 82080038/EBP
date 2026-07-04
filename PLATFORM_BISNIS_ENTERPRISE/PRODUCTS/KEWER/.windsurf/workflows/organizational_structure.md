---
description: Organizational structure workflow untuk aplikasi Kewer
---

# Organizational Structure Workflow

## Overview
Workflow untuk setup dan manajemen struktur organisasi baru di aplikasi Kewer.

## Bos Registration Workflow

### 1. Bos Mendaftar
```bash
# Bos mendaftar melalui halaman publik
Buka browser: http://localhost/kewer/pages/bos/register.php

Isi form:
- Username, password, konfirmasi password
- Nama lengkap, nama perusahaan
- Email, no. telepon
- Alamat lengkap (provinsi, kabupaten, kecamatan, desa)

Submit form - data tersimpan di tabel bos_registrations dengan status 'pending'
```

### 2. Superadmin Approval
```bash
# Login sebagai appOwner
Buka: http://localhost/kewer/login.php
Username: appowner
Password: AppOwner2024!

# Navigasi ke halaman approval
Buka: http://localhost/kewer/pages/app_owner/approvals.php

# Review dan setujui pendaftaran
- Lihat data bos pending
- Klik "Setujui" untuk approve
- Atau klik "Tolak" dengan alasan

Setelah approval:
- User account dibuat dengan role 'bos'
- Bos dapat login
- Status bos_registrations diupdate ke 'approved'
```

### 3. Bos Setup Kantor Pusat (Wajib)
```bash
# Login sebagai bos yang disetujui
Username: [username bos]
Password: [password bos]

# Otomatis diarahkan ke setup kantor pusat
Buka: http://localhost/kewer/pages/bos/setup_headquarters.php

Isi form:
- Kode cabang (misal: HQ)
- Nama kantor pusat
- No. telepon, email
- Alamat lengkap dengan dropdown wilayah
- Status: aktif

Submit form:
- Cabang dibuat dengan is_headquarters = 1
- Bos cabang_id diupdate ke ID kantor pusat
- Redirect ke dashboard
```

## Delegasi Permissions Workflow

### 1. Bos Delegate Permissions ke Karyawan
```bash
# Login sebagai bos
Buka: http://localhost/kewer/pages/bos/delegated_permissions.php

Pilih:
- Karyawan yang akan didelegasi permission
- Scope permission:
  * employee_crud: CRUD karyawan
  * branch_crud: CRUD cabang
  * branch_employee_crud: CRUD karyawan cabang
  * all_operations: Semua operasi
- Tanggal expired (opsional)
- Catatan (opsional)

Submit:
- Permission disimpan di tabel delegated_permissions
- Karyawan dapat menggunakan permission
- Bos dapat mencabut permission kapan saja
```

### 2. Testing Delegated Permissions
```bash
# Login sebagai karyawan yang didelegasi permission
Coba akses fitur sesuai scope yang didelegasi

Jika permission tidak ada:
- Akses ditolak
- Pesan error: "Anda tidak memiliki izin untuk mengakses halaman ini"
```

## Branch Manager Assignment Workflow

### 1. Bos Assign Manager ke Cabang
```bash
# Gunakan API branch_managers
curl -X POST "http://localhost/kewer/api/branch_managers.php?action=assign" \
  -d "cabang_id=[ID cabang]" \
  -d "manager_user_id=[ID user manager]" \
  -d "manager_type=manager_cabang" \
  -d "can_add_employees=1" \
  -d "can_manage_branch=1"

Manager types:
- manager_cabang: Manager cabang
- admin_cabang: Admin cabang
- petugas_cabang: Petugas cabang (fallback)
```

### 2. Manager Menambah Karyawan di Cabang
```bash
# Login sebagai manager cabang
Buka: http://localhost/kewer/pages/petugas/tambah.php

Jika can_add_employees = 1:
- Dapat menambah karyawan di cabang
- Karyawan otomatis memiliki owner_bos_id = ID bos
- Karyawan cabang_id = cabang tempat manager ditugaskan
```

## Fallback Scenarios

### Jika Admin Pusat Tidak Ada
```bash
# Bos dapat delegate admin_pusat permissions ke manager_pusat
Buka: http://localhost/kewer/pages/bos/delegated_permissions.php

Pilih:
- Karyawan: manager_pusat
- Scope: all_operations (atau scope yang sesuai)

Manager_pusat dapat:
- Melakukan tugas administratif lintas cabang
- Mengelola user dan cabang
```

### Jika Manager Cabang Tidak Ada
```bash
# Bos dapat delegate manager_cabang permissions ke admin_cabang
Buka: http://localhost/kewer/pages/bos/delegated_permissions.php

Pilih:
- Karyawan: admin_cabang
- Scope: branch_employee_crud

Admin_cabang dapat:
- Menambah karyawan di cabang
- Mengelola data cabang
```

### Jika Admin Cabang Juga Tidak Ada
```bash
# Bos dapat delegate ke petugas_cabang
Buka: http://localhost/kewer/pages/bos/delegated_permissions.php

Pilih:
- Karyawan: petugas_cabang
- Scope: branch_employee_crud

Petugas_cabang dapat:
- Menambah karyawan di cabang (terbatas)
```

## Data Ownership dan Filtering

### owner_bos_id
```bash
# Semua karyawan yang ditambah bos memiliki owner_bos_id = ID bos
# Semua cabang yang dibuat bos memiliki owner_bos_id = ID bos

# Filter data berdasarkan owner_bos_id
- Bos hanya melihat data milik organisasinya
- Karyawan hanya melihat data organisasi bosnya
- Superadmin melihat semua data
```

### is_headquarters
```bash
# Kantor pusat memiliki is_headquarters = 1
# Cabang biasa memiliki is_headquarters = 0

# Bos hanya boleh memiliki 1 kantor pusat
# Validasi di pages/cabang/tambah.php
```

## Testing Manual

### 1. Test Bos Registration
```bash
# Buka halaman registrasi
http://localhost/kewer/pages/bos/register.php

# Daftar bos baru
# Cek tabel bos_registrations - status harus 'pending'
```

### 2. Test Bos Approval
```bash
# Login sebagai superadmin
http://localhost/kewer/login.php

# Buka halaman approval
http://localhost/kewer/pages/superadmin/bos_approvals.php

# Setujui bos
# Cek tabel users - user bos harus dibuat
# Cek tabel bos_registrations - status harus 'approved'
```

### 3. Test Setup Kantor Pusat
```bash
# Login sebagai bos
# Otomatis diarahkan ke setup kantor pusat

# Buat kantor pusat
# Cek tabel cabang - is_headquarters harus = 1
# Cek tabel users - bos cabang_id harus diupdate
```

### 4. Test Delegasi Permissions
```bash
# Login sebagai bos
http://localhost/kewer/pages/bos/delegated_permissions.php

# Delegate permission ke karyawan
# Cek tabel delegated_permissions

# Login sebagai karyawan
# Coba akses fitur sesuai scope yang didelegasi
```

### 5. Test Branch Manager
```bash
# Assign manager ke cabang via API
# Cek tabel branch_managers

# Login sebagai manager
# Coba tambah karyawan di cabang
```

## Troubleshooting

### Bos Tidak Dapat Login
```bash
# Cek apakah bos sudah disetujui
SELECT * FROM bos_registrations WHERE username = '[username]';

# Cek apakah user bos sudah dibuat
SELECT * FROM users WHERE role = 'bos' AND username = '[username]';
```

### Bos Tidak Dapat Membuat Cabang
```bash
# Cek apakah bos sudah memiliki kantor pusat
SELECT * FROM cabang WHERE owner_bos_id = [ID bos] AND is_headquarters = 1;

# Jika belum, arahkan bos ke setup kantor pusat
```

### Delegated Permissions Tidak Berfungsi
```bash
# Cek tabel delegated_permissions
SELECT * FROM delegated_permissions WHERE user_id = [ID karyawan];

# Cek fungsi hasPermission() di includes/functions.php
# Pastikan delegated permissions di-cek
```

## Data Integrasi

Setiap entitas terintegrasi ke 3 database:
- **Orang** (users/nasabah): `kewer.users` → `db_orang.people` → `db_orang.addresses`
- **Koperasi** (cabang): `kewer.cabang` → `db_orang.people` → `db_orang.addresses`
- **Lokasi**: referensi ke `db_alamat_simple` (provinces/regencies/districts/villages)
- **Aktivitas**: `kewer.koperasi_activities` (16 kategori + JSON metadata)
- **Daerah tugas**: `kewer.petugas_daerah_tugas` (district/village level)
