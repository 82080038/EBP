<?php
/**
 * API: Resign / Pindah Cabang Karyawan
 *
 * POST /api/transfer_karyawan.php
 * Body (JSON):
 *   action           : 'resign' | 'pindah_cabang'
 *   user_id          : int    — ID karyawan yang akan diproses
 *   alasan           : string — Alasan resign / pindah
 *   -- hanya untuk action='pindah_cabang' --
 *   target_cabang_id : int   — ID cabang tujuan (dalam koperasi yang sama)
 *
 * Permission required: manage_users (bos atau manager_pusat)
 *
 * Rules:
 *   - Resign     : user di-nonaktifkan. Identitas (NIK) tetap ada di db_orang.
 *                  Jika mendaftar di koperasi lain, bos lain input NIK → data auto-fill.
 *                  User nonaktif tidak bisa login dan tidak bisa akses data lama.
 *   - Pindah Cabang : hanya dalam koperasi yang sama (owner_bos_id tidak berubah).
 *                  Transfer ke koperasi lain = bos baru daftarkan NIK yang sama.
 *   - Semua aksi dicatat di audit_log.
 */

// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);
// error_reporting(0);
// ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

try {
    require_once __DIR__ . '/../config/path.php';
    require_once BASE_PATH . '/includes/functions.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Configuration error: ' . $e->getMessage()]);
    exit();
}

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Permission: hanya bos dan manager_pusat
if (!hasPermission('manage_users')) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden - Hanya bos/manager_pusat yang dapat memproses resign/transfer']);
    exit();
}

$actor = getCurrentUser();
$input = json_decode(file_get_contents('php://input'), true);

$action          = trim($input['action'] ?? '');
$user_id         = (int)($input['user_id'] ?? 0);
$alasan          = trim($input['alasan'] ?? '');
$target_bos_id   = (int)($input['target_bos_id'] ?? 0);
$target_cabang_id = (int)($input['target_cabang_id'] ?? 0);

// ── Validasi input dasar ─────────────────────────────────────────
if (!in_array($action, ['resign', 'pindah_cabang'])) {
    http_response_code(400);
    echo json_encode(['error' => 'action harus "resign" atau "pindah_cabang"']);
    exit();
}

if ($user_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'user_id tidak valid']);
    exit();
}

if (empty($alasan)) {
    http_response_code(400);
    echo json_encode(['error' => 'alasan wajib diisi']);
    exit();
}

// ── Ambil data karyawan ──────────────────────────────────────────
$target_user = query("SELECT id, username, nama, role, cabang_id, owner_bos_id, status FROM users WHERE id = ?", [$user_id]);
if (!is_array($target_user) || empty($target_user)) {
    http_response_code(404);
    echo json_encode(['error' => 'Karyawan tidak ditemukan']);
    exit();
}
$target_user = $target_user[0];

// Tidak bisa proses bos atau appOwner
if (in_array($target_user['role'], ['bos', 'appOwner'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Tidak dapat memproses bos atau appOwner']);
    exit();
}

// Pastikan karyawan ini milik bos yang login
$actor_bos_id = getOwnerBosId();
if (!$actor_bos_id || (int)$target_user['owner_bos_id'] !== $actor_bos_id) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden - Karyawan ini bukan bagian dari koperasi Anda']);
    exit();
}

// Karyawan sudah nonaktif
if ($target_user['status'] === 'nonaktif') {
    http_response_code(400);
    echo json_encode(['error' => 'Karyawan sudah nonaktif']);
    exit();
}

$old_data = [
    'status'        => $target_user['status'],
    'owner_bos_id'  => $target_user['owner_bos_id'],
    'cabang_id'     => $target_user['cabang_id'],
];

// ── Proses RESIGN ────────────────────────────────────────────────
if ($action === 'resign') {
    $result = query(
        "UPDATE users SET status = 'nonaktif', updated_at = NOW() WHERE id = ?",
        [$user_id]
    );

    if (!$result) {
        http_response_code(500);
        echo json_encode(['error' => 'Gagal memproses resign']);
        exit();
    }

    // Hapus delegated permissions aktif milik karyawan ini
    query("UPDATE delegated_permissions SET is_active = 0 WHERE delegatee_id = ?", [$user_id]);

    // Sinkronisasi status di db_orang.people (identitas tetap ada, hanya status nonaktif)
    try {
        require_once BASE_PATH . '/includes/people_helper.php';
        syncPersonStatusOnResign($user_id);
    } catch (Exception $e) {
        error_log("syncPersonStatusOnResign error: " . $e->getMessage());
    }

    // Audit log
    logAudit(
        'resign_karyawan',
        'users',
        $user_id,
        $old_data,
        ['status' => 'nonaktif', 'alasan' => $alasan]
    );

    echo json_encode([
        'success' => true,
        'message' => "Karyawan {$target_user['nama']} ({$target_user['username']}) berhasil di-nonaktifkan. Data identitas (NIK) tetap tersimpan di platform.",
        'data'    => [
            'user_id'  => $user_id,
            'username' => $target_user['username'],
            'nama'     => $target_user['nama'],
            'action'   => 'resign',
            'alasan'   => $alasan,
            'catatan'  => 'Jika karyawan ini mendaftar di koperasi lain, bos baru cukup input NIK yang sama untuk auto-fill identitas.',
        ],
    ]);
    exit();
}

// ── Proses PINDAH CABANG (dalam koperasi yang sama) ──────────────
if ($action === 'pindah_cabang') {
    $target_cabang_id = (int)($input['target_cabang_id'] ?? 0);

    if ($target_cabang_id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'target_cabang_id wajib diisi untuk pindah cabang']);
        exit();
    }

    // Cabang tujuan harus milik koperasi yang sama (bos yang login)
    if (!validateCabangOwnership($target_cabang_id)) {
        http_response_code(403);
        echo json_encode(['error' => 'Cabang tujuan bukan milik koperasi Anda. Untuk pindah ke koperasi lain, karyawan harus resign dulu, lalu bos baru mendaftarkan NIK-nya.']);
        exit();
    }

    // Tidak boleh pindah ke cabang yang sama
    if ((int)$target_user['cabang_id'] === $target_cabang_id) {
        http_response_code(400);
        echo json_encode(['error' => 'Karyawan sudah berada di cabang ini']);
        exit();
    }

    $target_cabang = query("SELECT id, nama_cabang FROM cabang WHERE id = ?", [$target_cabang_id]);
    $nama_cabang   = $target_cabang[0]['nama_cabang'] ?? '-';

    $result = query(
        "UPDATE users SET cabang_id = ?, updated_at = NOW() WHERE id = ?",
        [$target_cabang_id, $user_id]
    );

    if (!$result) {
        http_response_code(500);
        echo json_encode(['error' => 'Gagal memproses pindah cabang']);
        exit();
    }

    // Audit log
    logAudit(
        'pindah_cabang_karyawan',
        'users',
        $user_id,
        $old_data,
        ['cabang_id' => $target_cabang_id, 'nama_cabang' => $nama_cabang, 'alasan' => $alasan]
    );

    echo json_encode([
        'success' => true,
        'message' => "Karyawan {$target_user['nama']} berhasil dipindah ke cabang {$nama_cabang}",
        'data'    => [
            'user_id'        => $user_id,
            'username'       => $target_user['username'],
            'nama'           => $target_user['nama'],
            'action'         => 'pindah_cabang',
            'ke_cabang_id'   => $target_cabang_id,
            'ke_cabang_nama' => $nama_cabang,
            'alasan'         => $alasan,
        ],
    ]);
    exit();
}
