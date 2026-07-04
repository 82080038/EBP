<?php
/**
 * API: Kas Petugas (Cash Tracking)
 * 
 * Endpoints for tracking cash held by field officers
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
    require_once BASE_PATH . '/includes/kas_petugas.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Configuration error: ' . $e->getMessage()]);
    exit();
}

// Authentication check
requireLogin();

$method = $_SERVER['REQUEST_METHOD'];
$kantor_id = 1; // Single office
$kasPetugas = new KasPetugas($kantor_id);

switch ($method) {
    case 'GET':
        // Get all records or specific date
        $tanggal = $_GET['tanggal'] ?? null;
        $records = $kasPetugas->getBranchRecords($tanggal);
        
        echo json_encode([
            'success' => true,
            'data' => $records
        ]);
        break;
        
    case 'POST':
        // Create new daily record
        $input = json_decode(file_get_contents('php://input'), true);
        
        $result = $kasPetugas->createDailyRecord([
            'petugas_id' => $input['petugas_id'],
            'tanggal' => $input['tanggal'],
            'saldo_awal' => $input['saldo_awal'] ?? 0,
            'total_terima' => $input['total_terima'] ?? 0,
            'total_disetor' => $input['total_disetor'] ?? 0,
            'catatan' => $input['catatan'] ?? null
        ]);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Record kas petugas berhasil dibuat',
                'id' => $result
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Gagal membuat record kas petugas']);
        }
        break;
        
    case 'PUT':
        // Update record
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID record diperlukan']);
            exit();
        }
        
        $result = $kasPetugas->updateRecord($id, [
            'total_terima' => $input['total_terima'],
            'total_disetor' => $input['total_disetor'],
            'catatan' => $input['catatan'] ?? null
        ]);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Record kas petugas berhasil diupdate'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Gagal mengupdate record kas petugas']);
        }
        break;
        
    case 'DELETE':
        // Delete kas petugas record (soft delete)
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID record diperlukan']);
            exit();
        }
        
        // Permission check
        if (!hasPermission('kas_petugas.delete')) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden - No permission to delete kas petugas']);
            exit();
        }
        
        // Get existing record
        $existing = query("SELECT * FROM kas_petugas WHERE id = ?", [$id]);
        if (!$existing) {
            http_response_code(404);
            echo json_encode(['error' => 'Kas Petugas record not found']);
            exit();
        }
        $existing = $existing[0];
        
        // Check if record has been reconciled
        if ($existing['status'] == 'reconciled') {
            http_response_code(400);
            echo json_encode(['error' => 'Cannot delete reconciled record']);
            exit();
        }
        
        // Soft delete - set status to deleted
        $result = query("UPDATE kas_petugas SET status = 'deleted', updated_at = CURRENT_TIMESTAMP WHERE id = ?", [$id]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Kas Petugas record deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete kas petugas record']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}
?>
