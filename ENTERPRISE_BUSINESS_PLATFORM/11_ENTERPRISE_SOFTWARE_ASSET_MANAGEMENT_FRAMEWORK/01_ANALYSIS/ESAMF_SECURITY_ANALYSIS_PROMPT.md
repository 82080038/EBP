# ESAMF Security Analysis Prompt

**Document ID:** ESAMF-SEC-001

**Version:** 1.0

**Purpose**

Prompt khusus untuk analisis keamanan repository secara mendalam sesuai standar Enterprise Software Asset Management Framework (ESAMF).

---

# OBJECTIVE

Lakukan analisis menyeluruh terhadap aspek keamanan repository saat ini.

Jangan melakukan perubahan source code.

Jangan melakukan security testing aktif (penetration testing).

Tugas Anda hanya:

* membaca konfigurasi keamanan;
* memahami implementasi keamanan;
* menginventarisasi vulnerability potensial;
* mendokumentasikan;
* mengklasifikasikan.

---

# OUTPUT LOCATION

Hasil analisis ditempatkan pada:

```text
11_ENTERPRISE_SOFTWARE_ASSET_MANAGEMENT_FRAMEWORK/

07_MIGRATION/

<PROJECT_NAME>/

12_SECURITY_ANALYSIS.md
```

---

# ANALYSIS CHECKLIST

## 1. Authentication Analysis

Identifikasi:

- Authentication method (JWT, Session, OAuth, SAML, etc.)
- Token storage (localStorage, sessionStorage, cookie, httpOnly)
- Token expiration policy
- Refresh token mechanism
- Password hashing algorithm
- Password policy (length, complexity, rotation)
- Multi-factor authentication
- Login rate limiting
- Account lockout mechanism
- Session management

## 2. Authorization Analysis

Identifikasi:

- RBAC implementation
- Permission system
- Role hierarchy
- Resource-based access control
- Attribute-based access control (ABAC)
- Permission inheritance
- Dynamic permissions
- Admin override mechanism

## 3. Data Protection Analysis

Analisis:

- Encryption at rest
- Encryption in transit (TLS/SSL)
- Sensitive data identification (PII, PCI, PHI)
- Data masking
- Anonymization
- Pseudonymization
- Key management
- Secret management

## 4. Input Validation Analysis

Cek:

- Server-side validation
- Client-side validation
- SQL injection protection
- XSS protection
- CSRF protection
- Command injection protection
- File upload validation
- Path traversal protection
- Header injection protection

## 5. API Security Analysis

Identifikasi:

- API authentication
- API rate limiting
- API key management
- CORS configuration
- Content Security Policy (CSP)
- HTTP security headers
- API versioning security
- GraphQL security (jika applicable)

## 6. Session Management Analysis

Analisis:

- Session fixation protection
- Session hijacking protection
- Session timeout
- Concurrent session limit
- Session invalidation
- Secure cookie flags (httpOnly, secure, sameSite)

## 7. Audit Trail Analysis

Identifikasi:

- Audit logging coverage
- Log retention policy
- Log protection
- Log tampering detection
- Sensitive action logging
- User activity tracking
- Change history

## 8. Error Handling Analysis

Cek:

- Error message exposure
- Stack trace exposure
- Debug mode in production
- Custom error pages
- Error logging
- User-friendly error messages

## 9. Dependency Security Analysis

Identifikasi:

- Vulnerable dependencies
- Outdated packages
- Known CVEs
- License compliance
- Supply chain security
- Third-party library risk

## 10. Configuration Security Analysis

Cek:

- Hardcoded credentials
- Secrets in code
- Environment variable usage
- Configuration file protection
- .gitignore coverage
- Sensitive files in repository

## 11. Infrastructure Security

Analisis:

- Server hardening
- Network security
- Firewall rules
- DDoS protection
- WAF configuration
- Container security (jika applicable)
- Cloud security (jika applicable)

## 12. Compliance Analysis

Identifikasi:

- GDPR compliance
- PDPA compliance
- PCI DSS compliance (jika applicable)
- HIPAA compliance (jika applicable)
- ISO 27001 compliance
- SOC 2 compliance
- Local regulation compliance

## 13. EBP Security Architecture Compliance

Bandingkan dengan standar EBP:

- Authentication flow
- Authorization flow
- Audit trail pattern
- Data encryption pattern
- Session management pattern
- RBAC implementation

---

# OUTPUT FORMAT

## Section 1: Security Overview

```markdown
## Security Overview

- **Authentication Method**: [JWT/Session/OAuth/etc]
- **Authorization Model**: [RBAC/ABAC/etc]
- **Encryption**: [AES-256/TLS-1.3/etc]
- **Compliance Standards**: [GDPR/PDPA/etc]
- **Security Score**: [0-100]
```

## Section 2: Authentication Analysis

```markdown
## Authentication

### Implementation
- **Method**: [description]
- **Token Storage**: [location]
- **Expiration**: [policy]
- **Refresh Token**: [Yes/No]

### Password Policy
- **Min Length**: [length]
- **Complexity**: [requirements]
- **Hashing**: [algorithm]
- **Rotation**: [policy]

### Multi-Factor Authentication
- **Enabled**: [Yes/No]
- **Methods**: [SMS/Email/TOTP/etc]
```

## Section 3: Authorization Analysis

```markdown
## Authorization

### RBAC Implementation
- **Roles**: [list]
- **Permissions**: [count]
- **Hierarchy**: [description]

### Permission System
| Permission | Resource | Action | Role |
|------------|----------|--------|------|
| [perm]     | [res]    | [act]  | [role]|
```

## Section 4: Data Protection

```markdown
## Data Protection

### Encryption
- **At Rest**: [algorithm]
- **In Transit**: [TLS version]
- **Key Management**: [method]

### Sensitive Data
| Data Type | Location | Protection Level |
|-----------|----------|-----------------|
| [type]    | [loc]    | [level]         |
```

## Section 5: Vulnerability Assessment

```markdown
## Vulnerability Assessment

### Critical
- [vulnerability]: [description] - [mitigation]

### High
- [vulnerability]: [description] - [mitigation]

### Medium
- [vulnerability]: [description] - [mitigation]

### Low
- [vulnerability]: [description] - [mitigation]
```

## Section 6: Dependency Security

```markdown
## Dependency Security

### Vulnerable Packages
| Package | Version | CVE | Severity | Action |
|---------|---------|-----|----------|--------|
| [pkg]   | [ver]   | [id]| [sev]    | [act]  |

### Outdated Packages
| Package | Current | Latest | Risk |
|---------|---------|--------|------|
| [pkg]   | [ver]   | [ver]  | [risk]|
```

## Section 7: EBP Security Compliance

```markdown
## EBP Security Architecture Compliance

### Compliant
- [pattern]: [description]

### Non-Compliant
- [pattern]: [description] - [recommendation]

### Missing
- [pattern]: [recommendation]
```

## Section 8: Recommendations

```markdown
## Security Recommendations

### Critical (Immediate)
- [recommendation]

### High (Within 1 week)
- [recommendation]

### Medium (Within 1 month)
- [recommendation]

### Low (Within 3 months)
- [recommendation]
```

---

# IMPORTANT RULES

- Jangan melakukan penetration testing
- Jangan melakukan exploit attempt
- Jangan mengubah konfigurasi keamanan
- Fokus pada analisis dan dokumentasi
- Gunakan bahasa Indonesia untuk penjelasan
- Sertakan referensi OWASP/CVE jika perlu

---

# Definition of Done

Analisis keamanan dianggap selesai apabila:

- Seluruh authentication flow telah didokumentasikan
- Seluruh authorization mechanism telah didokumentasikan
- Vulnerability potensial telah teridentifikasi
- Dependency security telah dianalisis
- EBP security compliance check telah dilakukan
- Rekomendasi mitigasi telah disusun
