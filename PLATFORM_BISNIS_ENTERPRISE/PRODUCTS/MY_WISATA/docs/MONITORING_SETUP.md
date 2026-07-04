# Monitoring Setup Guide - MyWisata Application

> **Versi:** 1.0 · **Tanggal:** 2026-07-01

---

## 1. Overview

Monitoring ensures the application runs smoothly, detects issues early, and provides insights for optimization.

---

## 2. System Monitoring

### 2.1 Install Monitoring Tools

```bash
sudo apt update
sudo apt install htop iotop nethogs sysstat -y
```

### 2.2 Enable System Statistics

```bash
sudo systemctl enable sysstat
sudo systemctl start sysstat
```

### 2.3 Monitor CPU Usage

```bash
htop
```

Or use `top`:

```bash
top
```

### 2.4 Monitor Memory Usage

```bash
free -m
```

### 2.5 Monitor Disk Usage

```bash
df -h
```

### 2.6 Monitor Disk I/O

```bash
iotop
```

### 2.7 Monitor Network Traffic

```bash
iftop
```

Or use `nethogs`:

```bash
sudo nethogs
```

---

## 3. Application Monitoring

### 3.1 Apache Monitoring

#### Check Apache Status

```bash
sudo systemctl status apache2
```

#### Monitor Apache Logs

```bash
sudo tail -f /var/log/apache2/mywisata_access.log
sudo tail -f /var/log/apache2/mywisata_error.log
```

#### Apache Status Module

Enable status module:

```bash
sudo a2enmod status
sudo systemctl restart apache2
```

Edit status config:

```bash
sudo nano /etc/apache2/mods-enabled/status.conf
```

```apache
<Location /server-status>
    SetHandler server-status
    Require ip 127.0.0.1
</Location>
```

Access: `http://localhost/server-status`

### 3.2 Nginx Monitoring

#### Check Nginx Status

```bash
sudo systemctl status nginx
```

#### Monitor Nginx Logs

```bash
sudo tail -f /var/log/nginx/mywisata_access.log
sudo tail -f /var/log/nginx/mywisata_error.log
```

#### Nginx Status Module

Add to server block:

```nginx
location /nginx_status {
    stub_status on;
    access_log off;
    allow 127.0.0.1;
    deny all;
}
```

Access: `http://localhost/nginx_status`

### 3.3 PHP-FPM Monitoring

#### Check PHP-FPM Status

```bash
sudo systemctl status php8.1-fpm
```

#### Monitor PHP-FPM Logs

```bash
sudo tail -f /var/log/php8.1-fpm.log
```

#### Enable PHP-FPM Status

Edit pool configuration:

```bash
sudo nano /etc/php/8.1/fpm/pool.d/www.conf
```

Uncomment:

```ini
pm.status_path = /status
ping.path = /ping
```

Access: `http://localhost/status`

---

## 4. Database Monitoring

### 4.1 Check MySQL Status

```bash
sudo systemctl status mysql
```

### 4.2 Monitor MySQL Logs

```bash
sudo tail -f /var/log/mysql/error.log
```

### 4.3 MySQL Process List

```bash
mysql -u root -p -e "SHOW PROCESSLIST;"
```

### 4.4 MySQL Status

```bash
mysql -u root -p -e "SHOW STATUS;"
```

### 4.5 MySQL Variables

```bash
mysql -u root -p -e "SHOW VARIABLES;"
```

### 4.6 Enable Slow Query Log

```sql
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 2;
SET GLOBAL slow_query_log_file = '/var/log/mysql/slow-query.log';
```

### 4.7 Monitor Slow Queries

```bash
sudo tail -f /var/log/mysql/slow-query.log
```

### 4.8 Analyze Slow Queries

```bash
sudo mysqldumpslow -s t -t 10 /var/log/mysql/slow-query.log
```

---

## 5. Application Logs Monitoring

### 5.1 Application Error Log

```bash
tail -f /var/www/mywisata/logs/error.log
```

### 5.2 Application Access Log

```bash
tail -f /var/www/mywisata/logs/access.log
```

### 5.3 Audit Log

```bash
tail -f /var/www/mywisata/logs/audit.log
```

### 5.4 Backup Log

```bash
tail -f /var/www/mywisata/logs/backup.log
```

---

## 6. Uptime Monitoring

### 6.1 UptimeRobot (Free)

1. Sign up at https://uptimerobot.com/
2. Add monitor for your domain
3. Configure alert settings
4. Set up email/SMS notifications

### 6.2 Pingdom (Free Tier)

1. Sign up at https://www.pingdom.com/
2. Add monitor for your domain
3. Configure response time alerts
4. Set up notifications

### 6.3 StatusCake (Free)

1. Sign up at https://www.statuscake.com/
2. Add HTTP monitor
3. Configure keyword check
4. Set up alerts

---

## 7. Performance Monitoring

### 7.1 New Relic (Free Tier)

1. Sign up at https://newrelic.com/
2. Install New Relic agent:

```bash
sudo apt install newrelic-php5 -y
```

3. Configure with license key
4. Monitor application performance

### 7.2 Datadog (Free Tier)

1. Sign up at https://www.datadoghq.com/
2. Install Datadog agent:

```bash
DD_API_KEY=your_api_key bash -c "$(curl -L https://raw.githubusercontent.com/DataDog/datadog-agent/master/cmd/agent/install_script.sh)"
```

3. Configure monitoring
4. View metrics in dashboard

### 7.3 Prometheus + Grafana

#### Install Prometheus

```bash
sudo apt install prometheus -y
sudo systemctl enable prometheus
sudo systemctl start prometheus
```

#### Install Grafana

```bash
sudo apt install grafana -y
sudo systemctl enable grafana-server
sudo systemctl start grafana-server
```

Access Grafana: `http://your-server:3000`

---

## 8. Log Management

### 8.1 Logrotate Setup

Create logrotate config:

```bash
sudo nano /etc/logrotate.d/mywisata
```

```
/var/www/mywisata/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
}
```

### 8.2 Centralized Logging

#### ELK Stack (Optional)

Install Elasticsearch, Logstash, Kibana for centralized logging.

#### Graylog (Alternative)

Install Graylog for log management and analysis.

---

## 9. Alerting Setup

### 9.1 Email Alerts

Configure mail server:

```bash
sudo apt install mailutils -y
```

Setup alerts in cron jobs or monitoring tools.

### 9.2 SMS Alerts

Use services like:
- Twilio
- Nexmo
- AWS SNS

### 9.3 Slack Alerts

Use Slack webhooks for notifications.

### 9.4 Discord Alerts

Use Discord webhooks for notifications.

---

## 10. Custom Monitoring Script

Create custom monitoring script:

```bash
sudo nano /usr/local/bin/mywisata-monitor.sh
```

```bash
#!/bin/bash

# MyWisata Monitoring Script

# Check Apache
if ! systemctl is-active --quiet apache2; then
    echo "Apache is down!" | mail -s "Apache Down Alert" admin@yourdomain.com
    systemctl start apache2
fi

# Check MySQL
if ! systemctl is-active --quiet mysql; then
    echo "MySQL is down!" | mail -s "MySQL Down Alert" admin@yourdomain.com
    systemctl start mysql
fi

# Check Disk Space
DISK_USAGE=$(df -h / | awk 'NR==2 {print $5}' | sed 's/%//')
if [ $DISK_USAGE -gt 80 ]; then
    echo "Disk usage is ${DISK_USAGE}%" | mail -s "Disk Space Warning" admin@yourdomain.com
fi

# Check Memory Usage
MEM_USAGE=$(free | awk '/Mem/{printf("%.0f"), $3/$2*100}')
if [ $MEM_USAGE -gt 80 ]; then
    echo "Memory usage is ${MEM_USAGE}%" | mail -s "Memory Warning" admin@yourdomain.com
fi
```

Make executable:

```bash
sudo chmod +x /usr/local/bin/mywisata-monitor.sh
```

Add to cron:

```cron
*/5 * * * * /usr/local/bin/mywisata-monitor.sh
```

---

## 11. Dashboard Setup

### 11.1 Grafana Dashboard

1. Install Grafana
2. Add Prometheus data source
3. Create dashboard with panels:
   - CPU Usage
   - Memory Usage
   - Disk Usage
   - Network Traffic
   - Apache Requests
   - MySQL Connections
   - Response Time

### 11.2 Custom Dashboard

Create simple HTML dashboard:

```html
<!DOCTYPE html>
<html>
<head>
    <title>MyWisata Monitoring</title>
    <meta http-equiv="refresh" content="30">
</head>
<body>
    <h1>MyWisata Application Monitoring</h1>
    <div>
        <h2>Server Status</h2>
        <p>Apache: <span id="apache">Checking...</span></p>
        <p>MySQL: <span id="mysql">Checking...</span></p>
        <p>Disk Usage: <span id="disk">Checking...</span></p>
    </div>
    <script>
        // Fetch status from API
        setInterval(function() {
            fetch('/api/status')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('apache').textContent = data.apache;
                    document.getElementById('mysql').textContent = data.mysql;
                    document.getElementById('disk').textContent = data.disk;
                });
        }, 30000);
    </script>
</body>
</html>
```

---

## 12. Security Monitoring

### 12.1 Fail2Ban Monitoring

```bash
sudo systemctl status fail2ban
sudo fail2ban-client status
```

### 12.2 Monitor Failed Login Attempts

```bash
sudo tail -f /var/log/auth.log | grep "Failed password"
```

### 12.3 Monitor Brute Force Attacks

```bash
sudo tail -f /var/log/auth.log | grep "Invalid user"
```

---

## 13. Backup Monitoring

### 13.1 Monitor Backup Jobs

Check backup logs:

```bash
tail -f /var/www/mywisata/logs/backup.log
```

### 13.2 Verify Backup Integrity

```bash
ls -lh /var/www/mywisata/backups/
```

### 13.3 Test Backup Restoration

Regularly test backup restoration process.

---

## 14. Performance Monitoring

### 14.1 Monitor Response Time

Use tools like:
- Pingdom
- GTmetrix
- PageSpeed Insights

### 14.2 Monitor Database Performance

Monitor slow queries and optimize.

### 14.3 Monitor Cache Hit Rate

Monitor cache effectiveness.

---

## 15. Monitoring Checklist

- [ ] System monitoring tools installed
- [ ] Apache/Nginx monitoring configured
- [ ] PHP-FPM monitoring configured
- [ ] MySQL monitoring configured
- [ ] Application logs monitored
- [ ] Uptime monitoring setup
- [ ] Performance monitoring setup
- [ ] Alerting configured
- [ ] Custom monitoring script created
- [ ] Dashboard setup
- [ ] Security monitoring configured
- [ ] Backup monitoring configured
- [ ] Regular monitoring schedule established

---

## 16. Best Practices

### 16.1 Regular Monitoring

- Check logs daily
- Review metrics weekly
- Conduct full audit monthly

### 16.2 Proactive Alerts

- Set up alerts for critical issues
- Use multiple notification channels
- Test alert system regularly

### 16.3 Documentation

- Document monitoring procedures
- Keep alert thresholds updated
- Maintain runbooks for common issues

### 16.4 Continuous Improvement

- Review monitoring effectiveness
- Adjust monitoring as needed
- Stay updated on new tools

---

## 17. Troubleshooting

### 17.1 Monitoring Tool Not Working

- Check service status
- Verify configuration
- Check logs for errors
- Restart service if needed

### 17.2 Alerts Not Sending

- Verify email configuration
- Check SMTP settings
- Test alert system
- Verify notification channels

### 17.3 High Resource Usage

- Identify the cause
- Optimize application
- Scale resources if needed
- Consider load balancing

---

## 18. Resources

- Prometheus: https://prometheus.io/
- Grafana: https://grafana.com/
- New Relic: https://newrelic.com/
- Datadog: https://www.datadoghq.com/
- UptimeRobot: https://uptimerobot.com/
- Pingdom: https://www.pingdom.com/

---

> **Dokumen selesai.** Monitoring setup guide untuk MyWisata Application.
