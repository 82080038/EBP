<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
require_once BASE_PATH . '/includes/business_logic.php';
requireLogin();

// Permission check
if (!hasPermission('view_pinjaman')) {
    header('Location: ' . baseUrl('dashboard.php'));
    exit();
}

$id = $_GET['id'] ?? null;

// Get cabang filter based on role
$user = getCurrentUser();
$role = $user['role'];
$user_cabang_id = $user['cabang_id'] ?? null;

// Get pinjaman data
$pinjaman = query("
    SELECT p.*, n.nama, n.telp, n.kode_nasabah, n.alamat, u.nama as petugas_nama
    FROM pinjaman p 
    JOIN nasabah n ON p.nasabah_id = n.id 
    LEFT JOIN users u ON p.petugas_id = u.id
    WHERE p.id = ?
", [$id]);

if (!$pinjaman) {
    header('Location: ' . baseUrl('pages/pinjaman/index.php'));
    exit();
}

$pinjaman = $pinjaman[0];

// Get installments
$angsuran = query("
    SELECT * FROM angsuran
    WHERE pinjaman_id = ?
    ORDER BY no_angsuran
", [$id]);
if (!is_array($angsuran)) {
    $angsuran = [];
}

// Get payments
$pembayaran = query("
    SELECT p.*, a.no_angsuran
    FROM pembayaran p
    JOIN angsuran a ON p.angsuran_id = a.id
    WHERE p.pinjaman_id = ?
    ORDER BY p.tanggal_bayar DESC
", [$id]);
if (!is_array($pembayaran)) {
    $pembayaran = [];
}

// Get statistics
$stats = query("
    SELECT 
        COUNT(*) as total_angsuran,
        SUM(CASE WHEN status = 'lunas' THEN 1 ELSE 0 END) as lunas,
        SUM(CASE WHEN status = 'telat' THEN 1 ELSE 0 END) as telat,
        SUM(CASE WHEN status = 'belum' THEN 1 ELSE 0 END) as belum,
        SUM(total_bayar) as total_dibayar,
        SUM(denda) as total_denda
    FROM angsuran 
    WHERE pinjaman_id = ?
", [$id])[0];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pinjaman - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <div class="main-container">
        <?php require_once BASE_PATH . '/includes/sidebar.php'; ?>
        
        <main class="content-area">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Detail Pinjaman</h1>
                    <div>
                        <?php if (in_array($pinjaman['status'], ['aktif', 'lunas'])): ?>
                            <a href="cetak_kartu.php?id=<?php echo $pinjaman['id']; ?>" class="btn btn-outline-primary me-2" target="_blank">
                                <i class="bi bi-printer"></i> Cetak Kartu
                            </a>
                        <?php endif; ?>
                        <?php if ($pinjaman['status'] === 'aktif'): ?>
                            <a href="../angsuran/bayar.php?pinjaman_id=<?php echo $pinjaman['id']; ?>" class="btn btn-success me-2">
                                <i class="bi bi-cash"></i> Bayar Angsuran
                            </a>
                            <?php if (hasPermission('manage_pembayaran')): ?>
                            <button class="btn btn-outline-warning me-2" onclick="modalLunasDipercepat(<?= $pinjaman['id'] ?>)">
                                <i class="bi bi-lightning"></i> Lunas Dipercepat
                            </button>
                            <?php endif; ?>
                            <?php if (hasPermission('pinjaman.approve')): ?>
                            <button class="btn btn-outline-info me-2" onclick="modalRestrukturisasi(<?= $pinjaman['id'] ?>)">
                                <i class="bi bi-arrow-repeat"></i> Restrukturisasi
                            </button>
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php if ($pinjaman['status'] === 'macet' && getCurrentUser()['role'] === 'bos'): ?>
                            <button class="btn btn-outline-danger me-2" onclick="modalWriteOff(<?= $pinjaman['id'] ?>)">
                                <i class="bi bi-trash"></i> Write-Off
                            </button>
                        <?php endif; ?>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                
                <!-- Loan Information -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="bi bi-info-circle"></i> Informasi Pinjaman</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Kode Pinjaman:</strong></td>
                                        <td><?php echo $pinjaman['kode_pinjaman']; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Nasabah:</strong></td>
                                        <td>
                                            <a href="../nasabah/detail.php?id=<?php echo $pinjaman['nasabah_id']; ?>">
                                                <?php echo $pinjaman['nama']; ?> (<?php echo $pinjaman['kode_nasabah']; ?>)
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Plafon:</strong></td>
                                        <td><?php echo formatRupiah($pinjaman['plafon']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Frekuensi:</strong></td>
                                        <td>
                                            <?php
                                            $frek = $pinjaman['frekuensi_id'] ?? $pinjaman['frekuensi'] ?? 'bulanan';
                                            echo getFrequencyLabel($frek);
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tenor:</strong></td>
                                        <td><?php echo $pinjaman['tenor']; ?> <?php echo getFrequencyPeriodLabel($frek); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Bunga/Bulan:</strong></td>
                                        <td><?php echo $pinjaman['bunga_per_bulan']; ?>%</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Total Pembayaran:</strong></td>
                                        <td><?php echo formatRupiah($pinjaman['total_pembayaran']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Angsuran/<?php echo getFrequencyPeriodLabel($frek); ?>:</strong></td>
                                        <td><?php echo formatRupiah($pinjaman['angsuran_total']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Status:</strong></td>
                                        <td>
                                            <?php
                                            $status_class = [
                                                'pengajuan' => 'info',
                                                'disetujui' => 'warning',
                                                'aktif' => 'success',
                                                'lunas' => 'secondary',
                                                'ditolak' => 'danger'
                                            ];
                                            ?>
                                            <span class="badge bg-<?php echo $status_class[$pinjaman['status']]; ?>">
                                                <?php echo ucfirst($pinjaman['status']); ?>
                                            </span>
                                            <?php
                                            $kol = (int)($pinjaman['kolektibilitas'] ?? 1);
                                            $kol_label = ['','Lancar','Dalam Perhatian Khusus','Kurang Lancar','Diragukan','Macet'];
                                            $kol_color = ['','success','warning','orange','danger','dark'];
                                            if ($kol > 1): ?>
                                            <span class="badge ms-1" style="background:<?= $kol >= 3 ? ($kol >= 4 ? '#dc3545' : '#fd7e14') : '#ffc107' ?>;color:#fff;" title="Kolektibilitas OJK">
                                                Kol-<?= $kol ?> <?= $kol_label[$kol] ?? '' ?>
                                            </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php if (($pinjaman['hari_tunggakan'] ?? 0) > 0): ?>
                                    <tr>
                                        <td><strong>Hari Tunggakan:</strong></td>
                                        <td><span class="text-danger fw-bold"><?= (int)$pinjaman['hari_tunggakan'] ?> hari</span></td>
                                    </tr>
                                    <?php endif; ?>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="bi bi-calendar"></i> Informasi Tanggal</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Tanggal Akad:</strong></td>
                                        <td><?php echo formatDate($pinjaman['tanggal_akad']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Jatuh Tempo:</strong></td>
                                        <td><?php echo formatDate($pinjaman['tanggal_jatuh_tempo']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Petugas:</strong></td>
                                        <td><?php echo $pinjaman['petugas_nama']; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tujuan Pinjaman:</strong></td>
                                        <td><?php echo $pinjaman['tujuan_pinjaman'] ?: '-'; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tipe Jaminan:</strong></td>
                                        <td>
                                            <?php 
                                            $jaminan_labels = ['tanpa'=>'Tanpa Jaminan','bpkb'=>'BPKB Kendaraan','shm'=>'SHM','ajb'=>'AJB','tabungan'=>'Tabungan/Deposito'];
                                            $jt = $pinjaman['jaminan_tipe'] ?? 'tanpa';
                                            $jt_color = ['tanpa'=>'secondary','bpkb'=>'info','shm'=>'success','ajb'=>'primary','tabungan'=>'warning'];
                                            ?>
                                            <span class="badge bg-<?php echo $jt_color[$jt] ?? 'secondary'; ?>"><?php echo $jaminan_labels[$jt] ?? '-'; ?></span>
                                        </td>
                                    </tr>
                                    <?php if (($pinjaman['jaminan_nilai'] ?? 0) > 0): ?>
                                    <tr>
                                        <td><strong>Nilai Jaminan:</strong></td>
                                        <td><?php echo formatRupiah($pinjaman['jaminan_nilai']); ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <tr>
                                        <td><strong>Ket. Jaminan:</strong></td>
                                        <td><?php echo $pinjaman['jaminan'] ?: '-'; ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Progress Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><i class="bi bi-graph-up"></i> Progress Pembayaran</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <h4><?php echo $stats['total_angsuran']; ?></h4>
                                <p class="text-muted">Total Angsuran</p>
                            </div>
                            <div class="col-md-3">
                                <h4 class="text-success"><?php echo $stats['lunas']; ?></h4>
                                <p class="text-muted">Lunas</p>
                            </div>
                            <div class="col-md-3">
                                <h4 class="text-warning"><?php echo $stats['telat']; ?></h4>
                                <p class="text-muted">Telat</p>
                            </div>
                            <div class="col-md-3">
                                <h4 class="text-danger"><?php echo $stats['belum']; ?></h4>
                                <p class="text-muted">Belum Bayar</p>
                            </div>
                        </div>
                        
                        <div class="progress mt-3" style="height: 25px;">
                            <?php
                            $progress = ($stats['total_angsuran'] > 0) ? ($stats['lunas'] / $stats['total_angsuran']) * 100 : 0;
                            ?>
                            <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $progress; ?>%">
                                <?php echo number_format($progress, 1); ?>% Lunas
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <strong>Total Dibayar:</strong> <?php echo formatRupiah($stats['total_dibayar']); ?>
                            <?php if ($stats['total_denda'] > 0): ?>
                                <br>
                                <strong>Total Denda:</strong> <span class="text-danger"><?php echo formatRupiah($stats['total_denda']); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Installment Schedule -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><i class="bi bi-calendar-check"></i> Jadwal Angsuran</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Jatuh Tempo</th>
                                        <th>Pokok</th>
                                        <th>Bunga</th>
                                        <th>Total</th>
                                        <th>Denda</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($angsuran as $a): ?>
                                        <tr>
                                            <td><?php echo $a['no_angsuran']; ?></td>
                                            <td><?php echo formatDate($a['jatuh_tempo']); ?></td>
                                            <td><?php echo formatRupiah($a['pokok']); ?></td>
                                            <td><?php echo formatRupiah($a['bunga']); ?></td>
                                            <td><?php echo formatRupiah($a['total_angsuran']); ?></td>
                                            <td>
                                                <?php if ($a['denda'] > 0): ?>
                                                    <span class="text-danger"><?php echo formatRupiah($a['denda']); ?></span>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $status_class = [
                                                    'belum' => 'warning',
                                                    'lunas' => 'success',
                                                    'telat' => 'danger'
                                                ];
                                                ?>
                                                <span class="badge bg-<?php echo $status_class[$a['status']]; ?>">
                                                    <?php echo ucfirst($a['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($a['status'] !== 'lunas' && $pinjaman['status'] === 'aktif'): ?>
                                                    <a href="../angsuran/bayar.php?id=<?php echo $a['id']; ?>" class="btn btn-sm btn-success">
                                                        <i class="bi bi-cash"></i> Bayar
                                                    </a>
                                                <?php elseif ($a['status'] === 'lunas'): ?>
                                                    <a href="cetak_kwitansi.php?angsuran_id=<?php echo $a['id']; ?>" class="btn btn-sm btn-outline-primary" target="_blank" title="Cetak Kwitansi">
                                                        <i class="bi bi-printer"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Payment History -->
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-clock-history"></i> Riwayat Pembayaran</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($pembayaran)): ?>
                            <p class="text-muted">Belum ada riwayat pembayaran</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Tanggal Bayar</th>
                                            <th>Angsuran Ke</th>
                                            <th>Jumlah</th>
                                            <th>Denda</th>
                                            <th>Total</th>
                                            <th>Cara Bayar</th>
                                            <th>Petugas</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pembayaran as $p): ?>
                                            <tr>
                                                <td><?php echo formatDate($p['tanggal_bayar']); ?></td>
                                                <td><?php echo $p['no_angsuran']; ?></td>
                                                <td><?php echo formatRupiah($p['jumlah_bayar']); ?></td>
                                                <td><?php echo formatRupiah($p['denda']); ?></td>
                                                <td><?php echo formatRupiah($p['total_bayar']); ?></td>
                                                <td><?php echo ucfirst($p['cara_bayar']); ?></td>
                                                <td>
                                                    <?php 
                                                    $petugas = query("SELECT nama FROM users WHERE id = ?", [$p['petugas_id']]);
                                                    echo $petugas[0]['nama'] ?? '-';
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    const API_BUSINESS = '<?= baseUrl('api/business.php') ?>';

    // Pelunasan Dipercepat
    async function modalLunasDipercepat(pinjaman_id) {
        const res = await fetch(API_BUSINESS + '?action=hitung_lunas_dipercepat&pinjaman_id=' + pinjaman_id);
        const d   = await res.json();
        if (d.error) { Swal.fire('Error', d.error, 'error'); return; }

        const html = `
            <table class="table table-sm text-start">
                <tr><td>Sisa Pokok</td><td class="text-end fw-bold">Rp ${d.sisa_pokok.toLocaleString('id-ID')}</td></tr>
                <tr><td>Bunga Sisa (normal)</td><td class="text-end">Rp ${d.bunga_sisa_normal.toLocaleString('id-ID')}</td></tr>
                <tr><td>Diskon Bunga (50%)</td><td class="text-end text-success">- Rp ${d.diskon_bunga.toLocaleString('id-ID')}</td></tr>
                <tr><td>Denda Terhutang</td><td class="text-end text-danger">Rp ${d.denda_terhitung ? d.denda_terhitung.toLocaleString('id-ID') : 0}</td></tr>
                <tr class="fw-bold table-primary"><td>Total Harus Dibayar</td><td class="text-end">Rp ${d.total_harus_dibayar.toLocaleString('id-ID')}</td></tr>
            </table>
            <select class="form-select mt-2" id="cara_bayar_lunas">
                <option value="tunai">Tunai</option>
                <option value="transfer">Transfer</option>
                <option value="digital">Digital</option>
            </select>`;

        const result = await Swal.fire({
            title: 'Pelunasan Dipercepat', html, icon: 'info',
            showCancelButton: true, confirmButtonText: 'Proses Sekarang', cancelButtonText: 'Batal',
            confirmButtonColor: '#198754'
        });
        if (!result.isConfirmed) return;

        const btn = Swal.showLoading();
        const resp = await fetch(API_BUSINESS, { method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({ action: 'lunas_dipercepat', pinjaman_id, confirm: true,
                cara_bayar: document.getElementById('cara_bayar_lunas')?.value || 'tunai' }) });
        const r = await resp.json();
        if (r.success) { Swal.fire('Berhasil', 'Pinjaman berhasil dilunasi.', 'success').then(() => location.reload()); }
        else { Swal.fire('Gagal', r.error || 'Terjadi kesalahan', 'error'); }
    }

    // Restrukturisasi
    async function modalRestrukturisasi(pinjaman_id) {
        const { value: formValues } = await Swal.fire({
            title: 'Restrukturisasi Pinjaman',
            html: `
                <div class="text-start">
                <div class="mb-2">
                    <label class="form-label">Tipe Restrukturisasi</label>
                    <select class="form-select" id="rst_tipe">
                        <option value="reschedule">Reschedule — Perpanjang Tenor</option>
                        <option value="reconditioning">Reconditioning — Turunkan Bunga</option>
                        <option value="restructuring">Restructuring — Kombinasi</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label">Alasan</label>
                    <select class="form-select" id="rst_alasan">
                        <option value="kesulitan_keuangan">Kesulitan Keuangan</option>
                        <option value="sakit">Sakit</option>
                        <option value="usaha_merugi">Usaha Merugi</option>
                        <option value="bencana_alam">Bencana Alam</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label">Tenor Baru (angsuran)</label>
                    <input type="number" class="form-control" id="rst_tenor" min="1" placeholder="Kosongkan jika tidak diubah">
                </div>
                <div class="mb-2">
                    <label class="form-label">Bunga Baru (%/bulan)</label>
                    <input type="number" class="form-control" id="rst_bunga" step="0.01" placeholder="Kosongkan jika tidak diubah">
                </div>
                <div class="mb-2">
                    <label class="form-label">Denda Dibebaskan (Rp)</label>
                    <input type="number" class="form-control" id="rst_denda" value="0">
                </div>
                <div class="mb-2">
                    <label class="form-label">Tanggal Efektif</label>
                    <input type="date" class="form-control" id="rst_tgl" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="mb-2">
                    <label class="form-label">Catatan</label>
                    <textarea class="form-control" id="rst_catatan" rows="2"></textarea>
                </div></div>`,
            showCancelButton: true, confirmButtonText: 'Simpan', cancelButtonText: 'Batal',
            confirmButtonColor: '#0dcaf0',
            preConfirm: () => ({
                tipe: document.getElementById('rst_tipe').value,
                alasan: document.getElementById('rst_alasan').value,
                tenor_baru: document.getElementById('rst_tenor').value || null,
                bunga_baru: document.getElementById('rst_bunga').value || null,
                denda_dibebaskan: document.getElementById('rst_denda').value || 0,
                tanggal_efektif: document.getElementById('rst_tgl').value,
                catatan: document.getElementById('rst_catatan').value,
            })
        });
        if (!formValues) return;
        const resp = await fetch(API_BUSINESS, { method: 'POST', headers: {'Content-Type':'application/json'},
            body: JSON.stringify({ action: 'restrukturisasi', pinjaman_id, ...formValues }) });
        const r = await resp.json();
        if (r.success) { Swal.fire('Berhasil', 'Restrukturisasi berhasil disimpan.', 'success').then(() => location.reload()); }
        else { Swal.fire('Gagal', r.error || 'Terjadi kesalahan', 'error'); }
    }

    // Write-Off
    async function modalWriteOff(pinjaman_id) {
        const { value: formValues } = await Swal.fire({
            title: 'Write-Off Pinjaman Macet',
            icon: 'warning',
            html: `<div class="text-start">
                <div class="alert alert-danger">Tindakan ini akan menghapusbukukan pinjaman macet dan tidak dapat dibatalkan. Pastikan semua upaya penagihan sudah dilakukan.</div>
                <div class="mb-2">
                    <label class="form-label">Alasan Write-Off</label>
                    <select class="form-select" id="wo_alasan">
                        <option value="meninggal">Nasabah Meninggal</option>
                        <option value="bangkrut">Usaha Bangkrut</option>
                        <option value="kabur">Nasabah Kabur / Tidak Diketahui Alamat</option>
                        <option value="tidak_ditemukan">Tidak Dapat Dihubungi</option>
                        <option value="force_majeure">Force Majeure</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label">Upaya Penagihan yang Sudah Dilakukan</label>
                    <textarea class="form-control" id="wo_upaya" rows="3" placeholder="Dokumentasikan upaya penagihan: kunjungan, surat peringatan, dll" required></textarea>
                </div>
                <div class="mb-2">
                    <label class="form-label">Status Aset Jaminan</label>
                    <select class="form-select" id="wo_aset">
                        <option value="tidak_ada">Tidak Ada Jaminan</option>
                        <option value="jaminan_ada">Ada Jaminan (Belum Diproses)</option>
                        <option value="sedang_diproses">Sedang Diproses</option>
                        <option value="sudah_disita">Sudah Disita/Dijual</option>
                    </select>
                </div>
            </div>`,
            showCancelButton: true, confirmButtonText: 'Write-Off Sekarang', cancelButtonText: 'Batal',
            confirmButtonColor: '#dc3545',
            preConfirm: () => {
                const upaya = document.getElementById('wo_upaya').value.trim();
                if (!upaya) { Swal.showValidationMessage('Dokumentasi upaya penagihan wajib diisi'); return false; }
                return { alasan: document.getElementById('wo_alasan').value, upaya_penagihan: upaya, status_aset: document.getElementById('wo_aset').value };
            }
        });
        if (!formValues) return;
        const resp = await fetch(API_BUSINESS, { method: 'POST', headers: {'Content-Type':'application/json'},
            body: JSON.stringify({ action: 'write_off', pinjaman_id, ...formValues }) });
        const r = await resp.json();
        if (r.success) { Swal.fire('Write-Off Berhasil', `Kerugian dicatat: Rp ${r.total_kerugian?.toLocaleString('id-ID')}`, 'success').then(() => location.reload()); }
        else { Swal.fire('Gagal', r.error || 'Terjadi kesalahan', 'error'); }
    }
    </script>
</body>
</html>
