<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();

// Only users with manage_cabang permission can delete cabang
if (!hasPermission('manage_cabang')) {
    header('Location: ' . baseUrl('dashboard.php'));
    exit();
}

$id = $_GET['id'] ?? '';
if (!$id) {
    header('Location: ' . baseUrl('pages/cabang/index.php'));
    exit();
}

$cabang = query("SELECT * FROM cabang WHERE id = ?", [$id]);
if (!$cabang) {
    header('Location: index.php');
    exit();
}
$cabang = $cabang[0];

// Check if cabang has users or other dependencies
$users_count_result = query("SELECT COUNT(*) as count FROM users WHERE cabang_id = ?", [$id]);
$users_count = is_array($users_count_result) && isset($users_count_result[0]) ? $users_count_result[0]['count'] : 0;

$nasabah_count_result = query("SELECT COUNT(*) as count FROM nasabah WHERE cabang_id = ?", [$id]);
$nasabah_count = is_array($nasabah_count_result) && isset($nasabah_count_result[0]) ? $nasabah_count_result[0]['count'] : 0;

if ($users_count > 0 || $nasabah_count > 0) {
    $_SESSION['error'] = 'Cabang tidak dapat dihapus karena masih memiliki data terkait';
    header('Location: index.php');
    exit();
}

$oldValue = $cabang;
$result = query("DELETE FROM cabang WHERE id = ?", [$id]);

if ($result) {
    logAudit('DELETE', 'cabang', $id, $oldValue, null);
    $_SESSION['success'] = 'Cabang berhasil dihapus';
} else {
    $_SESSION['error'] = 'Gagal menghapus cabang';
}

header('Location: index.php');
exit();
