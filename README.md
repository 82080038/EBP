# Enterprise Business Platform (EBP)

**A Software Company Platform for Building Enterprise Applications**

---

## About EBP

EBP is not just an application. EBP is a **software company platform** designed to build and manage multiple enterprise products with shared foundation and independent domain databases.

### Philosophy

```

ONE PLATFORM

+

MANY PRODUCTS

```

### Vision

To build a sustainable software company platform that enables rapid development of enterprise-grade applications across multiple industries.

### Mission

- Provide a robust foundation for enterprise applications
- Enable asset reuse across products
- Maintain high quality and security standards
- Support scalable multi-tenant architecture
- Facilitate long-term organizational growth

---

## Platform Architecture

### Core Platform

The core platform provides shared foundation used by all products:

- **Identity Management**: Users, Roles, Permissions, RBAC
- **Organization**: Tenants, Companies, Branches, Departments
- **Security**: Audit Trail, Login History, Security Events
- **Workflow**: Workflow Engine, Approval System
- **Notification**: Email, SMS, Push Notifications
- **File Management**: File Upload, Document Storage
- **Master Data**: Countries, Currencies, Tax Codes, Units
- **Business Partners**: Shared partner management

### Product Ecosystem

Each product has its own domain-specific database and business logic:

- **Restaurant ERP**: Menu, Orders, Kitchen, Inventory, Accounting
- **Hotel ERP**: Rooms, Reservations, Check-in/out, Housekeeping
- **Parking System**: Slots, Vehicles, Tickets, Rates
- **Agriculture ERP**: Farms, Harvests, Production, Warehouses
- **Legal System**: Clients, Cases, Documents, Billing

---

## Project Structure

```

EBP/

├── ENTERPRISE_BUSINESS_PLATFORM/

│   ├── 00_EBP_MANIFESTO/              # Constitution, Vision, Philosophy
│   ├── 01_ENTERPRISE_ARCHITECTURE/     # Architecture Overview
│   ├── 02_BUSINESS_FOUNDATION/         # Business Ontology, Master Data
│   ├── 03_TECHNICAL_STANDARD/         # Database Standard, Core Framework
│   ├── 04_BUSINESS_ENGINE/             # Engine Architecture
│   ├── 05_SECURITY_ARCHITECTURE/       # Security Architecture
│   ├── 06_DEVOPS_ARCHITECTURE/         # DevOps Architecture
│   ├── 07_PRODUCT_MANAGEMENT/         # Product Development Lifecycle
│   ├── 08_PRODUCT_BLUEPRINT/           # Restaurant ERP Blueprint
│   ├── 09_DATABASE_DESIGN/             # Database Design & ERD
│   ├── 10_API_DESIGN/                  # API Specification
│   ├── 11_APPLICATION_ARCHITECTURE/    # Backend & Frontend Architecture
│   ├── EBP_PLATFORM_DIRECTORY_STRUCTURE.md
│   ├── EBP_PLATFORM_MIGRATION_PLAN.md
│   └── EBP_DATABASE_ARCHITECTURE.md

│
└── ebp-restaurant-backend/              # Restaurant ERP Backend Implementation

    ├── config/                          # Database Configuration
    ├── core/                            # Core Framework (JWT, Response, Router)
    │   ├── Engines/                     # Business Engines (Stock, Kitchen, Accounting)
    │   └── Middleware/                  # Auth, Permission, Tenant Middleware
    ├── modules/                         # Business Modules
    │   ├── Auth/                         # Authentication Module
    │   └── Sales/                        # Sales Module (Orders, POS)
    ├── routes/                          # API Routes
    ├── public/                          # Public Entry Point
    └── README.md

```

---

## Documentation

### Foundation Documents

| Document | Description |
|----------|-------------|
| [EBP_CONSTITUTION.md](ENTERPRISE_BUSINESS_PLATFORM/00_EBP_MANIFESTO/EBP_CONSTITUTION.md) | Platform constitution and fundamental principles |
| [EBP_VISION_MISSION.md](ENTERPRISE_BUSINESS_PLATFORM/00_EBP_MANIFESTO/EBP_VISION_MISSION.md) | Vision and mission statements |
| [EBP_PHILOSOPHY.md](ENTERPRISE_BUSINESS_PLATFORM/00_EBP_MANIFESTO/EBP_PHILOSOPHY.md) | Platform philosophy and culture |
| [EBP_CORE_PRINCIPLES.md](ENTERPRISE_BUSINESS_PLATFORM/00_EBP_MANIFESTO/EBP_CORE_PRINCIPLES.md) | Core principles that must be followed |

### Architecture Documents

| Document | Description |
|----------|-------------|
| [EBP_ENTERPRISE_ARCHITECTURE.md](ENTERPRISE_BUSINESS_PLATFORM/01_ENTERPRISE_ARCHITECTURE/EBP_ENTERPRISE_ARCHITECTURE.md) | Enterprise architecture overview |
| [EBP_SECURITY_ARCHITECTURE.md](ENTERPRISE_BUSINESS_PLATFORM/05_SECURITY_ARCHITECTURE/EBP_SECURITY_ARCHITECTURE.md) | Security architecture and standards |
| [EBP_DEVOPS_ARCHITECTURE.md](ENTERPRISE_BUSINESS_PLATFORM/06_DEVOPS_ARCHITECTURE/EBP_DEVOPS_ARCHITECTURE.md) | DevOps and deployment architecture |

### Technical Documents

| Document | Description |
|----------|-------------|
| [EBP_DATABASE_STANDARD.md](ENTERPRISE_BUSINESS_PLATFORM/03_TECHNICAL_STANDARD/EBP_DATABASE_STANDARD.md) | Database design standards |
| [EBP_CORE_FRAMEWORK.md](ENTERPRISE_BUSINESS_PLATFORM/03_TECHNICAL_STANDARD/EBP_CORE_FRAMEWORK.md) | Core framework blueprint |
| [EBP_ENGINE_ARCHITECTURE.md](ENTERPRISE_BUSINESS_PLATFORM/04_BUSINESS_ENGINE/EBP_ENGINE_ARCHITECTURE.md) | Business engine architecture |

### Platform Strategy Documents

| Document | Description |
|----------|-------------|
| [EBP_PLATFORM_DIRECTORY_STRUCTURE.md](ENTERPRISE_BUSINESS_PLATFORM/EBP_PLATFORM_DIRECTORY_STRUCTURE.md) | Platform directory structure |
| [EBP_PLATFORM_MIGRATION_PLAN.md](ENTERPRISE_BUSINESS_PLATFORM/EBP_PLATFORM_MIGRATION_PLAN.md) | Migration strategy and plan |
| [EBP_DATABASE_ARCHITECTURE.md](ENTERPRISE_BUSINESS_PLATFORM/EBP_DATABASE_ARCHITECTURE.md) | Database architecture strategy |

### Restaurant ERP Documents

| Document | Description |
|----------|-------------|
| [EBP_PRODUCT_RESTAURANT_CAFE_ERP.md](ENTERPRISE_BUSINESS_PLATFORM/08_PRODUCT_BLUEPRINT/EBP_PRODUCT_RESTAURANT_CAFE_ERP.md) | Restaurant ERP product blueprint |
| [EBP_RESTAURANT_CAFE_BUSINESS_PROCESS.md](ENTERPRISE_BUSINESS_PLATFORM/08_PRODUCT_BLUEPRINT/EBP_RESTAURANT_CAFE_BUSINESS_PROCESS.md) | Business process documentation |
| [EBP_RESTAURANT_CAFE_MODULE_SPECIFICATION.md](ENTERPRISE_BUSINESS_PLATFORM/08_PRODUCT_BLUEPRINT/EBP_RESTAURANT_CAFE_MODULE_SPECIFICATION.md) | Module specifications |
| [EBP_RESTAURANT_CAFE_DATABASE_DESIGN.md](ENTERPRISE_BUSINESS_PLATFORM/09_DATABASE_DESIGN/EBP_RESTAURANT_CAFE_DATABASE_DESIGN.md) | Database design documentation |
| [EBP_RESTAURANT_CAFE_ERD.md](ENTERPRISE_BUSINESS_PLATFORM/09_DATABASE_DESIGN/EBP_RESTAURANT_CAFE_ERD.md) | Entity Relationship Diagram |
| [EBP_RESTAURANT_CAFE_MYSQL_SCHEMA.sql](ENTERPRISE_BUSINESS_PLATFORM/09_DATABASE_DESIGN/EBP_RESTAURANT_CAFE_MYSQL_SCHEMA.sql) | Complete MySQL schema |
| [EBP_RESTAURANT_CAFE_API_SPECIFICATION.md](ENTERPRISE_BUSINESS_PLATFORM/10_API_DESIGN/EBP_RESTAURANT_CAFE_API_SPECIFICATION.md) | API specification |
| [EBP_RESTAURANT_CAFE_BACKEND_ARCHITECTURE.md](ENTERPRISE_BUSINESS_PLATFORM/11_APPLICATION_ARCHITECTURE/EBP_RESTAURANT_CAFE_BACKEND_ARCHITECTURE.md) | Backend architecture |
| [EBP_RESTAURANT_CAFE_FRONTEND_ARCHITECTURE.md](ENTERPRISE_BUSINESS_PLATFORM/11_APPLICATION_ARCHITECTURE/EBP_RESTAURANT_CAFE_FRONTEND_ARCHITECTURE.md) | Frontend architecture |

---

## Technology Stack

### Backend

- **Language**: PHP 8.x
- **Database**: MySQL 8.x
- **Architecture**: REST API
- **Authentication**: JWT
- **Pattern**: Service Repository Pattern
- **Multi-tenant**: Supported

### Frontend

- **Language**: HTML5, CSS3, JavaScript
- **Framework**: jQuery, AJAX
- **UI Library**: Bootstrap
- **Architecture**: AJAX-based Application

### DevOps

- **Version Control**: Git
- **Repository**: GitHub
- **Containerization**: Docker (planned)
- **CI/CD**: GitHub Actions (planned)

---

## Database Architecture

### Core Database (ebp_core)

Shared foundation used by all products:

- Identity Management (users, roles, permissions)
- Organization (tenants, companies, branches)
- Security (audit_logs, login_history, security_events)
- Workflow (workflow_definitions, approval_requests)
- Notification (notifications, email_queue, sms_queue)
- File Management (files, documents, attachments)
- Master Data (countries, currencies, tax_codes, units)
- Business Partners (business_partners)

### Product Databases

Each product has its own domain-specific database:

- **ebp_restaurant**: Menu, Orders, Kitchen, Inventory, Accounting
- **ebp_hotel**: Rooms, Reservations, Check-in/out, Housekeeping
- **ebp_parking**: Slots, Vehicles, Tickets, Rates
- **ebp_agriculture**: Farms, Harvests, Production, Warehouses
- **ebp_legal**: Clients, Cases, Documents, Billing

### Multi-Tenant Strategy

- **Model A**: Database per tenant (Enterprise)
- **Model B**: Shared database with tenant_id (Startup)
- **Model C**: Hybrid (SaaS Platform)

---

## Getting Started

### Prerequisites

- PHP 8.x or higher
- MySQL 8.x or higher
- Composer (for dependency management)
- Git

### Installation

1. **Clone the repository**

```bash
git clone https://github.com/82080038/EBP.git
cd EBP
```

2. **Import Core Database Schema**

```bash
mysql -u root -p < ENTERPRISE_BUSINESS_PLATFORM/09_DATABASE_DESIGN/EBP_RESTAURANT_CAFE_MYSQL_SCHEMA.sql
```

3. **Configure Database Connection**

Edit `ebp-restaurant-backend/config/database.php`:

```php
private $host = "localhost";
private $dbname = "ebp_restaurant_erp";
private $username = "root";
private $password = "";
```

4. **Configure Web Server**

Point your web server to `ebp-restaurant-backend/public/` directory.

### API Endpoints

#### Authentication

**POST** `/api/v1/auth/login`

```json
{
  "username": "admin",
  "password": "password"
}
```

#### Create Order

**POST** `/api/v1/orders`

**Headers:**
```
Authorization: Bearer {access_token}
```

**Request Body:**
```json
{
  "customer_id": null,
  "items": [
    {
      "menu_id": 10,
      "qty": 2,
      "price": 25000
    }
  ]
}
```

---

## Enterprise Features

The Restaurant ERP backend includes enterprise-ready features:

✅ **JWT Authentication** - Token-based authentication with expiration
✅ **RBAC Permission Check** - Role-based access control
✅ **Tenant Isolation** - Multi-tenant data separation
✅ **Database Transaction** - ACID compliance with rollback on error
✅ **Stock Engine** - Automatic inventory deduction from recipe
✅ **Kitchen Queue** - Kitchen order creation
✅ **Accounting Journal** - Automatic journal entry generation
✅ **Audit Trail** - Complete activity logging

### Order Transaction Flow

```
Request
  ↓
JWT Authentication
  ↓
Permission Check (ORDER_CREATE)
  ↓
Validation
  ↓
BEGIN TRANSACTION
  ↓
Create Order
  ↓
Create Order Details
  ↓
Stock Engine (Deduct Inventory)
  ↓
Kitchen Engine (Create Kitchen Order)
  ↓
Accounting Engine (Create Journal)
  ↓
Audit Trail (Log Activity)
  ↓
COMMIT TRANSACTION
  ↓
Response
```

---

## Development Roadmap

### Phase 1: EBP Foundation (3 months)

- Authentication Module
- Permission Module
- Tenant Module
- Audit Module
- Database Layer
- API Framework
- Logging System
- File Management

### Phase 2: Restaurant MVP (2 months)

- Menu Management
- POS System
- Kitchen Display
- Basic Inventory
- Basic Reporting

### Phase 3: Restaurant Enterprise (3 months)

- Advanced Inventory
- Accounting Integration
- AI Features
- Multi-Outlet
- Advanced Reporting

### Phase 4: Second Product (4 months)

- Hotel ERP or Parking System
- Product Analysis
- Product Development
- Product Launch

### Phase 5: Platform Enhancement (Ongoing)

- Performance Optimization
- Security Enhancement
- Feature Addition
- Developer Experience

---

## Contributing

EBP follows professional software development practices:

1. **Follow Core Principles** - All changes must align with EBP_CORE_PRINCIPLES.md
2. **Separate Core from Product** - Core changes require architecture review
3. **Use Proper Branching** - feature branches for new features
4. **Write Tests** - Unit tests for core components
5. **Document Changes** - Update relevant documentation
6. **Follow Coding Standards** - Adhere to EBP_DATABASE_STANDARD.md

### Commit Convention

Format: `[type]: subject`

Types:
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation
- `style`: Code style
- `refactor`: Code refactoring
- `test`: Testing
- `chore`: Maintenance

Examples:
```
feat(core): add JWT authentication
feat(restaurant): add POS order creation
fix(core): resolve tenant isolation issue
docs(core): update API documentation
```

---

## License

This project is proprietary software. All rights reserved.

---

## Contact

- **Repository**: https://github.com/82080038/EBP
- **Platform**: Enterprise Business Platform (EBP)
- **First Product**: Restaurant & Cafe ERP

---

## Acknowledgments

EBP is built with the vision of creating a sustainable software company platform that enables rapid development of enterprise-grade applications across multiple industries.

---

**Version**: 1.0

**Last Updated**: 2026-07-01

**Status**: Foundation Complete - Restaurant ERP in Development
