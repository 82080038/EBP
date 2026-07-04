# Database Files

This directory contains SQL files for the EBP Restaurant Backend database.

## File Structure

### Schema & Migrations
- `schema.sql` - Complete database schema structure
- `migration_phase*.sql` - Database migration files for different development phases

### Current Data
- `current_data.sql` - Latest database export from phpMyAdmin (includes both schema and current data)

## Usage

### Restore Database from Current Data
```bash
mysql -u root --socket=/opt/lampp/var/mysql/mysql.sock ebp_restaurant_db < database/current_data.sql
```

### Restore Schema Only
```bash
mysql -u root --socket=/opt/lampp/var/mysql/mysql.sock ebp_restaurant_db < database/schema.sql
```

### Export Current Database
```bash
mysqldump -u root --socket=/opt/lampp/var/mysql/mysql.sock ebp_restaurant_db > database/current_data.sql
```

## Important Notes

- **current_data.sql** is synced with the production/development database
- Always backup before running migrations
- Migration files are for development history and should not be used for fresh installations
- Use `seed_data.php` in the root directory for initial data population

## Database Connection

Connection details are in `../config/database.php`:
- Host: localhost
- Socket: /opt/lampp/var/mysql/mysql.sock
- Database: ebp_restaurant_db
- Username: root
- Password: (empty)
