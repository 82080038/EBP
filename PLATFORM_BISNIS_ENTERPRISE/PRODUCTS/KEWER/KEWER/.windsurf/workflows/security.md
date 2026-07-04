---
description: Security improvements dan maintenance untuk aplikasi Kewer v2.5.0
---

# Security (v2.5.0)

## API Security

### Authentication
- **Session-based**: Untuk web application
- **API Token**: Untuk integrasi eksternal (Bearer token: kewer-api-token-2024)
- Token harus disimpan di environment variable, bukan hardcoded

### Authorization
- Role-based access control (RBAC) diterapkan di API level
- Permissions checked sebelum data access
- User hanya dapat akses data sesuai role dan cabang

### Input Validation
- Semua input divalidasi di server-side
- Gunakan prepared statements untuk SQL queries
- Sanitasi output untuk mencegah XSS

### Rate Limiting
- API rate limiting: 60 requests/minute
- Session timeout: 2 jam inactivity
- Failed login attempt limiting

## Client-Side Security

### XSS Prevention
- Escape semua user input sebelum render ke DOM
- Gunakan jQuery text() bukan html() untuk user content
- Validasi data dari API sebelum display

### CSRF Protection
- CSRF token untuk form submissions
- Verify token pada server-side
- Token regenerate per request

### Sensitive Data
- Jangan simpan sensitive data di localStorage
- Gunakan secure cookies
- Encrypt sensitive data di transit (HTTPS)

## Security Testing Checklist

### API Security
- [ ] Test authentication dengan invalid token
- [ ] Test authorization dengan different roles
- [ ] Test rate limiting
- [ ] Test SQL injection attempts
- [ ] Test XSS attempts

### Client-Side Security
- [ ] Verify no sensitive data in localStorage
- [ ] Check XSS vulnerabilities
- [ ] Verify CSRF protection
- [ ] Check for exposed API keys

## Legacy Security Workflow (Optional)

### 1. Security Setup Checklist
- [ ] CSRF protection telah diimplementasi di semua forms
- [ ] Session timeout telah dikonfigurasi (2 jam)
- [ ] Error handling global telah diimplementasi
- [ ] Rate limiting telah diimplementasi untuk API
- [ ] Input validation layer telah diimplementasi
- [ ] Permission check consistency telah diperbaiki
- [ ] Database indexes telah ditambahkan untuk performa

### 2. Security Testing

#### Test CSRF Protection
```bash
# Buka form dan coba submit tanpa CSRF token
# Harus mendapatkan error 403
curl -X POST http://localhost/kewer/pages/nasabah/tambah.php \
  -d "nama=test" \
  -H "Content-Type: application/x-www-form-urlencoded"
# Expected: 403 Forbidden atau error CSRF
```

#### Test Session Timeout
```bash
# Login dan tunggu 2 jam (atau ubah SESSION_LIFETIME di .env untuk testing lebih cepat)
# Setelah timeout, coba akses halaman yang butuh login
# Expected: Redirect ke login dengan pesan timeout
```

#### Test Rate Limiting
```bash
# Kirim lebih dari 60 request dalam 1 menit ke API
for i in {1..70}; do
  curl -H "Authorization: Bearer kewer-api-token-2024" \
    "http://localhost/kewer/api/alamat?action=provinces"
done
# Expected: Setelah 60 requests, mendapatkan 429 Too Many Requests
```

#### Test Permission Checks
```bash
# Login sebagai user dengan role rendah
# Coba akses halaman yang butuh permission lebih tinggi
# Expected: Redirect ke dashboard
```

### 3. Security Monitoring

#### Cek Error Logs
```bash
# Lihat error logs terbaru
tail -f /opt/lampp/htdocs/kewer/logs/error.log
```

#### Cek Application Logs
```bash
# Lihat application logs
tail -f /opt/lampp/htdocs/kewer/logs/app.log
```

#### Cek Audit Logs
```bash
# Lihat audit trail terbaru
echo "8208" | sudo -S /opt/lampp/bin/mysql -u root -proot kewer -e "
SELECT * FROM audit_log 
ORDER BY created_at DESC 
LIMIT 20;
"
```

### 4. Security Maintenance

#### Update Password untuk Production
```bash
# Ganti default passwords untuk production
# Gunakan password yang kuat (minimal 12 karakter, kombinasi huruf, angka, simbol)
# Update di database atau melalui UI user management
```

#### Update Environment Variables untuk Production
```bash
# Ubah APP_ENV dari development ke production di .env
# Matikan quick login di production
APP_ENV=production
```

#### Review Security Headers
```bash
# Cek security headers di .htaccess
# Pastikan headers berikut ada:
# - X-Frame-Options: DENY
# - X-Content-Type-Options: nosniff
# - X-XSS-Protection: 1; mode=block
# - Content-Security-Policy (opsional)
```

### 5. Database Security

#### Backup Database Sebelum Perubahan
```bash
cd /opt/lampp/htdocs/kewer
/opt/lampp/bin/mysqldump -u root -proot --routines --triggers kewer > database/kewer.sql
/opt/lampp/bin/mysqldump -u root -proot --routines --triggers db_alamat_simple > database/db_alamat_simple.sql
/opt/lampp/bin/mysqldump -u root -proot --routines --triggers db_orang > database/db_orang.sql
```

#### Cek Database Integrity
```bash
# Cek semua 3 database
/opt/lampp/bin/mysql -u root -proot -e "
SELECT 'kewer' as db, COUNT(*) as tables FROM information_schema.TABLES WHERE TABLE_SCHEMA='kewer'
UNION ALL SELECT 'db_alamat_simple', COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA='db_alamat_simple'
UNION ALL SELECT 'db_orang', COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA='db_orang';
"
```

### 6. API Security

#### Test API Authentication
```bash
# Test API tanpa token (harus gagal)
curl "http://localhost/kewer/api/alamat?action=provinces"
# Expected: 401 Unauthorized

# Test API dengan token (harus sukses)
curl -H "Authorization: Bearer kewer-api-token-2024" \
  "http://localhost/kewer/api/alamat?action=provinces"
# Expected: 200 OK dengan data
```

#### Test API Response Format
```bash
# Pastikan semua API response format konsisten
curl -H "Authorization: Bearer kewer-api-token-2024" \
  "http://localhost/kewer/api/alamat?action=provinces" | jq
# Expected: { "success": true, "data": [...] }
```

### 7. File Upload Security

#### Test File Upload Validation
```bash
# Coba upload file yang tidak diizinkan (misal: .exe, .php)
# Harus ditolak
```

#### Cek Upload Directory Permissions
```bash
# Pastikan upload directory tidak bisa dieksekusi
echo "8208" | sudo -S chmod -R 755 /opt/lampp/htdocs/kewer/uploads
echo "8208" | sudo -S chown -R daemon:daemon /opt/lampp/htdocs/kewer/uploads
```

### 8. Security Best Practices

#### Regular Password Changes
- Ganti password admin setiap 3 bulan
- Ganti password database setiap 6 bulan
- Gunakan password yang berbeda untuk setiap service

#### Regular Security Audits
- Review audit logs mingguan
- Review error logs harian
- Cek user access bulanan
- Review permission assignments bulanan

#### Keep Software Updated
- Update PHP ke versi terbaru yang support
- Update MySQL/MariaDB ke versi terbaru
- Update library dependencies (composer)

### 9. Security Incident Response

#### Jika Terdeteksi Security Breach
1. Segera matikan akses aplikasi
2. Backup database saat ini
3. Cek audit logs untuk aktivitas mencurigakan
4. Reset semua user passwords
5. Perbaiki vulnerability yang ditemukan
6. Restore dari backup yang bersih jika perlu
7. Dokumentasikan incident dan pembelajaran

### 10. Security Checklist untuk Deployment

Sebelum deploy ke production:
- [ ] APP_ENV diubah ke production
- [ ] Quick login dimatikan
- [ ] Default passwords diganti
- [ ] Database backup terbaru dibuat
- [ ] Security headers dikonfigurasi
- [ ] File upload permissions di-set dengan benar
- [ ] Error logging diaktifkan
- [ ] Rate limiting diaktifkan
- [ ] SSL/TLS diaktifkan (jika HTTPS)
- [ ] Firewall dikonfigurasi
- [ ] Regular backup schedule di-set
