<?php
/**
 * Page: Nasabah Pinjaman
 * 
 * Halaman untuk nasabah melihat daftar pinjaman mereka
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

$page_title = 'Pinjaman Saya';
include BASE_PATH . '/includes/header.php';
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0">
                <i class="bi bi-credit-card-2-front me-2"></i>
                Pinjaman Saya
            </h1>
            <p class="text-muted">Daftar pinjaman aktif dan riwayat pinjaman</p>
        </div>
    </div>

    <div class="row" id="pinjamanList">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Memuat data pinjaman...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Load pinjaman on page load
document.addEventListener('DOMContentLoaded', function() {
    loadPinjaman();
});

/**
 * Load pinjaman list
 */
async function loadPinjaman() {
    try {
        const response = await fetch('<?php echo baseUrl("api/nasabah_portal.php?action=pinjaman"); ?>');
        const result = await response.json();
        
        if (result.success) {
            renderPinjaman(result.data);
        } else {
            document.getElementById('pinjamanList').innerHTML = `
                <div class="col-12">
                    <div class="alert alert-danger">${result.error || 'Gagal memuat data'}</div>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error:', error);
        document.getElementById('pinjamanList').innerHTML = `
            <div class="col-12">
                <div class="alert alert-danger">Terjadi kesalahan saat memuat data</div>
            </div>
        `;
    }
}

/**
 * Render pinjaman list
 */
function renderPinjaman(data) {
    if (data.length === 0) {
        document.getElementById('pinjamanList').innerHTML = `
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    Anda belum memiliki pinjaman. Silakan hubungi petugas koperasi untuk mengajukan pinjaman.
                </div>
            </div>
        `;
        return;
    }
    
    let html = '';
    data.forEach(pinjaman => {
        const statusClass = getStatusClass(pinjaman.status);
        const statusLabel = getStatusLabel(pinjaman.status);
        
        html += `
            <div class="col-md-6 mb-4">
                <div class="card h-100 ${pinjaman.status === 'aktif' || pinjaman.status === 'disetujui' ? 'border-primary' : ''}">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><strong>${pinjaman.kode_pinjaman}</strong></span>
                        <span class="badge ${statusClass}">${statusLabel}</span>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-6">
                                <small class="text-muted">Jumlah Pinjaman</small>
                                <p class="mb-0 fw-bold">${formatRupiah(pinjaman.jumlah_pinjaman)}</p>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Sisa Hutang</small>
                                <p class="mb-0 fw-bold">${formatRupiah(pinjaman.sisa_pinjaman)}</p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <small class="text-muted">Tenor</small>
                                <p class="mb-0">${pinjaman.tenor} ${pinjaman.frekuensi_label || pinjaman.frekuensi_angsuran}</p>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Tanggal Pengajuan</small>
                                <p class="mb-0">${formatDate(pinjaman.tanggal_pengajuan)}</p>
                            </div>
                        </div>
                        ${pinjaman.status === 'aktif' || pinjaman.status === 'disetujui' ? `
                            <a href="<?php echo baseUrl('pages/nasabah/angsuran.php'); ?>?pinjaman_id=${pinjaman.id}" 
                               class="btn btn-primary btn-sm w-100">
                                <i class="bi bi-calendar-check me-1"></i> Lihat Jadwal Angsuran
                            </a>
                        ` : ''}
                    </div>
                </div>
            </div>
        `;
    });
    
    document.getElementById('pinjamanList').innerHTML = html;
}

/**
 * Get status badge class
 */
function getStatusClass(status) {
    const classes = {
        'aktif': 'bg-success',
        'disetujui': 'bg-primary',
        'pengajuan': 'bg-warning',
        'ditolak': 'bg-danger',
        'lunas': 'bg-info',
        'default': 'bg-secondary'
    };
    return classes[status] || classes['default'];
}

/**
 * Get status label
 */
function getStatusLabel(status) {
    const labels = {
        'aktif': 'AKTIF',
        'disetujui': 'DISETUJUI',
        'pengajuan': 'PENGAJUAN',
        'ditolak': 'DITOLAK',
        'lunas': 'LUNAS'
    };
    return labels[status] || status.toUpperCase();
}

/**
 * Format currency
 */
function formatRupiah(amount) {
    if (!amount || amount === 0) return 'Rp0';
    return 'Rp' + amount.toLocaleString('id-ID');
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
