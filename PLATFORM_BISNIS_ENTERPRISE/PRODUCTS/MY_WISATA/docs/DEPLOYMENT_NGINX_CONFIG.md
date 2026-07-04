# Nginx Configuration - MyWisata Application

> **Versi:** 1.0 · **Tanggal:** 2026-07-01

---

## 1. Install Nginx and PHP-FPM

### 1.1 Install Nginx

```bash
sudo apt install nginx -y
```

### 1.2 Install PHP-FPM

```bash
sudo apt install php8.1-fpm php8.1-mysql php8.1-curl php8.1-gd php8.1-mbstring php8.1-xml php8.1-zip -y
```

### 1.3 Start Services

```bash
sudo systemctl start nginx
sudo systemctl start php8.1-fpm
sudo systemctl enable nginx
sudo systemctl enable php8.1-fpm
```

---

## 2. Configure PHP-FPM

### 2.1 Edit PHP-FPM Configuration

```bash
sudo nano /etc/php/8.1/fpm/pool.d/www.conf
```

Update settings:

```ini
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 500
```

### 2.2 Restart PHP-FPM

```bash
sudo systemctl restart php8.1-fpm
```

---

## 3. Configure Nginx

### 3.1 Create Nginx Configuration

```bash
sudo nano /etc/nginx/sites-available/mywisata
```

```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/mywisata;
    index index.php index.html;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    # Logging
    access_log /var/log/nginx/mywisata_access.log;
    error_log /var/log/nginx/mywisata_error.log;

    # Main location block
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP processing
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Deny access to hidden files
    location ~ /\. {
        deny all;
    }

    # Deny access to sensitive files
    location ~ /(\.env|\.git|composer\.json|composer\.lock|package\.json|package-lock\.json)$ {
        deny all;
    }

    # Deny access to config files
    location ~ /app/config/ {
        deny all;
    }

    # Deny access to helpers
    location ~ /app/helpers/ {
        deny all;
    }

    # Deny access to core files
    location ~ /app/core/ {
        deny all;
    }

    # Deny access to middleware
    location ~ /app/middleware/ {
        deny all;
    }

    # Deny access to models
    location ~ /app/models/ {
        deny all;
    }

    # Deny access to controllers
    location ~ /app/controllers/ {
        deny all;
    }

    # Deny access to cache
    location ~ /cache/ {
        deny all;
    }

    # Deny access to logs
    location ~ /logs/ {
        deny all;
    }

    # Deny access to backups
    location ~ /backups/ {
        deny all;
    }

    # Deny access to tests
    location ~ /tests/ {
        deny all;
    }

    # Deny access to docs
    location ~ /docs/ {
        deny all;
    }

    # Deny access to prompting
    location ~ /prompting/ {
        deny all;
    }

    # Deny access to .devin
    location ~ /.devin/ {
        deny all;
    }

    # Allow access to uploads
    location /uploads/ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }

    # Allow access to assets
    location /assets/ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml text/javascript application/json application/javascript application/xml+rss application/rss+xml font/truetype font/opentype application/vnd.ms-fontobject image/svg+xml;
}
```

### 3.2 Enable Site

```bash
sudo ln -s /etc/nginx/sites-available/mywisata /etc/nginx/sites-enabled/
sudo rm /etc/nginx/sites-enabled/default
sudo nginx -t
sudo systemctl restart nginx
```

---

## 4. SSL Configuration with Let's Encrypt

### 4.1 Install Certbot

```bash
sudo apt install certbot python3-certbot-nginx -y
```

### 4.2 Obtain SSL Certificate

```bash
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

### 4.3 Auto-Renewal

```bash
sudo certbot renew --dry-run
```

### 4.4 Update Nginx Config for SSL

Certbot will automatically update the configuration. The final config will look like:

```nginx
server {
    listen 443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;
    
    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;
    
    # ... rest of the configuration
}

server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    return 301 https://$server_name$request_uri;
}
```

---

## 5. Performance Optimization

### 5.1 Enable FastCGI Cache

Add to nginx.conf:

```nginx
fastcgi_cache_path /var/cache/nginx levels=1:2 keys_zone=mywisata:100m inactive=60m;
fastcgi_cache_key "$scheme$request_method$host$request_uri";
```

Add to server block:

```nginx
set $skip_cache 0;

# Skip cache for POST requests
if ($request_method = POST) {
    set $skip_cache 1;
}

# Skip cache for logged-in users
if ($http_cookie ~* "session_id") {
    set $skip_cache 1;
}

# Skip cache for specific pages
if ($request_uri ~* "/(admin|auth|profile|booking)") {
    set $skip_cache 1;
}

location ~ \.php$ {
    fastcgi_cache mywisata;
    fastcgi_cache_valid 200 60m;
    fastcgi_cache_bypass $skip_cache;
    fastcgi_no_cache $skip_cache;
    add_header X-Cache-Status $upstream_cache_status;
    
    # ... rest of PHP configuration
}
```

### 5.2 Enable Browser Caching

Add to server block:

```nginx
location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
}
```

### 5.3 Enable Gzip Compression

Already included in the main configuration above.

---

## 6. Security Hardening

### 6.1 Rate Limiting

Add to http block in nginx.conf:

```nginx
limit_req_zone $binary_remote_addr zone=general:10m rate=10r/s;
limit_req_zone $binary_remote_addr zone=api:10m rate=30r/m;
```

Add to server block:

```nginx
# Apply rate limiting to API endpoints
location /api/ {
    limit_req zone=api burst=5 nodelay;
    # ... rest of location configuration
}

# Apply rate limiting to login
location /auth/login {
    limit_req zone=general burst=5 nodelay;
    # ... rest of location configuration
}
```

### 6.2 Block Bad Bots

Add to server block:

```nginx
if ($http_user_agent ~* (bot|crawl|spider|scraper)) {
    return 403;
}
```

### 6.3 Disable Server Tokens

Add to http block:

```nginx
server_tokens off;
```

---

## 7. Monitoring

### 7.1 Enable Nginx Status

Add to server block:

```nginx
location /nginx_status {
    stub_status on;
    access_log off;
    allow 127.0.0.1;
    deny all;
}
```

### 7.2 Monitor with htop

```bash
sudo apt install htop -y
htop
```

### 7.3 Monitor Log Files

```bash
sudo tail -f /var/log/nginx/mywisata_access.log
sudo tail -f /var/log/nginx/mywisata_error.log
```

---

## 8. Troubleshooting

### 8.1 Check Nginx Status

```bash
sudo systemctl status nginx
```

### 8.2 Check PHP-FPM Status

```bash
sudo systemctl status php8.1-fpm
```

### 8.3 Test Configuration

```bash
sudo nginx -t
```

### 8.4 Reload Nginx

```bash
sudo systemctl reload nginx
```

### 8.5 Restart Nginx

```bash
sudo systemctl restart nginx
```

### 8.6 Check PHP-FPM Logs

```bash
sudo tail -f /var/log/php8.1-fpm.log
```

---

## 9. Comparison: Apache vs Nginx

| Feature | Apache | Nginx |
|---------|--------|-------|
| Performance | Good | Excellent |
| Memory Usage | Higher | Lower |
| Concurrency | Process-based | Event-based |
| Configuration | .htaccess per directory | Centralized |
| PHP Processing | mod_php | PHP-FPM |
| Static Files | Good | Excellent |
| SSL | Good | Excellent |
| Caching | mod_cache | FastCGI cache |

---

## 10. Migration from Apache to Nginx

If migrating from Apache:

1. Backup Apache configuration
2. Install Nginx and PHP-FPM
3. Convert .htaccess rules to Nginx config
4. Test Nginx configuration
5. Switch DNS to Nginx
6. Monitor for issues

---

> **Dokumen selesai.** Nginx configuration untuk MyWisata Application.
