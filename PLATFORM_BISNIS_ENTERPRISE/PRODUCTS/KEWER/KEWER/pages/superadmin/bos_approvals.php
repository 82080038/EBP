<?php
// Legacy page — redirect to appOwner approval page
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();
header('Location: ' . baseUrl('pages/app_owner/approvals.php'));
exit();
requireRole('bos');

$error = '';
$success = '';

// Handle POST requests for approve/reject
if ($_POST) {
    // Validate CSRF token
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token';
    } else {
        $action = $_POST['action'] ?? '';
        $registration_id = $_POST['registration_id'] ?? '';
        $rejection_reason = $_POST['rejection_reason'] ?? '';
        
        if (empty($registration_id)) {
            $error = 'Registration ID diperlukan';
        } else {
            if ($action === 'approve') {
                // Direct database update instead of curl
                $result = query("UPDATE bos_registrations SET status = 'approved', approved_at = NOW(), approved_by = ? WHERE id = ?", [getCurrentUser()['id'], $registration_id]);
                
                if ($result) {
                    // Create user account for approved bos
                    $registration = query("SELECT * FROM bos_registrations WHERE id = ?", [$registration_id]);
                    if ($registration) {
                        $reg = $registration[0];
                        $password_hash = password_hash('password123', PASSWORD_DEFAULT);
                        
                        // Insert into users table
                        $user_result = query("INSERT INTO users (username, password, nama, email, telp, role, status, owner_bos_id) VALUES (?, ?, ?, ?, ?, 'bos', 'aktif', ?)", [
                            $reg['username'],
                            $password_hash,
                            $reg['nama'],
                            $reg['email'] ?? '',
                            $reg['telp'] ?? '',
                            $registration_id
                        ]);
                        
                        if ($user_result) {
                            $user_id = query("SELECT LAST_INSERT_ID() as id")[0]['id'];
                            // Update registration with user_id
                            query("UPDATE bos_registrations SET user_id = ? WHERE id = ?", [$user_id, $registration_id]);
                        }
                    }
                    
                    $_SESSION['success'] = 'Pendaftaran bos berhasil disetujui';
                    header('Location: ' . baseUrl('pages/superadmin/bos_approvals.php'));
                    exit();
                } else {
                    $error = 'Gagal menyetujui pendaftaran';
                }
            } elseif ($action === 'reject') {
                // Direct database update instead of curl
                $result = query("UPDATE bos_registrations SET status = 'rejected', rejected_at = NOW(), rejected_by = ?, rejection_reason = ? WHERE id = ?", [getCurrentUser()['id'], $rejection_reason, $registration_id]);
                
                if ($result) {
                    $_SESSION['success'] = 'Pendaftaran bos berhasil ditolak';
                    header('Location: ' . baseUrl('pages/superadmin/bos_approvals.php'));
                    exit();
                } else {
                    $error = 'Gagal menolak pendaftaran';
                }
            }
        }
    }
}

// Get pending registrations
$pending_registrations = query(
    "SELECT id, username, nama, email, telp, nama_usaha, alamat_usaha, created_at 
     FROM bos_registrations 
     WHERE status = 'pending' 
     ORDER BY created_at DESC"
);

// Get statistics
$total_pending = count($pending_registrations);
$total_approved = query("SELECT COUNT(*) as total FROM bos_registrations WHERE status = 'approved'")[0]['total'] ?? 0;
$total_rejected = query("SELECT COUNT(*) as total FROM bos_registrations WHERE status = 'rejected'")[0]['total'] ?? 0;

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Persetujuan Bos - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <div class="main-container">
        <?php require_once BASE_PATH . '/includes/sidebar.php'; ?>

        <main class="content-area">
            <div class="container-fluid py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="bi bi-person-check"></i> Persetujuan Pendaftaran Bos</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                                <i class="bi bi-arrow-clockwise"></i> Refresh
                            </button>
                        </div>
                    </div>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['success']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>
                
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title"><i class="bi bi-clock-history"></i> Pending</h5>
                                <h2 class="display-4"><?php echo $total_pending; ?></h2>
                                <p class="card-text">Pendaftaran menunggu persetujuan</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title"><i class="bi bi-check-circle"></i> Disetujui</h5>
                                <h2 class="display-4"><?php echo $total_approved; ?></h2>
                                <p class="card-text">Pendaftaran disetujui</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-danger text-white">
                            <div class="card-body">
                                <h5 class="card-title"><i class="bi bi-x-circle"></i> Ditolak</h5>
                                <h2 class="display-4"><?php echo $total_rejected; ?></h2>
                                <p class="card-text">Pendaftaran ditolak</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if ($pending_registrations && count($pending_registrations) > 0): ?>
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="bi bi-inbox"></i> Pendaftaran Pending (<?php echo count($pending_registrations); ?>)</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Username</th>
                                            <th>Nama</th>
                                            <th>Perusahaan</th>
                                            <th>Alamat</th>
                                            <th>Email</th>
                                            <th>Telp</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pending_registrations as $reg): ?>
                                        <tr>
                                            <td><?php echo formatDate($reg['created_at']); ?></td>
                                            <td><strong><?php echo htmlspecialchars($reg['username']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($reg['nama']); ?></td>
                                            <td><?php echo htmlspecialchars($reg['nama_usaha'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($reg['alamat_usaha'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($reg['email'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($reg['telp'] ?? '-'); ?></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-success" onclick="approveRegistration(<?php echo $reg['id']; ?>)">
                                                        <i class="bi bi-check"></i> Setujui
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger" onclick="rejectRegistration(<?php echo $reg['id']; ?>)">
                                                        <i class="bi bi-x"></i> Tolak
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info d-flex align-items-center" role="alert">
                        <i class="bi bi-info-circle-fill flex-shrink-0 me-2" style="font-size: 1.5rem;"></i>
                        <div>
                            <strong>Informasi:</strong> Tidak ada pendaftaran bos yang pending.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const csrfToken = '<?php echo generateCsrfToken(); ?>';
        
        function approveRegistration(id) {
            Swal.fire({
                title: 'Setujui Pendaftaran?',
                text: 'Apakah Anda yakin ingin menyetujui pendaftaran bos ini?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Setujui'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.innerHTML = '<input type="hidden" name="csrf_token" value="' + csrfToken + '"><input type="hidden" name="action" value="approve"><input type="hidden" name="registration_id" value="' + id + '">';
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
        
        function rejectRegistration(id) {
            Swal.fire({
                title: 'Tolak Pendaftaran?',
                text: 'Masukkan alasan penolakan:',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Tolak',
                input: 'text',
                inputPlaceholder: 'Alasan penolakan'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.innerHTML = '<input type="hidden" name="csrf_token" value="' + csrfToken + '"><input type="hidden" name="action" value="reject"><input type="hidden" name="registration_id" value="' + id + '"><input type="hidden" name="rejection_reason" value="' + (result.value || '') + '">';
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
    </script>
</body>
</html>
