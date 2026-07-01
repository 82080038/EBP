# ESAMF Migration Plan Prompt

**Document ID:** ESAMF-MIG-001

**Version:** 1.0

**Purpose**

Prompt khusus untuk menyusun rencana migrasi repository ke EBP Platform sesuai standar Enterprise Software Asset Management Framework (ESAMF).

---

# OBJECTIVE

Susun rencana migrasi komprehensif berdasarkan hasil analisis yang telah dilakukan.

Jangan melakukan migrasi.

Jangan melakukan refactoring.

Tugas Anda hanya:

* menganalisis hasil analisis sebelumnya;
* menyusun prioritas migrasi;
* merencanakan tahapan;
* estimasi effort;
* identifikasi risiko;
* mendokumentasikan.

---

# OUTPUT LOCATION

Hasil analisis ditempatkan pada:

```text
11_ENTERPRISE_SOFTWARE_ASSET_MANAGEMENT_FRAMEWORK/

07_MIGRATION/

<PROJECT_NAME>/

18_DATABASE_MIGRATION.md

19_REFACTORING_PLAN.md

20_PLATFORM_INTEGRATION_PLAN.md

21_RISK_ANALYSIS.md

22_RECOMMENDATION.md
```

---

# MIGRATION STRATEGY CHECKLIST

## 1. Migration Scope Definition

Definisikan scope migrasi:

- Apa yang akan dipindahkan ke EBP Core?
- Apa yang akan menjadi Shared Engine?
- Apa yang tetap di Product?
- Apa yang akan dihapus?
- Apa yang akan ditulis ulang?

## 2. Migration Phases

Susun tahapan migrasi:

### Phase 1: Preparation
- Environment setup
- Team preparation
- Documentation review
- Risk assessment

### Phase 2: Database Migration
- Schema alignment
- Data migration
- Index optimization
- Constraint setup

### Phase 3: Core Extraction
- EBP Core component extraction
- Refactoring
- Testing
- Documentation

### Phase 4: Shared Engine Creation
- Engine extraction
- Interface definition
- Configuration
- Testing

### Phase 5: Product Refactoring
- Product-specific refactoring
- Core integration
- Engine integration
- Testing

### Phase 6: Platform Integration
- EBP Platform integration
- Configuration
- Deployment
- Validation

### Phase 7: Validation
- Functional testing
- Performance testing
- Security testing
- User acceptance testing

### Phase 8: Cutover
- Production deployment
- Data synchronization
- Monitoring
- Rollback plan

## 3. Effort Estimation

Estimasi effort untuk setiap phase:

- Person-days
- Timeline
- Resource requirement
- Dependency

## 4. Risk Analysis

Identifikasi risiko:

- Technical risk
- Business risk
- Schedule risk
- Resource risk
- Data risk

## 5. Rollback Plan

Susun rollback plan:

- Rollback trigger
- Rollback procedure
- Rollback validation
- Rollback communication

## 6. Success Criteria

Definisikan success criteria:

- Functional criteria
- Performance criteria
- Security criteria
- User experience criteria

---

# DATABASE MIGRATION PLAN

## Schema Alignment

Identifikasi perubahan schema yang diperlukan:

- Table rename
- Column rename
- Data type change
- Constraint addition
- Index addition
- Relationship modification

## Data Migration

Rencanakan data migration:

- Data mapping
- Data transformation
- Data validation
- Data cleansing
- Data migration script

## Migration Script

Susun migration script:

- Pre-migration validation
- Migration execution
- Post-migration validation
- Rollback script

---

# REFACTORING PLAN

## Component Refactoring

Untuk setiap komponen yang akan di-refactor:

- Current state
- Target state
- Refactoring steps
- Testing strategy
- Rollback plan

## Code Quality Improvement

Identifikasi improvement:

- Code organization
- Naming convention
- Documentation
- Error handling
- Logging
- Testing

## Performance Optimization

Identifikasi optimization:

- Query optimization
- Caching strategy
- Index optimization
- Code optimization

---

# PLATFORM INTEGRATION PLAN

## EBP Core Integration

Rencanakan integrasi dengan EBP Core:

- Authentication integration
- Authorization integration
- Audit trail integration
- Configuration integration
- Logging integration

## Shared Engine Integration

Rencanakan integrasi dengan Shared Engine:

- Engine selection
- Configuration
- Interface implementation
- Testing

## Product Configuration

Rencanakan product configuration:

- Product-specific settings
- Feature flags
- Customization
- Branding

---

# RISK ANALYSIS

## Technical Risk

Identifikasi:

- Data loss risk
- Performance degradation risk
- Compatibility risk
- Integration risk

## Business Risk

Identifikasi:

- Downtime risk
- User adoption risk
- Feature regression risk
- Cost overrun risk

## Mitigation Strategy

Untuk setiap risiko, susun:

- Prevention measure
- Detection measure
- Response measure
- Recovery measure

---

# FINAL RECOMMENDATION

## Migration Decision

Berdasarkan analisis, rekomendasikan:

- Apakah repository siap untuk migrasi?
- Apakah perlu refactoring sebelum migrasi?
- Apakah perlu pembagian menjadi beberapa produk?
- Apakah perlu ekstraksi shared engine?

## Implementation Roadmap

Susun roadmap implementasi:

- Phase 1: [timeline] - [deliverables]
- Phase 2: [timeline] - [deliverables]
- Phase 3: [timeline] - [deliverables]
- dst.

## Resource Requirement

Identifikasi resource:

- Developer count
- Skill requirement
- Timeline
- Budget

## Success Metrics

Definisikan success metrics:

- Technical metrics
- Business metrics
- User satisfaction metrics

---

# OUTPUT FORMAT

## Section 1: Migration Overview

```markdown
## Migration Overview

- **Migration Type**: [Full/Partial/Incremental]
- **Target Platform**: [EBP Platform]
- **Estimated Duration**: [timeline]
- **Total Effort**: [person-days]
- **Risk Level**: [Low/Medium/High]
```

## Section 2: Migration Scope

```markdown
## Migration Scope

### Move to EBP Core
- [component]: [reason]

### Create Shared Engine
- [component]: [engine name]

### Keep in Product
- [component]: [reason]

### Deprecate
- [component]: [reason]

### Rewrite
- [component]: [reason]
```

## Section 3: Migration Phases

```markdown
## Migration Phases

### Phase 1: Preparation ([timeline])
**Effort**: [person-days]
**Deliverables**:
- [deliverable]

**Dependencies**:
- [dependency]

**Risks**:
- [risk]
```

## Section 4: Database Migration Plan

```markdown
## Database Migration

### Schema Changes
| Table | Change | Type | Impact |
|-------|--------|------|--------|
| [table]| [change]| [DDL/DML]| [High/Med/Low]|

### Data Migration
- [mapping]: [transformation]

### Migration Scripts
- [script]: [purpose]
```

## Section 5: Refactoring Plan

```markdown
## Refactoring Plan

### Component: [name]
**Current**: [description]
**Target**: [description]
**Effort**: [person-days]
**Risk**: [Low/Medium/High]

**Steps**:
1. [step]
2. [step]

**Testing**:
- [test type]
```

## Section 6: Platform Integration

```markdown
## Platform Integration

### EBP Core Integration
- [component]: [integration approach]

### Shared Engine Integration
- [engine]: [integration approach]

### Product Configuration
- [config]: [value]
```

## Section 7: Risk Analysis

```markdown
## Risk Analysis

### Technical Risks
| Risk | Probability | Impact | Mitigation |
|------|-------------|--------|------------|
| [risk]| [High/Med/Low]| [High/Med/Low]| [strategy]|

### Business Risks
| Risk | Probability | Impact | Mitigation |
|------|-------------|--------|------------|
| [risk]| [High/Med/Low]| [High/Med/Low]| [strategy]|
```

## Section 8: Rollback Plan

```markdown
## Rollback Plan

### Rollback Triggers
- [trigger]: [condition]

### Rollback Procedure
1. [step]
2. [step]

### Rollback Validation
- [validation]: [criteria]
```

## Section 9: Success Criteria

```markdown
## Success Criteria

### Functional
- [criteria]: [metric]

### Performance
- [criteria]: [metric]

### Security
- [criteria]: [metric]

### User Experience
- [criteria]: [metric]
```

## Section 10: Final Recommendation

```markdown
## Final Recommendation

### Migration Decision
**Status**: [Ready/Not Ready/Conditional]
**Reason**: [explanation]

### Implementation Roadmap
**Phase 1**: [timeline] - [deliverables]
**Phase 2**: [timeline] - [deliverables]
**Phase 3**: [timeline] - [deliverables]

### Resource Requirement
- **Developers**: [count]
- **Skills**: [skills]
- **Timeline**: [timeline]
- **Budget**: [estimate]

### Next Steps
1. [action]
2. [action]
3. [action]
```

---

# IMPORTANT RULES

- Jangan melakukan migrasi
- Jangan melakukan refactoring
- Fokus pada perencanaan dan dokumentasi
- Gunakan bahasa Indonesia untuk penjelasan
- Sertakan timeline dan estimasi yang realistis

---

# Definition of Done

Rencana migrasi dianggap selesai apabila:

- Scope migrasi telah didefinisikan
- Tahapan migrasi telah disusun
- Effort telah diestimasi
- Risiko telah teridentifikasi
- Rollback plan telah disusun
- Success criteria telah didefinisikan
- Rekomendasi akhir telah dibuat
