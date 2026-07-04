<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();
$page_title = 'Billing';
?>
<?php include __DIR__ . '/_header.php'; ?>

        <div id="alert-container"></div>

        <div id="loading-spinner">
            <div class="spinner-border spinner-border-sm" role="status"></div>
        </div>

        <div id="billing-content" style="display: none;">
        <!-- Billing Plans -->
        <div class="row g-3 mb-4" id="billingPlans">
        </div>

        <!-- Generate & Filter -->
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="d-flex gap-2 align-items-end">
                    <div>
                        <label class="form-label small mb-0">Bulan</label>
                        <select id="bulanSelect" class="form-select form-select-sm">
                        </select>
                    </div>
                    <div>
                        <label class="form-label small mb-0">Tahun</label>
                        <input type="number" id="tahunInput" class="form-control form-control-sm" value="<?php echo date('Y'); ?>" min="2024" max="2030">
                    </div>
                    <button class="btn btn-primary btn-sm" onclick="generateInvoices()">
                        <i class="bi bi-plus-circle"></i> Generate Invoice
                    </button>
                </div>
            </div>
            <div class="col-md-6 text-end">
                <div class="d-inline-flex gap-2 align-items-center">
                    <span class="small text-muted">Total: <strong id="sumTotal">-</strong></span>
                    <span class="small text-success">Dibayar: <strong id="sumPaid">-</strong></span>
                </div>
            </div>
        </div>

        <!-- Payment Method Info -->
        <div id="paymentMethodInfo">
        </div>

        <!-- Invoice list -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-receipt"></i> Invoice <span id="invoicePeriod"></span></h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Kode</th>
                                <th>Koperasi</th>
                                <th>Plan</th>
                                <th>Fixed</th>
                                <th>% Revenue</th>
                                <th>Usage</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="invoicesList">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        </div>

<script>
let currentMonth = new Date().getMonth() + 1;
let currentYear = new Date().getFullYear();

$(document).ready(function() {
    loadBillingData();
    setupMonthSelect();
});

function setupMonthSelect() {
    const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    let html = '';
    for (let m = 1; m <= 12; m++) {
        html += `<option value="${m}" ${m === currentMonth ? 'selected' : ''}>${months[m-1]}</option>`;
    }
    $('#bulanSelect').html(html);
    
    $('#bulanSelect, #tahunInput').on('change', function() {
        currentMonth = parseInt($('#bulanSelect').val());
        currentYear = parseInt($('#tahunInput').val());
        loadBillingData();
    });
}

function loadBillingData() {
    $('#loading-spinner').show();
    $('#billing-content').hide();
    
    $.ajax({
        url: 'api/business.php',
        method: 'GET',
        data: { action: 'billing_data', bulan: currentMonth, tahun: currentYear },
        success: function(response) {
            if (response.success) {
                renderBillingData(response.data);
                $('#loading-spinner').hide();
                $('#billing-content').show();
            } else {
                showAlert(response.error, 'danger');
            }
        },
        error: function() {
            showAlert('Gagal memuat data billing', 'danger');
        }
    });
}

function renderBillingData(data) {
    // Billing plans
    let plansHtml = '';
    data.plans.forEach(p => {
        let priceHtml = '';
        if (p.tipe === 'fixed') {
            priceHtml = `<h5 class="text-primary mb-0">Rp ${p.harga_bulanan.toLocaleString('id-ID')}<small class="text-muted">/bln</small></h5>`;
        } else if (p.tipe === 'percentage') {
            priceHtml = `<h5 class="text-success mb-0">${p.persentase_keuntungan}%<small class="text-muted"> revenue</small></h5>`;
        } else {
            priceHtml = `<h5 class="text-warning mb-0">Usage<small class="text-muted">-based</small></h5>`;
        }
        
        plansHtml += `
            <div class="col-md-4 col-lg">
                <div class="card stat-card h-100">
                    <div class="card-body text-center py-3">
                        <h6 class="mb-1">${p.nama}</h6>
                        <div class="text-muted small mb-1">${p.tipe.charAt(0).toUpperCase() + p.tipe.slice(1)}</div>
                        ${priceHtml}
                        <div class="mt-1"><span class="badge bg-secondary">${p.subscribers} subscribers</span></div>
                    </div>
                </div>
            </div>
        `;
    });
    $('#billingPlans').html(plansHtml);

    // Summary
    $('#sumTotal').text('Rp ' + data.summary.total.toLocaleString('id-ID'));
    $('#sumPaid').text('Rp ' + data.summary.paid.toLocaleString('id-ID'));

    // Payment method info
    if (data.primary_bank) {
        const tipeLabels = {
            'bank': 'Bank',
            'mobile_banking': 'Mobile Banking',
            'ewallet': 'E-Wallet',
            'qris': 'QRIS',
            'virtual_account': 'Virtual Account'
        };
        
        let accountInfo = '';
        if (data.primary_bank.tipe_pembayaran === 'ewallet') {
            accountInfo = `No. HP: <code>${data.primary_bank.nomor_hp || ''}</code>`;
        } else if (data.primary_bank.tipe_pembayaran === 'qris') {
            accountInfo = 'QR Code';
        } else {
            accountInfo = `No. Rek: <code>${data.primary_bank.nomor_rekening || ''}</code>`;
        }
        
        $('#paymentMethodInfo').html(`
            <div class="alert alert-info alert-dismissible fade show mb-3">
                <strong><i class="bi bi-bank"></i> Metode Pembayaran Utama:</strong>
                <span class="badge bg-primary">${tipeLabels[data.primary_bank.tipe_pembayaran] || data.primary_bank.tipe_pembayaran.charAt(0).toUpperCase() + data.primary_bank.tipe_pembayaran.slice(1)}</span>
                ${data.primary_bank.bank_name || ''} - ${accountInfo}
                ${data.primary_bank.nama_pemilik ? ' - a.n. ' + data.primary_bank.nama_pemilik : ''}
                ${data.primary_bank.cabang ? ' (' + data.primary_bank.cabang + ')' : ''}
            </div>
        `);
    } else {
        $('#paymentMethodInfo').html(`
            <div class="alert alert-warning mb-3">
                <strong><i class="bi bi-exclamation-triangle"></i> Belum ada metode pembayaran utama.</strong>
                Silakan set metode pembayaran di <a href="${baseUrl('pages/app_owner/settings.php')}">Settings</a> agar koperasi bisa melihat info pembayaran.
            </div>
        `);
    }

    // Invoice period
    const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    $('#invoicePeriod').text(`${months[data.filter.month - 1]} ${data.filter.year}`);

    // Invoices list
    if (data.invoices.length === 0) {
        $('#invoicesList').html('<tr><td colspan="9" class="text-center text-muted py-3">Belum ada invoice untuk periode ini</td></tr>');
        return;
    }

    let invoicesHtml = '';
    data.invoices.forEach(inv => {
        const statusColors = {
            'draft': 'secondary',
            'terbit': 'warning',
            'dibayar': 'success',
            'overdue': 'danger',
            'cancelled': 'dark'
        };
        
        let actionHtml = '';
        if (inv.status === 'terbit') {
            actionHtml = `<button class="btn btn-success btn-sm" onclick="markInvoicePaid(${inv.id})"><i class="bi bi-check2"></i> Dibayar</button>`;
        } else if (inv.status === 'dibayar') {
            actionHtml = `<small class="text-success"><i class="bi bi-check-circle-fill"></i> ${inv.tanggal_bayar ? new Date(inv.tanggal_bayar).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }) : ''}</small>`;
        }

        invoicesHtml += `
            <tr>
                <td><code class="small">${inv.kode_invoice}</code></td>
                <td><strong>${inv.nama_usaha || inv.bos_nama}</strong></td>
                <td><span class="badge bg-light text-dark">${inv.plan_nama}</span></td>
                <td class="text-end"><small>${inv.biaya_fixed > 0 ? inv.biaya_fixed.toLocaleString('id-ID') : '-'}</small></td>
                <td class="text-end"><small>${inv.biaya_persentase > 0 ? inv.biaya_persentase.toLocaleString('id-ID') : '-'}</small></td>
                <td class="text-end"><small>${inv.biaya_usage > 0 ? inv.biaya_usage.toLocaleString('id-ID') : '-'}</small></td>
                <td class="text-end"><strong>Rp ${inv.total.toLocaleString('id-ID')}</strong></td>
                <td>
                    <span class="badge bg-${statusColors[inv.status] || 'secondary'}">${inv.status.charAt(0).toUpperCase() + inv.status.slice(1)}</span>
                </td>
                <td>${actionHtml}</td>
            </tr>
        `;
    });
    $('#invoicesList').html(invoicesHtml);
}

function generateInvoices() {
    if (!confirm('Generate invoice untuk semua koperasi?')) return;
    
    const bulan = parseInt($('#bulanSelect').val());
    const tahun = parseInt($('#tahunInput').val());
    
    $.ajax({
        url: 'api/business.php',
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        data: JSON.stringify({
            action: 'generate_invoices',
            bulan: bulan,
            tahun: tahun
        }),
        success: function(response) {
            if (response.success) {
                showAlert(response.message);
                loadBillingData();
            } else {
                showAlert(response.error, 'danger');
            }
        },
        error: function(xhr) {
            const error = xhr.responseJSON?.error || 'Gagal generate invoice';
            showAlert(error, 'danger');
        }
    });
}

function markInvoicePaid(invoiceId) {
    if (!confirm('Tandai invoice ini sebagai dibayar?')) return;
    
    $.ajax({
        url: 'api/business.php',
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        data: JSON.stringify({
            action: 'mark_invoice_paid',
            invoice_id: invoiceId,
            metode_bayar: 'transfer'
        }),
        success: function(response) {
            if (response.success) {
                showAlert(response.message);
                loadBillingData();
            } else {
                showAlert(response.error, 'danger');
            }
        },
        error: function(xhr) {
            const error = xhr.responseJSON?.error || 'Gagal menandai invoice sebagai dibayar';
            showAlert(error, 'danger');
        }
    });
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
