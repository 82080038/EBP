<?php

declare(strict_types=1);

/**
 * EBP Core - Database Connection Manager
 * 
 * This is a core component of the Enterprise Business Platform
 * Used for database connectivity across all EBP products
 * 
 * @package EBP\Core\Database
 * @version 1.0.0
 */

class Database
{
    private string $host;
    private string $socket;
    private string $dbname;
    private string $username;
    private string $password;
    private string $charset;
    private static ?Database $instance = null;

    public function __construct(array $config = [])
    {
        $this->host = $config['host'] ?? getenv('DB_HOST') ?? 'localhost';
        $this->socket = $config['socket'] ?? getenv('DB_SOCKET') ?? '/opt/lampp/var/mysql/mysql.sock';
        $this->dbname = $config['dbname'] ?? getenv('DB_NAME') ?? 'ebp_platform_db';
        $this->username = $config['username'] ?? getenv('DB_USER') ?? 'ebp_app';
        $this->password = $config['password'] ?? getenv('DB_PASSWORD') ?? 'ebp_secure_password_2026';
        $this->charset = $config['charset'] ?? 'utf8mb4';
    }

    /**
     * Get singleton instance
     * 
     * @param array $config Database configuration
     * @return Database
     */
    public static function getInstance(array $config = []): Database
    {
        if (self::$instance === null) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    /**
     * Connect to database
     * 
     * @return PDO PDO instance
     * @throws PDOException If connection fails
     */
    public function connect(): PDO
    {
        try {
            // Try socket connection first
            $pdo = new PDO(
                "mysql:host={$this->host};unix_socket={$this->socket};dbname={$this->dbname};charset={$this->charset}",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_PERSISTENT => false
                ]
            );
            return $pdo;
        } catch (PDOException $e) {
            // Fallback to host connection if socket fails
            try {
                $pdo = new PDO(
                    "mysql:host={$this->host};dbname={$this->dbname};charset={$this->charset}",
                    $this->username,
                    $this->password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                        PDO::ATTR_PERSISTENT => false
                    ]
                );
                return $pdo;
            } catch (PDOException $e2) {
                throw new PDOException("Database connection failed: " . $e2->getMessage());
            }
        }
    }

    /**
     * Test database connection
     * 
     * @return bool True if connection successful
     */
    public function testConnection(): bool
    {
        try {
            $pdo = $this->connect();
            return $pdo !== null;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Get database information
     * 
     * @return array Database metadata
     */
    public function getDatabaseInfo(): array
    {
        try {
            $pdo = $this->connect();
            $stmt = $pdo->query("SELECT VERSION() as version, DATABASE() as database");
            return $stmt->fetch();
        } catch (PDOException $e) {
            return [
                'version' => 'Unknown',
                'database' => $this->dbname,
                'error' => $e->getMessage()
            ];
        }
    }
}
