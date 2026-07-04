<?php
/**
 * MyWisata Application - View Class
 * 
 * Base view class for rendering templates.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-06-30
 */

class View {
    /**
     * Render a view file
     * 
     * @param string $view View file path
     * @param array $data Data to pass to view
     */
    public static function render($view, $data = []) {
        if (file_exists(APP_ROOT . '/app/views/' . $view . '.php')) {
            extract($data);
            require_once APP_ROOT . '/app/views/' . $view . '.php';
        } else {
            die('View not found: ' . $view);
        }
    }
    
    /**
     * Escape output for XSS prevention
     * 
     * @param string $string String to escape
     * @return string Escaped string
     */
    public static function e($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Generate URL
     * 
     * @param string $path URL path
     * @return string Full URL
     */
    public static function url($path = '') {
        return BASE_URL . ltrim($path, '/');
    }
    
    /**
     * Generate asset URL
     * 
     * @param string $path Asset path
     * @return string Full asset URL
     */
    public static function asset($path) {
        return ASSETS_URL . ltrim($path, '/');
    }
    
    /**
     * Format currency
     * 
     * @param float $amount Amount
     * @return string Formatted currency
     */
    public static function currency($amount) {
        return CURRENCY_SYMBOL . number_format($amount, CURRENCY_DECIMALS, ',', '.');
    }
    
    /**
     * Format date
     * 
     * @param string $date Date string
     * @param string $format Date format
     * @return string Formatted date
     */
    public static function date($date, $format = null) {
        $format = $format ?: DATE_FORMAT;
        return date($format, strtotime($date));
    }
    
    /**
     * Truncate text
     * 
     * @param string $text Text to truncate
     * @param int $length Max length
     * @param string $suffix Suffix to add
     * @return string Truncated text
     */
    public static function truncate($text, $length = 100, $suffix = '...') {
        if (strlen($text) <= $length) {
            return $text;
        }
        return substr($text, 0, $length) . $suffix;
    }
}
