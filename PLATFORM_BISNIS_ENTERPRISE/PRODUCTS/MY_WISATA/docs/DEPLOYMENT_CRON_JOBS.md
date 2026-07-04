# Cron Jobs Setup - MyWisata Application

> **Versi:** 1.0 · **Tanggal:** 2026-07-01

---

## 1. Overview

Cron jobs are scheduled tasks that run automatically at specified intervals. This guide covers essential cron jobs for the MyWisata Application.

---

## 2. Prerequisites

- SSH access to VPS
- Sudo privileges
- Application deployed and configured

---

## 3. Edit Crontab

```bash
sudo crontab -e
```

Choose your preferred editor (nano, vim, etc.)

---

## 4. Essential Cron Jobs

### 4.1 Database Backup (Daily)

Backup database at 2 AM daily:

```cron
0 2 * * * /usr/bin/php /var/www/mywisata/app/helpers/Backup.php > /var/www/mywisata/logs/backup.log 2>&1
```

Or using mysqldump directly:

```cron
0 2 * * * mysqldump -u mywisata -p'password' mywisata > /var/www/mywisata/backups/db_backup_$(date +\%Y\%m\%d).sql
```

### 4.2 Cache Cleanup (Weekly)

Clear cache every Sunday at 3 AM:

```cron
0 3 * * 0 rm -rf /var/www/mywisata/app/cache/*
```

### 4.3 Log Rotation (Monthly)

Delete logs older than 30 days on the 1st of each month:

```cron
0 4 1 * * find /var/www/mywisata/logs -name "*.log" -mtime +30 -delete
```

### 4.4 Backup Cleanup (Weekly)

Delete backups older than 30 days every Sunday at 5 AM:

```cron
0 5 * * 0 find /var/www/mywisata/backups -name "*.sql" -mtime +30 -delete
```

### 4.5 SSL Certificate Renewal Check (Daily)

Check SSL certificate renewal at 3 AM daily:

```cron
0 3 * * * certbot renew --quiet --deploy-hook "systemctl reload apache2"
```

Or for Nginx:

```cron
0 3 * * * certbot renew --quiet --deploy-hook "systemctl reload nginx"
```

### 4.6 System Update Check (Weekly)

Check for system updates every Sunday at 6 AM:

```cron
0 6 * * 0 apt update && apt upgrade -y > /var/www/mywisata/logs/system_update.log 2>&1
```

### 4.7 Disk Space Check (Daily)

Check disk space and alert if > 80%:

```cron
0 7 * * * df -h | awk '$5 > 80 {print "Disk space warning: " $0}' >> /var/www/mywisata/logs/disk_space.log
```

### 4.8 Process Monitoring (Every 5 Minutes)

Check if Apache/Nginx and MySQL are running:

```cron
*/5 * * * * systemctl status apache2 > /dev/null || systemctl start apache2
*/5 * * * * systemctl status mysql > /dev/null || systemctl start mysql
```

### 4.9 Email Notifications (Daily)

Send daily summary email (requires mail setup):

```cron
0 8 * * * tail -n 100 /var/www/mywisata/logs/error.log | mail -s "Daily Error Log" admin@yourdomain.com
```

### 4.10 Session Cleanup (Daily)

Clean expired sessions from database:

```cron
0 1 * * * mysql -u mywisata -p'password' mywisata -e "DELETE FROM sessions WHERE expires < NOW()"
```

---

## 5. Complete Crontab Example

```cron
# MyWisata Application Cron Jobs

# Database Backup - Daily at 2 AM
0 2 * * * /usr/bin/php /var/www/mywisata/app/helpers/Backup.php > /var/www/mywisata/logs/backup.log 2>&1

# Cache Cleanup - Weekly on Sunday at 3 AM
0 3 * * 0 rm -rf /var/www/mywisata/app/cache/*

# Log Rotation - Monthly on 1st at 4 AM
0 4 1 * * find /var/www/mywisata/logs -name "*.log" -mtime +30 -delete

# Backup Cleanup - Weekly on Sunday at 5 AM
0 5 * * 0 find /var/www/mywisata/backups -name "*.sql" -mtime +30 -delete

# SSL Renewal Check - Daily at 3 AM
0 3 * * * certbot renew --quiet --deploy-hook "systemctl reload apache2"

# System Update Check - Weekly on Sunday at 6 AM
0 6 * * 0 apt update && apt upgrade -y > /var/www/mywisata/logs/system_update.log 2>&1

# Disk Space Check - Daily at 7 AM
0 7 * * * df -h | awk '$5 > 80 {print "Disk space warning: " $0}' >> /var/www/mywisata/logs/disk_space.log

# Process Monitoring - Every 5 minutes
*/5 * * * * systemctl status apache2 > /dev/null || systemctl start apache2
*/5 * * * * systemctl status mysql > /dev/null || systemctl start mysql

# Session Cleanup - Daily at 1 AM
0 1 * * * mysql -u mywisata -p'password' mywisata -e "DELETE FROM sessions WHERE expires < NOW()"
```

---

## 6. Cron Job Management

### 6.1 List Current Cron Jobs

```bash
sudo crontab -l
```

### 6.2 Edit Cron Jobs

```bash
sudo crontab -e
```

### 6.3 Remove All Cron Jobs

```bash
sudo crontab -r
```

### 6.4 Backup Cron Jobs

```bash
sudo crontab -l > /root/crontab_backup.txt
```

### 6.5 Restore Cron Jobs

```bash
sudo crontab /root/crontab_backup.txt
```

---

## 7. Monitor Cron Jobs

### 7.1 Check Cron Service Status

```bash
sudo systemctl status cron
```

### 7.2 View Cron Logs

```bash
sudo grep CRON /var/log/syslog
```

### 7.3 View Specific Cron Job Logs

```bash
tail -f /var/www/mywisata/logs/backup.log
```

### 7.4 Test Cron Job Manually

Run the command directly to test:

```bash
/usr/bin/php /var/www/mywisata/app/helpers/Backup.php
```

---

## 8. Cron Job Best Practices

### 8.1 Use Absolute Paths

Always use full paths for commands and files:

```cron
# Good
0 2 * * * /usr/bin/php /var/www/mywisata/app/helpers/Backup.php

# Bad
0 2 * * * php app/helpers/Backup.php
```

### 8.2 Redirect Output

Always redirect stdout and stderr:

```cron
0 2 * * * /usr/bin/php /var/www/mywisata/app/helpers/Backup.php > /var/www/mywisata/logs/backup.log 2>&1
```

### 8.3 Set Environment Variables

If needed, set environment variables:

```cron
0 2 * * * APP_ENV=production /usr/bin/php /var/www/mywisata/app/helpers/Backup.php
```

### 8.4 Use Lock Files

Prevent overlapping executions:

```cron
0 2 * * * flock -n /tmp/backup.lock /usr/bin/php /var/www/mywisata/app/helpers/Backup.php
```

### 8.5 Test Before Scheduling

Always test commands manually before adding to crontab.

---

## 9. Troubleshooting

### 9.1 Cron Job Not Running

Check:
1. Cron service is running
2. Syntax is correct
3. Paths are absolute
4. File permissions are correct
5. Command works manually

### 9.2 Permission Denied

Ensure the user running cron has permissions:

```bash
sudo chown -R mywisata:www-data /var/www/mywisata
```

### 9.3 Email Not Sent

Ensure mail is configured:

```bash
sudo apt install mailutils -y
```

### 9.4 Database Connection Error

Ensure credentials are correct in the script or use environment variables.

---

## 10. Advanced Cron Jobs

### 10.1 Randomized Execution

Run at random time within a range:

```cron
0 2-4 * * * sleep $((RANDOM \% 120)); /usr/bin/php /var/www/mywisata/app/helpers/Backup.php
```

### 10.2 Conditional Execution

Run only if specific condition is met:

```cron
0 2 * * * [ -f /var/www/mywisata/maintenance.flag ] && /usr/bin/php /var/www/mywisata/app/helpers/Backup.php
```

### 10.3 Multiple Commands

Chain multiple commands:

```cron
0 2 * * * /usr/bin/php /var/www/mywisata/app/helpers/Backup.php && /usr/bin/php /var/www/mywisata/app/helpers/Cache.php clear
```

---

## 11. Security Considerations

### 11.1 Restrict Cron Access

Edit `/etc/cron.allow`:

```
root
mywisata
```

### 11.2 Secure Credentials

Don't store passwords in crontab. Use environment variables or config files.

### 11.3 Log Rotation

Ensure logs are rotated to prevent disk space issues.

---

## 12. Monitoring Cron Jobs

### 12.1 Setup Monitoring

Use tools like:
- Cronitor
- Healthchecks.io
- Uptime monitoring

### 12.2 Alert on Failure

Setup email alerts for failed cron jobs.

### 12.3 Dashboard

Create a dashboard to monitor cron job status.

---

## 13. Cron Job Checklist

- [ ] Database backup scheduled
- [ ] Cache cleanup scheduled
- [ ] Log rotation scheduled
- [ ] Backup cleanup scheduled
- [ ] SSL renewal check scheduled
- [ ] System update check scheduled
- [ ] Disk space check scheduled
- [ ] Process monitoring scheduled
- [ ] Session cleanup scheduled
- [ ] All jobs tested manually
- [ ] Logs are being written
- [ ] Jobs are running on schedule
- [ ] Alerts configured for failures

---

## 14. Resources

- Crontab.guru: https://crontab.guru/
- Cron Job Documentation: https://man7.org/linux/man-pages/man5/crontab.5.html
- Systemd Timers: Alternative to cron for modern systems

---

> **Dokumen selesai.** Cron jobs setup guide untuk MyWisata Application.
