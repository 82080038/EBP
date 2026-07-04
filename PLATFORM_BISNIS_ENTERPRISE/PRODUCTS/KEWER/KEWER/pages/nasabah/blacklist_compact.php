<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();

$user = getCurrentUser();
$user_cabang_id = $user['cabang_id'] ?? null;
$kantor_id = $user_cabang_id ?? 1; // Use user's cabang_id or default to 1

$id = $_GET['id'] ?? null;
$can_manage = in_array($user['role'], ['bos', 'manager_pusat', 'manager_cabang', 'admin_pusat', 'admin_cabang']);

// Get blacklisted nasabah
$blacklist = query("
    SELECT n.*, 
           COUNT(p.id) as total_pinjaman,
           SUM(CASE WHEN p.status = 'lunas' THEN 1 ELSE 0 END) as pinjaman_lunas,
           n.blacklist_reason
    FROM nasabah n
    LEFT JOIN pinjaman p ON n.id = p.nasabah_id
    WHERE n.status = 'blacklist'
    GROUP BY n.id
    ORDER BY n.updated_at DESC
");

if (!is_array($blacklist)) {
    $blacklist = [];
}

// Get active nasabah count for stats
$stats = query("
    SELECT 
        SUM(CASE WHEN status = 'aktif' THEN 1 ELSE 0 END) as aktif,
        SUM(CASE WHEN status = 'blacklist' THEN 1 ELSE 0 END) as blacklist_count,
        SUM(CASE WHEN status = 'nonaktif' THEN 1 ELSE 0 END) as nonaktif
    FROM nasabah
");
$stats = $stats[0] ?? ['aktif' => 0, 'blacklist_count' => 0, 'nonaktif' => 0];

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blacklist Nasabah - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        .blacklist-card { border-left: 4px solid #dc3545; }
        .status-badge { font-size: 0.75rem; }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand" href="../../dashboard.php"><i class="bi bi-bank"></i> <?= APP_NAME ?></a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="index.php"><i class="bi bi-people"></i> Nasabah</a>
                <a class="nav-link active" href="blacklist_compact.php"><i class="bi bi-shield-exclamation"></i> Blacklist</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0"><i class="bi bi-shield-exclamation text-danger"></i> Blacklist Nasabah</h4>
                <small class="text-muted">Daftar nasabah yang diblokir dari pengajuan pinjaman</small>
            </div>
            <a href="index.php" class="btn btn-outline-primary">
                <i class="bi bi-people"></i> Semua Nasabah
            </a>
        </div>

        <!-- Stats -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card bg-white border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-shrink-0 bg-success bg-opacity-10 rounded p-3">
                            <i class="bi bi-people-fill text-success fs-4"></i>
                        </div>
                        <div class="ms-3">
                            <h5 class="mb-0"><?= $stats['aktif'] ?></h5>
                            <small class="text-muted">Nasabah Aktif</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-white border-danger border-2 shadow-sm">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-shrink-0 bg-danger bg-opacity-10 rounded p-3">
                            <i class="bi bi-shield-exclamation text-danger fs-4"></i>
                        </div>
                        <div class="ms-3">
                            <h5 class="mb-0 text-danger"><?= $stats['blacklist_count'] ?></h5>
                            <small class="text-muted">Blacklist</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-white border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-shrink-0 bg-secondary bg-opacity-10 rounded p-3">
                            <i class="bi bi-person-x text-secondary fs-4"></i>
                        </div>
                        <div class="ms-3">
                            <h5 class="mb-0"><?= $stats['nonaktif'] ?></h5>
                            <small class="text-muted">Nonaktif</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Blacklist Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                <span><i class="bi bi-list"></i> Daftar Nasabah Blacklist</span>
                <span class="badge bg-white text-danger"><?= count($blacklist) ?> nasabah</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="tableBlacklist" class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nasabah</th>
                                <th>Kontak</th>
                                <th>Alamat</th>
                                <th>Riwayat Pinjaman</th>
                                <th>Alasan Blacklist</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($blacklist as $n): ?>
                            <tr class="blacklist-card">
                                <td>
                                    <strong><?= htmlspecialchars($n['nama']) ?></strong>
                                    <br><small class="text-muted"><?= htmlspecialchars($n['kode_nasabah'] ?? '-') ?></small>
                                </td>
                                <td>
                                    <i class="bi bi-telephone"></i> <?= htmlspecialchars($n['telp'] ?? '-') ?>
                                    <br><small class="text-muted"><?= htmlspecialchars($n['ktp'] ?? '-') ?></small>
                                </td>
                                <td><?= htmlspecialchars($n['alamat'] ?? '-') ?></td>
                                <td>
                                    <span class="badge bg-success"><?= $n['pinjaman_lunas'] ?> Lunas</span>
                                    <span class="badge bg-info"><?= $n['total_pinjaman'] ?> Total</span>
                                </td>
                                <td>
                                    <span class="text-danger"><i class="bi bi-exclamation-triangle"></i></span>
                                    <?= htmlspecialchars($n['blacklist_reason'] ?? 'Tidak ada alasan') ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($can_manage): ?>
                                    <button class="btn btn-success btn-sm btn-unblock" 
                                            data-id="<?= $n['id'] ?>" 
                                            data-nama="<?= htmlspecialchars($n['nama']) ?>">
                                        <i class="bi bi-check-circle"></i> Buka Blokir
                                    </button>
                                    <?php endif; ?>
                                    <a href="detail.php?id=<?= $n['id'] ?>" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($blacklist)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="bi bi-shield-check text-success fs-1"></i>
                                    <p class="text-muted mt-2">Tidak ada nasabah yang di-blacklist</p>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Info Card -->
        <div class="alert alert-info mt-4 d-flex align-items-center">
            <i class="bi bi-info-circle fs-4 me-3"></i>
            <div>
                <strong>Informasi Blacklist:</strong>
                <ul class="mb-0 mt-1">
                    <li>Nasabah yang di-blacklist tidak dapat mengajukan pinjaman baru</li>
                    <li>Nasabah harus melunasi semua pinjaman sebelum bisa di-blacklist</li>
                    <li>Hanya Manager/Owner yang dapat membuka blokir</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Unblock Modal -->
    <div class="modal fade" id="modalUnblock" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="bi bi-check-circle"></i> Buka Blokir Nasabah</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Anda akan membuka blokir untuk nasabah:</p>
                    <h5 id="unblockNama" class="text-success"></h5>
                    <div class="alert alert-warning mt-3">
                        <i class="bi bi-exclamation-triangle"></i>
                        Nasabah akan dapat mengajukan pinjaman kembali.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success" id="btnConfirmUnblock">
                        <i class="bi bi-check-circle"></i> Ya, Buka Blokir
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
    let unblockId = null;
    const modalUnblock = new bootstrap.Modal(document.getElementById('modalUnblock'));

    $(document).ready(function() {
        // Initialize DataTable with null handling
        $('#tableBlacklist').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
            },
            pageLength: 25,
            order: [[4, 'desc']], // Sort by blacklist date
            columnDefs: [
                { orderable: false, targets: [5] },
                {
                    targets: '_all',
                    render: function(data, type, row) {
                        if (data === null || data === undefined || data === '') {
                            return '<span class="text-muted">-</span>';
                        }
                        return data;
                    }
                }
            ]
        });

        // Unblock button handler
        $('.btn-unblock').click(function() {
            unblockId = $(this).data('id');
            const nama = $(this).data('nama');
            $('#unblockNama').text(nama);
            modalUnblock.show();
        });

        // Confirm unblock
        $('#btnConfirmUnblock').click(function() {
            if (!unblockId) return;

            $(this).prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Memproses...');

            $.ajax({
                url: '../../api/nasabah_blacklist.php',
                method: 'PUT',
                contentType: 'application/json',
                data: JSON.stringify({
                    nasabah_id: unblockId,
                    status: 'aktif'
                }),
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', response.error || 'Gagal membuka blokir', 'error');
                    }
                },
                error: function(xhr) {
                    const resp = xhr.responseJSON || {};
                    Swal.fire('Error', resp.error || 'Terjadi kesalahan', 'error');
                },
                complete: function() {
                    $('#btnConfirmUnblock').prop('disabled', false).html('<i class="bi bi-check-circle"></i> Ya, Buka Blokir');
                }
            });
        });
    });
    </script>
    <!-- Include Notifications JS for compact pages -->
    <script src="<?php echo baseUrl('includes/js/notifications.js'); ?>"></script>
    <script>
    // Initialize notifications for compact page
    $(document).ready(function() {
        window.KewerNotifications.updateBadge();
    });
    </script>
</body>
</html>
