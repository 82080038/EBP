# RESTAURANT_ERP Prompting System

## Overview

This prompting system implements AI-driven development cycles for RESTAURANT_ERP, following Structured-Prompt-Driven Development (SPDD) and Spec-Driven Development (SDD) methodologies.

## Methodology

### REASONS Canvas Framework

Based on Martin Fowler's SPDD methodology, all prompts follow the REASONS structure:

- **R - Requirements**: What problem are we solving, and what is DoD?
- **E - Entities**: Domain entities and relationships
- **A - Approach**: The strategy of how we'll meet the requirements
- **S - Structure**: Where the change fits in the system; components and dependencies
- **O - Operations**: Break the abstract strategy into concrete, testable implementation steps
- **N - Norms**: Cross-cutting engineering norms (naming, observability, defensive coding, etc.)
- **S - Safeguards**: Non-negotiable boundaries (invariants, performance limits, security rules, etc.)

### Core Principles

1. **Spec-First**: Define intent explicitly before implementation
2. **Iterative Review**: When reality diverges, fix the prompt first — then update the code
3. **Version Control**: Treat prompts as first-class delivery artifacts
4. **Shared Intent**: Use structured prompts to capture requirements, domain language, design intent
5. **Governance**: Enforce quality gates and validation criteria

## Directory Structure

```
prompting/
├── README.md                    # This file
├── cycles/                      # Development cycle prompts
│   ├── 01-analysis.md          # Analysis and requirements gathering
│   ├── 02-design.md            # Architecture and design
│   ├── 03-implementation.md    # Code generation
│   ├── 04-testing.md           # Test generation and execution
│   ├── 05-integration.md       # Integration testing
│   └── 06-deployment.md        # Deployment and verification
├── templates/                   # Reusable prompt templates
│   ├── module-template.md      # New module creation
│   ├── api-endpoint-template.md # API endpoint development
│   ├── database-migration-template.md # Database migration
│   └── test-template.md        # Test case generation
├── context/                     # Project context and knowledge
│   ├── architecture.md         # System architecture
│   ├── coding-standards.md      # Coding standards and conventions
│   ├── database-schema.md      # Database schema reference
│   └── api-conventions.md      # API design conventions
└── evaluations/                 # Evaluation criteria and checklists
    ├── code-review-checklist.md
    ├── test-coverage-checklist.md
    └── security-checklist.md
```

## Usage

### Starting a New Development Cycle

1. **Analysis Phase**: Use `cycles/01-analysis.md` to understand requirements
2. **Design Phase**: Use `cycles/02-design.md` for architecture decisions
3. **Implementation Phase**: Use `cycles/03-implementation.md` for code generation
4. **Testing Phase**: Use `cycles/04-testing.md` for test creation
5. **Integration Phase**: Use `cycles/05-integration.md` for integration testing
6. **Deployment Phase**: Use `cycles/06-deployment.md` for deployment verification

### Using Templates

For common tasks, use templates from `templates/` directory:
- Creating a new module: `templates/module-template.md`
- Adding API endpoints: `templates/api-endpoint-template.md`
- Database changes: `templates/database-migration-template.md`
- Writing tests: `templates/test-template.md`

### Context Reference

Before starting any task, review context files:
- `context/architecture.md` - System architecture overview
- `context/coding-standards.md` - PHP coding standards
- `context/database-schema.md` - Database schema reference
- `context/api-conventions.md` - API design patterns

## Project Context

### Technology Stack

- **Backend**: PHP 8.x, MySQL 8.x, REST API
- **Frontend**: HTML5, CSS3, JavaScript, jQuery, Bootstrap
- **Authentication**: JWT
- **Architecture**: Service Repository Pattern
- **Multi-tenant**: Supported

### Current Status

- **Database**: 78 tables implemented (10 migrations)
- **Modules**: 40+ modules covering all restaurant operations
- **Implementation**: 15 phases, 540 tasks
- **Testing**: Playwright E2E tests
- **Language**: Primary Indonesian, English switching capability

### Key Features

1. Unified Reconciliation Engine
2. Open API Architecture
3. True Offline Capability
4. Compliance Management
5. Multi-Location Native
6. AI-Powered Analytics
7. Supply Chain Management
8. Consumer-Facing Application

## Best Practices

1. **Always start with analysis** - Don't skip to implementation
2. **Use REASONS canvas** - Structure your prompts properly
3. **Iterate on prompts** - Fix prompts first when reality diverges
4. **Test continuously** - Generate tests alongside code
5. **Document decisions** - Capture architectural decisions in prompts
6. **Follow conventions** - Use established coding standards
7. **Security first** - Always consider security implications

## Evaluation

After each cycle, use evaluation checklists:
- `evaluations/code-review-checklist.md` - Review generated code
- `evaluations/test-coverage-checklist.md` - Verify test coverage
- `evaluations/security-checklist.md` - Security validation

## Workflow

```
Analysis → Design → Implementation → Testing → Integration → Deployment
    ↑                                                          ↓
    └────────────────── Iteration & Refinement ←──────────────┘
```

## Version Control

All prompts are version-controlled. When updating prompts:
1. Document the reason for change
2. Update the prompt with new learnings
3. Test the updated prompt
4. Commit with descriptive message

## References

- [Structured-Prompt-Driven Development (SPDD)](https://martinfowler.com/articles/structured-prompt-driven/)
- [Spec-Driven Development (SDD)](https://developer.microsoft.com/blog/spec-driven-development-ai-native-engineering)
- [AI-Driven Development Life Cycle](https://aws.amazon.com/blogs/devops/ai-driven-development-life-cycle/)

---

**Version**: 1.0  
**Last Updated**: 2026-07-05  
**Status**: Active
