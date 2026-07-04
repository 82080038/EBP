# Platform Bisnis Enterprise (EBP)
# Dokumen Standar Database


**ID Dokumen:** EBP-DATABASE-STANDARD-001  
**Versi:** 1.0  
**Status:** Mandatory Technical Standard  
**Klasifikasi:** Database Governance  
**Pemilik:** Organisasi Platform Bisnis Enterprise  


---

# 1. Pendahuluan


## 1.1 Tujuan Dokumen


Dokumen ini mendefinisikan standar implementasi database untuk seluruh sistem yang dibangun menggunakan Platform Bisnis Enterprise (EBP).


Standar ini mencakup:

- database naming convention;
- table structure;
- primary key;
- foreign key;
- audit field;
- soft delete;
- versioning;
- indexing;
- transaction handling;
- security;
- migration;
- backup.


---

# 2. Prinsip Database EBP


EBP menggunakan prinsip:


```
Data Is A Strategic Asset


        ↓


Data Must Be Accurate


        ↓


Data Must Be Traceable


        ↓


Data Must Be Secure


        ↓


Data Must Be Scalable

```


---

# 3. Database Architecture Principle


Database EBP dibagi menjadi:


```
MASTER DATABASE

        |

TRANSACTION DATABASE

        |

REPORTING DATABASE

        |

ANALYTICAL DATABASE

        |

AI DATA REPOSITORY

```


---

# 4. Database Naming Convention


## 4.1 Database Name


Format:


```
ebp_[product]_[environment]
```


Contoh:


```
ebp_core_production

ebp_restaurant_staging

ebp_hotel_development

```


---

# 4.2 Table Naming


Wajib menggunakan:


```
snake_case
```


Contoh:


Benar:

```
customer_order

inventory_item

product_category

```


Salah:

```
CustomerOrder

InventoryItem

tblProduct

```


---

# 4.3 Table Name Rules


Gunakan:

- singular noun;
- bahasa Inggris;
- jelas.


Benar:


```
customer

product

invoice

employee

```


Tidak:


```
customers

tbl_customer

data_customer

```


---

# 4.4 Junction Table


Untuk hubungan many-to-many:


Format:


```
table1_table2
```


Contoh:


```
role_permission

product_category

employee_skill

```


---

# 5. Primary Key Standard


## 5.1 Primary Key Principle


Setiap tabel wajib memiliki primary key.


---

# 5.2 Primary Key Format


EBP menggunakan:


```
BIGINT AUTO_INCREMENT
```

atau


```
UUID
```


---

# 5.3 Pemilihan Primary Key


## Internal System


Gunakan:


```
BIGINT
```


Contoh:


```
customer_id
product_id
order_id

```


---

## Distributed System


Gunakan:


```
UUID
```


Contoh:


```
550e8400-e29b-41d4-a716

```


---

# 5.4 Primary Key Naming


Format:


```
[nama_table]_id
```


Contoh:


```
customer_id

product_id

invoice_id

```


---

# 6. Foreign Key Standard


## 6.1 Naming


Format:


```
nama_table_id
```


Contoh:


```
customer_id

product_id

branch_id

```


---

## 6.2 Relationship


Contoh:


```
customer

|

customer_order

|

order_item

|

product

```


---

# 7. Mandatory Audit Column


Setiap tabel bisnis wajib memiliki:


```
created_at

created_by

updated_at

updated_by

```


Contoh:


```sql
created_at DATETIME

created_by BIGINT

updated_at DATETIME

updated_by BIGINT
```


---

# 8. Record Status Standard


Semua tabel master dan transaksi wajib memiliki:


```
status
```


Contoh:


```
ACTIVE

INACTIVE

DRAFT

APPROVED

CANCELLED

ARCHIVED

```


---

# 9. Soft Delete Standard


## 9.1 Prinsip


EBP tidak melakukan hard delete untuk data bisnis penting.


---

## 9.2 Field


Gunakan:


```
deleted_at

deleted_by

is_deleted
```


Contoh:


```
is_deleted BOOLEAN DEFAULT FALSE
```


---

## 9.3 Alasan


Karena sistem harus mampu menjawab:


"Siapa menghapus data?"

"Kapan?"

"Apa isi data sebelumnya?"


---

# 10. Versioning Standard


## 10.1 Tujuan


Menyimpan histori perubahan data.


---

## 10.2 Field


Gunakan:


```
version_number

effective_date

expired_date

```


---

Contoh:


Harga produk:


```
product_price_history


id

product_id

price

effective_date

expired_date

```


---

# 11. Transaction Data Standard


Transaksi harus immutable.


Artinya:


Data transaksi yang sudah final tidak boleh diubah sembarangan.


---

Contoh:


Invoice:


Tidak:


```
Update total invoice lama
```


Tetapi:


```
Create adjustment transaction
```


---

# 12. Financial Data Standard


Data keuangan harus memiliki:


```
transaction_date

reference_number

amount

currency

status

```


---

# 13. Currency Standard


Tidak boleh menyimpan:


```
100000
```


tanpa informasi.


Gunakan:


```
amount

currency_code

```


Contoh:


```
amount = 100000

currency_code = IDR

```


---

# 14. Decimal Standard


Nilai uang:


Gunakan:


```
DECIMAL(18,2)
```


Contoh:


```
price DECIMAL(18,2)

amount DECIMAL(18,2)

```


---

# 15. Boolean Standard


Gunakan:


```
BOOLEAN
```


Contoh:


```
is_active

is_deleted

is_verified

```


---

# 16. Date and Time Standard


Gunakan:


```
UTC Timestamp
```


Format:


```
YYYY-MM-DD HH:mm:ss
```


---

# 17. Indexing Standard


## 17.1 Prinsip


Index dibuat berdasarkan kebutuhan query.


Tidak boleh:

- terlalu sedikit;
- terlalu banyak.


---

# 17.2 Primary Index


Setiap primary key otomatis memiliki index.


---

# 17.3 Foreign Key Index


Semua foreign key wajib memiliki index.


Contoh:


```
customer_id

product_id

branch_id

```


---

# 17.4 Search Index


Field pencarian:


Contoh:


```
email

phone

code

name

```


---

# 18. Unique Constraint


Data tertentu harus unik.


Contoh:


```
username

email

product_code

invoice_number

```


---

# 19. Database Normalization


EBP menggunakan:


Minimum:

```
Third Normal Form (3NF)
```


Tujuan:

- menghindari duplikasi;
- menjaga konsistensi.


---

# 20. Denormalization Rule


Denormalisasi hanya boleh dilakukan untuk:


- performance;
- reporting;
- analytics.


Harus terdokumentasi.


---

# 21. Naming Column Standard


Gunakan:


```
snake_case
```


Contoh:


```
first_name

last_name

created_at

updated_at

```


---

# 22. Reserved Column


Column standar:


```
id

code

name

description

status

created_at

created_by

updated_at

updated_by

deleted_at

deleted_by

```


---

# 23. Database Security Standard


Wajib:


- user database terpisah;
- password terenkripsi;
- privilege minimum;
- audit access.


---

# 24. Backup Standard


Database harus memiliki:


```
Daily Backup

Weekly Full Backup

Monthly Archive

```


---

# 25. Migration Standard


Semua perubahan database harus melalui migration.


Tidak boleh:


```
langsung edit production database
```


---

# 26. Database Documentation


Setiap tabel wajib memiliki dokumentasi:


```
Table Purpose

Column Description

Relationship

Business Rule

```


---

# 27. Example Standard Table


Contoh:


```sql
CREATE TABLE customer
(
    customer_id BIGINT PRIMARY KEY,

    party_id BIGINT,

    customer_code VARCHAR(50),

    status VARCHAR(20),

    created_at DATETIME,

    created_by BIGINT,

    updated_at DATETIME,

    updated_by BIGINT,

    deleted_at DATETIME,

    deleted_by BIGINT

);
```


---

# 28. Database Anti Pattern


Dilarang:


## 1.

Membuat tabel tanpa primary key.


---

## 2.

Menghapus transaksi penting.


---

## 3.

Menggunakan nama tabel tidak jelas.


---

## 4.

Menyimpan data tanpa histori.


---

## 5.

Membuat database berbeda untuk setiap modul tanpa alasan.


---

# 29. Enterprise Scalability Rule


Database harus siap menghadapi:


```
1 User

↓

100 User

↓

10.000 User

↓

1.000.000 User

```


---

# 30. Future Database Direction


EBP diarahkan mendukung:


- distributed database;
- cloud database;
- data warehouse;
- AI analytics;
- real-time processing.


---

# 31. Kesimpulan


Database EBP bukan hanya tempat penyimpanan data.

Database EBP adalah:

- memori organisasi;
- sumber keputusan;
- aset bisnis;
- fondasi kecerdasan buatan.


Karena itu:

> Database harus dirancang untuk bertahan lebih lama daripada aplikasi yang menggunakannya.


---

# Document End


ID Dokumen:

EBP-DATABASE-STANDARD-001


Versi:

1.0
