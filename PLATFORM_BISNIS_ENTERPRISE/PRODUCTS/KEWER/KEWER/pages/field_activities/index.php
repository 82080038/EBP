<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
require_once BASE_PATH . '/config/session.php';
requireLogin();

// Permission check
if (!hasPermission('angsuran.read')) {
    header('Location: ' . baseUrl('dashboard.php'));
    exit();
}

$user = getCurrentUser();
$cabang_id = getCurrentCabang();
$role = $user['role'];

// Get activities based on role
if (!in_array($role, ['petugas_pusat', 'petugas_cabang']) && $cabang_id) {
    // Petugas can only see their own activities
    $activities = query("SELECT foa.*, u.nama as petugas_nama, n.nama as nasabah_nama, 
                           p.kode_pinjaman, a.no_angsuran
                        FROM field_officer_activities foa
                        LEFT JOIN users u ON foa.petugas_id = u.id
                        LEFT JOIN nasabah n ON foa.nasabah_id = n.id
                        LEFT JOIN pinjaman p ON foa.pinjaman_id = p.id
                        LEFT JOIN angsuran a ON foa.angsuran_id = a.id
                        WHERE foa.petugas_id = ? AND foa.cabang_id = ?
                        ORDER BY foa.activity_date DESC, foa.activity_time DESC
                        LIMIT 100",
                        [$user['id'], $cabang_id]);
} elseif (in_array($role, ['manager_pusat', 'manager_cabang'])) {
    // Manager can see all activities in their branch
    $activities = query("SELECT foa.*, u.nama as petugas_nama, n.nama as nasabah_nama, 
                           p.kode_pinjaman, a.no_angsuran
                        FROM field_officer_activities foa
                        LEFT JOIN users u ON foa.petugas_id = u.id
                        LEFT JOIN nasabah n ON foa.nasabah_id = n.id
                        LEFT JOIN pinjaman p ON foa.pinjaman_id = p.id
                        LEFT JOIN angsuran a ON foa.angsuran_id = a.id
                        WHERE foa.cabang_id = ?
                        ORDER BY foa.activity_date DESC, foa.activity_time DESC
                        LIMIT 200",
                        [$cabang_id]);
} else {
    // Owner, bos, admin can see all activities
    $activities = query("SELECT foa.*, u.nama as petugas_nama, c.nama_cabang, n.nama as nasabah_nama, 
                           p.kode_pinjaman, a.no_angsuran
                        FROM field_officer_activities foa
                        LEFT JOIN users u ON foa.petugas_id = u.id
                        LEFT JOIN cabang c ON foa.cabang_id = c.id
                        LEFT JOIN nasabah n ON foa.nasabah_id = n.id
                        LEFT JOIN pinjaman p ON foa.pinjaman_id = p.id
                        LEFT JOIN angsuran a ON foa.angsuran_id = a.id
                        ORDER BY foa.activity_date DESC, foa.activity_time DESC
                        LIMIT 500");
}

if (!is_array($activities)) {
    $activities = [];
}

// Get petugas list for filter (manager and above)
$petugasList = [];
if (!in_array($role, ['petugas_pusat', 'petugas_cabang']) && $cabang_id) {
    $petugasList = query("SELECT id, nama FROM users WHERE cabang_id = ? AND role IN ('petugas_pusat', 'petugas_cabang')", [$cabang_id]);
    if (!is_array($petugasList)) {
        $petugasList = [];
    }
}

// Activity type labels
$activityLabels = [
    'survey_nasabah' => 'Survey Nasabah',
    'input_pinjaman' => 'Input Pinjaman',
    'kutip_angsuran' => 'Kutip Angsuran',
    'follow_up' => 'Follow Up',
    'promosi' => 'Promosi',
    'edukasi' => 'Edukasi',
    'lainnya' => 'Lainnya'
];

// Activity type colors
$activityColors = [
    'survey_nasabah' => 'primary',
    'input_pinjaman' => 'success',
    'kutip_angsuran' => 'info',
    'follow_up' => 'warning',
    'promosi' => 'secondary',
    'edukasi' => 'dark',
    'lainnya' => 'light'
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aktivitas Petugas Lapangan - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="main-container">
        <?php require_once BASE_PATH . '/includes/sidebar.php'; ?>

        <main class="content-area">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="bi bi-person-badge"></i> Aktivitas Petugas Lapangan</h2>
                    <?php if (in_array($role, ['petugas_pusat', 'petugas_cabang'])): ?>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                        <i class="bi bi-plus"></i> Catat Aktivitas
                    </button>
                    <?php endif; ?>
                </div>
                
                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Aktivitas</h5>
                                <h3 class="card-text"><?php echo count($activities); ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Survey Nasabah</h5>
                                <h3 class="card-text"><?php echo count(array_filter($activities, fn($a) => $a['activity_type'] === 'survey_nasabah')); ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title">Kutip Angsuran</h5>
                                <h3 class="card-text"><?php echo count(array_filter($activities, fn($a) => $a['activity_type'] === 'kutip_angsuran')); ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-dark">
                            <div class="card-body">
                                <h5 class="card-title">Follow Up</h5>
                                <h3 class="card-text"><?php echo count(array_filter($activities, fn($a) => $a['activity_type'] === 'follow_up')); ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Petugas</label>
                                <select class="form-select" id="filterPetugas">
                                    <option value="">Semua Petugas</option>
                                    <?php foreach ($petugasList as $p): ?>
                                    <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['nama']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Jenis Aktivitas</label>
                                <select class="form-select" id="filterActivityType">
                                    <option value="">Semua Jenis</option>
                                    <?php foreach ($activityLabels as $key => $label): ?>
                                    <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Tanggal Mulai</label>
                                <input type="date" class="form-control" id="filterTanggalMulai">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Tanggal Selesai</label>
                                <input type="date" class="form-control" id="filterTanggalSelesai">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Activities Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="activitiesTable">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Waktu</th>
                                        <th>Petugas</th>
                                        <?php if (!in_array($role, ['petugas_pusat', 'petugas_cabang'])): ?>
                                        <th>Cabang</th>
                                        <?php endif; ?>
                                        <th>Jenis Aktivitas</th>
                                        <th>Deskripsi</th>
                                        <th>Nasabah</th>
                                        <th>Lokasi</th>
                                        <th>Status</th>
                                        <?php if (in_array($role, ['petugas_pusat', 'petugas_cabang'])): ?>
                                        <th>Aksi</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($activities as $a): ?>
                                    <tr data-petugas="<?php echo $a['petugas_id']; ?>" 
                                        data-activity-type="<?php echo $a['activity_type']; ?>"
                                        data-tanggal="<?php echo $a['activity_date']; ?>">
                                        <td><?php echo date('d/m/Y', strtotime($a['activity_date'])); ?></td>
                                        <td><?php echo substr($a['activity_time'], 0, 5); ?></td>
                                        <td><?php echo htmlspecialchars($a['petugas_nama']); ?></td>
                                        <?php if (!in_array($role, ['petugas_pusat', 'petugas_cabang'])): ?>
                                        <td><?php echo htmlspecialchars($a['nama_cabang'] ?? '-'); ?></td>
                                        <?php endif; ?>
                                        <td>
                                            <span class="badge bg-<?php echo $activityColors[$a['activity_type']] ?? 'secondary'; ?>">
                                                <?php echo $activityLabels[$a['activity_type']] ?? ucfirst($a['activity_type']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($a['description'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($a['nasabah_nama'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($a['location'] ?? '-'); ?></td>
                                        <td>
                                            <span class="badge <?php echo $a['status'] === 'completed' ? 'bg-success' : ($a['status'] === 'pending' ? 'bg-warning' : 'bg-danger'); ?>">
                                                <?php echo ucfirst($a['status']); ?>
                                            </span>
                                        </td>
                                        <?php if (in_array($role, ['petugas_pusat', 'petugas_cabang'])): ?>
                                        <td>
                                            <button class="btn btn-sm btn-primary" onclick="editActivity(<?php echo $a['id']; ?>)">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="deleteActivity(<?php echo $a['id']; ?>)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                        <?php endif; ?>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Add Activity Modal (for petugas) -->
    <?php if (in_array($role, ['petugas_pusat', 'petugas_cabang'])): ?>
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Catat Aktivitas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="activityForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jenis Aktivitas *</label>
                                <select class="form-select" name="activity_type" required>
                                    <?php foreach ($activityLabels as $key => $label): ?>
                                    <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Lokasi</label>
                                <input type="text" class="form-control" name="location">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal *</label>
                                <input type="date" class="form-control" name="activity_date" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Waktu *</label>
                                <input type="time" class="form-control" name="activity_time" value="<?php echo date('H:i:s'); ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="completed">Completed</option>
                                <option value="pending">Pending</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" onclick="saveActivity()">Simpan</button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function saveActivity() {
            const form = document.getElementById('activityForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            
            fetch('<?php echo baseUrl('api/field_officer_activities.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    Swal.fire('Sukses', result.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    Swal.fire('Error', result.error || 'Gagal menyimpan aktivitas', 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error', 'Terjadi kesalahan', 'error');
            });
        }
        
        function editActivity(id) {
            Swal.fire('Info', 'Fitur edit akan segera tersedia', 'info');
        }
        
        function deleteActivity(id) {
            Swal.fire({
                title: 'Hapus Aktivitas?',
                text: 'Anda yakin ingin menghapus aktivitas ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('<?php echo baseUrl('api/field_officer_activities.php'); ?>?id=' + id, {
                        method: 'DELETE'
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            Swal.fire('Sukses', result.message, 'success');
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            Swal.fire('Error', result.error || 'Gagal menghapus aktivitas', 'error');
                        }
                    })
                    .catch(error => {
                        Swal.fire('Error', 'Terjadi kesalahan', 'error');
                    });
                }
            });
        }
        
        // Filter functionality
        $('#filterPetugas, #filterActivityType, #filterTanggalMulai, #filterTanggalSelesai').on('change', function() {
            const petugas = $('#filterPetugas').val();
            const activityType = $('#filterActivityType').val();
            const tanggalMulai = $('#filterTanggalMulai').val();
            const tanggalSelesai = $('#filterTanggalSelesai').val();
            
            $('#activitiesTable tbody tr').each(function() {
                const row = $(this);
                const show = true;
                
                if (petugas && row.data('petugas') != petugas) {
                    row.hide();
                    return;
                }
                
                if (activityType && row.data('activity-type') !== activityType) {
                    row.hide();
                    return;
                }
                
                if (tanggalMulai && row.data('tanggal') < tanggalMulai) {
                    row.hide();
                    return;
                }
                
                if (tanggalSelesai && row.data('tanggal') > tanggalSelesai) {
                    row.hide();
                    return;
                }
                
                row.show();
            });
        });
    </script>
</body>
</html>
