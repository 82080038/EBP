# Enterprise Business Platform (EBP)

# Workflow Engine Specification

**Document ID:** EBP-ENTERPRISE-CONTROL-WORKFLOW-ENGINE-001
**Version:** 1.0
**Category:** Enterprise Control Layer
**Status:** Official Architecture Specification

---

# 1. Introduction

Workflow Engine adalah komponen Enterprise Business Platform (EBP) yang mengatur alur proses bisnis, urutan pekerjaan, tanggung jawab pengguna, approval, escalation, dan penyelesaian aktivitas.

Workflow Engine memastikan bahwa:

* proses bisnis berjalan sesuai SOP;
* setiap aktivitas memiliki owner;
* approval tercatat;
* proses dapat diaudit;
* perubahan proses tidak membutuhkan perubahan kode.

---

# 2. Workflow Philosophy

EBP menggunakan prinsip:

> Business process should be modeled, not hard coded.

Artinya:

Tidak:

```php
if(manager==true)
{
approve();
}
```

Tetapi:

```text
Workflow Definition

↓

Workflow Engine

↓

Task Assignment

↓

Approval Process

```

---

# 3. Problem Without Workflow Engine

Dalam aplikasi biasa:

Contoh:

Purchase Request:

```text
Staff membuat permintaan

↓

Email Manager

↓

Manager approve

↓

Tidak ada histori jelas

```

Masalah:

* proses tidak standar;
* sulit audit;
* sulit mengetahui siapa terlambat;
* sulit membuat perubahan.

---

# 4. Workflow Engine Solution

Dengan Workflow Engine:

```text
Business Event

↓

Workflow Instance

↓

Task Generation

↓

User Action

↓

Next Step

↓

Completion

```

---

# 5. Workflow Engine Scope

Workflow Engine menangani:

## 5.1 Approval Workflow

Contoh:

* pembelian;
* pembayaran;
* diskon;
* refund;
* investasi.

---

## 5.2 Operational Workflow

Contoh:

Restaurant:

```text
Order Masuk

↓

Kitchen

↓

Cooking

↓

Quality Check

↓

Delivery

```

---

## 5.3 Document Workflow

Contoh:

* invoice;
* kontrak;
* izin;
* dokumen legal.

---

## 5.4 Employee Workflow

Contoh:

* cuti;
* lembur;
* reimbursement.

---

## 5.5 Exception Workflow

Contoh:

* stok habis;
* transaksi gagal;
* pembayaran terlambat.

---

# 6. Workflow Architecture

Arsitektur:

```text
Business Module

        |

        v

Workflow Engine

        |

        +---- Task Engine

        |

        +---- Approval Engine

        |

        +---- Notification Engine

        |

        +---- Audit Engine

```

---

# 7. Workflow Components

Workflow terdiri dari:

```text
Workflow Definition

Workflow Version

Workflow Instance

Workflow Step

Task

Approval

Transition

Condition

Action

History

```

---

# 8. Workflow Definition

Mendefinisikan template proses.

Contoh:

```text
PURCHASE_APPROVAL_WORKFLOW


Version:

1.0


Status:

Active

```

---

# 9. Workflow Instance

Setiap proses berjalan memiliki instance.

Contoh:

```text
Purchase Request #PR-00123


Workflow:

PURCHASE_APPROVAL


Status:

Waiting Manager Approval

```

---

# 10. Workflow Step

Setiap langkah:

Contoh:

```text
Step 1

Create Request


Step 2

Manager Approval


Step 3

Owner Approval


Step 4

Create Purchase Order

```

---

# 11. Task Engine

Task adalah pekerjaan yang diberikan kepada user.

Contoh:

```text
Task:

Approve Purchase Request


Assigned:

Manager Gudang


Deadline:

24 Hours

```

---

# 12. User Assignment

Workflow mendukung:

## Direct Assignment

```text
User A

```

---

## Role Assignment

```text
Role:

Manager Finance

```

---

## Dynamic Assignment

Contoh:

```text
Nilai transaksi > 100 juta

↓

Owner

```

---

# 13. Approval Engine

Approval mendukung:

## Single Approval

```text
Manager

↓

Approve

```

---

## Sequential Approval

```text
Supervisor

↓

Manager

↓

Owner

```

---

## Parallel Approval

```text
Finance

+

Legal

+

Operational

```

---

# 14. Workflow Condition

Workflow dapat menggunakan:

```text
Rule Engine

```

Contoh:

```text
IF

purchase.amount > 50.000.000


THEN

Owner Approval

```

---

# 15. Workflow Action

Setelah step selesai:

Action:

```text
Send Notification

Create Document

Update Status

Execute Rule

Generate Report

```

---

# 16. Workflow Database Design

## workflows

```sql
id

tenant_id

code

name

version

status

created_at

```

---

## workflow_steps

```sql
id

workflow_id

step_order

step_name

step_type

```

---

## workflow_instances

```sql
id

workflow_id

reference_type

reference_id

status

started_at

completed_at

```

---

## workflow_tasks

```sql
id

instance_id

assigned_user

assigned_role

status

deadline

```

---

## workflow_history

```sql
id

instance_id

action

user_id

old_status

new_status

created_at

```

---

# 17. Workflow State Machine

Workflow menggunakan konsep state.

Contoh:

```text
DRAFT

↓

SUBMITTED

↓

WAITING_APPROVAL

↓

APPROVED

↓

COMPLETED


```

---

# 18. Restaurant ERP Workflow Example

## Purchase Workflow

```text
Kitchen Request Ingredient

↓

Warehouse Review

↓

Purchase Request

↓

Manager Approval

↓

Supplier Order

↓

Receive Goods

↓

Inventory Update

```

---

# 19. Payment Workflow Example

```text
Invoice Created

↓

Verification

↓

Finance Approval

↓

Payment

↓

Accounting Journal

```

---

# 20. Employee Leave Workflow

```text
Employee Submit Leave

↓

Supervisor Approval

↓

HR Verification

↓

Final Approval

↓

Calendar Update

```

---

# 21. Workflow API

## Start Workflow

```http
POST

/api/v1/workflows/start

```

Request:

```json
{
"workflow_code":"PURCHASE_APPROVAL",
"reference_id":"PR001"
}

```

---

## Approve Task

```http
POST

/api/v1/workflows/task/approve

```

---

## Reject Task

```http
POST

/api/v1/workflows/task/reject

```

---

# 22. Notification Integration

Workflow terhubung:

```text
Workflow Engine

↓

Notification Engine

↓

Email

SMS

WhatsApp

Mobile Push

```

---

# 23. SLA Management

Workflow dapat memiliki:

```text
Start Time

Deadline

Reminder

Escalation

```

---

Contoh:

```text
Approval belum selesai 24 jam

↓

Reminder

↓

48 jam

↓

Escalate ke Owner

```

---

# 24. Escalation Engine

Jika user tidak melakukan:

```text
Task Pending

↓

Timeout

↓

Escalation Rule

↓

New Responsible Person

```

---

# 25. Workflow Versioning

Workflow memiliki:

```text
Version

Effective Date

Change History

```

---

Contoh:

```text
Purchase Workflow v1

digunakan sampai:

31-12-2026


Purchase Workflow v2

mulai:

01-01-2027

```

---

# 26. Workflow Security

Permission:

```text
Create Workflow

Edit Workflow

Approve Task

View History

Admin Workflow

```

---

# 27. Workflow Audit

Semua aktivitas dicatat:

```text
Who

When

Action

Previous State

New State

Comment

```

---

# 28. Workflow Testing

Setiap workflow wajib memiliki:

## Scenario Test

Contoh:

```text
Purchase < 10 juta

Expected:

Manager Approval

```

```text
Purchase > 100 juta

Expected:

Owner Approval

```

---

# 29. AI Assisted Workflow Design

Future capability:

AI membantu:

Input:

```text
"buat workflow pembelian restoran"

```

AI menghasilkan:

```text
Request

Approval

Purchase Order

Receiving

Payment

```

Kemudian manusia melakukan approval.

---

# 30. Workflow Engine Relationship

Hubungan:

```text
Configuration Engine

        |

Rule Engine

        |

Workflow Engine

        |

Business Engine

        |

Product Module

```

---

# 31. Final Architecture Vision

Workflow Engine menjadikan EBP:

```text
Application Software

        ↓

Business Process Platform

        ↓

Enterprise Operating System

```

---

# END OF DOCUMENT

Document ID:

EBP-ENTERPRISE-CONTROL-WORKFLOW-ENGINE-001

Version:

1.0
