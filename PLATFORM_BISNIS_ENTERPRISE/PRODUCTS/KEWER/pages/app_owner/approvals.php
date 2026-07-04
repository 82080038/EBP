<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();
$page_title = 'Persetujuan Bos';
?>
<?php include __DIR__ . '/_header.php'; ?>

        <div id="alert-container"></div>

        <div id="loading-spinner">
            <div class="spinner-border spinner-border-sm" role="status"></div>
        </div>

        <div id="approvals-content" style="display: none;">
        <!-- Filter tabs -->
        <ul class="nav nav-tabs mb-4" id="filterTabs">
            <li class="nav-item">
                <a class="nav-link active" href="#" data-status="pending">
                    <i class="bi bi-hourglass-split"></i> Pending <span class="badge bg-warning text-dark" id="pendingCount">-</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" data-status="approved">
                    <i class="bi bi-check-circle"></i> Approved <span class="badge bg-success" id="approvedCount">-</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" data-status="rejected">
                    <i class="bi bi-x-circle"></i> Rejected <span class="badge bg-danger" id="rejectedCount">-</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" data-status="all">
                    <i class="bi bi-list"></i> Semua
                </a>
            </li>
        </ul>

        <!-- Registrations list -->
        <div id="registrationsList">
        </div>
        </div>

        <!-- Reject Modal -->
        <div class="modal fade" id="rejectModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Tolak Pendaftaran</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-start">
                        <p>Tolak pendaftaran <strong id="rejectName"></strong> (<span id="rejectBusiness"></span>)?</p>
                        <div class="mb-3">
                            <label class="form-label">Alasan Penolakan</label>
                            <textarea id="rejectionReason" class="form-control" rows="3" required placeholder="Jelaskan alasan penolakan..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-danger" id="confirmRejectBtn">Tolak Pendaftaran</button>
                    </div>
                </div>
            </div>
        </div>

<script>
let currentFilter = 'pending';
let rejectRegistrationId = null;

$(document).ready(function() {
    loadRegistrations();
    setupFilterTabs();
    setupRejectModal();
});

function setupFilterTabs() {
    $('#filterTabs .nav-link').on('click', function(e) {
        e.preventDefault();
        $('#filterTabs .nav-link').removeClass('active');
        $(this).addClass('active');
        currentFilter = $(this).data('status');
        loadRegistrations();
    });
}

function loadRegistrations() {
    $('#loading-spinner').show();
    $('#approvals-content').hide();
    
    $.ajax({
        url: 'api/business.php',
        method: 'GET',
        data: { action: 'bos_registrations', status: currentFilter },
        success: function(response) {
            if (response.success) {
                renderRegistrations(response.data);
                $('#loading-spinner').hide();
                $('#approvals-content').show();
            } else {
                showAlert(response.error, 'danger');
            }
        },
        error: function() {
            showAlert('Gagal memuat data pendaftaran', 'danger');
        }
    });
}

function renderRegistrations(data) {
    // Update stats
    $('#pendingCount').text(data.stats.pending);
    $('#approvedCount').text(data.stats.approved);
    $('#rejectedCount').text(data.stats.rejected);

    // Render list
    if (data.registrations.length === 0) {
        const statusText = currentFilter !== 'all' ? `dengan status '${currentFilter}'` : '';
        $('#registrationsList').html(`
            <div class="text-center text-muted py-5">
                <i class="bi bi-inbox" style="font-size:3rem;"></i>
                <p class="mt-2">Tidak ada pendaftaran ${statusText}</p>
            </div>
        `);
        return;
    }

    let html = '';
    data.registrations.forEach(reg => {
        const date = new Date(reg.created_at);
        const dateStr = date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
        const timeStr = date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        
        let actionsHtml = '';
        if (reg.status === 'pending') {
            actionsHtml = `
                <button class="btn btn-success btn-sm me-1" onclick="approveRegistration(${reg.id})">
                    <i class="bi bi-check-lg"></i> Setujui
                </button>
                <button class="btn btn-danger btn-sm" onclick="showRejectModal(${reg.id}, '${reg.nama}', '${reg.nama_usaha || ''}')">
                    <i class="bi bi-x-lg"></i> Tolak
                </button>
            `;
        } else if (reg.status === 'approved') {
            actionsHtml = `
                <span class="badge bg-success px-3 py-2"><i class="bi bi-check-circle"></i> Approved</span>
                ${reg.approved_at ? `<br><small class="text-muted">${new Date(reg.approved_at).toLocaleString('id-ID')}</small>` : ''}
            `;
        } else {
            actionsHtml = `
                <span class="badge bg-danger px-3 py-2"><i class="bi bi-x-circle"></i> Rejected</span>
                ${reg.rejected_reason ? `<br><small class="text-muted" title="${reg.rejected_reason}">${reg.rejected_reason.substring(0, 50)}${reg.rejected_reason.length > 50 ? '...' : ''}</small>` : ''}
            `;
        }

        html += `
            <div class="card mb-3 border-0 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-1">${reg.nama}</h5>
                            <p class="mb-1 text-muted">
                                <i class="bi bi-building"></i> <strong>${reg.nama_usaha || '-'}</strong>
                            </p>
                            <small class="text-muted">
                                <i class="bi bi-person"></i> ${reg.username} &nbsp;
                                ${reg.email ? `<i class="bi bi-envelope"></i> ${reg.email} &nbsp;` : ''}
                                ${reg.telp ? `<i class="bi bi-telephone"></i> ${reg.telp}` : ''}
                            </small>
                            ${reg.alamat_usaha ? `<br><small class="text-muted"><i class="bi bi-geo-alt"></i> ${reg.alamat_usaha}</small>` : ''}
                        </div>
                        <div class="col-md-3 text-center">
                            <small class="text-muted d-block">Tanggal Daftar</small>
                            <strong>${dateStr}</strong>
                            <br><small class="text-muted">${timeStr} WIB</small>
                        </div>
                        <div class="col-md-3 text-end">
                            ${actionsHtml}
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    $('#registrationsList').html(html);
}

function approveRegistration(registrationId) {
    if (!confirm('Setujui pendaftaran ini?')) return;
    
    $.ajax({
        url: 'api/business.php',
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        data: JSON.stringify({
            action: 'approve_bos_registration',
            registration_id: registrationId
        }),
        success: function(response) {
            if (response.success) {
                showAlert(response.message);
                loadRegistrations();
            } else {
                showAlert(response.error, 'danger');
            }
        },
        error: function(xhr) {
            const error = xhr.responseJSON?.error || 'Gagal menyetujui pendaftaran';
            showAlert(error, 'danger');
        }
    });
}

function setupRejectModal() {
    $('#confirmRejectBtn').on('click', function() {
        const reason = $('#rejectionReason').val().trim();
        if (!reason) {
            showAlert('Alasan penolakan wajib diisi', 'danger');
            return;
        }
        
        $.ajax({
            url: 'api/business.php',
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            data: JSON.stringify({
                action: 'reject_bos_registration',
                registration_id: rejectRegistrationId,
                rejection_reason: reason
            }),
            success: function(response) {
                if (response.success) {
                    showAlert(response.message);
                    $('#rejectModal').modal('hide');
                    $('#rejectionReason').val('');
                    loadRegistrations();
                } else {
                    showAlert(response.error, 'danger');
                }
            },
            error: function(xhr) {
                const error = xhr.responseJSON?.error || 'Gagal menolak pendaftaran';
                showAlert(error, 'danger');
            }
        });
    });
}

function showRejectModal(registrationId, nama, namaUsaha) {
    rejectRegistrationId = registrationId;
    $('#rejectName').text(nama);
    $('#rejectBusiness').text(namaUsaha);
    $('#rejectionReason').val('');
    $('#rejectModal').modal('show');
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
