<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();

// Only bos can access this page
$user = getCurrentUser();
if (!$user || $user['role'] !== 'bos') {
    header('Location: ' . baseUrl('dashboard.php'));
    exit();
}

$error = '';
$success = '';

// Get employees owned by this bos
$employees = query("SELECT id, username, nama, role, cabang_id FROM users WHERE owner_bos_id = ? AND status = 'aktif' ORDER BY nama", [$user['id']]);

// Get delegated permissions made by this bos
$delegated_permissions = query(
    "SELECT dp.*, u.nama as delegatee_name, u.username as delegatee_username, u.role as delegatee_role 
     FROM delegated_permissions dp 
     JOIN users u ON dp.delegatee_id = u.id 
     WHERE dp.delegator_id = ? 
     ORDER BY dp.created_at DESC",
    [$user['id']]
);

if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'delegate') {
        $delegatee_id = $_POST['delegatee_id'] ?? '';
        $permission_scope = $_POST['permission_scope'] ?? '';
        $expires_at = $_POST['expires_at'] ?? '';
        $notes = $_POST['notes'] ?? '';
        
        // Send to API
        $data = [
            'delegatee_id' => $delegatee_id,
            'permission_scope' => $permission_scope,
            'expires_at' => $expires_at,
            'notes' => $notes
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, baseUrl('api/delegated_permissions.php?action=delegate'));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());
        $response = curl_exec($ch);
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        if ($result && $result['success']) {
            $success = $result['message'];
            header('Location: ' . baseUrl('pages/bos/delegated_permissions.php'));
            exit();
        } else {
            $error = $result['message'] ?? 'Gagal mendelegasikan permission';
        }
    } elseif ($action === 'revoke') {
        $delegation_id = $_POST['delegation_id'] ?? '';
        
        $data = ['delegation_id' => $delegation_id];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, baseUrl('api/delegated_permissions.php?action=revoke'));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());
        $response = curl_exec($ch);
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        if ($result && $result['success']) {
            $success = $result['message'];
            header('Location: ' . baseUrl('pages/bos/delegated_permissions.php'));
            exit();
        } else {
            $error = $result['message'] ?? 'Gagal mencabut permission';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delegasi Permission - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <div class="main-container">
        <?php require_once BASE_PATH . '/includes/sidebar.php'; ?>

        <main class="content-area">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="bi bi-shield-lock"></i> Delegasi Permission</h1>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <!-- Delegate Permission Form -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Delegate Permission Baru</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="delegate">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Pilih Karyawan *</label>
                                        <select name="delegatee_id" class="form-select" required>
                                            <option value="">Pilih Karyawan</option>
                                            <?php foreach ($employees as $emp): ?>
                                                <option value="<?php echo $emp['id']; ?>">
                                                    <?php echo htmlspecialchars($emp['nama']); ?> (<?php echo $emp['role']; ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Scope Permission *</label>
                                        <select name="permission_scope" class="form-select" required>
                                            <option value="">Pilih Scope</option>
                                            <option value="employee_crud">CRUD Karyawan</option>
                                            <option value="branch_crud">CRUD Cabang</option>
                                            <option value="branch_employee_crud">CRUD Karyawan Cabang</option>
                                            <option value="all_operations">Semua Operasi</option>
                                        </select>
                                        <small class="form-text">
                                            - CRUD Karyawan: Bisa tambah/edit/hapus karyawan di scope mereka<br>
                                            - CRUD Cabang: Bisa tambah/edit/hapus cabang<br>
                                            - CRUD Karyawan Cabang: Bisa tambah/edit/hapus karyawan di cabang<br>
                                            - Semua Operasi: Akses penuh ke semua operasi
                                        </small>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Berlaku Sampai (Opsional)</label>
                                        <input type="date" name="expires_at" class="form-control flatpickr-date">
                                        <small class="form-text">Kosongkan untuk tidak ada batas waktu</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Catatan</label>
                                        <textarea name="notes" class="form-control" rows="2" placeholder="Catatan untuk delegasi ini"></textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send"></i> Delegate Permission
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Delegated Permissions List -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-list-check"></i> Daftar Permission yang Didelegasikan</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($delegated_permissions && count($delegated_permissions) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Karyawan</th>
                                            <th>Role</th>
                                            <th>Scope</th>
                                            <th>Diberikan</th>
                                            <th>Expires</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($delegated_permissions as $dp): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($dp['delegatee_name']); ?></td>
                                            <td><span class="badge bg-secondary"><?php echo $dp['delegatee_role']; ?></span></td>
                                            <td>
                                                <?php
                                                $scope_labels = [
                                                    'employee_crud' => 'CRUD Karyawan',
                                                    'branch_crud' => 'CRUD Cabang',
                                                    'branch_employee_crud' => 'CRUD Karyawan Cabang',
                                                    'all_operations' => 'Semua Operasi'
                                                ];
                                                echo $scope_labels[$dp['permission_scope']] ?? $dp['permission_scope'];
                                                ?>
                                            </td>
                                            <td><?php echo formatDate($dp['granted_at']); ?></td>
                                            <td><?php echo $dp['expires_at'] ? formatDate($dp['expires_at']) : 'Tidak ada batas'; ?></td>
                                            <td>
                                                <?php if ($dp['is_active']): ?>
                                                    <span class="badge bg-success">Aktif</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Dicabut</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($dp['is_active']): ?>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="action" value="revoke">
                                                    <input type="hidden" name="delegation_id" value="<?php echo $dp['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Cabut permission ini?')">
                                                        <i class="bi bi-x"></i> Cabut
                                                    </button>
                                                </form>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> Belum ada permission yang didelegasikan.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/id.js"></script>
    <script>
        $(document).ready(function() {
            flatpickr('.flatpickr-date', {
                locale: 'id',
                dateFormat: 'Y-m-d',
                allowInput: true,
                altInput: true,
                altFormat: 'd F Y',
                theme: 'light'
            });
        });
    </script>
</body>
</html>
