<?php
/**
 * API: Feature Flags — hanya appOwner
 * GET  /api/feature_flags.php              → list semua fitur
 * POST /api/feature_flags.php { key, enabled: bool }  → toggle satu fitur
 * POST /api/feature_flags.php { bulk: [{key, enabled},...] } → toggle banyak sekaligus
 */
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');

require_once __DIR__ . '/../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
require_once BASE_PATH . '/includes/feature_flags.php';

requireLogin();
$user = getCurrentUser();

if ($user['role'] !== 'appOwner') {
    http_response_code(403);
    echo json_encode(['error' => 'Hanya appOwner yang dapat mengelola feature flags']);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    echo json_encode(getAllFeatures() ?: []);
    exit();
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];

    // Bulk toggle
    if (isset($input['bulk']) && is_array($input['bulk'])) {
        $updated = 0;
        foreach ($input['bulk'] as $item) {
            $key     = preg_replace('/[^a-z0-9_]/', '', $item['key'] ?? '');
            $enabled = $item['enabled'] ? 1 : 0;
            if (!$key) continue;
            $r = query(
                "UPDATE platform_features SET is_enabled = ?, changed_by = ?, changed_at = NOW() WHERE feature_key = ?",
                [$enabled, $user['id'], $key]
            );
            if ($r !== false) $updated++;
        }
        echo json_encode(['success' => true, 'updated' => $updated]);
        exit();
    }

    // Single toggle
    $key     = preg_replace('/[^a-z0-9_]/', '', $input['key'] ?? '');
    $enabled = isset($input['enabled']) ? ($input['enabled'] ? 1 : 0) : null;

    if (!$key || $enabled === null) {
        http_response_code(400);
        echo json_encode(['error' => 'Wajib: key (string) dan enabled (bool)']);
        exit();
    }

    $exists = query("SELECT id FROM platform_features WHERE feature_key = ?", [$key]);
    if (!$exists) {
        http_response_code(404);
        echo json_encode(['error' => "Feature '{$key}' tidak ditemukan"]);
        exit();
    }

    query(
        "UPDATE platform_features SET is_enabled = ?, changed_by = ?, changed_at = NOW() WHERE feature_key = ?",
        [$enabled, $user['id'], $key]
    );

    echo json_encode(['success' => true, 'key' => $key, 'enabled' => (bool)$enabled]);
    exit();
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
