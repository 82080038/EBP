# Cycle 1: Analysis Phase

## Purpose

Analyze requirements, understand the problem space, and define clear acceptance criteria before implementation.

## REASONS Canvas

### R - Requirements

**What problem are we solving?**
- Define the specific business problem or feature request
- Identify the target users and their needs
- Understand the current state vs desired state

**Definition of Done (DoD):**
- Requirements clearly documented
- Acceptance criteria defined
- Edge cases identified
- Dependencies mapped
- Risk assessment completed

### E - Entities

**Domain Entities:**
- Identify relevant domain entities (e.g., Order, Customer, Menu, Table)
- Define entity relationships
- Identify data flow between entities
- Map entity lifecycle

**Example for RESTAURANT_ERP:**
- Tenants, Users, Roles, Permissions
- Menu Categories, Products, Recipes
- Orders, Order Details, Payments
- Tables, Reservations
- Inventory, Suppliers, Purchase Orders

### A - Approach

**Strategy:**
- Analyze existing implementation (if any)
- Review research documentation (RESEARCH_*.md files)
- Check database schema (DATABASE/ directory)
- Review existing modules (BACKEND/modules/)
- Identify integration points

**Analysis Steps:**
1. Read relevant research files
2. Review implementation plan (IMPLEMENTATION_PLAN.md)
3. Check database migrations
4. Examine existing similar modules
5. Identify API endpoints needed
6. Map user roles and permissions

### S - Structure

**System Context:**
- Where does this fit in the module architecture?
- Which backend module folder?
- Database tables needed?
- API routes required?
- Frontend components needed?

**Module Structure:**
```
BACKEND/modules/[ModuleName]/
├── Controllers/
├── Services/
├── Repositories/
├── Models/
└── routes/
```

### O - Operations

**Concrete Analysis Steps:**
1. **Requirement Gathering**
   - Document user stories
   - Define acceptance criteria
   - Identify success metrics

2. **Domain Analysis**
   - Map domain entities
   - Define relationships
   - Identify business rules

3. **Technical Analysis**
   - Review existing code
   - Check database schema
   - Identify dependencies
   - Assess security requirements

4. **Integration Analysis**
   - API endpoints needed
   - External integrations
   - Data flow mapping

5. **Risk Assessment**
   - Technical risks
   - Security risks
   - Performance considerations
   - Compliance requirements

### N - Norms

**Analysis Standards:**
- Use Indonesian as primary language, English as secondary
- Follow project documentation structure
- Reference research files with citations
- Document assumptions clearly
- Include edge cases and error scenarios

**Documentation Format:**
- Markdown with clear sections
- Code examples where applicable
- Diagrams for complex flows
- Tables for entity relationships

### S - Safeguards

**Non-negotiable Boundaries:**
- Must comply with existing database schema
- Must follow security patterns (JWT, RBAC)
- Must support multi-tenant architecture
- Must include Indonesian/English language support
- Must consider offline capability
- Must align with compliance requirements

**Validation Criteria:**
- Requirements are complete and unambiguous
- Acceptance criteria are testable
- Dependencies are identified
- Risks are documented
- Technical feasibility confirmed

## Analysis Checklist

- [ ] Business problem clearly defined
- [ ] Target users identified
- [ ] Acceptance criteria documented
- [ ] Domain entities mapped
- [ ] Entity relationships defined
- [ ] Existing implementation reviewed
- [ ] Database schema checked
- [ ] API endpoints identified
- [ ] Security requirements assessed
- [ ] Compliance requirements checked
- [ ] Integration points mapped
- [ ] Risks documented
- [ ] Technical feasibility confirmed

## Output Format

After analysis, produce:

1. **Requirements Document**
   - User stories
   - Acceptance criteria
   - Success metrics

2. **Domain Model**
   - Entity definitions
   - Relationship diagram
   - Business rules

3. **Technical Specification**
   - Module structure
   - API endpoints
   - Database changes
   - Dependencies

4. **Risk Assessment**
   - Technical risks
   - Security risks
   - Mitigation strategies

## Next Steps

After completing analysis:
1. Review with stakeholders
2. Validate assumptions
3. Proceed to Design Phase (02-design.md)

---

**Version**: 1.0  
**Last Updated**: 2026-07-05
