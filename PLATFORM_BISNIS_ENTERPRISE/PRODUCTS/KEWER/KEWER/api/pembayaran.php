<?php
/**
 * API: Pembayaran (Payment)
 * 
 * Endpoints for managing payment data
 */

// Suppress errors to ensure JSON output
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

try {
    require_once __DIR__ . '/../config/path.php';
    require_once BASE_PATH . '/includes/functions.php';
    require_once BASE_PATH . '/includes/business_logic.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Configuration error: ' . $e->getMessage()]);
    exit();
}

// Authentication check
requireLogin();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Get pembayaran list
        $angsuran_id = $_GET['angsuran_id'] ?? null;
        $search = $_GET['search'] ?? '';
        $tanggal_mulai = $_GET['tanggal_mulai'] ?? null;
        $tanggal_selesai = $_GET['tanggal_selesai'] ?? null;
        
        $where = [];
        $params = [];
        
        if ($angsuran_id) {
            $where[] = "p.angsuran_id = ?";
            $params[] = $angsuran_id;
        }
        
        if ($search) {
            $where[] = "(p.kode_pembayaran LIKE ? OR n.nama LIKE ? OR n.kode_nasabah LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        if ($tanggal_mulai) {
            $where[] = "p.tanggal_bayar >= ?";
            $params[] = $tanggal_mulai;
        }
        
        if ($tanggal_selesai) {
            $where[] = "p.tanggal_bayar <= ?";
            $params[] = $tanggal_selesai;
        }
        
        // Isolasi data: hanya tampilkan pembayaran dari cabang milik bos yang login
        $cabang_filter = buildCabangFilter('p.cabang_id');
        if ($cabang_filter) {
            $where[] = ltrim($cabang_filter['clause'], 'AND ');
            $params  = array_merge($params, $cabang_filter['params']);
        }

        $where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        $pembayaran = query("
            SELECT p.*, a.jatuh_tempo, a.total_angsuran as nominal, n.nama, n.kode_nasabah, pin.kode_pinjaman
            FROM pembayaran p
            LEFT JOIN angsuran a ON p.angsuran_id = a.id
            LEFT JOIN pinjaman pin ON a.pinjaman_id = pin.id
            LEFT JOIN nasabah n ON pin.nasabah_id = n.id
            $where_clause 
            ORDER BY p.tanggal_bayar DESC, p.created_at DESC
        ", $params);
        
        if (!is_array($pembayaran)) $pembayaran = [];
        
        echo json_encode([
            'success' => true,
            'data' => $pembayaran,
            'total' => count($pembayaran)
        ]);
        break;
        
    case 'POST':
        // Create new pembayaran with denda support
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Permission check
        if (!hasPermission('manage_pembayaran')) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden - No permission to manage pembayaran']);
            exit();
        }
        
        // Validation
        if (!$input['angsuran_id']) {
            http_response_code(400);
            echo json_encode(['error' => 'angsuran_id is required']);
            exit();
        }
        
        // Get angsuran data with loan info
        $angsuran = query("
            SELECT a.*, p.frekuensi_id, p.nasabah_id, p.id as pinjaman_id
            FROM angsuran a 
            JOIN pinjaman p ON a.pinjaman_id = p.id 
            WHERE a.id = ?", [$input['angsuran_id']]);
        
        if (!is_array($angsuran) || empty($angsuran)) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Angsuran tidak ditemukan']);
            break;
        }
        
        // Use frekuensi_id
        $frekuensi_value = $angsuran['frekuensi_id'];
        $frekuensi_code = getFrequencyCode($frekuensi_value);
        
        // Calculate denda if not provided
        $tanggal_bayar = $input['tanggal_bayar'] ?? date('Y-m-d');
        $hari_telat = max(0, (strtotime($tanggal_bayar) - strtotime($angsuran['jatuh_tempo'])) / 86400);
        
        // Get denda settings
        $denda_settings = query("SELECT * FROM setting_denda WHERE frekuensi_id = ? AND is_active = 1", 
            [$frekuensi_value]);
        $denda_settings = (is_array($denda_settings) && !empty($denda_settings)) ? $denda_settings[0] : null;
        
        $grace_period = $denda_settings['grace_period'] ?? 0;
        $hari_telat_efektif = max(0, $hari_telat - $grace_period);
        
        // Calculate denda
        $denda_terhitung = 0;
        if ($hari_telat_efektif > 0 && $denda_settings) {
            if ($denda_settings['tipe_denda'] == 'persentase') {
                $denda_per_period = $angsuran['total_angsuran'] * ($denda_settings['nilai_denda'] / 100);
                if ($frekuensi_code == 'HARIAN' || $frekuensi_code == 'harian') {
                    $denda_per_hari = $denda_per_period;
                } elseif ($frekuensi_code == 'MINGGUAN' || $frekuensi_code == 'mingguan') {
                    $denda_per_hari = $denda_per_period / 7;
                } else {
                    $denda_per_hari = $denda_per_period / 30;
                }
                $denda_terhitung = $denda_per_hari * $hari_telat_efektif;
            } else {
                $denda_terhitung = $denda_settings['nilai_denda'] * $hari_telat_efektif;
            }
            
            // Apply max denda if set
            if ($denda_settings['denda_maksimal'] && $denda_terhitung > $denda_settings['denda_maksimal']) {
                $denda_terhitung = $denda_settings['denda_maksimal'];
            }
        }
        
        // Use provided denda if exists (from form calculation)
        $denda_dibayar = isset($input['denda_terhitung']) ? floatval($input['denda_terhitung']) : $denda_terhitung;
        
        // Handle waived denda
        $denda_dibebaskan = floatval($input['denda_dibebaskan'] ?? 0);
        if ($denda_dibebaskan > $denda_dibayar) {
            $denda_dibebaskan = $denda_dibayar;
        }
        $denda_dibayar -= $denda_dibebaskan;
        
        // Calculate total payment
        $total_pembayaran = $angsuran['total_angsuran'] + $denda_dibayar;
        
        // Validate payment amount
        $jumlah_diterima = floatval($input['jumlah_bayar'] ?? $total_pembayaran);
        if ($jumlah_diterima < $angsuran['total_angsuran']) {
            http_response_code(400);
            echo json_encode(['error' => 'Jumlah pembayaran kurang dari angsuran pokok']);
            exit();
        }
        
        // Generate kode pembayaran
        $kode_pembayaran = generateKode('BYR', 'pembayaran', 'kode_pembayaran');
        
        // Start transaction
        query("START TRANSACTION");
        
        try {
            // Insert pembayaran
            $lat = isset($input['lat']) && is_numeric($input['lat']) ? floatval($input['lat']) : null;
            $lng = isset($input['lng']) && is_numeric($input['lng']) ? floatval($input['lng']) : null;
            $akurasi_gps = isset($input['akurasi_gps']) ? (int)$input['akurasi_gps'] : null;

            $result = query("
                INSERT INTO pembayaran (
                    pinjaman_id, angsuran_id, kode_pembayaran, tanggal_bayar,
                    jumlah_bayar, denda, denda_dibayar, denda_waived, total_bayar, total_pembayaran,
                    keterangan, petugas_id, cara_bayar, cabang_id, lat, lng, akurasi_gps
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", [
                $angsuran['pinjaman_id'],
                $input['angsuran_id'],
                $kode_pembayaran,
                $tanggal_bayar,
                $jumlah_diterima,
                $denda_dibayar,
                $denda_dibayar,
                $denda_dibebaskan,
                $total_pembayaran,
                $total_pembayaran,
                $input['keterangan'] ?? '',
                getCurrentUser()['id'],
                $input['cara_bayar'] ?? 'tunai',
                1, // Single office structure
                $lat, $lng, $akurasi_gps
            ]);
            
            if (!$result) {
                throw new Exception('Failed to insert pembayaran');
            }
            
            $pembayaran_id = query("SELECT LAST_INSERT_ID() as id")[0]['id'];
            
            // Update angsuran
            $update_angsuran = query("
                UPDATE angsuran SET 
                    status = 'lunas',
                    tanggal_bayar = ?,
                    cara_bayar = ?,
                    denda_terhitung = ?,
                    denda_dibebaskan = ?,
                    hari_telat_saat_bayar = ?,
                    total_bayar_akhir = ?
                WHERE id = ?", [
                $tanggal_bayar,
                $input['cara_bayar'] ?? 'tunai',
                $denda_terhitung,
                $denda_dibebaskan,
                $hari_telat,
                $total_pembayaran,
                $input['angsuran_id']
            ]);
            
            if (!$update_angsuran) {
                throw new Exception('Failed to update angsuran');
            }
            
            // Check if all angsuran are paid (loan lunas)
            $unpaid = query("
                SELECT COUNT(*) as count FROM angsuran 
                WHERE pinjaman_id = ? AND status != 'lunas'", 
                [$angsuran['pinjaman_id']]);
            
            if ($unpaid[0]['count'] == 0) {
                // Mark loan as lunas
                query("
                    UPDATE pinjaman SET status = 'lunas', tanggal_lunas = ? WHERE id = ?",
                    [$tanggal_bayar, $angsuran['pinjaman_id']]
                );
            }
            
            // ── Penagihan System Integration ─────────────────────
            // Update penagihan status if exists
            $existing_penagihan = query("SELECT id, status FROM penagihan WHERE angsuran_id = ? AND status IN ('pending', 'dalam_proses')", [$input['angsuran_id']]);
            if ($existing_penagihan && is_array($existing_penagahan) && !empty($existing_penagahan)) {
                $penagihan_id = $existing_penagihan[0]['id'];
                query("UPDATE penagihan SET status = 'berhasil', tanggal_penagihan = ?, hasil = 'Pembayaran berhasil diterima', petugas_id = ?, updated_at = NOW() WHERE id = ?", 
                    [$tanggal_bayar, getCurrentUser()['id'], $penagihan_id]);
                
                // Log penagihan activity
                query("INSERT INTO penagihan_log (penagihan_id, aksi, hasil, petugas_id) VALUES (?, 'pembayaran', 'Pembayaran diterima: Rp " . formatRupiah($total_pembayaran) . "', ?)", 
                    [$penagihan_id, getCurrentUser()['id']]);
            } else {
                // Create penagihan record if doesn't exist
                $jenis_penagihan_id = 1; // Default to JATUH_TEMPO
                query("INSERT INTO penagihan (pinjaman_id, angsuran_id, jenis_penagihan_id, status, tanggal_jatuh_tempo, tanggal_penagihan, petugas_id, hasil) VALUES (?, ?, ?, 'berhasil', ?, ?, ?, 'Pembayaran berhasil diterima')", 
                    [$angsuran['pinjaman_id'], $input['angsuran_id'], $jenis_penagihan_id, $angsuran['jatuh_tempo'], $tanggal_bayar, getCurrentUser()['id']]);
                
                $new_penagihan_id = query("SELECT LAST_INSERT_ID() as id")[0]['id'];
                query("INSERT INTO penagihan_log (penagihan_id, aksi, hasil, petugas_id) VALUES (?, 'pembayaran', 'Pembayaran diterima: Rp " . formatRupiah($total_pembayaran) . "', ?)", 
                    [$new_penagihan_id, getCurrentUser()['id']]);
            }
            // ─────────────────────────────────────────────────────
            
            // Post accounting journal entry
            if (function_exists('postJurnalPembayaran')) {
                postJurnalPembayaran($pembayaran_id, 1);
            }

            // ── Business logic hooks ──────────────────────────────
            // 1. Jurnal kas otomatis
            catatJurnalKas(
                $angsuran['cabang_id'] ?? 1, 'masuk', 'angsuran',
                'pembayaran', $pembayaran_id, $total_pembayaran,
                "Angsuran pinjaman {$angsuran['pinjaman_id']}",
                getCurrentUser()['id']
            );

            // 2. Update skor kredit nasabah
            $nasabah_id_for_score = query("SELECT nasabah_id FROM pinjaman WHERE id = ?", [$angsuran['pinjaman_id']]);
            if ($nasabah_id_for_score) {
                $delta_skor = $hari_telat > 0 ? max(-5, -1 * (int)ceil($hari_telat / 7)) : +1;
                $alasan_skor = $hari_telat > 0 ? 'bayar_telat' : 'bayar_tepat_waktu';
                updateSkorKredit($nasabah_id_for_score[0]['nasabah_id'], $delta_skor, $alasan_skor, $pembayaran_id);
            }

            // 3. Deteksi kelebihan bayar
            $kelebihan = $jumlah_diterima - $total_pembayaran;
            if ($kelebihan > 0 && $nasabah_id_for_score) {
                prosesKelebihanBayar($nasabah_id_for_score[0]['nasabah_id'], $angsuran['pinjaman_id'], $pembayaran_id, $kelebihan, getCurrentUser()['id']);
            }

            // 4. Update sisa pokok berjalan di pinjaman
            $sisa_query = query("SELECT SUM(pokok) as sp FROM angsuran WHERE pinjaman_id = ? AND status = 'lunas'", [$angsuran['pinjaman_id']]);
            $pokok_lunas = floatval($sisa_query[0]['sp'] ?? 0);
            $plafon_query = query("SELECT plafon FROM pinjaman WHERE id = ?", [$angsuran['pinjaman_id']]);
            if ($plafon_query) {
                $sisa_pokok_berjalan = floatval($plafon_query[0]['plafon']) - $pokok_lunas;
                query("UPDATE pinjaman SET sisa_pokok_berjalan = ? WHERE id = ?", [$sisa_pokok_berjalan, $angsuran['pinjaman_id']]);
            }
            // ─────────────────────────────────────────────────────

            query("COMMIT");

            // Trigger webhook for pembayaran received
            require_once BASE_PATH . '/includes/webhook_trigger.php';
            $nasabah_info = query("SELECT n.nama, n.id, n.telepon FROM pinjaman pin JOIN nasabah n ON pin.nasabah_id = n.id WHERE pin.id = ?", [$angsuran['pinjaman_id']]);
            $pembayaran_data = [
                'nasabah_id' => $nasabah_info[0]['id'] ?? null,
                'nama_nasabah' => $nasabah_info[0]['nama'] ?? '',
                'pinjaman_id' => $angsuran['pinjaman_id'],
                'cabang_id' => 1,
                'petugas_id' => getCurrentUser()['id'],
                'latitude' => $lat,
                'longitude' => $lng,
                'nominal' => $total_pembayaran,
                'tanggal_bayar' => $tanggal_bayar
            ];
            triggerPembayaranReceived($pembayaran_id, $pembayaran_data);

            // ── WA Notifikasi Integration ─────────────────────
            if (isFeatureEnabled('wa_notifikasi') && !empty($nasabah_info[0]['telepon'])) {
                require_once BASE_PATH . '/includes/wa_notifikasi.php';
                $pinjaman_info = query("SELECT kode_pinjaman FROM pinjaman WHERE id = ?", [$angsuran['pinjaman_id']]);
                $pesan = templateWaKonfirmasiBayar(
                    ['nama' => $nasabah_info[0]['nama']],
                    ['kode_pinjaman' => $pinjaman_info[0]['kode_pinjaman'] ?? ''],
                    [
                        'kode_pembayaran' => $kode_pembayaran,
                        'total_bayar' => $total_pembayaran,
                        'tanggal_bayar' => $tanggal_bayar
                    ]
                );
                kirimWA($nasabah_info[0]['telepon'], $pesan, $nasabah_info[0]['id'], getCurrentUser()['id'], 'konfirmasi_bayar');
            }
            // ─────────────────────────────────────────────────────

            $new_pembayaran = query("
                SELECT p.*, a.no_angsuran, pin.kode_pinjaman, n.nama as nasabah_nama
                FROM pembayaran p
                JOIN angsuran a ON p.angsuran_id = a.id
                JOIN pinjaman pin ON a.pinjaman_id = pin.id
                JOIN nasabah n ON pin.nasabah_id = n.id
                WHERE p.id = ?", [$pembayaran_id])[0];
            
            echo json_encode([
                'success' => true, 
                'message' => 'Pembayaran berhasil disimpan',
                'data' => $new_pembayaran,
                'summary' => [
                    'angsuran' => $angsuran['total_angsuran'],
                    'denda_terhitung' => $denda_terhitung,
                    'denda_dibebaskan' => $denda_dibebaskan,
                    'denda_dibayar' => $denda_dibayar,
                    'total_pembayaran' => $total_pembayaran
                ]
            ]);
            
        } catch (Exception $e) {
            query("ROLLBACK");
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;
        
    case 'PUT':
        // Update pembayaran
        $pembayaran_id = $_GET['id'] ?? null;
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$pembayaran_id) {
            http_response_code(400);
            echo json_encode(['error' => 'pembayaran_id is required']);
            exit();
        }
        
        // Permission check
        if (!hasPermission('manage_pembayaran')) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden - No permission to manage pembayaran']);
            exit();
        }
        
        // Get existing pembayaran
        $existing = query("SELECT * FROM pembayaran WHERE id = ?", [$pembayaran_id]);
        if (!$existing) {
            http_response_code(404);
            echo json_encode(['error' => 'Pembayaran not found']);
            exit();
        }
        $existing = $existing[0];
        
        // Build update query
        $fields = [];
        $params = [];
        
        if (isset($input['tanggal_bayar'])) {
            $fields[] = "tanggal_bayar = ?";
            $params[] = $input['tanggal_bayar'];
        }
        if (isset($input['jumlah_bayar'])) {
            $fields[] = "jumlah_bayar = ?";
            $params[] = $input['jumlah_bayar'];
        }
        if (isset($input['denda'])) {
            $fields[] = "denda = ?";
            $params[] = $input['denda'];
        }
        if (isset($input['keterangan'])) {
            $fields[] = "keterangan = ?";
            $params[] = $input['keterangan'];
        }
        
        if (empty($fields)) {
            http_response_code(400);
            echo json_encode(['error' => 'No fields to update']);
            exit();
        }
        
        $fields[] = "updated_at = CURRENT_TIMESTAMP";
        $params[] = $pembayaran_id;
        
        $sql = "UPDATE pembayaran SET " . implode(', ', $fields) . " WHERE id = ?";
        $result = query($sql, $params);
        
        if ($result) {
            // Recalculate angsuran status
            $angsuran_id = $existing['angsuran_id'];
            $result_total = query("SELECT COALESCE(SUM(jumlah_bayar), 0) as total FROM pembayaran WHERE angsuran_id = ?", [$angsuran_id]);
            $total_paid = is_array($result_total) && isset($result_total[0]) ? $result_total[0]['total'] : 0;
            $angsuran_data = query("SELECT total_angsuran FROM angsuran WHERE id = ?", [$angsuran_id]);
            $angsuran_nominal = $angsuran_data ? $angsuran_data[0]['total_angsuran'] : 0;
            if ($total_paid >= $angsuran_nominal) {
                query("UPDATE angsuran SET status = 'lunas' WHERE id = ?", [$angsuran_id]);
            } else {
                query("UPDATE angsuran SET status = 'pending' WHERE id = ?", [$angsuran_id]);
            }
            
            $result = query("SELECT * FROM pembayaran WHERE id = ?", [$pembayaran_id]);
            $updated_pembayaran = is_array($result) && isset($result[0]) ? $result[0] : null;
            echo json_encode(['success' => true, 'data' => $updated_pembayaran]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update pembayaran']);
        }
        break;
        
    case 'DELETE':
        // Delete pembayaran
        $pembayaran_id = $_GET['id'] ?? null;
        
        if (!$pembayaran_id) {
            http_response_code(400);
            echo json_encode(['error' => 'pembayaran_id is required']);
            exit();
        }
        
        // Permission check
        if (!hasPermission('manage_pembayaran')) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden - No permission to manage pembayaran']);
            exit();
        }
        
        // Get existing pembayaran
        $existing = query("SELECT * FROM pembayaran WHERE id = ?", [$pembayaran_id]);
        if (!$existing) {
            http_response_code(404);
            echo json_encode(['error' => 'Pembayaran not found']);
            exit();
        }
        $existing = $existing[0];
        
        // Soft delete
        $result = query("DELETE FROM pembayaran WHERE id = ?", [$pembayaran_id]);
        
        if ($result) {
            // Recalculate angsuran status
            $angsuran_id = $existing['angsuran_id'];
            $total_paid = query("SELECT COALESCE(SUM(jumlah_bayar), 0) as total FROM pembayaran WHERE angsuran_id = ?", [$angsuran_id])[0]['total'];
            if ($total_paid > 0) {
                query("UPDATE angsuran SET status = 'pending' WHERE id = ?", [$angsuran_id]);
            } else {
                query("UPDATE angsuran SET status = 'belum_bayar' WHERE id = ?", [$angsuran_id]);
            }
            
            echo json_encode(['success' => true, 'message' => 'Pembayaran deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete pembayaran']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
