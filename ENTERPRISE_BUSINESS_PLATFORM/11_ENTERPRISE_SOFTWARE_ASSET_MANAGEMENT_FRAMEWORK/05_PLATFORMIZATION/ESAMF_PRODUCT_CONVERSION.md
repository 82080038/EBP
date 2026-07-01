# ESAMF Product Conversion

**Document ID:** ESAMF-PLATFORMIZATION-003

**Version:** 1.0

**Purpose:** Define the product conversion methodology for ESAMF

---

# Overview

Product Conversion is the process of transforming a migrated repository into a fully integrated EBP product. This involves converting the repository structure, integrating with EBP services, and ensuring compliance with EBP product standards.

---

# Product Structure

## EBP Product Structure

```
PRODUCTS/{PRODUCT}/
│
├── DOCUMENTATION/
│   ├── EBP_PRODUCT_{PRODUCT}.md
│   ├── EBP_{PRODUCT}_BUSINESS_PROCESS.md
│   ├── EBP_{PRODUCT}_MODULE_SPECIFICATION.md
│   ├── EBP_{PRODUCT}_DATABASE_DESIGN.md
│   ├── EBP_{PRODUCT}_ERD.md
│   ├── EBP_{PRODUCT}_API_SPECIFICATION.md
│   ├── EBP_{PRODUCT}_BACKEND_ARCHITECTURE.md
│   └── EBP_{PRODUCT}_FRONTEND_ARCHITECTURE.md
│
├── DATABASE/
│   └── EBP_{PRODUCT}_MYSQL_SCHEMA.sql
│
├── BACKEND/
│   ├── config/
│   ├── core/
│   ├── modules/
│   ├── routes/
│   └── public/
│
├── FRONTEND/
│   ├── assets/
│   ├── components/
│   ├── pages/
│   └── modules/
│
└── DEPLOYMENT/
    ├── docker/
    ├── kubernetes/
    └── scripts/
```

---

# Conversion Process

## Phase 1: Structure Conversion

### Step 1: Create Product Directory

```bash
mkdir -p /path/to/EBP/PRODUCTS/{PRODUCT}/
mkdir -p /path/to/EBP/PRODUCTS/{PRODUCT}/DOCUMENTATION/
mkdir -p /path/to/EBP/PRODUCTS/{PRODUCT}/DATABASE/
mkdir -p /path/to/EBP/PRODUCTS/{PRODUCT}/BACKEND/
mkdir -p /path/to/EBP/PRODUCTS/{PRODUCT}/FRONTEND/
mkdir -p /path/to/EBP/PRODUCTS/{PRODUCT}/DEPLOYMENT/
```

### Step 2: Convert Repository Structure

**Before (Repository Structure):**
```
restoran/
├── config/
├── core/
├── modules/
│   ├── Auth/
│   ├── Sales/
│   ├── Kitchen/
│   └── Menu/
├── public/
└── database/
```

**After (EBP Product Structure):**
```
PRODUCTS/RESTAURANT_ERP/
├── DOCUMENTATION/
├── DATABASE/
├── BACKEND/
│   ├── config/
│   ├── core/
│   ├── modules/
│   │   ├── Auth/ (Will use EBP Core)
│   │   ├── Sales/
│   │   ├── Kitchen/
│   │   └── Menu/
│   ├── routes/
│   └── public/
├── FRONTEND/
└── DEPLOYMENT/
```

### Step 3: Remove Extracted Components

```bash
# Remove components extracted to EBP Core
rm -rf BACKEND/modules/Auth/

# Remove components extracted to Shared Engines
rm -rf BACKEND/modules/Notification/
rm -rf BACKEND/modules/Report/
```

---

## Phase 2: Documentation Conversion

### Step 1: Create Product Documentation

```markdown
# EBP Product: Restaurant ERP

**Document ID:** EBP-PRODUCT-RESTAURANT-001

**Version:** 1.0

**Purpose:** Define the Restaurant ERP product

## Overview
Restaurant ERP is a comprehensive restaurant management system...

## Business Domain
Hospitality

## Features
- Point of Sale (POS)
- Kitchen Display System
- Menu Management
- Table Management
- Reservation System
- Inventory Management
- Reporting

## EBP Integration
- EBP Core: Authentication, Authorization, Audit, Configuration
- EBP Shared Engines: Notification, Reporting, Inventory

## Architecture
[Architecture description]
```

### Step 2: Create Business Process Documentation

```markdown
# Restaurant ERP Business Process

## Order Process
1. Customer places order
2. Order is routed to kitchen
3. Kitchen prepares order
4. Order is marked ready
5. Order is served
6. Payment is processed

## Reservation Process
1. Customer makes reservation
2. Reservation is confirmed
3. Table is assigned
4. Customer arrives
5. Reservation is checked in
```

### Step 3: Create Module Specification

```markdown
# Restaurant ERP Module Specification

## POS Module
- Purpose: Process orders and payments
- Components: OrderController, PaymentService
- Dependencies: EBP Core (Auth, Audit), Notification Engine

## Kitchen Display Module
- Purpose: Display orders to kitchen staff
- Components: KitchenDisplayController, RoutingService
- Dependencies: EBP Core (Auth), Notification Engine

## Menu Management Module
- Purpose: Manage menu items and categories
- Components: MenuController, ProductService
- Dependencies: EBP Core (Auth, Audit), Inventory Engine
```

---

## Phase 3: Database Conversion

### Step 1: Convert Database Schema

**Before:**
```sql
CREATE TABLE UserInfo (
    ID INT PRIMARY KEY,
    UserName VARCHAR(255),
    EmailAddress VARCHAR(255)
);
```

**After:**
```sql
CREATE TABLE users (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_users_username (username),
    INDEX idx_users_email (email)
);
```

### Step 2: Add EBP Standard Tables

```sql
-- EBP standard tables
CREATE TABLE audit_logs (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    action VARCHAR(255) NOT NULL,
    entity_type VARCHAR(255) NOT NULL,
    entity_id INT UNSIGNED NOT NULL,
    old_values JSON,
    new_values JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_audit_logs_user_id (user_id),
    INDEX idx_audit_logs_entity (entity_type, entity_id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

### Step 3: Create Migration Script

```php
<?php
// database/migrations/2024_01_01_000000_convert_to_ebp_schema.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ConvertToEBPSchema extends Migration
{
    public function up()
    {
        // Rename tables
        Schema::rename('UserInfo', 'users');
        
        // Rename columns
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('ID', 'id');
            $table->renameColumn('UserName', 'username');
            $table->renameColumn('EmailAddress', 'email');
        });
        
        // Add standard columns
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->onUpdate('CURRENT_TIMESTAMP');
            $table->timestamp('deleted_at')->nullable();
        });
        
        // Add indexes
        Schema::table('users', function (Blueprint $table) {
            $table->index('username', 'idx_users_username');
            $table->index('email', 'idx_users_email');
        });
    }
    
    public function down()
    {
        // Rollback changes
    }
}
```

---

## Phase 4: Backend Conversion

### Step 1: Update Configuration

**Before:**
```php
// config/app.php
return [
    'name' => 'Restaurant System',
    'providers' => [
        // Repository-specific providers
    ],
];
```

**After:**
```php
// config/app.php
return [
    'name' => env('APP_NAME', 'Restaurant ERP'),
    'providers' => [
        // EBP Core providers
        EBP\Core\Providers\AuthServiceProvider::class,
        EBP\Core\Providers\AuditServiceProvider::class,
        EBP\Core\Providers\ConfigServiceProvider::class,
        
        // EBP Shared Engine providers
        EBP\SharedEngines\Notification\NotificationServiceProvider::class,
        EBP\SharedEngines\Reporting\ReportingServiceProvider::class,
        
        // Product-specific providers
        App\Providers\ProductServiceProvider::class,
    ],
];
```

### Step 2: Update Routes

**Before:**
```php
// routes/web.php
Route::get('/login', 'AuthController@login');
Route::post('/login', 'AuthController@doLogin');
Route::get('/orders', 'OrderController@index');
```

**After:**
```php
// routes/web.php
use EBP\Core\Authentication\AuthMiddleware;
use EBP\Core\Authorization\AuthorizeMiddleware;

// EBP Core handles authentication
Route::post('/api/v1/auth/login', 'EBP\Core\Authentication\AuthController@login');

// Product routes with EBP middleware
Route::middleware(['auth', 'authorize'])->group(function () {
    Route::get('/api/v1/orders', 'OrderController@index');
    Route::post('/api/v1/orders', 'OrderController@create');
    Route::get('/api/v1/orders/{id}', 'OrderController@show');
});
```

### Step 3: Update Controllers

**Before:**
```php
class OrderController {
    private $authService;
    
    public function __construct() {
        $this->authService = new AuthService();
    }
    
    public function create() {
        if (!$this->authService->isAuthenticated()) {
            return redirect('/login');
        }
        
        $order = Order::create(request()->all());
        return response()->json($order);
    }
}
```

**After:**
```php
use EBP\Core\Authentication\AuthMiddleware;
use EBP\Core\Authorization\AuthorizeMiddleware;
use EBP\SharedEngines\Notification\NotificationEngineInterface;

class OrderController {
    private $notificationEngine;
    
    public function __construct(NotificationEngineInterface $notificationEngine) {
        $this->notificationEngine = $notificationEngine;
    }
    
    #[AuthMiddleware]
    #[AuthorizeMiddleware('order.create')]
    public function create(Request $request) {
        $order = Order::create($request->validated());
        
        $this->notificationEngine->sendTemplate('order_created', [
            'order_id' => $order->id
        ], $order->customer->email);
        
        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }
}
```

---

## Phase 5: Frontend Conversion

### Step 1: Update Project Structure

**Before:**
```
public/
├── index.html
├── css/
├── js/
└── assets/
```

**After:**
```
FRONTEND/
├── src/
│   ├── components/
│   ├── pages/
│   ├── modules/
│   ├── services/
│   └── utils/
├── public/
└── package.json
```

### Step 2: Integrate EBP Component Library

**Before:**
```jsx
// Custom button component
function Button({ children, onClick }) {
  return (
    <button 
      onClick={onClick}
      style={{
        padding: '10px 20px',
        backgroundColor: '#007bff',
        color: 'white'
      }}
    >
      {children}
    </button>
  );
}
```

**After:**
```jsx
import { Button } from '@ebp/components';

function OrderForm() {
  return (
    <Button variant="primary" onClick={handleSubmit}>
      Create Order
    </Button>
  );
}
```

### Step 3: Integrate EBP API Client

**Before:**
```jsx
function createOrder(data) {
  return fetch('/api/orders', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(data)
  });
}
```

**After:**
```jsx
import { ebpApiClient } from '@ebp/api';

function createOrder(data) {
  return ebpApiClient.post('/v1/orders', data);
}
```

---

## Phase 6: Deployment Conversion

### Step 1: Create Docker Configuration

```dockerfile
# Dockerfile
FROM php:8.2-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy application files
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /var/www

EXPOSE 9000
CMD ["php-fpm"]
```

### Step 2: Create Docker Compose

```yaml
# docker-compose.yml
version: '3.8'

services:
  app:
    build: .
    container_name: restaurant-erp-app
    restart: unless-stopped
    working_dir: /var/www
    volumes:
      - ./:/var/www
    networks:
      - ebp-network

  nginx:
    image: nginx:alpine
    container_name: restaurant-erp-nginx
    restart: unless-stopped
    ports:
      - "8080:80"
    volumes:
      - ./:/var/www
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf
    networks:
      - ebp-network

  mysql:
    image: mysql:8.0
    container_name: restaurant-erp-mysql
    restart: unless-stopped
    environment:
      MYSQL_DATABASE: restaurant_erp
      MYSQL_ROOT_PASSWORD: secret
      MYSQL_USER: ebp
      MYSQL_PASSWORD: secret
    volumes:
      - restaurant_erp_data:/var/lib/mysql
    networks:
      - ebp-network

networks:
  ebp-network:
    driver: bridge

volumes:
  restaurant_erp_data:
```

### Step 3: Create Kubernetes Configuration

```yaml
# k8s/deployment.yaml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: restaurant-erp
  labels:
    app: restaurant-erp
spec:
  replicas: 3
  selector:
    matchLabels:
      app: restaurant-erp
  template:
    metadata:
      labels:
        app: restaurant-erp
    spec:
      containers:
      - name: restaurant-erp
        image: ebp/restaurant-erp:latest
        ports:
        - containerPort: 9000
        env:
        - name: DB_HOST
          value: "mysql-service"
        - name: DB_DATABASE
          value: "restaurant_erp"
```

---

# Conversion Checklist

## Structure Conversion
- [ ] Product directory created
- [ ] Repository structure converted
- [ ] Extracted components removed
- [ ] EBP structure applied

## Documentation Conversion
- [ ] Product documentation created
- [ ] Business process documented
- [ ] Module specification created
- [ ] API specification created

## Database Conversion
- [ ] Database schema converted
- [ ] EBP standard tables added
- [ ] Migration script created
- [ ] Migration tested

## Backend Conversion
- [ ] Configuration updated
- [ ] Routes updated
- [ ] Controllers updated
- [ ] Services updated

## Frontend Conversion
- [ ] Project structure updated
- [ ] EBP component library integrated
- [ ] EBP API client integrated
- [ ] Design system applied

## Deployment Conversion
- [ ] Docker configuration created
- [ ] Docker compose created
- [ ] Kubernetes configuration created
- [ ] Deployment tested

## Integration
- [ ] EBP Core integrated
- [ ] EBP Shared Engines integrated
- [ ] EBP infrastructure integrated
- [ ] Integration tested

---

# Document End

**Document ID:** ESAMF-PLATFORMIZATION-003

**Version:** 1.0
