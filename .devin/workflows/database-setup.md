---
description: Setup and configure the EBP Restaurant database
---

# Database Setup Workflow

This workflow helps you set up and configure the EBP Restaurant database.

## Prerequisites

- XAMPP/LAMP stack installed
- MySQL service running
- Database user with appropriate permissions

## Steps

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

Current configuration in `config/database.php`:
- Host: localhost
- Socket: /opt/lampp/var/mysql/mysql.sock
- Database: ebp_restaurant_db
- Username: ebp_app
- Password: ebp_secure_password_2026

## Troubleshooting

- If MySQL connection fails, check if XAMPP MySQL service is running
- If authentication fails, verify database user credentials
- If tables are missing, import the schema and data files
