<?php
// Suppress errors to ensure JSON output
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    require_once __DIR__ . '/../config/path.php';
    require_once BASE_PATH . '/includes/functions.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Configuration error: ' . $e->getMessage()]);
    exit();
}

// API Authentication (JWT-based with legacy token support)
function authenticateAPI() {
    $headers = getallheaders();
    $token = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    
    // Try JWT authentication first
    require_once BASE_PATH . '/includes/jwt_helper.php';
    $user = JWTHelper::getCurrentUser();
    
    if ($user) {
        // Valid JWT token - set user in global scope
        $GLOBALS['api_user'] = $user;
        return;
    }
    
    // Fall back to legacy token for transition period
    if ($token !== 'Bearer kewer-api-token-2024') {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized - Invalid or missing token']);
        exit();
    }
    
    // Legacy token authenticated - set as admin user for backward compatibility
    $GLOBALS['api_user'] = [
        'id' => 1,
        'username' => 'api_user',
        'role' => 'bos',
        'cabang_id' => null
    ];
}

// Get request method and endpoint
$method = $_SERVER['REQUEST_METHOD'];
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);
$path_parts = explode('/', trim($path, '/'));

// Remove 'api' from path parts
if ($path_parts[0] === 'api') {
    array_shift($path_parts);
}

$endpoint = $path_parts[0] ?? '';
$id = $path_parts[1] ?? '';

// Authenticate all API requests
authenticateAPI();

try {
    switch ($endpoint) {
        case 'nasabah':
            require_once 'nasabah.php';
            break;
            
        case 'pinjaman':
            require_once 'pinjaman.php';
            break;
            
        case 'angsuran':
            require_once 'angsuran.php';
            break;
            
        case 'dashboard':
            require_once 'dashboard.php';
            break;
            
        case 'auth':
            require_once 'auth.php';
            break;
            
        case 'setting':
        case 'setting_bunga':
            require_once 'setting_bunga.php';
            break;
            
        case 'kas_petugas':
            require_once 'kas_petugas.php';
            break;
            
        case 'pengeluaran':
            require_once 'pengeluaran.php';
            break;
            
        case 'ocr':
            require_once 'ocr.php';
            break;
            
        case 'family_risk':
            require_once 'family_risk.php';
            break;
            
        case 'kas_bon':
            require_once 'kas_bon.php';
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint not found']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
