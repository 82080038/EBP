/**
 * MyWisata Application - Main JavaScript
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-06-30
 */

$(document).ready(function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // AJAX helper function
    window.ajax = function(options) {
        var defaults = {
            method: 'POST',
            dataType: 'json',
            headers: {
                'X-CSRF-Token': typeof CSRF_TOKEN !== 'undefined' ? CSRF_TOKEN : ''
            },
            beforeSend: function() {
                // Show loading indicator if needed
            },
            error: function(xhr, status, error) {
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
    
    // Format currency helper
    window.formatCurrency = function(amount) {
        return 'Rp ' + parseFloat(amount).toLocaleString('id-ID');
    };
    
    // Format date helper
    window.formatDate = function(dateString, format) {
        var date = new Date(dateString);
        var options = { year: 'numeric', month: 'long', day: 'numeric' };
        return date.toLocaleDateString('id-ID', options);
    };
});
