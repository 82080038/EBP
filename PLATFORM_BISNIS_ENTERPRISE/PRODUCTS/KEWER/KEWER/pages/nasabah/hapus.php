<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();

// Permission check
if (!hasPermission('manage_nasabah')) {
    header('Location: ' . baseUrl('dashboard.php'));
    exit();
}

$id = $_GET['id'] ?? null;

// Get cabang filter based on role
$user = getCurrentUser();
$role = $user['role'];
$user_cabang_id = $user['cabang_id'] ?? null;

// Check if nasabah exists
$nasabah = query("SELECT * FROM nasabah WHERE id = ?", [$id]);

if (!$nasabah) {
    header('Location: index.php');
    exit();
}

$nasabah = $nasabah[0];

// Check if nasabah has active loans
$active_loans_result = query("SELECT COUNT(*) as count FROM pinjaman WHERE nasabah_id = ? AND status IN ('pengajuan', 'disetujui', 'aktif')", [$id]);
$active_loans = is_array($active_loans_result) && isset($active_loans_result[0]) ? $active_loans_result[0]['count'] : 0;

if ($active_loans > 0) {
    $_SESSION['error'] = 'Tidak dapat menghapus nasabah yang masih memiliki pinjaman aktif';
    header('Location: index.php');
    exit();
}

// Delete files
if ($nasabah['foto_ktp'] && file_exists(BASE_PATH . '/' . $nasabah['foto_ktp'])) {
    unlink(BASE_PATH . '/' . $nasabah['foto_ktp']);
}

if ($nasabah['foto_selfie'] && file_exists(BASE_PATH . '/' . $nasabah['foto_selfie'])) {
    unlink(BASE_PATH . '/' . $nasabah['foto_selfie']);
}

// Delete nasabah
$result = query("DELETE FROM nasabah WHERE id = ?", [$id]);

if ($result) {
    $_SESSION['success'] = 'Nasabah berhasil dihapus';
} else {
    $_SESSION['error'] = 'Gagal menghapus nasabah';
}

header('Location: index.php');
exit();
?>
