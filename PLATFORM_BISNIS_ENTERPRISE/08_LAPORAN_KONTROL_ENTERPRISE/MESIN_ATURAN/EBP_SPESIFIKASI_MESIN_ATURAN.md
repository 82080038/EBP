# Enterprise Business Platform (EBP)

# Rule Engine Specification

**Document ID:** EBP-ENTERPRISE-CONTROL-RULE-ENGINE-001
**Version:** 1.0
**Category:** Enterprise Control Layer
**Status:** Official Architecture Specification

---

# 1. Introduction

Rule Engine adalah komponen inti Enterprise Business Platform (EBP) yang bertanggung jawab menjalankan aturan bisnis secara dinamis tanpa melakukan perubahan kode program.

Rule Engine memungkinkan:

* perubahan aturan bisnis tanpa deployment;
* konfigurasi aturan per tenant;
* mendukung banyak industri;
* mengurangi hard-coded business logic;
* meningkatkan fleksibilitas enterprise system.

---

# 2. Philosophy

EBP menggunakan prinsip:

> Business rules are data, not code.

Artinya:

Aturan bisnis:

Tidak:

```php
if($customer=="VIP"){
   discount=10;
}
```

Tetapi:

```text
Business Data

↓

Rule Engine

↓

Business Decision

```

---

# 3. Problem Without Rule Engine

Tanpa Rule Engine:

Contoh:

Restaurant:

```php
if(total > 500000)
{
 discount=10;
}

if(member=="gold")
{
 discount=15;
}

```

Masalah:

* setiap perubahan harus coding;
* risiko bug;
* sulit multi tenant;
* sulit audit.

---

# 4. Rule Engine Solution

Dengan Rule Engine:

```text
Transaction

↓

Rule Evaluation

↓

Decision

↓

Action

```

---

# 5. Rule Engine Scope

Rule Engine menangani:

## 5.1 Pricing Rule

Contoh:

* diskon;
* harga khusus;
* promo.

---

## 5.2 Approval Rule

Contoh:

Pembelian:

```text
Jika nilai pembelian > 50 juta

maka

approval owner
```

---

## 5.3 Inventory Rule

Contoh:

```text
Jika stok < minimum

buat purchase suggestion

```

---

## 5.4 Accounting Rule

Contoh:

```text
Penjualan selesai

↓

buat jurnal otomatis

```

---

## 5.5 Notification Rule

Contoh:

```text
Invoice jatuh tempo

↓

kirim notifikasi
```

---

## 5.6 Security Rule

Contoh:

```text
Manager

boleh approve

< 10 juta

```

---

# 6. Rule Architecture

Arsitektur:

```text
Business Event

        |

        v

Rule Engine

        |

        v

Condition Evaluation

        |

        v

Action Execution

```

---

# 7. Rule Components

Rule Engine terdiri dari:

```text
Rule Definition

Rule Condition

Rule Action

Rule Priority

Rule Version

Rule Execution

Rule History

```

---

# 8. Rule Definition

Mendefinisikan aturan.

Contoh:

```text
Rule:

Member Discount


Code:

DISC_MEMBER_001


Status:

Active

```

---

# 9. Rule Condition

Kondisi yang harus dipenuhi.

Contoh:

```json
{
"field":"customer.member_level",
"operator":"=",
"value":"GOLD"
}

```

---

# 10. Supported Operators

Minimal:

```text
=

!=

>

<

>=

<=

IN

BETWEEN

CONTAINS

EXISTS

```

---

# 11. Rule Action

Apa yang dilakukan jika rule terpenuhi.

Contoh:

```json
{
"action":"APPLY_DISCOUNT",
"value":10
}

```

---

# 12. Rule Priority

Jika banyak rule aktif:

Contoh:

```text
Priority 1

VIP Customer


Priority 2

Weekend Promo


Priority 3

General Discount

```

Urutan:

```text
Priority kecil

=

lebih tinggi
```

---

# 13. Rule Versioning

Setiap rule memiliki:

```text
version

effective_date

expired_date

```

Contoh:

```text
Promo Lebaran 2026

aktif:

01-03-2026

s/d

30-04-2026

```

---

# 14. Rule Database Design

## rules

```sql
id

tenant_id

rule_code

rule_name

module

status

priority

version

created_at

updated_at

```

---

## rule_conditions

```sql
id

rule_id

field

operator

value

logic_operator

```

---

## rule_actions

```sql
id

rule_id

action_type

action_value

```

---

## rule_execution_logs

```sql
id

rule_id

transaction_id

input_data

result

executed_at

```

---

# 15. Rule Evaluation Flow

Contoh:

Order Restaurant:

```text
Customer Order

↓

Load Active Rules

↓

Evaluate Conditions

↓

Execute Actions

↓

Save Result

```

---

# 16. Example Restaurant Discount Rule

Business:

Member Gold mendapat diskon.

Rule:

```json
{
"rule":"MEMBER_GOLD_DISCOUNT",

"condition":
{
"customer.level":"GOLD"
},

"action":
{
"discount":10
}

}

```

---

# 17. Example Inventory Rule

Business:

Stok minimum.

Rule:

```json
{
"condition":

{
"stock.qty":"<",
"stock.minimum":true
},

"action":

{
"create_purchase_request":true
}

}

```

---

# 18. Example Approval Rule

Business:

Pembelian besar.

Rule:

```text
IF

purchase.amount > 50000000


THEN

approval = OWNER

```

---

# 19. Rule Execution Context

Setiap rule menerima:

```json
{
"user":{},
"tenant":{},
"transaction":{},
"time":{},
"location":{}
}

```

---

# 20. Rule Engine Integration

Rule Engine digunakan oleh:

```text
Pricing Engine

Inventory Engine

Accounting Engine

Workflow Engine

Notification Engine

AI Engine

```

---

# 21. Rule Engine API

## Execute Rule

```http
POST

/api/v1/rules/execute

```

Request:

```json
{
"rule_code":"DISC_MEMBER",
"context":{}
}

```

Response:

```json
{
"success":true,
"result":{
"discount":10
}
}

```

---

# 22. Rule Security

Tidak semua user boleh:

* membuat rule;
* mengubah rule;
* menghapus rule.

Permission:

```text
SYSTEM_ADMIN

TENANT_OWNER

BUSINESS_MANAGER

USER

```

---

# 23. Rule Audit

Setiap perubahan:

dicatat:

```text
Who

When

Before

After

Reason

```

---

# 24. Rule Testing

Setiap rule wajib memiliki test case.

Contoh:

Rule:

```text
Member Gold Discount

```

Test:

```text
Input:

Gold Member

Total 100000


Expected:

Discount 10%

```

---

# 25. Rule Simulation Mode

Sebelum aktif:

rule dapat diuji:

```text
Simulation

↓

Review Result

↓

Activate

```

---

# 26. AI Assisted Rule Generation

Future capability:

AI dapat membantu:

```text
Business Description

↓

Generate Rule

↓

Human Approval

↓

Activate

```

Contoh:

Input:

> "Berikan diskon pelanggan lama"

AI membuat:

```text
Customer.age > 365 days

Action:

Discount 5%

```

---

# 27. Rule Marketplace Concept

Future:

EBP dapat memiliki:

```text
Industry Rule Package
```

Contoh:

Restaurant:

```text
Restaurant Promotion Package

Food Cost Rule Package

Inventory Rule Package

```

---

# 28. Rule Engine Principles

Aturan:

```text
No Hard Coding Business Rule

All Important Decisions Logged

Rules Must Be Versioned

Rules Must Be Testable

Rules Must Be Auditable

```

---

# 29. Relationship With Other Engines

```text
Configuration Engine

        |

        v

Rule Engine

        |

        v

Workflow Engine

        |

        v

Business Engine

        |

        v

Product Module

```

---

# 30. Final Architecture Vision

Rule Engine menjadikan EBP:

```text
Static Application

        ↓

Configurable Platform

        ↓

Adaptive Business Operating System

```

---

# END OF DOCUMENT

Document ID:

EBP-ENTERPRISE-CONTROL-RULE-ENGINE-001

Version:

1.0
