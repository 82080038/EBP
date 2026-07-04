<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();

// Only users with manage_users permission can add users
if (!hasPermission('manage_users')) {
    header('Location: ' . baseUrl('dashboard.php'));
    exit();
}

if ($_POST) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $nama = $_POST['nama'] ?? '';
    $email = $_POST['email'] ?? '';
    $role = $_POST['role'] ?? '';
    $gaji = $_POST['gaji'] ?? 0;
    $limit_kasbon = $_POST['limit_kasbon'] ?? 0;
    $status = $_POST['status'] ?? 'aktif';
    
    // Check duplicate username
    $check = query("SELECT id FROM users WHERE username = ?", [$username]);
    if ($check) {
        $_SESSION['error'] = 'Username sudah digunakan';
    } else {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert user
        $result = query("INSERT INTO users (username, password, nama, email, role, gaji, limit_kasbon, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)", [
            $username,
            $hashed_password,
            $nama,
            $email,
            $role,
            $gaji,
            $limit_kasbon,
            $status
        ]);
        
        if ($result) {
            // Log audit
            logAudit('CREATE', 'users', $result, null, ['username' => $username, 'nama' => $nama, 'role' => $role]);
            
            $_SESSION['success'] = 'User berhasil ditambahkan';
            header('Location: index.php');
            exit();
        } else {
            $_SESSION['error'] = 'Gagal menambahkan user';
        }
    }
}

header('Location: index.php');
exit();
