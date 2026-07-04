---
description: Deploy KEWER microfinance platform to production
---

# Deploy to Production - KEWER

## Pre-Deployment Checklist

- [ ] All tests passing
- [ ] Database migrations applied
- [ ] Environment variables configured
- [ ] Backup current database
- [ ] Review security settings
- [ ] Disable development mode

## Deployment Steps

### 1. Backup Current Database

```bash
# Windows
C:\xampp\mysql\bin\mysqldump.exe -u root -proot kewer > backup_kewer_$(date +%Y%m%d).sql
C:\xampp\mysql\bin\mysqldump.exe -u root -proot db_alamat > backup_db_alamat_$(date +%Y%m%d).sql
C:\xampp\mysql\bin\mysqldump.exe -u root -proot db_orang > backup_db_orang_$(date +%Y%m%d).sql

# Linux
/opt/lampp/bin/mysqldump -u root -proot kewer > backup_kewer_$(date +%Y%m%d).sql
/opt/lampp/bin/mysqldump -u root -proot db_alamat > backup_db_alamat_$(date +%Y%m%d).sql
/opt/lampp/bin/mysqldump -u root -proot db_orang > backup_db_orang_$(date +%Y%m%d).sql
```

### 2. Update Code

```bash
git pull origin main
```

### 3. Run Database Migrations (if any)

```bash
# Check for new migrations in database/migrations/
# Run in order
/opt/lampp/bin/mysql -u root -proot kewer < database/migrations/XXX_migration_name.sql
```

### 4. Configure Production Environment

Create `.env` file:
```env
APP_ENV=production
APP_URL=https://your-domain.com

# Database
DB_HOST=localhost
DB_NAME=kewer
DB_USER=production_user
DB_PASS=secure_password

# Security
DISABLE_TEST_LOGIN=true
SESSION_SECURE=true

# WhatsApp (if enabled)
WA_ENABLED=true
WA_PROVIDER=fonnte
WA_TOKEN=your_token

# Email
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=your_email@gmail.com
SMTP_PASS=your_app_password
```

### 5. Set File Permissions

**Linux:**
```bash
sudo chown -R www-data:www-data logs uploads
sudo chmod -R 755 logs uploads
```

### 6. Configure Cron Job

**Linux (crontab):**
```bash
# Edit crontab
crontab -e

# Add daily task at 00:00
0 0 * * * /usr/bin/php /var/www/kewer/cron_daily_tasks.php
```

**Windows (Task Scheduler):**
1. Open Task Scheduler
2. Create Basic Task: "Kewer Daily Tasks"
3. Trigger: Daily at 00:00
4. Action: Start a program
   - Program: `C:\xampp\php\php.exe`
   - Arguments: `C:\xampp\htdocs\kewer\cron_daily_tasks.php`
   - Start in: `C:\xampp\htdocs\kewer`

### 7. Restart Web Server

**Linux:**
```bash
sudo systemctl restart apache2
# or
sudo /opt/lampp/lampp restart
```

**Windows:**
- Restart Apache from XAMPP Control Panel

### 8. Verify Deployment

- Access production URL
- Test login with production credentials
- Verify all features working
- Check logs for errors

## Post-Deployment

- Monitor application logs
- Verify cron job execution
- Check database performance
- Monitor user activity

## Rollback Procedure

If deployment fails:

1. Restore database from backup
2. Revert code changes: `git checkout previous-commit`
3. Restart web server
4. Verify application is working
