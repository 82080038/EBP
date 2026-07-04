# MODUL 19 — MODUL REPORT & ANALYTIC

> **Versi:** 1.1 · **Tanggal:** 2026-06-30 · **Last Updated:** 2026-06-30

---

## 1. RINGKASAN

Modul laporan dan analitik untuk admin dan tour guide: dashboard statistik,
grafik tren, dan export laporan.

---

## 2. LAPORAN ADMIN

### 2.1 Dashboard Statistik

```php
<?php
class ReportController extends Controller {

    public function __construct() {
        Middleware::requireRole('admin');
    }

    public function dashboard() {
        $trxModel = $this->model('Transaction');
        $userModel = $this->model('User');
        $bookingModel = $this->model('Booking');
        $destModel = $this->model('Destination');

        $this->view('admin/reports/dashboard', [
            'title' => 'Laporan & Analitik',
            'stats' => [
                'total_users' => count($userModel->all(['status' => 'active'])),
                'total_revenue' => $trxModel->getTotalRevenue(),
                'monthly_revenue' => $trxModel->getMonthlyRevenue(),
                'total_bookings' => $bookingModel->countAll(),
                'monthly_bookings' => $bookingModel->countThisMonth(),
                'total_destinations' => count($destModel->all(['is_active' => 1]))
            ],
            'revenue_chart' => $trxModel->getMonthlyRevenueChart(12),
            'top_destinations' => $destModel->getTopSold(5),
            'top_guides' => $bookingModel->getTopGuides(5)
        ]);
    }
}
```

### 2.2 Model Queries

```php
// Transaction model
public function getTotalRevenue() {
    return $this->db->query(
        "SELECT COALESCE(SUM(net_amount), 0) as total FROM transactions WHERE payment_status = 'paid'"
    )->fetch()['total'];
}

public function getMonthlyRevenue() {
    return $this->db->query(
        "SELECT COALESCE(SUM(net_amount), 0) as total FROM transactions
         WHERE payment_status = 'paid'
         AND MONTH(created_at) = MONTH(CURRENT_DATE())
         AND YEAR(created_at) = YEAR(CURRENT_DATE())"
    )->fetch()['total'];
}

public function getMonthlyRevenueChart($months = 12) {
    return $this->db->query(
        "SELECT DATE_FORMAT(created_at, '%Y-%m') as month,
                COALESCE(SUM(net_amount), 0) as revenue
         FROM transactions WHERE payment_status = 'paid'
         AND created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL ? MONTH)
         GROUP BY month ORDER BY month"
    , [$months])->fetchAll();
}

// Destination model
public function getTopSold($limit = 5) {
    return $this->db->query(
        "SELECT d.name, d.city, COUNT(t.id) as tickets_sold,
                COALESCE(SUM(t.total_amount), 0) as revenue
         FROM destinations d
         LEFT JOIN ticket_orders t ON t.id IN (
             SELECT toi.order_id FROM ticket_order_items toi
             JOIN tickets tk ON toi.ticket_id = tk.id
             WHERE tk.destination_id = d.id
         )
         WHERE d.is_active = 1
         GROUP BY d.id ORDER BY tickets_sold DESC LIMIT ?"
    , [$limit])->fetchAll();
}

// Booking model
public function getTopGuides($limit = 5) {
    return $this->db->query(
        "SELECT u.name, g.rating_avg, g.total_tours,
                COUNT(b.id) as total_bookings,
                COALESCE(SUM(b.total_amount), 0) as revenue
         FROM tour_guides g
         JOIN users u ON g.user_id = u.id
         LEFT JOIN bookings b ON b.guide_id = g.id AND b.status = 'completed'
         WHERE g.is_verified = 1
         GROUP BY g.id ORDER BY total_bookings DESC LIMIT ?"
    , [$limit])->fetchAll();
}
```

---

## 3. VIEW: Dashboard Report

```php
<!-- app/views/admin/reports/dashboard.php -->
<?php include 'app/views/layouts/header.php'; ?>

<div class="container-fluid mt-3">
    <h2>Laporan & Analitik</h2>

    <div class="row mb-4">
        <div class="col-md-3"><div class="card bg-primary text-white"><div class="card-body">
            <h6>Total Pendapatan</h6><h4>Rp <?= number_format($stats['total_revenue'], 0, ',', '.') ?></h4>
        </div></div></div>
        <div class="col-md-3"><div class="card bg-success text-white"><div class="card-body">
            <h6>Pendapatan Bulan Ini</h6><h4>Rp <?= number_format($stats['monthly_revenue'], 0, ',', '.') ?></h4>
        </div></div></div>
        <div class="col-md-3"><div class="card bg-info text-white"><div class="card-body">
            <h6>Total Booking</h6><h4><?= $stats['total_bookings'] ?></h4>
        </div></div></div>
        <div class="col-md-3"><div class="card bg-warning text-white"><div class="card-body">
            <h6>Booking Bulan Ini</h6><h4><?= $stats['monthly_bookings'] ?></h4>
        </div></div></div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Tren Pendapatan 12 Bulan</div>
                <div class="card-body"><canvas id="revenueChart" height="100"></canvas></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">Destinasi Terlaris</div>
                <div class="card-body">
                    <table class="table table-sm">
                        <?php foreach ($top_destinations as $d): ?>
                        <tr><td><?= $d['name'] ?></td><td class="text-end"><?= $d['tickets_sold'] ?></td></tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
new Chart($('#revenueChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($revenue_chart, 'month')) ?>,
        datasets: [{
            label: 'Pendapatan',
            data: <?= json_encode(array_column($revenue_chart, 'revenue')) ?>,
            backgroundColor: 'rgba(54, 162, 235, 0.5)'
        }]
    }
});
</script>
<?php include 'app/views/layouts/footer.php'; ?>
```

---

## 4. EXPORT CSV

```php
public function exportTransactions() {
    Middleware::requireRole('admin');
    $trxModel = $this->model('Transaction');
    $data = $trxModel->getAllWithDetails();

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="transactions_' . date('Ymd') . '.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['Kode', 'User', 'Type', 'Amount', 'Method', 'Status', 'Tanggal']);
    foreach ($data as $row) {
        fputcsv($output, [$row['transaction_code'], $row['name'], $row['type'],
            $row['net_amount'], $row['payment_method'], $row['payment_status'], $row['created_at']]);
    }
    fclose($output);
    exit;
}
```

---

## 5. LAPORAN TOUR GUIDE

```php
public function guideEarnings() {
    Middleware::requireRole('tour_guide');
    $guideModel = $this->model('TourGuide');
    $guide = $guideModel->findByUserId($_SESSION['user_id']);

    $this->view('tourguide/earnings', [
        'title' => 'Pendapatan Saya',
        'total' => $guideModel->getTotalEarnings($guide['id']),
        'monthly' => $guideModel->getEarnings($guide['id'], 'month'),
        'history' => $guideModel->getEarnings($guide['id'])
    ]);
}
```

---

## 6. API ENDPOINTS

| Method | URL | Fungsi |
|--------|-----|--------|
| GET | `api/admin/report/dashboard` | Data dashboard |
| GET | `api/admin/report/transactions` | Laporan transaksi |
| GET | `api/admin/report/destinations` | Statistik destinasi |
| GET | `api/admin/report/guides` | Statistik guide |
| GET | `api/admin/report/export-csv` | Export CSV |
| GET | `api/guide/earnings` | Pendapatan guide |

---

## 7. USER ANALYTICS

**Status:** Not Implemented — MEDIUM PRIORITY

Implementasi user analytics untuk tracking user behavior dan engagement:

```php
// app/services/UserAnalyticsService.php
class UserAnalyticsService {
    public function trackPageView(int $userId, string $page, array $metadata = []): void {
        $sql = "INSERT INTO user_analytics 
                (user_id, event_type, page, metadata, created_at) 
                VALUES (:user_id, 'page_view', :page, :metadata, NOW())";
        
        $this->db->query($sql, [
            'user_id' => $userId,
            'page' => $page,
            'metadata' => json_encode($metadata)
        ]);
    }

    public function trackEvent(int $userId, string $eventName, array $data = []): void {
        $sql = "INSERT INTO user_analytics 
                (user_id, event_type, event_name, event_data, created_at) 
                VALUES (:user_id, 'custom_event', :event_name, :data, NOW())";
        
        $this->db->query($sql, [
            'user_id' => $userId,
            'event_name' => $eventName,
            'data' => json_encode($data)
        ]);
    }

    public function getUserFunnel(): array {
        $funnel = [
            'landing_page' => $this->getEventCount('page_view', '/'),
            'search_guide' => $this->getEventCount('page_view', '/guides'),
            'view_guide_profile' => $this->getEventCount('page_view', '/guide/'),
            'start_booking' => $this->getEventCount('custom_event', 'booking_started'),
            'complete_booking' => $this->getEventCount('custom_event', 'booking_completed'),
        ];

        return $funnel;
    }

    public function getUserRetention(int $days = 30): array {
        $sql = "SELECT user_id, MIN(created_at) as first_visit, 
                MAX(created_at) as last_visit,
                COUNT(DISTINCT DATE(created_at)) as active_days
                FROM user_analytics
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                GROUP BY user_id";
        
        return $this->db->query($sql, ['days' => $days])->fetchAll();
    }

    private function getEventCount(string $eventType, string $pattern): int {
        $sql = "SELECT COUNT(*) as cnt FROM user_analytics 
                WHERE event_type = :event_type AND (page LIKE :pattern OR event_name LIKE :pattern)";
        
        return $this->db->query($sql, [
            'event_type' => $eventType,
            'pattern' => "%{$pattern}%"
        ])->fetch()['cnt'];
    }
}
```

### User Analytics Table

```sql
CREATE TABLE user_analytics (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    session_id VARCHAR(100) NULL,
    event_type VARCHAR(50) NOT NULL,
    page VARCHAR(255) NULL,
    event_name VARCHAR(100) NULL,
    event_data JSON NULL,
    metadata JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_event_type (event_type),
    INDEX idx_created_at (created_at),
    INDEX idx_session_id (session_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Analytics Dashboard

```php
// app/controllers/Admin/AnalyticsController.php
class AnalyticsController extends Controller {
    public function index(): void {
        $analytics = new UserAnalyticsService();
        
        $this->view('admin/analytics', [
            'funnel' => $analytics->getUserFunnel(),
            'retention' => $analytics->getUserRetention(30),
            'top_pages' => $analytics->getTopPages(),
            'user_segments' => $analytics->getUserSegments(),
        ]);
    }
}
```

---

## 8. A/B TESTING

**Status:** Not Implemented — MEDIUM PRIORITY

Implementasi A/B testing untuk UX optimization:

```php
// app/services/ABTestService.php
class ABTestService {
    private $activeTests = [
        'booking_cta_color' => ['control' => 'blue', 'variant' => 'green'],
        'search_results_layout' => ['control' => 'list', 'variant' => 'grid'],
    ];

    public function getVariant(string $testName, int $userId): string {
        $variant = $this->getUserVariant($testName, $userId);
        
        if (!$variant) {
            $variant = $this->assignVariant($testName, $userId);
        }
        
        return $variant;
    }

    private function assignVariant(string $testName, int $userId): string {
        $variants = $this->activeTests[$testName];
        $variant = (rand(0, 1) === 0) ? 'control' : 'variant';
        
        $sql = "INSERT INTO ab_test_assignments 
                (test_name, user_id, variant, assigned_at) 
                VALUES (:test_name, :user_id, :variant, NOW())";
        
        $this->db->query($sql, [
            'test_name' => $testName,
            'user_id' => $userId,
            'variant' => $variant
        ]);
        
        return $variant;
    }

    public function trackConversion(string $testName, int $userId): void {
        $sql = "UPDATE ab_test_assignments 
                SET converted = 1, converted_at = NOW() 
                WHERE test_name = :test_name AND user_id = :user_id";
        
        $this->db->query($sql, [
            'test_name' => $testName,
            'user_id' => $userId
        ]);
    }

    public function getTestResults(string $testName): array {
        $sql = "SELECT variant, COUNT(*) as total, 
                SUM(converted) as conversions,
                (SUM(converted) / COUNT(*)) * 100 as conversion_rate
                FROM ab_test_assignments
                WHERE test_name = :test_name
                GROUP BY variant";
        
        return $this->db->query($sql, ['test_name' => $testName])->fetchAll();
    }
}
```

### A/B Test Table

```sql
CREATE TABLE ab_test_assignments (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    test_name VARCHAR(100) NOT NULL,
    user_id INT NOT NULL,
    variant VARCHAR(20) NOT NULL,
    converted TINYINT(1) DEFAULT 0,
    converted_at DATETIME NULL,
    assigned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY idx_test_user (test_name, user_id),
    INDEX idx_test_name (test_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## 9. RECOMMENDATION ENGINE

**Status:** Not Implemented — MEDIUM PRIORITY

Implementasi recommendation engine untuk personalized suggestions:

```php
// app/services/RecommendationService.php
class RecommendationService {
    public function getRecommendedGuides(int $userId, int $limit = 5): array {
        // Collaborative filtering based on user's past bookings
        $userPreferences = $this->getUserPreferences($userId);
        
        $sql = "SELECT g.*, 
                (g.rating * 0.4 + 
                 (SELECT COUNT(*) FROM bookings b WHERE b.guide_id = g.id) * 0.3 +
                 (g.languages LIKE :language) * 0.3) as score
                FROM tour_guides g
                WHERE g.status = 'approved'
                ORDER BY score DESC
                LIMIT :limit";
        
        return $this->db->query($sql, [
            'language' => "%{$userPreferences['language']}%",
            'limit' => $limit
        ])->fetchAll();
    }

    public function getRecommendedDestinations(int $userId, int $limit = 5): array {
        $userHistory = $this->getUserBookingHistory($userId);
        $preferredCategories = $this->extractCategories($userHistory);
        
        $sql = "SELECT d.*, 
                (d.rating * 0.5 + 
                 (d.category_id IN (:categories)) * 0.5) as score
                FROM destinations d
                WHERE d.category_id IN (:categories)
                ORDER BY score DESC
                LIMIT :limit";
        
        return $this->db->query($sql, [
            'categories' => $preferredCategories,
            'limit' => $limit
        ])->fetchAll();
    }

    private function getUserPreferences(int $userId): array {
        $sql = "SELECT g.languages, g.specialization 
                FROM bookings b
                JOIN tour_guides g ON b.guide_id = g.id
                WHERE b.user_id = :user_id
                GROUP BY g.id
                LIMIT 5";
        
        $bookings = $this->db->query($sql, ['user_id' => $userId])->fetchAll();
        
        $languages = [];
        foreach ($bookings as $booking) {
            $languages[] = $booking['languages'];
        }
        
        return ['language' => implode(',', $languages)];
    }
}
```

---

## 10. CHAT SUPPORT SYSTEM

**Status:** Not Implemented — MEDIUM PRIORITY

Implementasi live chat support untuk user assistance:

```php
// app/services/ChatSupportService.php
class ChatSupportService {
    public function sendMessage(int $userId, string $message): void {
        $sql = "INSERT INTO chat_messages 
                (user_id, sender_type, message, created_at) 
                VALUES (:user_id, 'user', :message, NOW())";
        
        $this->db->query($sql, ['user_id' => $userId, 'message' => $message]);
        
        // Notify admin
        $this->notificationService->notifyAdmin('new_chat_message', [
            'user_id' => $userId,
            'message' => $message
        ]);
    }

    public function getChatHistory(int $userId): array {
        $sql = "SELECT * FROM chat_messages 
                WHERE user_id = :user_id 
                ORDER BY created_at ASC";
        
        return $this->db->query($sql, ['user_id' => $userId])->fetchAll();
    }

    public function sendAdminResponse(int $userId, string $message): void {
        $sql = "INSERT INTO chat_messages 
                (user_id, sender_type, message, created_at) 
                VALUES (:user_id, 'admin', :message, NOW())";
        
        $this->db->query($sql, ['user_id' => $userId, 'message' => $message]);
    }
}
```

### Chat Messages Table

```sql
CREATE TABLE chat_messages (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    sender_type ENUM('user', 'admin') NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

> **Modul Selanjutnya:** `20_SECURITY_SYSTEM.md`
