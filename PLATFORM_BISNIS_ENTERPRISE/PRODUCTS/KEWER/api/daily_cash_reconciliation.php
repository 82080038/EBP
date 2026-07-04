<?php
// Suppress errors to ensure JSON output
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../config/path.php';
    require_once BASE_PATH . '/includes/functions.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Configuration error: ' . $e->getMessage()]);
    exit();
}

// Authentication check
requireLogin();

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Get current user
$user = getCurrentUser();
if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$kantor_id = 1; // Single office
$role = $user['role'];

// Permission check
if ($method === 'POST' && !hasPermission('kas.update')) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden - No permission']);
    exit();
}

if ($method === 'GET' && !hasPermission('kas.read')) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden - No permission']);
    exit();
}

switch ($method) {
    case 'GET':
        $tanggal = $_GET['tanggal'] ?? null;
        $status = $_GET['status'] ?? null;
        
        $where = [];
        $params = [];
        
        if ($tanggal) {
            $where[] = "tanggal = ?";
            $params[] = $tanggal;
        }
        
        if ($status) {
            $where[] = "status = ?";
            $params[] = $status;
        }
        
        $where_clause = !empty($where) ? "WHERE " . implode(' AND ', $where) : "";
        
        $sql = "SELECT dcr.*, c.nama_cabang, p.nama as prepared_by_nama, a.nama as approved_by_nama
                FROM daily_cash_reconciliation dcr
                LEFT JOIN cabang c ON 1=1
                LEFT JOIN users p ON dcr.prepared_by = p.id
                LEFT JOIN users a ON dcr.approved_by = a.id
                $where_clause
                ORDER BY dcr.tanggal DESC";
        
        $reconciliation = query($sql, $params);
        
        if (!is_array($reconciliation)) {
            $reconciliation = [];
        }
        
        echo json_encode(['success' => true, 'data' => $reconciliation]);
        break;
        
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid input']);
            exit();
        }
        
        $tanggal = $input['tanggal'] ?? date('Y-m-d');
        $kas_awal = $input['kas_awal'] ?? 0;
        $total_penerimaan = $input['total_penerimaan'] ?? 0;
        $total_pengeluaran = $input['total_pengeluaran'] ?? 0;
        $kas_akhir = $input['kas_akhir'] ?? 0;
        $kas_fisik = $input['kas_fisik'] ?? 0;
        $keterangan = $input['keterangan'] ?? null;
        $status = $input['status'] ?? 'draft';
        
        // Validation
        if (!$tanggal) {
            http_response_code(400);
            echo json_encode(['error' => 'tanggal is required']);
            exit();
        }
        
        // Calculate values if not provided
        if (!$kas_akhir) {
            $kas_akhir = $kas_awal + $total_penerimaan - $total_pengeluaran;
        }
        
        $selisih = $kas_fisik - $kas_akhir;
        
        // Check if reconciliation already exists for this date
        $existing = query("SELECT id FROM daily_cash_reconciliation WHERE tanggal = ?", 
                         [$tanggal]);
        
        if ($existing && is_array($existing) && count($existing) > 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Reconciliation already exists for this date']);
            exit();
        }
        
        $result = query("INSERT INTO daily_cash_reconciliation 
                        (tanggal, kas_awal, total_penerimaan, total_pengeluaran, 
                         kas_akhir, kas_fisik, selisih, keterangan, status, prepared_by) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                        [$tanggal, $kas_awal, $total_penerimaan, $total_pengeluaran, 
                         $kas_akhir, $kas_fisik, $selisih, $keterangan, $status, $user['id']]);
        
        if ($result !== false) {
            echo json_encode(['success' => true, 'message' => 'Reconciliation created successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create reconciliation']);
        }
        break;
        
    case 'PUT':
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Reconciliation ID is required']);
            exit();
        }
        
        // Check if reconciliation exists
        $existing = query("SELECT * FROM daily_cash_reconciliation WHERE id = ?", [$id]);
        if (!$existing || !is_array($existing) || count($existing) === 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Reconciliation not found']);
            exit();
        }
        
        $reconciliation = $existing[0];
        
        // Only manager can approve reconciliation
        if ($input['status'] === 'approved' && !in_array($role, ['bos', 'manager_pusat', 'manager_cabang', 'admin_pusat', 'admin_cabang'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden - Only manager can approve reconciliation']);
            exit();
        }
        
        $status = $input['status'] ?? null;
        $kas_fisik = $input['kas_fisik'] ?? null;
        $keterangan = $input['keterangan'] ?? null;
        
        $updateFields = [];
        $params = [];
        
        if ($status) {
            $updateFields[] = "status = ?";
            $params[] = $status;
            
            if ($status === 'submitted') {
                $updateFields[] = "submitted_at = NOW()";
            }
            
            if ($status === 'approved') {
                $updateFields[] = "approved_at = NOW()";
                $updateFields[] = "approved_by = ?";
                $params[] = $user['id'];
            }
        }
        
        if ($kas_fisik !== null) {
            $updateFields[] = "kas_fisik = ?";
            $params[] = $kas_fisik;
            
            // Recalculate selisih
            $selisih = $kas_fisik - $reconciliation['kas_akhir'];
            $updateFields[] = "selisih = ?";
            $params[] = $selisih;
        }
        
        if ($keterangan !== null) {
            $updateFields[] = "keterangan = ?";
            $params[] = $keterangan;
        }
        
        if (empty($updateFields)) {
            http_response_code(400);
            echo json_encode(['error' => 'No fields to update']);
            exit();
        }
        
        $params[] = $id;
        
        $sql = "UPDATE daily_cash_reconciliation SET " . implode(', ', $updateFields) . " WHERE id = ?";
        $result = query($sql, $params);
        
        if ($result !== false) {
            echo json_encode(['success' => true, 'message' => 'Reconciliation updated successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update reconciliation']);
        }
        break;
        
    case 'DELETE':
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Reconciliation ID is required']);
            exit();
        }
        
        // Check if reconciliation exists and is not approved
        $existing = query("SELECT * FROM daily_cash_reconciliation WHERE id = ?", [$id]);
        if (!$existing || !is_array($existing) || count($existing) === 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Reconciliation not found']);
            exit();
        }
        
        $reconciliation = $existing[0];
        
        // Cannot delete approved reconciliation
        if ($reconciliation['status'] === 'approved') {
            http_response_code(400);
            echo json_encode(['error' => 'Cannot delete approved reconciliation']);
            exit();
        }
        
        // Only bos or manager can delete
        if (!in_array($role, ['bos', 'manager_pusat', 'manager_cabang'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            exit();
        }
        
        $result = query("DELETE FROM daily_cash_reconciliation WHERE id = ?", [$id]);
        
        if ($result !== false) {
            echo json_encode(['success' => true, 'message' => 'Reconciliation deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete reconciliation']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?>
