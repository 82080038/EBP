<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();

// Only users with users management permission can access
if (!hasPermission('users.create') && !hasPermission('users.read')) {
    header('Location: ' . baseUrl('dashboard.php'));
    exit();
}

$currentUser = getCurrentUser();
$role = $currentUser['role'];
$user_cabang_id = $currentUser['cabang_id'] ?? null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Users - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/themes/light.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="main-container">
        <?php require_once BASE_PATH . '/includes/sidebar.php'; ?>
        
        <main class="content-area">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Manajemen Users</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                        <i class="bi bi-plus"></i> Tambah User
                    </button>
                </div>
                
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="usersTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Username</th>
                                        <th>Nama</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Cabang</th>
                                        <th>Gaji</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="users-table-body">
                                    <tr>
                                        <td colspan="9" class="text-center">
                                            <div class="spinner-border spinner-border-sm" role="status"></div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="tambah.php">
                        <?= csrfField() ?>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Username *</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Password *</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Nama *</label>
                                <input type="text" name="nama" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label>Role *</label>
                            <select name="role" class="form-select" required id="roleSelect">
                                <option value="">Pilih Role</option>
                                <optgroup label="Pusat (Tanpa Cabang)">
                                    <option value="bos">Bos</option>
                                    <option value="manager_pusat">Manager Pusat</option>
                                    <option value="admin_pusat">Admin Pusat</option>
                                    <option value="petugas_pusat">Petugas Pusat</option>
                                </optgroup>
                                <optgroup label="Cabang">
                                    <option value="manager_cabang">Manager Cabang</option>
                                    <option value="admin_cabang">Admin Cabang</option>
                                    <option value="petugas_cabang">Petugas Cabang</option>
                                    <option value="karyawan">Karyawan</option>
                                </optgroup>
                            </select>
                            <small class="text-muted" id="roleHint"></small>
                        </div>
                        <div class="mb-3" id="cabangField">
                            <label>Cabang</label>
                            <select name="cabang_id" class="form-select">
                                <option value="">Tanpa Cabang</option>
                            </select>
                            <small class="text-muted">Role pusat (bos, manager_pusat, admin_pusat, petugas_pusat) tidak memerlukan cabang</small>
                        </div>
                        <div class="mb-3">
                            <label>Gaji</label>
                            <input type="number" name="gaji" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>Limit Kas Bon</label>
                            <input type="number" name="limit_kasbon" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>Status</label>
                            <select name="status" class="form-select">
                                <option value="aktif">Aktif</option>
                                <option value="nonaktif">Nonaktif</option>
                            </select>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function formatRupiah(angka) {
            return new Intl.NumberFormat('id-ID').format(angka);
        }

        $(document).ready(function() {
            // Handle role selection to show/hide cabang field
            $('#roleSelect').on('change', function() {
                const role = $(this).val();
                const pusatRoles = ['bos', 'manager_pusat', 'admin_pusat', 'petugas_pusat'];
                const cabangRoles = ['manager_cabang', 'admin_cabang', 'petugas_cabang', 'karyawan'];
                
                if (pusatRoles.includes(role)) {
                    $('#cabangField').hide();
                    $('select[name="cabang_id"]').val('');
                    $('#roleHint').text('Role pusat tidak memerlukan cabang');
                } else if (cabangRoles.includes(role)) {
                    $('#cabangField').show();
                    $('#roleHint').text('Role cabang harus memiliki cabang');
                } else {
                    $('#cabangField').show();
                    $('#roleHint').text('');
                }
            });
            
            // Initialize with current selection
            $('#roleSelect').trigger('change');
            
            // Load cabang options via API
            loadCabangOptions();
            
            // Load users data via JSON API
            loadUsersData();
        });

        function loadCabangOptions() {
            window.KewerAPI.getCabang().done(response => {
                if (response.success && response.data) {
                    const cabangSelect = $('select[name="cabang_id"]');
                    response.data.forEach(c => {
                        cabangSelect.append(`<option value="${c.id}">${c.nama_cabang}</option>`);
                    });
                }
            });
        }

        function loadUsersData() {
            window.KewerAPI.getUsers().done(response => {
                if (response.success) {
                    renderUsersTable(response.data);
                } else {
                    $('#users-table-body').html('<tr><td colspan="9" class="text-center text-danger">Gagal memuat data</td></tr>');
                }
            }).fail(error => {
                $('#users-table-body').html('<tr><td colspan="9" class="text-center text-danger">Gagal memuat data</td></tr>');
            });
        }

        function renderUsersTable(data) {
            if (!data || data.length === 0) {
                $('#users-table-body').html('<tr><td colspan="9" class="text-center text-muted">Tidak ada data users</td></tr>');
                return;
            }

            const roleColors = {
                'bos': 'danger',
                'manager_pusat': 'primary',
                'manager_cabang': 'success',
                'admin_pusat': 'warning',
                'admin_cabang': 'info',
                'petugas_pusat': 'secondary',
                'petugas_cabang': 'dark',
                'karyawan': 'light text-dark'
            };

            const currentUserId = getCurrentUser().id;

            let html = '';
            data.forEach(u => {
                const roleColor = roleColors[u.role] || 'secondary';
                const statusClass = u.status === 'aktif' ? 'success' : 'danger';

                html += `
                    <tr>
                        <td>${u.id || '-'}</td>
                        <td>${u.username || '-'}</td>
                        <td>${u.nama || '-'}</td>
                        <td>${u.email || '-'}</td>
                        <td>
                            <span class="badge bg-${roleColor}">${u.role ? u.role.charAt(0).toUpperCase() + u.role.slice(1) : '-'}</span>
                        </td>
                        <td>${u.nama_cabang || '-'}</td>
                        <td>Rp ${formatRupiah(u.gaji || 0)}</td>
                        <td>
                            <span class="badge bg-${statusClass}">${u.status ? u.status.charAt(0).toUpperCase() + u.status.slice(1) : 'Aktif'}</span>
                        </td>
                        <td>
                            <a href="edit.php?id=${u.id}" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="permissions/index.php?user_id=${u.id}" class="btn btn-sm btn-info">
                                <i class="bi bi-shield-lock"></i>
                            </a>
                            ${u.id !== currentUserId ? `
                                <a href="hapus.php?id=${u.id}" class="btn btn-sm btn-danger" onclick="return confirm('Hapus user ini?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            ` : ''}
                        </td>
                    </tr>
                `;
            });

            $('#users-table-body').html(html);
        }
    </script>
</body>
</html>
