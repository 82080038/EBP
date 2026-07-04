# MODUL 11 — MODUL BOOKING & TRANSAKSI

> **Versi:** 1.1 · **Tanggal:** 2026-06-30 · **Last Updated:** 2026-06-30

---

## 1. RINGKASAN

Modul inti untuk pemesanan tour guide dan manajemen transaksi pembayaran.
Menghubungkan wisatawan, tour guide, dan sistem pembayaran.

---

## 2. ALUR BOOKING

```
Wisatawan pilih guide → Isi form booking → Hitung biaya
→ Create booking (pending) + Create transaction (pending)
→ Upload bukti bayar → Admin verifikasi → booking (confirmed)
→ Guide accept → Tour berlangsung → Guide complete → booking (completed)
→ Wisatawan review
```

## 3. STATUS FLOW

```
Booking:    pending → confirmed → completed
                ↓         ↓
            rejected  cancelled

Transaction: pending → paid → (refunded)
                 ↓
              failed / expired
```

---

## 4. KODE BOOKING

Format: `TG-BKG-YYYYMMDD-XXX` (contoh: `TG-BKG-20260630-001`)

```php
public function generateBookingCode() {
    $date = date('Ymd');
    $count = $this->db->query(
        "SELECT COUNT(*) as cnt FROM bookings WHERE booking_code LIKE ?",
        ["TG-BKG-{$date}-%"]
    )->fetch()['cnt'];
    return sprintf("TG-BKG-%s-%03d", $date, $count + 1);
}
```

---

## 5. CONTROLLER

```php
<?php
class BookingController extends Controller {

    public function create() {
        Middleware::requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['status' => 'error', 'message' => 'Method not allowed'], 405);
        }

        $input = json_decode(file_get_contents('php://input'), true);

        // Validate
        $v = new Validator($input);
        $v->required(['guide_id', 'booking_date', 'start_time', 'duration_hours']);
        if ($v->fails()) {
            $this->json(['status' => 'error', 'message' => $v->firstError()], 422);
        }

        // Check guide availability
        $guideModel = $this->model('TourGuide');
        $guide = $guideModel->find($input['guide_id']);
        if (!$guide || !$guide['is_available'] || !$guide['is_verified']) {
            $this->json(['status' => 'error', 'message' => 'Guide tidak tersedia'], 400);
        }

        $scheduleModel = $this->model('GuideSchedule');
        $schedule = $scheduleModel->getByGuideAndDate($input['guide_id'], $input['booking_date']);
        if ($schedule && $schedule['is_booked']) {
            $this->json(['status' => 'error', 'message' => 'Tanggal sudah dibooking'], 400);
        }

        // Calculate total
        $duration = (float)$input['duration_hours'];
        if ($duration >= 8) {
            $total = ceil($duration / 8) * $guide['daily_rate'];
        } else {
            $total = $duration * $guide['hourly_rate'];
        }

        // Create booking
        $bookingModel = $this->model('Booking');
        $bookingCode = $bookingModel->generateBookingCode();
        $bookingId = $bookingModel->insert([
            'booking_code' => $bookingCode,
            'user_id' => $_SESSION['user_id'],
            'guide_id' => $input['guide_id'],
            'booking_date' => $input['booking_date'],
            'start_time' => $input['start_time'],
            'duration_hours' => $duration,
            'num_participants' => $input['num_participants'] ?? 1,
            'destination_id' => $input['destination_id'] ?? null,
            'total_amount' => $total,
            'status' => 'pending',
            'notes' => $input['notes'] ?? null
        ]);

        // Create transaction
        $trxModel = $this->model('Transaction');
        $trxCode = $trxModel->generateTrxCode();
        $trxId = $trxModel->insert([
            'transaction_code' => $trxCode,
            'user_id' => $_SESSION['user_id'],
            'type' => 'booking_guide',
            'reference_id' => $bookingId,
            'gross_amount' => $total,
            'net_amount' => $total,
            'payment_method' => $input['payment_method'] ?? 'transfer',
            'payment_status' => 'pending'
        ]);

        // Update booking with transaction_id
        $bookingModel->update($bookingId, ['transaction_id' => $trxId]);

        // Notify guide
        $notifModel = $this->model('Notification');
        $guideUser = $guideModel->getUserId($input['guide_id']);
        $notifModel->insert([
            'user_id' => $guideUser,
            'type' => 'booking',
            'title' => 'Booking Baru',
            'message' => "Booking baru: {$bookingCode} tanggal {$input['booking_date']}",
            'link' => 'tourguide/bookings/pending'
        ]);

        Logger::audit('create', 'bookings', "Created booking {$bookingCode}");
        $this->json([
            'status' => 'success',
            'message' => 'Booking berhasil dibuat',
            'data' => ['booking_id' => $bookingId, 'booking_code' => $bookingCode, 'total' => $total]
        ]);
    }

    public function uploadProof($bookingId) {
        Middleware::requireAuth();
        $bookingModel = $this->model('Booking');
        $booking = $bookingModel->find($bookingId);

        if ($booking['user_id'] != $_SESSION['user_id']) {
            $this->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        if (isset($_FILES['proof']) && $_FILES['proof']['error'] === UPLOAD_ERR_OK) {
            $path = Helper::uploadFile($_FILES['proof'], 'public/uploads/proofs/');
            $trxModel = $this->model('Transaction');
            $trxModel->update($booking['transaction_id'], ['payment_proof' => $path]);
            $this->json(['status' => 'success', 'message' => 'Bukti bayar diupload']);
        }
        $this->json(['status' => 'error', 'message' => 'File tidak valid'], 400);
    }

    public function cancel($bookingId) {
        Middleware::requireAuth();
        $bookingModel = $this->model('Booking');
        $booking = $bookingModel->find($bookingId);

        if ($booking['user_id'] != $_SESSION['user_id'] && $_SESSION['role'] !== 'admin') {
            $this->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        if (!in_array($booking['status'], ['pending', 'confirmed'])) {
            $this->json(['status' => 'error', 'message' => 'Tidak bisa dibatalkan'], 400);
        }

        $bookingModel->update($bookingId, ['status' => 'cancelled']);

        // Free schedule
        $scheduleModel = $this->model('GuideSchedule');
        $scheduleModel->markAvailable($booking['guide_id'], $booking['booking_date']);

        // Notify
        $notifModel = $this->model('Notification');
        $notifModel->insert([
            'user_id' => $booking['guide_id'],
            'type' => 'booking',
            'title' => 'Booking Dibatalkan',
            'message' => "Booking {$booking['booking_code']} dibatalkan"
        ]);

        $this->json(['status' => 'success', 'message' => 'Booking dibatalkan']);
    }
}
```

---

## 6. API ENDPOINTS

| Method | URL | Fungsi |
|--------|-----|--------|
| POST | `api/booking/create` | Buat booking baru |
| POST | `api/booking/upload-proof/{id}` | Upload bukti bayar |
| POST | `api/booking/cancel/{id}` | Batalkan booking |
| GET | `api/booking/my` | Riwayat booking wisatawan |
| GET | `api/booking/guide/{status}` | Booking guide by status |
| POST | `api/booking/complete/{id}` | Guide selesaikan tour |
| GET | `api/transaction/all` | Semua transaksi (admin) |
| POST | `api/transaction/verify/{id}` | Admin verifikasi bayar |

---

## 7. VIEW: Halaman Pembayaran

```php
<!-- app/views/wisatawan/payment.php -->
<div class="card">
    <div class="card-header"><h4>Pembayaran</h4></div>
    <div class="card-body">
        <p><strong>Kode Booking:</strong> <?= $booking['booking_code'] ?></p>
        <p><strong>Total:</strong> Rp <?= number_format($booking['total_amount'], 0, ',', '.') ?></p>
        <p><strong>Metode:</strong> Transfer Bank</p>
        <hr>
        <p>Transfer ke: <strong>BCA 1234567890 a.n. Tour Guide App</strong></p>
        <form id="form-proof" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= CSRF_TOKEN ?>">
            <div class="mb-3">
                <label>Upload Bukti Transfer</label>
                <input type="file" name="proof" class="form-control" accept="image/*" required>
            </div>
            <button type="submit" class="btn btn-primary">Upload</button>
        </form>
    </div>
</div>
```

---

## 8. PAYMENT GATEWAY FALLBACK STRATEGY

**Status:** Not Implemented — HIGH PRIORITY

Implementasi multiple payment gateways dengan fallback:

```php
// app/services/PaymentGatewayService.php
class PaymentGatewayService {
    private $gateways = [
        'midtrans' => MidtransGateway::class,
        'xendit' => XenditGateway::class,
        'stripe' => StripeGateway::class,
    ];
    private $primaryGateway = 'midtrans';
    private $fallbackOrder = ['xendit', 'stripe'];

    public function createPayment(array $data): array {
        // Try primary gateway
        try {
            return $this->useGateway($this->primaryGateway, $data);
        } catch (PaymentGatewayException $e) {
            $this->logError($this->primaryGateway, $e);
            
            // Try fallback gateways
            foreach ($this->fallbackOrder as $gateway) {
                try {
                    return $this->useGateway($gateway, $data);
                } catch (PaymentGatewayException $e) {
                    $this->logError($gateway, $e);
                }
            }
            
            throw new PaymentException('All payment gateways failed');
        }
    }

    private function useGateway(string $gatewayName, array $data): array {
        $gateway = new $this->gateways[$gatewayName]();
        return $gateway->createPayment($data);
    }
}
```

### Payment Retry Logic with Exponential Backoff

```php
// app/services/PaymentRetryService.php
class PaymentRetryService {
    private $maxRetries = 3;
    private $baseDelay = 1000; // 1 second

    public function retryPayment(string $transactionId): bool {
        $attempt = 0;
        $delay = $this->baseDelay;

        while ($attempt < $this->maxRetries) {
            $attempt++;
            
            try {
                $result = $this->processPayment($transactionId);
                if ($result['success']) {
                    return true;
                }
            } catch (Exception $e) {
                if ($attempt === $this->maxRetries) {
                    throw $e;
                }
            }

            // Exponential backoff
            usleep($delay * 1000);
            $delay *= 2;
        }

        return false;
    }
}
```

---

## 9. DATABASE ROW-LEVEL LOCKING FOR BOOKINGS

**Status:** Not Implemented — HIGH PRIORITY

Mencegah double booking dengan row-level locking:

```php
// app/services/BookingService.php
class BookingService {
    public function createBooking(array $data): int {
        $db = Database::getInstance();
        
        try {
            $db->beginTransaction();
            
            // Lock guide availability row
            $sql = "SELECT * FROM guide_schedules 
                    WHERE guide_id = :guide_id AND date = :date 
                    FOR UPDATE";
            $schedule = $db->query($sql, [
                'guide_id' => $data['guide_id'],
                'date' => $data['booking_date']
            ])->fetch();

            if (!$schedule || $schedule['status'] !== 'available') {
                throw new BookingException('Guide tidak tersedia');
            }

            // Check concurrent bookings
            $sql = "SELECT COUNT(*) as cnt FROM bookings 
                    WHERE guide_id = :guide_id AND booking_date = :date 
                    AND status IN ('pending', 'confirmed')";
            $count = $db->query($sql, [
                'guide_id' => $data['guide_id'],
                'date' => $data['booking_date']
            ])->fetch()['cnt'];

            if ($count >= $schedule['max_bookings']) {
                throw new BookingException('Guide sudah penuh');
            }

            // Create booking
            $sql = "INSERT INTO bookings (user_id, guide_id, booking_date, guests, status, created_at) 
                    VALUES (:user_id, :guide_id, :booking_date, :guests, 'pending', NOW())";
            $db->query($sql, $data);
            $bookingId = (int) $db->lastInsertId();

            // Update schedule
            $sql = "UPDATE guide_schedules 
                    SET status = 'booked' 
                    WHERE guide_id = :guide_id AND date = :date";
            $db->query($sql, [
                'guide_id' => $data['guide_id'],
                'date' => $data['booking_date']
            ]);

            $db->commit();
            return $bookingId;

        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }
}
```

### Optimistic Locking Alternative

```php
// Using version column for optimistic locking
public function updateBooking(int $bookingId, array $data): bool {
    $sql = "UPDATE bookings 
            SET status = :status, version = version + 1 
            WHERE id = :id AND version = :version";
    
    $result = $this->db->query($sql, [
        'status' => $data['status'],
        'id' => $bookingId,
        'version' => $data['version']
    ]);

    if ($result->rowCount() === 0) {
        throw new ConcurrencyException('Booking diupdate oleh user lain');
    }

    return true;
}
```

---

> **Modul Selanjutnya:** `12_MODUL_TIKET_WISATA.md`
