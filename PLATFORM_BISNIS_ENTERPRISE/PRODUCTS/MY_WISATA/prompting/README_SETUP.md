# PANDUAN SETUP KONFIGURASI
# Tour Guide Application - Multi-Environment Configuration

> **Versi:** 1.0  
> **Tanggal:** 2026-06-30  
> **Tujuan:** Panduan setup konfigurasi untuk development di multiple komputer (Windows & Linux)

---

## 1. OVERVIEW

File `prompting/config.json` adalah konfigurasi terpusat yang memungkinkan:
- Development di multiple komputer (Windows & Linux) secara paralel
- Konfigurasi environment-specific (local, staging, production)
- Storage aman untuk credentials dan API keys
- Auto-detection paths sesuai OS
- Integrasi dengan AI development assistant (Cascade/Devin)

---

## 2. STRUKTUR FILE

```json
{
  "project_info": {
    "name": "Tour Guide Application",
    "tech_stack": { ... }
  },
  "starting_point": { ... },
  "environments": {
    "local": {
      "database": { ... },
      "url": "...",
      "os_specific": {
        "linux": { ... },
        "windows": { ... }
      }
    },
    "staging": { ... },
    "production": { ... }
  },
  "api_keys": { ... },
  "permissions": { ... },
  "escalation": { ... },
  "testing": { ... },
  "deployment": { ... },
  "scope": { ... },
  "preferences": { ... }
}
```

---

## 3. LANGKAH SETUP

### 3.1 Buka File Config

```bash
# Linux
nano /opt/lampp/htdocs/mywisata/prompting/config.json

# Windows
notepad C:\xampp\htdocs\mywisata\prompting/config.json
```

### 3.2 Konfigurasi Environment Local

#### Untuk LINUX:

```json
"environments": {
  "local": {
    "database": {
      "host": "localhost",
      "port": "3306",
      "username": "root",
      "password": "",
      "database_name": "mywisata",
      "charset": "utf8mb4",
      "collation": "utf8mb4_unicode_ci"
    },
    "url": "http://localhost/mywisata",
    "os_specific": {
      "linux": {
        "project_root": "/opt/lampp/htdocs/mywisata",
        "php_path": "/opt/lampp/bin/php",
        "mysql_path": "/opt/lampp/bin/mysql",
        "composer_path": "/usr/local/bin/composer",
        "sudo_password": "YOUR_SUDO_PASSWORD",
        "phpmyadmin_password": "YOUR_PHPMYADMIN_PASSWORD",
        "phpmyadmin_url": "http://localhost/phpmyadmin"
      }
    }
  }
}
```

**Sesuaikan:**
- `project_root` - lokasi project di komputer Anda
- `php_path` - path ke executable PHP
- `mysql_path` - path ke executable MySQL
- `composer_path` - path ke composer (jika ada)
- `sudo_password` - password SUDO (jika diperlukan)
- `phpmyadmin_password` - password phpMyAdmin

#### Untuk WINDOWS:

```json
"environments": {
  "local": {
    "database": {
      "host": "localhost",
      "port": "3306",
      "username": "root",
      "password": "",
      "database_name": "mywisata",
      "charset": "utf8mb4",
      "collation": "utf8mb4_unicode_ci"
    },
    "url": "http://localhost/mywisata",
    "os_specific": {
      "windows": {
        "project_root": "C:\\xampp\\htdocs\\mywisata",
        "php_path": "C:\\xampp\\php\\php.exe",
        "mysql_path": "C:\\xampp\\mysql\\bin\\mysql.exe",
        "composer_path": "C:\\ProgramData\\ComposerSetup\\bin\\composer.bat",
        "sudo_password": null,
        "phpmyadmin_password": "YOUR_PHPMYADMIN_PASSWORD",
        "phpmyadmin_url": "http://localhost/phpmyadmin"
      }
    }
  }
}
```

**Sesuaikan:**
- `project_root` - lokasi project di komputer Anda (gunakan double backslash `\\`)
- `php_path` - path ke executable PHP
- `mysql_path` - path ke executable MySQL
- `composer_path` - path ke composer (jika ada)
- `sudo_password` - set ke `null` (tidak dipakai di Windows)
- `phpmyadmin_password` - password phpMyAdmin

### 3.3 Konfigurasi API Keys (Opsional)

```json
"api_keys": {
  "openstreetmap": "free_no_api_key_required",
  "payment_gateway": null,
  "email_service": null,
  "sms_service": null,
  "other": {}
}
```

Isi jika ada:
- `payment_gateway` - API key untuk payment gateway (Midtrans, Stripe, dll)
- `email_service` - API key untuk email service (SendGrid, Mailgun, dll)
- `sms_service` - API key untuk SMS service

### 3.4 Konfigurasi Permissions

```json
"permissions": {
  "auto_write_files": true,
  "auto_execute_commands": false,
  "auto_database_changes": false,
  "approval_required_for": [
    "database_changes",
    "deployment",
    "production_changes"
  ]
}
```

**Penjelasan:**
- `auto_write_files: true` - AI boleh membuat/mengubah file tanpa approval
- `auto_execute_commands: false` - AI tidak boleh menjalankan command tanpa approval
- `auto_database_changes: false` - AI tidak boleh mengubah database tanpa approval
- `approval_required_for` - tindakan yang memerlukan approval manual

### 3.5 Konfigurasi Starting Point

```json
"starting_point": {
  "start_module": "05_DESAIN_DATABASE_MYSQL_ERD",
  "modules_completed": [],
  "modules_in_progress": [],
  "resume_from_state": false
}
```

**Penjelasan:**
- `start_module` - module pertama yang akan dikembangkan
- `modules_completed` - list module yang sudah selesai
- `modules_in_progress` - list module yang sedang berjalan
- `resume_from_state` - apakah resume dari state terakhir

---

## 4. VALIDASI CONFIG

Setelah mengedit, validasi bahwa file JSON valid:

```bash
# Linux
python3 -m json.tool /opt/lampp/htdocs/mywisata/prompting/config.json

# Windows (jika ada Python)
python -m json.tool C:\xampp\htdocs\mywisata\prompting/config.json

# Atau gunakan online JSON validator:
# https://jsonlint.com/
```

Jika ada error, perbaiki:
- Pastikan semua string di-quote dengan double quote `"`
- Pastikan tidak ada trailing comma
- Pastikan kurung kurawal `{}` dan kurung siku `[]` seimbang

---

## 5. PENGgunaan CONFIG

### 5.1 Untuk AI Development Assistant

AI (Cascade/Devin) akan:
1. Membaca `prompting/config.json` secara otomatis
2. Mendeteksi OS yang sedang digunakan
3. Menggunakan paths yang sesuai dengan OS
4. Menggunakan credentials yang sudah dikonfigurasi
5. Mengikuti permissions yang sudah di-set

### 5.2 Untuk Programmer Manual

Programmer dapat:
1. Membaca config untuk mengetahui paths yang benar
2. Menggunakan credentials yang sudah tersimpan
3. Menyesuaikan config jika ada perubahan environment
4. Berkontribusi dengan config yang sudah terstandar

---

## 6. CHECKLIST SETUP

- [ ] File `prompting/config.json` sudah dibuka
- [ ] Environment `local` sudah dikonfigurasi
- [ ] OS-specific config (linux/windows) sudah sesuai
- [ ] Database credentials sudah benar
- [ ] Paths (php, mysql, composer) sudah benar
- [ ] Passwords (sudo, phpmyadmin) sudah diisi
- [ ] API keys sudah diisi (jika ada)
- [ ] Permissions sudah disesuaikan
- [ ] Starting point sudah ditentukan
- [ ] File JSON sudah divalidasi (tidak ada error)
- [ ] File sudah disimpan

---

## 7. TROUBLESHOOTING

### 7.1 JSON Parse Error

**Masalah:** Error saat membaca file JSON

**Solusi:**
- Cek syntax JSON dengan validator
- Pastikan tidak ada trailing comma
- Pastikan semua string di-quote dengan double quote
- Pastikan kurung kurawal dan siku seimbang

### 7.2 Path Salah

**Masalah:** AI menggunakan path yang salah

**Solusi:**
- Pastikan `project_root` sesuai dengan lokasi project
- Pastikan `php_path` dan `mysql_path` benar
- Untuk Windows, gunakan double backslash `\\`
- Untuk Linux, gunakan forward slash `/`

### 7.3 Database Connection Failed

**Masalah:** Tidak bisa connect ke database

**Solusi:**
- Cek `database.username` dan `database.password`
- Pastikan MySQL sudah running
- Pastikan database sudah dibuat
- Cek port (default 3306)

### 7.4 Permission Denied

**Masalah:** AI tidak bisa menulis file

**Solusi:**
- Set `auto_write_files: true` di permissions
- Pastikan folder project writable
- Untuk Linux: `chmod -R 777 /opt/lampp/htdocs/mywisata`

---

## 8. BEST PRACTICES

1. **Jangan commit config.json ke repository** jika berisi sensitive data
2. **Gunakan .gitignore** untuk config.json jika berisi passwords
3. **Update config** saat ada perubahan environment
4. **Validasi JSON** setiap kali mengedit
5. **Dokumentasikan perubahan** di file ini atau di commit message
6. **Share config** dengan tim jika menggunakan environment yang sama

---

## 9. REFERENSI

- [README.md](../README.md) - Panduan utama project
- [prompting/README.md](README.md) - Panduan sistem prompting
- [docs/27_PANDUAN_INSTALASI_LOKAL.md](../docs/27_PANDUAN_INSTALASI_LOKAL.md) - Panduan instalasi lokal

---

> **Catatan:** File config.json adalah single source of truth untuk konfigurasi environment. Selalu update file ini jika ada perubahan environment atau credentials.
