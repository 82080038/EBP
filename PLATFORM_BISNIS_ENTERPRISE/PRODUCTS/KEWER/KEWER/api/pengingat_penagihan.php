<?php
/**
 * API: Pengingat Penagihan (Payment Reminder)
 * 
 * Endpoints untuk mengelola pengaturan pengingat penagihan per nasabah
 * Pengingat bisa diaktifkan/nonaktifkan oleh bos, admin, atau petugas lapangan
 * 
 * Access: bos, manager_pusat, manager_cabang, admin_pusat, admin_cabang, petugas_pusat, petugas_cabang
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
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

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied. Please login.']);
    exit();
}

// Check if user has appropriate role
$allowed_roles = ['bos', 'manager_pusat', 'manager_cabang', 'admin_pusat', 'admin_cabang', 'petugas_pusat', 'petugas_cabang'];
if (!in_array($_SESSION['role'], $allowed_roles)) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied. Only bos, admin, or petugas can manage reminders.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];
$user_nama = $_SESSION['nama'] ?? $_SESSION['username'];
$action = $_GET['action'] ?? 'list';

switch ($action) {
    case 'list':
        getListNasabahWithReminder($user_id, $user_role);
        break;
        
    case 'detail':
        $nasabah_id = $_GET['nasabah_id'] ?? null;
        getReminderDetail($nasabah_id);
        break;
        
    case 'update':
        updateReminderSetting($user_id, $user_nama);
        break;
        
    case 'toggle':
        toggleReminder($user_id, $user_nama);
        break;
        
    case 'filter':
        filterByReminderStatus();
        break;
        
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Invalid action']);
}

/**
 * Get list of nasabah with reminder settings
 */
function getListNasabahWithReminder($user_id, $user_role) {
    $search = $_GET['search'] ?? '';
    $status = $_GET['status'] ?? null; // 'aktif', 'nonaktif', or null for all
    $page = (int)($_GET['page'] ?? 1);
    $limit = (int)($_GET['limit'] ?? 20);
    $offset = ($page - 1) * $limit;
    
    // Build query
    $sql = "
        SELECT 
            n.id,
            n.kode_nasabah,
            n.nama,
            n.telp,
            n.pengingat_penagihan_aktif,
            n.catatan_pengingat,
            n.pengingat_diset_tanggal,
            u.nama as diset_oleh_nama,
            c.nama_cabang as nama_cabang,
            (SELECT COUNT(*) FROM pinjaman p WHERE p.nasabah_id = n.id AND p.status = 'aktif') as pinjaman_aktif
        FROM nasabah n
        LEFT JOIN users u ON n.pengingat_diset_oleh = u.id
        LEFT JOIN cabang c ON n.cabang_id = c.id
        WHERE n.status = 'aktif'
    ";
    $params = [];
    
    // Filter by search
    if ($search) {
        $sql .= " AND (n.nama LIKE ? OR n.kode_nasabah LIKE ? OR n.telp LIKE ?)";
        $search_param = "%$search%";
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
    }
    
    // Filter by reminder status
    if ($status === 'aktif') {
        $sql .= " AND n.pengingat_penagihan_aktif = 1";
    } elseif ($status === 'nonaktif') {
        $sql .= " AND n.pengingat_penagihan_aktif = 0";
    }
    
    // Get total count
    $count_sql = str_replace("SELECT 
            n.id,
            n.kode_nasabah,
            n.nama,
            n.telp,
            n.pengingat_penagihan_aktif,
            n.catatan_pengingat,
            n.pengingat_diset_tanggal,
            u.nama as diset_oleh_nama,
            c.nama_cabang as nama_cabang,
            (SELECT COUNT(*) FROM pinjaman p WHERE p.nasabah_id = n.id AND p.status = 'aktif') as pinjaman_aktif", "SELECT COUNT(*) as total", $sql);
    $count_result = query($count_sql, $params);
    $total = $count_result[0]['total'] ?? 0;
    
    // Add pagination
    $sql .= " ORDER BY n.nama ASC LIMIT $limit OFFSET $offset";
    
    $nasabah = query($sql, $params);
    
    echo json_encode([
        'success' => true,
        'data' => $nasabah,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => (int)$total,
            'total_pages' => ceil($total / $limit)
        ]
    ]);
}

/**
 * Get reminder detail for specific nasabah
 */
function getReminderDetail($nasabah_id) {
    if (!$nasabah_id) {
        http_response_code(400);
        echo json_encode(['error' => 'Nasabah ID required']);
        return;
    }
    
    $nasabah = query("
        SELECT 
            n.id,
            n.kode_nasabah,
            n.nama,
            n.telp,
            n.telp_keluarga,
            n.alamat,
            n.alamat_penagihan,
            n.pengingat_penagihan_aktif,
            n.catatan_pengingat,
            n.pengingat_diset_tanggal,
            n.pengingat_diset_oleh,
            u.nama as diset_oleh_nama,
            u.role as diset_oleh_role,
            c.nama_cabang as nama_cabang,
            (SELECT COUNT(*) FROM pinjaman p WHERE p.nasabah_id = n.id AND p.status = 'aktif') as pinjaman_aktif,
            (SELECT COUNT(*) FROM pinjaman p WHERE p.nasabah_id = n.id AND p.status = 'lunas') as total_pinjaman_lunas
        FROM nasabah n
        LEFT JOIN users u ON n.pengingat_diset_oleh = u.id
        LEFT JOIN cabang c ON n.cabang_id = c.id
        WHERE n.id = ?
    ", [$nasabah_id]);
    
    if (!$nasabah) {
        http_response_code(404);
        echo json_encode(['error' => 'Nasabah not found']);
        return;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $nasabah[0]
    ]);
}

/**
 * Update reminder setting
 */
function updateReminderSetting($user_id, $user_nama) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || empty($input['nasabah_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Nasabah ID required']);
        return;
    }
    
    $nasabah_id = $input['nasabah_id'];
    $aktif = isset($input['pengingat_aktif']) ? (int)$input['pengingat_aktif'] : 1;
    $catatan = $input['catatan'] ?? null;
    
    // Validate catatan when turning off
    if ($aktif === 0 && empty($catatan)) {
        http_response_code(400);
        echo json_encode(['error' => 'Catatan wajib diisi saat mematikan pengingat (contoh: nasabah suka kabur)']);
        return;
    }
    
    // Update nasabah
    $result = query("
        UPDATE nasabah 
        SET 
            pengingat_penagihan_aktif = ?,
            catatan_pengingat = ?,
            pengingat_diset_oleh = ?,
            pengingat_diset_tanggal = NOW(),
            updated_at = NOW()
        WHERE id = ?
    ", [$aktif, $catatan, $user_id, $nasabah_id]);
    
    if ($result !== false) {
        // Log the change
        $aktif_text = $aktif ? 'DIAKTIFKAN' : 'DINONAKTIFKAN';
        $catatan_text = $catatan ? " (Catatan: $catatan)" : '';
        
        query("
            INSERT INTO koperasi_activities 
            (user_id, activity_type, description, table_name, record_id, created_at)
            VALUES (?, 'update', ?, 'nasabah', ?, NOW())
        ", [
            $user_id,
            "Pengingat penagihan $aktif_text oleh $user_nama$catatan_text",
            $nasabah_id
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => $aktif ? 'Pengingat penagihan diaktifkan' : 'Pengingat penagihan dinonaktifkan',
            'data' => [
                'nasabah_id' => $nasabah_id,
                'pengingat_aktif' => $aktif,
                'catatan' => $catatan,
                'diset_oleh' => $user_nama,
                'diset_tanggal' => date('Y-m-d H:i:s')
            ]
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Gagal mengupdate pengaturan pengingat']);
    }
}

/**
 * Quick toggle reminder (without changing catatan)
 */
function toggleReminder($user_id, $user_nama) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || empty($input['nasabah_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Nasabah ID required']);
        return;
    }
    
    $nasabah_id = $input['nasabah_id'];
    
    // Get current status
    $current = query("SELECT pengingat_penagihan_aktif, catatan_pengingat FROM nasabah WHERE id = ?", [$nasabah_id]);
    if (!$current) {
        http_response_code(404);
        echo json_encode(['error' => 'Nasabah not found']);
        return;
    }
    
    $current_status = $current[0]['pengingat_penagihan_aktif'];
    $new_status = $current_status ? 0 : 1;
    
    // If turning off and no catatan exists, require catatan
    if (!$new_status && empty($current[0]['catatan_pengingat']) && empty($input['catatan'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Catatan wajib diisi saat mematikan pengingat']);
        return;
    }
    
    $catatan = $input['catatan'] ?? $current[0]['catatan_pengingat'];
    
    // Update
    $result = query("
        UPDATE nasabah 
        SET 
            pengingat_penagihan_aktif = ?,
            catatan_pengingat = COALESCE(?, catatan_pengingat),
            pengingat_diset_oleh = ?,
            pengingat_diset_tanggal = NOW(),
            updated_at = NOW()
        WHERE id = ?
    ", [$new_status, $catatan, $user_id, $nasabah_id]);
    
    if ($result !== false) {
        $aktif_text = $new_status ? 'DIAKTIFKAN' : 'DINONAKTIFKAN';
        
        echo json_encode([
            'success' => true,
            'message' => "Pengingat penagihan $aktif_text",
            'data' => [
                'nasabah_id' => $nasabah_id,
                'pengingat_aktif' => $new_status,
                'status_sebelumnya' => $current_status
            ]
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Gagal mengupdate pengaturan']);
    }
}

/**
 * Filter nasabah by reminder status
 */
function filterByReminderStatus() {
    $aktif_only = isset($_GET['aktif']) && $_GET['aktif'] == '1';
    $nonaktif_only = isset($_GET['nonaktif']) && $_GET['nonaktif'] == '1';
    
    $sql = "
        SELECT 
            n.id,
            n.kode_nasabah,
            n.nama,
            n.telp,
            n.pengingat_penagihan_aktif,
            n.catatan_pengingat,
            n.pengingat_diset_tanggal,
            u.nama as diset_oleh_nama
        FROM nasabah n
        LEFT JOIN users u ON n.pengingat_diset_oleh = u.id
        WHERE n.status = 'aktif'
    ";
    $params = [];
    
    if ($aktif_only) {
        $sql .= " AND n.pengingat_penagihan_aktif = 1";
    } elseif ($nonaktif_only) {
        $sql .= " AND n.pengingat_penagihan_aktif = 0";
    }
    
    $sql .= " ORDER BY n.pengingat_diset_tanggal DESC NULLS LAST, n.nama ASC";
    
    $nasabah = query($sql, $params);
    
    // Count summary
    $aktif_count = query("SELECT COUNT(*) as count FROM nasabah WHERE status = 'aktif' AND pengingat_penagihan_aktif = 1")[0]['count'];
    $nonaktif_count = query("SELECT COUNT(*) as count FROM nasabah WHERE status = 'aktif' AND pengingat_penagihan_aktif = 0")[0]['count'];
    
    echo json_encode([
        'success' => true,
        'data' => $nasabah,
        'summary' => [
            'total' => count($nasabah),
            'pengingat_aktif' => (int)$aktif_count,
            'pengingat_nonaktif' => (int)$nonaktif_count
        ]
    ]);
}
