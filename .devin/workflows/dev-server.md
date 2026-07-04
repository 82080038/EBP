---
description: Start the RESTAURANT_ERP development server
---

# Development Server Workflow - RESTAURANT_ERP

This workflow starts the PHP development server for the RESTAURANT_ERP backend.

## Steps

### Windows (XAMPP)

1. **Start XAMPP services**
   - Open XAMPP Control Panel
   - Start Apache and MySQL services

2. **Navigate to backend directory**
   ```bash
   cd C:\xampp\htdocs\EBP\PLATFORM_BISNIS_ENTERPRISE\PRODUCTS\RESTAURANT_ERP\BACKEND
   ```

3. **Start PHP development server**
   ```bash
   php -S localhost:8000 -t public
   ```

### Linux (XAMPP/LAMP)

1. **Start XAMPP services** (if not already running)
   ```bash
   echo "8208" | sudo -S /opt/lampp/lampp start
   ```

2. **Navigate to backend directory**
   ```bash
   cd /opt/lampp/htdocs/EBP/PLATFORM_BISNIS_ENTERPRISE/PRODUCTS/RESTAURANT_ERP/BACKEND
   ```

3. **Start PHP development server**
   ```bash
   php -S localhost:8000 -t public
   ```

The server will be available at: http://localhost:8000

## API Endpoints

- Base URL: http://localhost:8000/api/v1
- Authentication: POST /api/v1/auth/login
- API Documentation: See API_DOCUMENTATION.md

## Frontend Access

Frontend files are now located in `PLATFORM_BISNIS_ENTERPRISE/PRODUCTS/RESTAURANT_ERP/FRONTEND/`

- Mobile App: http://localhost:8000/frontend/mobile
- Kiosk App: http://localhost:8000/frontend/kiosk

## Project Structure

```
PLATFORM_BISNIS_ENTERPRISE/PRODUCTS/RESTAURANT_ERP/
├── BACKEND/          (PHP API server)
│   ├── public/
│   ├── core/
│   ├── modules/
│   └── routes/
├── FRONTEND/         (Frontend assets)
│   ├── mobile/
│   ├── kiosk/
│   ├── css/
│   └── js/
├── DATABASE/         (Database schema & migrations)
└── DOCUMENTATION/    (Documentation)
```

## Default Credentials

- Username: admin
- Password: admin123

## Stopping the Server

Press Ctrl+C in the terminal to stop the PHP development server.
