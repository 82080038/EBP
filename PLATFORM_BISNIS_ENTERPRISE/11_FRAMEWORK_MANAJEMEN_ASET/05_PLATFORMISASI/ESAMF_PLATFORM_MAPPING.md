# ESAMF Platform Mapping

**Document ID:** ESAMF-PLATFORMIZATION-001

**Version:** 1.0

**Purpose:** Define the platform mapping methodology for ESAMF

---

# Overview

Platform Mapping is the process of mapping repository components to their appropriate destinations within the EBP platform structure. This mapping determines where each component will reside after migration.

---

# Platform Structure

## EBP Platform Hierarchy

```
Enterprise Business Platform
│
├── 06_CORE_CODE/ (Core Assets)
│   ├── Authentication/
│   ├── Authorization/
│   ├── Audit/
│   ├── Configuration/
│   ├── Error Handling/
│   ├── Logging/
│   ├── Validation/
│   └── ...
│
├── 07_SHARED_ENGINES/ (Shared Engines)
│   ├── Notification Engine/
│   ├── Reporting Engine/
│   ├── Inventory Engine/
│   ├── Pricing Engine/
│   ├── Accounting Engine/
│   ├── Forecast Engine/
│   ├── AI Engine/
│   └── ...
│
└── PRODUCTS/ (Product Assets)
    ├── RESTAURANT_ERP/
    ├── HOTEL_ERP/
    ├── TOURISM_ERP/
    ├── RETAIL_ERP/
    ├── FINANCE_ERP/
    ├── EDUCATION_ERP/
    ├── CULTURE_ERP/
    └── PUBLIC_SERVICE_ERP/
```

---

# Mapping Criteria

## Core Asset Mapping

### Destination: 06_CORE_CODE/

### Criteria

- **Universal Usage**: Used by ALL products
- **Generic Logic**: No industry-specific logic
- **Fundamental Nature**: Essential for platform operation
- **No Business Rules**: Does not contain business rules

### Examples

| Component | Source Repository | Destination |
|-----------|------------------|-------------|
| Authentication | restoran | 06_CORE_CODE/Authentication |
| Authorization (RBAC) | restoran | 06_CORE_CODE/Authorization |
| Audit Trail | restoran | 06_CORE_CODE/Audit |
| Configuration Management | restoran | 06_CORE_CODE/Configuration |
| Error Handling | restoran | 06_CORE_CODE/ErrorHandling |
| Logging | restoran | 06_CORE_CODE/Logging |
| Validation | restoran | 06_CORE_CODE/Validation |
| User Management | restoran | 06_CORE_CODE/UserManagement |

---

## Shared Engine Mapping

### Destination: 07_SHARED_ENGINES/

### Criteria

- **Widely Applicable**: Used by MANY products (3+)
- **Mostly Generic**: Minimal industry-specific logic
- **Configurable**: Can be configured for different contexts
- **Complex**: Contains significant business logic

### Examples

| Component | Source Repository | Destination |
|-----------|------------------|-------------|
| Notification System | restoran | 07_SHARED_ENGINES/NotificationEngine |
| Reporting System | restoran | 07_SHARED_ENGINES/ReportingEngine |
| Inventory Management | restoran | 07_SHARED_ENGINES/InventoryEngine |
| Pricing Engine | restoran | 07_SHARED_ENGINES/PricingEngine |
| Payment Processing | restoran | 07_SHARED_ENGINES/PaymentEngine |
| File Management | restoran | 07_SHARED_ENGINES/FileEngine |
| Search Engine | restoran | 07_SHARED_ENGINES/SearchEngine |
| Queue Management | restoran | 07_SHARED_ENGINES/QueueEngine |

---

## Product Asset Mapping

### Destination: PRODUCTS/{PRODUCT}/

### Criteria

- **Domain-Specific**: Used by ONE product
- **Industry-Specific**: Contains business domain logic
- **Specialized**: Tailored to specific business needs
- **Unique**: Differentiates the product

### Examples

| Component | Source Repository | Destination |
|-----------|------------------|-------------|
| Point of Sale (POS) | restoran | PRODUCTS/RESTAURANT_ERP/ |
| Kitchen Display System | restoran | PRODUCTS/RESTAURANT_ERP/ |
| Menu Management | restoran | PRODUCTS/RESTAURANT_ERP/ |
| Table Management | restoran | PRODUCTS/RESTAURANT_ERP/ |
| Reservation System | restoran | PRODUCTS/RESTAURANT_ERP/ |
| Destination Management | mywisata | PRODUCTS/TOURISM_ERP/ |
| Tour Packages | mywisata | PRODUCTS/TOURISM_ERP/ |
| Supplier Management | panglong | PRODUCTS/RETAIL_ERP/ |
| Stock Trading | saham | PRODUCTS/FINANCE_ERP/ |
| Course Management | pelajaran | PRODUCTS/EDUCATION_ERP/ |
| Family Tree | tarombo | PRODUCTS/CULTURE_ERP/ |
| Visitor Registration | kewer | PRODUCTS/PUBLIC_SERVICE_ERP/ |

---

# Mapping Process

## Step 1: Component Inventory

```
Component Inventory:
- Component 1: [Name, Purpose, Current Location]
- Component 2: [Name, Purpose, Current Location]
- Component 3: [Name, Purpose, Current Location]
```

## Step 2: Classification

```
Component Classification:
- Component 1: [Core Asset/Shared Engine/Product Asset]
- Component 2: [Core Asset/Shared Engine/Product Asset]
- Component 3: [Core Asset/Shared Engine/Product Asset]
```

## Step 3: Destination Assignment

```
Destination Assignment:
- Component 1: [EBP Destination]
- Component 2: [EBP Destination]
- Component 3: [EBP Destination]
```

## Step 4: Dependency Mapping

```
Dependency Mapping:
- Component 1 depends on: [EBP Core/Shared Engine/Product]
- Component 2 depends on: [EBP Core/Shared Engine/Product]
- Component 3 depends on: [EBP Core/Shared Engine/Product]
```

## Step 5: Migration Order

```
Migration Order:
1. [Component 1: Priority, Dependencies]
2. [Component 2: Priority, Dependencies]
3. [Component 3: Priority, Dependencies]
```

---

# Mapping Examples

## Example 1: RESTORAN Repository

### Core Assets

| Component | Current Location | Destination | Priority |
|-----------|------------------|-------------|----------|
| Authentication | modules/Auth | 06_CORE_CODE/Authentication | High |
| Authorization | modules/Auth | 06_CORE_CODE/Authorization | High |
| Audit Trail | modules/Audit | 06_CORE_CODE/Audit | High |
| Configuration | config/ | 06_CORE_CODE/Configuration | High |
| Error Handling | core/ | 06_CORE_CODE/ErrorHandling | Medium |

### Shared Engines

| Component | Current Location | Destination | Priority |
|-----------|------------------|-------------|----------|
| Notification System | modules/Notification | 07_SHARED_ENGINES/NotificationEngine | High |
| Reporting System | modules/Report | 07_SHARED_ENGINES/ReportingEngine | Medium |
| Inventory Management | modules/Inventory | 07_SHARED_ENGINES/InventoryEngine | Medium |

### Product Assets

| Component | Current Location | Destination | Priority |
|-----------|------------------|-------------|----------|
| Point of Sale (POS) | modules/POS | PRODUCTS/RESTAURANT_ERP/ | High |
| Kitchen Display System | modules/Kitchen | PRODUCTS/RESTAURANT_ERP/ | High |
| Menu Management | modules/Menu | PRODUCTS/RESTAURANT_ERP/ | High |
| Table Management | modules/Table | PRODUCTS/RESTAURANT_ERP/ | Medium |
| Reservation System | modules/Reservation | PRODUCTS/RESTAURANT_ERP/ | Medium |

---

## Example 2: MYWISATA Repository

### Core Assets

| Component | Current Location | Destination | Priority |
|-----------|------------------|-------------|----------|
| Authentication | modules/Auth | 06_CORE_CODE/Authentication | High |
| User Management | modules/User | 06_CORE_CODE/UserManagement | High |

### Shared Engines

| Component | Current Location | Destination | Priority |
|-----------|------------------|-------------|----------|
| Notification System | modules/Notification | 07_SHARED_ENGINES/NotificationEngine | High |
| Booking System | modules/Booking | 07_SHARED_ENGINES/BookingEngine | Medium |
| Review System | modules/Review | 07_SHARED_ENGINES/ReviewEngine | Medium |

### Product Assets

| Component | Current Location | Destination | Priority |
|-----------|------------------|-------------|----------|
| Destination Management | modules/Destination | PRODUCTS/TOURISM_ERP/ | High |
| Tour Packages | modules/Tour | PRODUCTS/TOURISM_ERP/ | High |
| Guide Management | modules/Guide | PRODUCTS/TOURISM_ERP/ | Medium |

---

# Mapping Rules

## Rule 1: Universal Components → Core

If a component is used by ALL products and has no industry-specific logic, it must be mapped to 06_CORE_CODE/.

## Rule 2: Widely Used Components → Shared Engine

If a component is used by MANY products (3+) and has minimal industry-specific logic, it should be mapped to 07_SHARED_ENGINES/.

## Rule 3: Domain-Specific Components → Product

If a component is used by ONE product and has industry-specific logic, it must be mapped to PRODUCTS/{PRODUCT}/.

## Rule 4: Dependencies Must Be Resolved

All dependencies must be mapped before the dependent component can be migrated.

## Rule 5: No Duplication

If a component already exists in the destination, evaluate whether to:
- Merge with existing component
- Replace existing component
- Keep both components

---

# Mapping Conflicts

## Conflict Resolution

### Conflict: Component Exists in Destination

**Resolution Process:**

1. **Compare Functionality**
   - Are the components functionally equivalent?
   - Does the new component add functionality?
   - Is the new component better implemented?

2. **Evaluate Quality**
   - Which component has better code quality?
   - Which component has better test coverage?
   - Which component has better documentation?

3. **Decision**
   - **Merge**: Combine functionality from both components
   - **Replace**: Replace existing component with new component
   - **Keep Both**: Keep both components if they serve different purposes

### Conflict: Circular Dependencies

**Resolution Process:**

1. **Identify Circular Dependency**
   - Document the circular dependency
   - Assess impact on migration

2. **Break Circular Dependency**
   - Extract common functionality to Core
   - Introduce interface to break coupling
   - Refactor to remove circular dependency

3. **Re-evaluate Mapping**
   - Update mapping after breaking circular dependency

---

# Mapping Documentation

## Mapping Template

```markdown
# [Repository Name] Platform Mapping

## Overview
- Repository: [Repository Name]
- Business Domain: [Domain]
- Total Components: [Number]
- Core Assets: [Number]
- Shared Engines: [Number]
- Product Assets: [Number]

## Core Assets

| Component | Current Location | Destination | Priority | Dependencies |
|-----------|------------------|-------------|----------|-------------|
| [Component] | [Location] | [Destination] | [Priority] | [Dependencies] |

## Shared Engines

| Component | Current Location | Destination | Priority | Dependencies |
|-----------|------------------|-------------|----------|-------------|
| [Component] | [Location] | [Destination] | [Priority] | [Dependencies] |

## Product Assets

| Component | Current Location | Destination | Priority | Dependencies |
|-----------|------------------|-------------|----------|-------------|
| [Component] | [Location] | [Destination] | [Priority] | [Dependencies] |

## Migration Order

1. [Component 1: Priority, Dependencies]
2. [Component 2: Priority, Dependencies]
3. [Component 3: Priority, Dependencies]

## Conflicts

- [Conflict 1: Description, Resolution]
- [Conflict 2: Description, Resolution]
```

---

# Mapping Validation

## Validation Checklist

- [ ] All components are mapped
- [ ] All dependencies are resolved
- [ ] No circular dependencies exist
- [ ] No duplicates exist
- [ ] Migration order is logical
- [ ] Conflicts are documented
- [ ] Conflicts are resolved

---

# Document End

**Document ID:** ESAMF-PLATFORMIZATION-001

**Version:** 1.0
