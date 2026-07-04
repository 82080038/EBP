<?php
/**
 * MyWisata Application - Language Controller
 * 
 * Controller untuk mengelola perubahan bahasa
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-01
 */

class LanguageController extends Controller {
    
    /**
     * Set language
     * 
     * @param string $lang Language code (id, en)
     */
    public function set($lang) {
        // Set language
        Language::setLanguage($lang);
        
        // Redirect back to previous page or home
        $referer = $_SERVER['HTTP_REFERER'] ?? BASE_URL;
        header('Location: ' . $referer);
        exit;
    }
}
