---
description: How to deploy the application to a production Linux VPS server
---

# Deploy to Production Server

## Prerequisites
- Ubuntu 20.04+ VPS with SSH access
- Domain name pointing to server IP
- Sudo access

## Steps

1. Install LAMP stack:
   ```bash
   sudo apt update && sudo apt upgrade -y
   sudo apt install apache2 mysql-server php8.1 libapache2-mod-php8.1 php8.1-mysql php8.1-curl php8.1-gd php8.1-mbstring php8.1-xml php8.1-zip php8.1-intl -y
   ```

2. Secure MySQL:
   ```bash
   sudo mysql_secure_installation
   ```

3. Create database and user:
   ```bash
   sudo mysql -e "CREATE DATABASE mywisata CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   sudo mysql -e "CREATE USER 'mywisata'@'localhost' IDENTIFIED BY 'strong_password_here';"
   sudo mysql -e "GRANT ALL PRIVILEGES ON mywisata.* TO 'mywisata'@'localhost';"
   sudo mysql -e "FLUSH PRIVILEGES;"
   ```

4. Clone project:
   ```bash
   sudo git clone https://github.com/82080038/mywisata.git /var/www/mywisata
   ```

5. Import database:
   ```bash
   mysql -u mywisata -p mywisata < /var/www/mywisata/database/migration.sql
   mysql -u mywisata -p mywisata < /var/www/mywisata/database/seed.sql
   ```

6. Set permissions:
   ```bash
   sudo chown -R www-data:www-data /var/www/mywisata
   sudo chmod -R 755 /var/www/mywisata
   sudo chmod -R 775 /var/www/mywisata/public/uploads /var/www/mywisata/logs /var/www/mywisata/database/backup
   ```

7. Configure production settings:
   - Edit `app/config/config.php`: Set `APP_ENV=production`, `APP_DEBUG=false`, `BASE_URL=https://yourdomain.com/`
   - Edit `app/config/database.php`: Set production DB credentials

8. Configure Apache VirtualHost:
   ```apache
   <VirtualHost *:80>
       ServerName yourdomain.com
       DocumentRoot /var/www/mywisata
       <Directory /var/www/mywisata>
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```

9. Enable modules and site:
   ```bash
   sudo a2enmod rewrite headers
   sudo a2ensite mywisata.conf
   sudo systemctl reload apache2
   ```

10. Install SSL:
    ```bash
    sudo apt install certbot python3-certbot-apache -y
    sudo certbot --apache -d yourdomain.com
    ```

11. Setup cron jobs:
    ```bash
    # Database backup daily at 02:00
    0 2 * * * /opt/scripts/backup_db.sh
    # Event reminder at 09:00
    0 9 * * * php /var/www/mywisata/cron/event_reminder.php
    ```

12. Smoke test: Open `https://yourdomain.com/` and test login

## Post-Deployment Checklist
- [ ] SSL active (HTTPS works)
- [ ] `APP_DEBUG=false` (no errors shown to user)
- [ ] Default admin password changed
- [ ] File permissions correct
- [ ] Cron jobs active
- [ ] Security headers present (check with securityheaders.com)
- [ ] Sensitive files (.sql, .md, .log) not accessible

## Full guide
See `docs/25_DEPLOYMENT_SERVER.md` for complete deployment instructions.
