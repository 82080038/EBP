<?php
/**
 * API: Alamat (Address)
 * 
 * Endpoints for hierarchical address data from db_alamat_simple
 */
// Suppress errors to ensure JSON output
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

try {
    require_once __DIR__ . '/../config/path.php';
    require_once BASE_PATH . '/includes/functions.php';
    require_once BASE_PATH . '/includes/alamat_helper.php';
    require_once BASE_PATH . '/config/database.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Configuration error: ' . $e->getMessage()]);
    exit();
}
// Note: Address data should be publicly accessible for registration forms

// Apply rate limiting for API requests
try {
    if (!checkRateLimit(RATE_LIMIT_PER_MINUTE, 60)) {
        apiError('Too many requests. Please try again later.', 429);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Rate limit check failed: ' . $e->getMessage()]);
    exit();
}

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'provinces':
            // Get all provinces
            $provinces = getProvinces();
            apiSuccess($provinces);
            break;
            
        case 'regencies':
            // Get regencies by province ID
            $provinceId = $_GET['province_id'] ?? null;
            if (!$provinceId) {
                apiError('Province ID is required');
            }
            $regencies = getRegenciesByProvince($provinceId);
            apiSuccess($regencies);
            break;
            
        case 'districts':
            // Get districts by regency ID
            $regencyId = $_GET['regency_id'] ?? null;
            if (!$regencyId) {
                apiError('Regency ID is required');
            }
            $districts = getDistrictsByRegency($regencyId);
            apiSuccess($districts);
            break;
            
        case 'villages':
            // Get villages by district ID
            $districtId = $_GET['district_id'] ?? null;
            if (!$districtId) {
                apiError('District ID is required');
            }
            $villages = getVillagesByDistrict($districtId);
            apiSuccess($villages);
            break;
            
        default:
            apiError('Invalid action');
            break;
    }
} catch (Exception $e) {
    apiError($e->getMessage());
}
