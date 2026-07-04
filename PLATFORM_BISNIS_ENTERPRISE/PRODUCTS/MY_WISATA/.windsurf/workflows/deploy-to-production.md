---
description: How to deploy the application to a production Linux VPS server
---

# Deploy to Production Server

## Steps

1. Install LAMP stack on Ubuntu 20.04+
2. Create database `mywisata` and user
3. Clone project to `/var/www/mywisata`
4. Import `database/migration.sql` and `database/seed.sql` into `mywisata` database
5. Set permissions: `chown www-data`, `chmod 755`, `chmod 775` for uploads/logs/backup
6. Configure `config.php` (APP_ENV=production, APP_DEBUG=false) and `database.php`
7. Configure Apache VirtualHost with `AllowOverride All`
8. Enable `mod_rewrite` and `mod_headers`
9. Install SSL via Let's Encrypt (`certbot --apache`)
10. Setup cron jobs (backup, event reminder)
11. Smoke test

## Post-Deployment Checklist
- [ ] SSL active
- [ ] APP_DEBUG=false
- [ ] Default admin password changed
- [ ] File permissions correct
- [ ] Cron jobs active
- [ ] Security headers present

## Full guide
See `docs/25_DEPLOYMENT_SERVER.md` for complete instructions.
