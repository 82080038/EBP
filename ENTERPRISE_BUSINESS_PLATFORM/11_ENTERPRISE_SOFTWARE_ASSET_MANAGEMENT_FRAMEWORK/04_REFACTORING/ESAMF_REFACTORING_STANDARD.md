# ESAMF Refactoring Standard

**Document ID:** ESAMF-REFACTORING-001

**Version:** 1.0

**Purpose:** Define the refactoring standards for ESAMF

---

# Overview

Refactoring is the process of improving the internal structure of code without changing its external behavior. This standard defines the refactoring approach for migrating code to EBP standards.

---

# Refactoring Principles

## 1. Behavior Preservation

**Refactoring must not change the external behavior of the code.**

- Inputs produce the same outputs
- Side effects remain the same
- API contracts are maintained
- Business logic is preserved

## 2. Incremental Changes

**Refactoring should be done in small, safe increments.**

- One refactoring at a time
- Test after each change
- Commit after each successful refactoring
- Rollback if tests fail

## 3. Test-Driven Refactoring

**Refactoring must be supported by comprehensive tests.**

- Write tests before refactoring
- Ensure tests pass before refactoring
- Ensure tests pass after refactoring
- Add tests for edge cases

## 4. EBP Standards Compliance

**Refactored code must comply with EBP standards.**

- Coding standards (PSR-12)
- Architecture standards
- Security standards
- Documentation standards

---

# Refactoring Process

## Phase 1: Preparation

### Step 1: Understand Current Code

```
Component: [Component Name]
Location: [Component Location]
Purpose: [Component Purpose]
Dependencies: [List dependencies]
```

### Step 2: Write Characterization Tests

```php
// Characterization test - captures current behavior
class ComponentTest extends TestCase {
    public function testCurrentBehavior() {
        $component = new Component();
        $input = [/* test input */];
        $expected = [/* expected output */];
        
        $result = $component->method($input);
        
        $this->assertEquals($expected, $result);
    }
}
```

### Step 3: Identify Refactoring Opportunities

```
Refactoring Opportunities:
- [Opportunity 1: Type, Priority]
- [Opportunity 2: Type, Priority]
- [Opportunity 3: Type, Priority]
```

### Step 4: Create Refactoring Branch

```bash
git checkout -b refactor/[component-name]
```

---

## Phase 2: Refactoring

### Step 1: Apply Coding Standards

#### PSR-12 Compliance

```bash
# Run PHP_CodeSniffer
phpcs --standard=PSR12 [component-location]

# Fix automatically where possible
phpcbf --standard=PSR12 [component-location]
```

#### Naming Conventions

**Before:**
```php
class auth_service {
    function get_user_data() {
        // ...
    }
}
```

**After:**
```php
class AuthService {
    function getUserData() {
        // ...
    }
}
```

### Step 2: Improve Code Structure

#### Extract Method

**Before:**
```php
class OrderService {
    public function processOrder($order) {
        // Validate order
        if (empty($order->items)) {
            throw new Exception('Order has no items');
        }
        
        // Calculate total
        $total = 0;
        foreach ($order->items as $item) {
            $total += $item->price * $item->quantity;
        }
        
        // Apply discount
        if ($order->discount) {
            $total -= $order->discount;
        }
        
        // Save order
        $order->total = $total;
        $order->save();
    }
}
```

**After:**
```php
class OrderService {
    public function processOrder($order) {
        $this->validateOrder($order);
        $total = $this->calculateTotal($order);
        $total = $this->applyDiscount($order, $total);
        $this->saveOrder($order, $total);
    }
    
    private function validateOrder($order) {
        if (empty($order->items)) {
            throw new Exception('Order has no items');
        }
    }
    
    private function calculateTotal($order) {
        $total = 0;
        foreach ($order->items as $item) {
            $total += $item->price * $item->quantity;
        }
        return $total;
    }
    
    private function applyDiscount($order, $total) {
        if ($order->discount) {
            $total -= $order->discount;
        }
        return $total;
    }
    
    private function saveOrder($order, $total) {
        $order->total = $total;
        $order->save();
    }
}
```

#### Extract Class

**Before:**
```php
class OrderService {
    public function processOrder($order) {
        // Calculate tax
        $taxRate = 0.1;
        $tax = $order->total * $taxRate;
        
        // Calculate shipping
        $shipping = $this->calculateShipping($order);
        
        // Calculate final total
        $finalTotal = $order->total + $tax + $shipping;
    }
    
    private function calculateShipping($order) {
        // Shipping logic
    }
}
```

**After:**
```php
class OrderService {
    private $pricingService;
    
    public function __construct(PricingServiceInterface $pricingService) {
        $this->pricingService = $pricingService;
    }
    
    public function processOrder($order) {
        $finalTotal = $this->pricingService->calculateFinalTotal($order);
    }
}

class PricingService implements PricingServiceInterface {
    public function calculateFinalTotal($order) {
        $tax = $this->calculateTax($order);
        $shipping = $this->calculateShipping($order);
        return $order->total + $tax + $shipping;
    }
    
    private function calculateTax($order) {
        $taxRate = 0.1;
        return $order->total * $taxRate;
    }
    
    private function calculateShipping($order) {
        // Shipping logic
    }
}
```

### Step 3: Apply Dependency Injection

**Before:**
```php
class AuthService {
    private $db;
    
    public function __construct() {
        $this->db = new MySQLConnection();
    }
}
```

**After:**
```php
class AuthService {
    private $db;
    
    public function __construct(DatabaseInterface $db) {
        $this->db = $db;
    }
}
```

### Step 4: Implement Interfaces

**Before:**
```php
class AuthService {
    public function login($username, $password) {
        // Implementation
    }
}
```

**After:**
```php
interface AuthServiceInterface {
    public function login($username, $password, $context = []);
    public function logout($token);
    public function validate($token);
}

class AuthService implements AuthServiceInterface {
    public function login($username, $password, $context = []) {
        // Implementation
    }
    
    public function logout($token) {
        // Implementation
    }
    
    public function validate($token) {
        // Implementation
    }
}
```

### Step 5: Add Documentation

**Before:**
```php
class AuthService {
    public function login($username, $password) {
        // Implementation
    }
}
```

**After:**
```php
/**
 * Authentication Service
 *
 * Provides user authentication and session management functionality.
 *
 * @package EBP\Core\Authentication
 * @author Petrick Software
 * @version 1.0.0
 */
class AuthService implements AuthServiceInterface {
    /**
     * Authenticate user with username and password
     *
     * @param string $username User's username
     * @param string $password User's password
     * @param array $context Additional context (tenant_id, ip_address, etc.)
     * @return AuthResult Authentication result
     * @throws AuthenticationException If authentication fails
     */
    public function login($username, $password, $context = []) {
        // Implementation
    }
}
```

---

## Phase 3: Security Refactoring

### Step 1: Fix SQL Injection

**Before:**
```php
$query = "SELECT * FROM users WHERE username = '$username'";
$result = $db->query($query);
```

**After:**
```php
$query = "SELECT * FROM users WHERE username = ?";
$result = $db->query($query, [$username]);
```

### Step 2: Fix XSS

**Before:**
```php
echo $userInput;
```

**After:**
```php
echo htmlspecialchars($userInput, ENT_QUOTES, 'UTF-8');
```

### Step 3: Add CSRF Protection

**Before:**
```php
<form method="POST">
    <input type="text" name="data">
    <button type="submit">Submit</button>
</form>
```

**After:**
```php
<form method="POST">
    <input type="hidden" name="csrf_token" value="{{ csrf_token() }}">
    <input type="text" name="data">
    <button type="submit">Submit</button>
</form>
```

### Step 4: Add Input Validation

**Before:**
```php
public function createUser($data) {
    $user = new User();
    $user->name = $data['name'];
    $user->email = $data['email'];
    $user->save();
}
```

**After:**
```php
public function createUser($data) {
    $validated = $this->validator->validate($data, [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users'
    ]);
    
    $user = new User();
    $user->name = $validated['name'];
    $user->email = $validated['email'];
    $user->save();
}
```

---

## Phase 4: Performance Refactoring

### Step 1: Optimize Database Queries

**Before:**
```php
// N+1 query problem
$orders = Order::all();
foreach ($orders as $order) {
    echo $order->user->name; // Separate query for each order
}
```

**After:**
```php
// Eager loading
$orders = Order::with('user')->get();
foreach ($orders as $order) {
    echo $order->user->name; // No additional queries
}
```

### Step 2: Add Caching

**Before:**
```php
public function getSettings() {
    return Settings::all();
}
```

**After:**
```php
public function getSettings() {
    return Cache::remember('settings', 3600, function () {
        return Settings::all();
    });
}
```

### Step 3: Optimize Loops

**Before:**
```php
foreach ($items as $item) {
    $result[] = $this->processItem($item);
}
```

**After:**
```php
$results = array_map([$this, 'processItem'], $items);
```

---

## Phase 5: Testing

### Step 1: Run Tests

```bash
# Run all tests
phpunit

# Run specific test
phpunit --filter testMethodName
```

### Step 2: Verify Behavior

```bash
# Ensure characterization tests pass
phpunit --filter testCurrentBehavior
```

### Step 3: Add New Tests

```php
class RefactoredComponentTest extends TestCase {
    public function testNewBehavior() {
        // Test new behavior
    }
    
    public function testEdgeCase() {
        // Test edge case
    }
}
```

### Step 4: Achieve Coverage

- **Target**: > 80% code coverage
- **Critical Paths**: 100% coverage
- **Refactored Code**: 100% coverage

---

## Phase 6: Review

### Step 1: Self-Review

- [ ] Code follows EBP standards
- [ ] Behavior is preserved
- [ ] Tests pass
- [ ] No new issues introduced

### Step 2: Peer Review

- [ ] Peer review completed
- [ ] Reviewer feedback addressed
- [ ] Changes approved

### Step 3: Diff Review

```bash
# Review changes
git diff origin/main

# Review commit history
git log --oneline
```

---

## Phase 7: Commit

### Step 1: Stage Changes

```bash
git add [changed-files]
```

### Step 2: Commit with Message

```bash
git commit -m "refactor: [Component] - [Description]

- Changed [what]
- Reason [why]
- Impact [how it affects behavior]
"
```

### Step 3: Push to Remote

```bash
git push origin refactor/[component-name]
```

---

# Common Refactoring Patterns

## Pattern 1: Extract Method

**When:** Method is too long or does too many things

**How:** Extract part of method into separate method

**Before:**
```php
public function processOrder($order) {
    // Long method with multiple responsibilities
}
```

**After:**
```php
public function processOrder($order) {
    $this->validateOrder($order);
    $this->calculateTotal($order);
    $this->saveOrder($order);
}
```

## Pattern 2: Extract Class

**When:** Class has too many responsibilities

**How:** Extract related methods into separate class

**Before:**
```php
class OrderService {
    // Order processing
    // Pricing calculation
    // Shipping calculation
    // Tax calculation
}
```

**After:**
```php
class OrderService {
    private $pricingService;
    private $shippingService;
    
    public function __construct(
        PricingServiceInterface $pricingService,
        ShippingServiceInterface $shippingService
    ) {
        $this->pricingService = $pricingService;
        $this->shippingService = $shippingService;
    }
}
```

## Pattern 3: Replace Conditional with Polymorphism

**When:** Complex conditional logic based on type

**How:** Use polymorphism instead of conditionals

**Before:**
```php
class NotificationService {
    public function send($notification) {
        if ($notification->type === 'email') {
            // Send email
        } elseif ($notification->type === 'sms') {
            // Send SMS
        } elseif ($notification->type === 'push') {
            // Send push
        }
    }
}
```

**After:**
```php
interface NotificationProviderInterface {
    public function send($notification);
}

class EmailProvider implements NotificationProviderInterface {
    public function send($notification) {
        // Send email
    }
}

class SMSProvider implements NotificationProviderInterface {
    public function send($notification) {
        // Send SMS
    }
}

class NotificationService {
    private $providers;
    
    public function __construct(array $providers) {
        $this->providers = $providers;
    }
    
    public function send($notification) {
        $provider = $this->providers[$notification->type];
        $provider->send($notification);
    }
}
```

## Pattern 4: Introduce Parameter Object

**When:** Method has too many parameters

**How:** Group related parameters into object

**Before:**
```php
public function createUser($name, $email, $password, $address, $phone, $role) {
    // Implementation
}
```

**After:**
```php
public function createUser(CreateUserRequest $request) {
    // Implementation
}

class CreateUserRequest {
    public $name;
    public $email;
    public $password;
    public $address;
    public $phone;
    public $role;
}
```

## Pattern 5: Replace Magic Numbers with Constants

**When:** Magic numbers in code

**How:** Replace with named constants

**Before:**
```php
if ($user->age >= 18) {
    // Adult
}
```

**After:**
```php
const ADULT_AGE = 18;

if ($user->age >= self::ADULT_AGE) {
    // Adult
}
```

---

# Refactoring Checklist

## Preparation
- [ ] Current code understood
- [ ] Characterization tests written
- [ ] Refactoring opportunities identified
- [ ] Refactoring branch created

## Coding Standards
- [ ] PSR-12 compliance applied
- [ ] Naming conventions applied
- [ ] Code structure improved
- [ ] Dependencies injected

## Security
- [ ] SQL injection fixed
- [ ] XSS fixed
- [ ] CSRF protection added
- [ ] Input validation added

## Performance
- [ ] Database queries optimized
- [ ] Caching added
- [ ] Loops optimized
- [ ] Resource usage optimized

## Testing
- [ ] Tests pass
- [ ] Behavior preserved
- [ ] New tests added
- [ ] Coverage achieved

## Review
- [ ] Self-review completed
- [ ] Peer review completed
- [ ] Diff reviewed
- [ ] Changes approved

## Commit
- [ ] Changes staged
- [ ] Commit message written
- [ ] Pushed to remote

---

# Document End

**Document ID:** ESAMF-REFACTORING-001

**Version:** 1.0
