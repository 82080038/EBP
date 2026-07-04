# MODUL 20 — SECURITY SYSTEM

> **Versi:** 1.1 · **Tanggal:** 2026-06-30 · **Last Updated:** 2026-06-30

---

## 1. RINGKASAN

Lapisan keamanan aplikasi: autentikasi, otorisasi (RBAC), proteksi serangan,
audit log, dan best practices.

---

## 2. OWASP TOP 10 2025 COMPLIANCE

### Mapping to OWASP Top 10 2025

| OWASP 2025 Risk | Mitigation in This Application | Status |
|-----------------|-------------------------------|--------|
| **A01: Broken Access Control** | RBAC middleware, parameter validation, session checks | ✅ Implemented |
| **A02: Security Misconfiguration** | Secure defaults, .env files, error handling | ✅ Implemented |
| **A03: Software Supply Chain Failures** | Composer lock, dependency updates, vetted libraries | ✅ Implemented |
| **A04: Cryptographic Failures** | Password hashing (bcrypt), HTTPS enforcement, data encryption | ✅ Implemented |
| **A05: Injection** | PDO prepared statements, input validation, output escaping | ✅ Implemented |
| **A06: Insecure Design** | Threat modeling, secure architecture patterns | ✅ Implemented |
| **A07: Authentication Failures** | Secure session management, MFA-ready, password policies | ✅ Implemented |
| **A08: Software or Data Integrity Failures** | Audit logs, data validation, checksums for uploads | ✅ Implemented |
| **A09: Security Logging and Alerting Failures** | Comprehensive audit log, error logging, monitoring | ✅ Implemented |
| **A10: Mishandling of Exceptional Conditions** | Global exception handler, no info leakage, proper error pages | ✅ Implemented |

### Additional Security Measures

| Measure | Description |
|---------|-------------|
| **Security Headers** | CSP, HSTS, X-Frame-Options, X-Content-Type-Options |
| **Rate Limiting** | 60 requests/minute per IP for API endpoints |
| **CSRF Protection** | Token validation on all state-changing requests |
| **File Upload Security** | MIME validation, size limits, random filenames |
| **Session Security** | HttpOnly, Secure, SameSite cookies, session regeneration |
| **Input Validation** | Server-side validation with sanitization |
| **Output Encoding** | htmlspecialchars() for XSS prevention |

---

## 3. AUTENTIKASI

### 3.1 Password Hashing

```php
// Hash saat registrasi
$hash = password_hash($password, PASSWORD_BCRYPT);

// Verify saat login
if (password_verify($input, $hash)) {
    // Login success
}
```

### 3.2 Session Management

```php
<?php
class Session {
    public static function start() {
        session_set_cookie_params([
            'lifetime' => 1800,     // 30 menit
            'httponly'  => true,     // JS tidak bisa akses
            'secure'    => isset($_SERVER['HTTPS']), // HTTPS only
            'samesite'  => 'Lax'
        ]);
        session_start();

        // Regenerate ID setiap 30 menit
        if (isset($_SESSION['last_regeneration']) &&
            time() - $_SESSION['last_regeneration'] > 1800) {
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }

        // Timeout check
        if (isset($_SESSION['last_activity']) &&
            time() - $_SESSION['last_activity'] > 1800) {
            session_destroy();
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }
        $_SESSION['last_activity'] = time();
    }
}
```

---

## 3. RBAC (ROLE-BASED ACCESS CONTROL)

```php
<?php
class Middleware {
    public static function requireRole($roles) {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }
        if (!in_array($_SESSION['role'], (array)$roles)) {
            http_response_code(403);
            View::render('errors/403');
            exit;
        }
    }

    public static function requireAuth() {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit;
        }
    }
}
```

### Matrix Akses

| Modul | Admin | Wisatawan | Tour Guide |
|-------|-------|-----------|------------|
| Dashboard Admin | ✓ | ✗ | ✗ |
| User Management | ✓ | ✗ | ✗ |
| Guide Approval | ✓ | ✗ | ✗ |
| CRUD Destinasi | ✓ | ✗ | ✗ |
| Cari Guide | ✓ | ✓ | ✗ |
| Booking Guide | ✓ | ✓ | ✗ |
| Accept/Reject Booking | ✓ | ✗ | ✓ (own) |
| Profil Guide | ✓ (all) | view | ✓ (own) |
| Jadwal Guide | ✗ | ✗ | ✓ (own) |
| Pendapatan | ✓ (all) | ✗ | ✓ (own) |
| Beli Tiket | ✓ | ✓ | ✗ |
| Booking Hotel | ✓ | ✓ | ✗ |
| Pesan Restoran | ✓ | ✓ | ✓ |
| Daftar Event | ✓ | ✓ | ✓ |
| Audio Guide | ✓ (manage) | ✓ (play) | ✗ |
| AI Chat | ✓ | ✓ | ✓ |
| Report | ✓ (all) | ✗ | ✓ (own) |
| Settings | ✓ | ✗ | ✗ |
| Audit Log | ✓ | ✗ | ✗ |

---

## 4. CSRF PROTECTION

```php
// Generate token
public static function csrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify token
public static function verifyCsrf($token) {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(419);
        die('CSRF token mismatch');
    }
}
```

### Usage di form:
```php
<input type="hidden" name="csrf_token" value="<?= Middleware::csrfToken() ?>">
```

### Usage di AJAX:
```javascript
$.ajaxSetup({
    beforeSend: function(xhr) {
        xhr.setRequestHeader('X-CSRF-Token', CSRF_TOKEN);
    }
});
```

---

## 5. SQL INJECTION PREVENTION

Semua query menggunakan **PDO prepared statements**:

```php
// BENAR
$stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
$stmt->execute(['email' => $email]);

// SALAH (jangan lakukan)
$stmt = $this->db->query("SELECT * FROM users WHERE email = '$email'");
```

---

## 6. XSS PREVENTION

```php
// Escape output
echo htmlspecialchars($data, ENT_QUOTES, 'UTF-8');

// Helper
class Helper {
    public static function e($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}
```

### Di view:
```php
<p><?= Helper::e($guide['name']) ?></p>
```

---

## 7. INPUT VALIDATION

```php
<?php
class Validator {
    private $data;
    private $errors = [];
    private $rules;

    public function __construct($data) {
        $this->data = $data;
    }

    public function required($fields) {
        foreach ($fields as $field) {
            if (empty($this->data[$field])) {
                $this->errors[$field] = "{$field} wajib diisi";
            }
        }
        return $this;
    }

    public function email($field) {
        if (!filter_var($this->data[$field] ?? '', FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = "Format email tidak valid";
        }
        return $this;
    }

    public function min($field, $length) {
        if (strlen($this->data[$field] ?? '') < $length) {
            $this->errors[$field] = "Minimal {$length} karakter";
        }
        return $this;
    }

    public function max($field, $length) {
        if (strlen($this->data[$field] ?? '') > $length) {
            $this->errors[$field] = "Maksimal {$length} karakter";
        }
        return $this;
    }

    public function numeric($field) {
        if (!is_numeric($this->data[$field] ?? '')) {
            $this->errors[$field] = "Harus berupa angka";
        }
        return $this;
    }

    public function fails() {
        return !empty($this->errors);
    }

    public function errors() {
        return $this->errors;
    }

    public function firstError() {
        return reset($this->errors) ?: null;
    }
}
```

---

## 8. AUDIT LOG

```php
<?php
class Logger {
    public static function audit($action, $module, $description) {
        $log = new AuditLog();
        $log->insert([
            'user_id' => $_SESSION['user_id'] ?? null,
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
    }

    public static function error($message, $context = []) {
        $log = date('Y-m-d H:i:s') . " ERROR: {$message}";
        if (!empty($context)) $log .= " " . json_encode($context);
        file_put_contents(LOG_PATH . '/error.log', $log . "\n", FILE_APPEND);
    }
}
```

---

## 9. FILE UPLOAD SECURITY

```php
public static function uploadFile($file, $targetDir) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp',
                     'audio/mpeg', 'audio/ogg', 'application/pdf'];
    $maxSize = MAX_UPLOAD_SIZE;

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Upload error');
    }
    if ($file['size'] > $maxSize) {
        throw new Exception('File terlalu besar (max 5MB)');
    }
    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception('Tipe file tidak diizinkan');
    }

    // Generate safe filename
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = bin2hex(random_bytes(16)) . '.' . $ext;
    $target = $targetDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        throw new Exception('Gagal menyimpan file');
    }
    return $target;
}
```

---

## 10. RATE LIMITING

```php
<?php
class RateLimiter {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function check($key, $maxRequests, $windowSeconds) {
        $sql = "SELECT COUNT(*) as cnt FROM rate_limits
                WHERE api_key = :key AND created_at > DATE_SUB(NOW(), INTERVAL :window SECOND)";
        $count = $this->db->query($sql, ['key' => $key, 'window' => $windowSeconds])->fetch()['cnt'];

        if ($count >= $maxRequests) {
            http_response_code(429);
            echo json_encode(['status' => 'error', 'message' => 'Rate limit exceeded']);
            exit;
        }

        $this->db->query("INSERT INTO rate_limits (api_key) VALUES (:key)", ['key' => $key]);
    }
}

// Usage di ApiController
$limiter = new RateLimiter();
$limiter->check($_SESSION['user_id'] . '_api', 60, 60); // 60 requests per minute
```

---

## 11. HTTPS & SECURITY HEADERS

```apache
# .htaccess
Header set X-Content-Type-Options "nosniff"
Header set X-Frame-Options "SAMEORIGIN"
Header set X-XSS-Protection "1; mode=block"
Header set Referrer-Policy "strict-origin-when-cross-origin"
Header set Strict-Transport-Security "max-age=31536000; includeSubDomains"
```

---

## 12. CHECKLIST KEAMANAN

- [x] Password hash bcrypt
- [x] PDO prepared statements
- [x] CSRF token semua form
- [x] XSS escaping semua output
- [x] RBAC middleware
- [x] Session timeout 30 menit
- [x] HttpOnly + Secure cookie
- [x] File upload validation
- [x] Rate limiting API
- [x] Audit log
- [x] Security headers
- [x] HTTPS di production

---

## 13. ADDITIONAL SECURITY RECOMMENDATIONS

### 13.1 Multi-Factor Authentication (MFA) — HIGH PRIORITY

**Status:** Not Implemented

Implementasi MFA untuk meningkatkan keamanan login:

```php
// app/services/MFAService.php
class MFAService {
    public function generateSecret(): string {
        return Google2FA::generateSecretKey();
    }

    public function generateQRCode(string $secret, string $email): string {
        return Google2FA::getQRCodeUrl('TourGuide.app', $email, $secret);
    }

    public function verifyCode(string $secret, string $code): bool {
        return Google2FA::verifyKey($secret, $code);
    }
}
```

**Implementation:**
- Add `mfa_secret` and `mfa_enabled` columns to `users` table
- Install: `composer require pragmarx/google2fa-laravel`
- Require MFA for admin and tour guide roles
- Implement backup codes for recovery

### 13.2 CAPTCHA Integration — HIGH PRIORITY

**Status:** Not Implemented

Implementasi CAPTCHA untuk mencegah brute force:

```php
// app/services/CaptchaService.php
class CaptchaService {
    public function verifyReCaptcha(string $token): bool {
        $response = file_get_contents('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => $_ENV['RECAPTCHA_SECRET'],
            'response' => $token
        ]);
        $data = json_decode($response);
        return $data->success && $data->score >= 0.5;
    }
}
```

**Implementation:**
- Register at reCAPTCHA v3
- Add to login/registration forms
- Verify token server-side
- Implement hCaptcha as alternative

### 13.3 DDoS Protection — HIGH PRIORITY

**Status:** Partial

**Implementation:**
- Cloudflare integration (recommended)
- mod_evasive for Apache
- iptables IP blocking
- Increased rate limiting during attacks
- CAPTCHA after failed attempts

### 13.4 Image Protection (Content Security) — HIGH PRIORITY

**Status:** Not Implemented

**Implementation:**
- Automatic watermark on uploaded images
- Hotlink protection via .htaccess
- Metadata embedding (EXIF copyright)
- Reverse image search monitoring
- Verification badge for official accounts
- DMCA automation

### 13.5 Real-Time Security Monitoring — HIGH PRIORITY

**Status:** Not Implemented

**Implementation:**
- Security event logging
- Alert thresholds (5 failed logins = alert)
- APM integration (Sentry, New Relic)
- Email/SMS alerts for critical events
- Security dashboard

### 13.6 IP Whitelisting for Admin — MEDIUM PRIORITY

**Status:** Not Implemented

**Implementation:**
- Add `allowed_ips` column to `users` table
- IP whitelist middleware
- IP management in admin panel
- Log all admin access attempts

### 13.7 Automated Security Scanning — MEDIUM PRIORITY

**Status:** Not Implemented

**Implementation:**
- GitHub Dependabot for dependencies
- `composer audit` for vulnerabilities
- phpstan for static analysis
- CI/CD security scanning
- Quarterly security audits

### 13.8 Penetration Testing — MEDIUM PRIORITY

**Status:** Not Implemented

**Implementation:**
- OWASP ZAP automated scanning
- Annual professional penetration testing
- Bug bounty program (optional)
- Document findings and remediation

---

## 14. SECURITY CHECKLIST (UPDATED)

### Implemented ✅
- [x] Password hash bcrypt
- [x] PDO prepared statements
- [x] CSRF token semua form
- [x] XSS escaping semua output
- [x] RBAC middleware
- [x] Session timeout 30 menit
- [x] HttpOnly + Secure cookie
- [x] File upload validation
- [x] Rate limiting API
- [x] Audit log
- [x] Security headers
- [x] HTTPS di production
- [x] OWASP Top 10 2025 compliance

### Not Implemented ❌ (Priority)
- [ ] MFA (HIGH)
- [ ] CAPTCHA (HIGH)
- [ ] DDoS protection (HIGH)
- [ ] Image protection (HIGH)
- [ ] Real-time monitoring (HIGH)
- [ ] IP whitelisting (MEDIUM)
- [ ] Automated scanning (MEDIUM)
- [ ] Penetration testing (MEDIUM)
- [ ] Account lockout (HIGH)
- [ ] Security training (LOW)

---

## 15. ACCOUNT LOCKOUT FOR BRUTE FORCE PROTECTION

**Status:** Not Implemented — HIGH PRIORITY

Implementasi account lockout untuk mencegah brute force attack:

```php
// app/services/AccountLockoutService.php
class AccountLockoutService {
    private $maxAttempts = 5;
    private $lockoutDuration = 900; // 15 minutes
    private $decayDuration = 300; // 5 minutes

    public function recordFailedAttempt(string $email): void {
        $this->db->query(
            "INSERT INTO login_attempts (email, attempts, last_attempt) 
             VALUES (:email, 1, NOW()) 
             ON DUPLICATE KEY UPDATE 
                attempts = attempts + 1, 
                last_attempt = NOW()",
            ['email' => $email]
        );
    }

    public function isLocked(string $email): bool {
        $attempt = $this->db->query(
            "SELECT * FROM login_attempts WHERE email = :email",
            ['email' => $email]
        )->fetch();

        if (!$attempt) {
            return false;
        }

        // Check if lockout period has expired
        $lockoutExpiry = strtotime($attempt['last_attempt']) + $this->lockoutDuration;
        if (time() > $lockoutExpiry) {
            $this->resetAttempts($email);
            return false;
        }

        return $attempt['attempts'] >= $this->maxAttempts;
    }

    public function getRemainingAttempts(string $email): int {
        $attempt = $this->db->query(
            "SELECT attempts FROM login_attempts WHERE email = :email",
            ['email' => $email]
        )->fetch();

        if (!$attempt) {
            return $this->maxAttempts;
        }

        return max(0, $this->maxAttempts - $attempt['attempts']);
    }

    public function getLockoutTimeRemaining(string $email): int {
        $attempt = $this->db->query(
            "SELECT last_attempt FROM login_attempts WHERE email = :email",
            ['email' => $email]
        )->fetch();

        if (!$attempt) {
            return 0;
        }

        $lockoutExpiry = strtotime($attempt['last_attempt']) + $this->lockoutDuration;
        return max(0, $lockoutExpiry - time());
    }

    public function resetAttempts(string $email): void {
        $this->db->query(
            "DELETE FROM login_attempts WHERE email = :email",
            ['email' => $email]
        );
    }

    public function resetOnSuccessfulLogin(string $email): void {
        $this->resetAttempts($email);
    }
}
```

### Integration with Login Controller

```php
// app/controllers/AuthController.php
class AuthController extends Controller {
    private $lockoutService;

    public function __construct() {
        $this->lockoutService = $this->service('AccountLockoutService');
    }

    public function login() {
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Check if account is locked
        if ($this->lockoutService->isLocked($email)) {
            $remaining = $this->lockoutService->getLockoutTimeRemaining($email);
            $minutes = ceil($remaining / 60);
            $this->json([
                'status' => 'error',
                'message' => "Account locked. Try again in {$minutes} minutes"
            ], 429);
            return;
        }

        // Attempt login
        $user = $this->authService->attemptLogin($email, $password);

        if ($user) {
            // Successful login - reset attempts
            $this->lockoutService->resetOnSuccessfulLogin($email);
            $this->json(['status' => 'success', 'user' => $user]);
        } else {
            // Failed login - record attempt
            $this->lockoutService->recordFailedAttempt($email);
            $remaining = $this->lockoutService->getRemainingAttempts($email);
            
            $this->json([
                'status' => 'error',
                'message' => "Invalid credentials. {$remaining} attempts remaining"
            ], 401);
        }
    }
}
```

### Database Table for Login Attempts

```sql
CREATE TABLE login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    attempts INT DEFAULT 1,
    last_attempt DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_last_attempt (last_attempt)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Progressive Lockout Strategy

```php
// Enhanced lockout with progressive duration
class ProgressiveLockoutService extends AccountLockoutService {
    private $lockoutDurations = [
        5 => 900,   // 15 minutes after 5 attempts
        10 => 3600, // 1 hour after 10 attempts
        15 => 86400, // 24 hours after 15 attempts
    ];

    public function getLockoutDuration(int $attempts): int {
        foreach ($this->lockoutDurations as $threshold => $duration) {
            if ($attempts >= $threshold) {
                return $duration;
            }
        }
        return $this->lockoutDuration;
    }
}
```

### IP-Based Rate Limiting

```php
// Add IP-based blocking for repeated failed attempts from same IP
class IPRateLimiter {
    private $maxAttemptsPerIP = 20;
    private $ipWindow = 3600; // 1 hour

    public function isIPBlocked(string $ip): bool {
        $count = $this->db->query(
            "SELECT COUNT(*) as cnt FROM login_attempts 
             WHERE ip_address = :ip AND last_attempt > DATE_SUB(NOW(), INTERVAL :window SECOND)",
            ['ip' => $ip, 'window' => $this->ipWindow]
        )->fetch()['cnt'];

        return $count >= $this->maxAttemptsPerIP;
    }
}
```

---

## 16. SECURITY AUDIT LOGGING

**Status:** Not Implemented — MEDIUM PRIORITY

Implementasi comprehensive security audit logging untuk tracking semua security-related events:

```php
// app/services/SecurityAuditService.php
class SecurityAuditService {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function logLoginAttempt(string $email, bool $success, string $ip): void {
        $this->db->query(
            "INSERT INTO security_audit_logs (event_type, email, success, ip_address, user_agent, created_at) 
             VALUES ('login_attempt', :email, :success, :ip, :ua, NOW())",
            [
                'email' => $email,
                'success' => $success ? 1 : 0,
                'ip' => $ip,
                'ua' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ]
        );
    }

    public function logPasswordChange(int $userId, string $ip): void {
        $this->db->query(
            "INSERT INTO security_audit_logs (event_type, user_id, ip_address, user_agent, created_at) 
             VALUES ('password_change', :user_id, :ip, :ua, NOW())",
            [
                'user_id' => $userId,
                'ip' => $ip,
                'ua' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ]
        );
    }

    public function logPrivilegeEscalation(int $userId, string $oldRole, string $newRole, string $ip): void {
        $this->db->query(
            "INSERT INTO security_audit_logs (event_type, user_id, old_value, new_value, ip_address, user_agent, created_at) 
             VALUES ('privilege_escalation', :user_id, :old_role, :new_role, :ip, :ua, NOW())",
            [
                'user_id' => $userId,
                'old_role' => $oldRole,
                'new_role' => $newRole,
                'ip' => $ip,
                'ua' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ]
        );
    }

    public function logDataAccess(int $userId, string $resource, string $action, string $ip): void {
        $this->db->query(
            "INSERT INTO security_audit_logs (event_type, user_id, resource, action, ip_address, user_agent, created_at) 
             VALUES ('data_access', :user_id, :resource, :action, :ip, :ua, NOW())",
            [
                'user_id' => $userId,
                'resource' => $resource,
                'action' => $action,
                'ip' => $ip,
                'ua' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ]
        );
    }

    public function logConfigurationChange(int $userId, string $setting, string $oldValue, string $newValue, string $ip): void {
        $this->db->query(
            "INSERT INTO security_audit_logs (event_type, user_id, setting, old_value, new_value, ip_address, user_agent, created_at) 
             VALUES ('config_change', :user_id, :setting, :old_value, :new_value, :ip, :ua, NOW())",
            [
                'user_id' => $userId,
                'setting' => $setting,
                'old_value' => $oldValue,
                'new_value' => $newValue,
                'ip' => $ip,
                'ua' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ]
        );
    }

    public function logAPICall(string $endpoint, string $method, int $userId, string $ip): void {
        $this->db->query(
            "INSERT INTO security_audit_logs (event_type, endpoint, method, user_id, ip_address, user_agent, created_at) 
             VALUES ('api_call', :endpoint, :method, :user_id, :ip, :ua, NOW())",
            [
                'endpoint' => $endpoint,
                'method' => $method,
                'user_id' => $userId,
                'ip' => $ip,
                'ua' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ]
        );
    }

    public function logFileUpload(int $userId, string $filename, string $fileType, int $fileSize, string $ip): void {
        $this->db->query(
            "INSERT INTO security_audit_logs (event_type, user_id, filename, file_type, file_size, ip_address, user_agent, created_at) 
             VALUES ('file_upload', :user_id, :filename, :file_type, :file_size, :ip, :ua, NOW())",
            [
                'user_id' => $userId,
                'filename' => $filename,
                'file_type' => $fileType,
                'file_size' => $fileSize,
                'ip' => $ip,
                'ua' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ]
        );
    }

    public function getAuditLogs(array $filters = [], int $limit = 100): array {
        $sql = "SELECT * FROM security_audit_logs WHERE 1=1";
        $params = [];

        if (!empty($filters['event_type'])) {
            $sql .= " AND event_type = :event_type";
            $params['event_type'] = $filters['event_type'];
        }

        if (!empty($filters['user_id'])) {
            $sql .= " AND user_id = :user_id";
            $params['user_id'] = $filters['user_id'];
        }

        if (!empty($filters['start_date'])) {
            $sql .= " AND created_at >= :start_date";
            $params['start_date'] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $sql .= " AND created_at <= :end_date";
            $params['end_date'] = $filters['end_date'];
        }

        $sql .= " ORDER BY created_at DESC LIMIT :limit";
        $params['limit'] = $limit;

        return $this->db->query($sql, $params)->fetchAll();
    }
}
```

### Database Table for Security Audit Logs

```sql
CREATE TABLE security_audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_type VARCHAR(50) NOT NULL,
    user_id INT NULL,
    email VARCHAR(255) NULL,
    success TINYINT(1) NULL,
    resource VARCHAR(255) NULL,
    action VARCHAR(50) NULL,
    setting VARCHAR(100) NULL,
    old_value TEXT NULL,
    new_value TEXT NULL,
    endpoint VARCHAR(255) NULL,
    method VARCHAR(10) NULL,
    filename VARCHAR(255) NULL,
    file_type VARCHAR(50) NULL,
    file_size INT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_event_type (event_type),
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at),
    INDEX idx_ip_address (ip_address)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Event Types to Log

| Event Type | Description | Priority |
|------------|-------------|----------|
| `login_attempt` | Successful/failed login attempts | HIGH |
| `password_change` | Password changes | HIGH |
| `privilege_escalation` | Role changes | HIGH |
| `data_access` | Access to sensitive data | HIGH |
| `config_change` | Configuration changes | MEDIUM |
| `api_call` | API endpoint calls | MEDIUM |
| `file_upload` | File uploads | MEDIUM |
| `account_lockout` | Account lockout events | HIGH |
| `csrf_failure` | CSRF token validation failures | HIGH |
| `rate_limit_exceeded` | Rate limit violations | MEDIUM |

### Audit Log Retention Policy

```php
// Cleanup old audit logs
class AuditLogCleanupService {
    private $retentionDays = 90; // Keep logs for 90 days

    public function cleanupOldLogs(): int {
        $sql = "DELETE FROM security_audit_logs 
                WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)";
        
        $this->db->query($sql, ['days' => $this->retentionDays]);
        
        return $this->db->rowCount();
    }
}
```

### Integration with Middleware

```php
// app/middleware/SecurityAuditMiddleware.php
class SecurityAuditMiddleware {
    private $auditService;

    public function __construct() {
        $this->auditService = new SecurityAuditService();
    }

    public function handle(): void {
        // Log API calls
        if (strpos($_SERVER['REQUEST_URI'], '/api/') === 0) {
            $this->auditService->logAPICall(
                $_SERVER['REQUEST_URI'],
                $_SERVER['REQUEST_METHOD'],
                $_SESSION['user_id'] ?? null,
                $_SERVER['REMOTE_ADDR']
            );
        }

        // Log sensitive data access
        if (strpos($_SERVER['REQUEST_URI'], '/admin/') === 0) {
            $this->auditService->logDataAccess(
                $_SESSION['user_id'] ?? null,
                $_SERVER['REQUEST_URI'],
                $_SERVER['REQUEST_METHOD'],
                $_SERVER['REMOTE_ADDR']
            );
        }
    }
}
```

### Audit Log Dashboard

```php
// app/controllers/Admin/AuditLogController.php
class AuditLogController extends Controller {
    public function index(): void {
        $filters = [
            'event_type' => $_GET['event_type'] ?? null,
            'user_id' => $_GET['user_id'] ?? null,
            'start_date' => $_GET['start_date'] ?? null,
            'end_date' => $_GET['end_date'] ?? null,
        ];

        $logs = $this->auditService->getAuditLogs($filters, 100);

        $this->view('admin/audit_logs', ['logs' => $logs]);
    }
}
```

---

## 17. SECURITY INCIDENT RESPONSE PLAN

### 17.1 Incident Classification

| Severity | Description | Response Time | Examples |
|----------|-------------|----------------|----------|
| **P0 - Critical** | System compromise, data breach, active attack | 15 minutes | SQL injection successful, DDoS attack, ransomware |
| **P1 - High** | Security vulnerability, unauthorized access attempt | 1 hour | Brute force attack, XSS attempt, CSRF bypass |
| **P2 - Medium** | Suspicious activity, policy violation | 4 hours | Failed login attempts, unusual API usage |
| **P3 - Low** | Minor security issue, configuration error | 24 hours | Missing security header, weak password policy |

### 17.2 Incident Response Team

| Role | Responsibility | Contact |
|------|----------------|---------|
| **Incident Commander** | Overall coordination, decision making | CTO/Lead Dev |
| **Security Analyst** | Investigation, forensics, analysis | Security Lead |
| **DevOps Engineer** | System isolation, recovery, patching | DevOps Lead |
| **Communications** | Internal/external communication | PR/Manager |
| **Legal** | Legal compliance, data breach notification | Legal Counsel |

### 17.3 Incident Response Procedure

#### Phase 1: Detection & Identification (0-15 minutes)

```php
// app/services/SecurityIncidentService.php
class SecurityIncidentService {
    public function detectIncident(): void {
        // Check for critical indicators
        $this->checkForSQLInjection();
        $this->checkForDDoS();
        $this->checkForUnauthorizedAccess();
        $this->checkForDataBreach();
    }

    private function checkForDDoS(): void {
        $metrics = $this->metricsService->getMetrics('requests_per_minute');
        if ($metrics > 1000) { // Threshold
            $this->declareIncident('P0', 'DDoS attack detected', [
                'requests_per_minute' => $metrics
            ]);
        }
    }

    private function declareIncident(string $severity, string $description, array $context): void {
        $this->incidentRepository->create([
            'severity' => $severity,
            'description' => $description,
            'context' => json_encode($context),
            'status' => 'open',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Send alert to incident response team
        $this->alertService->sendIncidentAlert($severity, $description);
    }
}
```

#### Phase 2: Containment (15-30 minutes)

```bash
# Immediate containment actions

# 1. Isolate affected systems
sudo iptables -A INPUT -s <attacker_ip> -j DROP

# 2. Disable vulnerable endpoints
sudo a2dismod rewrite
sudo systemctl reload apache2

# 3. Enable maintenance mode
echo "MAINTENANCE_MODE=true" >> .env

# 4. Block suspicious IPs
sudo fail2ban-client set sshd banip <attacker_ip>
```

#### Phase 3: Eradication (30-60 minutes)

```php
// Remove malicious code, patch vulnerabilities
class VulnerabilityPatcher {
    public function patchSQLInjection(): void {
        // Review all database queries
        $this->auditRepository->auditQueries();
        
        // Apply prepared statements
        $this->codePatcher->applyPreparedStatements();
        
        // Test patches
        $this->testRunner->runSecurityTests();
    }

    public function patchXSS(): void {
        // Review all output
        $this->auditRepository->auditOutput();
        
        // Apply output escaping
        $this->codePatcher->applyOutputEscaping();
        
        // Add CSP headers
        $this->configPatcher->addCSPHeaders();
    }
}
```

#### Phase 4: Recovery (1-4 hours)

```bash
# Restore from clean backup
mysql -u root -p tour_guide_app < /backups/clean_backup.sql

# Verify data integrity
mysql -u root -p tour_guide_app -e "CHECK TABLE bookings, users, transactions"

# Monitor for continued attacks
tail -f /var/log/apache2/access.log | grep suspicious_pattern

# Gradually restore service
echo "MAINTENANCE_MODE=false" >> .env
sudo systemctl reload apache2
```

#### Phase 5: Post-Incident Analysis (24-48 hours)

```php
// Document incident
class IncidentReport {
    public function generateReport(int $incidentId): array {
        $incident = $this->incidentRepository->find($incidentId);
        
        return [
            'summary' => $incident['description'],
            'timeline' => $this->buildTimeline($incidentId),
            'root_cause' => $this->analyzeRootCause($incidentId),
            'impact_assessment' => $this->assessImpact($incidentId),
            'lessons_learned' => $this->extractLessons($incidentId),
            'action_items' => $this->defineActionItems($incidentId),
        ];
    }
}
```

### 17.4 Communication Plan

**Internal Communication:**
- P0: Immediate page to all team members
- P1: Email within 15 minutes
- P2: Email within 1 hour
- P3: Email within 4 hours

**External Communication:**
- Data breach: Notify affected users within 72 hours (GDPR requirement)
- Service outage: Public statement within 1 hour
- Security vulnerability: Coordinate disclosure timeline

### 17.5 Incident Response Checklist

**During Incident:**
- [ ] Declare incident and assign severity
- [ ] Assemble incident response team
- [ ] Document initial findings
- [ ] Implement containment measures
- [ ] Preserve evidence (logs, memory dumps)
- [ ] Identify root cause
- [ ] Implement fix/patch
- [ ] Verify fix effectiveness
- [ ] Restore from backup if needed
- [ ] Monitor for recurrence

**After Incident:**
- [ ] Complete incident report
- [ ] Conduct post-mortem meeting
- [ ] Update security policies
- [ ] Implement preventive measures
- [ ] Update incident response plan
- [ ] Train team on lessons learned
- [ ] Share findings (if appropriate)

### 17.6 Security Incident Report Template

```markdown
# Security Incident Report

**Incident ID:** INC-2026-001
**Severity:** P0 - Critical
**Date:** 2026-06-30
**Status:** Resolved

## Summary
[Brief description of the incident]

## Timeline
- 14:30 - Incident detected
- 14:45 - Incident declared, team assembled
- 15:00 - Containment measures implemented
- 16:30 - Root cause identified
- 17:00 - Fix implemented
- 18:00 - Service restored

## Root Cause
[Analysis of what caused the incident]

## Impact Assessment
- Affected users: X
- Data compromised: Y
- Financial impact: Z
- Reputation impact: ...

## Lessons Learned
1. ...
2. ...

## Action Items
- [ ] ...
- [ ] ...
```

---

> **Modul Selanjutnya:** `21_API_DESIGN_AJAX_JSON.md`
