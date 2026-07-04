# MODUL 01 — KONSEP DAN ANALISIS SISTEM

> **Aplikasi:** Tour Guide Application  
> **Stack:** PHP 8.1+ Native (Simple MVC) + MySQL 8.0+ + Bootstrap 5.3 + jQuery 3.7 + OpenStreetMap/Leaflet  
> **Versi Dokumen:** 1.1  
> **Tanggal:** 2026-06-30  
> **Last Updated:** 2026-06-30

---

## 1. LATAR BELAKANG

Indonesia memiliki potensi pariwisata yang sangat besar dengan ribuan destinasi
wisata tersebar dari Sabang sampai Merauke. Namun, wisatawan—baik domestik
maupun mancanegara—sering menghadapi kendala:

- **Kesulitan menemukan tour guide** yang terpercaya dan berbahasa sesuai kebutuhan
- **Tidak ada sistem terpadu** untuk booking guide, tiket wisata, hotel, dan restoran
- **Informasi destinasi terpencar** di berbagai platform tidak terintegrasi
- **Tour guide independen** kesulitan memasarkan jasa mereka secara profesional

Aplikasi **Tour Guide Application** dirancang sebagai platform terpadu yang
menghubungkan wisatawan dengan tour guide, destinasi wisata, serta layanan
pendukung (hotel, restoran, tiket, event) dalam satu sistem.

---

## 2. TUJUAN SISTEM

| No | Tujuan | Indikator Keberhasilan |
|----|--------|----------------------|
| 1 | Menyediakan platform booking tour guide online | Wisatawan dapat memesan guide < 5 menit |
| 2 | Mengintegrasikan layanan pariwisata dalam satu aplikasi | Booking guide + tiket + hotel dalam 1 transaksi |
| 3 | Memberikan navigasi peta berbasis OpenStreetMap | Rute wisata tampil dengan akurasi GPS |
| 4 | Menyediakan audio guide multibahasa | Minimal 3 bahasa (ID, EN, JP) |
| 5 | Memberikan rekomendasi cerdas berbasis AI | Rekomendasi destinasi sesuai preferensi user |
| 6 | Mendukung UMKM kuliner & restoran lokal | UMKM dapat mendaftar dan menerima pesanan |

---

## 3. RUANG LINGKUP SISTEM

### 3.1 Yang Termasuk (In-Scope)

- **Manajemen Pengguna:** Registrasi, login, profil untuk 3 role (Admin, Wisatawan, Tour Guide)
- **Modul Tour Guide:** Profil guide, bahasa, spesialisasi, rating & review
- **Modul Booking & Transaksi:** Pemesanan guide, tiket, hotel, restoran dengan sistem pembayaran
- **Modul Peta & GPS:** Peta interaktif OpenStreetMap, rute wisata, tracking lokasi
- **Modul Tiket Wisata:** E-ticket untuk destinasi wisata
- **Modul Hotel & Homestay:** Pemesanan akomodasi
- **Modul Restoran & UMKM:** Pemesanan makanan, daftar kuliner lokal
- **Modul Event & Budaya:** Kalender event, festival budaya
- **Modul Audio Guide:** Audio multibahasa untuk destinasi
- **Modul AI Tour Guide:** Chatbot rekomendasi, itinerary generator
- **Modul Notifikasi:** Notifikasi push, email, in-app
- **Modul Report & Analytic:** Laporan transaksi, statistik kunjungan
- **Modul Keamanan:** Role-based access control, enkripsi, audit log

### 3.2 Yang Tidak Termasuk (Out-of-Scope)

- Aplikasi mobile native (Android/iOS) — fokus pada web responsive PWA-ready
- Payment gateway eksternal terintegrasi penuh — simulasi pembayaran di fase awal
- Sistem operasional internal destinasi (manajemen staf destinasi)
- Integrasi dengan sistem pemerintah / dinas pariwisata

---

## 4. ANALISIS STAKEHOLDER

| Stakeholder | Kepentingan | Peran dalam Sistem |
|-------------|-----------|-------------------|
| **Wisatawan** | Mencari & memesan layanan wisata dengan mudah | End user — konsumen utama |
| **Tour Guide** | Mendapatkan klien, menampilkan profil profesional | Provider — penyedia jasa guide |
| **Admin Sistem** | Mengelola seluruh data, transaksi, dan laporan | Super user — operasional sistem |
| **Pemilik Destinasi** | Menjual tiket, mempromosikan destinasi | Provider — penjual tiket |
| **Pemilik Hotel/Homestay** | Menerima pemesanan akomodasi | Provider — penyedia akomodasi |
| **Pemilik Restoran/UMKM** | Menerima pesanan makanan | Provider — penyedia kuliner |
| **Penyelenggara Event** | Mempromosikan event budaya | Provider — penyelenggara event |

---

## 5. ANALISIS PASAR & KOMPETITOR

| Kompetitor | Kelebihan | Kekurangan | Posisi Kami |
|------------|----------|------------|-------------|
| **Traveloka** | Brand besar, banyak layanan | Tidak fokus tour guide lokal | Fokus guide lokal + integrasi |
| **Klook** | Pengalaman wisata global | Kurang destinasi lokal Indonesia | Destinasi lokal Indonesia |
| **GetYourGuide** | Sistem booking matang | Tidak ada di Indonesia | Solusi lokal dengan bahasa Indonesia |
| **Google Maps** | Peta akurat | Biaya API tinggi | OpenStreetMap gratis, tanpa biaya |

### Keunggulan Kompetitif (Unique Selling Point)

1. **Fokus Tour Guide lokal** — bukan sekadar tiket, tapi manusia (guide) sebagai inti
2. **OpenStreetMap gratis** — tanpa biaya API Google Maps
3. **Audio Guide multibahasa** — inklusif untuk wisatawan mancanegara
4. **AI Tour Guide** — rekomendasi cerdas berbasis preferensi
5. **Integrasi UMKM** — mendukung ekonomi lokal kuliner
6. **PHP Native ringan** — mudah deploy di VPS sederhana, biaya operasional rendah

---

## 6. ANALISIS KEBUTUHAN TEKNIS

### 6.1 Kebutuhan Perangkat Keras (Server)

| Komponen | Minimum | Rekomendasi |
|----------|---------|-------------|
| CPU | 2 core | 4 core |
| RAM | 2 GB | 4 GB |
| Storage | 20 GB SSD | 50 GB SSD |
| Bandwidth | 1 TB/bulan | Unlimited |

### 6.2 Kebutuhan Perangkat Lunak (Server)

| Komponen | Versi | Keterangan |
|----------|-------|-----------|
| OS | Ubuntu 22.04 LTS | Linux VPS |
| Web Server | Apache 2.4 / Nginx 1.18 | Reverse proxy support |
| PHP | 8.1+ | Native, PSR-4 autoloading, type declarations |
| MySQL | 8.0+ | Database utama dengan InnoDB |
| Composer | 2.x | Dependency management (opsional) |
| phpMyAdmin | 5.2+ | Opsional, manajemen DB |
| Certbot | Latest | SSL certificate automation |

### 6.3 Kebutuhan Perangkat Lunak (Client)

| Komponen | Keterangan |
|----------|-----------|
| Browser | Chrome 90+, Firefox 88+, Safari 14+, Samsung Internet |
| JavaScript | ES6+ (jQuery 3.7+) |
| Resolusi | **Mobile-First Responsive** (mobile 360px — tablet 768px — desktop 1920px) |
| **Primary Device** | **Smartphone (Android/iOS) — 80%+ user base** |
| Framework | **Bootstrap 5.3 Mobile-First** — grid system, responsive utilities |
| Touch Support | Optimized untuk touch interaction (tap, swipe, pinch) |

### 6.4 Kebutuhan Library & Plugin Frontend

| Library | Versi | Fungsi |
|---------|-------|--------|
| Bootstrap | 5.3.x | CSS framework, responsive layout |
| jQuery | 3.7.x | DOM manipulation, AJAX |
| Leaflet | 1.9.x | Peta OpenStreetMap |
| Font Awesome | 6.x | Ikon UI |
| DataTables | 1.13.x | Tabel data dengan pagination |
| Select2 | 4.1.x | Dropdown dengan search |
| SweetAlert2 | 11.x | Notifikasi popup |
| Chart.js | 4.x | Grafik analitik |
| QRCode.js | 1.0.x | QR code generation (client-side) |
| Moment.js | 2.29.x | Date/time formatting |

---

## 7. ANALISIS RISIKO

| Risiko | Probabilitas | Dampak | Mitigasi |
|--------|-------------|--------|----------|
| Data GPS tidak akurat | Sedang | Sedang | Validasi koordinat, fallback ke pencarian nama |
| Spam registrasi guide palsu | Tinggi | Tinggi | Verifikasi dokumen, approval admin |
| Pembayaran gagal/tidak valid | Sedang | Tinggi | Sistem verifikasi transaksi, log pembayaran |
| Server down saat peak season | Sedang | Tinggi | Backup harian, monitoring uptime |
| Abuse API endpoint | Tinggi | Sedang | Rate limiting, API key, CSRF token |
| Data user bocor | Rendah | Sangat Tinggi | Enkripsi password (bcrypt), HTTPS, SQL injection prevention |

---

## 8. ASUMSI DAN KETERBATASAN

### Asumsi

- Pengguna memiliki akses internet stabil
- Tour guide memiliki smartphone dengan GPS
- Server VPS tersedia dengan akses root
- Domain dan SSL certificate sudah disiapkan
- Browser modern mendukung ES6+ dan PWA features
- Compliance dengan regulasi data protection (GDPR, UU PDP Indonesia)

### Keterbatasan

- Tidak ada aplikasi mobile native (PWA-ready untuk masa depan)
- Pembayaran menggunakan simulasi / transfer manual di fase awal
- Audio guide di-generate manual (tidak otomatis TTS di fase awal)
- AI Tour Guide menggunakan rule-based / sederhana (bukan LLM eksternal berbayar)
- Tidak ada real-time WebSocket (menggunakan polling untuk notifikasi)
- Tidak ada multi-language i18n system di fase awal (Bahasa Indonesia + English)

---

## 9. DEFINISI ISTILAH

| Istilah | Definisi |
|---------|----------|
| **Tour Guide** | Orang yang memandu wisatawan ke destinasi wisata |
| **Wisatawan** | Pengguna yang melakukan booking layanan wisata |
| **Destinasi** | Tempat wisata yang dapat dikunjungi |
| **Itinerary** | Rencana perjalanan wisata |
| **E-Ticket** | Tiket elektronik untuk masuk destinasi |
| **Homestay** | Akomodasi rumah warga untuk wisatawan |
| **UMKM** | Usaha Mikro Kecil Menengah (kuliner/kerajinan) |
| **OpenStreetMap** | Peta dunia gratis dan open-source |
| **Leaflet** | Library JavaScript untuk menampilkan peta |
| **AJAX** | Asynchronous JavaScript and XML — komunikasi data tanpa reload |
| **PWA** | Progressive Web App — web yang dapat diinstall seperti aplikasi |
| **PSR** | PHP Standard Recommendation — standar coding PHP |
| **Composer** | Dependency manager untuk PHP |
| **RBAC** | Role-Based Access Control — sistem hak akses berbasis role |
| **GDPR** | General Data Protection Regulation — regulasi data privasi Eropa |
| **PCI DSS** | Payment Card Industry Data Security Standard — standar keamanan pembayaran |
| **SSL/TLS** | Secure Sockets Layer / Transport Layer Security — enkripsi komunikasi |
| **CI/CD** | Continuous Integration / Continuous Deployment — otomatisasi build dan deploy |
| **Docker** | Platform containerization untuk aplikasi |
| **OPcache** | Opcode cache PHP untuk performance optimization |

---

## 10. REFERENSI

### Teknologi
- [OpenStreetMap](https://www.openstreetmap.org/) — Peta dunia gratis dan open-source
- [Leaflet.js](https://leafletjs.com/) — Library JavaScript untuk peta interaktif
- [Bootstrap 5](https://getbootstrap.com/) — CSS framework modern
- [jQuery](https://jquery.com/) — JavaScript library untuk DOM manipulation
- [PHP Manual](https://www.php.net/manual/en/) — Dokumentasi resmi PHP 8.1+
- [MySQL Documentation](https://dev.mysql.com/doc/) — Dokumentasi MySQL 8.0+

### Best Practices
- [PSR Standards](https://www.php-fig.org/psr/) — PHP Framework Interop Group standards
- [OWASP Top 10 2025](https://owasp.org/Top10/2025/) — Security standards terbaru
- [MySQL 8.0 Optimization](https://dev.mysql.com/doc/refman/8.0/en/optimization.html) — Performance tuning

### Compliance
- [GDPR](https://gdpr.eu/) — General Data Protection Regulation
- [UU PDP Indonesia](https://www Kominfo.go.id/) — Undang-Undang Perlindungan Data Pribadi

### Referensi Tambahan
- [PHP Best Practices 2024](https://scriptbinary.com/php/best-practice-for-structuring-php-projects-in-2024)
- [Leaflet Best Practices](https://leafletjs.com/examples.html)
- [MySQL Indexing Strategies](https://cloudastra.co/blogs/indexing-strategies-mysql-8-0)

---

> **Modul Selanjutnya:** `02_SRS_REQUIREMENT_SYSTEM.md` — Spesifikasi kebutuhan perangkat lunak secara detail
