<?php
/**
 * Dashboard Analytics API
 * Provides real-time metrics and analytics data for dashboard
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

// Get request parameters
$action = $_GET['action'] ?? 'overview';
$cabang_id = $_GET['cabang_id'] ?? $_SESSION['cabang_id'] ?? null;
$period = $_GET['period'] ?? '30'; // days (7, 30, 60, 90)
$start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime("-{$period} days"));
$end_date = $_GET['end_date'] ?? date('Y-m-d');

try {
    switch ($action) {
        case 'overview':
            echo json_encode(getOverviewMetrics($cabang_id, $start_date, $end_date));
            break;
        case 'collection_rate':
            echo json_encode(getCollectionRate($cabang_id, $start_date, $end_date));
            break;
        case 'npl_ratio':
            echo json_encode(getNPLRatio($cabang_id, $start_date, $end_date));
            break;
        case 'pinjaman_aktif':
            echo json_encode(getPinjamanAktif($cabang_id));
            break;
        case 'angsuran_bulanan':
            echo json_encode(getAngsuranBulanan($cabang_id, $start_date, $end_date));
            break;
        case 'cabang_comparison':
            echo json_encode(getCabangComparison($start_date, $end_date));
            break;
        case 'trend_analysis':
            echo json_encode(getTrendAnalysis($cabang_id, $period));
            break;
        case 'top_nasabah':
            echo json_encode(getTopNasabah($cabang_id, $start_date, $end_date));
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

/**
 * Get overview metrics
 */
function getOverviewMetrics($cabang_id, $start_date, $end_date) {
    $cabang_filter = $cabang_id ? "AND p.cabang_id = ?" : "";
    $params = $cabang_id ? [$cabang_id] : [];
    
    // Total pinjaman aktif
    $sql = "SELECT COUNT(*) as total, SUM(p.plafon) as total_jumlah 
            FROM pinjaman p 
            WHERE p.status = 'disetujui' $cabang_filter";
    $result = query($sql, $params);
    $pinjaman = is_array($result) && isset($result[0]) ? $result[0] : ['total' => 0, 'total_jumlah' => 0];
    
    // Total angsuran bulan ini
    $sql = "SELECT COUNT(*) as total, SUM(a.total_bayar) as total_nominal 
            FROM angsuran a 
            JOIN pinjaman p ON a.pinjaman_id = p.id
            WHERE a.status = 'lunas' 
            AND DATE(a.tanggal_bayar) BETWEEN ? AND ? 
            $cabang_filter";
    $params2 = array_merge([$start_date, $end_date], $params);
    $result = query($sql, $params2);
    $angsuran = is_array($result) && isset($result[0]) ? $result[0] : ['total' => 0, 'total_nominal' => 0];
    
    // Total nasabah
    $sql = "SELECT COUNT(*) as total FROM nasabah WHERE status = 'aktif' $cabang_filter";
    $result = query($sql, $params);
    $nasabah = is_array($result) && isset($result[0]) ? $result[0] : ['total' => 0];
    
    // Total petugas
    $sql = "SELECT COUNT(*) as total FROM users WHERE role IN ('petugas_pusat', 'petugas_cabang') $cabang_filter";
    $result = query($sql, $params);
    $petugas = is_array($result) && isset($result[0]) ? $result[0] : ['total' => 0];
    
    return [
        'success' => true,
        'data' => [
            'pinjaman_aktif' => $pinjaman['total'],
            'total_pinjaman' => $pinjaman['total_jumlah'] ?? 0,
            'angsuran_bulan_ini' => $angsuran['total'],
            'total_angsuran' => $angsuran['total_nominal'] ?? 0,
            'total_nasabah' => $nasabah['total'],
            'total_petugas' => $petugas['total']
        ]
    ];
}

/**
 * Get collection rate (persentase pembayaran tepat waktu)
 */
function getCollectionRate($cabang_id, $start_date, $end_date) {
    $cabang_filter = $cabang_id ? "AND p.cabang_id = ?" : "";
    $params = $cabang_id ? [$cabang_id] : [];
    
    // Total angsuran jatuh tempo dalam periode
    $sql = "SELECT COUNT(*) as total 
            FROM angsuran a 
            JOIN pinjaman p ON a.pinjaman_id = p.id
            WHERE a.tanggal_jatuh_tempo BETWEEN ? AND ? 
            $cabang_filter";
    $params2 = array_merge([$start_date, $end_date], $params);
    $result = query($sql, $params2);
    $total = is_array($result) && isset($result[0]) ? $result[0]['total'] : 0;
    
    // Total angsuran dibayar tepat waktu (<= 3 hari dari jatuh tempo)
    $sql = "SELECT COUNT(*) as total 
            FROM angsuran a 
            JOIN pinjaman p ON a.pinjaman_id = p.id
            WHERE a.tanggal_jatuh_tempo BETWEEN ? AND ? 
            AND a.status = 'lunas'
            AND DATEDIFF(a.tanggal_bayar, a.tanggal_jatuh_tempo) <= 3
            $cabang_filter";
    $result = query($sql, $params2);
    $tepat_waktu = is_array($result) && isset($result[0]) ? $result[0]['total'] : 0;
    
    $rate = $total > 0 ? round(($tepat_waktu / $total) * 100, 2) : 0;
    
    return [
        'success' => true,
        'data' => [
            'collection_rate' => $rate,
            'total_jatuh_tempo' => $total,
            'tepat_waktu' => $tepat_waktu
        ]
    ];
}

/**
 * Get NPL Ratio (Non-Performing Loan)
 */
function getNPLRatio($cabang_id, $start_date, $end_date) {
    $cabang_filter = $cabang_id ? "AND p.cabang_id = ?" : "";
    $params = $cabang_id ? [$cabang_id] : [];
    
    // Total pinjaman aktif
    $sql = "SELECT SUM(p.plafon) as total 
            FROM pinjaman p 
            WHERE p.status = 'disetujui' 
            $cabang_filter";
    $result = query($sql, $params);
    $total_pinjaman = is_array($result) && isset($result[0]) ? $result[0]['total'] : 0;
    
    // Total pinjaman bermasalah (tunggakan > 30 hari)
    $sql = "SELECT SUM(p.plafon) as total 
            FROM pinjaman p 
            WHERE p.status = 'disetujui' 
            AND EXISTS (
                SELECT 1 FROM angsuran a 
                WHERE a.pinjaman_id = p.id 
                AND a.status = 'belum_lunas'
                AND DATEDIFF(CURDATE(), a.tanggal_jatuh_tempo) > 30
            )
            $cabang_filter";
    $result = query($sql, $params);
    $npl = is_array($result) && isset($result[0]) ? $result[0]['total'] : 0;
    
    $npl_ratio = $total_pinjaman > 0 ? round(($npl / $total_pinjaman) * 100, 2) : 0;
    
    return [
        'success' => true,
        'data' => [
            'npl_ratio' => $npl_ratio,
            'total_pinjaman' => $total_pinjaman,
            'npl_amount' => $npl
        ]
    ];
}

/**
 * Get pinjaman aktif data
 */
function getPinjamanAktif($cabang_id) {
    $cabang_filter = $cabang_id ? "WHERE p.cabang_id = ?" : "";
    $params = $cabang_id ? [$cabang_id] : [];
    
    $sql = "SELECT p.id, p.kode_pinjaman AS no_pinjaman, n.nama AS nama_nasabah, p.plafon, 
            p.tanggal_akad AS tanggal_pengajuan, p.status, c.nama_cabang
            FROM pinjaman p
            LEFT JOIN nasabah n ON p.nasabah_id = n.id
            LEFT JOIN cabang c ON p.cabang_id = c.id
            $cabang_filter
            ORDER BY p.tanggal_akad DESC
            LIMIT 50";
    
    $result = query($sql, $params);
    
    return [
        'success' => true,
        'data' => is_array($result) ? $result : []
    ];
}

/**
 * Get angsuran bulanan
 */
function getAngsuranBulanan($cabang_id, $start_date, $end_date) {
    $cabang_filter = $cabang_id ? "AND p.cabang_id = ?" : "";
    $params = $cabang_id ? [$cabang_id] : [];
    
    $sql = "SELECT DATE_FORMAT(a.tanggal_bayar, '%Y-%m') as bulan,
            COUNT(*) as total_angsuran,
            SUM(a.total_bayar) as total_nominal
            FROM angsuran a
            JOIN pinjaman p ON a.pinjaman_id = p.id
            WHERE a.status = 'lunas'
            AND a.tanggal_bayar BETWEEN ? AND ?
            $cabang_filter
            GROUP BY DATE_FORMAT(a.tanggal_bayar, '%Y-%m')
            ORDER BY bulan";
    
    $params2 = array_merge([$start_date, $end_date], $params);
    $result = query($sql, $params2);
    
    return [
        'success' => true,
        'data' => is_array($result) ? $result : []
    ];
}

/**
 * Get cabang comparison
 */
function getCabangComparison($start_date, $end_date) {
    $sql = "SELECT c.id, c.nama_cabang,
            COUNT(DISTINCT p.id) as total_pinjaman,
            SUM(p.plafon) as total_jumlah,
            SUM(CASE WHEN a.status = 'lunas' THEN a.total_bayar ELSE 0 END) as total_dibayar
            FROM cabang c
            LEFT JOIN pinjaman p ON c.id = p.cabang_id AND p.status = 'disetujui'
            LEFT JOIN angsuran a ON p.id = a.pinjaman_id AND a.status = 'lunas'
            AND a.tanggal_bayar BETWEEN ? AND ?
            GROUP BY c.id, c.nama_cabang
            ORDER BY total_jumlah DESC";
    
    $result = query($sql, [$start_date, $end_date]);
    
    return [
        'success' => true,
        'data' => is_array($result) ? $result : []
    ];
}

/**
 * Get trend analysis
 */
function getTrendAnalysis($cabang_id, $period) {
    $cabang_filter = $cabang_id ? "AND p.cabang_id = ?" : "";
    $params = $cabang_id ? [$cabang_id] : [];
    
    // Daily trend for last N days
    $sql = "SELECT DATE(a.tanggal_bayar) as tanggal,
            COUNT(*) as total_angsuran,
            SUM(a.total_bayar) as total_nominal
            FROM angsuran a
            JOIN pinjaman p ON a.pinjaman_id = p.id
            WHERE a.status = 'lunas'
            AND a.tanggal_bayar >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
            $cabang_filter
            GROUP BY DATE(a.tanggal_bayar)
            ORDER BY tanggal";
    
    $params2 = array_merge([$period], $params);
    $result = query($sql, $params2);
    
    return [
        'success' => true,
        'data' => is_array($result) ? $result : []
    ];
}

/**
 * Get top nasabah
 */
function getTopNasabah($cabang_id, $start_date, $end_date) {
    $cabang_filter = $cabang_id ? "AND p.cabang_id = ?" : "";
    $params = $cabang_id ? [$cabang_id] : [];
    
    $sql = "SELECT n.id, n.nama, n.ktp,
            COUNT(DISTINCT p.id) as total_pinjaman,
            SUM(p.plafon) as total_jumlah,
            SUM(CASE WHEN a.status = 'lunas' THEN a.total_bayar ELSE 0 END) as total_dibayar
            FROM nasabah n
            LEFT JOIN pinjaman p ON n.id = p.nasabah_id AND p.status = 'disetujui'
            LEFT JOIN angsuran a ON p.id = a.pinjaman_id AND a.status = 'lunas'
            AND a.tanggal_bayar BETWEEN ? AND ?
            WHERE n.status = 'aktif'
            $cabang_filter
            GROUP BY n.id, n.nama, n.ktp
            HAVING total_pinjaman > 0
            ORDER BY total_jumlah DESC
            LIMIT 10";
    
    $params2 = array_merge([$start_date, $end_date], $params);
    $result = query($sql, $params2);
    
    return [
        'success' => true,
        'data' => is_array($result) ? $result : []
    ];
}
