# VPS Setup Guide - MyWisata Application

> **Versi:** 1.0 · **Tanggal:** 2026-07-01

---

## 1. Prerequisites

- VPS dengan minimal 2GB RAM, 1 CPU, 40GB SSD
- Ubuntu 20.04 LTS atau 22.04 LTS
- Domain name yang sudah di-point ke VPS IP
- SSH access ke VPS

---

## 2. Initial Server Setup

### 2.1 Update System

```bash
sudo apt update
sudo apt upgrade -y
```

### 2.2 Install Required Packages

```bash
sudo apt install -y apache2 mysql-server php8.1 php8.1-mysql php8.1-curl php8.1-gd php8.1-mbstring php8.1-xml php8.1-zip unzip git
```

### 2.3 Configure Firewall

```bash
sudo ufw allow OpenSSH
sudo ufw allow 80
sudo ufw allow 443
sudo ufw enable
```

### 2.4 Create Non-Root User

```bash
sudo adduser mywisata
sudo usermod -aG sudo mywisata
```

---

## 3. MySQL Setup

### 3.1 Secure MySQL

```bash
sudo mysql_secure_installation
```

### 3.2 Create Database and User

```bash
sudo mysql -u root -p
```

```sql
CREATE DATABASE mywisata CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'mywisata'@'localhost' IDENTIFIED BY 'your_strong_password';
GRANT ALL PRIVILEGES ON mywisata.* TO 'mywisata'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 3.3 Import Database Schema

```bash
mysql -u mywisata -p mywisata < /path/to/database/migration.sql
```

---

## 4. Application Deployment

### 4.1 Clone Repository

```bash
cd /var/www
sudo git clone https://github.com/82080038/mywisata.git
sudo chown -R mywisata:www-data mywisata
cd mywisata
```

### 4.2 Configure Application

```bash
cp app/config/config.example.php app/config/config.php
cp app/config/database.example.php app/config/database.php
```

Edit `app/config/config.php`:

```php
define('APP_ENV', 'production');
define('APP_DEBUG', false);
define('BASE_URL', 'https://yourdomain.com');
```

Edit `app/config/database.php`:

```php
return [
    'host' => 'localhost',
    'database' => 'mywisata',
    'username' => 'mywisata',
    'password' => 'your_strong_password',
    'charset' => 'utf8mb4'
];
```

### 4.3 Set Permissions

```bash
sudo chown -R mywisata:www-data /var/www/mywisata
sudo chmod -R 755 /var/www/mywisata
sudo chmod -R 775 /var/www/mywisata/app/cache
sudo chmod -R 775 /var/www/mywisata/backups
sudo chmod -R 775 /var/www/mywisata/uploads
```

---

## 5. Apache Configuration

### 5.1 Enable mod_rewrite

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### 5.2 Create Virtual Host

```bash
sudo nano /etc/apache2/sites-available/mywisata.conf
```

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    DocumentRoot /var/www/mywisata
    
    <Directory /var/www/mywisata>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/mywisata_error.log
    CustomLog ${APACHE_LOG_DIR}/mywisata_access.log combined
</VirtualHost>
```

### 5.3 Enable Site

```bash
sudo a2ensite mywisata.conf
sudo a2dissite 000-default.conf
sudo systemctl restart apache2
```

---

## 6. SSL Setup with Let's Encrypt

### 6.1 Install Certbot

```bash
sudo apt install certbot python3-certbot-apache -y
```

### 6.2 Obtain SSL Certificate

```bash
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com
```

### 6.3 Auto-Renewal

```bash
sudo certbot renew --dry-run
```

---

## 7. Cron Jobs Setup

### 7.1 Database Backup (Daily)

```bash
sudo crontab -e
```

Add:

```
0 2 * * * /usr/bin/php /var/www/mywisata/app/helpers/Backup.php > /dev/null 2>&1
```

### 7.2 Cache Cleanup (Weekly)

```
0 3 * * 0 rm -rf /var/www/mywisata/app/cache/*
```

### 7.3 Log Rotation (Monthly)

```
0 4 1 * * find /var/www/mywisata/logs -name "*.log" -mtime +30 -delete
```

---

## 8. Monitoring Setup

### 8.1 Install Monitoring Tools

```bash
sudo apt install htop iotop -y
```

### 8.2 Setup Log Monitoring

```bash
sudo tail -f /var/log/apache2/mywisata_error.log
```

### 8.3 Setup Uptime Monitoring

Gunakan layanan seperti:
- UptimeRobot (gratis)
- Pingdom
- New Relic

---

## 9. Security Hardening

### 9.1 Disable Root SSH Login

```bash
sudo nano /etc/ssh/sshd_config
```

Set:
```
PermitRootLogin no
PasswordAuthentication no
```

### 9.2 Restart SSH

```bash
sudo systemctl restart sshd
```

### 9.3 Install Fail2Ban

```bash
sudo apt install fail2ban -y
sudo systemctl enable fail2ban
sudo systemctl start fail2ban
```

---

## 10. Performance Optimization

### 10.1 Enable OPcache

```bash
sudo nano /etc/php/8.1/apache2/php.ini
```

Set:
```
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
```

### 10.2 Restart Apache

```bash
sudo systemctl restart apache2
```

---

## 11. Troubleshooting

### 11.1 Check Apache Status

```bash
sudo systemctl status apache2
```

### 11.2 Check MySQL Status

```bash
sudo systemctl status mysql
```

### 11.3 View Error Logs

```bash
sudo tail -f /var/log/apache2/mywisata_error.log
```

### 11.4 Check Disk Space

```bash
df -h
```

### 11.5 Check Memory Usage

```bash
free -m
```

---

## 12. Backup Strategy

### 12.1 Automated Database Backup

Gunakan script backup yang sudah dibuat di `app/helpers/Backup.php`

### 12.2 File Backup

```bash
tar -czf mywisata-backup-$(date +%Y%m%d).tar.gz /var/www/mywisata
```

### 12.3 Offsite Backup

Upload backup ke:
- AWS S3
- Google Cloud Storage
- Dropbox
- FTP server lain

---

## 13. Go Live Checklist

- [ ] Database imported
- [ ] Config files updated
- [ ] Permissions set correctly
- [ ] Apache configured
- [ ] SSL certificate installed
- [ ] Cron jobs configured
- [ ] Firewall configured
- [ ] Monitoring setup
- [ ] Backup strategy in place
- [ ] Security hardening completed
- [ ] Performance testing passed
- [ ] DNS pointing correctly
- [ ] Smoke test completed

---

## 14. Post-Launch

### 14.1 Monitor First 24 Hours

- Check error logs regularly
- Monitor server resources
- Verify all features working

### 14.2 Collect User Feedback

- Setup feedback form
- Monitor user reviews
- Track bug reports

### 14.3 Performance Tuning

- Analyze slow queries
- Optimize caching
- Scale resources if needed

---

> **Dokumen selesai.** VPS setup guide untuk MyWisata Application.
