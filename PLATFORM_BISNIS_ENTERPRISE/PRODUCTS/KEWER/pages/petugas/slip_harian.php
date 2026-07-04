<?php
/**
 * Slip Harian Petugas — rekap kutipan hari ini
 * Bisa dicetak / screenshot oleh petugas sendiri
 */
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
require_once BASE_PATH . '/includes/feature_flags.php';
requireFeature('slip_harian', BASE_URL . '/dashboard.php');
requireLogin();

$user = getCurrentUser();
$role = $user['role'];

// Petugas hanya lihat miliknya; manager/bos bisa pilih petugas
$tanggal     = $_GET['tanggal'] ?? date('Y-m-d');
$petugas_id  = (int)($_GET['petugas_id'] ?? $user['id']);

// Role restriction
if (in_array($role, ['petugas_pusat', 'petugas_cabang'])) {
    $petugas_id = $user['id']; // Force ke diri sendiri
}
if (!hasPermission('view_petugas') && $petugas_id !== (int)$user['id']) {
    header('Location: ' . baseUrl('dashboard.php')); exit();
}

$petugas = query("SELECT * FROM users WHERE id = ?", [$petugas_id]);
if (!$petugas) { header('Location: ' . baseUrl('dashboard.php')); exit(); }
$petugas = $petugas[0];

// Kutipan hari ini oleh petugas
$pembayaran = query(
    "SELECT py.*, n.nama as nama_nasabah, n.kode_nasabah,
            p.kode_pinjaman, p.frekuensi_id,
            a.no_angsuran as angsuran_ke
     FROM pembayaran py
     JOIN pinjaman p ON py.pinjaman_id = p.id
     JOIN nasabah n ON p.nasabah_id = n.id
     LEFT JOIN angsuran a ON py.angsuran_id = a.id
     WHERE (py.petugas_id = ? OR py.petugas_pengganti_id = ?)
       AND DATE(py.tanggal_bayar) = ?
     ORDER BY py.created_at ASC",
    [$petugas_id, $petugas_id, $tanggal]
);
if (!$pembayaran) $pembayaran = [];

$total_kutipan = array_sum(array_column($pembayaran, 'total_bayar'));
$total_denda   = array_sum(array_column($pembayaran, 'denda'));
$jumlah_nasabah = count(array_unique(array_column($pembayaran, 'nama_nasabah')));

// Daftar petugas untuk filter (manager ke atas)
$petugas_list = [];
if (hasPermission('view_petugas')) {
    $petugas_list = query("SELECT id, nama, role FROM users WHERE role IN ('petugas_pusat','petugas_cabang') AND status='aktif' ORDER BY nama") ?: [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slip Harian — <?= htmlspecialchars($petugas['nama']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { font-size: 12px; }
            .slip-header { border-bottom: 2px solid #000; margin-bottom: 8px; }
            .main-container, .content-area { margin: 0; height: auto; overflow: visible; }
        }
    </style>
</head>
<body>
    <div class="main-container no-print-nav">
        <div class="no-print"><?php require_once BASE_PATH . '/includes/sidebar.php'; ?></div>
        <main class="content-area">

            <!-- Filter (no-print) -->
            <div class="no-print mb-3">
                <form method="GET" class="row g-2 align-items-end">
                    <?php if (!empty($petugas_list)): ?>
                    <div class="col-md-3">
                        <label class="form-label">Petugas</label>
                        <select name="petugas_id" class="form-select form-select-sm">
                            <?php foreach ($petugas_list as $pt): ?>
                            <option value="<?= $pt['id'] ?>" <?= $pt['id'] == $petugas_id ? 'selected' : '' ?>><?= htmlspecialchars($pt['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    <div class="col-md-2">
                        <label class="form-label">Tanggal</label>
                        <input type="date" name="tanggal" class="form-control form-control-sm" value="<?= $tanggal ?>">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-sm btn-primary">Tampilkan</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary ms-1" onclick="window.print()">
                            <i class="bi bi-printer"></i> Cetak
                        </button>
                    </div>
                </form>
            </div>

            <!-- Slip -->
            <div class="slip-header mb-3 pb-2 border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0"><strong><?= APP_NAME ?></strong> — Slip Harian Petugas</h5>
                        <small class="text-muted">Dicetak: <?= date('d/m/Y H:i') ?></small>
                    </div>
                    <div class="text-end">
                        <strong><?= htmlspecialchars($petugas['nama']) ?></strong><br>
                        <small><?= date('d F Y', strtotime($tanggal)) ?></small>
                    </div>
                </div>
            </div>

            <!-- Summary cards -->
            <div class="row g-2 mb-3">
                <div class="col-4">
                    <div class="card border-0 bg-success bg-opacity-10 text-center p-2">
                        <div class="fw-bold fs-5 text-success">Rp <?= number_format($total_kutipan, 0, ',', '.') ?></div>
                        <small class="text-muted">Total Kutipan</small>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card border-0 bg-primary bg-opacity-10 text-center p-2">
                        <div class="fw-bold fs-5 text-primary"><?= count($pembayaran) ?></div>
                        <small class="text-muted">Transaksi</small>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card border-0 bg-info bg-opacity-10 text-center p-2">
                        <div class="fw-bold fs-5 text-info"><?= $jumlah_nasabah ?></div>
                        <small class="text-muted">Nasabah</small>
                    </div>
                </div>
            </div>

            <!-- Tabel kutipan -->
            <table class="table table-sm table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th width="5%">#</th>
                        <th>Nasabah</th>
                        <th>Pinjaman</th>
                        <th>Angsuran ke-</th>
                        <th class="text-end">Jumlah</th>
                        <th class="text-end">Denda</th>
                        <th>Cara Bayar</th>
                        <th class="no-print">Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($pembayaran)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-3">Belum ada kutipan untuk tanggal ini</td></tr>
                <?php else: ?>
                    <?php foreach ($pembayaran as $i => $py): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td>
                            <strong><?= htmlspecialchars($py['nama_nasabah']) ?></strong><br>
                            <small class="text-muted"><?= htmlspecialchars($py['kode_nasabah']) ?></small>
                        </td>
                        <td><small><?= htmlspecialchars($py['kode_pinjaman']) ?></small></td>
                        <td class="text-center"><?= $py['angsuran_ke'] ?? '-' ?></td>
                        <td class="text-end">Rp <?= number_format($py['jumlah_bayar'], 0, ',', '.') ?></td>
                        <td class="text-end <?= $py['denda'] > 0 ? 'text-danger' : '' ?>">
                            <?= $py['denda'] > 0 ? 'Rp ' . number_format($py['denda'], 0, ',', '.') : '-' ?>
                        </td>
                        <td><span class="badge bg-secondary"><?= ucfirst($py['cara_bayar'] ?? 'tunai') ?></span></td>
                        <td class="no-print">
                            <?php if ($py['is_offline']): ?>
                            <span class="badge bg-warning text-dark">Offline</span>
                            <?php else: ?>
                            <span class="badge bg-success">Online</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="4" class="text-end">TOTAL</td>
                        <td class="text-end">Rp <?= number_format($total_kutipan, 0, ',', '.') ?></td>
                        <td class="text-end text-danger">Rp <?= number_format($total_denda, 0, ',', '.') ?></td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>

            <!-- Tanda tangan -->
            <div class="row mt-4 no-print-partial">
                <div class="col-6 text-center">
                    <div class="border-top border-dark mt-5 pt-1">Petugas: <?= htmlspecialchars($petugas['nama']) ?></div>
                </div>
                <div class="col-6 text-center">
                    <div class="border-top border-dark mt-5 pt-1">Penerima Setoran</div>
                </div>
            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
