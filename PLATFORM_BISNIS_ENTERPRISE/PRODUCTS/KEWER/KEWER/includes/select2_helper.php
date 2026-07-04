<?php
/**
 * Select2 Helper Functions
 * 
 * Provides helper functions for Select2 integration
 */

/**
 * Get Select2 CDN links
 */
function getSelect2CDN() {
    return [
        'css' => 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css',
        'css_bootstrap5' => 'https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css',
        'js' => 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js',
        'js_i18n_id' => 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/id.js'
    ];
}

/**
 * Initialize Select2 with default configuration
 */
function initSelect2($selector = '.form-select', $options = []) {
    $defaultOptions = [
        'theme' => 'bootstrap-5',
        'language' => 'id',
        'width' => '100%',
        'placeholder' => 'Pilih...',
        'allowClear' => true
    ];
    
    $finalOptions = array_merge($defaultOptions, $options);
    
    echo "<script>
    $(document).ready(function() {
        $('{$selector}').select2(" . json_encode($finalOptions) . ");
    });
    </script>";
}

/**
 * Initialize Select2 with AJAX for remote data
 */
function initSelect2Ajax($selector, $ajaxUrl, $options = []) {
    $defaultOptions = [
        'theme' => 'bootstrap-5',
        'language' => 'id',
        'width' => '100%',
        'placeholder' => 'Cari...',
        'allowClear' => true,
        'minimumInputLength' => 2,
        'ajax' => [
            'url' => $ajaxUrl,
            'dataType' => 'json',
            'delay' => 250,
            'data' => new \stdClass(),
            'processResults' => new \stdClass()
        ]
    ];
    
    $finalOptions = array_merge($defaultOptions, $options);
    
    echo "<script>
    $(document).ready(function() {
        $('{$selector}').select2(" . json_encode($finalOptions) . ");
    });
    </script>";
}
?>
