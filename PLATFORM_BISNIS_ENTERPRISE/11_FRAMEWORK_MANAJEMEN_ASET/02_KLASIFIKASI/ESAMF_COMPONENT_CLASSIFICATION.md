# ESAMF Component Classification

**Document ID:** ESAMF-CLASSIFICATION-001

**Version:** 1.0

**Purpose:** Define the classification system for software components in ESAMF

---

# Overview

Component Classification is the process of categorizing software components based on their reusability, scope, and applicability across the EBP ecosystem. This classification determines the migration destination and reuse strategy for each component.

---

# Classification Hierarchy

## Three-Tier Classification System

ESAMF uses a three-tier classification system:

```
Level 1: Core Asset (★★★★★)
├── Universal: Used by ALL products
├── Generic: No industry-specific logic
└── Destination: 06_CORE_CODE/

Level 2: Shared Engine (★★★★☆)
├── Widely applicable: Used by MANY products
├── Mostly generic: Minimal industry-specific logic
└── Destination: 07_SHARED_ENGINES/

Level 3: Product Asset (★★☆☆☆)
├── Domain-specific: Used by ONE product
├── Industry-specific: Business domain logic
└── Destination: PRODUCTS/
```

---

# Core Asset Classification

## Definition

**Core Assets** are universal components used by all EBP products with no industry-specific logic.

## Characteristics

- **Universal**: Used by ALL products
- **Generic**: No industry-specific logic
- **Fundamental**: Essential for platform operation
- **Stable**: Rarely changes
- **Well-tested**: Production-tested across multiple contexts

## Examples

### Authentication
```
Component: Authentication System
Purpose: User authentication and session management
Used by: All products
Logic: Generic - no industry-specific logic
Classification: Core Asset
Destination: 06_CORE_CODE/Authentication
```

### Authorization
```
Component: Authorization (RBAC)
Purpose: Role-based access control
Used by: All products
Logic: Generic - no industry-specific logic
Classification: Core Asset
Destination: 06_CORE_CODE/Authorization
```

### Audit Trail
```
Component: Audit Trail
Purpose: Action logging and tracking
Used by: All products
Logic: Generic - no industry-specific logic
Classification: Core Asset
Destination: 06_CORE_CODE/Audit
```

### Configuration
```
Component: Configuration Management
Purpose: Configuration storage and retrieval
Used by: All products
Logic: Generic - no industry-specific logic
Classification: Core Asset
Destination: 06_CORE_CODE/Configuration
```

### Error Handling
```
Component: Error Handling
Purpose: Exception handling and error logging
Used by: All products
Logic: Generic - no industry-specific logic
Classification: Core Asset
Destination: 06_CORE_CODE/ErrorHandling
```

## Classification Criteria

A component is classified as a Core Asset if it meets ALL of the following:

1. **Universal Usage**: Used by ALL products in the ecosystem
2. **Generic Logic**: Contains no industry-specific or domain-specific logic
3. **Fundamental Nature**: Essential for platform operation
4. **No Business Rules**: Does not contain business rules specific to any domain
5. **High Reusability**: Can be used in any context without modification

## Decision Tree

```
Is the component used by ALL products?
├── No → Not a Core Asset
└── Yes → Does it contain industry-specific logic?
    ├── Yes → Not a Core Asset
    └── No → Is it fundamental to platform operation?
        ├── No → Not a Core Asset
        └── Yes → Core Asset (★★★★★)
```

---

# Shared Engine Classification

## Definition

**Shared Engines** are widely applicable components used by many products with minimal industry-specific logic.

## Characteristics

- **Widely Applicable**: Used by MANY products (typically 3+)
- **Mostly Generic**: Minimal industry-specific logic
- **Configurable**: Can be configured for different contexts
- **Complex**: Contains significant business logic
- **Valuable**: Provides substantial value to products

## Examples

### Notification System
```
Component: Notification System
Purpose: Email, SMS, and push notifications
Used by: Restaurant, Hotel, Parking, etc.
Logic: Mostly generic - notification delivery
Classification: Shared Engine
Destination: 07_SHARED_ENGINES/NotificationEngine
```

### Reporting System
```
Component: Reporting System
Purpose: Report generation and analytics
Used by: Restaurant, Hotel, Parking, etc.
Logic: Mostly generic - report generation
Classification: Shared Engine
Destination: 07_SHARED_ENGINES/ReportingEngine
```

### Inventory System
```
Component: Inventory System
Purpose: Stock management and tracking
Used by: Restaurant, Hotel, Farming
Logic: Generic with configuration - inventory logic
Classification: Shared Engine
Destination: 07_SHARED_ENGINES/InventoryEngine
```

### Pricing Engine
```
Component: Pricing Engine
Purpose: Price calculation and management
Used by: Restaurant, Hotel, Parking
Logic: Generic with configuration - pricing logic
Classification: Shared Engine
Destination: 07_SHARED_ENGINES/PricingEngine
```

## Classification Criteria

A component is classified as a Shared Engine if it meets ALL of the following:

1. **Wide Usage**: Used by MANY products (typically 3+)
2. **Mostly Generic**: Contains minimal industry-specific logic
3. **Configurable**: Can be configured for different contexts
4. **Complex**: Contains significant business logic
5. **Valuable**: Provides substantial value to products

## Decision Tree

```
Is the component used by MANY products (3+)?
├── No → Not a Shared Engine
└── Yes → Does it contain significant industry-specific logic?
    ├── Yes → Not a Shared Engine
    └── No → Is it configurable for different contexts?
        ├── No → Not a Shared Engine
        └── Yes → Shared Engine (★★★★☆)
```

---

# Product Asset Classification

## Definition

**Product Assets** are domain-specific components used by a single product with industry-specific business logic.

## Characteristics

- **Domain-Specific**: Used by ONE product
- **Industry-Specific**: Contains business domain logic
- **Specialized**: Tailored to specific business needs
- **Unique**: Differentiates the product
- **Valuable**: Provides product-specific value

## Examples

### Point of Sale (POS)
```
Component: Point of Sale (POS)
Purpose: Order processing and payment
Used by: Restaurant ERP only
Logic: Restaurant-specific - order and payment logic
Classification: Product Asset
Destination: PRODUCTS/RESTAURANT_ERP/
```

### Kitchen Display System
```
Component: Kitchen Display System
Purpose: Kitchen workflow and order routing
Used by: Restaurant ERP only
Logic: Restaurant-specific - kitchen workflow
Classification: Product Asset
Destination: PRODUCTS/RESTAURANT_ERP/
```

### Menu Management
```
Component: Menu Management
Purpose: Menu and product management
Used by: Restaurant ERP only
Logic: Restaurant-specific - menu logic
Classification: Product Asset
Destination: PRODUCTS/RESTAURANT_ERP/
```

### Reservation System
```
Component: Reservation System
Purpose: Reservation and booking management
Used by: Restaurant ERP only
Logic: Restaurant-specific - reservation logic
Classification: Product Asset
Destination: PRODUCTS/RESTAURANT_ERP/
```

## Classification Criteria

A component is classified as a Product Asset if it meets ANY of the following:

1. **Single Usage**: Used by only ONE product
2. **Industry-Specific**: Contains significant industry-specific logic
3. **Domain-Specific**: Tailored to specific business domain
4. **Product-Unique**: Differentiates the product
5. **Not Configurable**: Cannot be easily configured for other contexts

## Decision Tree

```
Is the component used by only ONE product?
├── Yes → Product Asset (★★☆☆☆)
└── No → Does it contain significant industry-specific logic?
    ├── Yes → Product Asset (★★☆☆☆)
    └── No → Re-evaluate as Shared Engine
```

---

# Reusability Rating System

## Five-Star Rating System

ESAMF uses a five-star rating system to indicate component reusability:

```
★★★★★ (5 Stars) - Core Asset
- Universal: Used by ALL products
- Generic: No industry-specific logic
- Maximum reusability

★★★★☆ (4 Stars) - Shared Engine
- Widely applicable: Used by MANY products
- Mostly generic: Minimal industry-specific logic
- High reusability

★★★☆☆ (3 Stars) - Domain Shared
- Domain-specific: Used by products in same domain
- Some industry-specific logic
- Medium reusability

★★☆☆☆ (2 Stars) - Product Asset
- Domain-specific: Used by ONE product
- Industry-specific logic
- Low reusability

★☆☆☆☆ (1 Star) - Legacy
- Not reusable
- Should be refactored or deprecated
- No reusability
```

## Rating Criteria

### 5 Stars (Core Asset)
- Used by ALL products
- No industry-specific logic
- Universal applicability
- No configuration needed

### 4 Stars (Shared Engine)
- Used by MANY products (3+)
- Minimal industry-specific logic
- Configurable for different contexts
- High reusability

### 3 Stars (Domain Shared)
- Used by products in same domain (2-3)
- Some industry-specific logic
- Configurable within domain
- Medium reusability

### 2 Stars (Product Asset)
- Used by ONE product
- Industry-specific logic
- Not easily configurable
- Low reusability

### 1 Star (Legacy)
- Not reusable
- Should be refactored
- No reusability

---

# Classification Process

## Step 1: Identify Component

1. **Component Name**: [Name of component]
2. **Component Purpose**: [What the component does]
3. **Component Location**: [Where the component is located]
4. **Component Size**: [Lines of code]

## Step 2: Analyze Usage

1. **Current Usage**: [Which products use it]
2. **Potential Usage**: [Which products could use it]
3. **Usage Frequency**: [How often it's used]
4. **Usage Context**: [In what contexts it's used]

## Step 3: Analyze Logic

1. **Generic Logic**: [What generic logic it contains]
2. **Industry-Specific Logic**: [What industry-specific logic it contains]
3. **Business Rules**: [What business rules it contains]
4. **Domain Knowledge**: [What domain knowledge it contains]

## Step 4: Assess Configurability

1. **Configuration Options**: [What can be configured]
2. **Configuration Complexity**: [How complex configuration is]
3. **Configuration Flexibility**: [How flexible configuration is]
4. **Configuration Needs**: [What configuration is needed]

## Step 5: Apply Classification

1. **Use Decision Tree**: Apply appropriate decision tree
2. **Assign Classification**: Assign Core Asset, Shared Engine, or Product Asset
3. **Assign Rating**: Assign star rating
4. **Document Rationale**: Document classification rationale

## Step 6: Determine Destination

1. **Core Asset**: 06_CORE_CODE/[component]
2. **Shared Engine**: 07_SHARED_ENGINES/[component]
3. **Product Asset**: PRODUCTS/[product]/[component]

---

# Classification Examples

## Example 1: Authentication System

```
Component: Authentication System
Current Usage: All products
Potential Usage: All products
Generic Logic: User authentication, session management
Industry-Specific Logic: None
Configurability: Not needed (universal)
Classification: Core Asset
Rating: ★★★★★
Destination: 06_CORE_CODE/Authentication
Rationale: Universal component used by all products with no industry-specific logic
```

## Example 2: Notification System

```
Component: Notification System
Current Usage: Restaurant, Hotel, Parking
Potential Usage: All products
Generic Logic: Email, SMS, push notification delivery
Industry-Specific Logic: Minimal (notification templates)
Configurability: High (templates, providers)
Classification: Shared Engine
Rating: ★★★★☆
Destination: 07_SHARED_ENGINES/NotificationEngine
Rationale: Widely applicable component used by many products with minimal industry-specific logic
```

## Example 3: Point of Sale (POS)

```
Component: Point of Sale (POS)
Current Usage: Restaurant ERP only
Potential Usage: Restaurant ERP only
Generic Logic: Order processing
Industry-Specific Logic: Restaurant-specific order flow
Configurability: Low (tied to restaurant workflow)
Classification: Product Asset
Rating: ★★☆☆☆
Destination: PRODUCTS/RESTAURANT_ERP/
Rationale: Domain-specific component used by one product with industry-specific logic
```

---

# Classification Governance

## Classification Review

### Review Frequency
- **Initial Classification**: During analysis phase
- **Reclassification**: When component usage changes significantly
- **Periodic Review**: Annually

### Review Triggers
- Component usage changes (new products start using it)
- Component logic changes (becomes more/less generic)
- New components are added
- EBP platform evolves

## Classification Appeals

### Appeal Process
1. **Submit Appeal**: Document appeal with rationale
2. **Review Committee**: Classification review committee reviews
3. **Decision**: Committee makes decision
4. **Implementation**: Decision is implemented

### Appeal Criteria
- New evidence about component usage
- New evidence about component logic
- Changes in EBP platform
- Changes in business needs

---

# Document End

**Document ID:** ESAMF-CLASSIFICATION-001

**Version:** 1.0
