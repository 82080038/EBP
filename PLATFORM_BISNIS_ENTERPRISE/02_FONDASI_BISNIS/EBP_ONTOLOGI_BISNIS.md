# Platform Bisnis Enterprise (EBP)
# Dokumen Ontologi Bisnis


**ID Dokumen:** EBP-ONTOLOGI-BISNIS-001
**Versi:** 1.0
**Status:** Model Bisnis Fondasi
**Klasifikasi:** Arsitektur Data Enterprise
**Pemilik:** Organisasi Platform Bisnis Enterprise  


---

# 1. Pendahuluan


## 1.1 Tujuan Dokumen


Dokumen ini mendefinisikan objek bisnis utama yang digunakan oleh seluruh ekosistem Platform Bisnis Enterprise (EBP).


Ontologi Bisnis adalah:

> Model konsep bisnis yang mendefinisikan apa saja objek yang ada dalam dunia bisnis, bagaimana hubungan antar objek, serta bagaimana objek tersebut digunakan oleh berbagai industri.


---

# 2. Prinsip Ontologi Bisnis


EBP menggunakan prinsip:


```

Konsep Universal

```
    ↓
```

Objek Enterprise

```
    ↓
```

Spesialisasi Industri

```


Contoh:


Universal:

```

Pihak

```


Restoran:

```

Pelanggan

```


Hotel:

```

Tamu

```


Legal:

```

Klien

```


Semua berasal dari konsep:

```

Pihak

```


---

# 3. Hirarki Objek Bisnis


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

# 4. OBJEK BISNIS INTI


---

# 4.1 Organisasi


## Definisi


Organisasi adalah entitas bisnis yang memiliki tujuan, struktur, sumber daya, dan aktivitas.


Contoh:


- perusahaan;
- restoran;
- hotel;
- koperasi;
- yayasan;
- pemerintah.


---

## Atribut


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

## Relasi


Organisasi memiliki:


```

Organisasi

|

├── Lokasi

├── Departemen

├── Karyawan

├── Aset

├── Akun

└── Transaksi

```


---

# 4.2 Pihak


## Definisi


Pihak adalah pihak yang memiliki hubungan dengan organisasi.


Pihak dapat berupa:


```

Orang

atau

Organisasi

```


---

## Contoh


Pelanggan:

```

Orang

```


Supplier:

```

Organisasi

```


Karyawan:

```

Orang

```


Mitra:

```

Organisasi

```


---

## Atribut


```

party_id

party_type

name

contact

identity

status

```


---

# 4.3 Orang


## Definisi


Representasi individu manusia.


Digunakan untuk:


- pelanggan;
- karyawan;
- pemilik;
- pengemudi;
- anggota.


---

# 4.4 Lokasi


## Definisi


Tempat fisik atau virtual tempat aktivitas bisnis terjadi.


Contoh:


```

Kantor Pusat

Gudang

Cabang Restoran

Lokasi Pertanian

Area Parkir

```


---

## Atribut


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

Sumber Daya Manusia

Resource Fisik

Resource Keuangan

Resource Digital

```


---

# 5. ONTOLOGI PRODUK DAN LAYANAN


---

# 5.1 Produk


## Definisi


Barang atau jasa yang memiliki nilai bisnis.


Contoh:


Restoran:

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

## Atribut


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

# 5.2 Layanan


## Definisi


Aktivitas bernilai yang diberikan kepada pelanggan.


Contoh:


- konsultasi hukum;
- pemandu wisata;
- jasa parkir.


---

# 5.3 Kategori


## Definisi


Pengelompokan objek bisnis.


Contoh:


```

Kategori Makanan

Kategori Layanan Hukum

Kategori Produk

```


---

# 6. ONTOLOGI INVENTARIS


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

# 6.2 Stok


Definisi:


Jumlah item yang tersedia pada lokasi tertentu.


Formula:


```

Stok Tersedia

=

Stok Awal

*

Masuk

*

Keluar

```


---

# 6.3 Pergerakan Stok


Perubahan jumlah inventory.


Jenis:


```

Pembelian

Penjualan

Transfer

Penyesuaian

Waste

Produksi

```


---

# 7. ONTOLOGI TRANSAKSI


---

# 7.1 Transaksi


## Definisi


Aktivitas bisnis yang menghasilkan perubahan kondisi ekonomi atau operasional.


---

Contoh:


```

Penjualans

Pembelian

Pembayaran

Transfer

Biaya

```


---

# 7.2 Pesanan


Definisi:


Permintaan pembelian produk atau layanan.


---

Relationship:


```

Customer

↓

Pesanan

↓

Product

↓

Pembayaran

```


---

# 7.3 Pembayaran


Definisi:


Aktivitas pertukaran nilai.


Contoh:


```

Cash

Bank

Digital Pembayaran

Kredit

```


---

# 8. ONTOLOGI KEUANGAN


---

# 8.1 Akun


Tempat pencatatan nilai keuangan.


---

# 8.2 Pendapatan


Pendapatan organisasi.


---

# 8.3 Biaya


Pengeluaran organisasi.


---

# 8.4 Profit


Hasil ekonomi.


Formula:


```

Profit

=

Pendapatan

*

Biaya

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

Faktur

Contract

Resi

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

Pembelian Request

↓

Approval

↓

Pembelian Pesanan

↓

Pembayaran

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

Pesanan Created

Pembayaran Received

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

# 13.1 Aset


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

Pembelian History

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

Pesanan

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

Pesanan

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
