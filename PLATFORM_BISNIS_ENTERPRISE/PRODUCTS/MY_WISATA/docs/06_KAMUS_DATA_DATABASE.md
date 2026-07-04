# MODUL 06 — KAMUS DATA DATABASE

> **Aplikasi:** Tour Guide Application  
> **Versi Dokumen:** 1.1  
> **Tanggal:** 2026-06-30  
> **Last Updated:** 2026-06-30

---

## 1. MYSQL 8.0 DATA TYPES REFERENCE

### Numeric Types

| Type | Range | Storage | Use Case |
|------|-------|---------|----------|
| TINYINT | -128 to 127 | 1 byte | Small flags, status codes |
| SMALLINT | -32,768 to 32,767 | 2 bytes | Small IDs, counts |
| INT | -2.1B to 2.1B | 4 bytes | Standard IDs |
| BIGINT | -9.2E18 to 9.2E18 | 8 bytes | Large IDs, timestamps |
| DECIMAL(M,D) | Exact precision | M+2 bytes | Currency, precise calculations |
| FLOAT | Approximate | 4 bytes | Approximate measurements |

### String Types

| Type | Max Length | Storage | Use Case |
|------|------------|---------|----------|
| CHAR(N) | 255 | N bytes | Fixed-length codes |
| VARCHAR(N) | 65,535 | L+1 bytes | Variable-length text |
| TEXT | 65,535 | L+2 bytes | Short descriptions |
| MEDIUMTEXT | 16M | L+3 bytes | Long content |
| LONGTEXT | 4G | L+4 bytes | Very long content |
| JSON | 4G | L+4 bytes | Structured data |

### Date/Time Types

| Type | Range | Storage | Use Case |
|------|-------|---------|----------|
| DATE | 1000-01-01 to 9999-12-31 | 3 bytes | Dates only |
| TIME | -838:59:59 to 838:59:59 | 3 bytes | Time only |
| DATETIME | 1000-01-01 00:00:00 to 9999-12-31 23:59:59 | 8 bytes | Date and time |
| TIMESTAMP | 1970-01-01 00:00:01 UTC to 2038-01-19 03:14:07 UTC | 4 bytes | Auto-updating timestamps |
| YEAR | 1901 to 2155 | 1 byte | Year values |

### Validation Rules by Data Type

| Data Type | Validation Pattern | Example |
|-----------|-------------------|---------|
| VARCHAR(email) | FILTER_VALIDATE_EMAIL | user@example.com |
| VARCHAR(phone) | Regex: `^\+?[0-9]{10,15}$` | +6281234567890 |
| VARCHAR(url) | FILTER_VALIDATE_URL | https://example.com |
| DECIMAL(price) | >= 0 | 100.50 |
| ENUM(status) | Specific values only | 'pending', 'paid', 'cancelled' |
| JSON | Valid JSON structure | {"key": "value"} |

---

## 2. TABEL `users`

| Kolom | Tipe Data | Panjang | Null | Default | Keterangan |
|-------|-----------|---------|------|---------|-----------|
| id | BIGINT UNSIGNED | - | NO | AUTO_INCREMENT | Primary key |
| name | VARCHAR | 100 | NO | - | Nama lengkap pengguna |
| email | VARCHAR | 150 | NO | - | Email unik untuk login |
| password | VARCHAR | 255 | NO | - | Hash bcrypt password |
| phone | VARCHAR | 20 | YES | NULL | Nomor telepon |
| role | ENUM | - | NO | 'wisatawan' | admin, wisatawan, tour_guide |
| avatar | VARCHAR | 255 | YES | NULL | Path foto profil |
| status | ENUM | - | NO | 'active' | active, inactive, banned, pending |
| email_verified | TINYINT(1) | - | NO | 0 | Status verifikasi email |
| remember_token | VARCHAR | 255 | YES | NULL | Token remember me |
| last_login | DATETIME | - | YES | NULL | Waktu login terakhir |
| created_at | TIMESTAMP | - | YES | CURRENT_TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | - | YES | CURRENT_TIMESTAMP ON UPDATE | Waktu diupdate |

---

## 2. TABEL `user_profiles`

| Kolom | Tipe Data | Panjang | Null | Default | Keterangan |
|-------|-----------|---------|------|---------|-----------|
| id | BIGINT UNSIGNED | - | NO | AUTO_INCREMENT | Primary key |
| user_id | BIGINT UNSIGNED | - | NO | - | FK → users.id |
| first_name | VARCHAR | 50 | YES | NULL | Nama depan |
| last_name | VARCHAR | 50 | YES | NULL | Nama belakang |
| birth_date | DATE | - | YES | NULL | Tanggal lahir |
| gender | ENUM | - | YES | NULL | male, female, other |
| nationality | VARCHAR | 50 | YES | NULL | Kewarganegaraan |
| address | TEXT | - | YES | NULL | Alamat lengkap |
| city | VARCHAR | 100 | YES | NULL | Kota |
| province | VARCHAR | 100 | YES | NULL | Provinsi |
| postal_code | VARCHAR | 10 | YES | NULL | Kode pos |
| country | VARCHAR | 100 | YES | 'Indonesia' | Negara |
| bio | TEXT | - | YES | NULL | Biografi singkat |
| created_at | TIMESTAMP | - | YES | CURRENT_TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | - | YES | CURRENT_TIMESTAMP ON UPDATE | Waktu diupdate |

---

## 3. TABEL `tour_guides`

| Kolom | Tipe Data | Panjang | Null | Default | Keterangan |
|-------|-----------|---------|------|---------|-----------|
| id | BIGINT UNSIGNED | - | NO | AUTO_INCREMENT | Primary key |
| user_id | BIGINT UNSIGNED | - | NO | - | FK → users.id |
| license_number | VARCHAR | 50 | YES | NULL | Nomor lisensi guide |
| experience_years | INT | - | YES | 0 | Tahun pengalaman |
| hourly_rate | DECIMAL(10,2) | - | YES | 0 | Tarif per jam (IDR) |
| daily_rate | DECIMAL(10,2) | - | YES | 0 | Tarif per hari (IDR) |
| rating_avg | DECIMAL(2,1) | - | YES | 0.0 | Rata-rata rating |
| total_reviews | INT | - | YES | 0 | Total review |
| total_tours | INT | - | YES | 0 | Total tour selesai |
| is_verified | TINYINT(1) | - | NO | 0 | Status verifikasi admin |
| verified_at | DATETIME | - | YES | NULL | Waktu verifikasi |
| verified_by | BIGINT UNSIGNED | - | YES | NULL | FK → users.id (admin) |
| is_available | TINYINT(1) | - | NO | 1 | Status ketersediaan |
| latitude | DECIMAL(10,7) | - | YES | NULL | Latitude lokasi |
| longitude | DECIMAL(10,7) | - | YES | NULL | Longitude lokasi |
| created_at | TIMESTAMP | - | YES | CURRENT_TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | - | YES | CURRENT_TIMESTAMP ON UPDATE | Waktu diupdate |

---

## 4. TABEL `guide_languages`

| Kolom | Tipe Data | Panjang | Null | Default | Keterangan |
|-------|-----------|---------|------|---------|-----------|
| id | BIGINT UNSIGNED | - | NO | AUTO_INCREMENT | Primary key |
| guide_id | BIGINT UNSIGNED | - | NO | - | FK → tour_guides.id |
| language | VARCHAR | 50 | NO | - | Nama bahasa |
| proficiency | ENUM | - | NO | 'fluent' | basic, intermediate, fluent, native |

---

## 5. TABEL `guide_specializations`

| Kolom | Tipe Data | Panjang | Null | Default | Keterangan |
|-------|-----------|---------|------|---------|-----------|
| id | BIGINT UNSIGNED | - | NO | AUTO_INCREMENT | Primary key |
| guide_id | BIGINT UNSIGNED | - | NO | - | FK → tour_guides.id |
| specialization | VARCHAR | 100 | NO | - | Spesialisasi (alam, budaya, dll) |

---

## 6. TABEL `guide_schedules`

| Kolom | Tipe Data | Panjang | Null | Default | Keterangan |
|-------|-----------|---------|------|---------|-----------|
| id | BIGINT UNSIGNED | - | NO | AUTO_INCREMENT | Primary key |
| guide_id | BIGINT UNSIGNED | - | NO | - | FK → tour_guides.id |
| available_date | DATE | - | NO | - | Tanggal tersedia |
| start_time | TIME | - | YES | '08:00:00' | Jam mulai |
| end_time | TIME | - | YES | '17:00:00' | Jam selesai |
| is_booked | TINYINT(1) | - | NO | 0 | Sudah dibooking |
| notes | VARCHAR | 255 | YES | NULL | Catatan |

---

## 7. TABEL `guide_documents`

| Kolom | Tipe Data | Panjang | Null | Default | Keterangan |
|-------|-----------|---------|------|---------|-----------|
| id | BIGINT UNSIGNED | - | NO | AUTO_INCREMENT | Primary key |
| guide_id | BIGINT UNSIGNED | - | NO | - | FK → tour_guides.id |
| document_type | ENUM | - | NO | - | ktp, sertifikat, lisensi, other |
| file_path | VARCHAR | 255 | NO | - | Path file dokumen |
| uploaded_at | TIMESTAMP | - | YES | CURRENT_TIMESTAMP | Waktu upload |

---

## 8. TABEL `destination_categories`

| Kolom | Tipe Data | Panjang | Null | Default | Keterangan |
|-------|-----------|---------|------|---------|-----------|
| id | BIGINT UNSIGNED | - | NO | AUTO_INCREMENT | Primary key |
| name | VARCHAR | 100 | NO | - | Nama kategori |
| slug | VARCHAR | 100 | NO | - | URL slug unik |
| icon | VARCHAR | 50 | YES | NULL | Class Font Awesome |
| description | TEXT | - | YES | NULL | Deskripsi kategori |
| created_at | TIMESTAMP | - | YES | CURRENT_TIMESTAMP | Waktu dibuat |

---

## 9. TABEL `destinations`

| Kolom | Tipe Data | Panjang | Null | Default | Keterangan |
|-------|-----------|---------|------|---------|-----------|
| id | BIGINT UNSIGNED | - | NO | AUTO_INCREMENT | Primary key |
| category_id | BIGINT UNSIGNED | - | YES | NULL | FK → destination_categories.id |
| name | VARCHAR | 200 | NO | - | Nama destinasi |
| slug | VARCHAR | 200 | NO | - | URL slug unik |
| description | TEXT | - | YES | NULL | Deskripsi lengkap |
| short_desc | VARCHAR | 500 | YES | NULL | Deskripsi singkat |
| address | TEXT | - | YES | NULL | Alamat |
| city | VARCHAR | 100 | YES | NULL | Kota |
| province | VARCHAR | 100 | YES | NULL | Provinsi |
| latitude | DECIMAL(10,7) | - | NO | - | Latitude GPS |
| longitude | DECIMAL(10,7) | - | NO | - | Longitude GPS |
| entry_fee | DECIMAL(10,2) | - | YES | 0 | Harga tiket masuk |
| opening_time | TIME | - | YES | NULL | Jam buka |
| closing_time | TIME | - | YES | NULL | Jam tutup |
| rating_avg | DECIMAL(2,1) | - | YES | 0.0 | Rata-rata rating |
| total_reviews | INT | - | YES | 0 | Total review |
| total_visitors | INT | - | YES | 0 | Total pengunjung |
| daily_quota | INT | - | YES | NULL | Kuota tiket harian |
| is_active | TINYINT(1) | - | NO | 1 | Status aktif |
| is_featured | TINYINT(1) | - | NO | 0 | Destinasi unggulan |
| main_image | VARCHAR | 255 | YES | NULL | Foto utama |
| created_at | TIMESTAMP | - | YES | CURRENT_TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | - | YES | CURRENT_TIMESTAMP ON UPDATE | Waktu diupdate |

---

## 10. TABEL `destination_images`

| Kolom | Tipe Data | Panjang | Null | Default | Keterangan |
|-------|-----------|---------|------|---------|-----------|
| id | BIGINT UNSIGNED | - | NO | AUTO_INCREMENT | Primary key |
| destination_id | BIGINT UNSIGNED | - | NO | - | FK → destinations.id |
| file_path | VARCHAR | 255 | NO | - | Path file gambar |
| caption | VARCHAR | 255 | YES | NULL | Keterangan gambar |
| sort_order | INT | - | YES | 0 | Urutan tampil |

---

## 11. TABEL `tickets`

| Kolom | Tipe Data | Panjang | Null | Default | Keterangan |
|-------|-----------|---------|------|---------|-----------|
| id | BIGINT UNSIGNED | - | NO | AUTO_INCREMENT | Primary key |
| destination_id | BIGINT UNSIGNED | - | NO | - | FK → destinations.id |
| ticket_type | ENUM | - | NO | 'regular' | regular, child, senior, group, foreigner |
| price | DECIMAL(10,2) | - | NO | - | Harga tiket |
| description | VARCHAR | 255 | YES | NULL | Keterangan tiket |
| is_active | TINYINT(1) | - | NO | 1 | Status aktif |

---

## 12. TABEL `ticket_orders`

| Kolom | Tipe Data | Panjang | Null | Default | Keterangan |
|-------|-----------|---------|------|---------|-----------|
| id | BIGINT UNSIGNED | - | NO | AUTO_INCREMENT | Primary key |
| order_code | VARCHAR | 30 | NO | - | Kode unik (TG-TKT-YYYYMMDD-XXX) |
| user_id | BIGINT UNSIGNED | - | NO | - | FK → users.id |
| transaction_id | BIGINT UNSIGNED | - | YES | NULL | FK → transactions.id |
| visit_date | DATE | - | NO | - | Tanggal kunjungan |
| total_amount | DECIMAL(12,2) | - | NO | - | Total pembayaran |
| status | ENUM | - | NO | 'pending' | pending, paid, confirmed, used, cancelled, refunded |
| qr_code_path | VARCHAR | 255 | YES | NULL | Path QR code image |
| created_at | TIMESTAMP | - | YES | CURRENT_TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | - | YES | CURRENT_TIMESTAMP ON UPDATE | Waktu diupdate |

---

## 13. TABEL `ticket_order_items`

| Kolom | Tipe Data | Panjang | Null | Default | Keterangan |
|-------|-----------|---------|------|---------|-----------|
| id | BIGINT UNSIGNED | - | NO | AUTO_INCREMENT | Primary key |
| order_id | BIGINT UNSIGNED | - | NO | - | FK → ticket_orders.id |
| ticket_id | BIGINT UNSIGNED | - | NO | - | FK → tickets.id |
| quantity | INT | - | NO | 1 | Jumlah tiket |
| subtotal | DECIMAL(12,2) | - | NO | - | Subtotal harga |

---

## 14. TABEL `hotels`

| Kolom | Tipe Data | Panjang | Null | Default | Keterangan |
|-------|-----------|---------|------|---------|-----------|
| id | BIGINT UNSIGNED | - | NO | AUTO_INCREMENT | Primary key |
| owner_id | BIGINT UNSIGNED | - | NO | - | FK → users.id |
| name | VARCHAR | 200 | NO | - | Nama hotel/homestay |
| type | ENUM | - | NO | 'hotel' | hotel, homestay, villa, guesthouse |
| description | TEXT | - | YES | NULL | Deskripsi |
| address | TEXT | - | YES | NULL | Alamat |
| city | VARCHAR | 100 | YES | NULL | Kota |
| province | VARCHAR | 100 | YES | NULL | Provinsi |
| latitude | DECIMAL(10,7) | - | YES | NULL | Latitude GPS |
| longitude | DECIMAL(10,7) | - | YES | NULL | Longitude GPS |
| phone | VARCHAR | 20 | YES | NULL | Telepon |
| email | VARCHAR | 150 | YES | NULL | Email |
| rating_avg | DECIMAL(2,1) | - | YES | 0.0 | Rata-rata rating |
| total_reviews | INT | - | YES | 0 | Total review |
| main_image | VARCHAR | 255 | YES | NULL | Foto utama |
| is_approved | TINYINT(1) | - | NO | 0 | Status approval admin |
| is_active | TINYINT(1) | - | NO | 1 | Status aktif |
| created_at | TIMESTAMP | - | YES | CURRENT_TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | - | YES | CURRENT_TIMESTAMP ON UPDATE | Waktu diupdate |

---

## 15. TABEL `hotel_rooms`

| Kolom | Tipe Data | Panjang | Null | Default | Keterangan |
|-------|-----------|---------|------|---------|-----------|
| id | BIGINT UNSIGNED | - | NO | AUTO_INCREMENT | Primary key |
| hotel_id | BIGINT UNSIGNED | - | NO | - | FK → hotels.id |
| room_type | VARCHAR | 100 | NO | - | Tipe kamar |
| description | TEXT | - | YES | NULL | Deskripsi kamar |
| capacity | INT | - | YES | 2 | Kapasitas tamu |
| price_per_night | DECIMAL(10,2) | - | NO | - | Harga per malam |
| total_rooms | INT | - | NO | 1 | Total kamar tersedia |
| available_rooms | INT | - | NO | 1 | Kamar tersedia saat ini |
| amenities | JSON | - | YES | NULL | Fasilitas (array) |
| image | VARCHAR | 255 | YES | NULL | Foto kamar |
| is_active | TINYINT(1) | - | NO | 1 | Status aktif |

---

## 16. TABEL `hotel_bookings`

| Kolom | Tipe Data | Panjang | Null | Default | Keterangan |
|-------|-----------|---------|------|---------|-----------|
| id | BIGINT UNSIGNED | - | NO | AUTO_INCREMENT | Primary key |
| booking_code | VARCHAR | 30 | NO | - | Kode unik |
| user_id | BIGINT UNSIGNED | - | NO | - | FK → users.id |
| hotel_id | BIGINT UNSIGNED | - | NO | - | FK → hotels.id |
| room_id | BIGINT UNSIGNED | - | NO | - | FK → hotel_rooms.id |
| transaction_id | BIGINT UNSIGNED | - | YES | NULL | FK → transactions.id |
| check_in | DATE | - | NO | - | Tanggal check-in |
| check_out | DATE | - | NO | - | Tanggal check-out |
| num_rooms | INT | - | NO | 1 | Jumlah kamar |
| num_nights | INT | - | NO | 1 | Jumlah malam |
| total_amount | DECIMAL(12,2) | - | NO | - | Total pembayaran |
| status | ENUM | - | NO | 'pending' | pending, confirmed, checked_in, checked_out, cancelled |
| created_at | TIMESTAMP | - | YES | CURRENT_TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | - | YES | CURRENT_TIMESTAMP ON UPDATE | Waktu diupdate |

---

## 17. TABEL `restaurants`

| Kolom | Tipe Data | Panjang | Null | Default | Keterangan |
|-------|-----------|---------|------|---------|-----------|
| id | BIGINT UNSIGNED | - | NO | AUTO_INCREMENT | Primary key |
| owner_id | BIGINT UNSIGNED | - | NO | - | FK → users.id |
| name | VARCHAR | 200 | NO | - | Nama restoran/UMKM |
| type | ENUM | - | NO | 'restoran' | restoran, warung, kafe, umkm, street_food |
| cuisine_type | VARCHAR | 100 | YES | NULL | Jenis kuliner |
| description | TEXT | - | YES | NULL | Deskripsi |
| address | TEXT | - | YES | NULL | Alamat |
| city | VARCHAR | 100 | YES | NULL | Kota |
| province | VARCHAR | 100 | YES | NULL | Provinsi |
| latitude | DECIMAL(10,7) | - | YES | NULL | Latitude GPS |
| longitude | DECIMAL(10,7) | - | YES | NULL | Longitude GPS |
| phone | VARCHAR | 20 | YES | NULL | Telepon |
| email | VARCHAR | 150 | YES | NULL | Email |
| opening_time | TIME | - | YES | NULL | Jam buka |
| closing_time | TIME | - | YES | NULL | Jam tutup |
| rating_avg | DECIMAL(2,1) | - | YES | 0.0 | Rata-rata rating |
| total_reviews | INT | - | YES | 0 | Total review |
| main_image | VARCHAR | 255 | YES | NULL | Foto utama |
| is_approved | TINYINT(1) | - | NO | 0 | Status approval admin |
| is_active | TINYINT(1) | - | NO | 1 | Status aktif |
| created_at | TIMESTAMP | - | YES | CURRENT_TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | - | YES | CURRENT_TIMESTAMP ON UPDATE | Waktu diupdate |

---

## 18. TABEL `menu_items`

| Kolom | Tipe Data | Panjang | Null | Default | Keterangan |
|-------|-----------|---------|------|---------|-----------|
| id | BIGINT UNSIGNED | - | NO | AUTO_INCREMENT | Primary key |
| restaurant_id | BIGINT UNSIGNED | - | NO | - | FK → restaurants.id |
| name | VARCHAR | 200 | NO | - | Nama menu |
| description | TEXT | - | YES | NULL | Deskripsi menu |
| price | DECIMAL(10,2) | - | NO | - | Harga |
| category | VARCHAR | 50 | YES | NULL | Kategori menu |
| image | VARCHAR | 255 | YES | NULL | Foto menu |
| is_available | TINYINT(1) | - | NO | 1 | Status tersedia |

---

## 19. TABEL `restaurant_orders`

| Kolom | Tipe Data | Panjang | Null | Default | Keterangan |
|-------|-----------|---------|------|---------|-----------|
| id | BIGINT UNSIGNED | - | NO | AUTO_INCREMENT | Primary key |
| order_code | VARCHAR | 30 | NO | - | Kode unik |
| user_id | BIGINT UNSIGNED | - | NO | - | FK → users.id |
| restaurant_id | BIGINT UNSIGNED | - | NO | - | FK → restaurants.id |
| transaction_id | BIGINT UNSIGNED | - | YES | NULL | FK → transactions.id |
| order_type | ENUM | - | NO | 'dine_in' | dine_in, pickup, delivery |
| total_amount | DECIMAL(12,2) | - | NO | - | Total pembayaran |
| status | ENUM | - | NO | 'pending' | pending, confirmed, preparing, ready, completed, cancelled |
| notes | TEXT | - | YES | NULL | Catatan pesanan |
| created_at | TIMESTAMP | - | YES | CURRENT_TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | - | YES | CURRENT_TIMESTAMP ON UPDATE | Waktu diupdate |

---

## 20. TABEL `restaurant_order_items`

| Kolom | Tipe Data | Panjang | Null | Default | Keterangan |
|-------|-----------|---------|------|---------|-----------|
| id | BIGINT UNSIGNED | - | NO | AUTO_INCREMENT | Primary key |
| order_id | BIGINT UNSIGNED | - | NO | - | FK → restaurant_orders.id |
| menu_item_id | BIGINT UNSIGNED | - | NO | - | FK → menu_items.id |
| quantity | INT | - | NO | 1 | Jumlah pesanan |
| subtotal | DECIMAL(12,2) | - | NO | - | Subtotal harga |

---

## 21. TABEL `events`

| Kolom | Tipe Data | Panjang | Null | Default | Keterangan |
|-------|-----------|---------|------|---------|-----------|
| id | BIGINT UNSIGNED | - | NO | AUTO_INCREMENT | Primary key |
| organizer_id | BIGINT UNSIGNED | - | NO | - | FK → users.id |
| title | VARCHAR | 200 | NO | - | Judul event |
| slug | VARCHAR | 200 | NO | - | URL slug unik |
| description | TEXT | - | YES | NULL | Deskripsi event |
| category | ENUM | - | NO | 'budaya' | festival, seni, kuliner, olahraga, budaya, religi, other |
| start_date | DATETIME | - | NO | - | Tanggal & jam mulai |
| end_date | DATETIME | - | NO | - | Tanggal & jam selesai |
| location_name | VARCHAR | 200 | YES | NULL | Nama lokasi |
| address | TEXT | - | YES | NULL | Alamat |
| latitude | DECIMAL(10,7) | - | YES | NULL | Latitude GPS |
| longitude | DECIMAL(10,7) | - | YES | NULL | Longitude GPS |
| price | DECIMAL(10,2) | - | YES | 0 | Harga tiket event |
| max_participants | INT | - | YES | NULL | Kuota peserta |
| registered_count | INT | - | YES | 0 | Jumlah terdaftar |
| main_image | VARCHAR | 255 | YES | NULL | Foto utama |
| is_active | TINYINT(1) | - | NO | 1 | Status aktif |
| created_at | TIMESTAMP | - | YES | CURRENT_TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | - | YES | CURRENT_TIMESTAMP ON UPDATE | Waktu diupdate |

---

## 22. TABEL `event_registrations`

| Kolom | Tipe Data | Panjang | Null | Default | Keterangan |
|-------|-----------|---------|------|---------|-----------|
| id | BIGINT UNSIGNED | - | NO | AUTO_INCREMENT | Primary key |
| registration_code | VARCHAR | 30 | NO | - | Kode unik |
| user_id | BIGINT UNSIGNED | - | NO | - | FK → users.id |
| event_id | BIGINT UNSIGNED | - | NO | - | FK → events.id |
| transaction_id | BIGINT UNSIGNED | - | YES | NULL | FK → transactions.id |
| num_tickets | INT | - | NO | 1 | Jumlah tiket |
| total_amount | DECIMAL(12,2) | - | NO | - | Total pembayaran |
| status | ENUM | - | NO | 'registered' | registered, attended, cancelled |
| created_at | TIMESTAMP | - | YES | CURRENT_TIMESTAMP | Waktu dibuat |

---

## 23. TABEL `bookings` (Tour Guide Booking)

| Kolom | Tipe Data | Panjang | Null | Default | Keterangan |
|-------|-----------|---------|------|---------|-----------|
| id | BIGINT UNSIGNED | - | NO | AUTO_INCREMENT | Primary key |
| booking_code | VARCHAR | 30 | NO | - | Kode unik (TG-BKG-YYYYMMDD-XXX) |
| user_id | BIGINT UNSIGNED | - | NO | - | FK → users.id (wisatawan) |
| guide_id | BIGINT UNSIGNED | - | NO | - | FK → tour_guides.id |
| transaction_id | BIGINT UNSIGNED | - | YES | NULL | FK → transactions.id |
| booking_date | DATE | - | NO | - | Tanggal tour |
| start_time | TIME | - | NO | - | Jam mulai tour |
| duration_hours | DECIMAL(4,1) | - | NO | - | Durasi dalam jam |
| num_participants | INT | - | NO | 1 | Jumlah peserta |
| destination_id | BIGINT UNSIGNED | - | YES | NULL | FK → destinations.id (opsional) |
| total_amount | DECIMAL(12,2) | - | NO | - | Total pembayaran |
| status | ENUM | - | NO | 'pending' | pending, confirmed, completed, cancelled, rejected |
| notes | TEXT | - | YES | NULL | Catatan wisatawan |
| created_at | TIMESTAMP | - | YES | CURRENT_TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | - | YES | CURRENT_TIMESTAMP ON UPDATE | Waktu diupdate |

---

## 24. TABEL `transactions`

| Kolom | Tipe Data | Panjang | Null | Default | Keterangan |
|-------|-----------|---------|------|---------|-----------|
| id | BIGINT UNSIGNED | - | NO | AUTO_INCREMENT | Primary key |
| transaction_code | VARCHAR | 30 | NO | - | Kode unik (TG-TRX-YYYYMMDD-XXX) |
| user_id | BIGINT UNSIGNED | - | NO | - | FK → users.id |
| type | ENUM | - | NO | - | booking_guide, ticket, hotel, restaurant, event, refund |
| reference_id | BIGINT UNSIGNED | - | YES | NULL | ID referensi (booking/order) |
| gross_amount | DECIMAL(12,2) | - | NO | - | Amount sebelum diskon |
| discount | DECIMAL(12,2) | - | YES | 0 | Diskon |
| net_amount | DECIMAL(12,2) | - | NO | - | Amount setelah diskon |
| payment_method | ENUM | - | NO | 'transfer' | transfer, cash, e_wallet, other |
| payment_status | ENUM | - | NO | 'pending' | pending, paid, failed, refunded, expired |
| paid_at | DATETIME | - | YES | NULL | Waktu pembayaran |
| payment_proof | VARCHAR | 255 | YES | NULL | Path bukti bayar |
| notes | TEXT | - | YES | NULL | Catatan |
| created_at | TIMESTAMP | - | YES | CURRENT_TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | - | YES | CURRENT_TIMESTAMP ON UPDATE | Waktu diupdate |

---

## 25. TABEL `reviews`

| Kolom | Tipe Data | Panjang | Null | Default | Keterangan |
|-------|-----------|---------|------|---------|-----------|
| id | BIGINT UNSIGNED | - | NO | AUTO_INCREMENT | Primary key |
| user_id | BIGINT UNSIGNED | - | NO | - | FK → users.id |
| reviewable_type | ENUM | - | NO | - | guide, destination, hotel, restaurant, event |
| reviewable_id | BIGINT UNSIGNED | - | NO | - | ID entitas yang di-review |
| rating | TINYINT | - | NO | - | Rating 1-5 |
| comment | TEXT | - | YES | NULL | Komentar review |
| is_published | TINYINT(1) | - | NO | 1 | Status publish |
| created_at | TIMESTAMP | - | YES | CURRENT_TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | - | YES | CURRENT_TIMESTAMP ON UPDATE | Waktu diupdate |

---

## 26. TABEL `audio_guides`

| Kolom | Tipe Data | Panjang | Null | Default | Keterangan |
|-------|-----------|---------|------|---------|-----------|
| id | BIGINT UNSIGNED | - | NO | AUTO_INCREMENT | Primary key |
| destination_id | BIGINT UNSIGNED | - | NO | - | FK → destinations.id |
| language | VARCHAR | 10 | NO | 'id' | Kode bahasa (id, en, jp) |
| title | VARCHAR | 200 | NO | - | Judul audio |
| description | TEXT | - | YES | NULL | Deskripsi |
| file_path | VARCHAR | 255 | NO | - | Path file audio |
| duration_seconds | INT | - | YES | NULL | Durasi dalam detik |
| transcript | TEXT | - | YES | NULL | Transkrip teks |
| play_count | INT | - | YES | 0 | Jumlah diputar |
| is_active | TINYINT(1) | - | NO | 1 | Status aktif |
| created_at | TIMESTAMP | - | YES | CURRENT_TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | - | YES | CURRENT_TIMESTAMP ON UPDATE | Waktu diupdate |

---

## 27. TABEL `notifications`

| Kolom | Tipe Data | Panjang | Null | Default | Keterangan |
|-------|-----------|---------|------|---------|-----------|
| id | BIGINT UNSIGNED | - | NO | AUTO_INCREMENT | Primary key |
| user_id | BIGINT UNSIGNED | - | NO | - | FK → users.id |
| type | ENUM | - | NO | - | booking, payment, event, reminder, system, broadcast |
| title | VARCHAR | 200 | NO | - | Judul notifikasi |
| message | TEXT | - | NO | - | Isi pesan |
| link | VARCHAR | 255 | YES | NULL | URL tujuan klik |
| is_read | TINYINT(1) | - | NO | 0 | Status dibaca |
| is_email_sent | TINYINT(1) | - | NO | 0 | Status email terkirim |
| created_at | TIMESTAMP | - | YES | CURRENT_TIMESTAMP | Waktu dibuat |

---

## 28. TABEL `audit_logs`

| Kolom | Tipe Data | Panjang | Null | Default | Keterangan |
|-------|-----------|---------|------|---------|-----------|
| id | BIGINT UNSIGNED | - | NO | AUTO_INCREMENT | Primary key |
| user_id | BIGINT UNSIGNED | - | YES | NULL | FK → users.id |
| action | VARCHAR | 50 | NO | - | create, update, delete, login, logout |
| module | VARCHAR | 50 | NO | - | Modul terkait |
| description | TEXT | - | YES | NULL | Deskripsi aksi |
| ip_address | VARCHAR | 45 | YES | NULL | IP address |
| user_agent | VARCHAR | 255 | YES | NULL | Browser user agent |
| old_data | JSON | - | YES | NULL | Data sebelum perubahan |
| new_data | JSON | - | YES | NULL | Data setelah perubahan |
| created_at | TIMESTAMP | - | YES | CURRENT_TIMESTAMP | Waktu aksi |

---

## 29. TABEL `settings`

| Kolom | Tipe Data | Panjang | Null | Default | Keterangan |
|-------|-----------|---------|------|---------|-----------|
| id | BIGINT UNSIGNED | - | NO | AUTO_INCREMENT | Primary key |
| key_name | VARCHAR | 100 | NO | - | Key unik |
| value | TEXT | - | YES | NULL | Nilai setting |
| type | ENUM | - | NO | 'text' | text, number, boolean, json, image |
| description | VARCHAR | 255 | YES | NULL | Keterangan setting |
| updated_at | TIMESTAMP | - | YES | CURRENT_TIMESTAMP ON UPDATE | Waktu diupdate |

---

## 30. TABEL `chat_sessions`

| Kolom | Tipe Data | Panjang | Null | Default | Keterangan |
|-------|-----------|---------|------|---------|-----------|
| id | BIGINT UNSIGNED | - | NO | AUTO_INCREMENT | Primary key |
| user_id | BIGINT UNSIGNED | - | NO | - | FK → users.id |
| session_token | VARCHAR | 64 | NO | - | Token unik sesi |
| context | JSON | - | YES | NULL | Konteks percakapan |
| is_active | TINYINT(1) | - | NO | 1 | Status aktif |
| created_at | TIMESTAMP | - | YES | CURRENT_TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | - | YES | CURRENT_TIMESTAMP ON UPDATE | Waktu diupdate |

---

## 31. TABEL `chat_messages`

| Kolom | Tipe Data | Panjang | Null | Default | Keterangan |
|-------|-----------|---------|------|---------|-----------|
| id | BIGINT UNSIGNED | - | NO | AUTO_INCREMENT | Primary key |
| session_id | BIGINT UNSIGNED | - | NO | - | FK → chat_sessions.id |
| role | ENUM | - | NO | - | user, assistant |
| message | TEXT | - | NO | - | Isi pesan |
| metadata | JSON | - | YES | NULL | Data tambahan |
| created_at | TIMESTAMP | - | YES | CURRENT_TIMESTAMP | Waktu dibuat |

---

## 32. TABEL `rate_limits`

| Kolom | Tipe Data | Panjang | Null | Default | Keterangan |
|-------|-----------|---------|------|---------|-----------|
| id | BIGINT UNSIGNED | - | NO | AUTO_INCREMENT | Primary key |
| api_key | VARCHAR | 128 | NO | - | Key unik untuk rate limiting (contoh: `{user_id}_api`) |
| created_at | TIMESTAMP | - | YES | CURRENT_TIMESTAMP | Waktu request dicatat |

> **Catatan:** Tabel ini tidak memiliki foreign key karena bersifat independen.
> Data lama dibersihkan secara berkala (cron job) untuk mencegah pertumbuhan tak terbatas.

---

## 33. ENUM VALUES REFERENCE

| Kolom | Tabel | Values |
|-------|-------|--------|
| role | users | admin, wisatawan, tour_guide |
| status | users | active, inactive, banned, pending |
| gender | user_profiles | male, female, other |
| proficiency | guide_languages | basic, intermediate, fluent, native |
| document_type | guide_documents | ktp, sertifikat, lisensi, other |
| ticket_type | tickets | regular, child, senior, group, foreigner |
| status | ticket_orders | pending, paid, confirmed, used, cancelled, refunded |
| type | hotels | hotel, homestay, villa, guesthouse |
| status | hotel_bookings | pending, confirmed, checked_in, checked_out, cancelled |
| type | restaurants | restoran, warung, kafe, umkm, street_food |
| order_type | restaurant_orders | dine_in, pickup, delivery |
| status | restaurant_orders | pending, confirmed, preparing, ready, completed, cancelled |
| category | events | festival, seni, kuliner, olahraga, budaya, religi, other |
| status | event_registrations | registered, attended, cancelled |
| status | bookings | pending, confirmed, completed, cancelled, rejected |
| type | transactions | booking_guide, ticket, hotel, restaurant, event, refund |
| payment_method | transactions | transfer, cash, e_wallet, other |
| payment_status | transactions | pending, paid, failed, refunded, expired |
| reviewable_type | reviews | guide, destination, hotel, restaurant, event |
| type | notifications | booking, payment, event, reminder, system, broadcast |
| role | chat_messages | user, assistant |

---

> **Modul Selanjutnya:** `07_MODUL_ADMINISTRATOR.md` — Modul administrator secara lengkap
