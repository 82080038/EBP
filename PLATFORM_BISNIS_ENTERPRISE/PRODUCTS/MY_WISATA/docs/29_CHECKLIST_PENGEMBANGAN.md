# MODUL 29 — CHECKLIST PENGEMBANGAN

> **Aplikasi:** Tour Guide Application  
> **Versi:** 1.0  
> **Tanggal:** 2026-06-30

---

## 1. RINGKASAN

Dokumen ini menyediakan checklist terperinci per fase pengembangan, acceptance
criteria per modul, dan Definition of Done (DoD) untuk memastikan kualitas
pengembangan sesuai roadmap (Modul 26).

---

## 2. FASE 1 — MVP (MINGGU 1-6)

### 2.1 Minggu 1-2: Foundation

| ID | Task | Output | Status |
|----|------|--------|--------|
| F-01 | Setup struktur folder sesuai doc 04 | Struktur MVC siap | [ ] |
| F-02 | Buat `app/config/config.php` | Config app (BASE_URL, APP_NAME, dll) | [ ] |
| F-03 | Buat `app/config/database.php` | Config DB connection | [ ] |
| F-04 | Buat `app/core/Database.php` (PDO Singleton) | Koneksi DB aktif | [ ] |
| F-05 | Buat `app/core/App.php` (Router/Front Controller) | Routing berfungsi | [ ] |
| F-06 | Buat `app/core/Controller.php` (Base Controller) | Base class controller | [ ] |
| F-07 | Buat `app/core/Model.php` (Base Model) | Base class model | [ ] |
| F-08 | Buat `app/core/View.php` (View renderer) | View rendering | [ ] |
| F-09 | Buat `app/core/Middleware.php` | RBAC + Auth check | [ ] |
| F-10 | Buat `app/core/Session.php` | Session management | [ ] |
| F-11 | Buat `app/core/Helper.php` | Helper functions (e, upload, slug, email) | [ ] |
| F-12 | Buat `app/core/Validator.php` | Input validation | [ ] |
| F-13 | Buat `app/core/Logger.php` | Audit log + error log | [ ] |
| F-14 | Buat `index.php` (Entry point) | Front controller | [ ] |
| F-15 | Buat `.htaccess` | URL rewrite aktif | [ ] |
| F-16 | Buat layout: `header.php`, `footer.php`, `sidebar.php` | UI base template | [ ] |
| F-17 | Buat `AuthController` + `User` model | Login, register, logout | [ ] |
| F-18 | Buat view: `auth/login.php`, `auth/register.php` | Halaman login & register | [ ] |
| F-19 | Implementasi password hash (bcrypt) | Password aman | [ ] |
| F-20 | Implementasi session timeout (30 menit) | Auto logout | [ ] |
| F-21 | Implementasi CSRF token | Form protection | [ ] |
| F-22 | Buat `AdminController` + dashboard | Admin dashboard | [ ] |
| F-23 | Buat user management (CRUD) | Admin kelola user | [ ] |
| F-24 | Import database schema (`migration.sql`) | 33 tabel terbuat | [ ] |
| F-25 | Import seed data (`seed.sql`) | Data awal (admin, kategori) | [ ] |

### 2.2 Minggu 3-4: Tour Guide + Map + Booking

| ID | Task | Output | Status |
|----|------|--------|--------|
| TG-01 | Buat `TourGuide` model | CRUD guide data | [ ] |
| TG-02 | Buat `GuideLanguage` model | Tambah/hapus bahasa | [ ] |
| TG-03 | Buat `GuideSpecialization` model | Tambah/hapus spesialisasi | [ ] |
| TG-04 | Buat `GuideSchedule` model | Kalender ketersediaan | [ ] |
| TG-05 | Buat `GuideDocument` model | Upload dokumen verifikasi | [ ] |
| TG-06 | Buat `TourGuideController` | Controller modul guide | [ ] |
| TG-07 | Buat view: `tourguide/dashboard.php` | Dashboard guide | [ ] |
| TG-08 | Buat view: `tourguide/profile.php` | Edit profil guide | [ ] |
| TG-09 | Buat view: `tourguide/schedule.php` | Kalender jadwal | [ ] |
| TG-10 | Implementasi guide approval (admin) | Admin approve/reject guide | [ ] |
| TG-11 | Buat `MapController` | API markers + nearby | [ ] |
| TG-12 | Buat `public/assets/js/map.js` | Leaflet init + markers | [ ] |
| TG-13 | Buat view: `wisatawan/map.php` | Halaman peta | [ ] |
| TG-14 | Buat `WisatawanController` search guide | Search + filter guide | [ ] |
| TG-15 | Buat view: `wisatawan/search_guide.php` | Halaman cari guide | [ ] |
| TG-16 | Buat view: `wisatawan/guide_detail.php` | Detail profil guide | [ ] |
| BK-01 | Buat `Booking` model | CRUD booking | [ ] |
| BK-02 | Buat `Transaction` model | CRUD transaksi | [ ] |
| BK-03 | Buat `BookingController` | Create, cancel, upload proof | [ ] |
| BK-04 | Implementasi generate booking code | Format TG-BKG-YYYYMMDD-XXX | [ ] |
| BK-05 | Implementasi kalkulasi biaya | <8 jam: hourly, >=8 jam: daily | [ ] |
| BK-06 | Implementasi guide accept/reject booking | Status flow pending→confirmed | [ ] |
| BK-07 | Implementasi notifikasi booking | Notif ke wisatawan & guide | [ ] |
| BK-08 | Buat view: `wisatawan/payment.php` | Upload bukti bayar | [ ] |

### 2.3 Minggu 5-6: Tiket + Destinasi + Testing

| ID | Task | Output | Status |
|----|------|--------|--------|
| DT-01 | Buat `Destination` model | CRUD destinasi | [ ] |
| DT-02 | Buat `DestinationCategory` model | CRUD kategori | [ ] |
| DT-03 | Buat `DestinationImage` model | Upload foto destinasi | [ ] |
| DT-04 | Buat `DestinationController` (admin CRUD) | Admin kelola destinasi | [ ] |
| DT-05 | Buat view: `admin/destinations.php` | List destinasi admin | [ ] |
| DT-06 | Buat view: `wisatawan/destination_detail.php` | Detail destinasi | [ ] |
| TK-01 | Buat `Ticket` model | CRUD tiket | [ ] |
| TK-02 | Buat `TicketOrder` model | CRUD order tiket | [ ] |
| TK-03 | Buat `TicketOrderItem` model | Detail item order | [ ] |
| TK-04 | Implementasi beli tiket + quota check | Cek daily quota | [ ] |
| TK-05 | Implementasi QR code generation | Generate QR untuk e-ticket | [ ] |
| TK-06 | Implementasi verifikasi tiket | Scan/input kode → used | [ ] |
| TK-07 | Buat view: `wisatawan/e-ticket.php` | Tampilan e-ticket + QR | [ ] |
| TS-01 | Testing auth (TC-AUTH-01 s/d 10) | Semua test case lulus | [ ] |
| TS-02 | Testing booking (TC-BOOK-01 s/d 10) | Semua test case lulus | [ ] |
| TS-03 | Testing tiket (TC-TKT-01 s/d 05) | Semua test case lulus | [ ] |
| TS-04 | Testing security (TC-SEC-01 s/d 07) | Semua test case lulus | [ ] |
| TS-05 | Bug fix MVP | Tidak ada critical bug | [ ] |

---

## 3. FASE 2 — CORE FEATURES (MINGGU 7-12)

### 3.1 Minggu 7-8: Hotel & Restoran

| ID | Task | Output | Status |
|----|------|--------|--------|
| HT-01 | Buat `Hotel` model | CRUD hotel | [ ] |
| HT-02 | Buat `HotelRoom` model | CRUD kamar | [ ] |
| HT-03 | Buat `HotelBooking` model | CRUD booking hotel | [ ] |
| HT-04 | Buat `HotelController` | Register, search, book, approve | [ ] |
| HT-05 | Buat view: `wisatawan/hotel_search.php` | Search hotel | [ ] |
| HT-06 | Buat view: `wisatawan/hotel_detail.php` | Detail hotel + rooms | [ ] |
| HT-07 | Implementasi hotel approval (admin) | Admin approve hotel | [ ] |
| RS-01 | Buat `Restaurant` model | CRUD restoran | [ ] |
| RS-02 | Buat `MenuItem` model | CRUD menu | [ ] |
| RS-03 | Buat `RestaurantOrder` model | CRUD pesanan | [ ] |
| RS-04 | Buat `RestaurantOrderItem` model | Detail item pesanan | [ ] |
| RS-05 | Buat `RestaurantController` | Register, search, order, status | [ ] |
| RS-06 | Buat view: `wisatawan/restaurant_search.php` | Search restoran | [ ] |
| RS-07 | Buat view: `wisatawan/restaurant_detail.php` | Detail + menu + cart | [ ] |
| RS-08 | Implementasi keranjang + checkout (jQuery) | Cart functionality | [ ] |
| RS-09 | Implementasi restoran approval (admin) | Admin approve restoran | [ ] |

### 3.2 Minggu 9-10: Event & Notifikasi

| ID | Task | Output | Status |
|----|------|--------|--------|
| EV-01 | Buat `Event` model | CRUD event | [ ] |
| EV-02 | Buat `EventRegistration` model | CRUD pendaftaran | [ ] |
| EV-03 | Buat `EventController` | CRUD, detail, register | [ ] |
| EV-04 | Buat view: `wisatawan/events.php` | Kalender/list event | [ ] |
| EV-05 | Buat view: `wisatawan/event_detail.php` | Detail event + daftar | [ ] |
| EV-06 | Implementasi cek kuota peserta | Max participants check | [ ] |
| NF-01 | Buat `Notification` model | Send, getUnread, markRead | [ ] |
| NF-02 | Buat `NotificationController` | API endpoints notifikasi | [ ] |
| NF-03 | Implementasi badge notifikasi (polling 30s) | Real-time badge | [ ] |
| NF-04 | Implementasi email notification | mail() untuk booking/payment/event | [ ] |
| NF-05 | Implementasi broadcast (admin) | Kirim ke target role | [ ] |
| NF-06 | Buat cron: `event_reminder.php` | Notifikasi H-1 event | [ ] |

### 3.3 Minggu 11-12: Report & Dashboard

| ID | Task | Output | Status |
|----|------|--------|--------|
| RP-01 | Buat `ReportController` | Dashboard statistik | [ ] |
| RP-02 | Implementasi query total/monthly revenue | Stats pendapatan | [ ] |
| RP-03 | Implementasi query top destinations/guides | Ranking | [ ] |
| RP-04 | Buat view: `admin/reports/dashboard.php` | Dashboard report + Chart.js | [ ] |
| RP-05 | Implementasi export CSV | Download transaksi | [ ] |
| RP-06 | Implementasi pendapatan guide | Earnings view guide | [ ] |
| RP-07 | Buat view: `tourguide/earnings.php` | Halaman pendapatan guide | [ ] |

---

## 4. FASE 3 — ADVANCED (MINGGU 13-18)

### 4.1 Minggu 13-14: Audio Guide

| ID | Task | Output | Status |
|----|------|--------|--------|
| AG-01 | Buat `AudioGuide` model | CRUD audio | [ ] |
| AG-02 | Buat `AudioGuideController` | Upload, get by destination | [ ] |
| AG-03 | Buat view: `wisatawan/audio_guide.php` | Audio player + transkrip | [ ] |
| AG-04 | Implementasi upload audio (mp3/ogg/wav) | File upload + validation | [ ] |
| AG-05 | Implementasi play count tracking | Increment play count | [ ] |
| AG-06 | Buat view: `admin/audio_manage.php` | Admin kelola audio | [ ] |

### 4.2 Minggu 15-16: AI Tour Guide

| ID | Task | Output | Status |
|----|------|--------|--------|
| AI-01 | Buat `ChatSession` model | CRUD sesi chat | [ ] |
| AI-02 | Buat `ChatMessage` model | CRUD pesan chat | [ ] |
| AI-03 | Buat `AIGuideController` | Chat endpoint + processMessage | [ ] |
| AI-04 | Implementasi intent detection (keyword matching) | 10 intent categories | [ ] |
| AI-05 | Implementasi rekomendasi destinasi | Query featured destinations | [ ] |
| AI-06 | Implementasi generate itinerary | 3-day itinerary builder | [ ] |
| AI-07 | Buat view: `wisatawan/ai_chat.php` | Chat UI + quick replies | [ ] |
| AI-08 | Implementasi chat history | Load previous messages | [ ] |

### 4.3 Minggu 17-18: Review & Optimization

| ID | Task | Output | Status |
|----|------|--------|--------|
| RV-01 | Buat `Review` model | CRUD review | [ ] |
| RV-02 | Buat `ReviewController` | Create, list by entity | [ ] |
| RV-03 | Implementasi rating (1-5 stars) + comment | Review form | [ ] |
| RV-04 | Implementasi update rating_avg otomatis | Recalculate on new review | [ ] |
| RV-05 | Buat view: review form (modal/embedded) | UI submit review | [ ] |
| RV-06 | Buat view: `tourguide/reviews.php` | List review guide | [ ] |
| OP-01 | Optimasi query N+1 | Eager loading / JOIN | [ ] |
| OP-02 | Lazy loading images | `loading="lazy"` attr | [ ] |
| OP-03 | AJAX response caching (opsional) | Reduce redundant calls | [ ] |
| OP-04 | Database index optimization | Add missing indexes | [ ] |

---

## 5. FASE 4 — PRODUCTION (MINGGU 19-22)

### 5.1 Minggu 19-20: Security & Testing

| ID | Task | Output | Status |
|----|------|--------|--------|
| SC-01 | Implementasi `RateLimiter` class | Rate limiting API | [ ] |
| SC-02 | Buat tabel `rate_limits` di database | Storage rate limit | [ ] |
| SC-03 | Implementasi security headers (.htaccess) | X-Content-Type, X-Frame, dll | [ ] |
| SC-04 | Audit log untuk semua aksi penting | Audit trail | [ ] |
| SC-05 | Full test suite run (semua TC) | Semua lulus | [ ] |
| SC-06 | Performance test (Apache Bench) | <500ms API, <2s page | [ ] |
| SC-07 | Security test (OWASP checklist) | Tidak ada vuln | [ ] |
| SC-08 | Responsive test (360px - 1920px) | Layout rapi | [ ] |

### 5.2 Minggu 21: Deployment

| ID | Task | Output | Status |
|----|------|--------|--------|
| DP-01 | Setup VPS (LAMP stack) | Server siap | [ ] |
| DP-02 | Deploy code ke server | Code di server | [ ] |
| DP-03 | Konfigurasi database production | DB credentials | [ ] |
| DP-04 | Set permissions (uploads, logs, backup) | 775 writable dirs | [ ] |
| DP-05 | Konfigurasi Apache VirtualHost / Nginx | Web server config | [ ] |
| DP-06 | Install SSL (Let's Encrypt) | HTTPS aktif | [ ] |
| DP-07 | Setup cron jobs (backup, reminder) | Cron aktif | [ ] |
| DP-08 | Set `APP_ENV=production`, `APP_DEBUG=false` | Production config | [ ] |
| DP-09 | Smoke test di production | Basic flow OK | [ ] |

### 5.3 Minggu 22: Go Live

| ID | Task | Output | Status |
|----|------|--------|--------|
| GL-01 | DNS pointing ke server | Domain aktif | [ ] |
| GL-02 | Final smoke test | Semua fitur jalan | [ ] |
| GL-03 | Setup monitoring (log, uptime) | Monitoring aktif | [ ] |
| GL-04 | User acceptance test | UAT lulus | [ ] |
| GL-05 | Backup database awal | Backup tersimpan | [ ] |
| GL-06 | **GO LIVE** | Aplikasi live | [ ] |

---

## 6. ACCEPTANCE CRITERIA PER MODUL

### 6.1 Autentikasi

- [ ] Register wisatawan → langsung login
- [ ] Register guide → status pending, upload dokumen
- [ ] Login dengan email + password
- [ ] Logout → session destroyed
- [ ] Session timeout 30 menit auto logout
- [ ] Akses halaman tanpa login → redirect ke login
- [ ] Akses halaman admin sebagai wisatawan → 403

### 6.2 Tour Guide

- [ ] Guide bisa edit profil (avatar, bio, rates, lokasi)
- [ ] Guide bisa tambah/hapus bahasa & spesialisasi
- [ ] Guide bisa upload dokumen verifikasi
- [ ] Guide bisa set jadwal ketersediaan (kalender)
- [ ] Guide bisa accept/reject booking
- [ ] Guide bisa complete booking
- [ ] Guide bisa lihat pendapatan (hari/minggu/bulan/total)
- [ ] Guide bisa lihat review yang diberikan

### 6.3 Booking & Transaksi

- [ ] Wisatawan bisa booking guide (pilih tanggal, durasi, peserta)
- [ ] Kalkulasi biaya: <8 jam × hourly_rate, >=8 jam × daily_rate
- [ ] Cek ketersediaan guide di tanggal dipilih
- [ ] Generate booking code (TG-BKG-YYYYMMDD-XXX)
- [ ] Generate transaction code
- [ ] Upload bukti pembayaran
- [ ] Admin verifikasi pembayaran → status paid
- [ ] Wisatawan bisa cancel booking (pending/confirmed)
- [ ] Notifikasi terkirim ke semua pihak terkait

### 6.4 Destinasi & Tiket

- [ ] Admin bisa CRUD destinasi + kategori
- [ ] Admin bisa upload multiple foto destinasi
- [ ] Wisatawan bisa lihat detail destinasi
- [ ] Wisatawan bisa beli tiket (pilih jenis, jumlah, tanggal)
- [ ] Cek kuota harian destinasi
- [ ] Generate QR code untuk e-ticket
- [ ] Verifikasi tiket (admin/guide) → status used
- [ ] Tiket sudah dipakai → error

### 6.5 Hotel

- [ ] Pemilik bisa daftar hotel (menunggu approval)
- [ ] Admin bisa approve/reject hotel
- [ ] Wisatawan bisa search hotel (city, type)
- [ ] Wisatawan bisa lihat detail hotel + kamar
- [ ] Wisatawan bisa booking kamar (check-in/out)
- [ ] Kalkulasi: price_per_night × num_rooms × nights

### 6.6 Restoran

- [ ] Pemilik bisa daftar restoran (menunggu approval)
- [ ] Admin bisa approve/reject restoran
- [ ] Wisatawan bisa search restoran (city, type, cuisine)
- [ ] Wisatawan bisa lihat menu + tambah ke keranjang
- [ ] Checkout → create order + transaction
- [ ] Pemilik bisa update status order (preparing, ready, completed)

### 6.7 Event

- [ ] Admin bisa CRUD event
- [ ] Wisatawan bisa lihat kalender/list event
- [ ] Wisatawan bisa daftar event (bayar jika berbayar)
- [ ] Cek kuota peserta
- [ ] Notifikasi pengingat H-1 via cron

### 6.8 Map & GPS

- [ ] Peta Leaflet tampil dengan tile OSM
- [ ] Marker destinasi ter-plot dari DB
- [ ] Filter marker by kategori
- [ ] Popup info destinasi (nama, deskripsi, link)
- [ ] Geolocation user (browser API)
- [ ] Cari destinasi terdekat (Haversine)
- [ ] Marker clustering saat zoom out

### 6.9 Audio Guide

- [ ] Admin bisa upload audio per destinasi & bahasa
- [ ] Validasi format (mp3, ogg, wav) + size (max 5MB)
- [ ] Wisatawan bisa pilih bahasa → play audio
- [ ] Transkrip teks sinkron
- [ ] Play count tracking

### 6.10 AI Tour Guide

- [ ] Chatbot merespons greeting
- [ ] Rekomendasi destinasi terlaris
- [ ] Search by kategori (pantai, gunung, alam, dll)
- [ ] Rekomendasi tour guide terbaik
- [ ] Rekomendasi hotel & restoran
- [ ] Info event mendatang
- [ ] Generate itinerary 3 hari
- [ ] Quick replies button
- [ ] Chat history tersimpan

### 6.11 Notifikasi

- [ ] Notifikasi tersimpan di DB
- [ ] Badge notifikasi real-time (polling 30s)
- [ ] Tandai dibaca (single & all)
- [ ] Email notifikasi untuk booking/payment/event
- [ ] Admin broadcast ke target role

### 6.12 Report & Analytic

- [ ] Dashboard admin: total revenue, bookings, users, destinations
- [ ] Grafik tren pendapatan (Chart.js, 12 bulan)
- [ ] Top destinasi & top guides
- [ ] Export CSV transaksi
- [ ] Pendapatan guide (own earnings)

### 6.13 Security

- [ ] Password hash bcrypt
- [ ] PDO prepared statements (no SQL injection)
- [ ] CSRF token di semua form POST
- [ ] XSS escaping di semua output
- [ ] RBAC middleware per controller
- [ ] Rate limiting API (60 req/menit)
- [ ] Audit log untuk aksi penting
- [ ] Security headers (nosniff, frame-options, dll)
- [ ] File upload validation (type, size)
- [ ] Session HttpOnly + Secure

---

## 7. DEFINITION OF DONE (DoD)

### 7.1 Per Task

- [ ] Code mengikuti standar (doc 28)
- [ ] Manual test lulus
- [ ] Tidak ada error di console/log
- [ ] Responsive di mobile (360px) & desktop (1920px)
- [ ] CSRF token diterapkan (untuk POST)
- [ ] Input validation diterapkan
- [ ] Audit log untuk aksi penting

### 7.2 Per Modul

- [ ] Semua acceptance criteria terpenuhi
- [ ] Semua API endpoints berfungsi
- [ ] Semua view tampil dengan benar
- [ ] Tidak ada critical bug
- [ ] Code review approved
- [ ] Dokumentasi diperbarui jika perlu

### 7.3 Per Fase

- [ ] Semua task per fase selesai
- [ ] Testing fase lulus (TC sesuai)
- [ ] Tidak ada critical/high bug
- [ ] Demo ke stakeholder
- [ ] Sign-off dari PM/lead

---

## 8. RISK MITIGATION CHECKLIST

| Risiko | Mitigasi | Status |
|--------|----------|--------|
| Developer mundur | Dokumentasi lengkap, code review | [ ] |
| Scope creep | Patuhi MoSCoW, change request via PM | [ ] |
| Bug production | Staging environment, thorough testing | [ ] |
| DB performance | Index strategy, query optimization | [ ] |
| Security breach | Security test fase 4, OWASP checklist | [ ] |
| Server downtime | Backup harian, monitoring | [ ] |

---

> **Dokumen selesai.** Semua 29 modul dokumentasi telah didefinisikan sebagai landasan pembangunan aplikasi.
