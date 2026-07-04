# MODUL 13 — MODUL HOTEL & HOMESTAY

> **Versi:** 1.1 · **Tanggal:** 2026-06-30 · **Last Updated:** 2026-06-30

---

## 1. RINGKASAN

Modul untuk pendaftaran, pencarian, dan booking akomodasi (hotel, homestay, villa, guesthouse).

---

## 2. ALUR

```
Pemilik daftar hotel → Admin approve → Wisatawan cari
→ Pilih kamar → Booking (check-in/out) → Pembayaran
→ Confirmed → Check-in → Check-out → Review
```

---

## 3. CONTROLLER

```php
<?php
class HotelController extends Controller {

    // Pemilik: daftar hotel
    public function register() {
        Middleware::requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $this->validateInput($_POST);
            $hotelModel = $this->model('Hotel');

            // Upload main image
            $imagePath = Helper::uploadFile($_FILES['main_image'], 'uploads/hotels/');

            $id = $hotelModel->insert(array_merge($input, [
                'owner_id' => $_SESSION['user_id'],
                'main_image' => $imagePath,
                'is_approved' => 0
            ]));

            $this->json(['status' => 'success', 'message' => 'Hotel terdaftar, menunggu approval']);
        }
    }

    // Wisatawan: cari hotel
    public function search() {
        $hotelModel = $this->model('Hotel');
        $filters = [
            'city' => $_GET['city'] ?? null,
            'type' => $_GET['type'] ?? null,
        ];
        $hotels = $hotelModel->searchApproved($filters);
        $this->json(['status' => 'success', 'data' => $hotels]);
    }

    // Wisatawan: booking kamar
    public function book() {
        Middleware::requireAuth();
        $input = json_decode(file_get_contents('php://input'), true);

        $roomModel = $this->model('HotelRoom');
        $room = $roomModel->find($input['room_id']);
        if (!$room || $room['available_rooms'] < $input['num_rooms']) {
            $this->json(['status' => 'error', 'message' => 'Kamar tidak tersedia'], 400);
        }

        $checkIn = new DateTime($input['check_in']);
        $checkOut = new DateTime($input['check_out']);
        $nights = $checkIn->diff($checkOut)->days;
        $total = $room['price_per_night'] * $input['num_rooms'] * $nights;

        $bookingModel = $this->model('HotelBooking');
        $code = $bookingModel->generateCode();
        $bookingId = $bookingModel->insert([
            'booking_code' => $code,
            'user_id' => $_SESSION['user_id'],
            'hotel_id' => $room['hotel_id'],
            'room_id' => $input['room_id'],
            'check_in' => $input['check_in'],
            'check_out' => $input['check_out'],
            'num_rooms' => $input['num_rooms'],
            'num_nights' => $nights,
            'total_amount' => $total,
            'status' => 'pending'
        ]);

        // Create transaction
        $trxModel = $this->model('Transaction');
        $trxId = $trxModel->insert([
            'transaction_code' => $trxModel->generateTrxCode(),
            'user_id' => $_SESSION['user_id'],
            'type' => 'hotel',
            'reference_id' => $bookingId,
            'gross_amount' => $total,
            'net_amount' => $total,
            'payment_method' => $input['payment_method'] ?? 'transfer',
            'payment_status' => 'pending'
        ]);
        $bookingModel->update($bookingId, ['transaction_id' => $trxId]);

        $this->json(['status' => 'success', 'data' => ['booking_id' => $bookingId, 'code' => $code, 'total' => $total]]);
    }

    // Admin: approve hotel
    public function approve($id) {
        Middleware::requireRole('admin');
        $hotelModel = $this->model('Hotel');
        $hotelModel->update($id, ['is_approved' => 1]);

        $notifModel = $this->model('Notification');
        $hotel = $hotelModel->find($id);
        $notifModel->insert([
            'user_id' => $hotel['owner_id'],
            'type' => 'system',
            'title' => 'Hotel Disetujui',
            'message' => "Hotel {$hotel['name']} telah disetujui"
        ]);

        $this->json(['status' => 'success', 'message' => 'Hotel disetujui']);
    }
}
```

---

## 4. API ENDPOINTS

| Method | URL | Fungsi |
|--------|-----|--------|
| POST | `api/hotel/register` | Pemilik daftar hotel |
| GET | `api/hotels` | Cari hotel |
| GET | `api/hotel/{id}` | Detail hotel + rooms |
| POST | `api/hotel/book` | Booking kamar |
| GET | `api/hotel/my-bookings` | Booking hotel saya |
| POST | `api/admin/hotel/approve/{id}` | Admin approve |
| POST | `api/hotel/room/add` | Pemilik tambah kamar |

---

## 5. VIEW: Detail Hotel

```php
<!-- app/views/wisatawan/hotel_detail.php -->
<div class="row">
    <div class="col-md-8">
        <img src="<?= BASE_URL . $hotel['main_image'] ?>" class="img-fluid rounded mb-3">
        <h2><?= $hotel['name'] ?></h2>
        <p class="text-muted"><?= ucfirst($hotel['type']) ?> · <?= $hotel['city'] ?></p>
        <p><?= $hotel['description'] ?></p>

        <h4 class="mt-4">Kamar Tersedia</h4>
        <?php foreach ($rooms as $room): ?>
            <div class="card mb-2">
                <div class="card-body d-flex justify-content-between">
                    <div>
                        <h5><?= $room['room_type'] ?></h5>
                        <p class="small">Kapasitas: <?= $room['capacity'] ?> orang</p>
                        <p class="small">Tersedia: <?= $room['available_rooms'] ?> kamar</p>
                    </div>
                    <div class="text-end">
                        <h5 class="text-primary">Rp <?= number_format($room['price_per_night'], 0, ',', '.') ?></h5>
                        <small>per malam</small><br>
                        <button class="btn btn-sm btn-primary btn-book-room"
                                data-room-id="<?= $room['id'] ?>"
                                data-price="<?= $room['price_per_night'] ?>">
                            Booking
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="col-md-4">
        <div id="hotel-map" style="height: 300px;"></div>
    </div>
</div>
```

---

## 7. INTEGRATION REMINDERS

**Pastikan integrasi berikut sebelum production:**

- [ ] **Validation:** Validasi ketersediaan kamar sebelum booking
- [ ] **Security:** Rate limiting untuk pencarian dan booking
- [ ] **Payment:** Integrasi dengan payment gateway untuk deposit
- [ ] **Notification:** Kirim email/SMS konfirmasi booking
- [ ] **Analytics:** Track hotel search dan booking conversion
- [ ] **Cancellation:** Implementasi cancellation policy
- [ ] **Review:** Integrasi dengan modul review untuk rating
- [ ] **Map:** Integrasi dengan modul map untuk lokasi hotel

---

> **Modul Selanjutnya:** `14_MODUL_RESTORAN_UMKM.md`
