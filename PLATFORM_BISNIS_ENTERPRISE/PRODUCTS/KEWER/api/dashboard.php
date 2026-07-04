<?php
// Suppress errors to ensure JSON output
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    require_once __DIR__ . '/../config/path.php';
    require_once BASE_PATH . '/includes/functions.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Configuration error: ' . $e->getMessage()]);
    exit();
}

// Auth check
try {
    requireLogin();
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['error' => 'Authentication failed: ' . $e->getMessage()]);
    exit();
}

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

// Get current kantor from query parameter or use single office (id = 1)
$kantor_id = $_GET['kantor_id'] ?? 1;

// Get kantor info
$kantor = query("SELECT * FROM cabang WHERE id = ?", [$kantor_id]);
if (!$kantor) {
    http_response_code(404);
    echo json_encode(['error' => 'Kantor tidak ditemukan']);
    exit();
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        $action = $_GET['action'] ?? 'stats';
        
        if ($action === 'stats' || $action === 'all') {
            // Get dashboard statistics
            $nasabah_result = query("SELECT COUNT(*) as total FROM nasabah WHERE status = 'aktif'");
            $total_nasabah = is_array($nasabah_result) && isset($nasabah_result[0]) ? $nasabah_result[0]['total'] : 0;

            $pinjaman_result = query("SELECT COUNT(*) as total FROM pinjaman WHERE status = 'aktif'");
            $total_pinjaman = is_array($pinjaman_result) && isset($pinjaman_result[0]) ? $pinjaman_result[0]['total'] : 0;

            $outstanding_result = query("SELECT SUM(plafon) as total FROM pinjaman WHERE status = 'aktif'");
            $outstanding = is_array($outstanding_result) && isset($outstanding_result[0]) ? $outstanding_result[0]['total'] : 0;

            $late_payments = count(checkLatePayments());
            
            // Get pending approvals
            $pending_approvals_result = query("SELECT COUNT(*) as total FROM pinjaman WHERE status = 'pengajuan'");
            $pending_approvals = is_array($pending_approvals_result) && isset($pending_approvals_result[0]) ? $pending_approvals_result[0]['total'] : 0;
            
            // Get total cabang
            $cabang_result = query("SELECT COUNT(*) as total FROM cabang WHERE status = 'aktif'");
            $total_cabang = is_array($cabang_result) && isset($cabang_result[0]) ? $cabang_result[0]['total'] : 0;
            
            // Get today's payments
            $today_payments_result = query("SELECT COUNT(*) as total FROM pembayaran WHERE DATE(tanggal_bayar) = CURDATE()");
            $today_payments = is_array($today_payments_result) && isset($today_payments_result[0]) ? $today_payments_result[0]['total'] : 0;
            
            $stats_data = [
                'total_nasabah' => (int)$total_nasabah,
                'total_pinjaman' => (int)$total_pinjaman,
                'outstanding' => (float)$outstanding,
                'late_payments' => (int)$late_payments,
                'pending_approvals' => (int)$pending_approvals,
                'total_cabang' => (int)$total_cabang,
                'today_payments' => (int)$today_payments
            ];
            
            if ($action === 'stats') {
                echo json_encode(['success' => true, 'data' => $stats_data]);
                break;
            }
        }
        
        if ($action === 'recent' || $action === 'all') {
            // Get recent activities
            $recent_activities = [];
            try {
                // Get recent pinjaman
                $recent_pinjaman = query("SELECT CONCAT('Pinjaman ', kode_pinjaman, ' untuk ', n.nama) as activity, p.created_at FROM pinjaman p LEFT JOIN nasabah n ON p.nasabah_id = n.id ORDER BY p.created_at DESC LIMIT 3");
                
                // Get recent pembayaran
                $recent_pembayaran = query("SELECT CONCAT('Pembayaran ', jumlah_bayar, ' dari ', n.nama) as activity, pemb.created_at FROM pembayaran pemb LEFT JOIN angsuran a ON pemb.angsuran_id = a.id LEFT JOIN pinjaman p ON a.pinjaman_id = p.id LEFT JOIN nasabah n ON p.nasabah_id = n.id ORDER BY pemb.created_at DESC LIMIT 3");
                
                // Merge and sort by date
                $recent_activities = array_merge($recent_pinjaman ?: [], $recent_pembayaran ?: []);
                usort($recent_activities, function($a, $b) {
                    return strtotime($b['created_at']) - strtotime($a['created_at']);
                });
                $recent_activities = array_slice($recent_activities, 0, 5);
            } catch (Exception $e) {
                $recent_activities = [];
            }
            
            $recent_data = array_map(function($item) {
                return [
                    'activity' => $item['activity'],
                    'action' => $item['activity'],
                    'created_at' => $item['created_at']
                ];
            }, $recent_activities);
            
            if ($action === 'recent') {
                echo json_encode(['success' => true, 'data' => $recent_data]);
                break;
            }
        }
        
        if ($action === 'charts' || $action === 'all') {
            // Get monthly loan data
            $monthly_loans = query("
                SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count 
                FROM pinjaman 
                WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                GROUP BY month 
                ORDER BY month DESC
                LIMIT 12
            ");
            $months = array_reverse(array_column($monthly_loans ?: [], 'month'));
            $monthly_loans_data = array_reverse(array_column($monthly_loans ?: [], 'count'));
            
            // Get status distribution
            $status_data = query("
                SELECT status, COUNT(*) as count 
                FROM pinjaman 
                GROUP BY status
            ");
            $status_labels = array_column($status_data ?: [], 'status');
            $status_counts = array_column($status_data ?: [], 'count');
            
            $charts_data = [
                'months' => $months,
                'monthly_loans' => $monthly_loans_data,
                'status_labels' => $status_labels,
                'status_data' => $status_counts
            ];
            
            if ($action === 'charts') {
                echo json_encode(['success' => true, 'data' => $charts_data]);
                break;
            }
        }
        
        if ($action === 'all') {
            echo json_encode([
                'success' => true,
                'data' => array_merge($stats_data, [
                    'recent_activities' => $recent_data,
                    'months' => $months,
                    'monthly_loans' => $monthly_loans_data,
                    'status_labels' => $status_labels,
                    'status_data' => $status_counts
                ])
            ]);
            break;
        }
        
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?>
