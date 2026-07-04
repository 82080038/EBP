# Enterprise Software Asset Management Framework (ESAMF)

# Software Asset Inventory

**Document ID:** ESAMF-ANALYSIS-ASSET-INVENTORY-001

**Version:** 1.0

**Status:** Official Master Registry

**Owner:** Enterprise Business Platform (EBP)

---

# 1. Purpose

Dokumen ini merupakan **master registry** seluruh aset perangkat lunak yang dimiliki organisasi.

Semua repository, library internal, database, layanan, API, template, modul, dan produk wajib tercatat di dalam inventaris ini.

Dokumen ini menjadi **Single Source of Truth** bagi seluruh proses:

* Software Asset Management
* Product Development
* Enterprise Architecture
* Repository Migration
* Platform Integration
* Product Factory

---

# 2. Scope

Software Asset Inventory mencakup:

* Git Repository
* Database
* API
* Backend Service
* Frontend Application
* Shared Library
* Engine
* Plugin
* Documentation
* DevOps Configuration
* Infrastructure Script

---

# 3. Asset Classification

Seluruh aset diklasifikasikan menjadi:

| Asset Type     | Deskripsi                                |
| -------------- | ---------------------------------------- |
| Platform       | Enterprise Business Platform (EBP)       |
| Product        | Aplikasi yang digunakan pengguna akhir   |
| Shared Engine  | Engine yang digunakan oleh banyak produk |
| Core Module    | Komponen inti platform                   |
| Library        | Utility atau helper                      |
| Documentation  | Dokumen teknis dan bisnis                |
| Database       | Skema, migrasi, seed                     |
| API            | REST API, webhook, integration           |
| Infrastructure | Docker, CI/CD, deployment                |

---

# 4. Business Domain Classification

Seluruh produk dikelompokkan berdasarkan domain bisnis.

| Business Domain | Product                   |
| --------------- | ------------------------- |
| Hospitality     | Restaurant ERP, Hotel ERP |
| Tourism         | MyWisata                  |
| Retail          | Panglong ERP              |
| Finance         | Saham                     |
| Education       | Pelajaran                 |
| Culture         | Tarombo Digital           |
| Public Service  | Visitor Management        |
| Agriculture     | Farming ERP               |
| Legal           | Legal Plus                |
| Parking         | Parking System            |

Domain baru dapat ditambahkan tanpa mengubah struktur EBP.

---

# 5. Software Asset Registry

## 5.1 Enterprise Business Platform

| Attribute       | Value                                 |
| --------------- | ------------------------------------- |
| Asset Name      | Enterprise Business Platform          |
| Asset Code      | EBP-CORE                              |
| Category        | Platform                              |
| Business Domain | Cross Domain                          |
| Status          | Active                                |
| Technology      | PHP Native, MySQL, JavaScript, jQuery |
| Multi Tenant    | Yes                                   |
| Shared          | Yes                                   |
| Reusable        | 100%                                  |
| Parent Asset    | -                                     |

---

## 5.2 Restaurant

| Attribute          | Value             |
| ------------------ | ----------------- |
| Repository         | restoran          |
| Product Name       | Restaurant ERP    |
| Category           | Product           |
| Business Domain    | Hospitality       |
| Target Platform    | EBP               |
| Current Status     | Legacy / Existing |
| Migration Status   | Planned           |
| Architecture       | To Be Analyzed    |
| Database           | To Be Analyzed    |
| Shared Components  | Pending Analysis  |
| Product Components | Pending Analysis  |
| Priority           | High              |

---

## 5.3 MyWisata

| Attribute        | Value             |
| ---------------- | ----------------- |
| Repository       | mywisata          |
| Product Name     | Tourism Platform  |
| Category         | Product           |
| Business Domain  | Tourism           |
| Target Platform  | EBP               |
| Current Status   | Legacy / Existing |
| Migration Status | Planned           |
| Priority         | High              |

---

## 5.4 Panglong

| Attribute        | Value             |
| ---------------- | ----------------- |
| Repository       | panglong          |
| Product Name     | Panglong ERP      |
| Category         | Product           |
| Business Domain  | Retail            |
| Target Platform  | EBP               |
| Current Status   | Legacy / Existing |
| Migration Status | Planned           |
| Priority         | High              |

---

## 5.5 Saham

| Attribute        | Value               |
| ---------------- | ------------------- |
| Repository       | saham               |
| Product Name     | Investment Platform |
| Category         | Product             |
| Business Domain  | Finance             |
| Target Platform  | EBP                 |
| Current Status   | Existing            |
| Migration Status | Planned             |
| Priority         | Medium              |

---

## 5.6 Pelajaran

| Attribute        | Value             |
| ---------------- | ----------------- |
| Repository       | pelajaran         |
| Product Name     | Learning Platform |
| Category         | Product           |
| Business Domain  | Education         |
| Target Platform  | EBP               |
| Current Status   | Existing          |
| Migration Status | Planned           |
| Priority         | Medium            |

---

## 5.7 Tarombo

| Attribute        | Value           |
| ---------------- | --------------- |
| Repository       | tarombo         |
| Product Name     | Tarombo Digital |
| Category         | Product         |
| Business Domain  | Culture         |
| Target Platform  | EBP             |
| Current Status   | Existing        |
| Migration Status | Planned         |
| Priority         | High            |

---

## 5.8 Kewer

| Attribute        | Value            |
| ---------------- | ---------------- |
| Repository       | kewer            |
| Product Name     | To Be Determined |
| Category         | Product          |
| Business Domain  | To Be Analyzed   |
| Target Platform  | EBP              |
| Current Status   | Existing         |
| Migration Status | Planned          |
| Priority         | Pending          |

---

# 6. Asset Lifecycle

Seluruh software asset mengikuti lifecycle berikut:

```
Discovery
    │
    ▼
Registration
    │
    ▼
Analysis
    │
    ▼
Classification
    │
    ▼
Reuse Assessment
    │
    ▼
Refactoring
    │
    ▼
Platform Integration
    │
    ▼
Maintenance
    │
    ▼
Retirement (Jika Diperlukan)
```

---

# 7. Reusability Matrix

Setiap repository akan dinilai menggunakan skala:

| Score | Keterangan                                     |
| ----- | ---------------------------------------------- |
| 5     | Langsung dapat dipindahkan ke EBP Core         |
| 4     | Reusable dengan sedikit refactoring            |
| 3     | Memerlukan refactoring sedang                  |
| 2     | Memerlukan redesign besar                      |
| 1     | Tidak direkomendasikan untuk digunakan kembali |

Penilaian dilakukan untuk:

* Authentication
* Authorization
* Configuration
* Workflow
* Reporting
* Notification
* Database Layer
* Business Engine
* API
* UI Component

---

# 8. Migration Status

| Status      | Deskripsi                |
| ----------- | ------------------------ |
| Registered  | Sudah terdaftar          |
| Analyzing   | Sedang dianalisis        |
| Classified  | Sudah diklasifikasikan   |
| Refactoring | Sedang direstrukturisasi |
| Integrated  | Sudah menjadi bagian EBP |
| Deprecated  | Tidak digunakan lagi     |
| Archived    | Diarsipkan               |

---

# 9. Governance Rules

1. Setiap repository baru wajib didaftarkan.
2. Setiap perubahan besar wajib memperbarui inventaris.
3. Tidak boleh ada software asset yang tidak memiliki pemilik.
4. Setiap asset harus memiliki status lifecycle.
5. Seluruh keputusan migrasi harus terdokumentasi melalui Architecture Decision Record (ADR).

---

# 10. Integration with Enterprise Business Platform

Software Asset Inventory menjadi referensi utama bagi:

* Enterprise Architecture
* Product Factory
* Development Rules
* Core Framework
* Shared Engines
* DevOps
* Documentation
* Testing
* Release Management

---

# 11. Future Expansion

Framework ini dirancang agar mampu mengelola:

* puluhan repository;
* ratusan modul;
* ribuan class;
* jutaan baris kode;
* berbagai bahasa pemrograman;
* berbagai platform deployment.

---

# 12. Final Statement

Seluruh perangkat lunak organisasi merupakan aset strategis.

Melalui ESAMF Software Asset Inventory, setiap aset dapat ditemukan, dipahami, dievaluasi, dikembangkan, dan dimanfaatkan kembali secara terukur sebagai bagian dari Enterprise Business Platform.

---

**End of Document**
