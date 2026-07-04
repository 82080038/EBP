<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();
$page_title = 'Settings';
?>
<?php include __DIR__ . '/_header.php'; ?>

        <div id="alert-container"></div>

        <div class="accordion" id="settingsAccordion">
            <!-- Profile Section -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#profileSection">
                        <i class="bi bi-person-circle me-2"></i> Profil
                    </button>
                </h2>
                <div id="profileSection" class="accordion-collapse collapse show" data-bs-parent="#settingsAccordion">
                    <div class="accordion-body">
                        <div class="spinner-border spinner-border-sm" role="status"></div>
                    </div>
                </div>
            </div>

            <!-- Password Section -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#passwordSection">
                        <i class="bi bi-key me-2"></i> Ganti Password
                    </button>
                </h2>
                <div id="passwordSection" class="accordion-collapse collapse" data-bs-parent="#settingsAccordion">
                    <div class="accordion-body">
                        <form id="passwordForm">
                            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                            <input type="hidden" name="action" value="change_password">
                            <div class="mb-3">
                                <label class="form-label">Password Lama</label>
                                <input type="password" name="old_password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password Baru</label>
                                <input type="password" name="new_password" class="form-control" required minlength="8">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Konfirmasi Password</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-warning"><i class="bi bi-key"></i> Ubah Password</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Billing Plans Section -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#billingSection">
                        <i class="bi bi-receipt me-2"></i> Billing Plans
                    </button>
                </h2>
                <div id="billingSection" class="accordion-collapse collapse" data-bs-parent="#settingsAccordion">
                    <div class="accordion-body p-0">
                        <div class="spinner-border spinner-border-sm" role="status"></div>
                    </div>
                </div>
            </div>

            <!-- Bank Accounts Section -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#bankSection">
                        <i class="bi bi-bank me-2"></i> Rekening Bank Platform
                    </button>
                </h2>
                <div id="bankSection" class="accordion-collapse collapse" data-bs-parent="#settingsAccordion">
                    <div class="accordion-body">
                        <div class="spinner-border spinner-border-sm" role="status"></div>
                    </div>
                </div>
            </div>

            <!-- Platform Info Section -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#platformSection">
                        <i class="bi bi-info-circle me-2"></i> Platform Info
                    </button>
                </h2>
                <div id="platformSection" class="accordion-collapse collapse" data-bs-parent="#settingsAccordion">
                    <div class="accordion-body">
                        <div class="spinner-border spinner-border-sm" role="status"></div>
                    </div>
                </div>
            </div>
        </div>

<script>
$(document).ready(function() {
    loadProfile();
    loadBillingPlans();
    loadBankAccounts();
    loadPlatformInfo();
    setupPasswordForm();
});

function showAlert(message, type = 'success') {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    $('#alert-container').html(alertHtml);
}

function loadProfile() {
    window.KewerAPI.getCurrentUser().done(response => {
        if (response.success) {
            const user = response.data;
            const html = `
                <form id="profileForm">
                    <input type="hidden" name="csrf_token" value="${window.csrfToken || ''}">
                    <input type="hidden" name="action" value="update_profile">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" value="${user.username}" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama</label>
                        <input type="text" name="nama" class="form-control" value="${user.nama}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="${user.email || ''}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <input type="text" class="form-control" value="App Owner" disabled>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check"></i> Simpan</button>
                </form>
            `;
            $('#profileSection .accordion-body').html(html);
            setupProfileForm();
        }
    });
}

function setupProfileForm() {
    $('#profileForm').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize();
        $.ajax({
            url: 'pages/app_owner/settings.php',
            method: 'POST',
            data: formData,
            success: function(response) {
                showAlert('Profil berhasil diperbarui');
                loadProfile();
            },
            error: function() {
                showAlert('Gagal memperbarui profil', 'danger');
            }
        });
    });
}

function setupPasswordForm() {
    $('#passwordForm').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize();
        $.ajax({
            url: 'pages/app_owner/settings.php',
            method: 'POST',
            data: formData,
            success: function(response) {
                showAlert('Password berhasil diubah');
                $('#passwordForm')[0].reset();
            },
            error: function(xhr) {
                const error = xhr.responseJSON?.error || 'Gagal mengubah password';
                showAlert(error, 'danger');
            }
        });
    });
}

function loadBillingPlans() {
    $.ajax({
        url: 'api/business.php',
        method: 'GET',
        data: { action: 'billing_plans' },
        success: function(response) {
            if (response.success && response.data) {
                const plans = response.data;
                let html = '<div class="table-responsive"><table class="table table-sm table-hover mb-0"><thead class="table-light"><tr><th>Kode</th><th>Nama</th><th>Tipe</th><th>Harga/bln</th><th>% Profit</th><th>Per API</th><th>Per Render</th><th>Limits</th><th>Status</th></tr></thead><tbody>';
                plans.forEach(p => {
                    html += `
                        <tr>
                            <td><code>${p.kode}</code></td>
                            <td><strong>${p.nama}</strong></td>
                            <td><span class="badge bg-light text-dark">${p.tipe}</span></td>
                            <td>Rp ${p.harga_bulanan.toLocaleString('id-ID')}</td>
                            <td>${p.persentase_keuntungan > 0 ? p.persentase_keuntungan + '%' : '-'}</td>
                            <td>${p.harga_per_api_call > 0 ? 'Rp ' + p.harga_per_api_call.toLocaleString('id-ID') : '-'}</td>
                            <td>${p.harga_per_render > 0 ? 'Rp ' + p.harga_per_render.toLocaleString('id-ID') : '-'}</td>
                            <td class="small">
                                ${p.max_users ? p.max_users + ' users' : '∞'} /
                                ${p.max_cabang ? p.max_cabang + ' cabang' : '∞'} /
                                ${p.max_nasabah ? p.max_nasabah + ' nasabah' : '∞'}
                            </td>
                            <td>
                                <button class="btn btn-sm btn-${p.is_active ? 'success' : 'secondary'}" onclick="togglePlanStatus(${p.id}, ${p.is_active ? 0 : 1})">
                                    ${p.is_active ? 'Aktif' : 'Nonaktif'}
                                </button>
                            </td>
                        </tr>
                    `;
                });
                html += '</tbody></table></div>';
                $('#billingSection .accordion-body').html(html);
            }
        },
        error: function() {
            $('#billingSection .accordion-body').html('<div class="alert alert-danger">Gagal memuat billing plans</div>');
        }
    });
}

function togglePlanStatus(planId, newStatus) {
    const formData = new FormData();
    formData.append('csrf_token', window.csrfToken || '');
    formData.append('action', 'manage_plan');
    formData.append('plan_id', planId);
    formData.append('field', 'is_active');
    formData.append('value', newStatus);
    
    $.ajax({
        url: 'pages/app_owner/settings.php',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function() {
            showAlert('Billing plan berhasil diperbarui');
            loadBillingPlans();
        },
        error: function() {
            showAlert('Gagal memperbarui billing plan', 'danger');
        }
    });
}

function loadBankAccounts() {
    $.ajax({
        url: 'api/business.php',
        method: 'GET',
        data: { action: 'bank_accounts' },
        success: function(response) {
            if (response.success && response.data) {
                const accounts = response.data;
                let html = `
                    <form id="addBankForm" class="mb-4">
                        <input type="hidden" name="csrf_token" value="${window.csrfToken || ''}">
                        <input type="hidden" name="action" value="add_bank_account">
                        <div class="row g-2 mb-2">
                            <div class="col-md-3">
                                <label class="form-label small mb-0">Tipe Pembayaran</label>
                                <select name="tipe_pembayaran" class="form-select form-select-sm" id="tipe_pembayaran" onchange="togglePaymentFields()" required>
                                    <option value="bank">Bank Tradisional</option>
                                    <option value="mobile_banking">Mobile Banking</option>
                                    <option value="ewallet">E-Wallet (DANA/OVO/GoPay)</option>
                                    <option value="qris">QRIS</option>
                                    <option value="virtual_account">Virtual Account</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small mb-0" id="label_nama_bank">Nama Bank/Aplikasi</label>
                                <input type="text" name="nama_bank" class="form-control form-control-sm" placeholder="Contoh: BCA / DANA" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small mb-0" id="label_nomor">Nomor Rekening/HP</label>
                                <input type="text" name="nomor_rekening" class="form-control form-control-sm" id="field_nomor_rekening" placeholder="Nomor Rekening">
                                <input type="text" name="nomor_hp" class="form-control form-control-sm d-none" id="field_nomor_hp" placeholder="08xxxxxxxx">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small mb-0" id="label_pemilik">Nama Pemilik</label>
                                <input type="text" name="nama_pemilik" class="form-control form-control-sm" id="field_nama_pemilik" placeholder="Nama Pemilik" required>
                            </div>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-md-3">
                                <label class="form-label small mb-0" id="label_cabang">Cabang (opsional)</label>
                                <input type="text" name="cabang" class="form-control form-control-sm" placeholder="Cabang">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small mb-0 d-none" id="label_qris">QR Code String/URL</label>
                                <input type="text" name="qris_code" class="form-control form-control-sm d-none" id="field_qris_code" placeholder="Paste QR code string atau URL">
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_primary" id="is_primary">
                                    <label class="form-check-label small" for="is_primary">Utama</label>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm ms-2"><i class="bi bi-plus"></i> Tambah</button>
                            </div>
                        </div>
                        <div class="row g-2">
                            <div class="col-md-12">
                                <label class="form-label small mb-0">Keterangan (opsional)</label>
                                <input type="text" name="keterangan" class="form-control form-control-sm" placeholder="Catatan tambahan">
                            </div>
                        </div>
                    </form>
                `;
                
                if (accounts.length > 0) {
                    html += '<div class="table-responsive"><table class="table table-sm table-hover mb-0"><thead class="table-light"><tr><th>Tipe</th><th>Bank/Aplikasi</th><th>Nomor</th><th>Pemilik</th><th>Status</th><th>Aksi</th></tr></thead><tbody>';
                    accounts.forEach(acc => {
                        const tipeLabels = { bank: 'Bank', mobile_banking: 'Mobile Banking', ewallet: 'E-Wallet', qris: 'QRIS', virtual_account: 'VA' };
                        const tipeColors = { bank: 'primary', mobile_banking: 'info', ewallet: 'success', qris: 'warning', virtual_account: 'secondary' };
                        html += `
                            <tr>
                                <td><span class="badge bg-${tipeColors[acc.tipe_pembayaran] || 'secondary'}">${tipeLabels[acc.tipe_pembayaran] || acc.tipe_pembayaran}</span></td>
                                <td><strong>${acc.nama_bank || ''}</strong></td>
                                <td>${acc.tipe_pembayaran === 'ewallet' ? acc.nomor_hp : (acc.tipe_pembayaran === 'qris' ? 'QR Code' : acc.nomor_rekening)}</td>
                                <td>${acc.nama_pemilik || '-'}</td>
                                <td>${acc.is_primary ? '<span class="badge bg-primary">Utama</span>' : '<span class="badge bg-secondary">Cadangan</span>'}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        ${!acc.is_primary ? `<button class="btn btn-outline-primary btn-sm" onclick="setPrimaryBank(${acc.id})" title="Jadikan Utama"><i class="bi bi-star"></i></button>` : ''}
                                        <button class="btn btn-outline-danger btn-sm" onclick="deleteBankAccount(${acc.id})" title="Hapus"><i class="bi bi-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                        `;
                    });
                    html += '</tbody></table></div>';
                } else {
                    html += '<div class="text-center text-muted py-3">Belum ada metode pembayaran yang terdaftar</div>';
                }
                
                $('#bankSection .accordion-body').html(html);
                setupBankForm();
                togglePaymentFields();
            }
        },
        error: function() {
            $('#bankSection .accordion-body').html('<div class="alert alert-danger">Gagal memuat rekening bank</div>');
        }
    });
}

function setupBankForm() {
    $('#addBankForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        $.ajax({
            url: 'pages/app_owner/settings.php',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function() {
                showAlert('Metode pembayaran berhasil ditambahkan');
                loadBankAccounts();
            },
            error: function(xhr) {
                const error = xhr.responseJSON?.error || 'Gagal menambah metode pembayaran';
                showAlert(error, 'danger');
            }
        });
    });
}

function togglePaymentFields() {
    const tipe = document.getElementById('tipe_pembayaran').value;
    const labelNamaBank = document.getElementById('label_nama_bank');
    const labelNomor = document.getElementById('label_nomor');
    const labelPemilik = document.getElementById('label_pemilik');
    const labelCabang = document.getElementById('label_cabang');
    const labelQris = document.getElementById('label_qris');
    const fieldNomorRekening = document.getElementById('field_nomor_rekening');
    const fieldNomorHp = document.getElementById('field_nomor_hp');
    const fieldNamaPemilik = document.getElementById('field_nama_pemilik');
    const fieldQrisCode = document.getElementById('field_qris_code');

    labelNamaBank.textContent = 'Nama Bank/Aplikasi';
    labelNomor.classList.remove('d-none');
    labelPemilik.classList.remove('d-none');
    labelCabang.classList.remove('d-none');
    labelQris.classList.add('d-none');
    fieldNomorRekening.classList.remove('d-none');
    fieldNomorHp.classList.add('d-none');
    fieldNamaPemilik.classList.remove('d-none');
    fieldQrisCode.classList.add('d-none');

    if (tipe === 'bank' || tipe === 'mobile_banking') {
        labelNamaBank.textContent = 'Nama Bank';
        labelNomor.textContent = 'Nomor Rekening';
        labelPemilik.textContent = 'Nama Pemilik';
        fieldNomorRekening.required = true;
        fieldNamaPemilik.required = true;
    } else if (tipe === 'ewallet') {
        labelNamaBank.textContent = 'Nama Aplikasi';
        labelNomor.textContent = 'Nomor HP';
        labelPemilik.textContent = 'Nama Pemilik';
        labelCabang.classList.add('d-none');
        fieldNomorRekening.classList.add('d-none');
        fieldNomorHp.classList.remove('d-none');
        fieldNomorHp.required = true;
        fieldNamaPemilik.required = true;
    } else if (tipe === 'qris') {
        labelNamaBank.textContent = 'Nama Merchant';
        labelNomor.classList.add('d-none');
        labelPemilik.classList.add('d-none');
        labelCabang.classList.add('d-none');
        labelQris.classList.remove('d-none');
        fieldNomorRekening.classList.add('d-none');
        fieldNamaPemilik.classList.remove('d-none');
        fieldNamaPemilik.required = false;
        fieldQrisCode.classList.remove('d-none');
        fieldQrisCode.required = true;
    } else if (tipe === 'virtual_account') {
        labelNamaBank.textContent = 'Nama Bank';
        labelNomor.textContent = 'Nomor VA';
        labelPemilik.classList.add('d-none');
        labelCabang.classList.add('d-none');
        fieldNomorRekening.required = true;
        fieldNamaPemilik.required = false;
    }
}

function setPrimaryBank(accountId) {
    const formData = new FormData();
    formData.append('csrf_token', window.csrfToken || '');
    formData.append('action', 'set_primary_bank');
    formData.append('account_id', accountId);
    
    $.ajax({
        url: 'pages/app_owner/settings.php',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function() {
            showAlert('Rekening bank utama berhasil diubah');
            loadBankAccounts();
        },
        error: function() {
            showAlert('Gagal mengubah rekening bank utama', 'danger');
        }
    });
}

function deleteBankAccount(accountId) {
    if (!confirm('Hapus metode pembayaran ini?')) return;
    
    const formData = new FormData();
    formData.append('csrf_token', window.csrfToken || '');
    formData.append('action', 'delete_bank_account');
    formData.append('account_id', accountId);
    
    $.ajax({
        url: 'pages/app_owner/settings.php',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function() {
            showAlert('Rekening bank berhasil dihapus');
            loadBankAccounts();
        },
        error: function() {
            showAlert('Gagal menghapus rekening bank', 'danger');
        }
    });
}

function loadPlatformInfo() {
    $.ajax({
        url: 'api/business.php',
        method: 'GET',
        data: { action: 'platform_stats' },
        success: function(response) {
            if (response.success && response.data) {
                const stats = response.data;
                const html = `
                    <div class="row text-center">
                        <div class="col-md-3"><h4 class="mb-0">${stats.total_koperasi || 0}</h4><small class="text-muted">Koperasi</small></div>
                        <div class="col-md-3"><h4 class="mb-0">${stats.total_invoices || 0}</h4><small class="text-muted">Invoices</small></div>
                        <div class="col-md-3"><h4 class="mb-0">${stats.total_advice || 0}</h4><small class="text-muted">AI Advice</small></div>
                        <div class="col-md-3"><h4 class="mb-0">${window.APP_NAME || 'Kewer'}</h4><small class="text-muted">v1.0</small></div>
                    </div>
                `;
                $('#platformSection .accordion-body').html(html);
            }
        },
        error: function() {
            $('#platformSection .accordion-body').html('<div class="alert alert-danger">Gagal memuat platform info</div>');
        }
    });
}
</script>

<?php include __DIR__ . '/_footer.php'; ?>

