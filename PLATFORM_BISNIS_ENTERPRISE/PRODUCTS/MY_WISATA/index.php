<?php
/**
 * MyWisata Application - Front Controller
 * 
 * This is the main entry point for the Tour Guide Application.
 * All requests are routed through this file.
 * 
 * APPLICATION INFO:
 * - Name: MyWisata Application
 * - Description: Platform marketplace untuk layanan pariwisata
 * - Tech Stack: PHP 8.1+ (Native MVC), MySQL 8.0+, Bootstrap 5, jQuery, OpenStreetMap/Leaflet
 * - Architecture: Simple MVC with Repository Pattern and Service Layer
 * 
 * DEVELOPMENT:
 * - This application is developed using the Prompting System for autonomous development
 * - See: prompting/README.md for more information about the prompting system
 * - Config: prompting/config.json for multi-environment configuration (Windows & Linux)
 * 
 * FUTURE DEVELOPMENT:
 * - The prompting system will autonomously develop all modules
 * - See: prompting/05_cycle/00_MASTER_PROMPTING_CYCLE.md for the development cycle
 * - Starting module: 05_DESAIN_DATABASE_MYSQL_ERD
 * 
 * LICENSE: MIT
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-06-30
 */

// Define application constants
define('APP_ROOT', dirname(__FILE__));
define('APP_START_TIME', microtime(true));

// Load environment configuration
if (file_exists(APP_ROOT . '/app/config/config.php')) {
    require_once APP_ROOT . '/app/config/config.php';
} else {
    // Fallback if config doesn't exist yet
    define('APP_ENV', 'development');
    define('APP_DEBUG', true);
    define('BASE_URL', 'http://localhost/mywisata/');
    define('APP_NAME', 'MyWisata Application');
}

// Error reporting based on environment
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Set timezone
date_default_timezone_set('Asia/Jakarta');

// Load core classes
if (file_exists(APP_ROOT . '/app/core/App.php')) {
    require_once APP_ROOT . '/app/core/Database.php';
    require_once APP_ROOT . '/app/core/App.php';
    require_once APP_ROOT . '/app/core/Controller.php';
    require_once APP_ROOT . '/app/core/Model.php';
    require_once APP_ROOT . '/app/core/View.php';
    require_once APP_ROOT . '/app/middleware/Middleware.php';
    require_once APP_ROOT . '/app/helpers/Session.php';
    require_once APP_ROOT . '/app/helpers/Validator.php';
    require_once APP_ROOT . '/app/helpers/Logger.php';
    require_once APP_ROOT . '/app/helpers/FileUpload.php';
    require_once APP_ROOT . '/app/helpers/Email.php';
    require_once APP_ROOT . '/app/helpers/SMS.php';
    require_once APP_ROOT . '/app/helpers/RateLimiter.php';
    require_once APP_ROOT . '/app/helpers/Language.php';
    
    // Start session
    Session::start();
    // Language helper
    Language::getLanguage();
    
    // Initialize 
    // Initialize and run the application
    $app = new App();
    $app->run();
} else {
    // If core classes don't exist yet, show setup page
    showSetupPage();
}

/**
 * Show setup page when application is not yet configured
 */
function showSetupPage()
{
    ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyWisata Application - Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .setup-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 900px;
            padding: 40px;
        }
        .app-logo {
            font-size: 4rem;
            color: #667eea;
            margin-bottom: 20px;
        }
        .feature-icon {
            font-size: 2rem;
            color: #764ba2;
            margin-bottom: 10px;
        }
        .tech-badge {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 20px;
            padding: 5px 15px;
            margin: 5px;
            display: inline-block;
            font-size: 0.9rem;
        }
        .setup-status {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="setup-card">
            <div class="text-center">
                <i class="fas fa-map-marked-alt app-logo"></i>
                <h1 class="mb-3">MyWisata Application</h1>
                <p class="lead text-muted mb-4">Platform Marketplace untuk Layanan Pariwisata</p>
            </div>

            <div class="setup-status">
                <i class="fas fa-tools me-2"></i>
                <strong>Status:</strong> Application is being set up. Core files are not yet installed.
            </div>

            <h4 class="mb-3">Tentang Aplikasi</h4>
            <p class="text-muted">
                MyWisata Application adalah platform marketplace yang menghubungkan wisatawan dengan tour guide profesional, 
                destinasi wisata, hotel, restoran, dan event budaya. Aplikasi ini dibangun dengan PHP Native (Simple MVC), 
                MySQL, Bootstrap, jQuery, dan OpenStreetMap/Leaflet.
            </p>

            <h4 class="mb-3 mt-4">Fitur Utama</h4>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="text-center">
                        <i class="fas fa-user-tie feature-icon"></i>
                        <h6>Tour Guide Booking</h6>
                        <small class="text-muted">Cari & booking tour guide profesional</small>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="text-center">
                        <i class="fas fa-ticket-alt feature-icon"></i>
                        <h6>E-Ticket dengan QR</h6>
                        <small class="text-muted">Pembelian tiket & verifikasi QR code</small>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="text-center">
                        <i class="fas fa-map feature-icon"></i>
                        <h6>Peta Interaktif</h6>
                        <small class="text-muted">OpenStreetMap dengan geolocation</small>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="text-center">
                        <i class="fas fa-hotel feature-icon"></i>
                        <h6>Hotel & Homestay</h6>
                        <small class="text-muted">Pencarian & booking akomodasi</small>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="text-center">
                        <i class="fas fa-utensils feature-icon"></i>
                        <h6>Restoran & UMKM</h6>
                        <small class="text-muted">Pemesanan makanan online</small>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="text-center">
                        <i class="fas fa-robot feature-icon"></i>
                        <h6>AI Tour Guide</h6>
                        <small class="text-muted">Chatbot rekomendasi destinasi</small>
                    </div>
                </div>
            </div>

            <h4 class="mb-3 mt-4">Tech Stack</h4>
            <div class="mb-3">
                <span class="tech-badge"><i class="fab fa-php me-1"></i>PHP 8.1+</span>
                <span class="tech-badge"><i class="fas fa-database me-1"></i>MySQL 8.0+</span>
                <span class="tech-badge"><i class="fab fa-bootstrap me-1"></i>Bootstrap 5</span>
                <span class="tech-badge"><i class="fas fa-code me-1"></i>jQuery</span>
                <span class="tech-badge"><i class="fas fa-map me-1"></i>OpenStreetMap</span>
                <span class="tech-badge"><i class="fas fa-leaf me-1"></i>Leaflet</span>
            </div>

            <h4 class="mb-3 mt-4">Pengembangan</h4>
            <p class="text-muted">
                Aplikasi ini dikembangkan menggunakan <strong>Prompting System</strong> untuk autonomous development. 
                Sistem prompting memungkinkan AI development assistant (Cascade/Devin) untuk mengembangkan aplikasi 
                secara mandiri dan proaktif.
            </p>
            
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Informasi Penting:</strong>
                <ul class="mb-0 mt-2">
                    <li>Lihat <code>prompting/README.md</code> untuk panduan sistem prompting</li>
                    <li>Lihat <code>prompting/README_SETUP.md</code> untuk panduan setup konfigurasi</li>
                    <li>Konfigurasi environment di <code>prompting/config.json</code> (multi-environment: Windows & Linux)</li>
                    <li>Lihat <code>docs/27_PANDUAN_INSTALASI_LOKAL.md</code> untuk panduan instalasi lengkap</li>
                </ul>
            </div>

            <h4 class="mb-3 mt-4">Langkah Selanjutnya</h4>
            <ol class="text-muted">
                <li>Konfigurasi <code>prompting/config.json</code> sesuai environment Anda (Linux/Windows)</li>
                <li>Setup database MySQL dan import schema dari <code>database/migration.sql</code></li>
                <li>Konfigurasi <code>app/config/config.php</code> dan <code>app/config/database.php</code></li>
                <li>Jalankan prompting system untuk autonomous development</li>
            </ol>

            <div class="text-center mt-4">
                <a href="prompting/README_SETUP.md" class="btn btn-primary btn-lg me-2">
                    <i class="fas fa-cog me-2"></i>Panduan Setup
                </a>
                <a href="docs/27_PANDUAN_INSTALASI_LOKAL.md" class="btn btn-outline-primary btn-lg">
                    <i class="fas fa-book me-2"></i>Panduan Instalasi
                </a>
            </div>

            <hr class="my-4">
            
            <div class="text-center text-muted small">
                <p class="mb-0">
                    <strong>MyWisata Application</strong> &copy; 2026 | 
                    <a href="README.md">Documentation</a> | 
                    <a href="docs/00_DAFTAR_ISI.md">Table of Contents</a>
                </p>
                <p class="mb-0">
                    Developed with <i class="fas fa-heart text-danger"></i> using Prompting System for Autonomous Development
                </p>
            </div>
        </div>
    </div>
</body>
</html>
    <?php
}
