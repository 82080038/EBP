# ESAMF Module Analysis Prompt

**Document ID:** ESAMF-MOD-001

**Version:** 1.0

**Purpose**

Prompt khusus untuk analisis modul bisnis repository secara mendalam sesuai standar Enterprise Software Asset Management Framework (ESAMF).

---

# OBJECTIVE

Lakukan analisis menyeluruh terhadap seluruh modul bisnis dalam repository saat ini.

Jangan melakukan perubahan source code.

Jangan melakukan refactoring.

Tugas Anda hanya:

* membaca modul-modul;
* memahami business logic;
* menginventarisasi dependency;
* mendokumentasikan;
* mengklasifikasikan.

---

# OUTPUT LOCATION

Hasil analisis ditempatkan pada:

```text
11_ENTERPRISE_SOFTWARE_ASSET_MANAGEMENT_FRAMEWORK/

07_MIGRATION/

<PROJECT_NAME>/

06_MODULE_ANALYSIS.md
```

---

# ANALYSIS CHECKLIST

## 1. Module Discovery

Identifikasi seluruh modul:

- Nama modul
- Lokasi folder
- Tujuan modul
- Fitur utama
- Business domain

## 2. Module Dependency Analysis

Untuk setiap modul, identifikasi:

- Dependency ke modul lain
- Dependency ke service layer
- Dependency ke repository layer
- Dependency ke external service
- Circular dependency

## 3. Business Logic Analysis

Untuk setiap modul, analisis:

- Core business rules
- Business validation
- Business calculation
- Business workflow
- Business exception

## 4. Data Flow Analysis

Petakan alur data:

- Input source
- Processing steps
- Output destination
- Data transformation
- Data validation

## 5. Integration Analysis

Identifikasi:

- API endpoint yang disediakan
- API endpoint yang dikonsumsi
- Event yang dipublish
- Event yang di-subscribe
- Queue job yang diproses
- Cache yang digunakan

## 6. Module Reusability Assessment

Untuk setiap modul, tentukan:

- Apakah product-specific?
- Apakah domain-specific?
- Apakah cross-product reusable?
- Apakah bisa menjadi EBP Core?
- Apakah bisa menjadi Shared Engine?

## 7. Module Complexity Analysis

Analisis:

- Cyclomatic complexity
- Code volume
- Business rule complexity
- Integration complexity
- Maintenance effort

## 8. Module Quality Assessment

Cek:

- Code organization
- Naming convention
- Documentation
- Test coverage
- Error handling
- Logging

## 9. EBP Module Standard Compliance

Bandingkan dengan standar EBP:

- Module structure
- Service pattern
- Repository pattern
- Controller pattern
- Validation pattern
- Error handling pattern

---

# OUTPUT FORMAT

## Section 1: Module Overview

```markdown
## Module Overview

- **Total Modules**: [count]
- **Core Modules**: [count]
- **Support Modules**: [count]
- **Integration Modules**: [count]
```

## Section 2: Module List

```markdown
## Module List

| Module | Location | Purpose | Complexity | Reusability |
|--------|----------|---------|------------|-------------|
| [name] | [path]   | [desc]  | [Low/Med/High] | [score] |
```

## Section 3: Detailed Module Analysis

```markdown
## Module: [module_name]

**Purpose**: [description]
**Location**: [path]
**Business Domain**: [domain]

### Features
- [feature]: [description]

### Business Rules
- [rule]: [description]

### Dependencies
**Internal**
- [module]: [dependency type]

**External**
- [service]: [purpose]

### Data Flow
```
[Input] → [Process] → [Output]
```

### Integration
**API Provided**
- [method] [path]: [description]

**API Consumed**
- [method] [path]: [description]

**Events**
- Publish: [event]
- Subscribe: [event]

### Complexity
- **Cyclomatic Complexity**: [score]
- **Code Volume**: [LOC]
- **Business Rules**: [count]
- **Integration Points**: [count]

### Quality
- **Code Organization**: [1-5]
- **Documentation**: [1-5]
- **Test Coverage**: [%]
- **Error Handling**: [1-5]

### Reusability Assessment
★★★★★ [description]
```

## Section 4: Dependency Graph

```markdown
## Module Dependency Graph

[Mermaid diagram showing module dependencies]
```

## Section 5: Module Classification

```markdown
## Module Classification

### EBP Core Candidates
- [module]: [reason]

### Shared Engine Candidates
- [module]: [reason]

### Product-Specific Modules
- [module]: [reason]

### Infrastructure Modules
- [module]: [reason]
```

## Section 6: EBP Module Compliance

```markdown
## EBP Module Standard Compliance

### Compliant
- [module]: [pattern]

### Non-Compliant
- [module]: [pattern] - [recommendation]

### Missing
- [pattern]: [recommendation]
```

## Section 7: Recommendations

```markdown
## Recommendations

### Refactoring
- [recommendation]

### Extraction
- [recommendation]

### Consolidation
- [recommendation]

### Standardization
- [recommendation]
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

Analisis modul dianggap selesai apabila:

- Seluruh modul telah teridentifikasi
- Seluruh dependency telah dipetakan
- Business logic telah didokumentasikan
- Data flow telah dipetakan
- Reusability assessment telah selesai
- EBP module compliance check telah dilakukan
- Rekomendasi telah disusun
