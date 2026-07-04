<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();

// Only users with cabang management permission can access
if (!hasPermission('manage_cabang') && !hasPermission('view_cabang')) {
    header('Location: ' . baseUrl('dashboard.php'));
    exit();
}

$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Cabang - <?php echo APP_NAME; ?></title>
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
                    <h1 class="h2">Manajemen Cabang</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                        <i class="bi bi-plus"></i> Tambah Cabang
                    </button>
                </div>
                
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="cabangTable">
                                <thead>
                                    <tr>
                                        <th>Kode</th>
                                        <th>Nama Cabang</th>
                                        <th>Tipe</th>
                                        <th>Pemilik</th>
                                        <th>Alamat</th>
                                        <th>Telp</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="cabang-table-body">
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
    
    <!-- Add Cabang Modal -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Cabang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addForm">
                        <?= csrfField() ?>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kode Cabang *</label>
                                <input type="text" name="kode_cabang" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Cabang *</label>
                                <input type="text" name="nama_cabang" class="form-control" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Telp</label>
                                <input type="text" name="telp" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Provinsi</label>
                                <?php echo provinceDropdown('province_id', '', 'onchange="loadRegencies(this.value)" class="form-select"'); ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kabupaten/Kota</label>
                                <select name="regency_id" id="regency_id" class="form-select" onchange="loadDistricts(this.value)">
                                    <option value="">Pilih Kabupaten/Kota</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kecamatan</label>
                                <select name="district_id" id="district_id" class="form-select" onchange="loadVillages(this.value)">
                                    <option value="">Pilih Kecamatan</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Desa/Kelurahan</label>
                                <select name="village_id" id="village_id" class="form-select">
                                    <option value="">Pilih Desa/Kelurahan</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Keterangan Tambahan (Opsional)</label>
                            <textarea name="alamat" class="form-control" rows="2" placeholder="Nama jalan, nomor rumah, RT/RW (opsional)"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="aktif">Aktif</option>
                                <option value="nonaktif">Nonaktif</option>
                            </select>
                        </div>
                        <?php if ($currentUser['role'] === 'bos'): ?>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_headquarters" value="1" id="is_headquarters">
                                <label class="form-check-label" for="is_headquarters">
                                    <strong>Jadikan Kantor Pusat</strong>
                                </label>
                                <small class="form-text d-block">Bos hanya dapat memiliki satu kantor pusat</small>
                            </div>
                        </div>
                        <?php endif; ?>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" onclick="saveCabang()">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/kewer/includes/js/auto-focus.js"></script>
    <script src="/kewer/includes/js/enter-navigation.js"></script>
    <script src="/kewer/includes/js/alamat-loader.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/id.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/id.js"></script>
    <script>
        // Initialize modal when shown
        const addModal = document.getElementById('addModal');
        if (addModal) {
            addModal.addEventListener('shown.bs.modal', function() {
                // Initialize any modal-specific functionality here if needed
            });
        }
        
        function saveCabang() {
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
                // Check if there's an error message in the response
                if (html.includes('alert alert-danger')) {
                    // Extract error message
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const errorDiv = doc.querySelector('.alert-danger');
                    Swal.fire('Error', errorDiv ? errorDiv.textContent : 'Gagal menambahkan cabang', 'error');
                } else if (html.includes('alert alert-success')) {
                    Swal.fire('Sukses', 'Cabang berhasil ditambahkan', 'success').then(() => {
                        location.reload();
                    });
                } else {
                    // Check if it was successful by redirecting
                    if (html.includes('Cabang berhasil ditambahkan')) {
                        Swal.fire('Sukses', 'Cabang berhasil ditambahkan', 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', 'Gagal menambahkan cabang', 'error');
                    }
                }
            })
            .catch(error => {
                Swal.fire('Error', 'Terjadi kesalahan: ' + error.message, 'error');
            });
        }
        
        // Load cabang data via JSON API
        $(document).ready(function() {
            loadCabangData();
        });

        function loadCabangData() {
            window.KewerAPI.getCabang().done(response => {
                if (response.success) {
                    renderCabangTable(response.data);
                } else {
                    $('#cabang-table-body').html('<tr><td colspan="8" class="text-center text-danger">Gagal memuat data</td></tr>');
                }
            }).fail(error => {
                $('#cabang-table-body').html('<tr><td colspan="8" class="text-center text-danger">Gagal memuat data</td></tr>');
            });
        }

        function renderCabangTable(data) {
            if (!data || data.length === 0) {
                $('#cabang-table-body').html('<tr><td colspan="8" class="text-center text-muted">Tidak ada data cabang</td></tr>');
                return;
            }

            let html = '';
            data.forEach(c => {
                html += `
                    <tr>
                        <td>${c.kode_cabang || '-'}</td>
                        <td>
                            ${c.nama_cabang || ''}
                            ${c.is_headquarters ? '<span class="badge bg-primary ms-1">Pusat</span>' : ''}
                        </td>
                        <td>${c.is_headquarters ? 'Kantor Pusat' : 'Cabang'}</td>
                        <td>${c.owner_name || '-'}</td>
                        <td>${c.alamat || '-'}</td>
                        <td>${c.telp || '-'}</td>
                        <td>
                            <span class="badge bg-${c.status === 'aktif' ? 'success' : 'danger'}">${c.status === 'aktif' ? 'Aktif' : 'Nonaktif'}</span>
                        </td>
                        <td>
                            <a href="edit.php?id=${c.id}" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </td>
                    </tr>
                `;
            });

            $('#cabang-table-body').html(html);
        }
        
        // Load regencies when province changes
        function loadRegencies(provinceId) {
            const regencySelect = document.getElementById('regency_id');
            const districtSelect = document.getElementById('district_id');
            const villageSelect = document.getElementById('village_id');
            
            // Reset dependent dropdowns
            regencySelect.innerHTML = '<option value="">Pilih Kabupaten/Kota</option>';
            districtSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';
            villageSelect.innerHTML = '<option value="">Pilih Desa/Kelurahan</option>';
            
            if (!provinceId) return;
            
            fetch('/kewer/api/alamat.php?action=regencies&province_id=' + provinceId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        data.data.forEach(regency => {
                            const option = document.createElement('option');
                            option.value = regency.id;
                            option.textContent = regency.name;
                            regencySelect.appendChild(option);
                        });
                    }
                })
                .catch(error => console.error('Error loading regencies:', error));
        }
        
        // Load districts when regency changes
        function loadDistricts(regencyId) {
            const districtSelect = document.getElementById('district_id');
            const villageSelect = document.getElementById('village_id');
            
            // Reset dependent dropdowns
            districtSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';
            villageSelect.innerHTML = '<option value="">Pilih Desa/Kelurahan</option>';
            
            if (!regencyId) return;
            
            fetch('/kewer/api/alamat.php?action=districts&regency_id=' + regencyId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        data.data.forEach(district => {
                            const option = document.createElement('option');
                            option.value = district.id;
                            option.textContent = district.name;
                            districtSelect.appendChild(option);
                        });
                    }
                })
                .catch(error => console.error('Error loading districts:', error));
        }
        
        // Load villages when district changes
        function loadVillages(districtId) {
            const villageSelect = document.getElementById('village_id');
            
            // Reset dependent dropdown
            villageSelect.innerHTML = '<option value="">Pilih Desa/Kelurahan</option>';
            
            if (!districtId) return;
            
            fetch('/kewer/api/alamat.php?action=villages&district_id=' + districtId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        data.data.forEach(village => {
                            const option = document.createElement('option');
                            option.value = village.id;
                            option.textContent = village.name;
                            villageSelect.appendChild(option);
                        });
                    }
                })
                .catch(error => console.error('Error loading villages:', error));
        }
        
        $(document).ready(function() {
            // Only initialize DataTable if there's data
            var hasData = <?php echo !empty($cabang) ? 'true' : 'false'; ?>;

            if (hasData) {
                try {
                    var table = $('#cabangTable').DataTable({
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
                    $('#cabangTable').removeClass('table-striped table-hover');
                }
            } else {
                // Hide DataTables controls when no data
                $('#cabangTable').removeClass('table-striped table-hover');
                $('#cabangTable_wrapper').hide();
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
        
        // Replace confirm with SweetAlert2
        document.querySelectorAll('a[onclick^="return confirm"]').forEach(link => {
            link.removeAttribute('onclick');
            link.addEventListener('click', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Hapus Cabang',
                    text: 'Hapus cabang ini?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = this.href;
                    }
                });
            });
        });
        
        function showPending() {
            window.location.href = '?status=pending';
        }

        // Convert session alerts to SweetAlert2
        <?= getSessionAlertsJS() ?>
    </script>
</body>
</html>
