# MODUL 07 — MODUL ADMINISTRATOR

> **Aplikasi:** Tour Guide Application  
> **Versi Dokumen:** 1.0  
> **Tanggal:** 2026-06-30

---

## 1. RINGKASAN MODUL

Modul Administrator adalah panel kontrol utama untuk mengelola seluruh aspek
aplikasi: pengguna, tour guide, destinasi, transaksi, konten, dan laporan.

**Role:** `admin`  
**Akses:** Dashboard, User Management, Guide Approval, Destinasi, Tiket, Hotel,
Restoran, Event, Audio Guide, AI Knowledge Base, Transaksi, Laporan, Settings

---

## 2. MENU ADMINISTRATOR

```
Sidebar Admin
├── Dashboard
├── Manajemen Pengguna
│   ├── Daftar Pengguna
│   ├── Tambah/Edit Pengguna
│   └── Ban/Unban Pengguna
├── Tour Guide
│   ├── Daftar Guide
│   ├── Approval Guide (pending verification)
│   └── Dokumen Verifikasi
├── Destinasi Wisata
│   ├── Daftar Destinasi
│   ├── Tambah/Edit Destinasi
│   ├── Kategori Destinasi
│   └── Kelola Tiket
├── Hotel & Homestay
│   ├── Daftar Hotel
│   └── Approval Hotel
├── Restoran & UMKM
│   ├── Daftar Restoran
│   └── Approval Restoran
├── Event & Budaya
│   └── Daftar Event
├── Audio Guide
│   └── Kelola Audio
├── AI Tour Guide
│   └── Knowledge Base
├── Transaksi
│   ├── Semua Transaksi
│   └── Verifikasi Pembayaran
├── Laporan & Analitik
│   ├── Dashboard Statistik
│   ├── Laporan Transaksi
│   └── Export Laporan
├── Notifikasi
│   └── Broadcast
├── Pengaturan
│   ├── Konfigurasi Sistem
│   └── Audit Log
└── Backup Database
```

---

## 3. DASHBOARD ADMIN

### 3.1 Statistik Ringkasan

| Widget | Data | Sumber |
|--------|------|--------|
| Total Pengguna | Count users WHERE status='active' | users |
| Total Tour Guide | Count tour_guides WHERE is_verified=1 | tour_guides |
| Total Destinasi | Count destinations WHERE is_active=1 | destinations |
| Total Transaksi | Sum transactions WHERE payment_status='paid' | transactions |
| Pendapatan Bulan Ini | Sum net_amount bulan current | transactions |
| Booking Bulan Ini | Count bookings bulan current | bookings |
| Guide Pending Approval | Count tour_guides WHERE is_verified=0 | tour_guides |
| Hotel Pending Approval | Count hotels WHERE is_approved=0 | hotels |

### 3.2 Grafik

- **Grafik Tren Booking** — Line chart 30 hari terakhir (Chart.js)
- **Grafik Pendapatan** — Bar chart 12 bulan terakhir
- **Destinasi Terlaris** — Top 5 berdasarkan ticket_orders
- **Guide Terbaik** — Top 5 berdasarkan rating & total_tours

---

## 4. MANAJEMEN PENGGUNA

### 4.1 Daftar Pengguna

**URL:** `admin/users`  
**Controller:** `UserController::index()`  
**View:** `admin/users/index.php`

**Fitur:**
- DataTables dengan pagination, search, sort
- Filter by role (admin, wisatawan, tour_guide)
- Filter by status (active, inactive, banned, pending)
- Kolom: ID, Nama, Email, Role, Status, Tanggal Daftar, Aksi

### 4.2 Tambah/Edit Pengguna

**Form Fields:**

| Field | Tipe | Validasi |
|-------|------|----------|
| name | text | required, max 100 |
| email | email | required, unique |
| phone | text | optional, max 20 |
| role | select | required (admin, wisatawan, tour_guide) |
| status | select | required (active, inactive, banned) |
| password | password | required (min 8 char) saat tambah |

### 4.3 Aksi Pengguna

| Aksi | Method | Konfirmasi |
|------|--------|------------|
| Edit | GET edit + POST update | - |
| Ban | POST status=banned | SweetAlert2 confirm |
| Unban | POST status=active | SweetAlert2 confirm |
| Hapus | POST delete | SweetAlert2 confirm + audit log |

---

## 5. TOUR GUIDE APPROVAL

### 5.1 Daftar Guide Pending

**URL:** `admin/guides/pending`  
**Controller:** `TourGuideController::pending()`

**Tampilan:**
- List guide dengan status `is_verified=0`
- Tombol "Lihat Dokumen" → modal preview KTP/sertifikat
- Tombol "Approve" → set `is_verified=1`, `verified_at=NOW()`, `verified_by=session user`
- Tombol "Reject" → set `status=banned` + notifikasi ke guide

### 5.2 Approve Flow

```
1. Admin buka halaman pending
2. Klik "Lihat Dokumen" → modal tampilkan file_path
3. Klik "Approve"
4. SweetAlert2: "Setujui tour guide ini?"
5. AJAX POST → api/admin/guide/approve
6. Update tour_guides SET is_verified=1, verified_at, verified_by
7. Kirim notifikasi ke guide: "Selamat! Profil Anda disetujui"
8. Refresh tabel
```

---

## 6. MANAJEMEN DESTINASI

### 6.1 CRUD Destinasi

**URL:** `admin/destinations`  
**Controller:** `DestinationController`

**Form Fields:**

| Field | Tipe | Validasi |
|-------|------|----------|
| name | text | required, max 200 |
| category_id | select | required |
| slug | text | auto-generate from name, unique |
| short_desc | textarea | max 500 |
| description | textarea (rich) | required |
| address | textarea | required |
| city | text | required |
| province | text | required |
| latitude | number | required, -90 to 90 |
| longitude | number | required, -180 to 180 |
| entry_fee | number | min 0 |
| opening_time | time | optional |
| closing_time | time | optional |
| daily_quota | number | optional |
| main_image | file | image, max 5MB |
| is_active | checkbox | default checked |
| is_featured | checkbox | default unchecked |

### 6.2 Kelola Kategori

**URL:** `admin/destinations/categories`

| Field | Tipe | Validasi |
|-------|------|----------|
| name | text | required, max 100 |
| slug | text | auto-generate, unique |
| icon | text | Font Awesome class (fa-mountain) |
| description | textarea | optional |

### 6.3 Kelola Tiket per Destinasi

**URL:** `admin/destinations/{id}/tickets`

| Field | Tipe | Validasi |
|-------|------|----------|
| ticket_type | select | regular, child, senior, group, foreigner |
| price | number | required, min 0 |
| description | text | optional |
| is_active | checkbox | default checked |

---

## 7. APPROVAL HOTEL & RESTORAN

### 7.1 Hotel Approval

**URL:** `admin/hotels/pending`

**Flow:**
1. Pemilik daftar hotel → status `is_approved=0`
2. Admin review data & foto
3. Approve → `is_approved=1` + notifikasi ke pemilik
4. Reject → notifikasi dengan alasan

### 7.2 Restoran Approval

**URL:** `admin/restaurants/pending`

**Flow sama dengan hotel approval.**

---

## 8. MANAJEMEN EVENT

### 8.1 CRUD Event

**URL:** `admin/events`

| Field | Tipe | Validasi |
|-------|------|----------|
| title | text | required, max 200 |
| category | select | festival, seni, kuliner, olahraga, budaya, religi |
| description | textarea (rich) | required |
| start_date | datetime | required |
| end_date | datetime | required, after start_date |
| location_name | text | required |
| address | textarea | required |
| latitude | number | optional |
| longitude | number | optional |
| price | number | min 0, default 0 (gratis) |
| max_participants | number | optional |
| main_image | file | image, max 5MB |
| is_active | checkbox | default checked |

---

## 9. MANAJEMEN AUDIO GUIDE

**URL:** `admin/audio`

| Field | Tipe | Validasi |
|-------|------|----------|
| destination_id | select | required |
| language | select | id, en, jp, kr, cn |
| title | text | required, max 200 |
| description | textarea | optional |
| file_path | file | audio mp3/ogg, max 20MB |
| transcript | textarea | optional |

---

## 10. MANAJEMEN TRANSAKSI

### 10.1 Daftar Semua Transaksi

**URL:** `admin/transactions`

**Fitur:**
- DataTables dengan filter: tanggal, type, payment_status
- Kolom: Kode, User, Type, Amount, Method, Status, Tanggal
- Tombol "Lihat Detail" → modal detail transaksi + item
- Tombol "Verifikasi Pembayaran" → upload proof → set paid

### 10.2 Verifikasi Pembayaran

```
1. Admin buka transaksi status=pending
2. Lihat bukti transfer (payment_proof)
3. Klik "Verifikasi" → set payment_status=paid, paid_at=NOW()
4. Update status referensi (booking/ticket/hotel) → confirmed
5. Kirim notifikasi ke user: "Pembayaran dikonfirmasi"
```

---

## 11. LAPORAN & ANALITIK

### 11.1 Laporan Transaksi

**URL:** `admin/reports/transactions`

**Filter:**
- Rentang tanggal (datepicker)
- Type transaksi
- Status pembayaran

**Output:**
- Tabel detail transaksi
- Summary: total transaksi, total pendapatan, total refund
- Export CSV button

### 11.2 Statistik Destinasi

**URL:** `admin/reports/destinations`

**Data:**
- Total tiket terjual per destinasi
- Total pendapatan per destinasi
- Rating rata-rata
- Grafik kunjungan per bulan

### 11.3 Statistik Guide

**URL:** `admin/reports/guides`

**Data:**
- Total booking per guide
- Total pendapatan per guide
- Rating rata-rata
- Bahasa paling diminta

---

## 12. PENGATURAN SISTEM

### 12.1 Konfigurasi Umum

**URL:** `admin/settings`

| Setting Key | Tipe | Keterangan |
|-------------|------|-----------|
| site_name | text | Nama aplikasi |
| default_language | select | id, en |
| currency | text | IDR |
| contact_email | email | Email kontak |
| max_upload_size | number | Max upload (bytes) |
| enable_ai_chat | boolean | Aktifkan AI chat |
| enable_audio_guide | boolean | Aktifkan audio guide |
| enable_hotel_booking | boolean | Aktifkan booking hotel |
| enable_restaurant_order | boolean | Aktifkan pesan restoran |

### 12.2 Audit Log

**URL:** `admin/settings/audit-log`

- Daftar log dengan filter: user, action, module, tanggal
- Kolom: User, Action, Module, Description, IP, Time
- Tidak bisa edit/hapus (read-only)
- Auto-cleanup log > 90 hari

---

## 13. BROADCAST NOTIFIKASI

**URL:** `admin/notifications/broadcast`

| Field | Tipe | Validasi |
|-------|------|----------|
| target | select | all, wisatawan, tour_guide, specific_user |
| title | text | required, max 200 |
| message | textarea | required |
| link | text | optional URL |

**Flow:**
1. Admin isi form broadcast
2. AJAX POST → loop insert ke notifications untuk semua user target
3. Return jumlah notifikasi terkirim
4. SweetAlert2 success

---

## 14. CONTROLLER: AdminController

```php
<?php
class UserController extends Controller {

    public function __construct() {
        Middleware::requireRole('admin');
    }

    public function index() {
        $userModel = $this->model('User');
        $users = $userModel->all();
        $this->view('admin/users/index', [
            'title' => 'Manajemen Pengguna',
            'users' => $users
        ]);
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $this->validateInput($_POST);
            $input['password'] = password_hash($input['password'], PASSWORD_BCRYPT);
            $userModel = $this->model('User');
            $id = $userModel->insert($input);
            Logger::audit('create', 'users', "Created user #{$id}");
            $this->redirect('admin/users');
        }
        $this->view('admin/users/create', ['title' => 'Tambah Pengguna']);
    }

    public function edit($id) {
        $userModel = $this->model('User');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $this->validateInput($_POST);
            if (empty($input['password'])) unset($input['password']);
            else $input['password'] = password_hash($input['password'], PASSWORD_BCRYPT);
            $userModel->update($id, $input);
            Logger::audit('update', 'users', "Updated user #{$id}");
            $this->redirect('admin/users');
        }
        $user = $userModel->find($id);
        $this->view('admin/users/edit', ['title' => 'Edit Pengguna', 'user' => $user]);
    }

    public function delete($id) {
        $userModel = $this->model('User');
        $userModel->delete($id);
        Logger::audit('delete', 'users', "Deleted user #{$id}");
        $this->json(['status' => 'success', 'message' => 'Pengguna dihapus']);
    }
}
```

---

## 15. VIEW: Dashboard Admin

```php
<!-- app/views/admin/dashboard.php -->
<?php include 'app/views/layouts/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include 'app/views/layouts/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <h1 class="h2 mt-3">Dashboard Admin</h1>

            <!-- Statistik Cards -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <h5>Total Pengguna</h5>
                            <h2><?= $stats['total_users'] ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <h5>Total Guide</h5>
                            <h2><?= $stats['total_guides'] ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-info">
                        <div class="card-body">
                            <h5>Total Destinasi</h5>
                            <h2><?= $stats['total_destinations'] ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-warning">
                        <div class="card-body">
                            <h5>Pendapatan</h5>
                            <h2>Rp <?= number_format($stats['monthly_revenue'], 0, ',', '.') ?></h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grafik -->
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">Tren Booking 30 Hari</div>
                        <div class="card-body">
                            <canvas id="bookingChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">Destinasi Terlaris</div>
                        <div class="card-body">
                            <canvas id="topDestinations"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include 'app/views/layouts/footer.php'; ?>
```

---

> **Modul Selanjutnya:** `08_MODUL_WISATAWAN.md` — Modul wisatawan secara lengkap
