<?php
/**
 * API: Family Risk Management
 * 
 * Endpoints for managing family risk and preventing loans to problematic families
 */

// Suppress errors to ensure JSON output
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

try {
    require_once __DIR__ . '/../config/path.php';
    require_once BASE_PATH . '/includes/functions.php';
    require_once BASE_PATH . '/includes/family_risk.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Configuration error: ' . $e->getMessage()]);
    exit();
}

// Authentication check
requireLogin();

$method = $_SERVER['REQUEST_METHOD'];
$cabangId = getCurrentCabang();
$familyRisk = new FamilyRisk($cabangId);

switch ($method) {
    case 'GET':
        $action = $_GET['action'] ?? 'check';
        
        switch ($action) {
            case 'check':
                // Check family risk for nasabah
                $nasabahId = $_GET['nasabah_id'] ?? null;
                if (!$nasabahId) {
                    http_response_code(400);
                    echo json_encode(['error' => 'nasabah_id diperlukan']);
                    exit();
                }
                
                $risk = $familyRisk->checkFamilyRisk($nasabahId);
                echo json_encode([
                    'success' => true,
                    'data' => $risk
                ]);
                break;
                
            case 'validate':
                // Validate loan application
                $nasabahId = $_GET['nasabah_id'] ?? null;
                $plafon = $_GET['plafon'] ?? null;
                
                if (!$nasabahId || !$plafon) {
                    http_response_code(400);
                    echo json_encode(['error' => 'nasabah_id dan plafon diperlukan']);
                    exit();
                }
                
                $validation = $familyRisk->validateLoanApplication($nasabahId, $plafon);
                echo json_encode([
                    'success' => true,
                    'data' => $validation
                ]);
                break;
                
            case 'high_risk':
                // Get all high-risk families
                $families = $familyRisk->getHighRiskFamilies();
                echo json_encode([
                    'success' => true,
                    'data' => $families
                ]);
                break;
                
            case 'family':
                // Get family relationships for nasabah
                $nasabahId = $_GET['nasabah_id'] ?? null;
                if (!$nasabahId) {
                    http_response_code(400);
                    echo json_encode(['error' => 'nasabah_id diperlukan']);
                    exit();
                }
                
                $relationships = $familyRisk->getFamilyRelationships($nasabahId);
                echo json_encode([
                    'success' => true,
                    'data' => $relationships
                ]);
                break;
                
            case 'log':
                // Get risk log for nasabah
                $nasabahId = $_GET['nasabah_id'] ?? null;
                if (!$nasabahId) {
                    http_response_code(400);
                    echo json_encode(['error' => 'nasabah_id diperlukan']);
                    exit();
                }
                
                $log = $familyRisk->getRiskLog($nasabahId);
                echo json_encode([
                    'success' => true,
                    'data' => $log
                ]);
                break;
                
            default:
                http_response_code(400);
                echo json_encode(['error' => 'Invalid action. Use: check, validate, high_risk, family, log']);
        }
        break;
        
    case 'POST':
        $action = $_GET['action'] ?? 'add';
        
        switch ($action) {
            case 'add':
                // Add family relationship
                $input = json_decode(file_get_contents('php://input'), true);
                
                $result = $familyRisk->addFamilyRelationship($input['nasabah_id'], $input);
                
                if ($result) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Hubungan keluarga berhasil ditambahkan',
                        'id' => $result
                    ]);
                } else {
                    http_response_code(500);
                    echo json_encode(['error' => 'Gagal menambahkan hubungan keluarga']);
                }
                break;
                
            case 'blacklist':
                // Blacklist entire family
                $input = json_decode(file_get_contents('php://input'), true);
                
                $result = $familyRisk->blacklistFamily($input['alamat_keluarga'], $input['alasan']);
                
                if ($result) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Keluarga berhasil di-blacklist'
                    ]);
                } else {
                    http_response_code(500);
                    echo json_encode(['error' => 'Gagal blacklist keluarga']);
                }
                break;
                
            case 'family_risk':
                // Create or update family risk record
                $input = json_decode(file_get_contents('php://input'), true);
                
                $result = $familyRisk->createFamilyRisk($input);
                
                if ($result) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Record family risk berhasil ditambahkan/diupdate'
                    ]);
                } else {
                    http_response_code(500);
                    echo json_encode(['error' => 'Gagal menambahkan family risk']);
                }
                break;
                
            default:
                http_response_code(400);
                echo json_encode(['error' => 'Invalid action. Use: add, blacklist, family_risk']);
        }
        break;
        
    case 'PUT':
        // Update nasabah risk score
        $input = json_decode(file_get_contents('php://input'), true);
        $nasabahId = $_GET['nasabah_id'] ?? null;
        
        if (!$nasabahId) {
            http_response_code(400);
            echo json_encode(['error' => 'nasabah_id diperlukan']);
            exit();
        }
        
        $result = $familyRisk->updateNasabahRiskScore($nasabahId, $input['score_increase']);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Skor risiko keluarga berhasil diupdate'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Gagal mengupdate skor risiko']);
        }
        break;
        
    case 'DELETE':
        // Delete family risk record (soft delete)
        $nasabahId = $_GET['nasabah_id'] ?? null;
        
        if (!$nasabahId) {
            http_response_code(400);
            echo json_encode(['error' => 'nasabah_id is required']);
            exit();
        }
        
        // Permission check
        if (!hasPermission('view_laporan')) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden - No permission to manage family risk']);
            exit();
        }
        
        // Soft delete - set status to inactive
        $result = query("UPDATE family_risk SET status = 'inactive', updated_at = CURRENT_TIMESTAMP WHERE nasabah_id = ?", [$nasabahId]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Family risk record deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete family risk record']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}
?>
