<?php
/**
 * API: Setting Bunga Dinamis
 * 
 * Endpoints for managing dynamic interest rate settings
 */

// Suppress errors to ensure JSON output
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

try {
    require_once __DIR__ . '/../config/path.php';
    require_once BASE_PATH . '/includes/functions.php';
    require_once BASE_PATH . '/includes/bunga_calculator.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Configuration error: ' . $e->getMessage()]);
    exit();
}

// Authentication check
requireLogin();

$method = $_SERVER['REQUEST_METHOD'];
$kantor_id = 1; // Single office
$calculator = new BungaCalculator($kantor_id);

switch ($method) {
    case 'GET':
        // List all settings
        $settings = $calculator->getAllSettings();
        echo json_encode([
            'success' => true,
            'data' => $settings
        ]);
        break;
        
    case 'POST':
        // Create new setting
        $input = json_decode(file_get_contents('php://input'), true);
        
        $result = $calculator->createSetting([
            'jenis_pinjaman' => $input['jenis_pinjaman'],
            'tenor_min' => $input['tenor_min'],
            'tenor_max' => $input['tenor_max'],
            'bunga_default' => $input['bunga_default'],
            'bunga_min' => $input['bunga_min'],
            'bunga_max' => $input['bunga_max'],
            'faktor_risiko' => $input['faktor_risiko'] ?? 0,
            'jaminan_adjustment' => $input['jaminan_adjustment'] ?? 0
        ]);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Setting bunga berhasil ditambahkan',
                'id' => $result
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Gagal menambahkan setting bunga']);
        }
        break;
        
    case 'PUT':
        // Update setting
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID setting diperlukan']);
            exit();
        }
        
        $result = $calculator->updateSetting($id, [
            'bunga_default' => $input['bunga_default'],
            'bunga_min' => $input['bunga_min'],
            'bunga_max' => $input['bunga_max'],
            'faktor_risiko' => $input['faktor_risiko'] ?? 0,
            'jaminan_adjustment' => $input['jaminan_adjustment'] ?? 0
        ]);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Setting bunga berhasil diupdate'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Gagal mengupdate setting bunga']);
        }
        break;
        
    case 'DELETE':
        // Delete setting (soft delete - set status to nonaktif)
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID setting diperlukan']);
            exit();
        }
        
        $db = db();
        $result = $db->update("UPDATE setting_bunga SET status = 'nonaktif' WHERE id = ?", [$id]);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Setting bunga berhasil dihapus'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Gagal menghapus setting bunga']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}
?>
