# Project Context — Tour Guide Application

## Project Overview
Tour Guide Application (MyWisata) is a tourism marketplace web app built with PHP Native (Simple MVC + Repository Pattern + Service Layer), MySQL, Bootstrap 5, jQuery, and OpenStreetMap/Leaflet. It connects tourists with tour guides, destinations, hotels, restaurants, and cultural events.

## Tech Stack
- **Backend:** PHP 8.1+ (Native, no framework — custom MVC in `app/core/`)
- **Database:** MySQL 8.0+ (utf8mb4, 33 tables, InnoDB, database name: `mywisata`)
- **Frontend:** Bootstrap 5.3, jQuery 3.7, Font Awesome 6, SweetAlert2, DataTables, Select2, Chart.js
- **Map:** OpenStreetMap + Leaflet 1.9 (free, no API key needed)
- **Web Server:** Apache (mod_rewrite) or Nginx (PHP-FPM)
- **Local Dev:** XAMPP/LAMPP at `/opt/lampp/htdocs/mywisata/`

## Architecture
- **Pattern:** Simple MVC (Model-View-Controller) + Repository Pattern + Service Layer
- **Entry Point:** `index.php` (front controller → `app/core/App.php` router)
- **Routing:** URL-based (`?url=controller/method/params`) via `.htaccess` rewrite
- **Database:** PDO Singleton (`app/core/Database.php`), prepared statements only
- **Autoload:** `spl_autoload_register()` — no Composer needed (optional)
- **Auth:** Session-based, bcrypt password hash, RBAC middleware
- **API:** AJAX + JSON, all responses via `$this->json()` method
- **Prompting System:** Autonomous development framework in `prompting/` (config.json, cycle management, state tracking)

## Three User Roles
1. **admin** — Full system access (dashboard, user mgmt, approvals, reports, settings)
2. **wisatawan** — Consumer (search guide, book, buy tickets, order food, register events)
3. **tour_guide** — Provider (profile, schedule, accept bookings, earnings, reviews)

## Key Directories
- `docs/` — 43 documentation files (00-42, the blueprint for building this app)
- `prompting/` — Autonomous development prompting system (config.json, cycle templates, state tracking)
- `app/config/` — Configuration files (config.php, database.php)
- `app/core/` — Framework classes (App, Controller, Model, View, Database, Middleware, etc.)
- `app/controllers/` — Business logic controllers
- `app/models/` — Database interaction models (PDO)
- `app/views/` — HTML templates (layouts, auth, admin, wisatawan, tourguide, errors)
- `public/assets/` — CSS, JS, images, third-party libraries
- `public/uploads/` — User uploaded files (images, audio, documents, QR codes)
- `database/` — SQL migration and seed files
- `logs/` — Error log and audit log
- `cron/` — Cron job scripts (event reminder, rate limit cleanup)

## Important Constants (defined in index.php and app/config/config.php)
- `APP_ROOT` — Absolute path to project root (defined in `index.php`)
- `APP_START_TIME` — Microtime for performance tracking (defined in `index.php`)
- `BASE_URL` — App base URL (e.g., `http://localhost/mywisata/`)
- `APP_ENV` — `development` or `production`
- `APP_DEBUG` — `true` in dev, `false` in prod
- `CSRF_TOKEN` — Cross-site request forgery token (auto-generated per session)
- `MAX_UPLOAD_SIZE` — Max file upload size (default 5MB)

## Security Practices
- All SQL queries use PDO prepared statements (no string concatenation)
- All form POST requires CSRF token
- All output uses `Helper::e()` (htmlspecialchars) for XSS prevention
- Password hashed with `password_hash($pass, PASSWORD_BCRYPT)`
- Session: HttpOnly, Secure, SameSite, 30-minute timeout
- Rate limiting: 60 API requests/minute per user
- Audit log for all important actions
- File upload: MIME check, size limit, random filename

## Coding Conventions
- **PHP:** PascalCase classes, camelCase methods/variables, 4-space indent
- **JS:** camelCase functions/variables, 2-space indent, `const`/`let` (no `var`)
- **CSS:** kebab-case with `tg-` prefix for custom classes, 2-space indent
- **SQL:** UPPERCASE keywords, lowercase identifiers, snake_case tables/columns
- **Files:** PascalCase for class files, snake_case for view files

## Database
- Database name: `mywisata` (utf8mb4, utf8mb4_unicode_ci)
- 33 tables in `database/migration.sql`
- Seed data in `database/seed.sql`
- Default admin: `admin@mywisata.com` / `admin123`
- All PKs: `BIGINT UNSIGNED AUTO_INCREMENT`
- Coordinates: `DECIMAL(10,7)` for GPS accuracy

## How to Run Locally
1. Start XAMPP/LAMPP (Apache + MySQL)
2. Create database `mywisata` (utf8mb4, utf8mb4_unicode_ci)
3. Import `database/migration.sql` then `database/seed.sql`
4. Configure `app/config/database.php` and `app/config/config.php`
5. Set permissions: `chmod -R 777 public/uploads logs database/backup`
6. Open `http://localhost/mywisata/` in browser
7. Alternatively, configure via `prompting/config.json` for multi-environment setup (Linux/Windows)

## Current Status
- **Phase:** MVP Complete - Application fully implemented and tested
- **Entry Point:** `index.php` - Front controller with routing system
- **Testing:** 46 Playwright E2E tests passing (homepage, auth, destinations, hotels, restaurants, events, API, roles, tourguides)
- **Database:** MySQL schema and seed data imported (33 tables)
- **Features Implemented:**
  - Authentication (login, register, logout, CSRF protection)
  - Role-based access control (admin, wisatawan, tour_guide)
  - Public pages (home, destinations, hotels, restaurants, events, tourguides)
  - API endpoints (destinations, tourguides, hotels, restaurants, events, search)
  - Multi-language support (Indonesian, English)
  - Responsive UI with Bootstrap 5
- **Dev Server:** PHP built-in server with router.php for clean URLs (localhost:8080)
- **Next Steps:** Implement admin dashboard, user dashboard, booking system

## Configuration Files
- `.env.example` — Environment configuration template (copy to `.env`)
- `prompting/config.json` — Multi-environment config (Linux/Windows, DB credentials, API keys, permissions)
- `.editorconfig` — Editor formatting rules (4-space PHP, 2-space JS/CSS)
- `.gitignore` — Git ignore rules (excludes .env, logs, uploads, vendor)
- `CONTRIBUTING.md` — Contribution guide (Git workflow, coding standards, review checklist)

## Documentation Index
See `docs/00_DAFTAR_ISI.md` for complete table of contents of all 43 documentation files (00-42).

## GitHub Repository
- **URL:** https://github.com/82080038/mywisata
