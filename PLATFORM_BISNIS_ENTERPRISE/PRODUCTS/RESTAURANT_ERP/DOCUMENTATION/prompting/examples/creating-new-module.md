# Example: Creating a New Module

## Scenario

Create a new module for managing restaurant reservations with full CRUD operations, authentication, authorization, and testing.

## REASONS Canvas Prompt

### R - Requirements

**Problem**: Restaurant needs a reservation management system to handle table bookings, track availability, and reduce no-shows.

**Definition of Done**:
- Reservation CRUD operations implemented
- Table availability checking
- No-show prevention features
- Guest preference tracking
- Authentication and authorization
- Unit, integration, and E2E tests
- Code coverage >80%

### E - Entities

**Domain Entities**:
- Reservations (reservation_id, customer_id, table_id, date, time, party_size, status)
- Tables (table_id, table_number, capacity, section, status)
- Customers (customer_id, name, email, phone, preferences)

**Relationships**:
- Reservation belongs to Customer
- Reservation belongs to Table
- Customer has many Reservations
- Table has many Reservations

### A - Approach

**Strategy**:
1. Create database migration for reservations and tables
2. Implement Model classes
3. Implement Repository classes
4. Implement Service classes with business logic
5. Implement Controller classes
6. Define API routes
7. Add authentication and authorization
8. Create comprehensive tests

### S - Structure

**Module Structure**:
```
BACKEND/modules/Reservation/
├── Controllers/
│   └── ReservationController.php
├── Services/
│   └── ReservationService.php
├── Repositories/
│   └── ReservationRepository.php
├── Models/
│   └── Reservation.php
└── routes/
    └── reservation.php
```

### O - Operations

**Implementation Steps**:
1. Create migration file: `DATABASE/MIGRATION_011_RESERVATION_MANAGEMENT.sql`
2. Implement Reservation model with validation
3. Implement ReservationRepository with CRUD methods
4. Implement ReservationService with business logic:
   - Check table availability
   - Prevent double bookings
   - Calculate no-show probability
   - Send confirmation reminders
5. Implement ReservationController with HTTP methods
6. Define routes with authentication and authorization
7. Create unit tests for service methods
8. Create integration tests for API endpoints
9. Create E2E tests for user flows

### N - Norms

**Standards**:
- Follow PHP 8.x and PSR-12 coding standards
- Use Indonesian as primary language, English as secondary
- Implement JWT authentication
- Implement RBAC authorization
- Use Database::getInstance() for connections
- Use Transaction for data consistency
- Add audit logging for all changes
- Include PHPDoc comments

### S - Safeguards

**Non-negotiable Rules**:
- MUST use tenant_id for multi-tenant isolation
- MUST validate all inputs
- MUST check table availability before booking
- MUST prevent double bookings
- MUST implement authentication
- MUST implement authorization
- MUST use prepared statements
- MUST include audit logging

## Implementation Prompt

```
Using the REASONS canvas above, implement the Reservation module for RESTAURANT_ERP.

Follow these steps:
1. Create database migration file following template: prompting/templates/database-migration-template.md
2. Implement Model class following coding standards: prompting/context/coding-standards.md
3. Implement Repository class with tenant isolation
4. Implement Service class with business logic and transaction handling
5. Implement Controller class with input validation
6. Define routes with authentication and authorization middleware
7. Create unit tests following template: prompting/templates/test-template.md
8. Create integration tests for all API endpoints
9. Create E2E tests for reservation booking flow

Use these context files for reference:
- Architecture: prompting/context/architecture.md
- Database Schema: prompting/context/database-schema.md
- API Conventions: prompting/context/api-conventions.md

After implementation, verify:
- All code follows PSR-12 standards
- All endpoints require authentication
- All endpoints have permission checks
- Tenant isolation is enforced
- Audit logging is implemented
- Tests achieve >80% coverage
```

## Testing Prompt

```
Create comprehensive tests for the Reservation module following the test template: prompting/templates/test-template.md

Test requirements:
1. Unit tests for ReservationService:
   - createReservation() with valid data
   - createReservation() with invalid data (validation errors)
   - createReservation() with unavailable table
   - createReservation() with double booking prevention
   - updateReservation() with valid data
   - cancelReservation()
   - checkAvailability()
   - sendConfirmationReminder()

2. Integration tests for API endpoints:
   - GET /api/v1/reservations (list all)
   - GET /api/v1/reservations/:id (get one)
   - POST /api/v1/reservations (create)
   - PUT /api/v1/reservations/:id (update)
   - DELETE /api/v1/reservations/:id (cancel)
   - GET /api/v1/reservations/available (check availability)
   - Test authentication for all endpoints
   - Test authorization for each role
   - Test tenant isolation

3. E2E tests for user flows:
   - Complete reservation booking flow
   - Reservation modification flow
   - Reservation cancellation flow
   - No-show prevention flow

Use test coverage checklist: prompting/evaluations/test-coverage-checklist.md

Target: >80% code coverage
```

## Code Review Prompt

```
Review the Reservation module implementation using the code review checklist: prompting/evaluations/code-review-checklist.md

Focus areas:
1. Security:
   - SQL injection prevention
   - Input validation
   - Authentication and authorization
   - Tenant isolation
   - Audit logging

2. Performance:
   - Database query optimization
   - Index usage
   - Pagination implementation
   - Caching strategy

3. Code Quality:
   - PSR-12 compliance
   - PHPDoc completeness
   - Error handling
   - Code organization

4. Testing:
   - Test coverage
   - Test quality
   - Test documentation

Report any issues found with severity levels (Critical, High, Medium, Low).
```

---

**Version**: 1.0  
**Last Updated**: 2026-07-05
