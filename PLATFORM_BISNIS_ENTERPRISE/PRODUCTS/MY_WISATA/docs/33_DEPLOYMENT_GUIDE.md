# MODUL 33 — DEPLOYMENT GUIDE

> **Aplikasi:** Tour Guide Application (MyWisata)  
> **Versi:** 1.0.0  
> **Tanggal:** 2026-07-01  
> **Environment:** Production Linux VPS

---

## 1. RINGKASAN

Dokumen ini menyediakan panduan lengkap untuk deploy aplikasi MyWisata ke production server (Linux VPS). Panduan ini mencakup persiapan server, setup environment, konfigurasi security, dan proses deployment.

---

## 2. PERSIAPAN SERVER

### 2.1. System Requirements

- **OS:** Ubuntu 20.04 LTS / 22.04 LTS atau Debian 11+
- **RAM:** Minimum 2GB (recommended 4GB+)
- **Storage:** Minimum 20GB SSD
- **PHP:** 8.1+ atau 8.2+
- **MySQL:** 8.0+
- **Web Server:** Apache 2.4+ atau Nginx 1.18+

### 2.2. Install Dependencies

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install Apache
sudo apt install apache2 -y

# Install PHP 8.2
sudo apt install php8.2 php8.2-mysql php8.2-curl php8.2-gd php8.2-mbstring php8.2-xml php8.2-zip -y

# Install MySQL
sudo apt install mysql-server -y

# Install Composer (jika diperlukan)
sudo apt install composer -y

# Install Git
sudo apt install git -y
```

### 2.3. Configure PHP

```bash
# Edit php.ini
sudo nano /etc/php/8.2/apache2/php.ini

# Recommended settings:
upload_max_filesize = 10M
post_max_size = 10M
memory_limit = 256M
max_execution_time = 300
display_errors = Off
log_errors = On
error_log = /var/log/php_errors.log
```

---

## 3. SETUP DATABASE

### 3.1. Secure MySQL Installation

```bash
sudo mysql_secure_installation
```

### 3.2. Create Database and User

```bash
sudo mysql -u root -p
```

```sql
-- Create database
CREATE DATABASE mywisata CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user
CREATE USER 'mywisata_user'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD_HERE';

-- Grant privileges
GRANT ALL PRIVILEGES ON mywisata.* TO 'mywisata_user'@'localhost';

-- Flush privileges
FLUSH PRIVILEGES;

-- Exit
EXIT;
```

### 3.3. Import Schema

```bash
# From project root
mysql -u mywisata_user -p mywisata < database/migration.sql
mysql -u mywisata_user -p mywisata < database/seed.sql
```

---

## 4. DEPLOY APPLICATION

### 4.1. Clone Repository

```bash
# Navigate to web root
cd /var/www/html

# Clone repository
sudo git clone <REPOSITORY_URL> mywisata

# Set ownership
sudo chown -R www-data:www-data /var/www/html/mywisata
sudo chmod -R 755 /var/www/html/mywisata
```

### 4.2. Configure Environment

```bash
cd /var/www/html/mywisata

# Copy .env.example to .env
cp .env.example .env

# Edit .env
nano .env
```

```env
# App Settings
APP_ENV=production
APP_DEBUG=false
APP_NAME="MyWisata Application"
BASE_URL=https://yourdomain.com/

# Database
DB_HOST=localhost
DB_PORT=3306
DB_NAME=mywisata
DB_USER=mywisata_user
DB_PASS=STRONG_PASSWORD_HERE
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci

# Security
# CSRF_TOKEN is auto-generated per session, do not set manually

# Upload
MAX_UPLOAD_SIZE=5242880

# Email (for notifications)
MAIL_FROM=admin@yourdomain.com
MAIL_FROM_NAME="MyWisata App"

# Logging
LOG_PATH=/var/www/html/mywisata/logs
```

### 4.3. Configure Database Connection

```bash
# Edit database.php
nano app/config/database.php
```

```php
return [
    'host' => 'localhost',
    'port' => 3306,
    'database' => 'mywisata',
    'username' => 'mywisata_user',
    'password' => 'STRONG_PASSWORD_HERE',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
    
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_STRINGIFY_FETCHES => false,
    ],
];
```

### 4.4. Create Required Directories

```bash
cd /var/www/html/mywisata

# Create directories
sudo mkdir -p logs database/backup public/uploads

# Set permissions
sudo chmod -R 777 logs database/backup public/uploads
sudo chown -R www-data:www-data logs database/backup public/uploads
```

---

## 5. CONFIGURE APACHE

### 5.1. Enable Required Modules

```bash
sudo a2enmod rewrite
sudo a2enmod headers
sudo a2enmod ssl
```

### 5.2. Create Virtual Host

```bash
sudo nano /etc/apache2/sites-available/mywisata.conf
```

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAdmin admin@yourdomain.com
    DocumentRoot /var/www/html/mywisata

    <Directory /var/www/html/mywisata>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/mywisata_error.log
    CustomLog ${APACHE_LOG_DIR}/mywisata_access.log combined
</VirtualHost>
```

### 5.3. Enable Site and Restart Apache

```bash
sudo a2ensite mywisata.conf
sudo a2dissite 000-default.conf
sudo systemctl restart apache2
```

---

## 6. SSL CERTIFICATE (HTTPS)

### 6.1. Install Certbot

```bash
sudo apt install certbot python3-certbot-apache -y
```

### 6.2. Obtain SSL Certificate

```bash
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com
```

### 6.3. Auto-Renewal

```bash
sudo certbot renew --dry-run
```

---

## 7. SECURITY HARDENING

### 7.1. Firewall Configuration

```bash
# Install UFW
sudo apt install ufw -y

# Allow SSH
sudo ufw allow OpenSSH

# Allow HTTP/HTTPS
sudo ufw allow 80
sudo ufw allow 443

# Enable firewall
sudo ufw enable
```

### 7.2. Secure File Permissions

```bash
# Protect sensitive files
cd /var/www/html/mywisata

# Ensure .env is not accessible
sudo chmod 600 .env

# Protect app directory
sudo chmod -R 755 app/
```

### 7.3. Disable phpMyAdmin (if installed)

```bash
# Remove or disable phpMyAdmin in production
sudo a2dissite phpmyadmin.conf
sudo systemctl restart apache2
```

### 7.4. Security Headers (Already in .htaccess)

The following security headers are already configured in `.htaccess`:
- X-XSS-Protection
- X-Content-Type-Options
- X-Frame-Options
- Referrer-Policy
- Content-Security-Policy

---

## 8. MONITORING SETUP

### 8.1. Log Rotation

```bash
sudo nano /etc/logrotate.d/mywisata
```

```
/var/www/html/mywisata/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0644 www-data www-data
    sharedscripts
}
```

### 8.2. Database Backup Script

```bash
sudo nano /usr/local/bin/backup_mywisata.sh
```

```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/www/html/mywisata/database/backup"
mysqldump -u mywisata_user -p'STRONG_PASSWORD_HERE' mywisata > $BACKUP_DIR/backup_$DATE.sql
gzip $BACKUP_DIR/backup_$DATE.sql
find $BACKUP_DIR -name "backup_*.sql.gz" -mtime +7 -delete
```

```bash
# Make executable
sudo chmod +x /usr/local/bin/backup_mywisata.sh

# Add to crontab (daily at 2 AM)
sudo crontab -e
```

```
0 2 * * * /usr/local/bin/backup_mywisata.sh
```

---

## 9. VERIFICATION

### 9.1. Check Application Access

```bash
curl -I https://yourdomain.com
```

Expected response:
```
HTTP/1.1 200 OK
Content-Type: text/html; charset=UTF-8
X-XSS-Protection: 1; mode=block
X-Content-Type-Options: nosniff
X-Frame-Options: SAMEORIGIN
Referrer-Policy: strict-origin-when-cross-origin
Content-Security-Policy: default-src 'self' https:; ...
```

### 9.2. Run Security Tests

```bash
cd /var/www/html/mywisata
php tests/SecurityTest.php
```

Expected: All 8 tests passed

### 9.3. Run Unit Tests

```bash
php tests/UnitTest.php
```

Expected: All 7 tests passed

---

## 10. POST-DEPLOYMENT CHECKLIST

- [ ] Application accessible via HTTPS
- [ ] Database connection working
- [ ] File uploads functional
- [ ] User registration/login working
- [ ] All security tests passing
- [ ] All unit tests passing
- [ ] SSL certificate valid
- [ ] Firewall enabled
- [ ] Database backup script scheduled
- [ ] Log rotation configured
- [ ] Error logs monitored
- [ ] Default admin password changed
- [ ] phpMyAdmin disabled/removed
- [ ] .env file not accessible via web
- [ ] Sensitive files protected

---

## 11. TROUBLESHOOTING

### 11.1. 500 Internal Server Error

Check error logs:
```bash
sudo tail -f /var/log/apache2/mywisata_error.log
sudo tail -f /var/www/html/mywisata/logs/error.log
```

Common causes:
- File permissions issue
- Database connection failed
- PHP syntax error
- Missing dependencies

### 11.2. Database Connection Failed

```bash
# Test connection
mysql -u mywisata_user -p -h localhost mywisata

# Check MySQL service
sudo systemctl status mysql
```

### 11.3. File Upload Not Working

```bash
# Check permissions
ls -la /var/www/html/mywisata/public/uploads

# Check PHP upload settings
php -i | grep upload
```

---

## 12. MAINTENANCE

### 12.1. Regular Tasks

- **Daily:** Monitor error logs
- **Weekly:** Check disk space
- **Monthly:** Review security updates
- **Quarterly:** Review and update dependencies

### 12.2. Update Process

```bash
# Backup current version
cd /var/www/html
sudo cp -r mywisata mywisata_backup_$(date +%Y%m%d)

# Pull latest changes
cd mywisata
sudo git pull origin main

# Update dependencies (if using Composer)
sudo composer install --no-dev --optimize-autoloader

# Run migrations (if any)
mysql -u mywisata_user -p mywisata < database/migration.sql

# Clear cache (if applicable)
sudo rm -rf app/cache/*

# Restart services
sudo systemctl restart apache2
sudo systemctl restart mysql
```

---

## 13. CONTACT & SUPPORT

For issues or questions:
- **Email:** admin@mywisata.com
- **Documentation:** `/docs` directory
- **GitHub Issues:** [Repository URL]/issues

---

## 14. APPENDIX

### 14.1. Useful Commands

```bash
# Check Apache status
sudo systemctl status apache2

# Check MySQL status
sudo systemctl status mysql

# View Apache error log
sudo tail -f /var/log/apache2/error.log

# View application error log
sudo tail -f /var/www/html/mywisata/logs/error.log

# Check disk space
df -h

# Check memory usage
free -h

# Restart Apache
sudo systemctl restart apache2

# Restart MySQL
sudo systemctl restart mysql
```

### 14.2. File Locations

- **Application Root:** `/var/www/html/mywisata`
- **Apache Config:** `/etc/apache2/sites-available/mywisata.conf`
- **PHP Config:** `/etc/php/8.2/apache2/php.ini`
- **MySQL Config:** `/etc/mysql/my.cnf`
- **Application Logs:** `/var/www/html/mywisata/logs/`
- **Database Backups:** `/var/www/html/mywisata/database/backup/`
- **Uploads:** `/var/www/html/mywisata/public/uploads/`

---

**Document Version:** 1.0  
**Last Updated:** 2026-07-01  
**Status:** Ready for Production
