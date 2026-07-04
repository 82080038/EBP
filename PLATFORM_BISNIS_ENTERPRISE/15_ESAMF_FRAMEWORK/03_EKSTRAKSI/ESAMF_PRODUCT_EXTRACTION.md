# ESAMF Product Extraction

**Document ID:** ESAMF-EXTRACTION-003

**Version:** 1.0

**Purpose:** Define the methodology for extracting Product Assets from repositories

---

# Overview

Product Extraction is the process of isolating domain-specific components from repositories and preparing them for migration to EBP Products. Unlike Core Assets and Shared Engines, Product Assets are not extracted for reuse—they are extracted for refactoring to use EBP Core and Shared Engines.

---

# Product Asset Characteristics

Before extraction, verify the component meets Product Asset criteria:

- **Domain-Specific**: Used by ONE product
- **Industry-Specific**: Contains business domain logic
- **Specialized**: Tailored to specific business needs
- **Unique**: Differentiates the product
- **Valuable**: Provides product-specific value

---

# Extraction Process

## Phase 1: Preparation

### Step 1: Component Identification

```
Component: [Component Name]
Location: [Component Location]
Files:
- File 1: [Path]
- File 2: [Path]
- File 3: [Path]
```

### Step 2: Business Domain Analysis

```
Business Domain: [Hospitality/Tourism/Retail/Finance/Education/Culture/Public Service]
Product: [Product Name]
Business Purpose: [What business problem it solves]
Business Value: [Business value provided]
```

### Step 3: Dependency Analysis

```
EBP Core Dependencies:
- [Core Asset 1: How it's used]
- [Core Asset 2: How it's used]

EBP Shared Engine Dependencies:
- [Shared Engine 1: How it's used]
- [Shared Engine 2: How it's used]

Repository-Specific Dependencies:
- [Dependency 1: Should be replaced]
- [Dependency 2: Should be replaced]
```

### Step 4: Create Extraction Branch

```bash
git checkout -b extract/[component-name]
```

---

## Phase 2: Isolation

### Step 1: Copy Component Files

```bash
# Create temporary directory
mkdir -p temp/[component-name]

# Copy component files
cp [component-location]/* temp/[component-name]/
```

### Step 2: Identify Reusable Logic

```
Reusable Logic (Should use EBP Core/Shared Engines):
- [Logic 1: Can use EBP Core]
- [Logic 2: Can use Shared Engine]
- [Logic 3: Can use Shared Engine]
```

### Step 3: Identify Product-Specific Logic

```
Product-Specific Logic (Should remain in product):
- [Logic 1: Product-specific]
- [Logic 2: Product-specific]
- [Logic 3: Product-specific]
```

### Step 4: Identify Technical Debt

```
Technical Debt:
- [Debt 1: Description, Priority]
- [Debt 2: Description, Priority]
- [Debt 3: Description, Priority]
```

---

## Phase 3: Refactoring for EBP Integration

### Step 1: Replace with EBP Core

**Before:**
```php
class SalesController {
    private $authService;
    
    public function __construct() {
        $this->authService = new AuthService();
    }
}
```

**After:**
```php
use EBP\Core\Authentication\AuthServiceInterface;

class SalesController {
    private $authService;
    
    public function __construct(AuthServiceInterface $authService) {
        $this->authService = $authService;
    }
}
```

### Step 2: Replace with Shared Engines

**Before:**
```php
class SalesController {
    private $notificationService;
    
    public function __construct() {
        $this->notificationService = new NotificationService();
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
}
```

### Step 3: Remove Duplicate Code

**Before:**
```php
class SalesController {
    public function validateInput($data) {
        // Validation logic (duplicate of EBP Core)
    }
}
```

**After:**
```php
use EBP\Core\Validation\ValidationServiceInterface;

class SalesController {
    private $validationService;
    
    public function __construct(ValidationServiceInterface $validationService) {
        $this->validationService = $validationService;
    }
    
    public function createOrder($data) {
        $this->validationService->validate($data, $this->getValidationRules());
    }
}
```

### Step 4: Apply EBP Standards

**Before:**
```php
class SalesController {
    public function createOrder() {
        $order = new Order();
        $order->user_id = $_POST['user_id'];
        $order->total = $_POST['total'];
        $order->save();
    }
}
```

**After:**
```php
use EBP\Core\Response\ResponseInterface;

class SalesController {
    private $orderService;
    private $response;
    
    public function __construct(
        OrderServiceInterface $orderService,
        ResponseInterface $response
    ) {
        $this->orderService = $orderService;
        $this->response = $response;
    }
    
    public function createOrder(Request $request) {
        $validated = $request->validate([
            'user_id' => 'required|integer',
            'total' => 'required|numeric'
        ]);
        
        $order = $this->orderService->createOrder($validated);
        
        return $this->response->json($order, 201);
    }
}
```

---

## Phase 4: Business Logic Preservation

### Step 1: Document Business Rules

```php
/**
 * Kitchen Display System
 * 
 * Business Rules:
 * 1. Orders are routed to kitchen stations based on menu items
 * 2. High-priority orders are displayed at the top
 * 3. Orders are marked as "in progress" when chef accepts
 * 4. Orders are marked as "ready" when all items are complete
 * 5. Orders are marked as "served" when waiter confirms
 */
class KitchenDisplayService {
    // Implementation
}
```

### Step 2: Preserve Domain Logic

```php
class KitchenDisplayService {
    /**
     * Route order to appropriate kitchen station
     * 
     * Business Rule: Orders are routed based on menu item categories
     * - Appetizers → Prep Station
     * - Main Courses → Cooking Station
     * - Desserts → Pastry Station
     * - Drinks → Bar Station
     */
    public function routeOrder(Order $order) {
        foreach ($order->items as $item) {
            $station = $this->determineStation($item->category);
            $this->assignToStation($item, $station);
        }
    }
    
    private function determineStation($category) {
        // Restaurant-specific station logic
        $stationMap = [
            'appetizer' => 'prep',
            'main_course' => 'cooking',
            'dessert' => 'pastry',
            'drink' => 'bar'
        ];
        
        return $stationMap[$category] ?? 'cooking';
    }
}
```

### Step 3: Add Context Parameters

```php
class KitchenDisplayService {
    public function routeOrder(Order $order, $context = []) {
        // Context can include:
        // - restaurant_id
        // - kitchen_layout
        // - station_capacity
        // - priority_rules
        
        $restaurantId = $context['restaurant_id'] ?? null;
        $layout = $context['kitchen_layout'] ?? 'standard';
        
        // Route based on context
    }
}
```

---

## Phase 5: Standardization

### Step 1: Apply EBP Coding Standards

- **PSR-12**: Follow PSR-12 coding standards
- **Naming**: Follow EBP naming conventions
- **Documentation**: Add PHPDoc blocks
- **Comments**: Add inline comments for business logic

### Step 2: Apply EBP Architecture Standards

- **Dependency Injection**: Use EBP Core and Shared Engines
- **Service Layer**: Use service layer pattern
- **Repository Pattern**: Use repository pattern for data access
- **API Standards**: Follow EBP API standards

### Step 3: Apply EBP Security Standards

- **Authentication**: Use EBP Core Authentication
- **Authorization**: Use EBP Core Authorization
- **Input Validation**: Use EBP Core Validation
- **Output Encoding**: Use EBP Core Output Encoding

### Step 4: Apply EBP Performance Standards

- **Caching**: Use EBP Core Cache
- **Queueing**: Use EBP Core Queue
- **Monitoring**: Use EBP Core Monitoring
- **Logging**: Use EBP Core Logging

---

## Phase 6: Testing

### Step 1: Create Unit Tests

```php
class KitchenDisplayServiceTest extends TestCase {
    public function testRouteOrder() {
        $order = $this->createMock(Order::class);
        $order->items = [
            $this->createItem('appetizer'),
            $this->createItem('main_course')
        ];
        
        $service = new KitchenDisplayService();
        $service->routeOrder($order);
        
        $this->assertEquals('prep', $order->items[0]->station);
        $this->assertEquals('cooking', $order->items[1]->station);
    }
    
    public function testDetermineStation() {
        $service = new KitchenDisplayService();
        
        $this->assertEquals('prep', $service->determineStation('appetizer'));
        $this->assertEquals('cooking', $service->determineStation('main_course'));
        $this->assertEquals('pastry', $service->determineStation('dessert'));
        $this->assertEquals('bar', $service->determineStation('drink'));
    }
}
```

### Step 2: Create Integration Tests

```php
class KitchenDisplayServiceIntegrationTest extends TestCase {
    public function testRouteOrderWithDatabase() {
        $order = Order::create([
            'table_id' => 1,
            'status' => 'pending'
        ]);
        
        $order->items()->createMany([
            ['product_id' => 1, 'quantity' => 2],
            ['product_id' => 2, 'quantity' => 1]
        ]);
        
        $service = new KitchenDisplayService();
        $service->routeOrder($order);
        
        $this->assertDatabaseHas('kitchen_orders', [
            'order_id' => $order->id,
            'station' => 'prep'
        ]);
    }
}
```

### Step 3: Create EBP Integration Tests

```php
class KitchenDisplayServiceEBPIntegrationTest extends TestCase {
    public function testRouteOrderWithEBPNotification() {
        $notificationEngine = $this->createMock(NotificationEngineInterface::class);
        $notificationEngine->expects($this->once())
                          ->method('sendNotification');
        
        $service = new KitchenDisplayService($notificationEngine);
        $service->routeOrder($order);
    }
}
```

### Step 4: Achieve Test Coverage

- **Target**: > 80% code coverage
- **Business Logic**: 100% coverage
- **EBP Integration**: Test all EBP integrations
- **Error Paths**: Test all error paths

---

## Phase 7: Documentation

### Step 1: Create README

```markdown
# [Component Name]

## Purpose
[Brief description of component purpose]

## Business Domain
[Business domain]

## Business Rules
[List business rules]

## Installation
[Installation instructions]

## Usage
```php
use EBP\Products\[Product]\[Component]\[ComponentName];

$component = new [ComponentName]($ebpServices);
$result = $component->method($params);
```

## EBP Dependencies
[List EBP Core and Shared Engine dependencies]

## API
[API documentation]
```

### Step 2: Add PHPDoc Blocks

```php
/**
 * Kitchen Display Service
 *
 * Manages kitchen workflow and order routing for restaurant operations.
 *
 * Business Rules:
 * - Orders are routed to kitchen stations based on menu items
 * - High-priority orders are displayed at the top
 * - Orders are marked as "in progress" when chef accepts
 *
 * @package EBP\Products\RestaurantERP\Kitchen
 * @author Petrick Software
 * @version 1.0.0
 */
class KitchenDisplayService {
    /**
     * Route order to appropriate kitchen station
     *
     * Business Rule: Orders are routed based on menu item categories
     *
     * @param Order $order Order to route
     * @param array $context Additional context (restaurant_id, kitchen_layout, etc.)
     * @return void
     */
    public function routeOrder(Order $order, $context = []) {
        // Implementation
    }
}
```

### Step 3: Document Business Rules

```markdown
# Business Rules

## Order Routing
- Orders are routed to kitchen stations based on menu item categories
- Appetizers → Prep Station
- Main Courses → Cooking Station
- Desserts → Pastry Station
- Drinks → Bar Station

## Priority Handling
- VIP orders are displayed at the top
- Rush orders are highlighted
- Normal orders are displayed in order received

## Status Transitions
- pending → in_progress (when chef accepts)
- in_progress → ready (when all items complete)
- ready → served (when waiter confirms)
```

### Step 4: Create Migration Guide

```markdown
# Migration Guide

## Changes from Original
- [Change 1]
- [Change 2]
- [Change 3]

## EBP Integration
- [EBP Core integration 1]
- [EBP Shared Engine integration 1]

## Breaking Changes
- [Breaking change 1]
- [Breaking change 2]

## Migration Steps
1. [Step 1]
2. [Step 2]
3. [Step 3]
```

---

## Phase 8: Code Review

### Step 1: Self-Review

- [ ] Code follows EBP standards
- [ ] Code is well-documented
- [ ] Business logic is preserved
- [ ] EBP integration is correct
- [ ] Tests are comprehensive

### Step 2: Peer Review

- [ ] Peer review completed
- [ ] Reviewer feedback addressed
- [ ] Changes approved

### Step 3: Business Review

- [ ] Business rules verified
- [ ] Business logic preserved
- [ ] Business value maintained
- [ ] Changes approved

### Step 4: Architecture Review

- [ ] Architecture review completed
- [ ] Architecture concerns addressed
- [ ] Changes approved

---

## Phase 9: Integration

### Step 1: Integrate with Product

```bash
# Move to product directory
mv temp/[component-name] /path/to/PRODUCTS/[PRODUCT]/[component-name]
```

### Step 2: Update Product Configuration

```php
// Update product service provider
public function register() {
    $this->app->singleton(KitchenDisplayServiceInterface::class, function ($app) {
        return new KitchenDisplayService(
            $app->make(NotificationEngineInterface::class),
            $app->make(AuthServiceInterface::class)
        );
    });
}
```

### Step 3: Update Product Documentation

- Update product README
- Update API documentation
- Update architecture documentation

---

# Extraction Checklist

## Preparation
- [ ] Component identified
- [ ] Business domain analyzed
- [ ] Dependencies analyzed
- [ ] Extraction branch created

## Isolation
- [ ] Component files copied
- [ ] Reusable logic identified
- [ ] Product-specific logic identified
- [ ] Technical debt identified

## Refactoring
- [ ] Replaced with EBP Core
- [ ] Replaced with Shared Engines
- [ ] Removed duplicate code
- [ ] Applied EBP standards

## Business Logic Preservation
- [ ] Business rules documented
- [ ] Domain logic preserved
- [ ] Context parameters added

## Standardization
- [ ] EBP coding standards applied
- [ ] EBP architecture standards applied
- [ ] EBP security standards applied
- [ ] EBP performance standards applied

## Testing
- [ ] Unit tests created
- [ ] Integration tests created
- [ ] EBP integration tests created
- [ ] Test coverage > 80%

## Documentation
- [ ] README created
- [ ] PHPDoc blocks added
- [ ] Business rules documented
- [ ] Migration guide created

## Code Review
- [ ] Self-review completed
- [ ] Peer review completed
- [ ] Business review completed
- [ ] Architecture review completed

## Integration
- [ ] Integrated with product
- [ ] Product configuration updated
- [ ] Product documentation updated

---

# Common Extraction Patterns

## Pattern 1: EBP Core Replacement

**Problem:** Component has its own implementation of core functionality

**Solution:** Replace with EBP Core

```php
// Before
class SalesController {
    private $authService;
    
    public function __construct() {
        $this->authService = new AuthService();
    }
}

// After
use EBP\Core\Authentication\AuthServiceInterface;

class SalesController {
    private $authService;
    
    public function __construct(AuthServiceInterface $authService) {
        $this->authService = $authService;
    }
}
```

## Pattern 2: Shared Engine Integration

**Problem:** Component has its own implementation of shared functionality

**Solution:** Integrate with Shared Engine

```php
// Before
class SalesController {
    private $notificationService;
    
    public function __construct() {
        $this->notificationService = new NotificationService();
    }
}

// After
use EBP\SharedEngines\Notification\NotificationEngineInterface;

class SalesController {
    private $notificationEngine;
    
    public function __construct(NotificationEngineInterface $notificationEngine) {
        $this->notificationEngine = $notificationEngine;
    }
}
```

## Pattern 3: Business Logic Preservation

**Problem:** Need to preserve business logic while using EBP services

**Solution:** Wrap business logic in service layer

```php
// Before
class SalesController {
    public function createOrder() {
        // Business logic mixed with controller logic
        $order = new Order();
        $order->calculateTotal();
        $order->applyDiscount();
        $order->save();
    }
}

// After
class OrderService {
    /**
     * Calculate order total with restaurant-specific rules
     */
    private function calculateTotal(Order $order) {
        // Restaurant-specific business logic
    }
    
    /**
     * Apply discount with restaurant-specific rules
     */
    private function applyDiscount(Order $order) {
        // Restaurant-specific business logic
    }
}
```

## Pattern 4: Context-Aware Behavior

**Problem:** Component needs to behave differently based on context

**Solution:** Add context parameters

```php
// Before
class KitchenDisplayService {
    public function routeOrder(Order $order) {
        // Assumes standard kitchen layout
    }
}

// After
class KitchenDisplayService {
    public function routeOrder(Order $order, $context = []) {
        $layout = $context['kitchen_layout'] ?? 'standard';
        
        // Route based on layout
    }
}
```

---

# Document End

**Document ID:** ESAMF-EXTRACTION-003

**Version:** 1.0
