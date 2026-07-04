# Kode Inti EBP

**Versi:** 1.0.0

**Status:** Implementasi Awal

---

# Ikhtisar

Direktori ini berisi komponen inti dari Platform Bisnis Enterprise (EBP). Komponen-komponen ini dirancang untuk digunakan kembali di seluruh produk EBP dan menyediakan fungsionalitas dasar untuk autentikasi, konektivitas database, penanganan API, dan lainnya.

---

# Komponen Inti

## Autentikasi

**Lokasi:** `Authentication/`

**Komponen:**
- `JWT.php` - Implementasi JSON Web Token untuk autentikasi
- `AuthMiddleware.php` - Middleware autentikasi untuk permintaan API

**Fitur:**
- Enkoding dan dekoding token JWT
- Validasi token dengan pemeriksaan kedaluwarsa
- Autentikasi berbasis peran
- Manajemen konteks tenant dan cabang
- Manajemen kunci rahasia yang aman

**Penggunaan:**
```php
use EBP\Core\Authentication\JWT;
use EBP\Core\Authentication\AuthMiddleware;

// Buat token JWT
$jwt = new JWT();
$token = $jwt->encode([
    'user_id' => 1,
    'username' => 'admin',
    'tenant_id' => 1,
    'branch_id' => 2,
    'role' => 'Administrator',
    'exp' => time() + (60 * 60 * 8)
]);

// Autentikasi permintaan
$middleware = new AuthMiddleware();
$payload = $middleware->authenticate();
```

---

## Izin (Permission)

**Lokasi:** `Permission/`

**Status:** Akan diimplementasikan

**Komponen yang Direncanakan:**
- `PermissionMiddleware.php` - Middleware pemeriksaan izin
- `PermissionService.php` - Layanan manajemen izin

---

## Tenant

**Lokasi:** `Tenant/`

**Status:** Akan diimplementasikan

**Komponen yang Direncanakan:**
- `TenantMiddleware.php` - Middleware konteks tenant
- `TenantService.php` - Layanan manajemen tenant

---

## Audit

**Lokasi:** `Audit/`

**Status:** Akan diimplementasikan

**Komponen yang Direncanakan:**
- `Audit.php` - Utilitas logging audit
- `AuditService.php` - Layanan manajemen audit

---

## Database

**Lokasi:** `Database/`

**Komponen:**
- `Database.php` - Manajer koneksi database

**Fitur:**
- Pola singleton untuk manajemen koneksi
- Fallback koneksi socket dan host
- PDO dengan penanganan error yang tepat
- Pengujian koneksi
- Pengambilan informasi database
- Dukungan variabel lingkungan

**Penggunaan:**
```php
use EBP\Core\Database\Database;

// Dapatkan instance database
$db = Database::getInstance();
$pdo = $db->connect();

// Atau dengan konfigurasi kustom
$db = new Database([
    'host' => 'localhost',
    'dbname' => 'my_database',
    'username' => 'user',
    'password' => 'pass'
]);
$pdo = $db->connect();

// Uji koneksi
if ($db->testConnection()) {
    echo "Koneksi berhasil";
}

// Dapatkan info database
$info = $db->getDatabaseInfo();
```

---

## API

**Lokasi:** `API/`

**Komponen:**
- `Response.php` - Penangan respons API yang distandarisasi

**Fitur:**
- Format respons JSON
- Respons sukses dan error
- Manajemen kode status HTTP
- Penanganan error validasi
- Dukungan paginasi
- Format respons standar

**Penggunaan:**
```php
use EBP\Core\API\Response;

// Respons sukses
Response::success($data, 'Operasi berhasil');

// Respons error
Response::error('Input tidak valid', 400, $errors);

// Error validasi
Response::validationError($errors);

// Tidak ditemukan
Response::notFound('Sumber daya tidak ditemukan');

// Tidak terautentikasi
Response::unauthorized('Kredensial tidak valid');

// Dilarang
Response::forbidden('Akses ditolak');

// Error server
Response::serverError('Error internal');

// Respons terpaginasi
Response::paginated($data, $total, $page, $limit);
```

---

## Logging

**Lokasi:** `Logging/`

**Status:** Akan diimplementasikan

**Komponen yang Direncanakan:**
- `Logger.php` - Utilitas logging
- `LogService.php` - Layanan manajemen log

---

## File

**Lokasi:** `File/`

**Status:** Akan diimplementasikan

**Komponen yang Direncanakan:**
- `FileManager.php` - Utilitas manajemen file
- `StorageService.php` - Layanan manajemen penyimpanan

---

# Konfigurasi

## Variabel Lingkungan

Komponen inti mendukung variabel lingkungan berikut:

```bash
# Konfigurasi JWT
JWT_SECRET=kunci_rahasia_anda_di_sini

# Konfigurasi Database
DB_HOST=localhost
DB_SOCKET=/opt/lampp/var/mysql/mysql.sock
DB_NAME=ebp_platform_db
DB_USER=ebp_app
DB_PASSWORD=ebp_secure_password_2026
```

---

# Panduan Integrasi

## Menggunakan Inti EBP dalam Produk

1. **Sertakan Inti EBP dalam produk Anda:**
   ```php
   require_once '/path/to/EBP/06_CORE_CODE/Authentication/JWT.php';
   require_once '/path/to/EBP/06_CORE_CODE/Database/Database.php';
   ```

2. **Gunakan impor berbasis namespace:**
   ```php
   use EBP\Core\Authentication\JWT;
   use EBP\Core\Database\Database;
   ```

3. **Konfigurasikan variabel lingkungan:**
   ```bash
   export JWT_SECRET=kunci_rahasia_anda
   export DB_NAME=database_anda
   ```

---

# Status Pengembangan

| Komponen | Status | Prioritas |
|-----------|--------|----------|
| Autentikasi | ✅ Selesai | Tinggi |
| Izin | ⏳ Tertunda | Tinggi |
| Tenant | ⏳ Tertunda | Tinggi |
| Audit | ⏳ Tertunda | Sedang |
| Database | ✅ Selesai | Tinggi |
| API | ✅ Selesai | Tinggi |
| Logging | ⏳ Tertunda | Sedang |
| File | ⏳ Tertunda | Rendah |

---

# Langkah Selanjutnya

1. **Selesaikan komponen inti yang tersisa**
   - Implementasikan middleware Izin
   - Implementasikan layanan Tenant
   - Implementasikan logging Audit

2. **Tambahkan unit test**
   - Uji enkoding/dekoding JWT
   - Uji koneksi database
   - Uji respons API

3. **Tambahkan dokumentasi**
   - Dokumentasi API
   - Contoh penggunaan
   - Panduan integrasi

4. **Buat mesin bersama**
   - Mesin Harga
   - Mesin Inventaris
   - Mesin Akuntansi

---

**Akhir Dokumen**
