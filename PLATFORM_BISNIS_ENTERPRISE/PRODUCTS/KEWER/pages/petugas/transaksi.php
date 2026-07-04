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

// Get today's cash balance
$today = date('Y-m-d');
$kas_petugas = query("SELECT * FROM kas_petugas WHERE petugas_id = ? AND tanggal = ?", [$petugas_id, $today]);
$kas_petugas = is_array($kas_petugas) && isset($kas_petugas[0]) ? $kas_petugas[0] : null;

$saldo_awal = $kas_petugas['saldo_awal'] ?? 0;
$total_kutip = $kas_petugas['total_kutip'] ?? 0;
$total_disetor = $kas_petugas['total_disetor'] ?? 0;
$saldo_akhir = $kas_petugas['saldo_akhir'] ?? ($saldo_awal + $total_kutip - $total_disetor);

// Get today's transactions
$transaksi = query("
    SELECT 
        p.kode_pembayaran,
        n.nama,
        p.jumlah_bayar,
        p.tanggal_bayar,
        p.cara_bayar,
        'pembayaran' as jenis
    FROM pembayaran p
    JOIN angsuran a ON p.angsuran_id = a.id
    JOIN pinjaman pin ON a.pinjaman_id = pin.id
    JOIN nasabah n ON pin.nasabah_id = n.id
    WHERE p.petugas_id = ? AND DATE(p.tanggal_bayar) = ?
    UNION ALL
    SELECT 
        ks.id as kode_pembayaran,
        'Setoran Kas' as nama,
        ks.total_setoran as jumlah_bayar,
        ks.tanggal as tanggal_bayar,
        'setoran' as cara_bayar,
        'setoran' as jenis
    FROM kas_petugas_setoran ks
    WHERE ks.petugas_id = ? AND ks.tanggal = ?
    ORDER BY tanggal_bayar DESC
", [$petugas_id, $today, $petugas_id, $today]);

if (!is_array($transaksi)) {
    $transaksi = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Transaksi Petugas - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding-bottom: 80px;
        }
        .big-button {
            padding: 25px 20px;
            font-size: 1.3rem;
            font-weight: 600;
            border-radius: 15px;
            margin-bottom: 15px;
            min-height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 8px;
            transition: transform 0.2s;
        }
        .big-button:active {
            transform: scale(0.95);
        }
        .big-button i {
            font-size: 2rem;
        }
        .balance-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }
        .balance-amount {
            font-size: 2.5rem;
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
            <span class="navbar-brand mb-0 h1">
                <i class="bi bi-person-badge"></i> Transaksi Petugas
            </span>
            <a href="../../logout.php" class="btn btn-outline-light btn-sm">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Balance Card -->
        <div class="balance-card">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span><i class="bi bi-wallet2"></i> Saldo Hari Ini</span>
                <span class="badge bg-light text-dark"><?php echo date('d/m/Y'); ?></span>
            </div>
            <div class="balance-amount">
                Rp<?php echo number_format($saldo_akhir, 0, ',', '.'); ?>
            </div>
            <div class="row mt-3">
                <div class="col-4">
                    <small>Awal</small>
                    <div class="fw-bold">Rp<?php echo number_format($saldo_awal, 0, ',', '.'); ?></div>
                </div>
                <div class="col-4">
                    <small>Kutip</small>
                    <div class="fw-bold text-success">+Rp<?php echo number_format($total_kutip, 0, ',', '.'); ?></div>
                </div>
                <div class="col-4">
                    <small>Setor</small>
                    <div class="fw-bold text-danger">-Rp<?php echo number_format($total_disetor, 0, ',', '.'); ?></div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <h5 class="mb-3"><i class="bi bi-lightning"></i> Aksi Cepat</h5>
        <div class="row">
            <div class="col-6">
                <a href="../angsuran/bayar.php" class="btn btn-primary big-button w-100">
                    <i class="bi bi-cash-coin"></i>
                    Bayar Angsuran
                </a>
            </div>
            <div class="col-6">
                <a href="../pembayaran/index.php" class="btn btn-success big-button w-100">
                    <i class="bi bi-receipt"></i>
                    Riwayat Bayar
                </a>
            </div>
            <div class="col-6">
                <a href="../nasabah/index.php" class="btn btn-info big-button w-100">
                    <i class="bi bi-people"></i>
                    Cari Nasabah
                </a>
            </div>
            <div class="col-6">
                <a href="riwayat_harian.php" class="btn btn-warning big-button w-100">
                    <i class="bi bi-clock-history"></i>
                    Riwayat Hari Ini
                </a>
            </div>
        </div>

        <!-- Today's Transactions -->
        <h5 class="mb-3 mt-4"><i class="bi bi-list-ul"></i> Transaksi Hari Ini</h5>
        <?php if (empty($transaksi)): ?>
            <div class="text-center text-muted py-5">
                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                <p class="mt-3">Belum ada transaksi hari ini</p>
            </div>
        <?php else: ?>
            <?php foreach ($transaksi as $t): ?>
                <div class="transaction-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-bold"><?php echo htmlspecialchars($t['nama']); ?></div>
                            <small class="text-muted">
                                <i class="bi bi-clock"></i> <?php echo date('H:i', strtotime($t['tanggal_bayar'] ?? $t['tanggal'])); ?>
                                <?php if ($t['jenis'] === 'pembayaran'): ?>
                                    | <i class="bi bi-credit-card"></i> <?php echo ucfirst($t['cara_bayar']); ?>
                                <?php endif; ?>
                            </small>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold <?php echo $t['jenis'] === 'setoran' ? 'text-danger' : 'text-success'; ?>">
                                <?php echo $t['jenis'] === 'setoran' ? '-' : '+'; ?>Rp<?php echo number_format($t['jumlah_bayar'], 0, ',', '.'); ?>
                            </div>
                            <small class="badge bg-<?php echo $t['jenis'] === 'setoran' ? 'warning' : 'primary'; ?>">
                                <?php echo ucfirst($t['jenis']); ?>
                            </small>
                        </div>
                    </div>
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
                <a href="transaksi.php" class="nav-link active">
                    <i class="bi bi-cash-coin"></i>
                    <span>Transaksi</span>
                </a>
            </div>
            <div class="col-3">
                <a href="riwayat_harian.php" class="nav-link">
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
    <!-- Include Notifications JS for standalone pages -->
    <script src="<?php echo baseUrl('includes/js/notifications.js'); ?>"></script>
    <script>
    // Initialize notifications for standalone page
    $(document).ready(function() {
        window.KewerNotifications.updateBadge();
    });
    </script>
</body>
</html>
