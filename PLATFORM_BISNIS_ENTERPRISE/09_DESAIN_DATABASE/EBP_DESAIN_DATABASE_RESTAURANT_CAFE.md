# Enterprise Business Platform (EBP)
# Restaurant & Cafe ERP
# Database Design Document


**Document ID:** EBP-RC-DATABASE-DESIGN-001  
**Version:** 1.0  
**Database Engine:** MySQL Enterprise  
**Classification:** Enterprise Data Architecture  
**Product:** Restaurant & Cafe ERP  


---

# 1. Pendahuluan


## 1.1 Tujuan


Dokumen ini mendefinisikan desain database untuk EBP Restaurant & Cafe ERP.


Database harus mampu:


- mendukung UMKM;
- mendukung multi outlet;
- mendukung franchise;
- mendukung enterprise;
- mendukung AI analytics.


---

# 2. Database Philosophy


EBP menggunakan prinsip:


```

Data Is A Business Asset

```


Database bukan hanya tempat menyimpan data.


Database adalah:


- histori bisnis;
- sumber laporan;
- sumber keputusan;
- sumber AI.


---

# 3. Database Architecture


Arsitektur:


```

Tenant Layer

```
  ↓
```

Organization Layer

```
  ↓
```

Master Data Layer

```
  ↓
```

Transaction Layer

```
  ↓
```

Accounting Layer

```
  ↓
```

Analytics Layer

```


---

# 4. Multi Tenant Structure


EBP menggunakan:


```

Multi Tenant Database Architecture

```


Struktur:


```

EBP PLATFORM

|

+----------------+

| Tenant A       |

| Restaurant A   |

+----------------+

|

+----------------+

| Tenant B       |

| Cafe B         |

+----------------+

```


---

# 5. Tenant Core Table


## 5.1 table: tenants


Fungsi:


Menyimpan perusahaan pengguna EBP.


Field:


```

tenant_id PK

tenant_code

tenant_name

business_type

status

created_at

updated_at

```


---

# 6. Organization Structure


## 6.1 companies


```

company_id PK

tenant_id FK

company_name

tax_number

address

```


---

## 6.2 branches


```

branch_id PK

company_id FK

branch_code

branch_name

address

phone

status

```


---

Relationship:


```

Tenant

|

Company

|

Branch

```


---

# 7. User & Security Database


## users


```

user_id PK

tenant_id FK

username

password_hash

email

status

last_login

```


---

## roles


```

role_id PK

role_name

description

```


---

## permissions


```

permission_id PK

permission_code

description

```


---

## user_roles


```

user_id FK

role_id FK

```


---

# 8. MASTER DATA DESIGN


Master data adalah data dasar bisnis.


Kategori:


```

Customer Master

Product Master

Menu Master

Supplier Master

Employee Master

Inventory Master

```


---

# 9. Customer Master


## customers


```

customer_id PK

tenant_id FK

customer_code

name

phone

email

address

membership_level

created_at

```


---

# 10. Supplier Master


## suppliers


```

supplier_id PK

tenant_id FK

supplier_code

supplier_name

phone

address

payment_term

```


---

# 11. Employee Master


## employees


```

employee_id PK

tenant_id FK

employee_code

name

position

phone

join_date

status

```


---

# 12. Product Ingredient Master


## inventory_items


Menyimpan bahan baku.


```

item_id PK

tenant_id FK

item_code

item_name

category_id

unit_id

minimum_stock

current_cost

```


Contoh:


```

Beras

Ayam

Kopi

Gula

Minyak

```


---

# 13. Menu Master


## menus


```

menu_id PK

tenant_id FK

category_id FK

menu_code

menu_name

selling_price

status

```


---

# 14. Recipe Database


## recipes


```

recipe_id PK

menu_id FK

version

effective_date

```


---

## recipe_details


```

recipe_detail_id PK

recipe_id FK

item_id FK

quantity

unit

cost

```


Relasi:


```

Menu

|

Recipe

|

Ingredient

```


---

# 15. Transaction Database


Transaction adalah aktivitas bisnis.


Kategori:


```

Sales

Order

Purchase

Stock

Payment

Accounting

```


---

# 16. Order Transaction


## orders


```

order_id PK

tenant_id FK

branch_id FK

customer_id FK

table_id FK

order_number

order_status

order_date

created_by

```


---

## order_details


```

order_detail_id PK

order_id FK

menu_id FK

qty

price

discount

subtotal

```


---

# 17. Payment Transaction


## payments


```

payment_id PK

order_id FK

payment_method

amount

payment_status

payment_date

```


---

# 18. Invoice Database


## invoices


```

invoice_id PK

invoice_number

order_id FK

total_amount

tax

discount

grand_total

```


---

# 19. Kitchen Transaction


## kitchen_orders


```

kitchen_order_id PK

order_id FK

status

priority

start_time

finish_time

```


---

## kitchen_order_details


```

kitchen_order_detail_id

kitchen_order_id

menu_id

qty

status

```


---

# 20. Inventory Transaction


Inventory menggunakan konsep:


```

Stock Movement

```


---

## stock_transactions


```

stock_transaction_id PK

tenant_id FK

branch_id FK

item_id FK

transaction_type

quantity

reference_type

reference_id

transaction_date

```


---

Transaction Type:


```

PURCHASE

SALE_USAGE

TRANSFER

ADJUSTMENT

WASTE

RETURN

```


---

# 21. Stock Balance


## stock_balances


```

stock_balance_id PK

branch_id FK

item_id FK

quantity

average_cost

updated_at

```


---

# 22. Stock Opname


## stock_opnames


```

opname_id PK

branch_id FK

date

status

```


---

## stock_opname_details


```

opname_detail_id

opname_id

item_id

system_qty

actual_qty

difference

```


---

# 23. Purchasing Database


## purchase_orders


```

purchase_order_id PK

supplier_id FK

branch_id FK

po_number

status

order_date

```


---

## purchase_order_details


```

po_detail_id PK

purchase_order_id FK

item_id FK

qty

price

subtotal

```


---

# 24. Receiving Database


## goods_receipts


```

receipt_id PK

purchase_order_id

supplier_id

received_date

```


---

# 25. Accounting Database


EBP menggunakan:


```

Double Entry Accounting

```


---

# accounts


```

account_id PK

account_code

account_name

account_type

```


---

# journals


```

journal_id PK

transaction_date

reference_type

reference_id

description

```


---

# journal_details


```

journal_detail_id PK

journal_id FK

account_id FK

debit

credit

```


---

Contoh:


Penjualan:


```

Debit

Cash

Credit

Sales Revenue

```


---

# 26. Expense Database


## expenses


```

expense_id PK

category

amount

date

approval_status

```


---

# 27. Audit Database


Semua perubahan penting dicatat.


## audit_logs


```

audit_id PK

tenant_id

user_id

module

action

record_id

old_value

new_value

ip_address

created_at

```


---

# 28. Approval History


## approval_logs


```

approval_id PK

transaction_type

transaction_id

approved_by

status

approval_date

```


---

# 29. Notification Database


## notifications


```

notification_id PK

user_id

type

message

status

created_at

```


---

# 30. AI Data Architecture


AI membutuhkan histori.


EBP menyediakan:


```

Operational Database

```
    ↓
```

Data Warehouse

```
    ↓
```

AI Model

```


---

# 31. Sales Analytics Table


## ai_sales_daily


```

date

branch_id

total_sales

transaction_count

customer_count

```


---

# 32. Menu Performance


## ai_menu_analysis


```

menu_id

period

sales_qty

revenue

profit

ranking

```


---

# 33. Forecast Data


## ai_forecast_sales


```

forecast_id

branch_id

forecast_date

predicted_sales

confidence

```


---

# 34. Fraud Detection Data


## ai_fraud_detection


```

fraud_id

user_id

transaction_id

risk_score

reason

status

```


---

# 35. Database Relationship Overview


```

TENANT

|

COMPANY

|

BRANCH

|

+-------------+

|             |

MENU       INVENTORY

|             |

RECIPE     STOCK

|

ORDER

|

PAYMENT

|

ACCOUNTING

```


---

# 36. Index Strategy


Index wajib:


```

tenant_id

branch_id

created_at

transaction_date

status

code

```


---

# 37. Partition Strategy


Data besar:


Contoh:


Sales:


```

Partition By Year

```


Audit:


```

Partition By Month

```


---

# 38. Data Retention


Aturan:


```

Operational Data

Active

Historical Data

Archive

AI Data

Long Term

```


---

# 39. Database Security


Wajib:


- prepared statement;
- encrypted connection;
- user privilege;
- backup;
- audit.


---

# 40. Backup Strategy


Database:


```

Daily Backup

Weekly Full Backup

Monthly Archive

```


---

# 41. Future Scaling


Database dapat berkembang:


```

MySQL

↓

Replication

↓

Read Replica

↓

Data Warehouse

↓

AI Platform

```


---

# 42. Kesimpulan


Database EBP Restaurant & Cafe ERP dirancang sebagai:


```

Enterprise Business Database

```


Bukan hanya:


```

Database Kasir

```


Database harus mampu:


- menyimpan transaksi;
- mengontrol bisnis;
- menyediakan laporan;
- mendukung AI;
- berkembang menjadi platform enterprise.


---

# Document End


Document ID:

EBP-RC-DATABASE-DESIGN-001


Version:

1.0
