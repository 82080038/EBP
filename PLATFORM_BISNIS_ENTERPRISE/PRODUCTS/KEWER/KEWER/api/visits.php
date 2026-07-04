<?php
/**
 * Visits API
 * Handles visit logging with GPS tracking
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

$action = $_GET['action'] ?? 'create';

try {
    switch ($action) {
        case 'create':
            echo json_encode(createVisit());
            break;
        case 'list':
            echo json_encode(getVisits());
            break;
        case 'stats':
            echo json_encode(getVisitStats());
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

/**
 * Create new visit
 */
function createVisit() {
    $petugas_id = $_SESSION['user_id'];
    $cabang_id = $_SESSION['cabang_id'];
    $nasabah_id = $_POST['nasabah_id'] ?? null;
    $visit_type = $_POST['visit_type'] ?? 'pembayaran';
    $latitude = $_POST['latitude'] ?? null;
    $longitude = $_POST['longitude'] ?? null;
    $notes = $_POST['notes'] ?? null;
    
    // Validate required fields
    if (!$nasabah_id || !$latitude || !$longitude) {
        return ['success' => false, 'message' => 'nasabah_id, latitude, and longitude are required'];
    }
    
    // Validate GPS coordinates
    if (!\Kewer\Geo\GPSTracker::validateCoordinates($latitude, $longitude)) {
        return ['success' => false, 'message' => 'Invalid GPS coordinates'];
    }
    
    // Check geofence
    $geofenceCheck = \Kewer\Geo\GPSTracker::checkGeofence($latitude, $longitude, $cabang_id);
    $geofence_valid = $geofenceCheck['valid'] ? 1 : 0;
    $distance_from_cabang = $geofenceCheck['distance'] ?? null;
    
    // Insert visit
    $sql = "INSERT INTO visits (petugas_id, nasabah_id, cabang_id, visit_type, latitude, longitude, geofence_valid, distance_from_cabang, notes, visit_date) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    query($sql, [$petugas_id, $nasabah_id, $cabang_id, $visit_type, $latitude, $longitude, $geofence_valid, $distance_from_cabang, $notes]);
    
    return ['success' => true, 'message' => 'Visit logged successfully'];
}

/**
 * Get visits list
 */
function getVisits() {
    $petugas_id = $_GET['petugas_id'] ?? $_SESSION['user_id'];
    $cabang_id = $_GET['cabang_id'] ?? $_SESSION['cabang_id'];
    $visit_type = $_GET['visit_type'] ?? '';
    $start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
    $end_date = $_GET['end_date'] ?? date('Y-m-d');
    
    $where = ["v.cabang_id = ?"];
    $params = [$cabang_id];
    
    if ($petugas_id) {
        $where[] = "v.petugas_id = ?";
        $params[] = $petugas_id;
    }
    
    if ($visit_type) {
        $where[] = "v.visit_type = ?";
        $params[] = $visit_type;
    }
    
    if ($start_date) {
        $where[] = "DATE(v.visit_date) >= ?";
        $params[] = $start_date;
    }
    
    if ($end_date) {
        $where[] = "DATE(v.visit_date) <= ?";
        $params[] = $end_date;
    }
    
    $whereClause = implode(' AND ', $where);
    
    $sql = "SELECT v.*, n.nama as nama_nasabah, n.ktp, u.nama as nama_petugas, c.nama_cabang
            FROM visits v
            LEFT JOIN nasabah n ON v.nasabah_id = n.id
            LEFT JOIN users u ON v.petugas_id = u.id
            LEFT JOIN cabang c ON v.cabang_id = c.id
            WHERE $whereClause
            ORDER BY v.visit_date DESC
            LIMIT 100";
    
    $result = query($sql, $params);
    
    return [
        'success' => true,
        'data' => is_array($result) ? $result : []
    ];
}

/**
 * Get visit statistics
 */
function getVisitStats() {
    $cabang_id = $_GET['cabang_id'] ?? $_SESSION['cabang_id'];
    $petugas_id = $_GET['petugas_id'] ?? null;
    $start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
    $end_date = $_GET['end_date'] ?? date('Y-m-d');
    
    $where = ["v.cabang_id = ?", "DATE(v.visit_date) BETWEEN ? AND ?"];
    $params = [$cabang_id, $start_date, $end_date];
    
    if ($petugas_id) {
        $where[] = "v.petugas_id = ?";
        $params[] = $petugas_id;
    }
    
    $whereClause = implode(' AND ', $where);
    
    $sql = "SELECT COUNT(*) as total_visits,
            SUM(CASE WHEN v.geofence_valid = 1 THEN 1 ELSE 0 END) as valid_geofence,
            AVG(v.distance_from_cabang) as avg_distance,
            COUNT(DISTINCT v.nasabah_id) as unique_nasabah
            FROM visits v
            WHERE $whereClause";
    
    $result = query($sql, $params);
    
    return [
        'success' => true,
        'data' => is_array($result) && isset($result[0]) ? $result[0] : []
    ];
}
