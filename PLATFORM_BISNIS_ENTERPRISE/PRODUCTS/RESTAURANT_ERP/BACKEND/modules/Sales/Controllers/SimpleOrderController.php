<?php

// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';

if (!class_exists('OrderService')) {
    require_once __DIR__ . '/../Services/OrderService.php';
}

class SimpleOrderController
{
    private $service;

    public function __construct()
    {
        $this->service = new OrderService();
    }

    // Simple endpoint to get orders without middleware
    public function getAll($request = null)
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
        $pagination = \ScreenSizeHelper::getPaginationParams($request['query'] ?? [], $screenSize, 'orders');
        $limit = $pagination['limit'];
        $page = $pagination['page'];
        $offset = ($page - 1) * $limit;

        $status = isset($request['query']['status']) ? $request['query']['status'] : null;
        $statusClause = $status ? "AND o.status IN ('" . implode("','", explode(',', $status)) . "')" : "";

        $sql = "SELECT o.order_id, o.order_number, o.table_id, o.total_amount, o.status, o.created_at,
                t.table_number
                FROM orders o
                LEFT JOIN tables t ON o.table_id = t.table_id
                WHERE 1=1 $statusClause
                ORDER BY o.created_at DESC
                LIMIT ? OFFSET ?";

        $stmt = $db->prepare($sql);
        $stmt->execute([$limit, $offset]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Apply screen size filtering
        $result = [
            'success' => true,
            'data' => $orders,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'screen_size' => $screenSize
            ]
        ];

        $result = \ScreenSizeHelper::applyScreenSizeFilter($result, $screenSize, 'orders');

        Response::json($result);
    }
}
