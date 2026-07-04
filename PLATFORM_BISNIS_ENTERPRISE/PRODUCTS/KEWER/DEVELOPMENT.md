# Development Setup Guide - Kewer Application

> **Terakhir Diperbarui**: 8 Mei 2026
> **Versi Aplikasi**: v2.4.0

## Prerequisites

### Software Requirements
- **PHP**: 8.0+ (recommended 8.2)
- **MySQL/MariaDB**: 5.7+ (recommended 10.4+)
- **Web Server**: Apache (XAMPP/LAMPP) or Nginx
- **Composer**: For dependency management (optional but recommended)
- **Git**: For version control

### Browser Requirements
- Modern browser with JavaScript enabled (Chrome, Firefox, Edge, Safari)
- For testing: Chrome/Edge with DevTools

## Environment Setup

### Windows (XAMPP)

1. **Install XAMPP**
   - Download XAMPP from https://www.apachefriends.org/
   - Install to default location: `C:\xampp\`
   - Start Apache and MySQL services from XAMPP Control Panel

2. **Clone Repository**
   ```bash
   cd C:\xampp\htdocs
   git clone https://github.com/82080038/kewer.git
   cd kewer
   ```

3. **Configure Database**
   - Open phpMyAdmin: http://localhost/phpmyadmin
   - Create 3 databases:
     - `kewer` - Main application database
     - `db_alamat` - Address/location database
     - `db_orang` - People/identity database
   - Import SQL files from `database/` folder:
     - `database/kewer.sql` → to `kewer` database
     - `database/db_alamat.sql` → to `db_alamat` database
     - `database/db_orang.sql` → to `db_orang` database

4. **Configure Application**
   - Edit `config/database.php` if needed (defaults work for XAMPP Windows)
   - Edit `config/env.php` if needed (defaults work for development)
   - No `.env` file needed for development (uses defaults)

5. **Set Permissions**
   - Ensure `logs/` folder exists and is writable
   - Ensure `uploads/` folder exists and is writable

6. **Access Application**
   - Open browser: http://localhost/kewer
   - Test login with test users (see Test Users section below)

### Linux (XAMPP/LAMPP)

1. **Install XAMPP/LAMPP**
   ```bash
   # Download and install LAMPP
   wget https://www.apachefriends.org/xampp-files/8.2.0/xampp-linux-x64-8.2.0-0-installer.run
   sudo chmod +x xampp-linux-x64-8.2.0-0-installer.run
   sudo ./xampp-linux-x64-8.2.0-0-installer.run
   ```

2. **Start Services**
   ```bash
   sudo /opt/lampp/lampp start
   ```

3. **Clone Repository**
   ```bash
   cd /opt/lampp/htdocs
   git clone https://github.com/82080038/kewer.git
   cd kewer
   ```

4. **Configure Database**
   - Open phpMyAdmin: http://localhost/phpmyadmin
   - Create 3 databases: `kewer`, `db_alamat`, `db_orang`
   - Import SQL files from `database/` folder
   - SUDO password for MySQL: `8208`

5. **Configure Application**
   - Database configuration in `config/database.php` automatically handles Linux socket path
   - Environment configuration in `config/env.php` uses defaults

6. **Set Permissions**
   ```bash
   sudo mkdir -p logs uploads
   sudo chown -R daemon:daemon logs uploads
   sudo chmod -R 755 logs uploads
   ```

7. **Access Application**
   - Open browser: http://localhost/kewer
   - Test login with test users

## Configuration Files

### config/database.php
- **Multi-database setup**: Handles 3 databases (kewer, db_alamat, db_orang)
- **OS Detection**: Automatically detects Windows vs Linux for MySQL socket
- **Functions**:
  - `query()` - Main kewer database
  - `query_alamat()` - Address database
  - `query_orang()` - People database

### config/env.php
- **Environment Configuration**: Loads from `.env` file if available, otherwise uses defaults
- **Default Values**:
  - APP_ENV: development
  - APP_URL: http://localhost/kewer
  - DB_HOST: localhost
  - DB_NAME: kewer
  - DB_USER: root
  - DB_PASS: root
- **Optional .env file** (create for custom configuration):
  ```env
  APP_ENV=development
  APP_URL=http://localhost/kewer
  DB_HOST=localhost
  DB_NAME=kewer
  DB_USER=root
  DB_PASS=root
  WA_TOKEN=your_wa_token
  WA_PROVIDER=fonnte
  ```

## Database Migration

### Fresh Install
Use the SQL export files in `database/` folder:
```bash
# Windows
C:\xampp\mysql\bin\mysql.exe -u root -proot kewer < database/kewer.sql
C:\xampp\mysql\bin\mysql.exe -u root -proot db_alamat < database/db_alamat.sql
C:\xampp\mysql\bin\mysql.exe -u root -proot db_orang < database/db_orang.sql

# Linux
/opt/lampp/bin/mysql -u root -proot kewer < database/kewer.sql
/opt/lampp/bin/mysql -u root -proot db_alamat < database/db_alamat.sql
/opt/lampp/bin/mysql -u root -proot db_orang < database/db_orang.sql
```

### Running Migrations (if needed)
Migration scripts are in `database/migrations/`:
- Run migrations in order: 015, 017, 020, 021, 024, 025, 026, 027
- For fresh install, use the SQL export files (already include all migrations)

## Test Users

### Default Credentials
| Username | Password | Role | Purpose |
|----------|----------|------|---------|
| appowner | AppOwner2024! | appOwner | Platform owner (no data access) |
| patri | Kewer2024! | bos | Business owner (full access) |
| mgr_pusat | Kewer2024! | manager_pusat | Central manager |
| mgr_balige | Kewer2024! | manager_cabang | Branch manager |
| adm_pusat | Kewer2024! | admin_pusat | Central admin |
| adm_balige | Kewer2024! | admin_cabang | Branch admin |
| ptr_pngr1 | Kewer2024! | petugas_pusat | Central field officer |
| ptr_blg1 | Kewer2024! | petugas_cabang | Branch field officer |
| krw_pngr | Kewer2024! | karyawan | Staff (delegated permissions) |

### Quick Login (Development Only)
Add `?test_login=true&username=USERNAME&password=PASSWORD` to login URL:
```
http://localhost/kewer/login.php?test_login=true&username=patri&password=Kewer2024!
```

## Development Workflow

### Code Patterns
1. **Use prepared statements** for all SQL queries
2. **Check arrays before access**: `is_array($result) && isset($result[0])`
3. **Use helper functions**: `hasPermission()`, `isFeatureEnabled()`, `getFrequencyCode()`
4. **Follow page layout**: Use `sidebar.php` for all pages except compact versions

### Key Rules (v2.4.0)
- **Frekuensi Angsuran**: Use `frekuensi_id` (INT), NOT `frekuensi` enum (dropped in v2.4.0)
- **Multi-Database**: Use correct query function per database
- **Feature Flags**: Check `isFeatureEnabled()` before accessing new features
- **RBAC**: Use `hasPermission()` for access control

### Testing
1. Test login with all roles
2. Test CRUD operations
3. Test cross-database links (address dropdown, people data)
4. Test feature flags (if applicable)

## Common Issues

### Database Connection Failed
- **Windows**: Check if MySQL service is running in XAMPP Control Panel
- **Linux**: Check if LAMPP is running: `sudo /opt/lampp/lampp status`
- **Socket Error**: Ensure correct socket path (auto-detected in config/database.php)

### Session Issues
- Clear browser cookies
- Check session.save_path in php.ini
- Ensure `logs/` folder is writable

### Permission Denied
- **Windows**: Check folder permissions (should be writable by IIS/Apache user)
- **Linux**: Run: `sudo chown -R daemon:daemon logs uploads`

## Documentation

- **Development Rules**: `.windsurf/rules.md`
- **Workflows**: `.windsurf/workflows/`
  - setup.md - Initial setup
  - database.md - Database operations
  - bugfix.md - Bug fixing workflow
  - testing.md - Testing procedures
  - deployment.md - Deployment workflow
- **API Documentation**: README.md - API endpoints section
- **Role Documentation**: roles/README.md

## Support

For issues or questions:
1. Check `.windsurf/workflows/` for specific workflows
2. Check `.windsurf/rules.md` for development rules
3. Check README.md for general information
