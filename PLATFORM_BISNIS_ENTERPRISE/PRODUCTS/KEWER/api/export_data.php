<?php
/**
 * API: Export Data (CSV)
 * GET /api/export_data.php?entity=nasabah|pinjaman|angsuran|pembayaran
 */
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

try {
    require_once __DIR__ . '/../config/path.php';
    require_once BASE_PATH . '/includes/functions.php';
    require_once BASE_PATH . '/includes/feature_flags.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Configuration error: ' . $e->getMessage()]);
    exit();
}

// Authentication check
requireLogin();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($method !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

$entity = $_GET['entity'] ?? '';
$cabang_filter = buildCabangFilter('cabang_id');

switch ($entity) {
    case 'nasabah':
        $where = '';
        $params = [];
        if ($cabang_filter) {
            $where = ltrim($cabang_filter['clause'], 'AND ');
            $params = $cabang_filter['params'];
        }
        $where_clause = !empty($where) ? "WHERE $where" : '';
        
        $data = query("
            SELECT 
                n.id, n.kode_nasabah, n.nama, n.telepon, n.alamat, n.status,
                n.total_pinjaman_aktif, n.created_at, n.updated_at,
                c.nama_cabang
            FROM nasabah n
            LEFT JOIN cabang c ON n.cabang_id = c.id
            $where_clause
            ORDER BY n.created_at DESC
        ", $params);
        
        $filename = 'nasabah_' . date('Y-m-d_His');
        break;
        
    case 'pinjaman':
        $where = [];
        $params = [];
        if ($cabang_filter) {
            $where[] = ltrim($cabang_filter['clause'], 'AND ');
            $params = array_merge($params, $cabang_filter['params']);
        }
        $where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        $data = query("
            SELECT 
                p.id, p.kode_pinjaman, p.plafon, p.tenor, p.bunga_per_bulan,
                p.status, p.tanggal_akad, p.tanggal_jatuh_tempo, p.sisa_pokok_berjalan,
                p.created_at, p.updated_at,
                n.nama as nama_nasabah, n.kode_nasabah,
                c.nama_cabang
            FROM pinjaman p
            JOIN nasabah n ON p.nasabah_id = n.id
            LEFT JOIN cabang c ON p.cabang_id = c.id
            $where_clause
            ORDER BY p.created_at DESC
        ", $params);
        
        $filename = 'pinjaman_' . date('Y-m-d_His');
        break;
        
    case 'angsuran':
        $where = [];
        $params = [];
        if ($cabang_filter) {
            $where[] = ltrim($cabang_filter['clause'], 'AND ');
            $params = array_merge($params, $cabang_filter['params']);
        }
        $where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        $data = query("
            SELECT 
                a.id, a.ke, a.jatuh_tempo, a.total_angsuran, a.total_bayar,
                a.status, a.tanggal_bayar, a.hari_telat_saat_bayar,
                a.created_at, a.updated_at,
                p.kode_pinjaman,
                n.nama as nama_nasabah, n.kode_nasabah,
                c.nama_cabang
            FROM angsuran a
            JOIN pinjaman p ON a.pinjaman_id = p.id
            JOIN nasabah n ON p.nasabah_id = n.id
            LEFT JOIN cabang c ON p.cabang_id = c.id
            $where_clause
            ORDER BY a.jatuh_tempo DESC
        ", $params);
        
        $filename = 'angsuran_' . date('Y-m-d_His');
        break;
        
    case 'pembayaran':
        $where = [];
        $params = [];
        if ($cabang_filter) {
            $where[] = ltrim($cabang_filter['clause'], 'AND ');
            $params = array_merge($params, $cabang_filter['params']);
        }
        $where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        $data = query("
            SELECT 
                py.id, py.kode_pembayaran, py.tanggal_bayar, py.jumlah_bayar,
                py.denda, py.denda_dibayar, py.total_bayar, py.cara_bayar,
                py.created_at, py.updated_at,
                p.kode_pinjaman,
                a.ke as angsuran_ke,
                n.nama as nama_nasabah, n.kode_nasabah,
                c.nama_cabang,
                u.nama as nama_petugas
            FROM pembayaran py
            JOIN angsuran a ON py.angsuran_id = a.id
            JOIN pinjaman p ON a.pinjaman_id = p.id
            JOIN nasabah n ON p.nasabah_id = n.id
            LEFT JOIN cabang c ON py.cabang_id = c.id
            LEFT JOIN users u ON py.petugas_id = u.id
            $where_clause
            ORDER BY py.tanggal_bayar DESC
        ", $params);
        
        $filename = 'pembayaran_' . date('Y-m-d_His');
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Entity tidak dikenal. Gunakan: nasabah, pinjaman, angsuran, pembayaran']);
        exit();
}

if (!is_array($data)) $data = [];

// Export CSV
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '.csv"');

$out = fopen('php://output', 'w');
fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8

if (!empty($data)) {
    // Headers
    $headers = array_keys($data[0]);
    fputcsv($out, $headers);
    
    // Data rows
    foreach ($data as $row) {
        fputcsv($out, $row);
    }
}

fclose($out);
exit;
