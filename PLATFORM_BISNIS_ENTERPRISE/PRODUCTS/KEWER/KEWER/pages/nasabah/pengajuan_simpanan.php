<?php
/**
 * Page: Nasabah Pengajuan Simpanan
 * 
 * Halaman untuk nasabah mengajukan simpanan
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

$page_title = 'Pengajuan Simpanan';
include BASE_PATH . '/includes/header.php';
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0">
                <i class="bi bi-piggy-bank me-2"></i>
                Pengajuan Simpanan
            </h1>
            <p class="text-muted">Ajukan simpanan untuk masa depan yang lebih baik</p>
        </div>
    </div>

    <!-- Info Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info">
                <h5 class="alert-heading"><i class="bi bi-info-circle me-2"></i>Informasi Simpanan</h5>
                <p class="mb-1"><strong>Limit Simpanan:</strong> <span id="limitInfo">Memuat...</span></p>
                <p class="mb-1"><strong>Bunga:</strong> <span id="bungaInfo">Memuat...</span> per bulan</p>
                <p class="mb-0">Pilih jenis simpanan yang sesuai dengan kebutuhan Anda</p>
            </div>
        </div>
    </div>

    <!-- Pengajuan Form -->
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-file-earmark-text me-2"></i>
                    Form Pengajuan Simpanan
                </div>
                <div class="card-body">
                    <form id="pengajuanForm">
                        <div class="mb-3">
                            <label class="form-label">Koperasi Tujuan <span class="text-danger">*</span></label>
                            <select class="form-select" id="koperasiId" name="koperasi_id" required>
                                <option value="">Memuat koperasi...</option>
                            </select>
                            <small class="text-muted">Pilih koperasi tempat Anda ingin membuka simpanan</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Jenis Simpanan <span class="text-danger">*</span></label>
                            <select class="form-select" id="jenisSimpanan" name="jenis_simpanan" required>
                                <option value="">Pilih jenis simpanan...</option>
                                <option value="sukarela">Simpanan Sukarela</option>
                                <option value="wajib">Simpanan Wajib</option>
                                <option value="pokok">Simpanan Pokok</option>
                                <option value="berjangka">Simpanan Berjangka</option>
                            </select>
                            <small class="text-muted">
                                <strong>Sukarela:</strong> Bebas setoran | 
                                <strong>Wajib:</strong> Setoran rutin wajib | 
                                <strong>Pokok:</strong> Setoran awal keanggotaan | 
                                <strong>Berjangka:</strong> Simpanan dengan jangka waktu tertentu
                            </small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Jumlah Simpanan <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" id="jumlahSimpanan" name="jumlah_pengajuan" required min="100000" max="50000000" step="50000">
                            </div>
                            <small class="text-muted">Minimal Rp100.000, maksimal Rp50.000.000, kelipatan Rp50.000</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Metode Setoran <span class="text-danger">*</span></label>
                                <select class="form-select" id="metodeSetoran" name="metode_setoran" required>
                                    <option value="tunai">Tunai (Bayar di Kantor)</option>
                                    <option value="transfer">Transfer Bank</option>
                                    <option value="auto_debit">Auto Debit (Potong Pinjaman)</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Frekuensi Setoran <span class="text-danger">*</span></label>
                                <select class="form-select" id="frekuensiSetoran" name="frekuensi_setoran" required>
                                    <?php
                                    $active_frequencies = getActiveFrequencies();
                                    if ($active_frequencies && is_array($active_frequencies)):
                                        foreach ($active_frequencies as $freq):
                                    ?>
                                    <option value="<?= $freq['kode'] ?>" <?= ($_POST['frekuensi_setoran'] ?? '') === $freq['kode'] ? 'selected' : ''; ?>>
                                        <?= $freq['nama'] ?>
                                    </option>
                                    <?php endforeach; else: ?>
                                    <option value="harian">Harian</option>
                                    <option value="mingguan">Mingguan</option>
                                    <option value="bulanan" selected>Bulanan</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tujuan Simpanan (Opsional)</label>
                            <textarea class="form-control" id="tujuanSimpanan" name="tujuan_simpanan" rows="2" placeholder="Jelaskan tujuan Anda menabung (contoh: modal usaha, pendidikan anak, dana darurat, dll)..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Metode Penyerahan Simpanan <span class="text-danger">*</span></label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="metode_penyerahan" id="serahTeller" value="teller" checked required>
                                        <label class="form-check-label" for="serahTeller">
                                            <i class="bi bi-bank me-1"></i> Setor di Teller Kantor
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="metode_penyerahan" id="dijemputPetugas" value="dijemput_petugas" required>
                                        <label class="form-check-label" for="dijemputPetugas">
                                            <i class="bi bi-truck me-1"></i> Dijemput Petugas di Lokasi
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <small class="text-muted">Jika memilih "Dijemput Petugas", petugas akan menjemput dana simpanan di alamat Anda setelah pengajuan disetujui.</small>
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
                    Ringkasan Simpanan
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">Jumlah Simpanan</small>
                        <h5 id="ringkasanJumlah">Rp0</h5>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Jenis Simpanan</small>
                        <h5 id="ringkasanJenis">-</h5>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Estimasi Bunga/Bulan</small>
                        <h4 class="text-success" id="estimasiBunga">Rp0</h4>
                    </div>
                    <div class="alert alert-success small">
                        <i class="bi bi-check-circle me-1"></i>
                        Simpanan Anda akan diproses setelah disetujui oleh petugas koperasi.
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
                    Riwayat Pengajuan Simpanan
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="riwayatTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Koperasi</th>
                                    <th>Jenis</th>
                                    <th>Jumlah</th>
                                    <th>Frekuensi</th>
                                    <th>Status</th>
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
    document.getElementById('jumlahSimpanan').addEventListener('input', updateRingkasan);
    document.getElementById('jenisSimpanan').addEventListener('change', updateRingkasan);
    
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
        const response = await fetch('<?php echo baseUrl("api/nasabah_portal.php?action=pengajuan_simpanan_settings"); ?>');
        const result = await response.json();
        
        if (result.success) {
            settings = result.data;
            document.getElementById('limitInfo').textContent = 
                `Rp${parseInt(settings.jumlah_minimal).toLocaleString('id-ID')} - Rp${parseInt(settings.jumlah_maksimal).toLocaleString('id-ID')} (kelipatan Rp${parseInt(settings.kelipatan).toLocaleString('id-ID')})`;
            document.getElementById('bungaInfo').textContent = settings.bunga_per_bulan + '%';
            
            // Update form constraints
            document.getElementById('jumlahSimpanan').min = settings.jumlah_minimal;
            document.getElementById('jumlahSimpanan').max = settings.jumlah_maksimal;
            document.getElementById('jumlahSimpanan').step = settings.kelipatan;
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
        const response = await fetch('<?php echo baseUrl("api/nasabah_portal.php?action=list_pengajuan_simpanan"); ?>');
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
                    Belum ada riwayat pengajuan simpanan
                </td>
            </tr>
        `;
        return;
    }
    
    const jenisLabels = {
        'sukarela': 'Sukarela',
        'wajib': 'Wajib',
        'pokok': 'Pokok',
        'berjangka': 'Berjangka'
    };
    
    document.getElementById('riwayatTableBody').innerHTML = data.map(p => {
        const statusClass = {
            'diajukan': 'bg-warning',
            'diproses': 'bg-info',
            'disetujui': 'bg-success',
            'ditolak': 'bg-danger',
            'aktif': 'bg-primary',
            'selesai': 'bg-secondary'
        }[p.status_pengajuan] || 'bg-secondary';
        
        return `
            <tr>
                <td>${formatDate(p.created_at)}</td>
                <td>${p.nama_koperasi || 'Koperasi Utama'}</td>
                <td>${jenisLabels[p.jenis_simpanan] || p.jenis_simpanan}</td>
                <td>Rp${parseInt(p.jumlah_pengajuan).toLocaleString('id-ID')}</td>
                <td>${p.frekuensi_setoran}</td>
                <td><span class="badge ${statusClass}">${p.status_pengajuan.toUpperCase()}</span></td>
            </tr>
        `;
    }).join('');
}

/**
 * Update ringkasan
 */
function updateRingkasan() {
    const jumlah = parseFloat(document.getElementById('jumlahSimpanan').value) || 0;
    const jenis = document.getElementById('jenisSimpanan').value;
    const bunga = settings.bunga_per_bulan || 0.5;
    
    document.getElementById('ringkasanJumlah').textContent = 'Rp' + jumlah.toLocaleString('id-ID');
    
    const jenisLabels = {
        'sukarela': 'Simpanan Sukarela',
        'wajib': 'Simpanan Wajib',
        'pokok': 'Simpanan Pokok',
        'berjangka': 'Simpanan Berjangka'
    };
    document.getElementById('ringkasanJenis').textContent = jenisLabels[jenis] || '-';
    
    if (jumlah > 0) {
        const estimasiBunga = Math.round(jumlah * (bunga / 100));
        document.getElementById('estimasiBunga').textContent = 'Rp' + estimasiBunga.toLocaleString('id-ID');
    }
}

/**
 * Submit pengajuan
 */
async function submitPengajuan() {
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim...';
    
    const metodePenyerahan = document.querySelector('input[name="metode_penyerahan"]:checked')?.value || 'teller';
    
    const data = {
        koperasi_id: parseInt(document.getElementById('koperasiId').value),
        jenis_simpanan: document.getElementById('jenisSimpanan').value,
        jumlah_pengajuan: parseFloat(document.getElementById('jumlahSimpanan').value),
        metode_setoran: document.getElementById('metodeSetoran').value,
        frekuensi_setoran: document.getElementById('frekuensiSetoran').value,
        tujuan_simpanan: document.getElementById('tujuanSimpanan').value,
        metode_penyerahan: metodePenyerahan
    };
    
    try {
        const response = await fetch('<?php echo baseUrl("api/nasabah_portal.php?action=submit_pengajuan_simpanan"); ?>', {
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
                updateRingkasan();
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
