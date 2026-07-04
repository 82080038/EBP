<?php
/**
 * API: OCR KTP
 * 
 * Endpoint for OCR KTP scanning and validation
 */

// Suppress errors to ensure JSON output
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

try {
    require_once __DIR__ . '/../config/path.php';
    require_once BASE_PATH . '/includes/functions.php';
    require_once BASE_PATH . '/includes/ocr_ktp.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Configuration error: ' . $e->getMessage()]);
    exit();
}

// Authentication check
requireLogin();

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

$action = $_GET['action'] ?? 'scan';
$ocr = new OcrKtp();

switch ($action) {
    case 'scan':
        // Scan KTP image and extract data
        if (!isset($_FILES['ktp_image'])) {
            http_response_code(400);
            echo json_encode(['error' => 'File KTP tidak ditemukan']);
            exit();
        }
        
        $file = $_FILES['ktp_image'];
        
        // Validate file
        if ($file['error'] !== 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Gagal upload file']);
            exit();
        }
        
        // Save uploaded file temporarily
        $tempPath = sys_get_temp_dir() . '/ktp_scan_' . time() . '.jpg';
        move_uploaded_file($file['tmp_name'], $tempPath);
        
        // Extract data
        $result = $ocr->extractFromImage($tempPath);
        
        // Clean up temporary file
        unlink($tempPath);
        
        if ($result['success']) {
            echo json_encode([
                'success' => true,
                'data' => $result['parsed_data'],
                'confidence' => $result['parsed_data']['confidence']
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => $result['error']]);
        }
        break;
        
    case 'validate':
        // Validate KTP data (manual input)
        $input = json_decode(file_get_contents('php://input'), true);
        
        $result = $ocr->manualVerification($input);
        
        if ($result['success']) {
            echo json_encode([
                'success' => true,
                'data' => $result['data'],
                'message' => 'Data KTP valid'
            ]);
        } else {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'errors' => $result['errors']
            ]);
        }
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action. Use: scan or validate']);
}
?>
