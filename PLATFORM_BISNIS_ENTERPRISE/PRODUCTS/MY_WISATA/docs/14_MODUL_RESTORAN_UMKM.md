# MODUL 14 — MODUL RESTORAN & UMKM

> **Versi:** 1.1 · **Tanggal:** 2026-06-30 · **Last Updated:** 2026-06-30

---

## 1. RINGKASAN

Modul untuk pendaftaran restoran/UMMK, manajemen menu, dan pemesanan makanan.

---

## 2. ALUR

```
Pemilik daftar restoran → Admin approve → Wisatawan cari
→ Lihat menu → Tambah ke keranjang → Checkout
→ Create order (pending) + transaction → Restoran confirm
→ preparing → ready → completed → Review
```

---

## 3. CONTROLLER

```php
<?php
class RestaurantController extends Controller {

    public function register() {
        Middleware::requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $this->validateInput($_POST);
            $imagePath = Helper::uploadFile($_FILES['main_image'], 'uploads/restaurants/');
            $model = $this->model('Restaurant');
            $id = $model->insert(array_merge($input, [
                'owner_id' => $_SESSION['user_id'],
                'main_image' => $imagePath,
                'is_approved' => 0
            ]));
            $this->json(['status' => 'success', 'message' => 'Restoran terdaftar, menunggu approval']);
        }
    }

    public function search() {
        $model = $this->model('Restaurant');
        $data = $model->searchApproved([
            'city' => $_GET['city'] ?? null,
            'type' => $_GET['type'] ?? null,
            'cuisine' => $_GET['cuisine'] ?? null
        ]);
        $this->json(['status' => 'success', 'data' => $data]);
    }

    public function placeOrder() {
        Middleware::requireAuth();
        $input = json_decode(file_get_contents('php://input'), true);

        $items = $input['items'] ?? [];
        if (empty($items)) {
            $this->json(['status' => 'error', 'message' => 'Keranjang kosong'], 400);
        }

        // Calculate total
        $menuModel = $this->model('MenuItem');
        $total = 0;
        foreach ($items as $item) {
            $menu = $menuModel->find($item['menu_item_id']);
            if (!$menu || !$menu['is_available']) {
                $this->json(['status' => 'error', 'message' => 'Menu tidak tersedia'], 400);
            }
            $total += $menu['price'] * $item['quantity'];
        }

        // Create order
        $orderModel = $this->model('RestaurantOrder');
        $code = $orderModel->generateCode();
        $orderId = $orderModel->insert([
            'order_code' => $code,
            'user_id' => $_SESSION['user_id'],
            'restaurant_id' => $input['restaurant_id'],
            'order_type' => $input['order_type'] ?? 'dine_in',
            'total_amount' => $total,
            'status' => 'pending',
            'notes' => $input['notes'] ?? null
        ]);

        // Insert items
        foreach ($items as $item) {
            $menu = $menuModel->find($item['menu_item_id']);
            $orderModel->insertItem($orderId, $item['menu_item_id'], $item['quantity'], $menu['price'] * $item['quantity']);
        }

        // Create transaction
        $trxModel = $this->model('Transaction');
        $trxId = $trxModel->insert([
            'transaction_code' => $trxModel->generateTrxCode(),
            'user_id' => $_SESSION['user_id'],
            'type' => 'restaurant',
            'reference_id' => $orderId,
            'gross_amount' => $total,
            'net_amount' => $total,
            'payment_method' => $input['payment_method'] ?? 'cash',
            'payment_status' => 'pending'
        ]);
        $orderModel->update($orderId, ['transaction_id' => $trxId]);

        // Notify restaurant owner
        $restaurant = $this->model('Restaurant')->find($input['restaurant_id']);
        $notifModel = $this->model('Notification');
        $notifModel->insert([
            'user_id' => $restaurant['owner_id'],
            'type' => 'booking',
            'title' => 'Pesanan Baru',
            'message' => "Pesanan baru: {$code}"
        ]);

        $this->json(['status' => 'success', 'data' => ['order_id' => $orderId, 'code' => $code, 'total' => $total]]);
    }

    // Pemilik: update status order
    public function updateOrderStatus($orderId) {
        Middleware::requireAuth();
        $input = json_decode(file_get_contents('php://input'), true);
        $orderModel = $this->model('RestaurantOrder');
        $order = $orderModel->find($orderId);
        $restaurant = $this->model('Restaurant')->find($order['restaurant_id']);

        if ($restaurant['owner_id'] != $_SESSION['user_id'] && $_SESSION['role'] !== 'admin') {
            $this->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $orderModel->update($orderId, ['status' => $input['status']]);

        // Notify user
        $notifModel = $this->model('Notification');
        $notifModel->insert([
            'user_id' => $order['user_id'],
            'type' => 'booking',
            'title' => 'Status Pesanan Update',
            'message' => "Pesanan {$order['order_code']}: " . ucfirst($input['status'])
        ]);

        $this->json(['status' => 'success', 'message' => 'Status diupdate']);
    }
}
```

---

## 4. API ENDPOINTS

| Method | URL | Fungsi |
|--------|-----|--------|
| POST | `api/restaurant/register` | Daftar restoran |
| GET | `api/restaurants` | Cari restoran |
| GET | `api/restaurant/{id}` | Detail + menu |
| POST | `api/restaurant/order` | Place order |
| GET | `api/restaurant/my-orders` | Pesanan saya |
| POST | `api/restaurant/order/status/{id}` | Update status (pemilik) |
| POST | `api/restaurant/menu/add` | Tambah menu |
| POST | `api/admin/restaurant/approve/{id}` | Admin approve |

---

## 5. VIEW: Keranjang & Checkout (jQuery)

```javascript
let cart = [];

function addToCart(menuId, name, price) {
    let existing = cart.find(i => i.menu_item_id === menuId);
    if (existing) {
        existing.quantity++;
    } else {
        cart.push({ menu_item_id: menuId, name: name, price: price, quantity: 1 });
    }
    renderCart();
}

function renderCart() {
    let html = '';
    let total = 0;
    cart.forEach((item, idx) => {
        html += `<tr><td>${item.name}</td><td>Rp ${formatRupiah(item.price)}</td>
                 <td><input type="number" value="${item.quantity}" min="1"
                     onchange="updateQty(${idx}, this.value)"></td>
                 <td>Rp ${formatRupiah(item.price * item.quantity)}</td>
                 <td><button class="btn btn-sm btn-danger" onclick="removeItem(${idx})">×</button></td></tr>`;
        total += item.price * item.quantity;
    });
    $('#cart-body').html(html);
    $('#cart-total').text('Rp ' + formatRupiah(total));
}

function checkout(restaurantId) {
    if (cart.length === 0) { Swal.fire('Keranjang kosong', '', 'warning'); return; }
    let data = {
        restaurant_id: restaurantId,
        order_type: $('#order_type').val(),
        items: cart.map(i => ({ menu_item_id: i.menu_item_id, quantity: i.quantity })),
        csrf_token: CSRF_TOKEN
    };
    API.post('restaurant/order', data, function(res) {
        Swal.fire('Pesanan Berhasil!', 'Kode: ' + res.data.code, 'success')
           .then(() => location.href = BASE_URL + 'wisatawan/my-orders');
    });
}
```

---

## 7. INTEGRATION REMINDERS

**Pastikan integrasi berikut sebelum production:**

- [ ] **Validation:** Validasi stok menu sebelum order
- [ ] **Security:** Rate limiting untuk order dan menu access
- [ ] **Payment:** Integrasi dengan payment gateway
- [ ] **Notification:** Kirim notifikasi ke restoran saat ada order baru
- [ ] **Analytics:** Track menu popularity dan order conversion
- [ ] **Cancellation:** Implementasi cancellation policy
- [ ] **Review:** Integrasi dengan modul review untuk rating restoran
- [ ] **Map:** Integrasi dengan modul map untuk lokasi restoran

---

> **Modul Selanjutnya:** `15_MODUL_EVENT_BUDAYA.md`
