<?php
/**
 * API: Nasabah Blacklist Management
 */

// Suppress errors to ensure JSON output
// Commented out for development to see errors
// error_reporting(0);
// ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
$kantor_id = 1; // Single office
$user = getCurrentUser();

switch ($method) {
    case 'GET':
        $action = $_GET['action'] ?? 'list';
        
        if ($action === 'blacklist') {
            // Get blacklisted nasabah
            $nasabah = query("
                SELECT n.*, c.nama_cabang as cabang_nama,
                       bl.blacklist_reason, bl.blacklisted_at, bl.blacklisted_by,
                       u.nama as blacklisted_by_name
                FROM nasabah n
                LEFT JOIN cabang c ON 1=1
                LEFT JOIN (
                    SELECT nasabah_id, 
                           JSON_EXTRACT(audit_log, '$.reason') as blacklist_reason,
                           created_at as blacklisted_at,
                           created_by as blacklisted_by
                    FROM audit_log 
                    WHERE table_name = 'nasabah' AND action = 'blacklist'
                    ORDER BY created_at DESC
                ) bl ON n.id = bl.nasabah_id
                LEFT JOIN users u ON bl.blacklisted_by = u.id
                WHERE n.status = 'blacklist'
                GROUP BY n.id
                ORDER BY n.updated_at DESC
            ");
            
            echo json_encode(['success' => true, 'data' => $nasabah]);
        } else {
            // Check if specific nasabah is blacklisted
            $nasabah_id = $_GET['nasabah_id'] ?? null;
            if ($nasabah_id) {
                $status = query("SELECT status FROM nasabah WHERE id = ?", [$nasabah_id]);
                $is_blacklisted = ($status[0]['status'] ?? '') === 'blacklist';
                echo json_encode(['success' => true, 'is_blacklisted' => $is_blacklisted]);
            } else {
                echo json_encode(['success' => false, 'error' => 'nasabah_id required']);
            }
        }
        break;
        
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        $nasabah_id = $input['nasabah_id'] ?? null;
        $reason = $input['reason'] ?? '';
        
        if (!$nasabah_id) {
            http_response_code(400);
            echo json_encode(['error' => 'nasabah_id required']);
            exit();
        }
        
        // Check permission
        if (!hasPermission('manage_nasabah') && !hasPermission('blacklist_nasabah')) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden - No permission to blacklist nasabah']);
            exit();
        }
        
        // Check if nasabah has active loans
        $active_loans = query("
            SELECT COUNT(*) as count FROM pinjaman 
            WHERE nasabah_id = ? AND status IN ('aktif', 'disetujui', 'pengajuan')
        ", [$nasabah_id]);
        
        if ($active_loans[0]['count'] > 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Nasabah masih memiliki pinjaman aktif. Tidak dapat di-blacklist.']);
            exit();
        }
        
        // Update status to blacklist
        $result = query("
            UPDATE nasabah 
            SET status = 'blacklist', blacklist_reason = ?, updated_at = NOW() 
            WHERE id = ?
        ", [$reason, $nasabah_id]);
        
        if ($result) {
            // Get nasabah info for WA notification
            $nasabah_info = query("SELECT nama, telepon FROM nasabah WHERE id = ?", [$nasabah_id]);

            // Log to audit
            query("
                INSERT INTO audit_log (table_name, record_id, action, old_value, new_value, reason, created_by, created_at)
                VALUES ('nasabah', ?, 'blacklist', 'aktif', 'blacklist', ?, ?, NOW())
            ", [$nasabah_id, $reason, $user['id']]);

            // ── WA Notifikasi Integration ─────────────────────
            if (isFeatureEnabled('wa_notifikasi') && !empty($nasabah_info[0]['telepon'])) {
                require_once BASE_PATH . '/includes/wa_notifikasi.php';
                $pesan = "Yth. *{$nasabah_info[0]['nama']}*,\n\nMohon maaf, status akun Anda telah ditangguhkan (blacklist) karena: {$reason}\n\nSilakan hubungi kantor kami untuk informasi lebih lanjut.\n\n*Kewer Koperasi*";
                kirimWA($nasabah_info[0]['telepon'], $pesan, $nasabah_id, $user['id'], 'blacklist');
            }
            // ─────────────────────────────────────────────────────

            echo json_encode([
                'success' => true,
                'message' => 'Nasabah berhasil di-blacklist',
                'nasabah_id' => $nasabah_id
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to blacklist nasabah']);
        }
        break;
        
    case 'PUT':
        $input = json_decode(file_get_contents('php://input'), true);
        $nasabah_id = $input['nasabah_id'] ?? null;
        $new_status = $input['status'] ?? 'aktif'; // unblacklist
        
        if (!$nasabah_id) {
            http_response_code(400);
            echo json_encode(['error' => 'nasabah_id required']);
            exit();
        }
        
        // Only manager/owner can unblock
        if (!in_array($user['role'], ['bos', 'manager_pusat', 'manager_cabang', 'admin_pusat', 'admin_cabang'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Only manager or above can unblocklist nasabah']);
            exit();
        }
        
        $result = query("
            UPDATE nasabah 
            SET status = ?, blacklist_reason = NULL, updated_at = NOW() 
            WHERE id = ?
        ", [$new_status, $nasabah_id]);
        
        if ($result) {
            // Log to audit
            query("
                INSERT INTO audit_log (table_name, record_id, action, old_value, new_value, reason, created_by, created_at)
                VALUES ('nasabah', ?, 'unblacklist', 'blacklist', ?, 'Unblocked by manager', ?, NOW())
            ", [$nasabah_id, $new_status, $user['id']]);
            
            echo json_encode([
                'success' => true, 
                'message' => 'Nasabah berhasil di-unblocklist',
                'nasabah_id' => $nasabah_id
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to unblocklist nasabah']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
