<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();

$user = getCurrentUser();
$user_cabang_id = $user['cabang_id'] ?? null;
$kantor_id = $user_cabang_id ?? 1; // Use user's cabang_id or default to 1

$petugas_id = $_GET['petugas_id'] ?? null;
$tanggal = $_GET['tanggal'] ?? date('Y-m-d');
$petugas_filter = $_GET['petugas_id'] ?? '';

// Get petugas list
$is_petugas = in_array($user['role'], ['petugas_cabang', 'petugas_pusat']);
if ($is_petugas) {
    $petugas_filter = $user['id'];
}

$petugas_list = query("SELECT id, nama FROM users WHERE role IN ('petugas_cabang','petugas_pusat') AND status = 'aktif' ORDER BY nama");
if (!is_array($petugas_list)) $petugas_list = [];

// Build route: get installments due on selected date for the petugas
$where = "a.jatuh_tempo <= ? AND a.status IN ('belum','telat')";
$params = [$tanggal];

if ($petugas_filter) {
    $where .= " AND p.petugas_id = ?";
    $params[] = $petugas_filter;
}

$rute = query("
    SELECT a.id as angsuran_id, a.no_angsuran, a.jatuh_tempo, a.total_angsuran, a.denda, a.status,
           p.id as pinjaman_id, p.kode_pinjaman, p.frekuensi_id, p.petugas_id,
           n.id as nasabah_id, n.nama as nama_nasabah, n.alamat, n.telp, n.kode_nasabah,
           u.nama as nama_petugas,
           DATEDIFF(?, a.jatuh_tempo) as hari_telat
    FROM angsuran a
    JOIN pinjaman p ON a.pinjaman_id = p.id
    JOIN nasabah n ON p.nasabah_id = n.id
    LEFT JOIN users u ON p.petugas_id = u.id
    WHERE $where
    ORDER BY a.status DESC, a.jatuh_tempo ASC, n.nama ASC
", array_merge([$tanggal], $params));
if (!is_array($rute)) $rute = [];

// Stats
$total_target = count($rute);
$total_nominal = array_sum(array_map(function($r) { return $r['total_angsuran'] + $r['denda']; }, $rute));
$total_telat = count(array_filter($rute, function($r) { return $r['status'] === 'telat'; }));
$total_belum = count(array_filter($rute, function($r) { return $r['status'] === 'belum'; }));

// Group by petugas
$grouped = [];
foreach ($rute as $r) {
    $pid = $r['petugas_id'] ?? 0;
    $pname = $r['nama_petugas'] ?? 'Tanpa Petugas';
    if (!isset($grouped[$pid])) $grouped[$pid] = ['nama' => $pname, 'items' => []];
    $grouped[$pid]['items'][] = $r;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rute Harian - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .sidebar { min-height: calc(100vh - 56px); }
        .route-card { border-left: 4px solid #0d6efd; transition: all 0.2s; }
        .route-card:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.15); }
        .route-card.telat { border-left-color: #dc3545; }
        .route-number { width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px; }
        @media print {
            .no-print { display: none !important; }
            .sidebar { display: none !important; }
            .col-md-9, .col-lg-10 { width: 100% !important; max-width: 100% !important; }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <?php require_once BASE_PATH . '/includes/sidebar.php'; ?>

        <main class="content-area">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="bi bi-map"></i> Rute Harian Petugas</h1>
                    <button onclick="window.print()" class="btn btn-outline-primary no-print"><i class="bi bi-printer"></i> Cetak</button>
                </div>
                
                <!-- Filters -->
                <div class="card mb-4 no-print">
                    <div class="card-body">
                        <form method="GET" class="row g-2 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label form-label-sm">Tanggal</label>
                                <input type="date" name="tanggal" class="form-control form-control-sm flatpickr-date" value="<?php echo $tanggal; ?>">
                            </div>
                            <?php if (!$is_petugas): ?>
                            <div class="col-md-3">
                                <label class="form-label form-label-sm">Petugas</label>
                                <select name="petugas_id" class="form-select form-select-sm">
                                    <option value="">Semua Petugas</option>
                                    <?php foreach ($petugas_list as $p): ?>
                                        <option value="<?php echo $p['id']; ?>" <?php echo $petugas_filter == $p['id'] ? 'selected' : ''; ?>><?php echo $p['nama']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php endif; ?>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-search"></i> Filter</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Stats -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body py-2">
                                <h6>Target Kunjungan</h6>
                                <h4><?php echo $total_target; ?> nasabah</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body py-2">
                                <h6>Target Kutipan</h6>
                                <h4><?php echo formatRupiah($total_nominal); ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body py-2">
                                <h6>Telat Bayar</h6>
                                <h4><?php echo $total_telat; ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-dark">
                            <div class="card-body py-2">
                                <h6>Jatuh Tempo Hari Ini</h6>
                                <h4><?php echo $total_belum; ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Route List -->
                <?php if (empty($rute)): ?>
                    <div class="alert alert-info"><i class="bi bi-info-circle"></i> Tidak ada angsuran yang perlu dikutip pada tanggal <?php echo formatDate($tanggal); ?></div>
                <?php else: ?>
                    <?php foreach ($grouped as $pid => $group): ?>
                        <h5 class="mb-3"><i class="bi bi-person-badge"></i> <?php echo $group['nama']; ?> <span class="badge bg-secondary"><?php echo count($group['items']); ?> nasabah</span></h5>
                        <?php $no = 1; foreach ($group['items'] as $r): ?>
                            <div class="card mb-2 route-card <?php echo $r['status'] === 'telat' ? 'telat' : ''; ?>">
                                <div class="card-body py-2">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            <div class="route-number bg-<?php echo $r['status'] === 'telat' ? 'danger' : 'primary'; ?> text-white">
                                                <?php echo $no++; ?>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <strong><?php echo $r['nama_nasabah']; ?></strong>
                                            <small class="text-muted ms-2">(<?php echo $r['kode_nasabah']; ?>)</small>
                                            <br>
                                            <small>
                                                <i class="bi bi-geo-alt"></i> <?php echo $r['alamat'] ?: 'Alamat belum diisi'; ?>
                                                <?php if ($r['telp']): ?>
                                                    &nbsp;|&nbsp; <i class="bi bi-telephone"></i> 
                                                    <a href="https://wa.me/<?php echo preg_replace('/^0/', '62', $r['telp']); ?>" target="_blank"><?php echo $r['telp']; ?></a>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                        <div class="col-auto text-end">
                                            <div>
                                                <strong><?php echo formatRupiah($r['total_angsuran']); ?></strong>
                                                <?php if ($r['denda'] > 0): ?>
                                                    <small class="text-danger">+<?php echo formatRupiah($r['denda']); ?></small>
                                                <?php endif; ?>
                                            </div>
                                            <small>
                                                <?php echo $r['kode_pinjaman']; ?> - Ang #<?php echo $r['no_angsuran']; ?>
                                                <?php 
                                                $frek = $r['frekuensi_id'] ?? $r['frekuensi'] ?? 'bulanan';
                                                echo getFrequencyLabel($frek);
                                                ?>
                                            </small>
                                            <br>
                                            <?php if ($r['status'] === 'telat'): ?>
                                                <span class="badge bg-danger">Telat <?php echo $r['hari_telat']; ?> hari</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark">Jatuh tempo <?php echo formatDate($r['jatuh_tempo'], 'd/m'); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-auto no-print">
                                            <a href="../angsuran/bayar.php?id=<?php echo $r['angsuran_id']; ?>" class="btn btn-sm btn-success" title="Bayar">
                                                <i class="bi bi-cash"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <hr class="my-4">
                    <?php endforeach; ?>
                <?php endif; ?>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/id.js"></script>
    <script>
        $(document).ready(function() {
            flatpickr('.flatpickr-date', {
                locale: 'id',
                dateFormat: 'Y-m-d',
                allowInput: true,
                altInput: true,
                altFormat: 'd F Y',
                theme: 'light'
            });
        });
    </script>
</body>
</html>
