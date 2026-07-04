---
description: Impact Check Rule for Application Development
---

# Impact Check Rule

> **Terakhir diperbarui**: 7 Mei 2026

## Purpose
When fixing or modifying one part of the application, always check for impacts on other parts that may be affected.

## Guidelines

### Before Making Changes
1. **Understand Dependencies**: Identify all files, functions, and components that depend on the code being modified.
2. **Check Usage**: Search for all usages of the function/variable being changed across the codebase.
3. **Consider Side Effects**: Think about how the change might affect:
   - User experience (UI/UX)
   - Business logic flows
   - Data integrity
   - Security implications
   - Performance

### After Making Changes
1. **Test Affected Areas**: Test not just the changed code, but also all related functionality.
2. **Run Automated Tests**: Execute all relevant test suites.
3. **Manual Verification**: Manually test the application to ensure no regressions.
4. **Check Cross-Role Impact**: Verify the change doesn't break functionality for different user roles.

### Common Impact Areas

#### Frontend Changes
- Sidebar navigation
- Active menu highlighting
- Form validation
- JavaScript event handlers
- CSS styling

#### Backend Changes
- API endpoints
- Database queries
- Permission checks
- Session management
- Error handling

#### Configuration Changes
- Database connections (3 DB: kewer, db_alamat_simple, db_orang)
- Cross-DB links (users/nasabah/cabang ↔ db_orang.people)
- Path configurations
- Environment variables
- Permission definitions

### Example Scenarios

1. **Changing a helper function**:
   - Search for all files using this function
   - Test each file's functionality
   - Verify no breaking changes in function signature

2. **Modifying database schema**:
   - Check all queries using the affected table
   - Update migration scripts
   - Test data integrity

3. **Updating UI components**:
   - Check responsive behavior
   - Verify accessibility
   - Test across different browsers

4. **Changing permission logic**:
   - Test all user roles
   - Verify menu visibility
   - Check API access controls

### Tools to Use
- `grep` / `Grep` for code search
- `find_by_name` for file search
- Puppeteer tests for UI verification
- Python scripts for logic checking

### Documentation
Always update relevant documentation when making changes that affect system behavior.
