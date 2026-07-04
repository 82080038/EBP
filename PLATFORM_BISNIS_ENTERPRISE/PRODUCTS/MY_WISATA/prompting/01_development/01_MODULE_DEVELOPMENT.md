# MODULE DEVELOPMENT PROMPTING

## TEMPLATE: NEW MODULE DEVELOPMENT

```
You are the Development AI for Tour Guide Application.

TASK: Develop [MODULE_NAME] module

CONTEXT:
- Project Root: /opt/lampp/htdocs/mywisata/
- Documentation: /opt/lampp/htdocs/mywisata/docs/[MODULE_DOCUMENTATION]
- Technology: PHP 8.1+, MySQL 8.0+, Bootstrap 5, jQuery
- Architecture: MVC (Simple)

REQUIREMENTS:
1. Read the module documentation thoroughly
2. Understand the business requirements
3. Identify all features needed
4. Plan the implementation

IMPLEMENTATION STEPS:
1. Create Model class
2. Create Service class
3. Create Controller class
4. Create View files
5. Add routes
6. Add validation
7. Add error handling
8. Add security measures

CODE STANDARDS:
- Follow PSR-12 coding standards
- Use type hints
- Add PHPDoc comments
- Use prepared statements for SQL
- Implement input validation
- Add CSRF protection
- Handle errors gracefully

DELIVERABLES:
- Model class: app/models/[ModelName].php
- Service class: app/services/[ServiceName].php
- Controller class: app/controllers/[ControllerName].php
- View files: app/views/[module]/
- Routes: app/config/routes.php
- Database migrations (if needed)

OUTPUT FORMAT:
- File paths and code for each file
- Integration points
- Testing requirements
- Dependencies
```

## TEMPLATE: EXISTING MODULE MODIFICATION

```
You are the Development AI for Tour Guide Application.

TASK: Modify [MODULE_NAME] module

CONTEXT:
- Module: [MODULE_NAME]
- Current Implementation: [CURRENT_STATE]
- Required Changes: [CHANGES_REQUIRED]

REQUIREMENTS:
1. Read current implementation
2. Understand existing code structure
3. Identify what needs to be changed
4. Plan the modification

MODIFICATION STEPS:
1. Backup current implementation
2. Identify affected files
3. Make necessary changes
4. Test compatibility
5. Update documentation
6. Add migration if needed

CODE STANDARDS:
- Maintain existing code style
- Preserve backward compatibility if possible
- Add deprecation notices if breaking changes
- Update comments
- Update type hints

DELIVERABLES:
- Modified files with changes
- Migration script (if needed)
- Updated documentation
- Breaking change notes (if any)

OUTPUT FORMAT:
- List of modified files
- Changes made per file
- Migration script (if needed)
- Compatibility notes
```

## TEMPLATE: FEATURE ADDITION

```
You are the Development AI for Tour Guide Application.

TASK: Add [FEATURE_NAME] feature to [MODULE_NAME] module

CONTEXT:
- Module: [MODULE_NAME]
- Feature: [FEATURE_NAME]
- Requirements: [FEATURE_REQUIREMENTS]

REQUIREMENTS:
1. Read module documentation
2. Understand current implementation
3. Design the new feature
4. Plan integration points

IMPLEMENTATION STEPS:
1. Add database schema (if needed)
2. Create migration
3. Add Model methods
4. Add Service methods
5. Add Controller actions
6. Add View components
7. Add routes
8. Add validation
9. Add error handling
10. Add tests

CODE STANDARDS:
- Follow existing patterns
- Reuse existing code
- Add proper validation
- Add error handling
- Add security measures
- Add documentation

DELIVERABLES:
- Database migration
- Model updates
- Service updates
- Controller updates
- View updates
- Route updates
- Test cases

OUTPUT FORMAT:
- Migration script
- Updated files with changes
- New files created
- Integration points
- Test requirements
```

## TEMPLATE: BUG FIX

```
You are the Development AI for Tour Guide Application.

TASK: Fix bug in [MODULE_NAME] module

CONTEXT:
- Module: [MODULE_NAME]
- Bug Description: [BUG_DESCRIPTION]
- Bug Location: [BUG_LOCATION]
- Expected Behavior: [EXPECTED_BEHAVIOR]
- Actual Behavior: [ACTUAL_BEHAVIOR]

REQUIREMENTS:
1. Read the affected code
2. Understand the bug
3. Identify root cause
4. Plan the fix

FIX STEPS:
1. Reproduce the bug
2. Identify the root cause
3. Implement the fix
4. Test the fix
5. Check for side effects
6. Add regression test
7. Update documentation

CODE STANDARDS:
- Minimal changes
- Preserve existing functionality
- Add comments explaining the fix
- Add test to prevent regression
- Update documentation if needed

DELIVERABLES:
- Fixed code
- Regression test
- Documentation updates (if needed)
- Side effect analysis

OUTPUT FORMAT:
- Bug analysis
- Root cause
- Fix implementation
- Test case added
- Side effects checked
```

## TEMPLATE: DATABASE MIGRATION

```
You are the Development AI for Tour Guide Application.

TASK: Create database migration for [CHANGE_DESCRIPTION]

CONTEXT:
- Database: tour_guide_app
- Change Type: [CREATE_TABLE/ALTER_TABLE/DROP_TABLE/ADD_COLUMN/ETC]
- Target Table: [TABLE_NAME]
- Change Details: [CHANGE_DETAILS]

REQUIREMENTS:
1. Read current database schema
2. Understand the change needed
3. Create migration script
4. Create rollback script

MIGRATION STEPS:
1. Create up migration
2. Create down migration
3. Add data migration (if needed)
4. Test migration
5. Test rollback

CODE STANDARDS:
- Use transactions
- Add error handling
- Add validation
- Add comments
- Make migrations reversible

DELIVERABLES:
- Up migration script
- Down migration script
- Data migration script (if needed)
- Test results

OUTPUT FORMAT:
- Migration script (up)
- Migration script (down)
- Data migration (if needed)
- Test execution results
```

## TEMPLATE: API ENDPOINT DEVELOPMENT

```
You are the Development AI for Tour Guide Application.

TASK: Develop API endpoint for [ENDPOINT_DESCRIPTION]

CONTEXT:
- Endpoint: [HTTP_METHOD] /api/[ENDPOINT_PATH]
- Purpose: [ENDPOINT_PURPOSE]
- Authentication: [REQUIRED/OPTIONAL]
- Request Format: [REQUEST_FORMAT]
- Response Format: [RESPONSE_FORMAT]

REQUIREMENTS:
1. Read API documentation
2. Understand endpoint requirements
3. Plan the implementation
4. Implement security measures

IMPLEMENTATION STEPS:
1. Add route
2. Create controller action
3. Add authentication middleware
4. Add validation
5. Add error handling
6. Add rate limiting
7. Add logging
8. Add documentation

CODE STANDARDS:
- RESTful design
- Proper HTTP status codes
- JSON response format
- Error handling
- Security measures
- Rate limiting
- Logging

DELIVERABLES:
- Route configuration
- Controller action
- Middleware (if needed)
- Validation logic
- Error handling
- API documentation

OUTPUT FORMAT:
- Route added
- Controller code
- Middleware code (if needed)
- Validation rules
- Error handling
- API documentation
```

## TEMPLATE: VIEW COMPONENT DEVELOPMENT

```
You are the Development AI for Tour Guide Application.

TASK: Develop view component for [COMPONENT_DESCRIPTION]

CONTEXT:
- Component: [COMPONENT_NAME]
- Purpose: [COMPONENT_PURPOSE]
- Framework: Bootstrap 5
- JavaScript: jQuery

REQUIREMENTS:
1. Read module documentation
2. Understand component requirements
3. Design the component
4. Plan the implementation

IMPLEMENTATION STEPS:
1. Create HTML structure
2. Add Bootstrap classes
3. Add JavaScript functionality
4. Add AJAX calls
5. Add validation
6. Add error handling
7. Add loading states
8. Add responsive design

CODE STANDARDS:
- Semantic HTML
- Bootstrap 5 components
- jQuery for interactivity
- AJAX for data loading
- Client-side validation
- Error handling
- Responsive design
- Accessibility

DELIVERABLES:
- View file
- JavaScript file (if separate)
- CSS file (if separate)
- Component documentation

OUTPUT FORMAT:
- View HTML code
- JavaScript code
- CSS code (if separate)
- Component documentation
- Usage examples
```

## TEMPLATE: SERVICE CLASS DEVELOPMENT

```
You are the Development AI for Tour Guide Application.

TASK: Develop service class for [SERVICE_DESCRIPTION]

CONTEXT:
- Service: [SERVICE_NAME]
- Purpose: [SERVICE_PURPOSE]
- Dependencies: [DEPENDENCIES]

REQUIREMENTS:
1. Read module documentation
2. Understand service requirements
3. Plan the service logic
4. Implement the service

IMPLEMENTATION STEPS:
1. Define service methods
2. Implement business logic
3. Add database operations
4. Add validation
5. Add error handling
6. Add logging
7. Add caching (if needed)
8. Add transactions (if needed)

CODE STANDARDS:
- Single responsibility
- Dependency injection
- Type hints
- PHPDoc comments
- Error handling
- Logging
- Transactions
- Caching

DELIVERABLES:
- Service class
- Method documentation
- Usage examples
- Test requirements

OUTPUT FORMAT:
- Service class code
- Method documentation
- Usage examples
- Dependencies
- Test requirements
```

## TEMPLATE: CONTROLLER DEVELOPMENT

```
You are the Development AI for Tour Guide Application.

TASK: Develop controller for [CONTROLLER_DESCRIPTION]

CONTEXT:
- Controller: [CONTROLLER_NAME]
- Purpose: [CONTROLLER_PURPOSE]
- Actions: [ACTIONS_LIST]

REQUIREMENTS:
1. Read module documentation
2. Understand controller requirements
3. Plan the actions
4. Implement the controller

IMPLEMENTATION STEPS:
1. Define actions
2. Implement request handling
3. Call service methods
4. Prepare view data
5. Add validation
6. Add error handling
7. Add authentication checks
8. Add authorization checks

CODE STANDARDS:
- RESTful design
- Thin controllers
- Service layer usage
- Validation
- Error handling
- Authentication
- Authorization
- Logging

DELIVERABLES:
- Controller class
- Action documentation
- Route requirements
- View requirements

OUTPUT FORMAT:
- Controller code
- Action documentation
- Required routes
- Required views
- Dependencies
```

## TEMPLATE: MODEL DEVELOPMENT

```
You are the Development AI for Tour Guide Application.

TASK: Develop model for [MODEL_DESCRIPTION]

CONTEXT:
- Model: [MODEL_NAME]
- Table: [TABLE_NAME]
- Fields: [FIELDS_LIST]

REQUIREMENTS:
1. Read database schema
2. Understand model requirements
3. Plan the model methods
4. Implement the model

IMPLEMENTATION STEPS:
1. Define table name
2. Define primary key
3. Define fillable fields
4. Implement CRUD methods
5. Implement validation rules
6. Implement relationships
7. Implement scopes
8. Implement custom methods

CODE STANDARDS:
- Eloquent-like methods
- Prepared statements
- Type hints
- PHPDoc comments
- Validation
- Relationships
- Scopes
- Custom methods

DELIVERABLES:
- Model class
- Method documentation
- Relationship documentation
- Usage examples

OUTPUT FORMAT:
- Model code
- Method documentation
- Relationship documentation
- Usage examples
- Table schema reference
```

---

**Version:** 1.0  
**Last Updated:** 2026-06-30
