# Enterprise Business Platform (EBP)

# Architecture Guard Specification


**Document ID:** EBP-ARCHITECTURE-GUARD-001

**Version:** 1.0

**Category:** Architecture Enforcement Standard

**Status:** Official Enforcement Specification



---

# 1. Introduction


Dokumen ini mendefinisikan Architecture Guard untuk Enterprise Business Platform (EBP).

Architecture Guard memastikan:


* Developer tidak melanggar aturan arsitektur
* Code mengikuti pattern yang benar
* Dependency rule diikuti
* Constitution enforcement otomatis
* Quality gate di CI/CD

Tujuan:


```

CODE

+

ARCHITECTURE GUARD

+

CI/CD

=

CONSISTENT ARCHITECTURE

```



---

# 2. Architecture Guard Philosophy


EBP Architecture Guard menggunakan prinsip:


```

ENFORCEMENT OVER TRUST

```

Artinya:


* Jangan percaya developer mengikuti aturan
* Enforce aturan secara otomatis
* Reject code yang melanggar
* Catch violation sebelum merge



---

# 3. Architecture Rules


## Rule 1: Controller Cannot Access Database Directly


### Violation


```php
class OrderController
{
    public function createOrder()
    {
        // VIOLATION: Direct database access
        $result = $this->db->query(
            "INSERT INTO orders ..."
        );
    }
}
```


### Correct


```php
class OrderController
{
    public function createOrder()
    {
        // CORRECT: Use service
        $order = $this->orderService->createOrder($data);
    }
}
```


## Rule 2: Service Cannot Access HTTP Layer


### Violation


```php
class OrderService
{
    public function createOrder()
    {
        // VIOLATION: Service accessing HTTP
        $request = $this->request->input();
    }
}
```


### Correct


```php
class OrderService
{
    public function createOrder($data)
    {
        // CORRECT: Service receives data as parameter
        $order = $this->orderRepository->save($data);
    }
}
```


## Rule 3: Repository Cannot Contain Business Logic


### Violation


```php
class OrderRepository
{
    public function save($data)
    {
        // VIOLATION: Business logic in repository
        if ($data['total'] > 1000000) {
            $data['discount'] = 10;
        }
        
        $this->db->insert('orders', $data);
    }
}
```


### Correct


```php
class OrderRepository
{
    public function save($data)
    {
        // CORRECT: Repository only saves data
        $this->db->insert('orders', $data);
    }
}
```


## Rule 4: Model Cannot Contain Validation


### Violation


```php
class Order
{
    public function save()
    {
        // VIOLATION: Validation in model
        if ($this->total <= 0) {
            throw new Exception('Invalid total');
        }
    }
}
```


### Correct


```php
class OrderService
{
    public function createOrder($data)
    {
        // CORRECT: Validation in service
        $this->validateOrder($data);
        
        $order = $this->orderRepository->save($data);
    }
}
```


## Rule 5: Core Cannot Import Product Code


### Violation


```php
// In Core Framework
use EBP\Products\Restaurant\Order;

class CoreService
{
    // VIOLATION: Core importing product
}
```


### Correct


```php
// In Core Framework
namespace EBP\Core;

class CoreService
{
    // CORRECT: Core only uses core
}
```



---

# 4. Architecture Guard Implementation


## Static Analysis


### PHPStan


Configuration:


```neon
parameters:
    level: 8
    paths:
        - src
    ignoreErrors:
        # Allow specific violations with justification
    bootstrapFiles:
        - tests/bootstrap.php
```

Custom Rules:


```php
class NoDirectDatabaseAccessRule implements \PHPStan\Rules\Rule
{
    public function getNodeType(): string
    {
        return \PhpParser\Node\Expr\MethodCall::class;
    }
    
    public function processNode(\PhpParser\Node $node, \PHPStan\Analyser\Scope $scope): array
    {
        // Check if controller calls database directly
        if ($this->isController($scope) && $this->isDatabaseCall($node)) {
            return [
                new \PHPStan\Rules\RuleError(
                    'Controller cannot access database directly. Use service instead.',
                    \PHPStan\Rules\Line::fromLine($node->getStartLine())
                )
            ];
        }
        
        return [];
    }
}
```


## Dependency Analysis


### Deptrac


Configuration:


```yaml
deptrac:
    paths:
        - ./src
    layers:
        - name: Controller
          collectors:
            - type: className
              regex: .*Controller
        - name: Service
          collectors:
            - type: className
              regex: .*Service
        - name: Repository
          collectors:
            - type: className
              regex: .*Repository
        - name: Database
          collectors:
            - type: className
              regex: .*DB|.*PDO
    ruleset:
      Controller:
        - Service
      Service:
        - Repository
      Repository:
        - Database
      Database:
        - Controller # Forbidden
```



---

# 5. CI/CD Integration


## GitHub Actions Workflow


```yaml
name: Architecture Guard

on:
  pull_request:
    branches: [ master, develop ]

jobs:
  architecture-check:
    runs-on: ubuntu-latest
    
    steps:
      - uses: actions/checkout@v2
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.1'
      
      - name: Install dependencies
        run: composer install --no-progress
      
      - name: Run PHPStan
        run: vendor/bin/phpstan analyse src --level=8
      
      - name: Run Deptrac
        run: vendor/bin/deptrac analyse --config-file=deptrac.yaml
      
      - name: Run Custom Architecture Tests
        run: vendor/bin/phpunit tests/Architecture
```



---

# 6. Custom Architecture Tests


## Controller Database Access Test


```php
class ControllerDatabaseAccessTest extends TestCase
{
    public function testControllerCannotAccessDatabaseDirectly()
    {
        $controllers = $this->getAllControllers();
        
        foreach ($controllers as $controller) {
            $content = file_get_contents($controller);
            
            // Check for direct database calls
            $this->assertDoesNotMatchRegularExpression(
                '/\$this->db->query/',
                $content,
                "Controller {$controller} should not access database directly"
            );
            
            $this->assertDoesNotMatchRegularExpression(
                '/DB::/',
                $content,
                "Controller {$controller} should not use DB facade"
            );
        }
    }
}
```


## Service HTTP Access Test


```php
class ServiceHTTPAccessTest extends TestCase
{
    public function testServiceCannotAccessHTTP()
    {
        $services = $this->getAllServices();
        
        foreach ($services as $service) {
            $content = file_get_contents($service);
            
            // Check for HTTP access
            $this->assertDoesNotMatchRegularExpression(
                '/\$this->request/',
                $content,
                "Service {$service} should not access HTTP layer"
            );
            
            $this->assertDoesNotMatchRegularExpression(
                '/Request::/',
                $content,
                "Service {$service} should not use Request facade"
            );
        }
    }
}
```


## Core Product Dependency Test


```php
class CoreProductDependencyTest extends TestCase
{
    public function testCoreCannotImportProduct()
    {
        $coreFiles = $this->getCoreFiles();
        
        foreach ($coreFiles as $file) {
            $content = file_get_contents($file);
            
            // Check for product imports
            $this->assertDoesNotMatchRegularExpression(
                '/use EBP\\\\Products/',
                $content,
                "Core file {$file} should not import product code"
            );
        }
    }
}
```



---

# 7. Architecture Violation Handling


## Violation Severity


### Critical


* Core importing product
* Security violation
* Data exposure


### High


* Controller accessing database
* Service accessing HTTP
* Circular dependency


### Medium


* Repository with business logic
* Model with validation
* Missing dependency injection


### Low


* Code style violation
* Naming convention
* Missing documentation


## Violation Action


### Critical


```

REJECT MERGE

+

Notify ARB

+

Block until fixed

```


### High


```

REJECT MERGE

+

Notify developer

+

Require fix before retry

```


### Medium


```

WARNING

+

Allow merge with comment

+

Create issue to fix

```


### Low


```

COMMENT

+

Allow merge

+

Create issue to fix

```



---

# 8. Architecture Guard Dashboard


## Metrics


```

Total Violations: 150

Critical: 5
High: 20
Medium: 50
Low: 75

Violation Trend: Decreasing

Architecture Score: 85/100

```


## Violation by Type


```

Controller Database Access: 30

Service HTTP Access: 25

Repository Business Logic: 40

Model Validation: 35

Core Product Dependency: 5

Circular Dependency: 15

```



---

# 9. Constitution Enforcement


## Automated Constitution Check


```php
class ConstitutionGuard
{
    public function checkConstitution($code)
    {
        $violations = [];
        
        // Check architecture principles
        if ($this->violatesSeparationOfConcerns($code)) {
            $violations[] = [
                'type' => 'constitution',
                'rule' => 'separation_of_concerns',
                'severity' => 'high',
                'message' => 'Code violates separation of concerns principle'
            ];
        }
        
        // Check dependency rules
        if ($this->violatesDependencyRule($code)) {
            $violations[] = [
                'type' => 'constitution',
                'rule' => 'dependency_rule',
                'severity' => 'critical',
                'message' => 'Code violates dependency rule'
            ];
        }
        
        // Check modularity
        if ($this->violatesModularity($code)) {
            $violations[] = [
                'type' => 'constitution',
                'rule' => 'modularity',
                'severity' => 'medium',
                'message' => 'Code violates modularity principle'
            ];
        }
        
        return $violations;
    }
}
```



---

# 10. Pre-Commit Hook


## Git Hook


```bash
#!/bin/bash

# Run architecture guard before commit

echo "Running Architecture Guard..."

# Run PHPStan
vendor/bin/phpstan analyse src --level=8
if [ $? -ne 0 ]; then
    echo "Architecture violations found. Commit rejected."
    exit 1
fi

# Run Deptrac
vendor/bin/deptrac analyse --config-file=deptrac.yaml
if [ $? -ne 0 ]; then
    echo "Dependency violations found. Commit rejected."
    exit 1
fi

# Run architecture tests
vendor/bin/phpunit tests/Architecture
if [ $? -ne 0 ]; then
    echo "Architecture test failures found. Commit rejected."
    exit 1
fi

echo "Architecture guard passed. Proceeding with commit."
exit 0
```



---

# 11. Architecture Review Process


## Manual Review


When automated guard fails:


```

Developer

↓

Fix Code

↓

Re-run Guard

↓

If Pass → Merge

If Fail → ARB Review

↓

ARB Decision

├── Exception Granted → Merge

└── Exception Rejected → Fix Required

```



---

# 12. Best Practices


## Rule Definition


* Rules should be clear and specific
* Rules should be enforceable automatically
* Rules should have justification
* Rules should be documented


## Violation Handling


* Always provide clear error message
* Always suggest fix
* Always log violation
* Always track violation trend


## Continuous Improvement


* Review rules quarterly
* Update rules based on feedback
* Add new rules as needed
* Remove obsolete rules



---

# 13. Conclusion


EBP Architecture Guard memungkinkan:


```

CODE

+

ARCHITECTURE GUARD

+

CI/CD

=

CONSISTENT ARCHITECTURE

```


Manfaat:


* Enforce architecture rules automatically
* Prevent technical debt accumulation
* Maintain code quality
* Ensure consistency across team
* Professional software development


EBP Architecture Guard adalah kunci untuk menjaga konsistensi arsitektur dalam jangka panjang.



---

# END OF DOCUMENT


Document ID:

EBP-ARCHITECTURE-GUARD-001


Version:

1.0
