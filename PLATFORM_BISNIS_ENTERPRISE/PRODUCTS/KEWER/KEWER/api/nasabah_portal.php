<?php
/**
 * API: Nasabah Portal
 * 
 * Endpoints untuk nasabah melihat data pinjaman, angsuran, dan pembayaran milik sendiri
 * 
 * Access: nasabah only (own data)
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

// Check if user is logged in and is nasabah
if (!isLoggedIn() || $_SESSION['role'] !== 'nasabah') {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied. Nasabah only.']);
    exit();
}

$nasabah_id = $_SESSION['nasabah_id'] ?? null;
if (!$nasabah_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Nasabah ID not found in session']);
    exit();
}

$action = $_GET['action'] ?? 'dashboard';

switch ($action) {
    case 'dashboard':
        getDashboard($nasabah_id);
        break;
        
    case 'profile':
        getProfile($nasabah_id);
        break;
        
    case 'pinjaman':
        getPinjaman($nasabah_id);
        break;
        
    case 'pinjaman_detail':
        $pinjaman_id = $_GET['pinjaman_id'] ?? null;
        getPinjamanDetail($nasabah_id, $pinjaman_id);
        break;
        
    case 'angsuran':
        $pinjaman_id = $_GET['pinjaman_id'] ?? null;
        getAngsuran($nasabah_id, $pinjaman_id);
        break;
        
    case 'pembayaran':
        getPembayaran($nasabah_id);
        break;
        
    case 'update_profile':
        updateProfile($nasabah_id);
        break;
        
    case 'pengajuan_pinjaman_settings':
        getPengajuanPinjamanSettings();
        break;
        
    case 'submit_pengajuan_pinjaman':
        submitPengajuanPinjaman($nasabah_id);
        break;
        
    case 'list_pengajuan_pinjaman':
        listPengajuanPinjaman($nasabah_id);
        break;
        
    case 'pengajuan_simpanan_settings':
        getPengajuanSimpananSettings();
        break;
        
    case 'submit_pengajuan_simpanan':
        submitPengajuanSimpanan($nasabah_id);
        break;
        
    case 'list_pengajuan_simpanan':
        listPengajuanSimpanan($nasabah_id);
        break;
        
    case 'koperasi_terdaftar':
        getKoperasiTerdaftar($nasabah_id);
        break;
        
    case 'check_data_keluarga_required':
        checkDataKeluargaRequired($nasabah_id);
        break;
        
    case 'get_data_keluarga':
        getDataKeluarga($nasabah_id);
        break;
        
    case 'save_data_keluarga':
        saveDataKeluarga($nasabah_id);
        break;
        
    case 'upload_foto_kk':
        uploadFotoKK($nasabah_id);
        break;
        
    case 'add_anggota_keluarga':
        addAnggotaKeluarga($nasabah_id);
        break;
        
    case 'delete_anggota_keluarga':
        deleteAnggotaKeluarga($nasabah_id);
        break;
        
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Invalid action']);
}

/**
 * Get dashboard summary for nasabah
 */
function getDashboard($nasabah_id) {
    // Get nasabah info
    $nasabah = query("SELECT * FROM nasabah WHERE id = ?", [$nasabah_id]);
    if (!$nasabah) {
        echo json_encode(['error' => 'Nasabah not found']);
        return;
    }
    
    // Get active loans summary
    $active_loans = query("
        SELECT 
            COUNT(*) as total_loans,
            SUM(jumlah_pinjaman) as total_pinjaman,
            SUM(sisa_pinjaman) as total_sisa
        FROM pinjaman 
        WHERE nasabah_id = ? AND status IN ('aktif', 'disetujui')
    ", [$nasabah_id])[0];
    
    // Get next payment due
    $next_payment = query("
        SELECT a.*, p.kode_pinjaman
        FROM angsuran a
        JOIN pinjaman p ON a.pinjaman_id = p.id
        WHERE p.nasabah_id = ? 
          AND a.status = 'belum_bayar'
          AND a.tanggal_jatuh_tempo >= CURDATE()
        ORDER BY a.tanggal_jatuh_tempo ASC
        LIMIT 1
    ", [$nasabah_id])[0] ?? null;
    
    // Get total paid
    $total_paid = query("
        SELECT SUM(jumlah_bayar) as total 
        FROM pembayaran p
        JOIN angsuran a ON p.angsuran_id = a.id
        JOIN pinjaman pin ON a.pinjaman_id = pin.id
        WHERE pin.nasabah_id = ?
    ", [$nasabah_id])[0]['total'] ?? 0;
    
    // Get payment history count
    $payment_count = query("
        SELECT COUNT(*) as count 
        FROM pembayaran p
        JOIN angsuran a ON p.angsuran_id = a.id
        JOIN pinjaman pin ON a.pinjaman_id = pin.id
        WHERE pin.nasabah_id = ?
    ", [$nasabah_id])[0]['count'] ?? 0;
    
    echo json_encode([
        'success' => true,
        'data' => [
            'nasabah' => [
                'id' => $nasabah[0]['id'],
                'kode' => $nasabah[0]['kode_nasabah'],
                'nama' => $nasabah[0]['nama'],
                'status' => $nasabah[0]['status'],
                'blacklist' => $nasabah[0]['status'] === 'blacklist'
            ],
            'summary' => [
                'active_loans' => (int)$active_loans['total_loans'],
                'total_pinjaman' => (float)$active_loans['total_pinjaman'],
                'total_sisa' => (float)$active_loans['total_sisa'],
                'total_paid' => (float)$total_paid,
                'payment_count' => (int)$payment_count
            ],
            'next_payment' => $next_payment ? [
                'angsuran_ke' => $next_payment['angsuran_ke'],
                'jumlah' => (float)$next_payment['jumlah_angsuran'],
                'jatuh_tempo' => $next_payment['tanggal_jatuh_tempo'],
                'kode_pinjaman' => $next_payment['kode_pinjaman']
            ] : null
        ]
    ]);
}

/**
 * Get nasabah profile
 */
function getProfile($nasabah_id) {
    $nasabah = query("
        SELECT 
            n.*,
            p.name as province_name,
            r.name as regency_name,
            d.name as district_name,
            v.name as village_name
        FROM nasabah n
        LEFT JOIN db_alamat.provinces p ON n.province_id = p.id
        LEFT JOIN db_alamat.regencies r ON n.regency_id = r.id
        LEFT JOIN db_alamat.districts d ON n.district_id = d.id
        LEFT JOIN db_alamat.villages v ON n.village_id = v.id
        WHERE n.id = ?
    ", [$nasabah_id]);
    
    if (!$nasabah) {
        echo json_encode(['error' => 'Nasabah not found']);
        return;
    }
    
    $data = $nasabah[0];
    
    echo json_encode([
        'success' => true,
        'data' => [
            'id' => $data['id'],
            'kode_nasabah' => $data['kode_nasabah'],
            'nama' => $data['nama'],
            'ktp' => $data['ktp'],
            'telp' => $data['telp'],
            'email' => $data['email'],
            'alamat' => $data['alamat'],
            'alamat_lengkap' => [
                'alamat_rumah' => $data['alamat_rumah'],
                'province' => $data['province_name'],
                'regency' => $data['regency_name'],
                'district' => $data['district_name'],
                'village' => $data['village_name']
            ],
            'jenis_usaha' => $data['jenis_usaha'],
            'lokasi_pasar' => $data['lokasi_pasar'],
            'status' => $data['status'],
            'skor_kredit' => $data['skor_kredit'],
            'total_pinjaman_aktif' => $data['total_pinjaman_aktif'],
            'created_at' => $data['created_at']
        ]
    ]);
}

/**
 * Get pinjaman list for nasabah
 */
function getPinjaman($nasabah_id) {
    $pinjaman = query("
        SELECT 
            p.*,
            rb.nama as nama_bunga
        FROM pinjaman p
        LEFT JOIN ref_bunga rb ON p.bunga_id = rb.id
        WHERE p.nasabah_id = ?
        ORDER BY p.created_at DESC
    ", [$nasabah_id]);
    
    echo json_encode([
        'success' => true,
        'data' => $pinjaman
    ]);
}

/**
 * Get pinjaman detail with angsuran
 */
function getPinjamanDetail($nasabah_id, $pinjaman_id) {
    if (!$pinjaman_id) {
        echo json_encode(['error' => 'Pinjaman ID required']);
        return;
    }
    
    // Verify ownership
    $pinjaman = query("
        SELECT * FROM pinjaman 
        WHERE id = ? AND nasabah_id = ?
    ", [$pinjaman_id, $nasabah_id]);
    
    if (!$pinjaman) {
        echo json_encode(['error' => 'Pinjaman not found or access denied']);
        return;
    }
    
    // Get angsuran
    $angsuran = query("
        SELECT * FROM angsuran 
        WHERE pinjaman_id = ?
        ORDER BY angsuran_ke ASC
    ", [$pinjaman_id]);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'pinjaman' => $pinjaman[0],
            'angsuran' => $angsuran
        ]
    ]);
}

/**
 * Get angsuran for nasabah
 */
function getAngsuran($nasabah_id, $pinjaman_id = null) {
    $sql = "
        SELECT 
            a.*,
            p.kode_pinjaman,
            p.jumlah_pinjaman as total_pinjaman
        FROM angsuran a
        JOIN pinjaman p ON a.pinjaman_id = p.id
        WHERE p.nasabah_id = ?
    ";
    $params = [$nasabah_id];
    
    if ($pinjaman_id) {
        $sql .= " AND a.pinjaman_id = ?";
        $params[] = $pinjaman_id;
    }
    
    $sql .= " ORDER BY a.tanggal_jatuh_tempo DESC";
    
    $angsuran = query($sql, $params);
    
    echo json_encode([
        'success' => true,
        'data' => $angsuran
    ]);
}

/**
 * Get pembayaran history for nasabah
 */
function getPembayaran($nasabah_id) {
    $pembayaran = query("
        SELECT 
            p.*,
            a.angsuran_ke,
            a.tanggal_jatuh_tempo,
            pin.kode_pinjaman
        FROM pembayaran p
        JOIN angsuran a ON p.angsuran_id = a.id
        JOIN pinjaman pin ON a.pinjaman_id = pin.id
        WHERE pin.nasabah_id = ?
        ORDER BY p.tanggal_bayar DESC
    ", [$nasabah_id]);
    
    echo json_encode([
        'success' => true,
        'data' => $pembayaran
    ]);
}

/**
 * Update nasabah profile (limited fields)
 */
function updateProfile($nasabah_id) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid input']);
        return;
    }
    
    // Only allow updating certain fields
    $allowed_fields = ['telp', 'email', 'alamat_rumah'];
    $updates = [];
    $params = [];
    
    foreach ($allowed_fields as $field) {
        if (isset($input[$field])) {
            $updates[] = "$field = ?";
            $params[] = $input[$field];
        }
    }
    
    if (empty($updates)) {
        http_response_code(400);
        echo json_encode(['error' => 'No valid fields to update']);
        return;
    }
    
    $params[] = $nasabah_id;
    $sql = "UPDATE nasabah SET " . implode(', ', $updates) . ", updated_at = NOW() WHERE id = ?";
    
    $result = query($sql, $params);
    
    if ($result !== false) {
        echo json_encode([
            'success' => true,
            'message' => 'Profile updated successfully'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update profile']);
    }
}

/**
 * Get pengajuan pinjaman settings (limits from bos)
 */
function getPengajuanPinjamanSettings() {
    $settings = query("
        SELECT * FROM nasabah_pengaturan_limit 
        WHERE jenis_pengajuan = 'pinjaman' AND aktif = 1
        LIMIT 1
    ");
    
    if (!$settings) {
        // Default settings if not configured
        $settings = [[
            'jumlah_minimal' => 500000,
            'jumlah_maksimal' => 10000000,
            'kelipatan' => 100000,
            'bunga_per_bulan' => 2.00,
            'tenor_minimal' => 1,
            'tenor_maksimal' => 24,
            'frekuensi_angsuran' => 'bulanan',
            'persyaratan_khusus' => 'Minimal 1 bulan menjadi nasabah. Pinjaman harian max 100 hari, mingguan max 52 minggu.'
        ]];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $settings[0]
    ]);
}

/**
 * Check if data keluarga is required for this nasabah
 */
function checkDataKeluargaRequired($nasabah_id) {
    // Check if nasabah has previous loans
    $previous_loans = query("
        SELECT COUNT(*) as count FROM pinjaman 
        WHERE nasabah_id = ? AND status IN ('lunas', 'aktif', 'disetujui')
    ", [$nasabah_id])[0]['count'];
    
    // Check if data keluarga is already complete
    $data_keluarga = query("
        SELECT * FROM nasabah_keluarga 
        WHERE nasabah_id = ? AND status_verifikasi = 'terverifikasi'
    ", [$nasabah_id]);
    
    $required = $previous_loans > 0;
    $has_data = !empty($data_keluarga);
    
    // Update nasabah flags
    if ($required && !$has_data) {
        query("
            UPDATE nasabah 
            SET wajib_data_keluarga = TRUE, data_keluarga_lengkap = FALSE,
                tanggal_wajib_data_keluarga = COALESCE(tanggal_wajib_data_keluarga, CURDATE())
            WHERE id = ?
        ", [$nasabah_id]);
    } elseif ($has_data) {
        query("
            UPDATE nasabah 
            SET data_keluarga_lengkap = TRUE, wajib_data_keluarga = FALSE
            WHERE id = ?
        ", [$nasabah_id]);
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'required' => $required,
            'has_data' => $has_data,
            'previous_loans' => (int)$previous_loans,
            'message' => $required && !$has_data 
                ? 'Anda sudah pernah mengajukan pinjaman. Wajib melengkapi data keluarga dengan foto KK.' 
                : ($has_data ? 'Data keluarga sudah lengkap.' : 'Data keluarga belum wajib.')
        ]
    ]);
}

/**
 * Check nasabah cross-koperasi for warnings
 */
function checkCrossKoperasiWarning($nasabah_id, $current_koperasi_id) {
    // Get nasabah KTP and Telp
    $nasabah = query("SELECT ktp, telp, nama FROM nasabah WHERE id = ?", [$nasabah_id]);
    if (!$nasabah || empty($nasabah[0]['ktp'])) {
        return null; // Cannot check without KTP
    }
    
    $ktp = $nasabah[0]['ktp'];
    $telp = $nasabah[0]['telp'];
    
    // Check in other koperasi
    $cross_check = query("
        SELECT 
            n.id,
            n.nama,
            n.koperasi_id,
            k.nama_koperasi,
            COUNT(p.id) as pinjaman_aktif,
            SUM(COALESCE(p.sisa_pinjaman, 0)) as total_hutang,
            MAX(p.tanggal_pencairan) as pinjaman_terakhir
        FROM nasabah n
        LEFT JOIN koperasi_master k ON n.koperasi_id = k.id
        LEFT JOIN pinjaman p ON n.id = p.nasabah_id AND p.status = 'aktif'
        WHERE (n.ktp = ? OR n.telp = ?)
        AND n.koperasi_id != ?
        AND n.status != 'meninggal'
        GROUP BY n.id, n.koperasi_id
        HAVING pinjaman_aktif > 0 OR total_hutang > 0
    ", [$ktp, $telp, $current_koperasi_id]);
    
    if (!empty($cross_check)) {
        return [
            'has_warning' => true,
            'message' => 'Nasabah terdeteksi memiliki pinjaman aktif di koperasi lain',
            'data' => $cross_check
        ];
    }
    
    return null;
}

/**
 * Submit pengajuan pinjaman
 */
function submitPengajuanPinjaman($nasabah_id) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid input']);
        return;
    }
    
    // Validate required fields
    $required = ['jumlah_pengajuan', 'tenor', 'frekuensi_angsuran', 'tujuan_penggunaan', 'koperasi_id', 'metode_pengambilan'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            http_response_code(400);
            echo json_encode(['error' => "Field '$field' is required"]);
            return;
        }
    }
    
    // Check if data keluarga is required for repeat borrowers
    $previous_loans = query("
        SELECT COUNT(*) as count FROM pinjaman 
        WHERE nasabah_id = ? AND status IN ('lunas', 'aktif', 'disetujui')
    ", [$nasabah_id])[0]['count'];
    
    if ($previous_loans > 0) {
        $data_keluarga = query("
            SELECT * FROM nasabah_keluarga 
            WHERE nasabah_id = ? AND status_verifikasi = 'terverifikasi'
        ", [$nasabah_id]);
        
        if (empty($data_keluarga)) {
            http_response_code(400);
            echo json_encode([
                'error' => 'Data keluarga wajib diisi',
                'message' => 'Anda sudah pernah mengajukan pinjaman. Silakan lengkapi data keluarga dengan foto Kartu Keluarga terlebih dahulu.',
                'redirect_to' => 'data_keluarga'
            ]);
            return;
        }
    }
    
    // Check cross-koperasi warning
    $cross_warning = checkCrossKoperasiWarning($nasabah_id, $input['koperasi_id']);
    if ($cross_warning) {
        // Log the warning
        $nasabah = query("SELECT nama, ktp, telp FROM nasabah WHERE id = ?", [$nasabah_id]);
        $current_koperasi = query("SELECT nama_koperasi FROM koperasi_master WHERE id = ?", [$input['koperasi_id']]);
        
        query("
            INSERT INTO warning_cross_koperasi 
            (ktp_dicek, telp_dicek, nama_dicek, koperasi_pengecek_id, koperasi_pengecek_nama, 
             jumlah_koperasi_terdaftar, jumlah_koperasi_hutang, detail_temuan, status_warning)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'aktif')
        ", [
            $nasabah[0]['ktp'] ?? null,
            $nasabah[0]['telp'] ?? null,
            $nasabah[0]['nama'] ?? null,
            $input['koperasi_id'],
            $current_koperasi[0]['nama_koperasi'] ?? 'Unknown',
            count($cross_warning['data']),
            count(array_filter($cross_warning['data'], fn($r) => $r['total_hutang'] > 0)),
            json_encode($cross_warning['data'])
        ]);
        
        // Return warning but allow submission (as warning, not block)
        // The manager/bos can review this warning during approval
    }
    
    // Check settings limits
    $settings = query("
        SELECT * FROM nasabah_pengaturan_limit 
        WHERE jenis_pengajuan = 'pinjaman' AND aktif = 1
        LIMIT 1
    ")[0] ?? null;
    
    $min = $settings['jumlah_minimal'] ?? 500000;
    $max = $settings['jumlah_maksimal'] ?? 10000000;
    $kelipatan = $settings['kelipatan'] ?? 100000;
    
    $jumlah = (float)$input['jumlah_pengajuan'];
    
    // Validate amount
    if ($jumlah < $min || $jumlah > $max) {
        http_response_code(400);
        echo json_encode(['error' => "Jumlah pengajuan harus antara Rp" . number_format($min, 0, ',', '.') . " - Rp" . number_format($max, 0, ',', '.')]);
        return;
    }
    
    // Validate kelipatan
    if ($jumlah % $kelipatan !== 0) {
        http_response_code(400);
        echo json_encode(['error' => "Jumlah pengajuan harus kelipatan Rp" . number_format($kelipatan, 0, ',', '.')]);
        return;
    }
    
    // Check if nasabah has pending applications
    $pending = query("
        SELECT COUNT(*) as count FROM nasabah_pengajuan_pinjaman 
        WHERE nasabah_id = ? AND status_pengajuan IN ('diajukan', 'diproses')
    ", [$nasabah_id])[0]['count'];
    
    if ($pending > 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Anda memiliki pengajuan pinjaman yang masih diproses. Silakan tunggu persetujuan.']);
        return;
    }
    
    // Get nasabah address for diantar option
    $alamat_jemput = null;
    if ($input['metode_pengambilan'] === 'diantar_petugas') {
        $nasabah = query("SELECT alamat_rumah, alamat FROM nasabah WHERE id = ?", [$nasabah_id]);
        $alamat_jemput = $nasabah[0]['alamat_rumah'] ?? $nasabah[0]['alamat'] ?? null;
    }
    
    // Insert pengajuan
    $result = query("
        INSERT INTO nasabah_pengajuan_pinjaman 
        (nasabah_id, koperasi_id, jumlah_pengajuan, tenor, frekuensi_angsuran, tujuan_penggunaan, jaminan, status_pengajuan, metode_pengambilan, alamat_jemput, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'diajukan', ?, ?, NOW())
    ", [
        $nasabah_id,
        $input['koperasi_id'],
        $jumlah,
        $input['tenor'],
        $input['frekuensi_angsuran'],
        $input['tujuan_penggunaan'],
        $input['jaminan'] ?? null,
        $input['metode_pengambilan'],
        $alamat_jemput
    ]);
    
    if ($result) {
        $response = [
            'success' => true,
            'message' => 'Pengajuan pinjaman berhasil dikirim. Silakan tunggu persetujuan dari petugas koperasi.',
            'pengajuan_id' => $result,
            'metode_pengambilan' => $input['metode_pengambilan']
        ];
        
        // Add cross-koperasi warning if exists
        if ($cross_warning) {
            $response['cross_koperasi_warning'] = [
                'has_warning' => true,
                'message' => 'PERHATIAN: Nasabah terdeteksi memiliki pinjaman aktif di koperasi lain',
                'total_koperasi_dengan_hutang' => count($cross_warning['data']),
                'total_hutang_cross_koperasi' => array_sum(array_column($cross_warning['data'], 'total_hutang')),
                'detail_koperasi' => $cross_warning['data']
            ];
            $response['message'] .= ' PERHATIAN: Terdeteksi hutang di koperasi lain. Manager/Bos akan meninjau.';
        }
        
        echo json_encode($response);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Gagal mengirim pengajuan pinjaman']);
    }
}

/**
 * List pengajuan pinjaman history
 */
function listPengajuanPinjaman($nasabah_id) {
    $pengajuan = query("
        SELECT 
            p.*,
            c.nama_cabang as nama_koperasi,
            u.nama as disetujui_oleh_nama
        FROM nasabah_pengajuan_pinjaman p
        LEFT JOIN cabang c ON p.koperasi_id = c.id
        LEFT JOIN users u ON p.disetujui_oleh = u.id
        WHERE p.nasabah_id = ?
        ORDER BY p.created_at DESC
    ", [$nasabah_id]);
    
    echo json_encode([
        'success' => true,
        'data' => $pengajuan
    ]);
}

/**
 * Get pengajuan simpanan settings
 */
function getPengajuanSimpananSettings() {
    $settings = query("
        SELECT * FROM nasabah_pengaturan_limit 
        WHERE jenis_pengajuan = 'simpanan' AND aktif = 1
        LIMIT 1
    ");
    
    if (!$settings) {
        // Default settings if not configured
        $settings = [[
            'jumlah_minimal' => 100000,
            'jumlah_maksimal' => 50000000,
            'kelipatan' => 50000,
            'bunga_per_bulan' => 0.50,
            'tenor_minimal' => 0,
            'tenor_maksimal' => 0,
            'frekuensi_angsuran' => 'bulanan',
            'persyaratan_khusus' => 'Simpanan sukarela dengan bunga 0.5% per bulan. Minimal setoran Rp50.000.'
        ]];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $settings[0]
    ]);
}

/**
 * Submit pengajuan simpanan
 */
function submitPengajuanSimpanan($nasabah_id) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid input']);
        return;
    }
    
    // Validate required fields
    $required = ['jenis_simpanan', 'jumlah_pengajuan', 'metode_setoran', 'frekuensi_setoran', 'koperasi_id', 'metode_penyerahan'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            http_response_code(400);
            echo json_encode(['error' => "Field '$field' is required"]);
            return;
        }
    }
    
    // Check settings limits
    $settings = query("
        SELECT * FROM nasabah_pengaturan_limit 
        WHERE jenis_pengajuan = 'simpanan' AND aktif = 1
        LIMIT 1
    ")[0] ?? null;
    
    $min = $settings['jumlah_minimal'] ?? 100000;
    $max = $settings['jumlah_maksimal'] ?? 50000000;
    $kelipatan = $settings['kelipatan'] ?? 50000;
    
    $jumlah = (float)$input['jumlah_pengajuan'];
    
    // Validate amount
    if ($jumlah < $min || $jumlah > $max) {
        http_response_code(400);
        echo json_encode(['error' => "Jumlah simpanan harus antara Rp" . number_format($min, 0, ',', '.') . " - Rp" . number_format($max, 0, ',', '.')]);
        return;
    }
    
    // Validate kelipatan
    if ($jumlah % $kelipatan !== 0) {
        http_response_code(400);
        echo json_encode(['error' => "Jumlah simpanan harus kelipatan Rp" . number_format($kelipatan, 0, ',', '.')]);
        return;
    }
    
    // Get nasabah address for dijemput option
    $alamat_jemput = null;
    if ($input['metode_penyerahan'] === 'dijemput_petugas') {
        $nasabah = query("SELECT alamat_rumah, alamat FROM nasabah WHERE id = ?", [$nasabah_id]);
        $alamat_jemput = $nasabah[0]['alamat_rumah'] ?? $nasabah[0]['alamat'] ?? null;
    }
    
    // Insert pengajuan
    $result = query("
        INSERT INTO nasabah_pengajuan_simpanan 
        (nasabah_id, koperasi_id, jenis_simpanan, jumlah_pengajuan, metode_setoran, frekuensi_setoran, tujuan_simpanan, status_pengajuan, metode_penyerahan, alamat_jemput, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'diajukan', ?, ?, NOW())
    ", [
        $nasabah_id,
        $input['koperasi_id'],
        $input['jenis_simpanan'],
        $jumlah,
        $input['metode_setoran'],
        $input['frekuensi_setoran'],
        $input['tujuan_simpanan'] ?? null,
        $input['metode_penyerahan'],
        $alamat_jemput
    ]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Pengajuan simpanan berhasil dikirim. Silakan tunggu persetujuan dari petugas koperasi.',
            'pengajuan_id' => $result,
            'metode_penyerahan' => $input['metode_penyerahan']
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Gagal mengirim pengajuan simpanan']);
    }
}

/**
 * List pengajuan simpanan history
 */
function listPengajuanSimpanan($nasabah_id) {
    $pengajuan = query("
        SELECT 
            p.*,
            c.nama_cabang as nama_koperasi,
            u.nama as disetujui_oleh_nama
        FROM nasabah_pengajuan_simpanan p
        LEFT JOIN cabang c ON p.koperasi_id = c.id
        LEFT JOIN users u ON p.disetujui_oleh = u.id
        WHERE p.nasabah_id = ?
        ORDER BY p.created_at DESC
    ", [$nasabah_id]);
    
    echo json_encode([
        'success' => true,
        'data' => $pengajuan
    ]);
}

/**
 * Get koperasi where nasabah is registered
 */
function getKoperasiTerdaftar($nasabah_id) {
    // Get koperasi from nasabah_koperasi_terdaftar
    $koperasi = query("
        SELECT 
            kt.*,
            c.nama_cabang as nama_koperasi,
            c.alamat as alamat_koperasi
        FROM nasabah_koperasi_terdaftar kt
        LEFT JOIN cabang c ON kt.koperasi_id = c.id
        WHERE kt.nasabah_id = ? AND kt.status_anggota = 'aktif'
        ORDER BY kt.tanggal_terdaftar DESC
    ", [$nasabah_id]);
    
    // If no registered koperasi, return default koperasi (koperasi_id = 1)
    if (empty($koperasi)) {
        $default = query("
            SELECT 
                1 as koperasi_id,
                'Koperasi Utama' as nama_koperasi,
                'Kantor Pusat' as alamat_koperasi,
                CURRENT_DATE as tanggal_terdaftar,
                'aktif' as status_anggota
        ");
        $koperasi = $default;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $koperasi
    ]);
}

/**
 * Get data keluarga for nasabah
 */
function getDataKeluarga($nasabah_id) {
    $keluarga = query("
        SELECT * FROM nasabah_keluarga WHERE nasabah_id = ?
    ", [$nasabah_id]);
    
    if ($keluarga) {
        $anggota = query("
            SELECT * FROM nasabah_anggota_keluarga 
            WHERE nasabah_keluarga_id = ?
            ORDER BY FIELD(hubungan_dengan_kk, 'kepala_keluarga', 'istri', 'suami', 'anak', 'lainnya')
        ", [$keluarga[0]['id']]);
        $keluarga[0]['anggota'] = $anggota;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $keluarga[0] ?? null
    ]);
}

/**
 * Save data keluarga (header only)
 */
function saveDataKeluarga($nasabah_id) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid input']);
        return;
    }
    
    // Validate required fields
    $required = ['no_kk', 'nama_kepala_keluarga'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            http_response_code(400);
            echo json_encode(['error' => "Field '$field' wajib diisi"]);
            return;
        }
    }
    
    // Check if data already exists
    $existing = query("SELECT id FROM nasabah_keluarga WHERE nasabah_id = ?", [$nasabah_id]);
    
    if ($existing) {
        // Update
        $result = query("
            UPDATE nasabah_keluarga SET
                no_kk = ?,
                nama_kepala_keluarga = ?,
                alamat_keluarga = ?,
                rt = ?,
                rw = ?,
                desa_kelurahan = ?,
                kecamatan = ?,
                kabupaten = ?,
                provinsi = ?,
                kode_pos = ?,
                jumlah_anggota = ?,
                status_verifikasi = 'menunggu',
                updated_at = NOW()
            WHERE nasabah_id = ?
        ", [
            $input['no_kk'],
            $input['nama_kepala_keluarga'],
            $input['alamat_keluarga'] ?? null,
            $input['rt'] ?? null,
            $input['rw'] ?? null,
            $input['desa_kelurahan'] ?? null,
            $input['kecamatan'] ?? null,
            $input['kabupaten'] ?? null,
            $input['provinsi'] ?? null,
            $input['kode_pos'] ?? null,
            $input['jumlah_anggota'] ?? 0,
            $nasabah_id
        ]);
        $keluarga_id = $existing[0]['id'];
    } else {
        // Insert
        $result = query("
            INSERT INTO nasabah_keluarga 
            (nasabah_id, no_kk, nama_kepala_keluarga, alamat_keluarga, rt, rw, desa_kelurahan, 
             kecamatan, kabupaten, provinsi, kode_pos, jumlah_anggota, status_verifikasi, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'menunggu', NOW())
        ", [
            $nasabah_id,
            $input['no_kk'],
            $input['nama_kepala_keluarga'],
            $input['alamat_keluarga'] ?? null,
            $input['rt'] ?? null,
            $input['rw'] ?? null,
            $input['desa_kelurahan'] ?? null,
            $input['kecamatan'] ?? null,
            $input['kabupaten'] ?? null,
            $input['provinsi'] ?? null,
            $input['kode_pos'] ?? null,
            $input['jumlah_anggota'] ?? 0
        ]);
        $keluarga_id = $result;
    }
    
    if ($result !== false) {
        echo json_encode([
            'success' => true,
            'message' => 'Data keluarga berhasil disimpan',
            'keluarga_id' => $keluarga_id
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Gagal menyimpan data keluarga']);
    }
}

/**
 * Upload foto KK
 */
function uploadFotoKK($nasabah_id) {
    // Check if data keluarga exists
    $keluarga = query("SELECT id, foto_kk FROM nasabah_keluarga WHERE nasabah_id = ?", [$nasabah_id]);
    
    if (!$keluarga) {
        http_response_code(400);
        echo json_encode(['error' => 'Simpan data keluarga terlebih dahulu sebelum upload foto KK']);
        return;
    }
    
    // Handle file upload
    if (!isset($_FILES['foto_kk'])) {
        http_response_code(400);
        echo json_encode(['error' => 'File foto KK tidak ditemukan']);
        return;
    }
    
    $file = $_FILES['foto_kk'];
    $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowed_types)) {
        http_response_code(400);
        echo json_encode(['error' => 'Format file harus JPG atau PNG']);
        return;
    }
    
    if ($file['size'] > $max_size) {
        http_response_code(400);
        echo json_encode(['error' => 'Ukuran file maksimal 5MB']);
        return;
    }
    
    // Create upload directory
    $upload_dir = BASE_PATH . '/uploads/kk/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Generate filename
    $filename = 'kk_' . $nasabah_id . '_' . time() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
    $filepath = $upload_dir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // Delete old file if exists
        if ($keluarga[0]['foto_kk'] && file_exists($upload_dir . $keluarga[0]['foto_kk'])) {
            unlink($upload_dir . $keluarga[0]['foto_kk']);
        }
        
        // Update database
        $result = query("
            UPDATE nasabah_keluarga 
            SET foto_kk = ?
            WHERE nasabah_id = ?
        ", [$filename, $nasabah_id]);
        
        if ($result !== false) {
            echo json_encode([
                'success' => true,
                'message' => 'Foto KK berhasil diupload',
                'foto_kk' => $filename
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Gagal menyimpan foto KK']);
        }
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Gagal mengupload file']);
    }
}

/**
 * Add anggota keluarga
 */
function addAnggotaKeluarga($nasabah_id) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid input']);
        return;
    }
    
    // Get keluarga ID
    $keluarga = query("SELECT id FROM nasabah_keluarga WHERE nasabah_id = ?", [$nasabah_id]);
    if (!$keluarga) {
        http_response_code(400);
        echo json_encode(['error' => 'Data keluarga belum ada. Simpan data keluarga terlebih dahulu.']);
        return;
    }
    
    $keluarga_id = $keluarga[0]['id'];
    
    // Validate required fields
    $required = ['nama_lengkap', 'hubungan_dengan_kk'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            http_response_code(400);
            echo json_encode(['error' => "Field '$field' wajib diisi"]);
            return;
        }
    }
    
    // Insert anggota
    $result = query("
        INSERT INTO nasabah_anggota_keluarga 
        (nasabah_keluarga_id, nama_lengkap, nik, jenis_kelamin, tempat_lahir, tanggal_lahir,
         status_perkawinan, hubungan_dengan_kk, pekerjaan, agama, pendidikan, 
         kewarganegaraan, nama_ayah, nama_ibu, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ", [
        $keluarga_id,
        $input['nama_lengkap'],
        $input['nik'] ?? null,
        $input['jenis_kelamin'] ?? null,
        $input['tempat_lahir'] ?? null,
        $input['tanggal_lahir'] ?? null,
        $input['status_perkawinan'] ?? null,
        $input['hubungan_dengan_kk'],
        $input['pekerjaan'] ?? null,
        $input['agama'] ?? null,
        $input['pendidikan'] ?? null,
        $input['kewarganegaraan'] ?? 'WNI',
        $input['nama_ayah'] ?? null,
        $input['nama_ibu'] ?? null
    ]);
    
    if ($result) {
        // Update jumlah anggota
        query("
            UPDATE nasabah_keluarga 
            SET jumlah_anggota = (SELECT COUNT(*) FROM nasabah_anggota_keluarga WHERE nasabah_keluarga_id = ?)
            WHERE id = ?
        ", [$keluarga_id, $keluarga_id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Anggota keluarga berhasil ditambahkan',
            'anggota_id' => $result
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Gagal menambahkan anggota keluarga']);
    }
}

/**
 * Delete anggota keluarga
 */
function deleteAnggotaKeluarga($nasabah_id) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || empty($input['anggota_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Anggota ID required']);
        return;
    }
    
    // Get keluarga ID for verification
    $keluarga = query("SELECT id FROM nasabah_keluarga WHERE nasabah_id = ?", [$nasabah_id]);
    if (!$keluarga) {
        http_response_code(403);
        echo json_encode(['error' => 'Access denied']);
        return;
    }
    
    $keluarga_id = $keluarga[0]['id'];
    
    // Delete anggota (verify ownership)
    $result = query("
        DELETE FROM nasabah_anggota_keluarga 
        WHERE id = ? AND nasabah_keluarga_id = ?
    ", [$input['anggota_id'], $keluarga_id]);
    
    if ($result !== false) {
        // Update jumlah anggota
        query("
            UPDATE nasabah_keluarga 
            SET jumlah_anggota = (SELECT COUNT(*) FROM nasabah_anggota_keluarga WHERE nasabah_keluarga_id = ?)
            WHERE id = ?
        ", [$keluarga_id, $keluarga_id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Anggota keluarga berhasil dihapus'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Gagal menghapus anggota keluarga']);
    }
}
