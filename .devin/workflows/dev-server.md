---
description: Start the EBP Restaurant development server
---

# Development Server Workflow

This workflow starts the PHP development server for the EBP Restaurant backend.

## Steps

1. **Start XAMPP services** (if not already running)
   ```bash
   echo "8208" | sudo -S /opt/lampp/lampp start
   ```

2. **Navigate to backend directory**
   ```bash
   cd /opt/lampp/htdocs/EBP/ebp-restaurant-backend
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

- Mobile App: http://localhost:8000/frontend/mobile
- Kiosk App: http://localhost:8000/frontend/kiosk

## Default Credentials

- Username: admin
- Password: admin123

## Stopping the Server

Press Ctrl+C in the terminal to stop the PHP development server.
