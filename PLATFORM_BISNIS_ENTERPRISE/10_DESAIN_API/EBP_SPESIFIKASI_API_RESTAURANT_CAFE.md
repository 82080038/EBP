# Enterprise Business Platform (EBP)

# Restaurant & Cafe ERP

# API Specification Document


**Document ID:** EBP-RC-API-SPECIFICATION-001

**Version:** 1.0

**Architecture:** REST API Enterprise Standard

**Backend Target:** PHP 8.x Enterprise Framework

**Database:** MySQL 8.x



---

# 1. Document Purpose


Dokumen ini mendefinisikan standar API untuk EBP Restaurant & Cafe ERP.


API bertanggung jawab sebagai:


- komunikasi frontend dan backend;
- integrasi antar modul;
- keamanan akses data;
- validasi bisnis;
- workflow execution.


---

# 2. API Design Principle


EBP menggunakan prinsip:


```

API First Architecture

```


Artinya:

Semua modul harus memiliki API sebelum dibuatkan interface.


---

# 3. API Architecture


Struktur:


```

CLIENT

(Web / Mobile / POS Device)

```
    |

    | HTTPS


    ↓
```

API GATEWAY

```
    |
```

AUTHENTICATION

```
    |
```

CONTROLLER

```
    |
```

SERVICE LAYER

```
    |
```

BUSINESS ENGINE

```
    |
```

REPOSITORY

```
    |
```

DATABASE

```


---

# 4. API Standard


## Protocol


```

HTTPS

REST API

JSON

```


---

## Base URL


Production:


```

https://api.domain.com/v1/

```


Development:


```

http://localhost/api/v1/

```


---

# 5. HTTP Method Standard


| Method | Function |
|-|-|
| GET | mengambil data |
| POST | membuat data |
| PUT | update data |
| PATCH | perubahan sebagian |
| DELETE | soft delete |


---

# 6. Response Standard


Semua response menggunakan format:


```json
{
 "success": true,
 "message": "Operation success",
 "data": {},
 "errors": []
}
```


---

# 7. Error Response

Contoh:

```json
{
 "success": false,
 "message": "Validation error",
 "errors":[
    {
      "field":"menu_name",
      "message":"Required"
    }
 ]
}
```


---

# 8. Authentication

EBP menggunakan:

```

JWT Authentication

+

Refresh Token

```

Flow:

```

Login

 ↓

Access Token

 ↓

API Request

 ↓

Token Validation

 ↓

Response

```


---

# 9. Authentication API

## Login

```

POST

/auth/login

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
 "access_token":"xxxxx",
 "refresh_token":"xxxxx",
 "user":{
    "id":1,
    "role":"manager"
 }
}
```


---

## Logout

```

POST

/auth/logout

```


---

# 10. Tenant API

## Get Tenant Profile

```

GET

/tenant/profile

```

Response:

```json
{
 "tenant_name":"Restaurant ABC",
 "business_type":"Restaurant"
}
```


---

# 11. User Management API

## List User

```

GET

/users

```


---

## Create User

```

POST

/users

```

Request:

```json
{
"name":"John",
"username":"john",
"role_id":3
}
```


---

## Update User

```

PUT

/users/{id}

```


---

# 12. Branch API

## List Branch

```

GET

/branches

```


---

## Create Branch

```

POST

/branches

```


---

# 13. Customer API

## Customer List

```

GET

/customers

```


---

## Create Customer

```

POST

/customers

```

Request:

```json
{
"name":"Customer A",
"phone":"081xxx"
}
```


---

# 14. Menu Management API

## Get Menu

```

GET

/menu

```

Filter:

```

category

status

branch

```


---

## Create Menu

```

POST

/menu

```

Request:

```json
{
"name":"Nasi Goreng",
"price":25000,
"category_id":1
}
```


---

## Update Menu Price

```

PATCH

/menu/{id}/price

```


---

# 15. Recipe API

## Create Recipe

```

POST

/menu/{id}/recipe

```

Request:

```json
{
"ingredients":[
 {
  "item_id":10,
  "qty":0.2
 }
]
}
```


---

# 16. Table Management API

## Table Status

```

GET

/tables/status

```

Response:

```json
[
{
"id":1,
"status":"AVAILABLE"
}
]
```


---

# 17. ORDER API

## Create Order

```

POST

/orders

```

Request:

```json
{
"table_id":5,

"items":[

{
"menu_id":1,
"qty":2
}

]
}
```

Process:

```

Validate User

↓

Check Menu

↓

Check Price

↓

Create Order

↓

Send Kitchen

↓

Audit Log

```


---

## Get Order

```

GET

/orders/{id}

```


---

## Cancel Order

```

POST

/orders/{id}/cancel

```

Require:

```

reason

approval

```


---

# 18. Kitchen API

## Kitchen Queue

```

GET

/kitchen/orders

```


---

## Update Cooking Status

```

PUT

/kitchen/orders/{id}/status

```

Status:

```

NEW

COOKING

READY

SERVED

```


---

# 19. POS Payment API

## Create Payment

```

POST

/payments

```

Request:

```json
{
"order_id":1001,

"method":"CASH",

"amount":50000
}
```

Process:

```

Payment Validation

↓

Invoice

↓

Accounting Journal

↓

Close Transaction

```


---

# 20. Inventory API

## Stock Information

```

GET

/inventory/stock

```


---

## Stock Movement

```

POST

/inventory/movement

```

Example:

```json
{
"item_id":20,
"type":"WASTE",
"qty":2
}
```


---

# 21. Purchasing API

## Purchase Request

```

POST

/purchase/request

```


---

## Purchase Order

```

POST

/purchase/order

```


---

## Goods Receipt

```

POST

/purchase/receive

```

Effect:

```

Stock Increase

Accounting Update

Audit

```


---

# 22. Supplier API

```

GET

/suppliers


POST

/suppliers

PUT

/suppliers/{id}

```


---

# 23. Accounting API

## Journal

```

GET

/accounting/journal

```


---

## Financial Report

```

GET

/accounting/report/profit-loss

```


---

# 24. Reporting API

Dashboard:

```

GET

/dashboard


```

Response:

```json
{
"sales_today":5000000,
"transaction":120,
"profit":1500000
}
```


---

# 25. Notification API

## Get Notification

```

GET

/notifications

```


---

# 26. AI Analytics API

## Sales Prediction

```

GET

/ai/forecast/sales

```


---

## Menu Recommendation

```

GET

/ai/menu/recommendation

```


---

## Fraud Detection

```

GET

/ai/fraud

```


---

# 27. Integration API

External system:

```

Payment Gateway

Delivery Platform

Accounting Software

Printer

Marketplace

```

Example:

```

POST

/integration/payment/callback

```


---

# 28. Authorization Rule

Setiap API memiliki permission:

Contoh:

```

POST /orders


Permission:

ORDER_CREATE


```


---

# 29. API Audit

Semua request penting dicatat:

```

user

endpoint

request

response

ip

time

```


---

# 30. API Versioning

Format:

```

/api/v1/

```

Jika perubahan besar:

```

/api/v2/

```


---

# 31. API Security Requirement

Wajib:

```

HTTPS

JWT

Rate Limit

Input Validation

SQL Injection Protection

Audit Logging

```


---

# 32. Transaction Consistency

Untuk transaksi penting:

gunakan:

```

Database Transaction

BEGIN

COMMIT

ROLLBACK

```

Contoh:

Order:

```

Create Order

+

Reduce Stock

+

Create Kitchen Order

+

Create Audit


Jika gagal:

ROLLBACK

```


---

# 33. API Module Dependency

```

AUTH

 |

USER

 |

MASTER DATA

 |

ORDER

 |

PAYMENT

 |

INVENTORY

 |

ACCOUNTING

 |

REPORT

 |

AI

```


---

# 34. Future Expansion

API siap mendukung:

```

Mobile Application

Customer App

Self Ordering

QR Menu

Franchise System

AI Assistant

```


---

# 35. Conclusion

API EBP Restaurant & Cafe ERP dirancang sebagai:

```

Enterprise Integration Layer

```

Bukan hanya:

```

CRUD Endpoint

```

API harus:

* aman;
* konsisten;
* scalable;
* auditable;
* siap integrasi.


---

# Document End

Document ID:

EBP-RC-API-SPECIFICATION-001

Version:

1.0
