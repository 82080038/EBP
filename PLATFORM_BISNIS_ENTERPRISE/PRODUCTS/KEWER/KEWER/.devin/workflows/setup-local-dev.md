---
description: Setup local development environment for KEWER microfinance platform
---

# Setup Local Development Environment - KEWER

## Prerequisites

- PHP 8.0+ (recommended 8.2)
- MySQL/MariaDB 5.7+ (recommended 10.4+)
- XAMPP (Windows) or LAMPP (Linux)
- Composer (optional but recommended)
- Git

## Setup Steps

### 1. Clone Repository

```bash
cd C:\xampp\htdocs
git clone https://github.com/82080038/kewer.git
cd kewer
```

### 2. Start XAMPP Services

**Windows:**
- Open XAMPP Control Panel
- Start Apache and MySQL services

**Linux:**
```bash
sudo /opt/lampp/lampp start
```

### 3. Create Databases

**Windows:**
- Open phpMyAdmin: http://localhost/phpmyadmin
- Create 3 databases:
  - `kewer` - Main application database
  - `db_alamat` - Address/location database
  - `db_orang` - People/identity database

**Linux:**
```bash
/opt/lampp/bin/mysql -u root -proot --socket=/opt/lampp/var/mysql/mysql.sock -e "
  CREATE DATABASE IF NOT EXISTS kewer;
  CREATE DATABASE IF NOT EXISTS db_alamat;
  CREATE DATABASE IF NOT EXISTS db_orang;
"
```

### 4. Import Database Schema

**Windows:**
```bash
C:\xampp\mysql\bin\mysql.exe -u root -proot kewer < database/kewer.sql
C:\xampp\mysql\bin\mysql.exe -u root -proot db_alamat < database/db_alamat.sql
C:\xampp\mysql\bin\mysql.exe -u root -proot db_orang < database/db_orang.sql
```

**Linux:**
```bash
/opt/lampp/bin/mysql -u root -proot --socket=/opt/lampp/var/mysql/mysql.sock kewer < database/kewer.sql
/opt/lampp/bin/mysql -u root -proot --socket=/opt/lampp/var/mysql/mysql.sock db_alamat < database/db_alamat.sql
/opt/lampp/bin/mysql -u root -proot --socket=/opt/lampp/var/mysql/mysql.sock db_orang < database/db_orang.sql
```

### 5. Configure Application

- Database configuration is in `config/database.php` (auto-detects Windows vs Linux)
- Environment configuration is in `config/env.php` (uses defaults for development)
- No `.env` file needed for development

### 6. Set Permissions

**Windows:**
- Ensure `logs/` folder exists and is writable
- Ensure `uploads/` folder exists and is writable

**Linux:**
```bash
sudo mkdir -p logs uploads
sudo chown -R daemon:daemon logs uploads
sudo chmod -R 755 logs uploads
```

### 7. Install Dependencies (Optional)

```bash
# PHP dependencies (PDF export, OCR)
composer install

# Node.js dependencies (if using simulation)
npm install
```

### 8. Access Application

Open browser: http://localhost/kewer

## Test Users

| Username | Password | Role |
|----------|----------|------|
| appowner | AppOwner2024! | appOwner |
| patri | Kewer2024! | bos |
| mgr_pusat | Kewer2024! | manager_pusat |
| mgr_balige | Kewer2024! | manager_cabang |
| adm_pusat | Kewer2024! | admin_pusat |
| ptr_pngr1 | Kewer2024! | petugas_pusat |
| ptr_blg1 | Kewer2024! | petugas_cabang |
| krw_pngr | Kewer2024! | karyawan |

## Quick Login (Development Only)

Add `?test_login=true&username=USERNAME&password=PASSWORD` to login URL:
```
http://localhost/kewer/login.php?test_login=true&username=patri&password=Kewer2024!
```

## Troubleshooting

### Database Connection Failed
- **Windows**: Check if MySQL service is running in XAMPP Control Panel
- **Linux**: Check if LAMPP is running: `sudo /opt/lampp/lampp status`

### Socket Error
- Ensure correct socket path (auto-detected in config/database.php)
- Windows: No socket needed
- Linux: `/opt/lampp/var/mysql/mysql.sock`

### Permission Denied
- **Windows**: Check folder permissions
- **Linux**: Run: `sudo chown -R daemon:daemon logs uploads`
