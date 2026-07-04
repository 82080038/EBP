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

// Permission check - petugas can create their own activities, manager can view all
if ($method === 'POST' && !hasPermission('angsuran.create')) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden - No permission']);
    exit();
}

if ($method === 'GET' && !hasPermission('angsuran.read')) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden - No permission']);
    exit();
}

$kantor_id = 1; // Single office

switch ($method) {
    case 'GET':
        $petugas_id = $_GET['petugas_id'] ?? null;
        $activity_type = $_GET['activity_type'] ?? null;
        $tanggal_mulai = $_GET['tanggal_mulai'] ?? null;
        $tanggal_selesai = $_GET['tanggal_selesai'] ?? null;
        $status = $_GET['status'] ?? null;
        
        $where = [];
        $params = [];
        
        if ($petugas_id) {
            $where[] = "foa.petugas_id = ?";
            $params[] = $petugas_id;
        }
        
        if ($activity_type) {
            $where[] = "foa.activity_type = ?";
            $params[] = $activity_type;
        }
        
        if ($status) {
            $where[] = "foa.status = ?";
            $params[] = $status;
        }
        
        if ($tanggal_mulai) {
            $where[] = "foa.activity_date >= ?";
            $params[] = $tanggal_mulai;
        }
        
        if ($tanggal_selesai) {
            $where[] = "foa.activity_date <= ?";
            $params[] = $tanggal_selesai;
        }
        
        $where_clause = !empty($where) ? "WHERE " . implode(' AND ', $where) : "";
        
        $sql = "SELECT foa.*, u.nama as petugas_nama, n.nama as nasabah_nama, 
                       p.kode_pinjaman, a.no_angsuran
                FROM field_officer_activities foa
                LEFT JOIN users u ON foa.petugas_id = u.id
                LEFT JOIN nasabah n ON foa.nasabah_id = n.id
                LEFT JOIN pinjaman p ON foa.pinjaman_id = p.id
                LEFT JOIN angsuran a ON foa.angsuran_id = a.id
                $where_clause
                ORDER BY foa.activity_date DESC, foa.activity_time DESC";
        
        $activities = query($sql, $params);
        
        if (!is_array($activities)) {
            $activities = [];
        }
        
        echo json_encode(['success' => true, 'data' => $activities]);
        break;
        
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid input']);
            exit();
        }
        
        $petugas_id = $user['id']; // Only petugas can log their own activities
        $activity_type = $input['activity_type'] ?? null;
        $nasabah_id = $input['nasabah_id'] ?? null;
        $pinjaman_id = $input['pinjaman_id'] ?? null;
        $angsuran_id = $input['angsuran_id'] ?? null;
        $description = $input['description'] ?? null;
        $location = $input['location'] ?? null;
        $latitude = $input['latitude'] ?? null;
        $longitude = $input['longitude'] ?? null;
        $activity_date = $input['activity_date'] ?? date('Y-m-d');
        $activity_time = $input['activity_time'] ?? date('H:i:s');
        $status = $input['status'] ?? 'completed';
        
        // Validation
        if (!$activity_type) {
            http_response_code(400);
            echo json_encode(['error' => 'activity_type is required']);
            exit();
        }
        
        // Validate activity type
        $valid_types = ['survey_nasabah', 'input_pinjaman', 'kutip_angsuran', 'follow_up', 'promosi', 'edukasi', 'lainnya'];
        if (!in_array($activity_type, $valid_types)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid activity_type']);
            exit();
        }
        
        $result = query("INSERT INTO field_officer_activities 
                        (petugas_id, activity_type, nasabah_id, pinjaman_id, angsuran_id, 
                         description, location, latitude, longitude, activity_date, activity_time, status) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                        [$petugas_id, $activity_type, $nasabah_id, $pinjaman_id, 
                         $angsuran_id, $description, $location, $latitude, $longitude, 
                         $activity_date, $activity_time, $status]);
        
        if ($result !== false) {
            echo json_encode(['success' => true, 'message' => 'Activity logged successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to log activity']);
        }
        break;
        
    case 'PUT':
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Activity ID is required']);
            exit();
        }
        
        // Check if activity exists and belongs to current user (for petugas) or same cabang (for manager)
        $existing = query("SELECT * FROM field_officer_activities WHERE id = ?", [$id]);
        if (!$existing || !is_array($existing) || count($existing) === 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Activity not found']);
            exit();
        }
        
        $activity = $existing[0];
        
        // Petugas can only update their own activities
        if (in_array($user['role'], ['petugas_pusat', 'petugas_cabang']) && $activity['petugas_id'] != $user['id']) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden - Can only update own activities']);
            exit();
        }
        
        // Manager can update activities in their office
        if (in_array($user['role'], ['bos', 'manager_pusat', 'manager_cabang', 'admin_pusat', 'admin_cabang'])) {
            // Allow access for management roles
        }
        
        $status = $input['status'] ?? null;
        $description = $input['description'] ?? null;
        
        $updateFields = [];
        $params = [];
        
        if ($status) {
            $updateFields[] = "status = ?";
            $params[] = $status;
        }
        
        if ($description !== null) {
            $updateFields[] = "description = ?";
            $params[] = $description;
        }
        
        if (empty($updateFields)) {
            http_response_code(400);
            echo json_encode(['error' => 'No fields to update']);
            exit();
        }
        
        $params[] = $id;
        
        $sql = "UPDATE field_officer_activities SET " . implode(', ', $updateFields) . " WHERE id = ?";
        $result = query($sql, $params);
        
        if ($result !== false) {
            echo json_encode(['success' => true, 'message' => 'Activity updated successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update activity']);
        }
        break;
        
    case 'DELETE':
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Activity ID is required']);
            exit();
        }
        
        // Check if activity exists and belongs to current user
        $existing = query("SELECT * FROM field_officer_activities WHERE id = ?", [$id]);
        if (!$existing || !is_array($existing) || count($existing) === 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Activity not found']);
            exit();
        }
        
        $activity = $existing[0];
        
        // Only owner, bos, or the petugas who created it can delete
        if (in_array($user['role'], ['petugas_pusat', 'petugas_cabang']) && $activity['petugas_id'] != $user['id']) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden - Can only delete own activities']);
            exit();
        }
        
        $result = query("DELETE FROM field_officer_activities WHERE id = ?", [$id]);
        
        if ($result !== false) {
            echo json_encode(['success' => true, 'message' => 'Activity deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete activity']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?>
