# Enterprise Business Platform (EBP)

# Core Database Implementation

**Document ID:** EBP-IMPLEMENTATION-FOUNDATION-DATABASE-001
**Version:** 1.0
**Category:** Implementation Foundation
**Status:** Official Database Implementation Standard

---

# 1. Introduction

Database Core EBP adalah database utama yang menyediakan layanan fundamental untuk seluruh produk dalam Enterprise Business Platform.

Database Core tidak menyimpan transaksi bisnis spesifik produk.

Contoh:

Core menyimpan:

```text
User

Tenant

Role

Permission

Configuration

Workflow

Audit

Event

Notification

```

Sedangkan produk menyimpan:

```text
Restaurant:

Order

Menu

Kitchen

Inventory


Hotel:

Room

Reservation

Guest


Parking:

Ticket

Vehicle

Payment

```

---

# 2. Database Architecture

EBP menggunakan konsep:

```text
             APPLICATION


                 |


                 v


          CORE DATABASE


                 |


        +--------+--------+

        |                 |

        v                 v


 PRODUCT DATABASE     SHARED DATABASE

```

---

# 3. Database Separation Strategy

EBP menggunakan tiga jenis database:

## 3.1 Core Database

Nama:

```sql
ebp_core

```

Berisi:

* identity;
* security;
* configuration;
* system control.

---

## 3.2 Product Database

Contoh:

Restaurant:

```sql
ebp_restaurant

```

Hotel:

```sql
ebp_hotel

```

Parking:

```sql
ebp_parking

```

---

## 3.3 Reporting Database

Untuk:

* BI;
* dashboard;
* analytics.

Contoh:

```sql
ebp_warehouse

```

---

# 4. Core Database Principles

Database wajib:

* memiliki primary key;
* memiliki audit column;
* mendukung soft delete;
* memiliki tenant isolation;
* memiliki indexing;
* memiliki migration.

---

# 5. Database Naming Convention

Format:

```text
lowercase_snake_case

```

Contoh:

Benar:

```sql
user_accounts

tenant_settings

audit_logs

```

Salah:

```sql
UserAccounts

TenantSettings

```

---

# 6. Primary Key Standard

Semua tabel menggunakan:

```sql
BIGINT UNSIGNED

```

Contoh:

```sql
id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY

```

---

# 7. Universal Audit Column

Semua tabel wajib:

```sql
created_at DATETIME

created_by BIGINT


updated_at DATETIME

updated_by BIGINT


deleted_at DATETIME NULL


deleted_by BIGINT NULL

```

---

# 8. Tenant Column Standard

Untuk tabel multi tenant:

Wajib:

```sql
tenant_id BIGINT UNSIGNED

```

Contoh:

```sql
users

id

tenant_id

name

email

```

---

# 9. Core Database Structure

```text
ebp_core


├── identity

├── security

├── configuration

├── workflow

├── rules

├── events

├── audit

├── notification

├── system

```

---

# 10. Identity Module

Mengatur identitas pengguna.

Tabel:

```text
users

user_profiles

user_sessions

user_devices

```

---

# 11. Table: users

```sql
CREATE TABLE users (

id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

tenant_id BIGINT UNSIGNED,

username VARCHAR(100),

email VARCHAR(150),

password_hash VARCHAR(255),

status VARCHAR(20),

created_at DATETIME,

created_by BIGINT,

updated_at DATETIME,

updated_by BIGINT,

deleted_at DATETIME NULL

);

```

---

# 12. User Profile

```sql
user_profiles


id

user_id

full_name

phone

address

photo

```

---

# 13. Session Management

Table:

```text
user_sessions

```

Menyimpan:

```text
login

logout

device

ip address

token

```

---

# 14. Security Module

Struktur:

```text
roles

permissions

role_permissions

user_roles

```

---

# 15. Role Table

Contoh:

```text
SUPER_ADMIN

TENANT_ADMIN

MANAGER

STAFF

CUSTOMER

```

---

# 16. Permission Table

Contoh:

```text
restaurant.order.create

restaurant.order.delete

inventory.stock.view

finance.report.view

```

---

# 17. RBAC Relationship

```text
USER

 |

 v

USER_ROLE

 |

 v

ROLE

 |

 v

PERMISSION

```

---

# 18. Configuration Module

Tujuan:

Menghindari hard coding.

Contoh:

```text
tax_rate

currency

timezone

invoice_format

```

---

# 19. Configuration Table

```sql
system_configurations


id

tenant_id

config_key

config_value

type

```

---

# 20. Workflow Module

Menyimpan:

* workflow definition;
* workflow step;
* approval.

Tabel:

```text
workflow_definitions

workflow_steps

workflow_instances

workflow_logs

```

---

# 21. Rule Engine Storage

Tabel:

```text
business_rules

rule_conditions

rule_actions

```

---

# 22. Event System

Event disimpan:

```text
events

event_handlers

event_logs

```

---

# 23. Event Table

```sql
events


id

event_name

aggregate_type

payload

status

created_at

```

---

# 24. Audit Trail Module

Tujuan:

Merekam semua perubahan penting.

Tabel:

```text
audit_logs

```

---

# 25. Audit Log Structure

```sql
audit_logs


id

tenant_id

user_id

action

table_name

record_id

old_value

new_value

created_at

```

---

# 26. Notification Module

Tabel:

```text
notifications

notification_templates

notification_logs

```

---

# 27. System Module

Berisi:

```text
system_settings

system_logs

maintenance_logs

```

---

# 28. Database Migration Strategy

Tidak membuat database manual.

Menggunakan:

```text
migration files


001_create_users.sql

002_create_roles.sql

003_create_permission.sql

```

---

# 29. Database Version Control

Setiap perubahan:

```text
Migration

↓

Review

↓

Testing

↓

Deploy

```

---

# 30. Index Strategy

Index wajib:

```sql
tenant_id

created_at

updated_at

status

foreign_key

```

---

# 31. Soft Delete Strategy

Tidak menghapus:

```sql
DELETE FROM users

```

Tetapi:

```sql
UPDATE users

SET deleted_at=NOW()

```

---

# 32. Database Security

Wajib:

* prepared statement;
* least privilege user;
* encrypted backup;
* restricted access.

---

# 33. Backup Strategy

Level:

## Daily Backup

```text
incremental

```

## Weekly Backup

```text
full backup

```

## Monthly Backup

```text
archive

```

---

# 34. Disaster Recovery

Target:

## RPO

Recovery Point Objective

Contoh:

```text
maksimal kehilangan data 1 jam

```

---

## RTO

Recovery Time Objective

Contoh:

```text
sistem kembali online < 4 jam

```

---

# 35. Database Performance Strategy

Menggunakan:

```text
Indexing

Query Optimization

Caching

Partitioning

Archive Table

```

---

# 36. Core Database Example Flow

Login:

```text
User

↓

users

↓

roles

↓

permissions

↓

Application Access

```

---

# 37. Product Integration Example

Restaurant ERP:

```text
Restaurant Database


        |

        v


EBP Core


        |

        v


Authentication

Permission

Audit

Configuration

```

---

# 38. Development Rules

Tidak boleh:

```text
Product membuat tabel user sendiri

Product membuat permission sendiri tanpa core

Tidak ada audit

Hard coding configuration

```

---

# 39. Final Database Vision

EBP Database Architecture:

```text
Data Storage

      ↓

Business Data Platform

      ↓

Enterprise Knowledge Foundation

      ↓

AI Ready Data Platform

```

---

# END OF DOCUMENT

Document ID:

EBP-IMPLEMENTATION-FOUNDATION-DATABASE-001

Version:

1.0
