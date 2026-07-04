---
description: Setup dan konfigurasi awal aplikasi Kewer
---

# Setup Aplikasi Kewer (v2.5.0)

## Prerequisites
- XAMPP (Apache + MySQL/MariaDB + PHP 8.2+)
- Git
- Composer (untuk dependency management)

## Setup Steps

1. **Clone Repository**
   ```bash
   git clone https://github.com/82080038/kewer.git
   cd kewer
   ```

2. **Start XAMPP**
   - Start Apache
   - Start MySQL/MariaDB

3. **Import Database**
   - Buka phpMyAdmin
   - Buat 3 database: `kewer`, `db_alamat_simple`, `db_orang`
   - Import SQL files dari folder `database/`

4. **Install Dependencies**
   ```bash
   composer install
   ```

5. **Konfigurasi Environment**
   - Edit `config/database.php` sesuai konfigurasi database
   - Edit `config/path.php` sesuai lokasi aplikasi

6. **Setup Organizational Structure**
   - Login sebagai appOwner (appowner / AppOwner2024!)
   - Register Bos
   - Setup kantor pusat
   - Delegasi permissions
   - Assign branch managers

7. **Testing API**
   - Buka aplikasi di browser
   - Pastikan semua halaman menggunakan client-side rendering
   - Verifikasi API endpoint berfungsi dengan benar

## Client-Side Rendering (v2.5.0)
- Semua halaman menggunakan jQuery dan JSON API
- Global API helper di `includes/js/api.js`
- Master app JavaScript di `includes/js/app.js`
- Sidebar menginclude global JS files secara otomatis

## API Integration
- API endpoint tersedia di `/api/`
- Gunakan Bearer token: `kewer-api-token-2024`
- Response format JSON yang seragam
- Dokumentasi API: lihat `.windsurf/analysis.md`
