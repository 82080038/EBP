# MODUL 12 — MODUL TIKET WISATA

> **Versi:** 1.1 · **Tanggal:** 2026-06-30 · **Last Updated:** 2026-06-30

---

## 1. RINGKASAN

Modul untuk menjual dan mengelola e-ticket destinasi wisata dengan QR code.

---

## 2. ALUR PEMBELIAN

```
Wisatawan pilih destinasi → Pilih jenis tiket & jumlah
→ Cek kuota harian → Create ticket_order (pending) + transaction
→ Pembayaran → Generate QR code → E-ticket siap
→ Verifikasi di lokasi (scan QR) → status=used
```

---

## 3. KODE TIKET

Format: `TG-TKT-YYYYMMDD-XXX`

---

## 4. QR CODE GENERATION

Menggunakan library `endroid/qr-code` (Composer) atau API gratis:

```php
<?php
// Dengan endroid/qr-code
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;

public function generateQR($orderCode) {
    $qr = QrCode::create($orderCode)
        ->setWriter(new SvgWriter())
        ->setSize(200);
    $path = 'public/uploads/tickets/' . $orderCode . '.svg';
    $qr->writeFile($path);
    return $path;
}
```

Alternatif tanpa Composer (menggunakan API):
```php
public function generateQR($orderCode) {
    $url = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($orderCode);
    $img = file_get_contents($url);
    $path = 'public/uploads/tickets/' . $orderCode . '.png';
    file_put_contents($path, $img);
    return $path;
}
```

---

## 5. CONTROLLER

```php
<?php
class DestinationController extends Controller {

    public function buyTicket() {
        Middleware::requireAuth();
        $input = json_decode(file_get_contents('php://input'), true);

        // Validate
        $ticketModel = $this->model('Ticket');
        $ticket = $ticketModel->find($input['ticket_id']);
        if (!$ticket || !$ticket['is_active']) {
            $this->json(['status' => 'error', 'message' => 'Tiket tidak tersedia'], 400);
        }

        // Check daily quota
        $orderModel = $this->model('TicketOrder');
        $soldToday = $orderModel->countSoldByDate($input['ticket_id'], $input['visit_date']);
        $destModel = $this->model('Destination');
        $dest = $destModel->find($ticket['destination_id']);
        if ($dest['daily_quota'] && ($soldToday + $input['quantity']) > $dest['daily_quota']) {
            $this->json(['status' => 'error', 'message' => 'Kuota hari ini penuh'], 400);
        }

        $total = $ticket['price'] * $input['quantity'];
        $orderCode = $orderModel->generateOrderCode();

        $orderId = $orderModel->insert([
            'order_code' => $orderCode,
            'user_id' => $_SESSION['user_id'],
            'visit_date' => $input['visit_date'],
            'total_amount' => $total,
            'status' => 'pending'
        ]);

        $orderModel->insertItem($orderId, $input['ticket_id'], $input['quantity'], $total);

        // Create transaction
        $trxModel = $this->model('Transaction');
        $trxId = $trxModel->insert([
            'transaction_code' => $trxModel->generateTrxCode(),
            'user_id' => $_SESSION['user_id'],
            'type' => 'ticket',
            'reference_id' => $orderId,
            'gross_amount' => $total,
            'net_amount' => $total,
            'payment_method' => $input['payment_method'] ?? 'transfer',
            'payment_status' => 'pending'
        ]);
        $orderModel->update($orderId, ['transaction_id' => $trxId]);

        $this->json([
            'status' => 'success',
            'data' => ['order_id' => $orderId, 'order_code' => $orderCode, 'total' => $total]
        ]);
    }

    public function verifyTicket() {
        Middleware::requireRole(['admin', 'tour_guide']);
        $code = $_POST['ticket_code'] ?? '';
        $orderModel = $this->model('TicketOrder');
        $order = $orderModel->findByCode($code);
        if (!$order) {
            $this->json(['status' => 'error', 'message' => 'Tiket tidak ditemukan'], 404);
        }
        if ($order['status'] === 'used') {
            $this->json(['status' => 'error', 'message' => 'Tiket sudah digunakan'], 400);
        }
        $orderModel->update($order['id'], ['status' => 'used']);
        $this->json(['status' => 'success', 'message' => 'Tiket valid']);
    }
}
```

---

## 6. VIEW: E-Ticket

```php
<!-- app/views/wisatawan/e-ticket.php -->
<div class="card text-center mx-auto" style="max-width: 400px;">
    <div class="card-header bg-primary text-white">
        <h5>E-Ticket Wisata</h5>
    </div>
    <div class="card-body">
        <img src="<?= BASE_URL . $order['qr_code_path'] ?>" class="img-fluid mb-3">
        <h4><?= $order['destination_name'] ?></h4>
        <p>Kode: <strong><?= $order['order_code'] ?></strong></p>
        <p>Tanggal: <strong><?= $order['visit_date'] ?></strong></p>
        <p>Jumlah: <strong><?= $order['quantity'] ?> tiket</strong></p>
        <p>Status: <span class="badge bg-success"><?= ucfirst($order['status']) ?></span></p>
    </div>
    <div class="card-footer">
        <small>Tunjukkan QR code ini di lokasi</small>
    </div>
</div>
```

---

## 7. API ENDPOINTS

| Method | URL | Fungsi |
|--------|-----|--------|
| GET | `api/tickets/{destination_id}` | List tiket destinasi |
| POST | `api/ticket/buy` | Beli tiket |
| GET | `api/ticket/my` | Tiket saya |
| POST | `api/ticket/verify` | Verifikasi tiket (admin/guide) |

---

## 8. INTEGRATION REMINDERS

**Pastikan integrasi berikut sebelum production:**

- [ ] **Security:** QR code dengan digital signature untuk anti-fraud
- [ ] **Security:** Rate limiting untuk pembelian tiket
- [ ] **Performance:** Cache QR code generation
- [ ] **Validation:** Validasi kuota harian sebelum pembayaran
- [ ] **Analytics:** Track ticket sales dan conversion rate
- [ ] **Notification:** Kirim email/SMS setelah pembelian berhasil
- [ ] **Backup:** Backup data tiket harian
- [ ] **Recovery:** Implementasi refund mechanism

---

> **Modul Selanjutnya:** `13_MODUL_HOTEL_HOMESTAY.md`
