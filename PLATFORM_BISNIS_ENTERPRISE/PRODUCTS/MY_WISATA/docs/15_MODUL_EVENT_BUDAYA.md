# MODUL 15 — MODUL EVENT & BUDAYA

> **Versi:** 1.1 · **Tanggal:** 2026-06-30 · **Last Updated:** 2026-06-30

---

## 1. RINGKASAN

Modul untuk manajemen event budaya, festival, dan pendaftaran peserta.

---

## 2. ALUR

```
Admin/penyelenggara CRUD event → Wisatawan lihat kalender
→ Detail event → Daftar (bayar jika berbayar) → registered
→ Notifikasi H-1 → Attended (check-in) → Review
```

---

## 3. CONTROLLER

```php
<?php
class EventController extends Controller {

    public function index() {
        $model = $this->model('Event');
        $events = $model->getUpcoming(20);
        $this->view('wisatawan/events', ['title' => 'Event & Budaya', 'events' => $events]);
    }

    public function detail($id) {
        $model = $this->model('Event');
        $event = $model->find($id);
        $this->view('wisatawan/event_detail', ['title' => $event['title'], 'event' => $event]);
    }

    public function register() {
        Middleware::requireAuth();
        $input = json_decode(file_get_contents('php://input'), true);

        $eventModel = $this->model('Event');
        $event = $eventModel->find($input['event_id']);

        if (!$event || !$event['is_active']) {
            $this->json(['status' => 'error', 'message' => 'Event tidak tersimpan'], 404);
        }

        if ($event['max_participants'] && $event['registered_count'] >= $event['max_participants']) {
            $this->json(['status' => 'error', 'message' => 'Kuota penuh'], 400);
        }

        $regModel = $this->model('EventRegistration');
        $existing = $regModel->findByUserAndEvent($_SESSION['user_id'], $input['event_id']);
        if ($existing) {
            $this->json(['status' => 'error', 'message' => 'Sudah terdaftar'], 400);
        }

        $total = $event['price'] * $input['num_tickets'];
        $code = $regModel->generateCode();
        $regId = $regModel->insert([
            'registration_code' => $code,
            'user_id' => $_SESSION['user_id'],
            'event_id' => $input['event_id'],
            'num_tickets' => $input['num_tickets'],
            'total_amount' => $total,
            'status' => 'registered'
        ]);

        // Update registered count
        $eventModel->incrementRegistered($input['event_id'], $input['num_tickets']);

        // Create transaction if not free
        if ($total > 0) {
            $trxModel = $this->model('Transaction');
            $trxId = $trxModel->insert([
                'transaction_code' => $trxModel->generateTrxCode(),
                'user_id' => $_SESSION['user_id'],
                'type' => 'event',
                'reference_id' => $regId,
                'gross_amount' => $total,
                'net_amount' => $total,
                'payment_method' => $input['payment_method'] ?? 'transfer',
                'payment_status' => 'pending'
            ]);
            $regModel->update($regId, ['transaction_id' => $trxId]);
        }

        // Notify
        $notifModel = $this->model('Notification');
        $notifModel->insert([
            'user_id' => $_SESSION['user_id'],
            'type' => 'event',
            'title' => 'Pendaftaran Berhasil',
            'message' => "Anda terdaftar untuk event: {$event['title']}",
            'link' => 'wisatawan/my-events'
        ]);

        $this->json(['status' => 'success', 'data' => ['reg_id' => $regId, 'code' => $code]]);
    }

    // Admin: CRUD
    public function create() {
        Middleware::requireRole(['admin']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $this->validateInput($_POST);
            $imagePath = Helper::uploadFile($_FILES['main_image'], 'uploads/events/');
            $model = $this->model('Event');
            $id = $model->insert(array_merge($input, [
                'organizer_id' => $_SESSION['user_id'],
                'main_image' => $imagePath,
                'slug' => Helper::slugify($input['title'])
            ]));
            $this->redirect('admin/events');
        }
    }
}
```

---

## 4. API ENDPOINTS

| Method | URL | Fungsi |
|--------|-----|--------|
| GET | `api/events` | List event upcoming |
| GET | `api/event/{id}` | Detail event |
| POST | `api/event/register` | Daftar event |
| GET | `api/event/my-registrations` | Event saya |
| POST | `api/admin/event/create` | Admin create event |
| POST | `api/admin/event/update/{id}` | Admin update |
| POST | `api/admin/event/delete/{id}` | Admin delete |

---

## 5. VIEW: Kalender Event

```php
<!-- app/views/wisatawan/events.php -->
<div class="container mt-4">
    <h2>Event & Budaya</h2>

    <div class="row mb-4">
        <div class="col-md-12">
            <select id="filter-category" class="form-select" style="max-width: 250px;">
                <option value="">Semua Kategori</option>
                <option value="festival">Festival</option>
                <option value="seni">Seni</option>
                <option value="kuliner">Kuliner</option>
                <option value="budaya">Budaya</option>
            </select>
        </div>
    </div>

    <div class="row" id="events-grid">
        <?php foreach ($events as $event): ?>
            <div class="col-md-4 mb-3">
                <div class="card h-100">
                    <img src="<?= BASE_URL . $event['main_image'] ?>" class="card-img-top" style="height: 200px; object-fit: cover;">
                    <div class="card-body">
                        <span class="badge bg-info"><?= ucfirst($event['category']) ?></span>
                        <h5 class="mt-2"><?= $event['title'] ?></h5>
                        <p class="small text-muted">
                            <i class="fas fa-calendar"></i> <?= date('d M Y', strtotime($event['start_date'])) ?><br>
                            <i class="fas fa-map-marker-alt"></i> <?= $event['location_name'] ?>
                        </p>
                        <p class="small">
                            <?= $event['price'] > 0 ? 'Rp ' . number_format($event['price'], 0, ',', '.') : 'Gratis' ?>
                        </p>
                        <a href="<?= BASE_URL ?>wisatawan/event-detail/<?= $event['id'] ?>" class="btn btn-sm btn-primary">Detail</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
```

---

## 6. NOTIFIKASI PENGINGAT

Cron job atau trigger untuk kirim notifikasi H-1:

```php
// Jalankan via cron: 0 9 * * * php /path/to/cron/event_reminder.php
$eventModel = new Event();
$regs = $eventModel->getTomorrowRegistrations();
$notifModel = new Notification();
foreach ($regs as $reg) {
    $notifModel->insert([
        'user_id' => $reg['user_id'],
        'type' => 'reminder',
        'title' => 'Pengingat Event Besok',
        'message' => "Event {$reg['title']} besok jam {$reg['start_time']}",
        'link' => 'wisatawan/my-events'
    ]);
}
```

---

## 7. INTEGRATION REMINDERS

**Pastikan integrasi berikut sebelum production:**

- [ ] **Validation:** Validasi kapasitas event sebelum pendaftaran
- [ ] **Security:** Rate limiting untuk pendaftaran event
- [ ] **Payment:** Integrasi dengan payment gateway untuk event berbayar
- [ ] **Notification:** Integrasi dengan modul notification untuk reminder H-1
- [ ] **Analytics:** Track event registration dan attendance rate
- [ ] **Cancellation:** Implementasi cancellation policy
- [ ] **Review:** Integrasi dengan modul review untuk rating event
- [ ] **Calendar:** Integrasi dengan calendar API untuk sync ke user calendar

---

> **Modul Selanjutnya:** `16_MODUL_AUDIO_GUIDE.md`
