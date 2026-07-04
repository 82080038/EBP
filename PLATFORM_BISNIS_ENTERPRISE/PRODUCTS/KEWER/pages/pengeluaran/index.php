<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengeluaran - <?php echo APP_NAME; ?></title>
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
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="bi bi-receipt"></i> Pengeluaran Operasional</h2>
                    <a href="../../dashboard.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>

                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> 
                    Tracking biaya operasional: gaji, lembur, bonus, operasional, belanja rutin.
                </div>

                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h6>Total Pengeluaran</h6>
                                <h3 id="total-pengeluaran">Rp 0</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h6>Jumlah Transaksi</h6>
                                <h3 id="jumlah-transaksi">0</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pending Approvals Alert -->
                <div id="pending-alert" class="alert alert-warning mb-4" style="display: none;">
                    <h6><i class="bi bi-exclamation-triangle"></i> <span id="pending-count">0</span> Pengeluaran Menunggu Approval</h6>
                    <button class="btn btn-sm btn-primary" onclick="showPending()">
                        <i class="bi bi-eye"></i> Lihat Pending
                    </button>
                </div>

                <!-- Filter -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row">
                            <div class="col-md-3">
                                <label>Kategori</label>
                                <select class="form-select" name="kategori">
                                    <option value="">Semua</option>
                                    <option value="gaji" <?= $filters['kategori'] == 'gaji' ? 'selected' : '' ?>>Gaji</option>
                                    <option value="lembur" <?= $filters['kategori'] == 'lembur' ? 'selected' : '' ?>>Lembur</option>
                                <label>Status</label>
                                <select class="form-select" id="filterStatus" name="status">
                                    <option value="">Semua Status</option>
                                    <option value="draft">Draft</option>
                                    <option value="submitted">Submitted</option>
                                    <option value="approved" <?= $filters['status'] == 'approved' ? 'selected' : '' ?>>Approved</option>
                                    <option value="rejected" <?= $filters['status'] == 'rejected' ? 'selected' : '' ?>>Rejected</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label>Kategori</label>
                                <select class="form-select" id="filterKategori" name="kategori">
                                    <option value="">Semua Kategori</option>
                                    <?php
                                    $kategori_list = getActiveReferenceData('ref_kategori_pengeluaran');
                                    if (is_array($kategori_list)):
                                        foreach ($kategori_list as $kat):
                                            $kode = str_replace('KAT', '', strtolower($kat['kategori_kode']));
                                    ?>
                                        <option value="<?php echo $kode; ?>" <?= $filters['kategori'] == $kode ? 'selected' : '' ?>><?php echo htmlspecialchars($kat['kategori_nama']); ?></option>
                                    <?php
                                        endforeach;
                                    endif;
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label>Tanggal Mulai</label>
                                <input type="date" class="form-control" name="tanggal_mulai" value="<?= $filters['tanggal_mulai'] ?>">
                            </div>
                            <div class="col-md-2">
                                <label>Tanggal Selesai</label>
                                <input type="date" class="form-control" name="tanggal_selesai" value="<?= $filters['tanggal_selesai'] ?>">
                            </div>
                            <div class="col-md-2">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-filter"></i> Filter
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Expenses Table -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Daftar Pengeluaran</h5>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
                            <i class="bi bi-plus"></i> Tambah Pengeluaran
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="pengeluaranTable">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Kategori</th>
                                        <th>Sub Kategori</th>
                                        <th>Jumlah</th>
                                        <th>Keterangan</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="pengeluaran-table-body">
                                    <tr>
                                        <td colspan="7" class="text-center">
                                            <div class="spinner-border spinner-border-sm" role="status"></div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Pengeluaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addForm">
                        <div class="row">
                            <div class="mb-3">
                                <label class="form-label">Kategori</label>
                                <select class="form-select" name="kategori">
                                    <?php
                                    $kategori_list = getActiveReferenceData('ref_kategori_pengeluaran');
                                    if (is_array($kategori_list)):
                                        foreach ($kategori_list as $kat):
                                            $kode = str_replace('KAT', '', strtolower($kat['kategori_kode']));
                                    ?>
                                        <option value="<?php echo $kode; ?>"><?php echo htmlspecialchars($kat['kategori_nama']); ?></option>
                                    <?php
                                        endforeach;
                                    endif;
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Sub Kategori</label>
                                <input type="text" class="form-control" name="sub_kategori">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Jumlah</label>
                                <input type="number" class="form-control" name="jumlah" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Tanggal</label>
                                <input type="date" class="form-control" name="tanggal" value="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label>Keterangan</label>
                            <textarea class="form-control" name="keterangan" rows="2"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" onclick="saveExpense()">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/id.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/id.js"></script>
    <script src="/kewer/includes/js/auto-focus.js"></script>
    <script src="/kewer/includes/js/enter-navigation.js"></script>
    <script>
        $(document).ready(function() {
            // Only initialize DataTable if there's data
            var hasData = <?php echo !empty($expenses) ? 'true' : 'false'; ?>;

            if (hasData) {
                try {
                    var table = $('#pengeluaranTable').DataTable({
                        language: {
                            search: "Cari:",
                            lengthMenu: "Tampilkan _MENU_ data per halaman",
                            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                            infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                            infoFiltered: "(difilter dari _MAX_ total data)",
                            paginate: {
                                first: "Pertama",
                                last: "Terakhir",
                                next: "Selanjutnya",
                                previous: "Sebelumnya"
                            },
                            emptyTable: "Tidak ada data tersedia",
                            zeroRecords: "Tidak ada data yang cocok"
                        },
                        pageLength: 25,
                        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                        responsive: true,
                        order: [[0, 'desc']]
                    });
                } catch (e) {
                    console.error('DataTables initialization error:', e);
                    $('#pengeluaranTable').removeClass('table-striped table-hover');
                }
            } else {
                // Hide DataTables controls when no data
                $('#pengeluaranTable').removeClass('table-striped table-hover');
                $('#pengeluaranTable_wrapper').hide();
            }
            
            // Initialize Select2
            $('.form-select').select2({
                theme: 'bootstrap-5',
                language: 'id',
                width: '100%'
            });
            
            // Initialize Flatpickr for date inputs
            flatpickr('input[type="date"]', {
                locale: 'id',
                dateFormat: 'Y-m-d',
                allowInput: true,
                altInput: true,
                altFormat: 'd F Y',
                theme: 'light'
            });
        });
        
        // Load pengeluaran data via JSON API
        $(document).ready(function() {
            loadPengeluaranData();
        });

        function loadPengeluaranData() {
            const kategori = '<?php echo $_GET['kategori'] ?? ''; ?>';
            const status = '<?php echo $_GET['status'] ?? ''; ?>';
            const tanggal_mulai = '<?php echo $_GET['tanggal_mulai'] ?? date('Y-m-01'); ?>';
            const tanggal_selesai = '<?php echo $_GET['tanggal_selesai'] ?? date('Y-m-t'); ?>';
            
            window.KewerAPI.getPengeluaran({ kategori, status, tanggal_mulai, tanggal_selesai }).done(response => {
                if (response.success) {
                    if (response.total) {
                        $('#total-pengeluaran').text('Rp ' + formatRupiah(response.total.total_pengeluaran));
                        $('#jumlah-transaksi').text(response.total.jumlah_transaksi);
                    }
                    if (response.pending && response.pending.length > 0) {
                        $('#pending-count').text(response.pending.length);
                        $('#pending-alert').show();
                    }
                    renderPengeluaranTable(response.data);
                } else {
                    $('#pengeluaran-table-body').html('<tr><td colspan="7" class="text-center text-danger">Gagal memuat data</td></tr>');
                }
            }).fail(error => {
                $('#pengeluaran-table-body').html('<tr><td colspan="7" class="text-center text-danger">Gagal memuat data</td></tr>');
            });
        }

        function renderPengeluaranTable(data) {
            if (!data || data.length === 0) {
                $('#pengeluaran-table-body').html('<tr><td colspan="7" class="text-center text-muted">Tidak ada data pengeluaran</td></tr>');
                return;
            }

            let html = '';
            data.forEach(exp => {
                const statusClass = {
                    'pending': 'warning',
                    'approved': 'success',
                    'rejected': 'danger'
                }[exp.status] || 'secondary';

                html += `
                    <tr>
                        <td>${formatDate(exp.tanggal)}</td>
                        <td>${exp.kategori ? exp.kategori.charAt(0).toUpperCase() + exp.kategori.slice(1) : '-'}</td>
                        <td>${exp.sub_kategori || '-'}</td>
                        <td>Rp ${formatRupiah(exp.jumlah)}</td>
                        <td>${exp.keterangan || '-'}</td>
                        <td>
                            <span class="badge bg-${statusClass}">${exp.status ? exp.status.charAt(0).toUpperCase() + exp.status.slice(1) : 'Pending'}</span>
                        </td>
                        <td>
                            ${exp.status === 'pending' ? `
                                <button class="btn btn-sm btn-success" onclick="approveExpense(${exp.id})">
                                    <i class="bi bi-check"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="rejectExpense(${exp.id})">
                                    <i class="bi bi-x"></i>
                                </button>
                            ` : ''}
                        </td>
                    </tr>
                `;
            });

            $('#pengeluaran-table-body').html(html);
        }

        function formatRupiah(angka) {
            return new Intl.NumberFormat('id-ID').format(angka);
        }

        function formatDate(dateStr) {
            if (!dateStr) return '-';
            const date = new Date(dateStr);
            return date.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' });
        }

        function saveExpense() {
            const form = document.getElementById('addForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());

            fetch('/api/pengeluaran', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer kewer-api-token-2024'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Pengeluaran berhasil ditambahkan');
                    location.reload();
                } else {
                    alert('Gagal menambahkan pengeluaran: ' + data.error);
                }
            });
        }

        function approveExpense(id) {
            if (confirm('Approve pengeluaran ini?')) {
                fetch(`/api/pengeluaran?id=${id}&action=approve`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer kewer-api-token-2024'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Pengeluaran berhasil di-approve');
                        location.reload();
                    }
                });
            }
        }

        function rejectExpense(id) {
            if (confirm('Reject pengeluaran ini?')) {
                fetch(`/api/pengeluaran?id=${id}&action=reject`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer kewer-api-token-2024'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Pengeluaran berhasil di-reject');
                        location.reload();
                    }
                });
            }
        }

        function showPending() {
            window.location.href = '?status=pending';
        }

        // Convert session alerts to SweetAlert2
        <?= getSessionAlertsJS() ?>
    </script>
</body>
</html>
