<?php
/**
 * API: Provinsi Activation Management
 * 
 * Endpoints untuk mengelola provinsi yang aktif/non-aktif
 * untuk wilayah kerja bos koperasi
 * 
 * Access: appOwner only
 */

error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

try {
    require_once __DIR__ . '/../config/path.php';
    require_once BASE_PATH . '/includes/functions.php';
    require_once BASE_PATH . '/config/database.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Configuration error']);
    exit();
}

// Check if user is logged in and is appOwner
if (!isLoggedIn() || $_SESSION['role'] !== 'appOwner') {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied. appOwner only.']);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
$user_id = $_SESSION['user_id'] ?? 0;

switch ($method) {
    case 'GET':
        getProvinsiActivation();
        break;
        
    case 'POST':
    case 'PUT':
        updateProvinsiActivation($user_id);
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}

/**
 * Get all provinces with activation status
 */
function getProvinsiActivation() {
    $search = $_GET['search'] ?? '';
    $status = $_GET['status'] ?? 'all';
    
    $sql = "
        SELECT 
            pa.id,
            pa.province_id,
            pa.province_name,
            pa.is_active,
            pa.activated_at,
            pa.deactivated_at,
            pa.notes,
            u.username as activated_by_name
        FROM provinsi_activation pa
        LEFT JOIN users u ON pa.activated_by = u.id
        WHERE 1=1
    ";
    $params = [];
    
    if ($search) {
        $sql .= " AND pa.province_name LIKE ?";
        $params[] = "%$search%";
    }
    
    if ($status === 'active') {
        $sql .= " AND pa.is_active = 1";
    } elseif ($status === 'inactive') {
        $sql .= " AND pa.is_active = 0";
    }
    
    $sql .= " ORDER BY pa.province_name ASC";
    
    $result = query($sql, $params);
    
    // Get summary stats
    $stats = query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
            SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive
        FROM provinsi_activation
    ")[0];
    
    echo json_encode([
        'success' => true,
        'data' => $result,
        'stats' => $stats
    ]);
}

/**
 * Update province activation status
 */
function updateProvinsiActivation($user_id) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid input']);
        return;
    }
    
    $province_id = $input['province_id'] ?? null;
    $is_active = $input['is_active'] ?? null;
    $notes = $input['notes'] ?? null;
    
    if (!$province_id || $is_active === null) {
        http_response_code(400);
        echo json_encode(['error' => 'province_id and is_active are required']);
        return;
    }
    
    // Convert boolean to integer
    $is_active = $is_active ? 1 : 0;
    
    // Check if province exists in activation table
    $existing = query("SELECT id FROM provinsi_activation WHERE province_id = ?", [$province_id]);
    
    if ($existing) {
        // Update existing
        $sql = "
            UPDATE provinsi_activation 
            SET is_active = ?,
                activated_by = ?,
                activated_at = CASE WHEN ? = 1 THEN CURRENT_TIMESTAMP ELSE activated_at END,
                deactivated_at = CASE WHEN ? = 0 THEN CURRENT_TIMESTAMP ELSE NULL END,
                notes = COALESCE(?, notes)
            WHERE province_id = ?
        ";
        $result = query($sql, [$is_active, $user_id, $is_active, $is_active, $notes, $province_id]);
    } else {
        // Get province name from db_alamat
        $province = query_alamat("SELECT name FROM provinces WHERE id = ?", [$province_id]);
        $province_name = $province[0]['name'] ?? 'Unknown';
        
        // Insert new
        $sql = "
            INSERT INTO provinsi_activation 
            (province_id, province_name, is_active, activated_by, activated_at, notes)
            VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP, ?)
        ";
        $result = query($sql, [$province_id, $province_name, $is_active, $user_id, $notes]);
    }
    
    $status_text = $is_active ? 'activated' : 'deactivated';
    
    echo json_encode([
        'success' => true,
        'message' => "Province $status_text successfully",
        'data' => [
            'province_id' => $province_id,
            'is_active' => (bool)$is_active
        ]
    ]);
}
