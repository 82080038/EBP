<?php

// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';

class SimpleTableController
{
    // Simple endpoint to get tables without complex middleware
    public function getTables($request = null)
    {
                $host = 'localhost';
        $dbname = 'ebp_restaurant_db';
        $username = 'ebp_app';
        $password = 'ebp_secure_password_2026';
        $socket = '/opt/lampp/var/mysql/mysql.sock';

        $dsn = "mysql:host=$host;dbname=$dbname;unix_socket=$socket;charset=utf8mb4";
        $db = new PDO($dsn, $username, $password);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Get screen size from headers or parameters
        $headers = getallheaders();
        $screenSize = \ScreenSizeHelper::getScreenSize($headers, $request['query'] ?? []);

        // Get pagination with screen size defaults
        $pagination = \ScreenSizeHelper::getPaginationParams($request['query'] ?? [], $screenSize, 'tables');
        $limit = $pagination['limit'];
        $page = $pagination['page'];
        $offset = ($page - 1) * $limit;

        $sql = "SELECT table_id, table_number, table_name, capacity, status
                FROM tables
                ORDER BY table_number
                LIMIT ? OFFSET ?";

        $stmt = $db->prepare($sql);
        $stmt->execute([$limit, $offset]);
        $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Apply screen size filtering
        $result = [
            'success' => true,
            'data' => $tables,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'screen_size' => $screenSize
            ]
        ];

        $result = \ScreenSizeHelper::applyScreenSizeFilter($result, $screenSize, 'tables');

        Response::json($result);
    }
}
