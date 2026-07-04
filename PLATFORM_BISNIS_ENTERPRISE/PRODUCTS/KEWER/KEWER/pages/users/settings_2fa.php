<?php
/**
 * Halaman pengaturan 2FA untuk user sendiri
 */
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
require_once BASE_PATH . '/includes/feature_flags.php';
requireLogin();

if (!isFeatureEnabled('two_factor_auth')) {
    header('Location: ' . baseUrl('dashboard.php'));
    exit();
}

$user = getCurrentUser();
$roles_2fa = ['bos', 'manager_pusat', 'manager_cabang', 'admin_pusat', 'appOwner'];

if (!in_array($user['role'], $roles_2fa)) {
    header('Location: ' . baseUrl('dashboard.php')); exit();
}

$row = query("SELECT totp_enabled, totp_verified_at FROM users WHERE id = ?", [$user['id']]);
$totp_enabled = (bool)($row[0]['totp_enabled'] ?? false);
$totp_verified_at = $row[0]['totp_verified_at'] ?? null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan 2FA — <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <div class="main-container">
        <?php require_once BASE_PATH . '/includes/sidebar.php'; ?>
        <main class="content-area p-4">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
                <h2><i class="bi bi-shield-lock"></i> Two-Factor Authentication (2FA)</h2>
            </div>

            <div class="row justify-content-center">
                <div class="col-md-6">
                    <?php if ($totp_enabled): ?>
                    <div class="card border-success mb-4">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <i class="bi bi-shield-check text-success fs-1"></i>
                                <div>
                                    <h5 class="mb-0 text-success">2FA Aktif</h5>
                                    <small class="text-muted">Diaktifkan: <?= $totp_verified_at ? date('d/m/Y H:i', strtotime($totp_verified_at)) : '-' ?></small>
                                </div>
                            </div>
                            <p class="text-muted">Akun Anda dilindungi dengan autentikasi dua faktor. Setiap login memerlukan kode dari aplikasi authenticator.</p>
                            <hr>
                            <h6>Nonaktifkan 2FA</h6>
                            <p class="text-muted small">Masukkan kode dari aplikasi authenticator untuk menonaktifkan:</p>
                            <div class="input-group">
                                <input type="text" class="form-control" id="codeDisable" placeholder="000000" maxlength="6" inputmode="numeric">
                                <button class="btn btn-outline-danger" onclick="disable2FA()">Nonaktifkan</button>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <i class="bi bi-shield-exclamation text-warning fs-1"></i>
                                <div>
                                    <h5 class="mb-0">2FA Belum Aktif</h5>
                                    <small class="text-muted">Akun Anda belum dilindungi 2FA</small>
                                </div>
                            </div>
                            <p>Aktifkan Two-Factor Authentication untuk keamanan ekstra. Anda membutuhkan aplikasi seperti:</p>
                            <ul>
                                <li><strong>Google Authenticator</strong> (Android/iOS)</li>
                                <li><strong>Authy</strong> (Android/iOS/Desktop)</li>
                                <li><strong>Microsoft Authenticator</strong></li>
                            </ul>
                            <button class="btn btn-primary w-100" onclick="setup2FA()">
                                <i class="bi bi-qr-code-scan"></i> Mulai Setup 2FA
                            </button>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Panel setup (hidden by default) -->
                    <div id="setupPanel" class="card d-none">
                        <div class="card-header"><strong><i class="bi bi-qr-code"></i> Setup 2FA</strong></div>
                        <div class="card-body text-center">
                            <p class="text-muted">1. Scan QR code ini dengan aplikasi authenticator:</p>
                            <div id="qrContainer" class="mb-3">
                                <div class="spinner-border text-primary" role="status"></div>
                            </div>
                            <p class="text-muted small">Atau masukkan kode secret secara manual:</p>
                            <code id="secretDisplay" class="d-block bg-light p-2 rounded mb-3 user-select-all"></code>
                            <p>2. Masukkan kode 6 digit dari aplikasi untuk konfirmasi:</p>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control text-center fs-4 fw-bold" id="codeVerify"
                                       placeholder="000000" maxlength="6" inputmode="numeric" autocomplete="one-time-code">
                                <button class="btn btn-success" onclick="verify2FA()">Verifikasi & Aktifkan</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script>
    const API = '../../api/auth_2fa.php';

    async function setup2FA() {
        document.getElementById('setupPanel').classList.remove('d-none');
        const resp = await fetch(`${API}?action=setup`);
        const r = await resp.json();
        if (r.error) { Swal.fire('Error', r.error, 'error'); return; }

        document.getElementById('qrContainer').innerHTML =
            `<img src="${r.qr_url}" alt="QR Code 2FA" class="border rounded" width="200">`;
        document.getElementById('secretDisplay').textContent = r.secret;
    }

    async function verify2FA() {
        const code = document.getElementById('codeVerify').value.trim();
        if (code.length !== 6) { Swal.fire('Error', 'Masukkan 6 digit kode', 'error'); return; }
        const resp = await fetch(API, {
            method: 'POST', headers: {'Content-Type':'application/json'},
            body: JSON.stringify({ action: 'verify_setup', code })
        });
        const r = await resp.json();
        if (r.success) {
            Swal.fire('Berhasil!', '2FA berhasil diaktifkan. Akun Anda kini lebih aman.', 'success')
                .then(() => location.reload());
        } else {
            Swal.fire('Gagal', r.error || 'Kode tidak valid', 'error');
        }
    }

    async function disable2FA() {
        const code = document.getElementById('codeDisable').value.trim();
        if (code.length !== 6) { Swal.fire('Error', 'Masukkan 6 digit kode', 'error'); return; }
        const confirm = await Swal.fire({
            title: 'Nonaktifkan 2FA?', text: 'Akun Anda akan kurang aman.',
            icon: 'warning', showCancelButton: true,
            confirmButtonColor: '#dc3545', confirmButtonText: 'Ya, nonaktifkan'
        });
        if (!confirm.isConfirmed) return;
        const resp = await fetch(API, {
            method: 'POST', headers: {'Content-Type':'application/json'},
            body: JSON.stringify({ action: 'disable', code })
        });
        const r = await resp.json();
        if (r.success) {
            Swal.fire('Berhasil', '2FA dinonaktifkan', 'success').then(() => location.reload());
        } else {
            Swal.fire('Gagal', r.error || 'Kode tidak valid', 'error');
        }
    }
    </script>
</body>
</html>
