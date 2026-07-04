<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
require_once BASE_PATH . '/config/session.php';
requireLogin();

// Permission check
if (!hasPermission('pinjaman.auto_confirm')) {
    header('Location: ' . baseUrl('dashboard.php'));
    exit();
}

$user = getCurrentUser();
$cabang_id = getCurrentCabang();

// Get auto-confirm settings
require_once BASE_PATH . '/includes/auto_confirm.php';
$globalSettings = getAutoConfirmSettings(null);
$branchSettings = $cabang_id ? getAutoConfirmSettings($cabang_id) : null;

// Get all branch settings (for owner/admin at pusat)
$allSettings = [];
if (in_array($user['role'], ['bos', 'manager_pusat', 'manager_cabang'])) {
    $allSettings = query("SELECT acs.*, c.nama_cabang 
                          FROM auto_confirm_settings acs 
                          LEFT JOIN cabang c ON acs.cabang_id = c.id 
                          ORDER BY acs.cabang_id IS NULL, c.nama_cabang");
}

// Get auto-confirm stats
$stats = getAutoConfirmStats($cabang_id);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Auto-Confirm - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="main-container">
        <?php require_once BASE_PATH . '/includes/sidebar.php'; ?>

        <main class="content-area">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="bi bi-gear"></i> Pengaturan Auto-Confirm Pinjaman</h2>
                    <div>
                        <?php if ($user['role'] === 'bos'): ?>
                            <button class="btn btn-primary" onclick="showGlobalModal()">
                                <i class="bi bi-globe"></i> Pengaturan Global
                            </button>
                        <?php endif; ?>
                        <?php if ($cabang_id): ?>
                            <button class="btn btn-success" onclick="showBranchModal()">
                                <i class="bi bi-building"></i> Pengaturan Cabang
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Pinjaman</h5>
                                <h3 class="card-text"><?php echo $stats['total_pinjaman'] ?? 0; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Auto-Confirmed</h5>
                                <h3 class="card-text"><?php echo $stats['auto_confirmed_count'] ?? 0; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-dark">
                            <div class="card-body">
                                <h5 class="card-title">Manual Approved</h5>
                                <h3 class="card-text"><?php echo $stats['manual_approved_count'] ?? 0; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title">Avg Plafon Auto-Confirm</h5>
                                <h3 class="card-text"><?php echo 'Rp' . number_format($stats['avg_auto_confirmed_plafon'] ?? 0, 0, ',', '.'); ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Current Settings -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Pengaturan Saat Ini</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Pengaturan Global</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td>Status</td>
                                        <td>
                                            <span class="badge <?php echo $globalSettings['enabled'] ? 'bg-success' : 'bg-secondary'; ?>">
                                                <?php echo $globalSettings['enabled'] ? 'Aktif' : 'Nonaktif'; ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Batas Plafon</td>
                                        <td><?php echo 'Rp' . number_format($globalSettings['plafon_threshold'], 0, ',', '.'); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Batas Tenor</td>
                                        <td><?php echo $globalSettings['tenor_limit']; ?> bulan</td>
                                    </tr>
                                    <tr>
                                        <td>Max Risk Score</td>
                                        <td><?php echo $globalSettings['max_risk_score']; ?></td>
                                    </tr>
                                </table>
                            </div>
                            <?php if ($branchSettings): ?>
                            <div class="col-md-6">
                                <h6>Pengaturan Cabang</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td>Status</td>
                                        <td>
                                            <span class="badge <?php echo $branchSettings['enabled'] ? 'bg-success' : 'bg-secondary'; ?>">
                                                <?php echo $branchSettings['enabled'] ? 'Aktif' : 'Nonaktif'; ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Batas Plafon</td>
                                        <td><?php echo 'Rp' . number_format($branchSettings['plafon_threshold'], 0, ',', '.'); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Batas Tenor</td>
                                        <td><?php echo $branchSettings['tenor_limit']; ?> bulan</td>
                                    </tr>
                                    <tr>
                                        <td>Max Risk Score</td>
                                        <td><?php echo $branchSettings['max_risk_score']; ?></td>
                                    </tr>
                                </table>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- All Branch Settings (for owner/admin) -->
                <?php if ($user['role'] === 'bos'): ?>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Pengaturan Semua Cabang</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Cabang</th>
                                        <th>Status</th>
                                        <th>Batas Plafon</th>
                                        <th>Batas Tenor</th>
                                        <th>Max Risk</th>
                                        <th>Riwayat Nasabah</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($allSettings as $setting): ?>
                                    <tr>
                                        <td><?php echo $setting['cabang_id'] ? htmlspecialchars($setting['nama_cabang']) : '<strong>Global</strong>'; ?></td>
                                        <td>
                                            <span class="badge <?php echo $setting['enabled'] ? 'bg-success' : 'bg-secondary'; ?>">
                                                <?php echo $setting['enabled'] ? 'Aktif' : 'Nonaktif'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo 'Rp' . number_format($setting['plafon_threshold'], 0, ',', '.'); ?></td>
                                        <td><?php echo $setting['tenor_limit']; ?> bulan</td>
                                        <td><?php echo $setting['max_risk_score']; ?></td>
                                        <td>
                                            <?php if ($setting['require_nasabah_history']): ?>
                                                <?php echo $setting['min_nasabah_history_months']; ?> bulan
                                            <?php else: ?>
                                                Tidak required
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" onclick="editSetting(<?php echo $setting['cabang_id'] ?: 'null'; ?>)">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
    
    <!-- Global Settings Modal -->
    <div class="modal fade" id="globalModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Pengaturan Auto-Confirm Global</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="globalForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status Auto-Confirm</label>
                                <select class="form-select" name="enabled">
                                    <option value="false" <?php echo !$globalSettings['enabled'] ? 'selected' : ''; ?>>Nonaktif</option>
                                    <option value="true" <?php echo $globalSettings['enabled'] ? 'selected' : ''; ?>>Aktif</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Batas Plafon (Rp)</label>
                                <input type="number" class="form-control" name="plafon_threshold" value="<?php echo $globalSettings['plafon_threshold']; ?>">
                                <small class="text-muted">Pinjaman di atas plafon ini tidak akan di-auto-confirm</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Batas Tenor (bulan)</label>
                                <input type="number" class="form-control" name="tenor_limit" value="<?php echo $globalSettings['tenor_limit']; ?>">
                                <small class="text-muted">Tenor di atas batas ini tidak akan di-auto-confirm</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Max Risk Score</label>
                                <input type="number" class="form-control" name="max_risk_score" value="<?php echo $globalSettings['max_risk_score']; ?>">
                                <small class="text-muted">Risk score di atas nilai ini tidak akan di-auto-confirm</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3 form-check">
                                <input type="checkbox" class="form-check-input" name="require_nasabah_history" id="requireHistory" <?php echo $globalSettings['require_nasabah_history'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="requireHistory">Require Riwayat Nasabah</label>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Minimal Riwayat Nasabah (bulan)</label>
                                <input type="number" class="form-control" name="min_nasabah_history_months" value="<?php echo $globalSettings['min_nasabah_history_months']; ?>">
                        </div>
                        <input type="hidden" name="cabang_id" value="null">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary" onclick="saveGlobalSettings()">Simpan</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Branch Settings Modal -->
    <div class="modal fade" id="branchModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Pengaturan Auto-Confirm Cabang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="branchForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status Auto-Confirm</label>
                                <select class="form-select" name="enabled">
                                    <option value="false" <?php echo !$branchSettings['enabled'] ? 'selected' : ''; ?>>Nonaktif</option>
                                    <option value="true" <?php echo $branchSettings['enabled'] ? 'selected' : ''; ?>>Aktif</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Batas Plafon (Rp)</label>
                                <input type="number" class="form-control" name="plafon_threshold" value="<?php echo $branchSettings['plafon_threshold'] ?? 0; ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Batas Tenor (bulan)</label>
                                <input type="number" class="form-control" name="tenor_limit" value="<?php echo $branchSettings['tenor_limit'] ?? 0; ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Max Risk Score</label>
                                <input type="number" class="form-control" name="max_risk_score" value="<?php echo $branchSettings['max_risk_score'] ?? 10; ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3 form-check">
                                <input type="checkbox" class="form-check-input" name="require_nasabah_history" id="requireHistoryBranch" <?php echo $branchSettings['require_nasabah_history'] ?? true ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="requireHistoryBranch">Require Riwayat Nasabah</label>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Minimal Riwayat Nasabah (bulan)</label>
                                <input type="number" class="form-control" name="min_nasabah_history_months" value="<?php echo $branchSettings['min_nasabah_history_months'] ?? 3; ?>">
                            </div>
                        </div>
                        <input type="hidden" name="cabang_id" value="<?php echo $cabang_id; ?>">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary" onclick="saveBranchSettings()">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showGlobalModal() {
            new bootstrap.Modal(document.getElementById('globalModal')).show();
        }
        
        function showBranchModal() {
            new bootstrap.Modal(document.getElementById('branchModal')).show();
        }
        
        function saveGlobalSettings() {
            const form = document.getElementById('globalForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            data.enabled = data.enabled === 'true';
            data.cabang_id = null;
            data.require_nasabah_history = document.getElementById('requireHistory').checked;
            
            fetch('<?php echo baseUrl('api/auto_confirm_settings.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    Swal.fire('Sukses', result.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    Swal.fire('Error', result.error || 'Gagal menyimpan pengaturan', 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error', 'Terjadi kesalahan', 'error');
            });
        }
        
        function saveBranchSettings() {
            const form = document.getElementById('branchForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            data.enabled = data.enabled === 'true';
            data.require_nasabah_history = document.getElementById('requireHistoryBranch').checked;
            
            fetch('<?php echo baseUrl('api/auto_confirm_settings.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    Swal.fire('Sukses', result.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    Swal.fire('Error', result.error || 'Gagal menyimpan pengaturan', 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error', 'Terjadi kesalahan', 'error');
            });
        }
        
        function editSetting(cabangId) {
            // Load setting data and show modal
            // This would need to be implemented for editing specific branch settings
            Swal.fire('Info', 'Fitur edit akan segera tersedia', 'info');
        }
    </script>
</body>
</html>
