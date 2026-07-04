<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();

$user = getCurrentUser();
$cabang_id = getCurrentCabang();

// Get penagihan list
$penagihan = query("
    SELECT p.*, 
           n.kode_nasabah, n.nama as nama_nasabah, n.telp, n.alamat,
           pin.kode_pinjaman, pin.plafon,
           a.no_angsuran, a.jatuh_tempo, a.total_angsuran,
           u.nama as nama_petugas,
           jp.nama as jenis_penagihan_nama
    FROM penagihan p
    JOIN pinjaman pin ON p.pinjaman_id = pin.id
    JOIN nasabah n ON pin.nasabah_id = n.id
    LEFT JOIN angsuran a ON p.angsuran_id = a.id
    LEFT JOIN users u ON p.petugas_id = u.id
    LEFT JOIN ref_jenis_penagihan jp ON p.jenis_penagihan_id = jp.id
    WHERE pin.cabang_id = ? OR ? IS NULL
    ORDER BY p.tanggal_jatuh_tempo DESC, p.status ASC
", [$cabang_id, $cabang_id]);

if (!is_array($penagihan)) {
    $penagihan = [];
}

// Get statistics
$stats = query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN p.status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN p.status = 'dalam_proses' THEN 1 ELSE 0 END) as dalam_proses,
        SUM(CASE WHEN p.status = 'berhasil' THEN 1 ELSE 0 END) as berhasil,
        SUM(CASE WHEN p.status = 'gagal' THEN 1 ELSE 0 END) as gagal
    FROM penagihan p
    JOIN pinjaman pin ON p.pinjaman_id = pin.id
    WHERE pin.cabang_id = ? OR ? IS NULL
", [$cabang_id, $cabang_id]);
$stats = $stats[0] ?? ['total' => 0, 'pending' => 0, 'dalam_proses' => 0, 'berhasil' => 0, 'gagal' => 0];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Penagihan - Kewer</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-0"><i class="bi bi-collection"></i> Manajemen Penagihan</h2>
                <p class="text-muted mb-0">Kelola aktivitas penagihan dan follow-up nasabah</p>
            </div>
            <button class="btn btn-primary" onclick="autoCreatePenagihan()">
                <i class="bi bi-arrow-clockwise"></i> Auto-Create Penagihan
            </button>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <h3 class="mb-0"><?= $stats['total'] ?></h3>
                        <small class="text-muted">Total Penagihan</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm border-start border-warning border-3">
                    <div class="card-body text-center">
                        <h3 class="mb-0 text-warning"><?= $stats['pending'] ?></h3>
                        <small class="text-muted">Pending</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm border-start border-info border-3">
                    <div class="card-body text-center">
                        <h3 class="mb-0 text-info"><?= $stats['dalam_proses'] ?></h3>
                        <small class="text-muted">Dalam Proses</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm border-start border-success border-3">
                    <div class="card-body text-center">
                        <h3 class="mb-0 text-success"><?= $stats['berhasil'] ?></h3>
                        <small class="text-muted">Berhasil</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <table id="tablePenagihan" class="table table-hover table-striped mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Kode Pinjaman</th>
                            <th>Nasabah</th>
                            <th>Jenis</th>
                            <th>Jatuh Tempo</th>
                            <th>Status</th>
                            <th>Petugas</th>
                            <th>Hasil</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($penagihan as $p): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($p['kode_pinjaman'] ?? '-') ?></strong>
                            </td>
                            <td>
                                <div>
                                    <strong><?= htmlspecialchars($p['nama_nasabah'] ?? '-') ?></strong>
                                    <br><small class="text-muted"><?= htmlspecialchars($p['telp'] ?? '-') ?></small>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($p['jenis_penagihan_nama'] ?? '-') ?></td>
                            <td><?= date('d/m/Y', strtotime($p['tanggal_jatuh_tempo'])) ?></td>
                            <td><?= getStatusBadge($p['status']) ?></td>
                            <td><?= htmlspecialchars($p['nama_petugas'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($p['hasil'] ?? '-') ?></td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-primary btn-detail" data-id="<?= $p['id'] ?>" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <?php if ($p['status'] == 'pending'): ?>
                                <button class="btn btn-sm btn-outline-success btn-proses" data-id="<?= $p['id'] ?>" title="Proses">
                                    <i class="bi bi-play-fill"></i>
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

    <!-- Modal Proses -->
    <div class="modal fade" id="modalProses" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Proses Penagihan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formProses">
                    <div class="modal-body">
                        <input type="hidden" id="penagihanId">
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" id="status" required>
                                <option value="dalam_proses">Dalam Proses</option>
                                <option value="berhasil">Berhasil</option>
                                <option value="gagal">Gagal</option>
                                <option value="diabaikan">Diabaikan</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Hasil</label>
                            <textarea class="form-control" id="hasil" rows="3" placeholder="Catat hasil penagihan..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tindakan</label>
                            <textarea class="form-control" id="tindakan" rows="2" placeholder="Tindakan yang dilakukan..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    $(document).ready(function() {
        $('#tablePenagihan').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json' },
            pageLength: 25,
            order: [[3, 'desc']]
        });

        function getStatusBadge(status) {
            const badges = {
                'pending': '<span class="badge bg-warning text-dark">Pending</span>',
                'dalam_proses': '<span class="badge bg-info">Dalam Proses</span>',
                'berhasil': '<span class="badge bg-success">Berhasil</span>',
                'gagal': '<span class="badge bg-danger">Gagal</span>',
                'diabaikan': '<span class="badge bg-secondary">Diabaikan</span>'
            };
            return badges[status] || badges['pending'];
        }

        window.autoCreatePenagihan = function() {
            Swal.fire({
                title: 'Auto-Create Penagihan?',
                text: 'Akan membuat penagihan untuk angsuran yang jatuh tempo',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Buat'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '../../api/penagihan.php?action=auto_create',
                        method: 'POST',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Berhasil', response.message, 'success').then(() => location.reload());
                            } else {
                                Swal.fire('Error', response.error || 'Gagal membuat penagihan', 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error', 'Terjadi kesalahan', 'error');
                        }
                    });
                }
            });
        };

        $(document).on('click', '.btn-proses', function() {
            const id = $(this).data('id');
            $('#penagihanId').val(id);
            $('#modalProses').modal('show');
        });

        $('#formProses').submit(function(e) {
            e.preventDefault();
            const data = {
                id: $('#penagihanId').val(),
                status: $('#status').val(),
                hasil: $('#hasil').val(),
                tindakan: $('#tindakan').val()
            };

            $.ajax({
                url: '../../api/penagihan.php',
                method: 'PUT',
                contentType: 'application/json',
                data: JSON.stringify(data),
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Berhasil', 'Penagihan berhasil diupdate', 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Error', response.error || 'Gagal update penagihan', 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Terjadi kesalahan', 'error');
                }
            });
        });
    });
    </script>
</body>
</html>
