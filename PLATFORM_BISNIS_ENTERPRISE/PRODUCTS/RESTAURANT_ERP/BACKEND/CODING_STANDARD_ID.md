# Panduan Coding Standard EBP - Bahasa Indonesia

**Versi:** 1.0  
**Tanggal:** 3 Juli 2026  
**Platform:** Enterprise Business Platform (EBP)

---

## 1. Prinsip Dasar

### 1.1 Bahasa yang Digunakan

**Bahasa Indonesia untuk:**
- Komentar code
- Dokumentasi
- Variable names yang bersifat business logic
- API response messages
- UI text
- Nama file yang berkaitan dengan business logic

**Bahasa Inggris untuk:**
- Variable names teknis (best practice industri)
- Function names
- Class names
- Method names
- API endpoint paths
- Technical terms
- Keywords pemrograman

### 1.2 Contoh

```php
// ✅ BENAR - Komentar bahasa Indonesia, variable teknis bahasa Inggris
class OrderController {
    // Mendapatkan semua order berdasarkan tenant
    public function getAllOrders($tenantId) {
        $orders = $this->orderService->getByTenant($tenantId);
        return Response::success($orders, 'Data order berhasil diambil');
    }
}

// ❌ SALAH - Variable names bahasa Indonesia
class KontrolerPesanan {
    public function dapatkanSemuaPesanan($idTenant) {
        // ...
    }
}
```

---

## 2. Penamaan (Naming Conventions)

### 2.1 Class Names

- Gunakan PascalCase
- Gunakan bahasa Inggris untuk class teknis
- Gunakan bahasa Indonesia untuk class business logic jika relevan

```php
// Class teknis - Bahasa Inggris
class Database
class Router
class Response
class JWT

// Class business logic - Bisa bahasa Indonesia jika lebih natural
class OrderService
class PesananService  // Jika lebih natural untuk tim
class InventoryManager
class ManajerStok    // Jika lebih natural untuk tim
```

### 2.2 Function/Method Names

- Gunakan camelCase
- Gunakan bahasa Inggris (standar industri)

```php
public function getUserById($id)
public function createOrder($data)
public function updateInventory($productId, $quantity)
public function hapusPesanan($orderId)  // Jika lebih natural
```

### 2.3 Variable Names

- Gunakan camelCase
- Bahasa Inggris untuk variable teknis
- Bahasa Indonesia untuk variable business logic jika relevan

```php
// Variable teknis - Bahasa Inggris
$userId = 1;
$database = new Database();
$response = [];

// Variable business logic - Bisa bahasa Indonesia
$totalHarga = 100000;
$jumlahPesanan = 5;
$namaPelanggan = "Budi";
$stokTersedia = 10;
```

### 2.4 Constants

- Gunakan UPPER_SNAKE_CASE
- Bahasa Indonesia untuk constants business logic

```php
define('STATUS_PESANAN_PENDING', 'PENDING');
define('STATUS_PESANAN_SELESAI', 'COMPLETED');
define('MAX_PESANAN_PER_HARI', 100);
define('JAM_OPERASIAL_MULAI', '08:00');
```

---

## 3. Komentar Code

### 3.1 Aturan Umum

- Gunakan bahasa Indonesia untuk semua komentar
- Jelaskan "MENGAPA" bukan "APA"
- Komentar harus relevan dan up-to-date

```php
// ✅ BENAR
// Menghitung total harga dengan pajak dan diskon
// Formula: (subtotal * (1 + pajak)) - diskon
$totalHarga = ($subtotal * (1 + $pajak)) - $diskon;

// ❌ SALAH
// Hitung total
$total = $x * $y - $z;
```

### 3.2 Komentar Multi-line

```php
/**
 * Membuat pesanan baru dengan validasi lengkap
 * 
 * Proses:
 * 1. Validasi data input
 * 2. Cek ketersediaan stok
 * 3. Buat record pesanan
 * 4. Kurangi stok (Stock Engine)
 * 5. Buat kitchen order (Kitchen Engine)
 * 6. Catat audit trail
 * 
 * @param array $data Data pesanan
 * @return array Response dengan data pesanan
 * @throws Exception Jika validasi gagal
 */
public function createOrder($data) {
    // ...
}
```

---

## 4. API Response Messages

### 4.1 Format Response

```php
// Response structure
{
    "success": true/false,
    "message": "Pesan dalam bahasa Indonesia",
    "data": {} // atau []
}
```

### 4.2 Contoh Messages

```php
// Success messages
Response::success($data, 'Data berhasil disimpan');
Response::success($data, 'Pesanan berhasil dibuat');
Response::success($data, 'Stok berhasil diperbarui');
Response::success($data, 'Login berhasil');

// Error messages
Response::error('Data tidak ditemukan', 404);
Response::error('Permission ditolak', 403);
Response::error('Validasi gagal', 400, $errors);
Response::error('Terjadi kesalahan server', 500);
```

### 4.3 Messages yang Konsisten

```php
// CRUD operations
'Berhasil dibuat'      // Create
'Berhasil diperbarui'  // Update
'Berhasil dihapus'     // Delete
'Data berhasil diambil' // Read

// Authentication
'Login berhasil'
'Logout berhasil'
'Token tidak valid'
'Sesi telah berakhir'

// Validation
'Data tidak valid'
'Field wajib diisi'
'Format email salah'
'Nilai terlalu kecil/besar'

// Business logic
'Stok tidak mencukupi'
'Pesanan sudah selesai'
'User tidak aktif'
'Tenant tidak ditemukan'
```

---

## 5. Database

### 5.1 Table Names

- Gunakan snake_case
- Bahasa Inggris untuk table teknis
- Bahasa Indonesia untuk table business logic jika relevan

```sql
-- Table teknis
users
roles
permissions
audit_logs

-- Table business logic
orders
pesanan        -- Jika lebih natural
products
produk         -- Jika lebih natural
inventory
stok           -- Jika lebih natural
```

### 5.2 Column Names

- Gunakan snake_case
- Bahasa Inggris untuk column teknis
- Bahasa Indonesia untuk column business logic jika relevan

```sql
-- Column teknis
user_id
created_at
updated_at
deleted_at

-- Column business logic
order_number
nomor_pesanan  -- Jika lebih natural
total_amount
total_harga    -- Jika lebih natural
customer_name
nama_pelanggan -- Jika lebih natural
```

### 5.3 Comments

```sql
CREATE TABLE orders (
    order_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    -- ID tenant untuk multi-tenant isolation
    tenant_id BIGINT UNSIGNED NOT NULL,
    -- Nomor pesanan unik per tenant
    order_number VARCHAR(50) NOT NULL,
    -- Status pesanan: PENDING, CONFIRMED, PREPARING, READY, SERVED, COMPLETED, CANCELLED
    status ENUM('PENDING', 'CONFIRMED', 'PREPARING', 'READY', 'SERVED', 'COMPLETED', 'CANCELLED') DEFAULT 'PENDING',
    -- Total harga sebelum pajak dan diskon
    subtotal DECIMAL(10,2) NOT NULL,
    -- Total harga setelah pajak dan diskon
    total_amount DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (order_id)
);
```

---

## 6. Frontend

### 6.1 HTML/CSS Classes

- Gunakan kebab-case untuk CSS classes
- Bahasa Indonesia untuk UI-related classes jika relevan

```html
<!-- Class teknis -->
<div class="container">
<div class="row">
<div class="col-md-6">

<!-- Class business logic -->
<div class="tombol-simpan">
<div class="daftar-pesanan">
<div class="form-input-nama">
```

### 6.2 JavaScript

```javascript
// Variable teknis - Bahasa Inggris
const userId = 1;
const apiUrl = '/api/v1/orders';

// Variable business logic - Bisa bahasa Indonesia
const totalHarga = 100000;
const daftarPesanan = [];
const namaPelanggan = '';

// Function names - Bahasa Inggris
function fetchOrders() { }
function validateForm() { }
function tampilkanPesanan() { } // Jika lebih natural
```

### 6.3 UI Text

```html
<!-- Labels -->
<label for="nama">Nama Pelanggan</label>
<label for="email">Email</label>
<label for="total">Total Harga</label>

<!-- Buttons -->
<button type="submit">Simpan</button>
<button type="button">Hapus</button>
<button type="button">Batal</button>

<!-- Messages -->
<div class="alert alert-success">Data berhasil disimpan</div>
<div class="alert alert-error">Terjadi kesalahan</div>
```

---

## 7. Error Handling

### 7.1 Exception Messages

```php
// Gunakan bahasa Indonesia
throw new Exception('Data pesanan tidak ditemukan');
throw new Exception('Stok tidak mencukupi');
throw new Exception('User tidak memiliki permission');
throw new Exception('Koneksi database gagal');
```

### 7.2 Log Messages

```php
// Log dalam bahasa Indonesia
error_log('Gagal membuat pesanan: ' . $e->getMessage());
error_log('User login berhasil: ' . $username);
error_log('Stok diperbarui: ' . $productId);
```

---

## 8. Testing

### 8.1 Test Names

```php
// Test descriptions dalam bahasa Indonesia
public function test_membuat_pesanan_baru_berhasil()
public function test_login_dengan_kredensial_valid()
public function test_update_stok_ketika_stok_tidak_cukupi_gagal()
```

### 8.2 Test Comments

```php
/**
 * Test untuk memastikan pesanan berhasil dibuat
 * dengan data yang valid
 */
public function test_membuat_pesanan_baru_berhasil() {
    // Siapkan data test
    $data = [
        'customer_name' => 'Budi Santoso',
        'items' => [...]
    ];
    
    // Eksekusi test
    $response = $this->post('/api/v1/orders', $data);
    
    // Assert response
    $response->assertStatus(200);
    $response->assertJson(['success' => true]);
}
```

---

## 9. Dokumentasi

### 9.1 README

- Gunakan bahasa Indonesia
- Jelaskan dengan jelas dan ringkas

```markdown
# EBP Restaurant Backend

Sistem manajemen restoran untuk Enterprise Business Platform.

## Fitur

- Manajemen pesanan
- Manajemen stok
- Manajemen kitchen
- Multi-tenant support
- RBAC (Role-Based Access Control)

## Instalasi

1. Clone repository
2. Import database
3. Konfigurasi environment
4. Jalankan server
```

### 9.2 API Documentation

```markdown
## Membuat Pesanan

**Endpoint:** POST /api/v1/orders

**Deskripsi:** Membuat pesanan baru

**Request Body:**
```json
{
  "customer_name": "Budi Santoso",
  "items": [...]
}
```

**Response:**
```json
{
  "success": true,
  "message": "Pesanan berhasil dibuat",
  "data": {...}
}
```
```

---

## 10. Best Practices Tambahan

### 10.1 Konsistensi

- Pilih satu gaya dan konsisten
- Jangan campur bahasa dalam satu file
- Diskusikan dengan tim untuk standar

### 10.2 Readability

- Code harus mudah dibaca
- Komentar harus membantu, bukan mengganggu
- Gunakan nama yang deskriptif

### 10.3 Maintainability

- Code harus mudah di-maintain
- Dokumentasi harus up-to-date
- Refactor jika code sudah kompleks

---

## 11. Contoh Lengkap

### 11.1 Controller Example

```php
<?php

/**
 * Controller untuk manajemen pesanan
 * 
 * Menangani semua operasi CRUD untuk pesanan
 * termasuk validasi, business logic, dan response
 */
class OrderController
{
    private $orderService;
    private $stockEngine;
    private $kitchenEngine;

    /**
     * Constructor untuk dependency injection
     */
    public function __construct()
    {
        $this->orderService = new OrderService();
        $this->stockEngine = new StockEngine();
        $this->kitchenEngine = new KitchenEngine();
    }

    /**
     * Mendapatkan semua pesanan berdasarkan tenant
     * 
     * @param array $request Request data
     * @return void JSON response
     */
    public function getAllOrders($request)
    {
        try {
            // Ambil tenant_id dari request (dari JWT)
            $tenantId = $request['tenant_id'];
            
            // Ambil semua pesanan
            $orders = $this->orderService->getByTenant($tenantId);
            
            // Return response
            Response::success($orders, 'Data pesanan berhasil diambil');
        } catch (Exception $e) {
            // Log error
            error_log('Gagal mengambil pesanan: ' . $e->getMessage());
            
            // Return error response
            Response::error('Gagal mengambil data pesanan', 500);
        }
    }

    /**
     * Membuat pesanan baru
     * 
     * Proses:
     * 1. Validasi data input
     * 2. Cek ketersediaan stok
     * 3. Buat record pesanan
     * 4. Kurangi stok (Stock Engine)
     * 5. Buat kitchen order (Kitchen Engine)
     * 6. Catat audit trail
     * 
     * @param array $request Request data
     * @return void JSON response
     */
    public function createOrder($request)
    {
        try {
            // Validasi data input
            $validator = new Validator();
            $errors = $validator->validate($request['body'], [
                'customer_name' => 'required',
                'items' => 'required|array'
            ]);
            
            if (!empty($errors)) {
                Response::error('Data tidak valid', 400, $errors);
            }
            
            // Cek ketersediaan stok
            foreach ($request['body']['items'] as $item) {
                $stokTersedia = $this->stockEngine->checkAvailability(
                    $item['product_id'],
                    $item['quantity']
                );
                
                if (!$stokTersedia) {
                    Response::error('Stok tidak mencukupi untuk produk: ' . $item['product_id']);
                }
            }
            
            // Buat pesanan dalam transaction
            $database = new Database();
            $db = $database->connect();
            $db->beginTransaction();
            
            try {
                // Buat record pesanan
                $orderId = $this->orderService->create($request['body']);
                
                // Kurangi stok
                $this->stockEngine->deductFromRecipe($orderId, $request['branch_id']);
                
                // Buat kitchen order
                $this->kitchenEngine->createKitchenOrder($orderId);
                
                // Commit transaction
                $db->commit();
                
                // Return success response
                Response::success(
                    ['order_id' => $orderId],
                    'Pesanan berhasil dibuat'
                );
            } catch (Exception $e) {
                // Rollback jika ada error
                $db->rollBack();
                throw $e;
            }
        } catch (Exception $e) {
            // Log error
            error_log('Gagal membuat pesanan: ' . $e->getMessage());
            
            // Return error response
            Response::error('Gagal membuat pesanan', 500);
        }
    }
}
```

### 11.2 Service Example

```php
<?php

/**
 * Service untuk logika bisnis pesanan
 */
class OrderService
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    /**
     * Mendapatkan pesanan berdasarkan tenant
     * 
     * @param int $tenantId ID tenant
     * @return array Daftar pesanan
     */
    public function getByTenant($tenantId)
    {
        $sql = "
            SELECT 
                o.order_id,
                o.order_number,
                o.status,
                o.total_amount as total_harga,
                o.created_at as tanggal_dibuat,
                c.customer_name as nama_pelanggan
            FROM orders o
            LEFT JOIN customers c ON o.customer_id = c.customer_id
            WHERE o.tenant_id = ?
            AND o.deleted_at IS NULL
            ORDER BY o.created_at DESC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Membuat pesanan baru
     * 
     * @param array $data Data pesanan
     * @return int ID pesanan yang dibuat
     */
    public function create($data)
    {
        // Generate nomor pesanan
        $nomorPesanan = $this->generateOrderNumber($data['tenant_id']);
        
        // Hitung total harga
        $totalHarga = $this->calculateTotal($data['items']);
        
        // Insert pesanan
        $sql = "
            INSERT INTO orders (
                tenant_id,
                branch_id,
                order_number,
                customer_name,
                subtotal,
                total_amount,
                status,
                created_at
            ) VALUES (?, ?, ?, ?, ?, ?, 'PENDING', NOW())
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['branch_id'],
            $nomorPesanan,
            $data['customer_name'],
            $totalHarga,
            $totalHarga
        ]);
        
        return $this->db->lastInsertId();
    }

    /**
     * Generate nomor pesanan unik
     * 
     * Format: ORD-YYYYMMDD-XXXX
     * 
     * @param int $tenantId ID tenant
     * @return string Nomor pesanan
     */
    private function generateOrderNumber($tenantId)
    {
        $date = date('Ymd');
        $sql = "SELECT COUNT(*) as count FROM orders WHERE tenant_id = ? AND DATE(created_at) = CURDATE()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $count = $result['count'] + 1;
        return 'ORD-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Menghitung total harga dari items
     * 
     * @param array $items Daftar item pesanan
     * @return float Total harga
     */
    private function calculateTotal($items)
    {
        $total = 0;
        foreach ($items as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }
}
```

---

## 12. Checklist Review

Sebelum commit code, pastikan:

- [ ] Komentar menggunakan bahasa Indonesia
- [ ] Variable names teknis menggunakan bahasa Inggris
- [ ] API response messages menggunakan bahasa Indonesia
- [ ] Error messages menggunakan bahasa Indonesia
- [ ] Code mudah dibaca dan dimengerti
- [ ] Konsisten dengan standard tim
- [ ] Dokumentasi up-to-date
- [ ] Tidak ada hardcoded values
- [ ] Error handling sudah proper
- [ ] Code sudah di-test

---

**Dokumen ini akan terus di-update sesuai kebutuhan tim development.**
