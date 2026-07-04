<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();
$page_title = 'Koperasi';
?>
<?php include __DIR__ . '/_header.php'; ?>

        <div id="alert-container"></div>

        <div id="loading-spinner">
            <div class="spinner-border spinner-border-sm" role="status"></div>
        </div>

        <div id="koperasi-content" style="display: none;">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-building"></i> Koperasi Terdaftar (<span id="koperasiCount">-</span>)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Koperasi</th>
                                <th>Bos</th>
                                <th>Billing Plan</th>
                                <th>Terdaftar</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="koperasiList">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        </div>

        <!-- Assign Plan Modal -->
        <div class="modal fade" id="planModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Assign Billing Plan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Koperasi: <strong id="planKoperasiName"></strong></p>
                        <div id="currentPlanInfo" class="alert alert-info small"></div>
                        <div class="mb-3">
                            <label class="form-label">Pilih Plan</label>
                            <select id="billingPlanSelect" class="form-select" required>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-primary" id="confirmAssignPlanBtn">Assign Plan</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Generate Invoice Modal -->
        <div class="modal fade" id="invoiceModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Generate Invoice</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Koperasi: <strong id="invoiceKoperasiName"></strong></p>
                        <div id="invoicePlanInfo" class="alert alert-info small"></div>
                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label">Bulan</label>
                                    <select id="invoiceBulanSelect" class="form-select" required>
                                    </select>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label">Tahun</label>
                                    <input type="number" id="invoiceTahunInput" class="form-control" value="<?php echo date('Y'); ?>" min="2024" max="2030" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-success" id="confirmGenerateInvoiceBtn">Generate Invoice</button>
                    </div>
                </div>
            </div>
        </div>

<script>
let plansData = [];
let selectedKoperasiId = null;

$(document).ready(function() {
    loadKoperasiData();
    setupModals();
});

function loadKoperasiData() {
    $('#loading-spinner').show();
    $('#koperasi-content').hide();
    
    $.ajax({
        url: 'api/business.php',
        method: 'GET',
        data: { action: 'koperasi_data' },
        success: function(response) {
            if (response.success) {
                plansData = response.data.plans;
                renderKoperasiData(response.data);
                $('#loading-spinner').hide();
                $('#koperasi-content').show();
            } else {
                showAlert(response.error, 'danger');
            }
        },
        error: function() {
            showAlert('Gagal memuat data koperasi', 'danger');
        }
    });
}

function renderKoperasiData(data) {
    $('#koperasiCount').text(data.koperasi_list.length);
    
    if (data.koperasi_list.length === 0) {
        $('#koperasiList').html('<tr><td colspan="7" class="text-center text-muted py-4">Belum ada koperasi terdaftar</td></tr>');
        return;
    }

    let html = '';
    data.koperasi_list.forEach((kop, i) => {
        let statusBadge = '';
        if (kop.user_status === 'aktif') {
            if (!kop.plan_nama) {
                statusBadge = '<span class="badge bg-warning text-dark">Aktif (Belum ada billing)</span>';
            } else {
                statusBadge = '<span class="badge bg-success">Aktif</span>';
            }
        } else {
            statusBadge = '<span class="badge bg-secondary">Suspended</span>';
        }

        let planHtml = kop.plan_nama 
            ? `<span class="badge bg-primary">${kop.plan_nama}</span><br><small class="text-muted">${kop.plan_tipe === 'fixed' ? 'Rp ' + kop.harga_bulanan.toLocaleString('id-ID') + '/bln' : kop.plan_tipe.charAt(0).toUpperCase() + kop.plan_tipe.slice(1)}</small>`
            : '<span class="text-muted">-</span>';

        let actionsHtml = '';
        if (kop.user_id) {
            actionsHtml = '<div class="btn-group btn-group-sm">';
            
            // Assign Billing
            actionsHtml += `<button class="btn btn-outline-primary btn-sm" onclick="showAssignPlanModal(${kop.user_id}, '${kop.nama_usaha || kop.nama}', '${kop.plan_nama || ''}')" title="Assign Plan"><i class="bi bi-receipt"></i></button>`;
            
            // Generate Invoice (only if has billing plan)
            if (kop.plan_nama) {
                actionsHtml += `<button class="btn btn-outline-success btn-sm" onclick="showGenerateInvoiceModal(${kop.user_id}, '${kop.nama_usaha || kop.nama}', '${kop.plan_nama}', '${kop.plan_tipe}', ${kop.harga_bulanan || 0})" title="Generate Invoice"><i class="bi bi-file-earmark-plus"></i></button>`;
            }
            
            // Suspend/Activate
            if (kop.user_status === 'aktif') {
                actionsHtml += `<button class="btn btn-outline-danger btn-sm" onclick="suspendKoperasi(${kop.user_id})" title="Suspend"><i class="bi bi-pause-circle"></i></button>`;
            } else {
                actionsHtml += `<button class="btn btn-outline-success btn-sm" onclick="activateKoperasi(${kop.user_id})" title="Activate"><i class="bi bi-play-circle"></i></button>`;
            }
            
            actionsHtml += '</div>';
        }

        html += `
            <tr>
                <td>${i + 1}</td>
                <td>
                    <strong>${kop.nama_usaha || '-'}</strong>
                    ${kop.alamat_usaha ? `<br><small class="text-muted">${kop.alamat_usaha}</small>` : ''}
                </td>
                <td>
                    ${kop.nama}
                    <br><small class="text-muted"><code>${kop.username}</code></small>
                    ${kop.email ? `<br><small>${kop.email}</small>` : ''}
                </td>
                <td>${planHtml}</td>
                <td><small>${kop.approved_at ? new Date(kop.approved_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '-'}</small></td>
                <td>${statusBadge}</td>
                <td>${actionsHtml}</td>
            </tr>
        `;
    });
    
    $('#koperasiList').html(html);
}

function setupModals() {
    $('#confirmAssignPlanBtn').on('click', function() {
        const planId = parseInt($('#billingPlanSelect').val());
        if (!planId) {
            showAlert('Pilih billing plan', 'danger');
            return;
        }
        
        $.ajax({
            url: 'api/business.php',
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            data: JSON.stringify({
                action: 'assign_billing_plan',
                bos_user_id: selectedKoperasiId,
                billing_plan_id: planId
            }),
            success: function(response) {
                if (response.success) {
                    showAlert(response.message);
                    $('#planModal').modal('hide');
                    loadKoperasiData();
                } else {
                    showAlert(response.error, 'danger');
                }
            },
            error: function(xhr) {
                const error = xhr.responseJSON?.error || 'Gagal assign billing plan';
                showAlert(error, 'danger');
            }
        });
    });

    $('#confirmGenerateInvoiceBtn').on('click', function() {
        const bulan = parseInt($('#invoiceBulanSelect').val());
        const tahun = parseInt($('#invoiceTahunInput').val());
        
        if (!bulan || !tahun) {
            showAlert('Pilih bulan dan tahun', 'danger');
            return;
        }
        
        $.ajax({
            url: 'api/business.php',
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            data: JSON.stringify({
                action: 'generate_koperasi_invoice',
                bos_user_id: selectedKoperasiId,
                bulan: bulan,
                tahun: tahun
            }),
            success: function(response) {
                if (response.success) {
                    showAlert(response.message);
                    $('#invoiceModal').modal('hide');
                    loadKoperasiData();
                } else {
                    showAlert(response.error, 'danger');
                }
            },
            error: function(xhr) {
                const error = xhr.responseJSON?.error || 'Gagal generate invoice';
                showAlert(error, 'danger');
            }
        });
    });
}

function showAssignPlanModal(userId, koperasiName, currentPlan) {
    selectedKoperasiId = userId;
    $('#planKoperasiName').text(koperasiName);
    
    if (currentPlan) {
        $('#currentPlanInfo').html(`Plan saat ini: <strong>${currentPlan}</strong>`).show();
    } else {
        $('#currentPlanInfo').hide();
    }
    
    let optionsHtml = '<option value="">-- Pilih Plan --</option>';
    plansData.forEach(p => {
        const price = p.tipe === 'fixed' ? 'Rp ' + p.harga_bulanan.toLocaleString('id-ID') + '/bln' : (p.tipe === 'percentage' ? p.persentase_keuntungan + '% revenue' : 'Usage-based');
        optionsHtml += `<option value="${p.id}">${p.nama} (${price})</option>`;
    });
    $('#billingPlanSelect').html(optionsHtml);
    
    $('#planModal').modal('show');
}

function showGenerateInvoiceModal(userId, koperasiName, planNama, planTipe, hargaBulanan) {
    selectedKoperasiId = userId;
    $('#invoiceKoperasiName').text(koperasiName);
    
    const planInfo = planTipe === 'fixed' ? 'Rp ' + hargaBulanan.toLocaleString('id-ID') + '/bln' : planTipe.charAt(0).toUpperCase() + planTipe.slice(1);
    $('#invoicePlanInfo').html(`Plan saat ini: <strong>${planNama}</strong> (${planInfo})`);
    
    const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    let optionsHtml = '';
    for (let m = 1; m <= 12; m++) {
        optionsHtml += `<option value="${m}" ${m === new Date().getMonth() + 1 ? 'selected' : ''}>${months[m-1]}</option>`;
    }
    $('#invoiceBulanSelect').html(optionsHtml);
    
    $('#invoiceModal').modal('show');
}

function suspendKoperasi(userId) {
    if (!confirm('Suspend koperasi ini?')) return;
    
    selectedKoperasiId = userId;
    
    $.ajax({
        url: 'api/business.php',
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        data: JSON.stringify({
            action: 'suspend_koperasi',
            bos_user_id: userId
        }),
        success: function(response) {
            if (response.success) {
                showAlert(response.message);
                loadKoperasiData();
            } else {
                showAlert(response.error, 'danger');
            }
        },
        error: function(xhr) {
            const error = xhr.responseJSON?.error || 'Gagal suspend koperasi';
            showAlert(error, 'danger');
        }
    });
}

function activateKoperasi(userId) {
    if (!confirm('Aktifkan kembali koperasi ini?')) return;
    
    selectedKoperasiId = userId;
    
    $.ajax({
        url: 'api/business.php',
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        data: JSON.stringify({
            action: 'activate_koperasi',
            bos_user_id: userId
        }),
        success: function(response) {
            if (response.success) {
                showAlert(response.message);
                loadKoperasiData();
            } else {
                showAlert(response.error, 'danger');
            }
        },
        error: function(xhr) {
            const error = xhr.responseJSON?.error || 'Gagal aktifkan koperasi';
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
