<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
require_once BASE_PATH . '/includes/family_risk.php';
requireLogin();

// Permission check
if (!hasPermission('view_laporan')) {
    header('Location: ' . baseUrl('dashboard.php'));
    exit();
}

$cabang_id = getCurrentCabang();
$familyRisk = new FamilyRisk($cabang_id);

$highRiskFamilies = $familyRisk->getHighRiskFamilies();
if (!is_array($highRiskFamilies)) {
    $highRiskFamilies = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Family Risk - <?php echo APP_NAME; ?></title>
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
                    <h2><i class="bi bi-exclamation-triangle"></i> Family Risk Management</h2>
                    <a href="../../dashboard.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>

                <div class="alert alert-warning">
                    <i class="bi bi-info-circle"></i> 
                    Manajemen risiko keluarga untuk mencegah pinjaman ke keluarga bermasalah.
                </div>

                <!-- High Risk Families Alert -->
                <?php if (!empty($highRiskFamilies)): ?>
                <div class="alert alert-danger mb-4">
                    <h6><i class="bi bi-exclamation-triangle-fill"></i> <?= count($highRiskFamilies) ?> Keluarga Berisiko Tinggi</h6>
                    <p class="mb-0">Keluarga dengan riwayat pinjaman gagal bayar. Pinjaman ke keluarga ini akan diblok otomatis.</p>
                </div>
                <?php endif; ?>

                <!-- High Risk Families Table -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Keluarga Berisiko Tinggi</h5>
                        <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#blacklistModal">
                            <i class="bi bi-ban"></i> Blacklist Keluarga
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="familyRiskTable">
                                <thead>
                                    <tr>
                                        <th>Nama Kepala Keluarga</th>
                                        <th>Alamat Keluarga</th>
                                        <th>Tingkat Risiko</th>
                                        <th>Total Pinjaman Gagal</th>
                                        <th>Total Nasabah Bermasalah</th>
                                        <th>Tanggal Ditandai</th>
                                        <th>Alasan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($highRiskFamilies as $family): ?>
                                    <tr>
                                        <td><?= $family['nama_kepala_keluarga'] ?></td>
                                        <td><?= $family['alamat_keluarga'] ?></td>
                                        <td>
                                            <?php if ($family['tingkat_risiko'] == 'sangat_tinggi'): ?>
                                                <span class="badge bg-danger">Sangat Tinggi</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">Tinggi</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $family['total_pinjaman_gagal'] ?></td>
                                        <td><?= $family['total_nasabah_bermasalah'] ?></td>
                                        <td><?= formatDate($family['tanggal_ditandai']) ?></td>
                                        <td><?= $family['alasan'] ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-danger" onclick="blacklistFamily('<?= $family['alamat_keluarga'] ?>')">
                                                <i class="bi bi-ban"></i> Blacklist
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Check Risk Section -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Cek Risiko Keluarga</h5>
                    </div>
                    <div class="card-body">
                        <form id="checkRiskForm">
                            <div class="row">
                                <div class="col-md-6">
                                    <label>Nasabah ID</label>
                                    <input type="number" class="form-control" id="nasabah_id" placeholder="Masukkan ID nasabah">
                                </div>
                                <div class="col-md-6">
                                    <label>&nbsp;</label>
                                    <button type="button" class="btn btn-primary w-100" onclick="checkRisk()">
                                        <i class="bi bi-search"></i> Cek Risiko
                                    </button>
                                </div>
                            </div>
                        </form>

                        <div id="riskResult" class="mt-3" style="display: none;">
                            <!-- Risk result will be displayed here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Blacklist Modal -->
    <div class="modal fade" id="blacklistModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Blacklist Keluarga</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="blacklistForm">
                        <div class="mb-3">
                            <label>Alamat Keluarga</label>
                            <input type="text" class="form-control" name="alamat_keluarga" placeholder="Masukkan alamat keluarga" required>
                        </div>
                        <div class="mb-3">
                            <label>Alasan</label>
                            <textarea class="form-control" name="alasan" rows="3" required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" onclick="submitBlacklist()">Blacklist</button>
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
    <script>
        $(document).ready(function() {
            // Only initialize DataTable if there's data
            var hasData = <?php echo !empty($highRiskFamilies) ? 'true' : 'false'; ?>;

            if (hasData) {
                try {
                    var table = $('#familyRiskTable').DataTable({
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
                        order: [[5, 'desc']]
                    });
                } catch (e) {
                    console.error('DataTables initialization error:', e);
                    $('#familyRiskTable').removeClass('table-striped table-hover');
                }
            } else {
                // Hide DataTables controls when no data
                $('#familyRiskTable').removeClass('table-striped table-hover');
                $('#familyRiskTable_wrapper').hide();
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
        
        function checkRisk() {
            const nasabahId = document.getElementById('nasabah_id').value;
            
            if (!nasabahId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: 'Masukkan ID nasabah'
                });
                return;
            }

            fetch(`/api/family_risk?action=check&nasabah_id=${nasabahId}`, {
                headers: {
                    'Authorization': 'Bearer kewer-api-token-2024'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const resultDiv = document.getElementById('riskResult');
                    const risk = data.data;
                    
                    let alertClass = 'alert-success';
                    if (risk.risk === 'tinggi') alertClass = 'alert-warning';
                    if (risk.risk === 'sangat_tinggi') alertClass = 'alert-danger';
                    
                    resultDiv.innerHTML = `
                        <div class="alert ${alertClass}">
                            <h6>Risiko: ${risk.risk.toUpperCase()}</h6>
                            <p>${risk.message}</p>
                            ${risk.reason ? `<p><strong>Alasan:</strong> ${risk.reason}</p>` : ''}
                            ${risk.total_pinjaman_gagal ? `<p><strong>Total Pinjaman Gagal:</strong> ${risk.total_pinjaman_gagal}</p>` : ''}
                            ${risk.total_nasabah_bermasalah ? `<p><strong>Total Nasabah Bermasalah:</strong> ${risk.total_nasabah_bermasalah}</p>` : ''}
                        </div>
                    `;
                    resultDiv.style.display = 'block';
                }
            });
        }

        function blacklistFamily(alamat) {
            Swal.fire({
                title: 'Blacklist Keluarga',
                text: 'Blacklist keluarga di alamat ini? Semua nasabah dengan alamat ini tidak bisa mengajukan pinjaman.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Blacklist',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Alasan Blacklist',
                        input: 'text',
                        inputLabel: 'Alasan blacklist:',
                        inputPlaceholder: 'Masukkan alasan...',
                        showCancelButton: true,
                        confirmButtonText: 'Blacklist',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#d33'
                    }).then((result) => {
                        if (result.isConfirmed && result.value) {
                            fetch('/api/family_risk?action=blacklist', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Authorization': 'Bearer kewer-api-token-2024'
                                },
                                body: JSON.stringify({
                                    alamat_keluarga: alamat,
                                    alasan: result.value
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil',
                                        text: 'Keluarga berhasil di-blacklist',
                                        timer: 2000,
                                        showConfirmButton: false
                                    }).then(() => {
                                        location.reload();
                                    });
                                }
                            });
                        }
                    });
                }
            });
        }

        function submitBlacklist() {
            const form = document.getElementById('blacklistForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());

            fetch('/api/family_risk?action=blacklist', {
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
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Keluarga berhasil di-blacklist',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Gagal blacklist keluarga: ' + data.error
                    });
                }
            });
        }

        // Convert session alerts to SweetAlert2
    </script>
</body>
</html>
