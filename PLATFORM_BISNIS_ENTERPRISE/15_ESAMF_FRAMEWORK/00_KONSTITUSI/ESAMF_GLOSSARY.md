# ESAMF Glossary

**Document ID:** ESAMF-CONSTITUTION-004

**Version:** 1.0

**Purpose:** Define terminology used in the Enterprise Software Asset Management Framework

---

# A

**Asset**
Any software component that has value to the enterprise, including code, documentation, tests, and configuration.

**Asset Inventory**
A comprehensive catalog of all software assets in the EBP ecosystem, including their classification, status, and usage.

**Audit**
The systematic examination of a software repository to understand its structure, dependencies, and components.

---

# B

**Big-Bang Migration**
A migration approach where the entire system is migrated and deployed at once. ESAMF discourages this approach in favor of incremental migration.

**Business Domain**
A specific industry or business area, such as Hospitality, Tourism, Retail, Finance, Education, Culture, or Public Service.

**Business Logic**
The rules and processes that define how a business operates, embedded in software code.

---

# C

**Classification**
The process of categorizing software components based on their reusability and scope (Core Asset, Shared Engine, Product Asset).

**Component**
A self-contained piece of software that performs a specific function, such as Authentication, Notification, or Reporting.

**Component Extraction**
The process of isolating a component from its repository for migration to the EBP platform.

**Core Asset**
A universal component used by all products, with no industry-specific logic. Examples: Authentication, Authorization, Audit, Configuration.

---

# D

**Database Migration**
The process of migrating database schema and data to EBP standards.

**Dependency**
A relationship where one component relies on another to function.

**Dependency Injection**
A design pattern where dependencies are provided to a component rather than created internally, improving testability and flexibility.

---

# E

**EBP**
Enterprise Business Platform, the unified platform for building and managing software products at Petrick Software.

**EBP Core**
The set of universal components used by all EBP products (Phase 06).

**EBP Shared Engines**
The set of widely applicable engines used by many EBP products (Phase 07).

**Enterprise Knowledge Graph (EKG)**
A structured representation of all software assets and their relationships within EBP, enabling discovery and impact analysis.

**ESAMF**
Enterprise Software Asset Management Framework, the methodology for migrating existing software repositories to the EBP platform.

**Extraction**
The process of isolating a component from its repository for migration.

---

# F

**Framework**
A structured, documented methodology for achieving a specific outcome. ESAMF is a management framework.

---

# I

**Incremental Migration**
A migration approach where components are migrated one at a time, with testing and deployment at each step. ESAMF recommends this approach.

**Integration**
The process of connecting a migrated component to the EBP platform and its services.

---

# K

**Knowledge Capture**
The process of documenting business rules, domain logic, and rationale embedded in existing code.

---

# M

**Migration**
The overall process of moving a software repository or component to the EBP platform.

**Migration Plan**
A detailed plan for migrating a specific repository or component, including steps, timeline, and risk mitigation.

**Module**
A logical grouping of related functionality within a software system.

---

# P

**Platform**
A foundation for building and managing software products. EBP is Petrick Software's platform.

**Platformization**
The process of transforming a migrated component into a fully integrated EBP platform component.

**Product**
A complete software solution for a specific business domain, such as Restaurant ERP or Hotel ERP.

**Product Asset**
A domain-specific component used by a single product, with industry-specific logic. Examples: POS, Kitchen Display, Menu Management.

---

# Q

**Quality Gate**
A checkpoint in the management process where quality criteria must be met before proceeding.

---

# R

**Refactoring**
The process of improving the internal structure of code without changing its external behavior.

**Repository**
A collection of source code, documentation, and configuration for a software project.

**Reuse**
The practice of using existing software components in multiple contexts or products.

**Reusability Rating**
A score (1-5 stars) indicating how reusable a component is across products.

---

# S

**Shared Engine**
A widely applicable component used by many products, with minimal industry-specific logic. Examples: Notification, Reporting, Inventory, Pricing.

**Software Asset**
Any software component that has value to the enterprise, including code, documentation, tests, and configuration.

**Systematic Methodology**
A documented, repeatable process for achieving a specific outcome. ESAMF is a systematic methodology for software asset management.

---

# T

**Technical Debt**
The implied cost of additional rework caused by choosing an easy solution now instead of using a better approach that would take longer.

**Template**
A standardized document or code structure used to ensure consistency across asset management activities.

---

# V

**Validation**
The process of verifying that migrated code meets EBP standards and functions correctly.

---

# Classification Hierarchy

```
Enterprise Business Platform
├── Business Domain
│   ├── Hospitality
│   ├── Tourism
│   ├── Retail
│   ├── Finance
│   ├── Education
│   ├── Culture
│   └── Public Service
├── Software Product
├── Module
├── Engine
├── Component
├── Service
├── Class
└── Method
```

---

# Component Classification

```
Core Asset (★★★★★)
- Universal: Used by ALL products
- Generic: No industry-specific logic
- Examples: Authentication, Authorization, Audit, Configuration

Shared Engine (★★★★☆)
- Widely applicable: Used by MANY products
- Mostly generic: Minimal industry-specific logic
- Examples: Notification, Reporting, Inventory, Pricing

Product Asset (★★☆☆☆)
- Domain-specific: Used by ONE product
- Industry-specific: Business domain logic
- Examples: POS, Kitchen Display, Menu Management
```

---

# ESAMF Phases

```
00_CONSTITUTION
- Vision, Philosophy, Principles, Glossary

01_ANALYSIS
- Repository, Database, Source Code, Module, Dependency Analysis

02_CLASSIFICATION
- Component, Business Domain, Reusability Classification

03_EXTRACTION
- Core, Shared Engine, Product Extraction

04_REFACTORING
- Database, API, UI Refactoring

05_PLATFORMIZATION
- Platform Mapping, EBP Integration, Product Conversion

06_VALIDATION
- Validation Checklist, Testing Guide, Quality Gate

07_MIGRATION
- Repository-specific migration folders

08_REPORT
- Software Asset Inventory, Reusability Report, Platform Readiness

09_TEMPLATES
- Repository Audit, Module Audit, Database Audit, Migration Checklist

10_CASE_STUDIES
- Real-world migration examples
```

---

# EBP Phases

```
00_CONSTITUTION
- EBP Constitution, Vision, Philosophy, Core Principles

01_ARCHITECTURE
- Enterprise Architecture, Security Architecture, DevOps Architecture

02_FOUNDATION
- Business Ontology, Master Data Model

03_TECHNICAL_STANDARD
- Database Standard, Core Framework

04_ENGINE
- Engine Architecture

05_PRODUCT_MANAGEMENT
- Product Development Lifecycle

06_CORE_CODE
- Authentication, Authorization, Audit, Configuration, etc.

07_SHARED_ENGINES
- Pricing, Inventory, Accounting, Forecast, AI Engines

08_DOCUMENTATION_TEMPLATES
- API, Backend, Frontend Templates

09_IMPLEMENTATION_FOUNDATION
- Implementation foundation documents

10_PRODUCT_IMPLEMENTATION
- Product implementation documents

11_ENTERPRISE_SOFTWARE_ASSET_MIGRATION_FRAMEWORK
- ESAMF framework

12_ENTERPRISE_PRODUCT_FACTORY
- Product factory methodology

13_ENTERPRISE_DOCUMENTATION
- Enterprise documentation

14_ENTERPRISE_DEVOPS
- DevOps methodology

15_ENTERPRISE_OPERATIONS
- Operations methodology
```

---

# Document End

**Document ID:** ESAMF-CONSTITUTION-004

**Version:** 1.0
