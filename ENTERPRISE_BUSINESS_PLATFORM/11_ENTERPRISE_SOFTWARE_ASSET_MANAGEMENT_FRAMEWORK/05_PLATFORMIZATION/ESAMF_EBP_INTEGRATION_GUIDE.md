# ESAMF EBP Integration Guide

**Document ID:** ESAMF-PLATFORMIZATION-002

**Version:** 1.0

**Purpose:** Define the EBP integration methodology for ESAMF

---

# Overview

EBP Integration is the process of integrating migrated components with the EBP platform, including EBP Core services, Shared Engines, and platform infrastructure. This guide provides a systematic methodology for EBP integration.

---

# Integration Layers

## Layer 1: Core Service Integration

### Authentication Integration

**Before:**
```php
class SalesController {
    private $authService;
    
    public function __construct() {
        $this->authService = new AuthService();
    }
    
    public function createOrder() {
        $user = $this->authService->authenticate($_SESSION['token']);
    }
}
```

**After:**
```php
use EBP\Core\Authentication\AuthServiceInterface;
use EBP\Core\Authentication\AuthMiddleware;

class SalesController {
    private $authService;
    
    public function __construct(AuthServiceInterface $authService) {
        $this->authService = $authService;
    }
    
    #[AuthMiddleware]
    public function createOrder(Request $request) {
        $user = $request->user();
    }
}
```

### Authorization Integration

**Before:**
```php
class SalesController {
    public function deleteUser($id) {
        if (!$this->userService->canDelete($this->currentUser, $id)) {
            throw new Exception('Forbidden');
        }
    }
}
```

**After:**
```php
use EBP\Core\Authorization\AuthorizeMiddleware;

class SalesController {
    #[AuthorizeMiddleware('user.delete')]
    public function deleteUser($id) {
        // Authorization handled by middleware
    }
}
```

### Audit Trail Integration

**Before:**
```php
class SalesController {
    public function createOrder() {
        $order = $this->orderService->create($data);
        $this->logAction('order_created', $order->id);
    }
}
```

**After:**
```php
use EBP\Core\Audit\AuditServiceInterface;

class SalesController {
    private $auditService;
    
    public function __construct(AuditServiceInterface $auditService) {
        $this->auditService = $auditService;
    }
    
    public function createOrder() {
        $order = $this->orderService->create($data);
        $this->auditService->log('order_created', [
            'order_id' => $order->id,
            'user_id' => $this->currentUser->id
        ]);
    }
}
```

### Configuration Integration

**Before:**
```php
class NotificationService {
    private $emailProvider = 'sendgrid';
    private $apiKey = 'SG.xxx';
}
```

**After:**
```php
use EBP\Core\Configuration\ConfigServiceInterface;

class NotificationService {
    private $config;
    
    public function __construct(ConfigServiceInterface $config) {
        $this->config = $config;
    }
    
    public function sendEmail($message, $recipient) {
        $provider = $this->config->get('notification.email_provider');
        $apiKey = $this->config->get('notification.email_api_key');
    }
}
```

---

## Layer 2: Shared Engine Integration

### Notification Engine Integration

**Before:**
```php
class SalesController {
    private $notificationService;
    
    public function __construct() {
        $this->notificationService = new NotificationService();
    }
    
    public function createOrder() {
        $order = $this->orderService->create($data);
        $this->notificationService->sendEmail($order->customer->email, 'Order created');
    }
}
```

**After:**
```php
use EBP\SharedEngines\Notification\NotificationEngineInterface;

class SalesController {
    private $notificationEngine;
    
    public function __construct(NotificationEngineInterface $notificationEngine) {
        $this->notificationEngine = $notificationEngine;
    }
    
    public function createOrder() {
        $order = $this->orderService->create($data);
        $this->notificationEngine->sendTemplate('order_created', [
            'order_id' => $order->id,
            'customer_name' => $order->customer->name
        ], $order->customer->email);
    }
}
```

### Reporting Engine Integration

**Before:**
```php
class SalesController {
    public function generateReport() {
        $orders = Order::all();
        $report = $this->generateReportData($orders);
        return $report;
    }
}
```

**After:**
```php
use EBP\SharedEngines\Reporting\ReportingEngineInterface;

class SalesController {
    private $reportingEngine;
    
    public function __construct(ReportingEngineInterface $reportingEngine) {
        $this->reportingEngine = $reportingEngine;
    }
    
    public function generateReport() {
        return $this->reportingEngine->generateReport('sales_summary', [
            'date_range' => $this->request->input('date_range')
        ]);
    }
}
```

### Inventory Engine Integration

**Before:**
```php
class SalesController {
    public function createOrder() {
        $order = $this->orderService->create($data);
        foreach ($order->items as $item) {
            $this->updateInventory($item->product_id, $item->quantity);
        }
    }
}
```

**After:**
```php
use EBP\SharedEngines\Inventory\InventoryEngineInterface;

class SalesController {
    private $inventoryEngine;
    
    public function __construct(InventoryEngineInterface $inventoryEngine) {
        $this->inventoryEngine = $inventoryEngine;
    }
    
    public function createOrder() {
        $order = $this->orderService->create($data);
        foreach ($order->items as $item) {
            $this->inventoryEngine->reserveStock($item->product_id, $item->quantity, [
                'order_id' => $order->id
            ]);
        }
    }
}
```

---

## Layer 3: Infrastructure Integration

### Database Integration

**Before:**
```php
class OrderService {
    private $db;
    
    public function __construct() {
        $this->db = new MySQLConnection();
    }
    
    public function createOrder($data) {
        $this->db->query("INSERT INTO orders ...");
    }
}
```

**After:**
```php
use EBP\Core\Database\DatabaseInterface;

class OrderService {
    private $db;
    
    public function __construct(DatabaseInterface $db) {
        $this->db = $db;
    }
    
    public function createOrder($data) {
        return $this->db->table('orders')->insert($data);
    }
}
```

### Cache Integration

**Before:**
```php
class OrderService {
    public function getOrder($id) {
        return Order::find($id);
    }
}
```

**After:**
```php
use EBP\Core\Cache\CacheInterface;

class OrderService {
    private $cache;
    
    public function __construct(CacheInterface $cache) {
        $this->cache = $cache;
    }
    
    public function getOrder($id) {
        return $this->cache->remember("order_{$id}", 3600, function () use ($id) {
            return Order::find($id);
        });
    }
}
```

### Queue Integration

**Before:**
```php
class OrderService {
    public function createOrder($data) {
        $order = Order::create($data);
        $this->sendNotification($order);
        $this->updateInventory($order);
    }
}
```

**After:**
```php
use EBP\Core\Queue\QueueInterface;

class OrderService {
    private $queue;
    
    public function __construct(QueueInterface $queue) {
        $this->queue = $queue;
    }
    
    public function createOrder($data) {
        $order = Order::create($data);
        
        $this->queue->push('SendOrderNotification', ['order_id' => $order->id]);
        $this->queue->push('UpdateInventory', ['order_id' => $order->id]);
    }
}
```

### Logging Integration

**Before:**
```php
class OrderService {
    public function createOrder($data) {
        error_log("Creating order: " . json_encode($data));
        $order = Order::create($data);
    }
}
```

**After:**
```php
use EBP\Core\Logging\LoggerInterface;

class OrderService {
    private $logger;
    
    public function __construct(LoggerInterface $logger) {
        $this->logger = $logger;
    }
    
    public function createOrder($data) {
        $this->logger->info('Creating order', ['data' => $data]);
        $order = Order::create($data);
        $this->logger->info('Order created', ['order_id' => $order->id]);
    }
}
```

---

# Integration Process

## Phase 1: Dependency Analysis

### Step 1: Identify EBP Dependencies

```
EBP Core Dependencies:
- [Core Service 1: How it's used]
- [Core Service 2: How it's used]

EBP Shared Engine Dependencies:
- [Shared Engine 1: How it's used]
- [Shared Engine 2: How it's used]

Infrastructure Dependencies:
- [Infrastructure 1: How it's used]
- [Infrastructure 2: How it's used]
```

### Step 2: Create Integration Plan

```
Integration Steps:
1. [Step 1: Service, Priority]
2. [Step 2: Service, Priority]
3. [Step 3: Service, Priority]
```

---

## Phase 2: Service Registration

### Step 1: Create Service Provider

```php
<?php
// app/Providers/EBPServiceProvider.php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use EBP\Core\Authentication\AuthServiceInterface;
use EBP\Core\Authorization\AuthorizeServiceInterface;
use EBP\Core\Audit\AuditServiceInterface;
use EBP\SharedEngines\Notification\NotificationEngineInterface;

class EBPServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register EBP Core services
        $this->app->singleton(AuthServiceInterface::class, function ($app) {
            return new \EBP\Core\Authentication\AuthService(
                $app->make(ConfigServiceInterface::class),
                $app->make(DatabaseInterface::class)
            );
        });
        
        $this->app->singleton(AuthorizeServiceInterface::class, function ($app) {
            return new \EBP\Core\Authorization\AuthorizeService(
                $app->make(DatabaseInterface::class)
            );
        });
        
        $this->app->singleton(AuditServiceInterface::class, function ($app) {
            return new \EBP\Core\Audit\AuditService(
                $app->make(DatabaseInterface::class)
            );
        });
        
        // Register EBP Shared Engines
        $this->app->singleton(NotificationEngineInterface::class, function ($app) {
            return new \EBP\SharedEngines\Notification\NotificationEngine(
                $app->make(ConfigServiceInterface::class)
            );
        });
    }
    
    public function boot()
    {
        // Register middleware
        $this->app['router']->aliasMiddleware('auth', \EBP\Core\Authentication\AuthMiddleware::class);
        $this->app['router']->aliasMiddleware('authorize', \EBP\Core\Authorization\AuthorizeMiddleware::class);
    }
}
```

### Step 2: Register Service Provider

```php
// config/app.php

'providers' => [
    // ...
    App\Providers\EBPServiceProvider::class,
],
```

---

## Phase 3: Component Integration

### Step 1: Inject Dependencies

```php
use EBP\Core\Authentication\AuthServiceInterface;
use EBP\Core\Authorization\AuthorizeServiceInterface;
use EBP\Core\Audit\AuditServiceInterface;
use EBP\SharedEngines\Notification\NotificationEngineInterface;

class OrderService
{
    private $authService;
    private $authorizeService;
    private $auditService;
    private $notificationEngine;
    
    public function __construct(
        AuthServiceInterface $authService,
        AuthorizeServiceInterface $authorizeService,
        AuditServiceInterface $auditService,
        NotificationEngineInterface $notificationEngine
    ) {
        $this->authService = $authService;
        $this->authorizeService = $authorizeService;
        $this->auditService = $auditService;
        $this->notificationEngine = $notificationEngine;
    }
}
```

### Step 2: Replace Direct Calls

**Before:**
```php
$user = $_SESSION['user'];
```

**After:**
```php
$user = $this->authService->getCurrentUser();
```

### Step 3: Apply Middleware

```php
// routes/web.php

Route::middleware(['auth', 'authorize:order.create'])
    ->group(function () {
        Route::post('/orders', [OrderController::class, 'create']);
    });
```

---

## Phase 4: Configuration Integration

### Step 1: Create EBP Configuration File

```php
// config/ebp.php

return [
    'core' => [
        'authentication' => [
            'token_expiry' => env('AUTH_TOKEN_EXPIRY', 3600),
            'max_attempts' => env('AUTH_MAX_ATTEMPTS', 5),
        ],
        'audit' => [
            'enabled' => env('AUDIT_ENABLED', true),
            'log_all' => env('AUDIT_LOG_ALL', false),
        ],
    ],
    'shared_engines' => [
        'notification' => [
            'email_provider' => env('NOTIFICATION_EMAIL_PROVIDER', 'sendgrid'),
            'sms_provider' => env('NOTIFICATION_SMS_PROVIDER', 'twilio'),
        ],
    ],
];
```

### Step 2: Update Environment Variables

```bash
# .env

AUTH_TOKEN_EXPIRY=3600
AUTH_MAX_ATTEMPTS=5
AUDIT_ENABLED=true
AUDIT_LOG_ALL=false
NOTIFICATION_EMAIL_PROVIDER=sendgrid
NOTIFICATION_SMS_PROVIDER=twilio
```

---

## Phase 5: Testing Integration

### Step 1: Create Integration Tests

```php
class EBPIntegrationTest extends TestCase
{
    public function testAuthenticationIntegration()
    {
        $authService = $this->app->make(AuthServiceInterface::class);
        
        $result = $authService->login('testuser', 'password');
        
        $this->assertTrue($result->success);
        $this->assertNotNull($result->token);
    }
    
    public function testNotificationIntegration()
    {
        $notificationEngine = $this->app->make(NotificationEngineInterface::class);
        
        $result = $notificationEngine->sendTemplate('test', [], 'test@example.com');
        
        $this->assertTrue($result->success);
    }
}
```

### Step 2: Run Integration Tests

```bash
php artisan test --testsuite=Integration
```

---

# Integration Checklist

## Core Service Integration
- [ ] Authentication integrated
- [ ] Authorization integrated
- [ ] Audit Trail integrated
- [ ] Configuration integrated
- [ ] Error Handling integrated
- [ ] Logging integrated

## Shared Engine Integration
- [ ] Notification Engine integrated
- [ ] Reporting Engine integrated
- [ ] Inventory Engine integrated
- [ ] Pricing Engine integrated
- [ ] Payment Engine integrated

## Infrastructure Integration
- [ ] Database integrated
- [ ] Cache integrated
- [ ] Queue integrated
- [ ] Logging integrated
- [ ] Monitoring integrated

## Configuration
- [ ] EBP configuration file created
- [ ] Environment variables updated
- [ ] Service provider registered
- [ ] Middleware registered

## Testing
- [ ] Integration tests created
- [ ] Integration tests passing
- [ ] End-to-end tests passing
- [ ] Performance tests passing

---

# Common Integration Patterns

## Pattern 1: Service Injection

**Problem:** Component directly instantiates dependencies

**Solution:** Inject EBP services through constructor

```php
// Before
class OrderService {
    private $authService;
    
    public function __construct() {
        $this->authService = new AuthService();
    }
}

// After
use EBP\Core\Authentication\AuthServiceInterface;

class OrderService {
    private $authService;
    
    public function __construct(AuthServiceInterface $authService) {
        $this->authService = $authService;
    }
}
```

## Pattern 2: Middleware Application

**Problem:** Authentication/authorization logic in controller

**Solution:** Apply EBP middleware

```php
// Before
class OrderController {
    public function createOrder() {
        if (!$this->authService->isAuthenticated()) {
            return redirect('/login');
        }
    }
}

// After
class OrderController {
    #[AuthMiddleware]
    #[AuthorizeMiddleware('order.create')]
    public function createOrder() {
        // Authentication/authorization handled by middleware
    }
}
```

## Pattern 3: Event-Based Integration

**Problem:** Tight coupling between components

**Solution:** Use EBP event system

```php
// Before
class OrderService {
    public function createOrder($data) {
        $order = Order::create($data);
        $this->notificationService->sendOrderCreated($order);
        $this->inventoryService->updateInventory($order);
    }
}

// After
class OrderService {
    public function createOrder($data) {
        $order = Order::create($data);
        event(new OrderCreated($order));
    }
}

// Event listener
class OrderCreatedListener
{
    public function handle(OrderCreated $event)
    {
        $this->notificationEngine->sendTemplate('order_created', [], $event->order->customer->email);
        $this->inventoryEngine->reserveStock($event->order->items);
    }
}
```

---

# Document End

**Document ID:** ESAMF-PLATFORMIZATION-002

**Version:** 1.0
