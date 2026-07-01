# RESTAURANT ERP

# Use Case Document

**Document ID:** EBP-PRODUCT-RESTAURANT-USECASE-001
**Version:** 1.0
**Category:** Product Analysis
**Status:** Official Use Case Specification

---

# 1. Use Case Overview

Dokumen ini mendefinisikan use case utama untuk Restaurant ERP, mencakup interaksi antara user dengan sistem untuk mencapai tujuan bisnis.

---

# 2. Actor Definition

## 2.1 Primary Actors

* **Customer** - Pelanggan restoran
* **Cashier** - Kasir yang memproses transaksi
* **Waiter** - Pelayan yang melayani meja
* **Kitchen Staff** - Staf dapur yang menyiapkan makanan
* **Manager** - Manajer operasional restoran
* **Owner** - Pemilik restoran
* **Accountant** - Staf keuangan
* **Inventory Manager** - Staf pengelola stok
* **System Admin** - Administrator sistem

## 2.2 Secondary Actors

* **Payment Gateway** - Sistem pembayaran eksternal
* **Accounting System** - Sistem akuntansi eksternal
* **Notification Service** - Layanan notifikasi (WhatsApp, Email)
* **AI Engine** - Engine analisis AI

---

# 3. Use Case Catalog

## UC-01: Login to System

**Actor:** Cashier, Waiter, Manager, Owner, Accountant, Inventory Manager, System Admin

**Description:** User login ke sistem Restaurant ERP

**Precondition:**
* User memiliki akun
* User memiliki kredensial valid

**Main Flow:**
1. User membuka halaman login
2. User memasukkan email dan password
3. User klik tombol login
4. Sistem validasi kredensial
5. Sistem buat session
6. Sistem redirect ke dashboard

**Alternative Flow:**
- 4a. Kredensial invalid: Sistem tampilkan error message
- 4b. Akun terkunci: Sistem tampilkan pesan dan instruksi reset

**Postcondition:**
* User berhasil login
* Session aktif

---

## UC-02: Create Order

**Actor:** Cashier, Waiter

**Description:** Membuat order baru untuk pelanggan

**Precondition:**
* User sudah login
* Menu tersedia
* Table tersedia (untuk dine-in)

**Main Flow:**
1. User pilih "New Order"
2. User pilih tipe order (dine-in/takeaway/delivery)
3. User pilih table (jika dine-in)
4. User tambah item ke cart
5. User pilih modifier (jika ada)
6. User konfirmasi order
7. Sistem simpan order
8. Sistem kirim ke kitchen display
9. Sistem hitung total

**Alternative Flow:**
- 4a. Item tidak tersedia: Sistem tampilkan pesan out of stock
- 7a. Payment required: Sistem redirect ke payment flow

**Postcondition:**
* Order dibuat
* Status order: PENDING
* Kitchen menerima order

---

## UC-03: Process Payment

**Actor:** Cashier

**Description:** Memproses pembayaran order

**Precondition:**
* Order ada
* Total dihitung

**Main Flow:**
1. User pilih order
2. User pilih metode pembayaran
3. User masukkan nominal (jika cash)
4. User proses pembayaran
5. Sistem validasi pembayaran
6. Sistem update status order: PAID
7. Sistem cetak receipt
8. Sistem kirim notifikasi (jika perlu)

**Alternative Flow:**
- 5a. Pembayaran gagal: Sistem tampilkan error dan retry
- 5b. Pembayaran insufficient: Sistem tampilkan pesan kurang

**Postcondition:**
* Order dibayar
* Receipt dicetak
* Inventory terupdate
* Accounting journal dibuat

---

## UC-04: Manage Menu

**Actor:** Manager, Owner

**Description:** Mengelola menu restoran

**Precondition:**
* User memiliki permission menu management

**Main Flow:**
1. User buka menu management
2. User pilih action (create/edit/delete)
3. User input menu data
4. User upload gambar (jika create/edit)
5. User set harga
6. User set availability
7. User simpan
8. Sistem validasi data
9. Sistem update menu

**Alternative Flow:**
- 8a. Data invalid: Sistem tampilkan error
- 8a. Duplicate name: Sistem tampilkan pesan

**Postcondition:**
* Menu terupdate
* Cache di-invalidate

---

## UC-05: Manage Table

**Actor:** Manager, Waiter

**Description:** Mengelola meja restoran

**Precondition:**
* User memiliki permission table management

**Main Flow:**
1. User buka table management
2. User lihat table layout
3. User assign table ke order
6. User update table status
7. Sistem update table

**Alternative Flow:**
- 6a. Table occupied: Sistem tampilkan pesan

**Postcondition:**
* Table terassign
* Status terupdate

---

## UC-06: Process Kitchen Order

**Actor:** Kitchen Staff

**Description:** Memproses order di dapur

**Precondition:**
* Order masuk ke kitchen display

**Main Flow:**
1. Kitchen staff lihat KDS
2. Staff pilih order
3. Staff mulai preparation
4. Sistem update status: PREPARING
5. Staff selesai preparation
6. Sistem update status: READY
7. Staff konfirmasi serving
8. Sistem update status: SERVED

**Alternative Flow:**
- 4a. Item tidak available: Staff reject item

**Postcondition:**
* Order selesai
- Inventory terkonsumsi
* Notifikasi dikirim ke waiter

---

## UC-07: Manage Inventory

**Actor:** Inventory Manager

**Description:** Mengelola stok inventory

**Precondition:**
* User memiliki permission inventory management

**Main Flow:**
1. User buka inventory management
2. User lihat stock level
3. User pilih action (stock in/out/adjust)
4. User input quantity
5. User input reason
6. User simpan
7. Sistem update stock
8. Sistem catat audit trail

**Alternative Flow:**
- 7a. Stock insufficient: Sistem tampilkan error

**Postcondition:**
* Stock terupdate
* Audit trail tercatat

---

## UC-08: Create Purchase Order

**Actor:** Inventory Manager

**Description:** Membuat purchase order ke supplier

**Precondition:**
* Supplier tersedia
* Item tersedia

**Main Flow:**
1. User buka purchase order
2. User pilih supplier
3. User tambah item
4. User input quantity
5. User input expected price
6. User submit PO
7. Sistem simpan PO
8. Sistem kirim ke supplier (jika integrated)

**Alternative Flow:**
- 8a. Approval required: Sistem kirim ke approval workflow

**Postcondition:**
* PO dibuat
* Status: PENDING

---

## UC-09: Process Goods Receipt

**Actor:** Inventory Manager

**Description:** Menerima barang dari supplier

**Precondition:**
* PO ada
* Status PO: APPROVED

**Main Flow:**
1. User buka goods receipt
2. User pilih PO
3. User input actual quantity
4. User input batch/expiry (jika perlu)
5. User konfirmasi receipt
6. Sistem update stock
7. Sistem update PO status: RECEIVED
8. Sistem buat accounting journal

**Alternative Flow:**
- 6a. Variance detected: Sistem buat adjustment note

**Postcondition:**
* Stock bertambah
* PO selesai
* Accounting terupdate

---

## UC-10: Perform Stock Opname

**Actor:** Inventory Manager

**Description:** Melakukan stock opname

**Precondition:**
* Opname schedule ada

**Main Flow:**
1. User buka stock opname
2. User pilih location
3. User input actual count
4. Sistem hitung variance
5. User konfirmasi variance
6. Sistem buat adjustment
7. Sistem update stock
8. Sistem buat report

**Alternative Flow:**
- 6a. Variance besar: Sistem butuh approval

**Postcondition:**
* Stock terupdate
* Variance tercatat
* Report dibuat

---

## UC-11: Manage Recipe

**Actor:** Chef, Manager

**Description:** Mengelola resep/menu BOM

**Precondition:**
* Menu item ada
* Ingredient tersedia

**Main Flow:**
1. User buka recipe management
2. User pilih menu
3. User tambah ingredient
4. User input quantity
5. User input unit
6. User simpan recipe
7. Sistem hitung food cost
8. Sistem update recipe

**Alternative Flow:**
- 7a. Ingredient tidak ada: Sistem tampilkan error

**Postcondition:**
* Recipe terupdate
* Food cost terhitung

---

## UC-12: View Dashboard

**Actor:** Manager, Owner

**Description:** Melihat dashboard operasional

**Precondition:**
* User sudah login

**Main Flow:**
1. User buka dashboard
2. Sistem tampilkan sales overview
3. Sistem tampilkan top items
4. Sistem tampilkan low stock alert
5. Sistem tampilkan pending orders
6. Sistem tampilkan staff status

**Postcondition:**
* Dashboard ditampilkan
* Data real-time

---

## UC-13: Generate Report

**Actor:** Manager, Owner, Accountant

**Description:** Generate laporan

**Precondition:**
* User memiliki permission report

**Main Flow:**
1. User buka report
2. User pilih report type
3. User pilih date range
4. User pilih filter
5. User generate report
6. Sistem proses data
7. Sistem tampilkan report
8. User export (jika perlu)

**Alternative Flow:**
- 6a. Data besar: Sistem buat job queue

**Postcondition:**
* Report digenerate
* Data tersedia

---

## UC-14: Manage Customer

**Actor:** Waiter, Manager

**Description:** Mengelola data pelanggan

**Precondition:**
* User memiliki permission customer management

**Main Flow:**
1. User buka customer management
2. User pilih action (create/edit/view)
3. User input customer data
4. User simpan
5. Sistem validasi data
6. Sistem update customer

**Alternative Flow:**
- 5a. Duplicate: Sistem tampilkan pesan

**Postcondition:**
* Customer terupdate
* History tersimpan

---

## UC-15: Manage Reservation

**Actor:** Waiter, Manager

**Description:** Mengelola reservasi meja

**Precondition:**
* Table tersedia

**Main Flow:**
1. User buka reservation
2. User pilih date/time
3. User pilih table
4. User input customer info
5. User input guest count
6. User simpan reservation
7. Sistem konfirmasi
8. Sistem kirim notifikasi

**Alternative Flow:**
- 6a. Table unavailable: Sistem tampilkan error

**Postcondition:**
* Reservasi dibuat
* Table diblokir
- Notifikasi dikirim

---

## UC-16: Manage Expense

**Actor:** Manager, Accountant

**Description:** Mengelola pengeluaran

**Precondition:**
* User memiliki permission expense management

**Main Flow:**
1. User buka expense management
2. User pilih category
3. User input amount
4. User input description
5. User upload receipt (jika ada)
6. User submit expense
7. Sistem validasi
8. Sistem simpan expense

**Alternative Flow:**
- 7a. Approval required: Sistem kirim ke workflow

**Postcondition:**
* Expense tercatat
* Accounting terupdate

---

## UC-17: Manage Cashier Shift

**Actor:** Cashier, Manager

**Description:** Mengelola shift kasir

**Precondition:**
* Cashier sudah login

**Main Flow:**
1. User buka shift management
2. User buka shift (start)
3. Sistem catat start time
4. User proses transaksi
5. User tutup shift (end)
6. Sistem catat end time
7. Sistem hitung total
8. User input actual cash
9. Sistem hitung variance
10. User konfirmasi
11. Sistem selesaikan shift

**Alternative Flow:**
- 9a. Variance: Sistem butuh approval

**Postcondition:**
* Shift selesai
* Report dibuat
- Variance tercatat

---

## UC-18: View AI Forecast

**Actor:** Manager, Owner

**Description:** Melihat forecast AI

**Precondition:**
* AI engine aktif
* Data tersedia

**Main Flow:**
1. User buka AI forecast
2. User pilih type (sales/inventory/staff)
3. User pilih period
4. Sistem tampilkan forecast
5. User lihat recommendation
6. User download (jika perlu)

**Postcondition:**
* Forecast ditampilkan
* Recommendation tersedia

---

## UC-19: Manage User

**Actor:** System Admin, Manager

**Description:** Mengelola user sistem

**Precondition:**
* User memiliki permission user management

**Main Flow:**
1. User buka user management
2. User pilih action (create/edit/deactivate)
3. User input user data
4. User assign role
5. User simpan
6. Sistem validasi
7. Sistem update user

**Alternative Flow:**
- 6a. Duplicate email: Sistem tampilkan error

**Postcondition:**
* User terupdate
* Role terassign

---

## UC-20: Manage Role Permission

**Actor:** System Admin

**Description:** Mengelola role dan permission

**Precondition:**
* User memiliki permission role management

**Main Flow:**
1. User buka role management
2. User pilih role
3. User edit permission
4. User simpan
5. Sistem update permission

**Postcondition:**
* Permission terupdate
* Efek immediate

---

# 4. Use Case Relationship

## 4.1 Include

* UC-02 includes UC-03 (Create Order includes Process Payment)
* UC-08 includes UC-09 (Create PO includes Goods Receipt)

## 4.2 Extend

* UC-02 extends UC-20 (Manage Order extends with special discount)
* UC-07 extends UC-10 (Manage Inventory extends with Stock Opname)

## 4.3 Generalization

* UC-01 (Login) is generalization for all actor login flows

---

# END OF DOCUMENT

Document ID: EBP-PRODUCT-RESTAURANT-USECASE-001
Version: 1.0
