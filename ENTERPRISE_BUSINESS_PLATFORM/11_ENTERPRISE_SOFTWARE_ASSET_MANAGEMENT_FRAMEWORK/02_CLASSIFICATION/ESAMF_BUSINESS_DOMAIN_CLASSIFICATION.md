# ESAMF Business Domain Classification

**Document ID:** ESAMF-CLASSIFICATION-002

**Version:** 1.0

**Purpose:** Define the business domain classification system for ESAMF

---

# Overview

Business Domain Classification organizes software products and components by their business domain, enabling better organization, documentation, and reuse across the EBP ecosystem.

---

# Business Domain Hierarchy

## EBP Hierarchy

```
Enterprise Business Platform
│
├── Business Domain
│   ├── Hospitality
│   ├── Tourism
│   ├── Retail
│   ├── Finance
│   ├── Education
│   ├── Culture
│   └── Public Service
│
├── Software Product
├── Module
├── Engine
├── Component
├── Service
├── Class
└── Method
```

---

# Business Domains

## 1. Hospitality Domain

### Definition

The Hospitality domain encompasses businesses that provide lodging, food, and beverage services to customers.

### Characteristics

- **Customer-Facing**: Direct interaction with customers
- **Service-Oriented**: Focus on service quality
- **Time-Sensitive**: Real-time operations
- **Inventory-Dependent**: Food and beverage inventory
- **Staff-Intensive**: Requires significant staffing

### Products

```
Hospitality Domain
├── Restaurant ERP
│   ├── Point of Sale (POS)
│   ├── Kitchen Display System
│   ├── Menu Management
│   ├── Table Management
│   ├── Reservation System
│   └── Inventory Management
│
└── Hotel ERP
    ├── Room Management
    ├── Booking System
    ├── Check-in/Check-out
    ├── Housekeeping
    ├── Guest Services
    └── Billing
```

### Shared Components

```
Hospitality Shared Components:
- Reservation System (Shared Engine)
- Inventory Management (Shared Engine)
- Billing System (Shared Engine)
- Customer Management (Shared Engine)
```

### Domain-Specific Components

```
Restaurant-Specific:
- Kitchen Display System
- Menu Management
- Table Management

Hotel-Specific:
- Room Management
- Housekeeping
- Guest Services
```

---

## 2. Tourism Domain

### Definition

The Tourism domain encompasses businesses that provide travel, tourism, and destination services to customers.

### Characteristics

- **Experience-Oriented**: Focus on customer experience
- **Location-Based**: Tied to specific locations
- **Seasonal**: Varies by season
- **Multi-Vendor**: Involves multiple vendors
- **Review-Dependent**: Relies on customer reviews

### Products

```
Tourism Domain
└── MyWisata
    ├── Destination Management
    ├── Tour Packages
    ├── Booking System
    ├── Review System
    ├── Guide Management
    └── Payment Processing
```

### Shared Components

```
Tourism Shared Components:
- Booking System (Shared Engine)
- Review System (Shared Engine)
- Payment Processing (Shared Engine)
- Location Services (Shared Engine)
```

### Domain-Specific Components

```
Tourism-Specific:
- Destination Management
- Tour Packages
- Guide Management
```

---

## 3. Retail Domain

### Definition

The Retail domain encompasses businesses that sell goods directly to consumers.

### Characteristics

- **Inventory-Heavy**: Significant inventory management
- **Sales-Focused**: Focus on sales and revenue
- **Customer-Centric**: Customer relationship management
- **Promotion-Driven**: Frequent promotions and discounts
- **Multi-Channel**: Online and offline sales

### Products

```
Retail Domain
└── Panglong ERP
    ├── Point of Sale (POS)
    ├── Inventory Management
    ├── Supplier Management
    ├── Customer Management
    ├── Sales Reporting
    └── Promotion Management
```

### Shared Components

```
Retail Shared Components:
- Point of Sale (Shared Engine)
- Inventory Management (Shared Engine)
- Supplier Management (Shared Engine)
- Customer Management (Shared Engine)
- Sales Reporting (Shared Engine)
```

### Domain-Specific Components

```
Retail-Specific:
- Promotion Management
- Supplier Management
```

---

## 4. Finance Domain

### Definition

The Finance domain encompasses businesses that provide financial services, investment management, and financial planning.

### Characteristics

- **Data-Intensive**: Significant data processing
- **Security-Critical**: High security requirements
- **Regulated**: Subject to financial regulations
- **Real-Time**: Real-time data processing
- **Analytics-Heavy**: Extensive analytics and reporting

### Products

```
Finance Domain
└── Saham
    ├── Portfolio Management
    ├── Stock Trading
    ├── Market Analysis
    ├── Risk Management
    ├── Reporting
    └── Compliance
```

### Shared Components

```
Finance Shared Components:
- Portfolio Management (Shared Engine)
- Risk Management (Shared Engine)
- Reporting (Shared Engine)
- Compliance (Shared Engine)
```

### Domain-Specific Components

```
Finance-Specific:
- Stock Trading
- Market Analysis
```

---

## 5. Education Domain

### Definition

The Education domain encompasses businesses that provide educational services, training, and learning management.

### Characteristics

- **Content-Heavy**: Significant content management
- **User-Centric**: Focus on learner experience
- **Progress-Tracking**: Track learner progress
- **Assessment-Based**: Regular assessments and testing
- **Multi-Role**: Multiple user roles (student, teacher, admin)

### Products

```
Education Domain
└── Pelajaran
    ├── Course Management
    ├── Learning Management
    ├── Assessment System
    ├── Progress Tracking
    ├── Communication
    └── Reporting
```

### Shared Components

```
Education Shared Components:
- Course Management (Shared Engine)
- Assessment System (Shared Engine)
- Progress Tracking (Shared Engine)
- Reporting (Shared Engine)
```

### Domain-Specific Components

```
Education-Specific:
- Learning Management
- Communication
```

---

## 6. Culture Domain

### Definition

The Culture domain encompasses businesses and organizations that preserve, promote, and manage cultural heritage and genealogy.

### Characteristics

- **Heritage-Focused**: Focus on cultural preservation
- **Genealogy-Based**: Family and lineage tracking
- **Community-Oriented**: Community engagement
- **Documentation-Heavy**: Extensive documentation
- **Relationship-Centric**: Focus on relationships

### Products

```
Culture Domain
└── Tarombo Digital
    ├── Genealogy Management
    ├── Family Tree
    ├── Cultural Documentation
    ├── Community Management
    ├── Event Management
    └── Reporting
```

### Shared Components

```
Culture Shared Components:
- Genealogy Management (Shared Engine)
- Community Management (Shared Engine)
- Event Management (Shared Engine)
- Reporting (Shared Engine)
```

### Domain-Specific Components

```
Culture-Specific:
- Family Tree
- Cultural Documentation
```

---

## 7. Public Service Domain

### Definition

The Public Service domain encompasses government and public sector organizations that provide services to citizens.

### Characteristics

- **Citizen-Focused**: Focus on citizen services
- **Regulated**: Subject to government regulations
- **Security-Critical**: High security requirements
- **Transparency-Required**: Requires transparency
- **Multi-Agency**: Involves multiple agencies

### Products

```
Public Service Domain
└── Visitor Management
    ├── Visitor Registration
    ├── Access Control
    ├── Queue Management
    ├── Reporting
    ├── Integration
    └── Compliance
```

### Shared Components

```
Public Service Shared Components:
- Access Control (Shared Engine)
- Queue Management (Shared Engine)
- Reporting (Shared Engine)
- Compliance (Shared Engine)
```

### Domain-Specific Components

```
Public Service-Specific:
- Visitor Registration
- Integration
```

---

# Cross-Domain Components

## Universal Components

These components are used across ALL domains:

```
Universal Components (Core Assets):
- Authentication
- Authorization (RBAC)
- Audit Trail
- Configuration Management
- Error Handling
- Logging
- User Management
```

## Widely Used Components

These components are used across MANY domains:

```
Widely Used Components (Shared Engines):
- Notification System
- Reporting System
- Inventory Management
- Pricing Engine
- Payment Processing
- File Management
- Search Engine
```

## Domain-Specific Components

These components are specific to individual domains:

```
Domain-Specific Components (Product Assets):
- Kitchen Display System (Hospitality)
- Room Management (Hospitality)
- Tour Packages (Tourism)
- Stock Trading (Finance)
- Learning Management (Education)
- Family Tree (Culture)
- Visitor Registration (Public Service)
```

---

# Domain Classification Process

## Step 1: Identify Product

1. **Product Name**: [Name of product]
2. **Product Purpose**: [What the product does]
3. **Target Market**: [Who the product serves]
4. **Industry**: [Which industry]

## Step 2: Analyze Characteristics

1. **Primary Focus**: [What is the primary focus]
2. **Key Features**: [What are the key features]
3. **Target Users**: [Who are the target users]
4. **Business Model**: [What is the business model]

## Step 3: Compare with Domains

1. **Domain Match**: [Which domain best matches]
2. **Match Criteria**: [Why it matches this domain]
3. **Mismatch Criteria**: [Why it doesn't match other domains]
4. **Cross-Domain**: [Does it span multiple domains]

## Step 4: Assign Domain

1. **Primary Domain**: [Primary domain assignment]
2. **Secondary Domains**: [Secondary domain assignments, if any]
3. **Domain Rationale**: [Rationale for domain assignment]

## Step 5: Document Classification

1. **Document Domain**: [Document domain assignment]
2. **Document Rationale**: [Document classification rationale]
3. **Document Components**: [Document domain-specific components]
4. **Document Shared Components**: [Document shared components]

---

# Domain Mapping

## Repository to Domain Mapping

```
restoran → Hospitality Domain
├── Restaurant ERP
└── Hospitality

mywisata → Tourism Domain
├── MyWisata
└── Tourism

panglong → Retail Domain
├── Panglong ERP
└── Retail

saham → Finance Domain
├── Saham
└── Finance

pelajaran → Education Domain
├── Pelajaran
└── Education

tarombo → Culture Domain
├── Tarombo Digital
└── Culture

kewer → Public Service Domain
├── Visitor Management
└── Public Service
```

## Component to Domain Mapping

```
Authentication → Universal (All Domains)
Authorization → Universal (All Domains)
Audit Trail → Universal (All Domains)

Notification System → Widely Used (All Domains)
Reporting System → Widely Used (All Domains)
Inventory Management → Widely Used (Hospitality, Retail)

Kitchen Display System → Hospitality Domain
Room Management → Hospitality Domain
Tour Packages → Tourism Domain
Stock Trading → Finance Domain
Learning Management → Education Domain
Family Tree → Culture Domain
Visitor Registration → Public Service Domain
```

---

# Domain Collaboration

## Cross-Domain Reuse

### Hospitality + Tourism
- Shared: Reservation System
- Shared: Customer Management
- Shared: Billing System

### Retail + Hospitality
- Shared: Inventory Management
- Shared: Point of Sale
- Shared: Supplier Management

### Finance + Retail
- Shared: Payment Processing
- Shared: Reporting
- Shared: Risk Management

### Education + Culture
- Shared: Content Management
- Shared: Community Management
- Shared: Event Management

## Domain-Specific Standards

Each domain may have specific standards:

```
Hospitality Domain Standards:
- Real-time operations
- High availability
- Mobile-first design

Finance Domain Standards:
- High security
- Regulatory compliance
- Real-time data processing

Education Domain Standards:
- Accessibility standards
- Progress tracking
- Assessment standards
```

---

# Document End

**Document ID:** ESAMF-CLASSIFICATION-002

**Version:** 1.0
