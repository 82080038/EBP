<?php
/**
 * Geographic Analysis API
 * Provides radius-based search, demographic analysis, and geographic features
 * 
 * @author Kewer Development Team
 * @version 1.0.0
 */

require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../src/Geo/GPSTracker.php';

header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? 'radius_search';

try {
    switch ($action) {
        case 'radius_search':
            echo json_encode(radiusSearch());
            break;
        case 'demographic_analysis':
            echo json_encode(demographicAnalysis());
            break;
        case 'risk_by_location':
            echo json_encode(riskByLocation());
            break;
        case 'heatmap_data':
            echo json_encode(heatmapData());
            break;
        case 'area_classification':
            echo json_encode(areaClassification());
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

/**
 * Radius-based nasabah search
 */
function radiusSearch() {
    $cabang_id = $_GET['cabang_id'] ?? $_SESSION['cabang_id'] ?? null;
    $radius_km = $_GET['radius_km'] ?? 5; // Default 5km
    $limit = $_GET['limit'] ?? 50;
    
    if (!$cabang_id) {
        return ['success' => false, 'message' => 'cabang_id required'];
    }
    
    // Get cabang location
    $sql = "SELECT latitude, longitude FROM cabang WHERE id = ?";
    $result = query($sql, [$cabang_id]);
    
    if (!is_array($result) || empty($result) || !$result[0]['latitude'] || !$result[0]['longitude']) {
        return ['success' => false, 'message' => 'Cabang location not set'];
    }
    
    $centerLat = $result[0]['latitude'];
    $centerLng = $result[0]['longitude'];
    $radiusMeters = $radius_km * 1000;
    
    // Get nasabah with addresses
    $sql = "SELECT n.id, n.nama, n.ktp, a.latitude, a.longitude, c.nama_cabang,
            p.jumlah_pinjaman, p.status as pinjaman_status
            FROM nasabah n
            LEFT JOIN db_orang.addresses a ON n.db_orang_address_id = a.id
            LEFT JOIN cabang c ON n.cabang_id = c.id
            LEFT JOIN pinjaman p ON n.id = p.nasabah_id AND p.status = 'disetujui'
            WHERE n.cabang_id = ? AND n.status = 'aktif'
            AND a.latitude IS NOT NULL AND a.longitude IS NOT NULL
            LIMIT 1000";
    
    $result = query($sql, [$cabang_id]);
    
    if (!is_array($result)) {
        return ['success' => false, 'message' => 'Failed to fetch nasabah'];
    }
    
    $withinRadius = [];
    
    foreach ($result as $nasabah) {
        $distance = \Kewer\Geo\GPSTracker::calculateDistance($centerLat, $centerLng, $nasabah['latitude'], $nasabah['longitude']);
        
        if ($distance <= $radiusMeters) {
            $nasabah['distance_meters'] = $distance;
            $nasabah['distance_km'] = round($distance / 1000, 2);
            $withinRadius[] = $nasabah;
        }
    }
    
    // Sort by distance
    usort($withinRadius, function($a, $b) {
        return $a['distance_meters'] <=> $b['distance_meters'];
    });
    
    // Limit results
    $withinRadius = array_slice($withinRadius, 0, $limit);
    
    return [
        'success' => true,
        'data' => [
            'center' => ['lat' => $centerLat, 'lng' => $centerLng],
            'radius_km' => $radius_km,
            'total_found' => count($withinRadius),
            'nasabah' => $withinRadius
        ]
    ];
}

/**
 * Demographic analysis per area
 */
function demographicAnalysis() {
    $area_type = $_GET['area_type'] ?? 'district'; // province, regency, district, village
    $area_id = $_GET['area_id'] ?? null;
    
    if (!$area_id) {
        return ['success' => false, 'message' => 'area_id required'];
    }
    
    // Get nasabah in this area
    $sql = "SELECT n.id, n.nama, n.tanggal_lahir, n.pekerjaan, n.pendapatan,
            COUNT(DISTINCT p.id) as total_pinjaman,
            SUM(p.jumlah_pinjaman) as total_jumlah_pinjaman,
            SUM(CASE WHEN a.status = 'lunas' THEN a.nominal ELSE 0 END) as total_dibayar
            FROM nasabah n
            LEFT JOIN db_orang.addresses a ON n.db_orang_address_id = a.id
            LEFT JOIN pinjaman p ON n.id = p.nasabah_id AND p.status = 'disetujui'
            LEFT JOIN angsuran an ON p.id = an.pinjaman_id
            WHERE a.{$area_type}_id = ? AND n.status = 'aktif'
            GROUP BY n.id";
    
    $result = query($sql, [$area_id]);
    
    if (!is_array($result)) {
        return ['success' => false, 'message' => 'Failed to fetch nasabah'];
    }
    
    // Calculate statistics
    $totalNasabah = count($result);
    $totalPinjaman = 0;
    $totalJumlahPinjaman = 0;
    $totalDibayar = 0;
    $ageGroups = ['18-25' => 0, '26-35' => 0, '36-45' => 0, '46-55' => 0, '55+' => 0];
    $incomeGroups = ['<1M' => 0, '1-3M' => 0, '3-5M' => 0, '5-10M' => 0, '>10M' => 0];
    
    foreach ($result as $nasabah) {
        $totalPinjaman += $nasabah['total_pinjaman'] ?? 0;
        $totalJumlahPinjaman += $nasabah['total_jumlah_pinjaman'] ?? 0;
        $totalDibayar += $nasabah['total_dibayar'] ?? 0;
        
        // Age group
        if ($nasabah['tanggal_lahir']) {
            $age = calculateAge($nasabah['tanggal_lahir']);
            if ($age >= 18 && $age <= 25) $ageGroups['18-25']++;
            elseif ($age >= 26 && $age <= 35) $ageGroups['26-35']++;
            elseif ($age >= 36 && $age <= 45) $ageGroups['36-45']++;
            elseif ($age >= 46 && $age <= 55) $ageGroups['46-55']++;
            elseif ($age > 55) $ageGroups['55+']++;
        }
        
        // Income group
        $income = (int) ($nasabah['pendapatan'] ?? 0);
        if ($income < 1000000) $incomeGroups['<1M']++;
        elseif ($income < 3000000) $incomeGroups['1-3M']++;
        elseif ($income < 5000000) $incomeGroups['3-5M']++;
        elseif ($income < 10000000) $incomeGroups['5-10M']++;
        else $incomeGroups['>10M']++;
    }
    
    // Get area classification
    $areaSql = "SELECT klasifikasi, nama FROM db_alamat.{$area_type}s WHERE id = ?";
    $areaResult = query_alamat($areaSql, [$area_id]);
    $areaInfo = is_array($areaResult) && isset($areaResult[0]) ? $areaResult[0] : ['klasifikasi' => 'unknown', 'nama' => 'Unknown'];
    
    return [
        'success' => true,
        'data' => [
            'area' => $areaInfo,
            'total_nasabah' => $totalNasabah,
            'total_pinjaman' => $totalPinjaman,
            'total_jumlah_pinjaman' => $totalJumlahPinjaman,
            'total_dibayar' => $totalDibayar,
            'collection_rate' => $totalJumlahPinjaman > 0 ? round(($totalDibayar / $totalJumlahPinjaman) * 100, 2) : 0,
            'age_distribution' => $ageGroups,
            'income_distribution' => $incomeGroups
        ]
    ];
}

/**
 * Risk scoring by location
 */
function riskByLocation() {
    $area_type = $_GET['area_type'] ?? 'district';
    $limit = $_GET['limit'] ?? 20;
    
    // Get areas with risk scores
    $sql = "SELECT a.{$area_type}_id, area.nama as area_nama, area.klasifikasi,
            COUNT(DISTINCT n.id) as total_nasabah,
            AVG(n.credit_score) as avg_credit_score,
            SUM(CASE WHEN n.risk_level = 'Sangat Tinggi' THEN 1 ELSE 0 END) as sangat_tinggi,
            SUM(CASE WHEN n.risk_level = 'Tinggi' THEN 1 ELSE 0 END) as tinggi,
            SUM(CASE WHEN n.risk_level = 'Sedang' THEN 1 ELSE 0 END) as sedang,
            SUM(CASE WHEN n.risk_level = 'Rendah' THEN 1 ELSE 0 END) as rendah,
            SUM(CASE WHEN n.risk_level = 'Sangat Rendah' THEN 1 ELSE 0 END) as sangat_rendah
            FROM nasabah n
            LEFT JOIN db_orang.addresses a ON n.db_orang_address_id = a.id
            LEFT JOIN db_alamat.{$area_type}s area ON a.{$area_type}_id = area.id
            WHERE n.status = 'aktif' AND n.credit_score IS NOT NULL
            GROUP BY a.{$area_type}_id, area.nama, area.klasifikasi
            HAVING total_nasabah > 0
            ORDER BY avg_credit_score ASC
            LIMIT ?";
    
    $result = query($sql, [$limit]);
    
    if (!is_array($result)) {
        return ['success' => false, 'message' => 'Failed to fetch data'];
    }
    
    // Calculate risk score for each area
    foreach ($result as &$area) {
        $total = $area['total_nasabah'];
        $riskScore = 0;
        
        if ($total > 0) {
            $riskScore = (
                ($area['sangat_tinggi'] / $total) * 100 +
                ($area['tinggi'] / $total) * 75 +
                ($area['sedang'] / $total) * 50 +
                ($area['rendah'] / $total) * 25 +
                ($area['sangat_rendah'] / $total) * 0
            );
        }
        
        $area['area_risk_score'] = round($riskScore, 2);
        $area['risk_level'] = getRiskLevel($riskScore);
    }
    
    return [
        'success' => true,
        'data' => $result
    ];
}

/**
 * Heatmap data for nasabah distribution
 */
function heatmapData() {
    $cabang_id = $_GET['cabang_id'] ?? $_SESSION['cabang_id'] ?? null;
    
    if (!$cabang_id) {
        return ['success' => false, 'message' => 'cabang_id required'];
    }
    
    // Get cabang location
    $sql = "SELECT latitude, longitude FROM cabang WHERE id = ?";
    $result = query($sql, [$cabang_id]);
    
    if (!is_array($result) || empty($result)) {
        return ['success' => false, 'message' => 'Cabang not found'];
    }
    
    $centerLat = $result[0]['latitude'];
    $centerLng = $result[0]['longitude'];
    
    // Get all nasabah with GPS coordinates
    $sql = "SELECT n.id, n.nama, a.latitude, a.longitude,
            p.jumlah_pinjaman, n.credit_score, n.risk_level
            FROM nasabah n
            LEFT JOIN db_orang.addresses a ON n.db_orang_address_id = a.id
            LEFT JOIN pinjaman p ON n.id = p.nasabah_id AND p.status = 'disetujui'
            WHERE n.cabang_id = ? AND n.status = 'aktif'
            AND a.latitude IS NOT NULL AND a.longitude IS NOT NULL
            LIMIT 500";
    
    $result = query($sql, [$cabang_id]);
    
    if (!is_array($result)) {
        return ['success' => false, 'message' => 'Failed to fetch nasabah'];
    }
    
    $heatmapPoints = [];
    
    foreach ($result as $nasabah) {
        $heatmapPoints[] = [
            'lat' => (float) $nasabah['latitude'],
            'lng' => (float) $nasabah['longitude'],
            'weight' => 1, // Each nasabah = 1 point
            'nasabah_id' => $nasabah['id'],
            'nama' => $nasabah['nama'],
            'credit_score' => $nasabah['credit_score'],
            'risk_level' => $nasabah['risk_level']
        ];
    }
    
    return [
        'success' => true,
        'data' => [
            'center' => ['lat' => $centerLat, 'lng' => $centerLng],
            'total_points' => count($heatmapPoints),
            'points' => $heatmapPoints
        ]
    ];
}

/**
 * Area classification (urban vs rural)
 */
function areaClassification() {
    $area_type = $_GET['area_type'] ?? 'district';
    
    // Get all areas with classification
    $sql = "SELECT id, nama, klasifikasi, COUNT(DISTINCT n.id) as total_nasabah
            FROM db_alamat.{$area_type}s a
            LEFT JOIN db_orang.addresses addr ON a.id = addr.{$area_type}_id
            LEFT JOIN nasabah n ON addr.person_id = n.db_orang_user_id AND n.status = 'aktif'
            GROUP BY a.id, a.nama, a.klasifikasi
            ORDER BY total_nasabah DESC";
    
    $result = query_alamat($sql);
    
    if (!is_array($result)) {
        return ['success' => false, 'message' => 'Failed to fetch areas'];
    }
    
    // Classify areas
    $urban = [];
    $rural = [];
    
    foreach ($result as $area) {
        if ($area['klasifikasi'] == 'urban') {
            $urban[] = $area;
        } else {
            $rural[] = $area;
        }
    }
    
    return [
        'success' => true,
        'data' => [
            'urban_areas' => $urban,
            'rural_areas' => $rural,
            'total_urban' => count($urban),
            'total_rural' => count($rural)
        ]
    ];
}

/**
 * Helper: Calculate age
 */
function calculateAge($birthDate) {
    $birth = new \DateTime($birthDate);
    $today = new \DateTime();
    return $today->diff($birth)->y;
}

/**
 * Helper: Get risk level from score
 */
function getRiskLevel($score) {
    if ($score >= 75) {
        return 'Sangat Tinggi';
    } elseif ($score >= 50) {
        return 'Tinggi';
    } elseif ($score >= 25) {
        return 'Sedang';
    } elseif ($score >= 0) {
        return 'Rendah';
    } else {
        return 'Sangat Rendah';
    }
}
