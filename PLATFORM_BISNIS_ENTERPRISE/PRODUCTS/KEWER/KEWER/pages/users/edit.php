<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
// Only users with manage_users permission can edit users
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

if ($_POST) {
    $nama = $_POST['nama'] ?? '';
    $email = $_POST['email'] ?? '';
    $role = $_POST['role'] ?? '';
    $gaji = $_POST['gaji'] ?? 0;
    $limit_kasbon = $_POST['limit_kasbon'] ?? 0;
    $status = $_POST['status'] ?? 'aktif';
    
    // Update user
    $result = query("UPDATE users SET nama = ?, email = ?, role = ?, gaji = ?, limit_kasbon = ?, status = ? WHERE id = ?", [
        $nama,
        $email,
        $role,
        $gaji,
        $limit_kasbon,
        $status,
        $id
    ]);
    
    if ($result) {
        // Log audit
        logAudit('UPDATE', 'users', $id, $user, ['nama' => $nama, 'email' => $email, 'role' => $role, 'status' => $status]);
        
        $_SESSION['success'] = 'User berhasil diupdate';
        header('Location: index.php');
        exit();
    } else {
        $_SESSION['error'] = 'Gagal mengupdate user';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body>
    <div class="main-container">
        <?php require_once BASE_PATH . '/includes/sidebar.php'; ?>
        
        <main class="content-area">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Edit User</h1>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label>Username</label>
                                <input type="text" class="form-control" value="<?= $user['username'] ?>" disabled>
                            </div>
                            <div class="mb-3">
                                <label>Nama *</label>
                                <input type="text" name="nama" class="form-control" value="<?= $user['nama'] ?>" required>
                            </div>
                            <div class="mb-3">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" value="<?= $user['email'] ?>">
                            </div>
                            <div class="mb-3">
                                <label>Role *</label>
                                <select name="role" class="form-select" required>
                                    <option value="bos" <?= $user['role'] == 'bos' ? 'selected' : '' ?>>Bos</option>
                                    <option value="manager_pusat" <?= $user['role'] == 'manager_pusat' ? 'selected' : '' ?>>Manager Pusat</option>
                                    <option value="manager_cabang" <?= $user['role'] == 'manager_cabang' ? 'selected' : '' ?>>Manager Cabang</option>
                                    <option value="admin_pusat" <?= $user['role'] == 'admin_pusat' ? 'selected' : '' ?>>Admin Pusat</option>
                                    <option value="admin_cabang" <?= $user['role'] == 'admin_cabang' ? 'selected' : '' ?>>Admin Cabang</option>
                                    <option value="petugas_pusat" <?= $user['role'] == 'petugas_pusat' ? 'selected' : '' ?>>Petugas Pusat</option>
                                    <option value="petugas_cabang" <?= $user['role'] == 'petugas_cabang' ? 'selected' : '' ?>>Petugas Cabang</option>
                                    <option value="teller" <?= $user['role'] == 'teller' ? 'selected' : '' ?>>Teller</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Gaji</label>
                                <input type="number" name="gaji" class="form-control" value="<?= $user['gaji'] ?? 0 ?>">
                            </div>
                            <div class="mb-3">
                                <label>Limit Kas Bon</label>
                                <input type="number" name="limit_kasbon" class="form-control" value="<?= $user['limit_kasbon'] ?? 0 ?>">
                            </div>
                            <div class="mb-3">
                                <label>Status</label>
                                <select name="status" class="form-select">
                                    <option value="aktif" <?= $user['status'] == 'aktif' ? 'selected' : '' ?>>Aktif</option>
                                    <option value="nonaktif" <?= $user['status'] == 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                                </select>
                            </div>
                            <div class="text-end">
                                <a href="index.php" class="btn btn-secondary">Batal</a>
                                <button type="submit" class="btn btn-primary">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
