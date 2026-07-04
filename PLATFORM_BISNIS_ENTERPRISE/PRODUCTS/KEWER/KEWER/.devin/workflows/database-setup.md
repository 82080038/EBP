---
description: Setup and manage KEWER databases
---

# Database Setup - KEWER

## Database Architecture

KEWER uses 3 separate databases:

1. **kewer** - Main application database (64 tables + 3 views)
2. **db_alamat** - National address/location database (24 tables)
3. **db_orang** - People/identity database (20 tables)

## Initial Setup

### Create Databases

**Windows:**
```bash
# Using phpMyAdmin
# Open http://localhost/phpmyadmin
# Create databases: kewer, db_alamat, db_orang
```

**Linux:**
```bash
/opt/lampp/bin/mysql -u root -proot --socket=/opt/lampp/var/mysql/mysql.sock -e "
  CREATE DATABASE IF NOT EXISTS kewer;
  CREATE DATABASE IF NOT EXISTS db_alamat;
  CREATE DATABASE IF NOT EXISTS db_orang;
"
```

### Import Schema

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

## Database Migrations

Migrations are located in `database/migrations/`.

### Running Migrations

Run migrations in order:
```bash
# Example
/opt/lampp/bin/mysql -u root -proot kewer < database/migrations/015_kewer_ref_frekuensi_angsuran.sql
/opt/lampp/bin/mysql -u root -proot kewer < database/migrations/017_kewer_foreign_keys.sql
```

### Fresh Install

For a fresh install, use the export files (already include all migrations):
```bash
/opt/lampp/bin/mysql -u root -proot kewer < database/kewer_export.sql
/opt/lampp/bin/mysql -u root -proot db_alamat < database/db_alamat_export.sql
/opt/lampp/bin/mysql -u root -proot db_orang < database/db_orang_export.sql
```

## Backup

### Backup All Databases

**Windows:**
```bash
C:\xampp\mysql\bin\mysqldump.exe -u root -proot kewer > backup_kewer_%date%.sql
C:\xampp\mysql\bin\mysqldump.exe -u root -proot db_alamat > backup_db_alamat_%date%.sql
C:\xampp\mysql\bin\mysqldump.exe -u root -proot db_orang > backup_db_orang_%date%.sql
```

**Linux:**
```bash
/opt/lampp/bin/mysqldump -u root -proot kewer > backup_kewer_$(date +%Y%m%d).sql
/opt/lampp/bin/mysqldump -u root -proot db_alamat > backup_db_alamat_$(date +%Y%m%d).sql
/opt/lampp/bin/mysqldump -u root -proot db_orang > backup_db_orang_$(date +%Y%m%d).sql
```

### Restore

```bash
/opt/lampp/bin/mysql -u root -proot kewer < backup_kewer_20260704.sql
/opt/lampp/bin/mysql -u root -proot db_alamat < backup_db_alamat_20260704.sql
/opt/lampp/bin/mysql -u root -proot db_orang < backup_db_orang_20260704.sql
```

## Database Connections

The application uses 3 separate connections:

- `$conn` / `query()` - Main kewer database
- `$conn_alamat` / `query_alamat()` - Address database
- `$conn_orang` / `query_orang()` - People database

Configuration is in `config/database.php`.

## Cross-Database Relationships

- `users.db_orang_person_id` → `db_orang.people.person_id`
- `nasabah.db_orang_user_id` → `db_orang.people.person_id`
- `cabang.db_orang_person_id` → `db_orang.people.person_id`

## Troubleshooting

### Connection Failed
- Check MySQL/MariaDB is running
- Verify socket path (Linux: `/opt/lampp/var/mysql/mysql.sock`)
- Check credentials in `config/database.php`

### Foreign Key Errors
- Ensure all 3 databases are imported
- Run migrations in correct order
- Check for orphaned records

### Performance Issues
- Add indexes to frequently queried columns
- Use query optimization
- Consider read replicas for high traffic
