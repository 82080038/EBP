<?php
/**
 * Router script for PHP built-in server
 * Enables clean URLs without ?url= parameter
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Serve static files directly
if (preg_match('/\.(?:css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$/', $uri)) {
    $file = __DIR__ . '/public' . $uri;
    if (file_exists($file)) {
        return false; // Let PHP server handle the static file
    }
}

// Set URL parameter for routing
$_GET['url'] = ltrim($uri, '/');

// Include main index.php
require_once __DIR__ . '/index.php';
