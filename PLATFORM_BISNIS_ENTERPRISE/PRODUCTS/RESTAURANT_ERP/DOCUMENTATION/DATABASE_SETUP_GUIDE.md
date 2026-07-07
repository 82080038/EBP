# Database Setup Guide for New Developers

## Overview

This guide explains how to set up the EBP Restaurant database on a new development machine. The database is designed to be portable and can be easily set up on any platform (Linux, Windows, Mac) with any MySQL installation (XAMPP, MAMP, Docker, native MySQL).

## Quick Start (Recommended)

The fastest way to get started is to use the automated setup script:

```bash
cd /opt/lampp/htdocs/EBP/PLATFORM_BISNIS_ENTERPRISE/PRODUCTS/RESTAURANT_ERP/BACKEND
php setup_database.php
```

This script will:
- ✅ Automatically detect your MySQL configuration
- ✅ Try multiple connection methods (socket and TCP/IP)
- ✅ Try common root passwords
- ✅ Create the database if it doesn't exist
- ✅ Import complete database with sample data
- ✅ Run seed data if needed

## What's Included in the Database

The `current_data.sql` file contains:
- **Complete schema**: All 35 tables
- **Sample data**: 
  - 1 tenant (DEFAULT)
  - 1 company (Default Restaurant)
  - 1 branch (MAIN)
  - 7 users (admin, manager, waiter, kitchen, cashier, inventory, host)
  - 7 roles (Administrator, Restaurant Manager, Waiter, Kitchen Staff, Cashier, Inventory Manager, Host/Hostess)
  - 21 permissions
  - 3 menu categories
  - 5 menu products
  - Sample inventory items
  - Sample tables

**Default Login Credentials**:
- Username: `admin`
- Password: `admin123`

## Platform-Specific Setup

### Linux with XAMPP

1. **Start XAMPP MySQL**:
```bash
sudo /opt/lampp/lampp start
```

2. **Run setup script**:
```bash
cd /opt/lampp/htdocs/EBP/PLATFORM_BISNIS_ENTERPRISE/PRODUCTS/RESTAURANT_ERP/BACKEND
php setup_database.php
```

3. **Update .env** (if needed):
```bash
DB_HOST=localhost
DB_SOCKET=/opt/lampp/var/mysql/mysql.sock
DB_NAME=ebp_restaurant_db
DB_USER=ebp_app
DB_PASSWORD=ebp_secure_password_2026
```

### Windows with XAMPP

1. **Start XAMPP Control Panel**
2. **Start MySQL service**
3. **Open Command Prompt** as Administrator
4. **Run setup script**:
```cmd
cd C:\xampp\htdocs\EBP\PLATFORM_BISNIS_ENTERPRISE\PRODUCTS\RESTAURANT_ERP\BACKEND
php setup_database.php
```

5. **Update .env**:
```bash
DB_HOST=localhost
DB_PORT=3306
DB_SOCKET=
DB_NAME=ebp_restaurant_db
DB_USER=ebp_app
DB_PASSWORD=ebp_secure_password_2026
```

### Mac with MAMP

1. **Start MAMP**
2. **Start MySQL server**
3. **Open Terminal**
4. **Run setup script**:
```bash
cd /Applications/MAMP/htdocs/EBP/PLATFORM_BISNIS_ENTERPRISE/PRODUCTS/RESTAURANT_ERP/BACKEND
php setup_database.php
```

5. **Update .env**:
```bash
DB_HOST=localhost
DB_PORT=8889
DB_SOCKET=/Applications/MAMP/tmp/mysql/mysql.sock
DB_NAME=ebp_restaurant_db
DB_USER=ebp_app
DB_PASSWORD=ebp_secure_password_2026
```

### Docker

1. **Start MySQL container**:
```bash
docker run --name mysql_container -e MYSQL_ROOT_PASSWORD=root -d mysql:8.0
```

2. **Run setup script**:
```bash
cd /path/to/EBP/PLATFORM_BISNIS_ENTERPRISE/PRODUCTS/RESTAURANT_ERP/BACKEND
DB_HOST=mysql_container DB_PORT=3306 DB_ROOT_PASSWORD=root php setup_database.php
```

3. **Update .env**:
```bash
DB_HOST=mysql_container
DB_PORT=3306
DB_SOCKET=
DB_NAME=ebp_restaurant_db
DB_USER=ebp_app
DB_PASSWORD=ebp_secure_password_2026
```

## Manual Setup (If Script Fails)

### Step 1: Create Database

```bash
# Linux/Mac with XAMPP
mysql -u root --socket=/opt/lampp/var/mysql/mysql.sock -e "CREATE DATABASE IF NOT EXISTS ebp_restaurant_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"

# Windows/Mac with MAMP
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS ebp_restaurant_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"

# Docker
docker exec -i mysql_container mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS ebp_restaurant_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
```

### Step 2: Import Database

```bash
# Linux/Mac with XAMPP
mysql -u root --socket=/opt/lampp/var/mysql/mysql.sock ebp_restaurant_db < database/current_data.sql

# Windows/Mac with MAMP
mysql -u root -p ebp_restaurant_db < database/current_data.sql

# Docker
docker exec -i mysql_container mysql -u root -p ebp_restaurant_db < database/current_data.sql
```

### Step 3: Create Application User

```bash
# Linux/Mac with XAMPP
mysql -u root --socket=/opt/lampp/var/mysql/mysql.sock -e "CREATE USER IF NOT EXISTS 'ebp_app'@'localhost' IDENTIFIED BY 'ebp_secure_password_2026'; GRANT ALL PRIVILEGES ON ebp_restaurant_db.* TO 'ebp_app'@'localhost'; FLUSH PRIVILEGES;"

# Windows/Mac with MAMP
mysql -u root -p -e "CREATE USER IF NOT EXISTS 'ebp_app'@'localhost' IDENTIFIED BY 'ebp_secure_password_2026'; GRANT ALL PRIVILEGES ON ebp_restaurant_db.* TO 'ebp_app'@'localhost'; FLUSH PRIVILEGES;"

# Docker
docker exec -i mysql_container mysql -u root -p -e "CREATE USER IF NOT EXISTS 'ebp_app'@'%' IDENTIFIED BY 'ebp_secure_password_2026'; GRANT ALL PRIVILEGES ON ebp_restaurant_db.* TO 'ebp_app'@'%'; FLUSH PRIVILEGES;"
```

### Step 4: Update .env File

Copy `.env.example` to `.env` and update with your settings:
```bash
cp .env.example .env
nano .env
```

## Verification

### Test Database Connection

```bash
cd /opt/lampp/htdocs/EBP/PLATFORM_BISNIS_ENTERPRISE/PRODUCTS/RESTAURANT_ERP/BACKEND
php -r "require 'bootstrap.php'; \$db = new Database(); \$pdo = \$db->connect(); echo 'Database connection successful!\n';"
```

### Test API

```bash
# Start server
php -S localhost:8000 -t public

# Test login
curl -X POST http://localhost:8000/api/v1/auth/login -H "Content-Type: application/json" -d '{"username":"admin","password":"admin123"}'
```

## Troubleshooting

### "Could not connect to MySQL"

**Solutions**:
1. Check MySQL server is running
2. Verify socket path or TCP/IP port
3. Try different root passwords (empty, root, password, mysql, 123456)
4. Use environment variables: `DB_ROOT_PASSWORD=your_password php setup_database.php`

### "Access denied for user"

**Solutions**:
1. Verify root password is correct
2. Create application user manually (see Step 3 above)
3. Check user privileges

### "Database already exists"

**Solutions**:
1. This is normal if you've run setup before
2. The script will skip creation and import data
3. To start fresh: `mysql -u root -p -e "DROP DATABASE ebp_restaurant_db;"`

### Socket connection fails

**Solutions**:
1. The script will automatically try TCP/IP if socket fails
2. Set `DB_SOCKET=` in .env to force TCP/IP
3. Check MySQL configuration for correct socket path

## Common Issues by Platform

### Linux XAMPP
- **Issue**: Socket path varies
- **Solution**: Script auto-detects, or set `DB_SOCKET=/opt/lampp/var/mysql/mysql.sock`

### Windows XAMPP
- **Issue**: No socket support
- **Solution**: Script uses TCP/IP automatically, set `DB_PORT=3306`

### Mac MAMP
- **Issue**: Different port (8889)
- **Solution**: Set `DB_PORT=8889` in .env

### Docker
- **Issue**: Container networking
- **Solution**: Use container name as host, set `DB_HOST=mysql_container`

## Database Maintenance

### Export Current Database

```bash
# Linux/Mac with XAMPP
mysqldump -u root --socket=/opt/lampp/var/mysql/mysql.sock ebp_restaurant_db > database/current_data.sql

# Windows/Mac with MAMP
mysqldump -u root -p ebp_restaurant_db > database/current_data.sql

# Docker
docker exec mysql_container mysqldump -u root -p ebp_restaurant_db > database/current_data.sql
```

### Reset Database

```bash
# Drop and recreate
mysql -u root -p -e "DROP DATABASE IF EXISTS ebp_restaurant_db; CREATE DATABASE ebp_restaurant_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Re-import
mysql -u root -p ebp_restaurant_db < database/current_data.sql
```

## Next Steps

After database setup:

1. **Update .env** with your database credentials
2. **Test connection** using verification steps above
3. **Start the server**: `php -S localhost:8000 -t public`
4. **Login** with admin/admin123
5. **Run tests**: `npx playwright test`

## Support

If you encounter issues:
1. Check the troubleshooting section above
2. Review `database/README.md` for detailed information
3. Check `CONFIGURATION_GUIDE.md` for configuration help
4. Ensure PHP and MySQL are properly installed
