# MODUL 31 — KAMUS ISTILAH & GLOSARIUM

> **Aplikasi:** Tour Guide Application  
> **Versi:** 1.0  
> **Tanggal:** 2026-06-30

---

## 1. RINGKASAN

Dokumen ini berisi kamus istilah dan glosarium yang digunakan di seluruh
dokumentasi Tour Guide Application. Ditujukan untuk developer, tester, dan
stakeholder yang mungkin belum familiar dengan terminologi pariwisata
maupun teknis.

---

## 2. ISTILAH BISNIS & PARIWISATA

| Istilah | Definisi |
|---------|----------|
| **Wisatawan** | Pengguna aplikasi yang berperan sebagai konsumen / turis. Role: `wisatawan`. |
| **Tour Guide** | Pemandu wisata profesional yang menawarkan jasa tour. Role: `tour_guide`. |
| **Admin** | Administrator sistem yang mengelola seluruh aplikasi. Role: `admin`. |
| **UMKM** | Usaha Mikro, Kecil, dan Menengah. Dalam konteks ini: restoran, warung, kafe, street food. |
| **Homestay** | Akomodasi yang dikelola oleh penduduk lokal, biasanya lebih sederhana dari hotel. |
| **Destinasi** | Lokasi wisata yang dapat dikunjungi (pantai, gunung, museum, taman nasional, dll). |
| **E-Ticket** | Tiket elektronik dengan QR code yang dibeli melalui aplikasi. |
| **Booking** | Pemesanan jasa tour guide untuk tanggal dan durasi tertentu. |
| **Itinerary** | Rencana perjalanan / jadwal kunjungan destinasi selama beberapa hari. |
| **Specialization** | Area keahlian tour guide (contoh: adventure, cultural, photography, culinary). |
| **Daily Rate** | Tarif harian tour guide (untuk booking >= 8 jam). |
| **Hourly Rate** | Tarif per jam tour guide (untuk booking < 8 jam). |
| **Kuota Harian** | Batas jumlah tiket yang dapat dijual per destinasi per hari. |
| **Check-in** | Proses masuk akomodasi (hotel/homestay). |
| **Check-out** | Proses keluar akomodasi. |
| **Festival** | Event budaya yang dirayakan secara berkala. |
| **Audio Guide** | Panduan audio multibahasa untuk destinasi wisata. |
| **Transkrip** | Teks sinkron dengan audio guide untuk accessibility. |
| **Pemilik** | User yang mendaftarkan hotel/restoran sebagai owner. |
| **Owner** | Sama dengan Pemilik. User dengan role `wisatawan` yang juga mendaftarkan usaha. |

---

## 3. ISTILAH TEKNIS — BACKEND

| Istilah | Definisi |
|---------|----------|
| **PHP Native** | PHP murni tanpa framework (Laravel, CodeIgniter, dll). Hanya menggunakan custom MVC sederhana. |
| **MVC** | Model-View-Controller. Pola arsitektur yang memisahkan data (Model), tampilan (View), dan logika (Controller). |
| **PDO** | PHP Data Objects. Extension PHP untuk akses database dengan prepared statements (mencegah SQL injection). |
| **Prepared Statement** | Query SQL dengan parameter binding (`:param`) yang mencegah SQL injection. |
| **Front Controller** | Pola di mana semua request melewati satu file entry point (`index.php`) yang kemudian melakukan routing. |
| **Autoload** | Mekanisme PHP untuk otomatis load class file saat class dipanggil, menggunakan `spl_autoload_register`. |
| **Middleware** | Layer yang berjalan sebelum controller, digunakan untuk auth check dan RBAC. |
| **Singleton** | Pola desain yang memastikan hanya ada satu instance dari class (contoh: Database connection). |
| **CSRF Token** | Cross-Site Request Forgery Token. Token unik per session yang disertakan di setiap form POST untuk mencegah serangan CSRF. |
| **RBAC** | Role-Based Access Control. Sistem hak akses berdasarkan role pengguna (admin, wisatawan, tour_guide). |
| **bcrypt** | Algoritma hash password yang aman, digunakan via `password_hash()` dengan `PASSWORD_BCRYPT`. |
| **Session** | Data pengguna yang disimpan di server (`$_SESSION`), berisi user_id, role, name, csrf_token. |
| **Session Timeout** | Mekanisme auto-logout setelah 30 menit tidak ada aktivitas. |
| **Audit Log** | Catatan aksi penting user (create, update, delete, login, backup) untuk keperluan security audit. |
| **Rate Limiting** | Pembatasan jumlah request API per user (60 request/menit) untuk mencegah abuse. |
| **Migration SQL** | File SQL untuk membuat/memperbarui struktur database (`database/migration.sql`). |
| **Seed Data** | Data awal yang diinsert ke database untuk testing (admin default, kategori destinasi, settings). |
| **JSON Response** | Format response API: `{ status, message, data, meta }`. |
| **AJAX** | Asynchronous JavaScript and XML. Dalam aplikasi ini, menggunakan jQuery `$.ajax()` untuk komunikasi dengan API tanpa reload halaman. |

---

## 4. ISTILAH TEKNIS — FRONTEND

| Istilah | Definisi |
|---------|----------|
| **Bootstrap 5** | CSS framework untuk responsive layout, komponen UI (card, modal, table, navbar, dll). |
| **jQuery** | JavaScript library untuk DOM manipulation, AJAX, event handling. |
| **Leaflet** | JavaScript library untuk peta interaktif yang open-source dan ringan. |
| **OpenStreetMap** | Peta dunia gratis dan open-source, digunakan sebagai tile layer untuk Leaflet. |
| **Marker** | Pin/icon di peta yang menandai lokasi destinasi. |
| **Marker Cluster** | Pengelompokan marker yang berdekatan saat zoom out untuk menghindari tumpang tindih. |
| **Geolocation** | API browser untuk mendapatkan lokasi GPS pengguna (`navigator.geolocation`). |
| **Haversine** | Formula matematika untuk menghitung jarak antara dua titik koordinat (lat/lng). |
| **SweetAlert2** | Library JavaScript untuk popup alert yang lebih menarik dari `alert()` bawaan browser. |
| **DataTables** | Plugin jQuery untuk tabel interaktif dengan search, sort, dan pagination. |
| **Select2** | Plugin jQuery untuk dropdown/select yang lebih baik dengan search. |
| **Chart.js** | Library JavaScript untuk membuat grafik (bar, line, pie, doughnut). |
| **Font Awesome** | Library icon (SVG/font) untuk UI. |
| **QR Code** | Quick Response Code. Kode 2D yang berisi data (ticket code), dapat di-scan untuk verifikasi. |
| **Lazy Loading** | Memuat gambar hanya saat visible di viewport untuk menghemat bandwidth. |
| **Responsive** | Layout yang menyesuaikan dengan ukuran layar (mobile 360px - desktop 1920px). |
| **Template Literal** | JavaScript string dengan backtick (\`) yang mendukung interpolation `${variable}`. |

---

## 5. ISTILAH TEKNIS — DATABASE

| Istilah | Definisi |
|---------|----------|
| **ERD** | Entity Relationship Diagram. Diagram yang menunjukkan struktur tabel dan relasi antar tabel. |
| **DDL** | Data Definition Language. Perintah SQL untuk membuat struktur (`CREATE TABLE`, `ALTER TABLE`). |
| **DML** | Data Manipulation Language. Perintah SQL untuk data (`INSERT`, `UPDATE`, `DELETE`, `SELECT`). |
| **Foreign Key** | Kolom yang mereferensi primary key tabel lain untuk membuat relasi. |
| **Primary Key** | Kolom unik yang mengidentifikasi setiap baris (biasanya `id BIGINT UNSIGNED AUTO_INCREMENT`). |
| **Index** | Struktur data untuk mempercepat query (contoh: `INDEX idx_email (email)`). |
| **utf8mb4** | Charset MySQL yang mendukung emoji dan karakter multibyte penuh. |
| **InnoDB** | Storage engine MySQL yang mendukung transaksi dan foreign key. |
| **Transaction** | Sekumpulan operasi DB yang dijalankan sebagai satu unit (all-or-nothing: commit/rollback). |
| **Soft Delete** | Menghapus data dengan set `is_active=0` alih-alih `DELETE` fisik. |
| **CASCADE** | Opsi foreign key yang otomatis menghapus child record saat parent dihapus. |
| **SET NULL** | Opsi foreign key yang set NULL pada child record saat parent dihapus. |

---

## 6. ISTILAH TEKNIS — SECURITY

| Istilah | Definisi |
|---------|----------|
| **SQL Injection** | Serangan dengan menyisipkan kode SQL berbahaya di input. Dicegah dengan PDO prepared statements. |
| **XSS** | Cross-Site Scripting. Serangan dengan menyisipkan JavaScript berbahaya. Dicegah dengan `htmlspecialchars()`. |
| **CSRF** | Cross-Site Request Forgery. Serangan yang memaksa user melakukan aksi tanpa sadar. Dicegah dengan CSRF token. |
| **HttpOnly Cookie** | Cookie yang tidak bisa diakses via JavaScript (`document.cookie`), mencegah theft via XSS. |
| **Secure Cookie** | Cookie yang hanya dikirim via HTTPS. |
| **SameSite** | Atribut cookie yang mencegah pengiriman cookie pada cross-site request (`Strict` atau `Lax`). |
| **OWASP** | Open Web Application Security Project. Organisasi yang menyediakan standar security. |
| **Security Headers** | HTTP headers untuk keamanan: `X-Content-Type-Options`, `X-Frame-Options`, `X-XSS-Protection`, `HSTS`. |
| **HSTS** | HTTP Strict Transport Security. Header yang memaksa browser selalu menggunakan HTTPS. |
| **Input Validation** | Validasi data input sebelum diproses (required, email format, min/max length, numeric, dll). |
| **File Upload Security** | Validasi file upload: tipe (MIME), ukuran, nama file random, folder non-executable. |

---

## 7. ISTILAH TEKNIS — INFRASTRUKTUR

| Istilah | Definisi |
|---------|----------|
| **XAMPP** | Paket Apache + MySQL + PHP + Perl untuk Windows. |
| **LAMPP** | XAMPP untuk Linux (di `/opt/lampp/`). |
| **LAMP Stack** | Linux + Apache + MySQL + PHP. Stack teknologi untuk web server. |
| **LEMP Stack** | Linux + Nginx + MySQL + PHP. Alternatif LAMP dengan Nginx. |
| **VPS** | Virtual Private Server. Server virtual yang dapat dikonfigurasi sendiri. |
| **Apache** | Web server open-source (mod_rewrite untuk URL routing). |
| **Nginx** | Web server open-source alternatif (PHP-FPM untuk PHP). |
| **mod_rewrite** | Module Apache untuk URL rewriting (mengubah `index.php?url=auth/login` menjadi `/auth/login`). |
| **Virtual Host** | Konfigurasi Apache untuk hosting multiple domain di satu server. |
| **Let's Encrypt** | Otoritas sertifikat SSL gratis dan otomatis (via Certbot). |
| **SSL/HTTPS** | Secure Sockets Layer. Enkripsi komunikasi browser-server. |
| **Cron Job** | Penjadwalan tugas di Linux (contoh: backup database harian 02:00). |
| **phpMyAdmin** | Tool web-based untuk manajemen MySQL. |
| **OPcache** | Opcode cache PHP untuk mempercepat eksekusi script. |
| **Gzip Compression** | Kompresi response HTTP untuk mengurangi transfer size. |
| **CDN** | Content Delivery Network. Distribusi static assets dari server terdekat. |

---

## 8. ISTILAH TEKNIS — DEVOPS & TOOLING

| Istilah | Definisi |
|---------|----------|
| **Git** | Version control system terdistribusi. |
| **Branch** | Cabang independen dari codebase untuk pengembangan paralel. |
| **Pull Request** | Permintaan untuk menggabungkan branch ke branch utama (setelah code review). |
| **Composer** | Package manager untuk PHP (opsional, untuk autoload atau library seperti endroid/qr-code). |
| **PHPUnit** | Framework testing untuk PHP (opsional di aplikasi ini). |
| **Apache Bench (ab)** | Tool untuk performance testing HTTP server (`ab -n 100 -c 10 URL`). |
| **JMeter** | Tool untuk load testing yang lebih advanced. |
| **VS Code** | Visual Studio Code. Code editor yang direkomendasikan. |
| **Xdebug** | Extension PHP untuk debugging step-by-step di VS Code. |
| **Mermaid** | Bahasa markup untuk membuat diagram dari teks (flowchart, ERD, sequence diagram). |

---

## 9. SINGKATAN

| Singkatan | Kepanjangan |
|-----------|-------------|
| **API** | Application Programming Interface |
| **AJAX** | Asynchronous JavaScript and XML |
| **CSS** | Cascading Style Sheets |
| **CSV** | Comma-Separated Values |
| **DNS** | Domain Name System |
| **DOM** | Document Object Model |
| **ERD** | Entity Relationship Diagram |
| **FK** | Foreign Key |
| **GPS** | Global Positioning System |
| **HTML** | HyperText Markup Language |
| **HTTP** | HyperText Transfer Protocol |
| **HTTPS** | HTTP Secure |
| **JSON** | JavaScript Object Notation |
| **MIME** | Multipurpose Internet Mail Extensions |
| **MVC** | Model-View-Controller |
| **OSM** | OpenStreetMap |
| **PDO** | PHP Data Objects |
| **PK** | Primary Key |
| **PWA** | Progressive Web App |
| **RBAC** | Role-Based Access Control |
| **SRS** | Software Requirements Specification |
| **SSL** | Secure Sockets Layer |
| **UMKM** | Usaha Mikro, Kecil, dan Menengah |
| **URL** | Uniform Resource Locator |
| **UX** | User Experience |
| **VPS** | Virtual Private Server |
| **XHR** | XMLHttpRequest |

---

## 10. KODE FORMAT

| Kode | Format | Contoh |
|------|--------|--------|
| **Booking Code** | `TG-BKG-YYYYMMDD-XXX` | `TG-BKG-20260630-001` |
| **Transaction Code** | `TG-TRX-YYYYMMDD-XXX` | `TG-TRX-20260630-001` |
| **Ticket Order Code** | `TG-TKT-YYYYMMDD-XXX` | `TG-TKT-20260630-001` |
| **Hotel Booking Code** | `TG-HTL-YYYYMMDD-XXX` | `TG-HTL-20260630-001` |
| **Restaurant Order Code** | `TG-RST-YYYYMMDD-XXX` | `TG-RST-20260630-001` |
| **Event Registration Code** | `TG-EVT-YYYYMMDD-XXX` | `TG-EVT-20260630-001` |
| **Session Token** | 64 karakter hex | `a1b2c3d4e5f6...` |

> `XXX` = nomor urut 3 digit (001-999), reset per hari.

---

> **Modul Selanjutnya:** `32_AUDIT_KEAMANAN_CHECKLIST.md` — Checklist audit keamanan berbasis OWASP
