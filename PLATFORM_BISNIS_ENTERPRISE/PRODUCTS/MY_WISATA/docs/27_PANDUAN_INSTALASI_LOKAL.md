# MODUL 27 — PANDUAN INSTALASI LOKAL

> **Aplikasi:** Tour Guide Application  
> **Versi:** 1.0  
> **Tanggal:** 2026-06-30

---

## 1. RINGKASAN

Panduan instalasi lingkungan development lokal untuk Tour Guide Application
menggunakan XAMPP (Windows) atau LAMPP (Linux) dengan PHP 8.1+, MySQL 8.0+,
dan Apache.

**PENTING:** Aplikasi ini mendukung development di multiple komputer (Windows & Linux)
dengan konfigurasi terpusat di `prompting/config.json`. Lihat bagian **Konfigurasi
Multi-Environment** di bawah.

---

## 2. PRASYARAT

### 2.1 Software yang Dibutuhkan

| Software | Versi Minimum | Rekomendasi | Fungsi |
|----------|---------------|-------------|--------|
| XAMPP / LAMPP | 8.1 | 8.2+ | Apache + MySQL + PHP |
| Git | 2.30 | 2.40+ | Version control |
| VS Code | 1.80 | 1.90+ | Code editor |
| Browser | Chrome 100+ | Chrome 120+ | Testing |
| Composer | 2.5 | 2.7+ | PHP dependency (opsional) |

### 2.2 Ekstensi PHP yang Dibutuhkan

```
php -m | grep -E "pdo_mysql|mbstring|xml|curl|gd|zip|intl|fileinfo|openssl"
```

Ekstensi wajib:
- `pdo_mysql` — Database connection
- `mbstring` — Multibyte string handling
- `xml` — XML parsing
- `curl` — HTTP requests (opsional, untuk API QR)
- `gd` — Image processing
- `zip` — Archive handling
- `intl` — Internationalization
- `fileinfo` — File upload detection
- `openssl` — Random bytes, encryption

---

## 3. INSTALASI XAMPP (WINDOWS)

### 3.1 Download & Install

1. Download XAMPP dari https://www.apachefriends.org/
2. Pilih versi PHP 8.1+ (XAMPP 8.2 atau lebih baru)
3. Install ke `C:\xampp` (default)
4. Jalankan XAMPP Control Panel
5. Start **Apache** dan **MySQL**

### 3.2 Verifikasi

```cmd
C:\xampp\php\php.exe -v
:: Output: PHP 8.2.x (cli)

C:\xampp\php\php.exe -m | findstr "pdo_mysql mbstring gd"
:: Output: pdo_mysql, mbstring, gd
```

Buka browser: http://localhost → XAMPP dashboard terbuka

---

## 4. INSTALASI LAMPP (LINUX)

### 4.1 Download & Install

```bash
# Download
wget https://download.apachefriends.org/xampp-files/xampp-linux-x64-8.2.12-0-installer.run

# Make executable
chmod +x xampp-linux-x64-8.2.12-0-installer.run

# Install (sudo)
sudo ./xampp-linux-x64-8.2.12-0-installer.run

# Start
sudo /opt/lampp/lampp start
```

### 4.2 Verifikasi

```bash
/opt/lampp/bin/php -v
# Output: PHP 8.2.x (cli)

sudo /opt/lampp/lampp status
# Output: Apache running, MySQL running
```

Buka browser: http://localhost → XAMPP dashboard terbuka

---

## 5. SETUP PROJECT

### 5.1 Clone / Copy Project

```bash
# Linux
cd /opt/lampp/htdocs/
git clone https://github.com/yourrepo/tour-guide-app.git wisata
# atau copy manual ke /opt/lampp/htdocs/wisata/
```

```cmd
:: Windows
cd C:\xampp\htdocs
git clone https://github.com/yourrepo/tour-guide-app.git wisata
```

### 5.2 Struktur Folder yang Diharapkan

```
/opt/lampp/htdocs/wisata/        (Linux)
C:\xampp\htdocs\wisata\          (Windows)
├── app/
│   ├── config/
│   ├── core/
│   ├── controllers/
│   ├── models/
│   └── views/
├── public/
│   ├── assets/
│   └── uploads/
├── database/
├── logs/
├── docs/
├── .htaccess
└── index.php
```

### 5.3 Set Permissions (Linux only)

```bash
sudo chown -R $USER:$USER /opt/lampp/htdocs/wisata
sudo chmod -R 755 /opt/lampp/htdocs/wisata
chmod -R 777 /opt/lampp/htdocs/wisata/public/uploads
chmod -R 777 /opt/lampp/htdocs/wisata/logs
chmod -R 777 /opt/lampp/htdocs/wisata/database/backup
```

---

## 6. KONFIGURASI DATABASE

### 6.1 Buat Database via phpMyAdmin

1. Buka http://localhost/phpmyadmin
2. Klik **New** → buat database `tour_guide_app`
3. Pilih charset `utf8mb4_unicode_ci`

Atau via command line:

```bash
# Linux
/opt/lampp/bin/mysql -u root -e "
  CREATE DATABASE tour_guide_app 
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
"
```

```cmd
:: Windows
C:\xampp\mysql\bin\mysql -u root -e "CREATE DATABASE tour_guide_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### 6.2 Import Schema

```bash
# Linux
/opt/lampp/bin/mysql -u root tour_guide_app < /opt/lampp/htdocs/wisata/database/migration.sql
/opt/lampp/bin/mysql -u root tour_guide_app < /opt/lampp/htdocs/wisata/database/seed.sql
```

```cmd
:: Windows
C:\xampp\mysql\bin\mysql -u root tour_guide_app < C:\xampp\htdocs\wisata\database\migration.sql
C:\xampp\mysql\bin\mysql -u root tour_guide_app < C:\xampp\htdocs\wisata\database\seed.sql
```

Atau via phpMyAdmin:
1. Pilih database `tour_guide_app`
2. Tab **Import**
3. Pilih file `database/migration.sql` → Go
4. Ulangi untuk `database/seed.sql`

### 6.3 Konfigurasi Koneksi

Edit `app/config/database.php`:

```php
return [
    'host'    => 'localhost',
    'dbname'  => 'mywisata',
    'user'    => 'root',
    'pass'    => '',           // XAMPP default: kosong
    'charset' => 'utf8mb4',
];
```

Edit `app/config/config.php`:

```php
define('BASE_URL', 'http://localhost/mywisata/');
define('APP_ENV', 'development');  // development | production
define('APP_DEBUG', true);         // true di dev, false di prod
```

---

## 6.5 KONFIGURASI MULTI-ENVIRONMENT (WAJIB)

Aplikasi ini mendukung development di multiple komputer (Windows & Linux) dengan
konfigurasi terpusat. **WAJIB** mengkonfigurasi file ini sebelum memulai development.

### 6.5.1 Edit File Config

Edit file `prompting/config.json`:

```bash
# Linux
nano /opt/lampp/htdocs/mywisata/prompting/config.json

# Windows
notepad C:\xampp\htdocs\mywisata\prompting\config.json
```

### 6.5.2 Struktur Config

File config.json memiliki struktur berikut:

```json
{
  "environments": {
    "local": {
      "database": {
        "host": "localhost",
        "port": "3306",
        "username": "root",
        "password": "",
        "database_name": "mywisata"
      },
      "os_specific": {
        "linux": {
          "project_root": "/opt/lampp/htdocs/mywisata",
          "php_path": "/opt/lampp/bin/php",
          "mysql_path": "/opt/lampp/bin/mysql",
          "sudo_password": "8208",
          "phpmyadmin_password": "8208"
        },
        "windows": {
          "project_root": "C:\\xampp\\htdocs\\mywisata",
          "php_path": "C:\\xampp\\php\\php.exe",
          "mysql_path": "C:\\xampp\\mysql\\bin\\mysql.exe",
          "sudo_password": null,
          "phpmyadmin_password": "8208"
        }
      }
    }
  }
}
```

### 6.5.3 Sesuaikan dengan Environment Anda

**Untuk Linux:**
- Pastikan `project_root` sesuai dengan lokasi project
- Pastikan `php_path` dan `mysql_path` benar
- Isi `sudo_password` jika diperlukan
- Isi `phpmyadmin_password`

**Untuk Windows:**
- Pastikan `project_root` sesuai dengan lokasi project (gunakan double backslash `\\`)
- Pastikan `php_path` dan `mysql_path` benar
- `sudo_password` bisa null (tidak dipakai di Windows)
- Isi `phpmyadmin_password`

### 6.5.4 Environment Lain (Opsional)

Jika ada environment staging atau production, isi juga bagian:
- `environments.staging` - untuk staging server
- `environments.production` - untuk production server

### 6.5.5 Validasi Config

Setelah mengedit, validasi bahwa file JSON valid:

```bash
# Linux
python3 -m json.tool /opt/lampp/htdocs/mywisata/prompting/config.json

# Windows (jika ada Python)
python -m json.tool C:\xampp\htdocs\mywisata\prompting\config.json
```

Jika ada error JSON, perbaiki syntax (kurung kurawal, koma, quote).

### 6.5.6 Penggunaan Config

File config.json ini akan:
- Dibaca otomatis oleh AI development assistant (Cascade/Devin)
- Digunakan untuk menentukan paths yang benar sesuai OS
- Menyimpan credentials yang aman untuk multiple environment
- Mengizinkan development paralel di multiple komputer

Lihat [`prompting/README_SETUP.md`](../prompting/README_SETUP.md) untuk panduan lebih detail.

---

## 7. KONFIGURASI APACHE (XAMPP)

### 7.1 Enable mod_rewrite

XAMPP biasanya sudah mengaktifkan `mod_rewrite`. Jika belum:

**Linux:**
```bash
sudo /opt/lampp/lampp stopapache
# Edit /opt/lampp/etc/httpd.conf
# Uncomment: LoadModule rewrite_module modules/mod_rewrite.so
sudo /opt/lampp/lampp startapache
```

**Windows:**
```
1. Buka C:\xampp\apache\conf\httpd.conf
2. Uncomment: LoadModule rewrite_module modules/mod_rewrite.so
3. Restart Apache via XAMPP Control Panel
```

### 7.2 AllowOverride

Pastikan di `httpd.conf`:

```apache
<Directory "/opt/lampp/htdocs">
    AllowOverride All
    Require all granted
</Directory>
```

### 7.3 .htaccess Project

File `.htaccess` di root project:

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]

# Security headers
Header set X-Content-Type-Options "nosniff"
Header set X-Frame-Options "SAMEORIGIN"
Header set X-XSS-Protection "1; mode=block"
```

---

## 8. VERIFIKASI INSTALASI

### 8.1 Akses Aplikasi

Buka browser: **http://localhost/wisata/**

- Jika berhasil: Halaman login/home tampil
- Jika error 500: Cek `logs/error.log`
- Jika 404: Cek `.htaccess` dan `mod_rewrite`

### 8.2 Login Default

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@tourguide.app | admin123 |

> **Penting:** Ganti password admin setelah login pertama!

### 8.3 Cek Database Connection

```php
// Buat file test_db.php sementara di root project
<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=tour_guide_app;charset=utf8mb4', 'root', '');
    echo "Database connected successfully!";
    $count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    echo "<br>Users count: $count";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
// Hapus file setelah testing!
```

### 8.4 Cek phpinfo

```php
// Buat file phpinfo.php sementara
<?php phpinfo();
// Hapus file setelah testing!
```

---

## 9. KONFIGURASI VS CODE (OPSIONAL)

### 9.1 Extensions yang Direkomendasikan

| Extension | Fungsi |
|-----------|--------|
| PHP Intelephense | IntelliSense, autocomplete |
| PHP Debug | Xdebug debugging |
| PHP DocBlocker | Auto-generate docblocks |
| GitLens | Git integration |
| Tailwind CSS IntelliSense | CSS autocomplete (jika pakai) |
| Live Server | Static file preview |

### 9.2 Xdebug Setup

```ini
; Tambahkan di php.ini (/opt/lampp/etc/php.ini atau C:\xampp\php\php.ini)
[xdebug]
zend_extension=xdebug
xdebug.mode=debug
xdebug.start_with_request=yes
xdebug.client_port=9003
xdebug.client_host=127.0.0.1
```

VS Code `launch.json`:
```json
{
    "version": "0.2.0",
    "configurations": [
        {
            "name": "Listen for Xdebug",
            "type": "php",
            "request": "launch",
            "port": 9003,
            "pathMappings": {
                "/opt/lampp/htdocs/wisata": "${workspaceFolder}"
            }
        }
    ]
}
```

---

## 10. TROUBLESHOOTING

### 10.1 Blank Page / Error 500

| Penyebab | Solusi |
|----------|--------|
| PHP error tidak tampil | Set `display_errors = On` di `php.ini` |
| Syntax error | Cek `logs/error.log` |
| Missing extension | Cek `php -m`, install ekstensi yang kurang |
| Wrong BASE_URL | Sesuaikan di `app/config/config.php` |

### 10.2 Database Connection Failed

| Penyebab | Solusi |
|----------|--------|
| MySQL tidak running | Start MySQL via XAMPP Control Panel |
| Wrong credentials | Cek `app/config/database.php` |
| Database belum dibuat | Ikuti langkah 6.1 |
| Port bukan 3306 | Cek `/opt/lampp/etc/my.cnf` atau `C:\xampp\mysql\bin\my.ini` |

### 10.3 404 Not Found

| Penyebab | Solusi |
|----------|--------|
| mod_rewrite off | Enable di `httpd.conf` |
| .htaccess missing | Buat file `.htaccess` di root project |
| AllowOverride None | Ubah ke `AllowOverride All` |
| URL salah | Pastikan akses `http://localhost/wisata/` |

### 10.4 Port Conflict

```bash
# Cek port yang dipakai (Linux)
sudo netstat -tulpn | grep -E "80|3306|443"

# Jika port 80 dipakai, stop service:
sudo systemctl stop apache2     # jika ada Apache terpisah
sudo systemctl stop nginx       # jika ada Nginx

# Atau ubah port XAMPP:
# Edit /opt/lampp/etc/httpd.conf → Listen 8080
```

### 10.5 Upload File Gagal

| Penyebab | Solusi |
|----------|--------|
| Folder tidak writable | `chmod -R 777 public/uploads/` |
| File terlalu besar | Edit `php.ini`: `upload_max_filesize = 10M`, `post_max_size = 12M` |
| Tipe file tidak diizinkan | Cek allowed types di `Helper::uploadFile()` |

---

## 11. CHECKLIST INSTALASI LOKAL

- [ ] XAMPP/LAMPP terinstall dengan PHP 8.1+
- [ ] Apache dan MySQL running
- [ ] Ekstensi PHP lengkap (pdo_mysql, mbstring, gd, dll)
- [ ] Project ter-copy ke htdocs/wisata
- [ ] Database `tour_guide_app` dibuat
- [ ] Schema (migration.sql) diimport
- [ ] Seed data diimport
- [ ] `app/config/database.php` dikonfigurasi
- [ ] `app/config/config.php` dikonfigurasi (BASE_URL)
- [ ] `.htaccess` ada dan mod_rewrite aktif
- [ ] Folder uploads/logs writable
- [ ] Aplikasi bisa diakses di http://localhost/wisata/
- [ ] Login admin berhasil
- [ ] phpMyAdmin bisa diakses
- [ ] Xdebug terkonfigurasi (opsional)

---

> **Modul Selanjutnya:** `28_STANDAR_KODE_KONTRIBUSI.md` — Standar kode dan panduan kontribusi
