<?php
/**
 * API: Business Logic Endpoint
 * Handles: restrukturisasi, write-off, pelunasan dipercepat,
 *          nasabah meninggal, pengganti petugas, verifikasi kas,
 *          kelebihan bayar, notifikasi, offline queue
 *
 * POST /api/business.php
 * Body: { "action": "...", ...params }
 */

error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit(); }

try {
    require_once __DIR__ . '/../config/path.php';
    require_once BASE_PATH . '/includes/functions.php';
    require_once BASE_PATH . '/includes/business_logic.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Configuration error: ' . $e->getMessage()]);
    exit();
}

requireLogin();
$user   = getCurrentUser();
$method = $_SERVER['REQUEST_METHOD'];

// ── GET: ambil data ───────────────────────────────────────────────
if ($method === 'GET') {
    $action = $_GET['action'] ?? '';

    switch ($action) {
        // Hitung simulasi pelunasan dipercepat
        case 'hitung_lunas_dipercepat':
            $pinjaman_id = (int)($_GET['pinjaman_id'] ?? 0);
            if (!$pinjaman_id) { http_response_code(400); echo json_encode(['error' => 'pinjaman_id wajib']); exit(); }
            echo json_encode(hitungPelunasanDipercepat($pinjaman_id, $_GET['tanggal'] ?? null));
            break;

        // Cek kelayakan nasabah untuk pinjaman baru
        case 'cek_kelayakan_nasabah':
            $nasabah_id  = (int)($_GET['nasabah_id'] ?? 0);
            $owner_bos_id = getOwnerBosId();
            echo json_encode(cekKelayakanNasabah($nasabah_id, $owner_bos_id));
            break;

        // Notifikasi user yang login
        case 'notifikasi':
            $bos_cabang = getBosOwnedCabangIds();
            $cabang_id  = $user['cabang_id'] ?? ($bos_cabang[0] ?? null);
            $limit      = min((int)($_GET['limit'] ?? 20), 50);
            echo json_encode([
                'success' => true,
                'data'    => getNotifikasiUser($user['id'], $user['role'], $cabang_id, $limit),
            ]);
            break;

        // Antrian offline petugas
        case 'offline_queue':
            if (!hasPermission('manage_pembayaran')) { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit(); }
            $petugas_id = (int)($_GET['petugas_id'] ?? $user['id']);
            $status     = $_GET['status'] ?? 'pending';
            $rows = query("SELECT oq.*, n.nama as nama_nasabah, p.kode_pinjaman FROM pembayaran_offline_queue oq
                JOIN pinjaman p ON oq.pinjaman_id = p.id JOIN nasabah n ON p.nasabah_id = n.id
                WHERE oq.petugas_id = ? AND oq.status = ? ORDER BY oq.tanggal_kutip DESC",
                [$petugas_id, $status]) ?: [];
            echo json_encode(['success' => true, 'data' => $rows]);
            break;

        // Kelebihan bayar
        case 'kelebihan_bayar':
            if (!hasPermission('manage_pembayaran') && !hasPermission('view_laporan')) { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit(); }
            $cabang_filter = buildCabangFilter('n.cabang_id');
            $where = $cabang_filter ? "WHERE " . ltrim($cabang_filter['clause'], 'AND ') : "";
            $params = $cabang_filter ? $cabang_filter['params'] : [];
            $rows = query("SELECT kb.*, n.nama as nama_nasabah, n.kode_nasabah FROM kelebihan_bayar kb
                JOIN nasabah n ON kb.nasabah_id = n.id $where ORDER BY kb.created_at DESC", $params) ?: [];
            echo json_encode(['success' => true, 'data' => $rows]);
            break;

        // Riwayat skor kredit nasabah
        case 'skor_kredit':
            $nasabah_id = (int)($_GET['nasabah_id'] ?? 0);
            if (!$nasabah_id) { http_response_code(400); echo json_encode(['error' => 'nasabah_id wajib']); exit(); }
            $rows = query("SELECT * FROM riwayat_skor_kredit WHERE nasabah_id = ? ORDER BY created_at DESC LIMIT 30", [$nasabah_id]) ?: [];
            echo json_encode(['success' => true, 'data' => $rows]);
            break;

        // Billing plans for settings page
        case 'billing_plans':
            if ($user['role'] !== 'appOwner') { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit(); }
            $plans = query("SELECT * FROM billing_plans ORDER BY harga_bulanan") ?: [];
            echo json_encode(['success' => true, 'data' => $plans]);
            break;

        // Bank accounts for settings page
        case 'bank_accounts':
            if ($user['role'] !== 'appOwner') { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit(); }
            $accounts = query("SELECT * FROM platform_bank_accounts ORDER BY is_primary DESC, created_at DESC") ?: [];
            echo json_encode(['success' => true, 'data' => $accounts]);
            break;

        // Platform stats for settings page
        case 'platform_stats':
            if ($user['role'] !== 'appOwner') { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit(); }
            $total_koperasi = query("SELECT COUNT(*) as c FROM bos_registrations WHERE status = 'approved'");
            $total_koperasi = (is_array($total_koperasi) && isset($total_koperasi[0])) ? (int)$total_koperasi[0]['c'] : 0;
            $total_invoices = query("SELECT COUNT(*) as c FROM koperasi_invoices");
            $total_invoices = (is_array($total_invoices) && isset($total_invoices[0])) ? (int)$total_invoices[0]['c'] : 0;
            $total_advice = query("SELECT COUNT(*) as c FROM ai_advice");
            $total_advice = (is_array($total_advice) && isset($total_advice[0])) ? (int)$total_advice[0]['c'] : 0;
            echo json_encode(['success' => true, 'data' => [
                'total_koperasi' => $total_koperasi,
                'total_invoices' => $total_invoices,
                'total_advice' => $total_advice
            ]]);
            break;

        // App owner dashboard stats
        case 'app_owner_dashboard':
            if ($user['role'] !== 'appOwner') { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit(); }
            
            $total_pending = query("SELECT COUNT(*) as c FROM bos_registrations WHERE status = 'pending'");
            $total_pending = (is_array($total_pending) && isset($total_pending[0])) ? (int)$total_pending[0]['c'] : 0;
            
            $total_approved = query("SELECT COUNT(*) as c FROM bos_registrations WHERE status = 'approved'");
            $total_approved = (is_array($total_approved) && isset($total_approved[0])) ? (int)$total_approved[0]['c'] : 0;
            
            $total_bos = query("SELECT COUNT(*) as c FROM users WHERE role = 'bos' AND status = 'aktif'");
            $total_bos = (is_array($total_bos) && isset($total_bos[0])) ? (int)$total_bos[0]['c'] : 0;
            
            $total_users = query("SELECT COUNT(*) as c FROM users WHERE role != 'appOwner' AND status = 'aktif'");
            $total_users = (is_array($total_users) && isset($total_users[0])) ? (int)$total_users[0]['c'] : 0;
            
            $total_revenue = query("SELECT COALESCE(SUM(total),0) as c FROM koperasi_invoices WHERE status = 'dibayar'");
            $total_revenue = (is_array($total_revenue) && isset($total_revenue[0])) ? (float)$total_revenue[0]['c'] : 0;
            
            $overdue_invoices = query("SELECT COUNT(*) as c FROM koperasi_invoices WHERE status = 'overdue'");
            $overdue_invoices = (is_array($overdue_invoices) && isset($overdue_invoices[0])) ? (int)$overdue_invoices[0]['c'] : 0;
            
            $today_usage = query("SELECT COALESCE(SUM(total_api_calls),0) as api, COALESCE(SUM(total_renders),0) as renders FROM usage_daily_summary WHERE tanggal = CURDATE()");
            $today_api = (is_array($today_usage) && isset($today_usage[0])) ? (int)$today_usage[0]['api'] : 0;
            $today_renders = (is_array($today_usage) && isset($today_usage[0])) ? (int)$today_usage[0]['renders'] : 0;
            
            $recent = query("SELECT id, username, nama, nama_usaha, status, created_at FROM bos_registrations ORDER BY created_at DESC LIMIT 10");
            if (!is_array($recent)) $recent = [];
            
            $recent_advice = query("SELECT id, judul, kategori, prioritas, created_at FROM ai_advice ORDER BY created_at DESC LIMIT 5");
            if (!is_array($recent_advice)) $recent_advice = [];
            
            echo json_encode(['success' => true, 'data' => [
                'stats' => [
                    'total_pending' => $total_pending,
                    'total_approved' => $total_approved,
                    'total_bos' => $total_bos,
                    'total_users' => $total_users,
                    'total_revenue' => $total_revenue,
                    'overdue_invoices' => $overdue_invoices,
                    'today_api' => $today_api,
                    'today_renders' => $today_renders
                ],
                'recent_registrations' => $recent,
                'recent_advice' => $recent_advice
            ]]);
            break;

        // Get bos registrations for approvals page
        case 'bos_registrations':
            if ($user['role'] !== 'appOwner') { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit(); }
            
            $filter = $_GET['status'] ?? 'pending';
            $valid_filters = ['pending', 'approved', 'rejected', 'all'];
            if (!in_array($filter, $valid_filters)) $filter = 'pending';
            
            if ($filter === 'all') {
                $registrations = query("SELECT * FROM bos_registrations ORDER BY created_at DESC");
            } else {
                $registrations = query("SELECT * FROM bos_registrations WHERE status = ? ORDER BY created_at DESC", [$filter]);
            }
            if (!is_array($registrations)) $registrations = [];
            
            $cnt_pending = query("SELECT COUNT(*) as c FROM bos_registrations WHERE status = 'pending'");
            $cnt_pending = (is_array($cnt_pending) && isset($cnt_pending[0])) ? (int)$cnt_pending[0]['c'] : 0;
            $cnt_approved = query("SELECT COUNT(*) as c FROM bos_registrations WHERE status = 'approved'");
            $cnt_approved = (is_array($cnt_approved) && isset($cnt_approved[0])) ? (int)$cnt_approved[0]['c'] : 0;
            $cnt_rejected = query("SELECT COUNT(*) as c FROM bos_registrations WHERE status = 'rejected'");
            $cnt_rejected = (is_array($cnt_rejected) && isset($cnt_rejected[0])) ? (int)$cnt_rejected[0]['c'] : 0;
            
            echo json_encode(['success' => true, 'data' => [
                'registrations' => $registrations,
                'stats' => [
                    'pending' => $cnt_pending,
                    'approved' => $cnt_approved,
                    'rejected' => $cnt_rejected
                ]
            ]]);
            break;

        // Get billing data for billing page
        case 'billing_data':
            if ($user['role'] !== 'appOwner') { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit(); }
            
            $filter_status = $_GET['status'] ?? 'all';
            $filter_month = (int)($_GET['bulan'] ?? date('n'));
            $filter_year = (int)($_GET['tahun'] ?? date('Y'));
            
            $where = "WHERE ki.periode_bulan = $filter_month AND ki.periode_tahun = $filter_year";
            if ($filter_status !== 'all') {
                $where .= " AND ki.status = '" . addslashes($filter_status) . "'";
            }
            
            $invoices = query("
                SELECT ki.*, br.nama_usaha, bp.nama as plan_nama, bp.tipe as plan_tipe, u.nama as bos_nama
                FROM koperasi_invoices ki
                JOIN koperasi_billing kb ON kb.id = ki.koperasi_billing_id
                JOIN billing_plans bp ON bp.id = kb.billing_plan_id
                JOIN users u ON u.id = ki.bos_user_id
                LEFT JOIN bos_registrations br ON br.username = u.username
                $where
                ORDER BY ki.created_at DESC
            ");
            if (!is_array($invoices)) $invoices = [];
            
            // Summary
            $sum_total = 0; $sum_paid = 0;
            foreach ($invoices as $inv) {
                $sum_total += (float)$inv['total'];
                if ($inv['status'] === 'dibayar') $sum_paid += (float)$inv['total'];
            }
            
            // Billing plans summary
            $plans = query("SELECT bp.*, COUNT(kb.id) as subscribers FROM billing_plans bp LEFT JOIN koperasi_billing kb ON kb.billing_plan_id = bp.id AND kb.status = 'aktif' WHERE bp.is_active = 1 GROUP BY bp.id ORDER BY bp.harga_bulanan");
            if (!is_array($plans)) $plans = [];
            
            // Get primary bank account
            $primary_bank = query("SELECT * FROM platform_bank_accounts WHERE is_primary = 1 AND is_active = 1 LIMIT 1");
            $primary_bank = (is_array($primary_bank) && !empty($primary_bank)) ? $primary_bank[0] : null;
            
            echo json_encode(['success' => true, 'data' => [
                'invoices' => $invoices,
                'plans' => $plans,
                'primary_bank' => $primary_bank,
                'summary' => [
                    'total' => $sum_total,
                    'paid' => $sum_paid
                ],
                'filter' => [
                    'month' => $filter_month,
                    'year' => $filter_year,
                    'status' => $filter_status
                ]
            ]]);
            break;

        // Get koperasi data for koperasi page
        case 'koperasi_data':
            if ($user['role'] !== 'appOwner') { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit(); }
            
            // Get billing plans for assignment
            $plans = query("SELECT id, kode, nama, tipe, harga_bulanan, persentase_keuntungan FROM billing_plans WHERE is_active = 1 ORDER BY harga_bulanan");
            if (!is_array($plans)) $plans = [];
            
            // Get koperasi list
            $koperasi_list = query("
                SELECT 
                    br.id as reg_id, br.nama_usaha, br.alamat_usaha, br.username, br.nama, br.email, br.telp,
                    br.approved_at,
                    u.id as user_id, u.status as user_status,
                    bp.nama as plan_nama, bp.tipe as plan_tipe, bp.harga_bulanan,
                    kb.status as billing_status
                FROM bos_registrations br
                LEFT JOIN users u ON u.username = br.username AND u.role = 'bos'
                LEFT JOIN koperasi_billing kb ON kb.bos_user_id = u.id AND kb.status = 'aktif'
                LEFT JOIN billing_plans bp ON bp.id = kb.billing_plan_id
                WHERE br.status = 'approved'
                ORDER BY br.approved_at DESC
            ");
            if (!is_array($koperasi_list)) $koperasi_list = [];
            
            echo json_encode(['success' => true, 'data' => [
                'plans' => $plans,
                'koperasi_list' => $koperasi_list
            ]]);
            break;

        // Get usage data for usage page
        case 'usage_data':
            if ($user['role'] !== 'appOwner') { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit(); }
            
            $days = (int)($_GET['days'] ?? 30);
            if ($days < 7) $days = 7;
            if ($days > 90) $days = 90;

            // Per-koperasi usage summary
            $koperasi_usage = query("
                SELECT 
                    uds.bos_user_id,
                    u.nama as bos_nama,
                    br.nama_usaha,
                    SUM(uds.total_api_calls) as api_calls,
                    SUM(uds.total_renders) as renders,
                    SUM(uds.total_api_calls) + SUM(uds.total_renders) as total
                FROM usage_daily_summary uds
                JOIN users u ON u.id = uds.bos_user_id
                LEFT JOIN bos_registrations br ON br.username = u.username
                WHERE uds.tanggal >= DATE_SUB(CURDATE(), INTERVAL $days DAY)
                GROUP BY uds.bos_user_id, u.nama, br.nama_usaha
                ORDER BY total DESC
            ");
            if (!is_array($koperasi_usage)) $koperasi_usage = [];

            // Daily trend (total)
            $daily_trend = query("
                SELECT tanggal, SUM(total_api_calls) as api, SUM(total_renders) as renders
                FROM usage_daily_summary
                WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL $days DAY)
                GROUP BY tanggal ORDER BY tanggal
            ");
            if (!is_array($daily_trend)) $daily_trend = [];

            // Totals
            $grand_api = 0; $grand_renders = 0;
            foreach ($koperasi_usage as $ku) {
                $grand_api += (int)$ku['api_calls'];
                $grand_renders += (int)$ku['renders'];
            }

            // Top endpoints
            $top_endpoints = query("
                SELECT endpoint, tipe, COUNT(*) as cnt
                FROM usage_log
                WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL $days DAY)
                GROUP BY endpoint, tipe
                ORDER BY cnt DESC
                LIMIT 15
            ");
            if (!is_array($top_endpoints)) $top_endpoints = [];

            echo json_encode(['success' => true, 'data' => [
                'days' => $days,
                'koperasi_usage' => $koperasi_usage,
                'daily_trend' => $daily_trend,
                'top_endpoints' => $top_endpoints,
                'totals' => [
                    'api' => $grand_api,
                    'renders' => $grand_renders,
                    'koperasi_count' => count($koperasi_usage),
                    'avg_per_day' => ($grand_api + $grand_renders) / max($days, 1)
                ]
            ]]);
            break;

        // Get AI advisor data
        case 'ai_advisor_data':
            if ($user['role'] !== 'appOwner') { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit(); }
            
            $filter_kat = $_GET['kategori'] ?? 'all';
            $filter_bos = $_GET['bos'] ?? '';

            $where = "WHERE 1=1";
            if ($filter_kat !== 'all') {
                $where .= " AND a.kategori = '" . addslashes($filter_kat) . "'";
            }
            if ($filter_bos) {
                $where .= " AND a.bos_user_id = " . (int)$filter_bos;
            }

            $advice_list = query("
                SELECT a.*, u.nama as bos_nama, br.nama_usaha
                FROM ai_advice a
                LEFT JOIN users u ON u.id = a.bos_user_id
                LEFT JOIN bos_registrations br ON br.username = u.username
                $where
                ORDER BY a.created_at DESC
                LIMIT 50
            ");
            if (!is_array($advice_list)) $advice_list = [];

            // Stats
            $stat_baru = query("SELECT COUNT(*) as c FROM ai_advice WHERE status = 'baru'");
            $stat_baru = (is_array($stat_baru) && isset($stat_baru[0])) ? (int)$stat_baru[0]['c'] : 0;

            // Bos list for filter
            $bos_for_filter = query("SELECT u.id, u.nama, br.nama_usaha FROM users u LEFT JOIN bos_registrations br ON br.username = u.username WHERE u.role = 'bos' AND u.status = 'aktif' ORDER BY u.nama");
            if (!is_array($bos_for_filter)) $bos_for_filter = [];

            echo json_encode(['success' => true, 'data' => [
                'advice_list' => $advice_list,
                'stats' => ['baru' => $stat_baru],
                'bos_list' => $bos_for_filter,
                'filter' => ['kategori' => $filter_kat, 'bos' => $filter_bos]
            ]]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Action tidak dikenali']);
    }
    exit();
}

// ── POST: aksi bisnis ─────────────────────────────────────────────
if ($method === 'POST') {
    $input  = json_decode(file_get_contents('php://input'), true) ?? [];
    $action = trim($input['action'] ?? '');

    switch ($action) {

        // ─── Nasabah meninggal ────────────────────────────────────
        case 'nasabah_meninggal':
            if (!hasPermission('manage_nasabah')) { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit(); }
            $nasabah_id = (int)($input['nasabah_id'] ?? 0);
            $tanggal    = $input['tanggal_meninggal'] ?? date('Y-m-d');
            $catatan    = $input['catatan'] ?? '';
            if (!$nasabah_id) { http_response_code(400); echo json_encode(['error' => 'nasabah_id wajib']); exit(); }
            echo json_encode(prosesNasabahMeninggal($nasabah_id, $tanggal, $catatan, $user['id']));
            break;

        // ─── Ahli waris ───────────────────────────────────────────
        case 'tambah_ahli_waris':
            if (!hasPermission('manage_nasabah')) { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit(); }
            $req = ['nasabah_id', 'nama', 'hubungan'];
            foreach ($req as $f) { if (empty($input[$f])) { http_response_code(400); echo json_encode(['error' => "$f wajib"]); exit(); } }
            $result = query(
                "INSERT INTO ahli_waris (nasabah_id, nama, hubungan, ktp, telp, alamat, adalah_penjamin, catatan) VALUES (?,?,?,?,?,?,?,?)",
                [$input['nasabah_id'], $input['nama'], $input['hubungan'], $input['ktp'] ?? null,
                 $input['telp'] ?? null, $input['alamat'] ?? null, (int)($input['adalah_penjamin'] ?? 0), $input['catatan'] ?? null]
            );
            echo json_encode(['success' => (bool)$result]);
            break;

        // ─── Restrukturisasi ──────────────────────────────────────
        case 'restrukturisasi':
            if (!hasPermission('pinjaman.approve')) { http_response_code(403); echo json_encode(['error' => 'Forbidden — butuh pinjaman.approve']); exit(); }
            $pinjaman_id = (int)($input['pinjaman_id'] ?? 0);
            $tipe        = $input['tipe'] ?? '';
            if (!$pinjaman_id || !$tipe) { http_response_code(400); echo json_encode(['error' => 'pinjaman_id dan tipe wajib']); exit(); }
            echo json_encode(buatRestrukturisasi($pinjaman_id, $tipe, $input, $user['id']));
            break;

        // ─── Hitung & proses pelunasan dipercepat ─────────────────
        case 'lunas_dipercepat':
            if (!hasPermission('manage_pembayaran')) { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit(); }
            $pinjaman_id = (int)($input['pinjaman_id'] ?? 0);
            if (!$pinjaman_id) { http_response_code(400); echo json_encode(['error' => 'pinjaman_id wajib']); exit(); }
            $hitungan = hitungPelunasanDipercepat($pinjaman_id, $input['tanggal'] ?? null);
            if (isset($hitungan['error'])) { http_response_code(400); echo json_encode($hitungan); exit(); }
            // Jika confirm=true, eksekusi pelunasan
            if (!empty($input['confirm'])) {
                $kode = 'LNS-' . date('Ymd') . '-' . $pinjaman_id;
                $r = query(
                    "INSERT INTO pembayaran (cabang_id, pinjaman_id, angsuran_id, kode_pembayaran, jumlah_bayar, denda, total_bayar, tanggal_bayar, cara_bayar, petugas_id, keterangan)
                     SELECT p.cabang_id, p.id, a.id, ?, ?, ?, ?, ?, ?, ?, 'Pelunasan dipercepat'
                     FROM pinjaman p JOIN angsuran a ON a.pinjaman_id = p.id WHERE p.id = ? AND a.status != 'lunas' ORDER BY a.no_angsuran ASC LIMIT 1",
                    [$kode, $hitungan['total_harus_dibayar'], $hitungan['denda_terhutang'],
                     $hitungan['total_harus_dibayar'], $input['tanggal'] ?? date('Y-m-d'),
                     $input['cara_bayar'] ?? 'tunai', $user['id'], $pinjaman_id]
                );
                query("UPDATE angsuran SET status='lunas' WHERE pinjaman_id = ? AND status != 'lunas'", [$pinjaman_id]);
                query("UPDATE pinjaman SET status='lunas', tanggal_lunas=?, sisa_pokok_berjalan=0, updated_at=NOW() WHERE id=?", [$input['tanggal'] ?? date('Y-m-d'), $pinjaman_id]);
                $result_pinjaman = query("SELECT nasabah_id FROM pinjaman WHERE id=?", [$pinjaman_id]);
                $nasabah_id = is_array($result_pinjaman) && isset($result_pinjaman[0]) ? (int)$result_pinjaman[0]['nasabah_id'] : null;
                if ($nasabah_id) {
                    updateSkorKredit($nasabah_id, +5, 'bayar_tepat_waktu', $pinjaman_id);
                }
                $hitungan['lunas'] = true;
            }
            echo json_encode(array_merge(['success' => true], $hitungan));
            break;

        // ─── Write-off pinjaman macet ─────────────────────────────
        case 'write_off':
            if ($user['role'] !== 'bos') { http_response_code(403); echo json_encode(['error' => 'Hanya bos yang bisa melakukan write-off']); exit(); }
            $pinjaman_id = (int)($input['pinjaman_id'] ?? 0);
            if (!$pinjaman_id) { http_response_code(400); echo json_encode(['error' => 'pinjaman_id wajib']); exit(); }
            echo json_encode(prosesWriteOff($pinjaman_id, $input, $user['id']));
            break;

        // ─── Pengganti petugas (legacy alias → assign_pengganti) ──
        case 'pengganti_petugas':
            if (!hasPermission('manage_users') && !hasPermission('manage_kas')) { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit(); }
            $pt_id  = (int)($input['petugas_asli_id'] ?? $input['petugas_id'] ?? 0);
            $pg_id  = (int)($input['petugas_pengganti_id'] ?? $input['pengganti_id'] ?? 0);
            $tgl_m  = $input['tanggal'] ?? $input['tanggal_mulai'] ?? date('Y-m-d');
            $alasan = $input['alasan'] ?? $input['alasan_ketidakhadiran'] ?? 'lainnya';
            if (!$pt_id || !$pg_id) { http_response_code(400); echo json_encode(['error' => 'petugas_id dan pengganti_id wajib']); exit(); }
            echo json_encode(buatPenggantiPetugas($user['cabang_id'] ?? 1, $pt_id, $pg_id, $tgl_m, $tgl_m, $alasan, $input['catatan'] ?? null, $user['id']));
            break;

        // ─── Verifikasi kas petugas ───────────────────────────────
        case 'verifikasi_kas':
            if (!hasPermission('manage_kas') && !hasPermission('manage_laporan') && !in_array($user['role'], ['bos','manager_pusat','manager_cabang','admin_pusat'])) {
                http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit();
            }
            $kas_id = (int)($input['kas_id'] ?? 0);
            if (!$kas_id) { http_response_code(400); echo json_encode(['error' => 'kas_id wajib']); exit(); }
            $ket_selisih = $input['keterangan_selisih'] ?? null;
            // Simpan keterangan selisih dulu jika ada
            if ($ket_selisih) {
                query("UPDATE kas_petugas SET selisih_keterangan = ? WHERE id = ?", [$ket_selisih, $kas_id]);
            }
            echo json_encode(verifikasiKasPetugas($kas_id, $user['id']));
            break;

        // ─── Proses kelebihan bayar ───────────────────────────────
        case 'proses_kelebihan_bayar':
            if (!hasPermission('manage_pembayaran')) { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit(); }
            $kb_id   = (int)($input['kelebihan_bayar_id'] ?? 0);
            $aksi    = $input['aksi'] ?? ''; // 'dikembalikan' atau 'dikompensasi'
            if (!$kb_id || !in_array($aksi, ['dikembalikan', 'dikompensasi'])) {
                http_response_code(400); echo json_encode(['error' => 'kelebihan_bayar_id dan aksi (dikembalikan/dikompensasi) wajib']); exit();
            }
            $r = query("UPDATE kelebihan_bayar SET status = ?, diproses_oleh = ?, tanggal_proses = CURDATE(), keterangan = ?, updated_at = NOW() WHERE id = ? AND status = 'pending'",
                [$aksi, $user['id'], $input['keterangan'] ?? null, $kb_id]);
            echo json_encode(['success' => (bool)$r]);
            break;

        // ─── Baca notifikasi ──────────────────────────────────────
        case 'baca_notifikasi':
            $notif_id = (int)($input['notifikasi_id'] ?? 0);
            if (!$notif_id) { http_response_code(400); echo json_encode(['error' => 'notifikasi_id wajib']); exit(); }
            echo json_encode(['success' => (bool)bacaNotifikasi($notif_id, $user['id'])]);
            break;

        // ─── Tambah antrian offline ───────────────────────────────
        case 'tambah_offline_queue':
            if (!hasPermission('manage_pembayaran')) { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit(); }
            $req = ['nasabah_id', 'pinjaman_id', 'angsuran_id', 'jumlah_bayar', 'tanggal_kutip'];
            foreach ($req as $f) { if (empty($input[$f])) { http_response_code(400); echo json_encode(['error' => "$f wajib"]); exit(); } }
            $r = query(
                "INSERT INTO pembayaran_offline_queue (cabang_id, petugas_id, nasabah_id, pinjaman_id, angsuran_id, jumlah_bayar, tanggal_kutip, cara_bayar, keterangan, device_id)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [$user['cabang_id'], $user['id'], $input['nasabah_id'], $input['pinjaman_id'], $input['angsuran_id'],
                 $input['jumlah_bayar'], $input['tanggal_kutip'], $input['cara_bayar'] ?? 'tunai',
                 $input['keterangan'] ?? null, $input['device_id'] ?? null]
            );
            echo json_encode(['success' => (bool)$r, 'id' => $r ? query("SELECT LAST_INSERT_ID() as id")[0]['id'] : null]);
            break;

        // ─── Proses antrian offline ───────────────────────────────
        case 'proses_offline_queue':
            if (!hasPermission('manage_pembayaran') && $user['role'] !== 'admin_pusat') { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit(); }
            $queue_id = (int)($input['queue_id'] ?? 0);
            if (!$queue_id) { http_response_code(400); echo json_encode(['error' => 'queue_id wajib']); exit(); }
            echo json_encode(prosesOfflineQueue($queue_id, $user['id']));
            break;

        // ─── Blacklist platform (hanya appOwner) ──────────────────
        case 'platform_blacklist':
            if ($user['role'] !== 'appOwner') { http_response_code(403); echo json_encode(['error' => 'Hanya appOwner yang bisa blacklist platform-wide']); exit(); }
            $nasabah_id = (int)($input['nasabah_id'] ?? 0);
            $aktifkan   = (bool)($input['aktifkan'] ?? true);
            if (!$nasabah_id) { http_response_code(400); echo json_encode(['error' => 'nasabah_id wajib']); exit(); }
            query("UPDATE nasabah SET platform_blacklist = ? WHERE id = ?", [(int)$aktifkan, $nasabah_id]);
            logAudit('platform_blacklist', 'nasabah', $nasabah_id, [], ['aktifkan' => $aktifkan, 'alasan' => $input['alasan'] ?? '']);
            echo json_encode(['success' => true]);
            break;

        // ─── Auto tandai macet (admin/manager) ───────────────────
        case 'auto_tandai_macet':
            if (!hasPermission('manage_laporan') && $user['role'] !== 'bos') { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit(); }
            $cabang_id = (int)($input['cabang_id'] ?? 0) ?: null;
            $jumlah    = autoTandaiMacet($cabang_id);
            echo json_encode(['success' => true, 'ditandai' => $jumlah]);
            break;

        // ─── Kunci kas petugas ────────────────────────────────────
        case 'kunci_kas':
            if (!hasPermission('manage_kas') && !in_array($user['role'], ['bos', 'manager_pusat', 'manager_cabang'])) {
                http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit();
            }
            $kas_id = (int)($input['kas_id'] ?? 0);
            if (!$kas_id) { http_response_code(400); echo json_encode(['error' => 'kas_id wajib']); exit(); }
            $kas = query("SELECT * FROM kas_petugas WHERE id = ?", [$kas_id]);
            if (!$kas) { http_response_code(404); echo json_encode(['error' => 'Kas tidak ditemukan']); exit(); }
            if ($kas[0]['is_locked']) { echo json_encode(['success' => false, 'error' => 'Kas sudah terkunci']); exit(); }
            query("UPDATE kas_petugas SET is_locked = 1, status = 'locked', updated_at = NOW() WHERE id = ?", [$kas_id]);
            logAudit('kunci_kas', 'kas_petugas', $kas_id, ['is_locked' => 0], ['is_locked' => 1]);
            echo json_encode(['success' => true, 'message' => 'Kas berhasil dikunci']);
            break;

        // ─── Assign pengganti petugas ─────────────────────────────
        case 'assign_pengganti':
            if (!hasPermission('manage_kas') && !hasPermission('manage_users')) {
                http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit();
            }
            $petugas_id    = (int)($input['petugas_id'] ?? 0);
            $pengganti_id  = (int)($input['pengganti_id'] ?? 0);
            $tgl_mulai     = $input['tanggal_mulai'] ?? date('Y-m-d');
            $tgl_selesai   = $input['tanggal_selesai'] ?? date('Y-m-d');
            $alasan        = $input['alasan_ketidakhadiran'] ?? 'sakit';
            $catatan       = $input['catatan'] ?? null;
            $cab_id        = (int)($input['cabang_id'] ?? $user['cabang_id'] ?? 1);
            if (!$petugas_id || !$pengganti_id) { http_response_code(400); echo json_encode(['error' => 'petugas_id dan pengganti_id wajib']); exit(); }
            if ($petugas_id === $pengganti_id) { http_response_code(400); echo json_encode(['error' => 'Petugas dan pengganti tidak boleh sama']); exit(); }
            $r = buatPenggantiPetugas($cab_id, $petugas_id, $pengganti_id, $tgl_mulai, $tgl_selesai, $alasan, $catatan, $user['id']);
            echo json_encode($r);
            break;

        // ─── Tambah ahli waris ────────────────────────────────────
        case 'tambah_ahli_waris':
            if (!hasPermission('manage_nasabah')) { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit(); }
            $nasabah_id      = (int)($input['nasabah_id'] ?? 0);
            $nama_aw         = trim($input['nama'] ?? '');
            $hubungan        = $input['hubungan'] ?? 'lainnya';
            if (!$nasabah_id || !$nama_aw) { http_response_code(400); echo json_encode(['error' => 'nasabah_id dan nama wajib']); exit(); }
            // Validasi nasabah milik koperasi yang login
            $ns = query("SELECT owner_bos_id FROM nasabah WHERE id = ?", [$nasabah_id]);
            if (!$ns) { http_response_code(404); echo json_encode(['error' => 'Nasabah tidak ditemukan']); exit(); }
            if ($ns[0]['owner_bos_id'] && $ns[0]['owner_bos_id'] != getOwnerBosId() && $user['role'] !== 'appOwner') {
                http_response_code(403); echo json_encode(['error' => 'Nasabah bukan milik koperasi Anda']); exit();
            }
            $r = query(
                "INSERT INTO ahli_waris (nasabah_id, nama, hubungan, ktp, telp, adalah_penjamin, created_by)
                 VALUES (?, ?, ?, ?, ?, ?, ?)",
                [$nasabah_id, $nama_aw, $hubungan,
                 $input['ktp'] ?? null, $input['telp'] ?? null,
                 (int)($input['adalah_penjamin'] ?? 0), $user['id']]
            );
            echo json_encode(['success' => (bool)$r]);
            break;

        // ─── Proses lunas dipercepat (konfirmasi) ─────────────────
        case 'lunas_dipercepat':
            if (!hasPermission('manage_pembayaran')) { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit(); }
            $pinjaman_id = (int)($input['pinjaman_id'] ?? 0);
            if (!$pinjaman_id || empty($input['confirm'])) { http_response_code(400); echo json_encode(['error' => 'pinjaman_id dan confirm wajib']); exit(); }
            $cara_bayar = $input['cara_bayar'] ?? 'tunai';
            $r = prosesLunasDipercepat($pinjaman_id, $user['id'], $cara_bayar);
            echo json_encode($r);
            break;

        // ─── Approve bos registration (appOwner only) ───────────────
        case 'approve_bos_registration':
            if ($user['role'] !== 'appOwner') { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit(); }
            $registration_id = (int)($input['registration_id'] ?? 0);
            if (!$registration_id) { http_response_code(400); echo json_encode(['error' => 'registration_id wajib']); exit(); }
            
            $registration = query("SELECT * FROM bos_registrations WHERE id = ?", [$registration_id]);
            if (!is_array($registration) || empty($registration)) {
                http_response_code(404); echo json_encode(['error' => 'Pendaftaran tidak ditemukan']); exit();
            }
            
            $reg = $registration[0];
            if ($reg['status'] !== 'pending') {
                http_response_code(400); echo json_encode(['error' => 'Pendaftaran sudah diproses']); exit();
            }
            
            // Get default billing plan (STARTER)
            $default_plan = query("SELECT id FROM billing_plans WHERE kode = 'STARTER' AND is_active = 1 LIMIT 1");
            $default_plan_id = (is_array($default_plan) && !empty($default_plan)) ? (int)$default_plan[0]['id'] : null;
            
            // Create user account
            $user_result = query(
                "INSERT INTO users (username, password, nama, email, telp, role, status) VALUES (?, ?, ?, ?, ?, 'bos', 'aktif')",
                [$reg['username'], $reg['password'], $reg['nama'], $reg['email'] ?? '', $reg['telp'] ?? '']
            );
            
            if ($user_result) {
                $bos_user_id = query("SELECT LAST_INSERT_ID() as id")[0]['id'];
                
                // Auto-assign default billing plan
                if ($default_plan_id) {
                    query("INSERT INTO koperasi_billing (bos_user_id, billing_plan_id, status, tanggal_mulai, created_by) VALUES (?, ?, 'aktif', CURDATE(), ?)",
                        [$bos_user_id, $default_plan_id, $user['id']]);
                }
                
                query("UPDATE bos_registrations SET status = 'approved', approved_at = NOW(), approved_by = ? WHERE id = ?",
                    [$user['id'], $registration_id]);
                
                $plan_info = $default_plan_id ? " dengan plan STARTER" : " (belum ada billing plan)";
                echo json_encode(['success' => true, 'message' => "Bos '{$reg['nama']}' ({$reg['nama_usaha']}) berhasil disetujui. User aktif dengan username: {$reg['username']}{$plan_info}"]);
            } else {
                http_response_code(500); echo json_encode(['error' => 'Gagal membuat akun user Bos']); exit();
            }
            break;

        // ─── Reject bos registration (appOwner only) ────────────────
        case 'reject_bos_registration':
            if ($user['role'] !== 'appOwner') { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit(); }
            $registration_id = (int)($input['registration_id'] ?? 0);
            $rejection_reason = trim($input['rejection_reason'] ?? '');
            if (!$registration_id) { http_response_code(400); echo json_encode(['error' => 'registration_id wajib']); exit(); }
            
            $registration = query("SELECT * FROM bos_registrations WHERE id = ?", [$registration_id]);
            if (!is_array($registration) || empty($registration)) {
                http_response_code(404); echo json_encode(['error' => 'Pendaftaran tidak ditemukan']); exit();
            }
            
            $reg = $registration[0];
            if ($reg['status'] !== 'pending') {
                http_response_code(400); echo json_encode(['error' => 'Pendaftaran sudah diproses']); exit();
            }
            
            query("UPDATE bos_registrations SET status = 'rejected', rejected_reason = ?, approved_by = ?, approved_at = NOW() WHERE id = ?",
                [$rejection_reason, $user['id'], $registration_id]);
            
            echo json_encode(['success' => true, 'message' => "Pendaftaran '{$reg['nama']}' ditolak."]);
            break;

        // ─── Generate invoices (appOwner only) ─────────────────────────
        case 'generate_invoices':
            if ($user['role'] !== 'appOwner') { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit(); }
            $bulan = (int)($input['bulan'] ?? date('n'));
            $tahun = (int)($input['tahun'] ?? date('Y'));
            $generated = 0;

            $active_billings = query("SELECT kb.*, bp.* FROM koperasi_billing kb JOIN billing_plans bp ON bp.id = kb.billing_plan_id WHERE kb.status = 'aktif'");
            if (is_array($active_billings)) {
                foreach ($active_billings as $kb) {
                    // Check if invoice already exists
                    $exists = query("SELECT id FROM koperasi_invoices WHERE koperasi_billing_id = ? AND periode_bulan = ? AND periode_tahun = ?",
                        [$kb['id'], $bulan, $tahun]);
                    if (is_array($exists) && !empty($exists)) continue;

                    $biaya_fixed = 0; $biaya_persen = 0; $biaya_usage = 0;
                    $keuntungan = 0; $total_api = 0; $total_renders = 0;

                    if ($kb['tipe'] === 'fixed') {
                        $biaya_fixed = (float)$kb['harga_bulanan'];
                    } elseif ($kb['tipe'] === 'percentage') {
                        // Get koperasi profit from pembayaran this month
                        $profit = query("SELECT COALESCE(SUM(total_bayar),0) as p FROM pembayaran WHERE petugas_id IN (SELECT id FROM users WHERE (owner_bos_id = ? OR id = ?)) AND MONTH(tanggal_bayar) = ? AND YEAR(tanggal_bayar) = ?",
                            [$kb['bos_user_id'], $kb['bos_user_id'], $bulan, $tahun]);
                        $keuntungan = (is_array($profit) && isset($profit[0])) ? (float)$profit[0]['p'] : 0;
                        $biaya_persen = $keuntungan * ((float)$kb['persentase_keuntungan'] / 100);
                    } elseif ($kb['tipe'] === 'usage') {
                        $usage = query("SELECT COALESCE(SUM(total_api_calls),0) as api, COALESCE(SUM(total_renders),0) as renders FROM usage_daily_summary WHERE bos_user_id = ? AND MONTH(tanggal) = ? AND YEAR(tanggal) = ?",
                            [$kb['bos_user_id'], $bulan, $tahun]);
                        if (is_array($usage) && isset($usage[0])) {
                            $total_api = (int)$usage[0]['api'];
                            $total_renders = (int)$usage[0]['renders'];
                        }
                        $billable_api = max(0, $total_api - (int)$kb['api_call_gratis']);
                        $billable_renders = max(0, $total_renders - (int)$kb['render_gratis']);
                        $biaya_usage = ($billable_api * (float)$kb['harga_per_api_call']) + ($billable_renders * (float)$kb['harga_per_render']);
                    }

                    $subtotal = $biaya_fixed + $biaya_persen + $biaya_usage;
                    $kode = 'INV-' . $tahun . str_pad($bulan, 2, '0', STR_PAD_LEFT) . '-' . str_pad($kb['bos_user_id'], 4, '0', STR_PAD_LEFT);

                    query("INSERT INTO koperasi_invoices (koperasi_billing_id, bos_user_id, kode_invoice, periode_bulan, periode_tahun, biaya_fixed, biaya_persentase, keuntungan_koperasi, biaya_usage, total_api_calls, total_renders, subtotal, total, status, tanggal_terbit, tanggal_jatuh_tempo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'terbit', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 14 DAY))",
                        [$kb['id'], $kb['bos_user_id'], $kode, $bulan, $tahun, $biaya_fixed, $biaya_persen, $keuntungan, $biaya_usage, $total_api, $total_renders, $subtotal, $subtotal]);
                    $generated++;
                }
            }
            echo json_encode(['success' => true, 'message' => "$generated invoice berhasil di-generate untuk periode $bulan/$tahun."]);
            break;

        // ─── Mark invoice as paid (appOwner only) ─────────────────────
        case 'mark_invoice_paid':
            if ($user['role'] !== 'appOwner') { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit(); }
            $invoice_id = (int)($input['invoice_id'] ?? 0);
            $metode = $input['metode_bayar'] ?? 'transfer';
            if ($invoice_id) {
                query("UPDATE koperasi_invoices SET status = 'dibayar', tanggal_bayar = CURDATE(), metode_bayar = ? WHERE id = ?", [$metode, $invoice_id]);
                echo json_encode(['success' => true, 'message' => 'Invoice ditandai sebagai dibayar.']);
            } else {
                http_response_code(400); echo json_encode(['error' => 'invoice_id wajib']); exit();
            }
            break;

        // ─── Suspend koperasi (appOwner only) ─────────────────────────
        case 'suspend_koperasi':
            if ($user['role'] !== 'appOwner') { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit(); }
            $bos_user_id = (int)($input['bos_user_id'] ?? 0);
            if ($bos_user_id) {
                query("UPDATE users SET status = 'nonaktif' WHERE id = ? AND role = 'bos'", [$bos_user_id]);
                query("UPDATE koperasi_billing SET status = 'suspended' WHERE bos_user_id = ? AND status = 'aktif'", [$bos_user_id]);
                echo json_encode(['success' => true, 'message' => 'Koperasi berhasil di-suspend.']);
            } else {
                http_response_code(400); echo json_encode(['error' => 'bos_user_id wajib']); exit();
            }
            break;

        // ─── Activate koperasi (appOwner only) ────────────────────────
        case 'activate_koperasi':
            if ($user['role'] !== 'appOwner') { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit(); }
            $bos_user_id = (int)($input['bos_user_id'] ?? 0);
            if ($bos_user_id) {
                query("UPDATE users SET status = 'aktif' WHERE id = ? AND role = 'bos'", [$bos_user_id]);
                echo json_encode(['success' => true, 'message' => 'Koperasi berhasil diaktifkan kembali.']);
            } else {
                http_response_code(400); echo json_encode(['error' => 'bos_user_id wajib']); exit();
            }
            break;

        // ─── Assign billing plan to koperasi (appOwner only) ───────────
        case 'assign_billing_plan':
            if ($user['role'] !== 'appOwner') { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit(); }
            $bos_user_id = (int)($input['bos_user_id'] ?? 0);
            $plan_id = (int)($input['billing_plan_id'] ?? 0);
            if ($bos_user_id && $plan_id) {
                // Deactivate old billing
                query("UPDATE koperasi_billing SET status = 'cancelled' WHERE bos_user_id = ? AND status = 'aktif'", [$bos_user_id]);
                // Create new billing
                query("INSERT INTO koperasi_billing (bos_user_id, billing_plan_id, status, tanggal_mulai, created_by) VALUES (?, ?, 'aktif', CURDATE(), ?)",
                    [$bos_user_id, $plan_id, $user['id']]);
                echo json_encode(['success' => true, 'message' => 'Billing plan berhasil di-assign.']);
            } else {
                http_response_code(400); echo json_encode(['error' => 'bos_user_id dan billing_plan_id wajib']); exit();
            }
            break;

        // ─── Generate invoice for specific koperasi (appOwner only) ─────
        case 'generate_koperasi_invoice':
            if ($user['role'] !== 'appOwner') { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit(); }
            $bos_user_id = (int)($input['bos_user_id'] ?? 0);
            $bulan = (int)($input['bulan'] ?? date('n'));
            $tahun = (int)($input['tahun'] ?? date('Y'));

            // Get active billing for this koperasi
            $billing = query("SELECT kb.*, bp.* FROM koperasi_billing kb JOIN billing_plans bp ON bp.id = kb.billing_plan_id WHERE kb.bos_user_id = ? AND kb.status = 'aktif' LIMIT 1", [$bos_user_id]);
            if (!is_array($billing) || empty($billing)) {
                http_response_code(400); echo json_encode(['error' => 'Koperasi ini tidak memiliki billing plan aktif.']); exit();
            }

            $kb = $billing[0];

            // Check if invoice already exists
            $exists = query("SELECT id FROM koperasi_invoices WHERE koperasi_billing_id = ? AND periode_bulan = ? AND periode_tahun = ?",
                [$kb['id'], $bulan, $tahun]);
            if (is_array($exists) && !empty($exists)) {
                http_response_code(400); echo json_encode(['error' => 'Invoice untuk periode ini sudah ada.']); exit();
            }

            $biaya_fixed = 0; $biaya_persen = 0; $biaya_usage = 0;
            $keuntungan = 0; $total_api = 0; $total_renders = 0;

            if ($kb['tipe'] === 'fixed') {
                $biaya_fixed = (float)$kb['harga_bulanan'];
            } elseif ($kb['tipe'] === 'percentage') {
                // Get koperasi profit from pembayaran this month
                $profit = query("SELECT COALESCE(SUM(total_bayar),0) as p FROM pembayaran WHERE petugas_id IN (SELECT id FROM users WHERE (owner_bos_id = ? OR id = ?)) AND MONTH(tanggal_bayar) = ? AND YEAR(tanggal_bayar) = ?",
                    [$kb['bos_user_id'], $kb['bos_user_id'], $bulan, $tahun]);
                $keuntungan = (is_array($profit) && isset($profit[0])) ? (float)$profit[0]['p'] : 0;
                $biaya_persen = $keuntungan * ((float)$kb['persentase_keuntungan'] / 100);
            } elseif ($kb['tipe'] === 'usage') {
                $usage = query("SELECT COALESCE(SUM(total_api_calls),0) as api, COALESCE(SUM(total_renders),0) as renders FROM usage_daily_summary WHERE bos_user_id = ? AND MONTH(tanggal) = ? AND YEAR(tanggal) = ?",
                    [$kb['bos_user_id'], $bulan, $tahun]);
                if (is_array($usage) && isset($usage[0])) {
                    $total_api = (int)$usage[0]['api'];
                    $total_renders = (int)$usage[0]['renders'];
                }
                $billable_api = max(0, $total_api - (int)$kb['api_call_gratis']);
                $billable_renders = max(0, $total_renders - (int)$kb['render_gratis']);
                $biaya_usage = ($billable_api * (float)$kb['harga_per_api_call']) + ($billable_renders * (float)$kb['harga_per_render']);
            }

            $subtotal = $biaya_fixed + $biaya_persen + $biaya_usage;
            $kode = 'INV-' . $tahun . str_pad($bulan, 2, '0', STR_PAD_LEFT) . '-' . str_pad($kb['bos_user_id'], 4, '0', STR_PAD_LEFT);

            query("INSERT INTO koperasi_invoices (koperasi_billing_id, bos_user_id, kode_invoice, periode_bulan, periode_tahun, biaya_fixed, biaya_persentase, keuntungan_koperasi, biaya_usage, total_api_calls, total_renders, subtotal, total, status, tanggal_terbit, tanggal_jatuh_tempo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'terbit', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 14 DAY))",
                [$kb['id'], $kb['bos_user_id'], $kode, $bulan, $tahun, $biaya_fixed, $biaya_persen, $keuntungan, $biaya_usage, $total_api, $total_renders, $subtotal, $subtotal]);

            echo json_encode(['success' => true, 'message' => "Invoice {$kode} berhasil di-generate untuk periode {$bulan}/{$tahun}."]);
            break;

        // ─── Generate AI advice (appOwner only) ───────────────────────
        case 'generate_ai_advice':
            if ($user['role'] !== 'appOwner') { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit(); }
            $target_bos = $input['bos_user_id'] ?? '';
            $generated = 0;

            // Get koperasi list to analyze
            if ($target_bos === 'all' || empty($target_bos)) {
                $bos_list = query("SELECT u.id, u.nama, br.nama_usaha FROM users u LEFT JOIN bos_registrations br ON br.username = u.username WHERE u.role = 'bos' AND u.status = 'aktif'");
            } else {
                $bos_list = query("SELECT u.id, u.nama, br.nama_usaha FROM users u LEFT JOIN bos_registrations br ON br.username = u.username WHERE u.id = ?", [(int)$target_bos]);
            }
            if (!is_array($bos_list)) $bos_list = [];

            foreach ($bos_list as $bos) {
                $advice_items = generateAdviceForKoperasi($bos['id'], $bos['nama'], $bos['nama_usaha'] ?? '');
                foreach ($advice_items as $adv) {
                    query("INSERT INTO ai_advice (bos_user_id, kategori, judul, isi, prioritas, data_pendukung) VALUES (?, ?, ?, ?, ?, ?)",
                        [$bos['id'], $adv['kategori'], $adv['judul'], $adv['isi'], $adv['prioritas'], json_encode($adv['data'] ?? [])]);
                    $generated++;
                }
            }
            echo json_encode(['success' => true, 'message' => "$generated saran AI berhasil di-generate."]);
            break;

        // ─── Mark AI advice as read (appOwner only) ───────────────────
        case 'mark_advice_read':
            if ($user['role'] !== 'appOwner') { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit(); }
            $advice_id = (int)($input['advice_id'] ?? 0);
            if ($advice_id) {
                query("UPDATE ai_advice SET status = 'dibaca', dibaca_at = NOW() WHERE id = ?", [$advice_id]);
                echo json_encode(['success' => true, 'message' => 'Saran ditandai sebagai dibaca.']);
            } else {
                http_response_code(400); echo json_encode(['error' => 'advice_id wajib']); exit();
            }
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => "Action '{$action}' tidak dikenali"]);
    }
    exit();
}

/**
 * Generate AI advice based on koperasi data analysis
 */
function generateAdviceForKoperasi($bos_id, $bos_nama, $nama_usaha) {
    $advice = [];

    // 1. Analyze nasabah growth
    $nasabah_count = query("SELECT COUNT(*) as c FROM nasabah WHERE cabang_id IN (SELECT cabang_id FROM users WHERE id = ? OR owner_bos_id = ?)", [$bos_id, $bos_id]);
    $nasabah_total = (is_array($nasabah_count) && isset($nasabah_count[0])) ? (int)$nasabah_count[0]['c'] : 0;

    $nasabah_bulan_lalu = query("SELECT COUNT(*) as c FROM nasabah WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND cabang_id IN (SELECT cabang_id FROM users WHERE id = ? OR owner_bos_id = ?)", [$bos_id, $bos_id]);
    $nasabah_new = (is_array($nasabah_bulan_lalu) && isset($nasabah_bulan_lalu[0])) ? (int)$nasabah_bulan_lalu[0]['c'] : 0;

    if ($nasabah_total == 0) {
        $advice[] = [
            'kategori' => 'pertumbuhan',
            'judul' => "[$nama_usaha] Belum memiliki nasabah — mulai akuisisi segera",
            'isi' => "Koperasi $nama_usaha belum memiliki nasabah terdaftar. Disarankan untuk segera melakukan:\n\n1. Sosialisasi ke masyarakat sekitar\n2. Kerjasama dengan kelurahan/desa setempat\n3. Tawarkan program pinjaman ringan sebagai daya tarik awal\n4. Gunakan fitur petugas lapangan untuk kunjungan door-to-door",
            'prioritas' => 'kritis',
            'data' => ['nasabah_total' => 0]
        ];
    } elseif ($nasabah_new == 0 && $nasabah_total > 0) {
        $advice[] = [
            'kategori' => 'pertumbuhan',
            'judul' => "[$nama_usaha] Stagnasi — tidak ada nasabah baru dalam 30 hari",
            'isi' => "Koperasi $nama_usaha memiliki $nasabah_total nasabah tapi tidak ada penambahan baru dalam 30 hari terakhir. Saran:\n\n1. Evaluasi produk pinjaman apakah masih kompetitif\n2. Tingkatkan promosi di media sosial\n3. Adakan program referral untuk nasabah existing\n4. Pertimbangkan buka cabang baru di lokasi strategis",
            'prioritas' => 'tinggi',
            'data' => ['nasabah_total' => $nasabah_total, 'nasabah_baru_30d' => 0]
        ];
    }

    // 2. Analyze loan performance
    $pinjaman_aktif = query("SELECT COUNT(*) as c, COALESCE(SUM(plafon),0) as total FROM pinjaman WHERE status = 'aktif' AND cabang_id IN (SELECT cabang_id FROM users WHERE id = ? OR owner_bos_id = ?)", [$bos_id, $bos_id]);
    $pinjaman_cnt = (is_array($pinjaman_aktif) && isset($pinjaman_aktif[0])) ? (int)$pinjaman_aktif[0]['c'] : 0;
    $pinjaman_total = (is_array($pinjaman_aktif) && isset($pinjaman_aktif[0])) ? (float)$pinjaman_aktif[0]['total'] : 0;

    // Check late payments
    $late = query("SELECT COUNT(*) as c FROM angsuran WHERE status = 'belum_bayar' AND jatuh_tempo < CURDATE() AND pinjaman_id IN (SELECT id FROM pinjaman WHERE cabang_id IN (SELECT cabang_id FROM users WHERE id = ? OR owner_bos_id = ?))", [$bos_id, $bos_id]);
    $late_cnt = (is_array($late) && isset($late[0])) ? (int)$late[0]['c'] : 0;

    if ($late_cnt > 0 && $pinjaman_cnt > 0) {
        $late_ratio = round(($late_cnt / max($pinjaman_cnt, 1)) * 100);
        $advice[] = [
            'kategori' => 'risiko',
            'judul' => "[$nama_usaha] $late_cnt angsuran menunggak — tingkat keterlambatan {$late_ratio}%",
            'isi' => "Terdapat $late_cnt angsuran yang sudah melewati jatuh tempo. Rasio keterlambatan terhadap total pinjaman aktif: {$late_ratio}%.\n\nRekomendasi:\n1. Intensifkan penagihan oleh petugas lapangan\n2. Terapkan denda keterlambatan untuk efek jera\n3. Evaluasi proses verifikasi nasabah sebelum pencairan\n4. Pertimbangkan restrukturisasi untuk nasabah yang kesulitan",
            'prioritas' => $late_ratio > 30 ? 'kritis' : ($late_ratio > 15 ? 'tinggi' : 'sedang'),
            'data' => ['late_angsuran' => $late_cnt, 'pinjaman_aktif' => $pinjaman_cnt, 'late_ratio' => $late_ratio]
        ];
    }

    // 3. Branch utilization
    $cabang_count = query("SELECT COUNT(*) as c FROM cabang WHERE id IN (SELECT DISTINCT cabang_id FROM users WHERE id = ? OR owner_bos_id = ?)", [$bos_id, $bos_id]);
    $cabang_cnt = (is_array($cabang_count) && isset($cabang_count[0])) ? (int)$cabang_count[0]['c'] : 0;

    if ($cabang_cnt <= 1 && $nasabah_total > 50) {
        $advice[] = [
            'kategori' => 'pertumbuhan',
            'judul' => "[$nama_usaha] Potensi ekspansi cabang — $nasabah_total nasabah di 1 cabang",
            'isi' => "Dengan $nasabah_total nasabah yang dilayani hanya dari $cabang_cnt cabang, ada potensi untuk ekspansi. Pertimbangkan:\n\n1. Buka cabang di kecamatan/kota terdekat\n2. Analisis sebaran nasabah untuk lokasi optimal\n3. Rekrut manager cabang dari karyawan berprestasi\n4. Mulai dengan kantor kecil untuk minimalisir risiko",
            'prioritas' => 'sedang',
            'data' => ['nasabah' => $nasabah_total, 'cabang' => $cabang_cnt]
        ];
    }

    // 4. Staff efficiency
    $staff_count = query("SELECT COUNT(*) as c FROM users WHERE (owner_bos_id = ? OR id = ?) AND status = 'aktif' AND role != 'bos'", [$bos_id, $bos_id]);
    $staff_cnt = (is_array($staff_count) && isset($staff_count[0])) ? (int)$staff_count[0]['c'] : 0;

    if ($staff_cnt == 0 && $nasabah_total > 0) {
        $advice[] = [
            'kategori' => 'efisiensi',
            'judul' => "[$nama_usaha] Bos bekerja tanpa staf — risiko operasional tinggi",
            'isi' => "Koperasi $nama_usaha memiliki $nasabah_total nasabah tapi dikelola seorang diri oleh Bos. Ini berisiko dan tidak efisien.\n\nRekomendasi:\n1. Rekrut minimal 1 admin untuk administrasi\n2. Rekrut petugas lapangan untuk penagihan\n3. Delegasikan tugas operasional agar Bos fokus strategi",
            'prioritas' => 'tinggi',
            'data' => ['staf' => 0, 'nasabah' => $nasabah_total]
        ];
    }

    // 5. Product diversification
    if ($pinjaman_cnt > 10) {
        $avg_plafon = $pinjaman_total / max($pinjaman_cnt, 1);
        $advice[] = [
            'kategori' => 'produk',
            'judul' => "[$nama_usaha] Analisis produk — rata-rata plafon Rp " . number_format($avg_plafon, 0, ',', '.'),
            'isi' => "Dari $pinjaman_cnt pinjaman aktif, rata-rata plafon adalah Rp " . number_format($avg_plafon, 0, ',', '.') . ". Total outstanding: Rp " . number_format($pinjaman_total, 0, ',', '.') . ".\n\nSaran pengembangan produk:\n1. " . ($avg_plafon < 5000000 ? "Pertimbangkan produk pinjaman menengah (5-20jt) untuk nasabah berpengalaman" : "Produk mikro (< 2jt) bisa menarik nasabah baru yang belum berani besar") . "\n2. Variasi tenor: tawarkan tenor pendek (mingguan) dan panjang (6-12 bulan)\n3. Buat program pinjaman khusus: pendidikan, pertanian, UMKM",
            'prioritas' => 'sedang',
            'data' => ['pinjaman_aktif' => $pinjaman_cnt, 'avg_plafon' => $avg_plafon, 'total_outstanding' => $pinjaman_total]
        ];
    }

    // If no specific advice, give general encouragement
    if (empty($advice)) {
        $advice[] = [
            'kategori' => 'umum',
            'judul' => "[$nama_usaha] Kondisi operasional baik",
            'isi' => "Koperasi $nama_usaha berjalan dengan baik. Tidak ada masalah kritis yang terdeteksi saat ini. Terus pantau:\n\n1. Pertumbuhan nasabah\n2. Tingkat keterlambatan pembayaran\n3. Efisiensi operasional staf\n4. Diversifikasi produk pinjaman",
            'prioritas' => 'rendah',
            'data' => ['nasabah' => $nasabah_total, 'pinjaman' => $pinjaman_cnt, 'staf' => $staff_cnt ?? 0]
        ];
    }

    return $advice;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
