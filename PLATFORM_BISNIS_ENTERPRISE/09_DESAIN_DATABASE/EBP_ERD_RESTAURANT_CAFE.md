# Enterprise Business Platform (EBP)
# Restaurant & Cafe ERP
# Entity Relationship Diagram


**Document ID:** EBP-RC-ERD-001  
**Version:** 1.0  
**Database:** MySQL Enterprise  
**Architecture:** Multi Tenant ERP Database  


---

# 1. Tujuan ERD


Dokumen ini menjelaskan hubungan antar entity database pada sistem EBP Restaurant & Cafe ERP.


Tujuan:


- memastikan struktur data konsisten;
- memastikan integrasi antar modul;
- menjadi dasar implementasi database;
- menjadi referensi programmer.


---

# 2. ERD Architecture Philosophy


EBP menggunakan konsep:


```

Business Object Driven Database

```


Artinya:

Setiap tabel harus merepresentasikan objek bisnis.


Contoh:


Bukan:


```

tbl_jual

```


Tetapi:


```

sales_orders

```


karena merepresentasikan transaksi bisnis.


---

# 3. Database Domain Grouping


Database dibagi menjadi domain:


```

1. Identity Domain

2. Organization Domain

3. Master Data Domain

4. Sales Domain

5. Kitchen Domain

6. Inventory Domain

7. Purchasing Domain

8. Accounting Domain

9. Human Resource Domain

10. Audit Domain

11. AI Analytics Domain

```


---

# 4. High Level ERD


```

                     TENANT

                       |

                   COMPANY

                       |

                   BRANCH

                       |

    --------------------------------

    |              |               |

  USER          MENU          INVENTORY

    |              |               |

  ROLE          RECIPE          STOCK


                       |

                     ORDER

                       |

    --------------------------------

    |              |               |

 PAYMENT        KITCHEN        ACCOUNTING


                       |

                    REPORT

                       |

                       AI

```


---

# 5. CORE TABLE


Core table adalah tabel pusat yang digunakan hampir seluruh modul.


```

tenants

companies

branches

users

menus

orders

inventory_items

stock_transactions

journal_entries

audit_logs

```


---

# 6. IDENTITY DOMAIN ERD


## 6.1 User Relationship


Entity:


```

users

roles

permissions

user_roles

role_permissions

```


Relationship:


```

users

N

|

N

roles

roles

N

|

N

permissions

```


Cardinality:


```

User

Many To Many

Role

Role

Many To Many

Permission

```


---

# 7. ORGANIZATION DOMAIN


## Entity:


```

tenants

companies

branches

outlets

```


Relationship:


```

TENANT

1

|

N

COMPANY

COMPANY

1

|

N

BRANCH

BRANCH

1

|

N

OUTLET

```


Cardinality:


```

One Tenant

has Many Company

One Company

has Many Branch

```


---

# 8. MASTER DATA DOMAIN


## 8.1 Menu ERD


Entity:


```

menu_categories

menus

menu_prices

recipes

recipe_details

```


Relationship:


```

CATEGORY

1

|

N

MENU

MENU

1

|

N

RECIPE

RECIPE

1

|

N

RECIPE_DETAIL

RECIPE_DETAIL

N

|

1

INVENTORY_ITEM

```


---

## Penjelasan


Satu menu:


```

Nasi Goreng

```


memiliki resep:


```

Beras

Telur

Minyak

Bumbu

```


---

# 9. CUSTOMER DOMAIN


Entity:


```

customers

customer_memberships

customer_points

customer_transactions

```


Relationship:


```

CUSTOMER

1

|

N

ORDER

CUSTOMER

1

|

N

POINT_TRANSACTION

```


---

# 10. SALES DOMAIN


## Entity:


```

orders

order_details

payments

invoices

```


Relationship:


```

CUSTOMER

```
    |

    N
```

ORDER

ORDER

1

|

N

ORDER_DETAIL

ORDER

1

|

N

PAYMENT

ORDER

1

|

1

INVOICE

```


---

# 11. SALES TRANSACTION FLOW ERD


```

CUSTOMER

```
|
```

ORDER

```
|
```

ORDER_DETAIL

```
|
```

MENU

```
|
```

PAYMENT

```
|
```

INVOICE

```


---

# 12. TABLE MANAGEMENT DOMAIN


Entity:


```

restaurant_tables

table_reservations

```


Relationship:


```

BRANCH

1

|

N

TABLE

TABLE

1

|

N

ORDER

```


---

# 13. KITCHEN DOMAIN


Entity:


```

kitchen_orders

kitchen_order_details

kitchen_status_logs

```


Relationship:


```

ORDER

1

|

1

KITCHEN_ORDER

KITCHEN_ORDER

1

|

N

KITCHEN_DETAIL

KITCHEN_DETAIL

N

|

1

MENU

```


---

# 14. Kitchen Flow


```

ORDER CREATED

```
  ↓
```

KITCHEN QUEUE

```
  ↓
```

COOKING

```
  ↓
```

READY

```
  ↓
```

SERVED

```


---

# 15. INVENTORY DOMAIN


Entity:


```

inventory_categories

inventory_items

units

stock_balances

stock_transactions

```


Relationship:


```

CATEGORY

1

|

N

ITEM

ITEM

1

|

N

STOCK_TRANSACTION

BRANCH

1

|

N

STOCK_BALANCE

```


---

# 16. Inventory Transaction ERD


```

PURCHASE

|

STOCK_IN

SALE

|

STOCK_OUT

WASTE

|

STOCK_ADJUSTMENT

```


Semua masuk:


```

stock_transactions

```


---

# 17. PURCHASE DOMAIN


Entity:


```

suppliers

purchase_requests

purchase_orders

purchase_order_details

goods_receipts

```


Relationship:


```

SUPPLIER

1

|

N

PURCHASE_ORDER

PURCHASE_ORDER

1

|

N

DETAIL

PURCHASE_ORDER

1

|

1

RECEIVING

RECEIVING

N

|

N

INVENTORY_ITEM

```


---

# 18. ACCOUNTING DOMAIN


Entity:


```

accounts

journal_entries

journal_details

expenses

payments

```


Relationship:


```

JOURNAL_ENTRY

1

|

N

JOURNAL_DETAIL

JOURNAL_DETAIL

N

|

1

ACCOUNT

```


---

# 19. Accounting Flow


Penjualan:


```

ORDER

↓

PAYMENT

↓

JOURNAL_ENTRY

↓

GENERAL_LEDGER

```


---

# 20. HUMAN RESOURCE DOMAIN


Entity:


```

employees

employee_positions

employee_shifts

attendance

payroll

```


Relationship:


```

EMPLOYEE

1

|

N

SHIFT

EMPLOYEE

1

|

N

ATTENDANCE

```


---

# 21. AUDIT DOMAIN


Entity:


```

audit_logs

approval_logs

security_events

```


Relationship:


```

USER

1

|

N

AUDIT_LOG

TRANSACTION

1

|

N

AUDIT_LOG

```


---

# 22. AI ANALYTICS DOMAIN


Entity:


```

ai_sales_daily

ai_menu_analysis

ai_forecast

ai_fraud_detection

```


Relationship:


```

SALES_DATA

```
    |

    ↓
```

AI_ANALYSIS

```
    |

    ↓
```

AI_RESULT

```


---

# 23. Complete Business Flow ERD


```

CUSTOMER

↓

ORDER

↓

ORDER_DETAIL

↓

MENU

↓

RECIPE

↓

INGREDIENT

↓

STOCK_TRANSACTION

↓

ACCOUNTING

↓

REPORT

↓

AI_ANALYTICS

```


---

# 24. Cardinality Summary


| Relationship | Cardinality |
|-|-|
| Tenant - Company | 1:N |
| Company - Branch | 1:N |
| Branch - User | 1:N |
| Category - Menu | 1:N |
| Menu - Recipe | 1:N |
| Recipe - Ingredient | N:N |
| Customer - Order | 1:N |
| Order - Detail | 1:N |
| Order - Payment | 1:N |
| Supplier - Purchase | 1:N |
| Item - Stock Transaction | 1:N |
| Journal - Detail | 1:N |


---

# 25. Core Transaction Relationship


```

ORDER

|

+-- PAYMENT

|

+-- KITCHEN

|

+-- INVENTORY

|

+-- ACCOUNTING

|

+-- AUDIT

```


Satu transaksi menghasilkan:


```

Revenue

Stock Movement

Accounting Entry

Audit Trail

```


---

# 26. Data Integrity Rule


Semua tabel transaksi wajib memiliki:


```

tenant_id

branch_id

created_by

created_at

updated_at

```


---

# 27. Soft Delete Rule


Master data:


```

deleted_at

status

```


Tidak boleh langsung DELETE.


---

# 28. Historical Data Rule


Data transaksi:


Tidak boleh berubah.


Jika koreksi:


```

Adjustment Transaction

```


---

# 29. Future AI Data Relationship


```

Operational Database

```
    ↓
```

Data Warehouse

```
    ↓
```

Machine Learning Model

```
    ↓
```

Business Recommendation

```


---

# 30. Kesimpulan


ERD EBP Restaurant & Cafe ERP dirancang sebagai:


```

Integrated Enterprise Business Data Model

```


Bukan hanya:


```

Database POS

```


Model ini mampu mendukung:


- satu outlet;
- multi cabang;
- franchise;
- enterprise chain;
- AI analytics;
- financial control.


---

# Document End

Document ID:

EBP-RC-ERD-001

Version:

1.0
