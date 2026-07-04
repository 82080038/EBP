<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
require_once BASE_PATH . '/includes/business_logic.php';
require_once BASE_PATH . '/includes/feature_flags.php';
requireLogin();

$user = getCurrentUser();
$kantor_id = 1; // Single office

// Only manager+ can view
if (in_array($user['role'], ['teller'])) {
    header('Location: ' . baseUrl('dashboard.php'));
    exit();
}

$bulan = $_GET['bulan'] ?? date('Y-m');
$bulan_start = $bulan . '-01';
$bulan_end = date('Y-m-t', strtotime($bulan_start));
$bulan_label = date('F Y', strtotime($bulan_start));

// Get petugas performance
$params = [$bulan_start, $bulan_end, $bulan_start, $bulan_end, $bulan_start, $bulan_end, $bulan_start, $bulan_end];

$kinerja = query("
    SELECT 
        u.id, u.nama, u.role,
        (SELECT COUNT(*) FROM pinjaman p2 WHERE p2.petugas_id = u.id AND p2.created_at BETWEEN ? AND ?) as total_pinjaman_baru,
        (SELECT COALESCE(SUM(a2.total_bayar), 0) FROM angsuran a2 JOIN pinjaman p3 ON a2.pinjaman_id = p3.id WHERE p3.petugas_id = u.id AND a2.tanggal_bayar BETWEEN ? AND ? AND a2.status = 'lunas') as total_kutipan,
        (SELECT COUNT(*) FROM angsuran a3 JOIN pinjaman p4 ON a3.pinjaman_id = p4.id WHERE p4.petugas_id = u.id AND a3.tanggal_bayar BETWEEN ? AND ? AND a3.status = 'lunas') as total_angsuran_terbayar,
        (SELECT COUNT(*) FROM angsuran a4 JOIN pinjaman p5 ON a4.pinjaman_id = p5.id WHERE p5.petugas_id = u.id AND a4.status = 'telat' AND a4.jatuh_tempo BETWEEN ? AND ?) as total_telat
    FROM users u
    WHERE u.role IN ('petugas_cabang','petugas_pusat') AND u.status = 'aktif'
    ORDER BY total_kutipan DESC
", $params);
if (!is_array($kinerja)) $kinerja = [];

// Calculate rankings & totals
$grand_kutipan = 0;
$grand_terbayar = 0;
$grand_pinjaman = 0;
$grand_telat = 0;
foreach ($kinerja as $k) {
    $grand_kutipan += $k['total_kutipan'];
    $grand_terbayar += $k['total_angsuran_terbayar'];
    $grand_pinjaman += $k['total_pinjaman_baru'];
    $grand_telat += $k['total_telat'];
}

// Collection rate per petugas
foreach ($kinerja as &$k) {
    $target = query("SELECT COUNT(*) as c FROM angsuran a JOIN pinjaman p ON a.pinjaman_id = p.id WHERE p.petugas_id = ? AND a.jatuh_tempo BETWEEN ? AND ?", [$k['id'], $bulan_start, $bulan_end]);
    $k['target_angsuran'] = (is_array($target) && isset($target[0])) ? (int)$target[0]['c'] : 0;
    $k['collection_rate'] = $k['target_angsuran'] > 0 ? round(($k['total_angsuran_terbayar'] / $k['target_angsuran']) * 100, 1) : 0;
}
unset($k);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kinerja Petugas - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .sidebar { min-height: calc(100vh - 56px); }
        .perf-bar { height: 8px; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="main-container">
        <?php require_once BASE_PATH . '/includes/sidebar.php'; ?>
        
        <main class="content-area">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="bi bi-bar-chart"></i> Dashboard Kinerja Petugas</h1>
                    <div class="btn-group">
                        <a href="../../api/export.php?format=csv&jenis=comprehensive&tanggal_mulai=<?= $bulan_start ?>&tanggal_selesai=<?= $bulan_end ?>" class="btn btn-sm btn-outline-success">
                            <i class="bi bi-filetype-csv"></i> Export CSV
                        </a>
                        <?php if (in_array($user['role'], ['bos','manager_pusat','manager_cabang','appOwner']) && isFeatureEnabled('target_petugas')): ?>
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalTarget">
                            <i class="bi bi-bullseye"></i> Set Target
                        </button>
                        <?php endif; ?>
                        <a href="slip_harian.php" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-receipt"></i> Slip Harian
                        </a>
                    </div>
                </div>
                
                <!-- Month Filter -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-2 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label form-label-sm">Bulan</label>
                                <input type="month" name="bulan" class="form-control form-control-sm" value="<?php echo $bulan; ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-search"></i> Filter</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Summary -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body py-2">
                                <h6>Total Petugas</h6>
                                <h4><?php echo count($kinerja); ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body py-2">
                                <h6>Total Kutipan</h6>
                                <h4><?php echo formatRupiah($grand_kutipan); ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body py-2">
                                <h6>Angsuran Terbayar</h6>
                                <h4><?php echo number_format($grand_terbayar); ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body py-2">
                                <h6>Angsuran Telat</h6>
                                <h4><?php echo number_format($grand_telat); ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Performance Table -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-trophy"></i> Peringkat Kinerja — <?php echo $bulan_label; ?></h5>
                        <small class="text-muted">Progress bar = % kutipan vs target</small>
                    </div>
                    <div class="card-body">
                        <?php if (empty($kinerja)): ?>
                            <div class="alert alert-info"><i class="bi bi-info-circle"></i> Tidak ada data petugas</div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Petugas</th>
                                        <th class="text-end">Pinjaman Baru</th>
                                        <th class="text-end">Collection Rate</th>
                                        <th>Kutipan vs Target</th>
                                        <th class="text-end">Total Kutipan</th>
                                        <th class="text-end">Telat</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $rank = 1; foreach ($kinerja as $k):
                                        $target_data = getRealisasiVsTarget((int)$k['id'], $bulan);
                                        $pct_kutipan = $target_data['pct_kutipan'];
                                        $target_kutipan = $target_data['target_kutipan'];
                                        $rate = $k['collection_rate'];
                                        $bar_color = $rate >= 90 ? 'success' : ($rate >= 70 ? 'warning' : 'danger');
                                        $bar_kutipan_color = $pct_kutipan >= 100 ? 'success' : ($pct_kutipan >= 70 ? 'warning' : 'danger');
                                    ?>
                                    <tr>
                                        <td>
                                            <?php if ($rank === 1): ?><span class="badge bg-warning text-dark"><i class="bi bi-trophy-fill"></i> 1</span>
                                            <?php elseif ($rank === 2): ?><span class="badge bg-secondary">2</span>
                                            <?php elseif ($rank === 3): ?><span class="badge bg-danger">3</span>
                                            <?php else: ?><?php echo $rank; ?><?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($k['nama']); ?></strong>
                                            <br><small class="text-muted"><?php echo $k['role']; ?></small>
                                        </td>
                                        <td class="text-end"><?php echo $k['total_pinjaman_baru']; ?></td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="flex-grow-1"><div class="progress perf-bar"><div class="progress-bar bg-<?= $bar_color ?>" style="width:<?= min($rate,100) ?>%"></div></div></div>
                                                <small class="fw-bold text-<?= $bar_color ?>"><?= $rate ?>%</small>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($target_kutipan > 0): ?>
                                            <div class="d-flex align-items-center gap-1">
                                                <div class="flex-grow-1"><div class="progress perf-bar"><div class="progress-bar bg-<?= $bar_kutipan_color ?>" style="width:<?= min($pct_kutipan,100) ?>%"></div></div></div>
                                                <small class="text-<?= $bar_kutipan_color ?>"><?= $pct_kutipan ?>%</small>
                                            </div>
                                            <small class="text-muted d-block"><?= formatRupiah($k['total_kutipan']) ?> / <?= formatRupiah($target_kutipan) ?></small>
                                            <?php else: ?>
                                            <small class="text-muted"><?= formatRupiah($k['total_kutipan']) ?> <span class="badge bg-light text-secondary">no target</span></small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end"><strong><?php echo formatRupiah($k['total_kutipan']); ?></strong></td>
                                        <td class="text-end">
                                            <?php if ($k['total_telat'] > 0): ?><span class="badge bg-danger"><?= $k['total_telat'] ?></span>
                                            <?php else: ?><span class="badge bg-success">0</span><?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="../petugas/slip_harian.php?petugas_id=<?= $k['id'] ?>&tanggal=<?= date('Y-m-d') ?>" class="btn btn-xs btn-outline-secondary" title="Slip Harian"><i class="bi bi-receipt"></i></a>
                                        </td>
                                    </tr>
                                    <?php $rank++; endforeach; ?>
                                </tbody>
                                <tfoot class="table-secondary">
                                    <tr>
                                        <td colspan="3"><strong>Total</strong></td>
                                        <td>
                                            <?php
                                            $total_target_angsuran = array_sum(array_column($kinerja, 'target_angsuran'));
                                            $overall_rate = $total_target_angsuran > 0 ? round(($grand_terbayar / $total_target_angsuran) * 100, 1) : 0;
                                            ?>
                                            <strong><?= $overall_rate ?>%</strong>
                                        </td>
                                        <td><strong><?= formatRupiah($grand_kutipan) ?></strong></td>
                                        <td class="text-end"><strong><?= formatRupiah($grand_kutipan) ?></strong></td>
                                        <td class="text-end"><strong><?= $grand_telat ?></strong></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Modal Set Target -->
    <?php if (in_array($user['role'], ['bos','manager_pusat','manager_cabang','appOwner']) && isFeatureEnabled('target_petugas')): ?>
    <div class="modal fade" id="modalTarget" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-bullseye"></i> Set Target Petugas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Petugas</label>
                        <select class="form-select" id="tgt_petugas_id">
                            <?php foreach ($kinerja as $k): ?>
                            <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bulan</label>
                        <input type="month" class="form-control" id="tgt_bulan" value="<?= $bulan ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Target Kutipan (Rp)</label>
                        <input type="number" class="form-control" id="tgt_kutipan" placeholder="0" min="0">
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label">Target Nasabah Baru</label>
                            <input type="number" class="form-control" id="tgt_nasabah" placeholder="0" min="0">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Target Pinjaman Baru</label>
                            <input type="number" class="form-control" id="tgt_pinjaman" placeholder="0" min="0">
                        </div>
                    </div>
                    <div class="mb-3 mt-2">
                        <label class="form-label">Target Collection Rate (%)</label>
                        <input type="number" class="form-control" id="tgt_collection" placeholder="90" min="0" max="100" step="0.5" value="90">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" onclick="saveTarget()">Simpan Target</button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script>
    async function saveTarget() {
        const payload = {
            petugas_id:            parseInt(document.getElementById('tgt_petugas_id').value),
            bulan:                 document.getElementById('tgt_bulan').value,
            target_kutipan:        parseFloat(document.getElementById('tgt_kutipan').value) || 0,
            target_nasabah_baru:   parseInt(document.getElementById('tgt_nasabah').value) || 0,
            target_pinjaman_baru:  parseInt(document.getElementById('tgt_pinjaman').value) || 0,
            target_collection_rate: parseFloat(document.getElementById('tgt_collection').value) || 90,
        };
        const resp = await fetch('../../api/target_petugas.php', {
            method: 'POST', headers: {'Content-Type':'application/json'},
            body: JSON.stringify(payload)
        });
        const r = await resp.json();
        if (r.success) {
            Swal.fire('Berhasil', 'Target berhasil disimpan', 'success').then(() => location.reload());
        } else {
            Swal.fire('Gagal', r.error || 'Terjadi kesalahan', 'error');
        }
    }
    </script>
</body>
</html>
