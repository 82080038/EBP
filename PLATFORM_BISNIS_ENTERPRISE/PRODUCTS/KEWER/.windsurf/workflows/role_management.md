---
description: Workflow untuk manajemen role dan permissions di aplikasi Kewer
---

# Role Management Workflow

## Overview
Workflow ini digunakan untuk menambah, mengubah, atau menghapus role dan permissions dalam aplikasi Kewer.

## Prerequisites
- User harus memiliki role `superadmin` atau permissions `assign_permissions`
- Database harus sudah memiliki tabel `permissions`, `role_permissions`, dan `user_permissions`

## Langkah-langkah

### 1. Menambah Role Baru

Jika ingin menambah role baru ke sistem:

1. **Buat file role definition di `roles/` directory:**
   - Nama file: `{role_code}.json`
   - Format: JSON dengan struktur sesuai template di `roles/README.md`
   - Contoh: `roles/supervisor.json`

2. **Update role hierarchy di `includes/functions.php`:**
   - Tambahkan role baru ke fungsi `getRoleHierarchyLevel()`
   - Tentukan level hierarchy (1-9)
   - Update fungsi `canManageRole()` jika role bisa mengelola role lain

3. **Update sidebar menu di `includes/sidebar.php`:**
   - Tambahkan role ke array `pusat_roles` jika role akses pusat
   - Tambahkan permission checks untuk menu yang spesifik ke role tersebut

4. **Seed permissions di database:**
   - Jalankan SQL untuk insert permissions yang diperlukan role baru
   - Assign permissions ke role melalui `role_permissions` table

### 2. Mengubah Role yang Sudah Ada

1. **Update file role definition:**
   - Edit file JSON di `roles/{role_code}.json`
   - Update module access, permissions, dan features

2. **Update backend functions:**
   - Update `includes/functions.php` jika ada perubahan hierarchy
   - Update permission checks di pages jika diperlukan

3. **Update database permissions:**
   - Gunakan API `api/roles.php?action=assign_role_permission`
   - Atau langsung update table `role_permissions`

### 3. Menghapus Role

**PERINGATAN:** Hapus role hanya jika yakin tidak ada user yang menggunakan role tersebut.

1. **Cek user yang menggunakan role:**
```sql
SELECT COUNT(*) FROM users WHERE role = 'role_code';
```

2. **Reassign user ke role lain jika ada:**
```sql
UPDATE users SET role = 'new_role' WHERE role = 'old_role';
```

3. **Hapus role dari database:**
```sql
DELETE FROM role_permissions WHERE role_kode = 'role_code';
```

4. **Hapus file role definition:**
```bash
rm roles/role_code.json
```

### 4. Menambah Permission Baru

1. **Tambahkan permission ke table `permissions`:**
```sql
INSERT INTO permissions (kode, nama, kategori, deskripsi)
VALUES ('permission_code', 'Nama Permission', 'kategori', 'Deskripsi');
```

2. **Assign permission ke role yang sesuai:**
```sql
INSERT INTO role_permissions (role_kode, permission_id, granted)
SELECT 'role_code', id, 1 FROM permissions WHERE kode = 'permission_code';
```

3. **Update role definition files:**
   - Tambahkan permission ke module yang sesuai di file JSON

4. **Update permission checks di code:**
   - Gunakan `hasPermission('permission_code')` di pages/functions

### 5. Mengubah Permission

1. **Update deskripsi permission di database:**
```sql
UPDATE permissions SET nama = 'Nama Baru', deskripsi = 'Deskripsi Baru' 
WHERE kode = 'permission_code';
```

2. **Update role definition files jika diperlukan**

### 6. Menghapus Permission

**PERINGATAN:** Hapus permission hanya jika tidak digunakan lagi di code.

1. **Hapus dari role_permissions:**
```sql
DELETE FROM role_permissions WHERE permission_id = (SELECT id FROM permissions WHERE kode = 'permission_code');
```

2. **Hapus dari user_permissions:**
```sql
DELETE FROM user_permissions WHERE permission_id = (SELECT id FROM permissions WHERE kode = 'permission_code');
```

3. **Hapus dari permissions table:**
```sql
DELETE FROM permissions WHERE kode = 'permission_code';
```

## Testing

### Test Role Changes

1. Login sebagai user dengan role yang diubah
2. Verifikasi akses menu sesuai module access
3. Test fitur-fitur yang seharusnya bisa diakses
4. Pastikan fitur yang tidak boleh diakses terblokir

### Test Permission Changes

1. Login sebagai user dengan permission yang diubah
2. Verifikasi fitur yang menggunakan permission tersebut
3. Test CRUD operations jika permission berubah
4. Pastikan permission checks berfungsi dengan benar

## Rollback

Jika terjadi error setelah perubahan role/permission:

1. **Restore database dari backup:**
```bash
mysql -u root -p kewer < backup_kewer.sql
```

2. **Restore file role definition dari git:**
```bash
git checkout roles/role_code.json
```

3. **Restore functions.php dari git:**
```bash
git checkout includes/functions.php
```

## Best Practices

1. **Selalu backup database sebelum mengubah role/permission**
2. **Test di environment development dulu sebelum production**
3. **Dokumentasikan setiap perubahan role/permission**
4. **Gunakan version control untuk file role definition**
5. **Review impact changes ke user yang terdampak**
6. **Komunikasikan perubahan ke tim dan user**

## Troubleshooting

### Permission tidak berfungsi
- Cek apakah permission ada di table `permissions`
- Cek apakah permission di-assign ke role di `role_permissions`
- Cek apakah user memiliki role yang sesuai
- Cek apakah permission check menggunakan kode yang benar

### Menu tidak muncul
- Cek apakah user memiliki permission yang diperlukan
- Cek apakah role ada di array `pusat_roles` jika akses pusat
- Cek sidebar.php untuk permission checks
- Clear browser cache

### Role hierarchy tidak berfungsi
- Cek fungsi `getRoleHierarchyLevel()` di functions.php
- Cek apakah level hierarchy sudah benar
- Cek fungsi `canManageRole()` untuk logic yang benar
