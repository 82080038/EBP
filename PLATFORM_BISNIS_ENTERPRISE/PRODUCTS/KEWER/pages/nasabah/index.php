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
    <title>Data Nasabah - <?php echo APP_NAME; ?></title>
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
                    <h1 class="h2">Data Nasabah</h1>
                    <div class="btn-group">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                            <i class="bi bi-plus-circle"></i> Tambah Nasabah
                        </button>
                        <button class="btn btn-success" onclick="exportData('nasabah')">
                            <i class="bi bi-download"></i> Export CSV
                        </button>
                        <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#importModal">
                            <i class="bi bi-upload"></i> Import CSV
                        </button>
                    </div>
                </div>
                
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Nasabah</h5>
                                <h3><?php echo $stats['total']; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Aktif</h5>
                                <h3><?php echo $stats['aktif']; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h5 class="card-title">Nonaktif</h5>
                                <h3><?php echo $stats['nonaktif']; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body">
                                <h5 class="card-title">Blacklist</h5>
                                <h3><?php echo $stats['blacklist']; ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Filter and Search -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <input type="text" name="search" class="form-control" placeholder="Cari nama, KTP, telepon..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-3">
                                <select name="status" class="form-select">
                                    <option value="">Semua Status</option>
                                    <option value="aktif" <?php echo $status === 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                                    <option value="nonaktif" <?php echo $status === 'nonaktif' ? 'selected' : ''; ?>>Nonaktif</option>
                                    <option value="blacklist" <?php echo $status === 'blacklist' ? 'selected' : ''; ?>>Blacklist</option>
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
                            <table class="table table-striped table-hover" id="nasabahTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Kode</th>
                                        <th>Nama</th>
                                        <th>KTP</th>
                                        <th>Telepon</th>
                                        <th>Jenis Usaha</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="nasabah-table-body">
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
            </main>
        </div>
    </div>
    
    <!-- Import CSV Modal -->
    <div class="modal fade" id="importModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Import Data Nasabah (CSV)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>Format CSV:</strong> kode_nasabah, nama, telepon, alamat (opsional)
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pilih File CSV</label>
                        <input type="file" id="importFile" class="form-control" accept=".csv" required>
                    </div>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        Pastikan file CSV menggunakan pemisah koma (,) dan memiliki header yang sesuai.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" onclick="handleImport()">Import</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add Nasabah Modal -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Nasabah</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addForm" enctype="multipart/form-data">
                        <?= csrfField() ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            Masukkan No. Telepon atau No. KTP untuk mengecek apakah data sudah ada di database.
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">No. Telepon *</label>
                                <input type="tel" name="telp" id="telpInput" class="form-control" required onblur="checkTelp()">
                                <small class="form-text">08xxxxxxxxxx</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">No. KTP</label>
                                <input type="text" name="ktp" id="ktpInput" class="form-control" maxlength="16" onblur="checkKTP()">
                                <small class="form-text">Opsional - 16 digit angka</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Lengkap *</label>
                                <input type="text" name="nama" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jenis Usaha</label>
                                <select name="jenis_usaha" class="form-select">
                                    <option value="">Pilih Jenis Usaha</option>
                                    <option value="Pedagang Sayur">Pedagang Sayur</option>
                                    <option value="Pedagang Buah">Pedagang Buah</option>
                                    <option value="Warung Makan">Warung Makan</option>
                                    <option value="Warung Kelontong">Warung Kelontong</option>
                                    <option value="Toko Baju">Toko Baju</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
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
                            <label class="form-label">Lokasi Pasar/Warung</label>
                            <input type="text" name="lokasi_pasar" class="form-control">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Foto KTP</label>
                                <input type="file" name="foto_ktp" class="form-control" accept="image/*">
                                <small class="form-text">Upload foto KTP</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Foto Selfie + KTP</label>
                                <input type="file" name="foto_selfie" class="form-control" accept="image/*">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" onclick="saveNasabah()">Simpan</button>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Load nasabah data via JSON API
        $(document).ready(function() {
            loadNasabahData();
        });

        function loadNasabahData() {
            const search = '<?php echo $_GET['search'] ?? ''; ?>';
            const status = '<?php echo $_GET['status'] ?? ''; ?>';
            
            window.KewerAPI.getNasabah({ search, status }).done(response => {
                if (response.success) {
                    renderNasabahTable(response.data);
                } else {
                    $('#nasabah-table-body').html('<tr><td colspan="7" class="text-center text-danger">Gagal memuat data</td></tr>');
                }
            }).fail(error => {
                $('#nasabah-table-body').html('<tr><td colspan="7" class="text-center text-danger">Gagal memuat data</td></tr>');
            });
        }

        function renderNasabahTable(data) {
            if (!data || data.length === 0) {
                $('#nasabah-table-body').html('<tr><td colspan="7" class="text-center text-muted">Tidak ada data nasabah</td></tr>');
                return;
            }

            let html = '';
            data.forEach(n => {
                const statusClass = {
                    'aktif': 'success',
                    'nonaktif': 'warning',
                    'blacklist': 'danger'
                }[n.status] || 'secondary';

                html += `
                    <tr>
                        <td>${n.kode_nasabah || '-'}</td>
                        <td>
                            ${n.nama || ''}
                            ${n.foto_selfie ? '<i class="bi bi-camera-fill text-success" title="Ada foto"></i>' : ''}
                        </td>
                        <td>${n.ktp || '-'}</td>
                        <td>
                            <a href="https://wa.me/${n.telp.replace(/[^0-9]/g, '')}" target="_blank">
                                ${n.telp || '-'}
                                <i class="bi bi-whatsapp text-success"></i>
                            </a>
                        </td>
                        <td>${n.jenis_usaha || '-'}</td>
                        <td>
                            <span class="badge bg-${statusClass}">${n.status ? n.status.charAt(0).toUpperCase() + n.status.slice(1) : 'Aktif'}</span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="detail.php?id=${n.id}" class="btn btn-outline-primary" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="edit.php?id=${n.id}" class="btn btn-outline-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button onclick="confirmDelete(${n.id}, '${n.nama}')" class="btn btn-outline-danger" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });

            $('#nasabah-table-body').html(html);
        }

        function confirmDelete(id, nama) {
            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: `Apakah Anda yakin ingin menghapus nasabah ${nama}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonText: 'Batal',
                confirmButtonText: 'Ya, Hapus'
            }).then((result) => {
                if (result.isConfirmed) {
                    deleteNasabah(id);
                }
            });
        }

        function deleteNasabah(id) {
            window.KewerAPI.deleteNasabah(id).done(response => {
                if (response.success) {
                    Swal.fire('Berhasil', 'Nasabah berhasil dihapus', 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', response.error || 'Gagal menghapus nasabah', 'error');
                }
            }).fail(error => {
                Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
            });
        }

        function saveNasabah() {
            const form = document.getElementById('addForm');
            const formData = new FormData(form);
            
            window.KewerAPI.createNasabah(Object.fromEntries(formData)).done(response => {
                if (response.success) {
                    Swal.fire('Sukses', 'Nasabah berhasil ditambahkan', 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', response.error || 'Gagal menambahkan nasabah', 'error');
                }
            }).fail(error => {
                Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
            });
        }
        
        function showPending() {
            window.location.href = '?status=pending';
        }

        async function checkKTP() {
            const ktp = document.getElementById('ktpInput').value.trim();
            if (ktp.length === 0) return;

            try {
                const response = await fetch(`/kewer/api/search_people.php?ktp=${encodeURIComponent(ktp)}`);
                const data = await response.json();

                if (data.success && data.found) {
                    populateForm(data.data);
                    Swal.fire({
                        title: 'Data KTP Ditemukan!',
                        html: `Data dengan KTP <strong>${data.data.ktp}</strong> sudah terdaftar atas nama <strong>${data.data.nama}</strong><br><br>Form telah diisi otomatis.`,
                        icon: 'info',
                        confirmButtonText: 'OK'
                    });
                }
            } catch (error) {
                console.error('Error checking KTP:', error);
            }
        }

        async function checkTelp() {
            const telp = document.getElementById('telpInput').value.trim();
            if (telp.length < 10) return;

            try {
                const response = await fetch(`/kewer/api/search_people.php?telp=${encodeURIComponent(telp)}`);
                const data = await response.json();

                if (data.success && data.found) {
                    populateForm(data.data);
                    Swal.fire({
                        title: 'Data Telepon Ditemukan!',
                        html: `Data dengan No. Telepon <strong>${data.data.telp}</strong> sudah terdaftar atas nama <strong>${data.data.nama}</strong><br><br>Form telah diisi otomatis.`,
                        icon: 'info',
                        confirmButtonText: 'OK'
                    });
                }
            } catch (error) {
                console.error('Error checking telepon:', error);
            }
        }

        function populateForm(data) {
            if (data.nama) document.querySelector('input[name="nama"]').value = data.nama;
            if (data.ktp) document.querySelector('input[name="ktp"]').value = data.ktp;
            if (data.telp) document.querySelector('input[name="telp"]').value = data.telp;
            if (data.street_address) document.querySelector('textarea[name="alamat"]').value = data.street_address;
        }

        function handleImport() {
            const fileInput = document.getElementById('importFile');
            const file = fileInput.files[0];
            
            if (!file) {
                Swal.fire('Error', 'Pilih file CSV terlebih dahulu', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('csv_file', file);

            Swal.fire({
                title: 'Import Data',
                text: 'Sedang memproses...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch(`/kewer/api/import_data.php?entity=nasabah`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                Swal.fire({
                    title: data.success ? 'Berhasil' : 'Gagal',
                    text: `Total: ${data.total}, Berhasil: ${data.success_count}, Gagal: ${data.failed_count}`,
                    icon: data.success ? 'success' : 'error'
                }).then(() => {
                    if (data.success) {
                        location.reload();
                    }
                });
            })
            .catch(error => {
                Swal.fire('Error', 'Gagal mengupload file: ' + error.message, 'error');
            });
        }
    </script>
</body>
</html>
