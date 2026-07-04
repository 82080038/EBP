<?php
/**
 * API: Accounting (Akuntansi)
 * 
 * Endpoints for managing accounting/journal entries
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
    require_once BASE_PATH . '/includes/accounting_helper.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Configuration error: ' . $e->getMessage()]);
    exit();
}

// Authentication check
requireLogin();

$method = $_SERVER['REQUEST_METHOD'];
// No longer using cabangId - single office structure

switch ($method) {
    case 'GET':
        $action = $_GET['action'] ?? 'neraca_saldo';
        
        switch ($action) {
            case 'neraca_saldo':
                // Get trial balance
                $neraca_saldo = query("SELECT * FROM neraca_saldo ORDER BY kode");
                echo json_encode([
                    'success' => true,
                    'data' => $neraca_saldo
                ]);
                break;
                
            case 'neraca':
                // Get balance sheet
                $neraca = query("SELECT * FROM neraca ORDER BY kategori, kode");
                echo json_encode([
                    'success' => true,
                    'data' => $neraca
                ]);
                break;
                
            case 'labarugi':
                // Get income statement
                $labarugi = query("SELECT * FROM labarugi ORDER BY kategori, kode");
                
                // Calculate totals
                $pendapatan_total = 0;
                $beban_total = 0;
                
                foreach ($labarugi as $row) {
                    if ($row['kategori'] === 'Pendapatan') {
                        $pendapatan_total += $row['saldo_akhir'];
                    } elseif ($row['kategori'] === 'Beban') {
                        $beban_total += $row['saldo_akhir'];
                    }
                }
                
                $laba_bersih = $pendapatan_total - $beban_total;
                
                echo json_encode([
                    'success' => true,
                    'data' => $labarugi,
                    'summary' => [
                        'total_pendapatan' => $pendapatan_total,
                        'total_beban' => $beban_total,
                        'laba_bersih' => $laba_bersih
                    ]
                ]);
                break;
                
            case 'jurnal':
                // Get journal entries
                $tanggal_mulai = $_GET['tanggal_mulai'] ?? null;
                $tanggal_selesai = $_GET['tanggal_selesai'] ?? null;
                
                $where = [];
                $params = [];
                
                if ($tanggal_mulai) {
                    $where[] = "tanggal_jurnal >= ?";
                    $params[] = $tanggal_mulai;
                }
                
                if ($tanggal_selesai) {
                    $where[] = "tanggal_jurnal <= ?";
                    $params[] = $tanggal_selesai;
                }
                
                $where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
                
                $jurnal = query("SELECT * FROM jurnal $where_clause ORDER BY tanggal_jurnal DESC, nomor_jurnal DESC", $params);
                if (!is_array($jurnal)) $jurnal = [];
                
                // Get details for each journal
                foreach ($jurnal as &$j) {
                    $details = query("SELECT * FROM jurnal_detail WHERE jurnal_id = ?", [$j['id']]);
                    $j['details'] = $details;
                }
                
                echo json_encode([
                    'success' => true,
                    'data' => $jurnal,
                    'total' => count($jurnal)
                ]);
                break;
                
            case 'akun':
                // Get chart of accounts
                $tipe = $_GET['tipe'] ?? null;
                
                if ($tipe) {
                    $akun = getAllAkun($tipe);
                } else {
                    $akun = getAllAkun();
                }
                
                echo json_encode([
                    'success' => true,
                    'data' => $akun
                ]);
                break;
                
            case 'transaksi_log':
                // Get transaction log
                $tanggal_mulai = $_GET['tanggal_mulai'] ?? null;
                $tanggal_selesai = $_GET['tanggal_selesai'] ?? null;
                $tipe_transaksi = $_GET['tipe_transaksi'] ?? null;
                
                $where = [];
                $params = [];
                
                if ($tanggal_mulai) {
                    $where[] = "tanggal_transaksi >= ?";
                    $params[] = $tanggal_mulai;
                }
                
                if ($tanggal_selesai) {
                    $where[] = "tanggal_transaksi <= ?";
                    $params[] = $tanggal_selesai;
                }
                
                if ($tipe_transaksi) {
                    $where[] = "tipe_transaksi = ?";
                    $params[] = $tipe_transaksi;
                }
                
                $where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
                
                $transaksi = query("SELECT * FROM transaksi_log $where_clause ORDER BY tanggal_transaksi DESC, nomor_transaksi DESC", $params);
                
                echo json_encode([
                    'success' => true,
                    'data' => $transaksi,
                    'total' => count($transaksi)
                ]);
                break;
                
            default:
                http_response_code(400);
                echo json_encode(['error' => 'Invalid action. Use: neraca_saldo, neraca, labarugi, jurnal, akun, transaksi_log']);
        }
        break;
        
    case 'POST':
        // Create manual journal entry
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Permission check
        if (!hasPermission('view_laporan')) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden - No permission to manage accounting']);
            exit();
        }
        
        $result = createJurnal([
            'tanggal_jurnal' => $input['tanggal_jurnal'] ?? date('Y-m-d'),
            'tanggal_transaksi' => $input['tanggal_transaksi'] ?? date('Y-m-d'),
            'keterangan' => $input['keterangan'] ?? '',
            'details' => $input['details'] ?? []
        ]);
        
        if ($result['success']) {
            echo json_encode([
                'success' => true,
                'message' => 'Jurnal berhasil dibuat',
                'jurnal_id' => $result['jurnal_id'],
                'nomor_jurnal' => $result['nomor_jurnal']
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => $result['error'] ?? 'Gagal membuat jurnal']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}
