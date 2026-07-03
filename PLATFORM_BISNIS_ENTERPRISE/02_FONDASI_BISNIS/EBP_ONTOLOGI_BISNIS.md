# Enterprise Business Platform (EBP)
# Business Ontology Document


**Document ID:** EBP-BUSINESS-ONTOLOGY-001  
**Version:** 1.0  
**Status:** Foundation Business Model  
**Classification:** Enterprise Data Architecture  
**Owner:** Enterprise Business Platform Organization  


---

# 1. Pendahuluan


## 1.1 Tujuan Dokumen


Dokumen ini mendefinisikan objek bisnis utama yang digunakan oleh seluruh ekosistem Enterprise Business Platform (EBP).


Business Ontology adalah:

> Model konsep bisnis yang mendefinisikan apa saja objek yang ada dalam dunia bisnis, bagaimana hubungan antar objek, serta bagaimana objek tersebut digunakan oleh berbagai industri.


---

# 2. Prinsip Business Ontology


EBP menggunakan prinsip:


```

Universal Concept

```
    ↓
```

Enterprise Object

```
    ↓
```

Industry Specialization

```


Contoh:


Universal:

```

Party

```


Restaurant:

```

Customer

```


Hotel:

```

Guest

```


Legal:

```

Client

```


Semua berasal dari konsep:

```

Party

```


---

# 3. Business Object Hierarchy


Struktur utama EBP:


```

Enterprise

|

Organization

|

Party

|

Resource

|

Transaction

|

Event

|

Document

|

Knowledge

|

Decision

```


---

# 4. CORE BUSINESS OBJECT


---

# 4.1 Organization


## Definisi


Organization adalah entitas bisnis yang memiliki tujuan, struktur, sumber daya, dan aktivitas.


Contoh:


- perusahaan;
- restoran;
- hotel;
- koperasi;
- yayasan;
- pemerintah.


---

## Attributes


```

organization_id

organization_name

organization_type

registration_number

address

contact

status

```


---

## Relationship


Organization memiliki:


```

Organization

|

├── Location

├── Department

├── Employee

├── Asset

├── Account

└── Transaction

```


---

# 4.2 Party


## Definisi


Party adalah pihak yang memiliki hubungan dengan organisasi.


Party dapat berupa:


```

Person

atau

Organization

```


---

## Contoh


Customer:

```

Person

```


Supplier:

```

Organization

```


Employee:

```

Person

```


Partner:

```

Organization

```


---

## Attributes


```

party_id

party_type

name

contact

identity

status

```


---

# 4.3 Person


## Definisi


Representasi individu manusia.


Digunakan untuk:


- customer;
- employee;
- owner;
- driver;
- member.


---

# 4.4 Location


## Definisi


Tempat fisik atau virtual tempat aktivitas bisnis terjadi.


Contoh:


```

Head Office

Warehouse

Restaurant Branch

Farm Location

Parking Area

```


---

## Attributes


```

location_id

name

address

coordinate

type

status

```


---

# 4.5 Resource


## Definisi


Segala sesuatu yang digunakan organisasi untuk menghasilkan nilai.


Resource terdiri dari:


```

Human Resource

Physical Resource

Financial Resource

Digital Resource

```


---

# 5. PRODUCT AND SERVICE ONTOLOGY


---

# 5.1 Product


## Definisi


Barang atau jasa yang memiliki nilai bisnis.


Contoh:


Restaurant:

```

Menu makanan

```


Agriculture:

```

Jagung

```


Retail:

```

Produk toko

```


---

## Attributes


```

product_id

product_name

category

unit

price

cost

status

```


---

# 5.2 Service


## Definisi


Aktivitas bernilai yang diberikan kepada pelanggan.


Contoh:


- konsultasi hukum;
- tour guide;
- jasa parkir.


---

# 5.3 Category


## Definisi


Pengelompokan objek bisnis.


Contoh:


```

Food Category

Legal Service Category

Product Category

```


---

# 6. INVENTORY ONTOLOGY


---

# 6.1 Item


Definisi:


Objek yang dapat dikelola jumlahnya.


Contoh:


```

Bahan baku

Barang dagangan

Sparepart

```


---

# 6.2 Stock


Definisi:


Jumlah item yang tersedia pada lokasi tertentu.


Formula:


```

Available Stock

=

Beginning Stock

*

Incoming

*

Outgoing

```


---

# 6.3 Stock Movement


Perubahan jumlah inventory.


Jenis:


```

Purchase

Sale

Transfer

Adjustment

Waste

Production

```


---

# 7. TRANSACTION ONTOLOGY


---

# 7.1 Transaction


## Definisi


Aktivitas bisnis yang menghasilkan perubahan kondisi ekonomi atau operasional.


---

Contoh:


```

Sales

Purchase

Payment

Transfer

Expense

```


---

# 7.2 Order


Definisi:


Permintaan pembelian produk atau layanan.


---

Relationship:


```

Customer

↓

Order

↓

Product

↓

Payment

```


---

# 7.3 Payment


Definisi:


Aktivitas pertukaran nilai.


Contoh:


```

Cash

Bank

Digital Payment

Credit

```


---

# 8. FINANCIAL ONTOLOGY


---

# 8.1 Account


Tempat pencatatan nilai keuangan.


---

# 8.2 Revenue


Pendapatan organisasi.


---

# 8.3 Expense


Pengeluaran organisasi.


---

# 8.4 Profit


Hasil ekonomi.


Formula:


```

Profit

=

Revenue

*

Expense

```


---

# 9. HUMAN RESOURCE ONTOLOGY


---

# 9.1 Employee


Person yang bekerja untuk organisasi.


---

# 9.2 Position


Jabatan dalam organisasi.


---

# 9.3 Department


Kelompok fungsi organisasi.


Contoh:


```

Kitchen

Finance

Marketing

Legal

```


---

# 10. DOCUMENT ONTOLOGY


---

# 10.1 Document


Semua informasi resmi dalam bentuk dokumen.


Contoh:


```

Invoice

Contract

Receipt

Permit

Report

```


---

# 10.2 Document Version


Setiap perubahan dokumen harus memiliki histori.


---

# 11. WORKFLOW ONTOLOGY


---

# 11.1 Process


Rangkaian aktivitas bisnis.


---

# 11.2 Workflow


Aturan bagaimana proses berjalan.


Contoh:


```

Purchase Request

↓

Approval

↓

Purchase Order

↓

Payment

```


---

# 11.3 Approval


Persetujuan resmi.


---

# 12. EVENT ONTOLOGY


---

# 12.1 Event


Sesuatu yang terjadi dalam sistem.


Contoh:


```

Order Created

Payment Received

Stock Changed

Employee Login

```


---

Event digunakan untuk:


- automation;
- notification;
- AI analysis.


---

# 13. ASSET ONTOLOGY


---

# 13.1 Asset


Sumber daya bernilai yang dimiliki organisasi.


Contoh:


```

Vehicle

Machine

Building

Equipment

```


---

# 14. CUSTOMER ONTOLOGY


Customer bukan objek utama.

Customer adalah:


```

Party

*

Relationship

*

Transaction History

*

Preference

```


---

Customer memiliki:


```

Profile

Behavior

Purchase History

Loyalty

Feedback

```


---

# 15. RELATIONSHIP ONTOLOGY


EBP menggunakan konsep relationship.


Contoh:


```

Party

|

has relationship

|

Organization

```


Jenis:


```

Customer Relationship

Supplier Relationship

Employee Relationship

Partner Relationship

```


---

# 16. KNOWLEDGE ONTOLOGY


EBP menyimpan pengetahuan bisnis.


Contoh:


```

Rule

Policy

Procedure

Experience

Recommendation

```


---

# 17. AI BUSINESS ONTOLOGY


AI membutuhkan pemahaman objek bisnis.


Contoh:


AI memahami:


```

Customer

↓

Order

↓

Product

↓

Profit

↓

Behavior

```


---

# 18. Enterprise Object Relationship


Gambaran umum:


```

Organization

```
|

+-- Party

|

+-- Location

|

+-- Resource

|

+-- Product

|

+-- Transaction

|

+-- Document

|

+-- Event

|

+-- Knowledge
```

```


---

# 19. Aturan Ontology EBP


## Rule 1

Tidak boleh membuat objek baru jika konsepnya sudah tersedia.


---

## Rule 2

Objek industri harus berasal dari objek enterprise.


---

## Rule 3

Data master harus memiliki satu sumber utama.


---

## Rule 4

Semua transaksi harus memiliki hubungan dengan objek utama.


---

## Rule 5

Semua objek penting harus dapat diaudit.


---

# 20. Contoh Implementasi Industri


## Restaurant ERP


```

Party

↓

Customer

Product

↓

Menu

Transaction

↓

Order

Resource

↓

Kitchen Equipment

```


---

## Agriculture ERP


```

Party

↓

Farmer

Product

↓

Corn

Resource

↓

Land

Machine

```


---

## Legal ERP


```

Party

↓

Client

Service

↓

Legal Service

Document

↓

Contract

```


---

# 21. Kesimpulan


Business Ontology adalah bahasa universal EBP.

Dengan ontology yang benar:

- database menjadi konsisten;
- modul dapat digunakan ulang;
- AI memahami bisnis;
- integrasi menjadi mudah;
- produk baru dapat dibuat lebih cepat.


EBP tidak dibangun dari tabel database.

EBP dibangun dari pemahaman dunia bisnis.


---

# Document End


Document ID:

EBP-BUSINESS-ONTOLOGY-001


Version:

1.0
