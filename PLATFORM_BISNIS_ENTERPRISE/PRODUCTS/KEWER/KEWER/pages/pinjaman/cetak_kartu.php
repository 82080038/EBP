<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();

$user = getCurrentUser();
$user_cabang_id = $user['cabang_id'] ?? null;
$kantor_id = $user_cabang_id ?? 1; // Use user's cabang_id or default to 1

$pinjaman_id = $_GET['id'] ?? null;
if (!$pinjaman_id) {
    die('Parameter tidak valid');
}

// Get loan data
$pinjaman = query("
    SELECT p.*, n.nama as nama_nasabah, n.kode_nasabah, n.alamat, n.telp, n.ktp,
           c.nama_cabang, c.alamat as alamat_cabang, c.telp as telp_cabang
    FROM pinjaman p
    JOIN nasabah n ON p.nasabah_id = n.id
    LEFT JOIN cabang c ON p.cabang_id = c.id
    WHERE p.id = ?
", [$pinjaman_id]);

if (!$pinjaman || empty($pinjaman)) {
    die('Data pinjaman tidak ditemukan');
}
$loan = $pinjaman[0];
$frek = $loan['frekuensi_id'] ?? $loan['frekuensi'] ?? 'bulanan';

// Get installment schedule
$angsuran = query("
    SELECT * FROM angsuran
    WHERE pinjaman_id = ?
    ORDER BY no_angsuran ASC
", [$pinjaman_id]);
if (!is_array($angsuran)) $angsuran = [];

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
    <title>Kartu Angsuran - <?php echo $loan['kode_pinjaman']; ?></title>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; padding: 10px; }
            .page-break { page-break-after: always; }
        }
        body { font-family: Arial, sans-serif; font-size: 11px; max-width: 210mm; margin: 0 auto; padding: 15px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-bold { font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .info-table td { padding: 3px 5px; }
        .schedule-table th, .schedule-table td { 
            border: 1px solid #333; padding: 4px 6px; text-align: center; font-size: 10px;
        }
        .schedule-table th { background: #333; color: white; }
        .schedule-table tr:nth-child(even) { background: #f5f5f5; }
        .status-lunas { color: green; font-weight: bold; }
        .status-telat { color: red; font-weight: bold; }
        .status-belum { color: #666; }
        .header { border: 2px solid #333; padding: 10px; margin-bottom: 15px; }
        .header h2 { margin: 0 0 5px 0; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 3px; font-size: 10px; font-weight: bold; }
        .badge-harian { background: #ffc107; color: #000; }
        .badge-mingguan { background: #0dcaf0; color: #000; }
        .badge-bulanan { background: #0d6efd; color: #fff; }
        .btn-print { 
            display: inline-block; padding: 10px 20px; margin: 10px 5px;
            background: #198754; color: white; border: none; border-radius: 4px; 
            cursor: pointer; font-size: 14px; text-decoration: none;
        }
        .btn-back { 
            display: inline-block; padding: 10px 20px; margin: 10px 5px;
            background: #6c757d; color: white; border: none; border-radius: 4px; 
            cursor: pointer; font-size: 14px; text-decoration: none;
        }
        .summary-box { border: 1px solid #333; padding: 8px; margin-top: 10px; }
        .sign-area { display: flex; justify-content: space-between; margin-top: 30px; }
        .sign-box { text-align: center; width: 45%; }
        .sign-line { border-bottom: 1px solid #333; margin-top: 60px; padding-bottom: 5px; }
    </style>
</head>
<body>
    <div class="no-print text-center">
        <button onclick="window.print()" class="btn-print">🖨 Cetak Kartu Angsuran</button>
        <a href="detail.php?id=<?php echo $pinjaman_id; ?>" class="btn-back">← Kembali</a>
    </div>

    <div class="header">
        <table>
            <tr>
                <td style="width: 70%;">
                    <h2><?php echo strtoupper($company_name); ?></h2>
                    <div><?php echo $loan['nama_cabang']; ?></div>
                    <?php if ($loan['alamat_cabang']): ?>
                        <div style="font-size: 10px;"><?php echo $loan['alamat_cabang']; ?></div>
                    <?php endif; ?>
                </td>
                <td class="text-right" style="vertical-align: top;">
                    <div class="text-bold" style="font-size: 14px;">KARTU ANGSURAN</div>
                    <div class="badge badge-<?php echo $frek; ?>"><?php echo getFrequencyLabel($frek); ?></div>
                </td>
            </tr>
        </table>
    </div>

    <table class="info-table">
        <tr>
            <td style="width:50%">
                <table>
                    <tr><td><strong>No. Pinjaman</strong></td><td>: <?php echo $loan['kode_pinjaman']; ?></td></tr>
                    <tr><td><strong>Nasabah</strong></td><td>: <?php echo $loan['nama_nasabah']; ?></td></tr>
                    <tr><td><strong>Kode Nasabah</strong></td><td>: <?php echo $loan['kode_nasabah']; ?></td></tr>
                    <tr><td><strong>Alamat</strong></td><td>: <?php echo $loan['alamat'] ?? '-'; ?></td></tr>
                    <tr><td><strong>Telepon</strong></td><td>: <?php echo $loan['telp']; ?></td></tr>
                </table>
            </td>
            <td>
                <table>
                    <tr><td><strong>Plafon</strong></td><td>: <?php echo formatRupiah($loan['plafon']); ?></td></tr>
                    <tr><td><strong>Tenor</strong></td><td>: <?php echo $loan['tenor']; ?> <?php echo getFrequencyPeriodLabel($frek); ?></td></tr>
                    <tr><td><strong>Bunga/Bulan</strong></td><td>: <?php echo $loan['bunga_per_bulan']; ?>%</td></tr>
                    <tr><td><strong>Angsuran/<?php echo getFrequencyPeriodLabel($frek); ?></strong></td><td>: <?php echo formatRupiah($loan['angsuran_total']); ?></td></tr>
                    <tr><td><strong>Tanggal Akad</strong></td><td>: <?php echo formatDate($loan['tanggal_akad']); ?></td></tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="schedule-table">
        <thead>
            <tr>
                <th style="width:5%">No</th>
                <th style="width:15%">Jatuh Tempo</th>
                <th style="width:15%">Pokok</th>
                <th style="width:12%">Bunga</th>
                <th style="width:15%">Angsuran</th>
                <th style="width:10%">Denda</th>
                <th style="width:13%">Status</th>
                <th style="width:15%">Tgl Bayar</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $total_pokok = 0;
            $total_bunga = 0;
            $total_angsuran = 0;
            $total_denda = 0;
            $total_dibayar = 0;
            foreach ($angsuran as $a): 
                $total_pokok += $a['pokok'];
                $total_bunga += $a['bunga'];
                $total_angsuran += $a['total_angsuran'];
                $total_denda += $a['denda'];
                if ($a['status'] === 'lunas') $total_dibayar += ($a['total_bayar'] ?: $a['total_angsuran']);
            ?>
            <tr>
                <td><?php echo $a['no_angsuran']; ?></td>
                <td><?php echo formatDate($a['jatuh_tempo'], 'd/m/Y'); ?></td>
                <td class="text-right"><?php echo formatRupiah($a['pokok']); ?></td>
                <td class="text-right"><?php echo formatRupiah($a['bunga']); ?></td>
                <td class="text-right"><?php echo formatRupiah($a['total_angsuran']); ?></td>
                <td class="text-right"><?php echo $a['denda'] > 0 ? formatRupiah($a['denda']) : '-'; ?></td>
                <td>
                    <?php if ($a['status'] === 'lunas'): ?>
                        <span class="status-lunas">✓ Lunas</span>
                    <?php elseif ($a['status'] === 'telat'): ?>
                        <span class="status-telat">✗ Telat</span>
                    <?php else: ?>
                        <span class="status-belum">○ Belum</span>
                    <?php endif; ?>
                </td>
                <td><?php echo $a['tanggal_bayar'] ? formatDate($a['tanggal_bayar'], 'd/m/Y') : '-'; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr style="font-weight: bold; background: #e9ecef;">
                <td colspan="2">TOTAL</td>
                <td class="text-right"><?php echo formatRupiah($total_pokok); ?></td>
                <td class="text-right"><?php echo formatRupiah($total_bunga); ?></td>
                <td class="text-right"><?php echo formatRupiah($total_angsuran); ?></td>
                <td class="text-right"><?php echo $total_denda > 0 ? formatRupiah($total_denda) : '-'; ?></td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>

    <div class="summary-box">
        <table>
            <tr>
                <td><strong>Total Pinjaman:</strong> <?php echo formatRupiah($loan['total_pembayaran']); ?></td>
            </tr>
            <tr>
                <td><strong>Total Dibayar:</strong> <?php echo formatRupiah($total_dibayar); ?></td>
            </tr>
            <tr>
                <td><strong>Sisa:</strong> <?php echo formatRupiah($loan['total_pembayaran'] - $total_dibayar); ?></td>
            </tr>
        </table>
        
        <div style="margin-top: 10px; font-size: 10px;">
            <strong>Terbilang:</strong> <?php echo formatRupiahTerbilang($loan['total_pembayaran']); ?>
        </div>
    </div>

    <div class="sign-area">
        <div class="sign-box">
            <div>Nasabah</div>
            <div class="sign-line">(<?php echo $loan['nama_nasabah']; ?>)</div>
        </div>
        <div class="sign-box">
            <div>Petugas</div>
            <div class="sign-line">(____________________)</div>
        </div>
    </div>

    <div class="text-center" style="margin-top: 20px; font-size: 9px; color: #999;">
        Dicetak pada: <?php echo date('d/m/Y H:i'); ?> | <?php echo $company_name; ?>
    </div>
</body>
</html>
