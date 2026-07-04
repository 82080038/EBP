<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();

if (!hasPermission('view_laporan') && !hasPermission('assign_permissions')) {
    header('Location: ' . baseUrl('dashboard.php'));
    exit();
}

$cabang_id = getCurrentCabang();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Trail - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .json-preview { max-height: 200px; overflow-y: auto; font-size: 11px; background: #f8f9fa; padding: 8px; border-radius: 4px; white-space: pre-wrap; word-break: break-all; }
        .sidebar { min-height: calc(100vh - 56px); }
    </style>
</head>
<body>
    <div class="main-container">
        <?php require_once BASE_PATH . '/includes/sidebar.php'; ?>
        
        <main class="content-area">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="bi bi-shield-check"></i> Audit Trail</h1>
                </div>
                
                <!-- Stats -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body py-2">
                                <h6>Total Log</h6>
                                <h4><?php echo number_format($stat['total']); ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body py-2">
                                <h6>User Aktif</h6>
                                <h4><?php echo $stat['unique_users']; ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body py-2">
                                <h6>Jenis Aksi</h6>
                                <h4><?php echo $stat['unique_actions']; ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body py-2">
                                <h6>Tabel Terdampak</h6>
                                <h4><?php echo $stat['unique_tables']; ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-2 align-items-end">
                            <div class="col-md-2">
                                <label class="form-label form-label-sm">Aksi</label>
                                <select name="action" class="form-select form-select-sm">
                                    <option value="">Semua Aksi</option>
                                    <?php foreach ($actions as $a): ?>
                                        <option value="<?php echo $a['action']; ?>" <?php echo $filter_action === $a['action'] ? 'selected' : ''; ?>>
                                            <?php echo ucfirst($a['action']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label form-label-sm">Tabel</label>
                                <select name="table_name" class="form-select form-select-sm">
                                    <option value="">Semua Tabel</option>
                                    <?php foreach ($tables as $t): ?>
                                        <option value="<?php echo $t['table_name']; ?>" <?php echo $filter_table === $t['table_name'] ? 'selected' : ''; ?>>
                                            <?php echo $t['table_name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label form-label-sm">User</label>
                                <select name="user_id" class="form-select form-select-sm">
                                    <option value="">Semua User</option>
                                    <?php foreach ($users as $u): ?>
                                        <option value="<?php echo $u['id']; ?>" <?php echo $filter_user == $u['id'] ? 'selected' : ''; ?>>
                                            <?php echo $u['nama']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label form-label-sm">Dari</label>
                                <input type="date" name="date_from" class="form-control form-control-sm" value="<?php echo $filter_date_from; ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label form-label-sm">Sampai</label>
                                <input type="date" name="date_to" class="form-control form-control-sm" value="<?php echo $filter_date_to; ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-sm btn-primary me-1"><i class="bi bi-search"></i> Filter</button>
                                <a href="index.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-clockwise"></i></a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Audit Log Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-sm table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th style="width:5%">#</th>
                                        <th style="width:14%">Waktu</th>
                                        <th style="width:12%">User</th>
                                        <th style="width:10%">Aksi</th>
                                        <th style="width:10%">Tabel</th>
                                        <th style="width:5%">ID</th>
                                        <th style="width:10%">IP</th>
                                        <th style="width:34%">Detail</th>
                                    </tr>
                                </thead>
                                <tbody id="audit-table-body">
                                    <tr>
                                        <td colspan="8" class="text-center">
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function formatDate(dateStr) {
            if (!dateStr) return '-';
            const date = new Date(dateStr);
            return date.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' });
        }

        // Load audit data via JSON API
        $(document).ready(function() {
            loadAuditData();
        });

        function loadAuditData() {
            const action = '<?php echo $_GET['action'] ?? ''; ?>';
            const table_name = '<?php echo $_GET['table_name'] ?? ''; ?>';
            const user_id = '<?php echo $_GET['user_id'] ?? ''; ?>';
            const date_from = '<?php echo $_GET['date_from'] ?? date('Y-m-d', strtotime('-7 days')); ?>';
            const date_to = '<?php echo $_GET['date_to'] ?? date('Y-m-d'); ?>';

            window.KewerAPI.getAuditLog({ action, table_name, user_id, date_from, date_to }).done(response => {
                if (response.success) {
                    renderAuditTable(response.data);
                } else {
                    $('#audit-table-body').html('<tr><td colspan="8" class="text-center text-danger">Gagal memuat data</td></tr>');
                }
            }).fail(error => {
                $('#audit-table-body').html('<tr><td colspan="8" class="text-center text-danger">Gagal memuat data</td></tr>');
            });
        }

        function renderAuditTable(data) {
            if (!data || data.length === 0) {
                $('#audit-table-body').html('<tr><td colspan="8" class="text-center text-muted">Tidak ada data audit</td></tr>');
                return;
            }

            const actionColors = {
                'create': 'success', 'insert': 'success',
                'update': 'warning', 'edit': 'warning',
                'delete': 'danger', 'hapus': 'danger',
                'login': 'info', 'logout': 'secondary',
                'approve': 'primary', 'reject': 'danger',
                'blacklist': 'danger', 'unblacklist': 'success',
                'pembayaran': 'success'
            };

            let html = '';
            data.forEach(log => {
                const color = actionColors[log.action] || 'secondary';
                const hasDetails = log.old_value || log.new_value;

                html += `
                    <tr>
                        <td>${log.id || '-'}</td>
                        <td><small>${formatDate(log.created_at)}</small></td>
                        <td>
                            <small>${log.nama_user || 'System'}</small>
                            ${log.role ? `<br><span class="badge bg-secondary">${log.role}</span>` : ''}
                        </td>
                        <td>
                            <span class="badge bg-${color}">${log.action ? log.action.charAt(0).toUpperCase() + log.action.slice(1) : '-'}</span>
                        </td>
                        <td><small>${log.table_name || '-'}</small></td>
                        <td>${log.record_id || '-'}</td>
                        <td><small>${log.ip_address || '-'}</small></td>
                        <td>
                            ${hasDetails ? `
                                <button class="btn btn-xs btn-outline-info" type="button" data-bs-toggle="collapse" data-bs-target="#detail-${log.id}">
                                    <i class="bi bi-eye"></i> Detail
                                </button>
                                <div class="collapse mt-1" id="detail-${log.id}">
                                    ${log.old_value ? `<div class="mb-1"><small class="text-danger">Sebelum:</small></div><div class="json-preview">${log.old_value}</div>` : ''}
                                    ${log.new_value ? `<div class="mb-1 mt-1"><small class="text-success">Sesudah:</small></div><div class="json-preview">${log.new_value}</div>` : ''}
                                </div>
                            ` : '<small class="text-muted">-</small>'}
                        </td>
                    </tr>
                `;
            });

            $('#audit-table-body').html(html);
        }
    </script>
</body>
</html>
