---
description: AI-driven development cycle for RESTAURANT_ERP - analyze, design, implement, test, integrate, deploy
---

# AI-Driven Development Cycle for RESTAURANT_ERP

## Overview

This workflow implements a systematic AI-driven development cycle for RESTAURANT_ERP, following Structured-Prompt-Driven Development (SPDD) and Spec-Driven Development (SDD) methodologies.

## Prerequisites

- RESTAURANT_ERP project loaded in IDE
- Prompting system initialized in `prompting/` directory
- Access to project context files
- Database connection available

## Workflow Steps

### 1. Analysis Phase

**Purpose**: Analyze requirements and understand the problem space

**Steps**:
1. Read the prompting cycle file: `prompting/cycles/01-analysis.md`
2. Review project context:
   - `prompting/context/architecture.md`
   - `prompting/context/coding-standards.md`
   - `prompting/context/database-schema.md`
   - `prompting/context/api-conventions.md`
3. Review relevant research files from `research/` directory
4. Check implementation plan: `IMPLEMENTATION_PLAN.md`
5. Identify the specific feature or module to develop
6. Document requirements using REASONS canvas
7. Define acceptance criteria
8. Identify dependencies and risks

**Output**: Requirements document with REASONS canvas

**Verification**:
- [ ] Requirements clearly defined
- [ ] Acceptance criteria documented
- [ ] Dependencies identified
- [ ] Risks documented

### 2. Design Phase

**Purpose**: Create detailed technical design

**Steps**:
1. Read the prompting cycle file: `prompting/cycles/02-design.md`
2. Based on analysis outputs, create:
   - Architecture diagram
   - Data model (database tables)
   - API endpoint specifications
   - Security design
   - Performance considerations
3. Use appropriate template if creating a new module:
   - `prompting/templates/module-template.md`
   - `prompting/templates/api-endpoint-template.md`
   - `prompting/templates/database-migration-template.md`
4. Document design decisions
5. Review design against requirements

**Output**: Technical design document

**Verification**:
- [ ] Architecture documented
- [ ] Data model defined
- [ ] API endpoints specified
- [ ] Security controls designed
- [ ] Performance considered

### 3. Implementation Phase

**Purpose**: Generate production-ready code

**Steps**:
1. Read the prompting cycle file: `prompting/cycles/03-implementation.md`
2. Follow the implementation order:
   - Database migration (if needed)
   - Model class
   - Repository class
   - Service class
   - Controller class
   - Routes file
3. Use templates for consistency:
   - `prompting/templates/module-template.md` for new modules
   - `prompting/templates/api-endpoint-template.md` for new endpoints
4. Follow coding standards from `prompting/context/coding-standards.md`
5. Implement security controls:
   - JWT authentication
   - Permission checks
   - Input validation
   - Audit logging
6. Add PHPDoc comments
7. Run syntax checks

**Output**: Source code files

**Verification**:
- [ ] All components implemented
- [ ] Code follows standards
- [ ] Security controls in place
- [ ] PHPDoc comments added
- [ ] No syntax errors

### 4. Testing Phase

**Purpose**: Generate comprehensive tests

**Steps**:
1. Read the prompting cycle file: `prompting/cycles/04-testing.md`
2. Use test template: `prompting/templates/test-template.md`
3. Create unit tests for service methods
4. Create integration tests for API endpoints
5. Create E2E tests for user flows
6. Run tests and verify results
7. Check code coverage (>80% target)

**Output**: Test files and test results

**Verification**:
- [ ] Unit tests created
- [ ] Integration tests created
- [ ] E2E tests created
- [ ] All tests passing
- [ ] Coverage >80%

### 5. Integration Phase

**Purpose**: Integrate with existing system

**Steps**:
1. Read the prompting cycle file: `prompting/cycles/05-integration.md`
2. Register routes in main router
3. Configure middleware stack
4. Test authentication flow
5. Test authorization flow
6. Test tenant isolation
7. Test cross-module interactions
8. Test with real database
9. Monitor performance

**Output**: Integration report

**Verification**:
- [ ] Routes registered
- [ ] Middleware configured
- [ ] Authentication working
- [ ] Authorization working
- [ ] Tenant isolation verified
- [ ] Cross-module interactions tested

### 6. Deployment Phase

**Purpose**: Deploy to production

**Steps**:
1. Read the prompting cycle file: `prompting/cycles/06-deployment.md`
2. Backup current database
3. Backup current code
4. Apply database migrations
5. Deploy code changes
6. Update configuration
7. Restart services
8. Run smoke tests
9. Monitor system
10. Document deployment

**Output**: Deployment report

**Verification**:
- [ ] Backups completed
- [ ] Migrations applied
- [ ] Code deployed
- [ ] Services running
- [ ] Tests passing
- [ ] System monitored

## Iteration Rules

### When Reality Diverges

1. **Fix the prompt first** - Update the prompting cycle or template
2. **Then update the code** - Implement based on updated prompt
3. **Document the change** - Record what was learned
4. **Update templates** - Improve templates for future use

### Continuous Improvement

1. After each cycle, review what worked well
2. Update prompting cycles with lessons learned
3. Improve templates based on actual usage
4. Share improvements with team

## Quality Gates

### Before Proceeding to Next Phase

- **Analysis → Design**: Requirements complete and validated
- **Design → Implementation**: Design reviewed and approved
- **Implementation → Testing**: Code compiles and follows standards
- **Testing → Integration**: All tests passing with >80% coverage
- **Integration → Deployment**: Integration tests passing
- **Deployment → Complete**: Smoke tests passing, system stable

## Rollback Procedure

If any phase fails:

1. Stop current phase
2. Revert to previous stable state
3. Analyze failure
4. Update prompt with fix
5. Retry phase

## Success Criteria

- All phases completed successfully
- All tests passing
- Code coverage >80%
- No critical bugs
- Performance acceptable
- Documentation complete

## References

- Prompting System: `prompting/README.md`
- Architecture: `prompting/context/architecture.md`
- Coding Standards: `prompting/context/coding-standards.md`
- Database Schema: `prompting/context/database-schema.md`
- API Conventions: `prompting/context/api-conventions.md`
- Implementation Plan: `IMPLEMENTATION_PLAN.md`

---

**Version**: 1.0  
**Last Updated**: 2026-07-05
