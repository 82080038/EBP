<?php
/**
 * API Endpoint untuk Manajemen Role
 * 
 * Endpoint ini menyediakan fungsi CRUD untuk manajemen role dan permissions
 */

// Suppress errors to ensure JSON output
error_reporting(0);
ini_set('display_errors', 0);

try {
    require_once __DIR__ . '/../config/path.php';
    require_once BASE_PATH . '/includes/functions.php';
    require_once BASE_PATH . '/config/database.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Configuration error: ' . $e->getMessage()]);
    exit();
}

header('Content-Type: application/json');

// Get HTTP method
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
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

// Check if user has permission to manage roles
try {
    if (!hasPermission('assign_permissions')) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Forbidden - Insufficient permissions']);
        exit();
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Permission check failed: ' . $e->getMessage()]);
    exit();
}

// Route based on method
switch ($method) {
    case 'GET':
        handleGet();
        break;
    case 'POST':
        handlePost();
        break;
    case 'PUT':
        handlePut();
        break;
    case 'DELETE':
        handleDelete();
        break;
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
        break;
}

/**
 * Handle GET requests
 */
function handleGet() {
    global $user;
    
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'list':
            // Get all available roles from ref_roles table
            $roles = query("SELECT role_kode as code, role_nama as name, deskripsi as description, urutan_tampil as hierarchy_level 
                            FROM ref_roles 
                            WHERE status = 'aktif' 
                            ORDER BY urutan_tampil");
            if (!$roles) {
                // Fallback to hardcoded list if ref_roles is empty
                $roles = [
                    ['code' => 'bos', 'name' => 'Bos', 'description' => 'Pemilik usaha', 'hierarchy_level' => 1],
                    ['code' => 'manager_pusat', 'name' => 'Manager Pusat', 'description' => 'Manager di pusat', 'hierarchy_level' => 3],
                    ['code' => 'manager_cabang', 'name' => 'Manager Cabang', 'description' => 'Manager cabang', 'hierarchy_level' => 4],
                    ['code' => 'admin_pusat', 'name' => 'Admin Pusat', 'description' => 'Admin di pusat', 'hierarchy_level' => 5],
                    ['code' => 'admin_cabang', 'name' => 'Admin Cabang', 'description' => 'Admin cabang', 'hierarchy_level' => 6],
                    ['code' => 'petugas_pusat', 'name' => 'Petugas Pusat', 'description' => 'Petugas di pusat', 'hierarchy_level' => 7],
                    ['code' => 'petugas_cabang', 'name' => 'Petugas Cabang', 'description' => 'Petugas cabang', 'hierarchy_level' => 8],
                    ['code' => 'teller', 'name' => 'Teller', 'description' => 'Teller dengan akses view-only data dan update kas reconciliation', 'hierarchy_level' => 9]
                ];
            }
            echo json_encode(['success' => true, 'data' => $roles]);
            break;
            
        case 'permissions':
            // Get all permissions
            $permissions = query("SELECT * FROM permissions ORDER BY kategori, nama");
            if (!$permissions) {
                $permissions = [];
            }
            echo json_encode(['success' => true, 'data' => $permissions]);
            break;
            
        case 'role_permissions':
            // Get permissions for a specific role
            $role = $_GET['role'] ?? '';
            if (!$role) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Role parameter required']);
                exit();
            }
            
            $rolePermissions = query("SELECT rp.*, p.nama, p.kategori 
                                      FROM role_permissions rp
                                      JOIN permissions p ON rp.permission_id = p.id
                                      WHERE rp.role_kode = ? 
                                      ORDER BY p.kategori, p.nama", [$role]);
            if (!$rolePermissions) {
                $rolePermissions = [];
            }
            echo json_encode(['success' => true, 'data' => $rolePermissions]);
            break;
            
        case 'user_permissions':
            // Get permissions for a specific user
            $user_id = $_GET['user_id'] ?? '';
            if (!$user_id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'User ID parameter required']);
                exit();
            }
            
            $userPermissions = getUserPermissions($user_id);
            echo json_encode(['success' => true, 'data' => $userPermissions]);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
            break;
    }
}

/**
 * Handle POST requests
 */
function handlePost() {
    $action = $_GET['action'] ?? '';
    $input = json_decode(file_get_contents('php://input'), true);
    
    switch ($action) {
        case 'assign_role_permission':
            // Assign permission to role
            $role = $input['role'] ?? '';
            $permission_code = $input['permission_code'] ?? '';
            $granted = $input['granted'] ?? true;
            
            if (!$role || !$permission_code) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Role and permission_code required']);
                exit();
            }
            
            // Get permission ID
            $permission = query("SELECT id FROM permissions WHERE kode = ?", [$permission_code]);
            if (!$permission) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Permission not found']);
                exit();
            }
            
            // Upsert role permission
            $result = query("INSERT INTO role_permissions (role_kode, permission_id, granted) 
                           VALUES (?, ?, ?) 
                           ON DUPLICATE KEY UPDATE granted = ?", 
                           [$role, $permission[0]['id'], $granted ? 1 : 0, $granted ? 1 : 0]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Role permission updated']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to update role permission']);
            }
            break;
            
        case 'assign_user_permission':
            // Assign permission to user
            $user_id = $input['user_id'] ?? '';
            $permission_code = $input['permission_code'] ?? '';
            $granted = $input['granted'] ?? true;
            
            if (!$user_id || !$permission_code) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'User ID and permission_code required']);
                exit();
            }
            
            $result = grantPermission($user_id, $permission_code, $granted);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'User permission updated']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to update user permission']);
            }
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
            break;
    }
}

/**
 * Handle PUT requests
 */
function handlePut() {
    $action = $_GET['action'] ?? '';
    $input = json_decode(file_get_contents('php://input'), true);
    
    switch ($action) {
        case 'update_role':
            // Update role information
            $role_code = $input['role_code'] ?? '';
            $role_name = $input['role_name'] ?? '';
            $description = $input['description'] ?? '';
            $hierarchy_level = $input['hierarchy_level'] ?? null;
            
            if (!$role_code) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Role code required']);
                exit();
            }
            
            $params = [];
            $sql = "UPDATE ref_roles SET";
            
            if ($role_name) {
                $sql .= " role_nama = ?,";
                $params[] = $role_name;
            }
            if ($description !== '') {
                $sql .= " deskripsi = ?,";
                $params[] = $description;
            }
            if ($hierarchy_level !== null) {
                $sql .= " urutan_tampil = ?,";
                $params[] = $hierarchy_level;
            }
            
            $sql = rtrim($sql, ',');
            $sql .= " WHERE role_kode = ?";
            $params[] = $role_code;
            
            $result = query($sql, $params);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Role updated successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to update role']);
            }
            break;
            
        case 'update_permission':
            // Update permission information
            $permission_id = $input['permission_id'] ?? '';
            $name = $input['name'] ?? '';
            $description = $input['description'] ?? '';
            $category = $input['category'] ?? '';
            
            if (!$permission_id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Permission ID required']);
                exit();
            }
            
            $params = [];
            $sql = "UPDATE permissions SET";
            
            if ($name) {
                $sql .= " nama = ?,";
                $params[] = $name;
            }
            if ($description !== '') {
                $sql .= " deskripsi = ?,";
                $params[] = $description;
            }
            if ($category) {
                $sql .= " kategori = ?,";
                $params[] = $category;
            }
            
            $sql = rtrim($sql, ',');
            $sql .= " WHERE id = ?";
            $params[] = $permission_id;
            
            $result = query($sql, $params);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Permission updated successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to update permission']);
            }
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid action. Use: update_role, update_permission']);
            break;
    }
}

/**
 * Handle DELETE requests
 */
function handleDelete() {
    $action = $_GET['action'] ?? '';
    $input = json_decode(file_get_contents('php://input'), true);
    
    switch ($action) {
        case 'revoke_role_permission':
            // Revoke permission from role
            $role = $input['role'] ?? '';
            $permission_code = $input['permission_code'] ?? '';
            
            if (!$role || !$permission_code) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Role and permission_code required']);
                exit();
            }
            
            // Get permission ID
            $permission = query("SELECT id FROM permissions WHERE kode = ?", [$permission_code]);
            if (!$permission) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Permission not found']);
                exit();
            }
            
            // Delete role permission
            $result = query("DELETE FROM role_permissions WHERE role_kode = ? AND permission_id = ?", 
                           [$role, $permission[0]['id']]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Role permission revoked successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to revoke role permission']);
            }
            break;
            
        case 'revoke_user_permission':
            // Revoke permission from user
            $user_id = $input['user_id'] ?? '';
            $permission_code = $input['permission_code'] ?? '';
            
            if (!$user_id || !$permission_code) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'User ID and permission_code required']);
                exit();
            }
            
            $result = revokePermission($user_id, $permission_code);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'User permission revoked successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to revoke user permission']);
            }
            break;
            
        case 'delete_role':
            // Delete a role (only if not in use)
            $role_code = $input['role_code'] ?? '';
            
            if (!$role_code) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Role code required']);
                exit();
            }
            
            // Check if role is in use
            $usersWithRole = query("SELECT COUNT(*) as count FROM users WHERE role = ?", [$role_code]);
            if ($usersWithRole && $usersWithRole[0]['count'] > 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Cannot delete role that is in use']);
                exit();
            }
            
            // Delete role permissions first
            query("DELETE FROM role_permissions WHERE role_kode = ?", [$role_code]);
            
            // Delete role
            $result = query("DELETE FROM ref_roles WHERE role_kode = ?", [$role_code]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Role deleted successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to delete role']);
            }
            break;
            
        case 'delete_permission':
            // Delete a permission (only if not critical)
            $permission_id = $input['permission_id'] ?? '';
            
            if (!$permission_id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Permission ID required']);
                exit();
            }
            
            // Check if permission is in use
            $rolePermissions = query("SELECT COUNT(*) as count FROM role_permissions WHERE permission_id = ?", [$permission_id]);
            if ($rolePermissions && $rolePermissions[0]['count'] > 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Cannot delete permission that is assigned to roles']);
                exit();
            }
            
            // Delete permission
            $result = query("DELETE FROM permissions WHERE id = ?", [$permission_id]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Permission deleted successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to delete permission']);
            }
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid action. Use: revoke_role_permission, revoke_user_permission, delete_role, delete_permission']);
            break;
    }
}
