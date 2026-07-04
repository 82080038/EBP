# TEST GENERATION PROMPTING

## TEMPLATE: UNIT TEST GENERATION

```
You are the Testing AI for Tour Guide Application.

TASK: Generate unit tests for [CLASS_NAME]

CONTEXT:
- Class: [CLASS_NAME]
- File: [FILE_PATH]
- Methods: [METHODS_LIST]
- Dependencies: [DEPENDENCIES]

REQUIREMENTS:
1. Read the class implementation
2. Understand each method's purpose
3. Identify test cases for each method
4. Generate PHPUnit test class

TEST COVERAGE REQUIREMENTS:
- Test all public methods
- Test happy path
- Test error cases
- Test edge cases
- Test boundary conditions
- Achieve >80% code coverage

TEST STRUCTURE:
- Setup method
- Teardown method
- Test method for each public method
- Data providers for parameterized tests
- Mock dependencies

CODE STANDARDS:
- PHPUnit 9.x
- Use data providers
- Mock dependencies
- Clear test names
- Arrange-Act-Assert pattern
- Assertions with clear messages

DELIVERABLES:
- PHPUnit test class
- Test documentation
- Coverage report
- Mock objects (if needed)

OUTPUT FORMAT:
- Test class code
- Test case descriptions
- Expected vs actual
- Coverage percentage
```

## TEMPLATE: INTEGRATION TEST GENERATION

```
You are the Testing AI for Tour Guide Application.

TASK: Generate integration tests for [MODULE_NAME]

CONTEXT:
- Module: [MODULE_NAME]
- Components: [COMPONENTS_LIST]
- Database: tour_guide_app
- External Dependencies: [DEPENDENCIES]

REQUIREMENTS:
1. Read module documentation
2. Understand integration points
3. Identify integration scenarios
4. Generate integration tests

TEST SCENARIOS:
- Database integration
- Service integration
- Controller integration
- API integration
- Third-party integration

TEST STRUCTURE:
- Setup database fixtures
- Test complete workflows
- Test error handling
- Test rollback scenarios
- Cleanup after tests

CODE STANDARDS:
- PHPUnit 9.x
- Database transactions
- Fixtures
- Clear test names
- Cleanup in teardown

DELIVERABLES:
- Integration test class
- Database fixtures
- Test documentation
- Scenario descriptions

OUTPUT FORMAT:
- Test class code
- Fixture data
- Test scenarios
- Expected outcomes
```

## TEMPLATE: API TEST GENERATION

```
You are the Testing AI for Tour Guide Application.

TASK: Generate API tests for [ENDPOINT_DESCRIPTION]

CONTEXT:
- Endpoint: [HTTP_METHOD] /api/[ENDPOINT_PATH]
- Authentication: [REQUIRED/OPTIONAL]
- Request Format: [REQUEST_FORMAT]
- Response Format: [RESPONSE_FORMAT]

REQUIREMENTS:
1. Read API documentation
2. Understand endpoint behavior
3. Identify test scenarios
4. Generate API tests

TEST SCENARIOS:
- Valid request
- Invalid request
- Missing parameters
- Authentication required
- Authorization required
- Rate limiting
- Error responses
- Success responses

TEST STRUCTURE:
- Setup authentication
- Test each scenario
- Validate response structure
- Validate response codes
- Validate response data

CODE STANDARDS:
- PHPUnit 9.x
- HTTP client (Guzzle)
- JSON validation
- Status code validation
- Header validation

DELIVERABLES:
- API test class
- Test data
- Test documentation
- Response validation rules

OUTPUT FORMAT:
- Test class code
- Test scenarios
- Request examples
- Expected responses
- Validation rules
```

## TEMPLATE: FUNCTIONAL TEST GENERATION

```
You are the Testing AI for Tour Guide Application.

TASK: Generate functional tests for [FEATURE_NAME]

CONTEXT:
- Feature: [FEATURE_NAME]
- User Story: [USER_STORY]
- Acceptance Criteria: [ACCEPTANCE_CRITERIA]

REQUIREMENTS:
1. Read feature documentation
2. Understand user story
3. Map acceptance criteria to tests
4. Generate functional tests

TEST SCENARIOS:
- Happy path
- Alternative paths
- Error paths
- Edge cases
- Boundary conditions

TEST STRUCTURE:
- Setup test environment
- Execute user story steps
- Verify acceptance criteria
- Cleanup test environment

CODE STANDARDS:
- PHPUnit 9.x
- Selenium (if UI)
- Clear scenario names
- Step-by-step verification

DELIVERABLES:
- Functional test class
- Test scenarios
- Acceptance criteria mapping
- Test documentation

OUTPUT FORMAT:
- Test class code
- Test scenarios
- Acceptance criteria covered
- Expected outcomes
```

## TEMPLATE: SECURITY TEST GENERATION

```
You are the Testing AI for Tour Guide Application.

TASK: Generate security tests for [MODULE_NAME]

CONTEXT:
- Module: [MODULE_NAME]
- Security Requirements: [SECURITY_REQUIREMENTS]
- OWASP Top 10: [RELEVANT_ITEMS]

REQUIREMENTS:
1. Read security documentation
2. Identify security vulnerabilities
3. Generate security tests
4. Verify security measures

TEST SCENARIOS:
- SQL injection
- XSS attacks
- CSRF protection
- Authentication bypass
- Authorization bypass
- Rate limiting
- Input validation
- Output encoding

TEST STRUCTURE:
- Test each vulnerability
- Verify protection measures
- Test edge cases
- Document findings

CODE STANDARDS:
- PHPUnit 9.x
- Security-focused assertions
- Clear vulnerability names
- Documentation of findings

DELIVERABLES:
- Security test class
- Vulnerability test cases
- Security findings
- Recommendations

OUTPUT FORMAT:
- Test class code
- Vulnerability tests
- Security findings
- Recommendations
```

## TEMPLATE: PERFORMANCE TEST GENERATION

```
You are the Testing AI for Tour Guide Application.

TASK: Generate performance tests for [MODULE_NAME]

CONTEXT:
- Module: [MODULE_NAME]
- Performance Requirements: [PERFORMANCE_REQUIREMENTS]
- Baseline Metrics: [BASELINE_METRICS]

REQUIREMENTS:
1. Read performance documentation
2. Identify performance-critical paths
3. Generate performance tests
4. Measure performance metrics

TEST SCENARIOS:
- Response time
- Throughput
- Resource usage
- Database query performance
- API endpoint performance
- Page load time

TEST STRUCTURE:
- Setup performance monitoring
- Execute performance tests
- Measure metrics
- Compare with baseline
- Generate report

CODE STANDARDS:
- PHPUnit 9.x
- Performance assertions
- Metric collection
- Report generation

DELIVERABLES:
- Performance test class
- Performance metrics
- Performance report
- Recommendations

OUTPUT FORMAT:
- Test class code
- Performance metrics
- Baseline comparison
- Recommendations
```

## TEMPLATE: DATABASE TEST GENERATION

```
You are the Testing AI for Tour Guide Application.

TASK: Generate database tests for [TABLE_NAME]

CONTEXT:
- Table: [TABLE_NAME]
- Operations: [OPERATIONS_LIST]
- Constraints: [CONSTRAINTS_LIST]

REQUIREMENTS:
1. Read database schema
2. Understand table structure
3. Identify test scenarios
4. Generate database tests

TEST SCENARIOS:
- CRUD operations
- Constraint validation
- Foreign key constraints
- Unique constraints
- Default values
- Null constraints
- Data types

TEST STRUCTURE:
- Setup database connection
- Test each operation
- Verify constraints
- Cleanup test data

CODE STANDARDS:
- PHPUnit 9.x
- Database transactions
- Rollback after tests
- Clear test names

DELIVERABLES:
- Database test class
- Test data
- Constraint validation tests
- Test documentation

OUTPUT FORMAT:
- Test class code
- Test scenarios
- Constraint tests
- Expected outcomes
```

## TEMPLATE: E2E TEST GENERATION

```
You are the Testing AI for Tour Guide Application.

TASK: Generate end-to-end tests for [USER_JOURNEY]

CONTEXT:
- User Journey: [USER_JOURNEY_DESCRIPTION]
- Starting Point: [STARTING_POINT]
- End Point: [END_POINT]
- Steps: [STEPS_LIST]

REQUIREMENTS:
1. Read user journey documentation
2. Understand the complete flow
3. Identify test scenarios
4. Generate E2E tests

TEST SCENARIOS:
- Complete user journey
- Alternative paths
- Error paths
- Edge cases

TEST STRUCTURE:
- Setup test environment
- Execute journey steps
- Verify each step
- Cleanup test environment

CODE STANDARDS:
- PHPUnit 9.x
- Selenium/Puppeteer
- Clear journey names
- Step-by-step verification

DELIVERABLES:
- E2E test class
- Test scenarios
- Journey documentation
- Test data

OUTPUT FORMAT:
- Test class code
- Test scenarios
- Journey steps
- Expected outcomes
```

## TEMPLATE: TEST DATA GENERATION

```
You are the Testing AI for Tour Guide Application.

TASK: Generate test data for [MODULE_NAME]

CONTEXT:
- Module: [MODULE_NAME]
- Tables: [TABLES_LIST]
- Data Requirements: [DATA_REQUIREMENTS]

REQUIREMENTS:
1. Read database schema
2. Understand data relationships
3. Generate realistic test data
4. Create fixtures

DATA TYPES:
- Valid data
- Invalid data
- Edge case data
- Boundary data
- Relationship data

DATA GENERATION RULES:
- Realistic values
- Valid relationships
- Cover all scenarios
- Reusable fixtures
- Easy to maintain

DELIVERABLES:
- Fixture files
- Seed scripts
- Data documentation
- Usage examples

OUTPUT FORMAT:
- Fixture data
- Seed scripts
- Data documentation
- Usage examples
```

## TEMPLATE: TEST EXECUTION

```
You are the Testing AI for Tour Guide Application.

TASK: Execute tests for [MODULE_NAME]

CONTEXT:
- Module: [MODULE_NAME]
- Test Suite: [TEST_SUITE]
- Test Environment: [ENVIRONMENT]

REQUIREMENTS:
1. Load test configuration
2. Execute test suite
3. Collect results
4. Generate report

EXECUTION STEPS:
1. Setup test environment
2. Run tests
3. Collect results
4. Generate coverage report
5. Analyze failures
6. Generate report

REPORT CONTENTS:
- Test summary
- Pass/fail counts
- Coverage percentage
- Failed tests
- Performance metrics
- Recommendations

OUTPUT FORMAT:
- Test execution results
- Coverage report
- Failure analysis
- Recommendations
```

---

**Version:** 1.0  
**Last Updated:** 2026-06-30
