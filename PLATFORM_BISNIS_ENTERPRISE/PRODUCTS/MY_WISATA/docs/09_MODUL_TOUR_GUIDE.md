# MODUL 09 — MODUL TOUR GUIDE

> **Aplikasi:** Tour Guide Application  
> **Versi Dokumen:** 1.0  
> **Tanggal:** 2026-06-30

---

## 1. RINGKASAN MODUL

Modul Tour Guide adalah antarmuka untuk pemandu wisata dalam mengelola profil,
ketersediaan jadwal, menerima booking, dan melihat pendapatan.

**Role:** `tour_guide`  
**Akses:** Dashboard, Profil Guide, Jadwal, Booking Masuk, Pendapatan, Notifikasi

---

## 2. MENU TOUR GUIDE

```
Sidebar Tour Guide
├── Dashboard
├── Profil Saya
│   ├── Edit Profil
│   ├── Bahasa & Spesialisasi
│   └── Dokumen Verifikasi
├── Jadwal Ketersediaan
├── Booking Masuk
│   ├── Permintaan Baru (pending)
│   ├── Booking Aktif (confirmed)
│   └── Riwayat (completed/cancelled)
├── Pendapatan
│   ├── Ringkasan
│   └── Riwayat Penarikan
├── Review Saya
├── Notifikasi
└── Pengaturan Akun
```

---

## 3. DASHBOARD TOUR GUIDE

### 3.1 Statistik

| Widget | Data |
|--------|------|
| Booking Aktif | Count bookings WHERE guide_id=current AND status=confirmed |
| Permintaan Baru | Count bookings WHERE status=pending |
| Total Tour Selesai | Count bookings WHERE status=completed |
| Rating Rata-rata | rating_avg dari tour_guides |
| Pendapatan Bulan Ini | Sum net_amount dari transactions type=booking_guide |
| Jadwal Hari Ini | Cek guide_schedules tanggal hari ini |

### 3.2 Konten

- **Permintaan Booking Baru** — List booking pending dengan tombol Accept/Reject
- **Jadwal Hari Ini** — List booking confirmed untuk hari ini
- **Review Terbaru** — 5 review terakhir
- **Grafik Pendapatan** — Bar chart 6 bulan terakhir

---

## 4. PROFIL GUIDE

### 4.1 Edit Profil Utama

**URL:** `tourguide/profile`  
**Controller:** `TourGuideController::profile()`

| Field | Tipe | Validasi |
|-------|------|----------|
| avatar | file | image, max 2MB |
| name | text | required, max 100 |
| phone | text | required |
| bio | textarea | max 1000 |
| license_number | text | optional |
| experience_years | number | min 0 |
| hourly_rate | number | required, min 0 |
| daily_rate | number | required, min 0 |
| latitude | hidden | auto dari GPS atau peta |
| longitude | hidden | auto dari GPS atau peta |
| is_available | checkbox | default checked |

### 4.2 Bahasa & Spesialisasi

**URL:** `tourguide/profile/skills`

**Bahasa:**
- Tambah bahasa (select: Indonesia, English, Japanese, Korean, Mandarin, dll)
- Pilih proficiency (basic, intermediate, fluent, native)
- Hapus bahasa

**Spesialisasi:**
- Tambah spesialisasi (select: Alam, Budaya, Sejarah, Kuliner, Petualangan, Fotografi, dll)
- Hapus spesialisasi

**AJAX:**
```javascript
function addLanguage() {
    let data = {
        guide_id: GUIDE_ID,
        language: $('#language').val(),
        proficiency: $('#proficiency').val()
    };
    API.post('guide/add-language', data, function(response) {
        $('#languages-list').append(`
            <span class="badge bg-info me-1">
                ${data.language} (${data.proficiency})
                <button onclick="removeLanguage(${response.data.id})" class="btn-close btn-close-white ms-1"></button>
            </span>
        `);
    });
}
```

### 4.3 Dokumen Verifikasi

**URL:** `tourguide/profile/documents`

| Field | Tipe | Validasi |
|-------|------|----------|
| document_type | select | ktp, sertifikat, lisensi, other |
| file_path | file | image/pdf, max 5MB |

**Status:**
- `is_verified=0` → "Menunggu Verifikasi Admin"
- `is_verified=1` → "Terverifikasi" (badge hijau)
- Jika ditolak → "Ditolak" + alasan + tombol upload ulang

---

## 5. JADWAL KETERSEDIAAN

### 5.1 Kalender Ketersediaan

**URL:** `tourguide/schedule`  
**Controller:** `TourGuideController::schedule()`

**UI:**
- Kalender bulanan (FullCalendar atau custom)
- Klik tanggal → toggle available/booked
- Bulk action: "Tersedia setiap Senin-Jumat", "Tidak tersedia akhir pekan"

### 5.2 Tambah Jadwal

| Field | Tipe | Validasi |
|-------|------|----------|
| available_date | date | required, tidak boleh lewat |
| start_time | time | default 08:00 |
| end_time | time | default 17:00, after start_time |
| notes | text | optional |

### 5.3 AJAX Toggle

```javascript
function toggleDate(dateStr) {
    API.post('guide/toggle-schedule', { date: dateStr }, function(response) {
        if (response.data.available) {
            $('#cal-' + dateStr).addClass('bg-success').removeClass('bg-danger');
        } else {
            $('#cal-' + dateStr).addClass('bg-danger').removeClass('bg-success');
        }
    });
}
```

### 5.4 Model: GuideSchedule

```php
<?php
class GuideSchedule extends Model {
    protected $table = 'guide_schedules';

    public function getByGuideAndDate($guideId, $date) {
        $sql = "SELECT * FROM {$this->table}
                WHERE guide_id = :guide_id AND available_date = :date";
        return $this->db->query($sql, ['guide_id' => $guideId, 'date' => $date])->fetch();
    }

    public function getAvailableDates($guideId, $month, $year) {
        $sql = "SELECT available_date FROM {$this->table}
                WHERE guide_id = :guide_id
                AND MONTH(available_date) = :month
                AND YEAR(available_date) = :year
                AND is_booked = 0";
        return $this->db->query($sql, [
            'guide_id' => $guideId, 'month' => $month, 'year' => $year
        ])->fetchAll();
    }
}
```

---

## 6. BOOKING MASUK

### 6.1 Permintaan Baru (Pending)

**URL:** `tourguide/bookings/pending`

**Tampilan:**
- List booking dengan status=pending
- Card: Kode, Nama Wisatawan, Tanggal, Durasi, Jumlah Peserta, Total
- Tombol: **Accept** (hijau) / **Reject** (merah)

**Accept Flow:**
```
1. Guide klik "Accept"
2. SweetAlert2: "Terima booking ini?"
3. AJAX POST api/guide/accept-booking { booking_id }
4. Update booking SET status=confirmed
5. Update guide_schedules SET is_booked=1 untuk tanggal tersebut
6. Kirim notifikasi ke wisatawan: "Booking dikonfirmasi oleh guide"
7. Refresh list
```

**Reject Flow:**
```
1. Guide klik "Reject"
2. SweetAlert2 dengan input alasan
3. AJAX POST api/guide/reject-booking { booking_id, reason }
4. Update booking SET status=rejected
5. Kirim notifikasi ke wisatawan: "Booking ditolak: {reason}"
6. Refresh list
```

### 6.2 Booking Aktif (Confirmed)

**URL:** `tourguide/bookings/active`

**Tampilan:**
- List booking confirmed yang belum selesai
- Detail: tanggal, jam, lokasi, kontak wisatawan
- Tombol: "Selesaikan Tour" (set status=completed)

### 6.3 Riwayat Booking

**URL:** `tourguide/bookings/history`

**Tampilan:**
- DataTables dengan semua booking (completed, cancelled, rejected)
- Filter status & tanggal
- Kolom: Kode, Wisatawan, Tanggal, Status, Rating Diberikan

---

## 7. PENDAPATAN

### 7.1 Ringkasan Pendapatan

**URL:** `tourguide/earnings`

**Statistik:**
- Pendapatan Hari Ini
- Pendapatan Minggu Ini
- Pendapatan Bulan Ini
- Pendapatan Total (all time)
- Booking Selesai Bulan Ini

**Grafik:**
- Line chart pendapatan 6 bulan terakhir (Chart.js)

### 7.2 Riwayat Transaksi

**URL:** `tourguide/earnings/history`

**Tampilan:**
- Tabel transaksi dengan type=booking_guide
- Kolom: Kode, Tanggal, Wisatawan, Amount, Status Pembayaran
- Filter tanggal
- Export CSV

### 7.3 Model: Earnings

```php
<?php
// Di TourGuide model
public function getEarnings($guideId, $period = 'month') {
    $sql = "SELECT t.*, b.booking_code, u.name as wisatawan_name
            FROM transactions t
            JOIN bookings b ON t.reference_id = b.id
            JOIN users u ON b.user_id = u.id
            WHERE b.guide_id = :guide_id
            AND t.type = 'booking_guide'
            AND t.payment_status = 'paid'";

    if ($period === 'month') {
        $sql .= " AND MONTH(t.created_at) = MONTH(CURRENT_DATE())
                  AND YEAR(t.created_at) = YEAR(CURRENT_DATE())";
    } elseif ($period === 'week') {
        $sql .= " AND t.created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 7 DAY)";
    }

    $sql .= " ORDER BY t.created_at DESC";
    return $this->db->query($sql, ['guide_id' => $guideId])->fetchAll();
}

public function getTotalEarnings($guideId) {
    $sql = "SELECT COALESCE(SUM(t.net_amount), 0) as total
            FROM transactions t
            JOIN bookings b ON t.reference_id = b.id
            WHERE b.guide_id = :guide_id
            AND t.type = 'booking_guide'
            AND t.payment_status = 'paid'";
    return $this->db->query($sql, ['guide_id' => $guideId])->fetch()['total'];
}
```

---

## 8. REVIEW SAYA

### 8.1 Daftar Review

**URL:** `tourguide/reviews`

**Tampilan:**
- List review dengan rating & komentar
- Filter rating (1-5 stars)
- Rata-rata rating di atas
- Total review

---

## 9. CONTROLLER: TourGuideDashboardController

```php
<?php
class TourGuideController extends Controller {

    public function __construct() {
        Middleware::requireRole('tour_guide');
    }

    public function dashboard() {
        $guideModel = $this->model('TourGuide');
        $bookingModel = $this->model('Booking');
        $guide = $guideModel->findByUserId($_SESSION['user_id']);

        $this->view('tourguide/dashboard', [
            'title' => 'Dashboard Tour Guide',
            'guide' => $guide,
            'pending_bookings' => $bookingModel->getByGuide($guide['id'], 'pending'),
            'active_bookings' => $bookingModel->getByGuide($guide['id'], 'confirmed'),
            'stats' => [
                'total_completed' => $bookingModel->countByGuide($guide['id'], 'completed'),
                'total_earnings' => $guideModel->getTotalEarnings($guide['id']),
                'monthly_earnings' => $guideModel->getEarnings($guide['id'], 'month'),
                'rating' => $guide['rating_avg']
            ]
        ]);
    }

    public function acceptBooking($bookingId) {
        $bookingModel = $this->model('Booking');
        $booking = $bookingModel->find($bookingId);

        // Verify ownership
        $guideModel = $this->model('TourGuide');
        $guide = $guideModel->findByUserId($_SESSION['user_id']);
        if ($booking['guide_id'] != $guide['id']) {
            $this->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $bookingModel->update($bookingId, ['status' => 'confirmed']);

        // Mark schedule as booked
        $scheduleModel = $this->model('GuideSchedule');
        $scheduleModel->markBooked($guide['id'], $booking['booking_date']);

        // Notify wisatawan
        $notifModel = $this->model('Notification');
        $notifModel->insert([
            'user_id' => $booking['user_id'],
            'type' => 'booking',
            'title' => 'Booking Dikonfirmasi',
            'message' => "Guide {$_SESSION['name']} mengkonfirmasi booking {$booking['booking_code']}",
            'link' => 'wisatawan/my-bookings'
        ]);

        Logger::audit('accept_booking', 'bookings', "Accepted booking #{$bookingId}");
        $this->json(['status' => 'success', 'message' => 'Booking diterima']);
    }

    public function rejectBooking($bookingId) {
        $input = json_decode(file_get_contents('php://input'), true);
        $reason = $input['reason'] ?? '';

        $bookingModel = $this->model('Booking');
        $booking = $bookingModel->find($bookingId);

        $guideModel = $this->model('TourGuide');
        $guide = $guideModel->findByUserId($_SESSION['user_id']);
        if ($booking['guide_id'] != $guide['id']) {
            $this->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $bookingModel->update($bookingId, ['status' => 'rejected']);

        $notifModel = $this->model('Notification');
        $notifModel->insert([
            'user_id' => $booking['user_id'],
            'type' => 'booking',
            'title' => 'Booking Ditolak',
            'message' => "Booking {$booking['booking_code']} ditolak. Alasan: {$reason}",
            'link' => 'wisatawan/my-bookings'
        ]);

        Logger::audit('reject_booking', 'bookings', "Rejected booking #{$bookingId}");
        $this->json(['status' => 'success', 'message' => 'Booking ditolak']);
    }
}
```

---

## 10. VIEW: Dashboard Tour Guide

```php
<!-- app/views/tourguide/dashboard.php -->
<?php include 'app/views/layouts/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include 'app/views/layouts/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <h1 class="h2 mt-3">Dashboard Tour Guide</h1>

            <?php if (!$guide['is_verified']): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-clock"></i>
                    Profil Anda menunggu verifikasi admin.
                    Upload dokumen verifikasi untuk mempercepat proses.
                </div>
            <?php endif; ?>

            <!-- Stats -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <h6>Booking Aktif</h6>
                            <h3><?= count($active_bookings) ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-warning">
                        <div class="card-body">
                            <h6>Permintaan Baru</h6>
                            <h3><?= count($pending_bookings) ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <h6>Pendapatan Bulan Ini</h6>
                            <h3>Rp <?= number_format($stats['monthly_earnings'], 0, ',', '.') ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-info">
                        <div class="card-body">
                            <h6>Rating</h6>
                            <h3><?= $stats['rating'] ?> ★</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Bookings -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between">
                    <h5>Permintaan Booking Baru</h5>
                    <span class="badge bg-danger"><?= count($pending_bookings) ?></span>
                </div>
                <div class="card-body">
                    <?php if (empty($pending_bookings)): ?>
                        <p class="text-muted">Tidak ada permintaan baru.</p>
                    <?php else: ?>
                        <?php foreach ($pending_bookings as $booking): ?>
                            <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                <div>
                                    <strong><?= $booking['booking_code'] ?></strong><br>
                                    <small><?= $booking['booking_date'] ?> · <?= $booking['duration_hours'] ?> jam · <?= $booking['num_participants'] ?> peserta</small><br>
                                    <strong>Rp <?= number_format($booking['total_amount'], 0, ',', '.') ?></strong>
                                </div>
                                <div>
                                    <button class="btn btn-sm btn-success btn-accept"
                                            data-id="<?= $booking['id'] ?>">
                                        <i class="fas fa-check"></i> Terima
                                    </button>
                                    <button class="btn btn-sm btn-danger btn-reject"
                                            data-id="<?= $booking['id'] ?>">
                                        <i class="fas fa-times"></i> Tolak
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.btn-accept').click(function() {
        let id = $(this).data('id');
        Swal.fire({
            title: 'Terima booking ini?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Terima'
        }).then((result) => {
            if (result.isConfirmed) {
                API.post('guide/accept-booking', { booking_id: id }, function(res) {
                    Swal.fire('Berhasil!', res.message, 'success').then(() => location.reload());
                });
            }
        });
    });

    $('.btn-reject').click(function() {
        let id = $(this).data('id');
        Swal.fire({
            title: 'Tolak booking ini?',
            input: 'textarea',
            inputPlaceholder: 'Alasan penolakan',
            showCancelButton: true,
            confirmButtonText: 'Tolak'
        }).then((result) => {
            if (result.isConfirmed) {
                API.post('guide/reject-booking', { booking_id: id, reason: result.value }, function(res) {
                    Swal.fire('Berhasil', res.message, 'success').then(() => location.reload());
                });
            }
        });
    });
});
</script>

<?php include 'app/views/layouts/footer.php'; ?>
```

---

## 11. API ENDPOINTS — TOUR GUIDE

| Method | URL | Fungsi |
|--------|-----|--------|
| GET | `api/guide/profile` | Get profil guide |
| POST | `api/guide/update-profile` | Update profil |
| POST | `api/guide/add-language` | Tambah bahasa |
| POST | `api/guide/remove-language` | Hapus bahasa |
| POST | `api/guide/add-specialization` | Tambah spesialisasi |
| POST | `api/guide/remove-specialization` | Hapus spesialisasi |
| POST | `api/guide/upload-document` | Upload dokumen verifikasi |
| GET | `api/guide/schedule/{month}/{year}` | Get jadwal bulanan |
| POST | `api/guide/add-schedule` | Tambah jadwal tersedia |
| POST | `api/guide/toggle-schedule` | Toggle tersedia/tidak |
| GET | `api/guide/bookings/{status}` | Get booking by status |
| POST | `api/guide/accept-booking` | Accept booking |
| POST | `api/guide/reject-booking` | Reject booking |
| POST | `api/guide/complete-booking` | Selesaikan tour |
| GET | `api/guide/earnings/{period}` | Get pendapatan |
| GET | `api/guide/reviews` | Get review guide |

---

> **Modul Selanjutnya:** `10_MODUL_MAP_GPS_OPENSTREETMAP.md` — Modul peta & GPS
