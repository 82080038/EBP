# MODUL 04 — STRUKTUR FOLDER PHP NATIVE (MVC SEDERHANA)

> **Aplikasi:** Tour Guide Application  
> **Versi Dokumen:** 1.1  
> **Tanggal:** 2026-06-30  
> **Last Updated:** 2026-06-30

---

## 1. STRUKTUR FOLDER LENGKAP

```
wisata/                              # Root project
│
├── docs/                            # Dokumentasi (33 file .md)
│   ├── 00_DAFTAR_ISI.md
│   ├── 01_KONSEP_DAN_ANALISIS_SISTEM.md
│   ├── 02_SRS_REQUIREMENT_SYSTEM.md
│   ├── ...
│   ├── 26_ROADMAP_PENGEMBANGAN.md
│   ├── 27_PANDUAN_INSTALASI_LOKAL.md
│   ├── 28_STANDAR_KODE_KONTRIBUSI.md
│   ├── 29_CHECKLIST_PENGEMBANGAN.md
│   ├── 30_DIAGRAM_ALUR_BISNIS.md
│   ├── 31_KAMUS_ISTILAH_GLOSARIUM.md
│   └── 32_AUDIT_KEAMANAN_CHECKLIST.md
│
├── app/                             # Application core (MVC)
│   ├── config/                      # Konfigurasi
│   │   ├── config.php               # Config utama (BASE_URL, dll)
│   │   ├── database.php             # Kredensial database (fallback)
│   │   └── routes.php               # Definisi route (opsional)
│   │
│   ├── core/                        # Core framework
│   │   ├── App.php                  # Front controller & routing
│   │   ├── Controller.php           # Base controller class
│   │   ├── Model.php                # Base model class (PDO)
│   │   ├── View.php                 # View renderer
│   │   ├── Database.php             # PDO singleton
│   │   ├── Session.php              # Session manager
│   │   ├── Auth.php                 # Auth helper
│   │   ├── Middleware.php           # RBAC & CSRF
│   │   ├── Validator.php            # Input validation
│   │   ├── Helper.php               # Utility functions (e, upload, slug, email)
│   │   ├── Logger.php               # Error & audit logger
│   │   └── RateLimiter.php          # API rate limiting
│   │
│   ├── controllers/                 # Controller — logika bisnis
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── TourGuideController.php
│   │   ├── BookingController.php
│   │   ├── DestinationController.php
│   │   ├── MapController.php
│   │   ├── HotelController.php
│   │   ├── RestaurantController.php
│   │   ├── EventController.php
│   │   ├── AudioGuideController.php
│   │   ├── AIGuideController.php
│   │   ├── NotificationController.php
│   │   ├── ReportController.php
│   │   ├── ReviewController.php
│   │   ├── BackupController.php
│   │   ├── UserController.php
│   │   ├── WisatawanController.php
│   │   └── ApiController.php        # Endpoint AJAX/JSON
│   │
│   ├── models/                      # Model — interaksi DB
│   │   ├── User.php
│   │   ├── TourGuide.php
│   │   ├── GuideLanguage.php
│   │   ├── GuideSpecialization.php
│   │   ├── GuideSchedule.php
│   │   ├── Booking.php
│   │   ├── Transaction.php
│   │   ├── Destination.php
│   │   ├── DestinationCategory.php
│   │   ├── Ticket.php
│   │   ├── TicketOrder.php
│   │   ├── Hotel.php
│   │   ├── HotelRoom.php
│   │   ├── HotelBooking.php
│   │   ├── Restaurant.php
│   │   ├── MenuItem.php
│   │   ├── RestaurantOrder.php
│   │   ├── Event.php
│   │   ├── EventRegistration.php
│   │   ├── AudioGuide.php
│   │   ├── Notification.php
│   │   ├── Review.php
│   │   ├── AuditLog.php
│   │   ├── ChatSession.php
│   │   ├── ChatMessage.php
│   │   ├── GuideDocument.php
│   │   ├── DestinationImage.php
│   │   ├── TicketOrderItem.php
│   │   ├── RestaurantOrderItem.php
│   │   ├── TransactionItem.php
│   │   └── Setting.php
│   │
│   ├── services/                    # Service layer — business logic
│   │   ├── BookingService.php
│   │   ├── PaymentService.php
│   │   ├── NotificationService.php
│   │   ├── ReportService.php
│   │   └── EmailService.php
│   │
│   ├── repositories/                # Repository layer — data access
│   │   ├── BookingRepository.php
│   │   ├── UserRepository.php
│   │   ├── DestinationRepository.php
│   │   └── TransactionRepository.php
│   │
│   └── views/                       # View — template HTML
│       ├── layouts/
│       │   ├── header.php
│       │   ├── footer.php
│       │   ├── navbar.php
│       │   └── sidebar.php
│       │
│       ├── auth/
│       │   ├── login.php
│       │   ├── register.php
│       │   └── forgot_password.php
│       │
│       ├── admin/
│       │   ├── dashboard.php
│       │   ├── users/
│       │   ├── destinations/
│       │   ├── guides/
│       │   ├── bookings/
│       │   ├── hotels/
│       │   ├── restaurants/
│       │   ├── events/
│       │   ├── audio/
│       │   ├── reports/
│       │   └── settings/
│       │
│       ├── wisatawan/
│       │   ├── dashboard.php
│       │   ├── search_guide.php
│       │   ├── guide_detail.php
│       │   ├── booking_form.php
│       │   ├── payment.php
│       │   ├── my_bookings.php
│       │   ├── my_tickets.php
│       │   ├── e_ticket.php
│       │   ├── map.php
│       │   ├── hotel_search.php
│       │   ├── hotel_detail.php
│       │   ├── restaurant_search.php
│       │   ├── restaurant_detail.php
│       │   ├── events.php
│       │   ├── event_detail.php
│       │   ├── audio_guide.php
│       │   ├── ai_chat.php
│       │   ├── my_orders.php
│       │   ├── my_events.php
│       │   ├── destination_detail.php
│       │   └── profile.php
│       │
│       ├── tourguide/
│       │   ├── dashboard.php
│       │   ├── profile.php
│       │   ├── profile_skills.php
│       │   ├── profile_documents.php
│       │   ├── schedule.php
│       │   ├── bookings_pending.php
│       │   ├── bookings_active.php
│       │   ├── bookings_history.php
│       │   ├── earnings.php
│       │   ├── earnings_history.php
│       │   └── reviews.php
│       │
│       ├── components/              # Komponen reusable
│       │   ├── card_destination.php
│       │   ├── card_guide.php
│       │   ├── card_hotel.php
│       │   ├── card_restaurant.php
│       │   ├── card_event.php
│       │   ├── review_form.php
│       │   ├── rating_stars.php
│       │   └── pagination.php
│       │
│       └── errors/
│           ├── 401.php
│           ├── 403.php
│           ├── 404.php
│           ├── 419.php
│           ├── 429.php
│           └── 500.php
│
├── public/                          # Public assets (web accessible)
│   ├── index.php                    # Entry point → ../index.php
│   ├── assets/
│   │   ├── css/
│   │   │   ├── style.css            # Custom CSS
│   │   │   ├── admin.css            # Admin-specific CSS
│   │   │   ├── map.css              # Map-specific CSS
│   │   │   └── responsive.css       # Media queries
│   │   │
│   │   ├── js/
│   │   │   ├── app.js               # Global JS (AJAX helper, dll)
│   │   │   ├── map.js               # Leaflet map logic
│   │   │   ├── booking.js           # Booking form logic
│   │   │   ├── chat.js              # AI chatbot logic
│   │   │   ├── admin.js             # Admin dashboard logic
│   │   │   └── validation.js        # Form validation
│   │   │
│   │   ├── img/                     # Static images
│   │   │   ├── logo.png
│   │   │   ├── favicon.ico
│   │   │   └── default-avatar.png
│   │   │
│   │   └── lib/                     # Third-party libraries (local CDN)
│   │       ├── bootstrap/
│   │       ├── jquery/
│   │       ├── leaflet/
│   │       ├── fontawesome/
│   │       ├── datatables/
│   │       ├── select2/
│   │       ├── sweetalert2/
│   │       └── chartjs/
│   │
│   └── uploads/                     # User uploaded files
│       ├── guides/                  # Foto profil guide
│       ├── destinations/            # Foto destinasi
│       ├── hotels/                  # Foto hotel
│       ├── restaurants/             # Foto restoran & menu
│       ├── events/                  # Foto event
│       ├── audio/                   # File audio guide
│       ├── documents/               # Dokumen verifikasi guide
│       ├── tickets/                 # QR code e-ticket
│       └── proofs/                  # Bukti pembayaran
│
├── database/                        # Database scripts
│   ├── migration.sql                # Skema database lengkap
│   ├── seed.sql                     # Data dummy untuk testing
│   ├── backup/                      # Folder backup otomatis
│   └── update/                      # Script update schema
│       ├── 001_add_ai_chat_table.sql
│       └── 002_add_notification_preferences.sql
│
├── logs/                            # Log files
│   ├── error.log                    # Error log
│   ├── access.log                   # Access log
│   └── audit.log                    # Audit log
│
├── cron/                            # Cron job scripts
│   ├── event_reminder.php           # Notifikasi H-1 event
│   └── cleanup_rate_limits.php      # Cleanup old rate_limits entries
│
├── index.php                        # Front controller (entry point)
├── .htaccess                        # Apache rewrite rules
├── .gitignore                       # Git ignore
├── README.md                        # Project readme
└── composer.json                    # Opsional (untuk autoload)
```

---

## 1.1 ADDITIONAL FOLDERS (Modern PHP Practices)

### Tests Folder Structure
```
tests/
├── unit/                        # Unit tests (PHPUnit)
│   ├── UserTest.php
│   ├── BookingTest.php
│   └── PaymentTest.php
└── integration/                 # Integration tests
    ├── AuthTest.php
    └── APITest.php
```

### Environment Files
```
.env                              # Environment variables (TIDAK di-commit ke Git)
.env.example                      # Template untuk .env
```

### Composer Files
```
composer.json                     # Composer configuration
composer.lock                     # Lock file (auto-generated)
vendor/                           # Composer dependencies (auto-generated)
```

---

## 2. FILE PENTING — KODE INTI

### 2.1 Front Controller (`index.php`)

```php
<?php
// index.php — Entry point aplikasi
define('BASE_PATH', __DIR__);
define('APP_PATH', BASE_PATH . '/app');

// Load config
$config = require APP_PATH . '/config/config.php';
foreach ($config as $key => $value) {
    define($key, $value);
}

// Autoload core classes
spl_autoload_register(function ($className) {
    $paths = [
        APP_PATH . '/core/' . $className . '.php',
        APP_PATH . '/controllers/' . $className . '.php',
        APP_PATH . '/models/' . $className . '.php',
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// Start session
Session::start();

// Routing
$app = new App();
$app->run();
```

### 2.2 Router (`app/core/App.php`)

```php
<?php
class App {
    private $controller = 'DashboardController';
    private $method = 'index';
    private $params = [];

    public function run() {
        $url = $this->parseUrl();

        // Controller
        if (isset($url[0]) && file_exists(APP_PATH . '/controllers/' . ucfirst($url[0]) . 'Controller.php')) {
            $this->controller = ucfirst($url[0]) . 'Controller';
            unset($url[0]);
        }
        $this->controller = new $this->controller();

        // Method
        if (isset($url[1]) && method_exists($this->controller, $url[1])) {
            $this->method = $url[1];
            unset($url[1]);
        }

        // Parameters
        $this->params = $url ? array_values($url) : [];

        // Call
        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    private function parseUrl() {
        if (isset($_GET['url'])) {
            return explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
        }
        return [];
    }
}
```

### 2.3 `.htaccess` (Apache)

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]

# Protect sensitive files
<FilesMatch "\.(md|sql|log)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Protect directories
RedirectMatch 403 /\.(git|env|config)

# Enable CORS for API (optional)
<IfModule mod_headers.c>
    Header set Access-Control-Allow-Origin "*"
    Header set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"
    Header set Access-Control-Allow-Headers "Content-Type, X-CSRF-Token"
</IfModule>
```

### 2.4 Nginx Config (Alternatif)

```nginx
server {
    listen 80;
    server_name tourguide.app;
    root /var/www/wisata;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?url=$uri&$args;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    # Deny access to sensitive files
    location ~ /\.(md|sql|log|git|env) {
        deny all;
    }

    # Protect app directory
    location /app {
        deny all;
        return 403;
    }
}
```

### 2.5 `.gitignore`

```gitignore
# Environment
.env
*.log

# Database credentials
app/config/database.php

# Uploads (keep structure, ignore files)
public/uploads/*
!public/uploads/.gitkeep

# Backup
database/backup/*

# IDE
.idea/
.vscode/
*.swp

# OS
.DS_Store
Thumbs.db

# Dependencies
vendor/
node_modules/
```

---

## 3. INTEGRATION GUIDELINES — LAYER COMMUNICATION

### ⚠️ CRITICAL: Layers Must Communicate Properly

Saat membangun aplikasi, pastikan komunikasi antar layer berikut:

#### 3.1 Controller ↔ Service Layer Integration

| Rule | Implementation |
|------|----------------|
| **Thin Controllers** | Controller hanya handle HTTP request/response, business logic di service |
| **Service Injection** | Inject service via constructor atau method injection |
| **Error Handling** | Catch service exceptions dan convert ke HTTP response |
| **Data Transformation** | Transform request data ke service DTO |
| **Response Mapping** | Map service response ke API response format |

#### 3.2 Service ↔ Repository Integration

| Rule | Implementation |
|------|----------------|
| **Repository Injection** | Inject repository via constructor |
| **Transaction Management** | Service manages transaction boundaries |
| **Business Logic** | All business rules di service layer |
| **Data Aggregation** | Service aggregate data dari multiple repositories |
| **Caching** | Service layer handle caching logic |

#### 3.3 Repository ↔ Database Integration

| Rule | Implementation |
|------|----------------|
| **Prepared Statements** | Selalu gunakan prepared statements |
| **Type Safety** | Gunakan type hints untuk parameters |
| **Error Handling** | Catch database exceptions dan convert ke repository exceptions |
| **Connection Management** | Gunakan singleton Database instance |
| **Query Optimization** | Gunakan indexes dan avoid N+1 queries |

#### 3.4 Integration Validation Checklist

Sebelum commit code, pastikan:

- [ ] Controller tidak mengandung business logic
- [ ] Service layer mengelola transaction boundaries
- [ ] Repository hanya handle data access
- [ ] Semua query menggunakan prepared statements
- [ ] Error handling di setiap layer
- [ ] Type hints digunakan untuk parameters
- [ ] Data validation sebelum insert/update
- [ ] Response format konsisten
- [ ] CSRF token validasi untuk state-changing
- [ ] Authentication/authorization check
- [ ] Audit log tercatat untuk sensitive actions

---

## 4. KONVENSI PENAMAAN

### 4.1 File & Class

| Tipe | Konvensi | Contoh |
|------|----------|--------|
| Controller | PascalCase + Controller | `TourGuideController.php` |
| Model | PascalCase (singular) | `TourGuide.php` |
| View | snake_case | `guide_detail.php` |
| CSS/JS | snake_case | `booking_form.js` |
| Config | lowercase | `database.php` |

### 4.2 Database

| Tipe | Konvensi | Contoh |
|------|----------|--------|
| Table | snake_case (plural) | `tour_guides`, `bookings` |
| Column | snake_case | `first_name`, `created_at` |
| Primary Key | `id` | `id` |
| Foreign Key | `{table_singular}_id` | `tour_guide_id`, `booking_id` |
| Pivot Table | `{table1_singular}_{table2_singular}` | `guide_language` |

### 4.3 URL Route

| Pola | Contoh | Controller::Method |
|------|--------|-------------------|
| `/controller` | `/tourguide` | TourGuideController::index |
| `/controller/method` | `/tourguide/list` | TourGuideController::list |
| `/controller/method/param` | `/tourguide/detail/5` | TourGuideController::detail(5) |
| `/api/resource` | `/api/destinations` | ApiController::destinations |

---

## 4. KONVENSI CODING

### 4.1 PHP

```php
<?php
// PSR-12 style
class TourGuideController extends Controller {

    public function detail($id) {
        // Validate
        if (!$id) {
            $this->redirect('tourguide/list');
        }

        // Load model
        $guideModel = $this->model('TourGuide');
        $guide = $guideModel->find($id);

        // Render view
        $this->view('wisatawan/guide_detail', [
            'title' => $guide['name'],
            'guide' => $guide
        ]);
    }
}
```

### 4.2 JavaScript (jQuery)

```javascript
// Snake case for functions, camelCase for variables
function load_markers(category) {
    let url = 'map/markers';
    if (category) url += '?category=' + category;

    API.get(url, function(response) {
        // Render markers
    });
}
```

### 4.3 CSS

```css
/* BEM-like naming */
.guide-card { }
.guide-card__title { }
.guide-card__image { }
.guide-card--featured { }
```

---

## 5. STRUKTUR MODUL DALAM FOLDER

Setiap modul akan memiliki file di 3 layer MVC:

| Modul | Controller | Model | View Folder |
|-------|-----------|-------|-------------|
| Auth | AuthController | User | auth/ |
| Tour Guide | TourGuideController | TourGuide, GuideLanguage, GuideSchedule | admin/guides/, wisatawan/, tourguide/ |
| Booking | BookingController | Booking, Transaction | admin/bookings/, wisatawan/ |
| Map | MapController | Destination | wisatawan/map.php |
| Tiket | DestinationController | Destination, Ticket, TicketOrder | admin/destinations/, wisatawan/ |
| Hotel | HotelController | Hotel, HotelRoom, HotelBooking | admin/hotels/, wisatawan/ |
| Restoran | RestaurantController | Restaurant, MenuItem, RestaurantOrder | admin/restaurants/, wisatawan/ |
| Event | EventController | Event, EventRegistration | admin/events/, wisatawan/ |
| Audio | AudioGuideController | AudioGuide | admin/audio/, wisatawan/ |
| AI Guide | AIGuideController | (custom) | wisatawan/ai_chat.php |
| Notifikasi | NotificationController | Notification | components/ |
| Report | ReportController | (multiple) | admin/reports/ |
| User Mgmt | UserController | User | admin/users/ |

---

## 6. DEPENDENCY MANAGEMENT

### 6.1 Composer (Recommended for Modern PHP)

```json
{
    "name": "wisata/tour-guide-app",
    "description": "Tour Guide Application - PHP Native MVC",
    "type": "project",
    "require": {
        "php": ">=8.1",
        "ext-pdo": "*",
        "ext-json": "*",
        "ext-mbstring": "*",
        "ext-gd": "*"
    },
    "require-dev": {
        "phpunit/phpunit": "^10.0",
        "phpstan/phpstan": "^1.10"
    },
    "autoload": {
        "psr-4": {
            "App\\": "app/"
        },
        "files": [
            "app/config/config.php"
        ]
    },
    "autoload-dev": {
        "psr-4": {
            "Tests\\": "tests/"
        }
    },
    "scripts": {
        "test": "phpunit",
        "analyze": "phpstan analyse app --level=5"
    },
    "config": {
        "optimize-autoloader": true,
        "preferred-install": "dist",
        "sort-packages": true
    }
}
```

### 6.2 .env Example

```bash
# .env.example
# Copy this file to .env and fill in your values

# Application
APP_ENV=development
APP_DEBUG=true
BASE_URL=http://localhost/wisata/

# Database
DB_HOST=localhost
DB_NAME=tour_guide_app
DB_USER=root
DB_PASS=
DB_CHARSET=utf8mb4

# Session
SESSION_TIMEOUT=1800

# Upload
UPLOAD_PATH=public/uploads/
MAX_UPLOAD_SIZE=5242880

# Email (SMTP)
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@tourguide.app
MAIL_FROM_NAME=Tour Guide App
```

### 6.3 Frontend Libraries (CDN)

```html
<!-- CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
```

---

> **Modul Selanjutnya:** `05_DESAIN_DATABASE_MYSQL_ERD.md` — Desain database, ERD, dan kamus data
