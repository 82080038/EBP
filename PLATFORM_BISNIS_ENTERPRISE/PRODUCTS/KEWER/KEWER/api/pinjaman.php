<?php
// Suppress errors to ensure JSON output
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

try {
    require_once __DIR__ . '/../config/path.php';
    require_once BASE_PATH . '/includes/functions.php';
    require_once BASE_PATH . '/includes/accounting_helper.php';
    require_once BASE_PATH . '/includes/business_logic.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
    exit;
}

// Auth check
try {
    requireLogin();
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['error' => 'Authentication failed: ' . $e->getMessage()]);
    exit();
}

try {
    $user = getCurrentUser();
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['error' => 'Failed to get user: ' . $e->getMessage()]);
    exit();
}

if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Validasi kantor_id: harus milik bos yang sedang login
$kantor_id = $_GET['kantor_id'] ?? null;

if ($kantor_id) {
    if (!validateCabangOwnership($kantor_id)) {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden - Kantor ini bukan milik koperasi Anda']);
        exit();
    }
} else {
    $owned = getBosOwnedCabangIds();
    $kantor_id = $owned[0] ?? 1;
}

// Get kantor info
$kantor = query("SELECT * FROM cabang WHERE id = ?", [$kantor_id]);
if (!$kantor) {
    http_response_code(404);
    echo json_encode(['error' => 'Kantor tidak ditemukan']);
    exit();
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        // Get pinjaman list
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        
        $where = [];
        $params = [];
        
        if ($search) {
            $where[] = "(p.kode_pinjaman LIKE ? OR n.nama LIKE ? OR n.kode_nasabah LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        if ($status) {
            $where[] = "p.status = ?";
            $params[] = $status;
        }
        
        // Isolasi data: hanya tampilkan pinjaman dari cabang milik bos yang login
        $cabang_filter = buildCabangFilter('p.cabang_id');
        if ($cabang_filter) {
            $where[] = ltrim($cabang_filter['clause'], 'AND ');
            $params  = array_merge($params, $cabang_filter['params']);
        }

        $where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        $pinjaman = query("
            SELECT p.*, n.nama, n.kode_nasabah 
            FROM pinjaman p 
            JOIN nasabah n ON p.nasabah_id = n.id 
            $where_clause 
            ORDER BY p.created_at DESC
        ", $params);
        
        if (!is_array($pinjaman)) $pinjaman = [];
        
        echo json_encode([
            'success' => true,
            'data' => $pinjaman,
            'total' => count($pinjaman)
        ]);
        break;
        
    case 'POST':
        // Create new pinjaman
        if (!hasPermission('manage_pinjaman')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Forbidden - No permission to manage pinjaman']);
            break;
        }
        $input = json_decode(file_get_contents('php://input'), true);
        
        $nasabah_id = $input['nasabah_id'] ?? '';
        $plafon = $input['plafon'] ?? '';
        $tenor = $input['tenor'] ?? '';
        $frekuensi = $input['frekuensi'] ?? 'bulanan';
        $bunga_per_bulan = $input['bunga_per_bulan'] ?? '';
        $tanggal_akad = $input['tanggal_akad'] ?? '';
        $tujuan_pinjaman = $input['tujuan_pinjaman'] ?? '';
        $jaminan = $input['jaminan'] ?? '';
        
        // Convert frequency to ID if it's a code
        $frekuensi_id = getFrequencyId($frekuensi);
        
        // Validate frekuensi_id (must be 1, 2, or 3 for harian, mingguan, bulanan)
        if (!$frekuensi_id || !in_array($frekuensi_id, [1, 2, 3])) {
            $frekuensi_id = 3; // Default to bulanan
        }
        
        // Validation
        if (!$nasabah_id || !$plafon || !$tenor || !$bunga_per_bulan || !$tanggal_akad) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Semua field wajib diisi']);
            break;
        }
        
        if (!is_numeric($plafon) || $plafon <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Plafon harus berupa angka positif']);
            break;
        }
        
        $max_tenor = getMaxTenor($frekuensi_id);
        if (!is_numeric($tenor) || $tenor <= 0 || $tenor > $max_tenor) {
            http_response_code(400);
            $period_label = getFrequencyPeriodLabel($frekuensi_id);
            echo json_encode(['success' => false, 'error' => "Tenor harus antara 1-$max_tenor $period_label"]);
            break;
        }
        
        // Validasi kelayakan nasabah (blacklist, meninggal, skor kredit)
        require_once BASE_PATH . '/includes/business_logic.php';
        $owner_bos_id_check = getOwnerBosId();
        $layak = cekKelayakanNasabah($nasabah_id, $owner_bos_id_check);
        if (!$layak['allowed']) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => $layak['reason'], 'warnings' => $layak['warnings'] ?? []]);
            break;
        }

        // Check if nasabah has active loan
        $active_loan = query("SELECT id FROM pinjaman WHERE nasabah_id = ? AND status IN ('disetujui', 'aktif') AND override_pinjaman_aktif = 0", [$nasabah_id]);
        $override_aktif = (int)($input['override_pinjaman_aktif'] ?? 0);
        if ($active_loan && !$override_aktif) {
            http_response_code(400);
            echo json_encode([
                'success'  => false,
                'error'    => 'Nasabah masih memiliki pinjaman aktif. Jika ingin tetap proses, sertakan override_pinjaman_aktif=1 dengan alasan (hanya bos/manager).',
                'warnings' => $layak['warnings'] ?? [],
            ]);
            break;
        }
        if ($active_loan && $override_aktif) {
            // Hanya bos/manager_pusat yang boleh override
            if (!in_array(getCurrentUser()['role'] ?? '', ['bos', 'manager_pusat'])) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Hanya bos/manager_pusat yang bisa override pinjaman ganda']);
                break;
            }
        }
        
        // Calculate loan
        $calc = calculateLoan($plafon, $tenor, $bunga_per_bulan, $frekuensi_id);
        
        // Generate kode pinjaman
        $kode_pinjaman = generateKode('PNJ', 'pinjaman', 'kode_pinjaman');
        
        // Calculate due date based on frequency
        $frekuensi_code = getFrequencyCode($frekuensi_id);
        switch ($frekuensi_code) {
            case 'HARIAN':
                $tanggal_jatuh_tempo = date('Y-m-d', strtotime("+$tenor day", strtotime($tanggal_akad)));
                break;
            case 'MINGGUAN':
                $tanggal_jatuh_tempo = date('Y-m-d', strtotime("+$tenor week", strtotime($tanggal_akad)));
                break;
            default:
                $tanggal_jatuh_tempo = date('Y-m-d', strtotime("+$tenor month", strtotime($tanggal_akad)));
                break;
        }
        
        // Tentukan cabang_id untuk insert
        $nasabah_data = query("SELECT cabang_id, owner_bos_id FROM nasabah WHERE id = ?", [$nasabah_id]);
        $cabang_id_insert = $nasabah_data[0]['cabang_id'] ?? $user['cabang_id'] ?? 1;

        // Override pinjaman ganda?
        $override_aktif  = (int)($input['override_pinjaman_aktif'] ?? 0);
        $override_alasan = $input['override_alasan'] ?? null;
        $override_oleh   = $override_aktif ? $user['id'] : null;

        // Insert pinjaman dengan field v2.2.0
        $result = query("INSERT INTO pinjaman (
            cabang_id, kode_pinjaman, nasabah_id, plafon, tenor, frekuensi_id, bunga_per_bulan,
            total_bunga, total_pembayaran, angsuran_pokok, angsuran_bunga, angsuran_total,
            tanggal_akad, tanggal_jatuh_tempo, tujuan_pinjaman, jaminan,
            sisa_pokok_berjalan, override_pinjaman_aktif, override_oleh, override_alasan,
            status, petugas_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pengajuan', ?)", [
            $cabang_id_insert, $kode_pinjaman, $nasabah_id, $plafon, $tenor, $frekuensi_id, $bunga_per_bulan,
            $calc['total_bunga'], $calc['total_pembayaran'], $calc['angsuran_pokok'],
            $calc['angsuran_bunga'], $calc['angsuran_total'],
            $tanggal_akad, $tanggal_jatuh_tempo, $tujuan_pinjaman, $jaminan,
            $plafon, $override_aktif, $override_oleh, $override_alasan,
            $user['id']
        ]);

        if ($result) {
            $result_id = query("SELECT LAST_INSERT_ID() as id");
            $new_id = is_array($result_id) && isset($result_id[0]) ? $result_id[0]['id'] : null;
            // Update cache total_pinjaman_aktif di nasabah
            query("UPDATE nasabah SET total_pinjaman_aktif = (SELECT COUNT(*) FROM pinjaman WHERE nasabah_id = ? AND status IN ('aktif','disetujui','pengajuan')) WHERE id = ?", [$nasabah_id, $nasabah_id]);
            $result = query("SELECT * FROM pinjaman WHERE id = ?", [$new_id]);
            $new_pinjaman = is_array($result) && isset($result[0]) ? $result[0] : null;
            echo json_encode([
                'success' => true,
                'message' => 'Pengajuan pinjaman berhasil dibuat',
                'data' => $new_pinjaman,
                'warnings' => $layak['warnings'] ?? []
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Gagal membuat pengajuan pinjaman']);
        }
        break;
        
    case 'PUT':
        // Update pinjaman (approve/reject/update jaminan status)
        $input = json_decode(file_get_contents('php://input'), true);
        $pinjaman_id = $_GET['id'] ?? $input['id'] ?? '';
        $action = $_GET['action'] ?? '';
        
        // Handle jaminan_status update directly from body
        if (isset($input['jaminan_status'])) {
            $jaminan_status = $input['jaminan_status'];
            $catatan_status = $input['catatan_status'] ?? '';
            
            if (!$pinjaman_id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'ID required']);
                break;
            }
            
            $valid_statuses = ['aktif', 'dilepas', 'terjual', 'hilang'];
            if (!in_array($jaminan_status, $valid_statuses)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid jaminan_status']);
                break;
            }
            
            $result = query("UPDATE pinjaman SET jaminan_status = ? WHERE id = ?", 
                [$jaminan_status, $pinjaman_id]);
            
            if ($result) {
                // Log the update
                logAudit('update', 'pinjaman', $pinjaman_id, null, ['jaminan_status' => $jaminan_status, 'catatan' => $catatan_status]);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Status jaminan berhasil diupdate'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Gagal mengupdate status jaminan']);
            }
            break;
        }
        
        if (!$pinjaman_id || !$action) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID and action required']);
            break;
        }
        
        // Get pinjaman data
        $pinjaman = query("SELECT * FROM pinjaman WHERE id = ?", [$pinjaman_id]);
        if (!$pinjaman) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Pinjaman tidak ditemukan']);
            break;
        }
        
        $pinjaman = $pinjaman[0];
        
        switch ($action) {
            case 'approve':
                if ($pinjaman['status'] !== 'pengajuan') {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'Hanya dapat menyetujui pinjaman dengan status pengajuan']);
                    break;
                }
                
                // Update status + catat approved_by
                $result = query("UPDATE pinjaman SET status = 'disetujui', approved_by = ?, approved_at = NOW() WHERE id = ?", [$user['id'], $pinjaman_id]);

                if ($result) {
                    // Create loan schedule
                    $frek = $pinjaman['frekuensi_id'] ?? $pinjaman['frekuensi'] ?? 'bulanan';
                    createLoanSchedule($pinjaman_id, $pinjaman['plafon'], $pinjaman['tenor'], $pinjaman['bunga_per_bulan'], $pinjaman['tanggal_akad'], $frek);

                    // Update to aktif + set sisa_pokok_berjalan + tanggal_lunas_awal
                    query("UPDATE pinjaman SET status = 'aktif', sisa_pokok_berjalan = ?, tanggal_lunas_awal = ? WHERE id = ?",
                        [$pinjaman['plafon'], $pinjaman['tanggal_jatuh_tempo'], $pinjaman_id]);

                    // Update cache total_pinjaman_aktif nasabah
                    query("UPDATE nasabah SET total_pinjaman_aktif = (SELECT COUNT(*) FROM pinjaman WHERE nasabah_id = ? AND status IN ('aktif','disetujui','pengajuan')) WHERE id = ?",
                        [$pinjaman['nasabah_id'], $pinjaman['nasabah_id']]);

                    // Post accounting journal entry (pencairan)
                    postJurnalPinjaman($pinjaman_id, $kantor_id);

                    // Jurnal kas keluar untuk pencairan
                    catatJurnalKas(
                        $pinjaman['cabang_id'] ?? 1, 'keluar', 'pencairan',
                        'pinjaman', $pinjaman_id, $pinjaman['plafon'],
                        "Pencairan pinjaman {$pinjaman['kode_pinjaman']}", $user['id']
                    );

                    // Trigger webhook for pinjaman approval
                    require_once BASE_PATH . '/includes/webhook_trigger.php';
                    triggerPinjamanApproved($pinjaman_id, $pinjaman);

                    // ── WA Notifikasi Integration ─────────────────────
                    if (isFeatureEnabled('wa_notifikasi')) {
                        require_once BASE_PATH . '/includes/wa_notifikasi.php';
                        $nasabah_info = query("SELECT nama, telepon FROM nasabah WHERE id = ?", [$pinjaman['nasabah_id']]);
                        if (!empty($nasabah_info[0]['telepon'])) {
                            $plafon_fmt = 'Rp ' . number_format($pinjaman['plafon'], 0, ',', '.');
                            $pesan = "Yth. *{$nasabah_info[0]['nama']}*,\n\nSelamat! Pengajuan pinjaman Anda telah disetujui ✅\n\nDetail:\n• Kode: {$pinjaman['kode_pinjaman']}\n• Plafon: *{$plafon_fmt}*\n• Tenor: {$pinjaman['tenor']} bulan\n• Tanggal Akad: " . date('d/m/Y', strtotime($pinjaman['tanggal_akad'])) . "\n\nDana akan segera dicairkan. Silakan siapkan dokumen yang diperlukan.\n\n*Kewer Koperasi*";
                            kirimWA($nasabah_info[0]['telepon'], $pesan, $pinjaman['nasabah_id'], $user['id'], 'approval_pinjaman');
                        }
                    }
                    // ─────────────────────────────────────────────────────

                    echo json_encode(['success' => true, 'message' => 'Pinjaman berhasil disetujui dan diaktifkan']);
                } else {
                    http_response_code(500);
                    echo json_encode(['success' => false, 'error' => 'Gagal menyetujui pinjaman']);
                }
                break;
                
            case 'reject':
                if ($pinjaman['status'] !== 'pengajuan') {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'Hanya dapat menolak pinjaman dengan status pengajuan']);
                    break;
                }
                $rejection_reason = $input['reason'] ?? $input['rejection_reason'] ?? null;
                $result = query("UPDATE pinjaman SET status = 'ditolak', rejected_by = ?, rejected_at = NOW(), rejection_reason = ? WHERE id = ?",
                    [$user['id'], $rejection_reason, $pinjaman_id]);
                // Update cache nasabah
                query("UPDATE nasabah SET total_pinjaman_aktif = (SELECT COUNT(*) FROM pinjaman WHERE nasabah_id = ? AND status IN ('aktif','disetujui','pengajuan')) WHERE id = ?",
                    [$pinjaman['nasabah_id'], $pinjaman['nasabah_id']]);
                
                // Trigger webhook for pinjaman rejection
                require_once BASE_PATH . '/includes/webhook_trigger.php';
                triggerPinjamanRejected($pinjaman_id, $pinjaman, $rejection_reason);
                
                echo json_encode(['success' => true, 'message' => 'Pinjaman ditolak']);
                break;
                
            default:
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid action']);
                break;
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?>
