<?php
/**
 * API: Cabang (Branch)
 * 
 * Endpoints for managing branch/cabang data
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
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Configuration error: ' . $e->getMessage()]);
    exit();
}

// Authentication check
requireLogin();

$method = $_SERVER['REQUEST_METHOD'];
$user = getCurrentUser();
if (!$user) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

switch ($method) {
    case 'GET':
        // Get cabang list
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        
        $where = [];
        $params = [];
        
        // Filter by bos_id (untuk dropdown transfer karyawan)
        $filter_bos_id = $_GET['bos_id'] ?? null;
        if ($filter_bos_id) {
            $where[] = "c.owner_bos_id = ?";
            $params[] = (int)$filter_bos_id;
        } elseif ($user['role'] === 'bos') {
            // Isolasi: bos hanya lihat cabang miliknya sendiri
            $where[] = "c.owner_bos_id = ?";
            $params[] = (int)$user['id'];
        } elseif ($user['role'] !== 'appOwner') {
            // Staff: filter cabang berdasarkan owner_bos_id mereka
            $bos_id = getOwnerBosId();
            if ($bos_id) {
                $where[] = "c.owner_bos_id = ?";
                $params[] = $bos_id;
            } else {
                $where[] = "1=0";
            }
        }
        
        if ($search) {
            $where[] = "(c.kode_cabang LIKE ? OR c.nama_cabang LIKE ? OR c.alamat LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        if ($status) {
            $where[] = "c.status = ?";
            $params[] = $status;
        }
        
        $where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        $cabang = query("
            SELECT c.*, u.nama as owner_name, u.username as owner_username
            FROM cabang c
            LEFT JOIN users u ON c.owner_bos_id = u.id
            $where_clause 
            ORDER BY c.is_headquarters DESC, c.nama_cabang ASC
        ", $params);
        
        if (!is_array($cabang)) {
            $cabang = [];
        }
        
        // Add branch type label
        foreach ($cabang as &$c) {
            $c['tipe_cabang'] = $c['is_headquarters'] ? 'Kantor Pusat' : 'Cabang';
        }
        
        echo json_encode([
            'success' => true,
            'data' => $cabang,
            'total' => count($cabang)
        ]);
        break;
        
    case 'POST':
        // Create new cabang
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Permission check
        if (!hasPermission('manage_cabang')) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden - No permission to manage cabang']);
            exit();
        }
        
        // Validation
        if (!$input['kode_cabang'] || !$input['nama_cabang']) {
            http_response_code(400);
            echo json_encode(['error' => 'kode_cabang and nama_cabang are required']);
            exit();
        }
        
        // Check duplicate
        $check = query("SELECT id FROM cabang WHERE kode_cabang = ?", [$input['kode_cabang']]);
        if ($check) {
            http_response_code(409);
            echo json_encode(['error' => 'kode_cabang already exists']);
            exit();
        }
        
        // If bos, check headquarters limit
        if ($user['role'] === 'bos' && ($input['is_headquarters'] ?? false)) {
            $hq_check = query("SELECT id FROM cabang WHERE owner_bos_id = ? AND is_headquarters = 1", [$user['id']]);
            if ($hq_check) {
                http_response_code(400);
                echo json_encode(['error' => 'Bos can only have one headquarters']);
                exit();
            }
        }
        
        // Insert cabang
        $result = query("INSERT INTO cabang (kode_cabang, nama_cabang, alamat, telp, email, province_id, regency_id, district_id, village_id, status, is_headquarters, owner_bos_id, created_by_user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", [
            $input['kode_cabang'],
            $input['nama_cabang'],
            $input['alamat'] ?? '',
            $input['telp'] ?? '',
            $input['email'] ?? '',
            $input['province_id'] ?? null,
            $input['regency_id'] ?? null,
            $input['district_id'] ?? null,
            $input['village_id'] ?? null,
            $input['status'] ?? 'aktif',
            $input['is_headquarters'] ?? 0,
            $user['role'] === 'bos' ? $user['id'] : ($input['owner_bos_id'] ?? null),
            $user['id']
        ]);
        
        if ($result) {
            $new_cabang = query("SELECT * FROM cabang WHERE id = LAST_INSERT_ID()")[0];
            echo json_encode(['success' => true, 'data' => $new_cabang]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create cabang']);
        }
        break;
        
    case 'PUT':
        // Update cabang
        $cabang_id = $_GET['id'] ?? null;
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$cabang_id) {
            http_response_code(400);
            echo json_encode(['error' => 'cabang_id is required']);
            exit();
        }
        
        // Permission check
        if (!hasPermission('manage_cabang')) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden - No permission to manage cabang']);
            exit();
        }
        
        // Get existing cabang
        $existing = query("SELECT * FROM cabang WHERE id = ?", [$cabang_id]);
        if (!$existing) {
            http_response_code(404);
            echo json_encode(['error' => 'Cabang not found']);
            exit();
        }
        $existing = $existing[0];
        
        // Check ownership
        if ($user['role'] === 'bos' && $existing['owner_bos_id'] != $user['id']) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden - Can only edit own cabang']);
            exit();
        }
        
        // Check if changing headquarters
        if (($input['is_headquarters'] ?? $existing['is_headquarters']) && !$existing['is_headquarters']) {
            if ($user['role'] === 'bos') {
                $hq_check = query("SELECT id FROM cabang WHERE owner_bos_id = ? AND is_headquarters = 1 AND id != ?", [$user['id'], $cabang_id]);
                if ($hq_check) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Bos can only have one headquarters']);
                    exit();
                }
            }
        }
        
        // Build update query
        $fields = [];
        $params = [];
        
        if (isset($input['nama_cabang'])) {
            $fields[] = "nama_cabang = ?";
            $params[] = $input['nama_cabang'];
        }
        if (isset($input['alamat'])) {
            $fields[] = "alamat = ?";
            $params[] = $input['alamat'];
        }
        if (isset($input['telp'])) {
            $fields[] = "telp = ?";
            $params[] = $input['telp'];
        }
        if (isset($input['email'])) {
            $fields[] = "email = ?";
            $params[] = $input['email'];
        }
        if (isset($input['province_id'])) {
            $fields[] = "province_id = ?";
            $params[] = $input['province_id'];
        }
        if (isset($input['regency_id'])) {
            $fields[] = "regency_id = ?";
            $params[] = $input['regency_id'];
        }
        if (isset($input['district_id'])) {
            $fields[] = "district_id = ?";
            $params[] = $input['district_id'];
        }
        if (isset($input['village_id'])) {
            $fields[] = "village_id = ?";
            $params[] = $input['village_id'];
        }
        if (isset($input['status'])) {
            $fields[] = "status = ?";
            $params[] = $input['status'];
        }
        if (isset($input['is_headquarters'])) {
            $fields[] = "is_headquarters = ?";
            $params[] = $input['is_headquarters'];
        }
        
        if (empty($fields)) {
            http_response_code(400);
            echo json_encode(['error' => 'No fields to update']);
            exit();
        }
        
        $fields[] = "updated_at = CURRENT_TIMESTAMP";
        $params[] = $cabang_id;
        
        $sql = "UPDATE cabang SET " . implode(', ', $fields) . " WHERE id = ?";
        $result = query($sql, $params);
        
        if ($result) {
            $result_cabang = query("SELECT * FROM cabang WHERE id = ?", [$cabang_id]);
            $updated_cabang = is_array($result_cabang) && isset($result_cabang[0]) ? $result_cabang[0] : null;
            echo json_encode(['success' => true, 'data' => $updated_cabang]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update cabang']);
        }
        break;
        
    case 'DELETE':
        // Delete cabang
        $cabang_id = $_GET['id'] ?? null;
        
        if (!$cabang_id) {
            http_response_code(400);
            echo json_encode(['error' => 'cabang_id is required']);
            exit();
        }
        
        // Permission check
        if (!hasPermission('manage_cabang')) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden - No permission to manage cabang']);
            exit();
        }
        
        // Get existing cabang
        $existing = query("SELECT * FROM cabang WHERE id = ?", [$cabang_id]);
        if (!$existing) {
            http_response_code(404);
            echo json_encode(['error' => 'Cabang not found']);
            exit();
        }
        $existing = $existing[0];
        
        // Check ownership
        if ($user['role'] === 'bos' && $existing['owner_bos_id'] != $user['id']) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden - Can only delete own cabang']);
            exit();
        }
        
        // Check if it's a headquarters
        if ($existing['is_headquarters']) {
            http_response_code(400);
            echo json_encode(['error' => 'Cannot delete headquarters']);
            exit();
        }
        
        // Check if cabang has data
        $nasabah_count = query("SELECT COUNT(*) as count FROM nasabah WHERE cabang_id = ?", [$cabang_id])[0]['count'];
        $pinjaman_count = query("SELECT COUNT(*) as count FROM pinjaman WHERE cabang_id = ?", [$cabang_id])[0]['count'];
        
        if ($nasabah_count > 0 || $pinjaman_count > 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Cannot delete cabang with existing data (nasabah or pinjaman)']);
            exit();
        }
        
        // Soft delete
        $result = query("UPDATE cabang SET status = 'nonaktif', updated_at = CURRENT_TIMESTAMP WHERE id = ?", [$cabang_id]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Cabang deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete cabang']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
