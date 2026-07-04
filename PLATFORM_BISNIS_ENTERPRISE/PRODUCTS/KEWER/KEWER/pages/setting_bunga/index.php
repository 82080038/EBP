<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();

// Only users with manage_bunga permission can access settings
if (!hasPermission('manage_bunga') && !hasPermission('view_settings')) {
    header('Location: ' . baseUrl('dashboard.php'));
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setting Bunga - <?php echo APP_NAME; ?></title>
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
                    <h2><i class="bi bi-percent"></i> Setting Bunga Dinamis</h2>
                    <a href="../../dashboard.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>

                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> 
                    Pengaturan bunga dapat diatur secara dinamis per cabang, jenis pinjaman, dan tenor.
                    Bunga akan dihitung berdasarkan bunga dasar + adjustment risiko + adjustment jaminan.
                </div>

                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Daftar Setting Bunga</h5>
                        <?php if (hasRole('bos') || hasRole('manager_pusat') || hasRole('admin_pusat')): ?>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
                            <i class="bi bi-plus"></i> Tambah Setting
                        </button>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Cabang</th>
                                        <th>Jenis Pinjaman</th>
                                        <th>Tenor</th>
                                        <th>Bunga Default</th>
                                        <th>Bunga Min</th>
                                        <th>Bunga Max</th>
                                        <th>Faktor Risiko</th>
                                        <th>Adjustment Jaminan</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="setting-bunga-table-body">
                                    <tr>
                                        <td colspan="10" class="text-center">
                                            <div class="spinner-border spinner-border-sm" role="status"></div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Calculator Section -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Kalkulator Bunga</h5>
                    </div>
                    <div class="card-body">
                        <form id="calculatorForm">
                            <div class="row">
                                <div class="col-md-3">
                                    <label>Jenis Pinjaman</label>
                                    <select class="form-select" id="calc_jenis_pinjaman">
                                        <option value="harian">Harian</option>
                                        <option value="mingguan">Mingguan</option>
                                        <option value="bulanan">Bulanan</option>
                                        <option value="multi_guna">Multi Guna</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label>Tenor (bulan)</label>
                                    <input type="number" class="form-control" id="calc_tenor" value="12">
                                </div>
                                <div class="col-md-2">
                                    <label>Plafon</label>
                                    <input type="number" class="form-control" id="calc_plafon" value="5000000">
                                </div>
                                <div class="col-md-2">
                                    <label>Jaminan</label>
                                    <select class="form-select" id="calc_jaminan_tipe">
                                        <option value="tanpa">Tanpa Jaminan</option>
                                        <option value="bpkb">BPKB</option>
                                        <option value="shm">SHM</option>
                                        <option value="ajb">AJB</option>
                                        <option value="tabungan">Tabungan</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label>&nbsp;</label>
                                    <button type="button" class="btn btn-primary w-100" onclick="calculateBunga()">
                                        <i class="bi bi-calculator"></i> Hitung
                                    </button>
                                </div>
                            </div>
                        </form>

                        <div id="calcResult" class="mt-3" style="display: none;">
                            <div class="alert alert-success">
                                <h6>Hasil Perhitungan:</h6>
                                <table class="table table-sm mb-0">
                                    <tr>
                                        <td>Suku Bunga:</td>
                                        <td><strong id="result_suku_bunga"></strong></td>
                                    </tr>
                                    <tr>
                                        <td>Bunga Dasar:</td>
                                        <td id="result_bunga_dasar"></td>
                                    </tr>
                                    <tr>
                                        <td>Risiko Adjustment:</td>
                                        <td id="result_risiko_adj"></td>
                                    </tr>
                                    <tr>
                                        <td>Jaminan Adjustment:</td>
                                        <td id="result_jaminan_adj"></td>
                                    </tr>
                                    <tr>
                                        <td>Total Bunga:</td>
                                        <td><strong id="result_total_bunga"></strong></td>
                                    </tr>
                                    <tr>
                                        <td>Total Pembayaran:</td>
                                        <td><strong id="result_total_pembayaran"></strong></td>
                                    </tr>
                                    <tr>
                                        <td>Angsuran per Bulan:</td>
                                        <td><strong id="result_angsuran"></strong></td>
                                    </tr>
                                </table>
                            </div>
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
                    <h5 class="modal-title">Tambah Setting Bunga</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addForm">
                        <input type="hidden" id="setting_id" name="id">
                        <div class="mb-3">
                            <label>Jenis Pinjaman</label>
                            <select class="form-select" name="jenis_pinjaman" required>
                                <option value="harian">Harian</option>
                                <option value="mingguan">Mingguan</option>
                                <option value="bulanan">Bulanan</option>
                                <option value="multi_guna">Multi Guna</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Tenor Min</label>
                                <input type="number" class="form-control" name="tenor_min" value="1" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Tenor Max</label>
                                <input type="number" class="form-control" name="tenor_max" value="24" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label>Bunga Default (%)</label>
                                <input type="number" step="0.01" class="form-control" name="bunga_default" value="2.5" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label>Bunga Min (%)</label>
                                <input type="number" step="0.01" class="form-control" name="bunga_min" value="1.5" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label>Bunga Max (%)</label>
                                <input type="number" step="0.01" class="form-control" name="bunga_max" value="4.0" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Faktor Risiko (%)</label>
                                <input type="number" step="0.01" class="form-control" name="faktor_risiko" value="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Jaminan Adjustment (%)</label>
                                <input type="number" step="0.01" class="form-control" name="jaminan_adjustment" value="0">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" onclick="saveSetting()">Simpan</button>
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
        function formatRupiah(angka) {
            return new Intl.NumberFormat('id-ID').format(angka);
        }

        $(document).ready(function() {
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

            // Reset form when modal is closed
            document.getElementById('addModal').addEventListener('hidden.bs.modal', function() {
                document.getElementById('addForm').reset();
                document.getElementById('setting_id').value = '';
                document.querySelector('#addModal .btn-primary').textContent = 'Simpan';
            });

            // Load setting bunga data via JSON API
            loadSettingBungaData();
        });

        function loadSettingBungaData() {
            window.KewerAPI.getSettingBunga().done(response => {
                if (response.success) {
                    renderSettingBungaTable(response.data);
                } else {
                    $('#setting-bunga-table-body').html('<tr><td colspan="10" class="text-center text-danger">Gagal memuat data</td></tr>');
                }
            }).fail(error => {
                $('#setting-bunga-table-body').html('<tr><td colspan="10" class="text-center text-danger">Gagal memuat data</td></tr>');
            });
        }

        function renderSettingBungaTable(data) {
            if (!data || data.length === 0) {
                $('#setting-bunga-table-body').html('<tr><td colspan="10" class="text-center text-muted">Tidak ada data setting bunga</td></tr>');
                return;
            }

            let html = '';
            data.forEach(setting => {
                const statusClass = setting.status === 'aktif' ? 'success' : 'secondary';

                html += `
                    <tr>
                        <td>${setting.nama_cabang || 'Global'}</td>
                        <td>${setting.jenis_pinjaman ? setting.jenis_pinjaman.toUpperCase() : '-'}</td>
                        <td>${setting.tenor_min || 0} - ${setting.tenor_max || 0} bulan</td>
                        <td>${setting.bunga_default || 0}%</td>
                        <td>${setting.bunga_min || 0}%</td>
                        <td>${setting.bunga_max || 0}%</td>
                        <td>${setting.faktor_risiko || 0}%</td>
                        <td>${setting.jaminan_adjustment || 0}%</td>
                        <td>
                            <span class="badge bg-${statusClass}">${setting.status ? setting.status.charAt(0).toUpperCase() + setting.status.slice(1) : 'Aktif'}</span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-warning" onclick="editSetting(${setting.id})">
                                <i class="bi bi-pencil"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });

            $('#setting-bunga-table-body').html(html);
        }
        
        function calculateBunga() {
            const jenisPinjaman = document.getElementById('calc_jenis_pinjaman').value;
            const tenor = parseInt(document.getElementById('calc_tenor').value);
            const plafon = parseInt(document.getElementById('calc_plafon').value);
            const jaminanTipe = document.getElementById('calc_jaminan_tipe').value;

            fetch(`/api/setting_bunga/calculate`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer kewer-api-token-2024'
                },
                body: JSON.stringify({
                    jenis_pinjaman: jenisPinjaman,
                    tenor: tenor,
                    plafon: plafon,
                    jaminan_tipe: jaminanTipe
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('result_suku_bunga').textContent = data.suku_bunga + '%';
                    document.getElementById('result_bunga_dasar').textContent = data.bunga_dasar + '%';
                    document.getElementById('result_risiko_adj').textContent = data.risiko_adjustment + '%';
                    document.getElementById('result_jaminan_adj').textContent = data.jaminan_adjustment + '%';
                    document.getElementById('result_total_bunga').textContent = formatRupiah(data.total_bunga);
                    document.getElementById('result_total_pembayaran').textContent = formatRupiah(data.total_pembayaran);
                    document.getElementById('result_angsuran').textContent = formatRupiah(data.angsuran_total);
                    document.getElementById('calcResult').style.display = 'block';
                }
            });
        }

        function editSetting(id) {
            fetch(`/api/setting_bunga?id=${id}`, {
                method: 'GET',
                headers: {
                    'Authorization': 'Bearer kewer-api-token-2024'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data) {
                    const setting = data.data;
                    document.getElementById('setting_id').value = setting.id;
                    document.querySelector('select[name="jenis_pinjaman"]').value = setting.jenis_pinjaman;
                    document.querySelector('input[name="tenor_min"]').value = setting.tenor_min;
                    document.querySelector('input[name="tenor_max"]').value = setting.tenor_max;
                    document.querySelector('input[name="bunga_default"]').value = setting.bunga_default;
                    document.querySelector('input[name="bunga_min"]').value = setting.bunga_min;
                    document.querySelector('input[name="bunga_max"]').value = setting.bunga_max;
                    
                    // Show modal
                    const modal = new bootstrap.Modal(document.getElementById('addModal'));
                    modal.show();
                    
                    // Change button text
                    document.querySelector('#addModal .btn-primary').textContent = 'Update Setting';
                }
            });
        }

        function saveSetting() {
            const form = document.getElementById('addForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            const settingId = document.getElementById('setting_id').value;

            const url = settingId ? `/api/setting_bunga?id=${settingId}` : '/api/setting_bunga';
            const method = settingId ? 'PUT' : 'POST';

            fetch(url, {
                method: method,
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
                        text: settingId ? 'Setting berhasil diupdate' : 'Setting berhasil ditambahkan',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: settingId ? 'Gagal mengupdate setting: ' + data.error : 'Gagal menambahkan setting: ' + data.error
                    });
                }
            });
        }

    </script>
</body>
</html>
