<?php
/**
 * Delegated Permissions API
 * Handles delegation of permissions from bos to employees
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
        case 'delegate':
            if ($method === 'POST') {
                handleDelegatePermission();
            } else {
                apiError('Method not allowed', 405);
            }
            break;
            
        case 'list':
            if ($method === 'GET') {
                handleDelegatedPermissionsList();
            } else {
                apiError('Method not allowed', 405);
            }
            break;
            
        case 'revoke':
            if ($method === 'POST') {
                handleRevokePermission();
            } else {
                apiError('Method not allowed', 405);
            }
            break;
            
        case 'my_permissions':
            if ($method === 'GET') {
                handleMyDelegatedPermissions();
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
 * Handle permission delegation
 */
function handleDelegatePermission() {
    // Check if user is logged in
    if (!isLoggedIn()) {
        apiError('Unauthorized', 401);
    }
    
    $user = getCurrentUser();
    if (!$user || $user['role'] !== 'bos') {
        apiError('Hanya bos yang dapat mendelegasikan permission', 403);
    }
    
    $delegatee_id = $_POST['delegatee_id'] ?? '';
    $permission_scope = $_POST['permission_scope'] ?? '';
    $scope_limitation = $_POST['scope_limitation'] ?? null;
    $expires_at = $_POST['expires_at'] ?? null;
    $notes = $_POST['notes'] ?? '';
    
    // Validation
    if (empty($delegatee_id) || empty($permission_scope)) {
        apiError('Delegatee ID dan permission scope diperlukan');
    }
    
    // Valid permission scopes
    $valid_scopes = ['employee_crud', 'branch_crud', 'branch_employee_crud', 'all_operations'];
    if (!in_array($permission_scope, $valid_scopes)) {
        apiError('Permission scope tidak valid');
    }
    
    // Check if delegatee is owned by this bos
    $delegatee = query("SELECT id, owner_bos_id FROM users WHERE id = ?", [$delegatee_id]);
    if (!$delegatee || $delegatee[0]['owner_bos_id'] != $user['id']) {
        apiError('Delegatee tidak ditemukan atau tidak dimiliki oleh bos ini');
    }
    
    // Check if delegation already exists
    $existing = query(
        "SELECT id FROM delegated_permissions WHERE delegator_id = ? AND delegatee_id = ? AND permission_scope = ? AND is_active = true",
        [$user['id'], $delegatee_id, $permission_scope]
    );
    
    if ($existing) {
        apiError('Permission sudah didelegasikan');
    }
    
    // Insert delegation
    $scope_limitation_json = $scope_limitation ? json_encode($scope_limitation) : null;
    
    $result = query(
        "INSERT INTO delegated_permissions (delegator_id, delegatee_id, permission_scope, scope_limitation, expires_at, notes) VALUES (?, ?, ?, ?, ?, ?)",
        [$user['id'], $delegatee_id, $permission_scope, $scope_limitation_json, $expires_at ?: null, $notes]
    );
    
    if ($result) {
        apiSuccess(['message' => 'Permission berhasil didelegasikan']);
    } else {
        apiError('Gagal mendelegasikan permission');
    }
}

/**
 * Handle delegated permissions list
 */
function handleDelegatedPermissionsList() {
    // Check if user is logged in
    if (!isLoggedIn()) {
        apiError('Unauthorized', 401);
    }
    
    $user = getCurrentUser();
    if (!$user) {
        apiError('User not found', 404);
    }
    
    // If bos, show permissions delegated by this bos
    if ($user['role'] === 'bos') {
        $delegations = query(
            "SELECT dp.*, u.nama as delegatee_name, u.username as delegatee_username 
             FROM delegated_permissions dp 
             JOIN users u ON dp.delegatee_id = u.id 
             WHERE dp.delegator_id = ? 
             ORDER BY dp.granted_at DESC",
            [$user['id']]
        );
    } else {
        // If regular user, show permissions delegated to this user
        $delegations = query(
            "SELECT dp.*, u.nama as delegator_name, u.username as delegator_username 
             FROM delegated_permissions dp 
             JOIN users u ON dp.delegator_id = u.id 
             WHERE dp.delegatee_id = ? AND dp.is_active = true 
             ORDER BY dp.granted_at DESC",
            [$user['id']]
        );
    }
    
    apiSuccess($delegations);
}

/**
 * Handle permission revocation
 */
function handleRevokePermission() {
    // Check if user is logged in
    if (!isLoggedIn()) {
        apiError('Unauthorized', 401);
    }
    
    $user = getCurrentUser();
    if (!$user || $user['role'] !== 'bos') {
        apiError('Hanya bos yang dapat mencabut permission', 403);
    }
    
    $delegation_id = $_POST['delegation_id'] ?? '';
    
    if (empty($delegation_id)) {
        apiError('Delegation ID diperlukan');
    }
    
    // Check if delegation belongs to this bos
    $delegation = query(
        "SELECT id FROM delegated_permissions WHERE id = ? AND delegator_id = ?",
        [$delegation_id, $user['id']]
    );
    
    if (!$delegation) {
        apiError('Delegation tidak ditemukan atau bukan milik bos ini');
    }
    
    // Revoke delegation
    $result = query(
        "UPDATE delegated_permissions SET is_active = false, updated_at = CURRENT_TIMESTAMP WHERE id = ?",
        [$delegation_id]
    );
    
    if ($result) {
        apiSuccess(['message' => 'Permission berhasil dicabut']);
    } else {
        apiError('Gagal mencabut permission');
    }
}

/**
 * Handle my delegated permissions
 */
function handleMyDelegatedPermissions() {
    // Check if user is logged in
    if (!isLoggedIn()) {
        apiError('Unauthorized', 401);
    }
    
    $user = getCurrentUser();
    if (!$user) {
        apiError('User not found', 404);
    }
    
    // Get active delegated permissions for this user
    $delegations = query(
        "SELECT dp.*, u.nama as delegator_name 
         FROM delegated_permissions dp 
         JOIN users u ON dp.delegator_id = u.id 
         WHERE dp.delegatee_id = ? AND dp.is_active = true 
         AND (dp.expires_at IS NULL OR dp.expires_at > CURRENT_TIMESTAMP)
         ORDER BY dp.granted_at DESC",
        [$user['id']]
    );
    
    apiSuccess($delegations);
}
