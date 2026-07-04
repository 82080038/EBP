<?php
/**
 * Page: Nasabah Dashboard
 * 
 * Halaman dashboard untuk nasabah melihat ringkasan pinjaman dan pembayaran
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

$page_title = 'Dashboard Nasabah';
include BASE_PATH . '/includes/header.php';
?>

<div class="container py-4">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">Selamat Datang, <span id="nasabahNama">...</span></h1>
                    <p class="text-muted mb-0">Kode Nasabah: <strong id="nasabahKode">...</strong></p>
                </div>
                <div class="text-end">
                    <span id="nasabahStatus" class="badge bg-secondary fs-6">...</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-white-50">Pinjaman Aktif</h6>
                            <h3 class="mb-0" id="activeLoans">-</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-credit-card fs-1 text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-white-50">Sisa Hutang</h6>
                            <h3 class="mb-0" id="totalSisa">-</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-cash-stack fs-1 text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-white-50">Total Dibayar</h6>
                            <h3 class="mb-0" id="totalPaid">-</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-check-circle fs-1 text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-white-50">Pembayaran</h6>
                            <h3 class="mb-0" id="paymentCount">-</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-receipt fs-1 text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Next Payment Alert -->
    <div class="row mb-4" id="nextPaymentSection" style="display: none;">
        <div class="col-12">
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <i class="bi bi-calendar-event me-2"></i>
                    Pembayaran Berikutnya
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <h5 class="card-title" id="nextPaymentAmount">-</h5>
                            <p class="card-text">Angsuran ke-<span id="nextPaymentNumber">-</span></p>
                        </div>
                        <div class="col-md-4">
                            <h5 class="card-title" id="nextPaymentDate">-</h5>
                            <p class="card-text">Jatuh Tempo</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="<?php echo baseUrl('pages/nasabah/angsuran.php'); ?>" class="btn btn-warning">
                                <i class="bi bi-list me-1"></i> Lihat Jadwal
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <h5 class="mb-3">Menu Cepat</h5>
            <div class="row g-3">
                <div class="col-md-3">
                    <a href="<?php echo baseUrl('pages/nasabah/pinjaman.php'); ?>" class="card text-decoration-none h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-credit-card-2-front fs-1 text-primary mb-2"></i>
                            <h6 class="card-title">Pinjaman Saya</h6>
                            <p class="card-text small text-muted">Lihat status dan detail pinjaman</p>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="<?php echo baseUrl('pages/nasabah/angsuran.php'); ?>" class="card text-decoration-none h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-calendar-check fs-1 text-success mb-2"></i>
                            <h6 class="card-title">Jadwal Angsuran</h6>
                            <p class="card-text small text-muted">Lihat jadwal pembayaran</p>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="<?php echo baseUrl('pages/nasabah/pembayaran.php'); ?>" class="card text-decoration-none h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-receipt-cutoff fs-1 text-info mb-2"></i>
                            <h6 class="card-title">Riwayat Pembayaran</h6>
                            <p class="card-text small text-muted">Lihat bukti pembayaran</p>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="<?php echo baseUrl('pages/nasabah/profil.php'); ?>" class="card text-decoration-none h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-person-circle fs-1 text-secondary mb-2"></i>
                            <h6 class="card-title">Profil Saya</h6>
                            <p class="card-text small text-muted">Lihat dan update profil</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- No Active Loan Message -->
    <div class="row" id="noLoanMessage" style="display: none;">
        <div class="col-12">
            <div class="alert alert-info">
                <h5 class="alert-heading"><i class="bi bi-info-circle me-2"></i>Tidak Ada Pinjaman Aktif</h5>
                <p class="mb-0">Anda saat ini tidak memiliki pinjaman aktif. Silakan hubungi petugas koperasi untuk mengajukan pinjaman baru.</p>
            </div>
        </div>
    </div>
</div>

<script>
// Load dashboard data on page load
document.addEventListener('DOMContentLoaded', function() {
    loadDashboard();
});

/**
 * Load dashboard data
 */
async function loadDashboard() {
    try {
        const response = await fetch('<?php echo baseUrl("api/nasabah_portal.php?action=dashboard"); ?>');
        const result = await response.json();
        
        if (result.success) {
            updateDashboard(result.data);
        } else {
            showAlert('error', result.error || 'Gagal memuat data dashboard');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('error', 'Terjadi kesalahan saat memuat data');
    }
}

/**
 * Update dashboard UI
 */
function updateDashboard(data) {
    // Nasabah info
    document.getElementById('nasabahNama').textContent = data.nasabah.nama;
    document.getElementById('nasabahKode').textContent = data.nasabah.kode;
    
    // Status badge
    const statusBadge = document.getElementById('nasabahStatus');
    if (data.nasabah.blacklist) {
        statusBadge.className = 'badge bg-danger fs-6';
        statusBadge.innerHTML = '<i class="bi bi-x-octagon me-1"></i> BLACKLIST';
    } else if (data.nasabah.status === 'aktif') {
        statusBadge.className = 'badge bg-success fs-6';
        statusBadge.innerHTML = '<i class="bi bi-check-circle me-1"></i> AKTIF';
    } else {
        statusBadge.className = 'badge bg-secondary fs-6';
        statusBadge.textContent = data.nasabah.status.toUpperCase();
    }
    
    // Summary cards
    document.getElementById('activeLoans').textContent = data.summary.active_loans;
    document.getElementById('totalSisa').textContent = formatRupiah(data.summary.total_sisa);
    document.getElementById('totalPaid').textContent = formatRupiah(data.summary.total_paid);
    document.getElementById('paymentCount').textContent = data.summary.payment_count;
    
    // Next payment
    if (data.next_payment) {
        document.getElementById('nextPaymentSection').style.display = 'block';
        document.getElementById('nextPaymentAmount').textContent = formatRupiah(data.next_payment.jumlah);
        document.getElementById('nextPaymentNumber').textContent = data.next_payment.angsuran_ke;
        document.getElementById('nextPaymentDate').textContent = formatDate(data.next_payment.jatuh_tempo);
    } else {
        document.getElementById('nextPaymentSection').style.display = 'none';
    }
    
    // No loan message
    if (data.summary.active_loans === 0) {
        document.getElementById('noLoanMessage').style.display = 'block';
    } else {
        document.getElementById('noLoanMessage').style.display = 'none';
    }
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
        month: 'long',
        year: 'numeric'
    });
}

/**
 * Show alert
 */
function showAlert(type, message) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: type,
            title: type === 'success' ? 'Berhasil!' : 'Error!',
            text: message,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
        });
    } else {
        alert(message);
    }
}
</script>

<?php include BASE_PATH . '/includes/footer.php'; ?>
