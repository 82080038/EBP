<?php
/**
 * Page: Nasabah Angsuran
 * 
 * Halaman untuk nasabah melihat jadwal angsuran
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

$page_title = 'Jadwal Angsuran';
include BASE_PATH . '/includes/header.php';

$pinjaman_id = $_GET['pinjaman_id'] ?? null;
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0">
                <i class="bi bi-calendar-check me-2"></i>
                Jadwal Angsuran
            </h1>
            <p class="text-muted">Daftar pembayaran yang harus dilakukan</p>
        </div>
    </div>

    <?php if ($pinjaman_id): ?>
    <div class="row mb-3">
        <div class="col-12">
            <a href="<?php echo baseUrl('pages/nasabah/pinjaman.php'); ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Pinjaman
            </a>
        </div>
    </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-list-check me-2"></i>
                    Daftar Angsuran
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="angsuranTable">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Kode Pinjaman</th>
                                    <th>Angsuran Ke</th>
                                    <th>Jatuh Tempo</th>
                                    <th>Jumlah</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="angsuranTableBody">
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p class="mt-2 text-muted">Memuat data angsuran...</p>
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
// Load angsuran on page load
document.addEventListener('DOMContentLoaded', function() {
    loadAngsuran();
});

/**
 * Load angsuran data
 */
async function loadAngsuran() {
    const url = new URL('<?php echo baseUrl("api/nasabah_portal.php?action=angsuran"); ?>');
    <?php if ($pinjaman_id): ?>
    url.searchParams.append('pinjaman_id', '<?php echo $pinjaman_id; ?>');
    <?php endif; ?>
    
    try {
        const response = await fetch(url);
        const result = await response.json();
        
        if (result.success) {
            renderAngsuran(result.data);
        } else {
            document.getElementById('angsuranTableBody').innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-4 text-danger">
                        ${result.error || 'Gagal memuat data'}
                    </td>
                </tr>
            `;
        }
    } catch (error) {
        console.error('Error:', error);
        document.getElementById('angsuranTableBody').innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-4 text-danger">
                    Terjadi kesalahan saat memuat data
                </td>
            </tr>
        `;
    }
}

/**
 * Render angsuran table
 */
function renderAngsuran(data) {
    if (data.length === 0) {
        document.getElementById('angsuranTableBody').innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-4 text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                    Tidak ada data angsuran
                </td>
            </tr>
        `;
        return;
    }
    
    document.getElementById('angsuranTableBody').innerHTML = data.map((a, index) => {
        const statusClass = getStatusClass(a.status);
        const isLate = a.status === 'belum_bayar' && new Date(a.tanggal_jatuh_tempo) < new Date();
        
        return `
            <tr class="${isLate ? 'table-danger' : ''}">
                <td>${index + 1}</td>
                <td><span class="badge bg-light text-dark">${a.kode_pinjaman}</span></td>
                <td><strong>${a.angsuran_ke}</strong></td>
                <td>${formatDate(a.tanggal_jatuh_tempo)} ${isLate ? '<span class="badge bg-danger ms-1">Terlambat</span>' : ''}</td>
                <td class="fw-bold">${formatRupiah(a.jumlah_angsuran)}</td>
                <td><span class="badge ${statusClass}">${getStatusLabel(a.status)}</span></td>
            </tr>
        `;
    }).join('');
}

/**
 * Get status badge class
 */
function getStatusClass(status) {
    const classes = {
        'lunas': 'bg-success',
        'belum_bayar': 'bg-warning',
        'terlambat': 'bg-danger',
        'default': 'bg-secondary'
    };
    return classes[status] || classes['default'];
}

/**
 * Get status label
 */
function getStatusLabel(status) {
    const labels = {
        'lunas': 'LUNAS',
        'belum_bayar': 'BELUM BAYAR',
        'terlambat': 'TERLAMBAT'
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
