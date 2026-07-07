<?php

declare(strict_types=1);

/**
 * EBP Core - Structured Logger
 * 
 * Provides structured logging with different log levels
 * Logs to files with rotation support
 * 
 * @package EBP\Core\Logger
 * @version 1.0.0
 */

class Logger
{
    private string $logPath;
    private string $logFile;
    private array $context = [];

    // Log levels
    public const DEBUG = 'DEBUG';
    public const INFO = 'INFO';
    public const WARNING = 'WARNING';
    public const ERROR = 'ERROR';
    public const CRITICAL = 'CRITICAL';

    /**
     * Constructor
     * 
     * @param string $logPath Path to log directory
     * @param string $logFile Log file name
     */
    public function __construct(string $logPath = __DIR__ . '/../logs', string $logFile = 'app.log')
    {
        $this->logPath = rtrim($logPath, '/');
        $this->logFile = $logFile;
        
        // Create log directory if it doesn't exist
        if (!is_dir($this->logPath)) {
            mkdir($this->logPath, 0755, true);
        }
    }

    /**
     * Set context data
     * 
     * @param array $context Context data
     * @return self
     */
    public function withContext(array $context): self
    {
        $this->context = array_merge($this->context, $context);
        return $this;
    }

    /**
     * Log debug message
     * 
     * @param string $message Log message
     * @param array $context Additional context
     * @return void
     */
    public function debug(string $message, array $context = []): void
    {
        $this->log(self::DEBUG, $message, $context);
    }

    /**
     * Log info message
     * 
     * @param string $message Log message
     * @param array $context Additional context
     * @return void
     */
    public function info(string $message, array $context = []): void
    {
        $this->log(self::INFO, $message, $context);
    }

    /**
     * Log warning message
     * 
     * @param string $message Log message
     * @param array $context Additional context
     * @return void
     */
    public function warning(string $message, array $context = []): void
    {
        $this->log(self::WARNING, $message, $context);
    }

    /**
     * Log error message
     * 
     * @param string $message Log message
     * @param array $context Additional context
     * @return void
     */
    public function error(string $message, array $context = []): void
    {
        $this->log(self::ERROR, $message, $context);
    }

    /**
     * Log critical message
     * 
     * @param string $message Log message
     * @param array $context Additional context
     * @return void
     */
    public function critical(string $message, array $context = []): void
    {
        $this->log(self::CRITICAL, $message, $context);
    }

    /**
     * Log exception
     * 
     * @param Throwable $exception Exception to log
     * @param array $context Additional context
     * @return void
     */
    public function exception(Throwable $exception, array $context = []): void
    {
        $context = array_merge($context, [
            'exception' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ]);
        
        $this->log(self::ERROR, $exception->getMessage(), $context);
    }

    /**
     * Write log entry
     * 
     * @param string $level Log level
     * @param string $message Log message
     * @param array $context Additional context
     * @return void
     */
    public function log(string $level, string $message, array $context = []): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $context = array_merge($this->context, $context);
        
        $logEntry = [
            'timestamp' => $timestamp,
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'request_id' => $this->getRequestId(),
            'user_id' => $this->getUserId(),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'CLI',
            'uri' => $_SERVER['REQUEST_URI'] ?? 'CLI'
        ];

        $logLine = json_encode($logEntry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        
        $this->writeToFile($logLine);
    }

    /**
     * Write to log file
     * 
     * @param string $logLine Log line to write
     * @return void
     */
    private function writeToFile(string $logLine): void
    {
        $filePath = $this->logPath . '/' . $this->logFile;
        
        // Rotate log file if it's too large (>10MB)
        if (file_exists($filePath) && filesize($filePath) > 10 * 1024 * 1024) {
            $this->rotateLogFile($filePath);
        }

        file_put_contents($filePath, $logLine . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    /**
     * Rotate log file
     * 
     * @param string $filePath Path to log file
     * @return void
     */
    private function rotateLogFile(string $filePath): void
    {
        $timestamp = date('Y-m-d_His');
        $rotatedFile = $filePath . '.' . $timestamp;
        
        rename($filePath, $rotatedFile);
        
        // Keep only last 10 log files
        $logFiles = glob($filePath . '.*');
        if (count($logFiles) > 10) {
            usort($logFiles, function($a, $b) {
                return filemtime($a) - filemtime($b);
            });
            
            $filesToDelete = array_slice($logFiles, 0, count($logFiles) - 10);
            foreach ($filesToDelete as $file) {
                unlink($file);
            }
        }
    }

    /**
     * Get unique request ID
     * 
     * @return string
     */
    private function getRequestId(): string
    {
        static $requestId = null;
        
        if ($requestId === null) {
            $requestId = uniqid('req_', true);
        }
        
        return $requestId;
    }

    /**
     * Get current user ID if authenticated
     * 
     * @return string|null
     */
    private function getUserId(): ?string
    {
        // Try to get user ID from JWT token if available
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $token = str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']);
            try {
                $jwt = new JWT();
                $payload = $jwt->decode($token);
                $userId = $payload['user_id'] ?? null;
                return $userId !== null ? (string)$userId : null;
            } catch (Exception $e) {
                // Ignore JWT errors
            }
        }
        
        return null;
    }

    /**
     * Get logger instance (singleton)
     * 
     * @return self
     */
    public static function getInstance(): self
    {
        static $instance = null;
        
        if ($instance === null) {
            $instance = new self();
        }
        
        return $instance;
    }
}
