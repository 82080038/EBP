<?php
/**
 * API: Pembayaran Elektronik
 * 
 * Endpoint untuk:
 * 1. Manage rekening koperasi (BOS ONLY)
 * 2. Generate kode pembayaran untuk nasabah
 * 3. Generate kode penyetoran untuk petugas
 * 4. Verifikasi pembayaran dengan kode
 * 
 * Security: Role-based access control enforced
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

try {
    require_once __DIR__ . '/../config/path.php';
    require_once BASE_PATH . '/includes/functions.php';
    require_once BASE_PATH . '/config/database.php';
    require_once BASE_PATH . '/includes/koperasi_isolation.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Configuration error']);
    exit();
}

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied. Please login.']);
    exit();
}

// Feature flag — disabled until koperasi_rekening / kode_pembayaran schema is built
if (function_exists('isFeatureEnabled') && !isFeatureEnabled('pembayaran_elektronik')) {
    http_response_code(503);
    echo json_encode([
        'success' => false,
        'error' => 'Electronic payment feature is currently disabled (pending schema build-out).',
        'feature_flag' => 'pembayaran_elektronik'
    ]);
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];
$current_koperasi_id = getCurrentKoperasiId();
$action = $_GET['action'] ?? '';

// Role-based action routing
switch ($action) {
    // ===== BOS ONLY: Manage Rekening Koperasi =====
    case 'list_rekening':
        if ($user_role !== 'bos' && $user_role !== 'appOwner') {
            http_response_code(403);
            echo json_encode(['error' => 'Only bos can manage rekening']);
            return;
        }
        listRekening($current_koperasi_id);
        break;
        
    case 'add_rekening':
        if ($user_role !== 'bos' && $user_role !== 'appOwner') {
            http_response_code(403);
            echo json_encode(['error' => 'Only bos can add rekening']);
            return;
        }
        addRekening($user_id, $current_koperasi_id);
        break;
        
    case 'update_rekening':
        if ($user_role !== 'bos' && $user_role !== 'appOwner') {
            http_response_code(403);
            echo json_encode(['error' => 'Only bos can update rekening']);
            return;
        }
        updateRekening($user_id, $current_koperasi_id);
        break;
        
    case 'delete_rekening':
        if ($user_role !== 'bos' && $user_role !== 'appOwner') {
            http_response_code(403);
            echo json_encode(['error' => 'Only bos can delete rekening']);
            return;
        }
        deleteRekening($current_koperasi_id);
        break;
        
    case 'set_primary_rekening':
        if ($user_role !== 'bos' && $user_role !== 'appOwner') {
            http_response_code(403);
            echo json_encode(['error' => 'Only bos can set primary rekening']);
            return;
        }
        setPrimaryRekening($user_id, $current_koperasi_id);
        break;
    
    // ===== PUBLIC: Get Rekening Koperasi (Read Only) =====
    case 'get_rekening_koperasi':
        getRekeningKoperasiPublic($current_koperasi_id);
        break;
        
    // ===== NASABAH: Generate Kode Pembayaran =====
    case 'generate_kode_pembayaran':
        if ($user_role !== 'nasabah' && !in_array($user_role, ['bos', 'admin_pusat', 'admin_cabang', 'petugas_pusat', 'petugas_cabang'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }
        generateKodePembayaran($user_id, $user_role, $current_koperasi_id);
        break;
        
    case 'list_kode_pembayaran':
        listKodePembayaran($user_id, $user_role);
        break;
        
    // ===== PETUGAS: Generate Kode Penyetoran =====
    case 'generate_kode_penyetoran':
        if (!in_array($user_role, ['petugas_pusat', 'petugas_cabang', 'bos', 'admin_pusat', 'admin_cabang'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Only petugas/bos/admin can generate deposit code']);
            return;
        }
        generateKodePenyetoran($user_id, $user_role, $current_koperasi_id);
        break;
        
    case 'list_kode_penyetoran':
        listKodePenyetoran($user_id, $user_role);
        break;
        
    // ===== VERIFIKASI: Teller/Admin =====
    case 'verifikasi_pembayaran':
        if (!in_array($user_role, ['bos', 'admin_pusat', 'admin_cabang', 'manager_pusat', 'manager_cabang'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Only admin/manager/bos can verify payments']);
            return;
        }
        verifikasiPembayaran($user_id);
        break;
        
    case 'verifikasi_penyetoran_petugas':
        if (!in_array($user_role, ['bos', 'admin_pusat', 'admin_cabang', 'manager_pusat', 'manager_cabang'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Only admin/manager/bos can verify deposits']);
            return;
        }
        verifikasiPenyetoranPetugas($user_id);
        break;
        
    case 'cek_kode':
        cekKodePembayaran();
        break;
        
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Invalid action']);
}

// ============================================
// REKENING KOPERASI MANAGEMENT (BOS ONLY)
// ============================================

function listRekening($koperasi_id) {
    $rekening = query("
        SELECT r.*, k.nama_koperasi
        FROM koperasi_rekening r
        LEFT JOIN koperasi_master k ON r.koperasi_id = k.id
        WHERE r.koperasi_id = ?
        ORDER BY r.is_primary DESC, r.urutan ASC
    ", [$koperasi_id]);
    
    echo json_encode([
        'success' => true,
        'data' => $rekening
    ]);
}

function addRekening($user_id, $koperasi_id) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $required = ['nama_bank', 'nomor_rekening', 'nama_pemilik_rekening'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            http_response_code(400);
            echo json_encode(['error' => "Field '$field' wajib diisi"]);
            return;
        }
    }
    
    // If this is first rekening, make it primary
    $existing = query("SELECT COUNT(*) as count FROM koperasi_rekening WHERE koperasi_id = ? AND is_active = 1", [$koperasi_id]);
    $is_primary = ($existing[0]['count'] == 0) ? 1 : (int)($input['is_primary'] ?? 0);
    
    // If setting as primary, unset others
    if ($is_primary) {
        query("UPDATE koperasi_rekening SET is_primary = 0 WHERE koperasi_id = ?", [$koperasi_id]);
    }
    
    $result = query("
        INSERT INTO koperasi_rekening 
        (koperasi_id, nama_bank, nomor_rekening, nama_pemilik_rekening, 
         jenis_rekening, nama_ewallet, is_primary, is_active, urutan, created_by)
        VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?, ?)
    ", [
        $koperasi_id,
        $input['nama_bank'],
        $input['nomor_rekening'],
        $input['nama_pemilik_rekening'],
        $input['jenis_rekening'] ?? 'bank',
        $input['nama_ewallet'] ?? null,
        $is_primary,
        $input['urutan'] ?? 0,
        $user_id
    ]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Rekening berhasil ditambahkan',
            'rekening_id' => $result
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Gagal menambahkan rekening']);
    }
}

function updateRekening($user_id, $koperasi_id) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['rekening_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Rekening ID required']);
        return;
    }
    
    // Verify ownership
    $rekening = query("SELECT * FROM koperasi_rekening WHERE id = ? AND koperasi_id = ?", [$input['rekening_id'], $koperasi_id]);
    if (!$rekening) {
        http_response_code(404);
        echo json_encode(['error' => 'Rekening not found']);
        return;
    }
    
    // If setting as primary, unset others
    if (!empty($input['is_primary']) && $input['is_primary']) {
        query("UPDATE koperasi_rekening SET is_primary = 0 WHERE koperasi_id = ?", [$koperasi_id]);
    }
    
    $result = query("
        UPDATE koperasi_rekening 
        SET nama_bank = ?, nomor_rekening = ?, nama_pemilik_rekening = ?,
            jenis_rekening = ?, nama_ewallet = ?, is_primary = ?, 
            is_active = ?, urutan = ?, updated_by = ?, updated_at = NOW()
        WHERE id = ? AND koperasi_id = ?
    ", [
        $input['nama_bank'] ?? $rekening[0]['nama_bank'],
        $input['nomor_rekening'] ?? $rekening[0]['nomor_rekening'],
        $input['nama_pemilik_rekening'] ?? $rekening[0]['nama_pemilik_rekening'],
        $input['jenis_rekening'] ?? $rekening[0]['jenis_rekening'],
        $input['nama_ewallet'] ?? $rekening[0]['nama_ewallet'],
        (int)($input['is_primary'] ?? $rekening[0]['is_primary']),
        (int)($input['is_active'] ?? $rekening[0]['is_active']),
        $input['urutan'] ?? $rekening[0]['urutan'],
        $user_id,
        $input['rekening_id'],
        $koperasi_id
    ]);
    
    if ($result !== false) {
        echo json_encode(['success' => true, 'message' => 'Rekening berhasil diupdate']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Gagal mengupdate rekening']);
    }
}

function deleteRekening($koperasi_id) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['rekening_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Rekening ID required']);
        return;
    }
    
    // Soft delete - just deactivate
    $result = query("
        UPDATE koperasi_rekening 
        SET is_active = 0, updated_at = NOW()
        WHERE id = ? AND koperasi_id = ?
    ", [$input['rekening_id'], $koperasi_id]);
    
    if ($result !== false) {
        echo json_encode(['success' => true, 'message' => 'Rekening berhasil dinonaktifkan']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Gagal menonaktifkan rekening']);
    }
}

function setPrimaryRekening($user_id, $koperasi_id) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['rekening_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Rekening ID required']);
        return;
    }
    
    // Unset all primary
    query("UPDATE koperasi_rekening SET is_primary = 0 WHERE koperasi_id = ?", [$koperasi_id]);
    
    // Set new primary
    $result = query("
        UPDATE koperasi_rekening 
        SET is_primary = 1, updated_by = ?, updated_at = NOW()
        WHERE id = ? AND koperasi_id = ?
    ", [$user_id, $input['rekening_id'], $koperasi_id]);
    
    if ($result !== false) {
        echo json_encode(['success' => true, 'message' => 'Rekening utama berhasil diubah']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Gagal mengubah rekening utama']);
    }
}

// ============================================
// PUBLIC: Get Rekening Koperasi
// ============================================

function getRekeningKoperasiPublic($koperasi_id) {
    // Get primary and active rekening
    $rekening = query("
        SELECT id, nama_bank, nomor_rekening, nama_pemilik_rekening,
               jenis_rekening, nama_ewallet, is_primary
        FROM koperasi_rekening
        WHERE koperasi_id = ? AND is_active = 1
        ORDER BY is_primary DESC, urutan ASC
    ", [$koperasi_id]);
    
    // Get koperasi info
    $koperasi = query("SELECT nama_koperasi, kode_koperasi FROM koperasi_master WHERE id = ?", [$koperasi_id]);
    
    echo json_encode([
        'success' => true,
        'koperasi' => $koperasi[0] ?? null,
        'rekening' => $rekening,
        'instruksi' => [
            'title' => 'Cara Pembayaran Transfer',
            'steps' => [
                '1. Transfer ke salah satu rekening di atas',
                '2. Masukkan kode unik di berita transfer (contoh: PAY123456)',
                '3. Simpan bukti transfer',
                '4. Konfirmasi pembayaran melalui aplikasi'
            ]
        ]
    ]);
}

// ============================================
// KODE PEMBAYARAN NASABAH
// ============================================

function generateKodePembayaran($user_id, $user_role, $koperasi_id) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Get nasabah_id based on role
    if ($user_role === 'nasabah') {
        $nasabah = query("SELECT id FROM nasabah WHERE db_orang_user_id = ?", [$user_id]);
        $nasabah_id = $nasabah[0]['id'] ?? null;
    } else {
        // Admin/petugas generating for specific nasabah
        $nasabah_id = $input['nasabah_id'] ?? null;
    }
    
    if (!$nasabah_id) {
        http_response_code(400);
        echo json_encode(['error' => 'Nasabah ID required']);
        return;
    }
    
    // Validate required fields
    $required = ['jenis_pembayaran', 'jumlah_diharapkan'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            http_response_code(400);
            echo json_encode(['error' => "Field '$field' wajib diisi"]);
            return;
        }
    }
    
    // Generate unique code
    $kode_unik = generateUniqueCode('PAY');
    
    // Expiry 24 hours
    $expired_at = date('Y-m-d H:i:s', strtotime('+24 hours'));
    
    $result = query("
        INSERT INTO kode_pembayaran 
        (kode_unik, nasabah_id, koperasi_id, jenis_pembayaran, 
         jumlah_diharapkan, keterangan, expired_at)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ", [
        $kode_unik,
        $nasabah_id,
        $koperasi_id,
        $input['jenis_pembayaran'],
        $input['jumlah_diharapkan'],
        $input['keterangan'] ?? null,
        $expired_at
    ]);
    
    if ($result) {
        // Get rekening koperasi
        $rekening = query("
            SELECT * FROM koperasi_rekening 
            WHERE koperasi_id = ? AND is_active = 1 AND is_primary = 1
            LIMIT 1
        ", [$koperasi_id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Kode pembayaran berhasil dibuat',
            'data' => [
                'kode_unik' => $kode_unik,
                'jenis_pembayaran' => $input['jenis_pembayaran'],
                'jumlah_diharapkan' => (float)$input['jumlah_diharapkan'],
                'keterangan' => $input['keterangan'] ?? '',
                'expired_at' => $expired_at,
                'rekening_tujuan' => $rekening[0] ?? null,
                'instruksi' => "Transfer ke rekening di atas dengan jumlah exact: Rp" . number_format($input['jumlah_diharapkan'], 0, ',', '.') . " dan masukkan kode $kode_unik di berita transfer"
            ]
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Gagal membuat kode pembayaran']);
    }
}

function listKodePembayaran($user_id, $user_role) {
    $status = $_GET['status'] ?? 'aktif';
    $nasabah_id = $_GET['nasabah_id'] ?? null;
    
    $sql = "
        SELECT kp.*, n.nama as nama_nasabah, n.kode_nasabah
        FROM kode_pembayaran kp
        LEFT JOIN nasabah n ON kp.nasabah_id = n.id
        WHERE 1=1
    ";
    $params = [];
    
    if ($nasabah_id) {
        $sql .= " AND kp.nasabah_id = ?";
        $params[] = $nasabah_id;
    }
    
    if ($user_role === 'nasabah') {
        $nasabah = query("SELECT id FROM nasabah WHERE db_orang_user_id = ?", [$user_id]);
        $sql .= " AND kp.nasabah_id = ?";
        $params[] = $nasabah[0]['id'] ?? 0;
    }
    
    $sql .= " AND kp.status = ? ORDER BY kp.created_at DESC";
    $params[] = $status;
    
    $kodes = query($sql, $params);
    
    echo json_encode([
        'success' => true,
        'data' => $kodes
    ]);
}

// ============================================
// KODE PENYETORAN PETUGAS
// ============================================

function generateKodePenyetoran($user_id, $user_role, $koperasi_id) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Get cabang_id for petugas
    $user = query("SELECT cabang_id FROM users WHERE id = ?", [$user_id]);
    $cabang_id = $user[0]['cabang_id'] ?? null;
    
    $required = ['jumlah_penyetoran'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            http_response_code(400);
            echo json_encode(['error' => "Field '$field' wajib diisi"]);
            return;
        }
    }
    
    // Generate unique code
    $kode_unik = generateUniqueCode('DEP'); // DEP = Deposit
    
    // Expiry 24 hours
    $expired_at = date('Y-m-d H:i:s', strtotime('+24 hours'));
    
    $result = query("
        INSERT INTO kode_penyetoran_petugas 
        (kode_unik, petugas_id, koperasi_id, cabang_id, 
         jumlah_penyetoran, keterangan, expired_at)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ", [
        $kode_unik,
        $user_id,
        $koperasi_id,
        $cabang_id,
        $input['jumlah_penyetoran'],
        $input['keterangan'] ?? 'Penyetoran dana petugas',
        $expired_at
    ]);
    
    if ($result) {
        // Get rekening koperasi
        $rekening = query("
            SELECT * FROM koperasi_rekening 
            WHERE koperasi_id = ? AND is_active = 1 AND is_primary = 1
            LIMIT 1
        ", [$koperasi_id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Kode penyetoran berhasil dibuat',
            'data' => [
                'kode_unik' => $kode_unik,
                'jumlah_penyetoran' => (float)$input['jumlah_penyetoran'],
                'keterangan' => $input['keterangan'] ?? 'Penyetoran dana petugas',
                'expired_at' => $expired_at,
                'rekening_tujuan' => $rekening[0] ?? null,
                'instruksi' => "Transfer ke rekening di atas dengan jumlah exact: Rp" . number_format($input['jumlah_penyetoran'], 0, ',', '.') . " dan masukkan kode $kode_unik di berita transfer"
            ]
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Gagal membuat kode penyetoran']);
    }
}

function listKodePenyetoran($user_id, $user_role) {
    $status = $_GET['status'] ?? 'aktif';
    
    $sql = "
        SELECT kpp.*, u.nama as nama_petugas, c.nama_cabang
        FROM kode_penyetoran_petugas kpp
        LEFT JOIN users u ON kpp.petugas_id = u.id
        LEFT JOIN cabang c ON kpp.cabang_id = c.id
        WHERE kpp.status = ?
    ";
    $params = [$status];
    
    if ($user_role === 'petugas_pusat' || $user_role === 'petugas_cabang') {
        $sql .= " AND kpp.petugas_id = ?";
        $params[] = $user_id;
    }
    
    $sql .= " ORDER BY kpp.created_at DESC";
    
    $kodes = query($sql, $params);
    
    echo json_encode([
        'success' => true,
        'data' => $kodes
    ]);
}

// ============================================
// VERIFIKASI PEMBAYARAN
// ============================================

function verifikasiPembayaran($admin_id) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['kode_unik'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Kode unik wajib diisi']);
        return;
    }
    
    // Find the code
    $kode = query("
        SELECT * FROM kode_pembayaran 
        WHERE kode_unik = ? AND status = 'aktif'
    ", [$input['kode_unik']]);
    
    if (!$kode) {
        http_response_code(404);
        echo json_encode(['error' => 'Kode tidak ditemukan atau sudah tidak aktif']);
        return;
    }
    
    // Check if expired
    if (strtotime($kode[0]['expired_at']) < time()) {
        query("UPDATE kode_pembayaran SET status = 'expired' WHERE id = ?", [$kode[0]['id']]);
        http_response_code(400);
        echo json_encode(['error' => 'Kode sudah expired']);
        return;
    }
    
    // Update status
    $result = query("
        UPDATE kode_pembayaran 
        SET status = 'digunakan', 
            digunakan_at = NOW(),
            digunakan_oleh = ?,
            bukti_transfer = ?,
            catatan_verifikasi = ?
        WHERE id = ?
    ", [
        $admin_id,
        $input['bukti_transfer'] ?? null,
        $input['catatan'] ?? 'Pembayaran terverifikasi',
        $kode[0]['id']
    ]);
    
    if ($result !== false) {
        echo json_encode([
            'success' => true,
            'message' => 'Pembayaran berhasil diverifikasi',
            'data' => [
                'kode_unik' => $input['kode_unik'],
                'jenis_pembayaran' => $kode[0]['jenis_pembayaran'],
                'jumlah' => (float)$kode[0]['jumlah_diharapkan'],
                'nasabah_id' => $kode[0]['nasabah_id']
            ]
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Gagal memverifikasi pembayaran']);
    }
}

function verifikasiPenyetoranPetugas($admin_id) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['kode_unik'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Kode unik wajib diisi']);
        return;
    }
    
    // Find the code
    $kode = query("
        SELECT * FROM kode_penyetoran_petugas 
        WHERE kode_unik = ? AND status = 'aktif'
    ", [$input['kode_unik']]);
    
    if (!$kode) {
        http_response_code(404);
        echo json_encode(['error' => 'Kode tidak ditemukan atau sudah tidak aktif']);
        return;
    }
    
    // Check if expired
    if (strtotime($kode[0]['expired_at']) < time()) {
        query("UPDATE kode_penyetoran_petugas SET status = 'expired' WHERE id = ?", [$kode[0]['id']]);
        http_response_code(400);
        echo json_encode(['error' => 'Kode sudah expired']);
        return;
    }
    
    // Update status
    $result = query("
        UPDATE kode_penyetoran_petugas 
        SET status = 'digunakan', 
            digunakan_at = NOW(),
            diverifikasi_oleh = ?,
            diverifikasi_at = NOW(),
            bukti_transfer = ?,
            catatan_verifikasi = ?
        WHERE id = ?
    ", [
        $admin_id,
        $input['bukti_transfer'] ?? null,
        $input['catatan'] ?? 'Penyetoran terverifikasi',
        $kode[0]['id']
    ]);
    
    if ($result !== false) {
        echo json_encode([
            'success' => true,
            'message' => 'Penyetoran petugas berhasil diverifikasi',
            'data' => [
                'kode_unik' => $input['kode_unik'],
                'jumlah' => (float)$kode[0]['jumlah_penyetoran'],
                'petugas_id' => $kode[0]['petugas_id']
            ]
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Gagal memverifikasi penyetoran']);
    }
}

function cekKodePembayaran() {
    $kode_unik = $_GET['kode'] ?? null;
    
    if (!$kode_unik) {
        http_response_code(400);
        echo json_encode(['error' => 'Kode wajib diisi']);
        return;
    }
    
    // Check nasabah payment code
    $nasabah_kode = query("
        SELECT kp.*, n.nama as nama_nasabah, n.kode_nasabah
        FROM kode_pembayaran kp
        LEFT JOIN nasabah n ON kp.nasabah_id = n.id
        WHERE kp.kode_unik = ?
    ", [$kode_unik]);
    
    if ($nasabah_kode) {
        $is_expired = strtotime($nasabah_kode[0]['expired_at']) < time();
        
        echo json_encode([
            'success' => true,
            'type' => 'nasabah',
            'valid' => $nasabah_kode[0]['status'] === 'aktif' && !$is_expired,
            'data' => [
                'kode' => $nasabah_kode[0],
                'is_expired' => $is_expired,
                'expired_at' => $nasabah_kode[0]['expired_at']
            ]
        ]);
        return;
    }
    
    // Check petugas deposit code
    $petugas_kode = query("
        SELECT kpp.*, u.nama as nama_petugas
        FROM kode_penyetoran_petugas kpp
        LEFT JOIN users u ON kpp.petugas_id = u.id
        WHERE kpp.kode_unik = ?
    ", [$kode_unik]);
    
    if ($petugas_kode) {
        $is_expired = strtotime($petugas_kode[0]['expired_at']) < time();
        
        echo json_encode([
            'success' => true,
            'type' => 'petugas',
            'valid' => $petugas_kode[0]['status'] === 'aktif' && !$is_expired,
            'data' => [
                'kode' => $petugas_kode[0],
                'is_expired' => $is_expired,
                'expired_at' => $petugas_kode[0]['expired_at']
            ]
        ]);
        return;
    }
    
    echo json_encode([
        'success' => false,
        'valid' => false,
        'error' => 'Kode tidak ditemukan'
    ]);
}

// ============================================
// HELPER FUNCTIONS
// ============================================

function generateUniqueCode($prefix = 'PAY') {
    // Format: PREFIX + TIMESTAMP + RANDOM
    $timestamp = date('YmdHis');
    $random = strtoupper(substr(uniqid(), -4));
    $random2 = strtoupper(substr(md5(uniqid()), 0, 2));
    return $prefix . substr($timestamp, -6) . $random . $random2;
}
