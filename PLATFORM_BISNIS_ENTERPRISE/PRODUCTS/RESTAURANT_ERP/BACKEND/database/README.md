# Database Files

This directory contains SQL files for the EBP Restaurant Backend database.

## File Structure

### Schema & Migrations
- `schema.sql` - Complete database schema structure
- `migration_phase*.sql` - Database migration files for different development phases

### Current Data
- `current_data.sql` - Latest database export from phpMyAdmin (includes both schema and current data)
  - **Size**: ~55KB
  - **Contains**: Complete schema + sample data (1 tenant, 7 users, 7 roles, 21 permissions, 3 categories, 5 products)
  - **Portable**: Yes, can be imported on any MySQL installation

## Cross-Platform Database Setup

### Quick Setup (Recommended for New Developers)

The easiest way to set up the database on any platform is to use the automated setup script:

```bash
cd /opt/lampp/htdocs/EBP/PLATFORM_BISNIS_ENTERPRISE/PRODUCTS/RESTAURANT_ERP/BACKEND
php setup_database.php
```

This script will:
- Automatically detect your MySQL configuration (XAMPP, MAMP, Docker, etc.)
- Try multiple connection methods (socket and TCP/IP)
- Try common root passwords
- Create the database if it doesn't exist
- Import `current_data.sql` (schema + sample data)
- Run seed data if needed

### Manual Setup

#### 1. Create Database
```bash
# Linux/Mac with XAMPP
mysql -u root --socket=/opt/lampp/var/mysql/mysql.sock -e "CREATE DATABASE IF NOT EXISTS ebp_restaurant_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"

# Windows/Mac with MAMP
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS ebp_restaurant_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"

# Docker
docker exec -i mysql_container mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS ebp_restaurant_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
```

#### 2. Import Current Data (Recommended)
```bash
# Linux/Mac with XAMPP
mysql -u root --socket=/opt/lampp/var/mysql/mysql.sock ebp_restaurant_db < database/current_data.sql

# Windows/Mac with MAMP
mysql -u root -p ebp_restaurant_db < database/current_data.sql

# Docker
docker exec -i mysql_container mysql -u root -p ebp_restaurant_db < database/current_data.sql
```

#### 3. Alternative: Import Schema Only
```bash
# Linux/Mac with XAMPP
mysql -u root --socket=/opt/lampp/var/mysql/mysql.sock ebp_restaurant_db < database/schema.sql

# Windows/Mac with MAMP
mysql -u root -p ebp_restaurant_db < database/schema.sql

# Docker
docker exec -i mysql_container mysql -u root -p ebp_restaurant_db < database/schema.sql
```

#### 4. Run Seed Data (if using schema only)
```bash
php seed_data.php
```

### Platform-Specific Configurations

#### Linux with XAMPP
```bash
# Update .env file
DB_HOST=localhost
DB_SOCKET=/opt/lampp/var/mysql/mysql.sock
DB_NAME=ebp_restaurant_db
DB_USER=ebp_app
DB_PASSWORD=ebp_secure_password_2026
```

#### Windows with XAMPP
```bash
# Update .env file
DB_HOST=localhost
DB_PORT=3306
DB_SOCKET=
DB_NAME=ebp_restaurant_db
DB_USER=ebp_app
DB_PASSWORD=ebp_secure_password_2026
```

#### Mac with MAMP
```bash
# Update .env file
DB_HOST=localhost
DB_PORT=8889
DB_SOCKET=/Applications/MAMP/tmp/mysql/mysql.sock
DB_NAME=ebp_restaurant_db
DB_USER=ebp_app
DB_PASSWORD=ebp_secure_password_2026
```

#### Docker
```bash
# Update .env file
DB_HOST=mysql_container
DB_PORT=3306
DB_SOCKET=
DB_NAME=ebp_restaurant_db
DB_USER=ebp_app
DB_PASSWORD=ebp_secure_password_2026
```

### Export Current Database

```bash
# Linux/Mac with XAMPP
mysqldump -u root --socket=/opt/lampp/var/mysql/mysql.sock ebp_restaurant_db > database/current_data.sql

# Windows/Mac with MAMP
mysqldump -u root -p ebp_restaurant_db > database/current_data.sql

# Docker
docker exec mysql_container mysqldump -u root -p ebp_restaurant_db > database/current_data.sql
```

## Important Notes

- **current_data.sql** is portable and can be imported on any MySQL installation
- **current_data.sql** includes both schema and sample data for immediate development
- Always backup before running migrations
- Migration files are for development history and should not be used for fresh installations
- Use `setup_database.php` for automated cross-platform setup
- The `current_data.sql` file is committed to Git for easy developer onboarding

## Database Connection

Connection details are configured in `.env` file:
- Default Host: localhost
- Default Socket: /opt/lampp/var/mysql/mysql.sock (Linux XAMPP)
- Default Port: 3306 (TCP/IP fallback)
- Database: ebp_restaurant_db
- Username: ebp_app
- Password: ebp_secure_password_2026

## Troubleshooting

### Connection Issues
1. Check MySQL server is running
2. Verify socket path or TCP/IP port
3. Try different root passwords (empty, root, password, mysql)
4. Use `setup_database.php` for automatic detection

### Import Issues
1. Ensure database exists before importing
2. Check file permissions
3. Verify MySQL user has CREATE and INSERT privileges
4. Use `current_data.sql` for complete setup (schema + data)

### Cross-Platform Issues
1. Socket paths differ between platforms
2. Default ports vary (XAMPP: 3306, MAMP: 8889)
3. Use environment variables in `.env` for platform-specific settings
4. The setup script handles most platform differences automatically
