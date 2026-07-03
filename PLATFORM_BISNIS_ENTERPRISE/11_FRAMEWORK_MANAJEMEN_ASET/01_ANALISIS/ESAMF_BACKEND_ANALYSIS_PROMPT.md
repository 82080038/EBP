# ESAMF Backend Analysis Prompt

**Document ID:** ESAMF-BE-001

**Version:** 1.0

**Purpose**

Prompt khusus untuk analisis backend repository secara mendalam sesuai standar Enterprise Software Asset Management Framework (ESAMF).

---

# OBJECTIVE

Lakukan analisis menyeluruh terhadap backend repository saat ini.

Jangan melakukan perubahan source code.

Jangan melakukan refactoring.

Tugas Anda hanya:

* membaca file backend;
* memahami arsitektur;
* menginventarisasi komponen;
* mendokumentasikan;
* mengklasifikasikan.

---

# OUTPUT LOCATION

Hasil analisis ditempatkan pada:

```text
11_ENTERPRISE_SOFTWARE_ASSET_MANAGEMENT_FRAMEWORK/

07_MIGRATION/

<PROJECT_NAME>/

11_BACKEND_ANALYSIS.md
```

---

# ANALYSIS CHECKLIST

## 1. Backend Technology Stack

Identifikasi:

- Language (PHP, Python, Java, Node.js, etc.)
- Framework (Laravel, Symfony, Express, Spring, etc.)
- Architecture Pattern (MVC, MVVM, Clean Architecture, Hexagonal, etc.)
- API Style (REST, GraphQL, gRPC)
- Authentication (JWT, Session, OAuth, etc.)
- Database ORM/Query Builder
- Dependency Injection
- Queue System
- Cache System
- Logging System
- Validation Library

## 2. Folder Structure Analysis

Analisis struktur folder:

```
app/
config/
public/
routes/
storage/
vendor/
tests/
```

Jelaskan fungsi setiap folder.

## 3. Module/Package Analysis

Identifikasi seluruh module/package:

- Nama module
- Tujuan module
- Fitur utama
- Dependency antar module
- Entry point
- Export/Interface

## 4. Service Layer Analysis

Untuk setiap service:

- Nama service
- Tanggung jawab
- Method utama
- Input/Output
- Dependency
- Business logic complexity

## 5. Repository Layer Analysis

Untuk setiap repository:

- Nama repository
- Table yang di-handle
- Method CRUD
- Query complexity
- Relationship handling
- Caching strategy

## 6. Controller Layer Analysis

Untuk setiap controller:

- Nama controller
- Route yang di-handle
- Request validation
- Response format
- Middleware yang digunakan
- Error handling

## 7. Middleware Analysis

Identifikasi seluruh middleware:

- Authentication middleware
- Authorization middleware
- Logging middleware
- Rate limiting middleware
- CORS middleware
- Custom middleware

## 8. API Endpoint Analysis

Petakan seluruh endpoint:

- HTTP Method
- URL path
- Request body
- Response format
- Authentication required
- Rate limiting
- Error codes

## 9. Dependency Injection Analysis

Identifikasi:

- Service container
- Service providers
- Singleton services
- Transient services
- Factory pattern
- Dependency cycle

## 10. Configuration Analysis

Identifikasi:

- Environment variables
- Configuration files
- Feature flags
- Constants
- Global settings

## 11. Error Handling Analysis

Analisis:

- Exception handling
- Error responses
- Logging strategy
- User-friendly messages
- Debug information exposure

## 12. Security Analysis

Identifikasi:

- Input validation
- SQL injection protection
- XSS protection
- CSRF protection
- Rate limiting
- Authentication implementation
- Authorization implementation
- Sensitive data handling

## 13. Performance Analysis

Analisis:

- N+1 query problem
- Eager loading
- Lazy loading
- Caching strategy
- Query optimization
- Memory usage
- Response time

## 14. Testing Coverage

Identifikasi:

- Unit tests
- Integration tests
- Feature tests
- Test coverage percentage
- Mock usage

## 15. EBP Core Framework Compliance

Bandingkan dengan standar EBP:

- Service pattern
- Repository pattern
- Middleware pattern
- Response format
- Error handling
- Authentication flow
- Authorization flow

---

# OUTPUT FORMAT

## Section 1: Backend Overview

```markdown
## Backend Overview

- **Language**: [PHP/Python/Java/Node.js]
- **Framework**: [Laravel/Symfony/Express/Spring]
- **Architecture**: [MVC/Clean Architecture/Hexagonal]
- **API Style**: [REST/GraphQL/gRPC]
- **Total Modules**: [count]
- **Total Controllers**: [count]
- **Total Services**: [count]
- **Total Repositories**: [count]
- **Total Endpoints**: [count]
```

## Section 2: Technology Stack

```markdown
## Technology Stack

### Core
- [component]: [version]

### Authentication
- [component]: [version]

### Database
- [component]: [version]

### Queue
- [component]: [version]

### Cache
- [component]: [version]

### Logging
- [component]: [version]

### Validation
- [component]: [version]
```

## Section 3: Module Analysis

```markdown
## Module: [module_name]

**Purpose**: [description]
**Location**: [path]

### Components
- [component]: [description]

### Dependencies
- [module]: [dependency type]

### API Endpoints
- [method] [path]: [description]
```

## Section 4: Service Layer

```markdown
## Service: [service_name]

**Purpose**: [description]
**Location**: [path]

### Methods
| Method | Input | Output | Description |
|--------|-------|--------|-------------|
| [name] | [type]| [type] | [desc]      |

### Dependencies
- [service/repository]: [purpose]
```

## Section 5: Repository Layer

```markdown
## Repository: [repository_name]

**Purpose**: [description]
**Table**: [table_name]

### Methods
| Method | Query Type | Description |
|--------|------------|-------------|
| [name] | [SELECT/INSERT/UPDATE/DELETE] | [desc] |

### Relationships
- [relationship]: [target table]
```

## Section 6: API Documentation

```markdown
## API Endpoints

### [Module Name]
| Method | Path | Auth | Description |
|--------|------|------|-------------|
| [GET]  | [/path] | [x] | [desc]      |
```

## Section 7: EBP Compliance

```markdown
## EBP Core Framework Compliance

### Compliant
- [pattern]: [description]

### Non-Compliant
- [pattern]: [description] - [recommendation]

### Missing
- [pattern]: [recommendation]
```

## Section 8: Recommendations

```markdown
## Recommendations

### Architecture
- [recommendation]

### Performance
- [recommendation]

### Security
- [recommendation]

### Maintainability
- [recommendation]
```

---

# IMPORTANT RULES

- Jangan mengubah source code
- Jangan membuat commit Git
- Fokus pada analisis dan dokumentasi
- Gunakan bahasa Indonesia untuk penjelasan
- Sertakan contoh code jika perlu

---

# Definition of Done

Analisis backend dianggap selesai apabila:

- Seluruh module telah teridentifikasi
- Seluruh service telah didokumentasikan
- Seluruh repository telah didokumentasikan
- Seluruh endpoint telah dipetakan
- EBP compliance check telah dilakukan
- Rekomendasi telah disusun
