# MODUL 30 — DIAGRAM ALUR BISNIS

> **Aplikasi:** Tour Guide Application  
> **Versi:** 1.0  
> **Tanggal:** 2026-06-30

---

## 1. RINGKASAN

Dokumen ini menyajikan diagram alur (flowchart) untuk seluruh proses bisnis
utama aplikasi menggunakan sintaks **Mermaid**. Diagram dapat dirender di
GitHub, GitLab, VS Code (dengan extension Mermaid), atau tools online seperti
mermaid.live.

---

## 2. ALUR AUTENTIKASI & REGISTRASI

### 2.1 Registrasi Wisatawan

```mermaid
flowchart TD
    A[User buka halaman register] --> B{Pilih role}
    B -->|Wisatawan| C[Isi form: name, email, password]
    C --> D{Validasi}
    D -->|Gagal| E[Tampilkan error] --> C
    D -->|Sukses| F[Cek email duplikat]
    F -->|Duplikat| G[Error: email terdaftar] --> C
    F -->|Unik| H[Hash password bcrypt]
    H --> I[Insert users: role=wisatawan, status=active]
    I --> J[Insert user_profiles]
    J --> K[Redirect ke login + notif sukses]
```

### 2.2 Registrasi Tour Guide

```mermaid
flowchart TD
    A[User buka halaman register] --> B{Pilih role}
    B -->|Tour Guide| C[Isi form: name, email, password]
    C --> D{Validasi}
    D -->|Gagal| E[Tampilkan error] --> C
    D -->|Sukses| F[Hash password bcrypt]
    F --> G[Insert users: role=tour_guide, status=pending]
    G --> H[Insert tour_guides: is_verified=0]
    H --> I[Redirect ke dashboard guide]
    I --> J[Tampilkan: Menunggu verifikasi]
    J --> K[Guide upload dokumen]
    K --> L[Admin review dokumen]
    L -->{Approve?}
    L -->|Ya| M[is_verified=1, status=active]
    L -->|Tidak| N[status=banned + alasan]
    M --> O[Notifikasi: Terverifikasi]
    N --> P[Notifikasi: Ditolak]
```

### 2.3 Login

```mermaid
flowchart TD
    A[User input email + password] --> B[Cari user by email]
    B -->{User ada?}
    B -->|Tidak| C[Error: email tidak terdaftar]
    B -->|Ya| D{password_verify}
    D -->|Tidak| E[Error: password salah]
    D -->|Ya| F{status user}
    F -->|active| G[Set session: user_id, role, name]
    F -->|banned| H[Error: akun dibanned]
    F -->|pending| I[Error: menunggu approval]
    G --> J{Redirect by role}
    J -->|admin| K[admin/dashboard]
    J -->|wisatawan| L[wisatawan/dashboard]
    J -->|tour_guide| M[tourguide/dashboard]
```

---

## 3. ALUR BOOKING TOUR GUIDE

### 3.1 Create Booking

```mermaid
flowchart TD
    A[Wisatawan pilih guide] --> B[Pilih tanggal & durasi]
    B --> C[Hitung biaya]
    C --> D{durasi >= 8 jam?}
    D -->|Ya| E[total = ceil durasi/8 × daily_rate]
    D -->|Tidak| F[total = durasi × hourly_rate]
    E --> G[Cek jadwal guide tersedia]
    F --> G
    G -->{Tersedia?}
    G -->|Tidak| H[Error: tanggal sudah dibooking]
    G -->|Ya| I[Generate booking code]
    I --> J[Insert bookings: status=pending]
    J --> K[Generate transaction code]
    K --> L[Insert transactions: payment_status=pending]
    L --> M[Update booking.transaction_id]
    M --> N[Notifikasi ke guide: Booking Baru]
    N --> O[Redirect ke halaman pembayaran]
```

### 3.2 Pembayaran & Verifikasi

```mermaid
flowchart TD
    A[Wisatawan upload bukti bayar] --> B[Simpan file ke uploads/proofs/]
    B --> C[Update transaction.payment_proof]
    C --> D[Notifikasi ke admin: Perlu verifikasi]
    D --> E[Admin buka halaman transaksi]
    E --> F{Verifikasi pembayaran}
    F -->|Valid| G[payment_status=paid]
    F -->|Tidak valid| H[payment_status=failed]
    G --> I[booking.status=confirmed]
    H --> J[Notifikasi: Pembayaran ditolak]
    I --> K[Notifikasi ke wisatawan: Dikonfirmasi]
    I --> L[Notifikasi ke guide: Dikonfirmasi]
```

### 3.3 Guide Accept/Reject

```mermaid
flowchart TD
    A[Guide buka Booking Masuk] --> B{Aksi}
    B -->|Accept| C[Update booking.status=confirmed]
    C --> D[Update guide_schedules.is_booked=1]
    D --> E[Notifikasi ke wisatawan: Dikonfirmasi]
    B -->|Reject| F[Input alasan penolakan]
    F --> G[Update booking.status=rejected]
    G --> H[Notifikasi ke wisatawan: Ditolak + alasan]
```

### 3.4 Complete Booking

```mermaid
flowchart TD
    A[Guide klik Selesaikan Tour] --> B[Update booking.status=completed]
    B --> C[Update tour_guides.total_tours + 1]
    C --> D[Notifikasi ke wisatawan: Tour selesai]
    D --> E[Wisatawan beri rating & review]
    E --> F[Insert reviews]
    F --> G[Recalculate guide.rating_avg]
```

### 3.5 Cancel Booking

```mermaid
flowchart TD
    A[User klik Cancel] --> B{Status booking}
    B -->|completed/rejected| C[Error: tidak bisa cancel]
    B -->|pending/confirmed| D[Update booking.status=cancelled]
    D --> E[Update guide_schedules.is_booked=0]
    E --> F[Notifikasi ke guide: Booking dibatalkan]
```

---

## 4. ALUR PEMBELIAN TIKET

```mermaid
flowchart TD
    A[Wisatawan pilih destinasi] --> B[Pilih jenis tiket & jumlah]
    B --> C[Pilih tanggal kunjungan]
    C --> D[Cek kuota harian]
    D -->{Kuota cukup?}
    D -->|Tidak| E[Error: kuota penuh]
    D -->|Ya| F[Hitung total = price × quantity]
    F --> G[Generate order code: TG-TKT-YYYYMMDD-XXX]
    G --> H[Insert ticket_orders: status=pending]
    H --> I[Insert ticket_order_items]
    I --> J[Create transaction: payment_status=pending]
    J --> K[Upload bukti bayar]
    K --> L[Admin verifikasi]
    L -->{Valid?}
    L -->|Ya| M[payment_status=paid, order.status=paid]
    M --> N[Generate QR code]
    N --> O[E-ticket siap]
    L -->|Tidak| P[payment_status=failed]
```

### 4.1 Verifikasi Tiket di Lokasi

```mermaid
flowchart TD
    A[Admin/Guide input/scan kode tiket] --> B[Cari ticket_order by code]
    B -->{Tiket ada?}
    B -->|Tidak| C[Error: tidak ditemukan]
    B -->|Ya| D{Status tiket}
    D -->|used| E[Error: sudah digunakan]
    D -->|paid/confirmed| F[Update status=used]
    F --> G[Sukses: tiket valid]
    D -->|pending/cancelled| H[Error: tiket tidak valid]
```

---

## 5. ALUR BOOKING HOTEL

```mermaid
flowchart TD
    A[Wisatawan cari hotel] --> B[Filter: city, type, tanggal]
    B --> C[Pilih hotel]
    C --> D[Lihat detail + kamar tersedia]
    D --> E[Pilih kamar & jumlah]
    E --> F[Input check-in & check-out]
    F --> G[Hitung nights = checkOut - checkIn]
    G --> H[total = price_per_night × num_rooms × nights]
    H --> I{Kamar cukup?}
    I -->|Tidak| J[Error: kamar tidak tersedia]
    I -->|Ya| K[Generate booking code]
    K --> L[Insert hotel_bookings: status=pending]
    L --> M[Create transaction: payment_status=pending]
    M --> N[Upload bukti bayar]
    N --> O[Admin verifikasi]
    O -->{Valid?}
    O -->|Ya| P[status=confirmed]
    O -->|Tidak| Q[status=failed]
    P --> R[Check-in → checked_in]
    R --> S[Check-out → checked_out]
    S --> T[Wisatawan review hotel]
```

---

## 6. ALUR PESAN RESTORAN

```mermaid
flowchart TD
    A[Wisatawan cari restoran] --> B[Filter: city, type, cuisine]
    B --> C[Pilih restoran]
    C --> D[Lihat menu]
    D --> E[Tambah ke keranjang]
    E --> F{Ada item lain?}
    F -->|Ya| E
    F -->|Tidak| G[Checkout]
    G --> H[Hitung total semua item]
    H --> I[Pilih order_type: dine_in/pickup/delivery]
    I --> J[Generate order code]
    J --> K[Insert restaurant_orders: status=pending]
    K --> L[Insert restaurant_order_items]
    L --> M[Create transaction]
    M --> N[Notifikasi ke pemilik restoran]
    N --> O[Pemilik update status]
    O --> P[confirmed → preparing]
    P --> Q[preparing → ready]
    Q --> R[ready → completed]
    R --> S[Notifikasi ke wisatawan setiap update]
    S --> T[Wisatawan review restoran]
```

---

## 7. ALUR EVENT & PENDAFTARAN

```mermaid
flowchart TD
    A[Admin create event] --> B[Input: title, date, location, price, quota]
    B --> C[Upload main_image]
    C --> D[Insert events: is_active=1]
    D --> E[Event tampil di kalender]
    E --> F[Wisatawan lihat event]
    F --> G[Klik Detail]
    G --> H[Klik Daftar]
    H --> I{Sudah terdaftar?}
    I -->|Ya| J[Error: sudah terdaftar]
    I -->|Tidak| K{Kuota penuh?}
    K -->|Ya| L[Error: kuota penuh]
    K -->|Tidak| M[Generate registration code]
    M --> N[Insert event_registrations: status=registered]
    N --> O[Update events.registered_count]
    O --> P{Event berbayar?}
    P -->|Ya| Q[Create transaction: payment_status=pending]
    P -->|Gratis| R[Notifikasi: Pendaftaran berhasil]
    Q --> S[Upload bukti bayar]
    S --> T[Admin verifikasi]
    T --> R
    R --> U[Cron H-1: kirim pengingat]
    U --> V[Check-in: status=attended]
```

---

## 8. ALUR AI TOUR GUIDE

```mermaid
flowchart TD
    A[User kirim pesan] --> B[Lowercase + tokenize]
    B --> C[Intent detection]
    C --> D{Keyword match}
    D -->|halo/hai| E[Response: greeting]
    D -->|rekomendasi/saran| F[Query destinations.getFeatured]
    D -->|pantai/gunung/alam| G[Query by category]
    D -->|itinerary/rencana| H[Generate 3-day itinerary]
    D -->|guide/pemandu| I[Query guides.getTopRated]
    D -->|hotel/penginapan| J[Query hotels.getTopRated]
    D -->|makan/kuliner| K[Query restaurants.getTopRated]
    D -->|event/festival| L[Query events.getUpcoming]
    D -->|tidak cocok| M[Response: fallback]
    E --> N[Save user message + AI response]
    F --> N
    G --> N
    H --> N
    I --> N
    J --> N
    K --> N
    L --> N
    M --> N
    N --> O[Return JSON response]
    O --> P[Tampilkan di chat UI]
    P --> Q{Ada quick_replies?}
    Q -->|Ya| R[Tampilkan tombol quick replies]
    Q -->|Tidak| S[Selesai]
    R --> S
```

---

## 9. ALUR NOTIFIKASI

```mermaid
flowchart TD
    A[Trigger event] --> B{Jenis trigger}
    B -->|Booking dibuat| C[Notif ke guide + wisatawan]
    B -->|Booking confirmed| D[Notif ke wisatawan]
    B -->|Booking rejected| E[Notif ke wisatawan + alasan]
    B -->|Pembayaran verified| F[Notif ke wisatawan + guide]
    B -->|Event H-1 cron| G[Notif ke peserta event]
    B -->|Admin broadcast| H[Notif ke target role]
    C --> I[Insert notifications table]
    D --> I
    E --> I
    F --> I
    G --> I
    H --> I
    I --> J{Should send email?}
    J -->|Ya: booking/payment/event| K[Send email via mail]
    J -->|Tidak| L[Skip email]
    K --> M[Update is_email_sent=1]
    L --> N[Polling 30 detik di frontend]
    M --> N
    N --> O[Update badge notifikasi]
```

---

## 10. ALUR ADMIN APPROVAL

### 10.1 Guide Approval

```mermaid
flowchart TD
    A[Guide upload dokumen] --> B[Insert guide_documents]
    B --> C[Notifikasi ke admin: Dokumen baru]
    C --> D[Admin buka halaman approval]
    D --> E[Review dokumen: KTP, sertifikat, lisensi]
    E --> F{Keputusan}
    F -->|Approve| G[tour_guides.is_verified=1]
    G --> H[users.status=active]
    H --> I[Notifikasi ke guide: Terverifikasi]
    F -->|Reject| J[users.status=banned]
    J --> K[Notifikasi ke guide: Ditolak + alasan]
```

### 10.2 Hotel/Restoran Approval

```mermaid
flowchart TD
    A[Pemilik daftar hotel/restoran] --> B[Insert: is_approved=0]
    B --> C[Notifikasi ke admin]
    C --> D[Admin review data]
    D --> E{Approve?}
    E -->|Ya| F[is_approved=1]
    F --> G[Notifikasi ke pemilik: Disetujui]
    F --> H[Tampil di search wisatawan]
    E -->|Tidak| I[Notifikasi: Ditolak + alasan]
```

---

## 11. ALUR PAYMENT VERIFICATION (ADMIN)

```mermaid
flowchart TD
    A[Admin buka Transaksi] --> B[Filter: payment_status=pending]
    B --> C[Pilih transaksi]
    C --> D[Lihat detail: bukti bayar, amount]
    D --> E{Verifikasi}
    E -->|Valid| F[payment_status=paid]
    F --> G{Type transaksi}
    G -->|booking_guide| H[booking.status=confirmed]
    G -->|ticket| I[ticket_orders.status=paid]
    G -->|hotel| J[hotel_bookings.status=confirmed]
    G -->|event| K[event_registrations.status=registered]
    G -->|restaurant| L[restaurant_orders.status=confirmed]
    H --> M[Notifikasi ke wisatawan + guide]
    I --> N[Generate QR code e-ticket]
    J --> O[Notifikasi ke wisatawan]
    K --> P[Notifikasi ke wisatawan]
    L --> Q[Notifikasi ke pemilik + wisatawan]
    E -->|Tidak valid| R[payment_status=failed]
    R --> S[Notifikasi: Pembayaran ditolak]
```

---

## 12. ALUR BACKUP DATABASE

```mermaid
flowchart TD
    A[Cron 02:00 harian] --> B[Run backup_db.sh]
    B --> C[mysqldump → gzip]
    C --> D[Simpan ke database/backup/]
    D --> E[Hapus backup > 7 hari]
    E --> F[Log ke backup.log]
    F --> G[Selesai]

    H[Admin klik Backup] --> I[BackupController::create]
    I --> J[mysqldump via system]
    J --> K[Simpan .sql]
    K --> L[Audit log: backup]
    L --> M[Tampilkan di list backup]

    N[Admin klik Restore] --> O{Konfirmasi}
    O -->|Tidak| P[Batal]
    O -->|Ya| Q[mysql < backup.sql]
    Q --> R{Return code}
    R -->|0| S[Sukses + audit log]
    R -->|!=0| T[Error: restore gagal]
```

---

## 13. ALUR SESSION MANAGEMENT

```mermaid
flowchart TD
    A[Setiap request] --> B[Session::start]
    B --> C[Set cookie: HttpOnly, Secure, SameSite]
    C --> D[Cek last_regeneration]
    D -->{> 30 menit?}
    D -->|Ya| E[session_regenerate_id true]
    D -->|Tidak| F[Cek last_activity]
    E --> F
    F -->{> 30 menit idle?}
    F -->|Ya| G[session_destroy]
    G --> H[Redirect ke login]
    F -->|Tidak| I[Update last_activity]
    I --> J[Lanjut request]
```

---

## 14. ALUR RATE LIMITING

```mermaid
flowchart TD
    A[API request masuk] --> B[RateLimiter::check]
    B --> C[Key = user_id _ api]
    C --> D[Count requests last 60 detik]
    D -->{Count >= 60?}
    D -->|Ya| E[HTTP 429: Rate limit exceeded]
    D -->|Tidak| F[Insert rate_limits record]
    F --> G[Lanjut proses request]
```

---

## 15. ALUR FILE UPLOAD

```mermaid
flowchart TD
    A[User upload file] --> B{error?}
    B -->|Ya| C[Throw: Upload error]
    B -->|Tidak| D{size > 5MB?}
    D -->|Ya| E[Throw: File terlalu besar]
    D -->|Tidak| F{Type allowed?}
    F -->|Tidak| G[Throw: Tipe tidak diizinkan]
    F -->|Ya| H[Generate filename: random 32 hex]
    H --> I[move_uploaded_file]
    I --> J{Sukses?}
    J -->|Ya| K[Return path]
    J -->|Tidak| L[Throw: Gagal menyimpan]
```

---

> **Modul Selanjutnya:** `31_KAMUS_ISTILAH_GLOSARIUM.md` — Kamus istilah lengkap
