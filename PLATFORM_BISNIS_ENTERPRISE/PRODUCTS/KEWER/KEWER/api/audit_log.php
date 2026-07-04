<?php
/**
 * Audit Log API
 * Provides audit log viewing and filtering
 * 
 * @author Kewer Development Team
 * @version 1.0.0
 */

require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Check permission
if (!hasPermission('audit_log_view')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Permission denied']);
    exit;
}

$action = $_GET['action'] ?? 'list';

try {
    switch ($action) {
        case 'list':
            echo json_encode(getAuditLogs());
            break;
        case 'export':
            echo json_encode(exportAuditLogs());
            break;
        case 'stats':
            echo json_encode(getAuditStats());
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

/**
 * Get audit logs with filters
 */
function getAuditLogs() {
    $cabang_id = $_GET['cabang_id'] ?? $_SESSION['cabang_id'] ?? null;
    $user_id = $_GET['user_id'] ?? null;
    $action = $_GET['action_filter'] ?? null;
    $table_name = $_GET['table_name'] ?? null;
    $start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
    $end_date = $_GET['end_date'] ?? date('Y-m-d');
    $limit = $_GET['limit'] ?? 100;
    $offset = $_GET['offset'] ?? 0;
    
    $where = ["1=1"];
    $params = [];
    
    if ($cabang_id) {
        $where[] = "al.cabang_id = ?";
        $params[] = $cabang_id;
    }
    
    if ($user_id) {
        $where[] = "al.user_id = ?";
        $params[] = $user_id;
    }
    
    if ($action) {
        $where[] = "al.action LIKE ?";
        $params[] = "%$action%";
    }
    
    if ($table_name) {
        $where[] = "al.table_name = ?";
        $params[] = $table_name;
    }
    
    if ($start_date) {
        $where[] = "DATE(al.created_at) >= ?";
        $params[] = $start_date;
    }
    
    if ($end_date) {
        $where[] = "DATE(al.created_at) <= ?";
        $params[] = $end_date;
    }
    
    $whereClause = implode(' AND ', $where);
    
    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM audit_log al WHERE $whereClause";
    $countResult = query($countSql, $params);
    $total = is_array($countResult) && isset($countResult[0]) ? $countResult[0]['total'] : 0;
    
    // Get logs
    $sql = "SELECT al.*, u.nama as user_name, u.username, c.nama_cabang
            FROM audit_log al
            LEFT JOIN users u ON al.user_id = u.id
            LEFT JOIN cabang c ON al.cabang_id = c.id
            WHERE $whereClause
            ORDER BY al.created_at DESC
            LIMIT ? OFFSET ?";
    
    $params[] = (int) $limit;
    $params[] = (int) $offset;
    
    $result = query($sql, $params);
    
    return [
        'success' => true,
        'data' => is_array($result) ? $result : [],
        'total' => $total,
        'limit' => (int) $limit,
        'offset' => (int) $offset
    ];
}

/**
 * Export audit logs
 */
function exportAuditLogs() {
    $format = $_GET['format'] ?? 'csv';
    $cabang_id = $_GET['cabang_id'] ?? $_SESSION['cabang_id'] ?? null;
    $start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
    $end_date = $_GET['end_date'] ?? date('Y-m-d');
    
    $where = ["1=1"];
    $params = [];
    
    if ($cabang_id) {
        $where[] = "al.cabang_id = ?";
        $params[] = $cabang_id;
    }
    
    if ($start_date) {
        $where[] = "DATE(al.created_at) >= ?";
        $params[] = $start_date;
    }
    
    if ($end_date) {
        $where[] = "DATE(al.created_at) <= ?";
        $params[] = $end_date;
    }
    
    $whereClause = implode(' AND ', $where);
    
    $sql = "SELECT al.*, u.nama as user_name, u.username, c.nama_cabang
            FROM audit_log al
            LEFT JOIN users u ON al.user_id = u.id
            LEFT JOIN cabang c ON al.cabang_id = c.id
            WHERE $whereClause
            ORDER BY al.created_at DESC
            LIMIT 10000";
    
    $result = query($sql, $params);
    
    if (!is_array($result)) {
        return ['success' => false, 'message' => 'No data to export'];
    }
    
    if ($format == 'csv') {
        $filename = 'audit_log_' . date('Y-m-d_H-i-s') . '.csv';
        $filepath = BASE_PATH . '/exports/' . $filename;
        
        // Create exports directory if not exists
        if (!is_dir(BASE_PATH . '/exports')) {
            mkdir(BASE_PATH . '/exports', 0755, true);
        }
        
        $file = fopen($filepath, 'w');
        
        // Header
        fputcsv($file, ['ID', 'User', 'Username', 'Action', 'Table Name', 'Record ID', 'Old Values', 'New Values', 'IP Address', 'Created At', 'Cabang']);
        
        // Data
        foreach ($result as $row) {
            fputcsv($file, [
                $row['id'],
                $row['user_name'] ?? '',
                $row['username'] ?? '',
                $row['action'],
                $row['table_name'],
                $row['record_id'],
                $row['old_values'] ?? '',
                $row['new_values'] ?? '',
                $row['ip_address'],
                $row['created_at'],
                $row['nama_cabang'] ?? ''
            ]);
        }
        
        fclose($file);
        
        return [
            'success' => true,
            'filename' => $filename,
            'download_url' => '/exports/' . $filename,
            'total_rows' => count($result)
        ];
    }
    
    return ['success' => false, 'message' => 'Unsupported format'];
}

/**
 * Get audit statistics
 */
function getAuditStats() {
    $cabang_id = $_GET['cabang_id'] ?? $_SESSION['cabang_id'] ?? null;
    $start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
    $end_date = $_GET['end_date'] ?? date('Y-m-d');
    
    $where = ["1=1"];
    $params = [];
    
    if ($cabang_id) {
        $where[] = "cabang_id = ?";
        $params[] = $cabang_id;
    }
    
    if ($start_date) {
        $where[] = "DATE(created_at) >= ?";
        $params[] = $start_date;
    }
    
    if ($end_date) {
        $where[] = "DATE(created_at) <= ?";
        $params[] = $end_date;
    }
    
    $whereClause = implode(' AND ', $where);
    
    // Total logs
    $sql = "SELECT COUNT(*) as total FROM audit_log WHERE $whereClause";
    $result = query($sql, $params);
    $total = is_array($result) && isset($result[0]) ? $result[0]['total'] : 0;
    
    // By action
    $sql = "SELECT action, COUNT(*) as count FROM audit_log WHERE $whereClause GROUP BY action ORDER BY count DESC";
    $result = query($sql, $params);
    $byAction = is_array($result) ? $result : [];
    
    // By table
    $sql = "SELECT table_name, COUNT(*) as count FROM audit_log WHERE $whereClause GROUP BY table_name ORDER BY count DESC";
    $result = query($sql, $params);
    $byTable = is_array($result) ? $result : [];
    
    // By user
    $sql = "SELECT u.nama, u.username, COUNT(*) as count FROM audit_log al LEFT JOIN users u ON al.user_id = u.id WHERE $whereClause GROUP BY al.user_id ORDER BY count DESC LIMIT 10";
    $result = query($sql, $params);
    $byUser = is_array($result) ? $result : [];
    
    // Daily trend
    $sql = "SELECT DATE(created_at) as date, COUNT(*) as count FROM audit_log WHERE $whereClause GROUP BY DATE(created_at) ORDER BY date DESC LIMIT 30";
    $result = query($sql, $params);
    $dailyTrend = is_array($result) ? $result : [];
    
    return [
        'success' => true,
        'data' => [
            'total' => $total,
            'by_action' => $byAction,
            'by_table' => $byTable,
            'by_user' => $byUser,
            'daily_trend' => $dailyTrend
        ]
    ];
}
