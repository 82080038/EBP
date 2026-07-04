<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
require_once BASE_PATH . '/includes/business_logic.php';
requireLogin();

// Permission check
if (!hasPermission('view_nasabah')) {
    header('Location: ' . baseUrl('dashboard.php'));
    exit();
}

$id = $_GET['id'] ?? null;

// Get cabang filter based on role
$user = getCurrentUser();
$role = $user['role'];
$user_cabang_id = $user['cabang_id'] ?? null;

// Get nasabah data
$nasabah = query("
    SELECT n.*, c.nama_cabang 
    FROM nasabah n 
    LEFT JOIN cabang c ON n.cabang_id = c.id 
    WHERE n.id = ?
", [$id]);

if (!is_array($nasabah) || empty($nasabah)) {
    header('Location: index.php');
    exit();
}

$nasabah = $nasabah[0];

// Handle blacklist/unblacklist action
if ($_POST && isset($_POST['blacklist_action'])) {
    $aksi = $_POST['blacklist_action'];
    $alasan = trim($_POST['alasan'] ?? '');
    
    if (in_array($aksi, ['blacklist', 'unblacklist']) && $alasan !== '') {
        $result = toggleBlacklist($id, $aksi, $alasan);
        if ($result) {
            $_SESSION['success'] = ($aksi === 'blacklist') ? 'Nasabah berhasil di-blacklist' : 'Nasabah berhasil di-unblacklist';
            header("Location: detail.php?id=$id");
            exit();
        } else {
            $_SESSION['error'] = 'Gagal mengubah status nasabah';
        }
    } else {
        $_SESSION['error'] = 'Alasan harus diisi';
    }
    // Refresh nasabah data
    $nasabah_refresh = query("SELECT n.*, c.nama_cabang FROM nasabah n LEFT JOIN cabang c ON n.cabang_id = c.id WHERE n.id = ?", [$id]);
    if ($nasabah_refresh) $nasabah = $nasabah_refresh[0];
}

// Get blacklist history
$blacklist_log = query("SELECT bl.*, u.nama as nama_petugas FROM blacklist_log bl LEFT JOIN users u ON bl.dilakukan_oleh = u.id WHERE bl.nasabah_id = ? ORDER BY bl.created_at DESC", [$id]);
if (!is_array($blacklist_log)) $blacklist_log = [];

// Get loan history
$pinjaman = query("
    SELECT * FROM pinjaman
    WHERE nasabah_id = ?
    ORDER BY created_at DESC
", [$id]);
if (!is_array($pinjaman)) {
    $pinjaman = [];
}

// Get active loan
$pinjaman_aktif = query("
    SELECT *,
           (SELECT COUNT(*) FROM angsuran WHERE pinjaman_id = pinjaman.id AND status = 'lunas') as angsuran_lunas,
           (SELECT COUNT(*) FROM angsuran WHERE pinjaman_id = pinjaman.id) as total_angsuran
    FROM pinjaman
    WHERE nasabah_id = ? AND status = 'aktif'
", [$id]);
if (!is_array($pinjaman_aktif)) $pinjaman_aktif = [];

// Data v2.2.0
$ahli_waris    = query("SELECT * FROM ahli_waris WHERE nasabah_id = ? ORDER BY adalah_penjamin DESC", [$id]) ?: [];
$kelebihan_byr = query("SELECT * FROM kelebihan_bayar WHERE nasabah_id = ? ORDER BY created_at DESC LIMIT 10", [$id]) ?: [];
$riwayat_skor  = query("SELECT * FROM riwayat_skor_kredit WHERE nasabah_id = ? ORDER BY created_at DESC LIMIT 10", [$id]) ?: [];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Nasabah - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .img-thumbnail { max-width: 200px; }
    </style>
</head>
<body>
    <div class="main-container">
        <?php require_once BASE_PATH . '/includes/sidebar.php'; ?>
        
        <main class="content-area">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Detail Nasabah</h1>
                    <div>
                        <?php if ($nasabah['status'] !== 'blacklist' && !$nasabah['tanggal_meninggal']): ?>
                            <button class="btn btn-danger me-2" data-bs-toggle="modal" data-bs-target="#blacklistModal">
                                <i class="bi bi-slash-circle"></i> Blacklist
                            </button>
                        <?php elseif ($nasabah['status'] === 'blacklist'): ?>
                            <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#unblacklistModal">
                                <i class="bi bi-check-circle"></i> Unblacklist
                            </button>
                        <?php endif; ?>
                        <?php if (!$nasabah['tanggal_meninggal'] && hasPermission('manage_nasabah')): ?>
                        <button class="btn btn-outline-dark me-2" onclick="modalMeninggal(<?= $nasabah['id'] ?>, '<?= htmlspecialchars($nasabah['nama']) ?>')">
                            <i class="bi bi-file-earmark-x"></i> Meninggal
                        </button>
                        <?php endif; ?>
                        <a href="edit.php?id=<?php echo $nasabah['id']; ?>" class="btn btn-warning me-2">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                
                <!-- Nasabah Information -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="bi bi-person"></i> Informasi Nasabah</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <table class="table table-borderless">
                                            <tr>
                                                <td><strong>Kode Nasabah:</strong></td>
                                                <td><?php echo $nasabah['kode_nasabah']; ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Nama Lengkap:</strong></td>
                                                <td><?php echo $nasabah['nama']; ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>No. KTP:</strong></td>
                                                <td><?php echo $nasabah['ktp']; ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>No. Telepon:</strong></td>
                                                <td>
                                                    <a href="https://wa.me/<?php echo str_replace(['+', '-'], '', $nasabah['telp']); ?>" target="_blank">
                                                        <?php echo $nasabah['telp']; ?>
                                                        <i class="bi bi-whatsapp text-success"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Email:</strong></td>
                                                <td><?php echo $nasabah['email'] ?: '-'; ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <table class="table table-borderless">
                                            <tr>
                                                <td><strong>Alamat:</strong></td>
                                                <td><?php echo nl2br($nasabah['alamat']); ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Jenis Usaha:</strong></td>
                                                <td><?php echo $nasabah['jenis_usaha'] ?: '-'; ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Lokasi Pasar/Warung:</strong></td>
                                                <td><?php echo $nasabah['lokasi_pasar'] ?: '-'; ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Status:</strong></td>
                                                <td>
                                                    <?php
                                                    $status_class = ['aktif'=>'success','nonaktif'=>'warning','blacklist'=>'danger'];
                                                    ?>
                                                    <span class="badge bg-<?php echo $status_class[$nasabah['status']] ?? 'secondary'; ?>">
                                                        <?php echo ucfirst($nasabah['status']); ?>
                                                    </span>
                                                    <?php if ($nasabah['platform_blacklist']): ?>
                                                        <span class="badge bg-dark ms-1">Platform Blacklist</span>
                                                    <?php endif; ?>
                                                    <?php if ($nasabah['tanggal_meninggal']): ?>
                                                        <span class="badge bg-secondary ms-1"><i class="bi bi-file-earmark-x"></i> Meninggal <?= $nasabah['tanggal_meninggal'] ?></span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Skor Kredit:</strong></td>
                                                <td>
                                                    <?php
                                                    $skor = (int)($nasabah['skor_kredit'] ?? 100);
                                                    $skor_color = $skor >= 70 ? 'success' : ($skor >= 40 ? 'warning' : 'danger');
                                                    ?>
                                                    <div class="progress" style="height:18px;width:120px;display:inline-flex">
                                                        <div class="progress-bar bg-<?= $skor_color ?>" style="width:<?= $skor ?>%"><?= $skor ?>/100</div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Tanggal Daftar:</strong></td>
                                                <td><?php echo formatDate($nasabah['created_at']); ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="bi bi-camera"></i> Dokumen</h5>
                            </div>
                            <div class="card-body text-center">
                                <?php if ($nasabah['foto_ktp']): ?>
                                    <p><strong>Foto KTP</strong></p>
                                    <img src="../../<?php echo $nasabah['foto_ktp']; ?>" class="img-thumbnail mb-3" alt="KTP">
                                <?php else: ?>
                                    <p class="text-muted">Foto KTP tidak tersedia</p>
                                <?php endif; ?>
                                
                                <?php if ($nasabah['foto_selfie']): ?>
                                    <p><strong>Foto Selfie + KTP</strong></p>
                                    <img src="../../<?php echo $nasabah['foto_selfie']; ?>" class="img-thumbnail" alt="Selfie">
                                <?php else: ?>
                                    <p class="text-muted">Foto selfie tidak tersedia</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Active Loan Summary -->
                <?php if ($pinjaman_aktif): ?>
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5><i class="bi bi-cash-stack"></i> Pinjaman Aktif</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($pinjaman_aktif as $pinj): ?>
                        <div class="row">
                            <div class="col-md-3">
                                <strong>Kode:</strong><br>
                                <?php echo $pinj['kode_pinjaman']; ?>
                            </div>
                            <div class="col-md-3">
                                <strong>Plafon:</strong><br>
                                <?php echo formatRupiah($pinj['plafon']); ?>
                            </div>
                            <div class="col-md-3">
                                <strong>Angsuran:</strong><br>
                                <?php echo $pinj['angsuran_lunas']; ?>/<?php echo $pinj['total_angsuran']; ?>
                            </div>
                            <div class="col-md-3">
                                <strong>Status:</strong><br>
                                <span class="badge bg-warning">Aktif</span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Loan History -->
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-clock-history"></i> Riwayat Pinjaman</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($pinjaman)): ?>
                            <p class="text-muted">Belum ada riwayat pinjaman</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Kode Pinjaman</th>
                                            <th>Plafon</th>
                                            <th>Frekuensi</th>
                                            <th>Tenor</th>
                                            <th>Bunga/Bulan</th>
                                            <th>Tanggal Akad</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pinjaman as $p): ?>
                                            <tr>
                                                <td><?php echo $p['kode_pinjaman']; ?></td>
                                                <td><?php echo formatRupiah($p['plafon']); ?></td>
                                                <td>
                                                    <?php 
                                                    $pfrek = $p['frekuensi_id'] ?? $p['frekuensi'] ?? 'bulanan';
                                                    echo getFrequencyLabel($pfrek);
                                                    ?>
                                                </td>
                                                <td><?php echo $p['tenor']; ?> <?php echo getFrequencyPeriodLabel($pfrek); ?></td>
                                                <td><?php echo $p['bunga_per_bulan']; ?>%</td>
                                                <td><?php echo formatDate($p['tanggal_akad']); ?></td>
                                                <td>
                                                    <?php
                                                    $status_class = [
                                                        'pengajuan' => 'info',
                                                        'disetujui' => 'primary',
                                                        'aktif' => 'success',
                                                        'lunas' => 'secondary',
                                                        'ditolak' => 'danger'
                                                    ];
                                                    ?>
                                                    <span class="badge bg-<?php echo $status_class[$p['status']]; ?>">
                                                        <?php echo ucfirst($p['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="../pinjaman/detail.php?id=<?php echo $p['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <!-- Ahli Waris -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-people"></i> Ahli Waris / Penjamin</h5>
                        <?php if (hasPermission('manage_nasabah')): ?>
                        <button class="btn btn-sm btn-outline-primary" onclick="modalTambahAhliWaris(<?= $nasabah['id'] ?>)">
                            <i class="bi bi-plus"></i> Tambah
                        </button>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <?php if (empty($ahli_waris)): ?>
                            <p class="text-muted mb-0">Belum ada data ahli waris</p>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead><tr><th>Nama</th><th>Hubungan</th><th>KTP</th><th>Telepon</th><th>Penjamin?</th></tr></thead>
                                <tbody>
                                <?php foreach ($ahli_waris as $aw): ?>
                                <tr>
                                    <td><?= htmlspecialchars($aw['nama']) ?></td>
                                    <td><?= ucfirst($aw['hubungan']) ?></td>
                                    <td><?= $aw['ktp'] ?: '-' ?></td>
                                    <td><?= $aw['telp'] ?: '-' ?></td>
                                    <td><?= $aw['adalah_penjamin'] ? '<span class="badge bg-success">Ya</span>' : '-' ?></td>
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Kelebihan Bayar -->
                <?php if (!empty($kelebihan_byr)): ?>
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="bi bi-cash-coin"></i> Kelebihan Bayar</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead><tr><th>Tanggal</th><th>Jumlah</th><th>Status</th></tr></thead>
                                <tbody>
                                <?php foreach ($kelebihan_byr as $kb): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($kb['created_at'])) ?></td>
                                    <td>Rp <?= number_format($kb['jumlah']) ?></td>
                                    <td>
                                        <?php $kb_color = ['pending'=>'warning','dikembalikan'=>'success','dikompensasi'=>'info'][$kb['status']] ?? 'secondary'; ?>
                                        <span class="badge bg-<?= $kb_color ?>"><?= ucfirst($kb['status']) ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Riwayat Skor Kredit -->
                <?php if (!empty($riwayat_skor)): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-graph-up"></i> Riwayat Skor Kredit</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead><tr><th>Tanggal</th><th>Skor</th><th>Delta</th><th>Alasan</th></tr></thead>
                                <tbody>
                                <?php foreach ($riwayat_skor as $rs): ?>
                                <tr>
                                    <td><?= date('d/m/Y H:i', strtotime($rs['created_at'])) ?></td>
                                    <td><?= $rs['skor_sebelum'] ?> → <strong><?= $rs['skor_sesudah'] ?></strong></td>
                                    <td><span class="badge bg-<?= $rs['delta'] > 0 ? 'success' : 'danger' ?>"><?= $rs['delta'] > 0 ? '+' : '' ?><?= $rs['delta'] ?></span></td>
                                    <td><?= ucwords(str_replace('_', ' ', $rs['alasan'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Blacklist History -->
                <?php if (!empty($blacklist_log)): ?>
                <div class="card mb-4">
                    <div class="card-header bg-dark text-white">
                        <h5><i class="bi bi-shield-exclamation"></i> Riwayat Blacklist</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Aksi</th>
                                        <th>Alasan</th>
                                        <th>Oleh</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($blacklist_log as $bl): ?>
                                    <tr>
                                        <td><?php echo formatDate($bl['created_at'], 'd M Y H:i'); ?></td>
                                        <td>
                                            <?php if ($bl['aksi'] === 'blacklist'): ?>
                                                <span class="badge bg-danger">Blacklist</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">Unblacklist</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($bl['alasan']); ?></td>
                                        <td><?php echo $bl['nama_petugas'] ?? '-'; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
    
    <!-- Blacklist Modal -->
    <div class="modal fade" id="blacklistModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="blacklist_action" value="blacklist">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title"><i class="bi bi-slash-circle"></i> Blacklist Nasabah</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            Anda akan mem-blacklist <strong><?php echo $nasabah['nama']; ?></strong>. 
                            Nasabah yang di-blacklist tidak dapat mengajukan pinjaman baru.
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alasan Blacklist *</label>
                            <textarea name="alasan" class="form-control" rows="3" required placeholder="Masukkan alasan blacklist..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-slash-circle"></i> Blacklist
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Unblacklist Modal -->
    <div class="modal fade" id="unblacklistModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="blacklist_action" value="unblacklist">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title"><i class="bi bi-check-circle"></i> Unblacklist Nasabah</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            Anda akan menghapus status blacklist dari <strong><?php echo $nasabah['nama']; ?></strong>.
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alasan Unblacklist *</label>
                            <textarea name="alasan" class="form-control" rows="3" required placeholder="Masukkan alasan unblacklist..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Unblacklist
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const API_BUSINESS = '<?= baseUrl('api/business.php') ?>';

        function showPending() {
            window.location.href = '?status=pending';
        }

        // Modal meninggal dunia
        async function modalMeninggal(nasabah_id, nama) {
            const { value: formValues } = await Swal.fire({
                title: 'Proses Nasabah Meninggal',
                icon: 'warning',
                html: `<div class="text-start">
                    <div class="alert alert-warning">Nasabah <strong>${nama}</strong> akan ditandai meninggal. Semua pinjaman aktif yang tidak memiliki penjamin akan otomatis macet.</div>
                    <div class="mb-2">
                        <label class="form-label">Tanggal Meninggal</label>
                        <input type="date" class="form-control" id="tgl_meninggal" value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Catatan</label>
                        <textarea class="form-control" id="catatan_meninggal" rows="2" placeholder="Sumber informasi, dokumen, dll"></textarea>
                    </div>
                </div>`,
                showCancelButton: true, confirmButtonText: 'Proses', cancelButtonText: 'Batal',
                confirmButtonColor: '#6c757d',
                preConfirm: () => ({ tanggal_meninggal: document.getElementById('tgl_meninggal').value, catatan: document.getElementById('catatan_meninggal').value })
            });
            if (!formValues) return;
            const resp = await fetch(API_BUSINESS, { method: 'POST',
                headers: {'Content-Type':'application/json'},
                body: JSON.stringify({ action: 'nasabah_meninggal', nasabah_id, ...formValues }) });
            const r = await resp.json();
            if (r.success) {
                Swal.fire('Berhasil', `Data diperbarui. ${r.pinjaman_terdampak} pinjaman terdampak. ${r.ada_penjamin ? 'Ada penjamin, pinjaman dilanjutkan.' : 'Tidak ada penjamin — pinjaman ditandai macet.'}`, 'success')
                    .then(() => location.reload());
            } else { Swal.fire('Gagal', r.error || 'Terjadi kesalahan', 'error'); }
        }

        // Modal tambah ahli waris
        async function modalTambahAhliWaris(nasabah_id) {
            const { value: f } = await Swal.fire({
                title: 'Tambah Ahli Waris',
                html: `<div class="text-start">
                    <div class="mb-2"><label class="form-label">Nama *</label><input class="form-control" id="aw_nama" required></div>
                    <div class="mb-2"><label class="form-label">Hubungan *</label>
                        <select class="form-select" id="aw_hubungan">
                            <option value="suami">Suami</option><option value="istri">Istri</option>
                            <option value="anak">Anak</option><option value="orang_tua">Orang Tua</option>
                            <option value="saudara">Saudara</option><option value="lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div class="mb-2"><label class="form-label">No. KTP</label><input class="form-control" id="aw_ktp" maxlength="16"></div>
                    <div class="mb-2"><label class="form-label">Telepon</label><input class="form-control" id="aw_telp"></div>
                    <div class="form-check mb-2"><input type="checkbox" class="form-check-input" id="aw_penjamin"><label class="form-check-label" for="aw_penjamin">Menjadi penjamin pinjaman</label></div>
                </div>`,
                showCancelButton: true, confirmButtonText: 'Simpan', cancelButtonText: 'Batal',
                preConfirm: () => {
                    const nama = document.getElementById('aw_nama').value.trim();
                    if (!nama) { Swal.showValidationMessage('Nama wajib diisi'); return false; }
                    return { nasabah_id, nama, hubungan: document.getElementById('aw_hubungan').value,
                        ktp: document.getElementById('aw_ktp').value || null,
                        telp: document.getElementById('aw_telp').value || null,
                        adalah_penjamin: document.getElementById('aw_penjamin').checked ? 1 : 0 };
                }
            });
            if (!f) return;
            const resp = await fetch(API_BUSINESS, { method: 'POST', headers: {'Content-Type':'application/json'},
                body: JSON.stringify({ action: 'tambah_ahli_waris', ...f }) });
            const r = await resp.json();
            if (r.success) { Swal.fire('Berhasil', 'Ahli waris ditambahkan', 'success').then(() => location.reload()); }
            else { Swal.fire('Gagal', r.error || 'Terjadi kesalahan', 'error'); }
        }

        // Convert session alerts to SweetAlert2
        <?= getSessionAlertsJS() ?>
    </script>
</body>
</html>
