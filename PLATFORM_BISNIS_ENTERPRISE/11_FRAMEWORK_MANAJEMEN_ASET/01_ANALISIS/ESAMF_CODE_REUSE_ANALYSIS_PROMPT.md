# ESAMF Code Reuse Analysis Prompt

**Document ID:** ESAMF-REUSE-001

**Version:** 1.0

**Purpose**

Prompt khusus untuk analisis potensi code reuse dan ekstraksi komponen reusable sesuai standar Enterprise Software Asset Management Framework (ESAMF).

---

# OBJECTIVE

Lakukan analisis menyeluruh terhadap potensi code reuse dalam repository saat ini.

Jangan melakukan perubahan source code.

Jangan melakukan refactoring.

Tugas Anda hanya:

* membaca source code;
* mengidentifikasi komponen reusable;
* menilai kualitas dan kompleksitas;
* mengklasifikasikan kandidat;
* mendokumentasikan.

---

# OUTPUT LOCATION

Hasil analisis ditempatkan pada:

```text
11_ENTERPRISE_SOFTWARE_ASSET_MANAGEMENT_FRAMEWORK/

07_MIGRATION/

<PROJECT_NAME>/

14_REUSABLE_COMPONENT.md

15_PRODUCT_SPECIFIC_COMPONENT.md

16_EBP_CORE_CANDIDATE.md

17_SHARED_ENGINE_CANDIDATE.md
```

---

# ANALYSIS CHECKLIST

## 1. Component Discovery

Identifikasi seluruh komponen:

- Classes
- Interfaces
- Traits
- Services
- Repositories
- Helpers
- Utilities
- Middleware
- Validators
- Transformers
- Formatters
- Parsers
- Generators

## 2. Reusability Assessment

Untuk setiap komponen, nilai:

- Genericity (seberapa generic komponen ini)
- Coupling (seberapa tight coupling ke domain spesifik)
- Cohesion (seberapa cohesive fungsinya)
- Testability (seberapa mudah di-test)
- Documentation (seberapa baik dokumentasinya)
- Maintenance effort (seberapa mudah di-maintain)

## 3. Domain Specificity Analysis

Untuk setiap komponen, tentukan:

- Apakah domain-agnostic?
- Apakah domain-specific?
- Apakah product-specific?
- Apakah industry-specific?

## 4. Dependency Analysis

Analisis dependency komponen:

- External dependencies
- Internal dependencies
- Framework dependencies
- Library dependencies
- Configuration dependencies

## 5. Refactoring Effort Estimation

Untuk setiap komponen reusable, estimasi:

- Effort untuk extract (Low/Medium/High)
- Effort untuk generalize (Low/Medium/High)
- Effort untuk test (Low/Medium/High)
- Risk level (Low/Medium/High)

## 6. EBP Core Fit Analysis

Untuk setiap komponen, evaluasi:

- Apakah sesuai dengan EBP Core philosophy?
- Apakah berguna untuk multiple products?
- Apakah align dengan EBP architecture?
- Apakah memenuhi EBP quality standards?

## 7. Shared Engine Fit Analysis

Untuk setiap komponen, evaluasi:

- Apakah bisa menjadi standalone engine?
- Apakah memiliki interface yang jelas?
- Apakah bisa di-configure?
- Apakah bisa di-extend?

---

# REUSABILITY SCORING

Gunakan skala bintang 5:

★★★★★
- Dapat langsung dipindahkan ke EBP Core tanpa perubahan
- Domain-agnostic
- Well-documented
- Well-tested
- Low coupling

★★★★☆
- Perlu sedikit refactoring (1-2 jam)
- Hampir domain-agnostic
- Documentation cukup
- Test coverage > 80%
- Low-medium coupling

★★★☆☆
- Perlu refactoring sedang (1 hari)
- Ada beberapa domain-specific logic
- Documentation minimal
- Test coverage 50-80%
- Medium coupling

★★☆☆☆
- Perlu redesign (2-3 hari)
- Banyak domain-specific logic
- Tidak ada dokumentasi
- Test coverage < 50%
- High coupling

★☆☆☆☆
- Tidak direkomendasikan untuk reuse
- Sangat domain-specific
- Tightly coupled
- Tidak terdokumentasi
- Tidak ter-test

---

# CLASSIFICATION CRITERIA

## EBP Core Candidate
- Domain-agnostic
- Reusable across multiple products
- Align dengan EBP architecture
- High quality (★★★★★ atau ★★★★☆)
- Essential untuk platform

## Shared Engine Candidate
- Domain-specific tapi reusable
- Bisa menjadi standalone module
- Memiliki interface yang jelas
- Medium-high quality (★★★★☆ atau ★★★☆☆)
- Bisa di-configure dan di-extend

## Product-Specific Component
- Sangat domain-specific
- Hanya relevan untuk produk ini
- Tidak reusable untuk produk lain
- Quality apapun
- Tetap di product

## Infrastructure Component
- Infrastructure-related
- Deployment-specific
- Environment-specific
- Tidak reusable sebagai business logic
- Tetap di product atau jadi infra EBP

---

# OUTPUT FORMAT

## Section 1: Reuse Overview

```markdown
## Code Reuse Overview

- **Total Components Analyzed**: [count]
- **EBP Core Candidates**: [count]
- **Shared Engine Candidates**: [count]
- **Product-Specific**: [count]
- **Infrastructure**: [count]
```

## Section 2: Component Inventory

```markdown
## Component Inventory

| Component | Type | Location | Reusability Score | Classification |
|-----------|------|----------|-------------------|----------------|
| [name]    | [type]| [path]   | [★★★★★]          | [class]        |
```

## Section 3: EBP Core Candidates

```markdown
## EBP Core Candidates

### [Component Name]
**Location**: [path]
**Type**: [Service/Repository/Utility/etc]
**Score**: ★★★★★

**Purpose**: [description]

**Why EBP Core**:
- [reason 1]
- [reason 2]

**Refactoring Effort**: [Low/Medium/High]
**Risk Level**: [Low/Medium/High]

**Dependencies**:
- [dependency]: [impact]

**Recommendation**: [Immediate/Short-term/Long-term]
```

## Section 4: Shared Engine Candidates

```markdown
## Shared Engine Candidates

### [Component Name]
**Location**: [path]
**Type**: [Service/Repository/Utility/etc]
**Score**: ★★★★☆

**Purpose**: [description]

**Why Shared Engine**:
- [reason 1]
- [reason 2]

**Engine Name**: [proposed name]
**Interface**: [description]

**Refactoring Effort**: [Low/Medium/High]
**Risk Level**: [Low/Medium/High]

**Dependencies**:
- [dependency]: [impact]

**Recommendation**: [Immediate/Short-term/Long-term]
```

## Section 5: Product-Specific Components

```markdown
## Product-Specific Components

### [Component Name]
**Location**: [path]
**Type**: [Service/Repository/Utility/etc]
**Score**: ★★☆☆☆

**Purpose**: [description]

**Why Product-Specific**:
- [reason 1]
- [reason 2]

**Keep in Product**: [Yes/No]
```

## Section 6: Refactoring Roadmap

```markdown
## Refactoring Roadmap

### Phase 1: Quick Wins (1-2 days)
- [component]: [effort] - [benefit]

### Phase 2: Medium Effort (1-2 weeks)
- [component]: [effort] - [benefit]

### Phase 3: Major Refactoring (1-2 months)
- [component]: [effort] - [benefit]
```

## Section 7: Risk Assessment

```markdown
## Risk Assessment

### High Risk
- [component]: [risk] - [mitigation]

### Medium Risk
- [component]: [risk] - [mitigation]

### Low Risk
- [component]: [risk] - [mitigation]
```

---

# IMPORTANT RULES

- Jangan mengubah source code
- Jangan melakukan refactoring
- Fokus pada analisis dan dokumentasi
- Gunakan bahasa Indonesia untuk penjelasan
- Sertakan contoh code jika perlu

---

# Definition of Done

Analisis code reuse dianggap selesai apabila:

- Seluruh komponen telah teridentifikasi
- Reusability score telah ditentukan
- Klasifikasi telah selesai
- EBP Core candidates telah diidentifikasi
- Shared Engine candidates telah diidentifikasi
- Product-specific components telah diidentifikasi
- Refactoring roadmap telah disusun
