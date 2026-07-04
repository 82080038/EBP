<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();

$pembayaran_id = $_GET['pembayaran_id'] ?? null;
$angsuran_id = $_GET['angsuran_id'] ?? null;

if (!$pembayaran_id && !$angsuran_id) {
    die('Parameter tidak valid');
}

// Get payment data
if ($pembayaran_id) {
    $pembayaran = query("
        SELECT pb.*, a.no_angsuran, a.jatuh_tempo, a.pokok, a.bunga, a.total_angsuran, a.denda,
               p.kode_pinjaman, p.plafon, p.tenor, p.frekuensi_id, p.nasabah_id,
               n.nama as nama_nasabah, n.kode_nasabah, n.alamat, n.telp,
               c.nama_cabang, c.alamat as alamat_cabang, c.telp as telp_cabang,
               u.nama as nama_petugas
        FROM pembayaran pb
        JOIN angsuran a ON pb.angsuran_id = a.id
        JOIN pinjaman p ON pb.pinjaman_id = p.id
        JOIN nasabah n ON p.nasabah_id = n.id
        LEFT JOIN cabang c ON p.cabang_id = c.id
        LEFT JOIN users u ON pb.petugas_id = u.id
        WHERE pb.id = ?
    ", [$pembayaran_id]);
} else {
    $pembayaran = query("
        SELECT a.id as angsuran_id, a.pinjaman_id, a.no_angsuran, a.jatuh_tempo, a.pokok, a.bunga, 
               a.total_angsuran, a.denda, a.total_bayar, a.tanggal_bayar, a.status, a.cara_bayar,
               p.kode_pinjaman, p.plafon, p.tenor, p.frekuensi_id, p.nasabah_id,
               n.nama as nama_nasabah, n.kode_nasabah, n.alamat, n.telp,
               c.nama_cabang, c.alamat as alamat_cabang, c.telp as telp_cabang
        FROM angsuran a
        JOIN pinjaman p ON a.pinjaman_id = p.id
        JOIN nasabah n ON p.nasabah_id = n.id
        LEFT JOIN cabang c ON p.cabang_id = c.id
        WHERE a.id = ? AND a.status = 'lunas'
    ", [$angsuran_id]);
}

if (!$pembayaran || empty($pembayaran)) {
    die('Data pembayaran tidak ditemukan');
}
$data = $pembayaran[0];
$frek = $data['frekuensi_id'] ?? $data['frekuensi'] ?? 'bulanan';

// Get app settings
$settings = query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('app_name', 'company_name')");
$app_settings = [];
if (is_array($settings)) {
    foreach ($settings as $s) {
        $app_settings[$s['setting_key']] = $s['setting_value'];
    }
}
$company_name = $app_settings['company_name'] ?? $app_settings['app_name'] ?? APP_NAME;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kwitansi Pembayaran - <?php echo $data['kode_pinjaman']; ?></title>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; }
        }
        body { font-family: 'Courier New', monospace; font-size: 12px; max-width: 80mm; margin: 0 auto; padding: 10px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-bold { font-weight: bold; }
        .divider { border-top: 1px dashed #000; margin: 8px 0; }
        .divider-double { border-top: 2px double #000; margin: 8px 0; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 2px 0; vertical-align: top; }
        .header { margin-bottom: 10px; }
        .btn-print { 
            display: block; width: 100%; padding: 10px; margin: 15px 0;
            background: #198754; color: white; border: none; border-radius: 4px; 
            cursor: pointer; font-size: 14px;
        }
        .btn-print:hover { background: #157347; }
        .btn-back { 
            display: block; width: 100%; padding: 10px; margin: 5px 0;
            background: #6c757d; color: white; border: none; border-radius: 4px; 
            cursor: pointer; font-size: 14px; text-decoration: none; text-align: center;
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" class="btn-print">🖨 Cetak Kwitansi</button>
        <a href="detail.php?id=<?php echo $data['pinjaman_id'] ?? ''; ?>" class="btn-back">← Kembali</a>
    </div>

    <div class="header text-center">
        <div class="text-bold" style="font-size: 14px;"><?php echo strtoupper($company_name); ?></div>
        <div><?php echo $data['nama_cabang']; ?></div>
        <?php if ($data['alamat_cabang']): ?>
            <div style="font-size: 10px;"><?php echo $data['alamat_cabang']; ?></div>
        <?php endif; ?>
        <?php if ($data['telp_cabang']): ?>
            <div style="font-size: 10px;">Telp: <?php echo $data['telp_cabang']; ?></div>
        <?php endif; ?>
    </div>
    
    <div class="divider-double"></div>
    <div class="text-center text-bold">KWITANSI PEMBAYARAN</div>
    <div class="divider"></div>
    
    <table>
        <tr><td>No. Pinjaman</td><td>: <?php echo $data['kode_pinjaman']; ?></td></tr>
        <tr><td>Nasabah</td><td>: <?php echo $data['nama_nasabah']; ?></td></tr>
        <tr><td>Kode Nasabah</td><td>: <?php echo $data['kode_nasabah']; ?></td></tr>
        <tr><td>Frekuensi</td><td>: <?php echo getFrequencyLabel($frek); ?></td></tr>
    </table>
    
    <div class="divider"></div>
    
    <table>
        <tr><td>Angsuran Ke</td><td class="text-right"><?php echo $data['no_angsuran']; ?> / <?php echo $data['tenor']; ?></td></tr>
        <tr><td>Jatuh Tempo</td><td class="text-right"><?php echo formatDate($data['jatuh_tempo']); ?></td></tr>
        <tr><td>Pokok</td><td class="text-right"><?php echo formatRupiah($data['pokok']); ?></td></tr>
        <tr><td>Bunga</td><td class="text-right"><?php echo formatRupiah($data['bunga']); ?></td></tr>
        <tr><td>Angsuran</td><td class="text-right"><?php echo formatRupiah($data['total_angsuran']); ?></td></tr>
        <?php if ($data['denda'] > 0): ?>
        <tr><td>Denda</td><td class="text-right"><?php echo formatRupiah($data['denda']); ?></td></tr>
        <?php endif; ?>
    </table>
    
    <div class="divider-double"></div>
    
    <table>
        <tr class="text-bold">
            <td>TOTAL BAYAR</td>
            <td class="text-right"><?php echo formatRupiah(($data['total_bayar'] ?? $data['total_angsuran']) + ($data['denda'] ?? 0)); ?></td>
        </tr>
    </table>
    
    <div style="margin-top: 10px; font-size: 10px;">
        <strong>Terbilang:</strong> <?php echo formatRupiahTerbilang(($data['total_bayar'] ?? $data['total_angsuran']) + ($data['denda'] ?? 0)); ?>
    </div>
    
    <table>
        <tr><td>Cara Bayar</td><td class="text-right"><?php echo ucfirst($data['cara_bayar'] ?? 'tunai'); ?></td></tr>
        <tr><td>Tgl Bayar</td><td class="text-right"><?php echo formatDate($data['tanggal_bayar'] ?? date('Y-m-d')); ?></td></tr>
        <?php if (isset($data['nama_petugas'])): ?>
        <tr><td>Petugas</td><td class="text-right"><?php echo $data['nama_petugas']; ?></td></tr>
        <?php endif; ?>
    </table>
    
    <div class="divider"></div>
    <div class="text-center" style="font-size: 10px;">
        Terima kasih atas pembayaran Anda.<br>
        Simpan kwitansi ini sebagai bukti pembayaran.<br>
        <?php echo date('d/m/Y H:i'); ?>
    </div>
</body>
</html>
