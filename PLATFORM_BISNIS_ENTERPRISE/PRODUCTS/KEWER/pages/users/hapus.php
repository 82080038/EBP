<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();

// Only users with manage_users permission can delete users
if (!hasPermission('manage_users')) {
    header('Location: ' . baseUrl('dashboard.php'));
    exit();
}

$id = $_GET['id'] ?? '';
if (!$id) {
    header('Location: index.php');
    exit();
}

$user = query("SELECT * FROM users WHERE id = ?", [$id]);
if (!$user) {
    header('Location: index.php');
    exit();
}
$user = $user[0];

// Check if user has active loans or other dependencies
$active_loans_result = query("SELECT COUNT(*) as count FROM pinjaman WHERE petugas_id = ?", [$id]);
$active_loans = is_array($active_loans_result) && isset($active_loans_result[0]) ? $active_loans_result[0]['count'] : 0;

if ($active_loans > 0) {
    $_SESSION['error'] = 'User tidak dapat dihapus karena memiliki pinjaman aktif';
    header('Location: index.php');
    exit();
}

// Get old value for audit
$oldValue = $user;

// Delete user
$result = query("DELETE FROM users WHERE id = ?", [$id]);

if ($result) {
    // Log audit
    logAudit('DELETE', 'users', $id, $oldValue, null);
    
    $_SESSION['success'] = 'User berhasil dihapus';
} else {
    $_SESSION['error'] = 'Gagal menghapus user';
}

header('Location: index.php');
exit();
