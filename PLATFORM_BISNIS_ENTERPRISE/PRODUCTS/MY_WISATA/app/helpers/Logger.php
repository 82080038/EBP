<?php
/**
 * MyWisata Application - Logger Class
 * 
 * Handles audit logging and error logging.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-06-30
 */

class Logger {
    
    /**
     * Log audit action to database
     * 
     * @param string $action Action performed
     * @param string $module Module name
     * @param string $description Description
     * @param array $oldData Old data (before change)
     * @param array $newData New data (after change)
     */
    public static function audit($action, $module, $description, $oldData = null, $newData = null) {
        try {
            $db = Database::getInstance();
            
            $sql = "INSERT INTO audit_logs (user_id, action, module, description, ip_address, user_agent, old_data, new_data, created_at)
                    VALUES (:user_id, :action, :module, :description, :ip_address, :user_agent, :old_data, :new_data, NOW())";
            
            $params = [
                'user_id' => Session::get('user_id'),
                'action' => $action,
                'module' => $module,
                'description' => $description,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                'old_data' => $oldData ? json_encode($oldData) : null,
                'new_data' => $newData ? json_encode($newData) : null
            ];
            
            $db->query($sql, $params);
        } catch (Exception $e) {
            self::error('Failed to log audit: ' . $e->getMessage());
        }
    }
    
    /**
     * Log error to file
     * 
     * @param string $message Error message
     * @param array $context Additional context
     */
    public static function error($message, $context = []) {
        $logFile = defined('ERROR_LOG_FILE') ? ERROR_LOG_FILE : APP_ROOT . '/logs/error.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] ERROR: {$message}";
        
        if (!empty($context)) {
            $logMessage .= " | Context: " . json_encode($context);
        }
        
        $logMessage .= "\n";
        
        error_log($logMessage, 3, $logFile);
    }
    
    /**
     * Log info to file
     * 
     * @param string $message Info message
     * @param array $context Additional context
     */
    public static function info($message, $context = []) {
        $logFile = defined('ERROR_LOG_FILE') ? ERROR_LOG_FILE : APP_ROOT . '/logs/error.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] INFO: {$message}";
        
        if (!empty($context)) {
            $logMessage .= " | Context: " . json_encode($context);
        }
        
        $logMessage .= "\n";
        
        error_log($logMessage, 3, $logFile);
    }
    
    /**
     * Log warning to file
     * 
     * @param string $message Warning message
     * @param array $context Additional context
     */
    public static function warning($message, $context = []) {
        $logFile = defined('ERROR_LOG_FILE') ? ERROR_LOG_FILE : APP_ROOT . '/logs/error.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] WARNING: {$message}";
        
        if (!empty($context)) {
            $logMessage .= " | Context: " . json_encode($context);
        }
        
        $logMessage .= "\n";
        
        error_log($logMessage, 3, $logFile);
    }
}
