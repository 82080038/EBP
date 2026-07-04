<?php
require_once 'config/path.php';
require_once BASE_PATH . '/includes/functions.php';

if (isLoggedIn()) {
    header('Location: ' . baseUrl('dashboard.php'));
    exit();
}

$error = '';

// Check if session timeout
if (isset($_GET['timeout']) && $_GET['timeout'] == '1') {
    $error = 'Session Anda telah expired. Silakan login kembali.';
}

// Test-specific login for automated testing (GET request with credentials)
if (isset($_GET['test_login']) && $_GET['test_login'] === 'true' && APP_ENV === 'development') {
    $username = $_GET['username'] ?? '';

    if ($username) {
        $user = query("SELECT * FROM users WHERE username = ? AND status = 'aktif'", [$username]);
        if ($user) {
            // Check if bos has active billing plan
            if ($user[0]['role'] === 'bos') {
                $billing = query("SELECT * FROM koperasi_billing WHERE bos_user_id = ? AND status = 'aktif' LIMIT 1", [$user[0]['id']]);
                if (!is_array($billing) || empty($billing)) {
                    $error = 'Akun Anda belum memiliki billing plan aktif. Silakan hubungi admin.';
                } else {
                    $_SESSION['user_id'] = $user[0]['id'];
                    $_SESSION['username'] = $user[0]['username'];
                    $_SESSION['role'] = $user[0]['role'];
                    session_write_close();
                    header('Location: /kewer/dashboard.php');
                    exit();
                }
            } else {
                $_SESSION['user_id'] = $user[0]['id'];
                $_SESSION['username'] = $user[0]['username'];
                $_SESSION['role'] = $user[0]['role'];
                session_write_close();
                header('Location: /kewer/dashboard.php');
                exit();
            }
        }
    }
}

if ($_POST) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Login process — verify against database
    $user = query("SELECT * FROM users WHERE username = ? AND status = 'aktif'", [$username]);

    if ($user && password_verify($password, $user[0]['password'])) {
        // Check if bos has active billing plan
        if ($user[0]['role'] === 'bos') {
            $billing = query("SELECT * FROM koperasi_billing WHERE bos_user_id = ? AND status = 'aktif' LIMIT 1", [$user[0]['id']]);
            if (!is_array($billing) || empty($billing)) {
                $error = 'Akun Anda belum memiliki billing plan aktif. Silakan hubungi admin.';
            } else {
                $_SESSION['user_id'] = $user[0]['id'];
                $_SESSION['username'] = $user[0]['username'];
                $_SESSION['role'] = $user[0]['role'];
                session_write_close();
                header('Location: /kewer/dashboard.php');
                exit();
            }
        } else {
            $_SESSION['user_id'] = $user[0]['id'];
            $_SESSION['username'] = $user[0]['username'];
            $_SESSION['role'] = $user[0]['role'];
            session_write_close();
            header('Location: /kewer/dashboard.php');
            exit();
        }
    } else {
        $error = 'Username atau password salah';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            padding: 40px;
            width: 100%;
            max-width: 400px;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="text-center mb-4">
            <h2><?php echo APP_NAME; ?></h2>
            <p class="text-muted">Sistem Pinjaman Modal Pedagang</p>
        </div>
        
        <?php if (APP_ENV === 'development'): ?>
        <div class="alert alert-warning mb-3">
            <strong>⚠️ Development Mode</strong><br>
            Quick login aktif. Jangan gunakan di production!
        </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" id="loginForm">
            <?= csrfField() ?>
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" id="username" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" id="password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
        
                <?php if (APP_ENV === 'development'):
            $dev_users = query("SELECT username, nama, role FROM users WHERE status = 'aktif' ORDER BY FIELD(role, 'appOwner','bos','manager_pusat','manager_cabang','admin_pusat','admin_cabang','petugas_pusat','petugas_cabang','teller'), nama");
            if (is_array($dev_users) && count($dev_users) > 0):
                $role_colors = [
                    'appOwner' => 'danger', 'bos' => 'dark', 'manager_pusat' => 'primary', 'manager_cabang' => 'info',
                    'admin_pusat' => 'secondary', 'admin_cabang' => 'success',
                    'petugas_pusat' => 'warning', 'petugas_cabang' => 'warning',
                    'teller' => 'light border'
                ];
        ?>
        <div class="mt-3">
            <p class="text-center text-muted mb-2"><small>⚡ Quick Login (Dev):</small></p>
            <div class="d-grid gap-1">
                <?php foreach ($dev_users as $du): 
                    $btn = $role_colors[$du['role']] ?? 'secondary';
                    $label = strtoupper(str_replace('_', ' ', $du['role']));
                ?>
                <button type="button" class="btn btn-<?= $btn ?> btn-sm" onclick="quickLogin('<?= htmlspecialchars($du['username']) ?>')"><strong><?= $label ?></strong>: <?= htmlspecialchars($du['nama']) ?></button>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; endif; ?>

        <div class="mt-3 text-center">
            <p class="text-muted">Ingin mendaftarkan koperasi? <a href="<?php echo baseUrl('pages/bos/register.php'); ?>" class="text-primary">Daftar sebagai Bos</a></p>
        </div>
        
        <script>
            function quickLogin(username) {
                // Use test_login parameter for automated login (development mode only)
                window.location.href = 'login.php?test_login=true&username=' + encodeURIComponent(username);
            }
        </script>
    </div>
</body>
</html>
