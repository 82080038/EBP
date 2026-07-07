---
description: Setup and configure the RESTAURANT_ERP database
---

# Database Setup Workflow - RESTAURANT_ERP

This workflow helps you set up and configure the RESTAURANT_ERP database.

## Prerequisites

- XAMPP installed
- MySQL service running
- Database user with appropriate permissions

## Steps

### Windows (XAMPP)

1. **Start XAMPP services**
   - Open XAMPP Control Panel
   - Start Apache and MySQL services

2. **Run automated setup script (recommended)**
   ```bash
   cd C:\xampp\htdocs\EBP\PLATFORM_BISNIS_ENTERPRISE\PRODUCTS\RESTAURANT_ERP\BACKEND
   C:\xampp\php\php.exe setup_database.php
   ```

3. **Or manual setup using phpMyAdmin**
   - Open http://localhost/phpmyadmin
   - Create database: ebp_restaurant_db
   - Import: database/current_data.sql

4. **Verify tables and data**
   ```bash
   C:\xampp\mysql\bin\mysql.exe -u root -proot ebp_restaurant_db -e "SHOW TABLES;"
   ```

### Linux (XAMPP/LAMP)

1. **Start XAMPP services**
   ```bash
   echo "8208" | sudo -S /opt/lampp/lampp start
   ```

2. **Verify database connection**
   ```bash
   mysql -u ebp_app -pebp_secure_password_2026 --socket=/opt/lampp/var/mysql/mysql.sock -e "SHOW DATABASES;"
   ```

3. **Import database schema (if needed)**
   ```bash
   mysql -u ebp_app -pebp_secure_password_2026 --socket=/opt/lampp/var/mysql/mysql.sock ebp_restaurant_db < database/schema.sql
   ```

4. **Import current data (if needed)**
   ```bash
   mysql -u ebp_app -pebp_secure_password_2026 --socket=/opt/lampp/var/mysql/mysql.sock ebp_restaurant_db < database/current_data.sql
   ```

5. **Run migration scripts (if needed)**
   ```bash
   # Run migrations in order
   mysql -u ebp_app -pebp_secure_password_2026 --socket=/opt/lampp/var/mysql/mysql.sock ebp_restaurant_db < database/migration_phase1.sql
   mysql -u ebp_app -pebp_secure_password_2026 --socket=/opt/lampp/var/mysql/mysql.sock ebp_restaurant_db < database/migration_phase3_inventory.sql
   # Continue with other migration phases as needed
   ```

6. **Verify tables and data**
   ```bash
   mysql -u ebp_app -pebp_secure_password_2026 --socket=/opt/lampp/var/mysql/mysql.sock ebp_restaurant_db -e "SHOW TABLES;"
   mysql -u ebp_app -pebp_secure_password_2026 --socket=/opt/lampp/var/mysql/mysql.sock ebp_restaurant_db -e "SELECT COUNT(*) as count FROM tenants;"
   ```

## Database Configuration

Current configuration in `.env` (Windows XAMPP):
- Host: localhost
- Port: 3306
- Socket: (empty - using TCP/IP)
- Database: ebp_restaurant_db
- Username: root
- Password: root

Note: For Windows XAMPP, use root user with password 'root'. The .env file is used for configuration.

## Troubleshooting

- If MySQL connection fails, check if XAMPP MySQL service is running
- If authentication fails, verify database user credentials
- If tables are missing, import the schema and data files
- On Windows, use phpMyAdmin (http://localhost/phpmyadmin) for GUI database management
