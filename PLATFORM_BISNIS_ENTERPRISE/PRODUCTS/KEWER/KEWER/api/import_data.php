<?php
/**
 * API: Import Data (CSV)
 * POST /api/import_data.php?entity=nasabah|pinjaman
 */
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
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
$user = getCurrentUser();

if (!hasPermission('manage_nasabah') && !hasPermission('manage_pinjaman') && !in_array($user['role'], ['bos', 'manager_pusat', 'manager_cabang', 'admin_pusat', 'admin_cabang'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden - No permission to import data']);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

$entity = $_GET['entity'] ?? '';

// Check if file was uploaded
if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'CSV file is required']);
    exit();
}

$file = $_FILES['csv_file'];
$filepath = $file['tmp_name'];

// Validate CSV
$handle = fopen($filepath, 'r');
if ($handle === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to open CSV file']);
    exit();
}

// Read CSV with BOM handling
$bom = fread($handle, 3);
if ($bom !== "\xEF\xBB\xBF") {
    rewind($handle);
}

$headers = fgetcsv($handle);
if ($headers === false) {
    fclose($handle);
    http_response_code(400);
    echo json_encode(['error' => 'CSV file is empty or invalid']);
    exit();
}

$rows = [];
$errors = [];
$success_count = 0;
$row_number = 1; // Header is row 0, data starts at row 1

while (($row = fgetcsv($handle)) !== false) {
    $row_number++;
    if (count($row) !== count($headers)) {
        $errors[] = "Row $row_number: Column count mismatch (expected " . count($headers) . ", got " . count($row) . ")";
        continue;
    }
    $rows[] = array_combine($headers, $row);
}

fclose($handle);

if (empty($rows)) {
    http_response_code(400);
    echo json_encode(['error' => 'No data found in CSV file']);
    exit();
}

// Process based on entity
switch ($entity) {
    case 'nasabah':
        $result = importNasabah($rows, $user);
        break;
        
    case 'pinjaman':
        $result = importPinjaman($rows, $user);
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Entity tidak dikenal. Gunakan: nasabah, pinjaman']);
        exit();
}

echo json_encode($result);

/**
 * Import nasabah dari CSV
 */
function importNasabah(array $rows, array $user): array {
    $success = 0;
    $failed = 0;
    $errors = [];
    
    foreach ($rows as $index => $row) {
        $row_num = $index + 2; // +2 because header is row 1, data starts at row 2
        
        // Validate required fields
        if (empty($row['nama'])) {
            $errors[] = "Row $row_num: Field 'nama' is required";
            $failed++;
            continue;
        }
        
        if (empty($row['kode_nasabah'])) {
            $errors[] = "Row $row_num: Field 'kode_nasabah' is required";
            $failed++;
            continue;
        }
        
        // Check if kode_nasabah already exists
        $existing = query("SELECT id FROM nasabah WHERE kode_nasabah = ?", [$row['kode_nasabah']]);
        if ($existing) {
            $errors[] = "Row $row_num: kode_nasabah '{$row['kode_nasabah']}' already exists";
            $failed++;
            continue;
        }
        
        // Insert nasabah
        $result = query("
            INSERT INTO nasabah (
                kode_nasabah, nama, telepon, alamat, status,
                cabang_id, created_at, updated_at
            ) VALUES (?, ?, ?, ?, 'aktif', 1, NOW(), NOW())
        ", [
            $row['kode_nasabah'],
            $row['nama'],
            $row['telepon'] ?? null,
            $row['alamat'] ?? null
        ]);
        
        if ($result) {
            $success++;
            
            // Log audit
            $nasabah_id = query("SELECT LAST_INSERT_ID() as id")[0]['id'];
            query("
                INSERT INTO audit_log (table_name, record_id, action, old_value, new_value, created_by, created_at)
                VALUES ('nasabah', ?, 'import', NULL, ?, ?, NOW())
            ", [$nasabah_id, json_encode($row), $user['id']]);
        } else {
            $errors[] = "Row $row_num: Failed to insert nasabah";
            $failed++;
        }
    }
    
    return [
        'success' => true,
        'total' => count($rows),
        'success_count' => $success,
        'failed_count' => $failed,
        'errors' => $errors
    ];
}

/**
 * Import pinjaman dari CSV
 */
function importPinjaman(array $rows, array $user): array {
    $success = 0;
    $failed = 0;
    $errors = [];
    
    foreach ($rows as $index => $row) {
        $row_num = $index + 2;
        
        // Validate required fields
        if (empty($row['kode_nasabah'])) {
            $errors[] = "Row $row_num: Field 'kode_nasabah' is required";
            $failed++;
            continue;
        }
        
        if (empty($row['plafon'])) {
            $errors[] = "Row $row_num: Field 'plafon' is required";
            $failed++;
            continue;
        }
        
        if (empty($row['tenor'])) {
            $errors[] = "Row $row_num: Field 'tenor' is required";
            $failed++;
            continue;
        }
        
        // Get nasabah ID
        $nasabah = query("SELECT id FROM nasabah WHERE kode_nasabah = ?", [$row['kode_nasabah']]);
        if (!$nasabah) {
            $errors[] = "Row $row_num: Nasabah dengan kode_nasabah '{$row['kode_nasabah']}' tidak ditemukan";
            $failed++;
            continue;
        }
        
        $nasabah_id = $nasabah[0]['id'];
        
        // Generate kode pinjaman
        $kode_pinjaman = generateKode('PIN', 'pinjaman', 'kode_pinjaman');
        
        // Insert pinjaman
        $result = query("
            INSERT INTO pinjaman (
                kode_pinjaman, nasabah_id, plafon, tenor, bunga_per_bulan,
                tanggal_akad, tanggal_jatuh_tempo, status, cabang_id,
                sisa_pokok_berjalan, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, 'aktif', 1, ?, NOW(), NOW())
        ", [
            $kode_pinjaman,
            $nasabah_id,
            floatval($row['plafon']),
            intval($row['tenor']),
            floatval($row['bunga_per_bulan'] ?? 0),
            $row['tanggal_akad'] ?? date('Y-m-d'),
            $row['tanggal_jatuh_tempo'] ?? date('Y-m-d', strtotime('+' . intval($row['tenor']) . ' months')),
            floatval($row['plafon'])
        ]);
        
        if ($result) {
            $success++;
            
            // Log audit
            $pinjaman_id = query("SELECT LAST_INSERT_ID() as id")[0]['id'];
            query("
                INSERT INTO audit_log (table_name, record_id, action, old_value, new_value, created_by, created_at)
                VALUES ('pinjaman', ?, 'import', NULL, ?, ?, NOW())
            ", [$pinjaman_id, json_encode($row), $user['id']]);
        } else {
            $errors[] = "Row $row_num: Failed to insert pinjaman";
            $failed++;
        }
    }
    
    return [
        'success' => true,
        'total' => count($rows),
        'success_count' => $success,
        'failed_count' => $failed,
        'errors' => $errors
    ];
}
