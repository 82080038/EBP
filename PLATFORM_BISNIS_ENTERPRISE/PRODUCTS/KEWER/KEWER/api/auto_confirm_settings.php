<?php
// Suppress errors to ensure JSON output
error_reporting(0);
ini_set('display_errors', 0);

try {
    require_once __DIR__ . '/../config/path.php';
    require_once BASE_PATH . '/includes/functions.php';
    require_once BASE_PATH . '/includes/auto_confirm.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Configuration error: ' . $e->getMessage()]);
    exit();
}

header('Content-Type: application/json');

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Get current user
try {
    $user = getCurrentUser();
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['error' => 'Failed to get user: ' . $e->getMessage()]);
    exit();
}

if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Permission check
if (!hasPermission('pinjaman.auto_confirm')) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden - No permission']);
    exit();
}

switch ($method) {
    case 'GET':
        // Single office - use global settings (null cabang_id)
        $settings = getAutoConfirmSettings(null);
        echo json_encode(['success' => true, 'data' => $settings]);
        break;
        
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid input']);
            exit();
        }
        
        $enabled = $input['enabled'] ?? false;
        $plafon_threshold = $input['plafon_threshold'] ?? 0;
        $tenor_limit = $input['tenor_limit'] ?? 0;
        $max_risk_score = $input['max_risk_score'] ?? 10;
        $require_nasabah_history = $input['require_nasabah_history'] ?? true;
        $min_nasabah_history_months = $input['min_nasabah_history_months'] ?? 3;
        
        // Single office - always use global settings (null cabang_id)
        $cabang_id = null;
        
        // Check if setting exists
        $existing = query("SELECT id FROM auto_confirm_settings WHERE cabang_id IS NULL");
        
        if ($existing && count($existing) > 0) {
            // Update existing
            $result = query("UPDATE auto_confirm_settings 
                           SET enabled = ?, plafon_threshold = ?, tenor_limit = ?, 
                               max_risk_score = ?, require_nasabah_history = ?, 
                               min_nasabah_history_months = ?, updated_by = ? 
                           WHERE cabang_id IS NULL",
                           [$enabled, $plafon_threshold, $tenor_limit, 
                            $max_risk_score, $require_nasabah_history, 
                            $min_nasabah_history_months, $user['id']]);
            
            if ($result !== false) {
                echo json_encode(['success' => true, 'message' => 'Auto-confirm settings updated']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to update settings']);
            }
        } else {
            // Create new
            $result = query("INSERT INTO auto_confirm_settings 
                           (cabang_id, enabled, plafon_threshold, tenor_limit, 
                            max_risk_score, require_nasabah_history, min_nasabah_history_months, 
                            created_by) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                           [null, $enabled, $plafon_threshold, $tenor_limit, 
                            $max_risk_score, $require_nasabah_history, 
                            $min_nasabah_history_months, $user['id']]);
            
            if ($result !== false) {
                echo json_encode(['success' => true, 'message' => 'Auto-confirm settings created']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to create settings']);
            }
        }
        break;
        
    case 'DELETE':
        // Single office - only disable global setting
        $result = query("UPDATE auto_confirm_settings SET enabled = FALSE WHERE cabang_id IS NULL");
        
        if ($result !== false) {
            echo json_encode(['success' => true, 'message' => 'Auto-confirm settings disabled']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete settings']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?>
