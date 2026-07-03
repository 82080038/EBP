# ESAMF Database Analysis Prompt

**Document ID:** ESAMF-DB-001

**Version:** 1.0

**Purpose**

Prompt khusus untuk analisis database repository secara mendalam sesuai standar Enterprise Software Asset Management Framework (ESAMF).

---

# OBJECTIVE

Lakukan analisis menyeluruh terhadap database repository saat ini.

Jangan melakukan perubahan pada database.

Jangan menjalankan migrasi.

Jangan mengubah schema.

Tugas Anda hanya:

* membaca file schema/migration;
* memahami struktur tabel;
* menginventarisasi relationship;
* mendokumentasikan;
* mengklasifikasikan.

---

# OUTPUT LOCATION

Hasil analisis ditempatkan pada:

```text
11_ENTERPRISE_SOFTWARE_ASSET_MANAGEMENT_FRAMEWORK/

07_MIGRATION/

<PROJECT_NAME>/

05_DATABASE_ANALYSIS.md
```

---

# ANALYSIS CHECKLIST

## 1. Database Overview

Identifikasi:

- Database engine (MySQL, PostgreSQL, etc.)
- Database name
- Character set
- Collation
- Total jumlah tabel
- Total jumlah view
- Total jumlah stored procedure
- Total jumlah trigger
- Total jumlah function

## 2. Table Classification

Klasifikasikan seluruh tabel menjadi:

### Master Tables
- Tabel referensi/master data
- Contoh: tenants, users, roles, permissions, categories, products

### Transaction Tables
- Tabel transaksi bisnis
- Contoh: orders, payments, reservations, inventory_transactions

### Audit Tables
- Tabel audit trail
- Contoh: audit_logs, change_history

### Configuration Tables
- Tabel konfigurasi
- Contoh: settings, system_config

### Temporary Tables
- Tabel temporary/cache
- Contoh: temp_sessions, cache_tables

## 3. Table Analysis

Untuk setiap tabel, identifikasi:

- Nama tabel
- Tujuan tabel
- Kolom-kolom:
  - Primary Key
  - Foreign Key
  - Unique Key
  - Index
  - Data type
  - Nullable
  - Default value
  - Constraints
- Relationship:
  - One-to-One
  - One-to-Many
  - Many-to-Many
  - Self-referencing
- Trigger
- Default values
- Check constraints

## 4. Relationship Mapping

Petakan seluruh relationship:

- Parent-Child relationships
- Lookup relationships
- Many-to-Many junction tables
- Cascade rules (ON DELETE, ON UPDATE)
- Circular dependencies

## 5. Index Analysis

Identifikasi:

- Primary key indexes
- Unique indexes
- Composite indexes
- Foreign key indexes
- Full-text indexes
- Spatial indexes
- Unused indexes (jika ada data)
- Missing indexes (berdasarkan query pattern)

## 6. Data Integrity

Cek:

- Foreign key constraints
- Unique constraints
- Check constraints
- NOT NULL constraints
- Default values
- Enum values
- Data validation rules

## 7. Migration Analysis

Identifikasi:

- Migration files
- Migration versioning
- Rollback capability
- Seed data
- Test data

## 8. Performance Considerations

Analisis:

- Table sizes (jika ada data)
- Index efficiency
- Query optimization opportunities
- Partitioning needs
- Normalization level (1NF, 2NF, 3NF, BCNF)
- Denormalization opportunities

## 9. Security Analysis

Identifikasi:

- Sensitive data columns (password, token, pii)
- Encryption requirements
- Access control needs
- Audit trail coverage
- Data retention policies

## 10. EBP Database Standard Compliance

Bandingkan dengan standar EBP:

- Naming convention
- Primary key pattern
- Foreign key pattern
- Audit trail pattern
- Soft delete pattern
- Tenant isolation pattern
- Timestamp pattern

---

# OUTPUT FORMAT

## Section 1: Database Overview

```markdown
## Database Overview

- **Engine**: [MySQL/PostgreSQL/etc]
- **Name**: [database_name]
- **Character Set**: [utf8mb4/utf8/etc]
- **Collation**: [collation]
- **Total Tables**: [count]
- **Total Views**: [count]
- **Total Procedures**: [count]
- **Total Triggers**: [count]
```

## Section 2: Table Classification

```markdown
## Table Classification

### Master Tables ([count])
- [table_name]: [description]

### Transaction Tables ([count])
- [table_name]: [description]

### Audit Tables ([count])
- [table_name]: [description]

### Configuration Tables ([count])
- [table_name]: [description]

### Temporary Tables ([count])
- [table_name]: [description]
```

## Section 3: Detailed Table Analysis

```markdown
## Table: [table_name]

**Purpose**: [description]
**Type**: [Master/Transaction/Audit/Config/Temp]

### Columns
| Column | Type | PK | FK | Unique | Nullable | Default | Description |
|--------|------|----|----|--------|---------|---------|-------------|
| [col]  | [type]| [x]| [x]| [x]    | [x]     | [x]     | [desc]      |

### Relationships
- **Parent**: [table] (via [column])
- **Children**: [table] (via [column])
- **Many-to-Many**: [table] (via [junction_table])

### Indexes
- [index_name]: [columns] ([type])

### Triggers
- [trigger_name]: [description]
```

## Section 4: Relationship Diagram

```markdown
## Relationship Map

[ASCII diagram or Mermaid diagram showing relationships]
```

## Section 5: EBP Compliance

```markdown
## EBP Database Standard Compliance

### Compliant
- [item]: [description]

### Non-Compliant
- [item]: [description] - [recommendation]

### Missing
- [item]: [recommendation]
```

## Section 6: Recommendations

```markdown
## Recommendations

### Immediate
- [recommendation]

### Short-term
- [recommendation]

### Long-term
- [recommendation]
```

---

# IMPORTANT RULES

- Jangan mengubah database
- Jangan menjalankan query DDL/DML
- Jangan membuat migration baru
- Fokus pada analisis dan dokumentasi
- Gunakan bahasa Indonesia untuk penjelasan
- Sertakan contoh SQL jika perlu

---

# Definition of Done

Analisis database dianggap selesai apabila:

- Seluruh tabel telah teridentifikasi
- Seluruh relationship telah dipetakan
- Seluruh index telah didokumentasikan
- Klasifikasi tabel telah selesai
- EBP compliance check telah dilakukan
- Rekomendasi telah disusun
