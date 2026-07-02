<?php

class Database
{

    private $host = "localhost";
    private $socket = "/opt/lampp/var/mysql/mysql.sock";
    private $dbname = "ebp_restaurant_db";
    private $username = "ebp_app";
    private $password = "ebp_secure_password_2026";

    public function connect()
    {
        try {
            $pdo = new PDO(
                "mysql:host={$this->host};unix_socket={$this->socket};dbname={$this->dbname};charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
            return $pdo;
        } catch (PDOException $e) {
            // Fallback to host connection if socket fails
            try {
                $pdo = new PDO(
                    "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4",
                    $this->username,
                    $this->password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );
                return $pdo;
            } catch (PDOException $e2) {
                die("Database connection failed: " . $e2->getMessage());
            }
        }
    }

}
