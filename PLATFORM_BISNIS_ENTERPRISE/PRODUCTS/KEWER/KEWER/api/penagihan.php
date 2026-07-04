<?php
require_once '../includes/auth.php';
require_once '../includes/database_class.php';
require_once '../includes/business_logic.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// GET: List penagihan
if ($method === 'GET' && $action === '') {
    $cabang_id = getCurrentCabang();
    $penagihan = query("
        SELECT p.*, 
               n.kode_nasabah, n.nama as nama_nasabah, n.telp, n.alamat,
               pin.kode_pinjaman, pin.plafon,
               a.no_angsuran, a.jatuh_tempo, a.total_angsuran,
               u.nama as nama_petugas,
               jp.nama as jenis_penagihan_nama
        FROM penagihan p
        JOIN pinjaman pin ON p.pinjaman_id = pin.id
        JOIN nasabah n ON pin.nasabah_id = n.id
        LEFT JOIN angsuran a ON p.angsuran_id = a.id
        LEFT JOIN users u ON p.petugas_id = u.id
        LEFT JOIN ref_jenis_penagihan jp ON p.jenis_penagihan_id = jp.id
        WHERE pin.cabang_id = ? OR ? IS NULL
        ORDER BY p.tanggal_jatuh_tempo DESC, p.status ASC
    ", [$cabang_id, $cabang_id]);
    
    echo json_encode(['success' => true, 'data' => $penagihan ?: []]);
    exit;
}

// POST: Auto-create penagihan for overdue installments
if ($method === 'POST' && $action === 'auto_create') {
    $result = autoCreatePenagihanOverdue();
    echo json_encode($result);
    exit;
}

// PUT: Update penagihan
if ($method === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['id']) || !isset($input['status'])) {
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        exit;
    }
    
    $penagihan_id = $input['id'];
    $status = $input['status'];
    $hasil = $input['hasil'] ?? null;
    $tindakan = $input['tindakan'] ?? null;
    $petugas_id = getCurrentUser()['id'];
    
    // Update penagihan
    $result = query("
        UPDATE penagihan 
        SET status = ?, hasil = ?, tindakan = ?, petugas_id = ?, tanggal_penagihan = CURDATE(), updated_at = NOW()
        WHERE id = ?
    ", [$status, $hasil, $tindakan, $petugas_id, $penagihan_id]);
    
    if (!$result) {
        echo json_encode(['success' => false, 'error' => 'Gagal update penagihan']);
        exit;
    }
    
    // Log activity
    query("INSERT INTO penagihan_log (penagihan_id, aksi, hasil, petugas_id) VALUES (?, 'update', ?, ?)", 
        [$penagihan_id, "Status diubah ke {$status}", $petugas_id]);
    
    echo json_encode(['success' => true, 'message' => 'Penagihan berhasil diupdate']);
    exit;
}

// Method not allowed
echo json_encode(['success' => false, 'error' => 'Method not allowed']);
