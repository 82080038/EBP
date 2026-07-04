# MODUL 08 — MODUL WISATAWAN

> **Aplikasi:** Tour Guide Application  
> **Versi Dokumen:** 1.0  
> **Tanggal:** 2026-06-30

---

## 1. RINGKASAN MODUL

Modul Wisatawan adalah antarmuka utama untuk pengunjung yang ingin mencari dan
memesan layanan wisata: tour guide, tiket destinasi, hotel, restoran, dan event.

**Role:** `wisatawan`  
**Akses:** Dashboard, Cari Guide, Booking, Beli Tiket, Booking Hotel, Pesan Restoran,
Event, Audio Guide, AI Chat, Riwayat Transaksi, Notifikasi

---

## 2. MENU WISATAWAN

```
Navbar Wisatawan
├── Beranda
├── Cari Tour Guide
├── Destinasi Wisata (Peta)
├── Tiket Wisata
├── Hotel & Homestay
├── Restoran & UMKM
├── Event & Budaya
├── AI Tour Guide (Chat)
├── Notifikasi (badge)
└── Profil
    ├── Dashboard
    ├── Riwayat Booking
    ├── Tiket Saya
    ├── Booking Hotel Saya
    ├── Pesanan Saya
    ├── Event Saya
    ├── Pengaturan Profil
    └── Logout
```

---

## 3. DASHBOARD WISATAWAN

### 3.1 Statistik Personal

| Widget | Data |
|--------|------|
| Total Booking | Count bookings WHERE user_id = current |
| Total Tiket | Count ticket_orders WHERE user_id = current |
| Total Hotel Booking | Count hotel_bookings WHERE user_id = current |
| Total Pesanan Restoran | Count restaurant_orders WHERE user_id = current |
| Rating Diberikan | Count reviews WHERE user_id = current |
| Notifikasi Belum Dibaca | Count notifications WHERE is_read=0 |

### 3.2 Konten Dashboard

- **Booking Aktif** — Card booking dengan status pending/confirmed
- **Rekomendasi Destinasi** — Berdasarkan riwayat & lokasi
- **Event Mendatang** — Event terdekat berdasarkan tanggal
- **Notifikasi Terbaru** — 5 notifikasi terakhir

---

## 4. CARI TOUR GUIDE

### 4.1 Halaman Pencarian

**URL:** `wisatawan/search-guide`  
**Controller:** `TourGuideController::search()`  
**View:** `wisatawan/search_guide.php`

**Filter Pencarian:**

| Filter | Tipe | Opsi |
|--------|------|------|
| Lokasi/Kota | text/select | Berdasarkan city |
| Bahasa | select | id, en, jp, kr, cn |
| Spesialisasi | select | alam, budaya, sejarah, dll |
| Rating Minimum | select | 3.0, 4.0, 4.5 |
| Tarif Maks | number | Range harga |
| Tanggal Tersedia | date | Cek guide_schedules |

### 4.2 Hasil Pencarian

**Tampilan Card Guide:**

```
┌────────────────────────────────────────┐
│  [Foto]   Nama Guide                   │
│           ★ 4.8 (23 reviews)           │
│           Bahasa: ID, EN, JP           │
│           Spesialisasi: Alam, Budaya   │
│           Tarif: Rp 150.000/jam        │
│           Rp 1.200.000/hari            │
│                                       │
│           [Lihat Profil] [Booking]     │
└────────────────────────────────────────┘
```

### 4.3 Detail Profil Guide

**URL:** `wisatawan/guide-detail/{id}`  
**Controller:** `TourGuideController::detail($id)`

**Konten:**
- Foto profil, nama, rating
- Bio & pengalaman
- Bahasa yang dikuasai
- Spesialisasi
- Tarif (per jam & per hari)
- Jadwal ketersediaan (kalender)
- Review dari wisatawan sebelumnya
- Tombol "Booking Sekarang"

### 4.4 AJAX Search

```javascript
// assets/js/search-guide.js
$(document).ready(function() {
    let currentPage = 1;

    function loadGuides(page) {
        let filters = {
            city: $('#filter-city').val(),
            language: $('#filter-language').val(),
            specialization: $('#filter-spec').val(),
            min_rating: $('#filter-rating').val(),
            max_rate: $('#filter-rate').val(),
            date: $('#filter-date').val(),
            page: page
        };

        API.get('guides/search', filters, function(response) {
            let html = '';
            response.data.forEach(function(guide) {
                html += `
                <div class="col-md-6 mb-3">
                    <div class="card guide-card">
                        <div class="row g-0">
                            <div class="col-md-3">
                                <img src="${guide.avatar || DEFAULT_AVATAR}"
                                     class="img-fluid rounded-start">
                            </div>
                            <div class="col-md-9">
                                <div class="card-body">
                                    <h5>${guide.name}</h5>
                                    <p class="text-warning">
                                        ${'★'.repeat(Math.round(guide.rating_avg))}
                                        ${guide.rating_avg} (${guide.total_reviews})
                                    </p>
                                    <p class="small">${guide.languages}</p>
                                    <p class="small">Rp ${formatRupiah(guide.hourly_rate)}/jam</p>
                                    <a href="${BASE_URL}wisatawan/guide-detail/${guide.id}"
                                       class="btn btn-sm btn-outline-primary">Lihat Profil</a>
                                    <a href="${BASE_URL}wisatawan/booking-form/${guide.id}"
                                       class="btn btn-sm btn-primary">Booking</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`;
            });
            $('#guide-results').html(html);
            renderPagination(response.meta, page);
        });
    }

    $('#btn-search').click(function() {
        currentPage = 1;
        loadGuides(1);
    });

    loadGuides(1);
});
```

---

## 5. BOOKING TOUR GUIDE

### 5.1 Form Booking

**URL:** `wisatawan/booking-form/{guide_id}`  
**Controller:** `BookingController::createForm($guide_id)`

**Form Fields:**

| Field | Tipe | Validasi |
|-------|------|----------|
| guide_id | hidden | dari URL |
| booking_date | date | required, tidak boleh lewat dari hari ini |
| start_time | time | required |
| duration_hours | number | required, min 1, max 12 |
| num_participants | number | required, min 1, max 20 |
| destination_id | select | optional (pilih destinasi tujuan) |
| notes | textarea | optional |
| payment_method | select | transfer, cash |

### 5.2 Kalkulasi Biaya

```javascript
function calculateTotal() {
    let duration = parseFloat($('#duration_hours').val()) || 0;
    let hourlyRate = parseFloat($('#hourly_rate').val()) || 0;
    let dailyRate = parseFloat($('#daily_rate').val()) || 0;
    let total = 0;

    if (duration >= 8) {
        // Paket harian jika >= 8 jam
        let days = Math.ceil(duration / 8);
        total = days * dailyRate;
    } else {
        total = duration * hourlyRate;
    }

    $('#total_amount').val(total);
    $('#total_display').text('Rp ' + formatRupiah(total));
}

$('#duration_hours').on('input', calculateTotal);
```

### 5.3 Submit Booking

```javascript
$('#btn-submit-booking').click(function() {
    let data = {
        guide_id: $('#guide_id').val(),
        booking_date: $('#booking_date').val(),
        start_time: $('#start_time').val(),
        duration_hours: $('#duration_hours').val(),
        num_participants: $('#num_participants').val(),
        destination_id: $('#destination_id').val(),
        notes: $('#notes').val(),
        payment_method: $('#payment_method').val(),
        csrf_token: CSRF_TOKEN
    };

    API.post('booking/create', data, function(response) {
        Swal.fire({
            title: 'Booking Berhasil!',
            text: 'Kode booking: ' + response.data.booking_code,
            icon: 'success',
            confirmButtonText: 'Lihat Booking'
        }).then(() => {
            window.location.href = BASE_URL + 'wisatawan/my-bookings';
        });
    });
});
```

### 5.4 Flow Booking

```
1. Wisatawan pilih guide → klik "Booking"
2. Isi form booking → kalkulasi otomatis
3. Submit → AJAX POST api/booking/create
4. System create booking (status=pending) + transaction (status=pending)
5. Redirect ke halaman pembayaran
6. Upload bukti transfer (opsional)
7. Admin verifikasi → booking confirmed
8. Notifikasi ke wisatawan & guide
```

---

## 6. TIKET WISATA

### 6.1 Daftar Destinasi

**URL:** `wisatawan/destinations`

**Tampilan:**
- Grid card destinasi dengan foto
- Filter kategori (alam, budaya, sejarah, pantai, dll)
- Filter kota
- Search by nama

### 6.2 Detail Destinasi

**URL:** `wisatawan/destination-detail/{id}`

**Konten:**
- Galeri foto
- Deskripsi lengkap
- Info: harga tiket, jam buka, alamat
- Peta lokasi (Leaflet mini map)
- Audio guide player (jika tersedia)
- Review & rating
- Tombol "Beli Tiket"

### 6.3 Beli Tiket

**URL:** `wisatawan/buy-ticket/{destination_id}`

| Field | Tipe | Validasi |
|-------|------|----------|
| visit_date | date | required, tidak boleh lewat |
| ticket_type | select | dari tabel tickets |
| quantity | number | min 1, max daily_quota |

**Flow:**
1. Pilih tanggal kunjungan & jenis tiket
2. Input jumlah tiket
3. Sistem cek kuota harian
4. Hitung total → create ticket_order + transaction
5. Upload bukti pembayaran
6. Generate QR code e-ticket
7. Tampil di "Tiket Saya"

---

## 7. HOTEL & HOMESTAY

### 7.1 Pencarian Hotel

**URL:** `wisatawan/hotels`

**Filter:**

| Filter | Tipe |
|--------|------|
| Kota | select |
| Tanggal check-in | date |
| Tanggal check-out | date |
| Jumlah kamar | number |
| Tipe akomodasi | select (hotel, homestay, villa, guesthouse) |
| Harga range | number min-max |

### 7.2 Detail Hotel

**Konten:**
- Foto hotel
- Deskripsi, fasilitas
- Daftar tipe kamar dengan harga
- Ketersediaan kamar
- Peta lokasi
- Review
- Tombol "Booking Kamar"

### 7.3 Booking Hotel

| Field | Tipe | Validasi |
|-------|------|----------|
| room_id | hidden | dari pilihan |
| check_in | date | required |
| check_out | date | required, after check_in |
| num_rooms | number | min 1, max available_rooms |

**Kalkulasi:**
```
num_nights = check_out - check_in (in days)
total = num_rooms * num_nights * price_per_night
```

---

## 8. RESTORAN & UMKM

### 8.1 Pencarian Restoran

**URL:** `wisatawan/restaurants`

**Filter:**

| Filter | Tipe |
|--------|------|
| Kota | select |
| Jenis kuliner | text |
| Tipe (restoran, warung, kafe, UMKM) | select |
| Rating minimum | select |

### 8.2 Detail Restoran

**Konten:**
- Foto restoran
- Menu lengkap dengan harga
- Jam buka/tutup
- Peta lokasi
- Review
- Tombol "Pesan Makanan"

### 8.3 Pesan Makanan

**Flow:**
1. Pilih menu → tambah ke keranjang
2. Pilih tipe pesanan: dine_in, pickup, delivery
3. Submit → create restaurant_order + transaction
4. Restoran konfirmasi → preparing → ready
5. Notifikasi setiap status berubah

---

## 9. EVENT & BUDAYA

### 9.1 Kalender Event

**URL:** `wisatawan/events`

**Tampilan:**
- Kalender bulanan dengan marker event
- List event mendatang
- Filter kategori
- Search by nama

### 9.2 Detail Event

**Konten:**
- Foto, judul, deskripsi
- Tanggal & jam
- Lokasi + peta
- Harga tiket
- Kuota tersisa
- Tombol "Daftar Event"

### 9.3 Daftar Event

**Flow:**
1. Klik "Daftar" → input jumlah tiket
2. Hitung total → create event_registration + transaction
3. Pembayaran → confirmed
4. Notifikasi pengingat H-1

---

## 10. AUDIO GUIDE

### 10.1 Player Audio

**URL:** `wisatawan/audio-guide/{destination_id}`

**Fitur:**
- Pilih bahasa (id, en, jp)
- Audio player HTML5 (play, pause, stop, volume, seek)
- Transkrip teks (sync dengan audio)
- Download audio (opsional)

```html
<audio id="audio-player" controls>
    <source src="<?= BASE_URL ?>public/uploads/audio/<?= $audio->file_path ?>" type="audio/mpeg">
    Browser tidak mendukung audio.
</audio>
```

---

## 11. AI TOUR GUIDE (CHAT)

### 11.1 Chat Interface

**URL:** `wisatawan/ai-chat`

**UI:**
- Chat window (seperti WhatsApp/messenger)
- Input box di bawah
- Quick reply buttons: "Rekomendasi destinasi", "Buat itinerary", "FAQ"

**Flow:**
1. Wisatawan buka chat
2. Sistem create/retrieve chat_session
3. Wisatawan kirim pesan
4. AI engine proses (rule-based + keyword matching)
5. AI response dengan rekomendasi
6. Tampilkan di chat window

---

## 12. RIWAYAT TRANSAKSI

### 12.1 Riwayat Booking Guide

**URL:** `wisatawan/my-bookings`

**Tampilan:**
- Tabel booking dengan status
- Filter status (pending, confirmed, completed, cancelled)
- Tombol: Lihat Detail, Upload Bukti Bayar, Batalkan
- Review form (jika status=completed)

### 12.2 Tiket Saya

**URL:** `wisatawan/my-tickets`

**Tampilan:**
- Card tiket dengan QR code
- Status: pending, paid, confirmed, used
- Download e-ticket

### 12.3 Booking Hotel Saya

**URL:** `wisatawan/my-hotel-bookings`

### 12.4 Pesanan Restoran Saya

**URL:** `wisatawan/my-orders`

### 12.5 Event Saya

**URL:** `wisatawan/my-events`

---

## 13. RATING & REVIEW

### 13.1 Form Review

**Tampil setelah booking status=completed:**

| Field | Tipe | Validasi |
|-------|------|----------|
| rating | star (1-5) | required |
| comment | textarea | optional, max 1000 |

**Submit:**
```javascript
function submitReview(type, id) {
    let data = {
        reviewable_type: type,    // guide, destination, hotel, restaurant
        reviewable_id: id,
        rating: $('#rating').val(),
        comment: $('#comment').val(),
        csrf_token: CSRF_TOKEN
    };
    API.post('review/create', data, function(response) {
        Swal.fire('Terima kasih!', 'Review berhasil dikirim', 'success');
        // Update rating_avg & total_reviews di tabel terkait
    });
}
```

---

## 14. PENGATURAN PROFIL

### 14.1 Edit Profil

| Field | Tipe |
|-------|------|
| name | text |
| phone | text |
| avatar | file upload |
| first_name | text |
| last_name | text |
| birth_date | date |
| gender | select |
| nationality | text |
| address | textarea |
| city | text |
| province | text |
| postal_code | text |

### 14.2 Ganti Password

| Field | Tipe | Validasi |
|-------|------|----------|
| current_password | password | required, verify with password_verify |
| new_password | password | required, min 8 char |
| confirm_password | password | must match new_password |

---

## 15. CONTROLLER: WisatawanController

```php
<?php
class DashboardController extends Controller {

    public function __construct() {
        Middleware::requireRole(['wisatawan', 'admin']);
    }

    public function index() {
        $bookingModel = $this->model('Booking');
        $ticketModel = $this->model('TicketOrder');
        $notifModel = $this->model('Notification');

        $userId = $_SESSION['user_id'];

        $this->view('wisatawan/dashboard', [
            'title' => 'Dashboard Wisatawan',
            'stats' => [
                'total_bookings' => count($bookingModel->all(['user_id' => $userId])),
                'total_tickets' => count($ticketModel->all(['user_id' => $userId])),
                'unread_notifs' => count($notifModel->all(['user_id' => $userId, 'is_read' => 0]))
            ],
            'active_bookings' => $bookingModel->getActiveByUser($userId),
            'recent_notifs' => $notifModel->getRecentByUser($userId, 5)
        ]);
    }
}
```

---

## 16. VIEW: Dashboard Wisatawan

```php
<!-- app/views/wisatawan/dashboard.php -->
<?php include 'app/views/layouts/header.php'; ?>

<div class="container mt-4">
    <h2>Selamat datang, <?= $_SESSION['name'] ?>!</h2>

    <div class="row mb-4 mt-3">
        <div class="col-md-4 mb-2">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5>Total Booking Guide</h5>
                    <h3><?= $stats['total_bookings'] ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-2">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5>Total Tiket</h5>
                    <h3><?= $stats['total_tickets'] ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-2">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5>Notifikasi</h5>
                    <h3><?= $stats['unread_notifs'] ?> belum dibaca</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <h4>Booking Aktif</h4>
            <?php if (empty($active_bookings)): ?>
                <p class="text-muted">Belum ada booking aktif.</p>
                <a href="<?= BASE_URL ?>wisatawan/search-guide" class="btn btn-primary">
                    Cari Tour Guide
                </a>
            <?php else: ?>
                <?php foreach ($active_bookings as $booking): ?>
                    <div class="card mb-2">
                        <div class="card-body">
                            <h5><?= $booking['booking_code'] ?></h5>
                            <p>Tanggal: <?= $booking['booking_date'] ?></p>
                            <span class="badge bg-<?= $booking['status'] == 'confirmed' ? 'success' : 'warning' ?>">
                                <?= ucfirst($booking['status']) ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="col-md-4">
            <h4>Notifikasi Terbaru</h4>
            <?php foreach ($recent_notifs as $notif): ?>
                <div class="alert alert-light border">
                    <strong><?= $notif['title'] ?></strong><br>
                    <small><?= $notif['message'] ?></small>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php include 'app/views/layouts/footer.php'; ?>
```

---

> **Modul Selanjutnya:** `09_MODUL_TOUR_GUIDE.md` — Modul tour guide secara lengkap
