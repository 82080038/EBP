<?php
namespace Kewer\Logging;

class Logger {
    private static $logPath;
    private static $logLevels = [
        'DEBUG' => 0,
        'INFO' => 1,
        'WARNING' => 2,
        'ERROR' => 3,
        'CRITICAL' => 4
    ];
    private static $minLevel = 0;
    
    public static function init() {
        self::$logPath = BASE_PATH . '/logs';
        
        // Create logs directory if it doesn't exist
        if (!is_dir(self::$logPath)) {
            mkdir(self::$logPath, 0755, true);
        }
        
        // Set minimum log level based on environment
        self::$minLevel = APP_DEBUG ? 0 : self::$logLevels['INFO'];
    }
    
    /**
     * Log debug message
     */
    public static function debug($message, $context = []) {
        self::log('DEBUG', $message, $context);
    }
    
    /**
     * Log info message
     */
    public static function info($message, $context = []) {
        self::log('INFO', $message, $context);
    }
    
    /**
     * Log warning message
     */
    public static function warning($message, $context = []) {
        self::log('WARNING', $message, $context);
    }
    
    /**
     * Log error message
     */
    public static function error($message, $context = []) {
        self::log('ERROR', $message, $context);
    }
    
    /**
     * Log critical message
     */
    public static function critical($message, $context = []) {
        self::log('CRITICAL', $message, $context);
    }
    
    /**
     * Log message with level
     */
    private static function log($level, $message, $context = []) {
        self::init();
        
        // Check if this level should be logged
        if (self::$logLevels[$level] < self::$minLevel) {
            return;
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $logFile = self::$logPath . '/' . date('Y-m-d') . '.log';
        
        // Format log entry
        $logEntry = sprintf(
            "[%s] [%s] %s %s\n",
            $timestamp,
            $level,
            $message,
            !empty($context) ? json_encode($context) : ''
        );
        
        // Write to log file
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
        
        // If critical, also write to separate critical log
        if ($level === 'CRITICAL') {
            $criticalLog = self::$logPath . '/critical.log';
            file_put_contents($criticalLog, $logEntry, FILE_APPEND | LOCK_EX);
        }
    }
    
    /**
     * Log database query
     */
    public static function query($sql, $params = [], $executionTime = null) {
        if (!APP_DEBUG) {
            return;
        }
        
        $context = [
            'sql' => $sql,
            'params' => $params,
            'execution_time' => $executionTime
        ];
        
        self::debug('Database Query', $context);
    }
    
    /**
     * Log API request
     */
    public static function apiRequest($method, $endpoint, $userId = null, $responseCode = null) {
        $context = [
            'method' => $method,
            'endpoint' => $endpoint,
            'user_id' => $userId,
            'response_code' => $responseCode,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];
        
        $level = $responseCode >= 400 ? 'WARNING' : 'INFO';
        self::log($level, "API Request: {$method} {$endpoint}", $context);
    }
    
    /**
     * Log authentication attempt
     */
    public static function authAttempt($username, $success, $ip = null) {
        $context = [
            'username' => $username,
            'success' => $success,
            'ip' => $ip ?? ($_SERVER['REMOTE_ADDR'] ?? 'unknown')
        ];
        
        $level = $success ? 'INFO' : 'WARNING';
        self::log($level, "Authentication Attempt", $context);
    }
    
    /**
     * Log file upload
     */
    public static function fileUpload($filename, $size, $success, $userId = null) {
        $context = [
            'filename' => $filename,
            'size' => $size,
            'success' => $success,
            'user_id' => $userId
        ];
        
        $level = $success ? 'INFO' : 'ERROR';
        self::log($level, "File Upload", $context);
    }
    
    /**
     * Get recent log entries
     */
    public static function getRecentLogs($limit = 100, $level = null) {
        self::init();
        
        $logFile = self::$logPath . '/' . date('Y-m-d') . '.log';
        
        if (!file_exists($logFile)) {
            return [];
        }
        
        $logs = array_reverse(file($logFile));
        $filteredLogs = [];
        
        foreach ($logs as $log) {
            if ($level && strpos($log, "[$level]") === false) {
                continue;
            }
            
            $filteredLogs[] = trim($log);
            
            if (count($filteredLogs) >= $limit) {
                break;
            }
        }
        
        return $filteredLogs;
    }
    
    /**
     * Clear old log files (older than 30 days)
     */
    public static function clearOldLogs($days = 30) {
        self::init();
        
        $files = glob(self::$logPath . '/*.log');
        $cutoff = time() - ($days * 24 * 60 * 60);
        
        foreach ($files as $file) {
            if (filemtime($file) < $cutoff) {
                unlink($file);
            }
        }
    }
}
?>
