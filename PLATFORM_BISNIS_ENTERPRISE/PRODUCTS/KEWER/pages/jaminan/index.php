<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();

if (!hasPermission('view_laporan') && !hasPermission('manage_pinjaman')) {
    header('Location: ' . baseUrl('dashboard.php'));
    exit();
}

$kantor_id = 1; // Single office
$cabang_id = getCurrentCabang() ?: $kantor_id;

// Filters
$filter_status = $_GET['status'] ?? '';
$filter_tipe = $_GET['tipe'] ?? '';
$filter_search = $_GET['search'] ?? '';

// Build query
$where = ["1=1"];
$params = [];

if ($filter_status) {
    $where[] = "p.jaminan_status = ?";
    $params[] = $filter_status;
}
if ($filter_tipe) {
    $where[] = "p.jaminan_tipe = ?";
    $params[] = $filter_tipe;
}
if ($filter_search) {
    $where[] = "(n.nama LIKE ? OR p.kode_pinjaman LIKE ?)";
    $params[] = "%$filter_search%";
    $params[] = "%$filter_search%";
}

$where_clause = implode(" AND ", $where);

$jaminan = query("
    SELECT p.*, n.nama as nama_nasabah, n.telp, u.nama as nama_petugas,
           c.nama_cabang
    FROM pinjaman p
    JOIN nasabah n ON p.nasabah_id = n.id
    LEFT JOIN users u ON p.petugas_id = u.id
    LEFT JOIN cabang c ON p.cabang_id = c.id
    WHERE $where_clause AND p.jaminan_tipe != 'tanpa'
    ORDER BY p.created_at DESC
    LIMIT 500
", $params);
if (!is_array($jaminan)) $jaminan = [];

// Get reference data for jaminan tipe
$jaminan_tipe_list = getActiveReferenceData('ref_jaminan_tipe');
if (!is_array($jaminan_tipe_list)) $jaminan_tipe_list = [];

// Stats
$stats = query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN jaminan_status = 'aktif' THEN 1 ELSE 0 END) as aktif,
        SUM(CASE WHEN jaminan_status = 'dilepas' THEN 1 ELSE 0 END) as dilepas,
        SUM(CASE WHEN jaminan_status = 'terjual' THEN 1 ELSE 0 END) as terjual,
        SUM(CASE WHEN jaminan_status = 'hilang' THEN 1 ELSE 0 END) as hilang
    FROM pinjaman
    WHERE cabang_id = ? AND jaminan_tipe != 'tanpa'
", [$cabang_id]);
$stat = is_array($stats) && isset($stats[0]) ? $stats[0] : ['total' => 0, 'aktif' => 0, 'dilepas' => 0, 'terjual' => 0, 'hilang' => 0];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Jaminan - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        .sidebar { min-height: calc(vh - 56px); }
        .status-aktif { border-left: 4px solid #198754; }
        .status-dilepas { border-left: 4px solid #0dcaf0; }
        .status-terjual { border-left: 4px solid #dc3545; }
        .status-hilang { border-left: 4px solid #ffc107; }
    </style>
</head>
<body>
    <div class="main-container">
        <?php require_once BASE_PATH . '/includes/sidebar.php'; ?>

        <main class="content-area">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="bi bi-shield-lock"></i> Manajemen Jaminan</h1>
            </div>
            
            <!-- Stats -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body py-2">
                            <h6>Total Jaminan</h6>
                            <h4><?php echo number_format($stat['total'] ?? 0); ?></h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body py-2">
                            <h6>Aktif</h6>
                            <h4><?php echo number_format($stat['aktif'] ?? 0); ?></h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body py-2">
                            <h6>Dilepas</h6>
                            <h4><?php echo number_format($stat['dilepas'] ?? 0); ?></h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body py-2">
                            <h6>Terjual/Hilang</h6>
                            <h4><?php echo number_format(($stat['terjual'] ?? 0) + ($stat['hilang'] ?? 0)); ?></h4>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label form-label-sm">Status</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="">Semua Status</option>
                                <option value="aktif" <?php echo $filter_status === 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                                <option value="dilepas" <?php echo $filter_status === 'dilepas' ? 'selected' : ''; ?>>Dilepas</option>
                                <option value="terjual" <?php echo $filter_status === 'terjual' ? 'selected' : ''; ?>>Terjual</option>
                                <option value="hilang" <?php echo $filter_status === 'hilang' ? 'selected' : ''; ?>>Hilang</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label form-label-sm">Tipe Jaminan</label>
                            <select name="tipe" class="form-select form-select-sm">
                                <option value="">Semua Tipe</option>
                                <?php foreach ($jaminan_tipe_list as $jt): ?>
                                    <?php $kode = str_replace('JAM', '', strtolower($jt['tipe_kode'])); ?>
                                    <option value="<?php echo $kode; ?>" <?php echo $filter_tipe === $kode ? 'selected' : ''; ?>><?php echo htmlspecialchars($jt['tipe_nama']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label form-label-sm">Cari</label>
                            <input type="text" name="search" class="form-control form-control-sm" value="<?php echo htmlspecialchars($filter_search); ?>" placeholder="Nama atau Kode Pinjaman">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-sm btn-primary me-1"><i class="bi bi-search"></i> Filter</button>
                            <a href="index.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-clockwise"></i></a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Jaminan Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tableJaminan" class="table table-striped table-sm table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Kode Pinjaman</th>
                                    <th>Nasabah</th>
                                    <th>Tipe</th>
                                    <th>Nilai</th>
                                    <th>Deskripsi</th>
                                    <th>Status</th>
                                    <th>Petugas</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($jaminan)): ?>
                                    <tr><td colspan="8" class="text-center text-muted">Tidak ada data jaminan</td></tr>
                                <?php else: ?>
                                    <?php foreach ($jaminan as $j): ?>
                                        <tr class="status-<?php echo $j['jaminan_status'] ?? 'aktif'; ?>">
                                            <td>
                                                <strong><?php echo $j['kode_pinjaman']; ?></strong>
                                                <br><small class="text-muted"><?php echo formatDate($j['tanggal_akad'], 'd/m/Y'); ?></small>
                                            </td>
                                            <td>
                                                <strong><?php echo $j['nama_nasabah']; ?></strong>
                                                <br><small class="text-muted"><?php echo $j['telp'] ?? '-'; ?></small>
                                            </td>
                                            <td>
                                                <?php
                                                $tipe_labels = [
                                                    'bpkb' => 'BPKB Kendaraan',
                                                    'shm' => 'Sertifikat Tanah',
                                                    'ajb' => 'Akta Jual Beli',
                                                    'tabungan' => 'Buku Tabungan'
                                                ];
                                                echo $tipe_labels[$j['jaminan_tipe']] ?? ucfirst($j['jaminan_tipe']);
                                                ?>
                                            </td>
                                            <td class="text-end">
                                                <?php if ($j['jaminan_nilai']): ?>
                                                    <?php echo formatRupiah($j['jaminan_nilai']); ?>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <small><?php echo htmlspecialchars($j['jaminan'] ?? '-'); ?></small>
                                                <?php if ($j['jaminan_dokumen']): ?>
                                                    <br><small class="text-info"><i class="bi bi-file-earmark"></i> Dokumen ada</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $status_colors = [
                                                    'aktif' => 'success',
                                                    'dilepas' => 'info',
                                                    'terjual' => 'danger',
                                                    'hilang' => 'warning'
                                                ];
                                                $status_labels = [
                                                    'aktif' => 'Aktif',
                                                    'dilepas' => 'Dilepas',
                                                    'terjual' => 'Terjual',
                                                    'hilang' => 'Hilang'
                                                ];
                                                $status = $j['jaminan_status'] ?? 'aktif';
                                                ?>
                                                <span class="badge bg-<?php echo $status_colors[$status] ?? 'secondary'; ?>">
                                                    <?php echo $status_labels[$status] ?? ucfirst($status); ?>
                                                </span>
                                            </td>
                                            <td><small><?php echo $j['nama_petugas'] ?? '-'; ?></small></td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="../pinjaman/detail.php?id=<?php echo $j['id']; ?>" class="btn btn-outline-primary" title="Detail Pinjaman">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <?php if (hasPermission('manage_pinjaman')): ?>
                                                    <button class="btn btn-outline-warning btn-update-status" 
                                                            data-id="<?php echo $j['id']; ?>" 
                                                            data-status="<?php echo $status; ?>"
                                                            title="Update Status">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Update Status Modal -->
    <div class="modal fade" id="modalUpdateStatus" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil"></i> Update Status Jaminan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formUpdateStatus">
                        <input type="hidden" id="updateId" name="id">
                        <div class="mb-3">
                            <label class="form-label">Status Jaminan</label>
                            <select name="jaminan_status" class="form-select" required>
                                <option value="aktif">Aktif</option>
                                <option value="dilepas">Dilepas</option>
                                <option value="terjual">Terjual</option>
                                <option value="hilang">Hilang</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Catatan</label>
                            <textarea name="catatan" class="form-control" rows="3" placeholder="Alasan perubahan status..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnSaveStatus">
                        <i class="bi bi-save"></i> Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    let modalUpdateStatus;
    
    $(document).ready(function() {
        // Initialize DataTable
        $('#tableJaminan').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
            },
            pageLength: 25,
            order: [[0, 'desc']]
        });
        
        modalUpdateStatus = new bootstrap.Modal(document.getElementById('modalUpdateStatus'));
        
        // Update status button
        $('.btn-update-status').click(function() {
            const id = $(this).data('id');
            const status = $(this).data('status');
            
            $('#updateId').val(id);
            $('#formUpdateStatus select[name="jaminan_status"]').val(status);
            modalUpdateStatus.show();
        });
        
        // Save status
        $('#btnSaveStatus').click(function() {
            const id = $('#updateId').val();
            const status = $('#formUpdateStatus select[name="jaminan_status"]').val();
            const catatan = $('#formUpdateStatus textarea[name="catatan"]').val();
            
            $(this).prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Menyimpan...');
            
            $.ajax({
                url: '../../api/pinjaman.php',
                method: 'PUT',
                contentType: 'application/json',
                data: JSON.stringify({
                    id: id,
                    jaminan_status: status,
                    catatan_status: catatan
                }),
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: 'Status jaminan berhasil diupdate',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', response.error || 'Gagal mengupdate status', 'error');
                    }
                },
                error: function(xhr) {
                    const resp = xhr.responseJSON || {};
                    Swal.fire('Error', resp.error || 'Terjadi kesalahan', 'error');
                },
                complete: function() {
                    $('#btnSaveStatus').prop('disabled', false).html('<i class="bi bi-save"></i> Simpan');
                }
            });
        });
    });
    </script>
</body>
</html>
