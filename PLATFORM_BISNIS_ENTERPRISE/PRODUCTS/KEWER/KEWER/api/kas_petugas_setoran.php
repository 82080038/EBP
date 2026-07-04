<?php
// Suppress errors to ensure JSON output
error_reporting(0);
ini_set('display_errors', 0);

try {
    require_once __DIR__ . '/../config/path.php';
    require_once BASE_PATH . '/includes/functions.php';
    require_once BASE_PATH . '/config/database.php';
    require_once BASE_PATH . '/config/session.php';
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

$kantor_id = 1; // Single office

// Permission check
if ($method === 'POST' && !hasPermission('kas_petugas.update')) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden - No permission']);
    exit();
}

if ($method === 'GET' && !hasPermission('kas_petugas.read')) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden - No permission']);
    exit();
}

if ($method === 'PUT' && !hasPermission('kas.update')) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden - No permission']);
    exit();
}

switch ($method) {
    case 'GET':
        $petugas_id = $_GET['petugas_id'] ?? null;
        $tanggal_mulai = $_GET['tanggal_mulai'] ?? null;
        $tanggal_selesai = $_GET['tanggal_selesai'] ?? null;
        $status = $_GET['status'] ?? null;
        
        $where = [];
        $params = [];
        
        if ($petugas_id) {
            $where[] = "kps.petugas_id = ?";
            $params[] = $petugas_id;
        }
        
        if ($status) {
            $where[] = "kps.status = ?";
            $params[] = $status;
        }
        
        if ($tanggal_mulai) {
            $where[] = "kps.tanggal >= ?";
            $params[] = $tanggal_mulai;
        }
        
        if ($tanggal_selesai) {
            $where[] = "kps.tanggal <= ?";
            $params[] = $tanggal_selesai;
        }
        
        // Petugas can only see their own setoran
        if (in_array($user['role'], ['petugas_pusat', 'petugas_cabang'])) {
            $where[] = "kps.petugas_id = ?";
            $params[] = $user['id'];
        }
        
        $where_clause = !empty($where) ? "WHERE " . implode(' AND ', $where) : "";
        
        $sql = "SELECT kps.*, u.nama as petugas_nama, c.nama_cabang, 
                       ua.nama as approved_by_nama
                FROM kas_petugas_setoran kps
                LEFT JOIN users u ON kps.petugas_id = u.id
                LEFT JOIN cabang c ON 1=1
                LEFT JOIN users ua ON kps.approved_by = ua.id
                $where_clause
                ORDER BY kps.tanggal DESC, kps.created_at DESC";
        
        $setoran = query($sql, $params);
        
        if (!is_array($setoran)) {
            $setoran = [];
        }
        
        echo json_encode(['success' => true, 'data' => $setoran]);
        break;
        
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid input']);
            exit();
        }
        
        $petugas_id = $user['id']; // Only petugas can create their own setoran
        $tanggal = $input['tanggal'] ?? date('Y-m-d');
        $total_kas_petugas = $input['total_kas_petugas'] ?? 0;
        $total_setoran = $input['total_setoran'] ?? 0;
        $keterangan = $input['keterangan'] ?? null;
        $status = $input['status'] ?? 'pending';
        
        // Validation
        if (!$total_kas_petugas || !$total_setoran) {
            http_response_code(400);
            echo json_encode(['error' => 'total_kas_petugas and total_setoran are required']);
            exit();
        }
        
        $selisih = $total_setoran - $total_kas_petugas;
        
        // Check if setoran already exists for this date and petugas
        $existing = query("SELECT id FROM kas_petugas_setoran WHERE petugas_id = ? AND tanggal = ?", 
                         [$petugas_id, $tanggal]);
        
        if ($existing && is_array($existing) && count($existing) > 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Setoran already exists for this date']);
            exit();
        }
        
        $result = query("INSERT INTO kas_petugas_setoran 
                        (petugas_id, tanggal, total_kas_petugas, total_setoran, selisih, keterangan, status) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)",
                        [$petugas_id, $tanggal, $total_kas_petugas, $total_setoran, 
                         $selisih, $keterangan, $status]);
        
        if ($result !== false) {
            echo json_encode(['success' => true, 'message' => 'Setoran berhasil dicatat']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to record setoran']);
        }
        break;
        
    case 'PUT':
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Setoran ID is required']);
            exit();
        }
        
        // Check if setoran exists
        $existing = query("SELECT * FROM kas_petugas_setoran WHERE id = ?", [$id]);
        if (!$existing || !is_array($existing) || count($existing) === 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Setoran not found']);
            exit();
        }
        
        $setoran = $existing[0];
        
        // Only manager can approve setoran
        if ($input['status'] === 'approved' && !in_array($user['role'], ['bos', 'manager_pusat', 'manager_cabang', 'admin_pusat', 'admin_cabang'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden - Only manager can approve setoran']);
            exit();
        }
        
        $status = $input['status'] ?? null;
        
        if ($status === 'approved') {
            $result = query("UPDATE kas_petugas_setoran 
                           SET status = 'approved', approved_by = ?, approved_at = NOW() 
                           WHERE id = ?",
                           [$user['id'], $id]);
        } elseif ($status === 'rejected') {
            $result = query("UPDATE kas_petugas_setoran 
                           SET status = 'rejected', approved_by = ?, approved_at = NOW() 
                           WHERE id = ?",
                           [$user['id'], $id]);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid status']);
            exit();
        }
        
        if ($result !== false) {
            echo json_encode(['success' => true, 'message' => 'Setoran status updated']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update setoran']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?>
