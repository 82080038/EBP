<?php

// Serve static files and HTML for root path
$requestUri = $_SERVER['REQUEST_URI'];

// Serve index.html for root path
if ($requestUri === '/' || $requestUri === '/index.html') {
    require_once __DIR__ . '/index.html';
    exit;
}

// Serve API routes for /api paths
if (strpos($requestUri, '/api') === 0) {
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../core/Response.php';
    require_once __DIR__ . '/../core/Router.php';
    require_once __DIR__ . '/../routes/api.php';

    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
} else {
    // Return 404 for non-API, non-root paths
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Route not found', 'errors' => []]);
    exit;
}
