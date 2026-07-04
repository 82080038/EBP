# MODUL 37 — PERFORMANCE TUNING GUIDE

> **Versi:** 1.0 · **Tanggal:** 2026-06-30

---

## 1. RINGKASAN

Panduan optimasi performance untuk aplikasi Tour Guide agar berjalan optimal di production.

---

## 2. PHP OPTIMIZATION

### 2.1 PHP Configuration

**php.ini Optimization:**

```ini
; Memory Limit
memory_limit = 256M

; Execution Time
max_execution_time = 300

; Input Time
max_input_time = 300

; Post Max Size
post_max_size = 50M

; Upload Max Filesize
upload_max_filesize = 50M

; Max Input Vars
max_input_vars = 3000

; OPcache Enable
opcache.enable = 1
opcache.memory_consumption = 128
opcache.interned_strings_buffer = 8
opcache.max_accelerated_files = 10000
opcache.revalidate_freq = 2
opcache.fast_shutdown = 1
opcache.enable_cli = 1

; Realpath Cache
realpath_cache_size = 4096K
realpath_cache_ttl = 600
```

### 2.2 Code Optimization

**Use Prepared Statements:**

```php
// Bad
$result = $db->query("SELECT * FROM users WHERE id = " . $id);

// Good
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$result = $stmt->fetchAll();
```

**Avoid N+1 Query Problem:**

```php
// Bad
$bookings = $db->query("SELECT * FROM bookings")->fetchAll();
foreach ($bookings as $booking) {
    $guide = $db->query("SELECT * FROM guides WHERE id = " . $booking['guide_id'])->fetch();
}

// Good
$bookings = $db->query("
    SELECT b.*, g.name as guide_name 
    FROM bookings b 
    JOIN guides g ON b.guide_id = g.id
")->fetchAll();
```

**Use Indexing:**

```php
// Ensure database columns used in WHERE, JOIN, ORDER BY are indexed
CREATE INDEX idx_bookings_guide_id ON bookings(guide_id);
CREATE INDEX idx_bookings_user_id ON bookings(user_id);
CREATE INDEX idx_bookings_date ON bookings(booking_date);
```

---

## 3. MYSQL OPTIMIZATION

### 3.1 MySQL Configuration

**my.cnf Optimization:**

```ini
[mysqld]
# InnoDB Buffer Pool
innodb_buffer_pool_size = 2G
innodb_buffer_pool_instances = 2

# InnoDB Log File
innodb_log_file_size = 256M
innodb_log_buffer_size = 16M

# InnoDB Flush Method
innodb_flush_method = O_DIRECT

# Query Cache (MySQL 5.7 and below)
query_cache_type = 1
query_cache_size = 64M
query_cache_limit = 2M

# Connection Settings
max_connections = 500
thread_cache_size = 50
table_open_cache = 4000

# Slow Query Log
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow-query.log
long_query_time = 2

# Binary Log
log_bin = mysql-bin
binlog_format = ROW
expire_logs_days = 7
```

### 3.2 Query Optimization

**Use EXPLAIN:**

```sql
EXPLAIN SELECT * FROM bookings WHERE user_id = 123;
```

**Optimize JOINs:**

```sql
-- Bad
SELECT * FROM bookings b, guides g WHERE b.guide_id = g.id;

-- Good
SELECT b.*, g.name 
FROM bookings b 
INNER JOIN guides g ON b.guide_id = g.id;
```

**Use LIMIT:**

```sql
-- Bad
SELECT * FROM bookings;

-- Good
SELECT * FROM bookings LIMIT 100;
```

### 3.3 Indexing Strategy

```sql
-- Create composite indexes for common queries
CREATE INDEX idx_bookings_user_date ON bookings(user_id, booking_date);
CREATE INDEX idx_bookings_guide_status ON bookings(guide_id, status);

-- Use covering indexes
CREATE INDEX idx_covering ON bookings(user_id, guide_id, status, booking_date);
```

---

## 4. WEBSERVER OPTIMIZATION

### 4.1 Apache Optimization

**httpd.conf:**

```apache
# Enable KeepAlive
KeepAlive On
MaxKeepAliveRequests 100
KeepAliveTimeout 5

# Enable Compression
LoadModule deflate_module modules/mod_deflate.so
LoadModule headers_module modules/mod_headers.so

<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript
</IfModule>

# Enable Expires
LoadModule expires_module modules/mod_expires.so
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 year"
    ExpiresByType application/javascript "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
</IfModule>

# Enable MPM Prefork
<IfModule mpm_prefork_module>
    StartServers 5
    MinSpareServers 5
    MaxSpareServers 10
    MaxRequestWorkers 150
    MaxConnectionsPerChild 0
</IfModule>
```

### 4.2 Nginx Optimization

**nginx.conf:**

```nginx
# Worker Processes
worker_processes auto;
worker_rlimit_nofile 65535;

# Events
events {
    worker_connections 4096;
    use epoll;
    multi_accept on;
}

# HTTP
http {
    # Gzip Compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/javascript application/json;

    # Buffer Size
    client_body_buffer_size 128k;
    client_max_body_size 50m;
    client_header_buffer_size 1k;
    large_client_header_buffers 4 16k;

    # Timeouts
    client_body_timeout 12;
    client_header_timeout 12;
    keepalive_timeout 15;
    send_timeout 10;

    # Caching
    open_file_cache max=1000 inactive=20s;
    open_file_cache_valid 30s;
    open_file_cache_min_uses 2;
}
```

---

## 5. CACHING STRATEGY

### 5.1 Application Level Caching

**File Caching:**

```php
class Cache {
    private $cacheDir = '/tmp/cache/';
    
    public function set($key, $data, $ttl = 3600) {
        $file = $this->cacheDir . md5($key) . '.cache';
        $content = serialize([
            'data' => $data,
            'expires' => time() + $ttl
        ]);
        file_put_contents($file, $content);
    }
    
    public function get($key) {
        $file = $this->cacheDir . md5($key) . '.cache';
        if (!file_exists($file)) {
            return null;
        }
        
        $content = unserialize(file_get_contents($file));
        if ($content['expires'] < time()) {
            unlink($file);
            return null;
        }
        
        return $content['data'];
    }
}
```

**Redis Caching:**

```php
class RedisCache {
    private $redis;
    
    public function __construct() {
        $this->redis = new Redis();
        $this->redis->connect('127.0.0.1', 6379);
    }
    
    public function set($key, $data, $ttl = 3600) {
        $this->redis->setex($key, $ttl, serialize($data));
    }
    
    public function get($key) {
        $data = $this->redis->get($key);
        return $data ? unserialize($data) : null;
    }
}
```

### 5.2 Database Query Caching

```php
class QueryCache {
    private $cache;
    
    public function getCachedQuery($sql, $params = [], $ttl = 300) {
        $key = md5($sql . serialize($params));
        
        $result = $this->cache->get($key);
        if ($result !== null) {
            return $result;
        }
        
        $result = $this->db->query($sql, $params)->fetchAll();
        $this->cache->set($key, $result, $ttl);
        
        return $result;
    }
}
```

### 5.3 HTTP Caching

**Browser Caching Headers:**

```php
header('Cache-Control: public, max-age=31536000');
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');
```

**ETag Implementation:**

```php
$etag = md5(file_get_contents($file));
header('ETag: "' . $etag . '"');

if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && 
    trim($_SERVER['HTTP_IF_NONE_MATCH'], '"') == $etag) {
    header('HTTP/1.1 304 Not Modified');
    exit;
}
```

---

## 6. FRONTEND OPTIMIZATION

### 6.1 Minification

**CSS Minification:**

```bash
# Using cssnano
npm install -g cssnano-cli
cssnano input.css output.css
```

**JS Minification:**

```bash
# Using UglifyJS
npm install -g uglify-js
uglifyjs input.js -o output.js
```

### 6.2 Asset Optimization

**Image Optimization:**

```bash
# Using ImageMagick
convert input.jpg -quality 85 -strip output.jpg

# Using pngquant
pngquant input.png --output output.png
```

**Lazy Loading Images:**

```html
<img src="placeholder.jpg" data-src="actual.jpg" class="lazyload" alt="Image">
```

```javascript
document.addEventListener("DOMContentLoaded", function() {
    var lazyImages = [].slice.call(document.querySelectorAll("img.lazyload"));
    
    if ("IntersectionObserver" in window) {
        let lazyImageObserver = new IntersectionObserver(function(entries, observer) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    let lazyImage = entry.target;
                    lazyImage.src = lazyImage.dataset.src;
                    lazyImage.classList.remove("lazyload");
                    lazyImageObserver.unobserve(lazyImage);
                }
            });
        });
        
        lazyImages.forEach(function(lazyImage) {
            lazyImageObserver.observe(lazyImage);
        });
    }
});
```

### 6.3 CDN Integration

**CDN for Static Assets:**

```html
<!-- Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<!-- Font Awesome -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
```

---

## 7. DATABASE SCALING

### 7.1 Read Replicas

Implementasi read replicas untuk distribusi load baca:

```php
class Database {
    private $writeConnection;
    private $readConnections = [];
    
    public function query($sql, $params = []) {
        $isWrite = $this->isWriteQuery($sql);
        
        if ($isWrite) {
            return $this->writeConnection->query($sql, $params);
        } else {
            return $this->getReadConnection()->query($sql, $params);
        }
    }
}
```

### 7.2 Database Partitioning

Partition tabel besar berdasarkan tanggal:

```sql
ALTER TABLE bookings 
PARTITION BY RANGE (YEAR(booking_date)) (
    PARTITION p2023 VALUES LESS THAN (2024),
    PARTITION p2024 VALUES LESS THAN (2025),
    PARTITION p2025 VALUES LESS THAN (2026),
    PARTITION p_future VALUES LESS THAN MAXVALUE
);
```

### 7.3 Connection Pooling

Implementasi connection pooling untuk mengurangi overhead koneksi:

```php
class ConnectionPool {
    private $pool = [];
    private $maxConnections = 10;
    
    public function getConnection() {
        if (count($this->pool) > 0) {
            return array_pop($this->pool);
        }
        
        if (count($this->pool) < $this->maxConnections) {
            return $this->createConnection();
        }
        
        throw new Exception("Connection pool exhausted");
    }
    
    public function releaseConnection($connection) {
        array_push($this->pool, $connection);
    }
}
```

---

## 8. LOAD BALANCING

### 8.1 Nginx Load Balancer

```nginx
upstream backend {
    server 10.0.0.1:80;
    server 10.0.0.2:80;
    server 10.0.0.3:80;
}

server {
    listen 80;
    server_name tourguide.com;
    
    location / {
        proxy_pass http://backend;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }
}
```

### 8.2 Session Handling

**Redis Session Storage:**

```php
session_save_handler('redis');
session_save_path('tcp://127.0.0.1:6379');
```

**Sticky Sessions:**

```nginx
upstream backend {
    ip_hash;
    server 10.0.0.1:80;
    server 10.0.0.2:80;
}
```

---

## 9. MONITORING

### 9.1 Application Monitoring

**Performance Metrics:**

```php
class PerformanceMonitor {
    public function logRequest($uri, $duration) {
        $this->db->query(
            "INSERT INTO performance_logs (uri, duration, created_at) VALUES (?, ?, NOW())",
            [$uri, $duration]
        );
    }
    
    public function getSlowQueries($threshold = 1000) {
        return $this->db->query(
            "SELECT * FROM performance_logs WHERE duration > ? ORDER BY duration DESC LIMIT 100",
            [$threshold]
        )->fetchAll();
    }
}
```

### 9.2 Server Monitoring

**Using New Relic:**

```php
// Install New Relic PHP extension
// Configure newrelic.ini
newrelic.appname = "Tour Guide Application"
newrelic.license = "YOUR_LICENSE_KEY"
```

**Using Prometheus:**

```php
require 'vendor/autoload.php';
use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;

$registry = CollectorRegistry::getDefault();
$counter = $registry->getOrRegisterCounter(
    'app', 'requests_total', 'Total requests', ['method']
);
$counter->inc(['GET']);
```

---

## 10. PERFORMANCE CHECKLIST

### 10.1 Pre-Deployment Checklist

- [ ] OPcache enabled and configured
- [ ] Database indexes created
- [ ] Slow query log enabled
- [ ] Gzip compression enabled
- [ ] Browser caching configured
- [ ] CDN for static assets
- [ ] Images optimized
- [ ] CSS/JS minified
- [ ] Database connection pooling
- [ ] Caching layer implemented

### 10.2 Regular Maintenance

- [ ] Monitor slow query log weekly
- [ ] Review database indexes monthly
- [ ] Check cache hit ratio weekly
- [ ] Monitor server resources daily
- [ ] Review error logs daily
- [ ] Update dependencies monthly
- [ ] Performance testing quarterly

---

## 11. BENCHMARKING

### 11.1 Load Testing

**Using Apache Bench:**

```bash
ab -n 1000 -c 10 https://tourguide.com/
```

**Using JMeter:**

1. Create test plan
2. Add HTTP request sampler
3. Configure thread group (users, ramp-up)
4. Run test
5. Analyze results

### 11.2 Performance Targets

| Metric | Target |
|--------|--------|
| Page Load Time | < 2 seconds |
| Time to First Byte | < 200ms |
| Database Query Time | < 100ms |
| API Response Time | < 500ms |
| Concurrent Users | 1000+ |
| Uptime | 99.9% |

---

## 12. TROUBLESHOOTING PERFORMANCE

### 12.1 Slow Page Load

**Diagnosis:**
1. Check network tab in browser dev tools
2. Check slow query log
3. Check server resources (CPU, RAM, Disk I/O)
4. Check cache hit ratio

**Solutions:**
- Add database indexes
- Implement caching
- Optimize queries
- Scale horizontally
- Use CDN

### 12.2 High Memory Usage

**Diagnosis:**
1. Check PHP memory_limit
2. Check memory leaks in code
3. Check database buffer pool
4. Check number of concurrent connections

**Solutions:**
- Increase memory_limit
- Fix memory leaks
- Optimize database configuration
- Implement connection pooling
- Scale vertically

### 12.3 Database Locking

**Diagnosis:**
1. Check SHOW PROCESSLIST
2. Check InnoDB status
3. Check long-running transactions

**Solutions:**
- Optimize long-running queries
- Use row-level locking
- Implement read replicas
- Partition large tables
- Use queue for heavy operations

---

## 13. BEST PRACTICES

### 13.1 Code Level

- Use prepared statements
- Avoid N+1 queries
- Implement caching
- Use lazy loading
- Optimize loops
- Minimize database calls

### 13.2 Database Level

- Create appropriate indexes
- Use EXPLAIN for query analysis
- Optimize JOINs
- Use LIMIT for large result sets
- Partition large tables
- Regular maintenance (OPTIMIZE TABLE)

### 13.3 Server Level

- Enable compression
- Configure caching headers
- Use CDN
- Monitor resources
- Regular backups
- Security updates

---

## 14. RESOURCES

### 14.1 Tools

- **New Relic**: APM monitoring
- **Prometheus**: Metrics collection
- **Grafana**: Visualization
- **JMeter**: Load testing
- **Apache Bench**: Simple load testing
- **Blackfire**: PHP profiling

### 14.2 Documentation

- PHP Performance: https://www.php.net/manual/en/ini.core.php
- MySQL Optimization: https://dev.mysql.com/doc/refman/8.0/en/optimization.html
- Apache Performance: https://httpd.apache.org/docs/2.4/misc/perf-tuning.html
- Nginx Optimization: https://www.nginx.com/blog/tuning-nginx/

---

> **Modul Selanjutnya:** `38_TESTING_GUIDE.md`
