# MODUL 42 — THIRD-PARTY API INTEGRATION

> **Versi:** 1.0 · **Tanggal:** 2026-06-30

---

## 1. RINGKASAN

Dokumentasi integrasi dengan platform pihak ketiga seperti Traveloka, Booking.com, Agoda, dll untuk menerima pesanan hotel/homestay dan pembayaran.

---

## 2. KONSEP INTEGRASI

### 2.1 Arsitektur Integrasi

```
Platform Pihak Ketiga (Traveloka/Booking.com)
        ↓ (HTTP POST JSON)
Webhook Handler (Aplikasi Tour Guide)
        ↓
Order Processing Service
        ↓
Database (orders, bookings, transactions)
        ↓
Payment Gateway (Midtrans/Xendit)
        ↓
Response ke Platform Pihak Ketiga
```

### 2.2 Flow Integrasi

1. Platform pihak ketiga mengirim order via webhook (HTTP POST JSON)
2. Aplikasi menerima dan validasi order
3. Cek ketersediaan kamar/akomodasi
4. Create booking di sistem internal
5. Proses pembayaran (jika required)
6. Update status order
7. Kirim response ke platform pihak ketiga
8. Platform pihak ketiga mengirim notifikasi pembayaran
9. Aplikasi update status booking ke confirmed

---

## 3. WEBHOOK HANDLER

### 3.1 Webhook Endpoint

```php
// app/controllers/Api/WebhookController.php
class WebhookController extends Controller {
    
    public function receiveOrder(): void {
        // Get raw JSON input
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        // Validate webhook signature
        if (!$this->validateSignature($input, $_SERVER['HTTP_X_SIGNATURE'])) {
            $this->json(['status' => 'error', 'message' => 'Invalid signature'], 401);
            return;
        }
        
        // Process order based on platform
        $platform = $data['platform'] ?? null;
        
        switch ($platform) {
            case 'traveloka':
                $this->processTravelokaOrder($data);
                break;
            case 'booking.com':
                $this->processBookingComOrder($data);
                break;
            case 'agoda':
                $this->processAgodaOrder($data);
                break;
            default:
                $this->json(['status' => 'error', 'message' => 'Unknown platform'], 400);
        }
    }
    
    private function validateSignature($payload, $signature): bool {
        $secret = $_ENV['WEBHOOK_SECRET'];
        $expectedSignature = hash_hmac('sha256', $payload, $secret);
        return hash_equals($expectedSignature, $signature);
    }
}
```

### 3.2 Traveloka Integration

```php
// app/services/TravelokaIntegrationService.php
class TravelokaIntegrationService {
    private $apiKey;
    private $apiSecret;
    
    public function __construct() {
        $this->apiKey = $_ENV['TRAVELOKA_API_KEY'];
        $this->apiSecret = $_ENV['TRAVELOKA_API_SECRET'];
    }
    
    public function processOrder(array $data): array {
        // Validate required fields
        $required = ['order_id', 'hotel_id', 'check_in', 'check_out', 'guests', 'guest_name', 'guest_email'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                return ['status' => 'error', 'message' => "Missing field: $field"];
            }
        }
        
        // Check room availability
        $availability = $this->checkAvailability(
            $data['hotel_id'],
            $data['check_in'],
            $data['check_out'],
            $data['guests']
        );
        
        if (!$availability['available']) {
            return [
                'status' => 'error',
                'message' => 'Room not available',
                'reason' => $availability['reason']
            ];
        }
        
        // Create booking in system
        $booking = $this->createBooking($data);
        
        if (!$booking['success']) {
            return [
                'status' => 'error',
                'message' => 'Failed to create booking',
                'error' => $booking['error']
            ];
        }
        
        // Return success response
        return [
            'status' => 'success',
            'order_id' => $data['order_id'],
            'booking_id' => $booking['booking_id'],
            'booking_code' => $booking['booking_code'],
            'total_amount' => $booking['total_amount'],
            'currency' => 'IDR'
        ];
    }
    
    private function checkAvailability(int $hotelId, string $checkIn, string $checkOut, int $guests): array {
        $sql = "SELECT COUNT(*) as booked 
                FROM hotel_bookings 
                WHERE hotel_id = :hotel_id 
                AND status IN ('confirmed', 'pending')
                AND (
                    (check_in <= :check_out AND check_out >= :check_in)
                )";
        
        $result = $this->db->query($sql, [
            'hotel_id' => $hotelId,
            'check_in' => $checkIn,
            'check_out' => $checkOut
        ])->fetch();
        
        $totalRooms = $this->getTotalRooms($hotelId);
        $available = $totalRooms - $result['booked'];
        
        return [
            'available' => $available > 0,
            'reason' => $available <= 0 ? 'No rooms available' : null,
            'available_rooms' => $available
        ];
    }
    
    private function createBooking(array $data): array {
        try {
            $this->db->beginTransaction();
            
            // Get hotel details
            $hotel = $this->db->query(
                "SELECT * FROM hotels WHERE id = ?",
                [$data['hotel_id']]
            )->fetch();
            
            // Calculate total amount
            $nights = $this->calculateNights($data['check_in'], $data['check_out']);
            $totalAmount = $hotel['price_per_night'] * $nights * $data['guests'];
            
            // Create booking
            $bookingCode = $this->generateBookingCode();
            
            $sql = "INSERT INTO hotel_bookings 
                    (external_order_id, external_platform, hotel_id, check_in, check_out, 
                     guests, guest_name, guest_email, total_amount, booking_code, status, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";
            
            $this->db->query($sql, [
                $data['order_id'],
                'traveloka',
                $data['hotel_id'],
                $data['check_in'],
                $data['check_out'],
                $data['guests'],
                $data['guest_name'],
                $data['guest_email'],
                $totalAmount,
                $bookingCode
            ]);
            
            $bookingId = $this->db->lastInsertId();
            
            $this->db->commit();
            
            return [
                'success' => true,
                'booking_id' => $bookingId,
                'booking_code' => $bookingCode,
                'total_amount' => $totalAmount
            ];
            
        } catch (Exception $e) {
            $this->db->rollback();
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    private function calculateNights(string $checkIn, string $checkOut): int {
        $in = new DateTime($checkIn);
        $out = new DateTime($checkOut);
        return $in->diff($out)->days;
    }
    
    private function generateBookingCode(): string {
        return 'TRV-' . strtoupper(uniqid());
    }
}
```

### 3.3 Booking.com Integration

```php
// app/services/BookingComIntegrationService.php
class BookingComIntegrationService {
    
    public function processOrder(array $data): array {
        // Booking.com uses different field names
        $mappedData = [
            'order_id' => $data['reservation_id'],
            'hotel_id' => $this->mapHotelId($data['hotel_code']),
            'check_in' => $data['arrival_date'],
            'check_out' => $data['departure_date'],
            'guests' => $data['number_of_guests'],
            'guest_name' => $data['guest_name'],
            'guest_email' => $data['guest_email']
        ];
        
        // Process similar to Traveloka
        return $this->processGenericOrder($mappedData, 'booking.com');
    }
    
    private function mapHotelId(string $hotelCode): int {
        // Map Booking.com hotel code to internal hotel ID
        $hotel = $this->db->query(
            "SELECT id FROM hotels WHERE booking_com_code = ?",
            [$hotelCode]
        )->fetch();
        
        return $hotel ? $hotel['id'] : 0;
    }
}
```

---

## 4. DATABASE SCHEMA

### 4.1 External Orders Table

```sql
CREATE TABLE external_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    external_order_id VARCHAR(100) NOT NULL,
    external_platform VARCHAR(50) NOT NULL,
    hotel_id INT NOT NULL,
    check_in DATE NOT NULL,
    check_out DATE NOT NULL,
    guests INT NOT NULL,
    guest_name VARCHAR(255) NOT NULL,
    guest_email VARCHAR(255) NOT NULL,
    guest_phone VARCHAR(20) NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'IDR',
    booking_code VARCHAR(50) NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled', 'completed', 'refunded') DEFAULT 'pending',
    payment_status ENUM('unpaid', 'paid', 'failed', 'refunded') DEFAULT 'unpaid',
    external_data JSON NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY idx_external_order (external_platform, external_order_id),
    INDEX idx_hotel_id (hotel_id),
    INDEX idx_status (status),
    INDEX idx_dates (check_in, check_out)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 4.2 Webhook Logs Table

```sql
CREATE TABLE webhook_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    platform VARCHAR(50) NOT NULL,
    event_type VARCHAR(50) NOT NULL,
    payload JSON NOT NULL,
    response JSON NULL,
    status_code INT NULL,
    processed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_platform (platform),
    INDEX idx_event_type (event_type),
    INDEX idx_processed_at (processed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## 5. PAYMENT INTEGRATION

### 5.1 Payment Processing for External Orders

```php
// app/services/ExternalPaymentService.php
class ExternalPaymentService {
    
    public function processPayment(int $externalOrderId): array {
        // Get external order
        $order = $this->db->query(
            "SELECT * FROM external_orders WHERE id = ?",
            [$externalOrderId]
        )->fetch();
        
        if (!$order) {
            return ['success' => false, 'message' => 'Order not found'];
        }
        
        // Create payment via payment gateway
        $paymentService = new PaymentGatewayService();
        
        $paymentData = [
            'order_id' => $order['external_order_id'],
            'amount' => $order['total_amount'],
            'description' => "Booking {$order['booking_code']} - {$order['guest_name']}",
            'customer_email' => $order['guest_email'],
            'customer_name' => $order['guest_name'],
            'external_platform' => $order['external_platform']
        ];
        
        $payment = $paymentService->createPayment($paymentData);
        
        if (!$payment['success']) {
            return [
                'success' => false,
                'message' => 'Payment creation failed',
                'error' => $payment['error']
            ];
        }
        
        // Update order with payment info
        $this->db->query(
            "UPDATE external_orders SET payment_id = ?, payment_url = ? WHERE id = ?",
            [$payment['payment_id'], $payment['payment_url'], $externalOrderId]
        );
        
        return [
            'success' => true,
            'payment_url' => $payment['payment_url'],
            'payment_id' => $payment['payment_id']
        ];
    }
    
    public function handlePaymentCallback(array $data): void {
        $paymentId = $data['payment_id'];
        $status = $data['status'];
        
        // Update external order payment status
        if ($status === 'paid') {
            $this->db->query(
                "UPDATE external_orders SET payment_status = 'paid', status = 'confirmed' 
                 WHERE payment_id = ?",
                [$paymentId]
            );
            
            // Send confirmation to external platform
            $this->notifyPlatform($paymentId, 'confirmed');
        } elseif ($status === 'failed') {
            $this->db->query(
                "UPDATE external_orders SET payment_status = 'failed', status = 'cancelled' 
                 WHERE payment_id = ?",
                [$paymentId]
            );
            
            $this->notifyPlatform($paymentId, 'cancelled');
        }
    }
    
    private function notifyPlatform(string $paymentId, string $status): void {
        // Get order details
        $order = $this->db->query(
            "SELECT eo.*, h.name as hotel_name 
             FROM external_orders eo 
             JOIN hotels h ON eo.hotel_id = h.id 
             WHERE eo.payment_id = ?",
            [$paymentId]
        )->fetch();
        
        // Send webhook to platform
        $webhookUrl = $this->getPlatformWebhookUrl($order['external_platform']);
        
        $payload = [
            'event' => 'payment_status_changed',
            'order_id' => $order['external_order_id'],
            'status' => $status,
            'booking_code' => $order['booking_code'],
            'timestamp' => date('c')
        ];
        
        $this->sendWebhook($webhookUrl, $payload);
    }
}
```

---

## 6. API ENDPOINTS

### 6.1 Webhook Endpoints

| Method | URL | Description |
|--------|-----|-------------|
| POST | `/api/webhook/traveloka/order` | Receive order from Traveloka |
| POST | `/api/webhook/booking.com/order` | Receive order from Booking.com |
| POST | `/api/webhook/agoda/order` | Receive order from Agoda |
| POST | `/api/webhook/payment/callback` | Payment callback handler |

### 6.2 Management Endpoints

| Method | URL | Description |
|--------|-----|-------------|
| GET | `/api/admin/external-orders` | List all external orders |
| GET | `/api/admin/external-orders/{id}` | Get external order detail |
| POST | `/api/admin/external-orders/{id}/sync` | Sync status with platform |
| POST | `/api/admin/external-orders/{id}/cancel` | Cancel external order |

---

## 7. CONFIGURATION

### 7.1 Environment Variables

```bash
# Traveloka
TRAVELOKA_API_KEY=your_traveloka_api_key
TRAVELOKA_API_SECRET=your_traveloka_secret
TRAVELOKA_WEBHOOK_URL=https://api.tourguide.com/api/webhook/traveloka/order

# Booking.com
BOOKING_COM_API_KEY=your_booking_com_key
BOOKING_COM_PARTNER_ID=your_partner_id
BOOKING_COM_WEBHOOK_URL=https://api.tourguide.com/api/webhook/booking.com/order

# Agoda
AGODA_API_KEY=your_agoda_key
AGODA_PARTNER_ID=your_partner_id
AGODA_WEBHOOK_URL=https://api.tourguide.com/api/webhook/agoda/order

# Webhook Security
WEBHOOK_SECRET=your_webhook_secret_key
```

### 7.2 Platform Configuration

```php
// app/config/platforms.php
return [
    'traveloka' => [
        'api_key' => $_ENV['TRAVELOKA_API_KEY'],
        'api_secret' => $_ENV['TRAVELOKA_API_SECRET'],
        'webhook_url' => $_ENV['TRAVELOKA_WEBHOOK_URL'],
        'supported' => true
    ],
    'booking.com' => [
        'api_key' => $_ENV['BOOKING_COM_API_KEY'],
        'partner_id' => $_ENV['BOOKING_COM_PARTNER_ID'],
        'webhook_url' => $_ENV['BOOKING_COM_WEBHOOK_URL'],
        'supported' => true
    ],
    'agoda' => [
        'api_key' => $_ENV['AGODA_API_KEY'],
        'partner_id' => $_ENV['AGODA_PARTNER_ID'],
        'webhook_url' => $_ENV['AGODA_WEBHOOK_URL'],
        'supported' => false // Enable when ready
    ]
];
```

---

## 8. ERROR HANDLING

### 8.1 Common Errors

| Error | Cause | Solution |
|-------|--------|----------|
| Invalid signature | Webhook signature mismatch | Check WEBHOOK_SECRET |
| Missing fields | Required fields not in payload | Validate before processing |
| Room unavailable | No rooms available for dates | Return error to platform |
| Duplicate order | Order ID already exists | Check before creating |
| Payment failed | Payment gateway error | Retry or cancel order |

### 8.2 Error Response Format

```json
{
    "status": "error",
    "message": "Room not available",
    "error_code": "ROOM_UNAVAILABLE",
    "details": {
        "reason": "No rooms available for selected dates",
        "available_dates": ["2026-07-05", "2026-07-06"]
    }
}
```

---

## 9. SECURITY CONSIDERATIONS

### 9.1 Webhook Security

- **Signature Validation:** Validate HMAC signature for all webhooks
- **IP Whitelist:** Only accept requests from known platform IPs
- **Rate Limiting:** Implement rate limiting for webhook endpoints
- **TLS:** Use HTTPS for all webhook communications

### 9.2 Data Protection

- **PII Protection:** Encrypt guest personal information
- **PCI Compliance:** Handle payment data securely
- **Data Retention:** Follow data retention policies
- **Access Control:** Restrict access to external order data

---

## 10. TESTING

### 10.1 Webhook Testing

```bash
# Test webhook with curl
curl -X POST https://api.tourguide.com/api/webhook/traveloka/order \
  -H "Content-Type: application/json" \
  -H "X-Signature: $(echo -n '{"test":"data"}' | openssl dgst -sha256 -hmac 'secret' -binary | base64)" \
  -d '{
    "platform": "traveloka",
    "order_id": "TLO-123456",
    "hotel_id": 1,
    "check_in": "2026-07-01",
    "check_out": "2026-07-03",
    "guests": 2,
    "guest_name": "John Doe",
    "guest_email": "john@example.com"
  }'
```

### 10.2 Integration Testing

```php
// tests/Integration/TravelokaIntegrationTest.php
class TravelokaIntegrationTest extends TestCase {
    public function testProcessValidOrder() {
        $service = new TravelokaIntegrationService();
        
        $data = [
            'order_id' => 'TLO-TEST-001',
            'hotel_id' => 1,
            'check_in' => '2026-07-01',
            'check_out' => '2026-07-03',
            'guests' => 2,
            'guest_name' => 'Test User',
            'guest_email' => 'test@example.com'
        ];
        
        $result = $service->processOrder($data);
        
        $this->assertTrue($result['status'] === 'success');
        $this->assertArrayHasKey('booking_id', $result);
    }
}
```

---

## 11. MONITORING

### 11.1 Webhook Monitoring

```php
// Log all webhook requests
class WebhookLogger {
    public function logRequest(string $platform, string $eventType, array $payload, array $response = null, int $statusCode = null): void {
        $sql = "INSERT INTO webhook_logs 
                (platform, event_type, payload, response, status_code, processed_at) 
                VALUES (?, ?, ?, ?, ?, NOW())";
        
        $this->db->query($sql, [
            $platform,
            $eventType,
            json_encode($payload),
            json_encode($response),
            $statusCode
        ]);
    }
}
```

### 11.2 Alerting

- Alert on webhook failures
- Alert on payment failures
- Alert on duplicate orders
- Alert on rate limit violations

---

## 12. BEST PRACTICES

### 12.1 Idempotency

Ensure webhook processing is idempotent:

```php
public function processOrder(array $data): array {
    // Check if order already processed
    $existing = $this->db->query(
        "SELECT * FROM external_orders 
         WHERE external_order_id = ? AND external_platform = ?",
        [$data['order_id'], $data['platform']]
    )->fetch();
    
    if ($existing) {
        return [
            'status' => 'success',
            'booking_id' => $existing['id'],
            'booking_code' => $existing['booking_code'],
            'message' => 'Order already processed'
        ];
    }
    
    // Process new order...
}
```

### 12.2 Retry Logic

Implement retry logic for failed webhooks:

```php
class WebhookRetryService {
    public function retryFailedWebhook(int $logId): bool {
        $log = $this->db->query(
            "SELECT * FROM webhook_logs WHERE id = ?",
            [$logId]
        )->fetch();
        
        if (!$log) {
            return false;
        }
        
        // Re-process webhook
        $payload = json_decode($log['payload'], true);
        $result = $this->processWebhook($payload);
        
        // Update log
        $this->db->query(
            "UPDATE webhook_logs SET response = ?, status_code = ? WHERE id = ?",
            [json_encode($result), $result['status_code'], $logId]
        );
        
        return $result['success'];
    }
}
```

---

## 13. PLATFORM-SPECIFIC NOTES

### 13.1 Traveloka

- **API Documentation:** https://www.traveloka.com/partner/api
- **Webhook Format:** JSON with HMAC-SHA256 signature
- **Supported Operations:** Booking, Cancellation, Modification
- **Payment:** Traveloka handles payment, sends confirmation

### 13.2 Booking.com

- **API Documentation:** https://partners.booking.com/
- **Webhook Format:** XML or JSON
- **Supported Operations:** Booking, Cancellation, Modification
- **Payment:** Booking.com handles payment

### 13.3 Agoda

- **API Documentation:** https://partners.agoda.com/
- **Webhook Format:** JSON
- **Supported Operations:** Booking, Cancellation
- **Payment:** Agoda handles payment

---

## 14. CHECKLIST INTEGRATION

### 14.1 Pre-Integration Checklist

- [ ] Register as partner on platform
- [ ] Obtain API keys and credentials
- [ ] Configure webhook URLs
- [ ] Set up hotel mapping
- [ ] Test webhook endpoint
- [ ] Implement signature validation
- [ ] Configure payment gateway
- [ ] Set up monitoring

### 14.2 Post-Integration Checklist

- [ ] Test booking flow end-to-end
- [ ] Test cancellation flow
- [ ] Test payment callback
- [ ] Monitor webhook success rate
- [ ] Monitor error rates
- [ ] Set up alerts
- [ ] Document integration
- [ ] Train support team

---

## 15. TROUBLESHOOTING

### 15.1 Webhook Not Received

**Possible Causes:**
- Webhook URL incorrect
- Platform not sending webhooks
- Firewall blocking requests
- SSL certificate issues

**Solutions:**
- Verify webhook URL in platform dashboard
- Check platform webhook logs
- Check firewall rules
- Verify SSL certificate

### 15.2 Signature Validation Failed

**Possible Causes:**
- Secret key mismatch
- Encoding issues
- Timestamp expired

**Solutions:**
- Verify WEBHOOK_SECRET
- Check encoding (UTF-8)
- Check timestamp tolerance

### 15.3 Order Not Created

**Possible Causes:**
- Hotel ID not mapped
- Room unavailable
- Database error

**Solutions:**
- Verify hotel mapping
- Check room availability
- Check database logs

---

## 16. RESOURCES

### 16.1 Platform Documentation

- **Traveloka Partner API:** https://www.traveloka.com/partner/api
- **Booking.com Partner Hub:** https://partners.booking.com/
- **Agoda Partner Program:** https://partners.agoda.com/

### 16.2 Tools

- **Postman:** API testing
- **ngrok:** Webhook testing (local development)
- **RequestBin:** Webhook debugging

---

> **End of Module 42**
