# Contributing to Tour Guide Application

Terima kasih atas minat Anda untuk berkontribusi! Ikuti panduan di bawah ini.

---

## Quick Start

1. **Fork** repository di GitHub
2. **Clone** fork Anda:
   ```bash
   git clone https://github.com/YOUR_USERNAME/mywisata.git /opt/lampp/htdocs/mywisata
   ```
3. **Add upstream:**
   ```bash
   git remote add upstream https://github.com/82080038/mywisata.git
   ```
4. **Setup local dev** — ikuti [docs/27_PANDUAN_INSTALASI_LOKAL.md](docs/27_PANDUAN_INSTALASI_LOKAL.md)
5. **Baca dokumentasi** — mulai dari [docs/00_DAFTAR_ISI.md](docs/00_DAFTAR_ISI.md)

---

## Git Workflow

### Branch Strategy

```
main              → Production-ready code
├── develop       → Integration branch
│   ├── feature/nama-fitur
│   ├── fix/deskripsi-bug
│   └── refactor/area
```

### Branch Naming

| Tipe | Format | Contoh |
|------|--------|--------|
| Feature | `feature/{nama-fitur}` | `feature/booking-module` |
| Bug fix | `fix/{deskripsi}` | `fix/login-redirect` |
| Refactor | `refactor/{area}` | `refactor/model-queries` |
| Hotfix | `hotfix/{deskripsi}` | `hotfix/sql-injection` |

### Commit Message Format

```
type(scope): description
```

| Type | Deskripsi |
|------|-----------|
| `feat` | Fitur baru |
| `fix` | Bug fix |
| `refactor` | Refactoring |
| `docs` | Dokumentasi |
| `style` | Formatting, CSS |
| `test` | Testing |
| `chore` | Maintenance, config |

Contoh:
```
feat(booking): add booking cancellation flow
fix(auth): redirect after login based on role
docs(api): update endpoint list for hotel module
```

### Pull Request Flow

1. Buat branch dari `develop`: `git checkout -b feature/my-feature`
2. Commit perubahan dengan pesan yang jelas
3. Push: `git push origin feature/my-feature`
4. Buat Pull Request ke `develop`
5. Code review oleh minimal 1 reviewer
6. Approve & merge
7. Delete branch setelah merge

---

## Coding Standards

Baca lengkap di [docs/28_STANDAR_KODE_KONTRIBUSI.md](docs/28_STANDAR_KODE_KONTRIBUSI.md).

### Ringkasan

- **PHP:** 4-space indent, PascalCase class, camelCase method, `<?php` tags only
- **JS:** 2-space indent, `const`/`let` only, camelCase, AJAX via `API.helper()`
- **CSS:** 2-space indent, `tg-` prefix for custom classes
- **SQL:** UPPERCASE keywords, snake_case identifiers, PDO prepared statements only

### Wajib

- PDO prepared statements (no SQL injection)
- `Helper::e()` untuk output (no XSS)
- CSRF token di semua POST form
- `Middleware::requireRole()` di controller
- Input validation sebelum proses
- Audit log untuk aksi penting

---

## Code Review Checklist

### Umum
- [ ] Mengikuti naming convention
- [ ] Tidak ada debug code (var_dump, console.log)
- [ ] Tidak ada hardcoded credentials

### PHP
- [ ] PDO prepared statements
- [ ] Input divalidasi
- [ ] Output di-escape
- [ ] CSRF token di form POST
- [ ] Middleware role check
- [ ] Audit log untuk aksi penting

### JavaScript
- [ ] AJAX via API helper
- [ ] CSRF token disertakan
- [ ] Error handling dengan SweetAlert2

---

## Reporting Bugs

Gunakan GitHub Issues dengan template:

```
**Deskripsi bug:**
[Jelaskan bug dengan jelas]

**Langkah reproduksi:**
1. ...
2. ...
3. ...

**Expected:**
[Apa yang seharusnya terjadi]

**Actual:**
[Apa yang terjadi]

**Environment:**
- OS: [e.g., Ubuntu 22.04]
- PHP version: [e.g., 8.2]
- Browser: [e.g., Chrome 120]
```

---

## Development Checklist

Baca lengkap di [docs/29_CHECKLIST_PENGEMBANGAN.md](docs/29_CHECKLIST_PENGEMBANGAN.md).

---

## Pertanyaan?

- Baca [docs/00_DAFTAR_ISI.md](docs/00_DAFTAR_ISI.md) untuk indeks dokumentasi
- Baca [docs/31_KAMUS_ISTILAH_GLOSARIUM.md](docs/31_KAMUS_ISTILAH_GLOSARIUM.md) untuk istilah
- Buat GitHub Issue untuk pertanyaan teknis
