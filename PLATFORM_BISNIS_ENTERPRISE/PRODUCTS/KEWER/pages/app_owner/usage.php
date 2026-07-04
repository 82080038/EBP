<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();
$page_title = 'Usage';
?>
<?php include __DIR__ . '/_header.php'; ?>

        <div id="loading-spinner">
            <div class="spinner-border spinner-border-sm" role="status"></div>
        </div>

        <div id="usage-content" style="display: none;">
        <!-- Stats -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body py-3 text-center">
                        <div class="text-primary mb-1"><i class="bi bi-cloud-arrow-up fs-4"></i></div>
                        <h3 class="mb-0" id="statApi">-</h3>
                        <small class="text-muted">API Calls (<span id="daysLabel">30</span>d)</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body py-3 text-center">
                        <div class="text-success mb-1"><i class="bi bi-display fs-4"></i></div>
                        <h3 class="mb-0" id="statRenders">-</h3>
                        <small class="text-muted">Page Renders (<span id="daysLabel2">30</span>d)</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body py-3 text-center">
                        <div class="text-info mb-1"><i class="bi bi-building fs-4"></i></div>
                        <h3 class="mb-0" id="statKoperasi">-</h3>
                        <small class="text-muted">Koperasi Aktif</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body py-3 text-center">
                        <div class="text-warning mb-1"><i class="bi bi-graph-up fs-4"></i></div>
                        <h3 class="mb-0" id="statAvg">-</h3>
                        <small class="text-muted">Avg/day</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Period selector -->
        <div class="mb-3">
            <div class="btn-group btn-group-sm" id="periodSelector">
                <button class="btn btn-outline-dark" data-days="7">7 Hari</button>
                <button class="btn btn-dark" data-days="30">30 Hari</button>
                <button class="btn btn-outline-dark" data-days="60">60 Hari</button>
                <button class="btn btn-outline-dark" data-days="90">90 Hari</button>
            </div>
        </div>

        <div class="row g-3">
            <!-- Per-koperasi -->
            <div class="col-md-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white"><h6 class="mb-0"><i class="bi bi-bar-chart"></i> Usage per Koperasi</h6></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                    <tr><th>Koperasi</th><th class="text-end">API Calls</th><th class="text-end">Renders</th><th class="text-end">Total</th></tr>
                                </thead>
                                <tbody id="koperasiUsageList">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Top Endpoints -->
            <div class="col-md-5">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white"><h6 class="mb-0"><i class="bi bi-list-ol"></i> Top Endpoints</h6></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead class="table-light">
                                    <tr><th>Endpoint</th><th>Tipe</th><th class="text-end">Hits</th></tr>
                                </thead>
                                <tbody id="topEndpointsList">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Daily Trend mini -->
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header bg-white"><h6 class="mb-0"><i class="bi bi-graph-up"></i> Trend Harian</h6></div>
                    <div class="card-body" id="dailyTrendContainer" style="max-height:250px; overflow-y:auto;">
                    </div>
                </div>
            </div>
        </div>
        </div>

<script>
let currentDays = 30;

$(document).ready(function() {
    loadUsageData();
    setupPeriodSelector();
});

function setupPeriodSelector() {
    $('#periodSelector button').on('click', function() {
        $('#periodSelector button').removeClass('btn-dark').addClass('btn-outline-dark');
        $(this).removeClass('btn-outline-dark').addClass('btn-dark');
        currentDays = parseInt($(this).data('days'));
        loadUsageData();
    });
}

function loadUsageData() {
    $('#loading-spinner').show();
    $('#usage-content').hide();
    
    $.ajax({
        url: 'api/business.php',
        method: 'GET',
        data: { action: 'usage_data', days: currentDays },
        success: function(response) {
            if (response.success) {
                renderUsageData(response.data);
                $('#loading-spinner').hide();
                $('#usage-content').show();
            } else {
                showAlert(response.error, 'danger');
            }
        },
        error: function() {
            showAlert('Gagal memuat data usage', 'danger');
        }
    });
}

function renderUsageData(data) {
    // Update stats
    $('#statApi').text(data.totals.api.toLocaleString('id-ID'));
    $('#statRenders').text(data.totals.renders.toLocaleString('id-ID'));
    $('#statKoperasi').text(data.totals.koperasi_count);
    $('#statAvg').text(Math.round(data.totals.avg_per_day).toLocaleString('id-ID'));
    $('#daysLabel, #daysLabel2').text(data.days);

    // Koperasi usage list
    if (data.koperasi_usage.length === 0) {
        $('#koperasiUsageList').html('<tr><td colspan="4" class="text-center text-muted py-3">Belum ada data usage</td></tr>');
    } else {
        let html = '';
        data.koperasi_usage.forEach(ku => {
            html += `
                <tr>
                    <td>
                        <strong>${ku.nama_usaha || ku.bos_nama}</strong>
                        <br><small class="text-muted">${ku.bos_nama}</small>
                    </td>
                    <td class="text-end">${ku.api_calls.toLocaleString('id-ID')}</td>
                    <td class="text-end">${ku.renders.toLocaleString('id-ID')}</td>
                    <td class="text-end"><strong>${ku.total.toLocaleString('id-ID')}</strong></td>
                </tr>
            `;
        });
        $('#koperasiUsageList').html(html);
    }

    // Top endpoints
    if (data.top_endpoints.length === 0) {
        $('#topEndpointsList').html('<tr><td colspan="3" class="text-center text-muted py-3">-</td></tr>');
    } else {
        let html = '';
        data.top_endpoints.forEach(ep => {
            const badgeClass = ep.tipe === 'api_call' ? 'primary' : 'success';
            const label = ep.tipe === 'api_call' ? 'API' : 'Page';
            const endpointName = ep.endpoint.split('/').pop() || ep.endpoint;
            html += `
                <tr>
                    <td><code class="small">${endpointName}</code></td>
                    <td><span class="badge bg-${badgeClass}">${label}</span></td>
                    <td class="text-end">${ep.cnt.toLocaleString('id-ID')}</td>
                </tr>
            `;
        });
        $('#topEndpointsList').html(html);
    }

    // Daily trend
    if (data.daily_trend.length === 0) {
        $('#dailyTrendContainer').html('<div class="text-center text-muted py-3">Belum ada data trend</div>');
    } else {
        const maxTotal = Math.max(...data.daily_trend.map(dt => (parseInt(dt.api) + parseInt(dt.renders)))) || 1;
        let html = '';
        data.daily_trend.forEach(dt => {
            const total = parseInt(dt.api) + parseInt(dt.renders);
            const date = new Date(dt.tanggal);
            const dateStr = date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
            const apiPct = (dt.api / maxTotal) * 100;
            const renderPct = (dt.renders / maxTotal) * 100;
            
            html += `
                <div class="d-flex align-items-center gap-2 mb-1">
                    <small class="text-muted" style="width:60px">${dateStr}</small>
                    <div class="flex-grow-1">
                        <div class="progress" style="height:16px">
                            <div class="progress-bar bg-primary" style="width:${apiPct}%" title="API: ${dt.api.toLocaleString('id-ID')}"></div>
                            <div class="progress-bar bg-success" style="width:${renderPct}%" title="Render: ${dt.renders.toLocaleString('id-ID')}"></div>
                        </div>
                    </div>
                    <small class="text-muted" style="width:50px; text-align:right">${total.toLocaleString('id-ID')}</small>
                </div>
            `;
        });
        $('#dailyTrendContainer').html(html);
    }
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
