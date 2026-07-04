---
description: Setup local development environment for PELAJARAN
---

# Setup Local Development - PELAJARAN

This workflow helps you set up the local development environment for the EBP Education Platform - Pelajaran.

## Prerequisites

- XAMPP installed (Apache + PHP 8.x + MySQL)
- Node.js (optional, for future enhancements)
- Git

## Steps

### Windows (XAMPP)

1. **Start XAMPP services**
   - Open XAMPP Control Panel
   - Start Apache and MySQL services

2. **Create database**
   ```bash
   # Open phpMyAdmin: http://localhost/phpmyadmin
   # Create database: db_merdeka_belajar
   # Import schema from README.md
   ```

3. **Configure database connection**
   - Create `config/database.php` with your MySQL credentials
   - Use the schema provided in README.md

4. **Set up project structure**
   ```bash
   # Create required directories
   mkdir config api assets/img/avatars assets/img/materia assets/css assets/js views
   ```

5. **Seed initial data**
   - Import sample data for mapel, modul_konten, and kuis tables
   - Use data from official Kemendikbud SIBI materials

### Linux (XAMPP/LAMP)

1. **Start XAMPP services**
   ```bash
   echo "8208" | sudo -S /opt/lampp/lampp start
   ```

2. **Create database**
   ```bash
   mysql -u root -e "CREATE DATABASE db_merdeka_belajar;"
   mysql -u root db_merdeka_belajar < database_schema.sql
   ```

3. **Configure database connection**
   - Create `config/database.php` with your MySQL credentials

4. **Set up project structure**
   ```bash
   mkdir -p config api assets/img/{avatars,materi} assets/{css,js} views
   ```

## Verification

- Access the application at: http://localhost/EBP/PLATFORM_BISNIS_ENTERPRISE/PRODUCTS/PELAJARAN/
- Verify database connection
- Test Web Speech API for text-to-speech functionality

## Troubleshooting

- If MySQL connection fails, check XAMPP MySQL service
- If assets don't load, verify folder permissions
- If Web Speech API doesn't work, check browser compatibility (Chrome/Edge recommended)
