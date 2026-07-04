<?php
require_once __DIR__ . '/../config/path.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/includes/functions.php';

header('Content-Type: application/json');

// Get parameters
$ktp = $_GET['ktp'] ?? '';
$telp = $_GET['telp'] ?? '';

if (empty($ktp) && empty($telp)) {
    echo json_encode(['success' => false, 'message' => 'KTP atau telepon wajib diisi']);
    exit;
}

// Search in db_orang.people
$where = [];
$params = [];

if (!empty($ktp)) {
    $where[] = "ktp = ?";
    $params[] = $ktp;
}

if (!empty($telp)) {
    $where[] = "telp = ?";
    $params[] = $telp;
}

$where_clause = implode(" OR ", $where);

$person = query_orang("SELECT p.*, a.street_address, a.province_id, a.regency_id, a.district_id, a.village_id FROM people p LEFT JOIN addresses a ON a.person_id = p.id WHERE $where_clause", $params);

if (is_array($person) && !empty($person)) {
    $found_person = $person[0];
    echo json_encode([
        'success' => true,
        'found' => true,
        'data' => [
            'nama' => $found_person['nama'],
            'ktp' => $found_person['ktp'],
            'telp' => $found_person['telp'],
            'email' => $found_person['email'] ?? '',
            'street_address' => $found_person['street_address'] ?? '',
            'province_id' => $found_person['province_id'] ?? '',
            'regency_id' => $found_person['regency_id'] ?? '',
            'district_id' => $found_person['district_id'] ?? '',
            'village_id' => $found_person['village_id'] ?? ''
        ]
    ]);
} else {
    echo json_encode([
        'success' => true,
        'found' => false
    ]);
}
