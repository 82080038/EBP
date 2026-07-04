<?php
/**
 * SweetAlert2 Helper Functions
 * 
 * Provides helper functions for SweetAlert2 alerts and toasts
 */

/**
 * Show SweetAlert2 alert (PHP side)
 * Use this to output SweetAlert2 JavaScript code
 */
function showAlert($type, $title, $message = '', $timer = 3000) {
    $swal_types = [
        'success' => 'success',
        'error' => 'error',
        'warning' => 'warning',
        'info' => 'info',
        'question' => 'question'
    ];
    
    $swal_type = $swal_types[$type] ?? 'info';
    
    echo "<script>
        Swal.fire({
            icon: '{$swal_type}',
            title: '{$title}',
            text: '{$message}',
            timer: {$timer},
            timerProgressBar: true,
            showConfirmButton: false
        });
    </script>";
}

/**
 * Show SweetAlert2 toast (PHP side)
 */
function showToast($type, $title, $position = 'top-end', $timer = 3000) {
    $swal_types = [
        'success' => 'success',
        'error' => 'error',
        'warning' => 'warning',
        'info' => 'info'
    ];
    
    $swal_type = $swal_types[$type] ?? 'info';
    
    echo "<script>
        const Toast = Swal.mixin({
            toast: true,
            position: '{$position}',
            showConfirmButton: false,
            timer: {$timer},
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });
        
        Toast.fire({
            icon: '{$swal_type}',
            title: '{$title}'
        });
    </script>";
}

/**
 * Show SweetAlert2 confirm dialog (PHP side)
 */
function showConfirm($title, $text, $confirmText = 'Ya', $cancelText = 'Tidak', $callback = '') {
    echo "<script>
        Swal.fire({
            title: '{$title}',
            text: '{$text}',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: '{$confirmText}',
            cancelButtonText: '{$cancelText}'
        }).then((result) => {
            if (result.isConfirmed) {
                {$callback}
            }
        });
    </script>";
}

/**
 * Convert PHP session alerts to SweetAlert2
 * Call this at the end of pages to convert session alerts to SweetAlert2
 */
function convertSessionAlertsToSweetAlert() {
    if (isset($_SESSION['success'])) {
        showAlert('success', 'Berhasil', $_SESSION['success']);
        unset($_SESSION['success']);
    }
    if (isset($_SESSION['error'])) {
        showAlert('error', 'Gagal', $_SESSION['error']);
        unset($_SESSION['error']);
    }
    if (isset($_SESSION['warning'])) {
        showAlert('warning', 'Peringatan', $_SESSION['warning']);
        unset($_SESSION['warning']);
    }
    if (isset($_SESSION['info'])) {
        showAlert('info', 'Informasi', $_SESSION['info']);
        unset($_SESSION['info']);
    }
}
?>
