<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();

$user = getCurrentUser();
$user_cabang_id = $user['cabang_id'] ?? null;
$kantor_id = $user_cabang_id ?? 1; // Use user's cabang_id or default to 1
$cabang_id = getCurrentCabang() ?: $kantor_id;
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$frekuensi_filter = $_GET['frekuensi'] ?? '';

// Build query
$where = ["1=1"];
$params = [];

if ($search) {
    $where[] = "(p.kode_pinjaman LIKE ? OR n.nama LIKE ? OR n.ktp LIKE ? OR n.telp LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($status) {
    $where[] = "p.status = ?";
    $params[] = $status;
}

if ($frekuensi_filter) {
    // Use frekuensi_id only (enum column dropped in migration 024)
    if (is_numeric($frekuensi_filter)) {
        $where[] = "p.frekuensi_id = ?";
        $params[] = $frekuensi_filter;
    }
}

$where_clause = "WHERE " . implode(" AND ", $where);

// Get pinjaman data
$pinjaman = query("
    SELECT p.*, n.nama, n.telp, n.kode_nasabah, n.alamat
    FROM pinjaman p
    JOIN nasabah n ON p.nasabah_id = n.id
    $where_clause
    ORDER BY p.created_at DESC
", $params);

if (!is_array($pinjaman)) {
    $pinjaman = [];
}

// Get statistics by frekuensi - use frekuensi_id only
$stats_result = query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'pengajuan' THEN 1 ELSE 0 END) as pengajuan,
        SUM(CASE WHEN status = 'disetujui' THEN 1 ELSE 0 END) as disetujui,
        SUM(CASE WHEN status = 'aktif' THEN 1 ELSE 0 END) as aktif,
        SUM(CASE WHEN status = 'lunas' THEN 1 ELSE 0 END) as lunas,
        SUM(CASE WHEN frekuensi_id = 1 THEN 1 ELSE 0 END) as count_harian,
        SUM(CASE WHEN frekuensi_id = 2 THEN 1 ELSE 0 END) as count_mingguan,
        SUM(CASE WHEN frekuensi_id = 3 THEN 1 ELSE 0 END) as count_bulanan,
        SUM(plafon) as total_plafon
    FROM pinjaman
");

$stats = is_array($stats_result) && isset($stats_result[0]) ? $stats_result[0] : [
    'total' => 0, 'pengajuan' => 0, 'disetujui' => 0, 'aktif' => 0, 'lunas' => 0,
    'count_harian' => 0, 'count_mingguan' => 0, 'count_bulanan' => 0, 'total_plafon' => 0
];

// Get nasabah for dropdown
$nasabah_list = query("
    SELECT id, nama, kode_nasabah, telp 
    FROM nasabah 
    WHERE cabang_id = ? AND status = 'aktif'
    ORDER BY nama ASC
", [$cabang_id]);

if (!is_array($nasabah_list)) {
    $nasabah_list = [];
}

// Format currency helper

// Format frekuensi badge
function getFrekuensiBadge($frek_id) {
    // Use frekuensi_id only (enum column dropped in migration 024)
    $frek = $frek_id ?? 3; // Default to bulanan (id 3)
    $label = getFrequencyLabel($frek);
    $code = getFrequencyCode($frek);
    
    $icon = 'calendar-month';
    $class = 'primary';
    if ($code == 'HARIAN' || $code == 'harian') {
        $icon = 'sun';
        $class = 'warning text-dark';
    } elseif ($code == 'MINGGUAN' || $code == 'mingguan') {
        $icon = 'calendar-week';
        $class = 'info';
    }
    
    return "<span class=\"badge bg-{$class}\"><i class=\"bi bi-{$icon}\"></i> {$label}</span>";
}

// Format status badge
function getStatusBadge($status) {
    $badges = [
        'pengajuan' => '<span class="badge bg-secondary">Pengajuan</span>',
        'disetujui' => '<span class="badge bg-success">Disetujui</span>',
        'aktif' => '<span class="badge bg-primary">Aktif</span>',
        'lunas' => '<span class="badge bg-dark">Lunas</span>',
        'ditolak' => '<span class="badge bg-danger">Ditolak</span>'
    ];
    return $badges[$status] ?? '<span class="badge bg-secondary">' . ucfirst($status) . '</span>';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pinjaman - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
    <style>
        .stat-card { transition: transform 0.2s; }
        .stat-card:hover { transform: translateY(-3px); }
        .frekuensi-filter .btn-check:checked + .btn { box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25); }
    </style>
</head>
<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand" href="../../dashboard.php"><i class="bi bi-bank"></i> <?= APP_NAME ?></a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../../dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
                <a class="nav-link" href="../../logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <!-- Header & Actions -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0"><i class="bi bi-cash-stack"></i> Data Pinjaman</h4>
                <small class="text-muted">Kelola pinjaman harian, mingguan, dan bulanan</small>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahPinjaman">
                <i class="bi bi-plus-lg"></i> Tambah Pinjaman
            </button>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-2 col-sm-6">
                <div class="card stat-card border-0 shadow-sm bg-white">
                    <div class="card-body text-center py-3">
                        <h3 class="mb-1 text-primary"><?= $stats['total'] ?? 0 ?></h3>
                        <small class="text-muted">Total Pinjaman</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-sm-6">
                <div class="card stat-card border-0 shadow-sm bg-warning bg-opacity-10">
                    <div class="card-body text-center py-3">
                        <h3 class="mb-1 text-warning"><?= $stats['pengajuan'] ?? 0 ?></h3>
                        <small class="text-muted">Pengajuan</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-sm-6">
                <div class="card stat-card border-0 shadow-sm bg-success bg-opacity-10">
                    <div class="card-body text-center py-3">
                        <h3 class="mb-1 text-success"><?= ($stats['aktif'] ?? 0) + ($stats['disetujui'] ?? 0) ?></h3>
                        <small class="text-muted">Aktif</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-sm-6">
                <div class="card stat-card border-0 shadow-sm bg-dark bg-opacity-10">
                    <div class="card-body text-center py-3">
                        <h3 class="mb-1 text-dark"><?= $stats['lunas'] ?? 0 ?></h3>
                        <small class="text-muted">Lunas</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-12">
                <div class="card stat-card border-0 shadow-sm bg-info bg-opacity-10">
                    <div class="card-body text-center py-3">
                        <h3 class="mb-1 text-info"><?= formatRupiah($stats['total_plafon']) ?></h3>
                        <small class="text-muted">Total Plafon</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Frekuensi Filter -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-3">
                <div class="d-flex flex-wrap gap-2 align-items-center frekuensi-filter">
                    <span class="me-2 text-muted"><i class="bi bi-funnel"></i> Filter:</span>
                    <a href="?" class="btn btn-sm <?= !$frekuensi_filter ? 'btn-dark' : 'btn-outline-secondary' ?>">Semua</a>
                    <?php
                    $active_frequencies = getActiveFrequencies();
                    if ($active_frequencies && is_array($active_frequencies)):
                        foreach ($active_frequencies as $freq):
                            $freq_code = $freq['kode'];
                            $freq_id = $freq['id'];
                            $count_key = '';
                            if ($freq_code == 'HARIAN') $count_key = 'count_harian';
                            elseif ($freq_code == 'MINGGUAN') $count_key = 'count_mingguan';
                            else $count_key = 'count_bulanan';
                            
                            $is_active = ($frekuensi_filter == $freq_id || $frekuensi_filter == strtolower($freq_code));
                            $btn_class = '';
                            if ($freq_code == 'HARIAN') $btn_class = $is_active ? 'btn-warning' : 'btn-outline-warning';
                            elseif ($freq_code == 'MINGGUAN') $btn_class = $is_active ? 'btn-info' : 'btn-outline-info';
                            else $btn_class = $is_active ? 'btn-primary' : 'btn-outline-primary';
                    ?>
                    <a href="?frekuensi=<?= $freq_id ?>" class="btn btn-sm <?= $btn_class ?>">
                        <i class="bi bi-<?= $freq_code == 'HARIAN' ? 'sun' : ($freq_code == 'MINGGUAN' ? 'calendar-week' : 'calendar-month') ?>"></i> 
                        <?= $freq['nama'] ?> (<?= $stats[$count_key] ?? 0 ?>)
                    </a>
                    <?php endforeach; else: ?>
                    <a href="?frekuensi=harian" class="btn btn-sm <?= $frekuensi_filter == 'harian' ? 'btn-warning' : 'btn-outline-warning' ?>">
                        <i class="bi bi-sun"></i> Harian (<?= $stats['count_harian'] ?>)
                    </a>
                    <a href="?frekuensi=mingguan" class="btn btn-sm <?= $frekuensi_filter == 'mingguan' ? 'btn-info' : 'btn-outline-info' ?>">
                        <i class="bi bi-calendar-week"></i> Mingguan (<?= $stats['count_mingguan'] ?>)
                    </a>
                    <a href="?frekuensi=bulanan" class="btn btn-sm <?= $frekuensi_filter == 'bulanan' ? 'btn-primary' : 'btn-outline-primary' ?>">
                        <i class="bi bi-calendar-month"></i> Bulanan (<?= $stats['count_bulanan'] ?>)
                    </a>
                    <?php endif; ?>
                    
                    <div class="ms-auto d-flex gap-2">
                        <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Cari nasabah..." value="<?= htmlspecialchars($search) ?>">
                        <select id="statusFilter" class="form-select form-select-sm" style="width: 120px;">
                            <option value="">Semua Status</option>
                            <option value="pengajuan" <?= $status == 'pengajuan' ? 'selected' : '' ?>>Pengajuan</option>
                            <option value="aktif" <?= $status == 'aktif' ? 'selected' : '' ?>>Aktif</option>
                            <option value="lunas" <?= $status == 'lunas' ? 'selected' : '' ?>>Lunas</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="tablePinjaman" class="table table-hover table-striped mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Kode</th>
                                <th>Nasabah</th>
                                <th>Frekuensi</th>
                                <th class="text-end">Plafon</th>
                                <th class="text-center">Tenor</th>
                                <th class="text-end">Angsuran</th>
                                <th>Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pinjaman as $p): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($p['kode_pinjaman'] ?? '-') ?></strong>
                                    <br><small class="text-muted"><?= date('d/m/Y', strtotime($p['tanggal_akad'] ?? 'now')) ?></small>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($p['nama'] ?? '-') ?></strong>
                                    <br><small class="text-muted"><?= htmlspecialchars($p['kode_nasabah'] ?? '-') ?></small>
                                </td>
                                <td><?= getFrekuensiBadge($p['frekuensi_id']) ?></td>
                                <td class="text-end fw-bold"><?= formatRupiah($p['plafon']) ?></td>
                                <td class="text-center"><?= $p['tenor'] ?>x</td>
                                <td class="text-end"><?= formatRupiah($p['angsuran_total']) ?></td>
                                <td><?= getStatusBadge($p['status']) ?></td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="detail.php?id=<?= $p['id'] ?>" class="btn btn-outline-primary" title="Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <?php if ($p['status'] == 'pengajuan'): ?>
                                        <button class="btn btn-outline-success btn-approve" data-id="<?= $p['id'] ?>" title="Setujui">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                        <button class="btn btn-outline-danger btn-reject" data-id="<?= $p['id'] ?>" title="Tolak">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Pinjaman -->
    <div class="modal fade" id="modalTambahPinjaman" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Tambah Pinjaman Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formTambahPinjaman">
                    <div class="modal-body">
                        <div class="row g-3">
                            <!-- Nasabah -->
                            <div class="col-md-12">
                                <label class="form-label">Nasabah <span class="text-danger">*</span></label>
                                <select name="nasabah_id" class="form-select select2-nasabah" required>
                                    <option value="">Pilih Nasabah</option>
                                    <?php foreach ($nasabah_list as $n): ?>
                                    <option value="<?= $n['id'] ?>">
                                        <?= htmlspecialchars($n['nama']) ?> - <?= htmlspecialchars($n['kode_nasabah']) ?> (<?= htmlspecialchars($n['telp']) ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Frekuensi -->
                            <div class="col-md-6">
                                <label class="form-label">Frekuensi Angsuran <span class="text-danger">*</span></label>
                                <div class="d-flex gap-2">
                                    <?php
                                    $active_frequencies = getActiveFrequencies();
                                    if ($active_frequencies && is_array($active_frequencies)):
                                        foreach ($active_frequencies as $freq):
                                            $freq_code = $freq['kode'];
                                            $freq_id = $freq['id'];
                                            $checked = ($freq_code == 'BULANAN') ? 'checked' : '';
                                            $btn_class = '';
                                            if ($freq_code == 'HARIAN') $btn_class = 'btn-outline-warning';
                                            elseif ($freq_code == 'MINGGUAN') $btn_class = 'btn-outline-info';
                                            else $btn_class = 'btn-outline-primary';
                                    ?>
                                    <input type="radio" class="btn-check" name="frekuensi" id="freq_<?= strtolower($freq_code) ?>" value="<?= $freq_code ?>" data-id="<?= $freq_id ?>" data-max="<?= $freq['tenor_max'] ?>" data-period="<?= $freq['hari_per_periode'] ?>" autocomplete="off" <?= $checked ?>>
                                    <label class="btn <?= $btn_class ?> flex-fill" for="freq_<?= strtolower($freq_code) ?>">
                                        <i class="bi bi-<?= $freq_code == 'HARIAN' ? 'sun' : ($freq_code == 'MINGGUAN' ? 'calendar-week' : 'calendar-month') ?>"></i> <?= $freq['nama'] ?>
                                    </label>
                                    <?php endforeach; else: ?>
                                    <input type="radio" class="btn-check" name="frekuensi" id="freq_harian" value="harian" autocomplete="off">
                                    <label class="btn btn-outline-warning flex-fill" for="freq_harian">
                                        <i class="bi bi-sun"></i> Harian
                                    </label>
                                    
                                    <input type="radio" class="btn-check" name="frekuensi" id="freq_mingguan" value="mingguan" autocomplete="off">
                                    <label class="btn btn-outline-info flex-fill" for="freq_mingguan">
                                        <i class="bi bi-calendar-week"></i> Mingguan
                                    </label>
                                    
                                    <input type="radio" class="btn-check" name="frekuensi" id="freq_bulanan" value="bulanan" autocomplete="off" checked>
                                    <label class="btn btn-outline-primary flex-fill" for="freq_bulanan">
                                        <i class="bi bi-calendar-month"></i> Bulanan
                                    </label>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Plafon -->
                            <div class="col-md-6">
                                <label class="form-label">Plafon (Rp) <span class="text-danger">*</span></label>
                                <input type="number" name="plafon" class="form-control" min="100000" step="50000" required>
                            </div>

                            <!-- Tenor -->
                            <div class="col-md-6">
                                <label class="form-label">Tenor <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" name="tenor" class="form-control" id="inputTenor" min="1" max="12" required>
                                    <span class="input-group-text" id="labelTenor">bulan</span>
                                </div>
                                <small class="text-muted" id="helperTenor">Maksimal 12 bulan</small>
                            </div>

                            <!-- Bunga -->
                            <div class="col-md-6">
                                <label class="form-label">Bunga per Periode (%) <span class="text-danger">*</span></label>
                                <input type="number" name="bunga_per_bulan" class="form-control" step="0.01" min="0" max="100" required>
                                <small class="text-muted">Bunga per periode sesuai frekuensi</small>
                            </div>

                            <!-- Tanggal Akad -->
                            <div class="col-md-6">
                                <label class="form-label">Tanggal Akad <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_akad" class="form-control flatpickr" value="<?= date('Y-m-d') ?>" required>
                            </div>

                            <!-- Jaminan -->
                            <div class="col-md-6">
                                <label class="form-label">Jaminan</label>
                                <select name="jaminan" class="form-select">
                                    <option value="tanpa">Tanpa Jaminan</option>
                                    <option value="bpkb">BPKB</option>
                                    <option value="shm">SHM</option>
                                    <option value="ajb">AJB</option>
                                    <option value="tabungan">Tabungan</option>
                                </select>
                            </div>

                            <!-- Tujuan -->
                            <div class="col-md-12">
                                <label class="form-label">Tujuan Pinjaman</label>
                                <textarea name="tujuan_pinjaman" class="form-control" rows="2"></textarea>
                            </div>
                        </div>

                        <!-- Preview Perhitungan -->
                        <div class="alert alert-info mt-3" id="previewPerhitungan" style="display: none;">
                            <h6><i class="bi bi-calculator"></i> Preview Perhitungan</h6>
                            <div class="row small">
                                <div class="col-6">Total Bunga: <span id="previewTotalBunga" class="fw-bold">-</span></div>
                                <div class="col-6">Total Pembayaran: <span id="previewTotalBayar" class="fw-bold">-</span></div>
                                <div class="col-6">Angsuran Pokok: <span id="previewPokok">-</span></div>
                                <div class="col-6">Angsuran Bunga: <span id="previewBunga">-</span></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Simpan Pinjaman</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
    <script src="https://npmcdn.com/flatpickr@4.6.13/dist/l10n/id.js"></script>

    <script>
    $(document).ready(function() {
        // Initialize DataTable with null handling
        const table = $('#tablePinjaman').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
            },
            pageLength: 25,
            order: [[0, 'desc']],
            columnDefs: [
                { orderable: false, targets: [7] },
                { 
                    targets: [3, 5], 
                    render: function(data, type, row) {
                        if (type === 'sort' || type === 'type') {
                            // Extract number from format like "Rp 1.000.000"
                            return data ? parseInt(data.replace(/[^0-9]/g, '')) || 0 : 0;
                        }
                        return data || '-';
                    }
                },
                {
                    targets: '_all',
                    render: function(data, type, row) {
                        // Handle null/undefined values
                        if (data === null || data === undefined || data === '') {
                            return '<span class="text-muted">-</span>';
                        }
                        return data;
                    }
                }
            ]
        });

        // Initialize Select2
        $('.select2-nasabah').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#modalTambahPinjaman'),
            placeholder: 'Cari nasabah...',
            allowClear: true
        });

        // Initialize Flatpickr
        flatpickr('.flatpickr', {
            locale: 'id',
            dateFormat: 'Y-m-d',
            defaultDate: new Date()
        });

        // Frekuensi change handler - use data attributes from ref_frekuensi_angsuran
        $('input[name="frekuensi"]').change(function() {
            const selectedRadio = $(this);
            const maxTenor = parseInt(selectedRadio.attr('data-max')) || 24;
            const period = parseInt(selectedRadio.attr('data-period')) || 30;
            
            let label = 'bulan';
            let helper = 'Maksimal 24 bulan (2 tahun)';
            if (period === 1) {
                label = 'hari';
                helper = 'Maksimal 100 hari (±3 bulan)';
            } else if (period === 7) {
                label = 'minggu';
                helper = 'Maksimal 52 minggu (1 tahun)';
            }
            
            $('#inputTenor').attr('max', maxTenor).val('');
            $('#labelTenor').text(label);
            $('#tenorHelp').text(helper);
        });

        // Form submission
        $('#formTambahPinjaman').submit(function(e) {
            e.preventDefault();
            
            const data = {
                nasabah_id: $('select[name="nasabah_id"]').val(),
                frekuensi: $('input[name="frekuensi"]:checked').val(),
                plafon: $('input[name="plafon"]').val(),
                tenor: $('input[name="tenor"]').val(),
                bunga_per_bulan: $('input[name="bunga_per_bulan"]').val(),
                tanggal_akad: $('input[name="tanggal_akad"]').val(),
                jaminan: $('select[name="jaminan"]').val(),
                tujuan_pinjaman: $('textarea[name="tujuan_pinjaman"]').val()
            };

            // Validate
            if (!data.nasabah_id || !data.plafon || !data.tenor || !data.bunga_per_bulan) {
                Swal.fire('Error', 'Semua field wajib diisi', 'error');
                return;
            }

            // Submit via AJAX
            $.ajax({
                url: '../../api/pinjaman.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(data),
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', response.error || 'Gagal menyimpan', 'error');
                    }
                },
                error: function(xhr) {
                    const resp = xhr.responseJSON || {};
                    Swal.fire('Error', resp.error || 'Terjadi kesalahan', 'error');
                }
            });
        });

        // Approve/Reject handlers
        $(document).on('click', '.btn-approve', function() {
            const id = $(this).data('id');
            Swal.fire({
                title: 'Setujui Pinjaman?',
                text: 'Pinjaman akan diaktifkan dan jadwal angsuran akan dibuat',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Setujui',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `../../api/pinjaman.php?id=${id}&action=approve`,
                        method: 'PUT',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Berhasil!', response.message, 'success').then(() => location.reload());
                            } else {
                                Swal.fire('Error', response.error, 'error');
                            }
                        }
                    });
                }
            });
        });

        $(document).on('click', '.btn-reject', function() {
            const id = $(this).data('id');
            Swal.fire({
                title: 'Tolak Pinjaman?',
                text: 'Pinjaman akan ditolak dan tidak dapat diaktifkan',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Ya, Tolak',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `../../api/pinjaman.php?id=${id}&action=reject`,
                        method: 'PUT',
                        success: function(response) {
                            Swal.fire('Ditolak!', response.message, 'success').then(() => location.reload());
                        }
                    });
                }
            });
        });

        // Search input handler
        $('#searchInput').on('keyup', function() {
            table.search(this.value).draw();
        });

        // Initialize Flatpickr for date inputs
        flatpickr('.flatpickr', {
            locale: 'id',
            dateFormat: 'Y-m-d',
            allowInput: true,
            altInput: true,
            altFormat: 'd F Y',
            theme: 'light'
        });

        // Status filter
        $('#statusFilter').change(function() {
            const val = $(this).val();
            if (val) {
                window.location.href = '?status=' + val + '<?= $frekuensi_filter ? "&frekuensi=$frekuensi_filter" : "" ?>';
            } else {
                window.location.href = '?<?= $frekuensi_filter ? "frekuensi=$frekuensi_filter" : "" ?>';
            }
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
