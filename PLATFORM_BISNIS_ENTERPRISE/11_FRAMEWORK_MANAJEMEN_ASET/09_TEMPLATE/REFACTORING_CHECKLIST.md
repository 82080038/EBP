# Refactoring Checklist

**Template ID**: ESAMF-TEMPLATE-005

**Version**: 1.0

**Purpose**: Checklist for code refactoring

---

# Preparation

## Analysis

- [ ] Current code analyzed
- [ ] Refactoring opportunities identified
- [ ] Refactoring priorities established
- [ ] Refactoring plan created
- [ ] Refactoring plan approved

## Characterization Tests

- [ ] Characterization tests written
- [ ] Characterization tests passing
- [ ] Current behavior documented
- [ ] Expected behavior documented

## Branch Creation

- [ ] Refactoring branch created
- [ ] Branch name follows convention
- [ ] Branch based on correct commit

---

# Coding Standards

## PSR-12 Compliance

- [ ] PHP_CodeSniffer run
- [ ] PSR-12 violations identified
- [ ] PSR-12 violations fixed
- [ ] PSR-12 compliance verified

## Naming Conventions

- [ ] Class names follow convention
- [ ] Method names follow convention
- [ ] Variable names follow convention
- [ ] Constant names follow convention

## Documentation

- [ ] PHPDoc blocks added
- [ ] Method documentation complete
- [ ] Parameter documentation complete
- [ ] Return documentation complete

---

# Code Structure

## Extract Method

- [ ] Long methods identified
- [ ] Methods extracted
- [ ] Extracted methods tested
- [ ] Extracted methods documented

## Extract Class

- [ ] Classes with too many responsibilities identified
- [ ] Classes extracted
- [ ] Extracted classes tested
- [ ] Extracted classes documented

## Replace Conditional with Polymorphism

- [ ] Complex conditionals identified
- [ ] Polymorphism implemented
- [ ] Polymorphism tested
- [ ] Polymorphism documented

## Introduce Parameter Object

- [ ] Methods with too many parameters identified
- [ ] Parameter objects created
- [ ] Parameter objects tested
- [ ] Parameter objects documented

## Replace Magic Numbers with Constants

- [ ] Magic numbers identified
- [ ] Constants created
- [ ] Constants tested
- [ ] Constants documented

---

# Dependency Injection

## Direct Dependencies

- [ ] Direct dependencies identified
- [ ] Direct dependencies replaced with injection
- [ ] Injection tested
- [ ] Injection documented

## Interfaces

- [ ] Interfaces created
- [ ] Classes implement interfaces
- [ ] Interfaces tested
- [ ] Interfaces documented

## Service Providers

- [ ] Service providers created
- [ ] Service providers registered
- [ ] Service providers tested
- [ ] Service providers documented

---

# Security

## SQL Injection

- [ ] SQL injection vulnerabilities identified
- [ ] SQL injection fixed
- [ ] SQL injection tested
- [ ] SQL injection documented

## XSS

- [ ] XSS vulnerabilities identified
- [ ] XSS fixed
- [ ] XSS tested
- [ ] XSS documented

## CSRF

- [ ] CSRF protection added
- [ ] CSRF tested
- [ ] CSRF documented

## Input Validation

- [ ] Input validation added
- [ ] Input validation tested
- [ ] Input validation documented

---

# Performance

## Database Queries

- [ ] N+1 query problems identified
- [ ] N+1 query problems fixed
- [ ] Eager loading implemented
- [ ] Query performance tested

## Caching

- [ ] Caching opportunities identified
- [ ] Caching implemented
- [ ] Caching tested
- [ ] Caching documented

## Code Optimization

- [ ] Performance bottlenecks identified
- [ ] Performance bottlenecks fixed
- [ ] Performance tested
- [ ] Performance documented

---

# Testing

## Unit Tests

- [ ] Unit tests updated
- [ ] Unit tests passing
- [ ] Test coverage maintained
- [ ] Test coverage > 80%

## Integration Tests

- [ ] Integration tests updated
- [ ] Integration tests passing
- [ ] Integration coverage maintained
- [ ] Integration coverage > 60%

## Behavior Tests

- [ ] Characterization tests passing
- [ ] Behavior preserved
- [ ] No breaking changes
- [ ] Regression tests passing

---

# Review

## Self-Review

- [ ] Code follows EBP standards
- [ ] Behavior is preserved
- [ ] Tests pass
- [ ] No new issues introduced

## Peer Review

- [ ] Peer review requested
- [ ] Peer review completed
- [ ] Reviewer feedback addressed
- [ ] Changes approved

## Architecture Review

- [ ] Architecture review requested
- [ ] Architecture review completed
- [ ] Architecture concerns addressed
- [ ] Changes approved

## Security Review

- [ ] Security review requested
- [ ] Security review completed
- [ ] Security concerns addressed
- [ ] Changes approved

---

# Commit

## Staging

- [ ] Changes staged
- [ ] Staged changes verified
- [ ] No unintended changes

## Commit Message

- [ ] Commit message follows convention
- [ ] Commit message describes changes
- [ ] Commit message includes rationale
- [ ] Commit message includes impact

## Push

- [ ] Changes committed
- [ ] Changes pushed to remote
- [ ] Remote branch verified

---

# Documentation

## Code Documentation

- [ ] PHPDoc blocks updated
- [ ] Inline comments updated
- [ ] README updated
- [ ] Changelog updated

## Migration Documentation

- [ ] Migration guide updated
- [ ] Breaking changes documented
- [ ] Migration steps documented
- [ ] Rollback plan documented

---

# Sign-Off

**Developer**: [Name, Date]
**Reviewer**: [Name, Date]
**Architect**: [Name, Date]
