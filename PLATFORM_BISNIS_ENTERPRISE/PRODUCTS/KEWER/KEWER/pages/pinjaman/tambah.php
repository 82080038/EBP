<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();

// Permission check
if (!hasPermission('manage_pinjaman')) {
    header('Location: ' . baseUrl('dashboard.php'));
    exit();
}

// Get cabang filter based on role
$user = getCurrentUser();
$role = $user['role'];
$user_cabang_id = $user['cabang_id'] ?? null;

// Get active nasabah list with cabang filter
$cabang_filter = getCabangFilterForRole($role, $user_cabang_id, $user['id']);
if ($cabang_filter) {
    $cabang_filter = "AND " . $cabang_filter;
}
$nasabah_list = query("SELECT id, kode_nasabah, nama FROM nasabah WHERE status = 'aktif' $cabang_filter ORDER BY nama", []);
if (!is_array($nasabah_list)) {
    $nasabah_list = [];
}

$error = '';
$success = '';

if ($_POST) {
    $nasabah_id = $_POST['nasabah_id'] ?? '';
    $plafon = str_replace(['.', ','], '', $_POST['plafon'] ?? '');
    $frekuensi = $_POST['frekuensi'] ?? 'bulanan';
    $tenor = $_POST['tenor'] ?? '';
    $bunga_per_bulan = $_POST['bunga_per_bulan'] ?? '';
    $tanggal_akad = $_POST['tanggal_akad'] ?? '';
    $tujuan_pinjaman = $_POST['tujuan_pinjaman'] ?? '';
    $jaminan = $_POST['jaminan'] ?? '';
    $jaminan_tipe = $_POST['jaminan_tipe'] ?? 'tanpa';
    $jaminan_nilai = str_replace(['.', ','], '', $_POST['jaminan_nilai'] ?? '0');
    
    // Convert frequency to ID if it's a code
    $frekuensi_id = getFrequencyId($frekuensi);
    
    // Validate frekuensi
    if (!in_array($frekuensi, ['harian', 'mingguan', 'bulanan', 'HARIAN', 'MINGGUAN', 'BULANAN']) && !is_numeric($frekuensi)) {
        $frekuensi = 'bulanan';
        $frekuensi_id = 3;
    }
    
    // Validation
    $max_tenor = getMaxTenor($frekuensi_id);
    $period_label = getFrequencyPeriodLabel($frekuensi_id);
    
    if (!$nasabah_id || !$plafon || !$tenor || !$bunga_per_bulan || !$tanggal_akad) {
        $error = 'Semua field wajib diisi';
    } elseif (!is_numeric($plafon) || $plafon <= 0) {
        $error = 'Plafon harus berupa angka positif';
    } elseif (!is_numeric($tenor) || $tenor <= 0 || $tenor > $max_tenor) {
        $error = "Tenor harus antara 1-$max_tenor $period_label";
    } else {
        // Check if nasabah has active loan
        $active_loan = query("SELECT COUNT(*) as count FROM pinjaman WHERE nasabah_id = ? AND status IN ('aktif','disetujui','pengajuan')", [$nasabah_id]);
        if (is_array($active_loan) && isset($active_loan[0]) && $active_loan[0]['count'] > 0) {
            $error = 'Nasabah masih memiliki pinjaman aktif';
        } else {
            // Calculate loan
            $calc = calculateLoan($plafon, $tenor, $bunga_per_bulan, $frekuensi_id);
            
            // Generate kode pinjaman
            $kode_pinjaman = generateKode('PNJ', 'pinjaman', 'kode_pinjaman');
            
            // Calculate due date based on frequency
            $frekuensi_code = getFrequencyCode($frekuensi_id);
            switch ($frekuensi_code) {
                case 'HARIAN':
                    $tanggal_jatuh_tempo = date('Y-m-d', strtotime("+$tenor day", strtotime($tanggal_akad)));
                    break;
                case 'MINGGUAN':
                    $tanggal_jatuh_tempo = date('Y-m-d', strtotime("+$tenor week", strtotime($tanggal_akad)));
                    break;
                default:
                    $tanggal_jatuh_tempo = date('Y-m-d', strtotime("+$tenor month", strtotime($tanggal_akad)));
                    break;
            }
            
            // Insert pinjaman with transaction
            $result = crudTransaction(function() use ($user_cabang_id, $kode_pinjaman, $nasabah_id, $plafon, $tenor, $frekuensi_id, $bunga_per_bulan, $calc, $tanggal_akad, $tanggal_jatuh_tempo, $tujuan_pinjaman, $jaminan, $jaminan_tipe, $jaminan_nilai) {
                $result = query("INSERT INTO pinjaman (
                    cabang_id, kode_pinjaman, nasabah_id, plafon, tenor, frekuensi_id, bunga_per_bulan, 
                    total_bunga, total_pembayaran, angsuran_pokok, angsuran_bunga, angsuran_total,
                    tanggal_akad, tanggal_jatuh_tempo, tujuan_pinjaman, jaminan, jaminan_tipe, jaminan_nilai, status, petugas_id
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pengajuan', ?)", [
                    $user_cabang_id, $kode_pinjaman, $nasabah_id, $plafon, $tenor, $frekuensi_id, $bunga_per_bulan,
                    $calc['total_bunga'], $calc['total_pembayaran'], $calc['angsuran_pokok'], 
                    $calc['angsuran_bunga'], $calc['angsuran_total'], $tanggal_akad, 
                    $tanggal_jatuh_tempo, $tujuan_pinjaman, $jaminan, $jaminan_tipe, $jaminan_nilai ?: null, getCurrentUser()['id']
                ]);
                
                return $result;
            });
            
            if ($result) {
                $success = 'Pengajuan pinjaman berhasil dibuat';
                $_POST = [];
            } else {
                $error = 'Gagal membuat pengajuan pinjaman';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajukan Pinjaman - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/id.js"></script>
    <script src="/kewer/includes/js/auto-focus.js"></script>
    <script src="/kewer/includes/js/enter-navigation.js"></script>
    <script>
        function formatRupiah(angka) {
            return new Intl.NumberFormat('id-ID').format(angka);
        }
        
        const freqConfig = {
            harian:   { label: 'hari', periodLabel: 'Hari', max: 365, bungaDivisor: 30 },
            mingguan: { label: 'minggu', periodLabel: 'Minggu', max: 52, bungaDivisor: 4 },
            bulanan:  { label: 'bulan', periodLabel: 'Bulan', max: 24, bungaDivisor: 1 }
        };
        
        function updateFrequencyUI() {
            const frekSelect = document.getElementById('frekuensi');
            const selectedOption = frekSelect.options[frekSelect.selectedIndex];
            const maxTenor = parseInt(selectedOption.getAttribute('data-max')) || 24;
            const period = parseInt(selectedOption.getAttribute('data-period')) || 30;
            
            let label = 'bulan';
            let help = 'Harian: 1-365 | Mingguan: 1-52 | Bulanan: 1-24';
            if (period === 1) {
                label = 'hari';
                help = 'Maksimal 365 hari';
            } else if (period === 7) {
                label = 'minggu';
                help = 'Maksimal 52 minggu';
            }
            
            document.getElementById('tenorLabel').textContent = label;
            document.getElementById('tenor').max = maxTenor;
            document.getElementById('tenorHelp').textContent = help;
            
            calculatePreview();
        }
        
        function calculatePreview() {
            const plafon = parseFloat(document.getElementById('plafon').value.replace(/[^\d]/g, '')) || 0;
            const tenor = parseInt(document.getElementById('tenor').value) || 0;
            const bungaBulanan = parseFloat(document.getElementById('bunga').value) || 0;
            const frekSelect = document.getElementById('frekuensi');
            const selectedOption = frekSelect.options[frekSelect.selectedIndex];
            const period = parseInt(selectedOption.getAttribute('data-period')) || 30;
            
            let bungaDivisor = 1;
            let periodLabel = 'Bulan';
            if (period === 1) {
                bungaDivisor = 30;
                periodLabel = 'Hari';
            } else if (period === 7) {
                bungaDivisor = 4;
                periodLabel = 'Minggu';
            }
            
            // Convert monthly rate to per-period rate
            const bungaPerPeriod = bungaBulanan / bungaDivisor;
            
            if (plafon > 0 && tenor > 0 && bungaBulanan >= 0) {
                const totalBunga = plafon * (bungaPerPeriod / 100) * tenor;
                const totalPembayaran = plafon + totalBunga;
                const angsuranPokok = plafon / tenor;
                const angsuranBunga = totalBunga / tenor;
                const angsuranTotal = angsuranPokok + angsuranBunga;
                
                document.getElementById('loanPreview').innerHTML = `
                    <table class="table table-sm">
                        <tr>
                            <td>Frekuensi:</td>
                            <td class="text-end"><span class="badge bg-primary">${periodLabel}</span></td>
                        </tr>
                        <tr>
                            <td>Total Pinjaman:</td>
                            <td class="text-end">Rp ${formatRupiah(totalPembayaran)}</td>
                        </tr>
                        <tr>
                            <td>Total Bunga:</td>
                            <td class="text-end">Rp ${formatRupiah(totalBunga)}</td>
                        </tr>
                        <tr>
                            <td>Angsuran/${periodLabel}:</td>
                            <td class="text-end fw-bold">Rp ${formatRupiah(angsuranTotal)}</td>
                        </tr>
                        <tr>
                            <td>Pokok/${periodLabel}:</td>
                            <td class="text-end">Rp ${formatRupiah(angsuranPokok)}</td>
                        </tr>
                        <tr>
                            <td>Bunga/${periodLabel}:</td>
                            <td class="text-end">Rp ${formatRupiah(angsuranBunga)}</td>
                        </tr>
                        <tr>
                            <td>Bunga per ${label}:</td>
                            <td class="text-end">${bungaPerPeriod.toFixed(4)}%</td>
                        </tr>
                    </table>
                `;
            } else {
                document.getElementById('loanPreview').innerHTML = '<p class="text-muted">Isi data pinjaman untuk melihat simulasi</p>';
            }
        }
        
        // Initialize flatpickr
        flatpickr('.flatpickr-date', {
            locale: 'id',
            dateFormat: 'Y-m-d',
            allowInput: true,
            altInput: true,
            altFormat: 'd F Y',
            theme: 'light'
        });
        
        // Initialize event listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Format rupiah input
            document.getElementById('plafon').addEventListener('input', function(e) {
                e.target.value = formatRupiah(e.target.value.replace(/[^\d]/g, ''));
                calculatePreview();
            });
            
            calculatePreview();
            document.getElementById('frekuensi').addEventListener('change', updateFrequencyUI);
            document.getElementById('tenor').addEventListener('input', calculatePreview);
            document.getElementById('bunga').addEventListener('input', calculatePreview);
            
            // Initial setup
            updateFrequencyUI();
            calculatePreview();
        });
        
        $(document).ready(function() {
            // Auto-advance focus when select element is changed
            document.addEventListener("DOMContentLoaded", function() {
                const selects = document.querySelectorAll("select");
                selects.forEach(function(select) {
                    select.addEventListener("change", function() {
                        // Find the next form element
                        const form = this.form;
                        if (form) {
                            const elements = Array.from(form.elements);
                            const currentIndex = elements.indexOf(this);
                            
                            // Find the next visible, non-disabled, non-readonly element
                            for (let i = currentIndex + 1; i < elements.length; i++) {
                                const nextElement = elements[i];
                                if (nextElement &&
                                    nextElement.tagName !== "BUTTON" &&
                                    nextElement.type !== "hidden" &&
                                    nextElement.type !== "submit" &&
                                    !nextElement.disabled &&
                                    !nextElement.readOnly &&
                                    nextElement.offsetParent !== null) {
                                    nextElement.focus();
                                    break;
                                }
                            }
                        }
                    });
                });
            });
        });
    </script>
</head>
<body>
    <div class="main-container">
        <?php require_once BASE_PATH . '/includes/sidebar.php'; ?>
        
        <main class="content-area">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Ajukan Pinjaman Baru</h1>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-body">
                        <form method="POST" id="loanForm">
                            <?= csrfField() ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Nasabah *</label>
                                        <select name="nasabah_id" class="form-select" required>
                                            <option value="">Pilih Nasabah</option>
                                            <?php foreach ($nasabah_list as $n): ?>
                                                <option value="<?php echo $n['id']; ?>" <?php echo ($_POST['nasabah_id'] ?? '') == $n['id'] ? 'selected' : ''; ?>>
                                                    <?php echo $n['kode_nasabah']; ?> - <?php echo $n['nama']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Plafon Pinjaman *</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" name="plafon" class="form-control" id="plafon" value="<?php echo $_POST['plafon'] ?? ''; ?>" required>
                                        </div>
                                        <small class="form-text">Maksimal: <?php echo formatRupiah(10000000); ?></small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Frekuensi Angsuran *</label>
                                        <select name="frekuensi" class="form-select" id="frekuensi" required>
                                            <?php
                                            $active_frequencies = getActiveFrequencies();
                                            if ($active_frequencies && is_array($active_frequencies)):
                                                foreach ($active_frequencies as $freq):
                                            ?>
                                            <option value="<?= $freq['kode'] ?>" data-id="<?= $freq['id'] ?>" data-max="<?= $freq['tenor_max'] ?>" data-period="<?= $freq['hari_per_periode'] ?>" <?php echo ($_POST['frekuensi'] ?? '') === $freq['kode'] ? 'selected' : ''; ?>>
                                                <?= $freq['nama'] ?> (Max: <?= $freq['tenor_max'] ?>)
                                            </option>
                                            <?php endforeach; else: ?>
                                            <option value="harian" <?php echo ($_POST['frekuensi'] ?? '') === 'harian' ? 'selected' : ''; ?>>Harian</option>
                                            <option value="mingguan" <?php echo ($_POST['frekuensi'] ?? '') === 'mingguan' ? 'selected' : ''; ?>>Mingguan</option>
                                            <option value="bulanan" <?php echo ($_POST['frekuensi'] ?? 'bulanan') === 'bulanan' ? 'selected' : ''; ?>>Bulanan</option>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Tenor *</label>
                                        <div class="input-group">
                                            <input type="number" name="tenor" class="form-control" id="tenor" value="<?php echo $_POST['tenor'] ?? ''; ?>" min="1" max="24" required>
                                            <span class="input-group-text" id="tenorLabel">bulan</span>
                                        </div>
                                        <small class="form-text" id="tenorHelp">Harian: 1-365 | Mingguan: 1-52 | Bulanan: 1-24</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Bunga per Bulan *</label>
                                        <div class="input-group">
                                            <input type="number" name="bunga_per_bulan" class="form-control" id="bunga" value="<?php echo $_POST['bunga_per_bulan'] ?? '2.5'; ?>" step="0.1" min="0" max="10" required>
                                            <span class="input-group-text">%</span>
                                        </div>
                                        <small class="form-text text-muted">Bunga per bulan (akan dikonversi otomatis sesuai frekuensi)</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Tanggal Akad *</label>
                                        <input type="date" name="tanggal_akad" class="form-control flatpickr-date" value="<?php echo $_POST['tanggal_akad'] ?? date('Y-m-d'); ?>" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Tujuan Pinjaman</label>
                                        <textarea name="tujuan_pinjaman" class="form-control" rows="3"><?php echo $_POST['tujuan_pinjaman'] ?? ''; ?></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Tipe Jaminan</label>
                                        <select name="jaminan_tipe" class="form-select">
                                            <?php
                                            $jaminan_tipe_list = getActiveReferenceData('ref_jaminan_tipe');
                                            if (is_array($jaminan_tipe_list)):
                                                foreach ($jaminan_tipe_list as $jt):
                                                    $kode = str_replace('JAM', '', strtolower($jt['tipe_kode']));
                                            ?>
                                                <option value="<?php echo $kode; ?>" <?php echo ($_POST['jaminan_tipe'] ?? '') === $kode ? 'selected' : ''; ?>><?php echo htmlspecialchars($jt['tipe_nama']); ?></option>
                                            <?php
                                                endforeach;
                                            endif;
                                            ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Nilai Jaminan</label>
                                        <input type="text" name="jaminan_nilai" class="form-control" placeholder="Rp" value="<?php echo $_POST['jaminan_nilai'] ?? ''; ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Keterangan Jaminan</label>
                                        <textarea name="jaminan" class="form-control" rows="2" placeholder="Deskripsi detail jaminan..."><?php echo $_POST['jaminan'] ?? ''; ?></textarea>
                                    </div>
                                    
                                    <!-- Loan Calculation Preview -->
                                    <div class="card bg-light">
                                        <div class="card-header">
                                            <h6><i class="bi bi-calculator"></i> Simulasi Pinjaman</h6>
                                        </div>
                                        <div class="card-body" id="loanPreview">
                                            <p class="text-muted">Isi data pinjaman untuk melihat simulasi</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-end">
                                <a href="index.php" class="btn btn-secondary me-2">Batal</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-send"></i> Ajukan Pinjaman
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
