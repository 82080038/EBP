# Deployment Guide

## Prerequisites

- PHP 8.0 or higher
- MySQL 5.7 or higher
- Node.js 16 or higher (for Playwright tests)
- XAMPP/LAMP stack (recommended for development)

## Installation

### 1. Clone Repository
```bash
git clone <repository-url>
cd ebp-restaurant-backend
```

### 2. Install Dependencies
```bash
npm install
npx playwright install chromium
```

### 3. Database Setup

#### Option A: Import from current data (recommended for development)
```bash
mysql -u root --socket=/opt/lampp/var/mysql/mysql.sock ebp_restaurant_db < database/current_data.sql
```

#### Option B: Import schema only
```bash
mysql -u root --socket=/opt/lampp/var/mysql/mysql.sock ebp_restaurant_db < database/schema.sql
php seed_data.php
```

### 4. Configure Database Connection

Edit `config/database.php` if needed:
```php
private $host = "localhost";
private $socket = "/opt/lampp/var/mysql/mysql.sock";
private $dbname = "ebp_restaurant_db";
private $username = "root";
private $password = "";
```

### 5. Start PHP Server

Development server:
```bash
php -S localhost:8000 -t public
```

Production: Configure Apache/Nginx to point to `public/` directory

## Running Tests

### Run all tests
```bash
npm test
```

### Run tests with headed browser
```bash
npm run test:headed
```

### Run tests with UI mode
```bash
npm run test:ui
```

### View test report
```bash
npm run test:report
```

## Default Credentials

After running `seed_data.php`:
- Username: `admin`
- Password: `admin123`

## API Base URL

- Development: `http://localhost:8000/api/v1`
- Production: `https://your-domain.com/api/v1`

## File Structure

```
ebp-restaurant-backend/
├── config/          # Configuration files
├── core/            # Core classes (Router, Response, JWT, etc.)
├── database/        # SQL files and database exports
├── frontend/        # Frontend assets (kiosk, mobile)
├── modules/         # Business logic modules
├── public/          # Public web root
├── routes/          # API routes
├── tests/           # Playwright tests
├── seed_data.php    # Database seeding script
└── setup_database.php # Database setup script
```

## Security Notes

- Change default admin password in production
- Use environment variables for sensitive data
- Enable HTTPS in production
- Configure proper CORS settings
- Implement rate limiting for API endpoints

## Troubleshooting

### Database Connection Error
- Check MySQL service is running
- Verify socket path in `config/database.php`
- Ensure database exists

### API 500 Errors
- Check PHP error logs
- Verify all required PHP extensions are installed
- Ensure file permissions are correct

### Playwright Test Failures
- Ensure PHP server is running on port 8000
- Check database is seeded with test data
- Verify browser is installed: `npx playwright install`
