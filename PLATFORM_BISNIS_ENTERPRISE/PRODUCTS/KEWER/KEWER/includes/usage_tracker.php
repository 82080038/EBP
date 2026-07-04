<?php
/**
 * Usage Tracker — logs API calls and page renders per koperasi (bos)
 * Include this in config/session.php or similar early-load file.
 */
function trackUsage() {
    if (!function_exists('isLoggedIn') || !isLoggedIn()) return;
    if (!function_exists('getCurrentUser')) return;
    
    $user = getCurrentUser();
    if (!$user || $user['role'] === 'appOwner') return;
    
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    
    // Determine type
    $tipe = (strpos($uri, '/api/') !== false) ? 'api_call' : 'page_render';
    
    // Skip static assets
    if (preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|ttf|map)$/i', $uri)) return;
    
    // Determine bos_user_id
    $bos_user_id = null;
    if ($user['role'] === 'bos') {
        $bos_user_id = $user['id'];
    } elseif (!empty($user['owner_bos_id'])) {
        $bos_user_id = $user['owner_bos_id'];
    }
    
    if (!$bos_user_id) return;
    
    $today = date('Y-m-d');
    $endpoint = parse_url($uri, PHP_URL_PATH) ?? $uri;
    
    // Insert log (async-friendly: minimal query)
    global $conn;
    if (!$conn) return;
    
    $stmt = $conn->prepare("INSERT INTO usage_log (bos_user_id, user_id, tipe, endpoint, method, tanggal) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param('iissss', $bos_user_id, $user['id'], $tipe, $endpoint, $method, $today);
        $stmt->execute();
        $stmt->close();
    }
    
    // Update daily summary (upsert)
    if ($tipe === 'api_call') {
        $stmt = $conn->prepare("INSERT INTO usage_daily_summary (bos_user_id, tanggal, total_api_calls, total_renders) VALUES (?, ?, 1, 0) ON DUPLICATE KEY UPDATE total_api_calls = total_api_calls + 1");
        $stmt->bind_param("is", $bos_user_id, $today);
        $stmt->execute();
        $stmt->close();
    } else {
        $stmt = $conn->prepare("INSERT INTO usage_daily_summary (bos_user_id, tanggal, total_api_calls, total_renders) VALUES (?, ?, 0, 1) ON DUPLICATE KEY UPDATE total_renders = total_renders + 1");
        $stmt->bind_param("is", $bos_user_id, $today);
        $stmt->execute();
        $stmt->close();
    }
}

// Auto-track on include
trackUsage();
