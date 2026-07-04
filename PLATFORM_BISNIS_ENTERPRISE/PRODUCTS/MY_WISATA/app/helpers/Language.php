<?php
/**
 * MyWisata Application - Language Helper
 * 
 * Helper untuk mengelola multi-language support (Indonesia & English)
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-01
 */

class Language {
    
    private static $currentLang = 'id';
    private static $translations = [];
    private static $supportedLangs = ['id', 'en'];
    
    /**
     * Set current language
     * 
     * @param string $lang Language code (id, en)
     */
    public static function setLanguage($lang) {
        if (in_array($lang, self::$supportedLangs)) {
            self::$currentLang = $lang;
            $_SESSION['lang'] = $lang;
            setcookie('lang', $lang, time() + (86400 * 30), '/'); // 30 days
            // Reload translations immediately
            self::loadTranslations($lang);
        }
    }
    
    /**
     * Get current language
     * 
     * @return string Current language code
     */
    public static function getLanguage() {
        // Check session first
        if (isset($_SESSION['lang']) && in_array($_SESSION['lang'], self::$supportedLangs)) {
            self::$currentLang = $_SESSION['lang'];
        }
        // Check cookie
        elseif (isset($_COOKIE['lang']) && in_array($_COOKIE['lang'], self::$supportedLangs)) {
            self::$currentLang = $_COOKIE['lang'];
            $_SESSION['lang'] = $_COOKIE['lang'];
        }
        // Check browser language
        elseif (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $browserLang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
            if (in_array($browserLang, self::$supportedLangs)) {
                self::$currentLang = $browserLang;
            }
        }
        
        return self::$currentLang;
    }
    
    /**
     * Load translation file
     * 
     * @param string $lang Language code
     */
    private static function loadTranslations($lang) {
        $langFile = APP_ROOT . '/app/languages/' . $lang . '.php';
        
        if (file_exists($langFile)) {
            self::$translations = require $langFile;
        } else {
            self::$translations = [];
        }
    }
    
    /**
     * Get translation string
     * 
     * @param string $key Translation key
     * @param array $params Parameters to replace
     * @return string Translated text
     */
    public static function get($key, $params = []) {
        $lang = self::getLanguage();
        
        if (empty(self::$translations) || self::$currentLang !== $lang) {
            self::loadTranslations($lang);
            self::$currentLang = $lang;
        }
        
        $text = self::$translations[$key] ?? $key;
        
        // Replace parameters
        if (!empty($params)) {
            foreach ($params as $search => $replace) {
                $text = str_replace(':' . $search, $replace, $text);
            }
        }
        
        return $text;
    }
    
    /**
     * Alias for get()
     * 
     * @param string $key Translation key
     * @param array $params Parameters to replace
     * @return string Translated text
     */
    public static function trans($key, $params = []) {
        return self::get($key, $params);
    }
    
    /**
     * Get supported languages
     * 
     * @return array Supported language codes
     */
    public static function getSupportedLanguages() {
        return self::$supportedLangs;
    }
    
    /**
     * Get language name
     * 
     * @param string $code Language code
     * @return string Language name
     */
    public static function getLanguageName($code) {
        $names = [
            'id' => 'Indonesia',
            'en' => 'English'
        ];
        
        return $names[$code] ?? $code;
    }
}
