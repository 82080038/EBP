<?php

// Initialize error handler
require_once __DIR__ . '/../bootstrap.php';

// Get request URI
$requestUri = $_SERVER['REQUEST_URI'];
$requestUri = parse_url($requestUri, PHP_URL_PATH);

// Detect and strip base path for Apache Alias setups
// The app routes always start with /, /frontend/, or /api
// Find where the actual route starts in the URI
$routePatterns = ['/frontend/', '/api/', '/index.html', '/index.php'];
$basePath = '';
foreach ($routePatterns as $pattern) {
    $pos = strpos($requestUri, $pattern);
    if ($pos !== false && $pos > 0) {
        $basePath = substr($requestUri, 0, $pos);
        break;
    }
}
// Check if URI ends with a known file extension (static files)
if (!$basePath && preg_match('/\.(css|js|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf)$/', $requestUri)) {
    // Find /frontend/ or /css/ or /js/ in the path
    if (preg_match('/^(.*?)(\/frontend\/.*)$/', $requestUri, $m)) {
        $basePath = $m[1];
    }
}
// If URI is just a base path with optional trailing slash, it's the root
if (!$basePath) {
    // Check if this looks like a root request (not matching any known route)
    if ($requestUri !== '/' && strpos($requestUri, '/frontend/') === false && strpos($requestUri, '/api/') === false) {
        // Could be a base path like /EBP/restaurant or /EBP/restaurant/
        $basePath = rtrim($requestUri, '/');
    }
}

// Special handling for API routes - ensure base path is stripped correctly
if (strpos($requestUri, '/api') !== false && !$basePath) {
    // If we have /api in the URI but no base path was detected,
    // it might be that the pattern matching failed. Try again.
    $pos = strpos($requestUri, '/api');
    if ($pos > 0) {
        $basePath = substr($requestUri, 0, $pos);
    }
}

// Strip base path from request URI
if ($basePath && strpos($requestUri, $basePath) === 0) {
    $requestUri = substr($requestUri, strlen($basePath));
}
// Ensure leading slash
if ($requestUri === '' || $requestUri[0] !== '/') {
    $requestUri = '/' . $requestUri;
}

// Override REQUEST_URI so Router and other code see the stripped path
$_SERVER['REQUEST_URI'] = $requestUri;

// Serve index.html for root path
if ($requestUri === '/' || $requestUri === '/index.html') {
    require_once __DIR__ . '/index.html';
    exit;
}

// Serve frontend static files (CSS, JS, images)
if (strpos($requestUri, '/frontend/') === 0) {
    $filePath = __DIR__ . ltrim($requestUri, '/');
    if (file_exists($filePath) && !is_dir($filePath)) {
        $mimeType = mime_content_type($filePath);
        header("Content-Type: $mimeType");
        readfile($filePath);
        exit;
    }
}

// Serve frontend mobile app
if (strpos($requestUri, '/frontend/mobile') === 0) {
    $filePath = __DIR__ . '/../../FRONTEND/mobile' . substr($requestUri, strlen('/frontend/mobile'));
    if (file_exists($filePath) && !is_dir($filePath)) {
        $mimeType = mime_content_type($filePath);
        header("Content-Type: $mimeType");
        readfile($filePath);
        exit;
    } else {
        // Default to index.html for SPA routing
        require_once __DIR__ . '/../../FRONTEND/mobile/index.html';
        exit;
    }
}

// Serve frontend kiosk app
if (strpos($requestUri, '/frontend/kiosk') === 0) {
    $filePath = __DIR__ . '/../../FRONTEND/kiosk' . substr($requestUri, strlen('/frontend/kiosk'));
    if (file_exists($filePath) && !is_dir($filePath)) {
        $mimeType = mime_content_type($filePath);
        header("Content-Type: $mimeType");
        readfile($filePath);
        exit;
    } else {
        // Default to index.html for SPA routing
        require_once __DIR__ . '/../../FRONTEND/kiosk/index.html';
        exit;
    }
}

// Serve API routes for /api paths
if (strpos($requestUri, '/api') === 0) {
    require_once __DIR__ . '/../bootstrap.php';
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
