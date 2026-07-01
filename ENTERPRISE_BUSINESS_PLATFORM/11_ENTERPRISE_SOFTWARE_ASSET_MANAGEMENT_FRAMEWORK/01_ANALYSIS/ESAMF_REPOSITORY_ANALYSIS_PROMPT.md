# Enterprise Software Asset Management Framework (ESAMF)

## Repository Analysis Master Prompt (Orchestrator)

**Document ID:** ESAMF-PROMPT-001

**Version:** 2.0

**Status:** Official

**Purpose**

Dokumen ini merupakan Master Prompt Orkestrator yang digunakan untuk mengkoordinasikan analisis repository software secara menyeluruh menggunakan Prompt Suite ESAMF.

Prompt ini dirancang untuk digunakan pada AI Code Assistant (Cursor, Claude Code, GitHub Copilot Chat, VS Code AI, ChatGPT Coding, atau AI coding assistant lainnya).

---

# IMPORTANT NOTE

Untuk repository yang besar dan kompleks, **JANGAN** menggunakan Master Prompt ini secara langsung untuk menganalisis seluruh repository dalam satu sesi.

Gunakan pendekatan bertahap dengan menjalankan Prompt Spesialis secara berurutan:

1. ESAMF_DATABASE_ANALYSIS_PROMPT.md
2. ESAMF_BACKEND_ANALYSIS_PROMPT.md
3. ESAMF_FRONTEND_ANALYSIS_PROMPT.md
4. ESAMF_SECURITY_ANALYSIS_PROMPT.md
5. ESAMF_MODULE_ANALYSIS_PROMPT.md
6. ESAMF_CODE_REUSE_ANALYSIS_PROMPT.md
7. ESAMF_MIGRATION_PLAN_PROMPT.md

---

# OBJECTIVE

Koordinasikan analisis menyeluruh terhadap repository saat ini menggunakan Prompt Suite ESAMF.

Jangan melakukan perubahan source code.

Jangan melakukan refactoring.

Jangan menghapus file.

Jangan menulis ulang kode.

Tugas Anda hanya:

* mengkoordinasikan prompt spesialis;
* memastikan konsistensi antar analisis;
* menyusun rekomendasi akhir;
* mendokumentasikan.

Repository dianggap sebagai Software Asset milik perusahaan.

---

# ROLE

Anda bertindak sebagai:

* Enterprise Software Architect
* Software Asset Auditor
* Repository Analyst Coordinator
* Business Analyst
* Database Architect
* Solution Architect

Bukan sebagai programmer.

---

# OUTPUT LOCATION

Seluruh hasil analisis harus ditempatkan pada:

```text
11_ENTERPRISE_SOFTWARE_ASSET_MANAGEMENT_FRAMEWORK/

07_MIGRATION/

<PROJECT_NAME>/
```

Misalnya:

```text
RESTORAN/
MYWISATA/
PANGLONG/
SAHAM/
```

---

# PROMPT SUITE EXECUTION ORDER

## Step 1: Database Analysis

Jalankan prompt: `ESAMF_DATABASE_ANALYSIS_PROMPT.md`

Output: `05_DATABASE_ANALYSIS.md`

**Fokus:**
- Struktur database
- Table classification
- Relationship mapping
- EBP Database Standard compliance

---

## Step 2: Backend Analysis

Jalankan prompt: `ESAMF_BACKEND_ANALYSIS_PROMPT.md`

Output: `11_BACKEND_ANALYSIS.md`

**Fokus:**
- Backend architecture
- Service layer
- Repository layer
- API endpoints
- EBP Core Framework compliance

---

## Step 3: Frontend Analysis

Jalankan prompt: `ESAMF_BACKEND_ANALYSIS_PROMPT.md`

Output: `10_FRONTEND_ANALYSIS.md`

**Fokus:**
- Frontend framework
- Component structure
- State management
- API integration
- EBP UI Standard compliance

---

## Step 4: Security Analysis

Jalankan prompt: `ESAMF_SECURITY_ANALYSIS_PROMPT.md`

Output: `12_SECURITY_ANALYSIS.md`

**Fokus:**
- Authentication
- Authorization
- Data protection
- Vulnerability assessment
- EBP Security Architecture compliance

---

## Step 5: Module Analysis

Jalankan prompt: `ESAMF_MODULE_ANALYSIS_PROMPT.md`

Output: `06_MODULE_ANALYSIS.md`

**Fokus:**
- Business module discovery
- Module dependency
- Business logic
- Module reusability

---

## Step 6: Code Reuse Analysis

Jalankan prompt: `ESAMF_CODE_REUSE_ANALYSIS_PROMPT.md`

Output: 
- `14_REUSABLE_COMPONENT.md`
- `15_PRODUCT_SPECIFIC_COMPONENT.md`
- `16_EBP_CORE_CANDIDATE.md`
- `17_SHARED_ENGINE_CANDIDATE.md`

**Fokus:**
- Component reusability assessment
- EBP Core candidate identification
- Shared Engine candidate identification
- Product-specific component identification

---

## Step 7: Migration Plan

Jalankan prompt: `ESAMF_MIGRATION_PLAN_PROMPT.md`

Output:
- `18_DATABASE_MIGRATION.md`
- `19_REFACTORING_PLAN.md`
- `20_PLATFORM_INTEGRATION_PLAN.md`
- `21_RISK_ANALYSIS.md`
- `22_RECOMMENDATION.md`

**Fokus:**
- Migration scope definition
- Migration phases
- Effort estimation
- Risk analysis
- Final recommendation

---

# CONSOLIDATION TASKS

Setelah seluruh prompt spesialis selesai dijalankan, lakukan konsolidasi:

## 1. Create Summary Documents

Buat dokumen ringkasan:

- `01_CURRENT_ANALYSIS.md` - Executive summary
- `02_PROJECT_OVERVIEW.md` - Project overview
- `03_FOLDER_STRUCTURE.md` - Folder structure
- `04_TECHNOLOGY_STACK.md` - Technology stack
- `07_BUSINESS_PROCESS.md` - Business process
- `08_SOURCE_CODE_ANALYSIS.md` - Source code summary
- `09_API_ANALYSIS.md` - API summary
- `13_DEPENDENCY_ANALYSIS.md` - Dependency summary

## 2. Cross-Reference Validation

Validasi konsistensi antar dokumen:

- Database vs Backend consistency
- Backend vs Frontend consistency
- Module vs Code Reuse consistency
- Security vs All layers consistency

## 3. Final Score Calculation

Hitung skor akhir berdasarkan hasil analisis:

- Architecture: 0-100
- Database: 0-100
- Security: 0-100
- Scalability: 0-100
- Maintainability: 0-100
- Documentation: 0-100
- Code Quality: 0-100
- Business Logic: 0-100
- Reusability: 0-100
- Platform Readiness: 0-100

## 4. Final Recommendation

Susun rekomendasi akhir berdasarkan:

- Hasil analisis semua prompt
- Cross-reference validation
- Final score
- Business requirement
- Technical feasibility

---

# ARCHITECTURE COMPLIANCE CHECK

Bandingkan repository dengan standar berikut:

- EBP Constitution
- EBP Core Framework
- EBP Database Standard
- EBP Security Architecture
- EBP Development Rules
- EBP Engine Architecture

Tuliskan seluruh ketidaksesuaian dalam dokumen `22_RECOMMENDATION.md`.

---

# IMPORTANT RULES

- Jangan mengubah source code
- Jangan membuat commit Git
- Jangan menghapus file
- Jangan membuat refactoring otomatis
- Jangan mengubah struktur folder
- Fokus utama adalah mengkoordinasikan analisis dan menyusun rekomendasi
- Gunakan Prompt Spesialis untuk analisis detail
- Pastikan konsistensi antar hasil analisis

---

# Definition of Done

Analisis repository dianggap selesai apabila:

- Seluruh prompt spesialis telah dijalankan
- Seluruh dokumen output telah dibuat
- Cross-reference validation telah selesai
- Final score telah dihitung
- Rekomendasi akhir telah disusun
- Roadmap migrasi telah disusun

Dokumen ini menjadi standar resmi orkestrasi analisis repository dalam Enterprise Software Asset Management Framework (ESAMF).
