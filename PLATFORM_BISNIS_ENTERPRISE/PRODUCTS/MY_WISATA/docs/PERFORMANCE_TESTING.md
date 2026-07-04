# Performance Testing Guide - MyWisata Application

> **Versi:** 1.0 · **Tanggal:** 2026-07-01

---

## 1. Overview

Performance testing ensures the application can handle expected load and performs optimally under stress.

---

## 2. Testing Tools

### 2.1 Apache Bench (ab)

```bash
sudo apt install apache2-utils -y
```

### 2.2 JMeter

Download from: https://jmeter.apache.org/download_jmeter.cgi

### 2.3 Siege

```bash
sudo apt install siege -y
```

### 2.4 wrk

```bash
sudo apt install wrk -y
```

### 2.5 Loader.io

Online tool: https://loader.io/

---

## 3. Load Testing Scenarios

### 3.1 Homepage Load Test

Using Apache Bench:

```bash
ab -n 1000 -c 10 https://yourdomain.com/
```

Parameters:
- `-n 1000`: 1000 total requests
- `-c 10`: 10 concurrent requests

### 3.2 API Endpoint Load Test

```bash
ab -n 500 -c 5 -p api_test.json -T application/json https://yourdomain.com/api/destinations
```

### 3.3 Login Page Load Test

```bash
ab -n 200 -c 5 https://yourdomain.com/auth/login
```

### 3.4 Booking Process Load Test

```bash
ab -n 100 -c 2 -p booking.json -T application/json https://yourdomain.com/booking/store
```

---

## 4. Siege Testing

### 4.1 Simple Siege Test

```bash
siege -c 10 -t 30S https://yourdomain.com/
```

Parameters:
- `-c 10`: 10 concurrent users
- `-t 30S`: Test for 30 seconds

### 4.2 Siege with URL List

Create `urls.txt`:

```
https://yourdomain.com/
https://yourdomain.com/destinations
https://yourdomain.com/hotels
https://yourdomain.com/restaurants
https://yourdomain.com/events
```

Run siege:

```bash
siege -c 20 -f urls.txt
```

### 4.3 Siege Configuration

Edit `~/.siegerc`:

```
# Siege configuration
verbose = true
show-logfile = false
csv = true
fullurl = true
concurrent = 25
time = 30S
delay = 1
```

---

## 5. wrk Testing

### 5.1 Basic wrk Test

```bash
wrk -t4 -c100 -d30s https://yourdomain.com/
```

Parameters:
- `-t4`: 4 threads
- `-c100`: 100 connections
- `-d30s`: Test for 30 seconds

### 5.2 wrk with Lua Script

Create `script.lua`:

```lua
wrk.method = "POST"
wrk.body   = '{"test":"data"}'
wrk.headers["Content-Type"] = "application/json"
```

Run:

```bash
wrk -t4 -c10 -d30s -s script.lua https://yourdomain.com/api/test
```

---

## 6. Database Performance Testing

### 6.1 Slow Query Log

Enable in MySQL:

```sql
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 2;
SET GLOBAL slow_query_log_file = '/var/log/mysql/slow-query.log';
```

### 6.2 Analyze Slow Queries

```bash
sudo mysqldumpslow -s t -t 10 /var/log/mysql/slow-query.log
```

### 6.3 Explain Query

```sql
EXPLAIN SELECT * FROM destinations WHERE city = 'Jakarta';
```

### 6.4 Index Analysis

```sql
SHOW INDEX FROM destinations;
```

---

## 7. PHP Performance Testing

### 7.1 OPcache Status

Create `opcache.php`:

```php
<?php
phpinfo(INFO_MODULES);
?>
```

Access: `https://yourdomain.com/opcache.php`

### 7.2 PHP-FPM Status

Edit pool configuration:

```ini
pm.status_path = /status
ping.path = /ping
```

Access: `https://yourdomain.com/status`

### 7.3 XHProf (Optional)

Install XHProf for profiling:

```bash
sudo apt install php-xhprof -y
```

---

## 8. Web Server Performance

### 8.1 Apache Benchmark

```bash
ab -n 1000 -c 100 https://yourdomain.com/
```

### 8.2 Nginx Benchmark

```bash
ab -n 1000 -c 100 https://yourdomain.com/
```

### 8.3 Enable Compression

Ensure gzip is enabled in web server config.

### 8.4 Enable Caching

Configure browser caching and server-side caching.

---

## 9. Performance Metrics

### 9.1 Key Metrics to Monitor

- **Response Time**: < 200ms for static, < 500ms for dynamic
- **Throughput**: > 100 requests/second
- **Error Rate**: < 0.1%
- **CPU Usage**: < 70% under load
- **Memory Usage**: < 80% under load
- **Database Queries**: < 100ms per query

### 9.2 Acceptable Thresholds

| Metric | Good | Warning | Critical |
|--------|------|---------|----------|
| Response Time | < 200ms | 200-500ms | > 500ms |
| Throughput | > 100 req/s | 50-100 req/s | < 50 req/s |
| Error Rate | < 0.1% | 0.1-1% | > 1% |
| CPU Usage | < 70% | 70-90% | > 90% |
| Memory Usage | < 80% | 80-90% | > 90% |

---

## 10. Performance Optimization

### 10.1 Database Optimization

```sql
-- Add indexes
CREATE INDEX idx_destinations_city ON destinations(city);
CREATE INDEX idx_bookings_user_id ON bookings(user_id);
CREATE INDEX idx_bookings_status ON bookings(status);

-- Optimize tables
OPTIMIZE TABLE destinations;
OPTIMIZE TABLE bookings;
OPTIMIZE TABLE users;
```

### 10.2 PHP Optimization

Enable OPcache in `php.ini`:

```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
```

### 10.3 Web Server Optimization

#### Apache

Enable modules:

```bash
sudo a2enmod expires
sudo a2enmod headers
sudo a2enmod deflate
sudo a2enmod rewrite
```

#### Nginx

Enable gzip and caching in config.

### 10.4 Application Optimization

- Use caching for frequently accessed data
- Implement lazy loading for images
- Minify CSS and JavaScript
- Use CDN for static assets
- Implement database query caching

---

## 11. Load Testing Scenarios

### 11.1 Scenario 1: Normal Load

- 50 concurrent users
- 10 minutes duration
- Mix of read operations

```bash
siege -c 50 -t 600S -f urls.txt
```

### 11.2 Scenario 2: Peak Load

- 200 concurrent users
- 5 minutes duration
- Mix of read and write operations

```bash
siege -c 200 -t 300S -f urls.txt
```

### 11.3 Scenario 3: Stress Test

- 500 concurrent users
- 2 minutes duration
- Heavy write operations

```bash
siege -c 500 -t 120S -f urls.txt
```

---

## 12. Monitoring During Tests

### 12.1 System Resources

```bash
# CPU
htop

# Memory
free -m

# Disk I/O
iotop

# Network
iftop
```

### 12.2 Application Logs

```bash
# Apache logs
sudo tail -f /var/log/apache2/mywisata_access.log
sudo tail -f /var/log/apache2/mywisata_error.log

# Nginx logs
sudo tail -f /var/log/nginx/mywisata_access.log
sudo tail -f /var/log/nginx/mywisata_error.log

# PHP logs
sudo tail -f /var/log/php8.1-fpm.log

# MySQL logs
sudo tail -f /var/log/mysql/error.log
```

### 12.3 Database Performance

```bash
# MySQL process list
mysql -u root -p -e "SHOW PROCESSLIST;"

# MySQL status
mysql -u root -p -e "SHOW STATUS;"
```

---

## 13. Performance Testing Checklist

- [ ] Load testing tools installed
- [ ] Baseline performance measured
- [ ] Normal load test completed
- [ ] Peak load test completed
- [ ] Stress test completed
- [ ] Bottlenecks identified
- [ ] Optimizations applied
- [ ] Re-test after optimization
- [ ] Performance targets met
- [ ] Monitoring setup for production

---

## 14. Troubleshooting

### 14.1 High Response Time

- Check database queries
- Check server resources
- Check network latency
- Review application code

### 14.2 High CPU Usage

- Check for infinite loops
- Review database queries
- Check for caching issues
- Scale resources if needed

### 14.3 High Memory Usage

- Check for memory leaks
- Review PHP memory limit
- Check database connections
- Implement memory optimization

### 14.4 Database Slow Queries

- Add appropriate indexes
- Optimize queries
- Use query caching
- Consider database scaling

---

## 15. Continuous Performance Monitoring

### 15.1 Setup Monitoring Tools

- New Relic
- Datadog
- Prometheus + Grafana
- AWS CloudWatch

### 15.2 Setup Alerts

- Response time > 500ms
- Error rate > 1%
- CPU > 90%
- Memory > 90%
- Disk space < 10%

### 15.3 Regular Testing

- Weekly load tests
- Monthly stress tests
- Quarterly full performance audit

---

## 16. Resources

- Apache Bench: https://httpd.apache.org/docs/2.4/programs/ab.html
- Siege: https://www.joedog.org/siege-home/
- wrk: https://github.com/wg/wrk
- JMeter: https://jmeter.apache.org/
- Loader.io: https://loader.io/

---

> **Dokumen selesai.** Performance testing guide untuk MyWisata Application.
