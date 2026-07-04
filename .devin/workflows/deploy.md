---
description: Deploy EBP Restaurant backend to production
---

# Deployment Workflow

This workflow guides you through deploying the EBP Restaurant backend to production.

## Prerequisites

- Production server with PHP 8.x and MySQL 8.x
- Database access credentials
- Domain name configured
- SSL certificate (recommended)

## Steps

1. **Sync from GitHub**
   ```bash
   cd /opt/lampp/htdocs/EBP
   git pull origin master
   ```

2. **Configure production database**
   - Edit `config/database.php` with production credentials
   - Update host, socket, dbname, username, password
   - Ensure database user has appropriate permissions

3. **Import database schema** (if new deployment)
   ```bash
   mysql -h [host] -u [user] -p [database] < database/schema.sql
   ```

4. **Import current data** (if needed)
   ```bash
   mysql -h [host] -u [user] -p [database] < database/current_data.sql
   ```

5. **Run migrations** (if needed)
   - Run migration scripts in order from `database/` directory
   - Start with `migration_phase1.sql` and proceed sequentially

6. **Configure web server**
   - Apache: Point DocumentRoot to `public/` directory
   - Nginx: Configure root to point to `public/` directory
   - Enable mod_rewrite for Apache
   - Configure PHP-FPM for Nginx

7. **Set file permissions**
   ```bash
   chmod -R 755 public/
   chmod -R 644 public/*.php
   ```

8. **Configure environment variables** (if using)
   - Create `.env` file with production settings
   - Update JWT secret key
   - Update database credentials

9. **Test deployment**
   - Access API base URL
   - Test authentication endpoint
   - Verify database connection
   - Check frontend applications

10. **Enable HTTPS** (recommended)
    - Install SSL certificate
    - Configure web server for HTTPS
    - Update CORS settings if needed

## Security Checklist

- [ ] Change default admin password
- [ ] Update JWT secret key in `core/JWT.php`
- [ ] Enable HTTPS
- [ ] Configure proper CORS settings
- [ ] Set up firewall rules
- [ ] Enable rate limiting
- [ ] Configure backup strategy
- [ ] Set up monitoring

## Post-Deployment

- Monitor application logs
- Set up automated backups
- Configure error tracking
- Set up uptime monitoring
