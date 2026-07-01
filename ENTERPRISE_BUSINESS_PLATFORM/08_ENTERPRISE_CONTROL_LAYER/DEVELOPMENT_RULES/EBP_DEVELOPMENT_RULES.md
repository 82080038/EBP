# Enterprise Business Platform (EBP)

# Development Rules


**Document ID:** EBP-DEVELOPMENT-RULES-001

**Version:** 1.0

**Category:** Development Operation Standard

**Status:** Official Development Rules



---

# 1. Introduction


Dokumen ini mendefinisikan aturan pengembangan untuk Enterprise Business Platform (EBP).

EBP dikembangkan dengan model:


```

Founder / Product Owner

        |

        |

AI Development Assistant

        |

        |

Code + Documentation + Testing

        |

        |

EBP Platform

```


Tujuan:


```

SISTEM KERJA

+

SATU ORANG

+

AI

=

SOFTWARE ENTERPRISE KONSISTEN

```



---

# 2. Development Philosophy


EBP Development menggunakan prinsip:


```

LONG-TERM PLATFORM THINKING

```

Artinya:


* Setiap kode harus reusable
* Setiap kode harus maintainable
* Setiap kode harus scalable
* Setiap kode harus documented
* Setiap kode harus tested


EBP bukan dibangun sebagai aplikasi sekali pakai.

EBP dibangun sebagai platform jangka panjang.



---

# 3. AI Development Workflow


## Prerequisite Sebelum Coding


Sebelum meminta AI membuat kode, wajib tersedia:


```

Business Requirement

↓

Business Process

↓

Database Design

↓

API Specification

↓

Coding

↓

Testing

```


## Tidak Boleh Langsung


Salah:


```

"buatkan fitur X"

```


Benar:


```

Saya sudah memiliki:

- REQUIREMENT.md
- PROCESS.md
- DATABASE.md
- API.md

Buat implementasi coding sesuai spesifikasi tersebut.

```


## AI Code Review Process


Setiap kode yang dihasilkan AI harus melalui:


```

Generate

↓

Review Architecture

↓

Check Security

↓

Run Test

↓

Accept

```



---

# 4. Document First Development


## Aturan Utama


```

TIDAK ADA FITUR TANPA DOKUMEN

```


## Checklist Dokumen


Sebelum membuat fitur baru, wajib ada:


```

[ ] REQUIREMENT.md

[ ] PROCESS.md

[ ] DATABASE.md

[ ] API.md

[ ] TEST.md

```


## Contoh


Sebelum membuat:

```

Restaurant Discount System

```


Harus ada:

```

DISCOUNT_REQUIREMENT.md

DISCOUNT_PROCESS.md

DISCOUNT_DATABASE.md

DISCOUNT_API.md

DISCOUNT_TEST.md

```



---

# 5. Core vs Product Rules


## Aturan Paling Penting


```

CORE

=

ATURAN UMUM SEMUA BISNIS


PRODUCT

=

ATURAN BISNIS TERTENTU

```


## Core Framework


Berisi aturan umum:


```

User

Role

Permission

Audit

Notification

Configuration

Rule Engine

Workflow Engine

```


## Product


Berisi aturan bisnis:


```

Restaurant:

- Menu
- Recipe
- Kitchen
- Table


Hotel:

- Room
- Reservation
- Check-in/Check-out


Parking:

- Slot
- Rate
- Access

```


## Pemisahan


Core tidak boleh mengenal bisnis tertentu.

Product boleh menggunakan Core tapi tidak boleh mengubah Core.



---

# 6. Coding Architecture Rules


## Rule 1: Controller Tidak Boleh Akses Database Langsung


### VIOLATION


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


### CORRECT


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


## Rule 2: Service Tidak Boleh Akses HTTP Layer


### VIOLATION


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


### CORRECT


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


## Rule 3: Repository Tidak Boleh Mengandung Business Logic


### VIOLATION


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


### CORRECT


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


## Rule 4: Model Tidak Boleh Mengandung Validation


### VIOLATION


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


### CORRECT


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


## Rule 5: Core Tidak Boleh Import Product Code


### VIOLATION


```php
// In Core Framework
use EBP\Products\Restaurant\Order;

class CoreService
{
    // VIOLATION: Core importing product
}
```


### CORRECT


```php
// In Core Framework
namespace EBP\Core;

class CoreService
{
    // CORRECT: Core only uses core
}
```



---

# 7. Database Rules


## Standard Columns


Semua tabel wajib memiliki:


```sql
id BIGINT PRIMARY KEY AUTO_INCREMENT

created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

created_by BIGINT

updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP

updated_by BIGINT

deleted_at TIMESTAMP NULL

tenant_id BIGINT NOT NULL

version INT DEFAULT 1

```


## Naming Convention


Database: `snake_case`

Contoh:


```

user_roles

order_items

invoice_details

```


## Indexing


Semua foreign key wajib di-index:

```sql
INDEX idx_tenant_id (tenant_id)

INDEX idx_user_id (user_id)

INDEX idx_created_at (created_at)

```



---

# 8. API Rules


## URL Format


```

/api/v1/products

/api/v1/orders

/api/v1/payments

/api/v1/tenants

```


## HTTP Methods


```

GET: Retrieve data

POST: Create data

PUT: Update data (full)

PATCH: Update data (partial)

DELETE: Delete data

```


## Response Format


### Success


```json
{
  "success": true,
  "message": "Success",
  "data": {}
}
```


### Error


```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Invalid input",
    "details": {}
  }
}
```


## Pagination


```

GET /api/v1/orders?page=1&limit=20

Response:

{
  "success": true,
  "data": {
    "items": [],
    "pagination": {
      "page": 1,
      "limit": 20,
      "total": 100,
      "total_pages": 5
    }
  }
}
```



---

# 9. Git Rules


## Branch Strategy


```

main

    |

development

    |

feature/*

bugfix/*

hotfix/*

```


## Branch Naming


```

feature/restaurant-pos

feature/inventory-engine

feature/kitchen-queue

bugfix/auth-token

hotfix/security-patch

```


## Commit Message


Format:


```

type(scope): subject

body

footer

```


Type:


```

feat: new feature

fix: bug fix

docs: documentation

refactor: refactoring

test: testing

chore: maintenance

```


Example:


```

feat(restaurant): add POS module

- Implement order creation
- Add payment processing
- Integrate with inventory

Closes #123

```



---

# 10. Versioning Rules


## Semantic Versioning


Format: `MAJOR.MINOR.PATCH`


### MAJOR


Breaking changes


* API changes
* Database schema changes
* Architecture changes


### MINOR


New features, backward compatible


* New endpoints
* New modules
* New functionality


### PATCH


Bug fixes, backward compatible


* Bug fixes
* Performance improvements
* Documentation updates


## Database Migration


Format:


```

migration_001_create_users_table.sql

migration_002_add_roles.sql

migration_003_add_permissions.sql

```


## API Versioning


```

/api/v1/orders

/api/v2/orders

```



---

# 11. Testing Rules


## Aturan Utama


```

TIDAK BOLEH SELESAI TANPA TEST

```


## Test Types


```

Unit Test

↓

API Test

↓

Integration Test

↓

E2E Test

```


## Coverage Target


```

Unit Test: > 80%

API Test: 100%

Critical Path: 100%

```


## Test Naming


Format:


```

[MODULE]_[ACTION]_[EXPECTED_RESULT]

```


Example:


```

OrderService_createOrder_success

OrderService_createOrder_invalidData_failed

```



---

# 12. Change Management Sederhana


## Change Request Template


```markdown
# CHANGE_REQUEST.md

## Tanggal
2026-07-01

## Perubahan
Menambahkan fitur discount untuk restaurant

## Alasan
Customer membutuhkan diskon dinamis berdasarkan membership

## Dampak
- Database: Tambah tabel discount_rules
- API: Tambah endpoint /api/v1/discounts
- Backend: Tambah DiscountService

## Testing
- Unit test: DiscountService
- API test: /api/v1/discounts
- Integration test: Order dengan discount

## Status
[ ] Pending
[ ] In Progress
[ ] Completed
```



---

# 13. Naming Convention


## Database


```

snake_case

user_roles

order_items

```


## PHP


```

PascalCase (class)

camelCase (method/variable)

```


Example:


```php
class OrderService
{
    public function createOrder($data)
    {
        $orderId = $this->generateOrderId();
    }
}
```


## JavaScript


```

camelCase

```


Example:


```javascript
const createOrder = (data) => {
    const orderId = generateOrderId();
};
```



---

# 14. Definition of Done


Fitur dianggap selesai jika:


```

[ ] Requirement selesai

[ ] Business process selesai

[ ] Database design selesai

[ ] API specification selesai

[ ] Backend implementation selesai

[ ] Frontend implementation selesai

[ ] Unit test selesai

[ ] API test selesai

[ ] Integration test selesai

[ ] Dokumentasi selesai

[ ] Code review selesai

[ ] Merge ke development

```



---

# 15. Code Quality Rules


## PSR Standards


PHP code harus mengikuti:


```

PSR-4: Autoloading

PSR-12: Coding Style

```


## Code Comments


Wajib:


```php
/**
 * Create order
 * 
 * @param array $data Order data
 * @return Order Created order
 * @throws ValidationException If data invalid
 */
public function createOrder($data)
{
    // Implementation
}
```


## Magic Numbers


Dilarang:


```php
if ($total > 1000000) // VIOLATION
```


Gunakan constant:


```php
const DISCOUNT_THRESHOLD = 1000000;

if ($total > self::DISCOUNT_THRESHOLD) // CORRECT
```



---

# 16. Security Rules


## Input Validation


Semua input harus divalidasi:

```php
$this->validate($data, [
    'name' => 'required|string|max:255',
    'email' => 'required|email',
    'amount' => 'required|numeric|min:0'
]);
```


## SQL Injection


Selalu gunakan prepared statement:

```php
// CORRECT
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);

// VIOLATION
$stmt = $pdo->query("SELECT * FROM users WHERE id = $userId");
```


## XSS Protection


Output harus di-escape:

```php
// CORRECT
echo htmlspecialchars($userInput, ENT_QUOTES, 'UTF-8');

// VIOLATION
echo $userInput;
```



---

# 17. Performance Rules


## Database Query


Selalu gunakan index:

```sql
-- CORRECT
SELECT * FROM orders WHERE tenant_id = ? AND created_at >= ?

-- VIOLATION (tanpa index)
SELECT * FROM orders WHERE created_at >= ?
```


## N+1 Problem


Hindari N+1 query:

```php
// VIOLATION
$orders = Order::all();
foreach ($orders as $order) {
    $customer = $order->customer; // N+1 query
}

// CORRECT
$orders = Order::with('customer')->get();
```


## Caching


Gunakan caching untuk data yang sering diakses:

```php
// CORRECT
$plans = Cache::remember('subscription_plans', 3600, function() {
    return SubscriptionPlan::all();
});
```



---

# 18. Documentation Rules


## Code Documentation


Setiap class dan method wajib memiliki docblock:

```php
/**
 * Order Service
 * 
 * Handles order creation, modification, and cancellation
 */
class OrderService
{
    /**
     * Create new order
     * 
     * @param array $data Order data
     * @return Order
     * @throws ValidationException
     */
    public function createOrder($data)
    {
        // Implementation
    }
}
```


## API Documentation


Setiap endpoint wajib didokumentasikan:

```markdown
## Create Order

POST /api/v1/orders

### Request Body
{
  "customer_id": 1,
  "items": [...],
  "total": 100000
}

### Response
{
  "success": true,
  "data": {
    "order_id": 100,
    "status": "pending"
  }
}
```



---

# 19. Backup Rules


## Code Backup


Selalu commit ke GitHub sebelum perubahan besar:

```bash
git add .
git commit -m "backup: before major refactoring"
git push
```


## Database Backup


Sebelum migration:

```bash
mysqldump ebp > backup_$(date +%Y%m%d).sql
```



---

# 20. Best Practices Summary


## Sebelum Coding


- Pastikan requirement jelas
- Pastikan business process dipahami
- Pastikan database design siap
- Pastikan API specification siap


## Saat Coding


- Ikuti architecture rules
- Ikuti naming convention
- Tulis documentation
- Tulis test


## Setelah Coding


- Run test
- Code review
- Merge ke development
- Update documentation



---

# 21. Conclusion


EBP Development Rules memastikan:


```

SISTEM KERJA

+

SATU ORANG

+

AI

=

SOFTWARE ENTERPRISE KONSISTEN

```


Manfaat:


* Konsistensi kode
* Kualitas terjamin
* Maintenance mudah
- Scalable
* Professional


EBP Development Rules adalah kunci untuk membangun software enterprise secara konsisten dengan model satu orang + AI.



---

# END OF DOCUMENT


Document ID:

EBP-DEVELOPMENT-RULES-001


Version:

1.0
