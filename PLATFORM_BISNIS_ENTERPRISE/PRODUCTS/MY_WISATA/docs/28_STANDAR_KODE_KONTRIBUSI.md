# MODUL 28 — STANDAR KODE & KONTRIBUSI

> **Aplikasi:** Tour Guide Application  
> **Versi:** 1.0  
> **Tanggal:** 2026-06-30

---

## 1. RINGKASAN

Dokumen ini mendefinisikan standar penulisan kode, konvensi naming, Git workflow,
dan panduan kontribusi untuk tim developer Tour Guide Application.

---

## 2. STANDAR PENULISAN PHP

### 2.1 Konvensi Naming

| Elemen | Konvensi | Contoh |
|--------|----------|--------|
| Class | PascalCase | `TourGuideController`, `BookingModel` |
| Method | camelCase | `getByGuide()`, `acceptBooking()` |
| Variable | camelCase | `$bookingId`, `$totalAmount` |
| Constant | UPPER_SNAKE | `MAX_UPLOAD_SIZE`, `BASE_URL` |
| Property (class) | camelCase | `$table`, `$db` |
| File (class) | PascalCase | `TourGuideController.php` |
| File (view) | snake_case | `tour_guide_detail.php` |
| File (config) | lowercase | `database.php`, `config.php` |

### 2.2 Struktur File PHP (Class)

```php
<?php
class TourGuideController extends Controller {

    public function __construct() {
        Middleware::requireRole('tour_guide');
    }

    public function dashboard() {
        $guide = $this->model('TourGuide')->findByUserId($_SESSION['user_id']);
        $this->view('tourguide/dashboard', [
            'title' => 'Dashboard Tour Guide',
            'guide' => $guide
        ]);
    }
}
```

### 2.3 Aturan Penulisan

- Gunakan `<?php` (tidak pernah `<?` short tags)
- Indentasi: **4 spaces** (bukan tab)
- Max line length: **120 karakter**
- Selalu gunakan curly braces `{}` untuk if/else/loop
- Satu class per file

### 2.4 PDO Query Pattern

```php
// BENAR — Prepared statement
$sql = "SELECT * FROM bookings WHERE guide_id = :guide_id AND status = :status";
$result = $this->db->query($sql, [
    'guide_id' => $guideId,
    'status' => 'pending'
])->fetchAll();

// SALAH — Jangan pernah concatenate
$sql = "SELECT * FROM bookings WHERE guide_id = $guideId"; // DILARANG
```

### 2.5 Error Handling

```php
try {
    $this->db->beginTransaction();
    $bookingId = $bookingModel->insert($data);
    $trxId = $trxModel->insert($trxData);
    $this->db->commit();
} catch (PDOException $e) {
    $this->db->rollBack();
    Logger::error('Booking failed', ['error' => $e->getMessage()]);
    $this->json(['status' => 'error', 'message' => 'Terjadi kesalahan'], 500);
}
```

---

## 3. STANDAR PENULISAN JAVASCRIPT

### 3.1 Konvensi Naming

| Elemen | Konvensi | Contoh |
|--------|----------|--------|
| Function | camelCase | `loadMarkers()`, `addToCart()` |
| Variable | camelCase | `bookingId`, `totalAmount` |
| Constant | UPPER_SNAKE | `API_BASE_URL`, `CSRF_TOKEN` |
| jQuery selector | $ + camelCase | `$formBtn`, `$modal` |

### 3.2 Aturan Penulisan

- Indentasi: **2 spaces**
- Gunakan `const` / `let` (tidak pernah `var`)
- Semua AJAX melalui `API.post()` / `API.get()` helper
- Selalu sertakan `CSRF_TOKEN` di request
- Gunakan template literals (backtick) untuk HTML string

### 3.3 AJAX Pattern

```javascript
API.post('booking/create', {
    guide_id: guideId,
    booking_date: date,
    csrf_token: CSRF_TOKEN
}, function(response) {
    Swal.fire('Berhasil!', response.message, 'success')
       .then(() => location.reload());
});
```

### 3.4 SweetAlert2 Pattern

```javascript
Swal.fire({
    title: 'Hapus data ini?',
    text: 'Tindakan ini tidak dapat dibatalkan',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Ya, Hapus',
    cancelButtonText: 'Batal',
    confirmButtonColor: '#dc3545'
}).then((result) => {
    if (result.isConfirmed) {
        // Execute delete
    }
});
```

---

## 4. STANDAR PENULISAN CSS

- Gunakan class Bootstrap 5 sebanyak mungkin
- Custom CSS hanya untuk override atau komponen unik
- Naming: `kebab-case` (contoh: `.booking-card`, `.guide-avatar`)
- Prefix custom class: `tg-` untuk avoid conflict
- Indentasi: **2 spaces**

```css
:root {
    --tg-primary: #0d6efd;
    --tg-success: #198754;
}

.tg-guide-avatar {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
}
```

---

## 5. STANDAR PENULISAN SQL

| Elemen | Konvensi | Contoh |
|--------|----------|--------|
| Table | snake_case | `tour_guides`, `ticket_orders` |
| Column | snake_case | `user_id`, `created_at` |
| Index | idx_ + nama | `idx_user_status` |
| Primary key | `id` | `id BIGINT UNSIGNED` |

```sql
-- Keyword: UPPERCASE, nama: lowercase
SELECT b.id, b.booking_code, u.name AS wisatawan_name
FROM bookings b
INNER JOIN users u ON b.user_id = u.id
WHERE b.status = 'pending'
ORDER BY b.created_at DESC
LIMIT 20;
```

---

## 6. GIT WORKFLOW

### 6.1 Branch Strategy

```
main              → Production-ready code
├── develop       → Integration branch
│   ├── feature/booking-module
│   ├── feature/hotel-module
│   ├── fix/login-redirect
│   └── refactor/model-queries
```

### 6.2 Branch Naming

| Tipe | Format | Contoh |
|------|--------|--------|
| Feature | `feature/{nama-fitur}` | `feature/booking-module` |
| Bug fix | `fix/{deskripsi-singkat}` | `fix/login-redirect` |
| Refactor | `refactor/{area}` | `refactor/model-queries` |
| Hotfix | `hotfix/{deskripsi}` | `hotfix/sql-injection` |

### 6.3 Commit Message

Format: `type(scope): description`

| Type | Deskripsi |
|------|-----------|
| `feat` | Fitur baru |
| `fix` | Bug fix |
| `refactor` | Refactoring code |
| `docs` | Dokumentasi |
| `style` | Formatting, CSS |
| `test` | Testing |
| `chore` | Maintenance, config |

Contoh:
```
feat(booking): add booking cancellation flow
fix(auth): redirect after login based on role
docs(api): update endpoint list for hotel module
refactor(model): optimize getNearby haversine query
```

### 6.4 Pull Request Flow

```
1. Buat branch dari develop: git checkout -b feature/my-feature
2. Commit perubahan dengan pesan yang jelas
3. Push: git push origin feature/my-feature
4. Buat Pull Request ke develop
5. Code review oleh minimal 1 reviewer
6. Approve & merge
7. Delete branch setelah merge
```

---

## 7. CODE REVIEW CHECKLIST

### 7.1 Umum

- [ ] Mengikuti naming convention
- [ ] Tidak ada debug code (var_dump, console.log, dd)
- [ ] Tidak ada hardcoded credentials
- [ ] Tidak ada file yang tidak perlu (temp files, cache)

### 7.2 PHP

- [ ] Semua query menggunakan PDO prepared statement
- [ ] Input divalidasi sebelum diproses
- [ ] Output di-escape dengan `Helper::e()` di view
- [ ] Error handling dengan try-catch untuk operasi DB
- [ ] CSRF token di semua form POST
- [ ] Middleware diterapkan untuk role-based access
- [ ] Audit log untuk aksi penting

### 7.3 JavaScript

- [ ] AJAX melalui API helper
- [ ] CSRF token disertakan
- [ ] Error handling (Swal untuk user-facing error)
- [ ] Tidak ada inline event handler (gunakan event listener)

### 7.4 Database

- [ ] Query menggunakan index yang ada
- [ ] Tidak ada N+1 query problem
- [ ] Transaksi untuk operasi multi-table
- [ ] Limit offset untuk pagination

---

## 8. STRUKTUR CONTROLLER

```php
<?php
class ExampleController extends Controller {

    public function __construct() {
        // Middleware di constructor
        Middleware::requireRole('admin');
    }

    // GET — render view
    public function index() {
        $data = $this->model('Example')->all();
        $this->view('admin/examples', ['title' => 'Examples', 'data' => $data]);
    }

    // GET — JSON response (API)
    public function apiList() {
        $data = $this->model('Example')->all();
        $this->json(['status' => 'success', 'data' => $data]);
    }

    // POST — create (API)
    public function create() {
        $input = json_decode(file_get_contents('php://input'), true);
        // Validate
        $v = new Validator($input);
        $v->required(['name', 'type']);
        if ($v->fails()) {
            $this->json(['status' => 'error', 'message' => $v->firstError()], 422);
        }
        // Process
        $id = $this->model('Example')->insert($input);
        Logger::audit('create', 'examples', "Created #{$id}");
        $this->json(['status' => 'success', 'data' => ['id' => $id]], 201);
    }
}
```

---

## 9. STRUKTUR MODEL

```php
<?php
class Example extends Model {
    protected $table = 'examples';

    // Custom query methods
    public function getByCategory($categoryId) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE category_id = :cat_id AND is_active = 1
                ORDER BY created_at DESC";
        return $this->db->query($sql, ['cat_id' => $categoryId])->fetchAll();
    }

    // Code generation
    public function generateCode() {
        $date = date('Ymd');
        $count = $this->db->query(
            "SELECT COUNT(*) as cnt FROM {$this->table} WHERE code LIKE ?",
            ["EX-{$date}-%"]
        )->fetch()['cnt'];
        return sprintf("EX-%s-%03d", $date, $count + 1);
    }
}
```

---

## 10. STRUKTUR VIEW

```php
<?php include 'app/views/layouts/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include 'app/views/layouts/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <h1 class="h2 mt-3"><?= Helper::e($title) ?></h1>
            
            <!-- Content here -->
        </main>
    </div>
</div>

<script>
$(document).ready(function() {
    // Page-specific JS
});
</script>

<?php include 'app/views/layouts/footer.php'; ?>
```

---

## 11. DEFINITION OF DONE (DoD)

Sebuah task dianggap selesai jika:

- [ ] Code mengikuti standar penulisan
- [ ] Unit test / manual test lulus
- [ ] Code review approved
- [ ] Tidak ada error di console/log
- [ ] Dokumentasi diperbarui jika perlu
- [ ] Migration SQL dibuat jika ada perubahan DB
- [ ] Responsive di mobile (360px) dan desktop (1920px)
- [ ] CSRF token diterapkan
- [ ] Input validation diterapkan
- [ ] Audit log untuk aksi penting

---

> **Modul Selanjutnya:** `29_CHECKLIST_PENGEMBANGAN.md` — Checklist pengembangan per fase
