# Database Audit Template

**Template ID**: ESAMF-TEMPLATE-003

**Version**: 1.0

**Purpose**: Template for auditing databases within repositories

---

# Database Information

**Database Type**: [MySQL/PostgreSQL/SQL Server/Oracle/MongoDB/Other]
**Database Version**: [Version number]
**Database Name**: [Database name]
**Database Size**: [Total size in GB]
**Table Count**: [Number of tables]
**View Count**: [Number of views]
**Stored Procedures**: [Number of stored procedures]
**Triggers**: [Number of triggers]
**Indexes**: [Number of indexes]

---

# Database Location

**Database Host**: [Host address]
**Database Port**: [Port number]
**Connection Method**: [Connection method]
**Authentication**: [Authentication method]

---

# Data Volume

**Total Rows**: [Total number of rows across all tables]
**Largest Table**: [Table with most rows]
**Fastest Growing Table**: [Table with highest growth rate]
**Growth Rate**: [Daily/Weekly/Monthly growth]

---

# Schema Analysis

## Table Structure

**Main Tables**:
- Table 1: [Purpose, columns, row count]
- Table 2: [Purpose, columns, row count]
- Table 3: [Purpose, columns, row count]

## Naming Convention

**Naming Convention**: [Consistent/Somewhat consistent/Inconsistent/No convention]
**Convention Pattern**: [snake_case/camelCase/PascalCase/Mixed]
**Convention Compliance**: [Fully compliant/Partially compliant/Not compliant]

## Normalization

**Normalization Level**: [Fully normalized (3NF)/Partially normalized/Denormalized/No normalization]
**Normalization Issues**: [List normalization issues]

## Relationships

**Relationships**: [Proper foreign keys/Some foreign keys/No foreign keys/No relationships]
**Key Relationships**:
- Table A → Table B: [Relationship type, cardinality]
- Table C → Table D: [Relationship type, cardinality]

## Data Integrity

**Data Integrity**: [Constraints defined/Some constraints/No constraints/Not applicable]
**Constraint Types**:
- Primary keys: [Yes/No]
- Foreign keys: [Yes/No]
- Unique constraints: [Yes/No]
- Check constraints: [Yes/No]
- Not null constraints: [Yes/No]

---

# Performance Analysis

## Query Performance

**Query Performance**: [Optimized/Somewhat optimized/Not optimized/Unknown]
**Slow Queries**: [List slow queries with execution time]
**Query Patterns**: [Common query patterns]

## Index Usage

**Index Usage**: [Proper indexes/Some indexes/No indexes/Not applicable]
**Index Strategy**: [Description of index strategy]
**Missing Indexes**: [List tables needing indexes]
**Unused Indexes**: [List unused indexes]

## Caching

**Caching**: [Database caching/Application caching/No caching/Not applicable]
**Cache Strategy**: [Description of cache strategy]
**Cache Hit Rate**: [Percentage]

---

# Data Quality Analysis

## Data Completeness

**Data Completeness**: [Complete/Mostly complete/Partially complete/Incomplete]
**Missing Data**: [List tables with missing data]
**Null Values**: [List columns with excessive null values]

## Data Consistency

**Data Consistency**: [Consistent/Mostly consistent/Inconsistent]
**Inconsistent Data**: [List data consistency issues]
**Duplicate Data**: [List tables with duplicate data]

## Data Accuracy

**Data Accuracy**: [Accurate/Mostly accurate/Inaccurate]
**Inaccurate Data**: [List data accuracy issues]
**Data Validation**: [Validation rules in place]

---

# Security Analysis

## Access Control

**Database Access**: [Proper user permissions/Some user permissions/No user permissions/Not applicable]
**User Roles**: [List database user roles]
**Privilege Assignment**: [Appropriate/Inappropriate/Unknown]

## Data Encryption

**Encryption**:
- Data at rest: [Encrypted/Not encrypted/Not applicable]
- Data in transit: [Encrypted/Not encrypted/Not applicable]
**Encrypted Fields**: [List encrypted fields]
**Encryption Method**: [Encryption algorithm]

## SQL Injection Protection

**SQL Injection Protection**: [Parameterized queries/Prepared statements/Some protection/No protection]
**Vulnerabilities**: [List SQL injection vulnerabilities]

## Sensitive Data

**Sensitive Data**: [List tables with sensitive data]
**PII**: [Personally Identifiable Information locations]
**Compliance**: [GDPR/PCI-DSS/HIPAA/Other compliance requirements]

---

# Migration Complexity Analysis

## Schema Changes Required

**Schema Changes**: [Minor changes/Moderate changes/Major changes/Complete redesign]
**Required Changes**:
- Naming convention changes: [List]
- Data type changes: [List]
- Relationship changes: [List]
- Constraint changes: [List]

## Data Migration Complexity

**Data Volume**: [Small (< 1GB)/Medium (1-10GB)/Large (10-100GB)/Very Large (> 100GB)]
**Data Complexity**: [Simple structure/Moderate complexity/High complexity/Very high complexity]
**Migration Risk**: [Low risk/Medium risk/High risk/Very high risk]

## Downtime Required

**Estimated Downtime**: [No downtime/Minimal downtime (< 1 hour)/Moderate downtime (1-4 hours)/Significant downtime (> 4 hours)]
**Downtime Strategy**: [Zero downtime/Minimal downtime/Scheduled downtime]

## Data Transformation Requirements

**Data Transformation**: [None required/Minimal transformation/Moderate transformation/Extensive transformation]
**Transformations Required**:
- [Transformation 1]
- [Transformation 2]
- [Transformation 3]

---

# EBP Compliance Analysis

## Naming Standards

**EBP Naming Compliance**: [Fully compliant/Partially compliant/Not compliant]
**Required Changes**:
- Table name changes: [List]
- Column name changes: [List]
- Index name changes: [List]

## Structure Standards

**EBP Structure Compliance**: [Fully compliant/Partially compliant/Not compliant]
**Required Changes**:
- Primary key changes: [List]
- Foreign key changes: [List]
- Timestamp changes: [List]
- Soft delete changes: [List]

## Relationship Standards

**EBP Relationship Compliance**: [Fully compliant/Partially compliant/Not compliant]
**Required Changes**:
- Foreign key additions: [List]
- Cascade rules: [List]
- Index additions: [List]

---

# Database Audit Summary

**Overall Assessment**: [Summary of database state]
**Migration Priority**: [High/Medium/Low]
**Recommended Next Steps**: [List next steps]

---

# Database Sign-Off

**Auditor**: [Name]
**Date**: [Date]
**Reviewer**: [Name]
**Review Date**: [Date]
