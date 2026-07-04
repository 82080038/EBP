/**
 * MyWisata Application - Admin JavaScript
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-06-30
 */

$(document).ready(function () {
    // Initialize DataTables
    $('.datatable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
        },
        pageLength: 20,
        lengthMenu: [10, 20, 50, 100]
    });

    // AJAX helper function
    window.ajax = function (options) {
        var defaults = {
            method: 'POST',
            dataType: 'json',
            headers: {
                'X-CSRF-Token': typeof CSRF_TOKEN !== 'undefined' ? CSRF_TOKEN : ''
            },
            beforeSend: function () {
                // Show loading indicator if needed
            },
            error: function (xhr, status, error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Terjadi kesalahan. Silakan coba lagi.',
                    confirmButtonColor: '#0d6efd'
                });
            }
        };

        $.ajax($.extend({}, defaults, options));
    };

    // Approve guide function
    window.approveGuide = function (id) {
        Swal.fire({
            title: 'Setujui Tour Guide?',
            text: 'Apakah Anda yakin ingin menyetujui tour guide ini?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#dc3545',
            confirmButtonText: 'Ya, Setujui',
            cancelButtonText: 'Batal'
        }).then(function (result) {
            if (result.isConfirmed) {
                ajax({
                    url: window.APP_URL + 'admin/approveGuide',
                    method: 'POST',
                    data: { id: id },
                    success: function (response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(function () {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message,
                                confirmButtonColor: '#0d6efd'
                            });
                        }
                    }
                });
            }
        });
    };

    // Delete confirmation
    window.confirmDelete = function (url, message) {
        Swal.fire({
            title: 'Hapus Data?',
            text: message || 'Data yang dihapus tidak dapat dikembalikan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then(function (result) {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    };
});
