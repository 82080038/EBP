# MODUL 22 — USER ROLE & PERMISSION

> **Versi:** 1.0 · **Tanggal:** 2026-06-30

---

## 1. RINGKASAN

Sistem manajemen role dan permission untuk 3 jenis pengguna: Admin, Wisatawan,
dan Tour Guide.

---

## 2. ROLE DEFINITION

| Role | Deskripsi | Landing Page |
|------|-----------|-------------|
| `admin` | Super user, kelola seluruh sistem | admin/dashboard |
| `wisatawan` | Konsumen, cari & booking layanan | wisatawan/dashboard |
| `tour_guide` | Provider jasa pemandu wisata | tourguide/dashboard |

---

## 3. PERMISSION MATRIX

### 3.1 Modul Auth

| Aksi | Admin | Wisatawan | Tour Guide |
|------|-------|-----------|------------|
| Login | ✓ | ✓ | ✓ |
| Logout | ✓ | ✓ | ✓ |
| Register (wisatawan) | - | ✓ | - |
| Register (guide) | - | - | ✓ |
| Edit profil sendiri | ✓ | ✓ | ✓ |
| Edit profil orang lain | ✓ | ✗ | ✗ |
| Lupa password | ✓ | ✓ | ✓ |

### 3.2 Modul Tour Guide

| Aksi | Admin | Wisatawan | Tour Guide |
|------|-------|-----------|------------|
| Cari guide | ✓ | ✓ | ✗ |
| Lihat profil guide | ✓ | ✓ | ✓ (own) |
| Edit profil guide | ✓ (all) | ✗ | ✓ (own) |
| Tambah bahasa/spesialisasi | ✗ | ✗ | ✓ (own) |
| Set jadwal | ✗ | ✗ | ✓ (own) |
| Upload dokumen | ✗ | ✗ | ✓ (own) |
| Approve guide | ✓ | ✗ | ✗ |
| Ban guide | ✓ | ✗ | ✗ |

### 3.3 Modul Booking

| Aksi | Admin | Wisatawan | Tour Guide |
|------|-------|-----------|------------|
| Create booking | ✓ | ✓ | ✗ |
| View own booking | ✓ | ✓ | ✓ |
| View all booking | ✓ | ✗ | ✗ |
| Accept/reject booking | ✓ | ✗ | ✓ (own guide) |
| Complete booking | ✓ | ✗ | ✓ (own guide) |
| Cancel booking | ✓ | ✓ (own) | ✗ |
| Upload payment proof | ✓ | ✓ (own) | ✗ |
| Verify payment | ✓ | ✗ | ✗ |

### 3.4 Modul Destinasi & Tiket

| Aksi | Admin | Wisatawan | Tour Guide |
|------|-------|-----------|------------|
| CRUD destinasi | ✓ | ✗ | ✗ |
| View destinasi | ✓ | ✓ | ✓ |
| Beli tiket | ✓ | ✓ | ✗ |
| View my tickets | ✓ | ✓ | ✗ |
| Verify ticket | ✓ | ✗ | ✓ (at location) |

### 3.5 Modul Hotel

| Aksi | Admin | Wisatawan | Tour Guide |
|------|-------|-----------|------------|
| Register hotel | ✓ | ✓ (as owner) | ✗ |
| Approve hotel | ✓ | ✗ | ✗ |
| View hotels | ✓ | ✓ | ✓ |
| Book hotel | ✓ | ✓ | ✓ |
| Manage rooms | ✓ | ✓ (own) | ✗ |

### 3.6 Modul Restoran

| Aksi | Admin | Wisatawan | Tour Guide |
|------|-------|-----------|------------|
| Register restoran | ✓ | ✓ (as owner) | ✗ |
| Approve restoran | ✓ | ✗ | ✗ |
| View restoran | ✓ | ✓ | ✓ |
| Place order | ✓ | ✓ | ✓ |
| Manage menu | ✓ | ✓ (own) | ✗ |
| Update order status | ✓ | ✗ | ✗ (owner) |

### 3.7 Modul Event

| Aksi | Admin | Wisatawan | Tour Guide |
|------|-------|-----------|------------|
| CRUD event | ✓ | ✗ | ✗ |
| View event | ✓ | ✓ | ✓ |
| Register event | ✓ | ✓ | ✓ |

### 3.8 Modul Audio Guide

| Aksi | Admin | Wisatawan | Tour Guide |
|------|-------|-----------|------------|
| Upload audio | ✓ | ✗ | ✗ |
| Play audio | ✓ | ✓ | ✗ |
| Delete audio | ✓ | ✗ | ✗ |

### 3.9 Modul AI Chat

| Aksi | Admin | Wisatawan | Tour Guide |
|------|-------|-----------|------------|
| Chat | ✓ | ✓ | ✓ |
| View history | ✓ (all) | ✓ (own) | ✓ (own) |

### 3.10 Modul Report

| Aksi | Admin | Wisatawan | Tour Guide |
|------|-------|-----------|------------|
| Dashboard report | ✓ | ✗ | ✗ |
| Own earnings | ✗ | ✗ | ✓ |
| Export CSV | ✓ | ✗ | ✓ (own) |
| View all transactions | ✓ | ✗ | ✗ |

### 3.11 Modul Admin Only

| Aksi | Admin | Wisatawan | Tour Guide |
|------|-------|-----------|------------|
| User management | ✓ | ✗ | ✗ |
| Broadcast notif | ✓ | ✗ | ✗ |
| Settings | ✓ | ✗ | ✗ |
| Audit log | ✓ | ✗ | ✗ |
| Backup database | ✓ | ✗ | ✗ |

---

## 4. IMPLEMENTATION

### 4.1 Middleware di Controller

```php
class TourGuideController extends Controller {
    public function __construct() {
        Middleware::requireRole('tour_guide');
    }
}

class DestinationController extends Controller {
    public function index() {
        // Public access — no middleware
    }

    public function create() {
        Middleware::requireRole('admin');
        // ...
    }
}
```

### 4.2 Conditional Menu (Sidebar)

```php
<!-- app/views/layouts/sidebar.php -->
<?php $role = $_SESSION['role'] ?? 'guest'; ?>

<?php if ($role === 'admin'): ?>
    <a href="<?= BASE_URL ?>admin/dashboard">Dashboard</a>
    <a href="<?= BASE_URL ?>admin/users">Pengguna</a>
    <a href="<?= BASE_URL ?>admin/destinations">Destinasi</a>
    <a href="<?= BASE_URL ?>admin/reports">Laporan</a>
<?php endif; ?>

<?php if ($role === 'wisatawan'): ?>
    <a href="<?= BASE_URL ?>wisatawan/dashboard">Dashboard</a>
    <a href="<?= BASE_URL ?>wisatawan/search-guide">Cari Guide</a>
    <a href="<?= BASE_URL ?>wisatawan/destinations">Destinasi</a>
    <a href="<?= BASE_URL ?>wisatawan/my-bookings">Booking Saya</a>
<?php endif; ?>

<?php if ($role === 'tour_guide'): ?>
    <a href="<?= BASE_URL ?>tourguide/dashboard">Dashboard</a>
    <a href="<?= BASE_URL ?>tourguide/profile">Profil</a>
    <a href="<?= BASE_URL ?>tourguide/schedule">Jadwal</a>
    <a href="<?= BASE_URL ?>tourguide/bookings/pending">Booking Masuk</a>
    <a href="<?= BASE_URL ?>tourguide/earnings">Pendapatan</a>
<?php endif; ?>
```

---

## 5. FLOW REGISTRASI

```
Wisatawan register → status=active, role=wisatawan → langsung login

Tour Guide register → status=pending, role=tour_guide
→ Upload dokumen (KTP, sertifikat)
→ Admin review → approve (is_verified=1, status=active)
  OR reject (status=banned)
→ Notifikasi ke guide
```

---

> **Modul Selanjutnya:** `23_DATABASE_BACKUP_RECOVERY.md`
