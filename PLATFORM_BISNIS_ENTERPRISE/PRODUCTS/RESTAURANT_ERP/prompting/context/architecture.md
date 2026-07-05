# RESTAURANT_ERP Architecture

## System Overview

RESTAURANT_ERP is a comprehensive restaurant management system built on the Enterprise Business Platform (EBP). It follows a multi-tenant, service-oriented architecture with REST API design.

## Architecture Layers

### 1. Presentation Layer
- **Frontend**: HTML5, CSS3, JavaScript, jQuery, Bootstrap
- **Mobile App**: Responsive web application
- **Kiosk App**: Touch-optimized interface
- **Admin Dashboard**: Web-based management interface

### 2. API Layer
- **REST API**: JSON-based RESTful API
- **Authentication**: JWT token-based authentication
- **Middleware Stack**: Auth, Tenant, Permission, Validation, Rate Limit
- **Response Format**: Standardized JSON responses

### 3. Service Layer
- **Business Logic**: Service classes implement business rules
- **Transaction Management**: Database transactions for data consistency
- **Audit Logging**: Complete audit trail for all operations
- **Business Engines**: Stock, Kitchen, Accounting engines

### 4. Data Access Layer
- **Repository Pattern**: Repository classes for database operations
- **Database**: MySQL 8.x
- **Connection Pooling**: Singleton Database instance
- **Query Optimization**: Prepared statements, indexes

### 5. Data Layer
- **Database Schema**: 78 tables across 10 migrations
- **Multi-tenant**: Tenant isolation via tenant_id
- **Audit Fields**: created_at, updated_at, deleted_at, created_by, updated_by
- **Soft Delete**: Logical deletion via deleted_at

## Module Architecture

### Module Structure
```
BACKEND/modules/[ModuleName]/
├── Controllers/      # HTTP request handling
├── Services/         # Business logic
├── Repositories/     # Database operations
├── Models/          # Data models
└── routes/          # Route definitions
```

### Core Components
```
BACKEND/core/
├── Database.php           # Database connection manager
├── JWT.php               # Authentication token handler
├── Response.php          # API response handler
├── Router.php            # Route dispatcher
├── Transaction.php       # Transaction manager
├── Audit.php             # Audit logging
├── Messages.php          # Message translations
├── Middleware/           # Middleware stack
│   ├── AuthMiddleware.php
│   ├── TenantMiddleware.php
│   ├── PermissionMiddleware.php
│   ├── ValidationMiddleware.php
│   ├── RateLimitMiddleware.php
│   └── ErrorHandler.php
└── Engines/              # Business engines
    ├── StockEngine.php
    ├── KitchenEngine.php
    └── AccountingEngine.php
```

## Multi-Tenant Architecture

### Tenant Isolation
- **Database Level**: tenant_id column in all tenant-specific tables
- **Application Level**: TenantMiddleware enforces tenant context
- **API Level**: All queries include tenant_id filter
- **Session Level**: tenant_id stored in session

### Tenant Data Flow
```
Request → TenantMiddleware → Set tenant_id in session → 
Service layer uses tenant_id → Repository filters by tenant_id → 
Response includes only tenant data
```

## Security Architecture

### Authentication
- **JWT Tokens**: Stateless token-based authentication
- **Token Lifecycle**: Generation, validation, expiration
- **Refresh Mechanism**: Token refresh on expiration
- **Multi-device Support**: Multiple active sessions per user

### Authorization
- **RBAC**: Role-based access control
- **Permissions**: Granular permission system
- **PermissionMiddleware**: Enforces permission checks
- **Permission Inheritance**: Role-based permission inheritance

### Security Controls
- **Input Validation**: ValidationMiddleware for all inputs
- **SQL Injection Prevention**: PDO prepared statements
- **XSS Prevention**: Output escaping
- **CSRF Protection**: Token-based CSRF protection
- **Rate Limiting**: RateLimitMiddleware for API endpoints
- **Audit Logging**: Complete audit trail for security events

## Data Flow Architecture

### Request Flow
```
HTTP Request → Router → Middleware Stack → Controller → 
Service → Repository → Database → Response
```

### Transaction Flow
```
BEGIN TRANSACTION → Service Logic → Multiple Operations → 
COMMIT/ROLLBACK → Audit Log → Response
```

### Business Engine Integration
```
Service → Stock Engine → Inventory Updates
Service → Kitchen Engine → Kitchen Orders
Service → Accounting Engine → Journal Entries
```

## Database Architecture

### Schema Organization
- **Migrations**: 10 migration files (001-010)
- **Tables**: 78 tables across all modules
- **Indexes**: Optimized indexes for query performance
- **Foreign Keys**: Referential integrity
- **Constraints**: Data validation at database level

### Key Table Categories
- **Tenant Management**: tenants, tenant_configurations
- **User Management**: users, roles, permissions, user_roles
- **Menu Management**: menu_categories, menu_products, recipes
- **Order Management**: orders, order_details, payments
- **Inventory Management**: inventory, suppliers, purchase_orders
- **Kitchen Operations**: kitchen_orders, kitchen_queue
- **Reservation Management**: reservations, tables
- **Customer Management**: customers, customer_profiles
- **Analytics**: sales_reports, performance_metrics
- **AI Infrastructure**: ai_models, ai_predictions

## API Architecture

### API Design Principles
- **RESTful**: Follow REST conventions
- **Versioned**: /api/v1/ prefix
- **Standardized**: Consistent response format
- **Stateless**: No server-side session state
- **Resource-based**: URL structure based on resources

### Endpoint Structure
```
GET    /api/v1/[resource]           # List all
GET    /api/v1/[resource]/:id       # Get one
POST   /api/v1/[resource]           # Create
PUT    /api/v1/[resource]/:id       # Update
DELETE /api/v1/[resource]/:id       # Delete
```

### Response Format
```json
{
    "success": true,
    "message": "Success message",
    "data": { /* response data */ }
}
```

### Error Response Format
```json
{
    "success": false,
    "message": "Error message",
    "errors": { /* validation errors */ }
}
```

## Integration Architecture

### External Integrations
- **Payment Processors**: Stripe, Midtrans, etc.
- **Delivery Platforms**: GoFood, GrabFood, etc.
- **POS Systems**: Third-party POS integration
- **Accounting Systems**: External accounting software
- **Communication**: WhatsApp, Email, SMS

### Integration Patterns
- **API Connectors**: REST API integrations
- **Webhooks**: Real-time event notifications
- **Data Synchronization**: Batch data sync
- **Message Queues**: Asynchronous processing

## Performance Architecture

### Caching Strategy
- **Query Caching**: Database query result caching
- **API Caching**: Response caching for GET endpoints
- **Session Caching**: Session data caching
- **Static Asset Caching**: Frontend asset caching

### Optimization Techniques
- **Database Indexes**: Optimized indexes for frequent queries
- **Query Optimization**: Efficient SQL queries
- **Pagination**: Large result set pagination
- **Lazy Loading**: On-demand data loading
- **Connection Pooling**: Database connection reuse

## Scalability Architecture

### Horizontal Scaling
- **Stateless API**: Easy horizontal scaling
- **Load Balancing**: Multiple API server instances
- **Database Replication**: Read replicas for scaling
- **CDN**: Static content delivery

### Vertical Scaling
- **Resource Optimization**: Efficient resource usage
- **Database Optimization**: Query and schema optimization
- **Caching**: Reduce database load
- **Async Processing**: Background job processing

## Monitoring Architecture

### Logging
- **Application Logs**: Error and info logging
- **Audit Logs**: Complete audit trail
- **Security Logs**: Security event logging
- **Performance Logs**: Performance metrics

### Monitoring
- **Uptime Monitoring**: Service availability
- **Performance Monitoring**: Response times, throughput
- **Error Monitoring**: Error rates and patterns
- **Resource Monitoring**: CPU, memory, disk usage

## Deployment Architecture

### Environments
- **Development**: Local development environment
- **Staging**: Pre-production testing environment
- **Production**: Live production environment

### Deployment Strategy
- **Manual Deployment**: For small changes
- **Automated Deployment**: For large changes
- **Blue-Green Deployment**: For critical changes
- **Canary Deployment**: For experimental features

---

**Version**: 1.0  
**Last Updated**: 2026-07-05
