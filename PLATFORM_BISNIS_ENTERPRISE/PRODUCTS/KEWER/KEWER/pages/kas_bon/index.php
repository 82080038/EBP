<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();

// Only users with kas_bon management permission can access
if (!hasPermission('manage_kas_bon') && !hasPermission('view_kas_bon')) {
    header('Location: ' . baseUrl('dashboard.php'));
    exit();
}

$user = getCurrentUser();
$role = $user['role'];
$user_cabang_id = $user['cabang_id'] ?? null;
$kantor_id = $user_cabang_id ?? 1;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kas Bon - <?php echo APP_NAME; ?></title>
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
                    <h2><i class="bi bi-cash"></i> Kas Bon Karyawan</h2>
                    <a href="../../dashboard.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>

                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> 
                    Kas bon adalah pinjaman dana untuk karyawan yang akan dipotong dari gaji bulanan.
                </div>

                <!-- Pending Approvals Alert -->
                <div id="pending-alert" class="alert alert-warning mb-4" style="display: none;">
                    <h6><i class="bi bi-exclamation-triangle"></i> <span id="pending-count">0</span> Pengajuan Kas Bon Menunggu Approval</h6>
                    <button class="btn btn-sm btn-primary" onclick="showPending()">
                        <i class="bi bi-eye"></i> Lihat Pending
                    </button>
                </div>

                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h6>Total Kas Bon</h6>
                                <h3 id="total-kasbon">0</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h6>Total Jumlah</h6>
                                <h3 id="total-jumlah">Rp 0</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-dark">
                            <div class="card-body">
                                <h6>Total Sisa</h6>
                                <h3 id="total-sisa">Rp 0</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h6>Selesai</h6>
                                <h3 id="selesai">0</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row">
                            <div class="col-md-3">
                                <label>Status</label>
                                <select class="form-select" name="status">
                                    <option value="">Semua</option>
                                    <option value="pengajuan" <?= $status == 'pengajuan' ? 'selected' : '' ?>>Pengajuan</option>
                                    <option value="disetujui" <?= $status == 'disetujui' ? 'selected' : '' ?>>Disetujui</option>
                                    <option value="diberikan" <?= $status == 'diberikan' ? 'selected' : '' ?>>Diberikan</option>
                                    <option value="dipotong" <?= $status == 'dipotong' ? 'selected' : '' ?>>Dipotong</option>
                                    <option value="selesai" <?= $status == 'selesai' ? 'selected' : '' ?>>Selesai</option>
                                    <option value="ditolak" <?= $status == 'ditolak' ? 'selected' : '' ?>>Ditolak</option>
                                </select>
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

                <!-- Kas Bon Table -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Daftar Kas Bon</h5>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
                            <i class="bi bi-plus"></i> Ajukan Kas Bon
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="kasBonTable">
                                <thead>
                                    <tr>
                                        <th>Kode</th>
                                        <th>Karyawan</th>
                                        <th>Tanggal Pengajuan</th>
                                        <th>Jumlah</th>
                                        <th>Tenor</th>
                                        <th>Potongan/Bulan</th>
                                        <th>Sisa</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="kasbon-table-body">
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
            </div>
        </div>
    </div>

    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ajukan Kas Bon</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addForm">
                        <div class="mb-3">
                            <label>Karyawan</label>
                            <select class="form-select" name="karyawan_id" required>
                                <option value="">Pilih Karyawan</option>
                                <?php
                                $karyawan = query("SELECT * FROM users WHERE role IN ('teller','petugas_pusat','petugas_cabang') AND (cabang_id = ? OR cabang_id IS NULL) AND status = 'aktif'", [$cabang_id]);
                                if (!is_array($karyawan)) {
                                    $karyawan = [];
                                }
                                foreach ($karyawan as $k): ?>
                                    <option value="<?= $k['id'] ?>"><?= $k['nama'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Jumlah</label>
                                <input type="number" class="form-control" name="jumlah" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Tenor (bulan)</label>
                                <input type="number" class="form-control" name="tenor_bulan" value="1" min="1" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label>Tujuan</label>
                            <textarea class="form-control" name="tujuan" rows="2" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label>Catatan</label>
                            <textarea class="form-control" name="catatan" rows="2"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" onclick="saveKasBon()">Ajukan</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/id.js"></script>
    <script src="/kewer/includes/js/auto-focus.js"></script>
    <script src="/kewer/includes/js/enter-navigation.js"></script>
    <script>
        $(document).ready(function() {
            // Only initialize DataTable if there's data
            var hasData = <?php echo !empty($kasbons) ? 'true' : 'false'; ?>;

            if (hasData) {
                try {
                    var table = $('#kasBonTable').DataTable({
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
                    $('#kasBonTable').removeClass('table-striped table-hover');
                }
            } else {
                // Hide DataTables controls when no data
                $('#kasBonTable').removeClass('table-striped table-hover');
                $('#kasBonTable_wrapper').hide();
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
        
        // Load kas bon data via JSON API
        $(document).ready(function() {
            loadKasBonData();
        });

        function loadKasBonData() {
            const status = '<?php echo $_GET['status'] ?? ''; ?>';
            
            window.KewerAPI.getKasBon({ status }).done(response => {
                if (response.success) {
                    if (response.stats) {
                        $('#total-kasbon').text(response.stats.total_kasbon || 0);
                        $('#total-jumlah').text('Rp ' + formatRupiah(response.stats.total_jumlah || 0));
                        $('#total-sisa').text('Rp ' + formatRupiah(response.stats.total_sisa || 0));
                        $('#selesai').text(response.stats.selesai || 0);
                    }
                    if (response.pending && response.pending.length > 0) {
                        $('#pending-count').text(response.pending.length);
                        $('#pending-alert').show();
                    }
                    renderKasBonTable(response.data);
                } else {
                    $('#kasbon-table-body').html('<tr><td colspan="9" class="text-center text-danger">Gagal memuat data</td></tr>');
                }
            }).fail(error => {
                $('#kasbon-table-body').html('<tr><td colspan="9" class="text-center text-danger">Gagal memuat data</td></tr>');
            });
        }

        function renderKasBonTable(data) {
            if (!data || data.length === 0) {
                $('#kasbon-table-body').html('<tr><td colspan="9" class="text-center text-muted">Tidak ada data kas bon</td></tr>');
                return;
            }

            let html = '';
            data.forEach(kb => {
                const statusClass = {
                    'pengajuan': 'warning',
                    'disetujui': 'info',
                    'diberikan': 'primary',
                    'dipotong': 'secondary',
                    'selesai': 'success',
                    'ditolak': 'danger'
                }[kb.status] || 'secondary';

                html += `
                    <tr>
                        <td>${kb.kode_kasbon || '-'}</td>
                        <td>${kb.nama_karyawan || '-'}</td>
                        <td>${formatDate(kb.tanggal_pengajuan)}</td>
                        <td>Rp ${formatRupiah(kb.jumlah)}</td>
                        <td>${kb.tenor_bulan || 0} bulan</td>
                        <td>Rp ${formatRupiah(kb.potongan_per_bulan)}</td>
                        <td>Rp ${formatRupiah(kb.sisa_bon)}</td>
                        <td>
                            <span class="badge bg-${statusClass}">${kb.status ? kb.status.charAt(0).toUpperCase() + kb.status.slice(1) : 'Pengajuan'}</span>
                        </td>
                        <td>
                            ${kb.status === 'pengajuan' ? `
                                <button class="btn btn-sm btn-success" onclick="approveKasBon(${kb.id})">
                                    <i class="bi bi-check"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="rejectKasBon(${kb.id})">
                                    <i class="bi bi-x"></i>
                                </button>
                            ` : ''}
                            ${kb.status === 'disetujui' ? `
                                <button class="btn btn-sm btn-primary" onclick="giveKasBon(${kb.id})">
                                    <i class="bi bi-cash"></i> Berikan
                                </button>
                            ` : ''}
                            ${kb.status === 'diberikan' || kb.status === 'dipotong' ? `
                                <button class="btn btn-sm btn-warning" onclick="potongKasBon(${kb.id})">
                                    <i class="bi bi-arrow-down-circle"></i> Potong
                                </button>
                            ` : ''}
                            <button class="btn btn-sm btn-info" onclick="viewDetail(${kb.id})">
                                <i class="bi bi-eye"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });

            $('#kasbon-table-body').html(html);
        }

        function formatRupiah(angka) {
            return new Intl.NumberFormat('id-ID').format(angka);
        }

        function formatDate(dateStr) {
            if (!dateStr) return '-';
            const date = new Date(dateStr);
            return date.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' });
        }

        function saveKasBon() {
            const form = document.getElementById('addForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());

            fetch('/api/kas_bon?action=create', {
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
                    alert('Kas bon berhasil diajukan');
                    location.reload();
                } else {
                    alert('Gagal mengajukan kas bon: ' + data.error);
                }
            });
        }

        function approveKasBon(id) {
            if (confirm('Setujui kas bon ini?')) {
                fetch(`/api/kas_bon?id=${id}&action=approve`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer kewer-api-token-2024'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Kas bon berhasil disetujui');
                        location.reload();
                    }
                });
            }
        }

        function rejectKasBon(id) {
            const alasan = prompt('Alasan penolakan:');
            if (alasan) {
                fetch(`/api/kas_bon?id=${id}&action=reject`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer kewer-api-token-2024'
                    },
                    body: JSON.stringify({ alasan: alasan })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Kas bon berhasil ditolak');
                        location.reload();
                    }
                });
            }
        }

        function giveKasBon(id) {
            if (confirm('Berikan kas bon ini ke karyawan?')) {
                fetch(`/api/kas_bon?id=${id}&action=give`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer kewer-api-token-2024'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Kas bon berhasil diberikan');
                        location.reload();
                    }
                });
            }
        }

        function potongKasBon(id) {
            const bulan = prompt('Masukkan bulan potong (YYYY-MM):', new Date().toISOString().slice(0, 7));
            const jumlah = prompt('Masukkan jumlah potong:');
            
            if (bulan && jumlah) {
                fetch('/api/kas_bon?action=potong', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer kewer-api-token-2024'
                    },
                    body: JSON.stringify({
                        kas_bon_id: id,
                        bulan_potong: bulan,
                        jumlah_potong: jumlah,
                        potong_oleh: 1 // Get from session
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Potongan berhasil dicatat');
                        location.reload();
                    }
                });
            }
        }

        function viewDetail(id) {
            window.location.href = `detail.php?id=${id}`;
        }

        function showPending() {
            window.location.href = '?status=pending';
        }
        
        // Convert session alerts to SweetAlert2
        <?= getSessionAlertsJS() ?>
    </script>
</body>
</html>
