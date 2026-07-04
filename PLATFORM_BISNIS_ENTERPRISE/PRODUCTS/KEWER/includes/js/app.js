/**
 * Main Application JavaScript
 * Client-side rendering and data management
 */

(function($) {
    'use strict';

    // Application State
    const AppState = {
        currentPage: null,
        data: {},
        user: null
    };

    // Initialize Application
    $(document).ready(function() {
        initApp();
    });

    function initApp() {
        // Load current user
        loadCurrentUser();
        
        // Initialize notifications
        if (window.KewerNotifications) {
            window.KewerNotifications.updateBadge();
        }

        // Initialize common components
        initCommonComponents();

        // Page-specific initialization
        initPage();
    }

    function loadCurrentUser() {
        window.KewerAPI.getCurrentUser().done(response => {
            AppState.user = response.data;
            $(document).trigger('user:loaded', AppState.user);
        }).fail(error => {
            console.error('Failed to load user:', error);
        });
    }

    function initCommonComponents() {
        // Initialize DataTables with common settings
        $.extend(true, $.fn.dataTable.defaults, {
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
            },
            pageLength: 25,
            responsive: true,
            dom: 'Bfrtip',
            buttons: ['copy', 'csv', 'excel', 'pdf']
        });

        // Initialize Select2
        if ($.fn.select2) {
            $.fn.select2.defaults = {
                theme: 'bootstrap-5',
                width: '100%'
            };
        }

        // Initialize Flatpickr
        if (typeof flatpickr !== 'undefined') {
            flatpickr('.flatpickr', {
                locale: 'id',
                dateFormat: 'Y-m-d',
                allowInput: true
            });
        }

        // Initialize SweetAlert2
        if (typeof Swal !== 'undefined') {
            window.swal = Swal;
        }
    }

    function initPage() {
        const path = window.location.pathname;
        const page = path.split('/').pop().replace('.php', '');

        AppState.currentPage = page;

        // Page-specific initialization
        switch(page) {
            case 'dashboard':
                initDashboard();
                break;
            case 'index':
                const parent = path.split('/').slice(-2)[0];
                switch(parent) {
                    case 'nasabah':
                        initNasabahList();
                        break;
                    case 'pinjaman':
                        initPinjamanList();
                        break;
                    case 'angsuran':
                        initAngsuranList();
                        break;
                    case 'pembayaran':
                        initPembayaranList();
                        break;
                }
                break;
            case 'tambah':
                const parentTambah = path.split('/').slice(-2)[0];
                switch(parentTambah) {
                    case 'nasabah':
                        initNasabahForm();
                        break;
                    case 'pinjaman':
                        initPinjamanForm();
                        break;
                }
                break;
            case 'edit':
                const parentEdit = path.split('/').slice(-2)[0];
                switch(parentEdit) {
                    case 'nasabah':
                        initNasabahEdit();
                        break;
                    case 'pinjaman':
                        initPinjamanEdit();
                        break;
                }
                break;
            case 'detail':
                const parentDetail = path.split('/').slice(-2)[0];
                switch(parentDetail) {
                    case 'nasabah':
                        initNasabahDetail();
                        break;
                    case 'pinjaman':
                        initPinjamanDetail();
                        break;
                }
                break;
        }

        $(document).trigger('page:loaded', { page: page });
    }

    // ============ DASHBOARD ============
    function initDashboard() {
        loadDashboardStats();
        loadDashboardCharts();
        loadDashboardRecent();
    }

    function loadDashboardStats() {
        window.KewerAPI.getDashboardStats().done(response => {
            renderDashboardStats(response.data);
        });
    }

    function loadDashboardCharts() {
        window.KewerAPI.getDashboardCharts().done(response => {
            renderDashboardCharts(response.data);
        });
    }

    function loadDashboardRecent() {
        window.KewerAPI.getDashboardRecent().done(response => {
            renderDashboardRecent(response.data);
        });
    }

    function renderDashboardStats(data) {
        // Implement stats rendering
        $('#stats-container').html(renderStatsCards(data));
    }

    function renderDashboardCharts(data) {
        // Implement charts rendering
        $('#charts-container').html(renderCharts(data));
    }

    function renderDashboardRecent(data) {
        // Implement recent activity rendering
        $('#recent-container').html(renderRecentActivity(data));
    }

    // ============ NASABAH ============
    function initNasabahList() {
        const table = $('#nasabah-table');
        if (table.length) {
            table.ajaxDataTable({
                endpoint: '/nasabah.php',
                columns: [
                    { data: 'kode_nasabah' },
                    { data: 'nama' },
                    { data: 'ktp' },
                    { data: 'telp' },
                    { data: 'jenis_usaha' },
                    { data: 'status' },
                    {
                        data: null,
                        render: function(data, type, row) {
                            return renderActionButtons('nasabah', row.id);
                        }
                    }
                ]
            });
        }
    }

    function initNasabahForm() {
        // Form validation and submission
        $('#form-nasabah').submit(function(e) {
            e.preventDefault();
            const data = $(this).serializeJSON();
            
            window.KewerAPI.createNasabah(data).done(response => {
                Swal.fire('Berhasil', 'Nasabah berhasil ditambahkan', 'success')
                    .then(() => window.location.href = 'index.php');
            }).fail(error => {
                Swal.fire('Error', error.error, 'error');
            });
        });
    }

    function initNasabahEdit() {
        const id = getUrlParam('id');
        loadNasabahData(id);
    }

    function initNasabahDetail() {
        const id = getUrlParam('id');
        loadNasabahData(id);
    }

    function loadNasabahData(id) {
        window.KewerAPI.getNasabahDetail(id).done(response => {
            AppState.data.nasabah = response.data;
            renderNasabahDetail(response.data);
        });
    }

    function renderNasabahDetail(data) {
        // Implement detail rendering
    }

    // ============ PINJAMAN ============
    function initPinjamanList() {
        const table = $('#pinjaman-table');
        if (table.length) {
            table.ajaxDataTable({
                endpoint: '/pinjaman.php',
                columns: [
                    { data: 'kode_pinjaman' },
                    { data: 'nasabah_nama' },
                    { data: 'plafon' },
                    { data: 'tenor' },
                    { data: 'bunga_per_bulan' },
                    { data: 'status' },
                    {
                        data: null,
                        render: function(data, type, row) {
                            return renderActionButtons('pinjaman', row.id);
                        }
                    }
                ]
            });
        }
    }

    function initPinjamanForm() {
        // Form validation and submission
        $('#form-pinjaman').submit(function(e) {
            e.preventDefault();
            const data = $(this).serializeJSON();
            
            window.KewerAPI.createPinjaman(data).done(response => {
                Swal.fire('Berhasil', 'Pinjaman berhasil diajukan', 'success')
                    .then(() => window.location.href = 'index.php');
            }).fail(error => {
                Swal.fire('Error', error.error, 'error');
            });
        });
    }

    function initPinjamanEdit() {
        const id = getUrlParam('id');
        loadPinjamanData(id);
    }

    function initPinjamanDetail() {
        const id = getUrlParam('id');
        loadPinjamanData(id);
    }

    function loadPinjamanData(id) {
        window.KewerAPI.getPinjamanDetail(id).done(response => {
            AppState.data.pinjaman = response.data;
            renderPinjamanDetail(response.data);
        });
    }

    function renderPinjamanDetail(data) {
        // Implement detail rendering
    }

    // ============ ANGSURAN ============
    function initAngsuranList() {
        const table = $('#angsuran-table');
        if (table.length) {
            table.ajaxDataTable({
                endpoint: '/angsuran.php',
                columns: [
                    { data: 'no_angsuran' },
                    { data: 'pinjaman_kode' },
                    { data: 'nasabah_nama' },
                    { data: 'jatuh_tempo' },
                    { data: 'total_bayar' },
                    { data: 'status' },
                    {
                        data: null,
                        render: function(data, type, row) {
                            return renderActionButtons('angsuran', row.id);
                        }
                    }
                ]
            });
        }
    }

    // ============ PEMBAYARAN ============
    function initPembayaranList() {
        const table = $('#pembayaran-table');
        if (table.length) {
            table.ajaxDataTable({
                endpoint: '/pembayaran.php',
                columns: [
                    { data: 'kode_pembayaran' },
                    { data: 'pinjaman_kode' },
                    { data: 'nasabah_nama' },
                    { data: 'tanggal_bayar' },
                    { data: 'jumlah_bayar' },
                    { data: 'cara_bayar' },
                    {
                        data: null,
                        render: function(data, type, row) {
                            return renderActionButtons('pembayaran', row.id);
                        }
                    }
                ]
            });
        }
    }

    // ============ HELPER FUNCTIONS ============
    function getUrlParam(name) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(name);
    }

    function renderActionButtons(type, id) {
        return `
            <div class="btn-group btn-group-sm">
                <a href="${type}/detail.php?id=${id}" class="btn btn-info" title="Detail">
                    <i class="bi bi-eye"></i>
                </a>
                <a href="${type}/edit.php?id=${id}" class="btn btn-warning" title="Edit">
                    <i class="bi bi-pencil"></i>
                </a>
                <button class="btn btn-danger btn-delete" data-id="${id}" data-type="${type}" title="Hapus">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `;
    }

    function renderStatsCards(data) {
        let html = '';
        for (const [key, value] of Object.entries(data)) {
            html += `
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">${formatNumber(value)}</h5>
                            <p class="card-text text-muted">${key}</p>
                        </div>
                    </div>
                </div>
            `;
        }
        return html;
    }

    function formatNumber(num) {
        return new Intl.NumberFormat('id-ID').format(num);
    }

    function formatRupiah(num) {
        return 'Rp ' + formatNumber(num);
    }

    function formatDate(date) {
        return new Date(date).toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        });
    }

    // Global functions
    window.KewerApp = {
        AppState,
        loadCurrentUser,
        initPage,
        formatNumber,
        formatRupiah,
        formatDate
    };

    // Delete button handler
    $(document).on('click', '.btn-delete', function() {
        const id = $(this).data('id');
        const type = $(this).data('type');
        
        Swal.fire({
            title: 'Konfirmasi Hapus',
            text: 'Apakah Anda yakin ingin menghapus data ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                deleteRecord(type, id);
            }
        });
    });

    function deleteRecord(type, id) {
        let apiMethod;
        switch(type) {
            case 'nasabah':
                apiMethod = window.KewerAPI.deleteNasabah(id);
                break;
            case 'pinjaman':
                apiMethod = window.KewerAPI.deletePinjaman(id);
                break;
            case 'cabang':
                apiMethod = window.KewerAPI.deleteCabang(id);
                break;
            default:
                Swal.fire('Error', 'Tipe tidak dikenali', 'error');
                return;
        }

        apiMethod.done(response => {
            Swal.fire('Berhasil', 'Data berhasil dihapus', 'success')
                .then(() => location.reload());
        }).fail(error => {
            Swal.fire('Error', error.error, 'error');
        });
    }

})(jQuery);
