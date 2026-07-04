<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();

// Permission check
if (!hasPermission('manage_pembayaran')) {
    header('Location: ' . baseUrl('dashboard.php'));
    exit();
}

$kantor_id = 1; // Single office

if ($_POST) {
    $pinjaman_id = $_POST['pinjaman_id'] ?? '';
    $angsuran_id = $_POST['angsuran_id'] ?? '';
    $jumlah_bayar = $_POST['jumlah_bayar'] ?? 0;
    $denda = $_POST['denda'] ?? 0;
    $tanggal_bayar = $_POST['tanggal_bayar'] ?? '';
    $cara_bayar = $_POST['cara_bayar'] ?? '';
    $keterangan = $_POST['keterangan'] ?? '';
    
    $total_bayar = $jumlah_bayar + $denda;
    $petugas_id = getCurrentUser()['id'];
    
    // Generate kode pembayaran
    $kode_pembayaran = generateKode('BYR', 'pembayaran', 'kode_pembayaran');
    
    // Insert pembayaran
    $result = crudTransaction(function() use ($kantor_id, $pinjaman_id, $angsuran_id, $kode_pembayaran, $jumlah_bayar, $denda, $total_bayar, $tanggal_bayar, $cara_bayar, $petugas_id, $keterangan) {
        // Insert pembayaran
        $result = query("INSERT INTO pembayaran (cabang_id, pinjaman_id, angsuran_id, kode_pembayaran, jumlah_bayar, denda, total_bayar, tanggal_bayar, cara_bayar, petugas_id, keterangan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", [
            $kantor_id, $pinjaman_id, $angsuran_id, $kode_pembayaran, $jumlah_bayar, $denda, $total_bayar, $tanggal_bayar, $cara_bayar, $petugas_id, $keterangan
        ]);
        
        if ($result) {
            $pembayaran_id = query("SELECT LAST_INSERT_ID() as id")[0]['id'];
            
            // Update angsuran status
            query("UPDATE angsuran SET status = 'lunas', tanggal_bayar = ? WHERE id = ?", [$tanggal_bayar, $angsuran_id]);
            
            // Update pinjaman status if all angsuran are paid
            $check = query("SELECT COUNT(*) as unpaid FROM angsuran WHERE pinjaman_id = ? AND status != 'lunas'", [$pinjaman_id]);
            if ($check[0]['unpaid'] == 0) {
                query("UPDATE pinjaman SET status = 'lunas' WHERE id = ?", [$pinjaman_id]);
            }
            
            // Log the CRUD operation
            logCrudOperation('pembayaran', 'CREATE', $pembayaran_id, null, [
                'cabang_id' => $kantor_id,
                'pinjaman_id' => $pinjaman_id,
                'angsuran_id' => $angsuran_id,
                'kode_pembayaran' => $kode_pembayaran,
                'total_bayar' => $total_bayar
            ]);
        }
        
        return $result;
    });
    
    if ($result) {
        $_SESSION['success'] = 'Pembayaran berhasil ditambahkan';
        header('Location: index.php');
        exit();
    } else {
        $_SESSION['error'] = 'Gagal menambahkan pembayaran';
    }
}

header('Location: index.php');
exit();
