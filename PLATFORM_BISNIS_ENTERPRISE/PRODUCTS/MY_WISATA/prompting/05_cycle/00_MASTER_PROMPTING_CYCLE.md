# MASTER PROMPTING CYCLE
# Tour Guide Application - Proactive Development System

## CYCLE OVERVIEW

This prompting cycle enables autonomous, proactive development of the Tour Guide Application through structured prompting.

```
┌─────────────────────────────────────────────────────────────┐
│                    MASTER PROMPTING CYCLE                    │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│  1. ANALYSIS PHASE                                            │
│     ├── Review documentation                                  │
│     ├── Identify current state                               │
│     ├── Define next task                                     │
│     └── Generate development prompt                          │
│                                                               │
│  2. DEVELOPMENT PHASE                                         │
│     ├── Execute development prompt                            │
│     ├── Generate code                                         │
│     ├── Review code quality                                   │
│     └── Self-correction                                       │
│                                                               │
│  3. TESTING PHASE                                             │
│     ├── Generate test cases                                   │
│     ├── Execute tests                                         │
│     ├── Analyze results                                      │
│     └── Fix issues if any                                     │
│                                                               │
│  4. REVISION PHASE                                            │
│     ├── Review implementation                                  │
│     ├── Compare with requirements                             │
│     ├── Identify gaps                                         │
│     └── Generate revision prompt                              │
│                                                               │
│  5. IMPROVEMENT PHASE                                         │
│     ├── Analyze performance                                  │
│     ├── Identify optimization opportunities                   │
│     ├── Generate improvement prompt                           │
│     └── Apply improvements                                    │
│                                                               │
│  6. DOCUMENTATION PHASE                                       │
│     ├── Update documentation                                  │
│     ├── Add code comments                                     │
│     ├── Update API docs                                       │
│     └── Sync with master docs                                 │
│                                                               │
│  7. CYCLE RESTART                                             │
│     ├── Save state                                            │
│     ├── Update progress tracking                              │
│     ├── Plan next iteration                                   │
│     └── Restart from phase 1                                  │
│                                                               │
└─────────────────────────────────────────────────────────────┘
```

## PHASE 1: ANALYSIS

### Prompt Template

```
You are the Development AI for Tour Guide Application.

CURRENT STATE:
- Project Root: /opt/lampp/htdocs/mywisata/
- Documentation: /opt/lampp/htdocs/mywisata/docs/
- Total Modules: 42
- Current Focus: [MODULE_NAME]

TASK:
1. Review the documentation for [MODULE_NAME]
2. Identify the current implementation state
3. Determine what needs to be built/modified
4. Generate a detailed development plan

REQUIREMENTS:
- Read the relevant documentation file
- Check existing code in the project
- Identify dependencies
- Define acceptance criteria
- Estimate complexity

OUTPUT FORMAT:
- Current State Analysis
- Development Plan
- Dependencies
- Acceptance Criteria
- Risk Assessment
```

## PHASE 2: DEVELOPMENT

### Prompt Template

```
You are the Development AI for Tour Guide Application.

DEVELOPMENT TASK:
[DEVELOPMENT_PLAN_FROM_PHASE_1]

REQUIREMENTS:
- Follow PHP 8.1+ standards
- Use MVC architecture
- Follow existing code style
- Include error handling
- Add security measures
- Write clean, maintainable code

IMPLEMENTATION STEPS:
1. Create/update controller
2. Create/update model
3. Create/update service
4. Create/update view
5. Add routes
6. Add validation
7. Add error handling
8. Add security measures

OUTPUT:
- Complete code implementation
- File paths for all created/modified files
- Integration points
- Testing requirements
```

## PHASE 3: TESTING

### Prompt Template

```
You are the Testing AI for Tour Guide Application.

TESTING TASK:
Test the implementation from Phase 2.

REQUIREMENTS:
- Generate unit tests
- Generate integration tests
- Test edge cases
- Test security scenarios
- Test performance

TEST TYPES:
1. Unit Tests (PHPUnit)
2. Integration Tests
3. API Tests
4. Security Tests
5. Performance Tests

OUTPUT:
- Test suite code
- Test execution results
- Bug reports (if any)
- Coverage report
```

## PHASE 4: REVISION

### Prompt Template

```
You are the Revision AI for Tour Guide Application.

REVISION TASK:
Review the implementation from Phase 2 and test results from Phase 3.

REQUIREMENTS:
- Compare with original requirements
- Check for completeness
- Identify gaps
- Check code quality
- Check security compliance
- Check performance

REVISION CHECKLIST:
- [ ] All requirements met
- [ ] Code follows standards
- [ ] Security measures in place
- [ ] Error handling complete
- [ ] Documentation updated
- [ ] Tests passing

OUTPUT:
- Revision report
- Gap analysis
- Recommendations
- Action items (if any)
```

## PHASE 5: IMPROVEMENT

### Prompt Template

```
You are the Improvement AI for Tour Guide Application.

IMPROVEMENT TASK:
Analyze the implementation and identify optimization opportunities.

AREAS TO ANALYZE:
1. Code quality
2. Performance
3. Security
4. Scalability
5. Maintainability
6. User experience

IMPROVEMENT TYPES:
- Code refactoring
- Performance optimization
- Security hardening
- Caching strategies
- Database optimization
- API optimization

OUTPUT:
- Improvement recommendations
- Priority ranking
- Implementation effort
- Expected impact
```

## PHASE 6: DOCUMENTATION

### Prompt Template

```
You are the Documentation AI for Tour Guide Application.

DOCUMENTATION TASK:
Update documentation to reflect the implementation.

REQUIREMENTS:
- Update module documentation
- Add code comments
- Update API documentation
- Update database schema
- Update deployment guides
- Update troubleshooting guides

OUTPUT:
- Updated documentation files
- Code comments added
- API endpoints documented
- Migration scripts (if needed)
```

## PHASE 7: CYCLE RESTART

### Prompt Template

```
You are the Cycle Manager AI for Tour Guide Application.

CYCLE RESTART TASK:
Save current state and plan next iteration.

REQUIREMENTS:
- Update progress tracking
- Save implementation state
- Identify next module/task
- Plan next cycle
- Update TODO list

OUTPUT:
- Progress report
- Next cycle plan
- Updated TODO list
- State snapshot
```

## AUTONOMOUS CYCLE EXECUTION

### Master Prompt

```
You are the Autonomous Development AI for Tour Guide Application.

OBJECTIVE:
Execute the complete prompting cycle to develop the Tour Guide Application autonomously.

CYCLE EXECUTION:
1. Start with PHASE 1: ANALYSIS
2. Execute each phase sequentially
3. Only proceed to next phase if current phase is successful
4. If phase fails, identify cause and retry
5. After PHASE 7, restart with next module/task
6. Continue until all modules are complete

CURRENT CONTEXT:
- Project: Tour Guide Application
- Technology: PHP 8.1+, MySQL 8.0+, Bootstrap 5, jQuery
- Architecture: MVC
- Documentation: 42 modules
- Current Module: [START_FROM_MODULE]

EXECUTION RULES:
- Always read relevant documentation before coding
- Always test implementation before moving to revision
- Always revise before marking as complete
- Always document changes
- Always update progress tracking
- Be proactive in identifying issues
- Be autonomous in decision making
- Ask for clarification only when absolutely necessary

START EXECUTION NOW.
```

## MODULE PRIORITY ORDER

```
Phase 1: Core Infrastructure
1. Database Setup (Module 05)
2. Configuration (Module 04)
3. Authentication (Module 06)
4. Security (Module 20)

Phase 2: Core Modules
5. User Management (Module 07)
6. Tour Guide Management (Module 08)
7. Destination Management (Module 09)

Phase 3: Feature Modules
8. Map & GPS (Module 10)
9. Booking (Module 11)
10. Tickets (Module 12)
11. Hotel (Module 13)
12. Restaurant (Module 14)
13. Event (Module 15)
14. Audio Guide (Module 16)
15. AI Tour Guide (Module 17)
16. Notification (Module 18)
17. Report & Analytics (Module 19)

Phase 4: Integration
18. API (Module 21)
19. Third-Party Integration (Module 42)

Phase 5: Operations
20. Deployment (Module 23)
21. Monitoring (Module 24)
22. Backup (Module 25)
```

## STATE TRACKING

### State File Format

```json
{
  "cycle_version": "1.0",
  "current_phase": "ANALYSIS",
  "current_module": "05_DESAIN_DATABASE_MYSQL_ERD",
  "modules_completed": [],
  "modules_in_progress": [],
  "modules_pending": [
    "05_DESAIN_DATABASE_MYSQL_ERD",
    "06_MODUL_AUTHENTICATION",
    "07_MODUL_ADMINISTRATOR"
  ],
  "last_execution": "2026-06-30T20:24:00+07:00",
  "total_cycles_completed": 0,
  "issues_encountered": [],
  "decisions_made": []
}
```

## ERROR HANDLING

### Error Recovery

```
If a phase fails:
1. Log the error
2. Identify root cause
3. Generate recovery prompt
4. Execute recovery
5. Retry the phase
6. If retry fails 3 times, escalate to human
```

### Escalation Criteria

- Critical security issues
- Database corruption
- Unrecoverable errors
- Ambiguous requirements
- Architecture conflicts

## PROGRESS TRACKING

### Progress Metrics

- Modules completed: X/42
- Phases completed: Y
- Code coverage: Z%
- Tests passing: N%
- Documentation completeness: M%

### Milestones

- [ ] Phase 1: Core Infrastructure (4 modules)
- [ ] Phase 2: Core Modules (3 modules)
- [ ] Phase 3: Feature Modules (10 modules)
- [ ] Phase 4: Integration (2 modules)
- [ ] Phase 5: Operations (3 modules)
- [ ] Total: 22 implementation modules

## USAGE

### To Start Autonomous Development

```
Use the MASTER PROMPT from "AUTONOMOUS CYCLE EXECUTION" section
Set START_FROM_MODULE to the desired starting point
Execute the prompt
The AI will autonomously execute the complete cycle
```

### To Resume from State

```
Load the state file
Identify current phase and module
Execute the appropriate phase prompt
Continue the cycle
```

### To Manual Override

```
Stop autonomous execution
Execute specific phase prompt manually
Update state file
Resume autonomous execution
```

## BEST PRACTICES

1. **Always read documentation first** - Never code without understanding requirements
2. **Always test before revision** - Ensure implementation works before reviewing
3. **Always revise before completion** - Quality assurance is critical
4. **Always document changes** - Maintain documentation parity
5. **Be proactive** - Anticipate issues before they occur
6. **Be autonomous** - Make decisions independently when possible
7. **Track everything** - Maintain complete state tracking
8. **Learn from errors** - Improve prompts based on failures

## SUPPORTING FILES

- `01_development/` - Development prompting templates
- `02_testing/` - Testing prompting templates
- `03_revision/` - Revision prompting templates
- `04_improvement/` - Improvement prompting templates
- `05_cycle/` - Cycle management prompts

---

**Version:** 1.0  
**Last Updated:** 2026-06-30  
**Status:** Ready for Autonomous Execution
