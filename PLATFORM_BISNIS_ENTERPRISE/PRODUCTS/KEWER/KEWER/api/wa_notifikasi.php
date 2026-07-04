<?php
/**
 * API: WhatsApp Notifikasi
 * POST /api/wa_notifikasi.php { action: kirim_tagihan|kirim_konfirmasi|kirim_pengingat_batch, ... }
 * GET  /api/wa_notifikasi.php?action=log&limit=50
 */
// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);
// error_reporting(0);
// ini_set('display_errors', 0);
header('Content-Type: application/json');

require_once __DIR__ . '/../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
require_once BASE_PATH . '/includes/feature_flags.php';
require_once BASE_PATH . '/includes/wa_notifikasi.php';

if (!isFeatureEnabled('wa_notifikasi')) {
    http_response_code(403);
    echo json_encode(['error' => 'Fitur WhatsApp Notifikasi belum diaktifkan oleh appOwner.']);
    exit();
}

requireLogin();
$user   = getCurrentUser();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $action = $_GET['action'] ?? 'log';
    if ($action === 'log') {
        $limit = min((int)($_GET['limit'] ?? 50), 200);
        $logs  = query("SELECT wl.*, n.nama as nama_nasabah FROM wa_log wl LEFT JOIN nasabah n ON wl.nasabah_id = n.id ORDER BY wl.created_at DESC LIMIT ?", [$limit]);
        echo json_encode($logs ?: []);
    } elseif ($action === 'status') {
        echo json_encode([
            'enabled'   => isFeatureEnabled('wa_notifikasi'),
            'provider'  => 'in_app',
            'has_token' => true, // In-app system doesn't require external tokens
        ]);
    }
    exit();
}

if ($method === 'POST') {
    if (!hasPermission('manage_nasabah') && !in_array($user['role'], ['bos', 'manager_pusat', 'manager_cabang', 'appOwner'])) {
        http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit();
    }

    $input  = json_decode(file_get_contents('php://input'), true) ?? [];
    $action = $input['action'] ?? '';

    switch ($action) {
        case 'kirim_tagihan':
            // Kirim tagihan manual ke satu nasabah
            $nasabah_id = (int)($input['nasabah_id'] ?? 0);
            $pinjaman_id = (int)($input['pinjaman_id'] ?? 0);
            if (!$nasabah_id) { http_response_code(400); echo json_encode(['error' => 'nasabah_id wajib']); exit(); }

            $nasabah = query("SELECT * FROM nasabah WHERE id = ?", [$nasabah_id]);
            if (!$nasabah) { http_response_code(404); echo json_encode(['error' => 'Nasabah tidak ditemukan']); exit(); }
            $n = $nasabah[0];
            if (!$n['telepon']) { echo json_encode(['success' => false, 'error' => 'Nasabah tidak memiliki nomor telepon']); exit(); }

            // Cari angsuran terdekat
            $angsuran = query(
                "SELECT a.*, p.kode_pinjaman FROM angsuran a JOIN pinjaman p ON a.pinjaman_id = p.id
                 WHERE a.pinjaman_id = ? AND a.status != 'lunas' ORDER BY a.jatuh_tempo ASC LIMIT 1",
                [$pinjaman_id ?: 0]
            );
            if (!$angsuran) { echo json_encode(['success' => false, 'error' => 'Tidak ada angsuran aktif']); exit(); }
            $a = $angsuran[0];

            $pesan = $input['pesan_custom'] ?? templateWaPengingat(
                $n, ['kode_pinjaman' => $a['kode_pinjaman']],
                ['ke' => $a['ke'], 'jatuh_tempo' => $a['jatuh_tempo'], 'total_bayar' => $a['total_bayar']],
                'H-0'
            );
            $r = kirimWA($n['telepon'], $pesan, $nasabah_id, null, 'tagihan');
            echo json_encode($r);
            break;

        case 'kirim_konfirmasi':
            $pembayaran_id = (int)($input['pembayaran_id'] ?? 0);
            if (!$pembayaran_id) { http_response_code(400); echo json_encode(['error' => 'pembayaran_id wajib']); exit(); }

            $py = query(
                "SELECT py.*, n.nama as nama_nasabah, n.telepon, p.kode_pinjaman
                 FROM pembayaran py JOIN pinjaman p ON py.pinjaman_id = p.id JOIN nasabah n ON p.nasabah_id = n.id
                 WHERE py.id = ?", [$pembayaran_id]
            );
            if (!$py) { http_response_code(404); echo json_encode(['error' => 'Pembayaran tidak ditemukan']); exit(); }
            $py = $py[0];
            if (!$py['telepon']) { echo json_encode(['success' => false, 'error' => 'Nasabah tidak memiliki nomor telepon']); exit(); }

            $pesan = templateWaKonfirmasiBayar(
                ['nama' => $py['nama_nasabah']],
                ['kode_pinjaman' => $py['kode_pinjaman']],
                $py
            );
            $r = kirimWA($py['telepon'], $pesan, null, null, 'konfirmasi_bayar');
            echo json_encode($r);
            break;

        case 'kirim_pengingat_batch':
            if (!in_array($user['role'], ['bos', 'manager_pusat', 'manager_cabang', 'appOwner'])) {
                http_response_code(403); echo json_encode(['error' => 'Hanya manager ke atas']); exit();
            }
            $r = kirimWaPengingatBatch();
            echo json_encode(array_merge(['success' => true], $r));
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'action tidak dikenal: kirim_tagihan|kirim_konfirmasi|kirim_pengingat_batch']);
    }
    exit();
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
