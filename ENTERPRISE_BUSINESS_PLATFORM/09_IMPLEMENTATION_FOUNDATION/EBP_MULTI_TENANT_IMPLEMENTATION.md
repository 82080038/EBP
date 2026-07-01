# Enterprise Business Platform (EBP)

# Multi Tenant Implementation

**Document ID:** EBP-IMPLEMENTATION-FOUNDATION-MULTITENANT-001
**Version:** 1.0
**Category:** Implementation Foundation
**Status:** Official SaaS Architecture Standard

---

# 1. Introduction

Multi Tenant Architecture adalah kemampuan EBP untuk menjalankan satu platform software yang digunakan oleh banyak organisasi/perusahaan secara bersamaan.

Contoh:

```text
EBP CLOUD PLATFORM


        |

        |

+-------+--------+---------+

|                |         |

v                v         v


Restaurant A   Hotel B   Parking C


```

---

# 2. Multi Tenant Philosophy

EBP menggunakan prinsip:

> One platform, many businesses, isolated data.

Artinya:

Satu platform.

Banyak perusahaan.

Data tidak boleh bercampur.

---

# 3. Multi Tenant Objectives

Tujuan:

* mendukung SaaS;
* efisiensi server;
* satu kode untuk banyak pelanggan;
* mudah maintenance;
* keamanan data.

---

# 4. Tenant Definition

Tenant adalah:

> Entitas bisnis yang menggunakan EBP sebagai sistem operasionalnya.

Contoh:

```text
Tenant:

PT ABC Restaurant


Tenant:

Hotel XYZ


Tenant:

CV Parking Indonesia

```

---

# 5. Tenant Hierarchy

Struktur:

```text
SYSTEM

 |

 v

TENANT

 |

 v

BRANCH

 |

 v

LOCATION

 |

 v

USER

```

---

Contoh:

```text
EBP Platform


    |

    v


Restaurant Group


    |

    +------ Branch Jakarta

    |

    +------ Branch Medan

    |

    +------ Branch Bali

```

---

# 6. Multi Tenant Architecture Model

EBP mendukung tiga model.

---

# Model 1: Shared Database Shared Schema

Semua tenant:

```text
1 Database


1 Table


tenant_id

```

Contoh:

```sql
orders


id

tenant_id

customer

amount

```

---

Kelebihan:

* murah;
* mudah maintenance;
* cocok SaaS besar.

Kekurangan:

* query harus selalu tenant aware.

---

# Model 2: Shared Database Separate Schema

Contoh:

```text
database


tenant_001

tenant_002

tenant_003

```

---

Kelebihan:

* isolasi lebih baik.

Kekurangan:

* maintenance lebih kompleks.

---

# Model 3: Separate Database

Setiap tenant:

```text
Tenant A

database_a


Tenant B

database_b

```

---

Kelebihan:

* keamanan maksimum.

Kekurangan:

* biaya tinggi.

---

# 7. Recommended EBP Strategy

EBP menggunakan hybrid:

```text
CORE DATABASE


Shared Database


+

Product Database


Tenant Based Isolation

```

---

# 8. Database Architecture

```text
              EBP CORE DB


                 |

                 |

        Tenant Management


                 |

        +--------+---------+

        |                  |

        v                  v


Restaurant DB        Hotel DB


Tenant Filter       Tenant Filter

```

---

# 9. Tenant Table

Core:

```sql
CREATE TABLE tenants (

id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

code VARCHAR(50),

name VARCHAR(150),

status VARCHAR(20),

subscription_plan_id BIGINT,

created_at DATETIME

);

```

---

# 10. Tenant Profile

```sql
tenant_profiles


id

tenant_id

address

phone

email

logo

timezone

currency

```

---

# 11. Tenant Settings

Menyimpan konfigurasi:

```text
Tax

Currency

Language

Invoice

Business Rules

Theme

```

---

Table:

```sql
tenant_settings


id

tenant_id

key

value

```

---

# 12. Tenant Resolver

Setiap request:

```text
REQUEST


 |

 v


Identify Tenant


 |

 v


Load Tenant Context


 |

 v


Execute Business

```

---

# 13. Tenant Identification Method

EBP mendukung:

## Subdomain

Contoh:

```text
restaurantabc.ebp.com

```

---

## Header

API:

```http
X-Tenant-ID:100

```

---

## Token

JWT:

```json
{
"tenant_id":100
}

```

---

# 14. Tenant Context

Saat aplikasi berjalan:

```php
TenantContext:


tenant_id

tenant_name

database

configuration

```

---

# 15. Middleware Implementation

Flow:

```text
Request


↓

Tenant Middleware


↓

Find Tenant


↓

Set Context


↓

Continue

```

---

# 16. Database Query Rule

Semua query tenant wajib:

```sql
WHERE tenant_id = ?

```

---

Contoh:

Salah:

```sql
SELECT *
FROM orders;

```

---

Benar:

```sql
SELECT *
FROM orders

WHERE tenant_id=10;

```

---

# 17. Repository Tenant Safety

Repository:

```php
class OrderRepository{


public function all(){


return DB::table('orders')

->where(
'tenant_id',
Tenant::id()
)

->get();


}

}

```

---

# 18. Tenant Data Isolation

Wajib:

* tenant filter;
* permission check;
* database separation;
* audit.

---

# 19. User Tenant Relationship

User dapat:

```text
1 User

+

Multiple Tenant

```

Contoh:

Owner:

```text
John


Tenant A

Tenant B

Tenant C

```

---

Database:

```text
user_tenants


user_id

tenant_id

role_id

```

---

# 20. Tenant Role

Role dapat berbeda:

Contoh:

```text
Restaurant A

Manager


Restaurant B

Staff

```

---

# 21. Subscription Management

Tenant memiliki:

```text
Plan

License

Quota

Billing

Status

```

---

# 22. Subscription Table

```sql
subscriptions


id

tenant_id

plan

start_date

end_date

status

```

---

# 23. Feature Limitation

Contoh:

Plan Basic:

```text
1000 transaksi/bulan

```

Enterprise:

```text
Unlimited

AI Forecast

Advanced Report

```

---

# 24. Tenant Configuration Engine

Setiap tenant dapat memiliki:

```text
Tax berbeda

Harga berbeda

Workflow berbeda

Approval berbeda

```

---

# 25. Tenant Security Rules

Tidak boleh:

```text
Tenant A membaca Tenant B

Tenant A melihat laporan Tenant B

Tenant A mengakses API Tenant B

```

---

# 26. Audit Tenant

Semua audit:

```text
tenant_id

user_id

action

timestamp

```

---

# 27. Backup Strategy

Model:

## Shared Database

Backup:

```text
Database Full

Tenant Export

```

---

## Separate Database

Backup:

```text
Per Tenant

```

---

# 28. Tenant Migration

Jika tenant berkembang:

```text
Shared DB


        |

        v


Dedicated DB

```

---

# 29. Tenant Lifecycle

```text
Register Tenant


↓

Setup Configuration


↓

Create Admin


↓

Activate Subscription


↓

Business Operation


↓

Renew / Disable

```

---

# 30. Example Restaurant ERP

Tenant:

```text
Warung Batak Samosir

```

Data:

```text
Menu

Order

Inventory

Employee

Finance

```

Semua:

```text
tenant_id=100

```

---

# 31. Testing Multi Tenant

Test wajib:

## Isolation Test

```text
Tenant A login


Tidak boleh melihat Tenant B

```

---

## Permission Test

```text
Admin Tenant A


Tidak boleh akses System Admin

```

---

## API Test

```text
Request Tenant ID berbeda

Harus ditolak

```

---

# 32. Security Enhancement

Future:

```text
Row Level Security

Database Encryption

Tenant Specific Key

Zero Trust Architecture

```

---

# 33. Implementation Roadmap

Phase 1:

```text
tenant_id column

Tenant Middleware

Tenant Context

```

---

Phase 2:

```text
Subscription

Feature Control

Billing

```

---

Phase 3:

```text
Database Isolation

Enterprise Security

```

---

# 34. Final Vision

Multi Tenant menjadikan EBP:

```text
Single Application


        ↓


Multi Company Platform


        ↓


SaaS Enterprise Business Platform


        ↓


Software Company Infrastructure

```

---

# END OF DOCUMENT

Document ID:

EBP-IMPLEMENTATION-FOUNDATION-MULTITENANT-001

Version:

1.0
