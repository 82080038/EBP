# Platform Bisnis Enterprise (EBP)

# Dokumen Arsitektur Database


**ID Dokumen:** EBP-DATABASE-ARCHITECTURE-001

**Versi:** 1.0

**Tujuan:** Definisikan strategi arsitektur database untuk platform dan produk EBP



---

# 1. Filosofi Database


EBP database architecture follows the principle:


```

ONE CORE DATABASE

+

MULTIPLE PRODUCT DATABASES

```


NOT:


```

ONE DATABASE FOR ALL PRODUCTS

```


---

# 2. Why Separate Databases?


## Problem with Single Database Approach


If we use one database for all products:


```
ebp_unified_database

├── restaurant_tables
├── hotel_rooms
├── parking_tickets
├── farming_harvest
├── legal_documents
└── customers
```


Issues:


- Too many tables in single database
- Unclear relationships
- Difficult maintenance
- Changes in one product can break others
- Complex security
- Performance degradation
- No clear ownership


---

## Benefits of Separate Databases


```

EBP CORE DATABASE

+

PRODUCT DATABASES

```


Benefits:


- Clear separation of concerns
- Independent product development
- Easier maintenance
- Better security isolation
- Scalable architecture
- Clear data ownership
- Flexible deployment


---

# 3. Database Architecture Overview


```

                    EBP CORE DATABASE
                    (Shared Foundation)
                         |
                         |
        ---------------------------------
        |               |              |
        ↓               ↓              ↓


 RESTAURANT DB     HOTEL DB      PARKING DB
 (Domain Data)   (Domain Data)  (Domain Data)


```


---

# 4. EBP Core Database


**Database Name:** `ebp_core`


**Tujuan:** Shared foundation used by all products


## Core Database Schema


### Identity Management


```
users
├── user_id (PK)
├── tenant_id (FK)
├── username
├── password_hash
├── email
├── status
├── created_at
└── updated_at


roles
├── role_id (PK)
├── role_name
├── role_code
├── description
├── status
└── created_at


permissions
├── permission_id (PK)
├── permission_code
├── permission_name
├── module
├── description
└── created_at


user_roles
├── user_role_id (PK)
├── user_id (FK)
├── role_id (FK)
├── assigned_at
└── assigned_by


role_permissions
├── role_permission_id (PK)
├── role_id (FK)
├── permission_id (FK)
└── assigned_at
```


---

### Organization Management


```
tenants
├── tenant_id (PK)
├── tenant_name
├── tenant_code
├── business_type
├── status
├── created_at
└── updated_at


companies
├── company_id (PK)
├── tenant_id (FK)
├── company_name
├── company_code
├── tax_id
├── address
├── phone
├── email
├── status
├── created_at
└── updated_at


branches
├── branch_id (PK)
├── tenant_id (FK)
├── company_id (FK)
├── branch_name
├── branch_code
├── address
├── phone
├── email
├── status
├── created_at
└── updated_at


departments
├── department_id (PK)
├── tenant_id (FK)
├── company_id (FK)
├── branch_id (FK)
├── department_name
├── department_code
├── manager_id
├── status
└── created_at


locations
├── location_id (PK)
├── tenant_id (FK)
├── location_name
├── location_type
├── address
├── latitude
├── longitude
├── status
└── created_at
```


---

### Security & Audit


```
audit_logs
├── audit_id (PK)
├── tenant_id (FK)
├── user_id (FK)
├── module
├── action
├── record_id
├── table_name
├── old_value (JSON)
├── new_value (JSON)
├── ip_address
├── user_agent
├── created_at
└── created_by


login_history
├── login_id (PK)
├── user_id (FK)
├── tenant_id (FK)
├── login_time
├── logout_time
├── ip_address
├── user_agent
├── status
└── created_at


security_events
├── event_id (PK)
├── tenant_id (FK)
├── user_id (FK)
├── event_type
├── event_description
├── severity
├── ip_address
├── created_at
└── created_by
```


---

### Workflow Engine


```
workflow_definitions
├── workflow_id (PK)
├── tenant_id (FK)
├── workflow_name
├── workflow_code
├── module
├── definition (JSON)
├── status
├── created_at
└── created_by


workflow_instances
├── instance_id (PK)
├── workflow_id (FK)
├── tenant_id (FK)
├── entity_type
├── entity_id
├── current_state
├── status
├── started_at
├── completed_at
└── created_by


approval_requests
├── approval_id (PK)
├── tenant_id (FK)
├── workflow_instance_id (FK)
├── requested_by
├── approved_by
├── approval_status
├── approval_notes
├── requested_at
├── approved_at
└── status
```


---

### Notification System


```
notifications
├── notification_id (PK)
├── tenant_id (FK)
├── user_id (FK)
├── notification_type
├── title
├── message
├── data (JSON)
├── is_read
├── read_at
├── created_at
└── expires_at


email_queue
├── email_id (PK)
├── tenant_id (FK)
├── to_email
├── subject
├── body
├── status
├── sent_at
├── error_message
├── created_at
└── retry_count


sms_queue
├── sms_id (PK)
├── tenant_id (FK)
├── to_phone
├── message
├── status
├── sent_at
├── error_message
├── created_at
└── retry_count


push_queue
├── push_id (PK)
├── tenant_id (FK)
├── user_id (FK)
├── device_token
├── title
├── message
├── data (JSON)
├── status
├── sent_at
├── error_message
├── created_at
└── retry_count
```


---

### File Management


```
files
├── file_id (PK)
├── tenant_id (FK)
├── file_name
├── file_path
├── file_type
├── file_size
├── mime_type
├── uploaded_by
├── status
├── created_at
└── updated_at


documents
├── document_id (PK)
├── tenant_id (FK)
├── file_id (FK)
├── document_type
├── reference_type
├── reference_id
├── title
├── description
├── status
├── created_at
└── created_by


attachments
├── attachment_id (PK)
├── tenant_id (FK)
├── document_id (FK)
├── file_id (FK)
├── attachment_name
├── attachment_type
├── created_at
└── created_by
```


---

### Master Global Data


```
countries
├── country_id (PK)
├── country_name
├── country_code
├── currency_code
├── calling_code
├── status
└── created_at


currencies
├── currency_id (PK)
├── currency_name
├── currency_code
├── symbol
├── exchange_rate
├── status
└── updated_at


tax_codes
├── tax_id (PK)
├── tax_name
├── tax_code
├── tax_rate
├── tax_type
├── status
└── created_at


units
├── unit_id (PK)
├── unit_name
├── unit_code
├── base_unit_id
├── conversion_factor
├── status
└── created_at
```


---

### Business Partners (Shared)


```
business_partners
├── partner_id (PK)
├── tenant_id (FK)
├── partner_type
├── partner_name
├── partner_code
├── tax_id
├── phone
├── email
├── address
├── status
├── created_at
└── updated_at
```


---

# 5. Product Databases


## Restaurant ERP Database


**Database Name:** `ebp_restaurant`


**Tujuan:** Restaurant-specific data


### Restaurant-Specific Tables


```
menu_categories
menus
menu_prices
recipes
recipe_details
inventory_categories
inventory_items
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
restaurant_customers
customer_memberships
suppliers
food_cost_analysis
```


---

## Hotel ERP Database


**Database Name:** `ebp_hotel`


**Tujuan:** Hotel-specific data


### Hotel-Specific Tables


```
room_types
rooms
room_rates
room_availability
guests
reservations
reservation_details
check_ins
check_outs
housekeeping
room_service
hotel_inventory
hotel_accounting
hotel_guests
guest_history
```


---

## Parking System Database


**Database Name:** `ebp_parking`


**Tujuan:** Parking-specific data


### Parking-Specific Tables


```
parking_areas
parking_slots
parking_rates
vehicles
parking_tickets
parking_transactions
parking_passes
parking_violations
parking_owners
```


---

## Agriculture ERP Database


**Database Name:** `ebp_agriculture`


**Tujuan:** Agriculture-specific data


### Agriculture-Specific Tables


```
farms
farmers
crops
harvests
raw_materials
production
warehouses
sales
agriculture_inventory
agriculture_accounting
```


---

## Legal System Database


**Database Name:** `ebp_legal`


**Tujuan:** Legal-specific data


### Legal-Specific Tables


```
clients
cases
case_documents
legal_services
billing
legal_calendar
legal_team
```


---

# 6. Cross-Database Relationships


## User Access Flow


```

1. User Login

   ↓

2. Authenticate in EBP CORE DATABASE

   ↓

3. Get Tenant ID

   ↓

4. Get Product Access

   ↓

5. Connect to Product Database

   ↓

6. Access Product Data

```


---

## Business Partner Pattern


Core Database:


```
business_partners
├── partner_id
├── tenant_id
├── partner_name
├── phone
├── email
└── address
```


Restaurant Database:


```
restaurant_customers
├── customer_id
├── partner_id (FK to core.business_partners)
├── membership_level
├── favorite_menu
└── loyalty_points
```


Hotel Database:


```
hotel_guests
├── guest_id
├── partner_id (FK to core.business_partners)
├── passport_number
├── room_history
└── guest_type
```


---

# 7. Multi-Tenant Database Strategy


## Model A: Database Per Tenant (Recommended for Enterprise)


```

MySQL Server

├── ebp_core
├── tenant_restaurant_001
├── tenant_restaurant_002
├── tenant_hotel_001
├── tenant_hotel_002
└── tenant_parking_001

```


**Pros:**
- Complete data isolation
- Better security
- Independent backup/restore
- Better performance
- Easier migration


**Cons:**
- Higher infrastructure cost
- More complex management


**Use Case:**
- Enterprise clients
- High security requirements
- Large data volume


---

## Model B: Shared Database with Tenant ID (Recommended for Startup)


```

ebp_restaurant

├── orders
│   ├── order_id
│   ├── tenant_id
│   └── branch_id
├── menus
│   ├── menu_id
│   ├── tenant_id
│   └── branch_id
└── ...

```


**Pros:**
- Lower infrastructure cost
- Easier management
- Faster deployment


**Cons:**
- Shared resources
- Potential performance issues
- Complex security
- Difficult backup/restore


**Use Case:**
- Early stage startup
- Small tenants
- Cost-sensitive


---

## Model C: Hybrid (Recommended for EBP Platform)


```

Small Tenants:

Shared Database


Large Tenants:

Dedicated Database


```


**Logic:**
- Tenant with < 1000 transactions/day → Shared
- Tenant with > 1000 transactions/day → Dedicated


**Pros:**
- Cost-effective for small tenants
- Performance for large tenants
- Flexible scaling


**Cons:**
- Complex migration logic
- Dual management


**Use Case:**
- SaaS platform
- Mixed tenant sizes
- Growth-oriented


---

# 8. Database Connection Strategy


## Connection Manager


```php
class DatabaseManager
{

    private $coreConnection;

    private $productConnections = [];



    public function getCoreConnection()
    {

        if (!$this->coreConnection) {

            $this->coreConnection = $this->connect('ebp_core');

        }

        return $this->coreConnection;

    }



    public function getProductConnection($tenantId, $productType)
    {

        $key = $tenantId . '_' . $productType;



        if (!isset($this->productConnections[$key])) {

            $databaseName = $this->getTenantDatabase($tenantId, $productType);

            $this->productConnections[$key] = $this->connect($databaseName);

        }



        return $this->productConnections[$key];

    }



    private function getTenantDatabase($tenantId, $productType)
    {

        // Check if tenant has dedicated database

        // Otherwise use shared database

        return $databaseName;

    }

}
```


---

## Transaction Across Databases


For operations that span multiple databases:


```

BEGIN TRANSACTION (Core)

↓

BEGIN TRANSACTION (Product)

↓

Execute Operations

↓

COMMIT (Product)

↓

COMMIT (Core)

```


If any fails:


```

ROLLBACK (Product)

↓

ROLLBACK (Core)

```


---

# 9. Database Migration Strategy


## Version Control


Each database has migration files:


```

Core Database Migrations:

/migrations/core/
    ├── 001_create_tenants.sql
    ├── 002_create_users.sql
    ├── 003_create_roles.sql
    └── ...


Restaurant Database Migrations:

/migrations/restaurant/
    ├── 001_create_menu_categories.sql
    ├── 002_create_menus.sql
    ├── 003_create_orders.sql
    └── ...


Hotel Database Migrations:

/migrations/hotel/
    ├── 001_create_room_types.sql
    ├── 002_create_rooms.sql
    └── ...


```


---

## Migration Execution


```php
class MigrationRunner
{

    public function runCoreMigrations()
    {

        $this->runMigrations('core', 'ebp_core');

    }



    public function runProductMigrations($productType)
    {

        $this->runMigrations($productType, "ebp_{$productType}");

    }



    private function runMigrations($type, $database)
    {

        $migrations = $this->getPendingMigrations($type);



        foreach ($migrations as $migration) {

            $this->executeMigration($database, $migration);

            $this->recordMigration($type, $migration);

        }

    }

}
```


---

## Rollback Strategy


```php
class MigrationRollback
{

    public function rollback($type, $version)
    {

        $migration = $this->getMigration($type, $version);

        $this->executeRollback($migration);

        $this->removeMigrationRecord($type, $version);

    }

}
```


---

# 10. Backup Strategy


## Core Database Backup


**Frequency:** Daily


**Retention:** 30 days


**Strategy:**
- Full backup daily
- Incremental backup hourly
- Point-in-time recovery enabled


---

## Product Database Backup


**Frequency:**


- Large tenants: Daily
- Small tenants: Weekly


**Retention:** 90 days


**Strategy:**
- Per-tenant backup
- Automated backup scheduling
- Backup verification


---

## Backup Storage


**Local:** 7 days


**Off-site:** 30 days


**Cold Storage:** 1 year


---

## Backup Script


```bash
#!/bin/bash

# Core Database Backup
mysqldump ebp_core | gzip > /backup/core/ebp_core_$(date +%Y%m%d).sql.gz


# Product Database Backup
for tenant in $(mysql -N -e "SELECT tenant_id FROM ebp_core.tenants WHERE status='ACTIVE'"); do
    mysqldump ebp_restaurant_${tenant} | gzip > /backup/restaurant/ebp_restaurant_${tenant}_$(date +%Y%m%d).sql.gz
done


# Cleanup old backups
find /backup -name "*.sql.gz" -mtime +30 -delete
```


---

# 11. Scaling Strategy


## Read Replicas


```

Primary Database (Write)

    ↓

Read Replica 1 (Read)

Read Replica 2 (Read)

Read Replica 3 (Read)

```


**Use Case:**
- High read volume
- Reporting queries
- Analytics


---

## Sharding Strategy


```

Tenant Data Sharding

Shard 1: Tenant 1-100
Shard 2: Tenant 101-200
Shard 3: Tenant 201-300

```


**Use Case:**
- Very large tenant count
- High write volume
- Geographic distribution


---

## Caching Layer


```

Application

    ↓

Redis Cache

    ↓

Database

```


**Use Case:**
- Frequent queries
- Session storage
- Real-time data


---

# 12. Security Strategy


## Database User Permissions


### Core Database


```

ebp_core_read
- SELECT only on all tables


ebp_core_write
- SELECT, INSERT, UPDATE on operational tables
- No DELETE, DROP, ALTER


ebp_core_admin
- Full access (for migrations only)
```


### Product Database


```

ebp_restaurant_read
- SELECT only on restaurant tables


ebp_restaurant_write
- SELECT, INSERT, UPDATE on restaurant tables
- No DELETE, DROP, ALTER


ebp_restaurant_admin
- Full access (for migrations only)
```


---

## Data Encryption


**At Rest:**
- Transparent Data Encryption (TDE)
- File system encryption


**In Transit:**
- SSL/TLS connections
- Encrypted backups


**Sensitive Fields:**
- Password: bcrypt hash
- Credit card: AES-256 encryption
- Personal data: AES-256 encryption


---

## Audit Trail


All database changes logged:


```

audit_logs table in core database

- Who made change
- What was changed
- When was changed
- Old value
- New value
- IP address
- User agent

```


---

# 13. Monitoring Strategy


## Database Metrics


- Connection pool usage
- Query performance
- Slow query log
- Lock wait time
- Disk usage
- Memory usage
- CPU usage


## Alerts


- High connection usage (> 80%)
- Slow queries (> 1 second)
- Disk space low (< 20%)
- Replication lag
- Backup failure


---

# 14. Disaster Recovery


## Recovery Time Objective (RTO)


- Core Database: 1 hour
- Product Database: 4 hours


## Recovery Point Objective (RPO)


- Core Database: 15 minutes
- Product Database: 1 hour


## Recovery Process


1. Identify failure point
2. Select appropriate backup
3. Restore to staging
4. Verify data integrity
5. Switch DNS
6. Monitor performance


---

# 15. Data Consistency


## Eventual Consistency


For cross-database operations:


```

Order Created (Product DB)

↓

Event Published

↓

Inventory Updated (Product DB)

↓

Accounting Updated (Product DB)

↓

Audit Logged (Core DB)

```


Acceptable delay: < 5 seconds


---

## Strong Consistency


For critical operations:


```

BEGIN TRANSACTION

↓

All operations in same database

↓

COMMIT

```


---

# 16. Database Versioning


## Semantic Versioning


Format: `MAJOR.MINOR.PATCH`


- **MAJOR**: Breaking schema changes
- **MINOR**: New tables, backward compatible
- **PATCH**: Index changes, performance improvements


## Version Tracking


```

schema_migrations table

├── migration_id
├── version
├── description
├── executed_at
└── execution_time
```


---

# 17. Conclusion


EBP Database Architecture:


```

ONE CORE DATABASE

+

MULTIPLE PRODUCT DATABASES

```


This architecture enables:


- Clear separation of concerns
- Independent product development
- Better security
- Scalable infrastructure
- Flexible deployment
- Professional software company structure


EBP is building not just database schemas.

EBP is building a database platform for building applications.


---

# Document End


ID Dokumen:

EBP-DATABASE-ARCHITECTURE-001


Versi:

1.0
