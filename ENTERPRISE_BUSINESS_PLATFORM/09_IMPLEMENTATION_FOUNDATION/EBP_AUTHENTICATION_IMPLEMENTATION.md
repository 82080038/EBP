# Enterprise Business Platform (EBP)

# Authentication Implementation

**Document ID:** EBP-IMPLEMENTATION-FOUNDATION-AUTH-001
**Version:** 1.0
**Category:** Implementation Foundation
**Status:** Official Security Implementation Standard

---

# 1. Introduction

Authentication Engine adalah komponen EBP yang bertanggung jawab memastikan:

* siapa pengguna sistem;
* bagaimana pengguna masuk;
* bagaimana identitas diverifikasi;
* bagaimana akses diamankan.

Authentication merupakan fondasi sebelum:

* Authorization;
* RBAC;
* Multi Tenant Security;
* Audit;
* Compliance.

---

# 2. Authentication Philosophy

EBP menggunakan prinsip:

> Identity first, access second.

Artinya:

Sistem harus mengetahui:

```
WHO ARE YOU?
```

sebelum menentukan:

```
WHAT CAN YOU DO?
```

---

# 3. Authentication Architecture

```text
              USER


                |

                v


          LOGIN REQUEST


                |

                v


     AUTHENTICATION SERVICE


                |

     +----------+-----------+

     |                      |

     v                      v


 Credential              Security

 Verification            Policy


     |

     v


 Identity Token


     |

     v


 Application Access

```

---

# 4. Authentication Components

Authentication Engine terdiri dari:

```
Identity Service

Credential Manager

Password Manager

Session Manager

Token Manager

Device Manager

Security Policy

Audit Logger

```

---

# 5. User Identity Model

EBP memisahkan:

```
USER

=

Identity


ACCOUNT

=

Authentication Credential


PROFILE

=

Personal Information

```

---

# 6. Database Structure

Core database:

```
identity/

├── users

├── user_profiles

├── user_credentials

├── user_sessions

├── user_devices

└── login_logs

```

---

# 7. Users Table

```sql
CREATE TABLE users (

id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

tenant_id BIGINT UNSIGNED,

username VARCHAR(100),

email VARCHAR(150),

status VARCHAR(20),

created_at DATETIME,

updated_at DATETIME

);

```

---

# 8. User Credential Table

Password tidak disimpan di tabel user.

Table:

```
user_credentials
```

Contoh:

```sql
CREATE TABLE user_credentials (

id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

user_id BIGINT UNSIGNED,

password_hash VARCHAR(255),

password_changed_at DATETIME,

failed_attempt INT DEFAULT 0,

locked_until DATETIME NULL

);

```

---

# 9. Password Security

EBP wajib menggunakan:

```
Password Hashing

Salt

Strong Algorithm

Password Policy

```

Contoh PHP:

```php
$passwordHash =
password_hash(
$password,
PASSWORD_DEFAULT
);

```

---

# 10. Password Policy

Aturan:

```
Minimum Length

Complexity

Password History

Expiration Policy

Failed Attempt Protection

```

Contoh:

```
Minimal 8 karakter

Kombinasi huruf besar

Huruf kecil

Angka

Symbol

```

---

# 11. Login Flow

```text
User Input


   |

   v


Validate Request


   |

   v


Find User


   |

   v


Verify Password


   |

   v


Check Account Status


   |

   v


Create Session


   |

   v


Generate Token


   |

   v


Access System

```

---

# 12. Login API

Endpoint:

```
POST

/api/v1/auth/login

```

Request:

```json
{
"username":"admin",
"password":"password"
}

```

Response:

```json
{
"success":true,
"token":"xxxxx",
"user":{
"id":1,
"name":"Administrator"
}
}

```

---

# 13. Session Management

Session menyimpan:

```
User

Tenant

Device

IP Address

Login Time

Last Activity

Expiration

```

---

# 14. Session Table

```sql
CREATE TABLE user_sessions (

id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

user_id BIGINT,

token VARCHAR(255),

ip_address VARCHAR(50),

device VARCHAR(255),

expires_at DATETIME,

created_at DATETIME

);

```

---

# 15. Token Strategy

EBP mendukung:

## Internal Web Application

Menggunakan:

```
Secure Session Cookie

```

---

## API / Mobile

Menggunakan:

```
JWT Token

```

---

# 16. JWT Architecture

```text
LOGIN


 |

 v


Generate JWT


 |

 v


Client Store Token


 |

 v


API Request


 |

 v


Verify Token


```

---

# 17. JWT Payload

Contoh:

```json
{
"user_id":100,
"tenant_id":10,
"role":"manager",
"expire":123456789
}

```

---

# 18. Device Management

EBP mengenali:

```
Browser

Mobile Device

Tablet

POS Terminal

Kitchen Display

```

---

# 19. Device Table

```sql
user_devices


id

user_id

device_name

device_type

fingerprint

last_login

```

---

# 20. Multi Factor Authentication

EBP mendukung:

```
OTP

Email Verification

Authenticator App

Hardware Token

```

---

# 21. Login Security

Proteksi:

```
Brute Force Protection

Rate Limiting

IP Monitoring

Suspicious Login Detection

```

---

# 22. Failed Login Handling

Contoh:

```
5 gagal login

↓

Lock Account

↓

Require Verification

```

---

# 23. Authentication Middleware

Setiap request:

```text
Request


 |

 v


Auth Middleware


 |

 v


Validate Token


 |

 v


Load User Context


 |

 v


Continue

```

---

# 24. User Context

Setelah login:

System mengetahui:

```
Current User

Current Tenant

Current Role

Current Permission

```

---

# 25. Logout Flow

```text
Logout Request


 |

 v


Invalidate Token


 |

 v


Close Session


 |

 v


Write Audit Log

```

---

# 26. Audit Authentication

Dicatat:

```
Login Success

Login Failed

Logout

Password Change

Password Reset

Device Login

```

---

# 27. Authentication Audit Table

```sql
login_logs


id

user_id

action

ip_address

device

status

created_at

```

---

# 28. Password Reset

Flow:

```text
Request Reset


↓

Generate Token


↓

Send Notification


↓

Verify Token


↓

Change Password

```

---

# 29. Security Rules

Tidak boleh:

```
Plain Password Storage

Shared Account

Hardcoded Password

Unlimited Login Attempt

```

---

# 30. Integration With RBAC

Authentication:

```
WHO

```

RBAC:

```
WHAT CAN DO

```

Flow:

```
Login

↓

User Identity

↓

Role

↓

Permission

↓

Access

```

---

# 31. Integration With Multi Tenant

Authentication menghasilkan:

```
User

+

Tenant Context

```

Contoh:

```
User:
Budi


Tenant:
Restaurant ABC

```

---

# 32. Testing Strategy

Test:

## Unit Test

```
Password Validation

Token Generation

Session

```

---

## Security Test

```
Brute Force

Token Expiration

Unauthorized Access

```

---

## Browser Test

Dengan Playwright:

```
Login

Logout

Invalid Password

Session Timeout

```

---

# 33. Implementation Example

Login Controller:

```php
class AuthController{


public function login($request){


return
AuthService::login(
$request
);


}


}

```

---

Service:

```php
class AuthService{


public function login($data){


$user =
$userRepository
->findByEmail(
$data['email']
);


verifyPassword();


createSession();


return token;


}

}

```

---

# 34. Future Development

Authentication Engine dapat berkembang menjadi:

```
Enterprise Identity Platform


        ↓


Single Sign On


        ↓


Digital Identity Management

```

---

# 35. Final Vision

Authentication membuat EBP memiliki:

```
Secure Identity Foundation


        ↓


Trusted Business Platform


        ↓


Enterprise Security Layer

```

---

# END OF DOCUMENT

Document ID:

EBP-IMPLEMENTATION-FOUNDATION-AUTH-001

Version:

1.0
