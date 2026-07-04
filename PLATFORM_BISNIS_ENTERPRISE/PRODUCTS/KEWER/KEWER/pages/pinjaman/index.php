<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();

$user = getCurrentUser();
$role = $user['role'];
$user_cabang_id = $user['cabang_id'] ?? null;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pinjaman - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
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
                    <h1 class="h2">Data Pinjaman</h1>
                    <div class="btn-group">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                            <i class="bi bi-plus-circle"></i> Ajukan Pinjaman
                        </button>
                        <button class="btn btn-success" onclick="exportData('pinjaman')">
                            <i class="bi bi-download"></i> Export CSV
                        </button>
                    </div>
                </div>
                
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-2">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h6 class="card-title">Total</h6>
                                <h4><?php echo $stats['total']; ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h6 class="card-title">Pengajuan</h6>
                                <h4><?php echo $stats['pengajuan']; ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h6 class="card-title">Disetujui</h6>
                                <h4><?php echo $stats['disetujui']; ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h6 class="card-title">Aktif</h6>
                                <h4><?php echo $stats['aktif']; ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-secondary text-white">
                            <div class="card-body">
                                <h6 class="card-title">Lunas</h6>
                                <h4><?php echo $stats['lunas']; ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-dark text-white">
                            <div class="card-body">
                                <h6 class="card-title">Total Plafon</h6>
                                <h5><?php echo formatRupiah($stats['total_plafon']); ?></h5>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Filter and Search -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <input type="text" name="search" class="form-control" placeholder="Cari kode, nama, KTP, telepon..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-3">
                                <select name="status" class="form-select">
                                    <option value="">Semua Status</option>
                                    <option value="pengajuan" <?php echo $status === 'pengajuan' ? 'selected' : ''; ?>>Pengajuan</option>
                                    <option value="disetujui" <?php echo $status === 'disetujui' ? 'selected' : ''; ?>>Disetujui</option>
                                    <option value="aktif" <?php echo $status === 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                                    <option value="lunas" <?php echo $status === 'lunas' ? 'selected' : ''; ?>>Lunas</option>
                                    <option value="ditolak" <?php echo $status === 'ditolak' ? 'selected' : ''; ?>>Ditolak</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="bi bi-search"></i> Cari
                                </button>
                            </div>
                            <div class="col-md-3">
                                <a href="index.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-clockwise"></i> Reset
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Data Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="pinjamanTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Kode</th>
                                        <th>Nasabah</th>
                                        <th>Plafon</th>
                                        <th>Frekuensi</th>
                                        <th>Tenor</th>
                                        <th>Bunga/Bln</th>
                                        <th>Angsuran</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="pinjaman-table-body">
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
    
    <!-- Add Pinjaman Modal -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ajukan Pinjaman Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addForm">
                        <?= csrfField() ?>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nasabah *</label>
                                <select name="nasabah_id" class="form-select" required>
                                    <option value="">Pilih Nasabah</option>
                                    <?php foreach ($nasabah_list as $n): ?>
                                        <option value="<?php echo $n['id']; ?>">
                                            <?php echo $n['kode_nasabah']; ?> - <?php echo $n['nama']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Plafon Pinjaman *</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" name="plafon" class="form-control" id="plafon" required>
                                </div>
                                <small class="form-text">Maksimal: <?php echo formatRupiah(10000000); ?></small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Frekuensi Angsuran *</label>
                                <select name="frekuensi" class="form-select" id="frekuensi" required>
                                    <?php
                                    $active_frequencies = getActiveFrequencies();
                                    if ($active_frequencies && is_array($active_frequencies)):
                                        foreach ($active_frequencies as $freq):
                                    ?>
                                    <option value="<?= $freq['kode'] ?>" data-id="<?= $freq['id'] ?>" data-max="<?= $freq['tenor_max'] ?>" data-period="<?= $freq['hari_per_periode'] ?>">
                                        <?= $freq['nama'] ?> (Max: <?= $freq['tenor_max'] ?>)
                                    </option>
                                    <?php
                                        endforeach;
                                    else:
                                    ?>
                                    <option value="harian">Harian</option>
                                    <option value="mingguan">Mingguan</option>
                                    <option value="bulanan" selected>Bulanan</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tenor *</label>
                                <div class="input-group">
                                    <input type="number" name="tenor" class="form-control" id="tenor" min="1" max="24" required>
                                    <span class="input-group-text" id="tenorLabel">bulan</span>
                                </div>
                                <small class="form-text" id="tenorHelp">Harian: 1-365 | Mingguan: 1-52 | Bulanan: 1-24</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Bunga per Bulan *</label>
                                <div class="input-group">
                                    <input type="number" name="bunga_per_bulan" class="form-control" id="bunga" step="0.1" min="0" max="10" value="2.5" required>
                                    <span class="input-group-text">%</span>
                                </div>
                                <small class="form-text text-muted">Bunga per bulan (akan dikonversi otomatis sesuai frekuensi)</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Akad *</label>
                                <input type="date" name="tanggal_akad" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tujuan Pinjaman</label>
                                <textarea name="tujuan_pinjaman" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tipe Jaminan</label>
                                <select name="jaminan_tipe" class="form-select">
                                    <?php
                                    $jaminan_tipe_list = getActiveReferenceData('ref_jaminan_tipe');
                                    if (is_array($jaminan_tipe_list)):
                                        foreach ($jaminan_tipe_list as $jt):
                                            $kode = str_replace('JAM', '', strtolower($jt['tipe_kode']));
                                    ?>
                                        <option value="<?php echo $kode; ?>"><?php echo htmlspecialchars($jt['tipe_nama']); ?></option>
                                    <?php
                                        endforeach;
                                    endif;
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nilai Jaminan</label>
                                <input type="text" name="jaminan_nilai" class="form-control" placeholder="Rp">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Keterangan Jaminan</label>
                                <textarea name="jaminan" class="form-control" rows="2" placeholder="Deskripsi detail jaminan..."></textarea>
                            </div>
                        </div>
                        
                        <!-- Loan Calculation Preview -->
                        <div class="card bg-light">
                            <div class="card-header">
                                <h6><i class="bi bi-calculator"></i> Simulasi Pinjaman</h6>
                            </div>
                            <div class="card-body" id="loanPreview">
                                <p class="text-muted">Isi data pinjaman untuk melihat simulasi</p>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" onclick="savePinjaman()">Ajukan Pinjaman</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/kewer/includes/js/auto-focus.js"></script>
    <script src="/kewer/includes/js/enter-navigation.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/id.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/id.js"></script>
    <script>
        function formatRupiah(angka) {
            return new Intl.NumberFormat('id-ID').format(angka);
        }

        // Initialize modal when shown
        const addModal = document.getElementById('addModal');
        if (addModal) {
            addModal.addEventListener('shown.bs.modal', function() {
                // Initialize loan calculation
                const plafonInput = document.getElementById('plafon');
                const frekuensiInput = document.getElementById('frekuensi');
                const tenorInput = document.getElementById('tenor');
                const bungaInput = document.getElementById('bunga');
                
                if (plafonInput) {
                    plafonInput.addEventListener('input', function(e) {
                        e.target.value = formatRupiah(e.target.value.replace(/[^\d]/g, ''));
                        calculatePreview();
                    });
                }
                
                if (frekuensiInput) {
                    frekuensiInput.addEventListener('change', updateFrequencyUI);
                }
                
                if (tenorInput) {
                    tenorInput.addEventListener('input', calculatePreview);
                }
                
                if (bungaInput) {
                    bungaInput.addEventListener('input', calculatePreview);
                }
                
                // Initial setup
                updateFrequencyUI();
                calculatePreview();
            });
        }
        
        
        const freqConfig = {
            harian:   { label: 'hari', periodLabel: 'Hari', max: 365, bungaDivisor: 30 },
            mingguan: { label: 'minggu', periodLabel: 'Minggu', max: 52, bungaDivisor: 4 },
            bulanan:  { label: 'bulan', periodLabel: 'Bulan', max: 24, bungaDivisor: 1 }
        };
        
        function updateFrequencyUI() {
            const frekSelect = document.getElementById('frekuensi');
            const selectedOption = frekSelect.options[frekSelect.selectedIndex];
            const maxTenor = parseInt(selectedOption.getAttribute('data-max')) || 24;
            const period = parseInt(selectedOption.getAttribute('data-period')) || 30;
            
            let label = 'bulan';
            if (period === 1) label = 'hari';
            else if (period === 7) label = 'minggu';
            
            document.getElementById('tenorLabel').textContent = label;
            document.getElementById('tenor').max = maxTenor;
            document.getElementById('tenorHelp').textContent = `Maksimal: ${maxTenor} ${label}`;
            
            const tenorEl = document.getElementById('tenor');
            if (parseInt(tenorEl.value) > maxTenor) tenorEl.value = maxTenor;
            
            calculatePreview();
        }
        
        function calculatePreview() {
            const plafon = parseFloat(document.getElementById('plafon').value.replace(/[^\d]/g, '')) || 0;
            const tenor = parseInt(document.getElementById('tenor').value) || 0;
            const bungaBulanan = parseFloat(document.getElementById('bunga').value) || 0;
            const frekSelect = document.getElementById('frekuensi');
            const selectedOption = frekSelect.options[frekSelect.selectedIndex];
            const period = parseInt(selectedOption.getAttribute('data-period')) || 30;
            
            let bungaDivisor = 1;
            let periodLabel = 'Bulan';
            if (period === 1) {
                bungaDivisor = 30;
                periodLabel = 'Hari';
            } else if (period === 7) {
                bungaDivisor = 4;
                periodLabel = 'Minggu';
            }
            
            const bungaPerPeriod = bungaBulanan / bungaDivisor;
            
            if (plafon > 0 && tenor > 0 && bungaBulanan >= 0) {
                const totalBunga = plafon * (bungaPerPeriod / 100) * tenor;
                const totalPembayaran = plafon + totalBunga;
                const angsuranPokok = plafon / tenor;
                const angsuranBunga = totalBunga / tenor;
                const angsuranTotal = angsuranPokok + angsuranBunga;
                
                document.getElementById('loanPreview').innerHTML = `
                    <table class="table table-sm">
                        <tr><td>Frekuensi:</td><td class="text-end"><span class="badge bg-primary">${periodLabel}</span></td></tr>
                        <tr><td>Total Pinjaman:</td><td class="text-end">Rp ${formatRupiah(totalPembayaran)}</td></tr>
                        <tr><td>Total Bunga:</td><td class="text-end">Rp ${formatRupiah(totalBunga)}</td></tr>
                        <tr><td>Angsuran/${periodLabel}:</td><td class="text-end fw-bold">Rp ${formatRupiah(angsuranTotal)}</td></tr>
                        <tr><td>Pokok/${periodLabel}:</td><td class="text-end">Rp ${formatRupiah(angsuranPokok)}</td></tr>
                        <tr><td>Bunga/${periodLabel}:</td><td class="text-end">Rp ${formatRupiah(angsuranBunga)}</td></tr>
                        <tr><td>Bunga per ${periodLabel.toLowerCase()}:</td><td class="text-end">${bungaPerPeriod.toFixed(4)}%</td></tr>
                    </table>
                `;
            } else {
                document.getElementById('loanPreview').innerHTML = '<p class="text-muted">Isi data pinjaman untuk melihat simulasi</p>';
            }
        }
        
        function savePinjaman() {
            const form = document.getElementById('addForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            
            // Add CSRF token
            const csrfInput = document.querySelector('input[name="csrf_token"]');
            if (csrfInput) {
                data.csrf_token = csrfInput.value;
            }
            
            fetch('tambah.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(data)
            })
            .then(response => response.text())
            .then(html => {
                if (html.includes('alert alert-danger')) {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const errorDiv = doc.querySelector('.alert-danger');
                    Swal.fire('Error', errorDiv ? errorDiv.textContent : 'Gagal mengajukan pinjaman', 'error');
                } else if (html.includes('alert alert-success')) {
                    Swal.fire('Sukses', 'Pengajuan pinjaman berhasil dibuat', 'success').then(() => {
                        location.reload();
                    });
                } else {
                    if (html.includes('Pengajuan pinjaman berhasil dibuat')) {
                        Swal.fire('Sukses', 'Pengajuan pinjaman berhasil dibuat', 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', 'Gagal mengajukan pinjaman', 'error');
                    }
                }
            })
            .catch(error => {
                Swal.fire('Error', 'Terjadi kesalahan: ' + error.message, 'error');
            });
        }
        
        $(document).ready(function() {
            // Only initialize DataTable if there's data
            var hasData = <?php echo !empty($pinjaman) ? 'true' : 'false'; ?>;

            if (hasData) {
                try {
                    var table = $('#pinjamanTable').DataTable({
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
                    $('#pinjamanTable').removeClass('table-striped table-hover');
                }
            } else {
                // Hide DataTables controls when no data
                $('#pinjamanTable').removeClass('table-striped table-hover');
                $('#pinjamanTable_wrapper').hide();
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

            // Auto-advance focus when select element is changed
            document.addEventListener("DOMContentLoaded", function() {
                const selects = document.querySelectorAll("select");
                selects.forEach(function(select) {
                    select.addEventListener("change", function() {
                        // Find the next form element
                        const form = this.form;
                        if (form) {
                            const elements = Array.from(form.elements);
                            const currentIndex = elements.indexOf(this);
                            
                            // Find the next visible, non-disabled, non-readonly element
                            for (let i = currentIndex + 1; i < elements.length; i++) {
                                const nextElement = elements[i];
                                if (nextElement &&
                                    nextElement.tagName !== "BUTTON" &&
                                    nextElement.type !== "hidden" &&
                                    nextElement.type !== "submit" &&
                                    !nextElement.disabled &&
                                    !nextElement.readOnly &&
                                    nextElement.offsetParent !== null) {
                                    nextElement.focus();
                                    break;
                                }
                            }
                        }
                    });
                });
            });
        });
        
        // Load pinjaman data via JSON API
        $(document).ready(function() {
            loadPinjamanData();
        });

        function loadPinjamanData() {
            const search = '<?php echo $_GET['search'] ?? ''; ?>';
            const status = '<?php echo $_GET['status'] ?? ''; ?>';
            
            window.KewerAPI.getPinjaman({ search, status }).done(response => {
                if (response.success) {
                    renderPinjamanTable(response.data);
                } else {
                    $('#pinjaman-table-body').html('<tr><td colspan="9" class="text-center text-danger">Gagal memuat data</td></tr>');
                }
            }).fail(error => {
                $('#pinjaman-table-body').html('<tr><td colspan="9" class="text-center text-danger">Gagal memuat data</td></tr>');
            });
        }

        function renderPinjamanTable(data) {
            if (!data || data.length === 0) {
                $('#pinjaman-table-body').html('<tr><td colspan="9" class="text-center text-muted">Tidak ada data pinjaman</td></tr>');
                return;
            }

            let html = '';
            data.forEach(p => {
                const freqClass = {1: 'warning', 2: 'info', 3: 'primary'}[p.frekuensi_id] || 'primary';
                const freqLabel = ['harian', 'mingguan', 'bulanan'][p.frekuensi_id - 1] || 'bulanan';
                const freqPeriod = ['hari', 'minggu', 'bulan'][p.frekuensi_id - 1] || 'bulan';
                const statusClass = {
                    'pengajuan': 'info',
                    'disetujui': 'warning',
                    'aktif': 'success',
                    'lunas': 'secondary',
                    'ditolak': 'danger'
                }[p.status] || 'secondary';

                html += `
                    <tr>
                        <td>${p.kode_pinjaman || '-'}</td>
                        <td>
                            <div>
                                <strong>${p.nama || ''}</strong>
                                <br>
                                <small class="text-muted">${p.kode_nasabah || '-'}</small>
                            </div>
                        </td>
                        <td>Rp ${formatRupiah(p.plafon)}</td>
                        <td>
                            <span class="badge bg-${freqClass}">${freqLabel.charAt(0).toUpperCase() + freqLabel.slice(1)}</span>
                        </td>
                        <td>${p.tenor || 0} ${freqPeriod}</td>
                        <td>${p.bunga_per_bulan || 0}%</td>
                        <td>Rp ${formatRupiah(p.angsuran_total)}</td>
                        <td>
                            <span class="badge bg-${statusClass}">${p.status ? p.status.charAt(0).toUpperCase() + p.status.slice(1) : 'Aktif'}</span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="detail.php?id=${p.id}" class="btn btn-outline-primary" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                ${p.status === 'pengajuan' ? `<a href="edit.php?id=${p.id}" class="btn btn-outline-warning" title="Edit"><i class="bi bi-pencil"></i></a>` : ''}
                                ${p.status === 'pengajuan' ? `<button onclick="approveLoan(${p.id})" class="btn btn-outline-success" title="Setujui"><i class="bi bi-check-circle"></i></button>` : ''}
                                ${p.status === 'pengajuan' ? `<button onclick="rejectLoan(${p.id})" class="btn btn-outline-danger" title="Tolak"><i class="bi bi-x-circle"></i></button>` : ''}
                            </div>
                        </td>
                    </tr>
                `;
            });

            $('#pinjaman-table-body').html(html);
        }

        function approveLoan(id) {
            window.KewerAPI.approvePinjaman(id).done(response => {
                if (response.success) {
                    Swal.fire('Berhasil', 'Pinjaman disetujui', 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error', response.error || 'Gagal menyetujui', 'error');
                }
            }).fail(error => {
                Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
            });
        }
        
        function rejectLoan(id) {
            window.KewerAPI.rejectPinjaman(id).done(response => {
                if (response.success) {
                    Swal.fire('Berhasil', 'Pinjaman ditolak', 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error', response.error || 'Gagal menolak', 'error');
                }
            }).fail(error => {
                Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
            });
        }

    </script>
</body>
</html>
