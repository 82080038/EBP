<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();

$cabang_id = getCurrentCabang();
$angsuran_id = $_GET['id'] ?? '';

if (!$angsuran_id) {
    die('ID angsuran tidak valid');
}

// Get payment data with all details
$data = query("
    SELECT 
        a.*,
        p.kode_pinjaman, p.frekuensi_id, p.plafon as total_pinjaman,
        n.nama as nasabah_nama, n.telp as nasabah_telp, n.alamat as nasabah_alamat,
        c.nama_cabang as cabang_nama, c.alamat as cabang_alamat, c.telp as cabang_telp,
        byr.kode_pembayaran, byr.tanggal_bayar, byr.cara_bayar as metode,
        u.nama as petugas_nama
    FROM angsuran a
    JOIN pinjaman p ON a.pinjaman_id = p.id
    JOIN nasabah n ON p.nasabah_id = n.id
    JOIN cabang c ON a.cabang_id = c.id
    LEFT JOIN pembayaran byr ON byr.angsuran_id = a.id
    LEFT JOIN users u ON byr.petugas_id = u.id
    WHERE a.id = ? AND a.cabang_id = ? AND a.status = 'lunas'
", [$angsuran_id, $cabang_id]);

if (!$data || empty($data)) {
    die('Data pembayaran tidak ditemukan atau belum lunas');
}

$d = $data[0];

$frek = $d['frekuensi_id'] ?? $d['frekuensi'] ?? 'bulanan';
$period_label = getFrequencyPeriodLabel($frek);

$print_date = date('d/m/Y H:i:s');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kwitansi <?= htmlspecialchars($d['kode_pembayaran'] ?? 'KW-'.time()) ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.4;
            width: 80mm;
            padding: 5mm;
        }
        .center { text-align: center; }
        .right { text-align: right; }
        .bold { font-weight: bold; }
        .border-top { border-top: 1px dashed #000; padding-top: 5px; margin-top: 5px; }
        .border-bottom { border-bottom: 1px dashed #000; padding-bottom: 5px; margin-bottom: 5px; }
        .spacer { height: 5px; }
        .large { font-size: 14px; }
        .kwitansi-box {
            border: 2px solid #000;
            padding: 10px;
            margin: 10px 0;
        }
        @media print {
            body { width: 80mm; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="center">
        <div class="bold large"><?= strtoupper(htmlspecialchars($d['cabang_nama'])) ?></div>
        <div style="font-size: 10px;"><?= htmlspecialchars($d['cabang_alamat']) ?></div>
        <div style="font-size: 10px;">Telp: <?= htmlspecialchars($d['cabang_telp']) ?></div>
    </div>
    
    <div class="spacer"></div>
    <div class="border-top border-bottom center bold large">
        KWITANSI PEMBAYARAN
    </div>
    <div class="spacer"></div>
    
    <!-- Kwitansi Info -->
    <table width="100%">
        <tr>
            <td>No. Kwitansi</td>
            <td class="right bold"><?= htmlspecialchars($d['kode_pembayaran'] ?? '-') ?></td>
        </tr>
        <tr>
            <td>Tanggal</td>
            <td class="right"><?= date('d/m/Y', strtotime($d['tanggal_bayar'] ?? 'now')) ?></td>
        </tr>
        <tr>
            <td>Jam</td>
            <td class="right"><?= date('H:i', strtotime($d['tanggal_bayar'] ?? 'now')) ?> WIB</td>
        </tr>
    </table>
    
    <div class="spacer"></div>
    <div class="border-top"></div>
    <div class="spacer"></div>
    
    <!-- Nasabah Info -->
    <div class="bold">Diterima Dari:</div>
    <div><?= htmlspecialchars($d['nasabah_nama']) ?></div>
    <div style="font-size: 10px;"><?= htmlspecialchars($d['nasabah_telp']) ?></div>
    
    <div class="spacer"></div>
    
    <!-- Pinjaman Info -->
    <table width="100%">
        <tr>
            <td>No. Pinjaman</td>
            <td class="right"><?= htmlspecialchars($d['kode_pinjaman']) ?></td>
        </tr>
        <tr>
            <td>Angsuran Ke</td>
            <td class="right bold"><?= $d['no_angsuran'] ?> / <?= $d['frekuensi'] ?></td>
        </tr>
    </table>
    
    <div class="spacer"></div>
    <div class="border-top"></div>
    <div class="spacer"></div>
    
    <!-- Payment Details -->
    <div class="bold">Rincian Pembayaran:</div>
    <table width="100%">
        <tr>
            <td>Pokok</td>
            <td class="right">Rp <?= formatRupiah($d['pokok']) ?></td>
        </tr>
        <tr>
            <td>Bunga</td>
            <td class="right">Rp <?= formatRupiah($d['bunga']) ?></td>
        </tr>
        <?php if ($d['denda_terhitung'] > 0): ?>
        <tr>
            <td>Denda</td>
            <td class="right">Rp <?= formatRupiah($d['denda_terhitung']) ?></td>
        </tr>
        <?php endif; ?>
        <?php if ($d['denda_dibebaskan'] > 0): ?>
        <tr style="color: #28a745;">
            <td>Denda Dibebaskan</td>
            <td class="right">- Rp <?= formatRupiah($d['denda_dibebaskan']) ?></td>
        </tr>
        <?php endif; ?>
    </table>
    
    <div class="border-top"></div>
    
    <!-- Total -->
    <table width="100%">
        <tr class="large">
            <td class="bold">TOTAL BAYAR</td>
            <td class="right bold">Rp <?= formatRupiah($d['total_bayar_akhir'] ?? $d['total_angsuran']) ?></td>
        </tr>
    </table>
    
    <div class="kwitansi-box center">
        <div style="font-size: 10px;">Terbilang:</div>
        <div class="bold" style="font-style: italic;">
            <?= ucwords(str_replace(['0','1','2','3','4','5','6','7','8','9'], ['nol','satu','dua','tiga','empat','lima','enam','tujuh','delapan','sembilan'], formatRupiah($d['total_bayar_akhir'] ?? $d['total_angsuran']))) ?> Rupiah
        </div>
    </div>
    
    <div class="spacer"></div>
    <div class="border-top"></div>
    <div class="spacer"></div>
    
    <!-- Footer -->
    <table width="100%">
        <tr>
            <td width="50%" class="center">
                <div>Disetor Oleh,</div>
                <div style="height: 40px;"></div>
                <div class="bold"><?= htmlspecialchars($d['nasabah_nama']) ?></div>
            </td>
            <td width="50%" class="center">
                <div>Diterima Oleh,</div>
                <div style="height: 40px;"></div>
                <div class="bold"><?= htmlspecialchars($d['petugas_nama'] ?? $_SESSION['user']['nama'] ?? 'Petugas') ?></div>
            </td>
        </tr>
    </table>
    
    <div class="spacer"></div>
    <div class="border-top"></div>
    <div class="spacer"></div>
    
    <!-- Notes -->
    <div style="font-size: 9px; text-align: center;">
        <div>Terima kasih atas pembayaran Anda</div>
        <div>Simpan kwitansi ini sebagai bukti pembayaran</div>
        <div>--- <?= $print_date ?> ---</div>
    </div>
    
    <!-- Print Button -->
    <div class="no-print" style="margin-top: 20px; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 14px;">
            <i class="bi bi-printer"></i> Cetak Kwitansi
        </button>
        <a href="index.php" style="padding: 10px 20px; font-size: 14px; margin-left: 10px;">
            Kembali
        </a>
    </div>
    
    <script>
        // Auto print on load (optional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
