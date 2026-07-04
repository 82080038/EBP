# SSL Setup with Let's Encrypt - MyWisata Application

> **Versi:** 1.0 · **Tanggal:** 2026-07-01

---

## 1. Prerequisites

- Domain name pointing to your VPS IP
- Apache or Nginx installed and configured
- Root or sudo access to VPS

---

## 2. Install Certbot

### 2.1 For Apache

```bash
sudo apt update
sudo apt install certbot python3-certbot-apache -y
```

### 2.2 For Nginx

```bash
sudo apt update
sudo apt install certbot python3-certbot-nginx -y
```

---

## 3. Obtain SSL Certificate

### 3.1 For Apache

```bash
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com
```

### 3.2 For Nginx

```bash
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

### 3.3 Interactive Questions

Certbot will ask:
1. **Email address** - Enter your email for renewal notices
2. **Terms of Service** - Agree (A)
3. **Newsletter** - Optional (N)
4. **Redirect HTTP to HTTPS** - Choose option 2 (Redirect)

---

## 4. Verify SSL Installation

### 4.1 Check Certificate

```bash
sudo certbot certificates
```

### 4.2 Test HTTPS Access

```bash
curl -I https://yourdomain.com
```

### 4.3 SSL Labs Test

Visit: https://www.ssllabs.com/ssltest/

---

## 5. Auto-Renewal Setup

### 5.1 Test Renewal

```bash
sudo certbot renew --dry-run
```

### 5.2 Verify Cron Job

Certbot automatically creates a cron job. Verify:

```bash
sudo systemctl list-timers
```

Or check:

```bash
sudo crontab -l
```

You should see something like:

```
0 0,12 * * * root certbot -q renew --deploy-hook "systemctl reload apache2"
```

### 5.3 Manual Renewal

```bash
sudo certbot renew
```

### 5.4 Force Renewal

```bash
sudo certbot renew --force-renewal
```

---

## 6. Advanced SSL Configuration

### 6.1 Configure Strong SSL Settings

#### For Apache

Edit `/etc/apache2/sites-available/mywisata-le-ssl.conf`:

```apache
<IfModule mod_ssl.c>
    <VirtualHost *:443>
        ServerName yourdomain.com
        ServerAlias www.yourdomain.com
        DocumentRoot /var/www/mywisata
        
        # SSL Configuration
        SSLEngine on
        SSLCertificateFile /etc/letsencrypt/live/yourdomain.com/fullchain.pem
        SSLCertificateKeyFile /etc/letsencrypt/live/yourdomain.com/privkey.pem
        SSLCertificateChainFile /etc/letsencrypt/live/yourdomain.com/chain.pem
        
        # SSL Protocols
        SSLProtocol all -SSLv2 -SSLv3 -TLSv1 -TLSv1.1
        
        # SSL Ciphers
        SSLCipherSuite HIGH:!aNULL:!MD5:!3DES
        SSLHonorCipherOrder on
        
        # SSL Session
        SSLSessionCache shared:SSL:10m
        SSLSessionTimeout 10m
        
        # HSTS
        Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains; preload"
        
        # Security Headers
        Header always set X-Frame-Options "SAMEORIGIN"
        Header always set X-Content-Type-Options "nosniff"
        Header always set X-XSS-Protection "1; mode=block"
        Header always set Referrer-Policy "strict-origin-when-cross-origin"
        
        # ... rest of your configuration
    </VirtualHost>
</IfModule>
```

#### For Nginx

Edit `/etc/nginx/sites-available/mywisata`:

```nginx
server {
    listen 443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;
    
    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;
    
    # SSL Protocols
    ssl_protocols TLSv1.2 TLSv1.3;
    
    # SSL Ciphers
    ssl_ciphers 'ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384';
    ssl_prefer_server_ciphers on;
    
    # SSL Session
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;
    
    # HSTS
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;
    
    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    
    # ... rest of your configuration
}
```

### 6.2 Restart Web Server

#### Apache

```bash
sudo systemctl restart apache2
```

#### Nginx

```bash
sudo nginx -t
sudo systemctl restart nginx
```

---

## 7. HTTP to HTTPS Redirect

### 7.1 Apache

Certbot automatically creates the redirect. Verify in config:

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    Redirect permanent / https://yourdomain.com/
</VirtualHost>
```

### 7.2 Nginx

Certbot automatically creates the redirect. Verify in config:

```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    return 301 https://$server_name$request_uri;
}
```

---

## 8. Multi-Domain SSL

### 8.1 Add Additional Domain

```bash
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com -d subdomain.yourdomain.com
```

### 8.2 Wildcard Certificate

```bash
sudo certbot certonly --manual --preferred-challenges dns -d "*.yourdomain.com" -d "yourdomain.com"
```

This requires DNS TXT record validation.

---

## 9. Troubleshooting

### 9.1 Certificate Not Found

```bash
sudo certbot certificates
```

If not found, re-run the installation command.

### 9.2 Renewal Failed

```bash
sudo certbot renew --dry-run
```

Check logs:

```bash
sudo journalctl -u certbot.timer
```

### 9.3 Mixed Content Warning

Ensure all resources use HTTPS:
- Update image URLs
- Update script URLs
- Update CSS URLs
- Update API endpoints

### 9.4 HSTS Preload

To add to HSTS preload list:
1. Ensure HSTS header is set with `preload` directive
2. Submit to: https://hstspreload.org/

### 9.5 Certificate Expired

```bash
sudo certbot renew --force-renewal
sudo systemctl reload apache2
# or
sudo systemctl reload nginx
```

---

## 10. SSL Best Practices

### 10.1 Use Strong Ciphers

Only allow TLS 1.2 and TLS 1.3

### 10.2 Enable HSTS

Force HTTPS for all future requests

### 10.3 Regular Renewal

Certbot auto-renews, but monitor logs

### 10.4 Monitor Expiry

Set up alerts 30 days before expiry

### 10.5 Use OCSP Stapling

Improves SSL handshake performance

#### Apache

```apache
SSLUseStapling on
SSLStaplingCache "shmcb:logs/ssl_stapling(32768)" 
```

#### Nginx

```nginx
ssl_stapling on;
ssl_stapling_verify on;
ssl_trusted_certificate /etc/letsencrypt/live/yourdomain.com/chain.pem;
```

---

## 11. SSL Monitoring

### 11.1 Check Certificate Expiry

```bash
echo | openssl s_client -servername yourdomain.com -connect yourdomain.com:443 2>/dev/null | openssl x509 -noout -dates
```

### 11.2 SSL Labs Grade

Test regularly: https://www.ssllabs.com/ssltest/

Target: A+ grade

### 11.3 Certificate Transparency

Check: https://crt.sh/?q=yourdomain.com

---

## 12. Backup SSL Certificates

### 12.1 Backup Directory

```bash
sudo tar -czf letsencrypt-backup-$(date +%Y%m%d).tar.gz /etc/letsencrypt
```

### 12.2 Restore

```bash
sudo tar -xzf letsencrypt-backup-YYYYMMDD.tar.gz -C /
```

---

## 13. Security Checklist

- [ ] SSL certificate installed
- [ ] HTTP redirects to HTTPS
- [ ] HSTS enabled
- [ ] Security headers configured
- [ ] Strong ciphers configured
- [ ] Auto-renewal working
- [ ] Certificate expiry monitored
- [ ] SSL Labs test passed (A+)
- [ ] Mixed content resolved
- [ ] OCSP stapling enabled (optional)

---

## 14. Resources

- Let's Encrypt: https://letsencrypt.org/
- Certbot Documentation: https://certbot.eff.org/docs/
- SSL Labs: https://www.ssllabs.com/ssltest/
- HSTS Preload: https://hstspreload.org/
- Mozilla SSL Config Generator: https://ssl-config.mozilla.org/

---

> **Dokumen selesai.** SSL setup guide untuk MyWisata Application.
