---
description: Setup and configure EBP Restaurant Backend
---

# EBP Restaurant Backend Setup Workflow

This workflow helps you set up and configure the EBP Restaurant Backend application.

## Prerequisites

- XAMPP installed and running
- PHP 8.x available
- Node.js and npm installed

## Setup Steps

1. **Start XAMPP Services**
   ```bash
   sudo /opt/lampp/lampp start
   ```

2. **Clone Repository** (if not already done)
   ```bash
   git clone https://github.com/82080038/EBP.git
   cd EBP
   ```

3. **Install npm dependencies**
   ```bash
   cd ebp-restaurant-backend
   npm install
   ```

4. **Setup Database**
   ```bash
   php setup_database.php
   ```

5. **Test Database Connection**
   ```bash
   php test_connection.php
   ```

6. **Seed Initial Data** (optional)
   ```bash
   php seed_data.php
   ```

## Configuration Files

- Database config: `ebp-restaurant-backend/config/database.php`
- API routes: `ebp-restaurant-backend/routes/api.php`
- Public entry: `ebp-restaurant-backend/public/index.php`

## Access Points

- API Base URL: `http://localhost/ebp-restaurant-backend/public/api/v1`
- Login endpoint: `POST /api/v1/auth/login`
- Default credentials: username `admin`, password `password`

## Testing

Run Playwright tests:
```bash
npx playwright test
```

## Common Issues

- **MySQL connection error**: Ensure XAMPP MySQL is running
- **Database not found**: Run `php setup_database.php`
- **Permission denied**: Check file permissions in `/opt/lampp/htdocs/EBP`
