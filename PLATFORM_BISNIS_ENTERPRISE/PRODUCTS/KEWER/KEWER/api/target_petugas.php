<?php
/**
 * API: Target Petugas
 * GET  /api/target_petugas.php?bulan=YYYY-MM&petugas_id=
 * POST /api/target_petugas.php   { petugas_id, bulan, target_kutipan, target_nasabah_baru, target_pinjaman_baru, target_collection_rate }
 * PUT  /api/target_petugas.php   { id, ... }
 */
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');

require_once __DIR__ . '/../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
require_once BASE_PATH . '/includes/feature_flags.php';

if (!isFeatureEnabled('target_petugas')) {
    http_response_code(403);
    echo json_encode(['error' => 'Fitur Target Petugas belum diaktifkan oleh appOwner.']);
    exit();
}
require_once BASE_PATH . '/includes/business_logic.php';

requireLogin();
$user   = getCurrentUser();
$method = $_SERVER['REQUEST_METHOD'];

// GET — ambil data realisasi vs target
if ($method === 'GET') {
    $bulan      = $_GET['bulan'] ?? date('Y-m');
    $petugas_id = (int)($_GET['petugas_id'] ?? 0);

    if ($petugas_id) {
        echo json_encode(getRealisasiVsTarget($petugas_id, $bulan));
    } else {
        // Semua petugas
        $petugas_list = query("SELECT id FROM users WHERE role IN ('petugas_pusat','petugas_cabang') AND status='aktif'") ?: [];
        $result = [];
        foreach ($petugas_list as $p) {
            $result[] = getRealisasiVsTarget((int)$p['id'], $bulan);
        }
        echo json_encode($result);
    }
    exit();
}

// POST / PUT — set atau update target
if (in_array($method, ['POST', 'PUT'])) {
    if (!in_array($user['role'], ['bos', 'manager_pusat', 'manager_cabang', 'admin_pusat', 'appOwner'])) {
        http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit();
    }

    $input = json_decode(file_get_contents('php://input'), true) ?? [];

    $petugas_id = (int)($input['petugas_id'] ?? 0);
    $bulan      = $input['bulan'] ?? date('Y-m');
    if (!$petugas_id || !preg_match('/^\d{4}-\d{2}$/', $bulan)) {
        http_response_code(400); echo json_encode(['error' => 'petugas_id dan bulan (YYYY-MM) wajib']); exit();
    }

    $existing = query("SELECT id FROM target_petugas WHERE petugas_id = ? AND bulan = ?", [$petugas_id, $bulan]);

    $fields = [
        'target_kutipan'         => floatval($input['target_kutipan'] ?? 0),
        'target_nasabah_baru'    => (int)($input['target_nasabah_baru'] ?? 0),
        'target_pinjaman_baru'   => (int)($input['target_pinjaman_baru'] ?? 0),
        'target_collection_rate' => floatval($input['target_collection_rate'] ?? 90),
        'catatan'                => $input['catatan'] ?? null,
    ];

    if ($existing) {
        $r = query("UPDATE target_petugas SET target_kutipan=?, target_nasabah_baru=?, target_pinjaman_baru=?, target_collection_rate=?, catatan=?, dibuat_oleh=?, updated_at=NOW() WHERE petugas_id=? AND bulan=?",
            array_merge(array_values($fields), [$user['id'], $petugas_id, $bulan]));
    } else {
        $r = query("INSERT INTO target_petugas (cabang_id, petugas_id, bulan, target_kutipan, target_nasabah_baru, target_pinjaman_baru, target_collection_rate, catatan, dibuat_oleh)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [$user['cabang_id'] ?? 1, $petugas_id, $bulan,
             $fields['target_kutipan'], $fields['target_nasabah_baru'], $fields['target_pinjaman_baru'],
             $fields['target_collection_rate'], $fields['catatan'], $user['id']]);
    }

    echo json_encode(['success' => (bool)$r, 'data' => getRealisasiVsTarget($petugas_id, $bulan)]);
    exit();
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
