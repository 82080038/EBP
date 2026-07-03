# Enterprise Business Platform (EBP)

# Development Rules

**Document ID:** EBP-ENTERPRISE-CONTROL-DEVELOPMENT-RULES-001
**Version:** 1.0
**Category:** Enterprise Control Layer
**Status:** Official Development Standard

---

# 1. Introduction

Dokumen ini mendefinisikan aturan pembangunan software pada Enterprise Business Platform (EBP).

Tujuan utama:

* menjaga konsistensi arsitektur;
* menjaga kualitas kode;
* memastikan AI-assisted development tetap terarah;
* mencegah technical debt;
* memastikan setiap produk EBP dapat berkembang dalam jangka panjang.

EBP dikembangkan dengan model:

```
Founder / Product Owner

        +

AI Development Assistant

        +

Automated Testing

        +

Enterprise Architecture
```

---

# 2. Development Philosophy

EBP menggunakan prinsip:

## Document First

Tidak ada pembangunan fitur tanpa pemahaman bisnis dan dokumentasi.

Alur:

```
Business Problem

↓

Business Analysis

↓

Business Process

↓

Database Design

↓

API Design

↓

Implementation

↓

Testing

↓

Release
```

---

# 3. Core Development Principle

Setiap kode yang dibuat harus memenuhi:

## 3.1 Reusable

Kode yang bersifat umum harus berada di Core.

Contoh:

Benar:

```
Authentication Engine

RBAC

Notification

Audit Trail
```

Salah:

```
RestaurantLoginService
```

---

## 3.2 Maintainable

Kode harus mudah dipahami dan diperbaiki.

Dilarang:

* kode tanpa dokumentasi;
* fungsi terlalu panjang;
* logika bisnis tersembunyi.

---

## 3.3 Scalable

Setiap desain harus mempertimbangkan:

* penambahan user;
* penambahan tenant;
* penambahan produk;
* peningkatan transaksi.

---

# 4. AI Assisted Development Rules

AI merupakan development partner, bukan pengganti arsitektur.

AI wajib mengikuti:

```
EBP Constitution

        ↓

Architecture Document

        ↓

Business Requirement

        ↓

Technical Specification

        ↓

Code Generation
```

---

# 5. Aturan Meminta AI Membuat Kode

Tidak boleh:

```
Buatkan fitur order
```

Harus:

```
Buatkan Order Module berdasarkan:

- EBP Architecture
- Service Repository Pattern
- Database Standard
- API Specification
- Security Standard
- Testing Requirement
```

---

# 6. Code Acceptance Process

Kode dari AI harus melalui:

```
Generate Code

↓

Architecture Review

↓

Security Review

↓

Testing

↓

Documentation Update

↓

Accept
```

---

# 7. Core vs Product Rule

Aturan utama EBP:

```
CORE

=
Generic Business Capability


PRODUCT

=
Industry Specific Implementation
```

---

## Core Example

Masuk Core:

```
User

Role

Permission

Tenant

Audit

Workflow

Notification

File Management
```

---

## Product Example

Restaurant:

```
Menu

Recipe

Kitchen

Table

Order

Food Cost
```

---

# 8. Dependency Rule

Aturan:

```
PRODUCT

boleh menggunakan

CORE


CORE

tidak boleh bergantung kepada

PRODUCT
```

Contoh:

Benar:

```
Restaurant ERP

↓

Inventory Engine
```

Salah:

```
Inventory Engine

↓

Restaurant Menu
```

---

# 9. Backend Architecture Rule

EBP menggunakan:

```
Controller

↓

Service

↓

Repository

↓

Database
```

---

## Controller Rule

Controller hanya:

* menerima request;
* validasi input;
* memanggil service;
* mengembalikan response.

Tidak boleh:

```
SQL Query

Business Calculation

Payment Logic
```

---

# 10. Business Logic Rule

Business logic wajib berada di:

```
Service Layer

Business Engine

Rule Engine
```

Contoh:

Salah:

```php
if($total > 100000)
{
discount=10;
}
```

Benar:

```
Pricing Engine

↓

Discount Rule

↓

Calculation
```

---

# 11. Database Development Rule

Semua tabel wajib mengikuti:

```sql
id

tenant_id

created_at

created_by

updated_at

updated_by

deleted_at

version
```

---

# 12. Database Naming Convention

Menggunakan:

```
snake_case
```

Contoh:

Benar:

```
restaurant_orders

order_details

inventory_items
```

Salah:

```
RestaurantOrders

OrderDetail
```

---

# 13. Migration Rule

Perubahan database wajib menggunakan migration.

Tidak boleh:

```
Edit database langsung production
```

Harus:

```
Migration File

↓

Testing

↓

Deploy
```

---

# 14. API Development Rule

Semua API menggunakan versioning.

Contoh:

```
/api/v1/users

/api/v1/orders

/api/v1/products
```

---

# 15. API Response Standard

Success:

```json
{
 "success":true,
 "data":{}
}
```

Error:

```json
{
 "success":false,
 "error":{
   "code":"INVALID_REQUEST"
 }
}
```

---

# 16. Security Rule

Semua modul wajib memiliki:

```
Authentication

Authorization

Validation

Audit Logging
```

---

# 17. Multi Tenant Rule

Semua data bisnis wajib memiliki:

```
tenant_id
```

Tidak boleh:

```
Query tanpa tenant filter
```

---

# 18. Audit Rule

Perubahan penting harus dicatat:

```
Who

What

When

Before

After
```

Contoh:

```
User:

Manager


Action:

Change Price


Before:

10000


After:

15000
```

---

# 19. Testing Rule

Tidak ada fitur dianggap selesai tanpa test.

Minimum:

```
Unit Test

API Test

Business Flow Test
```

---

# 20. End To End Testing Rule

Untuk fitur penting:

gunakan:

```
Playwright
```

Contoh:

Restaurant:

```
Login

↓

Create Order

↓

Kitchen

↓

Payment

↓

Receipt
```

---

# 21. Git Development Rule

Branch:

```
main

development

feature/*

bugfix/*
```

---

Contoh:

```
feature/restaurant-pos

feature/inventory-engine
```

---

# 22. Commit Rule

Commit harus jelas.

Benar:

```
Add restaurant order service

Fix inventory stock calculation
```

Salah:

```
update

fix

test
```

---

# 23. Documentation Rule

Setiap fitur wajib memiliki:

```
Requirement Document

Database Document

API Document

Testing Document
```

---

# 24. Architecture Decision Record

Keputusan besar harus dicatat.

Format:

```
ADR-XXX


Tanggal:

Keputusan:

Alasan:

Dampak:

Alternatif:
```

---

# 25. Feature Development Lifecycle

Setiap fitur:

```
Idea

↓

Analysis

↓

Specification

↓

Development

↓

Testing

↓

Documentation

↓

Release
```

---

# 26. Definition of Done

Fitur dianggap selesai jika:

```
[ ] Business requirement selesai

[ ] Database selesai

[ ] Backend selesai

[ ] Frontend selesai

[ ] API selesai

[ ] Security diperiksa

[ ] Test berhasil

[ ] Dokumentasi diperbarui
```

---

# 27. Technical Debt Rule

Technical debt harus dicatat.

Contoh:

```
TECH-DEBT-001


Masalah:

Legacy Query


Solusi:

Migration ke Repository Pattern


Prioritas:

Medium
```

---

# 28. Product Expansion Rule

Produk baru harus menggunakan:

```
EBP Core

+

Shared Engine

+

Product Module
```

Tidak boleh:

```
Copy aplikasi lama

Rename

Modify
```

---

# 29. Release Rule

Setiap release memiliki:

```
Version

Changelog

Migration

Testing Report

Rollback Plan
```

---

# 30. CI/CD Pipeline Rule

## 30.1 Pipeline Stages

```

Build

        ↓

Unit Test

        ↓

Integration Test

        ↓

Security Scan

        ↓

Code Quality Check

        ↓

Deploy to Staging

        ↓

E2E Test

        ↓

Deploy to Production
```

## 30.2 Automated Testing Gates

Pipeline tidak boleh lanjut jika:

- Unit test gagal
- Integration test gagal
- Security scan menemukan critical issue
- Code quality di bawah threshold

## 30.3 Code Quality Metrics

Minimum threshold:

```

Code Coverage: 80%

Cyclomatic Complexity: < 10

Code Duplication: < 3%

Technical Debt Ratio: < 5%
```

## 30.4 Security Gates

```

Dependency Vulnerability Scan

Container Image Scan

SAST (Static Application Security Testing)

Secret Detection
```

---

# 31. Infrastructure as Code Rule

## 31.1 IaC Principles

```

Declarative

Idempotent

Version Controlled

Testable

Reusable
```

## 31.2 IaC Tools

```

Terraform untuk infrastructure

Ansible untuk configuration

Docker Compose untuk local development

Kubernetes manifests untuk production
```

## 31.3 IaC Testing

```

Plan validation

Security scanning

Compliance checking

Drift detection
```

---

# 32. GitOps Workflow

## 32.1 GitOps Principles

```

Git as single source of truth

Desired state in Git

Automated sync to cluster

Drift detection and correction
```

## 32.2 GitOps Tools

```

ArgoCD

Flux

GitHub Actions

GitLab CI
```

## 32.3 GitOps Benefits

```

Increased productivity

Enhanced reliability

Better compliance

Faster recovery
```

---

# 33. Container Development Rule

## 33.1 Dockerfile Best Practices

```

Use multi-stage builds

Minimize layer count

Use specific base images

Scan images for vulnerabilities

Don't run as root
```

## 33.2 Container Orchestration

```

Kubernetes for production

Docker Compose for development

Helm for package management
```

## 33.3 Container Security

```

Image signing

Runtime security

Network policies

Resource limits
```

---

# 34. Monitoring and Observability Rule

## 34.1 Application Monitoring

```

APM (Application Performance Monitoring)

Error tracking

Performance profiling

User experience monitoring
```

## 34.2 Infrastructure Monitoring

```

CPU, memory, disk usage

Network metrics

Container metrics

Kubernetes metrics
```

## 34.3 Business Metrics

```

Transaction volume

Revenue tracking

User activity

Conversion rates
```

## 34.4 Alerting Strategy

```

Define alert thresholds

Set up escalation policies

Use on-call rotation

Document runbooks
```

---

# 35. Long Term Principle

EBP dibangun dengan prinsip:

```
Today:

Restaurant ERP


Tomorrow:

Enterprise Business Platform


Future:

Multi-Product Platform

Multi-Cloud

AI-Powered
```

Setiap keputusan harus mempertimbangkan:

* reuse;
* scalability;
* security;
* maintainability;
* observability;
* cloud-native readiness.

---

# Conclusion

EBP Development Rules menjadi kontrak pembangunan seluruh software EBP.

Dokumen ini memastikan:

```
Founder

+

AI

+

Architecture

+

Automation

+

CI/CD

+

Observability
```

dapat menghasilkan software enterprise yang konsisten dan dapat berkembang.

---

**END OF DOCUMENT**

Document ID:

EBP-ENTERPRISE-CONTROL-DEVELOPMENT-RULES-001

Version:

1.1
