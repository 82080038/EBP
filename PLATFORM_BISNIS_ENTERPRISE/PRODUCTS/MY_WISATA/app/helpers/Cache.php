<?php
/**
 * MyWisata Application - Cache Helper
 * 
 * Handles caching functionality.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-01
 */

class Cache {
    
    private static $cacheDir;
    private static $defaultTTL = 3600; // 1 hour
    
    /**
     * Initialize cache
     */
    public static function init() {
        self::$cacheDir = APP_ROOT . '/cache';
        
        if (!is_dir(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0777, true);
        }
    }
    
    /**
     * Get cached value
     * 
     * @param string $key Cache key
     * @return mixed|false
     */
    public static function get($key) {
        self::init();
        
        $cacheFile = self::$cacheDir . '/' . md5($key) . '.cache';
        
        if (!file_exists($cacheFile)) {
            return false;
        }
        
        $data = unserialize(file_get_contents($cacheFile));
        
        if ($data['expires'] < time()) {
            self::delete($key);
            return false;
        }
        
        return $data['value'];
    }
    
    /**
     * Set cached value
     * 
     * @param string $key Cache key
     * @param mixed $value Value to cache
     * @param int $ttl Time to live in seconds
     * @return bool
     */
    public static function set($key, $value, $ttl = null) {
        self::init();
        
        if ($ttl === null) {
            $ttl = self::$defaultTTL;
        }
        
        $cacheFile = self::$cacheDir . '/' . md5($key) . '.cache';
        
        $data = [
            'value' => $value,
            'expires' => time() + $ttl,
            'created' => time()
        ];
        
        $result = file_put_contents($cacheFile, serialize($data));
        
        if ($result !== false) {
            Logger::info('Cache set', ['key' => $key, 'ttl' => $ttl]);
        }
        
        return $result !== false;
    }
    
    /**
     * Delete cached value
     * 
     * @param string $key Cache key
     * @return bool
     */
    public static function delete($key) {
        self::init();
        
        $cacheFile = self::$cacheDir . '/' . md5($key) . '.cache';
        
        if (file_exists($cacheFile)) {
            $result = unlink($cacheFile);
            if ($result) {
                Logger::info('Cache deleted', ['key' => $key]);
            }
            return $result;
        }
        
        return false;
    }
    
    /**
     * Clear all cache
     * 
     * @return bool
     */
    public static function clear() {
        self::init();
        
        $files = glob(self::$cacheDir . '/*.cache');
        $count = 0;
        
        foreach ($files as $file) {
            if (unlink($file)) {
                $count++;
            }
        }
        
        Logger::info('Cache cleared', ['files_deleted' => $count]);
        
        return $count > 0;
    }
    
    /**
     * Remember - Get from cache or execute callback
     * 
     * @param string $key Cache key
     * @param callable $callback Callback to execute if cache miss
     * @param int $ttl Time to live in seconds
     * @return mixed
     */
    public static function remember($key, $callback, $ttl = null) {
        $value = self::get($key);
        
        if ($value !== false) {
            return $value;
        }
        
        $value = $callback();
        self::set($key, $value, $ttl);
        
        return $value;
    }
}
