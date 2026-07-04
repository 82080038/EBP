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
$id = $_GET['id'] ?? '';
if (!$id) {
    header('Location: index.php');
    exit();
}

$pembayaran = query("SELECT * FROM pembayaran WHERE id = ?", [$id]);
if (!$pembayaran) {
    header('Location: index.php');
    exit();
}
$pembayaran = $pembayaran[0];

$oldValue = $pembayaran;
$result = query("DELETE FROM pembayaran WHERE id = ?", [$id]);

if ($result) {
    logAudit('DELETE', 'pembayaran', $id, $oldValue, null);
    $_SESSION['success'] = 'Pembayaran berhasil dihapus';
} else {
    $_SESSION['error'] = 'Gagal menghapus pembayaran';
}

header('Location: index.php');
exit();
