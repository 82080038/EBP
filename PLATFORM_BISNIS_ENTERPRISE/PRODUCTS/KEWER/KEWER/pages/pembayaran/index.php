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
    <title>Pembayaran - <?php echo APP_NAME; ?></title>
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
                    <h1 class="h2">Pembayaran</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                        <i class="bi bi-plus"></i> Tambah Pembayaran
                    </button>
                </div>
                
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="pembayaranTable">
                                <thead>
                                    <tr>
                                        <th>Kode</th>
                                        <th>Kode Pinjaman</th>
                                        <th>Nasabah</th>
                                        <th>Tanggal Bayar</th>
                                        <th>Jumlah Bayar</th>
                                        <th>Denda</th>
                                        <th>Total</th>
                                        <th>Cara Bayar</th>
                                        <th>Petugas</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="pembayaran-table-body">
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
            </main>
        </div>
    </div>

    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Pembayaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="tambah.php">
                        <div class="mb-3">
                            <label>Pinjaman *</label>
                            <select name="pinjaman_id" class="form-select" required>
                                <option value="">Pilih Pinjaman</option>
                                <?php
                                $pinjaman = query("SELECT p.id, p.kode_pinjaman, n.nama FROM pinjaman p JOIN nasabah n ON p.nasabah_id = n.id WHERE p.cabang_id = ? AND p.status = 'aktif'", [$cabang_id]);
                                if (!is_array($pinjaman)) {
                                    $pinjaman = [];
                                }
                                foreach ($pinjaman as $p): ?>
                                    <option value="<?= $p['id'] ?>"><?= $p['kode_pinjaman'] ?> - <?= $p['nama'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Angsuran *</label>
                            <select name="angsuran_id" class="form-select" required>
                                <option value="">Pilih Angsuran</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Jumlah Bayar *</label>
                                <input type="number" name="jumlah_bayar" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Denda</label>
                                <input type="number" name="denda" class="form-control" value="0">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label>Tanggal Bayar *</label>
                            <input type="date" name="tanggal_bayar" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label>Cara Bayar *</label>
                            <select name="cara_bayar" class="form-select" required>
                                <option value="tunai">Tunai</option>
                                <option value="transfer">Transfer</option>
                                <option value="digital">Digital</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Bukti Bayar</label>
                            <input type="file" name="bukti_bayar" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>Keterangan</label>
                            <textarea name="keterangan" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
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
            // Only initialize DataTable if there's data
            var hasData = <?php echo !empty($pembayaran) ? 'true' : 'false'; ?>;

            if (hasData) {
                try {
                    var table = $('#pembayaranTable').DataTable({
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
                        order: [[3, 'desc']]
                    });
                } catch (e) {
                    console.error('DataTables initialization error:', e);
                    $('#pembayaranTable').removeClass('table-striped table-hover');
                }
            } else {
                // Hide DataTables controls when no data
                $('#pembayaranTable').removeClass('table-striped table-hover');
                $('#pembayaranTable_wrapper').hide();
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
        
        // Load pembayaran data via JSON API
        $(document).ready(function() {
            loadPembayaranData();
        });

        function loadPembayaranData() {
            window.KewerAPI.getPembayaran().done(response => {
                if (response.success) {
                    renderPembayaranTable(response.data);
                } else {
                    $('#pembayaran-table-body').html('<tr><td colspan="10" class="text-center text-danger">Gagal memuat data</td></tr>');
                }
            }).fail(error => {
                $('#pembayaran-table-body').html('<tr><td colspan="10" class="text-center text-danger">Gagal memuat data</td></tr>');
            });
        }

        function renderPembayaranTable(data) {
            if (!data || data.length === 0) {
                $('#pembayaran-table-body').html('<tr><td colspan="10" class="text-center text-muted">Tidak ada data pembayaran</td></tr>');
                return;
            }

            let html = '';
            data.forEach(p => {
                html += `
                    <tr>
                        <td>${p.kode_pembayaran || '-'}</td>
                        <td>${p.kode_pinjaman || '-'}</td>
                        <td>${p.nama_nasabah || '-'}</td>
                        <td>${formatDate(p.tanggal_bayar)}</td>
                        <td>Rp ${formatRupiah(p.jumlah_bayar)}</td>
                        <td>Rp ${formatRupiah(p.denda)}</td>
                        <td>Rp ${formatRupiah(p.total_bayar)}</td>
                        <td>${p.cara_bayar || '-'}</td>
                        <td>${p.nama_petugas || '-'}</td>
                        <td>
                            <a href="edit.php?id=${p.id}" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button onclick="confirmDelete(${p.id})" class="btn btn-sm btn-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });

            $('#pembayaran-table-body').html(html);
        }

        function confirmDelete(id) {
            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: 'Apakah Anda yakin ingin menghapus pembayaran ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    deletePembayaran(id);
                }
            });
        }

        function deletePembayaran(id) {
            window.KewerAPI.deletePembayaran(id).done(response => {
                if (response.success) {
                    Swal.fire('Berhasil', 'Pembayaran berhasil dihapus', 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', response.error || 'Gagal menghapus pembayaran', 'error');
                }
            }).fail(error => {
                Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
            });
        }

        // Load angsuran when pinjaman is selected
        document.querySelector('select[name="pinjaman_id"]').addEventListener('change', function() {
            const pinjamanId = this.value;
            const angsuranSelect = document.querySelector('select[name="angsuran_id"]');
            
            if (pinjamanId) {
                fetch(`/api/angsuran?action=by_pinjaman&pinjaman_id=${pinjamanId}`, {
                    headers: {
                        'Authorization': 'Bearer kewer-api-token-2024'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    angsuranSelect.innerHTML = '<option value="">Pilih Angsuran</option>';
                    data.data.forEach(a => {
                        angsuranSelect.innerHTML += `<option value="${a.id}">${a.no_angsuran} - ${formatRupiah(a.total_angsuran)} - ${a.status}</option>`;
                    });
                });
            } else {
                angsuranSelect.innerHTML = '<option value="">Pilih Angsuran</option>';
            }
        });
        
        // Convert session alerts to SweetAlert2
        <?php
        if (isset($_SESSION['success'])) {
            echo "Swal.fire({icon: 'success', title: 'Berhasil', text: '" . $_SESSION['success'] . "', timer: 3000, showConfirmButton: false});";
            unset($_SESSION['success']);
        }
        if (isset($_SESSION['error'])) {
            echo "Swal.fire({icon: 'error', title: 'Gagal', text: '" . $_SESSION['error'] . "', timer: 3000, showConfirmButton: false});";
            unset($_SESSION['error']);
        }
        ?>
    </script>
</body>
</html>
