<?php
/**
 * Feature Flags Helper
 * Semua fitur v2.3.0 dikontrol via tabel platform_features.
 * Hanya appOwner yang bisa toggle via /pages/app_owner/features.php
 *
 * Usage:
 *   isFeatureEnabled('wa_notifikasi')   → bool
 *   requireFeature('two_factor_auth')   → exit 403 jika off
 *   getAllFeatures()                     → array semua fitur
 */

if (!function_exists('isFeatureEnabled')) {

    // Cache in-request agar tidak query DB berkali-kali
    $_FEATURE_CACHE = null;

    function _loadFeatures(): array {
        global $_FEATURE_CACHE;
        if ($_FEATURE_CACHE !== null) return $_FEATURE_CACHE;

        if (!function_exists('query')) return $_FEATURE_CACHE = [];

        try {
            $rows = query("SELECT feature_key, is_enabled FROM platform_features");
            if (!is_array($rows)) return $_FEATURE_CACHE = [];
            $_FEATURE_CACHE = array_column($rows, 'is_enabled', 'feature_key');
            return $_FEATURE_CACHE;
        } catch (\Throwable $e) {
            return $_FEATURE_CACHE = [];
        }
    }

    function isFeatureEnabled(string $key): bool {
        $features = _loadFeatures();
        // Jika tabel belum ada (migrasi belum dijalankan) → anggap OFF
        return (bool)($features[$key] ?? false);
    }

    function requireFeature(string $key, string $redirect = ''): void {
        if (!isFeatureEnabled($key)) {
            if (headers_sent() || !empty($redirect)) {
                header('Location: ' . ($redirect ?: (defined('BASE_URL') ? BASE_URL . '/dashboard.php' : '/kewer/dashboard.php')));
                exit();
            }
            http_response_code(403);
            echo json_encode(['error' => "Fitur '{$key}' belum diaktifkan. Hubungi appOwner."]);
            exit();
        }
    }

    function getAllFeatures(): array {
        if (!function_exists('query')) return [];
        try {
            return query("SELECT * FROM platform_features ORDER BY category, label") ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }
}
