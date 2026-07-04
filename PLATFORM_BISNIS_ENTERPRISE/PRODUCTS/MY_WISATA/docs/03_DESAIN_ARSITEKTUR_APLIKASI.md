# MODUL 03 — DESAIN ARSITEKTUR APLIKASI

> **Aplikasi:** Tour Guide Application  
> **Versi Dokumen:** 1.1  
> **Tanggal:** 2026-06-30  
> **Last Updated:** 2026-06-30

---

## 1. ARSITEKTUR SISTEM SECARA UMUM

### 1.1 Pola Arsitektur: MVC Sederhana

```
┌─────────────────────────────────────────────────────────┐
│                    CLIENT (Browser)                      │
│  HTML5 + CSS + Bootstrap 5 + jQuery + Leaflet           │
│  AJAX Request ──► JSON Response                          │
└─────────────┬───────────────────────────┬───────────────┘
              │                           │
         HTTP/HTTPS                  AJAX/JSON
              │                           │
┌─────────────▼───────────────────────────▼───────────────┐
│                  WEB SERVER (Apache/Nginx)               │
│              .htaccess / nginx.conf routing              │
└─────────────┬───────────────────────────────────────────┘
              │
┌─────────────▼───────────────────────────────────────────┐
│              PHP NATIVE APPLICATION                      │
│                                                          │
│  ┌──────────┐   ┌──────────┐   ┌──────────┐            │
│  │ Controller│──►│  Model   │──►│  View    │            │
│  │ (Logic)  │   │ (Data)   │   │ (UI)     │            │
│  └──────────┘   └────┬─────┘   └──────────┘            │
│                      │                                   │
│              ┌───────▼───────┐                          │
│              │   Database    │                          │
│              │   (MySQL 8)   │                          │
│              └───────────────┘                          │
└─────────────────────────────────────────────────────────┘
```

### 1.2 Alur Request

```
1. Browser → HTTP Request → index.php (Front Controller)
2. index.php → Routing → Controller sesuai URL
3. Controller → Model (query database via PDO)
4. Model → MySQL → return data array
5. Controller → View (render HTML) atau JSON (untuk AJAX)
6. Response → Browser
```

---

## 1.3 PSR Standards Compliance

Aplikasi ini mengikuti standar PHP-FIG (Framework Interop Group) untuk interoperabilitas:

| PSR | Deskripsi | Implementasi |
|-----|-----------|--------------|
| **PSR-1** | Basic coding standard | Class names PascalCase, method names camelCase |
| **PSR-12** | Extended coding style guide | Indentasi 4 spaces, no tabs, line length 120 chars |
| **PSR-4** | Autoloading standard | Composer autoload dengan namespace `App\\` |

### Contoh PSR-4 Structure

```php
// composer.json
{
    "autoload": {
        "psr-4": {
            "App\\": "app/"
        }
    }
}
```

```php
// app/Controllers/AuthController.php
namespace App\Controllers;

class AuthController {
    // ...
}
```

---

## 2. ARSITEKTUR MVC DETAIL

### 2.1 Front Controller Pattern

Semua request melewati satu pintu masuk: `index.php`

```
URL: index.php?url=controller/method/param1/param2

Contoh:
index.php?url=tourguide/list          → TourGuideController::list()
index.php?url=booking/create          → BookingController::create()
index.php?url=api/destinations        → ApiController::destinations() (JSON)
```

### 2.2 Struktur MVC

```
app/
├── controllers/          # Controller — logika bisnis
│   ├── AuthController.php
│   ├── TourGuideController.php
│   ├── BookingController.php
│   ├── MapController.php
│   ├── DestinationController.php
│   ├── HotelController.php
│   ├── RestaurantController.php
│   ├── EventController.php
│   ├── AudioGuideController.php
│   ├── AIGuideController.php
│   ├── NotificationController.php
│   ├── ReportController.php
│   └── ApiController.php       # Endpoint AJAX/JSON
│
├── models/               # Model — interaksi database
│   ├── User.php
│   ├── TourGuide.php
│   ├── Booking.php
│   ├── Destination.php
│   ├── Hotel.php
│   ├── Restaurant.php
│   ├── Event.php
│   ├── AudioGuide.php
│   ├── Notification.php
│   └── Transaction.php
│
├── views/                # View — template HTML
│   ├── layouts/
│   │   ├── header.php
│   │   ├── footer.php
│   │   └── sidebar.php
│   ├── auth/
│   │   ├── login.php
│   │   └── register.php
│   ├── admin/
│   ├── wisatawan/
│   ├── tourguide/
│   └── errors/
│       └── 404.php
│
├── core/                 # Core framework files
│   ├── App.php           # Front controller & routing
│   ├── Controller.php    # Base controller class
│   ├── Model.php         # Base model class (PDO wrapper)
│   ├── View.php          # View renderer
│   ├── Database.php      # PDO connection singleton
│   ├── Session.php       # Session manager
│   ├── Auth.php          # Authentication helper
│   ├── Middleware.php    # RBAC middleware
│   └── Helper.php        # Utility functions
│
└── config/
    ├── config.php        # App configuration
    └── database.php      # Database credentials
```

### 2.4 Service Layer Pattern

Service layer memisahkan business logic dari controller untuk testability dan reusability:

```
app/
├── services/            # Business logic layer
│   ├── BookingService.php
│   ├── PaymentService.php
│   ├── NotificationService.php
│   └── ReportService.php
```

```php
<?php
// app/services/BookingService.php
namespace App\Services;

class BookingService {
    private $bookingModel;
    private $transactionModel;
    private $notificationService;

    public function __construct(
        BookingModel $bookingModel,
        TransactionModel $transactionModel,
        NotificationService $notificationService
    ) {
        $this->bookingModel = $bookingModel;
        $this->transactionModel = $transactionModel;
        $this->notificationService = $notificationService;
    }

    public function createBooking(array $data): array {
        // Business logic validation
        if (!$this->isGuideAvailable($data['guide_id'], $data['date'])) {
            throw new Exception('Guide tidak tersedia pada tanggal tersebut');
        }

        // Create booking
        $bookingId = $this->bookingModel->insert($data);

        // Create transaction
        $transactionData = [
            'booking_id' => $bookingId,
            'amount' => $data['total_amount'],
            'status' => 'pending'
        ];
        $transactionId = $this->transactionModel->insert($transactionData);

        // Send notification
        $this->notificationService->send(
            $data['guide_id'],
            'booking_request',
            ['booking_id' => $bookingId]
        );

        return ['booking_id' => $bookingId, 'transaction_id' => $transactionId];
    }

    private function isGuideAvailable(int $guideId, string $date): bool {
        // Availability check logic
        return true;
    }
}
```

### 2.5 Repository Pattern

Repository pattern menyediakan abstraksi data access untuk testability:

```
app/
├── repositories/        # Data access layer
│   ├── BookingRepository.php
│   ├── UserRepository.php
│   └── DestinationRepository.php
```

```php
<?php
// app/repositories/BookingRepository.php
namespace App\Repositories;

interface BookingRepositoryInterface {
    public function findById(int $id): ?array;
    public function findByGuideId(int $guideId): array;
    public function findByUserId(int $userId): array;
    public function create(array $data): int;
    public function update(int $id, array $data): bool;
}

class BookingRepository implements BookingRepositoryInterface {
    private $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function findById(int $id): ?array {
        $sql = "SELECT * FROM bookings WHERE id = :id";
        $stmt = $this->db->query($sql, ['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function findByGuideId(int $guideId): array {
        $sql = "SELECT * FROM bookings WHERE guide_id = :guide_id ORDER BY created_at DESC";
        $stmt = $this->db->query($sql, ['guide_id' => $guideId]);
        return $stmt->fetchAll();
    }

    // ... other methods
}
```

### 2.6 Base Controller Class (PHP 8.1+ with Type Declarations)

```php
<?php
// app/core/Controller.php
namespace App\Core;

use App\Core\View;
use App\Core\Database;

abstract class Controller {
    protected View $view;
    protected Database $db;

    public function __construct() {
        $this->view = new View();
        $this->db = Database::getInstance();
    }

    // Load model dynamically with type hint
    protected function model(string $modelName): object {
        $className = "App\\Models\\{$modelName}";
        if (class_exists($className)) {
            return new $className();
        }
        throw new Exception("Model {$modelName} not found");
    }

    // Load service with dependency injection
    protected function service(string $serviceName): object {
        $className = "App\\Services\\{$serviceName}";
        if (class_exists($className)) {
            return new $className();
        }
        throw new Exception("Service {$serviceName} not found");
    }

    // Render view with layout
    protected function view(string $viewName, array $data = []): void {
        $this->view->render($viewName, $data);
    }

    // Return JSON response (for AJAX)
    protected function json(array $data, int $status = 200): never {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    // Redirect
    protected function redirect(string $url): never {
        header("Location: " . BASE_URL . $url);
        exit;
    }
}
```

### 2.7 Base Model Class (PHP 8.1+ with Type Declarations)

```php
<?php
// app/core/Model.php
namespace App\Core;

use App\Core\Database;
use PDO;

abstract class Model {
    protected Database $db;
    protected string $table;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // Find by ID with return type
    public function find(int $id): ?array {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $result = $this->db->query($sql, ['id' => $id])->fetch();
        return $result ?: null;
    }

    // Find all with optional conditions
    public function all(array $conditions = [], ?int $limit = null): array {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $key => $value) {
                $where[] = "{$key} = :{$key}";
                $params[$key] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        if ($limit) $sql .= " LIMIT {$limit}";
        return $this->db->query($sql, $params)->fetchAll();
    }

    // Insert with return type
    public function insert(array $data): int {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $this->db->query($sql, $data);
        return (int) $this->db->lastInsertId();
    }

    // Update with return type
    public function update(int $id, array $data): int {
        $set = [];
        foreach (array_keys($data) as $key) {
            $set[] = "{$key} = :{$key}";
        }
        $sql = "UPDATE {$this->table} SET " . implode(', ', $set) . " WHERE id = :id";
        $data['id'] = $id;
        return $this->db->query($sql, $data)->rowCount();
    }

    // Delete with return type
    public function delete(int $id): int {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        return $this->db->query($sql, ['id' => $id])->rowCount();
    }
}
```

### 2.8 Database Connection (PDO Singleton with Environment Variables)

```php
<?php
// app/core/Database.php
namespace App\Core;

use PDO;
use PDOException;

class Database {
    private static ?Database $instance = null;
    private PDO $pdo;

    private function __construct() {
        // Load from environment variables or config file
        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $dbname = $_ENV['DB_NAME'] ?? 'tour_guide_app';
        $user = $_ENV['DB_USER'] ?? 'root';
        $pass = $_ENV['DB_PASS'] ?? '';
        $charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';

        $dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";

        try {
            $this->pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => false, // Disable persistent connections for better resource management
            ]);
        } catch (PDOException $e) {
            throw new PDOException("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function query(string $sql, array $params = []): PDOStatement {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function lastInsertId(): string|false {
        return $this->pdo->lastInsertId();
    }

    public function beginTransaction(): bool {
        return $this->pdo->beginTransaction();
    }

    public function commit(): bool {
        return $this->pdo->commit();
    }

    public function rollBack(): bool {
        return $this->pdo->rollBack();
    }

    // Prevent cloning
    private function __clone() {}

    // Prevent unserialization
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}
```

---

## 3. ARSITEKTUR KOMUNIKASI DATA (AJAX + JSON)

### 3.1 Pola AJAX Request

```
Frontend (jQuery AJAX)
    │
    ├── POST /index.php?url=api/booking/create
    │     Body: { guide_id: 5, date: "2026-07-01", duration: 8 }
    │     Response: { status: "success", booking_id: 123, code: "TG-20260701-001" }
    │
    ├── GET /index.php?url=api/destinations?category=alam
    │     Response: { status: "success", data: [...], total: 15 }
    │
    └── GET /index.php?url=api/map/markers
          Response: { status: "success", markers: [{lat, lng, name, ...}] }
```

### 3.2 Format Response JSON Standar

```json
{
  "status": "success" | "error",
  "message": "Deskripsi pesan",
  "data": { ... } | [ ... ],
  "meta": {
    "total": 100,
    "page": 1,
    "per_page": 20
  }
}
```

### 3.3 ApiController Pattern

```php
<?php
// app/controllers/ApiController.php
class ApiController extends Controller {

    // GET /api/destinations
    public function destinations() {
        $this->requireAuth();
        $model = $this->model('Destination');
        $category = $_GET['category'] ?? null;
        $data = $model->all($category ? ['category' => $category] : []);
        $this->json([
            'status' => 'success',
            'data' => $data,
            'meta' => ['total' => count($data)]
        ]);
    }

    // POST /api/booking/create
    public function createBooking() {
        $this->requireAuth();
        $input = json_decode(file_get_contents('php://input'), true);
        // Validation...
        $booking = $this->model('Booking');
        $id = $booking->insert($input);
        $this->json([
            'status' => 'success',
            'message' => 'Booking berhasil dibuat',
            'data' => ['booking_id' => $id]
        ]);
    }
}
```

---

## 4. ARSITEKTUR KEAMANAN

### 4.1 Lapisan Keamanan

```
┌─────────────────────────────────────┐
│  Layer 1: HTTPS/SSL (Transport)     │  ← Enkripsi jaringan
├─────────────────────────────────────┤
│  Layer 2: Authentication (Session)  │  ← Login + session
├─────────────────────────────────────┤
│  Layer 3: RBAC (Authorization)      │  ← Role-based access
├─────────────────────────────────────┤
│  Layer 4: Input Validation           │  ← Server-side validation
├─────────────────────────────────────┤
│  Layer 5: PDO Prepared Statements    │  ← Anti SQL injection
├─────────────────────────────────────┤
│  Layer 6: Output Escaping            │  ← Anti XSS
├─────────────────────────────────────┤
│  Layer 7: CSRF Token                 │  ← Anti CSRF
└─────────────────────────────────────┘
```

### 4.2 RBAC Middleware

```php
<?php
// app/core/Middleware.php
class Middleware {
    public static function requireRole($roles) {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }
        if (!in_array($_SESSION['role'], (array)$roles)) {
            http_response_code(403);
            View::render('errors/403');
            exit;
        }
    }

    public static function requireAuth() {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit;
        }
    }

    public static function csrfToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrf($token) {
        return hash_equals($_SESSION['csrf_token'] ?? '', $token);
    }
}
```

---

## 5. ARSITEKTUR FRONTEND

### 5.1 Layout System

```
┌─────────────────────────────────────────────┐
│  Header (Navbar + Logo + Notif Badge)       │
├──────────┬──────────────────────────────────┤
│          │                                  │
│  Sidebar │     Content Area (View)          │
│  (Role-  │                                  │
│  based   │     ┌──────────────────────┐    │
│  menu)   │     │  Page Content        │    │
│          │     │  (Bootstrap grid)    │    │
│          │     └──────────────────────┘    │
│          │                                  │
├──────────┴──────────────────────────────────┤
│  Footer (Copyright + Links)                 │
└─────────────────────────────────────────────┘
```

### 5.2 Template Inheritance

```php
<!-- app/views/layouts/header.php -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Tour Guide App' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <?php include 'app/views/layouts/navbar.php'; ?>

<!-- app/views/layouts/footer.php -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="<?= BASE_URL ?>assets/js/app.js"></script>
</body>
</html>
```

### 5.3 AJAX Helper (jQuery)

```javascript
// assets/js/app.js
const API = {
    request: function(url, method, data, callback) {
        $.ajax({
            url: BASE_URL + 'api/' + url,
            method: method,
            data: data,
            dataType: 'json',
            beforeSend: function() {
                $('#loading').show();
            },
            success: function(response) {
                if (response.status === 'success') {
                    callback(response);
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function(xhr) {
                Swal.fire('Error', 'Terjadi kesalahan: ' + xhr.statusText, 'error');
            },
            complete: function() {
                $('#loading').hide();
            }
        });
    },
    get: function(url, callback) {
        this.request(url, 'GET', {}, callback);
    },
    post: function(url, data, callback) {
        this.request(url, 'POST', data, callback);
    }
};
```

---

## 6. ARSITEKTUR PERFORMANCE OPTIMIZATION

### 6.1 OPcache Configuration

OPcache (Opcode Cache) dramatically improves PHP performance by caching compiled bytecode:

```ini
; php.ini or .user.ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
opcache.enable_cli=0
opcache.validate_timestamps=1
```

### 6.2 Gzip Compression

Enable Gzip compression in Apache/Nginx:

```apache
# .htaccess
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript application/json
</IfModule>
```

### 6.3 Browser Caching

Set cache headers for static assets:

```apache
# .htaccess
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>
```

---

## 7. ARSITEKTUR DATABASE CONNECTION

### 7.1 Konfigurasi Database dengan Environment Variables

```bash
# .env (TIDAK di-commit ke Git)
DB_HOST=localhost
DB_NAME=tour_guide_app
DB_USER=root
DB_PASS=
DB_CHARSET=utf8mb4

APP_ENV=development
APP_DEBUG=true
BASE_URL=http://localhost/wisata/
```

```php
<?php
// app/config/database.php (fallback jika .env tidak ada)
return [
    'host'    => $_ENV['DB_HOST'] ?? 'localhost',
    'dbname'  => $_ENV['DB_NAME'] ?? 'tour_guide_app',
    'user'    => $_ENV['DB_USER'] ?? 'root',
    'pass'    => $_ENV['DB_PASS'] ?? '',
    'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
];
```

### 7.2 Config Utama

```php
<?php
// app/config/config.php
// Load environment variables
if (file_exists(__DIR__ . '/../../.env')) {
    $lines = file(__DIR__ . '/../../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) continue;
        putenv($line);
    }
}

define('BASE_URL', getenv('BASE_URL') ?: 'http://localhost/wisata/');
define('APP_NAME', 'Tour Guide Application');
define('APP_VERSION', '1.0.0');
define('APP_ENV', getenv('APP_ENV') ?: 'production');
define('APP_DEBUG', getenv('APP_DEBUG') === 'true');
define('DEFAULT_LANGUAGE', 'id');
define('SESSION_TIMEOUT', 1800); // 30 menit
define('UPLOAD_PATH', 'public/uploads/');
define('MAX_UPLOAD_SIZE', 5242880); // 5MB
```

---

## 8. ARSITEKTUR PETA (OpenStreetMap + Leaflet)

### 8.1 Komponen Peta

```
┌─────────────────────────────────────┐
│  Leaflet.js (Frontend)              │
│  ├── Tile Layer (OpenStreetMap)     │
│  ├── Marker Layer (Destinasi)       │
│  ├── Popup Layer (Info Destinasi)   │
│  ├── Route Layer (Itinerary)        │
│  └── Cluster Layer (Marker Group)   │
├─────────────────────────────────────┤
│  AJAX API (Backend)                 │
│  ├── GET /api/map/markers           │  ← Ambil semua marker
│  ├── GET /api/map/route/:id         │  ← Ambil rute itinerary
│  └── GET /api/map/nearby?lat&lng    │  ← Destinasi terdekat
└─────────────────────────────────────┘
```

### 8.2 Inisialisasi Peta

```javascript
// assets/js/map.js
let map = L.map('map').setView([-2.5, 118], 5); // Indonesia

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors',
    maxZoom: 19
}).addTo(map);

// Load markers via AJAX
API.get('map/markers', function(response) {
    response.data.forEach(function(dest) {
        let marker = L.marker([dest.latitude, dest.longitude])
            .bindPopup(`
                <strong>${dest.name}</strong><br>
                ${dest.description}<br>
                <a href="${BASE_URL}destination/detail/${dest.id}">Lihat Detail</a>
            `)
            .addTo(map);
    });
});
```

---

## 8. DIAGRAM ARSITEKTUR MODUL

```
                    ┌──────────────┐
                    │  Auth Module │
                    │  (Login/Reg) │
                    └──────┬───────┘
                           │
           ┌───────────────┼───────────────┐
           │               │               │
    ┌──────▼──────┐ ┌──────▼──────┐ ┌──────▼──────┐
    │   Admin     │ │  Wisatawan  │ │ Tour Guide  │
    │  Dashboard  │ │  Dashboard  │ │  Dashboard  │
    └──────┬──────┘ └──────┬──────┘ └──────┬──────┘
           │               │               │
    ┌──────┴───────────────┴───────────────┴──────┐
    │              SHARED MODULES                  │
    │                                              │
    │  ┌─────────┐ ┌─────────┐ ┌─────────┐       │
    │  │ Tour    │ │ Booking │ │  Map &  │       │
    │  │ Guide   │ │ & Trans │ │  GPS    │       │
    │  └─────────┘ └─────────┘ └─────────┘       │
    │  ┌─────────┐ ┌─────────┐ ┌─────────┐       │
    │  │ Tiket   │ │ Hotel & │ │Restoran │       │
    │  │ Wisata  │ │Homestay │ │ & UMKM  │       │
    │  └─────────┘ └─────────┘ └─────────┘       │
    │  ┌─────────┐ ┌─────────┐ ┌─────────┐       │
    │  │ Event & │ │ Audio   │ │   AI    │       │
    │  │ Budaya  │ │ Guide   │ │  Guide  │       │
    │  └─────────┘ └─────────┘ └─────────┘       │
    │  ┌─────────┐ ┌─────────┐                    │
    │  │ Notif   │ │ Report  │                    │
    │  └─────────┘ └─────────┘                    │
    └──────────────────────────────────────────────┘
```

---

## 9. ARSITEKTUR FRONTEND (MOBILE-FIRST BOOTSTRAP 5)

### 9.1 Mobile-First Design Principles

Aplikasi ini menggunakan **Bootstrap 5.3 dengan pendekatan mobile-first**, artinya:

- **Primary Target:** Smartphone (Android/iOS) — 80%+ user base
- **Breakpoints:**
  - Extra small (xs): <576px (smartphone portrait)
  - Small (sm): ≥576px (smartphone landscape)
  - Medium (md): ≥768px (tablet)
  - Large (lg): ≥992px (desktop)
  - Extra large (xl): ≥1200px (large desktop)
  - XX large (xxl): ≥1400px (ultra-wide)

### 9.2 Bootstrap 5 Grid System

```html
<!-- Mobile-first approach -->
<div class="container">
  <div class="row">
    <!-- Full width on mobile, 6 cols on tablet, 4 cols on desktop -->
    <div class="col-12 col-md-6 col-lg-4">
      <!-- Content -->
    </div>
  </div>
</div>
```

### 9.3 Touch-Optimized UI Guidelines

| Element | Mobile Requirement | Bootstrap Class |
|---------|-------------------|----------------|
| Buttons | Minimum 44x44px tap target | `btn btn-lg` |
| Form inputs | Large touch targets | `form-control-lg` |
| Navigation | Bottom nav for mobile | Fixed bottom navbar |
| Modals | Full-screen on mobile | `modal-fullscreen` |
| Tables | Horizontal scroll on mobile | `table-responsive` |
| Images | Responsive with lazy load | `img-fluid`, `loading="lazy"` |

### 9.4 Viewport Meta Tag

```html
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
```

### 9.5 Mobile-Specific Components

```html
<!-- Bottom navigation for mobile -->
<nav class="navbar fixed-bottom navbar-light bg-light d-md-none">
  <div class="container-fluid justify-content-around">
    <a href="#" class="nav-link">Home</a>
    <a href="#" class="nav-link">Search</a>
    <a href="#" class="nav-link">Bookings</a>
    <a href="#" class="nav-link">Profile</a>
  </div>
</nav>

<!-- Desktop navigation (hidden on mobile) -->
<nav class="navbar navbar-expand-lg navbar-light bg-light d-none d-md-block">
  <!-- Desktop nav content -->
</nav>
```

### 9.6 Responsive Images

```html
<!-- Responsive image with srcset -->
<img src="img-360.jpg"
     srcset="img-360.jpg 360w,
             img-768.jpg 768w,
             img-1920.jpg 1920w"
     sizes="(max-width: 768px) 360px,
            (max-width: 1920px) 768px,
            1920px"
     class="img-fluid"
     alt="Description"
     loading="lazy">
```

### 9.7 PWA Manifest for Mobile

```json
{
  "name": "Tour Guide App",
  "short_name": "TourGuide",
  "start_url": "/",
  "display": "standalone",
  "background_color": "#ffffff",
  "theme_color": "#007bff",
  "icons": [
    {
      "src": "/icons/icon-192.png",
      "sizes": "192x192",
      "type": "image/png"
    },
    {
      "src": "/icons/icon-512.png",
      "sizes": "512x512",
      "type": "image/png"
    }
  ]
}
```

---

## 10. ARSITEKTUR FILE UPLOAD

```
Upload Flow:
1. Form POST (multipart/form-data)
2. Controller validate (type, size)
3. Move to public/uploads/{module}/
4. Save path to database
5. Return URL for display

Directory Structure:
public/uploads/
├── guides/          # Foto profil tour guide
├── destinations/    # Foto destinasi wisata
├── hotels/          # Foto hotel/homestay
├── restaurants/     # Foto restoran & menu
├── events/          # Foto event
├── audio/           # File audio guide
├── documents/       # Dokumen verifikasi guide
└── tickets/         # QR code e-ticket
```

---

## 11. ARSITEKTUR SESSION & AUTHENTICATION

```
Login Flow:
1. User submit email + password
2. AuthController → User model → verify password (password_verify)
3. Create session: $_SESSION['user_id'], $_SESSION['role'], $_SESSION['name']
4. Redirect to role-based dashboard

Session Data:
$_SESSION = [
    'user_id'   => 1,
    'role'      => 'admin' | 'wisatawan' | 'tour_guide',
    'name'      => 'John Doe',
    'email'     => 'john@example.com',
    'csrf_token'=> 'a1b2c3d4...',
    'login_time'=> 1688123456
];

Logout Flow:
1. session_destroy()
2. Redirect to login page
```

---

## 12. INTEGRATION REMINDERS — FE, API/MIDDLEWARE, BE

### ⚠️ CRITICAL: Integration Points Must Be Validated

Saat membangun aplikasi, pastikan integrasi antar layer berikut berjalan dengan benar:

#### 12.1 Frontend ↔ API Integration

| Aspect | Requirement | Validation Point |
|--------|-------------|------------------|
| **AJAX Calls** | Semua request ke API menggunakan helper yang konsisten | `assets/js/ajax.js` |
| **CSRF Token** | Setiap POST/PUT/DELETE request menyertakan CSRF token | Middleware validation |
| **Error Handling** | Frontend menangani error response dengan user-friendly message | SweetAlert2 display |
| **Loading States** | Tampilkan loading indicator selama request berjalan | Skeleton/Spinner |
| **Data Validation** | Validasi input di frontend sebelum kirim ke API | Client-side validation |

#### 12.2 API/Middleware ↔ Backend Integration

| Aspect | Requirement | Validation Point |
|--------|-------------|------------------|
| **Request Validation** | Middleware validasi input sebelum reach controller | `Middleware.php` |
| **Authentication** | Setiap protected endpoint cek session/token | Auth middleware |
| **Authorization** | RBAC check sebelum akses resource | Permission middleware |
| **Rate Limiting** | API rate limiting untuk mencegah abuse | `RateLimiter.php` |
| **Response Format** | Semua response mengikuti format JSON standar | Controller `json()` method |

#### 12.3 Backend ↔ Database Integration

| Aspect | Requirement | Validation Point |
|--------|-------------|------------------|
| **Prepared Statements** | Semua query menggunakan PDO prepared statements | Model methods |
| **Transaction Management** | Gunakan transaction untuk operasi multi-step | `Database::beginTransaction()` |
| **Error Handling** | Catch database errors dan log dengan detail | Try-catch blocks |
| **Data Sanitization** | Sanitasi data sebelum insert/update | Model validation |
| **Connection Pooling** | Singleton pattern untuk database connection | `Database::getInstance()` |

### 12.4 Application Flow Validation Checklist

Sebelum deploy, pastikan flow berikut sudah di-test:

#### Booking Flow
```
[FE] User pilih destinasi → [API] GET /api/destinations/{id}
[FE] User pilih tanggal → [API] POST /api/bookings/check-availability
[FE] User konfirmasi → [API] POST /api/bookings/create
[BE] Validasi guide availability → [DB] SELECT FROM bookings
[BE] Create booking → [DB] INSERT INTO bookings
[BE] Create transaction → [DB] INSERT INTO transactions
[BE] Send notification → [BE] NotificationService
[API] Return success → [FE] Tampilkan konfirmasi
```

#### Payment Flow
```
[FE] User pilih metode pembayaran → [API] POST /api/payments/initiate
[BE] Generate payment URL → [BE] PaymentService
[FE] Redirect ke payment gateway → [External] Payment Gateway
[External] Callback → [API] POST /api/payments/callback
[BE] Validasi signature → [BE] PaymentService
[BE] Update transaction status → [DB] UPDATE transactions
[BE] Update booking status → [DB] UPDATE bookings
[BE] Send notification → [BE] NotificationService
[API] Return success → [FE] Tampilkan status pembayaran
```

#### Authentication Flow
```
[FE] User submit login → [API] POST /api/auth/login
[BE] Validasi input → [Middleware] Validation
[BE] Cek credentials → [DB] SELECT FROM users
[BE] Verify password → [BE] password_verify()
[BE] Create session → [BE] Session::set()
[BE] Generate CSRF token → [BE] Session::set('csrf_token')
[API] Return user data → [FE] Redirect ke dashboard
```

### 12.5 Business Logic Validation Rules

#### Critical Business Rules yang Harus Diimplementasikan:

1. **Guide Availability Check**
   - Guide tidak bisa double-book pada tanggal yang sama
   - Validasi sebelum create booking
   - Gunakan database lock untuk race condition prevention

2. **Payment Validation**
   - Payment amount harus match dengan booking total
   - Validasi payment gateway signature
   - Tidak ada partial payment tanpa approval

3. **Ticket Generation**
   - QR code hanya generate setelah payment confirmed
   - Ticket harus unique dan tidak bisa digunakan ulang
   - Validasi ticket saat check-in

4. **Review Validation**
   - Hanya user yang pernah booking bisa review
   - Satu booking = satu review per destinasi
   - Review tidak bisa dihapus setelah 24 jam

5. **Rating Calculation**
   - Rating average harus dihitung real-time
   - Update trigger setiap review ditambah
   - Cache untuk performance

### 12.6 Integration Testing Strategy

```php
// Example: Integration test untuk booking flow
class BookingIntegrationTest {
    public function testCompleteBookingFlow() {
        // 1. Login sebagai wisatawan
        $this->loginAsWisatawan();
        
        // 2. Cek availability
        $response = $this->api->post('/api/bookings/check-availability', [
            'guide_id' => 1,
            'date' => '2026-07-15'
        ]);
        $this->assertTrue($response['data']['available']);
        
        // 3. Create booking
        $response = $this->api->post('/api/bookings/create', [
            'guide_id' => 1,
            'date' => '2026-07-15',
            'guests' => 2
        ]);
        $this->assertEquals(201, $response['status']);
        
        // 4. Verify database state
        $booking = $this->db->query("SELECT * FROM bookings WHERE id = ?", [$response['data']['booking_id']]);
        $this->assertEquals('pending', $booking['status']);
        
        // 5. Verify transaction created
        $transaction = $this->db->query("SELECT * FROM transactions WHERE booking_id = ?", [$response['data']['booking_id']]);
        $this->assertNotNull($transaction);
    }
}
```

### 12.7 Common Integration Pitfalls to Avoid

| Pitfall | Impact | Prevention |
|---------|--------|------------|
| **Hardcoded URLs** | Tidak portable | Gunakan `BASE_URL` constant |
| **Missing error handling** | App crash saat error | Try-catch di semua critical paths |
| **Race conditions** | Double booking | Database transactions + locks |
| **Inconsistent data types** | Type mismatch errors | Strict type declarations |
| **Missing validation** | Invalid data di DB | Validasi di FE + BE + DB |
| **No rollback on failure** | Data inconsistency | Transaction rollback on error |
| **Silent failures** | User tidak tahu error | Log semua errors + user feedback |

---

## 13. PRODUCTION ISSUES ANALYSIS — REAL-WORLD SCENARIOS

### ⚠️ CRITICAL: Common Production Issues & Mitigation

Berdasarkan analisis dari internet mengenai aplikasi tour guide/travel booking di production, berikut adalah masalah yang sering terjadi dan bagaimana aplikasi ini mengatasinya:

#### 13.1 High Traffic/Peak Load Issues

| Issue | Real-World Scenario | Mitigation in This App | Status |
|-------|---------------------|------------------------|--------|
| **Server Overload** | Libur Lebaran/Natal, traffic spike 10x | OPcache, Gzip compression, CDN for static assets | ✅ Ready |
| **Database Connection Pool Exhaustion** | 1000+ concurrent requests | Singleton pattern, connection pooling, read replicas (future) | ⚠️ Partial |
| **Slow Page Load** | High latency due to heavy content | Lazy loading, image optimization, skeleton screens | ✅ Ready |
| **API Rate Limiting** | Bot attacks, abusive requests | Rate limiter (60 req/min), IP blocking | ✅ Ready |
| **Session Storage Overload** | Thousands of active sessions | Session timeout (30 min), session cleanup cron | ✅ Ready |

**Additional Recommendations:**
- Implement load balancer (Nginx/HAProxy) for horizontal scaling
- Use Redis for session storage in production
- Implement database read replicas for read-heavy operations
- Use queue system (RabbitMQ/Redis) for async tasks

#### 13.2 Payment Gateway Failures

| Issue | Real-World Scenario | Mitigation in This App | Status |
|-------|---------------------|------------------------|--------|
| **Payment Gateway Downtime** | Midtrans/Xendit maintenance | Multiple payment gateway integration (fallback) | ❌ Not Implemented |
| **Payment Timeout** | Slow network, gateway timeout | Retry mechanism with exponential backoff | ❌ Not Implemented |
| **Payment Status Mismatch** | Success di gateway tapi gagal di DB | Transaction rollback, payment verification callback | ✅ Ready |
| **Double Payment** | User click pay button twice | Idempotent payment requests, unique transaction codes | ✅ Ready |
| **Refund Issues** | User request refund but system fails | Refund workflow with audit trail | ⚠️ Partial |

**Additional Recommendations:**
- Integrate multiple payment gateways (Midtrans, Xendit, Stripe)
- Implement payment retry logic with exponential backoff
- Add payment webhook verification with signature validation
- Implement refund management system with approval workflow
- Store payment gateway response for dispute resolution

#### 13.3 Database Performance Issues

| Issue | Real-World Scenario | Mitigation in This App | Status |
|-------|---------------------|------------------------|--------|
| **Slow Queries** | Complex joins, no indexes | MySQL 8.0 Three-Star indexing, query optimization | ✅ Ready |
| **N+1 Query Problem** | Loop queries in code | Repository pattern with eager loading | ⚠️ Partial |
| **Deadlocks** | Concurrent booking requests | Transaction isolation levels, retry logic | ❌ Not Implemented |
| **Data Corruption** | Disk failure, power outage | Daily backups, transaction logs | ✅ Ready |
| **Disk Space Full** | Uploads growing indefinitely | File cleanup cron, storage monitoring | ⚠️ Partial |

**Additional Recommendations:**
- Implement database connection pooling
- Use read replicas for reporting/analytics
- Implement database partitioning for large tables
- Add slow query monitoring and alerting
- Implement automated backup verification

#### 13.4 Third-Party API Failures

| Issue | Real-World Scenario | Mitigation in This App | Status |
|-------|---------------------|------------------------|--------|
| **Map API Down** | OpenStreetMap/Leaflet tiles not loading | Graceful degradation, cached tiles | ⚠️ Partial |
| **SMS Gateway Failure** | OTP not sent | Email fallback, multiple SMS providers | ❌ Not Implemented |
| **Email Service Down** | Booking confirmation not sent | Queue system, retry mechanism | ❌ Not Implemented |
| **Geocoding API Failure** | Address to coordinates conversion | Fallback to manual input, cached results | ⚠️ Partial |

**Additional Recommendations:**
- Implement API circuit breaker pattern
- Use multiple providers for critical services
- Implement request queuing with retry logic
- Cache third-party API responses
- Add API health monitoring

#### 13.5 User Experience Issues

| Issue | Real-World Scenario | Mitigation in This App | Status |
|-------|---------------------|------------------------|--------|
| **Poor Mobile Connectivity** | User di area dengan sinyal lemah | Offline-first capabilities, PWA | ✅ Ready |
| **Slow Image Loading** | Large images on slow connection | WebP format, lazy loading, CDN | ✅ Ready |
| **Complex Booking Flow** | User abandon booking due to complexity | Simplified flow, progress indicators | ⚠️ Partial |
| **No Offline Access** | User tidak bisa akses tanpa internet | Service worker, critical resource caching | ✅ Ready |
| **Dark Mode Not Supported** | User prefer dark mode but not available | System-aware dark mode | ✅ Ready |

**Additional Recommendations:**
- Implement progressive loading for images
- Add offline booking queue (sync when online)
- Implement A/B testing for UX optimization
- Add user onboarding/tutorial
- Implement accessibility features (screen readers)

#### 13.6 Security Incidents

| Issue | Real-World Scenario | Mitigation in This App | Status |
|-------|---------------------|------------------------|--------|
| **DDoS Attack** | Malicious traffic flood server | Rate limiting, Cloudflare (future) | ⚠️ Partial |
| **SQL Injection** | Hacker inject malicious SQL | PDO prepared statements | ✅ Ready |
| **XSS Attack** | Malicious script in user input | Output escaping, CSP headers | ✅ Ready |
| **CSRF Attack** | Fake requests from malicious sites | CSRF token validation | ✅ Ready |
| **Data Breach** | Unauthorized access to user data | Encryption at rest, access control | ✅ Ready |
| **Brute Force Login** | Automated password guessing | Rate limiting, account lockout | ⚠️ Partial |

**Additional Recommendations:**
- Implement Web Application Firewall (WAF)
- Add IP whitelisting for admin access
- Implement security incident response plan
- Regular security audits and penetration testing
- Implement security monitoring and alerting

#### 13.7 Data Consistency Issues

| Issue | Real-World Scenario | Mitigation in This App | Status |
|-------|---------------------|------------------------|--------|
| **Double Booking** | Two users book same guide simultaneously | Database locking, availability check | ⚠️ Partial |
| **Payment-Booking Mismatch** | Payment success but booking not created | Transaction rollback, compensation logic | ✅ Ready |
| **Orphaned Records** | Booking created but no transaction | Foreign key constraints, cleanup jobs | ✅ Ready |
| **Race Conditions** | Concurrent updates cause data loss | Optimistic locking, versioning | ❌ Not Implemented |
| **Data Replication Lag** | Master-slave sync delay | Single database (no replication) | N/A |

**Additional Recommendations:**
- Implement database row-level locking for bookings
- Add compensation transactions for failed operations
- Implement event sourcing for audit trail
- Add data integrity checks and reconciliation
- Implement idempotent operations

#### 13.8 Scalability Issues

| Issue | Real-World Scenario | Mitigation in This App | Status |
|-------|---------------------|------------------------|--------|
| **Vertical Scaling Limit** | Single server can't handle load | Horizontal scaling ready (stateless) | ✅ Ready |
| **Session Storage Bottleneck** | File-based sessions don't scale | Redis session storage (future) | ⚠️ Partial |
| **File Storage Limit** | Local disk fills with uploads | Cloud storage (S3/Cloudinary) | ❌ Not Implemented |
| **Database Size Growth** | Tables become too large | Partitioning, archiving (future) | ⚠️ Partial |
| **API Rate Limiting** | Too many requests from single user | Per-user rate limiting | ⚠️ Partial |

**Additional Recommendations:**
- Implement containerization (Docker) for easy scaling
- Use cloud storage for file uploads
- Implement database sharding for large datasets
- Add auto-scaling based on load metrics
- Implement API versioning for backward compatibility

#### 13.9 Backup & Recovery Issues

| Issue | Real-World Scenario | Mitigation in This App | Status |
|-------|---------------------|------------------------|--------|
| **Data Loss** | Disk failure, accidental deletion | Daily automated backups | ✅ Ready |
| **Backup Corruption** | Backup file damaged | Multiple backup copies, verification | ⚠️ Partial |
| **Long Recovery Time** | RTO (Recovery Time Objective) too long | Point-in-time recovery, hot standby | ❌ Not Implemented |
| **No Disaster Recovery** | Data center goes down | Offsite backup, multi-region deployment | ❌ Not Implemented |
| **Backup Not Tested** | Restore fails when needed | Regular restore testing | ❌ Not Implemented |

**Additional Recommendations:**
- Implement point-in-time recovery (PITR)
- Store backups in multiple locations (on-site + off-site)
- Implement automated backup verification
- Document disaster recovery procedures
- Regularly test restore procedures

#### 13.10 Monitoring & Alerting Issues

| Issue | Real-World Scenario | Mitigation in This App | Status |
|-------|---------------------|------------------------|--------|
| **No Visibility** | Don't know when system is down | Error logging, audit log | ⚠️ Partial |
| **Late Detection** | Issues discovered by users first | Real-time monitoring (future) | ❌ Not Implemented |
| **No Alerting** | Team not notified of critical issues | Email/SMS alerts (future) | ❌ Not Implemented |
| **No Performance Metrics** | Can't track system health | Application performance monitoring (future) | ❌ Not Implemented |
| **No User Behavior Analytics** | Don't know how users use app | Analytics integration (future) | ❌ Not Implemented |

**Additional Recommendations:**
- Implement APM (Application Performance Monitoring)
- Set up log aggregation (ELK stack)
- Implement health check endpoints
- Add uptime monitoring (Pingdom/UptimeRobot)
- Implement error tracking (Sentry)
- Add user analytics (Google Analytics/Mixpanel)

### 13.1 Production Readiness Assessment

| Category | Readiness | Score | Notes |
|----------|-----------|-------|-------|
| **Performance** | Medium | 70% | OPcache, Gzip ready, need CDN |
| **Scalability** | Medium | 65% | Stateless, need horizontal scaling |
| **Security** | High | 85% | OWASP compliant, need MFA |
| **Reliability** | Medium | 60% | Backups ready, need HA |
| **Monitoring** | Low | 40% | Logging ready, need APM |
| **Disaster Recovery** | Low | 35% | Backups ready, need DR plan |
| **Payment Reliability** | Medium | 55% | Single gateway, need fallback |
| **Third-Party Resilience** | Low | 45% | Graceful degradation partial |
| **Data Consistency** | Medium | 65% | Transactions ready, need locking |
| **User Experience** | High | 80% | Mobile-first, PWA ready |

**Overall Production Readiness: 60%**

### 13.2 Critical Action Items Before Production

#### Must-Have (P0)
1. **Implement multiple payment gateways** with fallback
2. **Add payment retry logic** with exponential backoff
3. **Implement database row-level locking** for bookings
4. **Set up real-time monitoring** and alerting
5. **Implement security incident response plan**
6. **Add automated backup verification**
7. **Implement rate limiting per user** (not just per IP)
8. **Add account lockout** for brute force protection

#### Should-Have (P1)
1. **Implement Redis** for session storage
2. **Add cloud storage** for file uploads
3. **Implement circuit breaker** for third-party APIs
4. **Set up CDN** for static assets
5. **Implement load balancer** for horizontal scaling
6. **Add APM** for performance monitoring
7. **Implement disaster recovery plan**
8. **Add security audit logging**

#### Nice-to-Have (P2)
1. **Implement database read replicas**
2. **Add database partitioning**
3. **Implement event sourcing**
4. **Add user analytics**
5. **Implement A/B testing**
6. **Add chat support system**
7. **Implement recommendation engine**

---

## 14. MONITORING & OBSERVABILITY

### 14.1 Application Performance Monitoring (APM)

**Status:** Not Implemented — HIGH PRIORITY

Implementasi APM untuk production monitoring:

```php
// app/services/MonitoringService.php
class MonitoringService {
    private $sentry;

    public function __construct() {
        $this->sentry = new Sentry\Client([
            'dsn' => $_ENV['SENTRY_DSN'],
            'environment' => $_ENV['APP_ENV'],
        ]);
    }

    public function logError(string $message, array $context = []): void {
        $this->sentry->captureException(new Exception($message), $context);
    }

    public function logPerformance(string $operation, float $duration): void {
        // Log performance metrics
        $this->sentry->addBreadcrumb($operation, 'performance', [
            'duration_ms' => $duration * 1000
        ]);
    }
}
```

### 14.2 Health Check Endpoints

```php
// app/controllers/HealthController.php
namespace App\Controllers;

class HealthController extends Controller {
    public function index(): void {
        $health = [
            'status' => 'healthy',
            'timestamp' => time(),
            'version' => APP_VERSION,
            'environment' => APP_ENV,
            'services' => [
                'database' => $this->checkDatabase(),
                'cache' => $this->checkCache(),
                'storage' => $this->checkStorage(),
                'external_apis' => $this->checkExternalAPIs(),
            ]
        ];
        $this->json($health);
    }

    private function checkDatabase(): array {
        try {
            $this->db->query("SELECT 1")->fetch();
            return ['status' => 'up', 'latency_ms' => $this->measureLatency()];
        } catch (Exception $e) {
            return ['status' => 'down', 'error' => $e->getMessage()];
        }
    }

    private function measureLatency(): float {
        $start = microtime(true);
        $this->db->query("SELECT 1")->fetch();
        return (microtime(true) - $start) * 1000;
    }
}
```

### 14.3 Logging Strategy

```php
// app/core/Logger.php
namespace App\Core;

class Logger {
    private static ?Logger $instance = null;
    private $logFile;

    private function __construct() {
        $this->logFile = LOGS_PATH . '/app.log';
    }

    public static function getInstance(): Logger {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function info(string $message, array $context = []): void {
        $this->log('INFO', $message, $context);
    }

    public function warning(string $message, array $context = []): void {
        $this->log('WARNING', $message, $context);
    }

    public function error(string $message, array $context = []): void {
        $this->log('ERROR', $message, $context);
    }

    private function log(string $level, string $message, array $context): void {
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = json_encode($context);
        $logLine = "[{$timestamp}] [{$level}] {$message} {$contextStr}\n";
        file_put_contents($this->logFile, $logLine, FILE_APPEND);
    }
}
```

### 14.4 Metrics Collection

```php
// app/services/MetricsService.php
class MetricsService {
    private $redis;

    public function __construct() {
        $this->redis = new Redis();
        $this->redis->connect('127.0.0.1', 6379);
    }

    public function incrementCounter(string $metric, int $value = 1): void {
        $key = "metrics:{$metric}:" . date('Y-m-d-H');
        $this->redis->incrBy($key, $value);
        $this->redis->expire($key, 86400); // 24 hours
    }

    public function recordTiming(string $metric, float $duration): void {
        $key = "metrics:{$metric}:timing:" . date('Y-m-d-H');
        $this->redis->lPush($key, $duration);
        $this->redis->lTrim($key, 0, 999); // Keep last 1000
        $this->redis->expire($key, 86400);
    }

    public function getMetrics(string $metric, int $hours = 24): array {
        $metrics = [];
        for ($i = 0; $i < $hours; $i++) {
            $hour = date('Y-m-d-H', strtotime("-{$i} hours"));
            $key = "metrics:{$metric}:{$hour}";
            $value = $this->redis->get($key) ?: 0;
            $metrics[$hour] = $value;
        }
        return array_reverse($metrics);
    }
}
```

### 14.5 Alerting Strategy

```php
// app/services/AlertService.php
class AlertService {
    private $emailService;
    private $thresholds = [
        'error_rate' => 10, // 10 errors per minute
        'response_time' => 2000, // 2 seconds
        'database_latency' => 500, // 500ms
    ];

    public function checkAlerts(): void {
        $this->checkErrorRate();
        $this->checkResponseTime();
        $this->checkDatabaseLatency();
    }

    private function checkErrorRate(): void {
        $errorCount = $this->getErrorCountLastMinute();
        if ($errorCount > $this->thresholds['error_rate']) {
            $this->sendAlert('High Error Rate', "Error count: {$errorCount}/min");
        }
    }

    private function sendAlert(string $subject, string $message): void {
        $this->emailService->send(
            $_ENV['ALERT_EMAIL'],
            "[ALERT] {$subject}",
            $message
        );
    }
}
```

### 14.6 Dashboard Recommendations

**Tools to Consider:**
- **Grafana** — Open-source metrics visualization
- **Kibana** — Log visualization and analysis
- **Sentry Dashboard** — Error tracking dashboard
- **New Relic Dashboard** — APM dashboard (paid)
- **Datadog Dashboard** — Infrastructure monitoring (paid)

**Key Metrics to Monitor:**
- Request rate (requests/second)
- Response time (p50, p95, p99)
- Error rate (errors/total requests)
- Database query latency
- Cache hit rate
- Server CPU/memory usage
- Disk I/O
- Network I/O

### 14.7 Log Aggregation

**ELK Stack Setup:**
```yaml
# docker-compose.yml for ELK
version: '3.8'
services:
  elasticsearch:
    image: elasticsearch:8.0.0
    environment:
      - discovery.type=single-node
    ports:
      - "9200:9200"
  
  logstash:
    image: logstash:8.0.0
    volumes:
      - ./logstash.conf:/usr/share/logstash/pipeline/logstash.conf
    ports:
      - "5044:5044"
  
  kibana:
    image: kibana:8.0.0
    ports:
      - "5601:5601"
    depends_on:
      - elasticsearch
```

### 14.8 Circuit Breaker for Third-Party APIs

**Status:** Not Implemented — MEDIUM PRIORITY

Implementasi circuit breaker pattern untuk mencegah cascade failures dari third-party API failures:

```php
// app/services/CircuitBreakerService.php
class CircuitBreakerService {
    private $states = [];
    private $threshold = 5; // Failures before opening
    private $timeout = 60; // Seconds before retry
    private $halfOpenMaxCalls = 3;

    public function call(string $service, callable $callback) {
        $state = $this->getState($service);

        if ($state['status'] === 'open') {
            if ($this->shouldAttemptReset($state)) {
                $this->setState($service, 'half_open');
            } else {
                throw new CircuitBreakerException("Circuit breaker OPEN for {$service}");
            }
        }

        try {
            $result = $callback();
            $this->recordSuccess($service);
            return $result;
        } catch (Exception $e) {
            $this->recordFailure($service);
            throw $e;
        }
    }

    private function getState(string $service): array {
        if (!isset($this->states[$service])) {
            $this->states[$service] = [
                'status' => 'closed',
                'failures' => 0,
                'last_failure_time' => null,
                'half_open_calls' => 0
            ];
        }
        return $this->states[$service];
    }

    private function recordSuccess(string $service): void {
        $state = $this->getState($service);
        
        if ($state['status'] === 'half_open') {
            $state['half_open_calls']++;
            if ($state['half_open_calls'] >= $this->halfOpenMaxCalls) {
                $this->setState($service, 'closed');
            }
        } else {
            $state['failures'] = 0;
        }
        
        $this->states[$service] = $state;
    }

    private function recordFailure(string $service): void {
        $state = $this->getState($service);
        $state['failures']++;
        $state['last_failure_time'] = time();
        $state['half_open_calls'] = 0;

        if ($state['failures'] >= $this->threshold) {
            $state['status'] = 'open';
        }

        $this->states[$service] = $state;
    }

    private function shouldAttemptReset(array $state): bool {
        return time() - $state['last_failure_time'] >= $this->timeout;
    }

    private function setState(string $service, string $status): void {
        $this->states[$service]['status'] = $status;
        if ($status === 'closed') {
            $this->states[$service]['failures'] = 0;
            $this->states[$service]['half_open_calls'] = 0;
        }
    }
}
```

### Usage Example

```php
// app/services/ExternalAPIService.php
class ExternalAPIService {
    private $circuitBreaker;

    public function __construct() {
        $this->circuitBreaker = new CircuitBreakerService();
    }

    public function callPaymentGateway(array $data): array {
        return $this->circuitBreaker->call('payment_gateway', function() use ($data) {
            // Call actual payment gateway
            return $this->paymentGateway->createPayment($data);
        });
    }

    public function callSMSGateway(string $phone, string $message): bool {
        return $this->circuitBreaker->call('sms_gateway', function() use ($phone, $message) {
            // Call actual SMS gateway
            return $this->smsGateway->send($phone, $message);
        });
    }

    public function callEmailGateway(string $to, string $subject, string $body): bool {
        return $this->circuitBreaker->call('email_gateway', function() use ($to, $subject, $body) {
            // Call actual email gateway
            return $this->emailGateway->send($to, $subject, $body);
        });
    }
}
```

### Fallback Strategy

```php
// Add fallback when circuit is open
class ExternalAPIService {
    public function callSMSGateway(string $phone, string $message): bool {
        try {
            return $this->circuitBreaker->call('sms_gateway', function() use ($phone, $message) {
                return $this->smsGateway->send($phone, $message);
            });
        } catch (CircuitBreakerException $e) {
            // Fallback to email notification
            $this->emailGateway->send(
                'admin@example.com',
                'SMS Gateway Down',
                "Failed to send SMS to {$phone}: {$message}"
            );
            return false;
        }
    }
}
```

### Circuit Breaker States

| State | Description | Behavior |
|-------|-------------|----------|
| **Closed** | Normal operation | All requests pass through |
| **Open** | Circuit is open | All requests fail immediately |
| **Half-Open** | Testing recovery | Limited requests allowed to test |

### Monitoring Circuit Breaker

```php
// Add circuit breaker status to health check
class HealthController extends Controller {
    public function index(): void {
        $health = [
            'status' => 'healthy',
            'circuit_breakers' => $this->getCircuitBreakerStatus()
        ];
        $this->json($health);
    }

    private function getCircuitBreakerStatus(): array {
        $cb = new CircuitBreakerService();
        return [
            'payment_gateway' => $cb->getState('payment_gateway'),
            'sms_gateway' => $cb->getState('sms_gateway'),
            'email_gateway' => $cb->getState('email_gateway'),
            'map_api' => $cb->getState('map_api'),
        ];
    }
}
```

---

### 14.9 Database Read Replicas

**Status:** Not Implemented — MEDIUM PRIORITY

Implementasi database read replicas untuk scaling read operations:

```php
// app/config/database.php
return [
    'write' => [
        'host' => $_ENV['DB_WRITE_HOST'],
        'dbname' => $_ENV['DB_NAME'],
        'user' => $_ENV['DB_USER'],
        'pass' => $_ENV['DB_PASS'],
    ],
    'read' => [
        ['host' => $_ENV['DB_READ_HOST_1']],
        ['host' => $_ENV['DB_READ_HOST_2']],
        ['host' => $_ENV['DB_READ_HOST_3']],
    ],
];
```

### Read-Write Split Implementation

```php
// app/core/Database.php
class Database {
    private $writeConnection;
    private $readConnections = [];
    private $currentReadIndex = 0;

    public function __construct() {
        $this->writeConnection = $this->createConnection('write');
        
        foreach ($config['read'] as $readConfig) {
            $this->readConnections[] = $this->createConnection($readConfig);
        }
    }

    public function query(string $sql, array $params = []) {
        $isWrite = $this->isWriteQuery($sql);
        
        if ($isWrite) {
            return $this->writeConnection->query($sql, $params);
        } else {
            return $this->getReadConnection()->query($sql, $params);
        }
    }

    private function isWriteQuery(string $sql): bool {
        $writeKeywords = ['INSERT', 'UPDATE', 'DELETE', 'CREATE', 'ALTER', 'DROP'];
        $sqlUpper = strtoupper(trim($sql));
        
        foreach ($writeKeywords as $keyword) {
            if (strpos($sqlUpper, $keyword) === 0) {
                return true;
            }
        }
        
        return false;
    }

    private function getReadConnection() {
        // Round-robin load balancing
        $connection = $this->readConnections[$this->currentReadIndex];
        $this->currentReadIndex = ($this->currentReadIndex + 1) % count($this->readConnections);
        
        return $connection;
    }
}
```

### MySQL Master-Slave Replication Setup

```bash
# On Master Server
# /etc/mysql/mysql.conf.d/mysqld.cnf
[mysqld]
server-id = 1
log-bin = mysql-bin
binlog-format = ROW
binlog-do-db = tour_guide_app

# Create replication user
mysql -u root -p
CREATE USER 'replicator'@'%' IDENTIFIED BY 'replication_password';
GRANT REPLICATION SLAVE ON *.* TO 'replicator'@'%';
FLUSH PRIVILEGES;

# Get master status
SHOW MASTER STATUS;
```

```bash
# On Slave Server
# /etc/mysql/mysql.conf.d/mysqld.cnf
[mysqld]
server-id = 2
relay-log = mysql-relay-bin
read-only = 1

# Configure slave
mysql -u root -p
CHANGE MASTER TO
  MASTER_HOST='master-server-ip',
  MASTER_USER='replicator',
  MASTER_PASSWORD='replication_password',
  MASTER_LOG_FILE='mysql-bin.000001',
  MASTER_LOG_POS=154;

START SLAVE;
SHOW SLAVE STATUS;
```

### Health Check for Replicas

```php
// app/services/ReplicaHealthService.php
class ReplicaHealthService {
    public function checkReplicaHealth(): array {
        $health = [];
        
        foreach ($this->readConnections as $index => $connection) {
            try {
                $result = $connection->query("SHOW SLAVE STATUS")->fetch();
                $health[] = [
                    'replica' => $index + 1,
                    'status' => 'healthy',
                    'lag_seconds' => $result['Seconds_Behind_Master'] ?? 0,
                    'running' => $result['Slave_IO_Running'] === 'Yes' && $result['Slave_SQL_Running'] === 'Yes'
                ];
            } catch (Exception $e) {
                $health[] = [
                    'replica' => $index + 1,
                    'status' => 'unhealthy',
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return $health;
    }
}
```

---

### 14.10 Database Partitioning

**Status:** Not Implemented — MEDIUM PRIORITY

Implementasi database partitioning untuk large tables:

```sql
-- Partition bookings table by date
ALTER TABLE bookings 
PARTITION BY RANGE (YEAR(booking_date)) (
    PARTITION p2023 VALUES LESS THAN (2024),
    PARTITION p2024 VALUES LESS THAN (2025),
    PARTITION p2025 VALUES LESS THAN (2026),
    PARTITION p2026 VALUES LESS THAN (2027),
    PARTITION p_future VALUES LESS THAN MAXVALUE
);

-- Partition audit_logs by date
ALTER TABLE audit_logs 
PARTITION BY RANGE (YEAR(created_at)) (
    PARTITION p2023 VALUES LESS THAN (2024),
    PARTITION p2024 VALUES LESS THAN (2025),
    PARTITION p2025 VALUES LESS THAN (2026),
    PARTITION p2026 VALUES LESS THAN (2027),
    PARTITION p_future VALUES LESS THAN MAXVALUE
);

-- Partition security_audit_logs by date
ALTER TABLE security_audit_logs 
PARTITION BY RANGE (YEAR(created_at)) (
    PARTITION p2023 VALUES LESS THAN (2024),
    PARTITION p2024 VALUES LESS THAN (2025),
    PARTITION p2025 VALUES LESS THAN (2026),
    PARTITION p2026 VALUES LESS THAN (2027),
    PARTITION p_future VALUES LESS THAN MAXVALUE
);
```

### Partition Management

```php
// app/services/PartitionManagementService.php
class PartitionManagementService {
    public function addYearPartition(string $table, int $year): void {
        $nextYear = $year + 1;
        
        $sql = "ALTER TABLE {$table} 
                ADD PARTITION (PARTITION p{$year} VALUES LESS THAN ({$nextYear}))";
        
        $this->db->query($sql);
    }

    public function dropOldPartitions(string $table, int $keepYears): void {
        $cutoffYear = date('Y') - $keepYears;
        
        $sql = "ALTER TABLE {$table} 
                DROP PARTITION p{$cutoffYear}";
        
        $this->db->query($sql);
    }

    public function getPartitionInfo(string $table): array {
        $sql = "SELECT PARTITION_NAME, PARTITION_ORDINAL_POSITION, 
                PARTITION_METHOD, PARTITION_EXPRESSION, TABLE_ROWS
                FROM information_schema.PARTITIONS
                WHERE TABLE_NAME = :table";
        
        return $this->db->query($sql, ['table' => $table])->fetchAll();
    }
}
```

---

### 14.11 Event Sourcing

**Status:** Not Implemented — MEDIUM PRIORITY

Implementasi event sourcing untuk audit trail dan eventual consistency:

```php
// app/services/EventStoreService.php
class EventStoreService {
    public function storeEvent(string $aggregateType, string $aggregateId, string $eventType, array $data): void {
        $sql = "INSERT INTO event_store 
                (aggregate_type, aggregate_id, event_type, event_data, created_at) 
                VALUES (:aggregate_type, :aggregate_id, :event_type, :data, NOW())";
        
        $this->db->query($sql, [
            'aggregate_type' => $aggregateType,
            'aggregate_id' => $aggregateId,
            'event_type' => $eventType,
            'data' => json_encode($data)
        ]);
    }

    public function getEvents(string $aggregateType, string $aggregateId): array {
        $sql = "SELECT * FROM event_store 
                WHERE aggregate_type = :type AND aggregate_id = :id 
                ORDER BY created_at ASC";
        
        return $this->db->query($sql, [
            'type' => $aggregateType,
            'id' => $aggregateId
        ])->fetchAll();
    }

    public function replayEvents(string $aggregateType, string $aggregateId): array {
        $events = $this->getEvents($aggregateType, $aggregateId);
        $state = [];

        foreach ($events as $event) {
            $state = $this->applyEvent($state, $event);
        }

        return $state;
    }

    private function applyEvent(array $state, array $event): array {
        $eventType = $event['event_type'];
        $data = json_decode($event['event_data'], true);

        switch ($eventType) {
            case 'booking_created':
                $state['status'] = 'pending';
                $state['created_at'] = $event['created_at'];
                break;
            case 'booking_confirmed':
                $state['status'] = 'confirmed';
                $state['confirmed_at'] = $event['created_at'];
                break;
            case 'booking_completed':
                $state['status'] = 'completed';
                $state['completed_at'] = $event['created_at'];
                break;
        }

        return $state;
    }
}
```

### Event Store Table

```sql
CREATE TABLE event_store (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    aggregate_type VARCHAR(50) NOT NULL,
    aggregate_id VARCHAR(100) NOT NULL,
    event_type VARCHAR(50) NOT NULL,
    event_data JSON NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_aggregate (aggregate_type, aggregate_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

### 14.12 Monitoring Checklist

Before production deployment, ensure:

- [ ] Health check endpoint implemented
- [ ] Error logging configured
- [ ] Performance metrics collection
- [ ] Alert thresholds defined
- [ ] Email/SMS alerting configured
- [ ] Log aggregation setup
- [ ] Dashboard configured
- [ ] Uptime monitoring active
- [ ] APM integration (Sentry/New Relic)
- [ ] Database slow query monitoring
- [ ] Cache hit rate monitoring
- [ ] Server resource monitoring

---

## 15. ERROR HANDLING STRATEGY

### 15.1 Environment-based Error Display

```php
// app/config/config.php
define('APP_ENV', 'development');  // development | production
define('APP_DEBUG', APP_ENV === 'development');

// app/core/App.php — di method run()
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', BASE_PATH . '/logs/error.log');
}
```

### 11.2 Global Exception Handler

```php
// app/core/App.php
set_exception_handler(function($e) {
    Logger::error('Unhandled exception', [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
    if (APP_DEBUG) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Terjadi kesalahan internal'
        ]);
    }
});

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    Logger::error("PHP Error [{$errno}]", [
        'message' => $errstr,
        'file' => $errfile,
        'line' => $errline
    ]);
    if (APP_DEBUG) {
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }
    return true;
});
```

### 11.3 Error Pages

| HTTP Code | View File | Deskripsi |
|-----------|-----------|-----------|
| 401 | `errors/401.php` | Unauthorized — belum login |
| 403 | `errors/403.php` | Forbidden — salah role |
| 404 | `errors/404.php` | Not Found — halaman/route tidak ada |
| 419 | `errors/419.php` | CSRF Token Mismatch |
| 429 | `errors/429.php` | Too Many Requests — rate limit |
| 500 | `errors/500.php` | Internal Server Error |

```php
// app/core/App.php — handle 404
private function handle404() {
    http_response_code(404);
    if ($this->isAjax()) {
        $this->json(['status' => 'error', 'message' => 'Endpoint not found'], 404);
    } else {
        View::render('errors/404');
    }
    exit;
}
```

### 11.4 Database Transaction Pattern

```php
try {
    $this->db->beginTransaction();
    // Multiple operations
    $this->db->commit();
} catch (PDOException $e) {
    $this->db->rollBack();
    Logger::error('Transaction failed', ['error' => $e->getMessage()]);
    $this->json(['status' => 'error', 'message' => 'Operasi gagal'], 500);
}
```

---

## 12. CORS CONFIGURATION

### 12.1 Same-Origin (Default)

Aplikasi ini menggunakan AJAX same-origin (frontend dan backend di domain yang sama),
sehingga CORS **tidak perlu** dikonfigurasi untuk pengembangan normal.

### 12.2 Development (Jika Frontend Terpisah)

Jika frontend berjalan di port berbeda (contoh: `localhost:3000` untuk Vite/webpack dev server):

```php
// app/core/App.php — di awal method run()
if (APP_ENV === 'development') {
    header('Access-Control-Allow-Origin: http://localhost:3000');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token, Authorization');
    header('Access-Control-Allow-Credentials: true');

    // Handle preflight
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}
```

### 12.3 Production (API untuk Mobile App — Masa Depan)

```php
// Hanya jika API diakses dari domain berbeda
$allowedOrigins = ['https://app.yourdomain.com', 'https://m.yourdomain.com'];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token, Authorization');
    header('Access-Control-Allow-Credentials: true');
    header('Vary: Origin');
}
```

> **Peringatan:** Jangan pernah set `Access-Control-Allow-Origin: *` dengan `Allow-Credentials: true`.

---

## 13. ENVIRONMENT MANAGEMENT

### 13.1 Konfigurasi Per Environment

| Setting | Development | Production |
|---------|-------------|------------|
| `APP_ENV` | `development` | `production` |
| `APP_DEBUG` | `true` | `false` |
| `display_errors` | `On` | `Off` |
| `log_errors` | `On` | `On` |
| `BASE_URL` | `http://localhost/wisata/` | `https://yourdomain.com/` |
| DB Password | (kosong / root) | (strong password) |
| CORS | Allow localhost:3000 | Same-origin only |
| HTTPS | Optional | Wajib (HSTS) |

### 13.2 Environment via .env (Opsional)

Jika menggunakan library `vlucas/phpdotenv` (Composer):

```env
# .env (TIDAK di-commit, ada di .gitignore)
APP_ENV=development
APP_DEBUG=true
DB_HOST=localhost
DB_NAME=tour_guide_app
DB_USER=root
DB_PASS=
BASE_URL=http://localhost/wisata/
```

```php
// app/config/config.php
if (file_exists(BASE_PATH . '/.env')) {
    $lines = file(BASE_PATH . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) continue;
        putenv($line);
    }
}

define('APP_ENV', getenv('APP_ENV') ?: 'development');
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
// dst.
```

### 13.3 Tanpa .env (PHP Native Murni)

Gunakan file config terpisah per environment:

```
app/config/
├── config.php          → Default config (production)
├── config.local.php    → Override untuk development (TIDAK di-commit)
```

```php
// app/config/config.php
define('APP_ENV', 'production');
define('APP_DEBUG', false);

// Override dengan local config jika ada
if (file_exists(__DIR__ . '/config.local.php')) {
    require __DIR__ . '/config.local.php';
}
```

```php
// app/config/config.local.php (TIDAK di-commit)
define('APP_ENV', 'development');
define('APP_DEBUG', true);
define('BASE_URL', 'http://localhost/wisata/');
```

---

> **Modul Selanjutnya:** `04_STRUKTUR_FOLDER_PHP_NATIVE.md` — Struktur folder project secara lengkap
