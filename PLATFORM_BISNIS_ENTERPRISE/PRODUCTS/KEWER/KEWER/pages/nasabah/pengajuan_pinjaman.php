<?php
/**
 * Page: Nasabah Pengajuan Pinjaman
 * 
 * Halaman untuk nasabah mengajukan pinjaman baru
 * 
 * Access: nasabah only
 */

require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';

// Check if user is logged in and is nasabah
if (!isLoggedIn() || $_SESSION['role'] !== 'nasabah') {
    header('Location: ' . baseUrl('login.php'));
    exit();
}

$nasabah_id = $_SESSION['nasabah_id'] ?? null;
if (!$nasabah_id) {
    header('Location: ' . baseUrl('login.php?error=no_nasabah_id'));
    exit();
}

$page_title = 'Pengajuan Pinjaman';
include BASE_PATH . '/includes/header.php';
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0">
                <i class="bi bi-credit-card me-2"></i>
                Pengajuan Pinjaman
            </h1>
            <p class="text-muted">Ajukan pinjaman baru dengan mudah</p>
        </div>
    </div>

    <!-- Info Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info">
                <h5 class="alert-heading"><i class="bi bi-info-circle me-2"></i>Informasi</h5>
                <p class="mb-1"><strong>Limit Pinjaman:</strong> <span id="limitInfo">Memuat...</span></p>
                <p class="mb-1"><strong>Tenor:</strong> <span id="tenorInfo">Memuat...</span></p>
                <p class="mb-0"><strong>Frekuensi:</strong> 
                    <?php
                    $active_frequencies = getActiveFrequencies();
                    if ($active_frequencies && is_array($active_frequencies)):
                        foreach ($active_frequencies as $freq):
                            echo $freq['nama'] . ' (max ' . $freq['tenor_max'] . ' ' . getFrequencyPeriodLabel($freq['id']) . '), ';
                        endforeach;
                    else:
                        echo 'Harian (max 100 hari), Mingguan (max 52 minggu), Bulanan (max 24 bulan)';
                    endif;
                    ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Pengajuan Form -->
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-file-earmark-text me-2"></i>
                    Form Pengajuan Pinjaman
                </div>
                <div class="card-body">
                    <form id="pengajuanForm">
                        <div class="mb-3">
                            <label class="form-label">Koperasi Tujuan <span class="text-danger">*</span></label>
                            <select class="form-select" id="koperasiId" name="koperasi_id" required>
                                <option value="">Memuat koperasi...</option>
                            </select>
                            <small class="text-muted">Pilih koperasi tempat Anda ingin mengajukan pinjaman</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Jumlah Pinjaman <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" id="jumlahPinjaman" name="jumlah_pengajuan" required min="500000" max="10000000" step="100000">
                            </div>
                            <small class="text-muted">Minimal Rp500.000, maksimal Rp10.000.000, kelipatan Rp100.000</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tenor <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="tenor" name="tenor" required min="1" max="24">
                                <small class="text-muted">Jumlah periode angsuran</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Frekuensi Angsuran <span class="text-danger">*</span></label>
                                <select class="form-select" id="frekuensi" name="frekuensi_angsuran" required>
                                    <?php
                                    $active_frequencies = getActiveFrequencies();
                                    if ($active_frequencies && is_array($active_frequencies)):
                                        foreach ($active_frequencies as $freq):
                                    ?>
                                    <option value="<?= $freq['kode'] ?>" data-id="<?= $freq['id'] ?>" data-max="<?= $freq['tenor_max'] ?>" data-period="<?= $freq['hari_per_periode'] ?>">
                                        <?= $freq['nama'] ?> (Max <?= $freq['tenor_max'] ?> <?= getFrequencyPeriodLabel($freq['id']) ?>)
                                    </option>
                                    <?php endforeach; else: ?>
                                    <option value="harian">Harian (Max 100 hari)</option>
                                    <option value="mingguan">Mingguan (Max 52 minggu)</option>
                                    <option value="bulanan" selected>Bulanan (Max 24 bulan)</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tujuan Penggunaan <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="tujuan" name="tujuan_penggunaan" rows="3" required placeholder="Jelaskan tujuan penggunaan pinjaman ini..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Jaminan (Opsional)</label>
                            <textarea class="form-control" id="jaminan" name="jaminan" rows="2" placeholder="Jelaskan jaminan yang akan diberikan jika ada..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Metode Pengambilan Dana <span class="text-danger">*</span></label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="metode_pengambilan" id="ambilTeller" value="teller" checked required>
                                        <label class="form-check-label" for="ambilTeller">
                                            <i class="bi bi-bank me-1"></i> Ambil di Teller Kantor
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="metode_pengambilan" id="diantarPetugas" value="diantar_petugas" required>
                                        <label class="form-check-label" for="diantarPetugas">
                                            <i class="bi bi-truck me-1"></i> Diantar Petugas ke Lokasi
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <small class="text-muted">Jika memilih "Diantar Petugas", petugas akan mengantar dana ke alamat Anda setelah pengajuan disetujui.</small>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="bi bi-send me-2"></i>Kirim Pengajuan
                            </button>
                            <a href="<?php echo baseUrl('pages/nasabah/dashboard.php'); ?>" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-2"></i>Kembali
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Summary -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-calculator me-2"></i>
                    Estimasi Angsuran
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">Jumlah Pinjaman</small>
                        <h5 id="estimasiJumlah">Rp0</h5>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Tenor</small>
                        <h5 id="estimasiTenor">-</h5>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Estimasi Angsuran per Periode</small>
                        <h4 class="text-primary" id="estimasiAngsuran">Rp0</h4>
                    </div>
                    <div class="alert alert-warning small">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Estimasi ini belum termasuk bunga dan biaya administrasi. Angsuran final akan dihitung oleh petugas koperasi.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Riwayat Pengajuan -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-clock-history me-2"></i>
                    Riwayat Pengajuan Pinjaman
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="riwayatTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Koperasi</th>
                                    <th>Jumlah</th>
                                    <th>Tenor</th>
                                    <th>Status</th>
                                    <th>Catatan</th>
                                </tr>
                            </thead>
                            <tbody id="riwayatTableBody">
                                <tr>
                                    <td colspan="6" class="text-center py-3 text-muted">
                                        Memuat riwayat...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let settings = {};

// Load settings and koperasi on page load
document.addEventListener('DOMContentLoaded', function() {
    loadSettings();
    loadKoperasi();
    loadRiwayat();
    
    // Setup form events
    document.getElementById('jumlahPinjaman').addEventListener('input', updateEstimasi);
    document.getElementById('tenor').addEventListener('input', updateEstimasi);
    document.getElementById('frekuensi').addEventListener('change', updateEstimasi);
    
    // Form submit
    document.getElementById('pengajuanForm').addEventListener('submit', function(e) {
        e.preventDefault();
        submitPengajuan();
    });
});

/**
 * Load pengajuan settings
 */
async function loadSettings() {
    try {
        const response = await fetch('<?php echo baseUrl("api/nasabah_portal.php?action=pengajuan_pinjaman_settings"); ?>');
        const result = await response.json();
        
        if (result.success) {
            settings = result.data;
            document.getElementById('limitInfo').textContent = 
                `Rp${parseInt(settings.jumlah_minimal).toLocaleString('id-ID')} - Rp${parseInt(settings.jumlah_maksimal).toLocaleString('id-ID')} (kelipatan Rp${parseInt(settings.kelipatan).toLocaleString('id-ID')})`;
            document.getElementById('tenorInfo').textContent = 
                `${settings.tenor_minimal} - ${settings.tenor_maksimal} ${settings.frekuensi_angsuran}`;
            
            // Update form constraints
            document.getElementById('jumlahPinjaman').min = settings.jumlah_minimal;
            document.getElementById('jumlahPinjaman').max = settings.jumlah_maksimal;
            document.getElementById('jumlahPinjaman').step = settings.kelipatan;
            document.getElementById('tenor').min = settings.tenor_minimal;
            document.getElementById('tenor').max = settings.tenor_maksimal;
        }
    } catch (error) {
        console.error('Error loading settings:', error);
    }
}

/**
 * Load koperasi list
 */
async function loadKoperasi() {
    try {
        const response = await fetch('<?php echo baseUrl("api/nasabah_portal.php?action=koperasi_terdaftar"); ?>');
        const result = await response.json();
        
        if (result.success && result.data.length > 0) {
            const select = document.getElementById('koperasiId');
            select.innerHTML = result.data.map(k => 
                `<option value="${k.koperasi_id}">${k.nama_koperasi}</option>`
            ).join('');
        }
    } catch (error) {
        console.error('Error loading koperasi:', error);
    }
}

/**
 * Load riwayat pengajuan
 */
async function loadRiwayat() {
    try {
        const response = await fetch('<?php echo baseUrl("api/nasabah_portal.php?action=list_pengajuan_pinjaman"); ?>');
        const result = await response.json();
        
        if (result.success) {
            renderRiwayat(result.data);
        }
    } catch (error) {
        console.error('Error loading riwayat:', error);
    }
}

/**
 * Render riwayat table
 */
function renderRiwayat(data) {
    if (data.length === 0) {
        document.getElementById('riwayatTableBody').innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-3 text-muted">
                    Belum ada riwayat pengajuan
                </td>
            </tr>
        `;
        return;
    }
    
    document.getElementById('riwayatTableBody').innerHTML = data.map(p => {
        const statusClass = {
            'diajukan': 'bg-warning',
            'diproses': 'bg-info',
            'disetujui': 'bg-success',
            'ditolak': 'bg-danger'
        }[p.status_pengajuan] || 'bg-secondary';
        
        return `
            <tr>
                <td>${formatDate(p.created_at)}</td>
                <td>${p.nama_koperasi || 'Koperasi Utama'}</td>
                <td>Rp${parseInt(p.jumlah_pengajuan).toLocaleString('id-ID')}</td>
                <td>${p.tenor} ${p.frekuensi_angsuran}</td>
                <td><span class="badge ${statusClass}">${p.status_pengajuan.toUpperCase()}</span></td>
                <td>${p.alasan_penolakan || p.catatan_petugas || '-'}</td>
            </tr>
        `;
    }).join('');
}

/**
 * Update estimasi angsuran
 */
function updateEstimasi() {
    const jumlah = parseFloat(document.getElementById('jumlahPinjaman').value) || 0;
    const tenor = parseInt(document.getElementById('tenor').value) || 1;
    const frekSelect = document.getElementById('frekuensi');
    const selectedOption = frekSelect.options[frekSelect.selectedIndex];
    const period = parseInt(selectedOption.getAttribute('data-period')) || 30;
    
    let periodLabel = 'bulan';
    if (period === 1) periodLabel = 'hari';
    else if (period === 7) periodLabel = 'minggu';
    
    document.getElementById('estimasiJumlah').textContent = 'Rp' + jumlah.toLocaleString('id-ID');
    document.getElementById('estimasiTenor').textContent = tenor + ' ' + periodLabel;
    
    if (jumlah > 0 && tenor > 0) {
        const angsuran = Math.ceil(jumlah / tenor);
        document.getElementById('estimasiAngsuran').textContent = 'Rp' + angsuran.toLocaleString('id-ID');
    }
}

/**
 * Submit pengajuan
 */
async function submitPengajuan() {
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim...';
    
    const metodePengambilan = document.querySelector('input[name="metode_pengambilan"]:checked')?.value || 'teller';
    
    const data = {
        koperasi_id: parseInt(document.getElementById('koperasiId').value),
        jumlah_pengajuan: parseFloat(document.getElementById('jumlahPinjaman').value),
        tenor: parseInt(document.getElementById('tenor').value),
        frekuensi_angsuran: document.getElementById('frekuensi').value,
        tujuan_penggunaan: document.getElementById('tujuan').value,
        jaminan: document.getElementById('jaminan').value,
        metode_pengambilan: metodePengambilan
    };
    
    try {
        const response = await fetch('<?php echo baseUrl("api/nasabah_portal.php?action=submit_pengajuan_pinjaman"); ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: result.message,
                confirmButtonText: 'OK'
            }).then(() => {
                document.getElementById('pengajuanForm').reset();
                loadRiwayat();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: result.error || 'Terjadi kesalahan',
                confirmButtonText: 'OK'
            });
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Terjadi kesalahan saat mengirim pengajuan',
            confirmButtonText: 'OK'
        });
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="bi bi-send me-2"></i>Kirim Pengajuan';
    }
}

/**
 * Format date
 */
function formatDate(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    });
}
</script>

<?php include BASE_PATH . '/includes/footer.php'; ?>
