<?php
namespace Kewer\Cache;

class CacheManager {
    private static $cacheDir;
    private static $prefix;
    
    public static function init() {
        self::$cacheDir = BASE_PATH . '/cache';
        self::$prefix = CACHE_PREFIX;
        
        // Create cache directory if it doesn't exist
        if (!is_dir(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0755, true);
        }
    }
    
    /**
     * Get cached value
     */
    public static function get($key, $default = null) {
        self::init();
        
        $filename = self::getFilename($key);
        
        if (!file_exists($filename)) {
            return $default;
        }
        
        $data = unserialize(file_get_contents($filename));
        
        // Check if expired
        if (time() > $data['expires']) {
            self::delete($key);
            return $default;
        }
        
        return $data['value'];
    }
    
    /**
     * Set cached value
     */
    public static function set($key, $value, $ttl = 3600) {
        self::init();
        
        $filename = self::getFilename($key);
        
        $data = [
            'value' => $value,
            'expires' => time() + $ttl,
            'created' => time()
        ];
        
        file_put_contents($filename, serialize($data));
        
        return true;
    }
    
    /**
     * Delete cached value
     */
    public static function delete($key) {
        self::init();
        
        $filename = self::getFilename($key);
        
        if (file_exists($filename)) {
            return unlink($filename);
        }
        
        return false;
    }
    
    /**
     * Clear all cache
     */
    public static function clear() {
        self::init();
        
        $files = glob(self::$cacheDir . '/*.cache');
        
        foreach ($files as $file) {
            unlink($file);
        }
        
        return true;
    }
    
    /**
     * Clear expired cache
     */
    public static function clearExpired() {
        self::init();
        
        $files = glob(self::$cacheDir . '/*.cache');
        $cleared = 0;
        
        foreach ($files as $file) {
            $data = unserialize(file_get_contents($file));
            
            if (time() > $data['expires']) {
                unlink($file);
                $cleared++;
            }
        }
        
        return $cleared;
    }
    
    /**
     * Remember value (get or set)
     */
    public static function remember($key, $ttl, $callback) {
        $value = self::get($key);
        
        if ($value !== null) {
            return $value;
        }
        
        $value = $callback();
        self::set($key, $value, $ttl);
        
        return $value;
    }
    
    /**
     * Get cache filename
     */
    private static function getFilename($key) {
        $safeKey = md5(self::$prefix . $key);
        return self::$cacheDir . '/' . $safeKey . '.cache';
    }
    
    /**
     * Get cache statistics
     */
    public static function getStats() {
        self::init();
        
        $files = glob(self::$cacheDir . '/*.cache');
        $totalSize = 0;
        $expired = 0;
        $valid = 0;
        
        foreach ($files as $file) {
            $totalSize += filesize($file);
            
            $data = unserialize(file_get_contents($file));
            
            if (time() > $data['expires']) {
                $expired++;
            } else {
                $valid++;
            }
        }
        
        return [
            'total_files' => count($files),
            'total_size' => $totalSize,
            'expired_files' => $expired,
            'valid_files' => $valid
        ];
    }
}
?>
