<?php
/**
 * Page: Nasabah Pembayaran
 * 
 * Halaman untuk nasabah melihat riwayat pembayaran
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

$page_title = 'Riwayat Pembayaran';
include BASE_PATH . '/includes/header.php';
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0">
                <i class="bi bi-receipt-cutoff me-2"></i>
                Riwayat Pembayaran
            </h1>
            <p class="text-muted">Daftar pembayaran yang telah dilakukan</p>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-list-check me-2"></i>
                    Histori Pembayaran
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="pembayaranTable">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Kode Pinjaman</th>
                                    <th>Angsuran Ke</th>
                                    <th>Tanggal Bayar</th>
                                    <th>Jumlah Bayar</th>
                                    <th>Metode</th>
                                </tr>
                            </thead>
                            <tbody id="pembayaranTableBody">
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p class="mt-2 text-muted">Memuat data pembayaran...</p>
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
// Load pembayaran on page load
document.addEventListener('DOMContentLoaded', function() {
    loadPembayaran();
});

/**
 * Load pembayaran data
 */
async function loadPembayaran() {
    try {
        const response = await fetch('<?php echo baseUrl("api/nasabah_portal.php?action=pembayaran"); ?>');
        const result = await response.json();
        
        if (result.success) {
            renderPembayaran(result.data);
        } else {
            document.getElementById('pembayaranTableBody').innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-4 text-danger">
                        ${result.error || 'Gagal memuat data'}
                    </td>
                </tr>
            `;
        }
    } catch (error) {
        console.error('Error:', error);
        document.getElementById('pembayaranTableBody').innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-4 text-danger">
                    Terjadi kesalahan saat memuat data
                </td>
            </tr>
        `;
    }
}

/**
 * Render pembayaran table
 */
function renderPembayaran(data) {
    if (data.length === 0) {
        document.getElementById('pembayaranTableBody').innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-4 text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                    Belum ada riwayat pembayaran
                </td>
            </tr>
        `;
        return;
    }
    
    document.getElementById('pembayaranTableBody').innerHTML = data.map((p, index) => {
        return `
            <tr>
                <td>${index + 1}</td>
                <td><span class="badge bg-light text-dark">${p.kode_pinjaman}</span></td>
                <td><strong>${p.angsuran_ke}</strong></td>
                <td>${formatDate(p.tanggal_bayar)}</td>
                <td class="fw-bold text-success">${formatRupiah(p.jumlah_bayar)}</td>
                <td><span class="badge bg-info">${p.metode_pembayaran || 'Tunai'}</span></td>
            </tr>
        `;
    }).join('');
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
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}
</script>

<?php include BASE_PATH . '/includes/footer.php'; ?>
