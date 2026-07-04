<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
require_once BASE_PATH . '/config/session.php';
requireLogin();

// Permission check
if (!hasPermission('kas.read')) {
    header('Location: ' . baseUrl('dashboard.php'));
    exit();
}

$user = getCurrentUser();
$cabang_id = getCurrentCabang();
$role = $user['role'];

// Get reconciliation data via API
$apiUrl = baseUrl('api/daily_cash_reconciliation.php');
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl . '?cabang_id=' . $cabang_id);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer kewer-api-token-2024',
    'Content-Type: application/json'
]);
$response = curl_exec($ch);
curl_close($ch);

$reconciliationData = json_decode($response, true);
$reconciliationList = ($reconciliationData['success'] ?? false) && isset($reconciliationData['data']) ? $reconciliationData['data'] : [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekonsiliasi Kas Harian - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="main-container">
        <?php require_once BASE_PATH . '/includes/sidebar.php'; ?>

        <main class="content-area">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="bi bi-calculator"></i> Rekonsiliasi Kas Harian</h2>
                    <?php if (in_array($role, ['manager_pusat', 'manager_cabang', 'teller'])): ?>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                        <i class="bi bi-plus"></i> Buat Rekonsiliasi
                    </button>
                    <?php endif; ?>
                </div>
                
                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Rekonsiliasi</h5>
                                <h3 class="card-text"><?php echo count($reconciliationList); ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Approved</h5>
                                <h3 class="card-text"><?php echo count(array_filter($reconciliationList, fn($r) => $r['status'] === 'approved')); ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-dark">
                            <div class="card-body">
                                <h5 class="card-title">Pending</h5>
                                <h3 class="card-text"><?php echo count(array_filter($reconciliationList, fn($r) => $r['status'] === 'draft' || $r['status'] === 'submitted')); ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body">
                                <h5 class="card-title">Ada Selisih</h5>
                                <h3 class="card-text"><?php echo count(array_filter($reconciliationList, fn($r) => $r['selisih'] != 0)); ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" id="filterStatus">
                                    <option value="">Semua Status</option>
                                    <option value="draft">Draft</option>
                                    <option value="submitted">Submitted</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Tanggal Mulai</label>
                                <input type="date" class="form-control" id="filterTanggalMulai">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Tanggal Selesai</label>
                                <input type="date" class="form-control" id="filterTanggalSelesai">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Reconciliation Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="reconciliationTable">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Kas Awal</th>
                                        <th>Penerimaan</th>
                                        <th>Pengeluaran</th>
                                        <th>Kas Akhir</th>
                                        <th>Kas Fisik</th>
                                        <th>Selisih</th>
                                        <th>Status</th>
                                        <th>Prepared By</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reconciliationList as $rec): ?>
                                    <tr data-status="<?php echo $rec['status']; ?>" data-tanggal="<?php echo $rec['tanggal']; ?>">
                                        <td><?php echo date('d/m/Y', strtotime($rec['tanggal'])); ?></td>
                                        <td><?php echo 'Rp' . number_format($rec['kas_awal'], 0, ',', '.'); ?></td>
                                        <td><?php echo 'Rp' . number_format($rec['total_penerimaan'], 0, ',', '.'); ?></td>
                                        <td><?php echo 'Rp' . number_format($rec['total_pengeluaran'], 0, ',', '.'); ?></td>
                                        <td><?php echo 'Rp' . number_format($rec['kas_akhir'], 0, ',', '.'); ?></td>
                                        <td><?php echo 'Rp' . number_format($rec['kas_fisik'], 0, ',', '.'); ?></td>
                                        <td>
                                            <span class="<?php echo $rec['selisih'] == 0 ? 'text-success' : 'text-danger'; ?>">
                                                <?php echo 'Rp' . number_format($rec['selisih'], 0, ',', '.'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo $rec['status'] === 'approved' ? 'bg-success' : ($rec['status'] === 'draft' ? 'bg-secondary' : ($rec['status'] === 'submitted' ? 'bg-info' : 'bg-danger')); ?>">
                                                <?php echo ucfirst($rec['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($rec['prepared_by_nama'] ?? '-'); ?></td>
                                        <td>
                                            <?php if ($rec['status'] === 'draft'): ?>
                                            <button class="btn btn-sm btn-primary" onclick="editReconciliation(<?php echo $rec['id']; ?>)">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-info" onclick="submitReconciliation(<?php echo $rec['id']; ?>)">
                                                <i class="bi bi-send"></i>
                                            </button>
                                            <?php endif; ?>
                                            <?php if ($rec['status'] === 'submitted' && in_array($role, ['bos', 'manager_cabang'])): ?>
                                            <button class="btn btn-sm btn-success" onclick="approveReconciliation(<?php echo $rec['id']; ?>)">
                                                <i class="bi bi-check"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="rejectReconciliation(<?php echo $rec['id']; ?>)">
                                                <i class="bi bi-x"></i>
                                            </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
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
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Buat Rekonsiliasi Kas Harian</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="reconciliationForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tanggal *</label>
                                    <input type="date" class="form-control flatpickr-date" name="tanggal" value="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" name="status">
                                        <option value="draft">Draft</option>
                                        <option value="submitted">Submitted</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Kas Awal *</label>
                                    <input type="number" class="form-control" name="kas_awal" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Total Penerimaan *</label>
                                    <input type="number" class="form-control" name="total_penerimaan" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Total Pengeluaran *</label>
                                    <input type="number" class="form-control" name="total_pengeluaran" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Kas Akhir (Auto)</label>
                                    <input type="number" class="form-control" name="kas_akhir" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Kas Fisik *</label>
                                    <input type="number" class="form-control" name="kas_fisik" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Selisih (Auto)</label>
                                    <input type="number" class="form-control" name="selisih" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Catatan</label>
                            <textarea class="form-control" name="keterangan" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" onclick="saveReconciliation()">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-calculate kas_akhir and selisih
        $('input[name="kas_awal"], input[name="total_penerimaan"], input[name="total_pengeluaran"]').on('input', function() {
            const kasAwal = parseFloat($('input[name="kas_awal"]').val()) || 0;
            const penerimaan = parseFloat($('input[name="total_penerimaan"]').val()) || 0;
            const pengeluaran = parseFloat($('input[name="total_pengeluaran"]').val()) || 0;
            const kasAkhir = kasAwal + penerimaan - pengeluaran;
            $('input[name="kas_akhir"]').val(kasAkhir);
            
            const kasFisik = parseFloat($('input[name="kas_fisik"]').val()) || 0;
            const selisih = kasFisik - kasAkhir;
            $('input[name="selisih"]').val(selisih);
        });
        
        $('input[name="kas_fisik"]').on('input', function() {
            const kasAkhir = parseFloat($('input[name="kas_akhir"]').val()) || 0;
            const kasFisik = parseFloat($('input[name="kas_fisik"]').val()) || 0;
            const selisih = kasFisik - kasAkhir;
            $('input[name="selisih"]').val(selisih);
        });
        
        // Filter functionality
        $('#filterStatus, #filterTanggalMulai, #filterTanggalSelesai').on('change', function() {
            const status = $('#filterStatus').val();
            const tanggalMulai = $('#filterTanggalMulai').val();
            const tanggalSelesai = $('#filterTanggalSelesai').val();
            
            $('#reconciliationTable tbody tr').each(function() {
                const row = $(this);
                
                if (status && row.data('status') !== status) {
                    row.hide();
                    return;
                }
                
                if (tanggalMulai && row.data('tanggal') < tanggalMulai) {
                    row.hide();
                    return;
                }
                
                if (tanggalSelesai && row.data('tanggal') > tanggalSelesai) {
                    row.hide();
                    return;
                }
                
                row.show();
            });
        });
        
        function saveReconciliation() {
            const form = document.getElementById('reconciliationForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());

            fetch('<?php echo baseUrl('api/daily_cash_reconciliation.php'); ?>', {
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
                    Swal.fire('Error', result.error || 'Gagal menyimpan rekonsiliasi', 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error', 'Terjadi kesalahan', 'error');
            });
        }
        
        function editReconciliation(id) {
            Swal.fire('Info', 'Fitur edit akan segera tersedia', 'info');
        }
        
        function submitReconciliation(id) {
            Swal.fire({
                title: 'Submit Rekonsiliasi?',
                text: 'Anda yakin ingin submit rekonsiliasi ini?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Submit',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('<?php echo baseUrl('api/daily_cash_reconciliation.php'); ?>?id=' + id, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ status: 'submitted' })
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            Swal.fire('Sukses', result.message, 'success');
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            Swal.fire('Error', result.error || 'Gagal submit rekonsiliasi', 'error');
                        }
                    })
                    .catch(error => {
                        Swal.fire('Error', 'Terjadi kesalahan', 'error');
                    });
                }
            });
        }
        
        function approveReconciliation(id) {
            Swal.fire({
                title: 'Approve Rekonsiliasi?',
                text: 'Anda yakin ingin menyetujui rekonsiliasi ini?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Approve',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('<?php echo baseUrl('api/daily_cash_reconciliation.php'); ?>?id=' + id, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ status: 'approved' })
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            Swal.fire('Sukses', result.message, 'success');
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            Swal.fire('Error', result.error || 'Gagal approve rekonsiliasi', 'error');
                        }
                    })
                    .catch(error => {
                        Swal.fire('Error', 'Terjadi kesalahan', 'error');
                    });
                }
            });
        }
        
        function rejectReconciliation(id) {
            Swal.fire({
                title: 'Reject Rekonsiliasi?',
                text: 'Anda yakin ingin menolak rekonsiliasi ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Reject',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('<?php echo baseUrl('api/daily_cash_reconciliation.php'); ?>?id=' + id, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ status: 'rejected' })
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            Swal.fire('Sukses', result.message, 'success');
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            Swal.fire('Error', result.error || 'Gagal reject rekonsiliasi', 'error');
                        }
                    })
                    .catch(error => {
                        Swal.fire('Error', 'Terjadi kesalahan', 'error');
                    });
                }
            });
        }
    </script>
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
