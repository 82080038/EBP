---
description: How to set up the local development environment for Tour Guide Application
---

# Setup Local Development Environment

## Prerequisites
- XAMPP/LAMPP with PHP 8.1+ and MySQL 8.0+
- Git
- VS Code (recommended) with PHP Intelephense extension

## Steps

1. Clone the repository:
   ```bash
   git clone https://github.com/82080038/mywisata.git /opt/lampp/htdocs/mywisata
   ```

2. Start Apache and MySQL via XAMPP/LAMPP control panel

3. Create database:
   ```bash
   /opt/lampp/bin/mysql -u root -e "CREATE DATABASE mywisata CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   ```

4. Import schema and seed data:
   ```bash
   /opt/lampp/bin/mysql -u root mywisata < /opt/lampp/htdocs/mywisata/database/migration.sql
   /opt/lampp/bin/mysql -u root mywisata < /opt/lampp/htdocs/mywisata/database/seed.sql
   ```

5. Configure database connection in `app/config/database.php` and app settings in `app/config/config.php`

6. Set folder permissions (Linux):
   ```bash
   chmod -R 777 public/uploads logs database/backup
   ```

7. Open browser: `http://localhost/mywisata/`

8. Login with default admin: `admin@mywisata.com` / `admin123`

## Full guide
See `docs/27_PANDUAN_INSTALASI_LOKAL.md` for detailed instructions.
