<?php
/**
 * Branch Managers API
 * Handles branch manager assignment by bos
 */

// Suppress errors to ensure JSON output
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../config/path.php';
    require_once BASE_PATH . '/includes/functions.php';
    require_once BASE_PATH . '/config/database.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Configuration error: ' . $e->getMessage()]);
    exit();
}

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($action) {
        case 'assign':
            if ($method === 'POST') {
                handleAssignBranchManager();
            } else {
                apiError('Method not allowed', 405);
            }
            break;
            
        case 'list':
            if ($method === 'GET') {
                handleBranchManagersList();
            } else {
                apiError('Method not allowed', 405);
            }
            break;
            
        case 'update':
            if ($method === 'POST') {
                handleUpdateBranchManager();
            } else {
                apiError('Method not allowed', 405);
            }
            break;
            
        case 'remove':
            if ($method === 'POST') {
                handleRemoveBranchManager();
            } else {
                apiError('Method not allowed', 405);
            }
            break;
            
        default:
            apiError('Invalid action');
            break;
    }
} catch (Exception $e) {
    apiError($e->getMessage());
}

/**
 * Handle branch manager assignment
 */
function handleAssignBranchManager() {
    // Check if user is logged in
    if (!isLoggedIn()) {
        apiError('Unauthorized', 401);
    }
    
    $user = getCurrentUser();
    if (!$user || $user['role'] !== 'bos') {
        apiError('Hanya bos yang dapat menugaskan manajer cabang', 403);
    }
    
    $cabang_id = $_POST['cabang_id'] ?? '';
    $manager_user_id = $_POST['manager_user_id'] ?? '';
    $manager_type = $_POST['manager_type'] ?? '';
    $can_add_employees = $_POST['can_add_employees'] ?? true;
    $can_manage_branch = $_POST['can_manage_branch'] ?? true;
    
    // Validation
    if (empty($cabang_id) || empty($manager_user_id) || empty($manager_type)) {
        apiError('Cabang ID, manager user ID, dan manager type diperlukan');
    }
    
    // Valid manager types
    $valid_types = ['manager_cabang', 'admin_cabang', 'petugas_cabang'];
    if (!in_array($manager_type, $valid_types)) {
        apiError('Manager type tidak valid');
    }
    
    // Check if branch belongs to this bos
    $cabang = query("SELECT id, owner_bos_id FROM cabang WHERE id = ?", [$cabang_id]);
    if (!$cabang || $cabang[0]['owner_bos_id'] != $user['id']) {
        apiError('Cabang tidak ditemukan atau tidak dimiliki oleh bos ini');
    }
    
    // Check if user belongs to this bos
    $manager_user = query("SELECT id, owner_bos_id, role FROM users WHERE id = ?", [$manager_user_id]);
    if (!$manager_user || $manager_user[0]['owner_bos_id'] != $user['id']) {
        apiError('User tidak ditemukan atau tidak dimiliki oleh bos ini');
    }
    
    // Check if manager is already assigned to this branch
    $existing = query(
        "SELECT id FROM branch_managers WHERE cabang_id = ? AND manager_user_id = ? AND is_active = true",
        [$cabang_id, $manager_user_id]
    );
    
    if ($existing) {
        apiError('User sudah ditugaskan sebagai manajer cabang ini');
    }
    
    // Insert branch manager assignment
    $result = query(
        "INSERT INTO branch_managers (cabang_id, manager_user_id, manager_type, appointed_by_bos_id, can_add_employees, can_manage_branch) VALUES (?, ?, ?, ?, ?, ?)",
        [$cabang_id, $manager_user_id, $manager_type, $user['id'], $can_add_employees, $can_manage_branch]
    );
    
    if ($result) {
        apiSuccess(['message' => 'Manajer cabang berhasil ditugaskan']);
    } else {
        apiError('Gagal menugaskan manajer cabang');
    }
}

/**
 * Handle branch managers list
 */
function handleBranchManagersList() {
    // Check if user is logged in
    if (!isLoggedIn()) {
        apiError('Unauthorized', 401);
    }
    
    $user = getCurrentUser();
    if (!$user) {
        apiError('User not found', 404);
    }
    
    $cabang_id = $_GET['cabang_id'] ?? '';
    
    // If bos, show all branch managers for their branches
    if ($user['role'] === 'bos') {
        $where_clause = "WHERE bm.appointed_by_bos_id = ?";
        $params = [$user['id']];
        
        if ($cabang_id) {
            $where_clause .= " AND bm.cabang_id = ?";
            $params[] = $cabang_id;
        }
    } else {
        // If regular user, show branch managers for their branch
        $where_clause = "WHERE bm.cabang_id = ? AND bm.is_active = true";
        $params = [$cabang_id];
    }
    
    $managers = query(
        "SELECT bm.*, c.nama_cabang, u.nama as manager_name, u.username as manager_username, u.role as manager_role 
         FROM branch_managers bm 
         JOIN cabang c ON bm.cabang_id = c.id 
         JOIN users u ON bm.manager_user_id = u.id 
         $where_clause 
         ORDER BY bm.appointed_at DESC",
        $params
    );
    
    apiSuccess($managers);
}

/**
 * Handle branch manager update
 */
function handleUpdateBranchManager() {
    // Check if user is logged in
    if (!isLoggedIn()) {
        apiError('Unauthorized', 401);
    }
    
    $user = getCurrentUser();
    if (!$user || $user['role'] !== 'bos') {
        apiError('Hanya bos yang dapat mengupdate manajer cabang', 403);
    }
    
    $manager_id = $_POST['manager_id'] ?? '';
    $can_add_employees = $_POST['can_add_employees'] ?? null;
    $can_manage_branch = $_POST['can_manage_branch'] ?? null;
    
    if (empty($manager_id)) {
        apiError('Manager ID diperlukan');
    }
    
    // Check if manager assignment belongs to this bos
    $manager = query(
        "SELECT id FROM branch_managers WHERE id = ? AND appointed_by_bos_id = ?",
        [$manager_id, $user['id']]
    );
    
    if (!$manager) {
        apiError('Manajer cabang tidak ditemukan atau bukan ditugaskan oleh bos ini');
    }
    
    // Build update query
    $updates = [];
    $params = [];
    
    if ($can_add_employees !== null) {
        $updates[] = "can_add_employees = ?";
        $params[] = $can_add_employees;
    }
    
    if ($can_manage_branch !== null) {
        $updates[] = "can_manage_branch = ?";
        $params[] = $can_manage_branch;
    }
    
    if (empty($updates)) {
        apiError('Tidak ada field yang diupdate');
    }
    
    $updates[] = "updated_at = CURRENT_TIMESTAMP";
    $params[] = $manager_id;
    
    $sql = "UPDATE branch_managers SET " . implode(', ', $updates) . " WHERE id = ?";
    
    $result = query($sql, $params);
    
    if ($result) {
        apiSuccess(['message' => 'Manajer cabang berhasil diupdate']);
    } else {
        apiError('Gagal mengupdate manajer cabang');
    }
}

/**
 * Handle branch manager removal
 */
function handleRemoveBranchManager() {
    // Check if user is logged in
    if (!isLoggedIn()) {
        apiError('Unauthorized', 401);
    }
    
    $user = getCurrentUser();
    if (!$user || $user['role'] !== 'bos') {
        apiError('Hanya bos yang dapat menghapus manajer cabang', 403);
    }
    
    $manager_id = $_POST['manager_id'] ?? '';
    
    if (empty($manager_id)) {
        apiError('Manager ID diperlukan');
    }
    
    // Check if manager assignment belongs to this bos
    $manager = query(
        "SELECT id FROM branch_managers WHERE id = ? AND appointed_by_bos_id = ?",
        [$manager_id, $user['id']]
    );
    
    if (!$manager) {
        apiError('Manajer cabang tidak ditemukan atau bukan ditugaskan oleh bos ini');
    }
    
    // Remove manager (set is_active to false)
    $result = query(
        "UPDATE branch_managers SET is_active = false, updated_at = CURRENT_TIMESTAMP WHERE id = ?",
        [$manager_id]
    );
    
    if ($result) {
        apiSuccess(['message' => 'Manajer cabang berhasil dihapus']);
    } else {
        apiError('Gagal menghapus manajer cabang');
    }
}
