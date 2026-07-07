# EBP Restaurant ERP - Role-Based Menu Analysis

**Date**: 2026-07-05
**Purpose**: Deep analysis of appropriate menu access per hierarchy level and role

---

## 1. Hierarchy Structure

### Level 1: Platform Owner (Application Owner)
- **Entity**: EBP Platform company / SaaS provider
- **Scope**: All tenants across the platform
- **Goal**: Platform health, tenant growth, subscription management, global analytics
- **Users**: `platform` (is_platform_owner = 1)
- **Primary Activities**:
  - Manage tenants (create, configure, suspend)
  - Monitor platform-wide analytics
  - Manage platform users and global settings
  - Configure enterprise-wide features
  - Handle integrations, billing, compliance

### Level 2: Tenant Owner (Restaurant Owner)
- **Entity**: Individual restaurant / F&B business
- **Scope**: Single tenant (restaurant)
- **Goal**: Business profitability, operations oversight, strategic decisions
- **Users**: `admin` (Administrator role)
- **Primary Activities**:
  - Full operational control of the restaurant
  - Financial oversight and reporting
  - Staff management and HR
  - Menu, pricing, inventory strategy
  - Customer relationship management

### Level 3: Tenant Member (Restaurant Staff)
- **Entity**: Employees within a restaurant
- **Scope**: Single tenant, limited by job function
- **Goal**: Execute daily tasks efficiently
- **Users**: manager, waiter, kitchen, cashier, inventory, host
- **Primary Activities**: Role-specific operational tasks

---

## 2. Deep Role Analysis

### Level 1: Platform Owner

#### What They Need
- **Enterprise-wide visibility**: overview of all tenants, revenue, growth
- **Tenant management**: onboard, configure, monitor tenants
- **Platform administration**: global users, settings, integrations
- **Financial oversight**: platform-level accounting, subscription billing
- **Compliance & quality**: audit logs, quality standards, sustainability metrics
- **Strategic tools**: AI analytics, reports, multi-location data

#### What They DON'T Need
- Day-to-day restaurant operations (menu editing, taking orders, kitchen display)
- Single-tenant operational data (unless aggregated)
- Reservation management for one restaurant
- Delivery management for one restaurant

#### Recommended Menu
- overview, enterprise, tenant, users, settings, reports, accounting, hr, crm, ai, integration, quality, supplychain, sustainability, location, maintenance, whatsapp

---

### Level 2: Tenant Owner

#### What They Need
- **Full business visibility**: overview, reports, accounting
- **Operations management**: menu, tables, orders, inventory, kitchen, reservation, delivery
- **People management**: users, hr, roles, permissions
- **Customer management**: crm, loyalty, reservations
- **Strategic configuration**: settings, integration, ai insights
- **Quality & compliance**: quality, sustainability, maintenance

#### Recommended Menu
- ALL tabs except enterprise and tenant (platform-level only)
- overview, menu, tables, orders, inventory, kitchen, users, settings, accounting, reservation, crm, reports, hr, delivery, ai, integration, quality, supplychain, sustainability, location, maintenance, whatsapp

---

### Level 3: Tenant Member

#### 3.1 Administrator (Tenant Admin)
- **Role**: Deputy to tenant owner, IT admin
- **Scope**: Full operational access within tenant
- **Activities**: System configuration, user management, menu setup, reports
- **Recommended Menu**: All operational tabs (same as Tenant Owner)
- overview, menu, tables, orders, inventory, kitchen, users, settings, accounting, reservation, crm, reports, hr, delivery, ai, integration, quality, supplychain, sustainability, location, maintenance, whatsapp

#### 3.2 Restaurant Manager
- **Role**: Daily operations manager
- **Scope**: Operational oversight, staff coordination, customer satisfaction
- **Activities**:
  - Monitor tables, orders, reservations
  - Manage inventory and kitchen workflow
  - Review sales reports and accounting
  - Handle CRM and staff (HR)
  - Coordinate delivery and supply chain
- **Recommended Menu**:
  - overview, menu, tables, orders, inventory, kitchen, reservation, reports, hr, crm, delivery, supplychain, quality, accounting
- **NOT needed**: enterprise, tenant, platform settings, ai configuration, integration setup, location management, maintenance scheduling, sustainability metrics, whatsapp setup

#### 3.3 Waiter
- **Role**: Front-of-house service
- **Scope**: Customer service, order taking, table management
- **Activities**:
  - View tables and take orders
  - Create reservations
  - View menu (read-only)
  - Update order status
- **Recommended Menu**:
  - overview, tables, orders, reservation, menu
- **NOT needed**: inventory, kitchen, accounting, hr, crm, reports, delivery, settings, supplychain, etc.

#### 3.4 Kitchen Staff
- **Role**: Food preparation
- **Scope**: Kitchen operations
- **Activities**:
  - View kitchen orders
  - View incoming orders
  - Check inventory (ingredients)
  - Update order status
- **Recommended Menu**:
  - overview, kitchen, orders, inventory (read-only), menu (read-only)
- **NOT needed**: tables, accounting, crm, hr, delivery, reservation, etc.

#### 3.5 Cashier
- **Role**: Payment processing, billing
- **Scope**: Checkout and financial transactions
- **Activities**:
  - Process orders and payments
  - View accounting and reports
  - View tables (for billing)
  - View menu (for pricing)
- **Recommended Menu**:
  - overview, orders, accounting, reports, tables, menu
- **NOT needed**: inventory, kitchen, hr, crm, supplychain, etc.

#### 3.6 Inventory Manager
- **Role**: Stock and supply management
- **Scope**: Inventory, procurement, quality control
- **Activities**:
  - Manage inventory levels
  - Supplier management (supplychain)
  - Quality checks
  - View orders (to forecast demand)
  - View reports for inventory analytics
- **Recommended Menu**:
  - overview, inventory, supplychain, quality, orders (read-only), reports, menu (read-only)
- **NOT needed**: tables, kitchen, accounting (write), hr, crm, reservation, delivery

#### 3.7 Host/Hostess
- **Role**: Guest reception and seating
- **Scope**: Front desk, reservations, seating
- **Activities**:
  - Manage reservations
  - Manage table status
  - View orders for guests
  - View menu (for guest questions)
- **Recommended Menu**:
  - overview, tables, reservation, orders (read-only), menu
- **NOT needed**: inventory, kitchen, accounting, hr, delivery, supplychain, etc.

---

## 3. Recommended Final Menu Configuration

```javascript
const MENU_ACCESS = {
    PLATFORM_OWNER: {
        label: 'Platform Owner',
        tabs: [
            'overview', 'enterprise', 'tenant', 'users', 'settings',
            'reports', 'accounting', 'hr', 'crm', 'ai', 'integration',
            'quality', 'supplychain', 'sustainability', 'location',
            'maintenance', 'whatsapp'
        ]
    },
    TENANT_OWNER: {
        label: 'Tenant Owner',
        tabs: [
            'overview', 'menu', 'tables', 'orders', 'inventory', 'kitchen',
            'users', 'settings', 'accounting', 'reservation', 'crm', 'reports',
            'hr', 'delivery', 'ai', 'integration', 'quality', 'supplychain',
            'sustainability', 'location', 'maintenance', 'whatsapp'
        ]
    },
    TENANT_MEMBER: {
        Administrator: [
            'overview', 'menu', 'tables', 'orders', 'inventory', 'kitchen',
            'users', 'settings', 'accounting', 'reservation', 'crm', 'reports',
            'hr', 'delivery', 'ai', 'integration', 'quality', 'supplychain',
            'sustainability', 'location', 'maintenance', 'whatsapp'
        ],
        'Restaurant Manager': [
            'overview', 'menu', 'tables', 'orders', 'inventory', 'kitchen',
            'reservation', 'reports', 'hr', 'crm', 'delivery', 'supplychain',
            'quality', 'accounting'
        ],
        Waiter: [
            'overview', 'tables', 'orders', 'reservation', 'menu'
        ],
        'Kitchen Staff': [
            'overview', 'kitchen', 'orders', 'inventory', 'menu'
        ],
        Cashier: [
            'overview', 'orders', 'accounting', 'reports', 'tables', 'menu'
        ],
        'Inventory Manager': [
            'overview', 'inventory', 'supplychain', 'quality', 'orders', 'reports', 'menu'
        ],
        'Host/Hostess': [
            'overview', 'tables', 'reservation', 'orders', 'menu'
        ]
    }
};
```

---

## 4. Summary of Exclusions by Role

| Role | Excluded Menus (Why) |
|------|---------------------|
| **Platform Owner** | menu, tables, orders, inventory, kitchen, reservation, delivery (operational, not platform-level) |
| **Tenant Owner** | enterprise, tenant (platform-level only) |
| **Administrator** | enterprise, tenant (platform-level only) |
| **Restaurant Manager** | enterprise, tenant, settings (platform/owner-only), ai, integration, sustainability, location, maintenance, whatsapp (specialized/owner config) |
| **Waiter** | inventory, kitchen, accounting, hr, crm, reports, delivery, settings, supplychain, quality, sustainability, location, maintenance, whatsapp, ai, integration, enterprise, tenant, users |
| **Kitchen Staff** | tables, reservation, accounting, hr, crm, reports, delivery, settings, supplychain, sustainability, location, maintenance, whatsapp, ai, integration, enterprise, tenant, users |
| **Cashier** | inventory, kitchen, hr, crm, delivery, supplychain, quality, sustainability, location, maintenance, whatsapp, ai, integration, enterprise, tenant, users, reservation |
| **Inventory Manager** | tables, kitchen, accounting (write), hr, crm, reservation, delivery, settings, sustainability, location, maintenance, whatsapp, ai, integration, enterprise, tenant, users |
| **Host/Hostess** | inventory, kitchen, accounting, hr, crm, delivery, supplychain, quality, sustainability, location, maintenance, whatsapp, ai, integration, enterprise, tenant, users |

---

## 5. Notes

- This configuration assumes **menu should be read-only for operational staff** unless they have explicit permission.
- In production, menu access should be enforced by backend permissions (PermissionMiddleware), not just frontend hiding.
- Platform Owner should be able to **impersonate** any tenant to view operational data when needed, but this is a separate feature.
