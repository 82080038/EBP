# Platform Bisnis Enterprise (EBP)

# Dokumen Struktur Direktori Platform


**ID Dokumen:** EBP-PLATFORM-DIRECTORY-001

**Versi:** 1.0

**Tujuan:** Mendefinisikan struktur organisasi EBP sebagai platform perusahaan software



---

# 1. Filosofi Platform


EBP bukan aplikasi tunggal. EBP adalah **platform perusahaan software**.


Prinsip inti:


```

SATU PLATFORM

+

BANYAK PRODUK

```


Analogi:


- Microsoft memiliki Platform .NET → Banyak Produk
- Google memiliki Platform Cloud → Banyak Layanan
- Salesforce memiliki Platform → Banyak Solusi CRM
- Oracle memiliki Platform Fusion → Banyak Aplikasi Enterprise


EBP mengikuti pola yang sama.


---

# 2. Mengapa Pendekatan Platform?


## Masalah dengan Pendekatan Copy-Paste


Jika kita menyalin dokumen EBP ke setiap proyek:


```
Restaurant_Project/
    └── copy ENTERPRISE_BUSINESS_PLATFORM/

Hotel_Project/
    └── copy ENTERPRISE_BUSINESS_PLATFORM/

Parking_Project/
    └── copy ENTERPRISE_BUSINESS_PLATFORM/
```


Hasil:


- Beberapa versi dokumen inti
- Standar tidak konsisten
- Tidak ada perbaikan bersama
- Mimpi buruk pemeliharaan
- Tidak ada penggunaan kembali aset


## Manfaat Pendekatan Platform


```
EBP_PLATFORM/
    ├── CORE/ (Bersama)
    └── PRODUCTS/
        ├── Restaurant ERP
        ├── Hotel ERP
        ├── Parking System
        └── Farming ERP
```


Manfaat:


- Sumber kebenaran tunggal
- Perbaikan bersama menguntungkan semua produk
- Standar konsisten
- Penggunaan kembali aset
- Organisasi yang dapat diskalakan


---

# 3. Struktur Direktori Platform


```
EBP_PLATFORM/

│
├── 00_CONSTITUTION/
│
│   ├── EBP_CONSTITUTION.md
│   ├── EBP_VISION_MISSION.md
│   ├── EBP_PHILOSOPHY.md
│   └── EBP_CORE_PRINCIPLES.md
│
│
├── 01_ARCHITECTURE/
│
│   ├── EBP_ENTERPRISE_ARCHITECTURE.md
│   ├── EBP_SECURITY_ARCHITECTURE.md
│   └── EBP_DEVOPS_ARCHITECTURE.md
│
│
├── 02_FOUNDATION/
│
│   ├── EBP_BUSINESS_ONTOLOGY.md
│   └── EBP_MASTER_DATA_MODEL.md
│
│
├── 03_TECHNICAL_STANDARD/
│
│   ├── EBP_DATABASE_STANDARD.md
│   └── EBP_CORE_FRAMEWORK.md
│
│
├── 04_ENGINE/
│
│   └── EBP_ENGINE_ARCHITECTURE.md
│
│
├── 05_PRODUCT_MANAGEMENT/
│
│   └── EBP_PRODUCT_DEVELOPMENT_LIFECYCLE.md
│
│
├── 06_CORE_CODE/
│
│   ├── Authentication/
│   ├── Permission/
│   ├── Audit/
│   ├── Tenant/
│   ├── Workflow/
│   └── Notification/
│
│
├── 07_SHARED_ENGINES/
│
│   ├── Pricing Engine/
│   ├── Inventory Engine/
│   ├── Accounting Engine/
│   ├── Forecast Engine/
│   └── AI Engine/
│
│
├── 08_DOCUMENTATION_TEMPLATES/
│
│   ├── API Specification Template
│   ├── Backend Architecture Template
│   └── Frontend Architecture Template
│
│
├── 11_ENTERPRISE_SOFTWARE_ASSET_MANAGEMENT_FRAMEWORK/

│   ├── README.md
│   ├── EBP_REPOSITORY_MIGRATION_GUIDE.md
│   ├── EBP_CODE_REUSE_STANDARD.md
│   ├── EBP_REPOSITORY_CLASSIFICATION.md
│   ├── EBP_REPOSITORY_AUDIT_TEMPLATE.md
│   ├── EBP_COMPONENT_EXTRACTION_GUIDE.md
│   ├── EBP_PLATFORMIZATION_GUIDE.md
│   ├── EBP_MIGRATION_CHECKLIST.md
│   ├── EBP_SOFTWARE_ASSET_INVENTORY.md
│   ├── EBP_ENTERPRISE_KNOWLEDGE_GRAPH.md
│   │
│   ├── 00_CONSTITUTION/
│   │   ├── ESAMF_VISION.md
│   │   ├── ESAMF_PHILOSOPHY.md
│   │   ├── ESAMF_CORE_PRINCIPLES.md
│   │   └── ESAMF_GLOSSARY.md
│   │
│   ├── 01_ANALYSIS/
│   │   ├── ESAMF_REPOSITORY_ANALYSIS_STANDARD.md
│   │   ├── ESAMF_DATABASE_ANALYSIS_STANDARD.md
│   │   ├── ESAMF_SOURCE_CODE_ANALYSIS_STANDARD.md
│   │   ├── ESAMF_MODULE_ANALYSIS_STANDARD.md
│   │   └── ESAMF_DEPENDENCY_ANALYSIS.md
│   │
│   ├── 02_CLASSIFICATION/
│   │   ├── ESAMF_COMPONENT_CLASSIFICATION.md
│   │   ├── ESAMF_BUSINESS_DOMAIN_CLASSIFICATION.md
│   │   └── ESAMF_REUSABILITY_MATRIX.md
│   │
│   ├── 03_EXTRACTION/
│   │   ├── ESAMF_CORE_EXTRACTION_GUIDE.md
│   │   ├── ESAMF_SHARED_ENGINE_EXTRACTION.md
│   │   └── ESAMF_PRODUCT_EXTRACTION.md
│   │
│   ├── 04_REFACTORING/
│   │   ├── ESAMF_REFACTORING_STANDARD.md
│   │   ├── ESAMF_DATABASE_REFACTORING.md
│   │   ├── ESAMF_API_REFACTORING.md
│   │   └── ESAMF_UI_REFACTORING.md
│   │
│   ├── 05_PLATFORMIZATION/
│   │   ├── ESAMF_PLATFORM_MAPPING.md
│   │   ├── ESAMF_EBP_INTEGRATION_GUIDE.md
│   │   └── ESAMF_PRODUCT_CONVERSION.md
│   │
│   ├── 06_VALIDATION/
│   │   ├── ESAMF_VALIDATION_CHECKLIST.md
│   │   ├── ESAMF_TESTING_GUIDE.md
│   │   └── ESAMF_QUALITY_GATE.md
│   │
│   ├── 07_MANAGEMENT/
│   │   ├── RESTORAN/
│   │   │   ├── 01_CURRENT_ANALYSIS.md
│   │   │   ├── 02_DATABASE_ANALYSIS.md
│   │   │   ├── 03_SOURCE_CODE_ANALYSIS.md
│   │   │   ├── 04_MODULE_ANALYSIS.md
│   │   │   ├── 05_REUSABLE_COMPONENTS.md
│   │   │   ├── 06_CORE_EXTRACTION_PLAN.md
│   │   │   ├── 07_DATABASE_MIGRATION_PLAN.md
│   │   │   ├── 08_EBP_PRODUCT_MAPPING.md
│   │   │   ├── 09_REFACTORING_PLAN.md
│   │   │   └── 10_IMPLEMENTATION_PROGRESS.md
│   │   │
│   │   ├── MYWISATA/
│   │   │   ├── 01_CURRENT_ANALYSIS.md
│   │   │   ├── 02_DATABASE_ANALYSIS.md
│   │   │   ├── 03_SOURCE_CODE_ANALYSIS.md
│   │   │   ├── 04_MODULE_ANALYSIS.md
│   │   │   ├── 05_REUSABLE_COMPONENTS.md
│   │   │   ├── 06_CORE_EXTRACTION_PLAN.md
│   │   │   ├── 07_DATABASE_MIGRATION_PLAN.md
│   │   │   ├── 08_EBP_PRODUCT_MAPPING.md
│   │   │   ├── 09_REFACTORING_PLAN.md
│   │   │   └── 10_IMPLEMENTATION_PROGRESS.md
│   │   │
│   │   ├── PANGLONG/
│   │   │   ├── 01_CURRENT_ANALYSIS.md
│   │   │   ├── 02_DATABASE_ANALYSIS.md
│   │   │   ├── 03_SOURCE_CODE_ANALYSIS.md
│   │   │   ├── 04_MODULE_ANALYSIS.md
│   │   │   ├── 05_REUSABLE_COMPONENTS.md
│   │   │   ├── 06_CORE_EXTRACTION_PLAN.md
│   │   │   ├── 07_DATABASE_MIGRATION_PLAN.md
│   │   │   ├── 08_EBP_PRODUCT_MAPPING.md
│   │   │   ├── 09_REFACTORING_PLAN.md
│   │   │   └── 10_IMPLEMENTATION_PROGRESS.md
│   │   │
│   │   ├── SAHAM/
│   │   │   ├── 01_CURRENT_ANALYSIS.md
│   │   │   ├── 02_DATABASE_ANALYSIS.md
│   │   │   ├── 03_SOURCE_CODE_ANALYSIS.md
│   │   │   ├── 04_MODULE_ANALYSIS.md
│   │   │   ├── 05_REUSABLE_COMPONENTS.md
│   │   │   ├── 06_CORE_EXTRACTION_PLAN.md
│   │   │   ├── 07_DATABASE_MIGRATION_PLAN.md
│   │   │   ├── 08_EBP_PRODUCT_MAPPING.md
│   │   │   ├── 09_REFACTORING_PLAN.md
│   │   │   └── 10_IMPLEMENTATION_PROGRESS.md
│   │   │
│   │   ├── PELAJARAN/
│   │   │   ├── 01_CURRENT_ANALYSIS.md
│   │   │   ├── 02_DATABASE_ANALYSIS.md
│   │   │   ├── 03_SOURCE_CODE_ANALYSIS.md
│   │   │   ├── 04_MODULE_ANALYSIS.md
│   │   │   ├── 05_REUSABLE_COMPONENTS.md
│   │   │   ├── 06_CORE_EXTRACTION_PLAN.md
│   │   │   ├── 07_DATABASE_MIGRATION_PLAN.md
│   │   │   ├── 08_EBP_PRODUCT_MAPPING.md
│   │   │   ├── 09_REFACTORING_PLAN.md
│   │   │   └── 10_IMPLEMENTATION_PROGRESS.md
│   │   │
│   │   ├── TAROMBO/
│   │   │   ├── 01_CURRENT_ANALYSIS.md
│   │   │   ├── 02_DATABASE_ANALYSIS.md
│   │   │   ├── 03_SOURCE_CODE_ANALYSIS.md
│   │   │   ├── 04_MODULE_ANALYSIS.md
│   │   │   ├── 05_REUSABLE_COMPONENTS.md
│   │   │   ├── 06_CORE_EXTRACTION_PLAN.md
│   │   │   ├── 07_DATABASE_MIGRATION_PLAN.md
│   │   │   ├── 08_EBP_PRODUCT_MAPPING.md
│   │   │   ├── 09_REFACTORING_PLAN.md
│   │   │   └── 10_IMPLEMENTATION_PROGRESS.md
│   │   │
│   │   └── KEWER/
│   │       ├── 01_CURRENT_ANALYSIS.md
│   │       ├── 02_DATABASE_ANALYSIS.md
│   │       ├── 03_SOURCE_CODE_ANALYSIS.md
│   │       ├── 04_MODULE_ANALYSIS.md
│   │       ├── 05_REUSABLE_COMPONENTS.md
│   │       ├── 06_CORE_EXTRACTION_PLAN.md
│   │       ├── 07_DATABASE_MIGRATION_PLAN.md
│   │       ├── 08_EBP_PRODUCT_MAPPING.md
│   │       ├── 09_REFACTORING_PLAN.md
│   │       └── 10_IMPLEMENTATION_PROGRESS.md
│   │
│   ├── 08_REPORT/
│   │   ├── ESAMF_SOFTWARE_ASSET_INVENTORY.md
│   │   ├── ESAMF_REUSABILITY_REPORT.md
│   │   ├── ESAMF_PLATFORM_READINESS.md
│   │   └── ESAMF_PRODUCT_MATURITY.md
│   │
│   ├── 09_TEMPLATES/
│   │   ├── REPOSITORY_AUDIT_TEMPLATE.md
│   │   ├── MODULE_AUDIT_TEMPLATE.md
│   │   ├── DATABASE_AUDIT_TEMPLATE.md
│   │   ├── MIGRATION_CHECKLIST.md
│   │   └── REFACTORING_CHECKLIST.md
│   │
│   └── 10_CASE_STUDIES/
│       └── RESTAURANT_MIGRATION.md
│
│
├── 12_ENTERPRISE_PRODUCT_FACTORY/
│
│   └── EBP_PRODUCT_FACTORY_GUIDE.md
│
│
├── 13_DOCUMENTATION/
│
│   ├── EBP_DOCUMENTATION_STANDARD.md
│   ├── EBP_DOCUMENTATION_GUIDE.md
│   └── EBP_DOCUMENTATION_TEMPLATES.md
│
│
├── 14_DEVOPS/
│
│   ├── EBP_DEVOPS_STANDARD.md
│   ├── EBP_CI_CD_GUIDE.md
│   ├── EBP_DEPLOYMENT_GUIDE.md
│   └── EBP_MONITORING_GUIDE.md
│
│
├── 15_OPERATIONS/
│
│   ├── EBP_OPERATIONS_STANDARD.md
│   ├── EBP_SUPPORT_GUIDE.md
│   ├── EBP_MAINTENANCE_GUIDE.md
│   └── EBP_INCIDENT_MANAGEMENT.md
│
│
└── PRODUCTS/


    │
    │
    ├── RESTAURANT_ERP/
    │
    │   ├── DOCUMENTATION/
    │   │
    │   │   ├── EBP_PRODUCT_RESTAURANT_CAFE_ERP.md
    │   │   ├── EBP_RESTAURANT_CAFE_BUSINESS_PROCESS.md
    │   │   ├── EBP_RESTAURANT_CAFE_MODULE_SPECIFICATION.md
    │   │   ├── EBP_RESTAURANT_CAFE_DATABASE_DESIGN.md
    │   │   ├── EBP_RESTAURANT_CAFE_ERD.md
    │   │   ├── EBP_RESTAURANT_CAFE_API_SPECIFICATION.md
    │   │   ├── EBP_RESTAURANT_CAFE_BACKEND_ARCHITECTURE.md
    │   │   └── EBP_RESTAURANT_CAFE_FRONTEND_ARCHITECTURE.md
    │   │
    │   ├── DATABASE/
    │   │
    │   │   └── EBP_RESTAURANT_CAFE_MYSQL_SCHEMA.sql
    │   │
    │   ├── BACKEND/
    │   │
    │   │   ├── config/
    │   │   ├── core/
    │   │   ├── modules/
    │   │   ├── routes/
    │   │   └── public/
    │   │
    │   ├── FRONTEND/
    │   │
    │   │   ├── assets/
    │   │   ├── components/
    │   │   ├── pages/
    │   │   └── modules/
    │   │
    │   └── DEPLOYMENT/
    │
    │
    ├── HOTEL_ERP/
    │
    │   ├── DOCUMENTATION/
    │   ├── DATABASE/
    │   ├── BACKEND/
    │   ├── FRONTEND/
    │   └── DEPLOYMENT/
    │
    │
    ├── PARKING_SYSTEM/
    │
    │   ├── DOCUMENTATION/
    │   ├── DATABASE/
    │   ├── BACKEND/
    │   ├── FRONTEND/
    │   └── DEPLOYMENT/
    │
    │
    ├── FARMING_ERP/
    │
    │   ├── DOCUMENTATION/
    │   ├── DATABASE/
    │   ├── BACKEND/
    │   ├── FRONTEND/
    │   └── DEPLOYMENT/
    │
    │
    └── LEGAL_SYSTEM/
    │
        ├── DOCUMENTATION/
        ├── DATABASE/
        ├── BACKEND/
        ├── FRONTEND/
        └── DEPLOYMENT/

```


---

# 4. Klasifikasi Inti vs Produk


## Platform Inti (Bersama)


Lokasi: `EBP_PLATFORM/00-07/`


Dokumen dan kode yang:


- Generik
- Dapat digunakan kembali
- Tidak spesifik industri
- Fondasi untuk semua produk


Contoh:


```
EBP_CORE_PRINCIPLES.md
EBP_SECURITY_ARCHITECTURE.md
EBP_DATABASE_STANDARD.md
EBP_ENGINE_ARCHITECTURE.md
Modul Autentikasi
Modul Izin
Modul Audit
Mesin Inventaris
Mesin Akuntansi
```


Digunakan oleh:


- Restaurant ERP
- Hotel ERP
- Parking System
- Farming ERP
- Legal System
- Semua produk masa depan


---

## Spesifik Produk


Lokasi: `EBP_PLATFORM/PRODUCTS/{PRODUCT_NAME}/`


Dokumen dan kode yang:


- Spesifik industri
- Spesifik proses bisnis
- Spesifik modul
- Spesifik UI


Contoh:


```
EBP_RESTAURANT_CAFE_BUSINESS_PROCESS.md
EBP_RESTAURANT_CAFE_MODULE_SPECIFICATION.md
Antarmuka POS Restaurant
Sistem Tampilan Dapur
Manajemen Menu
```


Digunakan oleh:


- Hanya Restaurant ERP


---

# 5. Strategi Repositori Git


## Organisasi Git


```
EBP-PLATFORM (Organisasi)


Repositori:


1. ebp-constitution
2. ebp-architecture
3. ebp-foundation
4. ebp-technical-standard
5. ebp-engine
6. ebp-product-management
7. ebp-core-code
8. ebp-shared-engines
9. ebp-restaurant-erp
10. ebp-hotel-erp
11. ebp-parking-system
12. ebp-farming-erp
13. ebp-legal-system
```


## Struktur Repositori


### Repositori Inti (Bersama)


```
ebp-constitution/
    ├── 00_CONSTITUTION/
    └── README.md


ebp-architecture/
    ├── 01_ARCHITECTURE/
    └── README.md


ebp-foundation/
    ├── 02_FOUNDATION/
    └── README.md


ebp-technical-standard/
    ├── 03_TECHNICAL_STANDARD/
    └── README.md


ebp-engine/
    ├── 04_ENGINE/
    └── README.md


ebp-product-management/
    ├── 05_PRODUCT_MANAGEMENT/
    └── README.md


ebp-core-code/
    ├── 06_CORE_CODE/
    └── README.md


ebp-shared-engines/
    ├── 07_SHARED_ENGINES/
    └── README.md
```


### Repositori Produk (Independen)


```
ebp-restaurant-erp/
    ├── DOCUMENTATION/
    ├── DATABASE/
    ├── BACKEND/
    ├── FRONTEND/
    ├── DEPLOYMENT/
    └── README.md


ebp-hotel-erp/
    ├── DOCUMENTATION/
    ├── DATABASE/
    ├── BACKEND/
    ├── FRONTEND/
    ├── DEPLOYMENT/
    └── README.md
```


---

# 6. Manajemen Dependensi


## Produk Bergantung pada Inti


Setiap repositori produk memiliki:


```
composer.json (PHP)
package.json (JavaScript)
requirements.txt (Python)
```


Dependensi inti:


```json
{
  "name": "ebp/restaurant-erp",
  "require": {
    "ebp/core-framework": "^1.0",
    "ebp/security": "^1.0",
    "ebp/inventory-engine": "^1.0",
    "ebp/accounting-engine": "^1.0"
  }
}
```


---

# 7. Strategi Versi


## Versi Semantik


Format: `MAJOR.MINOR.PATCH`


- **MAJOR**: Perubahan yang merusak
- **MINOR**: Fitur baru, kompatibel mundur
- **PATCH**: Perbaikan bug


## Versi Platform Inti


```
ebp-core-framework: 1.0.0
ebp-security: 1.2.3
ebp-inventory-engine: 2.1.0
```


## Versi Produk


```
ebp-restaurant-erp: 1.0.0
ebp-hotel-erp: 1.0.0
```


## Matriks Kompatibilitas


Dokumentasikan versi inti mana yang bekerja dengan versi produk mana.


---

# 8. Aturan Kontribusi Pengembang


## Perubahan Platform Inti


Siapa yang dapat mengubah:


- Hanya Tim Inti
- Memerlukan Tinjauan Arsitektur
- Memerlukan Analisis Dampak
- Memerlukan Pengujian lintas produk


Proses:


```

Usulan

↓

Tinjauan Arsitektur

↓

Analisis Dampak

↓

Implementasi

↓

Pengujian Lintas Produk

↓

Rilis

```


---

## Perubahan Produk


Siapa yang dapat mengubah:


- Tim Produk
- Mengikuti Standar Inti
- Tidak ada dampak pada produk lain


Proses:


```

Permintaan Fitur

↓

Tinjauan Produk

↓

Implementasi

↓

Pengujian Produk

↓

Rilis

```


---

# 9. Manajemen Rilis


## Rilis Platform Inti


Frekuensi:


- Kuartalan (Major)
- Bulanan (Minor)
- Mingguan (Patch)


Proses:


```

Kembangkan

↓

Uji

↓

Dokumentasikan

↓

Catatan Rilis

↓

Deploy

↓

Beritahu Tim Produk

```


---

## Rilis Produk


Frekuensi:


- Sesuai kebutuhan (Major)
- Bulanan (Minor)
- Mingguan (Patch)


Proses:


```

Kembangkan

↓

Uji

↓

Dokumentasikan

↓

Catatan Rilis

↓

Deploy

```


---

# 10. Standar Dokumentasi


## Dokumentasi Inti


Lokasi: `EBP_PLATFORM/00-07/`


Standar:


- Harus generik
- Harus dapat digunakan kembali
- Tidak boleh merujuk industri spesifik
- Harus menggunakan terminologi EBP


---

## Dokumentasi Produk


Lokasi: `EBP_PLATFORM/PRODUCTS/{PRODUCT_NAME}/DOCUMENTATION/`


Standar:


- Harus merujuk standar inti
- Harus spesifik industri
- Harus mengikuti template dokumentasi
- Harus menyertakan proses bisnis


---

# 11. Aturan Berbagi Kode


## Kode Inti


Lokasi: `EBP_PLATFORM/06_CORE_CODE/` dan `EBP_PLATFORM/07_SHARED_ENGINES/`


Aturan:


- Harus agnostik framework
- Harus diuji secara independen
- Harus memiliki dokumentasi komprehensif
- Harus mengikuti standar coding


---

## Kode Produk


Lokasi: `EBP_PLATFORM/PRODUCTS/{PRODUCT_NAME}/BACKEND/`


Aturan:


- Dapat menggunakan kode inti
- Dapat memperluas kode inti
- Tidak dapat memodifikasi kode inti secara langsung
- Harus mengikuti standar inti


---

# 12. Strategi Migrasi


## Struktur yang Ada


Saat ini:


```
/ENTERPRISE_BUSINESS_PLATFORM/
    ├── 00_EBP_MANIFESTO/
    ├── 01_ENTERPRISE_ARCHITECTURE/
    ├── 02_BUSINESS_FOUNDATION/
    ├── 03_TECHNICAL_STANDARD/
    ├── 04_BUSINESS_ENGINE/
    ├── 05_SECURITY_ARCHITECTURE/
    ├── 06_DEVOPS_ARCHITECTURE/
    ├── 07_PRODUCT_MANAGEMENT/
    ├── 08_PRODUCT_BLUEPRINT/
    ├── 09_DATABASE_DESIGN/
    ├── 10_API_DESIGN/
    └── 11_APPLICATION_ARCHITECTURE/
```


## Rencana Migrasi


Fase 1: Reorganisasi Inti


```
00_EBP_MANIFESTO → 00_CONSTITUTION
01_ENTERPRISE_ARCHITECTURE → 01_ARCHITECTURE
02_BUSINESS_FOUNDATION → 02_FOUNDATION
03_TECHNICAL_STANDARD → 03_TECHNICAL_STANDARD
04_BUSINESS_ENGINE → 04_ENGINE
05_SECURITY_ARCHITECTURE → 01_ARCHITECTURE
06_DEVOPS_ARCHITECTURE → 01_ARCHITECTURE
07_PRODUCT_MANAGEMENT → 05_PRODUCT_MANAGEMENT
```


Fase 2: Pindahkan Dokumentasi Produk


```
08_PRODUCT_BLUEPRINT → PRODUCTS/RESTAURANT_ERP/DOCUMENTATION
09_DATABASE_DESIGN → PRODUCTS/RESTAURANT_ERP/DOCUMENTATION
10_API_DESIGN → PRODUCTS/RESTAURANT_CAFE/DOCUMENTATION
11_APPLICATION_ARCHITECTURE → PRODUCTS/RESTAURANT_CAFE/DOCUMENTATION
```


Fase 3: Buat Folder Produk


```
PRODUCTS/
    ├── RESTAURANT_ERP/
    ├── HOTEL_ERP/
    ├── PARKING_SYSTEM/
    └── FARMING_ERP/
```


---

# 13. Klasifikasi Aset


## Aset Inti (IP Perusahaan)


- Konstitusi EBP
- Arsitektur EBP
- Standar EBP
- Mesin EBP
- Kode Inti


Nilai:


- Jangka panjang
- Dapat digunakan kembali
- Keunggulan kompetitif


---

## Aset Produk (IP Produk)


- Proses Bisnis
- Modul Industri
- UI Produk
- Database Produk


Nilai:


- Jangka menengah
- Spesifik industri
- Generator pendapatan


---

# 14. Struktur Tim


## Tim Inti


Tanggung jawab:


- Memelihara platform inti
- Mengembangkan mesin bersama
- Menetapkan standar
- Meninjau arsitektur


---

## Tim Produk


Tanggung jawab:


- Mengembangkan produk
- Mengimplementasikan proses bisnis
- Membangun fitur industri
- Dukungan pelanggan


---

# 15. Manajemen Pengetahuan


## Pengetahuan Inti


Lokasi: `EBP_PLATFORM/00-07/`


Akses:


- Semua tim
- Hanya baca untuk tim produk
- Hanya tulis untuk tim inti


---

## Pengetahuan Produk


Lokasi: `EBP_PLATFORM/PRODUCTS/{PRODUCT_NAME}/DOCUMENTATION/`


Akses:


- Tim produk
- Tim inti (hanya baca)


---

# 16. Jaminan Kualitas


## Pengujian Inti


- Unit test
- Integration test
- Uji kompatibilitas lintas produk
- Uji performa


---

## Pengujian Produk


- Unit test
- Integration test
- Uji proses bisnis
- Uji penerimaan pengguna


---

# 17. Strategi Deployment


## Deployment Inti


- Infrastruktur bersama
- Rilis berversi
- Kemampuan rollback
- Monitoring


---

## Deployment Produk


- Deployment independen
- Infrastruktur spesifik produk
- Rilis berversi
- Kemampuan rollback


---

# 18. Keamanan


## Keamanan Inti


- Autentikasi
- Otorisasi
- Enkripsi
- Logging audit
- Patch keamanan


---

## Keamanan Produk


- Validasi aturan bisnis
- Validasi input
- Pemeriksaan izin
- Keamanan spesifik produk


---

# 19. Dukungan dan Pemeliharaan


## Dukungan Inti


- Tanggung jawab tim inti
- SLA didefinisikan
- Prioritas berdasarkan dampak


---

## Dukungan Produk


- Tanggung jawab tim produk
- SLA didefinisikan
- Berhadapan dengan pelanggan


---

# 20. Strategi Pertumbuhan


## Pertumbuhan Platform


Tambahkan kemampuan inti baru:


```
06_CORE_CODE/
    ├── Integrasi AI/
    ├── Blockchain/
    └── IoT/
```


---

## Pertumbuhan Produk


Tambahkan produk baru:


```
PRODUCTS/
    ├── RESTAURANT_ERP/
    ├── HOTEL_ERP/
    ├── PARKING_SYSTEM/
    ├── FARMING_ERP/
    ├── LEGAL_SYSTEM/
    ├── HEALTHCARE_ERP/
    └── EDUCATION_ERP/
```


---

# 21. Kesimpulan


Struktur Direktori Platform EBP mendefinisikan:


```

SATU PLATFORM

+

BANYAK PRODUK

```


Struktur ini memungkinkan:


- Penggunaan kembali aset
- Standar konsisten
- Organisasi yang dapat diskalakan
- Pengembangan efisien
- Keberlanjutan jangka panjang


EBP tidak hanya membangun aplikasi.


EBP membangun platform perusahaan software.


---

# Akhir Dokumen


ID Dokumen:

EBP-PLATFORM-DIRECTORY-001


Versi:

1.0
