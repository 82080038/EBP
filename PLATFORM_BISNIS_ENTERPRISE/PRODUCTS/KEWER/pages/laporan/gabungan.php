<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();

// Only pusat roles can access consolidated reports
$user = getCurrentUser();
$role = $user['role'];
$pusat_roles = ['bos', 'manager_pusat', 'admin_pusat', 'petugas_pusat'];

if (!in_array($role, $pusat_roles)) {
    header('Location: ' . baseUrl('dashboard.php'));
    exit();
}

$kantor_id = 1; // Single office
$cabang_id = $_GET['cabang_id'] ?? '';
$tanggal_mulai = $_GET['tanggal_mulai'] ?? date('Y-m-01');
$tanggal_selesai = $_GET['tanggal_selesai'] ?? date('Y-m-d');

// Get all branches
$cabang_list = query("SELECT * FROM cabang WHERE status = 'aktif' ORDER BY nama_cabang");
if (!is_array($cabang_list)) {
    $cabang_list = [];
}

// Build date filter for JOIN condition
$date_filter = "DATE(p.created_at) BETWEEN ? AND ?";
$params = [$tanggal_mulai, $tanggal_selesai];

// Get consolidated data
$consolidated_data = query("
    SELECT 
        c.id as cabang_id,
        c.nama_cabang,
        COUNT(DISTINCT p.nasabah_id) as total_nasabah_pinjaman,
        COUNT(p.id) as total_pinjaman,
        SUM(p.plafon) as total_plafon,
        SUM(p.total_pembayaran) as total_tagihan,
        SUM(CASE WHEN p.status = 'aktif' THEN 1 ELSE 0 END) as pinjaman_aktif,
        SUM(CASE WHEN p.status = 'lunas' THEN 1 ELSE 0 END) as pinjaman_lunas,
        SUM(CASE WHEN p.status IN ('pengajuan', 'disetujui') THEN 1 ELSE 0 END) as pinjaman_pending,
        SUM(CASE WHEN p.status = 'ditolak' THEN 1 ELSE 0 END) as pinjaman_ditolak
    FROM cabang c
    LEFT JOIN pinjaman p ON c.id = p.cabang_id AND $date_filter
    GROUP BY c.id, c.nama_cabang
    ORDER BY c.nama_cabang
", $params);

if (!is_array($consolidated_data)) {
    $consolidated_data = [];
}

// Calculate grand totals
$grand_total = [
    'total_nasabah_pinjaman' => array_sum(array_column($consolidated_data, 'total_nasabah_pinjaman')),
    'total_pinjaman' => array_sum(array_column($consolidated_data, 'total_pinjaman')),
    'total_plafon' => array_sum(array_column($consolidated_data, 'total_plafon')),
    'total_tagihan' => array_sum(array_column($consolidated_data, 'total_tagihan')),
    'pinjaman_aktif' => array_sum(array_column($consolidated_data, 'pinjaman_aktif')),
    'pinjaman_lunas' => array_sum(array_column($consolidated_data, 'pinjaman_lunas')),
    'pinjaman_pending' => array_sum(array_column($consolidated_data, 'pinjaman_pending')),
    'pinjaman_ditolak' => array_sum(array_column($consolidated_data, 'pinjaman_ditolak'))
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Gabungan - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
    <style>
        .summary-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .summary-value {
            font-size: 2rem;
            font-weight: 700;
        }
        .branch-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container-fluid">
            <a href="index.php" class="btn btn-outline-light btn-sm">
                <i class="bi bi-arrow-left"></i>
            </a>
            <span class="navbar-brand mb-0 h1">
                <i class="bi bi-file-earmark-bar-graph"></i> Laporan Gabungan
            </span>
            <a href="../../logout.php" class="btn btn-outline-light btn-sm">
                <i class="bi bi-box-arrow-right"></i>
            </a>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Cabang</label>
                        <select name="cabang_id" class="form-select">
                            <option value="">Semua Cabang</option>
                            <?php foreach ($cabang_list as $c): ?>
                                <option value="<?php echo $c['id']; ?>" <?php echo $cabang_id == $c['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($c['nama_cabang']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Tanggal Mulai</label>
                        <input type="text" name="tanggal_mulai" class="form-control flatpickr" value="<?php echo date('d/m/Y', strtotime($tanggal_mulai)); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Tanggal Selesai</label>
                        <input type="text" name="tanggal_selesai" class="form-control flatpickr" value="<?php echo date('d/m/Y', strtotime($tanggal_selesai)); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-filter"></i> Filter
                            </button>
                            <a href="gabungan.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-clockwise"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Grand Total Summary -->
        <div class="summary-card">
            <h4><i class="bi bi-graph-up"></i> Ringkasan Total (Semua Cabang)</h4>
            <div class="row mt-3">
                <div class="col-md-3">
                    <small>Total Pinjaman</small>
                    <div class="summary-value"><?php echo $grand_total['total_pinjaman']; ?></div>
                </div>
                <div class="col-md-3">
                    <small>Total Plafon</small>
                    <div class="summary-value">Rp<?php echo number_format($grand_total['total_plafon'], 0, ',', '.'); ?></div>
                </div>
                <div class="col-md-3">
                    <small>Total Tagihan</small>
                    <div class="summary-value">Rp<?php echo number_format($grand_total['total_tagihan'], 0, ',', '.'); ?></div>
                </div>
                <div class="col-md-3">
                    <small>Nasabah Berpinjaman</small>
                    <div class="summary-value"><?php echo $grand_total['total_nasabah_pinjaman']; ?></div>
                </div>
            </div>
        </div>

        <!-- Status Breakdown -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <i class="bi bi-check-circle" style="font-size: 2rem;"></i>
                        <h6 class="mt-2">Pinjaman Aktif</h6>
                        <h3><?php echo $grand_total['pinjaman_aktif']; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <i class="bi bi-check2-all" style="font-size: 2rem;"></i>
                        <h6 class="mt-2">Pinjaman Lunas</h6>
                        <h3><?php echo $grand_total['pinjaman_lunas']; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark">
                    <div class="card-body text-center">
                        <i class="bi bi-clock" style="font-size: 2rem;"></i>
                        <h6 class="mt-2">Pending</h6>
                        <h3><?php echo $grand_total['pinjaman_pending']; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body text-center">
                        <i class="bi bi-x-circle" style="font-size: 2rem;"></i>
                        <h6 class="mt-2">Ditolak</h6>
                        <h3><?php echo $grand_total['pinjaman_ditolak']; ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Per Branch Detail -->
        <h5 class="mb-3"><i class="bi bi-building"></i> Detail per Cabang</h5>
        <?php if (empty($consolidated_data)): ?>
            <div class="text-center text-muted py-5">
                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                <p class="mt-3">Tidak ada data untuk periode ini</p>
            </div>
        <?php else: ?>
            <?php foreach ($consolidated_data as $data): ?>
                <div class="branch-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0"><i class="bi bi-building"></i> <?php echo htmlspecialchars($data['nama_cabang']); ?></h5>
                        <span class="badge bg-primary"><?php echo $data['total_pinjaman']; ?> Pinjaman</span>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <table class="table table-sm">
                                <tr>
                                    <td>Total Plafon</td>
                                    <td class="fw-bold">Rp<?php echo number_format($data['total_plafon'] ?? 0, 0, ',', '.'); ?></td>
                                </tr>
                                <tr>
                                    <td>Total Tagihan</td>
                                    <td class="fw-bold">Rp<?php echo number_format($data['total_tagihan'] ?? 0, 0, ',', '.'); ?></td>
                                </tr>
                                <tr>
                                    <td>Nasabah</td>
                                    <td class="fw-bold"><?php echo $data['total_nasabah_pinjaman']; ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-4">
                            <table class="table table-sm">
                                <tr>
                                    <td>Aktif</td>
                                    <td class="fw-bold text-success"><?php echo $data['pinjaman_aktif']; ?></td>
                                </tr>
                                <tr>
                                    <td>Lunas</td>
                                    <td class="fw-bold text-primary"><?php echo $data['pinjaman_lunas']; ?></td>
                                </tr>
                                <tr>
                                    <td>Pending</td>
                                    <td class="fw-bold text-warning"><?php echo $data['pinjaman_pending']; ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-4">
                            <table class="table table-sm">
                                <tr>
                                    <td>Ditolak</td>
                                    <td class="fw-bold text-danger"><?php echo $data['pinjaman_ditolak']; ?></td>
                                </tr>
                                <tr>
                                    <td>Rate Lunas</td>
                                    <td class="fw-bold">
                                        <?php 
                                        $rate = $data['total_pinjaman'] > 0 ? ($data['pinjaman_lunas'] / $data['total_pinjaman']) * 100 : 0;
                                        echo number_format($rate, 1); ?>%
                                    </td>
                                </tr>
                                <tr>
                                    <td>Rate Aktif</td>
                                    <td class="fw-bold">
                                        <?php 
                                        $rate_aktif = $data['total_pinjaman'] > 0 ? ($data['pinjaman_aktif'] / $data['total_pinjaman']) * 100 : 0;
                                        echo number_format($rate_aktif, 1); ?>%
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- Export Buttons -->
        <div class="card mt-4">
            <div class="card-body text-center">
                <h5><i class="bi bi-download"></i> Ekspor Laporan</h5>
                <p class="text-muted">Unduh laporan dalam berbagai format</p>
                <div class="d-flex justify-content-center gap-2">
                    <button class="btn btn-success" onclick="exportCSV()">
                        <i class="bi bi-file-earmark-csv"></i> CSV
                    </button>
                    <button class="btn btn-danger" onclick="window.print()">
                        <i class="bi bi-printer"></i> PDF/Print
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/id.js"></script>
    <!-- Include Notifications JS for standalone pages -->
    <script src="<?php echo baseUrl('includes/js/notifications.js'); ?>"></script>
    <script>
        flatpickr('.flatpickr', {
            dateFormat: 'd/m/Y',
            locale: 'id',
            onChange: function(selectedDates, dateStr, instance) {
                instance.element.value = dateStr;
            }
        });

        function exportCSV() {
            const data = <?php echo json_encode($consolidated_data); ?>;
            let csv = 'Cabang,Total Pinjaman,Total Plafon,Total Tagihan,Nasabah,Aktif,Lunas,Pending,Ditolak\n';
            
            data.forEach(row => {
                csv += `"${row.nama_cabang}",${row.total_pinjaman},${row.total_plafon},${row.total_tagihan},${row.total_nasabah_pinjaman},${row.pinjaman_aktif},${row.pinjaman_lunas},${row.pinjaman_pending},${row.pinjaman_ditolak}\n`;
            });
            
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'laporan_gabungan_<?php echo date('Y-m-d'); ?>.csv';
            a.click();
            window.URL.revokeObjectURL(url);
        }
        
        // Initialize notifications for standalone page
        $(document).ready(function() {
            window.KewerNotifications.updateBadge();
        });
    </script>
</body>
</html>
