# Enterprise Business Platform (EBP)
# Restaurant & Cafe Business Process


**Document ID:** EBP-RC-BUSINESS-PROCESS-001  
**Version:** 1.0  
**Status:** Business Analysis Standard  
**Classification:** Operational Blueprint  
**Product:** EBP Restaurant & Cafe ERP  


---

# 1. Pendahuluan


## 1.1 Tujuan Dokumen


Dokumen ini mendefinisikan seluruh proses bisnis operasional restoran dan cafe.


Tujuan:


- memahami aktivitas lapangan;
- menerjemahkan proses menjadi sistem;
- mengurangi kehilangan;
- meningkatkan kontrol bisnis.


---

# 2. Business Model Restaurant & Cafe


Restaurant memiliki aliran utama:


```

CUSTOMER

↓

ORDER

↓

PRODUCTION

↓

DELIVERY

↓

PAYMENT

↓

ACCOUNTING

↓

ANALYSIS

```


---

# 3. Actor Dalam Sistem


## Internal Actor


```

Owner

Manager

Supervisor

Cashier

Waiter

Kitchen Staff

Barista

Warehouse Staff

Purchasing Staff

Accountant

```


---

## External Actor


```

Customer

Supplier

Payment Provider

Delivery Platform

```


---

# 4. Struktur Organisasi


Contoh:


```

Owner

|

Manager

|

---

|       |        |

Cashier Waiter Kitchen

```
         |

      Warehouse
```


---

# 5. CUSTOMER JOURNEY


## 5.1 Customer Datang


Pilihan:


```

Dine In

Take Away

Delivery

Online Order

```


---

# 5.2 Customer Dine In


Flow:


```

Customer Masuk

↓

Diberi Meja

↓

Melihat Menu

↓

Memesan

↓

Menunggu

↓

Menerima Pesanan

↓

Makan

↓

Pembayaran

↓

Selesai

```


---

# 5.3 Digital Customer Flow


Sistem mencatat:


```

Customer

Table

Order

Time

Items

Payment

Feedback

```


---

# 6. TABLE MANAGEMENT


## Tujuan


Mengelola meja restoran.


Data:


```

Table Number

Capacity

Status

Current Order

```


---

Status meja:


```

AVAILABLE

RESERVED

OCCUPIED

CLEANING

```


---

# 7. WAITER PROCESS


## 7.1 Login


Waiter masuk:


```

Username

Password

Shift

Outlet

```


---

# 7.2 Mengambil Order


Waiter:


```

Pilih Meja

↓

Pilih Menu

↓

Input Jumlah

↓

Catatan Customer

↓

Submit Order

```


---

# 7.3 Order Validation


Sistem melakukan:


```

Check Menu Availability

Check Price

Calculate Total

Send Kitchen Order

```


---

# 7.4 Waiter Activity Monitoring


Sistem mencatat:


```

Waiter

Order Count

Average Service Time

Cancelled Order

```


---

# 8. CASHIER PROCESS


## 8.1 Membuka Shift


Cashier:


```

Login

↓

Input Modal Awal

↓

Open Cash Session

```


---

# 8.2 Transaksi Pembayaran


Flow:


```

Customer Request Bill

↓

Cashier Review

↓

Apply Discount

↓

Payment

↓

Receipt

```


---

# 8.3 Payment Method


Mendukung:


```

Cash

Debit

Credit Card

QR Payment

Transfer

E-Wallet

```


---

# 8.4 Closing Shift


Cashier:


```

Hitung Uang

↓

Bandingkan Sistem

↓

Submit Closing

```


---

# 9. KITCHEN PROCESS


## 9.1 Kitchen Receive Order


Order masuk:


```

NEW ORDER

↓

QUEUE

↓

PROCESS

↓

READY

```


---

# 9.2 Kitchen Display System


Menampilkan:


```

Order Number

Menu

Quantity

Time

Priority

```


---

# 9.3 Cooking Process


Contoh:


Ayam Geprek:


```

Ambil Bahan

↓

Masak

↓

Quality Check

↓

Sajikan

```


---

# 10. BARISTA PROCESS


Untuk cafe:


Flow:


```

Receive Coffee Order

↓

Prepare Ingredient

↓

Process Drink

↓

Quality Check

↓

Serve

```


---

# 11. INVENTORY PROCESS


## 11.1 Stock Master


Setiap barang memiliki:


```

Item

Category

Unit

Minimum Stock

Cost

Supplier

```


---

# 11.2 Goods Receiving


Ketika barang datang:


```

Supplier Delivery

↓

Check Quantity

↓

Quality Check

↓

Input Receiving

↓

Stock Increase

```


---

# 11.3 Stock Movement


Jenis:


```

Purchase

Usage

Transfer

Adjustment

Waste

Return

```


---

# 12. RECIPE PROCESS


Setiap menu memiliki resep.


Contoh:


Menu:


```

Nasi Goreng

```


Komposisi:


```

Beras

Telur

Bumbu

Minyak

Sayuran

```


---

# 13. AUTOMATIC STOCK REDUCTION


Ketika order selesai:


```

Order Paid

↓

Recipe Calculation

↓

Reduce Ingredient Stock

↓

Update Cost

```


---

# 14. PURCHASING PROCESS


## 14.1 Need Detection


Sistem:


```

Stock Below Minimum

↓

Purchase Recommendation

```


---

# 14.2 Purchase Request


Staff:


```

Create Request

↓

Manager Approval

```


---

# 14.3 Purchase Order


Flow:


```

Approved Request

↓

PO Created

↓

Supplier

↓

Delivery

```


---

# 15. SUPPLIER PROCESS


Data:


```

Supplier Profile

Contact

Product

Price History

Payment Term

```


---

# 16. OWNER PROCESS


Owner membutuhkan:


```

Dashboard

Revenue

Profit

Stock

Employee

Performance

```


---

# 17. OWNER DASHBOARD


Informasi:


## Hari Ini


```

Sales

Transaction Count

Top Menu

Cash Position

```


---

## Bulanan


```

Revenue

Expense

Profit

Growth

```


---

# 18. MANAGER PROCESS


Manager mengontrol:


```

Operational Activity

Employee

Stock

Purchase

Approval

```


---

# 19. APPROVAL PROCESS


Contoh:


Pembelian:


```

Staff Request

↓

Manager

↓

Owner

↓

Purchase

```


---

# 20. ACCOUNTING PROCESS


Setiap transaksi menghasilkan:


```

Revenue

Cost

Expense

Profit

```


---

# 21. FINANCIAL FLOW


Penjualan:


```

Customer Payment

↓

Cash/Bank

↓

Revenue

```


Pembelian:


```

Supplier Payment

↓

Expense

↓

Inventory Cost

```


---

# 22. AUDIT PROCESS


Tujuan:


Mencegah:


- manipulasi;
- kehilangan barang;
- kecurangan.


---

# 23. Audit Activity


Dicatat:


```

Who

What

When

Where

Before

After

```


---

# 24. FRAUD SCENARIO


## Scenario 1


Kasir membatalkan transaksi.


Sistem:


```

Record Cancellation

Require Reason

Manager Approval

Audit Log

```


---

## Scenario 2


Stok hilang.


Sistem:


```

Stock Difference

Investigation

Adjustment Approval

```


---

## Scenario 3


Harga diubah.


Sistem:


```

Price Change History

Approval

Audit

```


---

# 25. SOP OPERASIONAL


## Opening SOP


```

Open Outlet

Check Cash

Check Equipment

Check Stock

Prepare Kitchen

```


---

## Operating SOP


```

Receive Order

Process Food

Serve Customer

Record Transaction

Monitor Stock

```


---

## Closing SOP


```

Close Order

Cash Reconciliation

Stock Check

Cleaning

Daily Report

```


---

# 26. DAILY BUSINESS CYCLE


```

OPENING

↓

ORDER

↓

PRODUCTION

↓

SALES

↓

PAYMENT

↓

REPORT

↓

CLOSING

```


---

# 27. Exception Handling


Sistem harus menangani:


```

Customer Cancel

Kitchen Delay

Stock Empty

Wrong Order

Refund

Payment Failed

```


---

# 28. BUSINESS KPI


## Sales KPI


```

Daily Sales

Average Transaction

Customer Count

```


---

## Operational KPI


```

Preparation Time

Waste Percentage

Stock Accuracy

```


---

## Financial KPI


```

Gross Profit

Food Cost

Operating Expense

```


---

# 29. AI Opportunity


AI dapat membantu:


```

Forecast Demand

Menu Recommendation

Stock Prediction

Fraud Detection

```


---

# 30. Kesimpulan


Restaurant & Cafe ERP harus memahami bisnis secara menyeluruh.


Bukan hanya:


```

Input Order

Cetak Nota

```


Tetapi:


```

Mengelola

Manusia

Barang

Uang

Proses

Data

Keputusan

```


Prinsip:


```

Digitalize The Business Process,

Not Only The Transaction.

```


---

# Document End


Document ID:

EBP-RC-BUSINESS-PROCESS-001


Version:

1.0
