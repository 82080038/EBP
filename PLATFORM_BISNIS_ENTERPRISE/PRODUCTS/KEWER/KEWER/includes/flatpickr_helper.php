<?php
/**
 * Flatpickr Helper Functions
 * 
 * Provides helper functions for Flatpickr date picker integration
 */

/**
 * Get Flatpickr CDN links
 */
function getFlatpickrCDN() {
    return [
        'css' => 'https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css',
        'css_bootstrap' => 'https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/themes/light.css',
        'js' => 'https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js',
        'js_i18n_id' => 'https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/id.js'
    ];
}

/**
 * Initialize Flatpickr with default configuration
 */
function initFlatpickr($selector = 'input[type="date"]', $options = []) {
    $defaultOptions = [
        'locale' => 'id',
        'dateFormat' => 'Y-m-d',
        'allowInput' => true,
        'altInput' => true,
        'altFormat' => 'd F Y',
        'theme' => 'light'
    ];
    
    $finalOptions = array_merge($defaultOptions, $options);
    
    echo "<script>
    $(document).ready(function() {
        flatpickr('{$selector}', " . json_encode($finalOptions) . ");
    });
    </script>";
}

/**
 * Initialize Flatpickr for date range
 */
function initFlatpickrRange($selector, $options = []) {
    $defaultOptions = [
        'locale' => 'id',
        'dateFormat' => 'Y-m-d',
        'allowInput' => true,
        'mode' => 'range',
        'theme' => 'light'
    ];
    
    $finalOptions = array_merge($defaultOptions, $options);
    
    echo "<script>
    $(document).ready(function() {
        flatpickr('{$selector}', " . json_encode($finalOptions) . ");
    });
    </script>";
}

/**
 * Initialize Flatpickr for month selection
 */
function initFlatpickrMonth($selector, $options = []) {
    $defaultOptions = [
        'locale' => 'id',
        'dateFormat' => 'Y-m',
        'allowInput' => true,
        'plugins' => [['monthSelect', ['shorthand' => true, 'dateFormat' => 'Y-m', 'theme' => 'light']]],
        'theme' => 'light'
    ];
    
    $finalOptions = array_merge($defaultOptions, $options);
    
    echo "<script>
    $(document).ready(function() {
        flatpickr('{$selector}', " . json_encode($finalOptions) . ");
    });
    </script>";
}
?>
