<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();

$cabang_id = getCurrentCabang();
$angsuran_id = $_GET['id'] ?? '';

if (!$angsuran_id) {
    header('Location: index.php');
    exit;
}

// Get angsuran data with denda calculation
$angsuran = query("
    SELECT 
        a.*, 
        p.kode_pinjaman, p.frekuensi_id, p.nasabah_id,
        n.nama as nasabah_nama, n.telp as nasabah_telp, n.alamat,
        DATEDIFF(CURDATE(), a.jatuh_tempo) as hari_telat_sekarang,
        COALESCE(
            (SELECT 
                CASE 
                    WHEN sd.tipe_denda = 'persentase' THEN
                        (a.total_angsuran * sd.nilai_denda / 100) * 
                        CASE 
                            WHEN p.frekuensi_id = 1 THEN 1
                            WHEN p.frekuensi_id = 2 THEN 1/7
                            ELSE 1/30
                        END * GREATEST(0, DATEDIFF(CURDATE(), a.jatuh_tempo) - COALESCE(sd.grace_period, 0))
                    ELSE
                        sd.nilai_denda * GREATEST(0, DATEDIFF(CURDATE(), a.jatuh_tempo) - COALESCE(sd.grace_period, 0))
                END
            FROM setting_denda sd 
            WHERE sd.frekuensi_id = p.frekuensi_id AND sd.is_active = 1 
            AND (sd.cabang_id = a.cabang_id OR sd.cabang_id IS NULL)
            ORDER BY sd.cabang_id DESC LIMIT 1),
            0
        ) as denda_terhitung,
        COALESCE(
            (SELECT COALESCE(sd.grace_period, 0)
            FROM setting_denda sd 
            WHERE sd.frekuensi_id = p.frekuensi_id AND sd.is_active = 1 
            AND (sd.cabang_id = a.cabang_id OR sd.cabang_id IS NULL)
            ORDER BY sd.cabang_id DESC LIMIT 1),
            0
        ) as grace_period
    FROM angsuran a
    JOIN pinjaman p ON a.pinjaman_id = p.id
    JOIN nasabah n ON p.nasabah_id = n.id
    WHERE a.id = ? AND a.cabang_id = ?
", [$angsuran_id, $cabang_id]);

if (!$angsuran || empty($angsuran)) {
    header('Location: index.php?error=not_found');
    exit;
}

$a = $angsuran[0];

// Check if already paid
if ($a['status'] == 'lunas') {
    header('Location: index.php?error=already_paid');
    exit;
}

// Calculate denda
$hari_telat = max(0, $a['hari_telat_sekarang'] ?? 0);
$grace = $a['grace_period'] ?? 0;
$hari_telat_efektif = max(0, $hari_telat - $grace);

// Use frekuensi_id only (enum column dropped in migration 024)
$frekuensi_value = $a['frekuensi_id'];
$frekuensi_code = getFrequencyCode($frekuensi_value);

if ($frekuensi_code == 'HARIAN' || $frekuensi_code == 'harian') {
    $denda_per_hari = ($a['total_angsuran'] * ($a['denda_rate'] ?? 0.5)) / 100;
} elseif ($frekuensi_code == 'MINGGUAN' || $frekuensi_code == 'mingguan') {
    $denda_per_hari = ($a['total_angsuran'] * ($a['denda_rate'] ?? 2.0)) / 100 / 7;
} else {
    $denda_per_hari = ($a['total_angsuran'] * ($a['denda_rate'] ?? 5.0)) / 100 / 30;
}

$denda_terhitung = $hari_telat_efektif * $denda_per_hari;
$total_tagihan = $a['total_angsuran'] + $denda_terhitung;

// Get payment methods
$metode_list = ['Tunai', 'Transfer Bank', 'QRIS', 'Debit', 'Lainnya'];

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bayar Angsuran - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .tagihan-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .denda-card { background: #fff3cd; }
        .waive-section { display: none; }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand" href="../../dashboard.php"><i class="bi bi-bank"></i> <?= APP_NAME ?></a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="index.php"><i class="bi bi-arrow-left"></i> Kembali</a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4><i class="bi bi-cash-coin"></i> Pembayaran Angsuran</h4>
                    <span class="badge bg-<?= $hari_telat > 0 ? 'danger' : 'success' ?> fs-6">
                        <?= $hari_telat > 0 ? "Telat {$hari_telat} hari" : 'Tepat Waktu' ?>
                    </span>
                </div>

                <!-- Info Nasabah -->
                <div class="card shadow-sm mb-3">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-muted">Nasabah</small>
                                <h5 class="mb-0"><?= htmlspecialchars($a['nasabah_nama']) ?></h5>
                                <small><?= htmlspecialchars($a['nasabah_telp']) ?></small>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <small class="text-muted">Pinjaman</small>
                                <h5 class="mb-0"><?= htmlspecialchars($a['kode_pinjaman']) ?></h5>
                                <small>Angsuran ke-<?= $a['no_angsuran'] ?> - <?= getFrequencyLabel($frekuensi_value) ?></small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tagihan Card -->
                <div class="card tagihan-card shadow mb-3">
                    <div class="card-body text-center py-4">
                        <small class="opacity-75">TOTAL TAGIHAN</small>
                        <h2 class="mb-0 fw-bold" id="displayTotal"><?= formatRupiah($total_tagihan) ?></h2>
                        <small class="opacity-75">
                            Jatuh tempo: <?= date('d/m/Y', strtotime($a['jatuh_tempo'])) ?>
                        </small>
                    </div>
                </div>

                <!-- Rincian -->
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-receipt"></i> Rincian Pembayaran</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Pokok</span>
                            <span class="fw-bold"><?= formatRupiah($a['pokok']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Bunga</span>
                            <span class="fw-bold"><?= formatRupiah($a['bunga']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                            <span>Total Angsuran</span>
                            <span class="fw-bold"><?= formatRupiah($a['total_angsuran']) ?></span>
                        </div>
                        
                        <?php if ($denda_terhitung > 0): ?>
                        <div id="dendaRow" class="d-flex justify-content-between mb-2 text-danger">
                            <span>
                                <i class="bi bi-exclamation-triangle"></i> Denda 
                                <small class="text-muted">(<?= $a['denda_rate'] ?>% x <?= $hari_telat_efektif ?> hari)</small>
                            </span>
                            <span class="fw-bold" id="dendaValue"><?= formatRupiah($denda_terhitung) ?></span>
                        </div>
                        <?php endif; ?>

                        <?php if (($a['bisa_waive'] ?? 1) && $denda_terhitung > 0): ?>
                        <div id="waiveRow" class="d-flex justify-content-between mb-2 text-success" style="display: none !important;">
                            <span><i class="bi bi-check-circle"></i> Denda Dibebaskan</span>
                            <span class="fw-bold" id="waiveValue">-<?= formatRupiah($denda_terhitung) ?></span>
                        </div>
                        <?php endif; ?>

                        <div class="d-flex justify-content-between pt-2 border-top">
                            <span class="fw-bold">TOTAL BAYAR</span>
                            <span class="fw-bold fs-5 text-primary" id="finalTotal"><?= formatRupiah($total_tagihan) ?></span>
                        </div>
                    </div>
                </div>

                <!-- Form Pembayaran -->
                <div class="card shadow-sm">
                    <div class="card-body">
                        <form id="formPembayaran">
                            <input type="hidden" name="angsuran_id" value="<?= $angsuran_id ?>">
                            <input type="hidden" name="pinjaman_id" value="<?= $a['pinjaman_id'] ?>">
                            <input type="hidden" name="nasabah_id" value="<?= $a['nasabah_id'] ?>">
                            <input type="hidden" name="denda_terhitung" id="inputDenda" value="<?= $denda_terhitung ?>">
                            <input type="hidden" name="denda_dibebaskan" id="inputWaive" value="0">

                            <?php if (($a['bisa_waive'] ?? 1) && $denda_terhitung > 0): ?>
                            <!-- Waive Denda Option -->
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="checkWaive">
                                    <label class="form-check-label" for="checkWaive">
                                        <i class="bi bi-gift"></i> Bebaskan Denda
                                    </label>
                                </div>
                                <div id="waiveSection" class="waive-section mt-2">
                                    <label class="form-label">Jumlah Denda Dibebaskan</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" class="form-control" id="inputWaiveAmount" value="<?= round($denda_terhitung) ?>" max="<?= round($denda_terhitung) ?>">
                                    </div>
                                    <label class="form-label mt-2">Alasan Pembebasan</label>
                                    <textarea name="denda_alasan" class="form-control" rows="2" placeholder="Contoh: Nasabah loyal, kesepakatan, dll"></textarea>
                                </div>
                            </div>
                            <?php endif; ?>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tanggal Bayar <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal_bayar" class="form-control" value="<?= date('Y-m-d') ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Metode Pembayaran <span class="text-danger">*</span></label>
                                    <select name="metode" class="form-select" required>
                                        <option value="Tunai" selected>Tunai</option>
                                        <?php foreach ($metode_list as $m): ?>
                                            <?php if ($m != 'Tunai'): ?>
                                            <option value="<?= $m ?>"><?= $m ?></option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Keterangan</label>
                                <textarea name="keterangan" class="form-control" rows="2"></textarea>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-check-circle"></i> 
                                    Bayar <?= formatRupiah($total_tagihan) ?>
                                </button>
                                <a href="index.php" class="btn btn-outline-secondary">Batal</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Receipt Modal -->
    <div class="modal fade" id="modalKwitansi" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-body text-center p-4">
                    <div class="mb-3">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                    </div>
                    <h5>Pembayaran Berhasil!</h5>
                    <p class="text-muted mb-3">Kwitansi telah dicetak</p>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-primary" onclick="printKwitansi()">
                            <i class="bi bi-printer"></i> Cetak Kwitansi
                        </button>
                        <a href="index.php" class="btn btn-outline-secondary">Kembali</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    function formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID').format(angka);
    }

    const angsuranData = {
        pokok: <?= $a['pokok'] ?>,
        bunga: <?= $a['bunga'] ?>,
        total: <?= $a['total_angsuran'] ?>,
        denda: <?= $denda_terhitung ?>,
        grandTotal: <?= $total_tagihan ?>
    };

    // Waive denda handler
    $('#checkWaive').change(function() {
        const isWaived = $(this).is(':checked');
        $('#waiveSection').toggle(isWaived);
        
        if (isWaived) {
            const waiveAmount = parseFloat($('#inputWaiveAmount').val()) || 0;
            updateTotals(waiveAmount);
        } else {
            updateTotals(0);
        }
    });

    $('#inputWaiveAmount').on('input', function() {
        const waiveAmount = parseFloat($(this).val()) || 0;
        updateTotals(waiveAmount);
    });

    function updateTotals(waiveAmount) {
        const maxWaive = angsuranData.denda;
        const actualWaive = Math.min(waiveAmount, maxWaive);
        const newTotal = angsuranData.grandTotal - actualWaive;
        
        $('#inputWaive').val(actualWaive);
        $('#finalTotal').text(formatRupiah(newTotal));
        $('#displayTotal').text(formatRupiah(newTotal));
        
        if (actualWaive > 0) {
            $('#waiveRow').show().css('display', 'flex !important');
            $('#waiveValue').text('-' + formatRupiah(actualWaive));
            $('#dendaRow').hide();
        } else {
            $('#waiveRow').hide();
            $('#dendaRow').show();
        }
    }

    // GPS: ambil koordinat di background saat halaman dimuat (jika fitur aktif)
    let gpsData = { lat: null, lng: null, akurasi_gps: null };
    <?php
    require_once BASE_PATH . '/includes/feature_flags.php';
    if (isFeatureEnabled('gps_pembayaran')): ?>
    if ('geolocation' in navigator) {
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                gpsData.lat        = pos.coords.latitude;
                gpsData.lng        = pos.coords.longitude;
                gpsData.akurasi_gps = Math.round(pos.coords.accuracy);
                console.log('GPS acquired:', gpsData.lat, gpsData.lng, `±${gpsData.akurasi_gps}m`);
            },
            (err) => { console.warn('GPS tidak tersedia:', err.message); },
            { enableHighAccuracy: true, timeout: 8000, maximumAge: 30000 }
        );
    }
    <?php endif; ?>

    // Form submission
    $('#formPembayaran').submit(function(e) {
        e.preventDefault();
        
        const formData = {
            angsuran_id: $('input[name="angsuran_id"]').val(),
            pinjaman_id: $('input[name="pinjaman_id"]').val(),
            nasabah_id: $('input[name="nasabah_id"]').val(),
            tanggal_bayar: $('input[name="tanggal_bayar"]').val(),
            metode: $('select[name="metode"]').val(),
            keterangan: $('textarea[name="keterangan"]').val(),
            denda_terhitung: parseFloat($('#inputDenda').val()) || 0,
            denda_dibebaskan: parseFloat($('#inputWaive').val()) || 0,
            denda_alasan: $('textarea[name="denda_alasan"]').val() || '',
            // Sertakan GPS jika tersedia
            ...( gpsData.lat ? { lat: gpsData.lat, lng: gpsData.lng, akurasi_gps: gpsData.akurasi_gps } : {} )
        };

        // Validate
        if (!formData.tanggal_bayar) {
            Swal.fire('Error', 'Tanggal bayar wajib diisi', 'error');
            return;
        }

        Swal.fire({
            title: 'Konfirmasi Pembayaran',
            html: `Total yang akan dibayar: <strong>${$('#finalTotal').text()}</strong>${gpsData.lat ? '<br><small class="text-success"><i class="bi bi-geo-alt-fill"></i> Lokasi GPS tersimpan</small>' : ''}`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Bayar',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                submitPayment(formData);
            }
        });
    });

    function submitPayment(data) {
        $.ajax({
            url: '../../api/pembayaran.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(response) {
                if (response.success) {
                    $('#modalKwitansi').modal('show');
                } else {
                    Swal.fire('Error', response.error || 'Gagal menyimpan pembayaran', 'error');
                }
            },
            error: function(xhr) {
                const resp = xhr.responseJSON || {};
                Swal.fire('Error', resp.error || 'Terjadi kesalahan sistem', 'error');
            }
        });
    }

    function printKwitansi() {
        // Open print window
        window.open(`cetak_kwitansi.php?id=${$('input[name="angsuran_id"]').val()}`, '_blank');
    }
    </script>
    <!-- Include Notifications JS for compact pages -->
    <script src="<?php echo baseUrl('includes/js/notifications.js'); ?>"></script>
    <script>
    // Initialize notifications for compact page
    $(document).ready(function() {
        window.KewerNotifications.updateBadge();
    });
    </script>
</body>
</html>
