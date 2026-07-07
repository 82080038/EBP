# Configuration Management Guide

## Overview

This document describes the configuration management system for the EBP Restaurant ERP application, including how to manage environment-specific settings and avoid hardcoded values.

## Backend Configuration

### Environment Variables (.env)

The backend uses a `.env` file for configuration. Copy `.env.example` to `.env` and update with your actual values.

#### File Location
```
/opt/lampp/htdocs/EBP/PLATFORM_BISNIS_ENTERPRISE/PRODUCTS/RESTAURANT_ERP/BACKEND/.env
```

#### Configuration Variables

```bash
# Database Configuration
DB_HOST=localhost
DB_SOCKET=/opt/lampp/var/mysql/mysql.sock
DB_NAME=ebp_restaurant_db
DB_USER=ebp_app
DB_PASSWORD=ebp_secure_password_2026

# JWT Configuration
JWT_SECRET=ebp_secret_key_change_in_production
JWT_ALGORITHM=HS256
JWT_EXPIRATION=3600

# Application Configuration
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost:8000
API_BASE_URL=http://localhost:8000/api/v1

# CORS Configuration
CORS_ALLOWED_ORIGINS=*
CORS_ALLOWED_METHODS=GET,POST,PUT,DELETE,OPTIONS
CORS_ALLOWED_HEADERS=Content-Type,Authorization

# File Upload Configuration
UPLOAD_MAX_SIZE=10485760
ALLOWED_FILE_TYPES=jpg,jpeg,png,gif,pdf

# Email Configuration (for future use)
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls

# SMS Configuration (for future use)
SMS_API_KEY=
SMS_API_SECRET=
SMS_SENDER_ID=

# WhatsApp Configuration (for future use)
WHATSAPP_API_KEY=
WHATSAPP_PHONE_NUMBER_ID=
```

### Loading Mechanism

The `bootstrap.php` file loads environment variables from `.env`:

```php
// Load environment variables from .env file
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        list($name, $value) = explode('=', $line, 2);
        putenv(trim($name) . '=' . trim($value));
    }
}

// Set default values (only used if .env doesn't exist or doesn't have these values)
if (!getenv('DB_HOST')) putenv('DB_HOST=localhost');
// ... other defaults
```

### Accessing Configuration in Code

```php
// Get environment variable
$dbHost = getenv('DB_HOST');
$dbUser = getenv('DB_USER');

// Database class automatically uses environment variables
$db = new Database(); // Uses DB_HOST, DB_USER, DB_PASSWORD, etc.
```

## Frontend Configuration

### Configuration File

The frontend uses a centralized configuration file for all settings.

#### File Location
```
/opt/lampp/htdocs/EBP/PLATFORM_BISNIS_ENTERPRISE/PRODUCTS/RESTAURANT_ERP/FRONTEND/frontend/js/config.js
```

#### Configuration Structure

```javascript
const Config = {
    // API Configuration
    api: {
        baseURL: window.API_BASE_URL || 'http://localhost:8000/api/v1',
        timeout: 30000,
        retryAttempts: 3
    },
    
    // Application Configuration
    app: {
        name: 'EBP Restaurant',
        version: '1.0.0',
        environment: window.APP_ENV || 'development',
        debug: window.APP_DEBUG === 'true' || false
    },
    
    // Authentication Configuration
    auth: {
        tokenKey: 'authToken',
        userKey: 'ebp_user',
        tenantIdKey: 'tenantId',
        branchIdKey: 'branchId',
        tokenRefreshThreshold: 300
    },
    
    // UI Configuration
    ui: {
        itemsPerPage: 20,
        dateFormat: 'DD/MM/YYYY',
        timeFormat: 'HH:mm',
        currency: 'IDR',
        currencySymbol: 'Rp'
    },
    
    // Feature Flags
    features: {
        enableLoyalty: true,
        enableDelivery: true,
        enableReservations: true,
        enableKitchenDisplay: true,
        enableReports: true
    }
};
```

### Environment-Specific Configuration

For different environments (development, staging, production), you can set configuration values via:

1. **HTML meta tags** (recommended for production):
```html
<script>
window.API_BASE_URL = 'https://api.yourdomain.com/api/v1';
window.APP_ENV = 'production';
window.APP_DEBUG = 'false';
</script>
```

2. **Build process** (if using bundlers):
```javascript
// webpack.config.js
const webpack = require('webpack');

module.exports = {
    // ...
    plugins: [
        new webpack.DefinePlugin({
            'window.API_BASE_URL': JSON.stringify(process.env.API_BASE_URL),
            'window.APP_ENV': JSON.stringify(process.env.NODE_ENV)
        })
    ]
};
```

### Accessing Configuration in Code

```javascript
// API Client uses configuration
class APIClient {
    constructor() {
        this.baseURL = Config.api.baseURL;
        // ...
    }
}

// Access configuration anywhere
const apiURL = Config.api.baseURL;
const isDebug = Config.app.debug;
```

## Security Best Practices

### Development Environment

**Current Policy**: `.env` and configuration files are **NOT ignored** in Git during development for easier collaboration.

**Benefits**:
- Easier onboarding for new developers
- Shared development configuration
- Quick setup without manual configuration

**Precautions**:
- Use development-appropriate passwords
- Change sensitive values before production deployment
- Never commit production secrets

### Production Deployment

1. **Use environment-specific .env files**:
```bash
# Development (committed to Git)
.env

# Production (NOT committed to Git)
.env.production
.env.staging
```

2. **Update .gitignore for production**:
```gitignore
# Configuration Files
.env.production
.env.staging
.env.local
```

3. **Use strong passwords** in production
4. **Change JWT secret** to a random, secure value
5. **Set APP_DEBUG=false** in production
6. **Restrict CORS origins** to specific domains
7. **Use environment-specific configuration** for each deployment

### Example Production .env

```bash
# Database Configuration
DB_HOST=production-db-server.com
DB_SOCKET=
DB_NAME=ebp_restaurant_prod
DB_USER=ebp_prod_user
DB_PASSWORD=STRONG_RANDOM_PASSWORD_HERE

# JWT Configuration
JWT_SECRET=RANDOM_256_BIT_SECRET_KEY_HERE
JWT_ALGORITHM=HS256
JWT_EXPIRATION=3600

# Application Configuration
APP_ENV=production
APP_DEBUG=false
APP_URL=https://restaurant.yourdomain.com
API_BASE_URL=https://api.yourdomain.com/api/v1

# CORS Configuration
CORS_ALLOWED_ORIGINS=https://restaurant.yourdomain.com,https://admin.yourdomain.com
CORS_ALLOWED_METHODS=GET,POST,PUT,DELETE,OPTIONS
CORS_ALLOWED_HEADERS=Content-Type,Authorization
```

## Hardcoded Values Removed

### Backend
- ✅ Database credentials moved to `.env`
- ✅ JWT configuration moved to `.env`
- ✅ Application URLs moved to `.env`
- ✅ Default values kept as fallbacks in `bootstrap.php`

### Frontend
- ✅ API base URL moved to `config.js`
- ✅ Configuration loaded before other scripts
- ✅ Support for environment-specific overrides

### Test Files
- ⚠️ Test files still contain hardcoded values (acceptable for testing)
- ⚠️ Documentation files still contain examples (acceptable)

## Migration Guide

### For Existing Deployments

1. **Create .env file**:
```bash
cd /opt/lampp/htdocs/EBP/PLATFORM_BISNIS_ENTERPRISE/PRODUCTS/RESTAURANT_ERP/BACKEND
cp .env.example .env
```

2. **Update .env with production values**:
```bash
nano .env
# Update DB_PASSWORD, JWT_SECRET, APP_URL, etc.
```

3. **Update frontend config**:
```bash
cd /opt/lampp/htdocs/EBP/PLATFORM_BISNIS_ENTERPRISE/PRODUCTS/RESTAURANT_ERP/FRONTEND/frontend
nano js/config.js
# Update baseURL for production
```

4. **Test the configuration**:
```bash
# Backend
cd /opt/lampp/htdocs/EBP/PLATFORM_BISNIS_ENTERPRISE/PRODUCTS/RESTAURANT_ERP/BACKEND
php -S localhost:8000 -t public

# Frontend
# Open browser and test API connectivity
```

## Troubleshooting

### Database Connection Issues

1. Check `.env` file exists
2. Verify database credentials in `.env`
3. Check database server is running
4. Review error logs

### API Connection Issues

1. Check `Config.api.baseURL` in browser console
2. Verify backend server is running
3. Check CORS configuration
4. Review network tab in browser dev tools

### Environment Variables Not Loading

1. Verify `.env` file is in correct location
2. Check file permissions
3. Ensure no syntax errors in `.env`
4. Check `bootstrap.php` is being loaded

## Version History

- **v1.0** (2026-07-06): Initial configuration management system
  - Backend .env support
  - Frontend config.js
  - Removed hardcoded credentials
  - Added fallback mechanisms
