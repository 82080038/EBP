# MODUL 02 — SOFTWARE REQUIREMENT SPECIFICATION (SRS)

> **Aplikasi:** Tour Guide Application  
> **Versi Dokumen:** 1.1  
> **Tanggal:** 2026-06-30  
> **Last Updated:** 2026-06-30

---

## 1. PENDAHULUAN

### 1.1 Tujuan Dokumen
Mendefinisikan kebutuhan fungsional dan non-fungsional Tour Guide Application
sebagai acuan desain, implementasi, dan testing.

### 1.2 Lingkup
3 role pengguna (Admin, Wisatawan, Tour Guide) dengan 13 modul fungsional:
Tour Guide, Map/GPS, Booking, Tiket, Hotel, Restoran, Event, Audio Guide,
AI Guide, Notifikasi, Report, Security, dan User Management.

---

## 2. KEBUTUHAN FUNGSIONAL

### 2.1 Modul Autentikasi & Manajemen Pengguna

| ID | Kebutuhan | Prioritas |
|----|-----------|-----------|
| FR-AUTH-01 | Registrasi untuk Wisatawan dan Tour Guide | Tinggi |
| FR-AUTH-02 | Login dengan email & password | Tinggi |
| FR-AUTH-03 | Logout | Tinggi |
| FR-AUTH-04 | Lupa password via email | Sedang |
| FR-AUTH-05 | Password di-hash (bcrypt) | Tinggi |
| FR-AUTH-06 | Verifikasi email saat registrasi | Sedang |
| FR-AUTH-07 | Manajemen profil untuk setiap role | Tinggi |
| FR-AUTH-08 | Admin CRUD pengguna | Tinggi |
| FR-AUTH-09 | Tour Guide upload dokumen verifikasi | Tinggi |
| FR-AUTH-10 | Admin approve/reject registrasi Tour Guide | Tinggi |

### 2.2 Modul Tour Guide

| ID | Kebutuhan | Prioritas |
|----|-----------|-----------|
| FR-TG-01 | Tour Guide buat profil (bio, foto, bahasa, spesialisasi) | Tinggi |
| FR-TG-02 | Tour Guide atur tarif per jam/hari/paket | Tinggi |
| FR-TG-03 | Tour Guide atur ketersediaan jadwal | Tinggi |
| FR-TG-04 | Wisatawan cari guide (lokasi, bahasa, rating) | Tinggi |
| FR-TG-05 | Wisatawan lihat detail profil guide | Tinggi |
| FR-TG-06 | Wisatawan beri rating & review | Tinggi |
| FR-TG-07 | Ranking guide berdasarkan rating | Sedang |
| FR-TG-08 | Tour Guide lihat daftar booking | Tinggi |
| FR-TG-09 | Tour Guide accept/reject booking | Tinggi |
| FR-TG-10 | Admin kelola kategori spesialisasi | Sedang |

### 2.3 Modul Map & GPS (OpenStreetMap)

| ID | Kebutuhan | Prioritas |
|----|-----------|-----------|
| FR-MAP-01 | Peta interaktif OpenStreetMap + Leaflet | Tinggi |
| FR-MAP-02 | Marker destinasi wisata di peta | Tinggi |
| FR-MAP-03 | Cari destinasi (nama/kategori) | Tinggi |
| FR-MAP-04 | Rute dari lokasi user ke destinasi | Sedang |
| FR-MAP-05 | Popup info saat marker diklik | Tinggi |
| FR-MAP-06 | Deteksi lokasi GPS pengguna | Sedang |
| FR-MAP-07 | Admin tambah/edit/move marker | Tinggi |
| FR-MAP-08 | Cluster marker saat zoom out | Sedang |
| FR-MAP-09 | Simpan koordinat (lat, lng) per destinasi | Tinggi |
| FR-MAP-10 | Itinerary route multiple destinasi | Sedang |

### 2.4 Modul Booking & Transaksi

| ID | Kebutuhan | Prioritas |
|----|-----------|-----------|
| FR-BOOK-01 | Wisatawan booking tour guide | Tinggi |
| FR-BOOK-02 | Pilih tanggal, durasi, jumlah peserta | Tinggi |
| FR-BOOK-03 | Hitung total biaya otomatis | Tinggi |
| FR-BOOK-04 | Kode booking unik | Tinggi |
| FR-BOOK-05 | Metode pembayaran (transfer/simulasi) | Tinggi |
| FR-BOOK-06 | Status booking (pending/confirmed/completed/cancelled) | Tinggi |
| FR-BOOK-07 | Riwayat booking wisatawan | Tinggi |
| FR-BOOK-08 | Tour Guide accept/reject booking | Tinggi |
| FR-BOOK-09 | Notifikasi saat status berubah | Tinggi |
| FR-BOOK-10 | Admin kelola semua transaksi | Tinggi |
| FR-BOOK-11 | Pembatalan dengan kebijakan refund | Sedang |
| FR-BOOK-12 | Invoice/bukti transaksi (PDF-ready) | Sedang |

### 2.5 Modul Tiket Wisata

| ID | Kebutuhan | Prioritas |
|----|-----------|-----------|
| FR-TKT-01 | Admin CRUD destinasi (nama, deskripsi, harga, foto) | Tinggi |
| FR-TKT-02 | Wisatawan beli tiket destinasi | Tinggi |
| FR-TKT-03 | E-ticket dengan QR code | Tinggi |
| FR-TKT-04 | Kategori destinasi (alam, budaya, sejarah) | Tinggi |
| FR-TKT-05 | Wisatawan lihat tiket dimiliki | Tinggi |
| FR-TKT-06 | Verifikasi tiket (scan QR/input kode) | Sedang |
| FR-TKT-07 | Kuota tiket harian per destinasi | Sedang |
| FR-TKT-08 | Tiket terlaris / rekomendasi | Sedang |

### 2.6 Modul Hotel & Homestay

| ID | Kebutuhan | Prioritas |
|----|-----------|-----------|
| FR-HTL-01 | Pemilik daftar hotel/homestay | Tinggi |
| FR-HTL-02 | Wisatawan cari akomodasi (lokasi & tanggal) | Tinggi |
| FR-HTL-03 | Booking kamar (check-in/out, jumlah) | Tinggi |
| FR-HTL-04 | Hitung biaya berdasarkan malam | Tinggi |
| FR-HTL-05 | Ketersediaan kamar real-time | Sedang |
| FR-HTL-06 | Pemilik kelola kamar & ketersediaan | Tinggi |
| FR-HTL-07 | Review akomodasi | Sedang |
| FR-HTL-08 | Admin approve/reject akomodasi | Tinggi |

### 2.7 Modul Restoran & UMKM

| ID | Kebutuhan | Prioritas |
|----|-----------|-----------|
| FR-RST-01 | Pemilik daftar restoran/UMKM | Tinggi |
| FR-RST-02 | Wisatawan cari restoran (lokasi & kuliner) | Tinggi |
| FR-RST-03 | Wisatawan lihat menu & pesan | Tinggi |
| FR-RST-04 | Kode pesanan untuk pickup/delivery | Sedang |
| FR-RST-05 | Pemilik kelola menu & harga | Tinggi |
| FR-RST-06 | Review restoran | Sedang |
| FR-RST-07 | UMKM terdekat dengan lokasi wisatawan | Sedang |
| FR-RST-08 | Admin approve/reject restoran | Tinggi |

### 2.8 Modul Event & Budaya

| ID | Kebutuhan | Prioritas |
|----|-----------|-----------|
| FR-EVT-01 | Admin/penyelenggara CRUD event | Tinggi |
| FR-EVT-02 | Kalender event | Tinggi |
| FR-EVT-03 | Wisatawan lihat detail & daftar event | Tinggi |
| FR-EVT-04 | Event terdekat berdasarkan tanggal | Sedang |
| FR-EVT-05 | Event di sekitar lokasi (peta) | Sedang |
| FR-EVT-06 | Notifikasi pengingat event | Sedang |
| FR-EVT-07 | Kategori event (festival, seni, kuliner) | Sedang |

### 2.9 Modul Audio Guide

| ID | Kebutuhan | Prioritas |
|----|-----------|-----------|
| FR-AUD-01 | Admin upload audio per destinasi (multi-bahasa) | Tinggi |
| FR-AUD-02 | Wisatawan putar audio guide | Tinggi |
| FR-AUD-03 | Format MP3, OGG | Tinggi |
| FR-AUD-04 | Kontrol play, pause, stop, volume | Tinggi |
| FR-AUD-05 | Pilih bahasa audio | Tinggi |
| FR-AUD-06 | Transkrip teks (opsional) | Sedang |
| FR-AUD-07 | Admin kelola bahasa tersedia | Sedang |
| FR-AUD-08 | Statistik pemutaran audio | Rendah |

### 2.10 Modul AI Tour Guide

| ID | Kebutuhan | Prioritas |
|----|-----------|-----------|
| FR-AI-01 | Chatbot rekomendasi destinasi | Tinggi |
| FR-AI-02 | Generate itinerary berdasarkan preferensi | Sedang |
| FR-AI-03 | Rekomendasi dari riwayat booking | Sedang |
| FR-AI-04 | Rekomendasi dari lokasi GPS | Sedang |
| FR-AI-05 | FAQ destinasi | Sedang |
| FR-AI-06 | Belajar preferensi dari rating & review | Rendah |
| FR-AI-07 | Admin kelola knowledge base | Sedang |

### 2.11 Modul Notifikasi

| ID | Kebutuhan | Prioritas |
|----|-----------|-----------|
| FR-NOT-01 | Notifikasi in-app | Tinggi |
| FR-NOT-02 | Notifikasi email konfirmasi booking | Tinggi |
| FR-NOT-03 | Notifikasi status booking berubah | Tinggi |
| FR-NOT-04 | Notifikasi pengingat event/tour | Sedang |
| FR-NOT-05 | Riwayat notifikasi | Tinggi |
| FR-NOT-06 | Preferensi notifikasi | Sedang |
| FR-NOT-07 | Badge notifikasi belum dibaca | Tinggi |
| FR-NOT-08 | Admin broadcast notifikasi | Sedang |

### 2.12 Modul Report & Analytic

| ID | Kebutuhan | Prioritas |
|----|-----------|-----------|
| FR-RPT-01 | Dashboard ringkasan (user, booking, revenue) | Tinggi |
| FR-RPT-02 | Laporan transaksi (filter tanggal) | Tinggi |
| FR-RPT-03 | Statistik destinasi terlaris | Tinggi |
| FR-RPT-04 | Statistik tour guide terbaik | Sedang |
| FR-RPT-05 | Grafik tren booking (Chart.js) | Sedang |
| FR-RPT-06 | Export laporan CSV/Excel | Sedang |
| FR-RPT-07 | Laporan pendapatan Tour Guide | Sedang |
| FR-RPT-08 | Demografi wisatawan | Rendah |
| FR-RPT-09 | Real-time analytics dashboard | Sedang |
| FR-RPT-10 | Custom report builder | Rendah |

---

## 3. KEBUTUHAN NON-FUNGSIONAL

### 3.1 Performa

| ID | Kebutuhan | Target |
|----|-----------|--------|
| NFR-PERF-01 | Waktu respons halaman | < 2 detik |
| NFR-PERF-02 | Waktu respons API AJAX | < 500ms |
| NFR-PERF-03 | Waktu query database | < 100ms |
| NFR-PERF-04 | Peta lazy loading marker | < 3 detik |
| NFR-PERF-05 | 100 concurrent users | Tanpa degradasi |
| NFR-PERF-06 | OPcache enabled di production | - |
| NFR-PERF-07 | Gzip compression enabled | - |
| NFR-PERF-08 | Database index optimization | Three-Star System |
| NFR-PERF-09 | Image optimization (WebP, lazy load) | - |
| NFR-PERF-10 | CDN untuk static assets (opsional) | - |

### 3.2 Keamanan

| ID | Kebutuhan |
|----|-----------|
| NFR-SEC-01 | Password hash bcrypt (`password_hash()`) |
| NFR-SEC-02 | Validasi input server-side & client-side |
| NFR-SEC-03 | PDO prepared statements (anti SQL injection) |
| NFR-SEC-04 | `htmlspecialchars()` (anti XSS) |
| NFR-SEC-05 | CSRF token untuk semua form |
| NFR-SEC-06 | Session timeout 30 menit idle |
| NFR-SEC-07 | HTTPS/SSL wajib di production |
| NFR-SEC-08 | RBAC untuk setiap endpoint |
| NFR-SEC-09 | Audit log untuk aksi sensitif |
| NFR-SEC-10 | Rate limiting untuk API (60 req/menit) |
| NFR-SEC-11 | Security headers (CSP, HSTS, X-Frame-Options) |
| NFR-SEC-12 | OWASP Top 10 2025 compliance |
| NFR-SEC-13 | File upload validation (MIME, size, random filename) |
| NFR-SEC-14 | Session cookie: HttpOnly, Secure, SameSite |
| NFR-SEC-15 | Environment variable untuk sensitive config (.env) |

### 3.3 Reliabilitas

| ID | Kebutuhan |
|----|-----------|
| NFR-REL-01 | Uptime target 99.5% |
| NFR-REL-02 | Database backup harian otomatis |
| NFR-REL-03 | Error handling dengan log file |
| NFR-REL-04 | Graceful degradation saat API peta gagal |

### 3.4 Usability

| ID | Kebutuhan |
|----|-----------|
| NFR-USA-01 | **UI responsive mobile-first (Bootstrap 5.3)** — **Primary target: smartphone** |
| NFR-USA-02 | Bahasa Indonesia (default) + English |
| NFR-USA-03 | Navigasi dengan breadcrumb |
| NFR-USA-04 | Loading indicator untuk AJAX |
| NFR-USA-05 | Validasi form dengan pesan error jelas |
| NFR-USA-06 | WCAG 2.1 Level AA compliance (accessibility) |
| NFR-USA-07 | PWA-ready (manifest.json, service worker) — **Installable di smartphone** |
| NFR-USA-08 | Skeleton loading untuk async content |
| NFR-USA-09 | Infinite scroll untuk list (opsional) |
| NFR-USA-10 | Dark mode support (system-aware) |
| NFR-USA-11 | **Touch-optimized UI** — tap targets minimum 44x44px |
| NFR-USA-12 | **Mobile-specific gestures** — swipe, pinch-to-zoom untuk peta |
| NFR-USA-13 | **Offline-first capabilities** — cache critical resources |
| NFR-USA-14 | **Responsive images** — srcset untuk berbagai resolusi device |
| NFR-USA-15 | **Viewport meta tag** — proper scaling untuk mobile browsers |

### 3.5 Maintainability

| ID | Kebutuhan |
|----|-----------|
| NFR-MAINT-01 | Pola MVC sederhana |
| NFR-MAINT-02 | PSR-4 autoloading |
| NFR-MAINT-03 | Konfigurasi terpisah dari kode (.env) |
| NFR-MAINT-04 | Dokumentasi inline untuk fungsi kompleks |
| NFR-MAINT-05 | Version control dengan Git |
| NFR-MAINT-06 | PSR-12 coding standard compliance |
| NFR-MAINT-07 | Type declarations (PHP 8.1+) |
| NFR-MAINT-08 | Service layer pattern untuk business logic |
| NFR-MAINT-09 | Repository pattern untuk data access |

### 3.6 Portability

| ID | Kebutuhan |
|----|-----------|
| NFR-PORT-01 | Deploy di Apache dan Nginx |
| NFR-PORT-02 | Deploy di shared hosting dan VPS |
| NFR-PORT-03 | Tidak bergantung ekstensi PHP non-standard |
| NFR-PORT-04 | Docker containerization support (opsional) |
| NFR-PORT-05 | PHP 8.1+ compatibility |

---

## 4. USE CASE DIAGRAM (Tekstual)

### 4.1 Actor: Wisatawan

```
Wisatawan
├── Registrasi / Login
├── Kelola Profil
├── Cari Tour Guide
├── Booking Tour Guide
├── Beli Tiket Wisata
├── Booking Hotel/Homestay
├── Pesan Makanan Restoran
├── Lihat Event & Daftar
├── Putar Audio Guide
├── Chat dengan AI Guide
├── Beri Rating & Review
├── Lihat Notifikasi
└── Lihat Riwayat Transaksi
```

### 4.2 Actor: Tour Guide

```
Tour Guide
├── Registrasi & Upload Dokumen
├── Kelola Profil & Tarif
├── Atur Ketersediaan Jadwal
├── Terima/Tolak Booking
├── Lihat Jadwal Tour
├── Lihat Pendapatan
└── Lihat Notifikasi
```

### 4.3 Actor: Admin

```
Admin
├── Kelola Pengguna (CRUD)
├── Approve Tour Guide
├── Kelola Destinasi Wisata
├── Kelola Kategori
├── Kelola Event
├── Kelola Audio Guide
├── Kelola Knowledge Base AI
├── Lihat Semua Transaksi
├── Lihat Laporan & Analitik
├── Backup Database
├── Kelola Keamanan & Audit Log
└── Broadcast Notifikasi
```

---

## 5. MATRIKS KEBUTUHAN vs MODUL

| Modul | FR Count | Prioritas Tinggi | Prioritas Sedang | Prioritas Rendah |
|-------|----------|-----------------|-----------------|-----------------|
| Autentikasi | 10 | 7 | 2 | 0 |
| Tour Guide | 10 | 8 | 2 | 0 |
| Map & GPS | 10 | 5 | 4 | 0 |
| Booking | 12 | 8 | 4 | 0 |
| Tiket | 8 | 5 | 3 | 0 |
| Hotel | 8 | 5 | 2 | 0 |
| Restoran | 8 | 5 | 2 | 0 |
| Event | 7 | 3 | 4 | 0 |
| Audio Guide | 8 | 5 | 2 | 1 |
| AI Guide | 7 | 1 | 5 | 1 |
| Notifikasi | 8 | 4 | 3 | 0 |
| Report | 8 | 3 | 4 | 1 |
| **TOTAL** | **104** | **59** | **37** | **3** |

---

## 6. PRIORITAS PENGEMBANGAN (MoSCoW)

### Must Have (Fase 1 — MVP)
- Autentikasi & Manajemen Pengguna
- Modul Tour Guide (profil, cari, booking dasar)
- Modul Map & GPS (peta, marker, popup)
- Modul Booking & Transaksi (dasar)
- Modul Tiket Wisata (CRUD + beli)

### Should Have (Fase 2)
- Modul Hotel & Homestay
- Modul Restoran & UMKM
- Modul Event & Budaya
- Modul Notifikasi (in-app + email)
- Modul Report & Analytic (dashboard)

### Could Have (Fase 3)
- Modul Audio Guide
- Modul AI Tour Guide (chatbot dasar)
- Rating & Review lengkap
- Export laporan

### Won't Have (Fase 4 — Future)
- Mobile native app
- Payment gateway integrasi penuh
- AI berbasis LLM eksternal
- Integrasi sistem pemerintah

---

## 7. KEBUTUHAN COMPLIANCE & PRIVACY

### 7.1 GDPR Compliance (General Data Protection Regulation)

| ID | Kebutuhan | Prioritas |
|----|-----------|-----------|
| C-GDPR-01 | Consent cookie banner | Tinggi |
| C-GDPR-02 | Right to data access (user dapat download data) | Tinggi |
| C-GDPR-03 | Right to data deletion (account deletion) | Tinggi |
| C-GDPR-04 | Data retention policy (auto-delete inactive accounts) | Sedang |
| C-GDPR-05 | Privacy policy page | Tinggi |
| C-GDPR-06 | Data breach notification procedure | Sedang |

### 7.2 UU PDP Indonesia (Perlindungan Data Pribadi)

| ID | Kebutuhan | Prioritas |
|----|-----------|-----------|
| C-PDP-01 | Explicit consent untuk data collection | Tinggi |
| C-PDP-02 | Data encryption at rest dan in transit | Tinggi |
| C-PDP-03 | Access control untuk data sensitif | Tinggi |
| C-PDP-04 | Audit trail untuk data access | Sedang |
| C-PDP-05 | Local data storage compliance | Sedang |

### 7.3 PCI DSS Compliance (Payment Card Industry)

| ID | Kebutuhan | Prioritas |
|----|-----------|-----------|
| C-PCI-01 | Tidak simpan data kartu kredit penuh | Tinggi |
| C-PCI-02 | Payment gateway terintegrasi (Midtrans/Xendit) | Tinggi |
| C-PCI-03 | SSL/TLS untuk semua payment pages | Tinggi |
| C-PCI-04 | Fraud detection system | Sedang |

---

## 8. REFERENSI

### Standards
- [PSR Standards](https://www.php-fig.org/psr/) — PHP Framework Interop Group
- [OWASP Top 10 2025](https://owasp.org/Top10/2025/) — Web Application Security
- [WCAG 2.1](https://www.w3.org/WAI/WCAG21/quickref/) — Web Content Accessibility Guidelines
- [GDPR](https://gdpr.eu/) — General Data Protection Regulation

### Best Practices
- [PHP Best Practices 2024](https://scriptbinary.com/php/best-practice-for-structuring-php-projects-in-2024)
- [MySQL 8.0 Optimization](https://dev.mysql.com/doc/refman/8.0/en/optimization.html)
- [PWA Best Practices](https://web.dev/progressive-web-apps/)

---

> **Modul Selanjutnya:** `03_DESAIN_ARSITEKTUR_APLIKASI.md` — Desain arsitektur sistem secara teknis
