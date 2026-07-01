# Enterprise Business Platform (EBP)

# Restaurant & Cafe ERP

# Backend Architecture Document


**Document ID:** EBP-RC-BACKEND-ARCHITECTURE-001

**Version:** 1.0

**Backend Technology Target:**

PHP 8.x

MySQL 8.x

REST API Architecture



---

# 1. Introduction


Dokumen ini mendefinisikan arsitektur backend EBP Restaurant & Cafe ERP.


Tujuan:


- memisahkan business logic dengan database;
- menjaga kode mudah dipelihara;
- mendukung pertumbuhan enterprise;
- memungkinkan integrasi AI dan sistem eksternal.


---

# 2. Backend Architecture Philosophy


EBP menggunakan prinsip:


```

Clean Architecture

*

Domain Driven Design

*

Service Repository Pattern

*

API First

```


---

# 3. High Level Backend Architecture


```

CLIENT

(Web / Mobile / POS)

```
    |

    ↓
```

API ROUTER

```
    |

    ↓
```

MIDDLEWARE

```
    |

    ↓
```

CONTROLLER

```
    |

    ↓
```

SERVICE LAYER

```
    |

    ↓
```

BUSINESS ENGINE

```
    |

    ↓
```

REPOSITORY

```
    |

    ↓
```

DATABASE

```


---

# 4. Technology Stack


## Backend


```

PHP 8.x

Composer

PDO MySQL

REST API

JWT Authentication

```


---

## Database


```

MySQL 8.x

InnoDB

Transaction Support

```


---

## Supporting System


```

Redis

Queue Worker

Cron Scheduler

File Storage

Logging System

```


---

# 5. Project Folder Structure


Recommended:


```

ebp-restaurant-backend/

├── app/

│

├── Core/

│   ├── Database/

│   ├── Security/

│   ├── Logger/

│   ├── Exception/

│

├── Config/

│   ├── database.php

│   ├── app.php

│

├── Modules/

│

│   ├── Auth/

│   │

│   │── Controllers/

│   │── Services/

│   │── Repositories/

│   │── Models/

│

│   ├── Sales/

│   ├── Inventory/

│   ├── Purchasing/

│   ├── Accounting/

│   ├── Customer/

│   ├── Menu/

│   └── AI/

│

├── Middleware/

├── Routes/

├── Storage/

│   ├── Logs/

│   ├── Uploads/

├── Tests/

├── Public/

└── Vendor/

```


---

# 6. Module Based Architecture


Setiap modul memiliki:


```

Module

├── Controller

├── Service

├── Repository

├── Model

├── DTO

├── Validator

└── Events

```


Contoh:


```

Sales Module

SalesController

SalesService

SalesRepository

OrderModel

PaymentModel

```


---

# 7. MVC Architecture


EBP menggunakan:


```

MODEL

↓

SERVICE

↓

CONTROLLER

↓

VIEW/API

```


Tetapi:


Business logic TIDAK berada di Controller.


---

# 8. Controller Layer


Tanggung jawab:


- menerima request;
- validasi awal;
- memanggil service;
- mengirim response.


Contoh:


```

OrderController

createOrder()

getOrder()

cancelOrder()

```


Tidak boleh:


```

SQL Query

Business Rule

Calculation

```


---

# 9. Service Layer


Service adalah pusat proses bisnis.


Contoh:


```

OrderService

createOrder()

calculateTotal()

validateOrder()

```


Service mengatur:


- workflow;
- transaction;
- business rule.


---

# 10. Repository Layer


Repository bertanggung jawab:


```

Database Access

```


Contoh:


```

OrderRepository

find()

save()

update()

delete()

```


Tidak boleh:


```

Business Decision

```


---

# 11. Business Engine Architecture


EBP memiliki Business Engine.


Struktur:


```

Business Engine

├── Pricing Engine

├── Inventory Engine

├── Accounting Engine

├── Workflow Engine

├── Notification Engine

├── Forecast Engine

├── AI Engine

```


---

# 12. Pricing Engine


Tugas:


- menghitung harga;
- diskon;
- promo;
- membership.


Contoh:


```

Menu Price

*

Customer Level

*

Promotion Rule

=

Final Price

```


---

# 13. Inventory Engine


Tugas:


- stock calculation;
- recipe deduction;
- reorder.


Contoh:


```

Order Completed

↓

Recipe Calculation

↓

Reduce Ingredient Stock

```


---

# 14. Accounting Engine


Tugas:


Membuat:


```

Journal Entry

Debit

Credit

Ledger

```


Contoh:


Sales:


```

Debit Cash

Credit Revenue

```


---

# 15. Workflow Engine


Mengelola:


```

Approval

Status Change

Business Process

```


Contoh:


Purchase:


```

REQUEST

↓

APPROVAL

↓

ORDER

↓

RECEIVE

```


---

# 16. Middleware Architecture


Middleware berada sebelum Controller.


Flow:


```

Request

↓

Authentication Middleware

↓

Tenant Middleware

↓

Permission Middleware

↓

Controller

```


---

# 17. Authentication Middleware


Fungsi:


- membaca JWT;
- validasi token;
- mengambil user.


---

# 18. Tenant Middleware


Setiap request:


wajib memiliki:


```

tenant_id

```


Contoh:


User Restaurant A


tidak boleh melihat:


Restaurant B.


---

# 19. Permission Middleware


Contoh:


Endpoint:


```

POST /orders

```


Permission:


```

ORDER_CREATE

```


---

# 20. Database Layer


Struktur:


```

Database Manager

↓

Connection Pool

↓

PDO Driver

↓

MySQL

```


---

# 21. Database Standard


Wajib:


- prepared statement;
- transaction;
- foreign key;
- index.


---

# 22. Transaction Management


Contoh:


Create Order:


```

BEGIN TRANSACTION

Create Order

Create Detail

Create Kitchen Queue

Update Stock

Create Audit

COMMIT

```


Jika gagal:


```

ROLLBACK

```


---

# 23. Queue Architecture


Untuk proses berat:


gunakan:


```

Message Queue

```


Contoh:


```

Order Paid

↓

Queue

↓

Send Notification

↓

Generate Report

↓

AI Update

```


---

# 24. Queue Worker


Folder:


```

Workers/

├── NotificationWorker

├── ReportWorker

├── AIWorker

```


---

# 25. Scheduler Architecture


Cron Job:


```

Scheduler

├── Daily Report

├── Stock Check

├── Forecast Update

├── Backup

```


---

# 26. Logging Architecture


Semua aktivitas dicatat.


Level:


```

DEBUG

INFO

WARNING

ERROR

CRITICAL

```


---

# 27. Log Example


```

2026-07-01

USER:

102

ACTION:

CREATE_ORDER

MODULE:

POS

STATUS:

SUCCESS

```


---

# 28. Audit Trail


Berbeda dengan log.


Audit menyimpan:


```

Who

What

When

Before

After

```


---

# 29. Exception Handling


Semua error:


```

Catch

Log

Return Safe Message

```


Tidak boleh:


```

Expose Database Error

```


---

# 30. Security Layer


Backend wajib:


```

Password Hash

JWT

CSRF Protection

Input Validation

SQL Injection Protection

Rate Limit

```


---

# 31. File Upload Management


Untuk:


- menu image;
- invoice;
- document.


Struktur:


```

storage/uploads/

tenant_id/

menu/

invoice/

```


---

# 32. Testing Architecture


Testing:


```

Unit Test

Service Test

API Test

Integration Test

```


---

# 33. Unit Testing


Contoh:


Test:


```

Pricing Engine

```


Input:


```

Price 10000

Discount 10%

```


Expected:


```

9000

```


---

# 34. API Testing


Test:


```

POST /orders

```


Check:


```

Response

Database

Audit

Stock

```


---

# 35. Performance Consideration


Backend harus siap:


```

Caching

Queue

Database Index

Pagination

API Rate Limit

```


---

# 36. Deployment Architecture


Production:


```

Load Balancer

```
    |
```

Application Server

```
    |
```

Database Server

```
    |
```

Storage Server

```


---

# 37. Scaling Strategy


Awal:


```

Single Server

```


Pertumbuhan:


```

Separate API Server

Separate Database

Queue Server

AI Server

```


---

# 38. Development Standard


Setiap fitur wajib:


```

Requirement

↓

API

↓

Service

↓

Repository

↓

Database

↓

Test

```


---

# 39. Backend Development Rule


Developer TIDAK BOLEH:


```

Query SQL di Controller

Mengakses Database langsung dari Frontend

Mengabaikan Audit

Menghapus Data Permanen

```


---

# 40. Conclusion


Backend EBP Restaurant & Cafe ERP dirancang sebagai:


```

Enterprise Application Backend Platform

```


Bukan sekadar:


```

PHP CRUD Application

```


Arsitektur ini siap mendukung:


- restoran;
- cafe;
- franchise;
- multi outlet;
- SaaS;
- AI analytics.


---

# Document End


Document ID:

EBP-RC-BACKEND-ARCHITECTURE-001


Version:

1.0
