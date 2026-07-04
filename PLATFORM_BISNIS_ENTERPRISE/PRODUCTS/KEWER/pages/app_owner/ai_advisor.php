<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();
$page_title = 'AI Advisor';
?>
<?php include __DIR__ . '/_header.php'; ?>

        <div id="alert-container"></div>

        <div id="loading-spinner">
            <div class="spinner-border spinner-border-sm" role="status"></div>
        </div>

        <div id="advisor-content" style="display: none;">
        <!-- Controls -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="d-flex gap-2 align-items-end">
                    <div>
                        <label class="form-label small mb-0">Target Koperasi</label>
                        <select id="targetBosSelect" class="form-select form-select-sm">
                        </select>
                    </div>
                    <button class="btn btn-primary btn-sm" onclick="generateAdvice()">
                        <i class="bi bi-robot"></i> Generate Saran
                    </button>
                </div>
            </div>
            <div class="col-md-6 text-end">
                <span class="badge bg-warning text-dark"><i class="bi bi-bell"></i> <span id="statBaru">-</span> saran baru</span>
            </div>
        </div>

        <!-- Filter -->
        <div class="mb-3">
            <div class="btn-group btn-group-sm" id="kategoriFilter">
            </div>
            <button id="clearFilterBtn" class="btn btn-sm btn-outline-secondary ms-2" style="display:none;">× Hapus filter koperasi</button>
        </div>

        <!-- Advice list -->
        <div id="adviceList">
        </div>
        </div>

<script>
let bosListData = [];
let currentFilterKategori = 'all';
let currentFilterBos = '';

$(document).ready(function() {
    loadAdvisorData();
    setupKategoriFilter();
});

function setupKategoriFilter() {
    const kats = [
        { key: 'all', label: 'Semua' },
        { key: 'pertumbuhan', label: 'Pertumbuhan' },
        { key: 'risiko', label: 'Risiko' },
        { key: 'efisiensi', label: 'Efisiensi' },
        { key: 'produk', label: 'Produk' },
        { key: 'umum', label: 'Umum' }
    ];
    
    let html = '';
    kats.forEach(k => {
        html += `<button class="btn btn-outline-dark kategori-btn" data-kategori="${k.key}">${k.label}</button>`;
    });
    $('#kategoriFilter').html(html);
    
    $('#kategoriFilter').on('click', '.kategori-btn', function() {
        $('#kategoriFilter button').removeClass('btn-dark').addClass('btn-outline-dark');
        $(this).removeClass('btn-outline-dark').addClass('btn-dark');
        currentFilterKategori = $(this).data('kategori');
        loadAdvisorData();
    });
    
    // Set initial state
    $('#kategoriFilter button[data-kategori="all"]').removeClass('btn-outline-dark').addClass('btn-dark');
    
    $('#clearFilterBtn').on('click', function() {
        currentFilterBos = '';
        $(this).hide();
        loadAdvisorData();
    });
}

function loadAdvisorData() {
    $('#loading-spinner').show();
    $('#advisor-content').hide();
    
    $.ajax({
        url: 'api/business.php',
        method: 'GET',
        data: { action: 'ai_advisor_data', kategori: currentFilterKategori, bos: currentFilterBos },
        success: function(response) {
            if (response.success) {
                bosListData = response.data.bos_list;
                renderAdvisorData(response.data);
                $('#loading-spinner').hide();
                $('#advisor-content').show();
            } else {
                showAlert(response.error, 'danger');
            }
        },
        error: function() {
            showAlert('Gagal memuat data advisor', 'danger');
        }
    });
}

function renderAdvisorData(data) {
    // Update stats
    $('#statBaru').text(data.stats.baru);
    
    // Populate bos select
    let selectHtml = '<option value="all">Semua Koperasi</option>';
    data.bos_list.forEach(b => {
        selectHtml += `<option value="${b.id}">${b.nama_usaha || b.nama}</option>`;
    });
    $('#targetBosSelect').html(selectHtml);
    
    // Update kategori filter buttons
    $('#kategoriFilter button').removeClass('btn-dark').addClass('btn-outline-dark');
    $(`#kategoriFilter button[data-kategori="${data.filter.kategori}"]`).removeClass('btn-outline-dark').addClass('btn-dark');
    
    // Show/hide clear filter button
    if (data.filter.bos) {
        $('#clearFilterBtn').show();
    } else {
        $('#clearFilterBtn').hide();
    }
    
    // Render advice list
    if (data.advice_list.length === 0) {
        $('#adviceList').html(`
            <div class="text-center text-muted py-5">
                <i class="bi bi-robot" style="font-size:3rem"></i>
                <p class="mt-2">Belum ada saran AI. Klik "Generate Saran" untuk menganalisis koperasi.</p>
            </div>
        `);
        return;
    }
    
    const pcolor = { 'kritis': 'danger', 'tinggi': 'warning', 'sedang': 'info', 'rendah': 'secondary' };
    const kicon = { 'pertumbuhan': 'graph-up-arrow', 'risiko': 'exclamation-triangle', 'efisiensi': 'speedometer2', 'produk': 'box-seam', 'umum': 'info-circle' };
    
    let html = '';
    data.advice_list.forEach(adv => {
        const borderClass = adv.status === 'baru' ? `border-start border-4 border-${pcolor[adv.prioritas] || 'secondary'}` : '';
        const koperasiLink = adv.nama_usaha ? `<a href="#" onclick="filterByKoperasi(${adv.bos_user_id}); return false;" class="badge bg-primary text-decoration-none">${adv.nama_usaha}</a>` : '';
        const baruBadge = adv.status === 'baru' ? '<span class="badge bg-warning text-dark">Baru</span>' : '';
        const readAction = adv.status === 'baru' 
            ? `<button class="btn btn-outline-primary btn-sm" onclick="markAsRead(${adv.id})"><i class="bi bi-check2"></i> Dibaca</button>`
            : '<small class="text-success"><i class="bi bi-check-circle"></i></small>';
        
        let dataBadges = '';
        if (adv.data_pendukung) {
            const data = JSON.parse(adv.data_pendukung);
            if (data) {
                Object.keys(data).forEach(key => {
                    const val = isNumeric(data[key]) ? parseInt(data[key]).toLocaleString('id-ID') : data[key];
                    dataBadges += `<span class="badge bg-light text-dark me-1">${key.replace('_', ' ')}: <strong>${val}</strong></span>`;
                });
            }
        }
        
        html += `
            <div class="card border-0 shadow-sm mb-3 ${borderClass}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="d-flex gap-2 mb-2">
                                <span class="badge bg-${pcolor[adv.prioritas] || 'secondary'}">${adv.prioritas.charAt(0).toUpperCase() + adv.prioritas.slice(1)}</span>
                                <span class="badge bg-light text-dark"><i class="bi bi-${kicon[adv.kategori] || 'info-circle'}"></i> ${adv.kategori.charAt(0).toUpperCase() + adv.kategori.slice(1)}</span>
                                ${koperasiLink}
                                ${baruBadge}
                            </div>
                            <h6 class="mb-2">${adv.judul}</h6>
                            <div class="text-muted small" style="white-space: pre-line;">${adv.isi}</div>
                            ${dataBadges ? `<div class="mt-2">${dataBadges}</div>` : ''}
                        </div>
                        <div class="text-end ms-3">
                            <small class="text-muted d-block">${new Date(adv.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })}</small>
                            <div class="mt-1">${readAction}</div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    $('#adviceList').html(html);
}

function generateAdvice() {
    if (!confirm('Generate saran AI untuk koperasi terpilih?')) return;
    
    const targetBos = $('#targetBosSelect').val();
    
    $.ajax({
        url: 'api/business.php',
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        data: JSON.stringify({
            action: 'generate_ai_advice',
            bos_user_id: targetBos
        }),
        success: function(response) {
            if (response.success) {
                showAlert(response.message);
                loadAdvisorData();
            } else {
                showAlert(response.error, 'danger');
            }
        },
        error: function(xhr) {
            const error = xhr.responseJSON?.error || 'Gagal generate saran AI';
            showAlert(error, 'danger');
        }
    });
}

function markAsRead(adviceId) {
    $.ajax({
        url: 'api/business.php',
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        data: JSON.stringify({
            action: 'mark_advice_read',
            advice_id: adviceId
        }),
        success: function(response) {
            if (response.success) {
                showAlert(response.message);
                loadAdvisorData();
            } else {
                showAlert(response.error, 'danger');
            }
        },
        error: function(xhr) {
            const error = xhr.responseJSON?.error || 'Gagal menandai saran sebagai dibaca';
            showAlert(error, 'danger');
        }
    });
}

function filterByKoperasi(bosId) {
    currentFilterBos = bosId;
    loadAdvisorData();
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

function isNumeric(n) {
    return !isNaN(parseFloat(n)) && isFinite(n);
}
</script>

<?php include __DIR__ . '/_footer.php'; ?>
