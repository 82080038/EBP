<?php
/**
 * API: In-App Notifications
 * GET  /api/notifications.php?action=list&limit=20
 * GET  /api/notifications.php?action=count&status=sent
 * POST /api/notifications.php?action=mark_read&id=123
 */
header('Content-Type: application/json');

require_once __DIR__ . '/../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
require_once BASE_PATH . '/includes/feature_flags.php';

requireLogin();
$user = getCurrentUser();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method === 'GET') {
    switch ($action) {
        case 'list':
            $limit = min((int)($_GET['limit'] ?? 20), 100);
            $offset = (int)($_GET['offset'] ?? 0);
            $status = $_GET['status'] ?? 'sent';
            
            // Filter notifications for current user based on role
            $where = "WHERE nq.status = ?";
            $params = [$status];
            
            // If petugas, show notifications for their assigned nasabah
            if (in_array($user['role'], ['petugas_pusat', 'petugas_cabang'])) {
                $where .= " AND (nq.petugas_id = ? OR nq.nasabah_id IN (SELECT id FROM nasabah WHERE cabang_id = ?))";
                $params[] = $user['id'];
                $params[] = $user['cabang_id'];
            }
            
            $notifications = query("
                SELECT nq.*, 
                       CASE 
                           WHEN nq.nasabah_id IS NOT NULL THEN (SELECT nama FROM nasabah WHERE id = nq.nasabah_id)
                           WHEN nq.petugas_id IS NOT NULL THEN (SELECT username FROM users WHERE id = nq.petugas_id)
                           ELSE 'System'
                       END as sender_name
                FROM notification_queue nq
                $where
                ORDER BY nq.created_at DESC
                LIMIT ? OFFSET ?
            ", array_merge($params, [$limit, $offset]));
            
            echo json_encode([
                'success' => true,
                'data' => $notifications ?: [],
                'total' => count($notifications ?: [])
            ]);
            break;

        case 'count':
            $status = $_GET['status'] ?? 'sent';
            
            // Count unread notifications
            $where = "WHERE status = ?";
            $params = [$status];
            
            if (in_array($user['role'], ['petugas_pusat', 'petugas_cabang'])) {
                $where .= " AND (petugas_id = ? OR nasabah_id IN (SELECT id FROM nasabah WHERE cabang_id = ?))";
                $params[] = $user['id'];
                $params[] = $user['cabang_id'];
            }
            
            $count = query("SELECT COUNT(*) as total FROM notification_queue $where", $params)[0]['total'] ?? 0;
            
            echo json_encode([
                'success' => true,
                'count' => $count
            ]);
            break;

        case 'stats':
            // Get notification statistics
            $stats = query("
                SELECT 
                    status,
                    COUNT(*) as total
                FROM notification_queue
                GROUP BY status
            ");
            
            $stats_map = [];
            foreach ($stats as $s) {
                $stats_map[$s['status']] = $s['total'];
            }
            
            echo json_encode([
                'success' => true,
                'stats' => [
                    'pending' => $stats_map['pending'] ?? 0,
                    'processing' => $stats_map['processing'] ?? 0,
                    'sent' => $stats_map['sent'] ?? 0,
                    'failed' => $stats_map['failed'] ?? 0,
                    'cancelled' => $stats_map['cancelled'] ?? 0
                ]
            ]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action. Use: list, count, stats']);
    }
    exit();
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    $action = $input['action'] ?? '';

    switch ($action) {
        case 'mark_read':
            $id = (int)($input['id'] ?? 0);
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID required']);
                exit();
            }
            
            // Add is_read column if not exists
            try {
                query("ALTER TABLE notification_queue ADD COLUMN IF NOT EXISTS is_read TINYINT(1) NOT NULL DEFAULT 0");
            } catch (Exception $e) {
                // Column might already exist
            }
            
            $result = query("UPDATE notification_queue SET is_read = 1 WHERE id = ?", [$id]);
            
            echo json_encode([
                'success' => (bool)$result
            ]);
            break;

        case 'mark_all_read':
            $status = $input['status'] ?? 'sent';
            
            // Add is_read column if not exists
            try {
                query("ALTER TABLE notification_queue ADD COLUMN IF NOT EXISTS is_read TINYINT(1) NOT NULL DEFAULT 0");
            } catch (Exception $e) {
                // Column might already exist
            }
            
            $where = "WHERE status = ?";
            $params = [$status];
            
            if (in_array($user['role'], ['petugas_pusat', 'petugas_cabang'])) {
                $where .= " AND (petugas_id = ? OR nasabah_id IN (SELECT id FROM nasabah WHERE cabang_id = ?))";
                $params[] = $user['id'];
                $params[] = $user['cabang_id'];
            }
            
            $result = query("UPDATE notification_queue SET is_read = 1 $where", $params);
            
            echo json_encode([
                'success' => (bool)$result,
                'affected' => $result ? $result->affected_rows : 0
            ]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action. Use: mark_read, mark_all_read']);
    }
    exit();
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
