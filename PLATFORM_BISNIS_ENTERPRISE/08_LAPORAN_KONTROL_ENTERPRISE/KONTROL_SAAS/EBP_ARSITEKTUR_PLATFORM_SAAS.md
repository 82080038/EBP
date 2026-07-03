# Enterprise Business Platform (EBP)

# SaaS Platform Architecture


**Document ID:** EBP-SAAS-PLATFORM-ARCHITECTURE-001

**Version:** 1.0

**Category:** Platform Architecture Standard

**Status:** Official Architecture Specification



---

# 1. Introduction


Dokumen ini mendefinisikan arsitektur SaaS (Software as a Service) untuk Enterprise Business Platform (EBP).

EBP bukan hanya aplikasi restoran.

EBP adalah platform software yang menyediakan berbagai produk:


* Restaurant ERP
* Hotel ERP
* Parking System
* Farming ERP
* Legal System

Sebagai SaaS platform, EBP harus:


* Multi-tenant
* Scalable
* Secure
* Reliable
* Billable



---

# 2. SaaS Philosophy


EBP SaaS Platform menggunakan prinsip:


```

ONE PLATFORM

+

MULTIPLE PRODUCTS

+

MULTIPLE TENANTS

=

SOFTWARE COMPANY

```


Artinya:


* Satu platform untuk semua produk
* Banyak produk di atas platform
* Banyak tenant menggunakan produk
* Revenue dari subscription



---

# 3. SaaS Business Model


## Subscription Model


Tenant membayar subscription berdasarkan:


* Plan (Basic, Professional, Enterprise)
* Product (Restaurant, Hotel, Parking)
* Usage (Users, Transactions, Storage)
* Duration (Monthly, Yearly)


## Pricing Tiers


### Basic Plan


```
Features:
- POS
- Inventory
- Basic Reporting

Price: Rp 500.000/bulan
Users: 5
Transactions: Unlimited
Storage: 5 GB
```


### Professional Plan


```
Features:
- All Basic Features
- Accounting
- Multi Branch
- Advanced Reporting
- API Access

Price: Rp 2.000.000/bulan
Users: 20
Transactions: Unlimited
Storage: 50 GB
```


### Enterprise Plan


```
Features:
- All Professional Features
- AI Forecast
- Custom Development
- Dedicated Support
- SLA 99.9%

Price: Custom
Users: Unlimited
Transactions: Unlimited
Storage: Unlimited
```



---

# 4. SaaS Architecture Overview


```

                    CUSTOMER


                       |


                       ↓


              WEB APPLICATION


                       |


                       ↓


              API GATEWAY


                       |


        -------------------------------


        |                               |


    EBP CORE                      PRODUCT SERVICES


    - Authentication                - Restaurant Service
    - Authorization                 - Hotel Service
    - Tenant Management             - Parking Service
    - Billing                       - Farming Service
    - Configuration                 - Legal Service
    - Feature Flag


        |                               |


        -------------------------------


                       |


                       ↓


              DATA LAYER


        - Shared Database
        - Dedicated Database
        - Cache (Redis)
        - File Storage


                       |


                       ↓


              INFRASTRUCTURE


        - Load Balancer
        - Application Server
        - Database Server
        - CDN

```



---

# 5. Multi-Tenant Architecture


## Tenant Isolation


Setiap tenant memiliki:


* tenant_id (unique identifier)
* tenant_code (business code)
* tenant_name (display name)
* subscription_plan (plan type)
* status (active, suspended, cancelled)


## Tenant Data Strategy


### Shared Database (Small Customers)


```
ebp_shared

├── tenants
├── users
├── subscriptions
├── invoices
└── tenant_data
    ├── restaurant_data (tenant_id=1)
    ├── restaurant_data (tenant_id=2)
    └── hotel_data (tenant_id=3)
```


### Dedicated Database (Enterprise Customers)


```
ebp_tenant_001 (Restaurant A)
ebp_tenant_002 (Hotel B)
ebp_tenant_003 (Parking C)
```


### Hybrid Strategy


```
Small Customer (< 100 users) → Shared Database

Medium Customer (100-500 users) → Shared Database with Schema

Enterprise Customer (> 500 users) → Dedicated Database

```



---

# 6. Subscription Management


## Subscription Schema


```sql
CREATE TABLE subscriptions (
    subscription_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    tenant_id BIGINT NOT NULL,
    plan_id BIGINT NOT NULL,
    product_id BIGINT NOT NULL,
    subscription_status ENUM('active', 'suspended', 'cancelled', 'trial') NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    billing_cycle ENUM('monthly', 'yearly') NOT NULL,
    auto_renew BOOLEAN DEFAULT TRUE,
    max_users INT,
    max_storage_gb INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY uk_tenant_product (tenant_id, product_id),
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_status (subscription_status),
    INDEX idx_end_date (end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```


## Plan Schema


```sql
CREATE TABLE subscription_plans (
    plan_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    plan_name VARCHAR(50) NOT NULL,
    plan_code VARCHAR(20) NOT NULL UNIQUE,
    plan_type ENUM('basic', 'professional', 'enterprise') NOT NULL,
    price_monthly DECIMAL(10,2) NOT NULL,
    price_yearly DECIMAL(10,2) NOT NULL,
    max_users INT,
    max_storage_gb INT,
    features JSON NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    
    INDEX idx_plan_type (plan_type),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```


## Invoice Schema


```sql
CREATE TABLE invoices (
    invoice_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    tenant_id BIGINT NOT NULL,
    subscription_id BIGINT NOT NULL,
    invoice_number VARCHAR(50) NOT NULL UNIQUE,
    invoice_date DATE NOT NULL,
    due_date DATE NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    tax_amount DECIMAL(10,2) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('draft', 'sent', 'paid', 'overdue', 'cancelled') NOT NULL,
    payment_method VARCHAR(50),
    paid_at TIMESTAMP NULL,
    
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_status (status),
    INDEX idx_due_date (due_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```



---

# 7. Feature Flag System


## Feature Flag Schema


```sql
CREATE TABLE feature_flags (
    flag_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    flag_name VARCHAR(100) NOT NULL UNIQUE,
    flag_description TEXT,
    is_global BOOLEAN DEFAULT TRUE,
    enabled_for_plans JSON,
    enabled_since TIMESTAMP NULL,
    enabled_until TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_flag_name (flag_name),
    INDEX idx_global (is_global)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```


## Tenant Feature Flags


```sql
CREATE TABLE tenant_feature_flags (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    tenant_id BIGINT NOT NULL,
    flag_name VARCHAR(100) NOT NULL,
    is_enabled BOOLEAN DEFAULT FALSE,
    enabled_at TIMESTAMP NULL,
    enabled_by BIGINT,
    
    UNIQUE KEY uk_tenant_flag (tenant_id, flag_name),
    INDEX idx_tenant_id (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```



---

# 8. API Gateway


## Gateway Responsibilities


* Authentication
* Rate limiting
* Request routing
* Response aggregation
* Logging
* Monitoring


## Gateway Configuration


```yaml
gateway:
  routes:
    - path: /api/v1/restaurant/*
      service: restaurant-service
      rate_limit: 1000/minute
    
    - path: /api/v1/hotel/*
      service: hotel-service
      rate_limit: 500/minute
    
    - path: /api/v1/parking/*
      service: parking-service
      rate_limit: 1000/minute
  
  authentication:
    type: jwt
    issuer: ebp-platform
    secret: ${JWT_SECRET}
  
  rate_limiting:
    enabled: true
    default: 1000/minute
    burst: 100
  
  logging:
    enabled: true
    level: info
```



---

# 9. Service Architecture


## Core Services


### Authentication Service


```
Responsibilities:
- User authentication
- Token generation
- Token validation
- Password management

Endpoints:
- POST /auth/login
- POST /auth/logout
- POST /auth/refresh
- POST /auth/forgot-password
```


### Tenant Service


```
Responsibilities:
- Tenant management
- Tenant configuration
- Tenant status

Endpoints:
- GET /tenants/{id}
- POST /tenants
- PUT /tenants/{id}
- DELETE /tenants/{id}
```


### Billing Service


```
Responsibilities:
- Subscription management
- Invoice generation
- Payment processing
- Usage tracking

Endpoints:
- GET /subscriptions
- POST /subscriptions
- GET /invoices
- POST /payments
```


## Product Services


### Restaurant Service


```
Responsibilities:
- POS
- Order management
- Kitchen display
- Inventory
- Reporting

Endpoints:
- GET /restaurant/orders
- POST /restaurant/orders
- GET /restaurant/inventory
- GET /restaurant/reports
```


### Hotel Service


```
Responsibilities:
- Room management
- Reservation
- Check-in/check-out
- Housekeeping

Endpoints:
- GET /hotel/rooms
- POST /hotel/reservations
- POST /hotel/checkin
- POST /hotel/checkout
```



---

# 10. Data Architecture


## Database Strategy


### Core Database (Shared)


```
ebp_core

├── tenants
├── users
├── roles
├── permissions
├── subscriptions
├── invoices
├── feature_flags
└── audit_logs
```


### Product Database (Shared or Dedicated)


```
ebp_restaurant

├── menus
├── orders
├── inventory
├── customers
└── reports

ebp_hotel

├── rooms
├── reservations
├── guests
└── housekeeping
```


## Cache Strategy


```
Redis

├── sessions
├── api_cache
├── configuration_cache
├── feature_flag_cache
└── rate_limit_cache
```


## File Storage


```

storage/

├── uploads/
│   ├── tenant_001/
│   ├── tenant_002/
│   └── tenant_003/
├── documents/
├── images/
└── backups/

```



---

# 11. Security Architecture


## Multi-Layer Security


### Layer 1: Network Security


* DDoS protection
* Firewall
* SSL/TLS encryption
* IP whitelisting


### Layer 2: Application Security


* Authentication (JWT)
* Authorization (RBAC)
* Input validation
* SQL injection protection
* XSS protection
* CSRF protection


### Layer 3: Data Security


* Encryption at rest
* Encryption in transit
* Data masking
* Backup encryption


### Layer 4: Tenant Security


* Tenant isolation
* Data segregation
* Access control
* Audit logging



---

# 12. Scalability Architecture


## Horizontal Scaling


```

Load Balancer

    ↓

Application Server 1
Application Server 2
Application Server 3

    ↓

Database Cluster (Primary + Replica)

```


## Vertical Scaling


```

Application Server

- More CPU
- More RAM
- More Storage

Database Server

- More CPU
- More RAM
- Faster Storage (SSD)

```


## Auto Scaling


```

CPU > 70% → Scale Up

CPU < 30% → Scale Down

Request Rate > Threshold → Scale Up

Request Rate < Threshold → Scale Down

```



---

# 13. Monitoring and Observability


## Metrics


### Platform Metrics


* Active tenants
* API requests
* Response time
* Error rate
* CPU usage
* Memory usage


### Business Metrics


* MRR (Monthly Recurring Revenue)
* Churn rate
* Trial conversion
* Feature usage
* Plan distribution


## Alerts


### Critical Alerts


* Platform down
* Database down
* Security breach
* Payment failure


### Warning Alerts


* High error rate
* Slow response time
* High CPU usage
* Low disk space



---

# 14. Backup and Disaster Recovery


## Backup Strategy


### Database Backup


```

Daily Backup: Full backup

Hourly Backup: Incremental backup

Real-time: Replication

```


### File Backup


```

Daily Backup: Full backup

Weekly Backup: Archive

```


## Disaster Recovery


### RTO (Recovery Time Objective)


* Critical: 1 hour
* Important: 4 hours
* Normal: 24 hours


### RPO (Recovery Point Objective)


* Critical: 15 minutes
* Important: 1 hour
* Normal: 24 hours



---

# 15. Deployment Architecture


## Environments


### Development


```

Local development

Feature branches

Staging for testing

```


### Staging


```

Production-like environment

Pre-release testing

Performance testing

```


### Production


```

Live environment

High availability

Load balanced

```


## CI/CD Pipeline


```

Code Commit

↓

Build

↓

Test

↓

Deploy to Staging

↓

UAT

↓

Deploy to Production

↓

Monitor

```



---

# 16. API Versioning


## Versioning Strategy


```

/api/v1/restaurant/orders

/api/v2/restaurant/orders

```


## Deprecation Policy


* Old version supported for 6 months
* Notification sent 3 months before deprecation
* Migration guide provided
* Sunset date communicated



---

# 17. Documentation


## Required Documentation


### API Documentation


* OpenAPI/Swagger specification
* Endpoint documentation
* Request/response examples
* Error codes


### Developer Documentation


* Getting started guide
* Architecture overview
* Best practices
* Troubleshooting


### User Documentation


* User manual
- Feature guide
- FAQ
- Video tutorials


### Operational Documentation


* Deployment guide
* Monitoring guide
* Incident response
* Disaster recovery



---

# 18. Compliance


## Data Privacy


* GDPR compliance
* Data retention policy
* Data deletion policy
* Data export functionality


## Security Compliance


* SOC 2 Type II
* ISO 27001
* PCI DSS (if payment processing)


## Service Level Agreement (SLA)


### Uptime


* Basic: 99.5%
* Professional: 99.9%
* Enterprise: 99.95%


### Support


* Basic: Email support (48h response)
* Professional: Email + Chat (24h response)
* Enterprise: Phone + Chat + Dedicated (1h response)



---

# 19. Migration Path


## From Single Application to SaaS Platform


### Phase 1: Foundation


* Implement multi-tenant architecture
* Add subscription management
* Implement billing system


### Phase 2: Product Separation


* Separate restaurant as product
* Implement product-specific features
* Add product subscription


### Phase 3: Platform Expansion


* Add hotel product
* Add parking product
* Add other products


### Phase 4: Enterprise Features


* Add AI features
* Add advanced analytics
* Add custom development



---

# 20. Conclusion


EBP SaaS Platform Architecture memungkinkan:


```

ONE PLATFORM

+

MULTIPLE PRODUCTS

+

MULTIPLE TENANTS

=

SOFTWARE COMPANY

```


Manfaat:


* Recurring revenue
* Scalable architecture
* Multi-product capability
* Professional SaaS platform
* True software company


EBP SaaS Platform Architecture adalah kunci untuk menjadi software company yang sustainable dan profitable.



---

# END OF DOCUMENT


Document ID:

EBP-SAAS-PLATFORM-ARCHITECTURE-001


Version:

1.0
