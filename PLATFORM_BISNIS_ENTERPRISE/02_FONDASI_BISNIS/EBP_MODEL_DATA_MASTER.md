# Enterprise Business Platform (EBP)
# Master Data Model Document


**Document ID:** EBP-MASTER-DATA-MODEL-001  
**Version:** 1.0  
**Status:** Enterprise Data Foundation  
**Classification:** Core Data Architecture  
**Owner:** Enterprise Business Platform Organization  


---

# 1. Pendahuluan


## 1.1 Tujuan Dokumen


Dokumen ini mendefinisikan struktur Master Data Model yang digunakan oleh seluruh ekosistem Enterprise Business Platform (EBP).


Master Data Model adalah:

> Struktur data utama yang menjadi sumber kebenaran (Single Source of Truth) untuk seluruh proses bisnis.


---

# 2. Prinsip Master Data EBP


EBP menggunakan prinsip:


```

One Data Model

```
    ↓
```

Many Business Applications

```
    ↓
```

Consistent Information

```


---

# 3. Definisi Master Data


Master Data adalah data inti yang:

- digunakan banyak modul;
- memiliki umur panjang;
- menjadi referensi transaksi;
- tidak berubah setiap saat.


Contoh:

```

Customer

Product

Supplier

Employee

Location

Organization

```


---

# 4. Kategori Master Data


EBP membagi Master Data menjadi:


```

A. Enterprise Master Data

B. Party Master Data

C. Product Master Data

D. Resource Master Data

E. Financial Master Data

F. Operational Master Data

G. Security Master Data

H. Analytical Master Data

```


---

# 5. ENTERPRISE MASTER DATA


# 5.1 Organization Master


## Fungsi

Menyimpan informasi organisasi.


Contoh:

- perusahaan;
- cabang;
- unit usaha.


---

## Entity


```

organization

```


---

## Struktur


```

organization_id

parent_organization_id

organization_code

organization_name

organization_type

registration_number

tax_number

address

phone

email

status

created_at

updated_at

```


---

## Relationship


```

Organization

|

+-- Branch

+-- Department

+-- Employee

+-- Asset

+-- Transaction

```


---

# 5.2 Branch Master


## Fungsi

Menyimpan lokasi operasional.


Contoh:


Restaurant:

```

Cabang Medan

Cabang Samosir

```


---

## Entity


```

branch

```


---

# 5.3 Department Master


Contoh:


```

Kitchen

Finance

Marketing

Warehouse

Legal

```


---

# 6. PARTY MASTER DATA


Party adalah konsep universal.


```

Party

|

+-- Person

|

+-- Organization

```


---

# 6.1 Party Master


Entity:


```

party

```


Struktur:


```

party_id

party_type

party_code

name

phone

email

address

status

```


---

# 6.2 Person Master


Digunakan untuk:


- customer;
- employee;
- owner.


Struktur:


```

person_id

party_id

first_name

last_name

birth_date

gender

identity_number

```


---

# 6.3 Organization Party


Digunakan untuk:


- supplier;
- partner;
- vendor.


Struktur:


```

party_id

company_name

registration_number

tax_number

```


---

# 7. CUSTOMER MASTER


Customer bukan tabel terpisah secara konsep.


Struktur:


```

Party

*

Customer Relationship

```


Entity:


```

customer_profile

```


Isi:


```

customer_id

party_id

customer_type

membership_level

join_date

```


---

# 8. SUPPLIER MASTER


Supplier menggunakan konsep Party.


Entity:


```

supplier_profile

```


Struktur:


```

supplier_id

party_id

supplier_type

payment_term

rating

```


---

# 9. PRODUCT MASTER DATA


Produk adalah objek utama bisnis.


Struktur:


```

Product

|

+-- Category

|

+-- Unit

|

+-- Price

|

+-- Inventory

```


---

# 9.1 Product Master


Entity:


```

product

```


Struktur:


```

product_id

product_code

product_name

product_type

category_id

unit_id

description

status

```


---

# 9.2 Product Category


Contoh:


Restaurant:


```

Food

Drink

Dessert

```


---

# 9.3 Unit Master


Contoh:


```

Kg

Liter

Pcs

Box

Plate

```


---

# 9.4 Product Price


Harga harus memiliki histori.


Entity:


```

product_price_history

```


Struktur:


```

price_id

product_id

price

effective_date

expired_date

created_by

```


---

# 10. INVENTORY MASTER DATA


---

# 10.1 Inventory Item


Entity:


```

inventory_item

```


Digunakan untuk:


- bahan baku;
- barang dagangan;
- sparepart.


---

# 10.2 Warehouse Master


Entity:


```

warehouse

```


Contoh:


```

Gudang Utama

Gudang Cabang

Cold Storage

```


---

# 10.3 Stock Location


Entity:


```

stock_location

```


---

# 11. RESOURCE MASTER DATA


Resource adalah aset yang digunakan organisasi.


---

# 11.1 Asset Master


Entity:


```

asset

```


Contoh:


```

Mobil

Mesin

Komputer

Gedung

```


Struktur:


```

asset_id

asset_code

asset_name

category

purchase_date

value

status

```


---

# 11.2 Vehicle Master


Contoh:


- kendaraan distribusi;
- kendaraan operasional.


---

# 11.3 Equipment Master


Contoh:


Restaurant:


```

Oven

Freezer

Coffee Machine

```


---

# 12. HUMAN RESOURCE MASTER


---

# 12.1 Employee Master


Employee berasal dari:


```

Person

*

Employment Relationship

```


Entity:


```

employee

```


Struktur:


```

employee_id

party_id

employee_number

join_date

position_id

department_id

status

```


---

# 12.2 Position Master


Contoh:


```

Manager

Cashier

Chef

Driver

```


---

# 13. FINANCIAL MASTER DATA


---

# 13.1 Account Master


Chart of Account.


Entity:


```

account

```


Contoh:


```

Cash

Bank

Sales

Expense

Inventory

```


---

# 13.2 Tax Master


Menyimpan:


```

Tax Type

Tax Rate

Regulation

```


---

# 14. SECURITY MASTER DATA


---

# 14.1 User Master


Entity:


```

user

```


---

# 14.2 Role Master


Entity:


```

role

```


---

# 14.3 Permission Master


Entity:


```

permission

```


Relationship:


```

User

↓

Role

↓

Permission

```


---

# 15. CONFIGURATION MASTER


Data yang dapat berubah tanpa coding.


Contoh:


```

Discount Rule

Payment Method

Number Format

Workflow Setting

```


---

# 16. MASTER DATA RELATIONSHIP MODEL


Gambaran:


```

Organization

|

Party

|

Customer / Supplier / Employee

|

Transaction

|

Product / Service

|

Resource

|

Financial Record

```


---

# 17. Master Data Governance


Setiap Master Data wajib memiliki:


```

Owner

Approval

Validation Rule

History

Audit Trail

```


---

# 18. Data Lifecycle


Setiap data memiliki:


```

Create

↓

Validate

↓

Active

↓

Update

↓

Archive

```


---

# 19. Larangan Master Data


Tidak boleh:


## 1.

Membuat customer di setiap modul.


## 2.

Membuat product berbeda antar aplikasi.


## 3.

Menghapus data master transaksi.


## 4.

Menyimpan data penting tanpa histori.


---

# 20. Contoh Implementasi Restaurant ERP


Restaurant menggunakan:


```

Organization

Branch

Party

Customer

Supplier

Product

Inventory

Employee

Asset

Account

```


Tidak membuat database sendiri.

Menggunakan EBP Core.


---

# 21. Contoh Implementasi Agriculture ERP


Menggunakan:


```

Farmer

Land

Crop

Product

Inventory

Transaction

Asset

```


Tetap berasal dari Master Model yang sama.


---

# 22. Future Expansion


Model ini harus mendukung:


- IoT;
- AI;
- Blockchain;
- Digital Twin;
- Marketplace;
- Supply Chain Network.


---

# 23. Kesimpulan


Master Data Model adalah fondasi data EBP.

Jika Business Ontology adalah bahasa bisnis,

maka Master Data Model adalah struktur ingatan EBP.


Dengan model ini:

- aplikasi baru lebih cepat dibuat;
- integrasi lebih mudah;
- data konsisten;
- AI lebih mudah memahami bisnis.


---

# Document End


Document ID:

EBP-MASTER-DATA-MODEL-001


Version:

1.0
