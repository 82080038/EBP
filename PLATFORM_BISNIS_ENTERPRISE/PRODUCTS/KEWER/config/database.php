<?php
// Load path configuration first
require_once __DIR__ . '/path.php';

// Database Configuration (only define if not already defined by env.php)
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_NAME')) define('DB_NAME', 'kewer');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', 'root');

// Multi-Database Configuration
if (!defined('DB_ALAMAT_HOST')) define('DB_ALAMAT_HOST', 'localhost');
if (!defined('DB_ALAMAT_NAME')) define('DB_ALAMAT_NAME', 'db_alamat');
if (!defined('DB_ALAMAT_USER')) define('DB_ALAMAT_USER', 'root');
if (!defined('DB_ALAMAT_PASS')) define('DB_ALAMAT_PASS', 'root');

if (!defined('DB_ORANG_HOST')) define('DB_ORANG_HOST', 'localhost');
if (!defined('DB_ORANG_NAME')) define('DB_ORANG_NAME', 'db_orang');
if (!defined('DB_ORANG_USER')) define('DB_ORANG_USER', 'root');
if (!defined('DB_ORANG_PASS')) define('DB_ORANG_PASS', 'root');

if (!defined('DB_KEWER_HOST')) define('DB_KEWER_HOST', 'localhost');
if (!defined('DB_KEWER_NAME')) define('DB_KEWER_NAME', 'kewer');
if (!defined('DB_KEWER_USER')) define('DB_KEWER_USER', 'root');
if (!defined('DB_KEWER_PASS')) define('DB_KEWER_PASS', 'root');

// Determine MySQL socket based on OS
$mysql_socket = PHP_OS === 'WINNT' ? null : '/opt/lampp/var/mysql/mysql.sock';

// Create main connection (kewer - transactions)
$conn = new mysqli(DB_KEWER_HOST, DB_KEWER_USER, DB_KEWER_PASS, DB_KEWER_NAME, 3306, $mysql_socket);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4");

// Create address database connection
$conn_alamat = new mysqli(DB_ALAMAT_HOST, DB_ALAMAT_USER, DB_ALAMAT_PASS, DB_ALAMAT_NAME, 3306, $mysql_socket);
if ($conn_alamat->connect_error) {
    die("Address database connection failed: " . $conn_alamat->connect_error);
}
$conn_alamat->set_charset("utf8mb4");

// Create people database connection
$conn_orang = new mysqli(DB_ORANG_HOST, DB_ORANG_USER, DB_ORANG_PASS, DB_ORANG_NAME, 3306, $mysql_socket);
if ($conn_orang->connect_error) {
    die("People database connection failed: " . $conn_orang->connect_error);
}
$conn_orang->set_charset("utf8mb4");

// Global function for query (main kewer database)
function query($sql, $params = []) {
    global $conn;
    
    $sql_trimmed = strtoupper(ltrim($sql));
    
    if (empty($params)) {
        // Direct query without parameters
        $result = mysqli_query($conn, $sql);
        if (strpos($sql_trimmed, 'SELECT') === 0) {
            $data = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
            return $data;
        }
        return mysqli_affected_rows($conn);
    }
    
    // Prepared statement with parameters
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return [];
    }
    
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
    
    $stmt->execute();
    
    if (strpos($sql_trimmed, 'SELECT') === 0) {
        $result = $stmt->get_result();
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }
    
    return $stmt->affected_rows;
}

// Function for address database queries
function query_alamat($sql, $params = []) {
    global $conn_alamat;
    
    $sql_check = strtoupper(ltrim($sql));
    $stmt = $conn_alamat->prepare($sql);
    
    if ($params) {
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    
    if (strpos($sql_check, 'SELECT') === 0) {
        $result = $stmt->get_result();
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }
    
    return $stmt->affected_rows;
}

// Function for people database queries
function query_orang($sql, $params = []) {
    global $conn_orang;
    
    $sql_check = strtoupper(ltrim($sql));
    $stmt = $conn_orang->prepare($sql);
    
    if ($params) {
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    
    if (strpos($sql_check, 'SELECT') === 0) {
        $result = $stmt->get_result();
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }
    
    return $stmt->affected_rows;
}
