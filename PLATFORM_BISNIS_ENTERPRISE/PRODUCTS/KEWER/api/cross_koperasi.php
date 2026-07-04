<?php
/**
 * API: Cross Koperasi Check
 * 
 * Endpoint untuk cek nasabah di koperasi lain
 * Memberikan warning jika nasabah masih punya hutang di koperasi lain
 * 
 * Access: bos, admin, petugas (dari semua koperasi)
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

try {
    require_once __DIR__ . '/../config/path.php';
    require_once BASE_PATH . '/includes/functions.php';
    require_once BASE_PATH . '/config/database.php';
    require_once BASE_PATH . '/includes/koperasi_isolation.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Configuration error']);
    exit();
}

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied. Please login.']);
    exit();
}

// Feature flag — disabled until koperasi_master / warning_cross_koperasi schema is built
if (function_exists('isFeatureEnabled') && !isFeatureEnabled('cross_koperasi_check')) {
    http_response_code(503);
    echo json_encode([
        'success' => false,
        'error' => 'Cross-koperasi feature is currently disabled (pending schema build-out).',
        'feature_flag' => 'cross_koperasi_check'
    ]);
    exit();
}

// Only specific roles can access cross-koperasi data
$allowed_roles = ['appOwner', 'bos', 'manager_pusat', 'manager_cabang', 'admin_pusat', 'admin_cabang', 'petugas_pusat', 'petugas_cabang'];
if (!in_array($_SESSION['role'], $allowed_roles)) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied. Only admin/petugas can access cross-koperasi check.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];
$current_koperasi_id = getCurrentKoperasiId();
$action = $_GET['action'] ?? 'check_nasabah';

switch ($action) {
    case 'check_nasabah':
        checkNasabahCrossKoperasi($user_id, $current_koperasi_id);
        break;
        
    case 'check_by_ktp':
        checkByKTP($user_id, $current_koperasi_id);
        break;
        
    case 'check_by_telp':
        checkByTelp($user_id, $current_koperasi_id);
        break;
        
    case 'list_warnings':
        listWarnings($current_koperasi_id);
        break;
        
    case 'update_warning_status':
        updateWarningStatus($user_id);
        break;
        
    case 'request_info':
        requestInfo($user_id, $current_koperasi_id);
        break;
        
    case 'sync_nasabah':
        syncNasabahToCrossKoperasi();
        break;
        
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Invalid action']);
}

/**
 * Check nasabah across all koperasi
 */
function checkNasabahCrossKoperasi($user_id, $current_koperasi_id) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Support both GET and POST
    $ktp = $input['ktp'] ?? $_GET['ktp'] ?? null;
    $telp = $input['telp'] ?? $_GET['telp'] ?? null;
    $nama = $input['nama'] ?? $_GET['nama'] ?? null;
    
    if (empty($ktp) && empty($telp)) {
        http_response_code(400);
        echo json_encode(['error' => 'KTP atau Nomor HP wajib diisi untuk pengecekan']);
        return;
    }
    
    $results = [];
    $has_warning = false;
    $total_hutang = 0;
    
    // 1. Check by KTP (primary identifier)
    if ($ktp) {
        $by_ktp = query("
            SELECT 
                n.*,
                k.nama_koperasi,
                p.kode_pinjaman,
                p.jumlah_pinjaman,
                p.sisa_pinjaman,
                p.status as status_pinjaman_detail
            FROM nasabah n
            LEFT JOIN koperasi_master k ON n.koperasi_id = k.id
            LEFT JOIN pinjaman p ON n.id = p.nasabah_id AND p.status = 'aktif'
            WHERE n.ktp = ? 
            AND n.koperasi_id != ?
            AND n.status != 'meninggal'
        ", [$ktp, $current_koperasi_id]);
        
        if ($by_ktp) {
            foreach ($by_ktp as $row) {
                $koperasi_key = $row['koperasi_id'] ?? 'unknown';
                if (!isset($results[$koperasi_key])) {
                    $results[$koperasi_key] = [
                        'koperasi_id' => $row['koperasi_id'],
                        'koperasi_nama' => $row['nama_koperasi'] ?? 'Koperasi Tidak Dikenal',
                        'nasabah_id' => $row['id'],
                        'nama_nasabah' => $row['nama'],
                        'ktp' => $row['ktp'],
                        'telp' => $row['telp'],
                        'pinjaman_aktif' => [],
                        'total_hutang' => 0,
                        'status_nasabah' => $row['status']
                    ];
                }
                
                if ($row['kode_pinjaman']) {
                    $results[$koperasi_key]['pinjaman_aktif'][] = [
                        'kode_pinjaman' => $row['kode_pinjaman'],
                        'jumlah_pinjaman' => (float)$row['jumlah_pinjaman'],
                        'sisa_pinjaman' => (float)$row['sisa_pinjaman'],
                        'status' => $row['status_pinjaman_detail']
                    ];
                    $results[$koperasi_key]['total_hutang'] += (float)$row['sisa_pinjaman'];
                    $total_hutang += (float)$row['sisa_pinjaman'];
                    $has_warning = true;
                }
            }
        }
    }
    
    // 2. Check by Telp (secondary, only if no KTP match or to supplement)
    if ($telp && (empty($results) || empty($ktp))) {
        $by_telp = query("
            SELECT 
                n.*,
                k.nama_koperasi,
                p.kode_pinjaman,
                p.jumlah_pinjaman,
                p.sisa_pinjaman,
                p.status as status_pinjaman_detail
            FROM nasabah n
            LEFT JOIN koperasi_master k ON n.koperasi_id = k.id
            LEFT JOIN pinjaman p ON n.id = p.nasabah_id AND p.status = 'aktif'
            WHERE n.telp = ? 
            AND n.koperasi_id != ?
            AND n.status != 'meninggal'
            AND (n.ktp != ? OR n.ktp IS NULL OR ? IS NULL)
        ", [$telp, $current_koperasi_id, $ktp ?? '', $ktp ?? '']);
        
        if ($by_telp) {
            foreach ($by_telp as $row) {
                $koperasi_key = $row['koperasi_id'] ?? 'unknown';
                if (!isset($results[$koperasi_key])) {
                    $results[$koperasi_key] = [
                        'koperasi_id' => $row['koperasi_id'],
                        'koperasi_nama' => $row['nama_koperasi'] ?? 'Koperasi Tidak Dikenal',
                        'nasabah_id' => $row['id'],
                        'nama_nasabah' => $row['nama'],
                        'ktp' => $row['ktp'],
                        'telp' => $row['telp'],
                        'pinjaman_aktif' => [],
                        'total_hutang' => 0,
                        'status_nasabah' => $row['status']
                    ];
                }
                
                if ($row['kode_pinjaman']) {
                    $results[$koperasi_key]['pinjaman_aktif'][] = [
                        'kode_pinjaman' => $row['kode_pinjaman'],
                        'jumlah_pinjaman' => (float)$row['jumlah_pinjaman'],
                        'sisa_pinjaman' => (float)$row['sisa_pinjaman'],
                        'status' => $row['status_pinjaman_detail']
                    ];
                    $results[$koperasi_key]['total_hutang'] += (float)$row['sisa_pinjaman'];
                    $total_hutang += (float)$row['sisa_pinjaman'];
                    $has_warning = true;
                }
            }
        }
    }
    
    // 3. Log the warning check
    if (!empty($results)) {
        $current_koperasi = query("SELECT nama_koperasi FROM koperasi_master WHERE id = ?", [$current_koperasi_id]);
        $current_koperasi_nama = $current_koperasi[0]['nama_koperasi'] ?? 'Koperasi Tidak Dikenal';
        
        $detail_temuan = [];
        foreach ($results as $r) {
            $detail_temuan[] = [
                'koperasi_id' => $r['koperasi_id'],
                'koperasi_nama' => $r['koperasi_nama'],
                'status' => !empty($r['pinjaman_aktif']) ? 'ada_hutang' : 'terdaftar',
                'jumlah_hutang' => $r['total_hutang']
            ];
        }
        
        query("
            INSERT INTO warning_cross_koperasi 
            (ktp_dicek, telp_dicek, nama_dicek, koperasi_pengecek_id, koperasi_pengecek_nama, 
             user_pengecek_id, jumlah_koperasi_terdaftar, jumlah_koperasi_hutang, detail_temuan, status_warning)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'aktif')
        ", [
            $ktp,
            $telp,
            $nama,
            $current_koperasi_id,
            $current_koperasi_nama,
            $user_id,
            count($results),
            count(array_filter($results, fn($r) => $r['total_hutang'] > 0)),
            json_encode($detail_temuan)
        ]);
    }
    
    // Re-index array
    $results = array_values($results);
    
    echo json_encode([
        'success' => true,
        'has_warning' => $has_warning,
        'data' => [
            'ktp_dicek' => $ktp,
            'telp_dicek' => $telp,
            'nama_dicek' => $nama,
            'jumlah_koperasi_ditemukan' => count($results),
            'total_hutang_cross_koperasi' => $total_hutang,
            'koperasi_ditemukan' => $results,
            'rekomendasi' => $has_warning 
                ? 'NASABAH MASIH MEMILIKI HUTANG DI KOPERASI LAIN. Pertimbangkan risiko sebelum memberikan pinjaman.'
                : 'Nasabah tidak ditemukan di koperasi lain dengan hutang aktif.'
        ]
    ]);
}

/**
 * Check by KTP only
 */
function checkByKTP($user_id, $current_koperasi_id) {
    $ktp = $_GET['ktp'] ?? null;
    
    if (!$ktp) {
        http_response_code(400);
        echo json_encode(['error' => 'KTP required']);
        return;
    }
    
    // Get current koperasi name
    $koperasi = query("SELECT nama_koperasi FROM koperasi_master WHERE id = ?", [$current_koperasi_id]);
    $koperasi_nama = $koperasi[0]['nama_koperasi'] ?? 'Unknown';
    
    // Search across all koperasi except current
    $nasabah_list = query("
        SELECT 
            n.id as nasabah_id,
            n.nama,
            n.ktp,
            n.telp,
            n.status as status_nasabah,
            n.koperasi_id,
            k.nama_koperasi,
            COUNT(p.id) as jumlah_pinjaman_aktif,
            SUM(COALESCE(p.sisa_pinjaman, 0)) as total_hutang
        FROM nasabah n
        LEFT JOIN koperasi_master k ON n.koperasi_id = k.id
        LEFT JOIN pinjaman p ON n.id = p.nasabah_id AND p.status = 'aktif'
        WHERE n.ktp = ?
        AND n.koperasi_id != ?
        AND n.status != 'meninggal'
        GROUP BY n.id, n.koperasi_id
    ", [$ktp, $current_koperasi_id]);
    
    echo json_encode([
        'success' => true,
        'ktp' => $ktp,
        'koperasi_pengecek' => $koperasi_nama,
        'jumlah_ditemukan' => count($nasabah_list),
        'data' => $nasabah_list
    ]);
}

/**
 * Check by Telp only
 */
function checkByTelp($user_id, $current_koperasi_id) {
    $telp = $_GET['telp'] ?? null;
    
    if (!$telp) {
        http_response_code(400);
        echo json_encode(['error' => 'Telp required']);
        return;
    }
    
    // Clean phone number
    $telp = preg_replace('/[^0-9]/', '', $telp);
    
    $koperasi = query("SELECT nama_koperasi FROM koperasi_master WHERE id = ?", [$current_koperasi_id]);
    $koperasi_nama = $koperasi[0]['nama_koperasi'] ?? 'Unknown';
    
    $nasabah_list = query("
        SELECT 
            n.id as nasabah_id,
            n.nama,
            n.ktp,
            n.telp,
            n.status as status_nasabah,
            n.koperasi_id,
            k.nama_koperasi,
            COUNT(p.id) as jumlah_pinjaman_aktif,
            SUM(COALESCE(p.sisa_pinjaman, 0)) as total_hutang
        FROM nasabah n
        LEFT JOIN koperasi_master k ON n.koperasi_id = k.id
        LEFT JOIN pinjaman p ON n.id = p.nasabah_id AND p.status = 'aktif'
        WHERE REPLACE(REPLACE(REPLACE(n.telp, '-', ''), ' ', ''), '+', '') LIKE ?
        AND n.koperasi_id != ?
        AND n.status != 'meninggal'
        GROUP BY n.id, n.koperasi_id
    ", ["%$telp%", $current_koperasi_id]);
    
    echo json_encode([
        'success' => true,
        'telp' => $telp,
        'koperasi_pengecek' => $koperasi_nama,
        'jumlah_ditemukan' => count($nasabah_list),
        'data' => $nasabah_list
    ]);
}

/**
 * List warnings for current koperasi
 */
function listWarnings($current_koperasi_id) {
    $status = $_GET['status'] ?? 'aktif';
    $limit = (int)($_GET['limit'] ?? 50);
    
    $warnings = query("
        SELECT * FROM warning_cross_koperasi 
        WHERE koperasi_pengecek_id = ? 
        AND status_warning = ?
        ORDER BY created_at DESC
        LIMIT ?
    ", [$current_koperasi_id, $status, $limit]);
    
    $total = query("SELECT COUNT(*) as count FROM warning_cross_koperasi WHERE koperasi_pengecek_id = ?", [$current_koperasi_id])[0]['count'];
    $aktif = query("SELECT COUNT(*) as count FROM warning_cross_koperasi WHERE koperasi_pengecek_id = ? AND status_warning = 'aktif'", [$current_koperasi_id])[0]['count'];
    
    echo json_encode([
        'success' => true,
        'summary' => [
            'total' => (int)$total,
            'aktif' => (int)$aktif
        ],
        'data' => $warnings
    ]);
}

/**
 * Update warning status
 */
function updateWarningStatus($user_id) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['warning_id']) || empty($input['status'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Warning ID and status required']);
        return;
    }
    
    $valid_status = ['aktif', 'dilihat', 'diabaikan', 'ditindaklanjuti'];
    if (!in_array($input['status'], $valid_status)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid status']);
        return;
    }
    
    $result = query("
        UPDATE warning_cross_koperasi 
        SET status_warning = ?, catatan_tindak_lanjut = ?, updated_at = NOW()
        WHERE id = ?
    ", [$input['status'], $input['catatan'] ?? null, $input['warning_id']]);
    
    if ($result !== false) {
        echo json_encode([
            'success' => true,
            'message' => 'Status warning diupdate'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Gagal mengupdate status']);
    }
}

/**
 * Request info from other koperasi
 */
function requestInfo($user_id, $current_koperasi_id) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['ke_koperasi_id']) || empty($input['ktp_nasabah'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Target koperasi and KTP required']);
        return;
    }
    
    // Get koperasi names
    $dari_koperasi = query("SELECT nama_koperasi FROM koperasi_master WHERE id = ?", [$current_koperasi_id]);
    $ke_koperasi = query("SELECT nama_koperasi FROM koperasi_master WHERE id = ?", [$input['ke_koperasi_id']]);
    
    $result = query("
        INSERT INTO permintaan_info_koperasi 
        (dari_koperasi_id, dari_koperasi_nama, dari_user_id, ke_koperasi_id, ke_koperasi_nama,
         ktp_nasabah, nama_nasabah, jenis_permintaan, pesan)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ", [
        $current_koperasi_id,
        $dari_koperasi[0]['nama_koperasi'] ?? 'Unknown',
        $user_id,
        $input['ke_koperasi_id'],
        $ke_koperasi[0]['nama_koperasi'] ?? 'Unknown',
        $input['ktp_nasabah'],
        $input['nama_nasabah'] ?? null,
        $input['jenis_permintaan'] ?? 'info_pinjaman',
        $input['pesan'] ?? 'Mohon informasi status pinjaman nasabah ini'
    ]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Permintaan info terkirim ke koperasi terkait',
            'permintaan_id' => $result
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Gagal mengirim permintaan']);
    }
}

/**
 * Sync nasabah data to cross-koperasi table (run periodically)
 */
function syncNasabahToCrossKoperasi() {
    // Only appOwner can run this
    if ($_SESSION['role'] !== 'appOwner') {
        http_response_code(403);
        echo json_encode(['error' => 'Only appOwner can run sync']);
        return;
    }
    
    // Clear old data
    query("TRUNCATE TABLE nasabah_cross_koperasi");
    
    // Insert current data
    query("
        INSERT INTO nasabah_cross_koperasi 
        (ktp, telp, nama, nasabah_id, koperasi_id, koperasi_nama, status_pinjaman, jumlah_hutang, jumlah_pinjaman_aktif)
        SELECT 
            n.ktp,
            n.telp,
            n.nama,
            n.id,
            n.koperasi_id,
            k.nama_koperasi,
            CASE 
                WHEN n.status = 'blacklist' THEN 'blacklist'
                WHEN COUNT(p.id) > 0 THEN 'aktif'
                ELSE 'tidak_ada'
            END,
            SUM(COALESCE(p.sisa_pinjaman, 0)),
            COUNT(p.id)
        FROM nasabah n
        LEFT JOIN koperasi_master k ON n.koperasi_id = k.id
        LEFT JOIN pinjaman p ON n.id = p.nasabah_id AND p.status = 'aktif'
        WHERE n.status != 'meninggal'
        GROUP BY n.id
    ");
    
    $count = query("SELECT COUNT(*) as count FROM nasabah_cross_koperasi")[0]['count'];
    
    echo json_encode([
        'success' => true,
        'message' => 'Sync completed',
        'total_records' => (int)$count
    ]);
}
