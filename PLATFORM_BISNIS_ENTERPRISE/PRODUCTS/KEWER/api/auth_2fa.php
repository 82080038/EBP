<?php
/**
 * API: Two-Factor Authentication (TOTP)
 * Menggunakan TOTP (RFC 6238) compatible dengan Google Authenticator / Authy
 *
 * GET  /api/auth_2fa.php?action=setup          — generate secret + QR URL
 * POST /api/auth_2fa.php { action: verify_setup, code }  — verifikasi & aktifkan 2FA
 * POST /api/auth_2fa.php { action: verify, code }        — verifikasi saat login
 * POST /api/auth_2fa.php { action: disable, code }       — nonaktifkan 2FA
 */
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');

require_once __DIR__ . '/../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
require_once BASE_PATH . '/includes/feature_flags.php';

if (!isFeatureEnabled('two_factor_auth')) {
    http_response_code(403);
    echo json_encode(['error' => 'Fitur 2FA belum diaktifkan oleh appOwner.']);
    exit();
}

requireLogin();
$user   = getCurrentUser();
$method = $_SERVER['REQUEST_METHOD'];

// ── Hanya untuk role sensitif ──────────────────────────────────────
$ROLES_2FA = ['bos', 'manager_pusat', 'manager_cabang', 'admin_pusat', 'appOwner'];

// ── TOTP helper functions ─────────────────────────────────────────

function totp_secret(): string {
    $chars  = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $secret = '';
    for ($i = 0; $i < 32; $i++) {
        $secret .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $secret;
}

function totp_base32_decode(string $secret): string {
    $base32chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $secret      = strtoupper($secret);
    $buf         = '';
    $bits        = 0;
    $value       = 0;

    foreach (str_split($secret) as $char) {
        $pos = strpos($base32chars, $char);
        if ($pos === false) continue;
        $value = ($value << 5) | $pos;
        $bits += 5;
        if ($bits >= 8) {
            $bits -= 8;
            $buf  .= chr(($value >> $bits) & 0xFF);
        }
    }
    return $buf;
}

function totp_get_code(string $secret, int $counter = 0): string {
    $key     = totp_base32_decode($secret);
    $time    = $counter ?: (int)floor(time() / 30);
    $msgPack = pack('J', $time);
    $hash    = hash_hmac('sha1', $msgPack, $key, true);
    $offset  = ord($hash[19]) & 0x0F;
    $code    = ((ord($hash[$offset]) & 0x7F) << 24)
             | ((ord($hash[$offset + 1]) & 0xFF) << 16)
             | ((ord($hash[$offset + 2]) & 0xFF) << 8)
             | (ord($hash[$offset + 3]) & 0xFF);
    return str_pad($code % 1000000, 6, '0', STR_PAD_LEFT);
}

function totp_verify(string $secret, string $code, int $window = 1): bool {
    $t = (int)floor(time() / 30);
    for ($i = -$window; $i <= $window; $i++) {
        if (totp_get_code($secret, $t + $i) === $code) return true;
    }
    return false;
}

function totp_qr_url(string $secret, string $label, string $issuer = 'Kewer'): string {
    $otpauth = rawurlencode("otpauth://totp/{$issuer}:{$label}?secret={$secret}&issuer={$issuer}&algorithm=SHA1&digits=6&period=30");
    return "https://api.qrserver.com/v1/create-qr-code/?data={$otpauth}&size=200x200";
}

// ── GET: setup ─────────────────────────────────────────────────────
if ($method === 'GET') {
    $action = $_GET['action'] ?? 'setup';

    if ($action === 'setup') {
        // Buat atau ambil secret existing (jika belum diaktifkan)
        $existing = query("SELECT totp_secret, totp_enabled FROM users WHERE id = ?", [$user['id']]);
        $secret = ($existing && $existing[0]['totp_enabled'] == 0 && $existing[0]['totp_secret'])
                    ? $existing[0]['totp_secret']
                    : totp_secret();

        // Simpan secret (belum aktif)
        query("UPDATE users SET totp_secret = ?, totp_enabled = 0 WHERE id = ?", [$secret, $user['id']]);

        $label  = rawurlencode($user['username'] ?? $user['nama'] ?? 'user');
        echo json_encode([
            'secret'  => $secret,
            'qr_url'  => totp_qr_url($secret, $user['username'] ?? 'user'),
            'enabled' => (bool)($existing[0]['totp_enabled'] ?? false),
            'instruction' => 'Scan QR ini dengan Google Authenticator / Authy, lalu masukkan kode 6 digit untuk mengaktifkan 2FA.',
        ]);
    } elseif ($action === 'status') {
        $row = query("SELECT totp_enabled FROM users WHERE id = ?", [$user['id']]);
        echo json_encode(['enabled' => (bool)($row[0]['totp_enabled'] ?? false)]);
    }
    exit();
}

// ── POST ──────────────────────────────────────────────────────────
if ($method === 'POST') {
    $input  = json_decode(file_get_contents('php://input'), true) ?? [];
    $action = $input['action'] ?? '';
    $code   = preg_replace('/[^0-9]/', '', $input['code'] ?? '');

    switch ($action) {

        case 'verify_setup':
            // Verifikasi kode dan aktifkan 2FA
            if (!in_array($user['role'], $ROLES_2FA)) {
                http_response_code(403); echo json_encode(['error' => '2FA hanya untuk role manajer ke atas']); exit();
            }
            $row = query("SELECT totp_secret FROM users WHERE id = ?", [$user['id']]);
            if (!$row || !$row[0]['totp_secret']) {
                http_response_code(400); echo json_encode(['error' => 'Belum ada secret. Panggil GET ?action=setup terlebih dahulu.']); exit();
            }
            if (!totp_verify($row[0]['totp_secret'], $code)) {
                http_response_code(400); echo json_encode(['error' => 'Kode tidak valid atau sudah kedaluwarsa']); exit();
            }
            query("UPDATE users SET totp_enabled = 1, totp_verified_at = NOW() WHERE id = ?", [$user['id']]);
            echo json_encode(['success' => true, 'message' => '2FA berhasil diaktifkan']);
            break;

        case 'verify':
            // Verifikasi saat login (session ada tapi 2FA_PENDING)
            if (!isset($_SESSION['2fa_pending_user_id'])) {
                echo json_encode(['success' => false, 'error' => 'Tidak ada sesi 2FA yang tertunda']);
                exit();
            }
            $uid = (int)$_SESSION['2fa_pending_user_id'];
            $row = query("SELECT totp_secret FROM users WHERE id = ?", [$uid]);
            if (!$row || !totp_verify($row[0]['totp_secret'], $code)) {
                echo json_encode(['success' => false, 'error' => 'Kode 2FA tidak valid']);
                exit();
            }
            // Login sukses — set session normal
            $_SESSION['user_id']        = $uid;
            $_SESSION['2fa_verified']   = true;
            unset($_SESSION['2fa_pending_user_id']);
            echo json_encode(['success' => true, 'redirect' => '/kewer/dashboard.php']);
            break;

        case 'disable':
            if (!in_array($user['role'], $ROLES_2FA)) {
                http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit();
            }
            $row = query("SELECT totp_secret FROM users WHERE id = ?", [$user['id']]);
            if (!$row || !totp_verify($row[0]['totp_secret'], $code)) {
                http_response_code(400); echo json_encode(['error' => 'Kode tidak valid']); exit();
            }
            query("UPDATE users SET totp_enabled = 0, totp_secret = NULL, totp_verified_at = NULL WHERE id = ?", [$user['id']]);
            echo json_encode(['success' => true, 'message' => '2FA berhasil dinonaktifkan']);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'action tidak dikenal: verify_setup|verify|disable']);
    }
    exit();
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
