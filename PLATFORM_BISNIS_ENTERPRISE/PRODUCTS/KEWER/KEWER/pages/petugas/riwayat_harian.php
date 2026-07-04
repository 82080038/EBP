<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();

// Only petugas can access this page
$user = getCurrentUser();
if (!in_array($user['role'], ['petugas_cabang', 'petugas_pusat'])) {
    header('Location: ' . baseUrl('dashboard.php'));
    exit();
}

$kantor_id = 1; // Single office
$petugas_id = $user['id'];
$tanggal = $_GET['tanggal'] ?? date('Y-m-d');

// Get cash balance for selected date
$kas_petugas = query("SELECT * FROM kas_petugas WHERE petugas_id = ? AND cabang_id = ? AND tanggal = ?", [$petugas_id, $kantor_id, $tanggal]);
$kas_petugas = is_array($kas_petugas) && isset($kas_petugas[0]) ? $kas_petugas[0] : null;

$saldo_awal = $kas_petugas['saldo_awal'] ?? 0;
$total_kutip = $kas_petugas['total_kutip'] ?? 0;
$total_disetor = $kas_petugas['total_disetor'] ?? 0;
$saldo_akhir = $kas_petugas['saldo_akhir'] ?? ($saldo_awal + $total_kutip - $total_disetor);

// Get transactions for selected date
$transaksi = query("
    SELECT 
        p.kode_pembayaran,
        n.nama,
        n.kode_nasabah,
        p.jumlah_bayar,
        p.denda,
        p.total_bayar,
        p.tanggal_bayar,
        p.cara_bayar,
        'pembayaran' as jenis,
        a.no_angsuran,
        pin.kode_pinjaman
    FROM pembayaran p
    JOIN angsuran a ON p.angsuran_id = a.id
    JOIN pinjaman pin ON a.pinjaman_id = pin.id
    JOIN nasabah n ON pin.nasabah_id = n.id
    WHERE p.petugas_id = ? AND p.cabang_id = ? AND DATE(p.tanggal_bayar) = ?
    UNION ALL
    SELECT 
        ks.id as kode_pembayaran,
        'Setoran Kas' as nama,
        'N/A' as kode_nasabah,
        ks.total_setoran as jumlah_bayar,
        0 as denda,
        ks.total_setoran as total_bayar,
        ks.tanggal as tanggal_bayar,
        'setoran' as cara_bayar,
        'setoran' as jenis,
        0 as no_angsuran,
        'N/A' as kode_pinjaman
    FROM kas_petugas_setoran ks
    WHERE ks.petugas_id = ? AND ks.cabang_id = ? AND ks.tanggal = ?
    ORDER BY tanggal_bayar DESC
", [$petugas_id, $kantor_id, $tanggal, $petugas_id, $kantor_id, $tanggal]);

if (!is_array($transaksi)) {
    $transaksi = [];
}

// Calculate totals
$total_pembayaran = array_sum(array_column(array_filter($transaksi, fn($t) => $t['jenis'] === 'pembayaran'), 'total_bayar'));
$total_setoran = array_sum(array_column(array_filter($transaksi, fn($t) => $t['jenis'] === 'setoran'), 'total_bayar'));
$total_denda = array_sum(array_column(array_filter($transaksi, fn($t) => $t['jenis'] === 'pembayaran'), 'denda'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Riwayat Harian - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-bottom: 80px;
        }
        .summary-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .summary-value {
            font-size: 1.8rem;
            font-weight: 700;
        }
        .transaction-item {
            background: white;
            border-radius: 15px;
            padding: 15px;
            margin-bottom: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            padding: 10px 15px;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        .bottom-nav .nav-link {
            display: flex;
            flex-direction: column;
            align-items: center;
            font-size: 0.75rem;
            color: #6c757d;
        }
        .bottom-nav .nav-link.active {
            color: #667eea;
        }
        .bottom-nav .nav-link i {
            font-size: 1.5rem;
            margin-bottom: 3px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <nav class="navbar navbar-dark bg-dark sticky-top">
        <div class="container-fluid">
            <a href="transaksi.php" class="btn btn-outline-light btn-sm">
                <i class="bi bi-arrow-left"></i>
            </a>
            <span class="navbar-brand mb-0 h1">
                <i class="bi bi-clock-history"></i> Riwayat Harian
            </span>
            <a href="../../logout.php" class="btn btn-outline-light btn-sm">
                <i class="bi bi-box-arrow-right"></i>
            </a>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Date Filter -->
        <div class="card mb-3">
            <div class="card-body">
                <label class="form-label fw-bold">Pilih Tanggal</label>
                <input type="text" class="form-control" id="tanggal" value="<?php echo date('d/m/Y', strtotime($tanggal)); ?>">
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-3">
            <div class="col-6">
                <div class="summary-card">
                    <div class="text-muted small">Saldo Awal</div>
                    <div class="summary-value text-primary">
                        Rp<?php echo number_format($saldo_awal, 0, ',', '.'); ?>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="summary-card">
                    <div class="text-muted small">Saldo Akhir</div>
                    <div class="summary-value text-success">
                        Rp<?php echo number_format($saldo_akhir, 0, ',', '.'); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-6">
                <div class="summary-card">
                    <div class="text-muted small">Total Kutip</div>
                    <div class="summary-value text-success">
                        +Rp<?php echo number_format($total_kutip, 0, ',', '.'); ?>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="summary-card">
                    <div class="text-muted small">Total Setor</div>
                    <div class="summary-value text-danger">
                        -Rp<?php echo number_format($total_disetor, 0, ',', '.'); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-6">
                <div class="summary-card">
                    <div class="text-muted small">Total Pembayaran</div>
                    <div class="summary-value text-info">
                        Rp<?php echo number_format($total_pembayaran, 0, ',', '.'); ?>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="summary-card">
                    <div class="text-muted small">Total Denda</div>
                    <div class="summary-value text-warning">
                        Rp<?php echo number_format($total_denda, 0, ',', '.'); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transaction List -->
        <h5 class="mb-3"><i class="bi bi-list-ul"></i> Detail Transaksi</h5>
        <?php if (empty($transaksi)): ?>
            <div class="text-center text-muted py-5">
                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                <p class="mt-3">Tidak ada transaksi pada tanggal ini</p>
            </div>
        <?php else: ?>
            <?php foreach ($transaksi as $t): ?>
                <div class="transaction-item">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <div class="fw-bold"><?php echo htmlspecialchars($t['nama']); ?></div>
                            <?php if ($t['jenis'] === 'pembayaran'): ?>
                                <small class="text-muted">
                                    <?php echo htmlspecialchars($t['kode_nasabah']); ?> | 
                                    <?php echo htmlspecialchars($t['kode_pinjaman']); ?>
                                </small>
                            <?php endif; ?>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold <?php echo $t['jenis'] === 'setoran' ? 'text-danger' : 'text-success'; ?>">
                                <?php echo $t['jenis'] === 'setoran' ? '-' : '+'; ?>Rp<?php echo number_format($t['total_bayar'], 0, ',', '.'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="bi bi-clock"></i> <?php echo date('H:i', strtotime($t['tanggal_bayar'] ?? $t['tanggal'])); ?>
                            <?php if ($t['jenis'] === 'pembayaran'): ?>
                                | <i class="bi bi-credit-card"></i> <?php echo ucfirst($t['cara_bayar']); ?>
                                | <i class="bi bi-calendar-check"></i> Angsuran ke-<?php echo $t['no_angsuran']; ?>
                            <?php endif; ?>
                        </small>
                        <small class="badge bg-<?php echo $t['jenis'] === 'setoran' ? 'warning' : 'primary'; ?>">
                            <?php echo ucfirst($t['jenis']); ?>
                        </small>
                    </div>
                    <?php if ($t['jenis'] === 'pembayaran' && $t['denda'] > 0): ?>
                        <div class="mt-2 pt-2 border-top">
                            <small class="text-warning">
                                <i class="bi bi-exclamation-triangle"></i> Termasuk denda: Rp<?php echo number_format($t['denda'], 0, ',', '.'); ?>
                            </small>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Bottom Navigation -->
    <div class="bottom-nav">
        <div class="row">
            <div class="col-3">
                <a href="../../dashboard.php" class="nav-link">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </div>
            <div class="col-3">
                <a href="transaksi.php" class="nav-link">
                    <i class="bi bi-cash-coin"></i>
                    <span>Transaksi</span>
                </a>
            </div>
            <div class="col-3">
                <a href="riwayat_harian.php" class="nav-link active">
                    <i class="bi bi-clock-history"></i>
                    <span>Riwayat</span>
                </a>
            </div>
            <div class="col-3">
                <a href="../kas_petugas/index.php" class="nav-link">
                    <i class="bi bi-wallet2"></i>
                    <span>Setor</span>
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/id.js"></script>
    <!-- Include Notifications JS for standalone pages -->
    <script src="<?php echo baseUrl('includes/js/notifications.js'); ?>"></script>
    <script>
        flatpickr("#tanggal", {
            dateFormat: "d/m/Y",
            defaultDate: "<?php echo date('d/m/Y', strtotime($tanggal)); ?>",
            locale: "id",
            onChange: function(selectedDates, dateStr, instance) {
                if (selectedDates.length > 0) {
                    const date = selectedDates[0];
                    const formattedDate = date.toISOString().split('T')[0];
                    window.location.href = '?tanggal=' + formattedDate;
                }
            }
        });
        
        // Initialize notifications for standalone page
        $(document).ready(function() {
            window.KewerNotifications.updateBadge();
        });
    </script>
</body>
</html>
