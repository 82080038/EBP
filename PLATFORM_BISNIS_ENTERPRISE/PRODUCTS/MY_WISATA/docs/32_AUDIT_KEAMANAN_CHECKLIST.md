# MODUL 32 — AUDIT KEAMANAN CHECKLIST (OWASP-BASED)

> **Aplikasi:** Tour Guide Application  
> **Versi:** 1.0  
> **Tanggal:** 2026-06-30  
> **Referensi:** OWASP Top 10 (2021) + OWASP ASVS Level 1

---

## 1. RINGKASAN

Dokumen ini menyediakan checklist audit keamanan komprehensif berbasis
**OWASP Top 10 (2021)** dan **OWASP Application Security Verification Standard
(ASVS) Level 1**. Digunakan oleh developer dan security auditor untuk
memverifikasi bahwa aplikasi memenuhi standar keamanan minimum sebelum
production release.

---

## 2. OWASP TOP 10 (2021) — CHECKLIST

### A01: Broken Access Control

| ID | Kontrol | Status | Implementasi |
|----|---------|--------|--------------|
| AC-01 | Setiap controller memiliki middleware role check | [x] | `Middleware::requireRole()` di constructor |
| AC-02 | User hanya bisa akses data miliknya (own data) | [ ] | Cek `user_id` di setiap query |
| AC-03 | Admin-only endpoints terproteksi | [x] | `Middleware::requireRole('admin')` |
| AC-04 | IDOR dicegah (tidak bisa akses data orang lain via ID) | [ ] | Validasi ownership di controller |
| AC-05 | API endpoints memiliki autentikasi | [x] | Session check di `ApiController` |
| AC-06 | Direct object reference dicegah | [ ] | Cek `booking.user_id === session.user_id` |
| AC-07 | Default deny (deny by default, allow by exception) | [x] | Middleware menolak jika tidak match |
| AC-08 | Privilege escalation dicegah | [x] | Role tidak bisa diubah via form biasa |

### A02: Cryptographic Failures

| ID | Kontrol | Status | Implementasi |
|----|---------|--------|--------------|
| CR-01 | Password di-hash dengan bcrypt | [x] | `password_hash($pass, PASSWORD_BCRYPT)` |
| CR-02 | Password tidak pernah dikirim plain text | [ ] | HTTPS di production |
| CR-03 | Password tidak pernah di-log | [x] | Logger tidak mencatat field password |
| CR-04 | Session ID di-regenerate setelah login | [x] | `session_regenerate_id(true)` |
| CR-05 | Session cookie: HttpOnly + Secure + SameSite | [x] | `session_set_cookie_params()` |
| CR-06 | CSRF token di-generate dengan `random_bytes()` | [x] | `bin2hex(random_bytes(32))` |
| CR-07 | API keys / secrets tidak hardcoded | [x] | Di config file, tidak di git |
| CR-08 | File upload: nama file di-randomize | [x] | `bin2hex(random_bytes(16))` |

### A03: Injection

| ID | Kontrol | Status | Implementasi |
|----|---------|--------|--------------|
| IN-01 | Semua query menggunakan PDO prepared statements | [x] | `$db->query($sql, $params)` |
| IN-02 | Tidak ada string concatenation di query SQL | [x] | Code review: grep `$sql.*\$` |
| IN-03 | Input user tidak pernah dieksekusi sebagai code | [x] | Tidak ada `eval()` / `exec()` dengan input |
| IN-04 | Order by / limit tidak dari input langsung | [ ] | Whitelist kolom sort |
| IN-05 | LIKE query di-escape wildcard | [ ] | `addcslashes($input, '%_')` |
| IN-06 | Command injection dicegah | [x] | `escapeshellarg()` di BackupController |

### A04: Insecure Design

| ID | Kontrol | Status | Implementasi |
|----|---------|--------|--------------|
| DS-01 | Threat modeling dilakukan untuk flow kritis | [x] | Booking, payment, file upload |
| DS-02 | Rate limiting di endpoint API | [x] | `RateLimiter::check()` 60 req/menit |
| DS-03 | Rate limiting di endpoint auth (login) | [x] | 5 attempt per menit |
| DS-04 | Account lockout setelah N failed login | [ ] | 5x gagal → lock 15 menit |
| DS-05 | Password minimum 8 karakter | [x] | Validator: `min:8` |
| DS-06 | Password complexity (huruf + angka) | [ ] | Validator: regex check |
| DS-07 | Confirm password di registrasi | [x] | Field `password_confirmation` |
| DS-08 | Email verification (opsional) | [ ] | Token verifikasi via email |

### A05: Security Misconfiguration

| ID | Kontrol | Status | Implementasi |
|----|---------|--------|--------------|
| MC-01 | `display_errors = Off` di production | [x] | `APP_DEBUG = false` |
| MC-02 | `error_reporting(0)` di production | [x] | Di `App::run()` |
| MC-03 | Error log ditulis ke file, tidak tampil | [x] | `ini_set('log_errors', 1)` |
| MC-04 | Directory listing disabled | [x] | `.htaccess: Options -Indexes` |
| MC-05 | File sensitif tidak accessible | [x] | `.htaccess: deny .sql, .md, .log, .env` |
| MC-06 | Folder `app/` tidak directly accessible | [x] | `.htaccess: Deny from all` |
| MC-07 | Default credentials diganti | [ ] | Admin password diubah setelah install |
| MC-08 | XAMPP default page dihapus di production | [ ] | Hapus `/opt/lampp/htdocs/xampp/` |
| MC-09 | `app/config/config.local.php` di .gitignore | [x] | Tidak di-commit |
| MC-10 | Session timeout 30 menit | [x] | `Session::checkTimeout()` |

### A06: Vulnerable and Outdated Components

| ID | Kontrol | Status | Implementasi |
|----|---------|--------|--------------|
| VC-01 | PHP version 8.1+ (latest patch) | [x] | PHP 8.2.12 |
| VC-02 | MySQL version 8.0+ | [x] | MySQL 8.0+ |
| VC-03 | Bootstrap 5.3.x (latest) | [x] | CDN link check |
| VC-04 | jQuery 3.7.x (latest) | [x] | CDN link check |
| VC-05 | Leaflet 1.9.x (latest) | [x] | CDN link check |
| VC-06 | SweetAlert2 11.x (latest) | [x] | CDN link check |
| VC-07 | Composer dependencies updated | [x] | Tidak menggunakan composer |
| VC-08 | No known CVE di dependencies | [x] | Check GitHub advisories |

### A07: Identification and Authentication Failures

| ID | Kontrol | Status | Implementasi |
|----|---------|--------|--------------|
| AU-01 | Login menggunakan email + password | [x] | `AuthController::login()` |
| AU-02 | Password diverifikasi dengan `password_verify()` | [x] | Tidak pernah compare plain text |
| AU-03 | Session di-destroy saat logout | [x] | `session_destroy()` + cookie clear |
| AU-04 | Session ID di-regenerate setelah login | [x] | `session_regenerate_id(true)` |
| AU-05 | "Remember me" tidak menyimpan password | [x] | Token random, bukan password |
| AU-06 | Forgot password via token (bukan kirim password) | [ ] | Token expire 1 jam |
| AU-07 | Failed login tidak reveal info (user ada/tidak) | [x] | Pesan: "Email atau password salah" |
| AU-08 | Concurrent session dicegah (opsional) | [ ] | Track session_id di DB |

### A08: Software and Data Integrity Failures

| ID | Kontrol | Status | Implementasi |
|----|---------|--------|--------------|
| SI-01 | CSRF token di semua form POST | [x] | `<input type="hidden" name="csrf_token">` |
| SI-02 | CSRF token di semua AJAX POST | [x] | Header `X-CSRF-Token` |
| SI-03 | CSRF token di-verify di controller | [x] | `Middleware::verifyCsrf()` |
| SI-04 | CSRF token di-regenerate per session | [x] | Saat login |
| SI-05 | File upload: MIME type di-verify | [x] | `finfo_file()` check |
| SI-06 | File upload: extension di-verify | [x] | Whitelist extension |
| SI-07 | File upload: size di-verify | [x] | Max 5MB (`MAX_UPLOAD_SIZE`) |
| SI-08 | File upload: stored di luar webroot (atau protected) | [x] | `public/uploads/` dengan `.htaccess` |

### A09: Security Logging and Monitoring Failures

| ID | Kontrol | Status | Implementasi |
|----|---------|--------|--------------|
| LM-01 | Login berhasil di-log | [x] | `Logger::audit('login', 'auth', ...)` |
| LM-02 | Login gagal di-log | [x] | `Logger::audit('login_failed', 'auth', ...)` |
| LM-03 | Logout di-log | [x] | `Logger::audit('logout', 'auth', ...)` |
| LM-04 | Create/Update/Delete di-log | [x] | `Logger::audit('create', 'module', ...)` |
| LM-05 | Backup/Restore di-log | [x] | `Logger::audit('backup', 'database', ...)` |
| LM-06 | Error di-log ke `logs/error.log` | [x] | `Logger::error()` |
| LM-07 | Audit log retention 90 hari | [ ] | Cron cleanup |
| LM-08 | Log berisi: user_id, action, module, IP, timestamp | [x] | `audit_logs` table |
| LM-09 | Log tidak berisi data sensitif (password, token) | [x] | Filter di Logger |

### A10: Server-Side Request Forgery (SSRF)

| ID | Kontrol | Status | Implementasi |
|----|---------|--------|--------------|
| SF-01 | Tidak ada fetch URL dari input user | [x] | Tidak ada `file_get_contents($_GET)` |
| SF-02 | QR code API: URL hardcoded/whitelist | [x] | Hanya `api.qrserver.com` atau local |
| SF-03 | Curl: target URL tidak dari input user | [x] | Hardcoded endpoint |
| SF-04 | Internal IP/localhost tidak accessible via SSRF | [x] | N/A jika no URL fetch |

---

## 3. OWASP ASVS LEVEL 1 — VERIFIKASI TAMBAHAN

### 3.1 Architecture & Threat Modeling

| ID | Kontrol | Status |
|----|---------|--------|
| AS-01 | Komunikasi sensitive via HTTPS | [ ] |
| AS-02 | Defense in depth (multiple layers) | [x] |
| AS-03 | Separation of concerns (MVC) | [x] |
| AS-04 | Input validation di server-side (tidak hanya client) | [x] |

### 3.2 Authentication

| ID | Kontrol | Status |
|----|---------|--------|
| AS-05 | Password hash cost >= 10 (bcrypt) | [x] |
| AS-06 | Session timeout server-side | [x] |
| AS-07 | Re-authentication untuk aksi sensitif (opsional) | [ ] |

### 3.3 Session Management

| ID | Kontrol | Status |
|----|---------|--------|
| AS-08 | Session ID tidak di URL | [x] |
| AS-09 | Session cookie: HttpOnly | [x] |
| AS-10 | Session cookie: Secure (HTTPS) | [ ] |
| AS-11 | Session cookie: SameSite=Lax atau Strict | [x] |
| AS-12 | Logout menghapus session server-side | [x] |

### 3.4 Access Control

| ID | Kontrol | Status |
|----|---------|--------|
| AS-13 | Authorization check di server-side | [x] |
| AS-14 | Default deny untuk semua endpoint | [x] |
| AS-15 | Role check tidak hanya di frontend | [x] |

### 3.5 Input Validation

| ID | Kontrol | Status |
|----|---------|--------|
| AS-16 | Semua input divalidasi (type, length, format) | [x] |
| AS-17 | Whitelist approach (bukan blacklist) | [x] |
| AS-18 | Negative numbers dicegah where applicable | [x] |

### 3.6 Output Encoding

| ID | Kontrol | Status |
|----|---------|--------|
| AS-19 | HTML output di-escape (htmlspecialchars) | [x] |
| AS-20 | JSON output di-encode (json_encode) | [x] |
| AS-21 | SQL output menggunakan prepared statements | [x] |
| AS-22 | JavaScript output di-escape (JSON.stringify) | [x] |
| AS-23 | URL output di-encode (urlencode) | [x] |

---

## 4. SECURITY HEADERS CHECKLIST

| Header | Nilai | Status |
|--------|-------|--------|
| `X-Content-Type-Options` | `nosniff` | [x] |
| `X-Frame-Options` | `SAMEORIGIN` | [x] |
| `X-XSS-Protection` | `1; mode=block` | [x] |
| `Referrer-Policy` | `strict-origin-when-cross-origin` | [x] |
| `Strict-Transport-Security` | `max-age=31536000; includeSubDomains` | [ ] |
| `Content-Security-Policy` | `default-src 'self'` (opsional) | [x] |
| `Permissions-Policy` | `geolocation=(self), camera=(), microphone=()` | [ ] |

Implementasi via `.htaccess`:
```apache
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
    Header set Referrer-Policy "strict-origin-when-cross-origin"
    Header set Strict-Transport-Security "max-age=31536000; includeSubDomains"
    Header set Permissions-Policy "geolocation=(self), camera=(), microphone=()"
</IfModule>
```

---

## 5. FILE UPLOAD SECURITY CHECKLIST

| ID | Kontrol | Status |
|----|---------|--------|
| FU-01 | Max file size 5MB | [x] |
| FU-02 | Allowed types: jpg, jpeg, png, webp (image) | [x] |
| FU-03 | Allowed types: mp3, ogg, wav (audio) | [x] |
| FU-04 | Allowed types: pdf, jpg, png (document) | [x] |
| FU-05 | MIME type check via `finfo_file()` | [x] |
| FU-06 | Extension check (whitelist) | [x] |
| FU-07 | Filename di-randomize (32 hex) | [x] |
| FU-08 | Upload folder non-executable (.htaccess: php_flag engine off) | [x] |
| FU-09 | Tidak ada path traversal di filename | [x] |
| FU-10 | Image dimension check (opsional) | [ ] |

```apache
# public/uploads/.htaccess
php_flag engine off
Options -ExecCGI
AddHandler cgi-script .php .pl .py .sh .cgi
<FilesMatch "\.(php|pl|py|sh|cgi)$">
    Deny from all
</FilesMatch>
```

---

## 6. API SECURITY CHECKLIST

| ID | Kontrol | Status |
|----|---------|--------|
| AP-01 | Semua endpoint butuh autentikasi (kecuali login/register) | [ ] |
| AP-02 | Role check per endpoint | [ ] |
| AP-03 | CSRF token di POST | [ ] |
| AP-04 | Rate limiting: 60 req/menit per user | [ ] |
| AP-05 | Input validation di semua endpoint | [ ] |
| AP-06 | Error response tidak leak stack trace di production | [ ] |
| AP-07 | Pagination max 50 per page | [ ] |
| AP-08 | No sensitive data di response (password, token) | [ ] |
| AP-09 | HTTP method correct (GET untuk read, POST untuk write) | [ ] |
| AP-10 | CORS: same-origin only di production | [ ] |

---

## 7. DATABASE SECURITY CHECKLIST

| ID | Kontrol | Status |
|----|---------|--------|
| DB-01 | PDO prepared statements (no concatenation) | [ ] |
| DB-02 | Database user dengan limited privileges | [ ] |
| DB-03 | No `root` user di production | [ ] |
| DB-04 | Password database kuat (16+ karakter) | [ ] |
| DB-05 | Connection via localhost (no remote) | [ ] |
| DB-06 | Backup terenkripsi (opsional) | [ ] |
| DB-07 | `utf8mb4` charset | [ ] |
| DB-08 | Foreign key constraints aktif | [ ] |

---

## 8. DEPLOYMENT SECURITY CHECKLIST

| ID | Kontrol | Status |
|----|---------|--------|
| DP-01 | SSL/HTTPS aktif (Let's Encrypt) | [ ] |
| DP-02 | HTTP redirect ke HTTPS | [ ] |
| DP-03 | `APP_DEBUG = false` | [ ] |
| DP-04 | `display_errors = Off` | [ ] |
| DP-05 | Folder `app/`, `logs/`, `database/` tidak directly accessible | [ ] |
| DP-06 | File `.md`, `.sql`, `.log`, `.env` tidak accessible | [ ] |
| DP-07 | OPcache aktif | [ ] |
| DP-08 | Firewall: hanya port 80, 443, 22 | [ ] |
| DP-09 | SSH key-based auth (no password) | [ ] |
| DP-10 | Fail2ban untuk SSH (opsional) | [ ] |

---

## 9. AUDIT PROCEDURE

### 9.1 Pre-Deployment Audit

1. Jalankan semua checklist di atas (tandai ✓ atau ✗)
2. Untuk setiap ✗, buat issue/ticket untuk remediasi
3. Lakukan penetration testing manual:
   - Test SQL injection di login form
   - Test XSS di review/comment
   - Test CSRF di form POST
   - Test IDOR di booking/ticket/hotel
   - Test file upload (PHP disguised)
   - Test rate limiting
4. Scan dengan tools:
   - `sqlmap` (SQL injection)
   - `nikto` (web server scanner)
   - `OWASP ZAP` (proxy scanner)
5. Sign-off dari security auditor

### 9.2 Post-Deployment Audit

1. Verifikasi security headers via https://securityheaders.com
2. Verifikasi SSL via https://www.ssllabs.com/ssltest/
3. Test login dengan default credentials (harus diganti)
4. Test akses file sensitif: `/.env`, `/database/migration.sql`, `/logs/error.log`
5. Test directory listing: `/public/uploads/`
6. Monitor audit log untuk aktivitas mencurigakan
7. Audit berkala: bulanan (log review), quarterly (full scan)

### 9.3 Audit Log Review

| Aktivitas Mencurigakan | Indikator |
|------------------------|-----------|
| Brute force login | >5 failed login dari IP yang sama < 1 menit |
| Privilege escalation | Role change di audit log |
| Mass data access | >100 GET request ke API < 1 menit |
| File upload abuse | Upload file non-image/audio |
| Backup access | Download backup di luar jam kerja |
| Config change | Update settings di audit log |

---

## 10. RISK RATING

| Severity | Deskripsi | Contoh | SLA Fix |
|----------|-----------|--------|---------|
| **Critical** | Eksploitasi langsung, data breach | SQL injection, auth bypass | 24 jam |
| **High** | Eksploitasi memungkinkan akses data | IDOR, XSS stored, CSRF | 48 jam |
| **Medium** | Eksploitasi terbatas | Missing rate limit, info leak | 1 minggu |
| **Low** | Defense in depth improvement | Missing security header | 2 minggu |
| **Info** | Best practice | Naming convention, logging | Backlog |

---

> **Dokumen selesai.** Semua 32 modul dokumentasi telah didefinisikan sebagai landasan pembangunan aplikasi.
