# Prompting System Quick Reference

## Overview

Quick reference guide for the RESTAURANT_ERP AI-driven development prompting system.

## Directory Structure

```
prompting/
├── README.md                    # System overview
├── cycles/                      # Development cycles
│   ├── 01-analysis.md          # Analysis phase
│   ├── 02-design.md            # Design phase
│   ├── 03-implementation.md    # Implementation phase
│   ├── 04-testing.md           # Testing phase
│   ├── 05-integration.md       # Integration phase
│   └── 06-deployment.md        # Deployment phase
├── templates/                   # Reusable templates
│   ├── module-template.md      # New module
│   ├── api-endpoint-template.md # API endpoint
│   ├── database-migration-template.md # Database migration
│   └── test-template.md        # Testing
├── context/                     # Project reference
│   ├── architecture.md         # System architecture
│   ├── coding-standards.md      # Coding standards
│   ├── database-schema.md      # Database schema
│   └── api-conventions.md      # API conventions
├── evaluations/                 # Evaluation checklists
│   ├── code-review-checklist.md
│   ├── test-coverage-checklist.md
│   └── security-checklist.md
└── examples/                    # Example prompts
    ├── creating-new-module.md
    ├── adding-api-endpoint.md
    └── database-migration.md
```

## REASONS Canvas (Quick Reference)

### R - Requirements
- What problem are we solving?
- Definition of Done (DoD)

### E - Entities
- Domain entities and relationships
- Data flow between entities

### A - Approach
- Implementation strategy
- Technical approach

### S - Structure
- Where does this fit in the system?
- Components and dependencies

### O - Operations
- Concrete implementation steps
- Testable steps

### N - Norms
- Cross-cutting engineering standards
- Naming, observability, defensive coding

### S - Safeguards
- Non-negotiable boundaries
- Invariants, performance limits, security rules

## Development Cycle Flow

```
Analysis → Design → Implementation → Testing → Integration → Deployment
    ↑                                                          ↓
    └────────────────── Iteration & Refinement ←──────────────┘
```

## Phase Checklists

### Analysis Phase (01-analysis.md)
- [ ] Requirements documented
- [ ] Acceptance criteria defined
- [ ] Domain entities mapped
- [ ] Dependencies identified
- [ ] Risks documented

### Design Phase (02-design.md)
- [ ] Architecture documented
- [ ] Data model defined
- [ ] API endpoints specified
- [ ] Security controls designed
- [ ] Performance considered

### Implementation Phase (03-implementation.md)
- [ ] Database migration created
- [ ] Model implemented
- [ ] Repository implemented
- [ ] Service implemented
- [ ] Controller implemented
- [ ] Routes defined
- [ ] Code follows standards

### Testing Phase (04-testing.md)
- [ ] Unit tests created
- [ ] Integration tests created
- [ ] E2E tests created
- [ ] All tests passing
- [ ] Coverage >80%

### Integration Phase (05-integration.md)
- [ ] Routes registered
- [ ] Middleware configured
- [ ] Authentication working
- [ ] Authorization working
- [ ] Tenant isolation verified

### Deployment Phase (06-deployment.md)
- [ ] Backups completed
- [ ] Migrations applied
- [ ] Code deployed
- [ ] Services running
- [ ] Smoke tests passing

## Templates Quick Reference

### Module Template
Use when: Creating a new module with full CRUD
File: `templates/module-template.md`
Includes: Model, Repository, Service, Controller, Routes

### API Endpoint Template
Use when: Adding a new API endpoint
File: `templates/api-endpoint-template.md`
Includes: Controller method, Service method, Route definition

### Database Migration Template
Use when: Creating database schema changes
File: `templates/database-migration-template.md`
Includes: Table structure, Foreign keys, Indexes, Rollback

### Test Template
Use when: Creating tests
File: `templates/test-template.md`
Includes: Unit tests, Integration tests, E2E tests

## Context Files Quick Reference

### Architecture (context/architecture.md)
- System layers
- Module structure
- Multi-tenant architecture
- Security architecture
- Data flow

### Coding Standards (context/coding-standards.md)
- PHP standards (PSR-12)
- Database naming conventions
- API conventions
- JavaScript standards
- HTML/CSS standards

### Database Schema (context/database-schema.md)
- Migration files overview
- Core tables structure
- Standard column patterns
- Index patterns
- Foreign key patterns

### API Conventions (context/api-conventions.md)
- Base URL and versioning
- Authentication (JWT)
- Resource naming
- HTTP methods
- Response format
- Error handling

## Evaluation Checklists Quick Reference

### Code Review (evaluations/code-review-checklist.md)
- Syntax and style
- Type hints
- PHPDoc comments
- Security
- Error handling
- Performance
- Testing

### Test Coverage (evaluations/test-coverage-checklist.md)
- Unit tests
- Integration tests
- E2E tests
- Coverage metrics
- Test quality
- Regression testing

### Security (evaluations/security-checklist.md)
- Authentication
- Authorization
- Input validation
- SQL injection prevention
- XSS prevention
- CSRF prevention
- Data security

## Common Commands

### Development
```bash
# Start development server
php -S localhost:8000 -t BACKEND/public

# Run PHP syntax check
php -l filename.php

# Run unit tests
cd BACKEND/tests
phpunit unit/

# Run integration tests
phpunit integration/

# Run E2E tests
npx playwright test e2e/

# Run all tests
phpunit
npx playwright test

# Generate coverage report
phpunit --coverage-html coverage/
```

### Database
```bash
# Backup database
mysqldump -u root -p ebp_restaurant_db > backup.sql

# Restore database
mysql -u root -p ebp_restaurant_db < backup.sql

# Apply migration
mysql -u root -p ebp_restaurant_db < DATABASE/MIGRATION_XXX.sql

# Check table structure
mysql -u root -p ebp_restaurant_db -e "DESCRIBE table_name;"
```

## Key Principles

### Iteration Rule
**When reality diverges:**
1. Fix the prompt first
2. Then update the code
3. Document the change
4. Update templates

### Quality Gates
- Analysis → Design: Requirements complete
- Design → Implementation: Design approved
- Implementation → Testing: Code compiles
- Testing → Integration: Tests passing, >80% coverage
- Integration → Deployment: Integration tests passing
- Deployment → Complete: Smoke tests passing

### Non-negotiable Rules
- MUST use Database::getInstance() for connections
- MUST use JWT for authentication
- MUST use Response class for API responses
- MUST implement tenant isolation
- MUST use Transaction for data changes
- MUST include audit logging
- MUST validate all inputs
- MUST use prepared statements

## Workflow Usage

### Using Devin Workflow
```bash
# Run the AI-driven development cycle
/ai-development-cycle
```

This will guide you through all 6 phases systematically.

### Manual Workflow
1. Read `cycles/01-analysis.md` for analysis
2. Read `cycles/02-design.md` for design
3. Read `cycles/03-implementation.md` for implementation
4. Read `cycles/04-testing.md` for testing
5. Read `cycles/05-integration.md` for integration
6. Read `cycles/06-deployment.md` for deployment

### Using Templates
1. Choose appropriate template from `templates/`
2. Customize for your specific task
3. Follow the implementation steps
4. Verify against checklists

### Using Examples
1. Find similar example in `examples/`
2. Adapt the REASONS canvas
3. Follow the implementation prompt
4. Use the testing prompt
5. Apply the code review prompt

## Quick Start Guide

### For New Module
1. Read `templates/module-template.md`
2. Follow the REASONS canvas
3. Implement in order: Migration → Model → Repository → Service → Controller → Routes
4. Create tests using `templates/test-template.md`
5. Review using `evaluations/code-review-checklist.md`

### For New API Endpoint
1. Read `templates/api-endpoint-template.md`
2. Add controller method
3. Add service method (if needed)
4. Add route with authentication/authorization
5. Create integration tests
6. Review using `evaluations/code-review-checklist.md`

### For Database Migration
1. Read `templates/database-migration-template.md`
2. Create migration file
3. Test on staging
4. Verify table structure
5. Prepare rollback script
6. Review using `evaluations/code-review-checklist.md`

## Troubleshooting

### Code Not Working
1. Check syntax: `php -l filename.php`
2. Review against coding standards
3. Check database connection
4. Verify authentication
5. Check permissions
6. Review error logs

### Tests Failing
1. Check test coverage checklist
2. Verify test data
3. Check database state
4. Verify authentication tokens
5. Review test isolation

### Integration Issues
1. Check route registration
2. Verify middleware configuration
3. Check authentication flow
4. Verify tenant isolation
5. Review cross-module dependencies

## Best Practices

### Before Starting
- Read relevant context files
- Review similar examples
- Understand the requirements
- Check existing implementations

### During Implementation
- Follow REASONS canvas
- Use templates consistently
- Write tests alongside code
- Document decisions
- Run syntax checks frequently

### After Implementation
- Run all tests
- Check code coverage
- Review with checklists
- Document changes
- Update templates if needed

## References

- System Overview: `README.md`
- Development Cycle: `cycles/01-analysis.md` through `cycles/06-deployment.md`
- Templates: `templates/`
- Context: `context/`
- Evaluations: `evaluations/`
- Examples: `examples/`
- Devin Workflow: `.devin/workflows/ai-development-cycle.md`

---

**Version**: 1.0  
**Last Updated**: 2026-07-05
