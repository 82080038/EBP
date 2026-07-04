# EBP Travel Platform - MyWisata

> Aplikasi pemandu wisata berbasis web yang menghubungkan wisatawan dengan tour guide profesional, destinasi wisata, hotel, restoran, dan event budaya.

[![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4.svg)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1.svg)](https://mysql.com)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3.svg)](https://getbootstrap.com)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

---

## Tentang Aplikasi

**MyWisata Application** adalah platform marketplace untuk layanan pariwisata yang dibangun dengan **PHP Native (Simple MVC)**, **MySQL**, dan **OpenStreetMap/Leaflet**. Aplikasi ini menghubungkan tiga jenis pengguna:

| Role | Deskripsi |
|------|-----------|
| **Admin** | Mengelola seluruh sistem: user, destinasi, approval, transaksi, laporan |
| **Wisatawan** | Mencari & booking guide, beli tiket, pesan hotel/restoran, daftar event |
| **Tour Guide** | Menawarkan jasa pemandu, kelola jadwal, terima booking, lihat pendapatan |

---

## Fitur Utama

- **Tour Guide Booking** — Cari, booking, dan pembayaran tour guide dengan kode unik
- **E-Ticket dengan QR Code** — Pembelian tiket destinasi + verifikasi via QR
- **Hotel & Homestay** — Pencarian dan booking akomodasi
- **Restoran & UMKM** — Pemesanan makanan dengan keranjang online
- **Event & Budaya** — Kalender event + pendaftaran peserta
- **Peta Interaktif** — OpenStreetMap + Leaflet dengan marker, geolocation, routing
- **Audio Guide Multibahasa** — Panduan audio per destinasi dengan transkrip
- **AI Tour Guide** — Chatbot rekomendasi destinasi & itinerary (rule-based)
- **Notifikasi** — In-app + email dengan badge real-time
- **Laporan & Analitik** — Dashboard statistik, grafik Chart.js, export CSV
- **Keamanan** — CSRF, XSS prevention, SQL injection protection, RBAC, rate limiting, audit log

---

## Tech Stack

| Komponen | Teknologi |
|----------|-----------|
| Backend | PHP 8.1+ (Native, Simple MVC) |
| Database | MySQL 8.0+ (utf8mb4) |
| Frontend | Bootstrap 5.3, jQuery 3.7 |
| Peta | OpenStreetMap + Leaflet 1.9 |
| Icons | Font Awesome 6 |
| Tables | DataTables 1.13 |
| Select | Select2 4.1 |
| Alert | SweetAlert2 11 |
| Charts | Chart.js 4 |
| Web Server | Apache (mod_rewrite) / Nginx (PHP-FPM) |

---

## Struktur Project

```
mywisata/
├── docs/                 # 33 file dokumentasi (.md)
├── app/
│   ├── config/           # Konfigurasi (config.php, database.php)
│   ├── core/             # Core framework (App, Controller, Model, View, dll)
│   ├── controllers/      # Controller — logika bisnis
│   ├── models/           # Model — interaksi database (PDO)
│   └── views/            # View — template HTML (layouts, auth, admin, wisatawan, tourguide)
├── public/
│   ├── assets/           # CSS, JS, images, third-party libraries
│   └── uploads/          # File upload user (gambar, audio, dokumen, QR code)
├── database/
│   ├── migration.sql     # Skema database (33 tabel)
│   ├── seed.sql          # Data awal
│   └── backup/           # Folder backup otomatis
├── logs/                 # Log files (error.log, audit.log)
├── cron/                 # Cron job scripts
├── index.php             # Front controller (entry point)
├── .htaccess             # Apache rewrite rules
└── README.md             # File ini
```

---

## Instalasi Cepat (Local Development)

### Prasyarat

- PHP 8.1+ dengan MySQL 8.0+ (XAMPP/LAMPP atau native)
- Node.js 18+ (untuk Playwright testing)
- Browser modern (Chrome/Firefox/Edge)

### Langkah Instalasi

1. **Clone repository**
   ```bash
   git clone https://github.com/82080038/mywisata.git
   cd mywisata
   ```

2. **Start MySQL server**
   ```bash
   # XAMPP/LAMPP
   sudo /opt/lampp/lampp start

   # Atau native MySQL
   sudo systemctl start mysql
   ```

3. **Buat database**
   ```bash
   mysql -u root -e "CREATE DATABASE mywisata CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   ```

4. **Import schema & seed data**
   ```bash
   mysql -u root mywisata < database/migration.sql
   mysql -u root mywisata < database/seed.sql
   ```

5. **Install dependencies untuk testing**
   ```bash
   npm install
   npx playwright install chromium
   ```

6. **Start PHP development server**
   ```bash
   php -S localhost:8080 router.php
   ```

7. **Akses aplikasi** di browser: `http://localhost:8080/`

### Menjalankan Tests

```bash
# Jalankan semua tests
npx playwright test

# Jalankan dengan browser visible (headed)
npx playwright test --project=chromium --headed

# Lihat test report
npx playwright show-report
```

### Multi-Environment Support

Aplikasi ini mendukung development di multiple komputer (Windows & Linux) dengan konfigurasi terpusat di `prompting/config.json`. Lihat [`prompting/README_SETUP.md`](prompting/README_SETUP.md) untuk panduan setup lengkap.

### Login Default

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@mywisata.com | admin123 |

> **Penting:** Ganti password admin setelah login pertama!

---

## Dokumentasi

Dokumentasi lengkap berada di folder [`docs/`](docs/). Lihat [`docs/00_DAFTAR_ISI.md`](docs/00_DAFTAR_ISI.md) untuk indeks lengkap.

### Dokumentasi Penting

| Dokumen | Deskripsi |
|---------|-----------|
| [`docs/01_KONSEP_DAN_ANALISIS_SISTEM.md`](docs/01_KONSEP_DAN_ANALISIS_SISTEM.md) | Konsep, analisis pasar, scope |
| [`docs/03_DESAIN_ARSITEKTUR_APLIKASI.md`](docs/03_DESAIN_ARSITEKTUR_APLIKASI.md) | Arsitektur MVC, core classes, security |
| [`docs/05_DESAIN_DATABASE_MYSQL_ERD.md`](docs/05_DESAIN_DATABASE_MYSQL_ERD.md) | DDL 33 tabel, ERD, index strategy |
| [`docs/27_PANDUAN_INSTALASI_LOKAL.md`](docs/27_PANDUAN_INSTALASI_LOKAL.md) | Panduan instalasi lokal lengkap |
| [`docs/28_STANDAR_KODE_KONTRIBUSI.md`](docs/28_STANDAR_KODE_KONTRIBUSI.md) | Standar kode & Git workflow |
| [`docs/29_CHECKLIST_PENGEMBANGAN.md`](docs/29_CHECKLIST_PENGEMBANGAN.md) | Checklist per fase pengembangan |
| [`docs/30_DIAGRAM_ALUR_BISNIS.md`](docs/30_DIAGRAM_ALUR_BISNIS.md) | Diagram flowchart semua proses bisnis |
| [`docs/32_AUDIT_KEAMANAN_CHECKLIST.md`](docs/32_AUDIT_KEAMANAN_CHECKLIST.md) | Checklist audit keamanan OWASP |

---

## Roadmap Pengembangan

| Fase | Minggu | Fokus |
|------|--------|-------|
| **Fase 1: MVP** | 1-6 | Auth, Tour Guide, Map, Booking, Tiket |
| **Fase 2: Core Features** | 7-12 | Hotel, Restoran, Event, Notifikasi, Report |
| **Fase 3: Advanced** | 13-18 | Audio Guide, AI Chatbot, Review, Optimization |
| **Fase 4: Production** | 19-22 | Security, Testing, Deployment, Go Live |

**Total estimasi:** 22 minggu (5.5 bulan) — MVP hingga Go Live

Lihat [`docs/26_ROADMAP_PENGEMBANGAN.md`](docs/26_ROADMAP_PENGEMBANGAN.md) untuk detail lengkap.

---

## Database

- **33 tabel** MySQL dengan charset `utf8mb4`
- Storage engine: `InnoDB` (transaksi + foreign key)
- Primary key: `BIGINT UNSIGNED AUTO_INCREMENT`
- Koordinat GPS: `DECIMAL(10,7)` untuk akurasi

Lihat:
- [`docs/05_DESAIN_DATABASE_MYSQL_ERD.md`](docs/05_DESAIN_DATABASE_MYSQL_ERD.md) — DDL + ERD
- [`docs/06_KAMUS_DATA_DATABASE.md`](docs/06_KAMUS_DATA_DATABASE.md) — Kamus data

---

## API Endpoints

Semua komunikasi frontend-backend via AJAX dengan response JSON standar:

```json
{
  "status": "success | error",
  "message": "Deskripsi pesan",
  "data": { ... },
  "meta": { "total": 100, "page": 1, "per_page": 20 }
}
```

Lihat [`docs/21_API_DESIGN_AJAX_JSON.md`](docs/21_API_DESIGN_AJAX_JSON.md) untuk daftar endpoint lengkap.

---

## Keamanan

| Aspek | Implementasi |
|-------|-------------|
| Password Hash | bcrypt (`PASSWORD_BCRYPT`) |
| SQL Injection | PDO Prepared Statements |
| XSS | `htmlspecialchars()` di semua output |
| CSRF | Token per session, verified di POST |
| RBAC | Middleware role check per controller |
| Rate Limiting | 60 request/menit per user |
| Audit Log | Log semua aksi penting |
| File Upload | MIME check, size limit, random filename |
| Session | HttpOnly, Secure, SameSite, 30min timeout |

Lihat [`docs/20_SECURITY_SYSTEM.md`](docs/20_SECURITY_SYSTEM.md) dan [`docs/32_AUDIT_KEAMANAN_CHECKLIST.md`](docs/32_AUDIT_KEAMANAN_CHECKLIST.md).

---

## Testing

| Tipe | Tools |
|------|-------|
| Unit Test | PHPUnit (opsional) / manual |
| API Test | Postman / curl |
| UI Test | Browser manual |
| Security Test | OWASP checklist |
| Performance Test | Apache Bench / JMeter |

Lihat [`docs/24_TESTING_SYSTEM.md`](docs/24_TESTING_SYSTEM.md) untuk test cases lengkap.

---

## Deployment

### Server Minimum

| Komponen | Minimum | Rekomendasi |
|----------|---------|-------------|
| OS | Ubuntu 20.04 LTS | Ubuntu 22.04 LTS |
| CPU | 2 core | 4 core |
| RAM | 2 GB | 4 GB |
| Storage | 20 GB SSD | 50 GB SSD |

### Quick Deploy

```bash
# 1. Setup LAMP stack
sudo apt install apache2 mysql-server php8.1 php8.1-mysql php8.1-gd php8.1-mbstring php8.1-xml php8.1-curl php8.1-zip php8.1-intl

# 2. Copy project
sudo cp -r mywisata /var/www/mywisata
sudo chown -R www-data:www-data /var/www/mywisata

# 3. Import database
mysql -u root -p -e "CREATE DATABASE tour_guide_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p tour_guide_app < /var/www/mywisata/database/migration.sql
mysql -u root -p tour_guide_app < /var/www/mywisata/database/seed.sql

# 4. Setup SSL
sudo apt install certbot python3-certbot-apache
sudo certbot --apache -d yourdomain.com

# 5. Enable rewrite
sudo a2enmod rewrite headers
sudo systemctl reload apache2
```

Lihat [`docs/25_DEPLOYMENT_SERVER.md`](docs/25_DEPLOYMENT_SERVER.md) untuk panduan deployment lengkap.

---

## Backup Database

```bash
# Manual backup
mysqldump -u root tour_guide_app | gzip > backup_$(date +%Y%m%d).sql.gz

# Cron harian (02:00)
0 2 * * * /opt/scripts/backup_db.sh
```

Lihat [`docs/23_DATABASE_BACKUP_RECOVERY.md`](docs/23_DATABASE_BACKUP_RECOVERY.md) untuk strategi backup lengkap.

---

## Kontribusi

1. Fork repository
2. Buat branch: `git checkout -b feature/nama-fitur`
3. Commit dengan format: `feat(scope): description`
4. Push: `git push origin feature/nama-fitur`
5. Buat Pull Request ke `develop` branch

Lihat [`docs/28_STANDAR_KODE_KONTRIBUSI.md`](docs/28_STANDAR_KODE_KONTRIBUSI.md) untuk standar kode dan Git workflow lengkap.

---

## Lisensi

MIT License — bebas digunakan, dimodifikasi, dan didistribusikan.

---

## Kontak

- **Email:** admin@mywisata.com
- **Repository:** https://github.com/82080038/mywisata
- **Domain:** mywisata.com (akan dibeli sebagai DNS aplikasi)

---

> **Catatan:** Aplikasi ini dibangun sebagai landasan pembangunan platform pariwisata berbasis PHP Native yang ringan, biaya operasional rendah (OpenStreetMap gratis), dan dapat dikembangkan bertahap dari MVP hingga platform lengkap. Lihat dokumentasi lengkap di folder [`docs/`](docs/).
