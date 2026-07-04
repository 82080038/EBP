# Platform Bisnis Enterprise (EBP)

**Platform Perusahaan Software untuk Membangun Aplikasi Enterprise**

---

## Tentang EBP

EBP bukan hanya sebuah aplikasi. EBP adalah **platform perusahaan software** yang dirancang untuk membangun dan mengelola banyak produk enterprise dengan fondasi bersama dan database domain independen.

### Filosofi

```

SATU PLATFORM

+

BANYAK PRODUK

```

### Visi

Untuk membangun platform perusahaan software yang berkelanjutan yang memungkinkan pengembangan cepat aplikasi enterprise-grade di berbagai industri.

### Misi

- Menyediakan fondasi yang kuat untuk aplikasi enterprise
- Memungkinkan penggunaan ulang aset di berbagai produk
- Menjaga standar kualitas dan keamanan yang tinggi
- Mendukung arsitektur multi-tenant yang skalabel
- Memfasilitasi pertumbuhan organisasi jangka panjang

---

## Arsitektur Platform

### Platform Inti

Platform inti menyediakan fondasi bersama yang digunakan oleh semua produk:

- **Manajemen Identitas**: Pengguna, Peran, Izin, RBAC
- **Organisasi**: Tenant, Perusahaan, Cabang, Departemen
- **Keamanan**: Jejak Audit, Riwayat Login, Kejadian Keamanan
- **Workflow**: Mesin Workflow, Sistem Persetujuan
- **Notifikasi**: Email, SMS, Notifikasi Push
- **Manajemen File**: Upload File, Penyimpanan Dokumen
- **Data Master**: Negara, Mata Uang, Kode Pajak, Satuan
- **Mitra Bisnis**: Manajemen mitra bersama

### Ekosistem Produk

Setiap produk memiliki database domain-spesifik dan logika bisnisnya sendiri:

- **Restaurant ERP**: Menu, Pesanan, Dapur, Inventaris, Akuntansi
- **Hotel ERP**: Kamar, Reservasi, Check-in/out, Housekeeping
- **Sistem Parkir**: Slot, Kendaraan, Tiket, Tarif
- **Agriculture ERP**: Pertanian, Panen, Produksi, Gudang
- **Sistem Legal**: Klien, Kasus, Dokumen, Tagihan

---

## Struktur Proyek

```

EBP/

├── PLATFORM_BISNIS_ENTERPRISE/

│   ├── 00_MANIFESTO_EBP/              # Konstitusi, Visi, Filosofi
│   ├── 01_ARSITEKTUR_ENTERPRISE/       # Ikhtisar Arsitektur
│   ├── 02_FONDASI_BISNIS/             # Ontologi Bisnis, Data Master
│   ├── 03_STANDAR_TEKNIS/             # Standar Database, Framework Inti
│   ├── 04_MESIN_BISNIS/               # Arsitektur Mesin
│   ├── 05_ARSITEKTUR_KEAMANAN/        # Arsitektur Keamanan
│   ├── 06_ARSITEKTUR_DEVOPS/          # Arsitektur DevOps
│   ├── 07_MANAJEMEN_PRODUK/           # Siklus Pengembangan Produk
│   ├── 08_BLUEPRINT_PRODUK/           # Blueprint Restaurant ERP
│   ├── 09_DESAIN_DATABASE/            # Desain Database & ERD
│   ├── 10_DESIGN_API/                 # Spesifikasi API
│   ├── 11_ARSITEKTUR_APLIKASI/        # Arsitektur Backend & Frontend
│   ├── EBP_STRUKTUR_DIREKTORI_PLATFORM.md
│   ├── EBP_RENCANA_MIGRASI_PLATFORM.md
│   └── EBP_ARSITEKTUR_DATABASE.md

│
└── ebp-restaurant-backend/              # Implementasi Backend Restaurant ERP

    ├── config/                          # Konfigurasi Database
    ├── core/                            # Framework Inti (JWT, Response, Router)
    │   ├── Engines/                     # Mesin Bisnis (Stock, Kitchen, Accounting)
    │   └── Middleware/                  # Middleware Auth, Permission, Tenant
    ├── modules/                         # Modul Bisnis
    │   ├── Auth/                         # Modul Autentikasi
    │   └── Sales/                        # Modul Penjualan (Pesanan, POS)
    ├── routes/                          # Route API
    ├── public/                          # Titik Masuk Publik
    └── README.md

```


---

## Dokumentasi

### Dokumen Fondasi

| Dokumen | Deskripsi |
|----------|-------------|
| [EBP_CONSTITUTION.md](PLATFORM_BISNIS_ENTERPRISE/00_MANIFESTO_EBP/EBP_CONSTITUTION.md) | Konstitusi platform dan prinsip fundamental |
| [EBP_VISION_MISSION.md](PLATFORM_BISNIS_ENTERPRISE/00_MANIFESTO_EBP/EBP_VISION_MISSION.md) | Pernyataan visi dan misi |
| [EBP_PHILOSOPHY.md](PLATFORM_BISNIS_ENTERPRISE/00_MANIFESTO_EBP/EBP_PHILOSOPHY.md) | Filosofi dan budaya platform |
| [EBP_CORE_PRINCIPLES.md](PLATFORM_BISNIS_ENTERPRISE/00_MANIFESTO_EBP/EBP_CORE_PRINCIPLES.md) | Prinsip inti yang harus diikuti |

### Dokumen Arsitektur

| Dokumen | Deskripsi |
|----------|-------------|
| [EBP_ENTERPRISE_ARCHITECTURE.md](PLATFORM_BISNIS_ENTERPRISE/01_ARSITEKTUR_ENTERPRISE/EBP_ENTERPRISE_ARCHITECTURE.md) | Ikhtisar arsitektur enterprise |
| [EBP_SECURITY_ARCHITECTURE.md](PLATFORM_BISNIS_ENTERPRISE/05_ARSITEKTUR_KEAMANAN/EBP_SECURITY_ARCHITECTURE.md) | Arsitektur dan standar keamanan |
| [EBP_DEVOPS_ARCHITECTURE.md](PLATFORM_BISNIS_ENTERPRISE/06_ARSITEKTUR_DEVOPS/EBP_DEVOPS_ARCHITECTURE.md) | Arsitektur DevOps dan deployment |

### Dokumen Teknis

| Dokumen | Deskripsi |
|----------|-------------|
| [EBP_DATABASE_STANDARD.md](PLATFORM_BISNIS_ENTERPRISE/03_STANDAR_TEKNIS/EBP_DATABASE_STANDARD.md) | Standar desain database |
| [EBP_CORE_FRAMEWORK.md](PLATFORM_BISNIS_ENTERPRISE/03_STANDAR_TEKNIS/EBP_CORE_FRAMEWORK.md) | Blueprint framework inti |
| [EBP_ENGINE_ARCHITECTURE.md](PLATFORM_BISNIS_ENTERPRISE/04_MESIN_BISNIS/EBP_ENGINE_ARCHITECTURE.md) | Arsitektur mesin bisnis |

### Dokumen Strategi Platform

| Dokumen | Deskripsi |
|----------|-------------|
| [EBP_STRUKTUR_DIREKTORI_PLATFORM.md](PLATFORM_BISNIS_ENTERPRISE/EBP_STRUKTUR_DIREKTORI_PLATFORM.md) | Struktur direktori platform |
| [EBP_RENCANA_MIGRASI_PLATFORM.md](PLATFORM_BISNIS_ENTERPRISE/EBP_RENCANA_MIGRASI_PLATFORM.md) | Strategi dan rencana migrasi |
| [EBP_ARSITEKTUR_DATABASE.md](PLATFORM_BISNIS_ENTERPRISE/EBP_ARSITEKTUR_DATABASE.md) | Strategi arsitektur database |

### Dokumen Restaurant ERP

| Dokumen | Deskripsi |
|----------|-------------|
| [EBP_PRODUCT_RESTAURANT_CAFE_ERP.md](PLATFORM_BISNIS_ENTERPRISE/08_BLUEPRINT_PRODUK/EBP_PRODUCT_RESTAURANT_CAFE_ERP.md) | Blueprint produk Restaurant ERP |
| [EBP_RESTAURANT_CAFE_BUSINESS_PROCESS.md](PLATFORM_BISNIS_ENTERPRISE/08_BLUEPRINT_PRODUK/EBP_RESTAURANT_CAFE_BUSINESS_PROCESS.md) | Dokumentasi proses bisnis |
| [EBP_RESTAURANT_CAFE_MODULE_SPECIFICATION.md](PLATFORM_BISNIS_ENTERPRISE/08_BLUEPRINT_PRODUK/EBP_RESTAURANT_CAFE_MODULE_SPECIFICATION.md) | Spesifikasi modul |
| [EBP_RESTAURANT_CAFE_DATABASE_DESIGN.md](PLATFORM_BISNIS_ENTERPRISE/09_DESAIN_DATABASE/EBP_RESTAURANT_CAFE_DATABASE_DESIGN.md) | Dokumentasi desain database |
| [EBP_RESTAURANT_CAFE_ERD.md](PLATFORM_BISNIS_ENTERPRISE/09_DESAIN_DATABASE/EBP_RESTAURANT_CAFE_ERD.md) | Diagram Hubungan Entitas |
| [EBP_RESTAURANT_CAFE_MYSQL_SCHEMA.sql](PLATFORM_BISNIS_ENTERPRISE/09_DESAIN_DATABASE/EBP_RESTAURANT_CAFE_MYSQL_SCHEMA.sql) | Skema MySQL lengkap |
| [EBP_RESTAURANT_CAFE_API_SPECIFICATION.md](PLATFORM_BISNIS_ENTERPRISE/10_DESIGN_API/EBP_RESTAURANT_CAFE_API_SPECIFICATION.md) | Spesifikasi API |
| [EBP_RESTAURANT_CAFE_BACKEND_ARCHITECTURE.md](PLATFORM_BISNIS_ENTERPRISE/11_ARSITEKTUR_APLIKASI/EBP_RESTAURANT_CAFE_BACKEND_ARCHITECTURE.md) | Arsitektur backend |
| [EBP_RESTAURANT_CAFE_FRONTEND_ARCHITECTURE.md](PLATFORM_BISNIS_ENTERPRISE/11_ARSITEKTUR_APLIKASI/EBP_RESTAURANT_CAFE_FRONTEND_ARCHITECTURE.md) | Arsitektur frontend |

---

## Stack Teknologi

### Backend

- **Bahasa**: PHP 8.x
- **Database**: MySQL 8.x
- **Arsitektur**: REST API
- **Autentikasi**: JWT
- **Pola**: Service Repository Pattern
- **Multi-tenant**: Didukung

### Frontend

- **Bahasa**: HTML5, CSS3, JavaScript
- **Framework**: jQuery, AJAX
- **Library UI**: Bootstrap
- **Arsitektur**: Aplikasi Berbasis AJAX

### DevOps

- **Kontrol Versi**: Git
- **Repositori**: GitHub
- **Kontainerisasi**: Docker (rencana)
- **CI/CD**: GitHub Actions (rencana)

---

## Arsitektur Database

### Database Inti (ebp_core)

Fondasi bersama yang digunakan oleh semua produk:

- Manajemen Identitas (users, roles, permissions)
- Organisasi (tenants, companies, branches)
- Keamanan (audit_logs, login_history, security_events)
- Workflow (workflow_definitions, approval_requests)
- Notifikasi (notifications, email_queue, sms_queue)
- Manajemen File (files, documents, attachments)
- Data Master (countries, currencies, tax_codes, units)
- Mitra Bisnis (business_partners)

### Database Produk

Setiap produk memiliki database domain-spesifiknya sendiri:

- **ebp_restaurant**: Menu, Pesanan, Dapur, Inventaris, Akuntansi
- **ebp_hotel**: Kamar, Reservasi, Check-in/out, Housekeeping
- **ebp_parking**: Slot, Kendaraan, Tiket, Tarif
- **ebp_agriculture**: Pertanian, Panen, Produksi, Gudang
- **ebp_legal**: Klien, Kasus, Dokumen, Tagihan

### Strategi Multi-Tenant

- **Model A**: Database per tenant (Enterprise)
- **Model B**: Database bersama dengan tenant_id (Startup)
- **Model C**: Hibrida (Platform SaaS)

---

## Memulai

### Prasyarat

- PHP 8.x atau lebih tinggi
- MySQL 8.x atau lebih tinggi
- Composer (untuk manajemen dependensi)
- Git

### Instalasi

1. **Clone repositori**

```bash
git clone https://github.com/82080038/EBP.git
cd EBP
```

2. **Impor Skema Database Inti**

```bash
mysql -u root -p < PLATFORM_BISNIS_ENTERPRISE/09_DESAIN_DATABASE/EBP_RESTAURANT_CAFE_MYSQL_SCHEMA.sql
```

3. **Konfigurasi Koneksi Database**

Edit `ebp-restaurant-backend/config/database.php`:

```php
private $host = "localhost";
private $dbname = "ebp_restaurant_erp";
private $username = "root";
private $password = "";
```

4. **Konfigurasi Web Server**

Arahkan web server Anda ke direktori `ebp-restaurant-backend/public/`.

### Endpoint API

#### Autentikasi

**POST** `/api/v1/auth/login`

```json
{
  "username": "admin",
  "password": "password"
}
```

#### Buat Pesanan

**POST** `/api/v1/orders`

**Header:**
```
Authorization: Bearer {access_token}
```

**Body Request:**
```json
{
  "customer_id": null,
  "items": [
    {
      "menu_id": 10,
      "qty": 2,
      "price": 25000
    }
  ]
}
```

---

## Fitur Enterprise

Backend Restaurant ERP mencakup fitur enterprise-ready:

✅ **Autentikasi JWT** - Autentikasi berbasis token dengan kedaluwarsa
✅ **Pengecekan Izin RBAC** - Kontrol akses berbasis peran
✅ **Isolasi Tenant** - Pemisahan data multi-tenant
✅ **Transaksi Database** - Kepatuhan ACID dengan rollback saat error
✅ **Mesin Stok** - Pengurangan inventaris otomatis dari resep
✅ **Antrian Dapur** - Pembuatan pesanan dapur
✅ **Jurnal Akuntansi** - Generasi jurnal otomatis
✅ **Jejak Audit** - Logging aktivitas lengkap

### Alur Transaksi Pesanan

```
Request
  ↓
Autentikasi JWT
  ↓
Pengecekan Izin (ORDER_CREATE)
  ↓
Validasi
  ↓
BEGIN TRANSACTION
  ↓
Buat Pesanan
  ↓
Buat Detail Pesanan
  ↓
Mesin Stok (Kurangi Inventaris)
  ↓
Mesin Dapur (Buat Pesanan Dapur)
  ↓
Mesin Akuntansi (Buat Jurnal)
  ↓
Jejak Audit (Log Aktivitas)
  ↓
COMMIT TRANSACTION
  ↓
Respons
```

---

## Peta Jalan Pengembangan

### Fase 1: Fondasi EBP (3 bulan)

- Modul Autentikasi
- Modul Izin
- Modul Tenant
- Modul Audit
- Layer Database
- Framework API
- Sistem Logging
- Manajemen File

### Fase 2: Restaurant MVP (2 bulan)

- Manajemen Menu
- Sistem POS
- Tampilan Dapur
- Inventaris Dasar
- Laporan Dasar

### Fase 3: Restaurant Enterprise (3 bulan)

- Inventaris Lanjutan
- Integrasi Akuntansi
- Fitur AI
- Multi-Outlet
- Laporan Lanjutan

### Fase 4: Produk Kedua (4 bulan)

- Hotel ERP atau Sistem Parkir
- Analisis Produk
- Pengembangan Produk
- Peluncuran Produk

### Fase 5: Peningkatan Platform (Berlanjut)

- Optimasi Performa
- Peningkatan Keamanan
- Penambahan Fitur
- Pengalaman Developer

---

## Kontribusi

EBP mengikuti praktik pengembangan software profesional:

1. **Ikuti Prinsip Inti** - Semua perubahan harus selaras dengan EBP_CORE_PRINCIPLES.md
2. **Pisahkan Inti dari Produk** - Perubahan inti memerlukan tinjauan arsitektur
3. **Gunakan Branching yang Tepat** - branch fitur untuk fitur baru
4. **Tulis Tes** - Unit test untuk komponen inti
5. **Dokumentasikan Perubahan** - Update dokumentasi yang relevan
6. **Ikuti Standar Coding** - Patuhi EBP_DATABASE_STANDARD.md

### Konvensi Commit

Format: `[type]: subject`

Tipe:
- `feat`: Fitur baru
- `fix`: Perbaikan bug
- `docs`: Dokumentasi
- `style`: Gaya kode
- `refactor`: Refactoring kode
- `test`: Pengujian
- `chore`: Pemeliharaan

Contoh:
```
feat(core): tambahkan autentikasi JWT
feat(restaurant): tambahkan pembuatan pesanan POS
fix(core): selesaikan masalah isolasi tenant
docs(core): perbarui dokumentasi API
```

---

## Lisensi

Proyek ini adalah software proprietary. Hak cipta dilindungi.

---

## Kontak

- **Repositori**: https://github.com/82080038/EBP
- **Platform**: Platform Bisnis Enterprise (EBP)
- **Produk Pertama**: Restaurant & Cafe ERP

---

## Penghargaan

EBP dibangun dengan visi menciptakan platform perusahaan software yang berkelanjutan yang memungkinkan pengembangan cepat aplikasi enterprise-grade di berbagai industri.

---

**Versi**: 1.0

**Terakhir Diperbarui**: 2026-07-01

**Status**: Fondasi Selesai - Restaurant ERP dalam Pengembangan
