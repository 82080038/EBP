# Enterprise Business Platform (EBP)

# Platform Migration Plan Document


**Document ID:** EBP-PLATFORM-MIGRATION-001

**Version:** 1.0

**Purpose:** Define the migration strategy from current structure to platform-based organization



---

# 1. Migration Objective


Transform EBP from:


```
Single Project with Documentation

```


To:


```

Software Company Platform with Multiple Products

```


Goal:


- Separate core platform from product-specific code
- Enable asset reuse across products
- Establish clear dependency rules
- Create scalable organization structure
- Enable multi-product development


---

# 2. Current State Analysis


## Current Structure


```
ENTERPRISE_BUSINESS_PLATFORM/

├── 00_EBP_MANIFESTO/
│   ├── EBP_CONSTITUTION.md
│   ├── EBP_VISION_MISSION.md
│   ├── EBP_PHILOSOPHY.md
│   └── EBP_CORE_PRINCIPLES.md
│
├── 01_ENTERPRISE_ARCHITECTURE/
│   └── EBP_ENTERPRISE_ARCHITECTURE.md
│
├── 02_BUSINESS_FOUNDATION/
│   ├── EBP_BUSINESS_ONTOLOGY.md
│   └── EBP_MASTER_DATA_MODEL.md
│
├── 03_TECHNICAL_STANDARD/
│   ├── EBP_DATABASE_STANDARD.md
│   └── EBP_CORE_FRAMEWORK.md
│
├── 04_BUSINESS_ENGINE/
│   └── EBP_ENGINE_ARCHITECTURE.md
│
├── 05_SECURITY_ARCHITECTURE/
│   └── EBP_SECURITY_ARCHITECTURE.md
│
├── 06_DEVOPS_ARCHITECTURE/
│   └── EBP_DEVOPS_ARCHITECTURE.md
│
├── 07_PRODUCT_MANAGEMENT/
│   └── EBP_PRODUCT_DEVELOPMENT_LIFECYCLE.md
│
├── 08_PRODUCT_BLUEPRINT/
│   ├── EBP_PRODUCT_RESTAURANT_CAFE_ERP.md
│   ├── EBP_RESTAURANT_CAFE_BUSINESS_PROCESS.md
│   └── EBP_RESTAURANT_CAFE_MODULE_SPECIFICATION.md
│
├── 09_DATABASE_DESIGN/
│   ├── EBP_RESTAURANT_CAFE_DATABASE_DESIGN.md
│   ├── EBP_RESTAURANT_CAFE_ERD.md
│   └── EBP_RESTAURANT_CAFE_MYSQL_SCHEMA.sql
│
├── 10_API_DESIGN/
│   └── EBP_RESTAURANT_CAFE_API_SPECIFICATION.md
│
├── 11_APPLICATION_ARCHITECTURE/
│   ├── EBP_RESTAURANT_CAFE_BACKEND_ARCHITECTURE.md
│   └── EBP_RESTAURANT_CAFE_FRONTEND_ARCHITECTURE.md
│
└── ebp-restaurant-backend/
    ├── config/
    ├── core/
    ├── modules/
    ├── routes/
    └── public/
```


## Issues Identified


1. **No clear separation** between core platform and product-specific code
2. **Mixed documentation** - core and product docs in same structure
3. **No dependency management** - products cannot declare core dependencies
4. **No versioning strategy** - core and products not versioned separately
5. **No repository strategy** - single monolithic structure
6. **Backend code mixed** - core and restaurant-specific code together


---

# 3. Target State Structure


## Final Directory Structure


```
EBP_PLATFORM/

│
├── 00_CONSTITUTION/
│
│   ├── EBP_CONSTITUTION.md
│   ├── EBP_VISION_MISSION.md
│   ├── EBP_PHILOSOPHY.md
│   └── EBP_CORE_PRINCIPLES.md
│
│
├── 01_ARCHITECTURE/
│
│   ├── EBP_ENTERPRISE_ARCHITECTURE.md
│   ├── EBP_SECURITY_ARCHITECTURE.md
│   └── EBP_DEVOPS_ARCHITECTURE.md
│
│
├── 02_FOUNDATION/
│
│   ├── EBP_BUSINESS_ONTOLOGY.md
│   └── EBP_MASTER_DATA_MODEL.md
│
│
├── 03_TECHNICAL_STANDARD/
│
│   ├── EBP_DATABASE_STANDARD.md
│   └── EBP_CORE_FRAMEWORK.md
│
│
├── 04_ENGINE/
│
│   └── EBP_ENGINE_ARCHITECTURE.md
│
│
├── 05_PRODUCT_MANAGEMENT/
│
│   └── EBP_PRODUCT_DEVELOPMENT_LIFECYCLE.md
│
│
├── 06_CORE_CODE/
│
│   ├── Authentication/
│   │   ├── JWT.php
│   │   ├── AuthMiddleware.php
│   │   └── AuthController.php
│   │
│   ├── Permission/
│   │   ├── PermissionMiddleware.php
│   │   └── PermissionService.php
│   │
│   ├── Tenant/
│   │   ├── TenantMiddleware.php
│   │   └── TenantService.php
│   │
│   ├── Audit/
│   │   ├── Audit.php
│   │   └── AuditService.php
│   │
│   ├── Database/
│   │   ├── Database.php
│   │   ├── Transaction.php
│   │   └── ConnectionPool.php
│   │
│   ├── API/
│   │   ├── Router.php
│   │   ├── Response.php
│   │   └── Request.php
│   │
│   ├── Logging/
│   │   ├── Logger.php
│   │   └── LogService.php
│   │
│   └── File/
│       ├── FileManager.php
│       └── StorageService.php
│
│
├── 07_SHARED_ENGINES/
│
│   ├── Pricing Engine/
│   │   ├── PricingService.php
│   │   ├── DiscountCalculator.php
│   │   └── PromotionEngine.php
│   │
│   ├── Inventory Engine/
│   │   ├── StockService.php
│   │   ├── StockDeduction.php
│   │   └── ReorderCalculator.php
│   │
│   ├── Accounting Engine/
│   │   ├── JournalService.php
│   │   ├── LedgerService.php
│   │   └── BalanceCalculator.php
│   │
│   ├── Workflow Engine/
│   │   ├── WorkflowService.php
│   │   ├── ApprovalEngine.php
│   │   └── StateMachine.php
│   │
│   ├── Notification Engine/
│   │   ├── NotificationService.php
│   │   ├── EmailService.php
│   │   └── SMSService.php
│   │
│   ├── Forecast Engine/
│   │   ├── ForecastService.php
│   │   ├── SalesPredictor.php
│   │   └── DemandCalculator.php
│   │
│   └── AI Engine/
│       ├── AIService.php
│       ├── FraudDetection.php
│       └── RecommendationEngine.php
│
│
├── 08_DATABASE/
│
│   ├── ebp_core_schema.sql
│   │
│   │   ├── tenants
│   │   ├── companies
│   │   ├── branches
│   │   ├── users
│   │   ├── roles
│   │   ├── permissions
│   │   ├── user_roles
│   │   ├── role_permissions
│   │   ├── audit_logs
│   │   └── notifications
│   │
│   └── README.md
│
│
├── 09_DEVOPS/
│
│   ├── docker/
│   │   ├── Dockerfile
│   │   └── docker-compose.yml
│   │
│   ├── kubernetes/
│   │   ├── deployment.yml
│   │   └── service.yml
│   │
│   ├── ci-cd/
│   │   ├── github-actions.yml
│   │   └── jenkinsfile
│   │
│   └── monitoring/
│       ├── prometheus.yml
│       └── grafana-dashboard.json
│
│
├── 10_DOCUMENTATION/
│
│   ├── API_Standard.md
│   ├── Coding_Standard.md
│   ├── Testing_Standard.md
│   └── Deployment_Standard.md
│
│
└── PRODUCTS/


    │
    │
    ├── RESTAURANT_ERP/
    │
    │   ├── DOCUMENTATION/
    │   │
    │   │   ├── EBP_PRODUCT_RESTAURANT_CAFE_ERP.md
    │   │   ├── EBP_RESTAURANT_CAFE_BUSINESS_PROCESS.md
    │   │   ├── EBP_RESTAURANT_CAFE_MODULE_SPECIFICATION.md
    │   │   ├── EBP_RESTAURANT_CAFE_DATABASE_DESIGN.md
    │   │   ├── EBP_RESTAURANT_CAFE_ERD.md
    │   │   ├── EBP_RESTAURANT_CAFE_API_SPECIFICATION.md
    │   │   ├── EBP_RESTAURANT_CAFE_BACKEND_ARCHITECTURE.md
    │   │   └── EBP_RESTAURANT_CAFE_FRONTEND_ARCHITECTURE.md
    │   │
    │   ├── DATABASE/
    │   │
    │   │   ├── ebp_restaurant_schema.sql
    │   │   │
    │   │   │   ├── customers
    │   │   │   ├── menu_categories
    │   │   │   ├── menus
    │   │   │   ├── recipes
    │   │   │   ├── restaurant_tables
    │   │   │   ├── orders
    │   │   │   ├── order_details
    │   │   │   ├── kitchen_orders
    │   │   │   ├── stock_balances
    │   │   │   └── other restaurant tables
    │   │   │
    │   │   └── README.md
    │   │
    │   ├── BACKEND/
    │   │
    │   │   ├── config/
    │   │   │   └── database.php
    │   │   │
    │   │   ├── modules/
    │   │   │
    │   │   │   ├── Sales/
    │   │   │   │   ├── Controllers/
    │   │   │   │   ├── Services/
    │   │   │   │   ├── Repositories/
    │   │   │   │   └── Models/
    │   │   │   │
    │   │   │   ├── Menu/
    │   │   │   │   ├── Controllers/
    │   │   │   │   ├── Services/
    │   │   │   │   ├── Repositories/
    │   │   │   │   └── Models/
    │   │   │   │
    │   │   │   ├── Kitchen/
    │   │   │   │   ├── Controllers/
    │   │   │   │   ├── Services/
    │   │   │   │   ├── Repositories/
    │   │   │   │   └── Models/
    │   │   │   │
    │   │   │   └── Inventory/
    │   │   │       ├── Controllers/
    │   │   │       ├── Services/
    │   │   │       ├── Repositories/
    │   │   │       └── Models/
    │   │   │
    │   │   ├── routes/
    │   │   │   └── api.php
    │   │   │
    │   │   └── public/
    │   │       └── index.php
    │   │
    │   ├── FRONTEND/
    │   │
    │   │   ├── assets/
    │   │   │   ├── css/
    │   │   │   ├── js/
    │   │   │   └── images/
    │   │   │
    │   │   ├── components/
    │   │   │   ├── Button.php
    │   │   │   ├── Table.php
    │   │   │   └── Form.php
    │   │   │
    │   │   ├── pages/
    │   │   │   ├── dashboard.php
    │   │   │   ├── pos.php
    │   │   │   ├── kitchen.php
    │   │   │   └── inventory.php
    │   │   │
    │   │   └── modules/
    │   │       ├── pos/
    │   │       ├── kitchen/
    │   │       └── inventory/
    │   │
    │   └── DEPLOYMENT/
    │
    │       ├── docker/
    │       ├── kubernetes/
    │       └── ci-cd/
    │
    │
    ├── HOTEL_ERP/
    │
    │   ├── DOCUMENTATION/
    │   ├── DATABASE/
    │   ├── BACKEND/
    │   ├── FRONTEND/
    │   └── DEPLOYMENT/
    │
    │
    ├── PARKING_SYSTEM/
    │
    │   ├── DOCUMENTATION/
    │   ├── DATABASE/
    │   ├── BACKEND/
    │   ├── FRONTEND/
    │   └── DEPLOYMENT/
    │
    │
    ├── FARMING_ERP/
    │
    │   ├── DOCUMENTATION/
    │   ├── DATABASE/
    │   ├── BACKEND/
    │   ├── FRONTEND/
    │   └── DEPLOYMENT/
    │
    │
    └── LEGAL_SYSTEM/
    │
        ├── DOCUMENTATION/
        ├── DATABASE/
        ├── BACKEND/
        ├── FRONTEND/
        └── DEPLOYMENT/

```


---

# 4. Core vs Product Classification


## Core Platform Components


Located in: `EBP_PLATFORM/00-09/`


### Authentication


```
JWT.php
AuthMiddleware.php
AuthController.php
LoginService.php
TokenService.php
```


Purpose:


- User authentication
- Token generation/validation
- Session management
- Password hashing


Used by:


- All products


---

### Permission (RBAC)


```
PermissionMiddleware.php
PermissionService.php
RoleService.php
PermissionChecker.php
```


Purpose:


- Role-based access control
- Permission validation
- Role management
- Permission assignment


Used by:


- All products


---

### Tenant Management


```
TenantMiddleware.php
TenantService.php
TenantIsolation.php
TenantContext.php
```


Purpose:


- Multi-tenant isolation
- Tenant context management
- Data separation
- Tenant configuration


Used by:


- All products


---

### Audit Trail


```
Audit.php
AuditService.php
AuditLogger.php
AuditQuery.php
```


Purpose:


- Activity logging
- Change tracking
- Compliance reporting
- Security auditing


Used by:


- All products


---

### Database Management


```
Database.php
Transaction.php
ConnectionPool.php
QueryBuilder.php
```


Purpose:


- Database connection
- Transaction management
- Query building
- Connection pooling


Used by:


- All products


---

### API Framework


```
Router.php
Response.php
Request.php
Middleware.php
```


Purpose:


- HTTP routing
- Response formatting
- Request handling
- Middleware pipeline


Used by:


- All products


---

### Logging


```
Logger.php
LogService.php
LogFile.php
LogFormatter.php
```


Purpose:


- Application logging
- Error logging
- Debug logging
- Log rotation


Used by:


- All products


---

### File Management


```
FileManager.php
StorageService.php
FileUpload.php
FileValidator.php
```


Purpose:


- File upload/download
- Storage management
- File validation
- File organization


Used by:


- All products


---

### Pricing Engine


```
PricingService.php
DiscountCalculator.php
PromotionEngine.php
TaxCalculator.php
```


Purpose:


- Price calculation
- Discount application
- Promotion management
- Tax calculation


Used by:


- Restaurant ERP
- Hotel ERP
- Retail products


---

### Inventory Engine


```
StockService.php
StockDeduction.php
ReorderCalculator.php
StockMovement.php
```


Purpose:


- Stock management
- Stock calculation
- Reorder logic
- Stock movement tracking


Used by:


- Restaurant ERP
- Hotel ERP
- Retail ERP
- Manufacturing ERP


---

### Accounting Engine


```
JournalService.php
LedgerService.php
BalanceCalculator.php
FinancialReport.php
```


Purpose:


- Journal entry creation
- Ledger management
- Balance calculation
- Financial reporting


Used by:


- All products with financial features


---

### Workflow Engine


```
WorkflowService.php
ApprovalEngine.php
StateMachine.php
WorkflowDefinition.php
```


Purpose:


- Workflow management
- Approval processes
- State transitions
- Workflow definitions


Used by:


- All products with approval workflows


---

### Notification Engine


```
NotificationService.php
EmailService.php
SMSService.php
PushNotification.php
```


Purpose:


- Notification delivery
- Email sending
- SMS sending
- Push notifications


Used by:


- All products


---

### Forecast Engine


```
ForecastService.php
SalesPredictor.php
DemandCalculator.php
TrendAnalyzer.php
```


Purpose:


- Sales forecasting
- Demand prediction
- Trend analysis
- Capacity planning


Used by:


- Restaurant ERP
- Hotel ERP
- Retail ERP


---

### AI Engine


```
AIService.php
FraudDetection.php
RecommendationEngine.php
PatternRecognition.php
```


Purpose:


- AI-powered features
- Fraud detection
- Recommendations
- Pattern recognition


Used by:


- All products requiring AI


---

## Product-Specific Components


Located in: `EBP_PLATFORM/PRODUCTS/{PRODUCT_NAME}/`


### Restaurant ERP Specific


```
Menu Management
Recipe Management
Kitchen Display System
Table Management
POS Interface
Food Cost Calculation
Restaurant Inventory
Order Management
Payment Processing
```


Purpose:


- Restaurant-specific business logic
- Industry-specific workflows
- Restaurant UI components


Used by:


- Only Restaurant ERP


---

### Hotel ERP Specific


```
Room Management
Reservation System
Check-in/Check-out
Housekeeping
Room Service
Guest Management
```


Purpose:


- Hotel-specific business logic
- Industry-specific workflows
- Hotel UI components


Used by:


- Only Hotel ERP


---

### Parking System Specific


```
Slot Management
Vehicle Entry/Exit
Payment Calculation
Parking Duration
Rate Management
```


Purpose:


- Parking-specific business logic
- Industry-specific workflows
- Parking UI components


Used by:


- Only Parking System


---

# 5. Dependency Rules


## Core Platform Rules


### Rule 1: No Product Knowledge


Core components MUST NOT:


- Know about specific products
- Reference product-specific tables
- Contain industry-specific logic
- Depend on product code


Example:


❌ **WRONG:**
```php
class InventoryEngine {
    public function calculateFoodCost() {
        // Food cost is restaurant-specific
    }
}
```


✅ **CORRECT:**
```php
class InventoryEngine {
    public function calculateCost($itemType, $recipe) {
        // Generic cost calculation
    }
}
```


---

### Rule 2: Generic Interface


Core components MUST:


- Accept generic parameters
- Return generic results
- Use industry-agnostic terminology
- Provide extensible interfaces


Example:


❌ **WRONG:**
```php
class StockService {
    public function deductFoodIngredient($menuId) {
        // Menu is restaurant-specific
    }
}
```


✅ **CORRECT:**
```php
class StockService {
    public function deductStock($itemId, $quantity, $reason) {
        // Generic stock deduction
    }
}
```


---

## Product Rules


### Rule 1: Use Core


Product components MUST:


- Use core authentication
- Use core permission system
- Use core database layer
- Use core API framework
- Use core engines where applicable


Example:


```php
class OrderService {
    private $authMiddleware;
    private $permissionMiddleware;
    private $stockEngine;
    private $accountingEngine;
    
    public function __construct() {
        $this->authMiddleware = new AuthMiddleware();
        $this->permissionMiddleware = new PermissionMiddleware();
        $this->stockEngine = new StockEngine();
        $this->accountingEngine = new AccountingEngine();
    }
}
```


---

### Rule 2: Extend Core


Product components CAN:


- Extend core classes
- Override core methods
- Add product-specific logic
- Implement product-specific interfaces


Example:


```php
class RestaurantStockService extends StockService {
    public function deductFromRecipe($orderId) {
        // Restaurant-specific recipe deduction
        $recipeItems = $this->getRecipeItems($orderId);
        foreach ($recipeItems as $item) {
            parent::deductStock($item['item_id'], $item['quantity'], 'SALE_USAGE');
        }
    }
}
```


---

### Rule 3: No Core Modification


Product components MUST NOT:


- Modify core code directly
- Change core database schema
- Alter core API contracts
- Break core dependencies


Example:


❌ **WRONG:**
```php
// In product code
class AuthMiddleware {
    public function authenticate() {
        // Modified core authentication
    }
}
```


✅ **CORRECT:**
```php
// In product code
class RestaurantAuthMiddleware extends AuthMiddleware {
    public function authenticate() {
        // Extend with restaurant-specific logic
        parent::authenticate();
        $this->checkRestaurantAccess();
    }
}
```


---

# 6. Database Migration Strategy


## Core Database


Database Name: `ebp_core`


Tables:


```
tenants
companies
branches
users
roles
permissions
user_roles
role_permissions
audit_logs
notifications
security_events
approval_logs
```


Purpose:


- Multi-tenant foundation
- User management
- Role-based access control
- Audit trail
- Security logging


Used by:


- All products


---

## Product Databases


### Restaurant ERP


Database Name: `ebp_restaurant`


Tables:


```
customers
customer_memberships
suppliers
menu_categories
menus
menu_prices
recipes
recipe_details
inventory_categories
inventory_items
units
restaurant_tables
orders
order_details
payments
invoices
kitchen_orders
kitchen_order_details
stock_balances
stock_transactions
stock_opnames
stock_opname_details
stock_transfers
stock_transfer_details
purchase_requests
purchase_request_details
purchase_orders
purchase_order_details
goods_receipts
goods_receipt_details
accounts
journal_entries
journal_details
expenses
ai_sales_daily
ai_menu_analysis
ai_forecast_sales
ai_fraud_detection
ai_stock_prediction
```


Purpose:


- Restaurant-specific data
- Menu management
- Order processing
- Kitchen operations
- Restaurant inventory
- Restaurant accounting


Used by:


- Only Restaurant ERP


---

### Hotel ERP


Database Name: `ebp_hotel`


Tables:


```
guests
rooms
room_types
reservations
check_ins
check_outs
housekeeping
room_service
hotel_inventory
hotel_accounting
```


Purpose:


- Hotel-specific data
- Room management
- Reservation system
- Hotel operations


Used by:


- Only Hotel ERP


---

## Database Connection Strategy


Each product connects to:


1. Core database (for authentication, permissions, audit)
2. Product database (for product-specific data)


Example:


```php
class Database {
    public function connectCore() {
        // Connect to ebp_core
    }
    
    public function connectProduct($productName) {
        // Connect to product-specific database
    }
}
```


---

# 7. Repository Strategy


## Git Organization


Organization: `EBP-PLATFORM`


### Core Repositories


```
ebp-constitution
ebp-architecture
ebp-foundation
ebp-technical-standard
ebp-engine
ebp-product-management
ebp-core-code
ebp-shared-engines
ebp-core-database
ebp-devops
```


### Product Repositories


```
ebp-restaurant-erp
ebp-hotel-erp
ebp-parking-system
ebp-farming-erp
ebp-legal-system
```


---

## Repository Structure


### Core Repository Example


```
ebp-core-code/
├── src/
│   ├── Authentication/
│   ├── Permission/
│   ├── Tenant/
│   ├── Audit/
│   ├── Database/
│   ├── API/
│   ├── Logging/
│   └── File/
├── tests/
├── composer.json
├── README.md
└── LICENSE
```


### Product Repository Example


```
ebp-restaurant-erp/
├── DOCUMENTATION/
├── DATABASE/
├── BACKEND/
├── FRONTEND/
├── DEPLOYMENT/
├── tests/
├── composer.json
├── README.md
└── LICENSE
```


---

## Dependency Management


### Core Repository composer.json


```json
{
  "name": "ebp/core-code",
  "description": "EBP Core Framework",
  "type": "library",
  "require": {
    "php": ">=8.0",
    "ext-pdo": "*",
    "ext-json": "*"
  },
  "autoload": {
    "psr-4": {
      "EBP\\Core\\": "src/"
    }
  }
}
```


### Product Repository composer.json


```json
{
  "name": "ebp/restaurant-erp",
  "description": "EBP Restaurant ERP",
  "type": "project",
  "require": {
    "php": ">=8.0",
    "ebp/core-code": "^1.0",
    "ebp/shared-engines": "^1.0",
    "ebp/core-database": "^1.0"
  },
  "autoload": {
    "psr-4": {
      "EBP\\Restaurant\\": "BACKEND/"
    }
  }
}
```


---

# 8. Coding Convention


## Namespace Convention


### Core Code


```
EBP\Core\Authentication
EBP\Core\Permission
EBP\Core\Tenant
EBP\Core\Audit
EBP\Core\Database
EBP\Core\API
EBP\Core\Logging
EBP\Core\File
```


### Shared Engines


```
EBP\Engine\Pricing
EBP\Engine\Inventory
EBP\Engine\Accounting
EBP\Engine\Workflow
EBP\Engine\Notification
EBP\Engine\Forecast
EBP\Engine\AI
```


### Product Code


```
EBP\Restaurant\Sales
EBP\Restaurant\Menu
EBP\Restaurant\Kitchen
EBP\Restaurant\Inventory
EBP\Hotel\Room
EBP\Hotel\Reservation
EBP\Parking\Slot
EBP\Parking\Vehicle
```


---

## Class Naming Convention


### Core Classes


```
AuthMiddleware
PermissionService
TenantContext
AuditLogger
DatabaseManager
Router
Response
Logger
FileManager
```


### Engine Classes


```
PricingService
StockService
JournalService
WorkflowService
NotificationService
ForecastService
AIService
```


### Product Classes


```
OrderService
MenuService
KitchenService
RoomService
ReservationService
SlotService
VehicleService
```


---

## Database Naming Convention


### Core Tables


```
tenants
companies
branches
users
roles
permissions
user_roles
role_permissions
audit_logs
notifications
security_events
```


### Product Tables


```
restaurant_menus
restaurant_orders
hotel_rooms
hotel_reservations
parking_slots
parking_vehicles
```


Prefix with product name to avoid conflicts.


---

## API Naming Convention


### Core APIs


```
/api/v1/auth/login
/api/v1/auth/logout
/api/v1/users
/api/v1/roles
/api/v1/permissions
```


### Product APIs


```
/api/v1/restaurant/orders
/api/v1/restaurant/menu
/api/v1/restaurant/kitchen
/api/v1/hotel/rooms
/api/v1/hotel/reservations
/api/v1/parking/slots
/api/v1/parking/vehicles
```


Prefix with product name to avoid conflicts.


---

## Git Commit Convention


### Format


```
[type]: subject

body

footer
```


### Types


- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation
- `style`: Code style
- `refactor`: Code refactoring
- `test`: Testing
- `chore`: Maintenance


### Examples


```
feat(core): add JWT authentication

feat(restaurant): add POS order creation

fix(core): resolve tenant isolation issue

docs(core): update API documentation

refactor(core): improve database connection pooling
```


---

# 9. Development Roadmap


## Phase 1: EBP Foundation (3 months)


### Objective


Build core platform foundation


### Tasks


1. **Authentication Module**
   - JWT implementation
   - Login/logout
   - Token refresh
   - Password hashing

2. **Permission Module**
   - RBAC implementation
   - Permission checking
   - Role management
   - Permission assignment

3. **Tenant Module**
   - Tenant isolation
   - Tenant context
   - Data separation
   - Tenant configuration

4. **Audit Module**
   - Activity logging
   - Change tracking
   - Audit query
   - Compliance reporting

5. **Database Layer**
   - Connection management
   - Transaction support
   - Query builder
   - Connection pooling

6. **API Framework**
   - Router
   - Response handler
   - Request handler
   - Middleware pipeline

7. **Logging System**
   - Logger implementation
   - Log levels
   - Log rotation
   - Log formatting

8. **File Management**
   - File upload
   - File download
   - Storage service
   - File validation


### Deliverables


- Core framework v1.0
- Core database schema
- API documentation
- Testing suite


---

## Phase 2: Restaurant MVP (2 months)


### Objective


Build minimum viable restaurant ERP


### Tasks


1. **Menu Management**
   - Menu CRUD
   - Category management
   - Price management
   - Recipe management

2. **POS System**
   - Order creation
   - Order modification
   - Payment processing
   - Receipt generation

3. **Kitchen Display**
   - Kitchen queue
   - Order status
   - Priority management
   - Completion tracking

4. **Basic Inventory**
   - Stock view
   - Stock movement
   - Low stock alert
   - Supplier management

5. **Basic Reporting**
   - Sales report
   - Item report
   - Daily summary
   - Revenue tracking


### Deliverables


- Restaurant ERP v1.0
- Restaurant database schema
- POS interface
- Kitchen display
- Basic reports


---

## Phase 3: Restaurant Enterprise (3 months)


### Objective


Add enterprise features to restaurant ERP


### Tasks


1. **Advanced Inventory**
   - Stock opname
   - Stock transfer
   - Purchase order
   - Goods receipt

2. **Accounting Integration**
   - Journal entries
   - Ledger management
   - Financial reports
   - Tax calculation

3. **AI Features**
   - Sales forecast
   - Menu recommendation
   - Fraud detection
   - Stock prediction

4. **Multi-Outlet**
   - Branch management
   - Centralized inventory
   - Consolidated reporting
   - Inter-branch transfer

5. **Advanced Reporting**
   - Profit & loss
   - Cash flow
   - Food cost analysis
   - Performance metrics


### Deliverables


- Restaurant ERP v2.0
- Advanced inventory
- Accounting integration
- AI features
- Multi-outlet support


---

## Phase 4: Second Product (4 months)


### Objective


Build second product on EBP platform


### Options


- Hotel ERP
- Parking System
- Farming ERP
- Legal System


### Tasks


1. **Product Analysis**
   - Business process
   - Module specification
   - Database design
   - API specification

2. **Product Development**
   - Backend development
   - Frontend development
   - Integration with core
   - Testing

3. **Product Launch**
   - Deployment
   - Documentation
   - Training
   - Support


### Deliverables


- Second product v1.0
- Product documentation
- Integration with core
- Launch ready


---

## Phase 5: Platform Enhancement (Ongoing)


### Objective


Continuously improve core platform


### Tasks


1. **Performance Optimization**
   - Caching
   - Query optimization
   - Load balancing
   - CDN integration

2. **Security Enhancement**
   - Advanced authentication
   - Encryption
   - Security monitoring
   - Compliance

3. **Feature Addition**
   - New engines
   - New integrations
   - New capabilities
   - Platform extensions

4. **Developer Experience**
   - Better documentation
   - Developer tools
   - Testing frameworks
   - Deployment automation


### Deliverables


- Core platform v2.0+
- Continuous improvement
- Better developer experience
- Enhanced capabilities


---

# 10. Migration Execution Plan


## Phase 1: Documentation Reorganization (1 week)


### Tasks


1. Create new folder structure
2. Move core documents to appropriate folders
3. Move product documents to PRODUCTS/RESTAURANT_ERP/
4. Update internal references
5. Create README files for each folder


### Deliverables


- Reorganized documentation structure
- Updated document references
- Folder README files


---

## Phase 2: Code Separation (2 weeks)


### Tasks


1. Extract core code from ebp-restaurant-backend
2. Move to EBP_PLATFORM/06_CORE_CODE/
3. Remove product-specific logic from core
4. Create proper interfaces
5. Add dependency injection


### Deliverables


- Separated core code
- Core interfaces
- Dependency injection setup


---

## Phase 3: Database Separation (1 week)


### Tasks


1. Split schema into core and restaurant
2. Create ebp_core database
3. Create ebp_restaurant database
4. Update connection logic
5. Migrate existing data


### Deliverables


- Core database schema
- Restaurant database schema
- Updated connection logic
- Data migration


---

## Phase 4: Repository Setup (1 week)


### Tasks


1. Create Git organization
2. Create core repositories
3. Create product repositories
4. Setup composer dependencies
5. Configure CI/CD


### Deliverables


- Git organization
- Core repositories
- Product repositories
- Dependency management
- CI/CD pipeline


---

## Phase 5: Testing & Validation (1 week)


### Tasks


1. Test core framework
2. Test product integration
3. Test database connections
4. Test API endpoints
5. Validate dependencies


### Deliverables


- Test results
- Validation report
- Bug fixes
- Documentation updates


---

# 11. Risk Management


## Risk 1: Breaking Changes


**Description:** Core changes may break products


**Mitigation:**
- Semantic versioning
- Deprecation period
- Migration guides
- Backward compatibility


---

## Risk 2: Dependency Conflicts


**Description:** Products may have conflicting dependencies


**Mitigation:**
- Strict versioning
- Dependency resolution
- Compatibility testing
- Clear upgrade path


---

## Risk 3: Data Migration Issues


**Description:** Database migration may fail


**Mitigation:**
- Backup strategy
- Migration scripts
- Rollback plan
- Data validation


---

## Risk 4: Team Adoption


**Description:** Team may resist new structure


**Mitigation:**
- Training
- Documentation
- Support
- Gradual transition


---

# 12. Success Criteria


## Technical Success


- ✅ Clear separation between core and product
- ✅ Products can use core independently
- ✅ Core changes don't break products
- ✅ Proper dependency management
- ✅ Versioned releases


## Organizational Success


- ✅ Team understands structure
- ✅ Development workflow established
- ✅ Repository strategy implemented
- ✅ Documentation complete
- ✅ Training completed


## Business Success


- ✅ Platform enables product development
- ✅ Asset reuse reduces development time
- ✅ Consistent quality across products
- ✅ Scalable organization
- ✅ Long-term sustainability


---

# 13. Conclusion


This migration plan transforms EBP from:


```
Single Project

```

To:


```

Software Company Platform

+

Multiple Products

```


The migration ensures:


- Clear separation of concerns
- Asset reuse across products
- Scalable organization
- Long-term sustainability
- Professional software company structure


EBP is building not just applications.

EBP is building a platform for building applications.


---

# Document End


Document ID:

EBP-PLATFORM-MIGRATION-001


Version:

1.0
