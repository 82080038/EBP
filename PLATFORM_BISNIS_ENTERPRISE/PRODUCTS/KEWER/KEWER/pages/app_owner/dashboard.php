<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();
$page_title = 'Dashboard';
?>
<?php include __DIR__ . '/_header.php'; ?>

        <div id="alert-container"></div>

        <div id="loading-spinner">
            <div class="spinner-border spinner-border-sm" role="status"></div>
        </div>

        <div id="dashboard-content" style="display: none;">
        <!-- Stats Row 1: Platform -->
        <div class="row g-3 mb-3">
            <div class="col-md-2 col-6">
                <div class="card stat-card">
                    <div class="card-body py-3 text-center">
                        <div class="text-warning mb-1"><i class="bi bi-hourglass-split fs-4"></i></div>
                        <h3 class="mb-0" id="totalPending">-</h3>
                        <small class="text-muted">Pending</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="card stat-card">
                    <div class="card-body py-3 text-center">
                        <div class="text-success mb-1"><i class="bi bi-building-check fs-4"></i></div>
                        <h3 class="mb-0" id="totalApproved">-</h3>
                        <small class="text-muted">Koperasi Aktif</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="card stat-card">
                    <div class="card-body py-3 text-center">
                        <div class="text-primary mb-1"><i class="bi bi-people fs-4"></i></div>
                        <h3 class="mb-0" id="totalUsers">-</h3>
                        <small class="text-muted">Total User</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="card stat-card">
                    <div class="card-body py-3 text-center">
                        <div class="text-info mb-1"><i class="bi bi-currency-dollar fs-4"></i></div>
                        <h3 class="mb-0" id="totalRevenue">-</h3>
                        <small class="text-muted">Revenue</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="card stat-card">
                    <div class="card-body py-3 text-center">
                        <div class="text-secondary mb-1"><i class="bi bi-cloud-arrow-up fs-4"></i></div>
                        <h3 class="mb-0" id="todayApi">-</h3>
                        <small class="text-muted">API Today</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="card stat-card">
                    <div class="card-body py-3 text-center">
                        <div class="text-danger mb-1"><i class="bi bi-display fs-4"></i></div>
                        <h3 class="mb-0" id="todayRenders">-</h3>
                        <small class="text-muted">Renders Today</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <!-- Recent Registrations -->
            <div class="col-md-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="bi bi-clock-history"></i> Pendaftaran Terbaru</h6>
                        <a href="<?php echo baseUrl('pages/app_owner/approvals.php'); ?>" class="btn btn-sm btn-outline-primary">Semua</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                    <tr><th>Nama</th><th>Koperasi</th><th>Tanggal</th><th>Status</th></tr>
                                </thead>
                                <tbody id="recentRegistrations">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- AI Advisor -->
            <div class="col-md-5">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="bi bi-robot"></i> AI Advisor</h6>
                        <a href="<?php echo baseUrl('pages/app_owner/ai_advisor.php'); ?>" class="btn btn-sm btn-outline-primary">Semua</a>
                    </div>
                    <div class="card-body" id="recentAdvice">
                    </div>
                </div>
            </div>
        </div>
        </div>

<script>
$(document).ready(function() {
    loadDashboard();
});

function loadDashboard() {
    $.ajax({
        url: 'api/business.php',
        method: 'GET',
        data: { action: 'app_owner_dashboard' },
        success: function(response) {
            if (response.success) {
                renderDashboard(response.data);
                $('#loading-spinner').hide();
                $('#dashboard-content').show();
            } else {
                showAlert(response.error, 'danger');
            }
        },
        error: function() {
            showAlert('Gagal memuat data dashboard', 'danger');
        }
    });
}

function renderDashboard(data) {
    // Stats
    $('#totalPending').text(data.stats.total_pending);
    $('#totalApproved').text(data.stats.total_approved);
    $('#totalUsers').text(data.stats.total_users);
    $('#totalRevenue').text('Rp ' + data.stats.total_revenue.toLocaleString('id-ID'));
    $('#todayApi').text(data.stats.today_api.toLocaleString('id-ID'));
    $('#todayRenders').text(data.stats.today_renders.toLocaleString('id-ID'));

    // Recent registrations
    let regHtml = '';
    if (data.recent_registrations.length === 0) {
        regHtml = '<tr><td colspan="4" class="text-center text-muted py-3">Belum ada pendaftaran</td></tr>';
    } else {
        data.recent_registrations.forEach(reg => {
            const sc = { 'pending': 'badge-pending', 'approved': 'badge-approved', 'rejected': 'badge-rejected' };
            regHtml += `
                <tr>
                    <td><strong>${reg.nama}</strong><br><small class="text-muted">${reg.username}</small></td>
                    <td>${reg.nama_usaha || '-'}</td>
                    <td><small>${new Date(reg.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })}</small></td>
                    <td>
                        <span class="badge ${sc[reg.status] || 'bg-secondary'}">${reg.status.charAt(0).toUpperCase() + reg.status.slice(1)}</span>
                    </td>
                </tr>
            `;
        });
    }
    $('#recentRegistrations').html(regHtml);

    // Recent advice
    let adviceHtml = '';
    if (data.recent_advice.length === 0) {
        adviceHtml = '<p class="text-muted text-center py-3"><i class="bi bi-robot fs-3 d-block mb-2"></i>Belum ada saran AI. Generate saran dari halaman AI Advisor.</p>';
    } else {
        data.recent_advice.forEach(adv => {
            const pcolor = { 'kritis': 'danger', 'tinggi': 'warning', 'sedang': 'info', 'rendah': 'secondary' };
            adviceHtml += `
                <div class="d-flex gap-2 mb-3 pb-2 border-bottom">
                    <span class="badge bg-${pcolor[adv.prioritas] || 'secondary'} align-self-start">${adv.prioritas}</span>
                    <div>
                        <strong class="small">${adv.judul}</strong>
                        <br><small class="text-muted">${adv.kategori.charAt(0).toUpperCase() + adv.kategori.slice(1)} · ${new Date(adv.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' })}</small>
                    </div>
                </div>
            `;
        });
    }
    $('#recentAdvice').html(adviceHtml);
}

function showAlert(message, type = 'success') {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    $('#alert-container').html(alertHtml);
}
</script>

<?php include __DIR__ . '/_footer.php'; ?>
