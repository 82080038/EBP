---
description: How to create a new module (controller, model, views) following project conventions
---

# Create a New Module

## Steps

1. **Create Model** at `app/models/ExampleName.php` — extends `Model`, uses PDO prepared statements
2. **Create Controller** at `app/controllers/ExampleNameController.php` — extends `Controller`, add `Middleware::requireRole()` in constructor
3. **Create View** at `app/views/role/example_name.php` — include header/footer layouts, escape output with `Helper::e()`
4. **Add API endpoint** if needed — return JSON via `$this->json()`, include CSRF token, validate input
5. **Add to sidebar** in `app/views/layouts/sidebar.php`

## Checklist
- [ ] PDO prepared statements (no string concatenation)
- [ ] Middleware role check in controller
- [ ] CSRF token in all POST forms
- [ ] Output escaped with `Helper::e()`
- [ ] Standard JSON response format
- [ ] Audit log for create/update/delete
- [ ] Input validation
- [ ] Responsive layout

## Reference
- `docs/03_DESAIN_ARSITEKTUR_APLIKASI.md`
- `docs/28_STANDAR_KODE_KONTRIBUSI.md`
- `docs/21_API_DESIGN_AJAX_JSON.md`
