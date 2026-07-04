<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();

// Only users with assign_permissions permission can access
if (!hasPermission('assign_permissions')) {
    header('Location: ' . baseUrl('dashboard.php'));
    exit();
}

$user_id = $_GET['user_id'] ?? '';

if (!$user_id) {
    header('Location: ' . baseUrl('pages/users/index.php'));
    exit();
}

// Get current user info
$current_user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Permissions - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .permission-category {
            margin-bottom: 20px;
        }
        .permission-category h5 {
            margin-bottom: 15px;
            color: #495057;
            border-bottom: 2px solid #dee2e6;
            padding-bottom: 10px;
        }
        .permission-item {
            padding: 10px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            margin-bottom: 10px;
            background: #f8f9fa;
        }
        .permission-item:hover {
            background: #e9ecef;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <?php require_once BASE_PATH . '/includes/sidebar.php'; ?>

        <main class="content-area">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Kelola Permissions: <?= htmlspecialchars($target_user['nama']) ?></h1>
                    <a href="../users/index.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>

                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> Permissions berhasil diperbarui!
                    </div>
                <?php endif; ?>

                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Informasi User</h5>
                        <table class="table table-sm">
                            <tr>
                                <th>Username:</th>
                                <td id="user-username">-</td>
                            </tr>
                            <tr>
                                <th>Nama:</th>
                                <td id="user-nama">-</td>
                            </tr>
                            <tr>
                                <th>Role:</th>
                                <td id="user-role">-</td>
                            </tr>
                            <tr>
                                <th>Email:</th>
                                <td id="user-email">-</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <form method="POST" id="permissionsForm">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-4">Permissions</h5>
                            
                            <div id="permissions-container">
                                <div class="text-center">
                                    <div class="spinner-border spinner-border-sm" role="status"></div>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Simpan Permissions
                                </button>
                                <a href="../users/index.php" class="btn btn-secondary">
                                    <i class="bi bi-x"></i> Batal
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const userId = '<?php echo $user_id; ?>';

        // Load permissions data via JSON API
        $(document).ready(function() {
            loadPermissionsData();
        });

        function loadPermissionsData() {
            window.KewerAPI.getUserPermissions(userId).done(response => {
                if (response.success) {
                    renderPermissionsContainer(response.target_user, response.permissions_by_category, response.user_permission_codes);
                } else {
                    $('#permissions-container').html('<div class="alert alert-danger">Gagal memuat data permissions</div>');
                }
            }).fail(error => {
                $('#permissions-container').html('<div class="alert alert-danger">Gagal memuat data permissions</div>');
            });
        }

        function renderPermissionsContainer(targetUser, permissionsByCategory, userPermissionCodes) {
            if (!targetUser) {
                $('#permissions-container').html('<div class="alert alert-danger">User tidak ditemukan</div>');
                return;
            }

            // Populate user info
            $('#user-username').text(targetUser.username || '-');
            $('#user-nama').text(targetUser.nama || '-');
            $('#user-role').text(targetUser.role || '-');
            $('#user-email').text(targetUser.email || '-');

            let html = '';
            for (const [category, permissions] of Object.entries(permissionsByCategory)) {
                html += `
                    <div class="permission-category">
                        <h5>${category.charAt(0).toUpperCase() + category.slice(1)}</h5>
                `;
                permissions.forEach(perm => {
                    const checked = userPermissionCodes.includes(perm.kode) ? 'checked' : '';
                    html += `
                        <div class="permission-item">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" 
                                       name="permissions[]" 
                                       value="${perm.kode}"
                                       id="perm_${perm.kode}"
                                       ${checked}>
                                <label class="form-check-label" for="perm_${perm.kode}">
                                    <strong>${perm.nama}</strong>
                                    <small class="text-muted d-block">${perm.deskripsi}</small>
                                </label>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
            }

            $('#permissions-container').html(html);
        }

        // Handle form submission
        $('#permissionsForm').on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const permissionsToGrant = formData.getAll('permissions[]');

            window.KewerAPI.updateUserPermissions(userId, permissionsToGrant).done(response => {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Permissions berhasil disimpan',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = '../users/index.php';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: response.error || 'Gagal menyimpan permissions'
                    });
                }
            }).fail(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Gagal menyimpan permissions'
                });
            });
        });
    </script>
</body>
</html>
