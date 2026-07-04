# MODUL 25 — DEPLOYMENT SERVER

> **Versi:** 1.1 · **Tanggal:** 2026-06-30 · **Last Updated:** 2026-06-30

---

## 1. RINGKASAN

Panduan deployment aplikasi ke server Linux VPS dengan Apache/Nginx,
PHP 8.1+, dan MySQL 8.0+.

---

## 2. SPESIFIKASI SERVER

| Komponen | Minimum | Rekomendasi |
|----------|---------|-------------|
| OS | Ubuntu 20.04 LTS | Ubuntu 22.04 LTS |
| CPU | 2 core | 4 core |
| RAM | 2 GB | 4 GB |
| Storage | 20 GB SSD | 50 GB SSD |
| Bandwidth | 1 TB/bulan | Unlimited |

---

## 3. INSTALASI STACK (LAMP)

### 3.1 Update System

```bash
sudo apt update && sudo apt upgrade -y
```

### 3.2 Install Apache

```bash
sudo apt install apache2 -y
sudo systemctl enable apache2
sudo systemctl start apache2
```

### 3.3 Install MySQL

```bash
sudo apt install mysql-server -y
sudo mysql_secure_installation
sudo systemctl enable mysql
sudo systemctl start mysql
```

### 3.4 Install PHP 8.1

```bash
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install php8.1 libapache2-mod-php8.1 php8.1-mysql php8.1-curl \
    php8.1-gd php8.1-mbstring php8.1-xml php8.1-zip php8.1-intl -y
```

### 3.5 Install phpMyAdmin (opsional)

```bash
sudo apt install phpmyadmin -y
```

---

## 4. DEPLOY APLIKASI

### 4.1 Clone/Copy Project

```bash
cd /var/www/
git clone https://github.com/yourrepo/tour-guide-app.git wisata
# atau
cp -r /path/to/wisata /var/www/wisata
```

### 4.2 Set Permissions

```bash
sudo chown -R www-data:www-data /var/www/wisata
sudo chmod -R 755 /var/www/wisata
sudo chmod -R 775 /var/www/wisata/public/uploads
sudo chmod -R 775 /var/www/wisata/database/backup
sudo chmod -R 775 /var/www/wisata/logs
```

### 4.3 Konfigurasi Database

```bash
mysql -u root -p
```

```sql
CREATE DATABASE tour_guide_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'tourguide'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON tour_guide_app.* TO 'tourguide'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 4.4 Import Schema

```bash
mysql -u tourguide -p tour_guide_app < /var/www/wisata/database/migration.sql
mysql -u tourguide -p tour_guide_app < /var/www/wisata/database/seed.sql
```

### 4.5 Update Config

```php
// app/config/database.php
return [
    'host'    => 'localhost',
    'dbname'  => 'tour_guide_app',
    'user'    => 'tourguide',
    'pass'    => 'strong_password_here',
    'charset' => 'utf8mb4',
];

// app/config/config.php
define('BASE_URL', 'https://yourdomain.com/');
```

---

## 5. APACHE VIRTUAL HOST

```apache
# /etc/apache2/sites-available/tourguide.conf
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    DocumentRoot /var/www/wisata

    <Directory /var/www/wisata>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/tourguide_error.log
    CustomLog ${APACHE_LOG_DIR}/tourguide_access.log combined
</VirtualHost>
```

```bash
sudo a2ensite tourguide.conf
sudo a2enmod rewrite
sudo systemctl reload apache2
```

---

## 6. NGINX CONFIG (ALTERNATIF)

```nginx
# /etc/nginx/sites-available/tourguide
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/wisata;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?url=$uri&$args;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    location ~ /\.(md|sql|log|git|env) {
        deny all;
    }

    location /app {
        deny all;
        return 403;
    }

    client_max_body_size 20M;
}
```

```bash
sudo ln -s /etc/nginx/sites-available/tourguide /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

---

## 7. SSL/HTTPS (Let's Encrypt)

```bash
sudo apt install certbot python3-certbot-apache -y
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com
```

Untuk Nginx:
```bash
sudo apt install certbot python3-certbot-nginx -y
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

---

## 8. SETUP CRON JOBS

```bash
# crontab -e

# Backup database harian 02:00
0 2 * * * /opt/scripts/backup_db.sh

# Notifikasi pengingat event H-1 (09:00)
0 9 * * * php /var/www/wisata/cron/event_reminder.php

# Cleanup audit log > 90 hari (00:00 setiap minggu)
0 0 * * 0 mysql -u tourguide -p'pass' tour_guide_app -e "DELETE FROM audit_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY)"
```

---

## 9. PRODUCTION MONITORING & SCALING

### 9.1 Application Performance Monitoring (APM)

**Status:** Not Implemented — HIGH PRIORITY

Implementasi APM untuk monitoring production:

```bash
# Install Sentry for error tracking
composer require sentry/sentry
```

**Implementation:**
- Sentry for error tracking and performance monitoring
- New Relic or Datadog for APM (optional)
- Uptime monitoring (Pingdom, UptimeRobot)
- Log aggregation (ELK stack or Loki)

### 9.2 Health Check Endpoints

```php
// app/controllers/HealthController.php
class HealthController extends Controller {
    public function index() {
        $health = [
            'status' => 'healthy',
            'timestamp' => time(),
            'services' => [
                'database' => $this->checkDatabase(),
                'cache' => $this->checkCache(),
                'storage' => $this->checkStorage(),
            ]
        ];
        $this->json($health);
    }
}
```

### 9.3 Horizontal Scaling Strategy

**Load Balancer Configuration (Nginx):**

```nginx
upstream backend {
    least_conn;
    server 10.0.1.10:80;
    server 10.0.1.11:80;
}
```

### 9.4 Redis for Session Storage

```bash
sudo apt install redis-server php8.1-redis
```

### 9.5 Cloud Storage for File Uploads

```bash
composer require aws/aws-sdk-php
```

### 9.6 CDN for Static Assets

- CloudFront or Cloudflare for CDN
- Enable gzip compression
- Configure cache behavior

### 9.7 Containerization (Docker)

```dockerfile
FROM php:8.1-apache
RUN docker-php-ext-install pdo pdo_mysql mbstring gd
RUN a2enmod rewrite
```

### 9.8 CI/CD Pipeline

```yaml
# .github/workflows/deploy.yml
name: Deploy to Production
on:
  push:
    branches: [main]
```

### 9.9 Disaster Recovery Plan

- Daily automated backups to S3
- Point-in-time recovery (PITR)
- Offsite backup in different region
- Weekly backup verification

---

## 10. OPTIMISASI PRODUKSI

### 10.1 PHP

```ini
; /etc/php/8.1/apache2/php.ini (atau fpm/php.ini)
memory_limit = 256M
upload_max_filesize = 20M
post_max_size = 25M
max_execution_time = 60
opcache.enable = 1
opcache.memory_consumption = 128
opcache.max_accelerated_files = 4000
```

### 10.2 MySQL

```ini
# /etc/mysql/mysql.conf.d/mysqld.cnf
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
query_cache_size = 64M
max_connections = 100
```

### 10.3 Apache

```bash
sudo a2enmod deflate expires headers
```

```apache
# .htaccess — compression & cache
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/css application/javascript application/json
</IfModule>

<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 30 days"
    ExpiresByType image/png "access plus 30 days"
    ExpiresByType text/css "access plus 7 days"
    ExpiresByType application/javascript "access plus 7 days"
</IfModule>
```

---

## 10. MONITORING

### 10.1 Log Files

| Log | Path |
|-----|------|
| Apache error | /var/log/apache2/tourguide_error.log |
| Apache access | /var/log/apache2/tourguide_access.log |
| PHP error | /var/log/php8.1/error.log |
| MySQL | /var/log/mysql/error.log |
| App error | /var/www/wisata/logs/error.log |
| App audit | /var/www/wisata/logs/audit.log |

### 10.2 Uptime Monitoring

```bash
# Simple check script
curl -s -o /dev/null -w "%{http_code}" https://yourdomain.com
# Expected: 200
```

---

## 11. CHECKLIST DEPLOYMENT

- [ ] Server LAMP/LNMP terinstall
- [ ] Database dibuat & schema diimport
- [ ] Config diupdate (BASE_URL, DB credentials)
- [ ] Permissions folder uploads, logs, backup
- [ ] Virtual host aktif
- [ ] .htaccess / nginx rewrite aktif
- [ ] SSL/HTTPS terpasang
- [ ] Cron jobs backup & reminder aktif
- [ ] PHP opcache aktif
- [ ] Gzip compression aktif
- [ ] File sensitive (.md, .sql, .env) tidak accessible
- [ ] Test login & basic flow
- [ ] Monitoring log aktif

---

> **Modul Selanjutnya:** `26_ROADMAP_PENGEMBANGAN.md`
