<?php
/**
 * API: Petugas Tugas Pengajuan
 * 
 * Endpoints untuk petugas/teller mengelola tugas pengajuan pinjaman dan simpanan
 * - Melihat daftar tugas (jemput simpanan, antar pinjaman)
 * - Update status tugas
 * - Input hasil kunjungan
 * 
 * Access: petugas_cabang, petugas_pusat, admin_cabang, admin_pusat
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

try {
    require_once __DIR__ . '/../config/path.php';
    require_once BASE_PATH . '/includes/functions.php';
    require_once BASE_PATH . '/config/database.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Configuration error']);
    exit();
}

// Check if user is logged in and has appropriate role
if (!isLoggedIn()) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied. Please login.']);
    exit();
}

$allowed_roles = ['petugas_cabang', 'petugas_pusat', 'admin_cabang', 'admin_pusat', 'manager_cabang', 'manager_pusat', 'bos'];
if (!in_array($_SESSION['role'], $allowed_roles)) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied. Petugas/Teller only.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$action = $_GET['action'] ?? 'list_tugas';

switch ($action) {
    case 'list_tugas':
        getListTugas($user_id, $role);
        break;
        
    case 'detail_tugas':
        $tugas_id = $_GET['tugas_id'] ?? null;
        getDetailTugas($tugas_id);
        break;
        
    case 'update_status':
        updateStatusTugas($user_id);
        break;
        
    case 'input_hasil':
        inputHasilKunjungan($user_id);
        break;
        
    case 'list_pengajuan_approve':
        getListPengajuanForApproval($user_id, $role);
        break;
        
    case 'approve_pinjaman':
        approvePengajuanPinjaman($user_id, $role);
        break;
        
    case 'approve_simpanan':
        approvePengajuanSimpanan($user_id, $role);
        break;
        
    case 'batalkan_pengajuan':
        batalkanPengajuan($user_id, $role);
        break;
        
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Invalid action']);
}

/**
 * Get list of tugas for petugas
 */
function getListTugas($user_id, $role) {
    $status = $_GET['status'] ?? 'dibuat';
    $jenis = $_GET['jenis'] ?? null;
    
    $sql = "
        SELECT 
            t.*,
            n.nama as nama_nasabah,
            n.telp as telp_nasabah,
            p.jumlah_pengajuan as jumlah_pinjaman,
            s.jenis_simpanan,
            s.jumlah_pengajuan as jumlah_simpanan
        FROM tugas_petugas_pengajuan t
        JOIN nasabah n ON t.nasabah_id = n.id
        LEFT JOIN nasabah_pengajuan_pinjaman p ON t.pengajuan_pinjaman_id = p.id
        LEFT JOIN nasabah_pengajuan_simpanan s ON t.pengajuan_simpanan_id = s.id
        WHERE 1=1
    ";
    $params = [];
    
    // Filter by petugas unless admin/manager
    if (!in_array($role, ['admin_pusat', 'admin_cabang', 'manager_pusat', 'manager_cabang', 'bos'])) {
        $sql .= " AND t.petugas_id = ?";
        $params[] = $user_id;
    }
    
    if ($status) {
        $sql .= " AND t.status_tugas = ?";
        $params[] = $status;
    }
    
    if ($jenis) {
        $sql .= " AND t.jenis_tugas = ?";
        $params[] = $jenis;
    }
    
    $sql .= " ORDER BY t.tanggal_tugas ASC, t.created_at ASC";
    
    $tugas = query($sql, $params);
    
    echo json_encode([
        'success' => true,
        'data' => $tugas,
        'count' => count($tugas)
    ]);
}

/**
 * Get detail of specific tugas
 */
function getDetailTugas($tugas_id) {
    if (!$tugas_id) {
        http_response_code(400);
        echo json_encode(['error' => 'Tugas ID required']);
        return;
    }
    
    $sql = "
        SELECT 
            t.*,
            n.nama as nama_nasabah,
            n.kode_nasabah,
            n.telp as telp_nasabah,
            n.alamat as alamat_nasabah,
            n.alamat_rumah,
            p.jumlah_pengajuan as jumlah_pinjaman,
            p.tenor,
            p.frekuensi_angsuran,
            p.tujuan_penggunaan,
            p.jaminan,
            s.jenis_simpanan,
            s.jumlah_pengajuan as jumlah_simpanan,
            s.metode_setoran,
            s.frekuensi_setoran,
            s.tujuan_simpanan
        FROM tugas_petugas_pengajuan t
        JOIN nasabah n ON t.nasabah_id = n.id
        LEFT JOIN nasabah_pengajuan_pinjaman p ON t.pengajuan_pinjaman_id = p.id
        LEFT JOIN nasabah_pengajuan_simpanan s ON t.pengajuan_simpanan_id = s.id
        WHERE t.id = ?
    ";
    
    $tugas = query($sql, [$tugas_id]);
    
    if (!$tugas) {
        http_response_code(404);
        echo json_encode(['error' => 'Tugas not found']);
        return;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $tugas[0]
    ]);
}

/**
 * Update status tugas
 */
function updateStatusTugas($user_id) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || empty($input['tugas_id']) || empty($input['status_tugas'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Tugas ID and status are required']);
        return;
    }
    
    $valid_status = ['dibuat', 'dikerjakan', 'selesai', 'dibatalkan'];
    if (!in_array($input['status_tugas'], $valid_status)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid status']);
        return;
    }
    
    $result = query("
        UPDATE tugas_petugas_pengajuan 
        SET status_tugas = ?, updated_at = NOW()
        WHERE id = ? AND petugas_id = ?
    ", [$input['status_tugas'], $input['tugas_id'], $user_id]);
    
    if ($result !== false) {
        echo json_encode([
            'success' => true,
            'message' => 'Status tugas berhasil diupdate'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Gagal mengupdate status tugas']);
    }
}

/**
 * Input hasil kunjungan
 */
function inputHasilKunjungan($user_id) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || empty($input['tugas_id']) || empty($input['hasil_kunjungan'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Tugas ID and hasil kunjungan are required']);
        return;
    }
    
    $result = query("
        UPDATE tugas_petugas_pengajuan 
        SET hasil_kunjungan = ?, catatan_petugas = ?, status_tugas = 'selesai', updated_at = NOW()
        WHERE id = ? AND petugas_id = ?
    ", [
        $input['hasil_kunjungan'],
        $input['catatan_petugas'] ?? null,
        $input['tugas_id'],
        $user_id
    ]);
    
    if ($result !== false) {
        echo json_encode([
            'success' => true,
            'message' => 'Hasil kunjungan berhasil disimpan'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Gagal menyimpan hasil kunjungan']);
    }
}

/**
 * Get list of pengajuan waiting for approval
 */
function getListPengajuanForApproval($user_id, $role) {
    $jenis = $_GET['jenis'] ?? 'pinjaman'; // 'pinjaman' or 'simpanan'
    $status = $_GET['status'] ?? 'diajukan'; // 'diajukan' or 'diproses'
    
    if ($jenis === 'pinjaman') {
        $sql = "
            SELECT 
                p.*,
                n.nama as nama_nasabah,
                n.kode_nasabah,
                n.telp,
                n.alamat_rumah,
                c.nama_cabang as nama_koperasi
            FROM nasabah_pengajuan_pinjaman p
            JOIN nasabah n ON p.nasabah_id = n.id
            LEFT JOIN cabang c ON p.koperasi_id = c.id
            WHERE p.status_pengajuan = ?
            ORDER BY p.created_at ASC
        ";
    } else {
        $sql = "
            SELECT 
                p.*,
                n.nama as nama_nasabah,
                n.kode_nasabah,
                n.telp,
                n.alamat_rumah,
                c.nama_cabang as nama_koperasi
            FROM nasabah_pengajuan_simpanan p
            JOIN nasabah n ON p.nasabah_id = n.id
            LEFT JOIN cabang c ON p.koperasi_id = c.id
            WHERE p.status_pengajuan = ?
            ORDER BY p.created_at ASC
        ";
    }
    
    $pengajuan = query($sql, [$status]);
    
    echo json_encode([
        'success' => true,
        'data' => $pengajuan,
        'count' => count($pengajuan)
    ]);
}

/**
 * Approve pengajuan pinjaman
 */
function approvePengajuanPinjaman($user_id, $role) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || empty($input['pengajuan_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Pengajuan ID required']);
        return;
    }
    
    // Check if can approve
    if (!in_array($role, ['manager_cabang', 'manager_pusat', 'bos'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Only manager or bos can approve']);
        return;
    }
    
    // Get pengajuan details
    $pengajuan = query("SELECT * FROM nasabah_pengajuan_pinjaman WHERE id = ?", [$input['pengajuan_id']]);
    if (!$pengajuan) {
        http_response_code(404);
        echo json_encode(['error' => 'Pengajuan not found']);
        return;
    }
    
    $status = $input['status'] ?? 'disetujui'; // 'disetujui' or 'ditolak'
    $catatan = $input['catatan_petugas'] ?? null;
    
    // Update pengajuan
    $result = query("
        UPDATE nasabah_pengajuan_pinjaman 
        SET status_pengajuan = ?, disetujui_oleh = ?, tanggal_persetujuan = CURDATE(), catatan_petugas = ?, updated_at = NOW()
        WHERE id = ?
    ", [$status, $user_id, $catatan, $input['pengajuan_id']]);
    
    if ($result !== false) {
        // If approved and diantar_petugas, create tugas
        if ($status === 'disetujui' && $pengajuan[0]['metode_pengambilan'] === 'diantar_petugas') {
            createTugasAntarPinjaman($input['pengajuan_id'], $pengajuan[0]);
        }
        
        echo json_encode([
            'success' => true,
            'message' => $status === 'disetujui' ? 'Pengajuan pinjaman disetujui' : 'Pengajuan pinjaman ditolak'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Gagal mengupdate pengajuan']);
    }
}

/**
 * Approve pengajuan simpanan
 */
function approvePengajuanSimpanan($user_id, $role) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || empty($input['pengajuan_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Pengajuan ID required']);
        return;
    }
    
    // Check if can approve
    if (!in_array($role, ['manager_cabang', 'manager_pusat', 'bos'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Only manager or bos can approve']);
        return;
    }
    
    // Get pengajuan details
    $pengajuan = query("SELECT * FROM nasabah_pengajuan_simpanan WHERE id = ?", [$input['pengajuan_id']]);
    if (!$pengajuan) {
        http_response_code(404);
        echo json_encode(['error' => 'Pengajuan not found']);
        return;
    }
    
    $status = $input['status'] ?? 'disetujui'; // 'disetujui' or 'ditolak'
    $catatan = $input['catatan_petugas'] ?? null;
    
    // Update pengajuan
    $result = query("
        UPDATE nasabah_pengajuan_simpanan 
        SET status_pengajuan = ?, disetujui_oleh = ?, tanggal_persetujuan = CURDATE(), catatan_petugas = ?, updated_at = NOW()
        WHERE id = ?
    ", [$status, $user_id, $catatan, $input['pengajuan_id']]);
    
    if ($result !== false) {
        // If approved and dijemput_petugas, create tugas
        if ($status === 'disetujui' && $pengajuan[0]['metode_penyerahan'] === 'dijemput_petugas') {
            createTugasJemputSimpanan($input['pengajuan_id'], $pengajuan[0]);
        }
        
        echo json_encode([
            'success' => true,
            'message' => $status === 'disetujui' ? 'Pengajuan simpanan disetujui' : 'Pengajuan simpanan ditolak'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Gagal mengupdate pengajuan']);
    }
}

/**
 * Batalkan pengajuan
 */
function batalkanPengajuan($user_id, $role) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || empty($input['pengajuan_id']) || empty($input['jenis'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Pengajuan ID and jenis required']);
        return;
    }
    
    $jenis = $input['jenis']; // 'pinjaman' or 'simpanan'
    $alasan = $input['alasan_penolakan'] ?? 'Dibatalkan oleh petugas';
    
    if ($jenis === 'pinjaman') {
        $result = query("
            UPDATE nasabah_pengajuan_pinjaman 
            SET status_pengajuan = 'ditolak', alasan_penolakan = ?, updated_at = NOW()
            WHERE id = ?
        ", [$alasan, $input['pengajuan_id']]);
    } else {
        $result = query("
            UPDATE nasabah_pengajuan_simpanan 
            SET status_pengajuan = 'ditolak', alasan_penolakan = ?, updated_at = NOW()
            WHERE id = ?
        ", [$alasan, $input['pengajuan_id']]);
    }
    
    if ($result !== false) {
        echo json_encode([
            'success' => true,
            'message' => 'Pengajuan berhasil dibatalkan'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Gagal membatalkan pengajuan']);
    }
}

/**
 * Helper: Create tugas for antar pinjaman
 */
function createTugasAntarPinjaman($pengajuan_id, $pengajuan_data) {
    // Get nasabah details
    $nasabah = query("SELECT nama, alamat_rumah, alamat FROM nasabah WHERE id = ?", [$pengajuan_data['nasabah_id']]);
    $alamat = $nasabah[0]['alamat_rumah'] ?? $nasabah[0]['alamat'] ?? 'Alamat tidak tersedia';
    
    // Create tugas
    query("
        INSERT INTO tugas_petugas_pengajuan 
        (petugas_id, jenis_tugas, pengajuan_pinjaman_id, nasabah_id, alamat_tujuan, tanggal_tugas, status_tugas, created_at)
        VALUES (NULL, 'antar_pinjaman', ?, ?, ?, CURDATE(), 'dibuat', NOW())
    ", [
        $pengajuan_id,
        $pengajuan_data['nasabah_id'],
        $alamat
    ]);
}

/**
 * Helper: Create tugas for jemput simpanan
 */
function createTugasJemputSimpanan($pengajuan_id, $pengajuan_data) {
    // Get nasabah details
    $nasabah = query("SELECT nama, alamat_rumah, alamat FROM nasabah WHERE id = ?", [$pengajuan_data['nasabah_id']]);
    $alamat = $nasabah[0]['alamat_rumah'] ?? $nasabah[0]['alamat'] ?? 'Alamat tidak tersedia';
    
    // Create tugas
    query("
        INSERT INTO tugas_petugas_pengajuan 
        (petugas_id, jenis_tugas, pengajuan_simpanan_id, nasabah_id, alamat_tujuan, tanggal_tugas, status_tugas, created_at)
        VALUES (NULL, 'jemput_simpanan', ?, ?, ?, CURDATE(), 'dibuat', NOW())
    ", [
        $pengajuan_id,
        $pengajuan_data['nasabah_id'],
        $alamat
    ]);
}
