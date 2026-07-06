<?php

// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';

class SimpleMenuController
{
    // Simple endpoint to get categories without complex middleware
    public function getCategories($request = null)
    {
                $host = 'localhost';
        $dbname = 'ebp_restaurant_db';
        $username = 'ebp_app';
        $password = 'ebp_secure_password_2026';
        $socket = '/opt/lampp/var/mysql/mysql.sock';

        $dsn = "mysql:host=$host;dbname=$dbname;unix_socket=$socket;charset=utf8mb4";
        $db = new PDO($dsn, $username, $password);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "SELECT category_id, category_code, category_name, description, status
                FROM categories
                ORDER BY category_name";

        $stmt = $db->prepare($sql);
        $stmt->execute();
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        Response::success($categories);
    }

    // Simple endpoint to get products without complex middleware
    public function getProducts($request = null)
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
        $pagination = \ScreenSizeHelper::getPaginationParams($request['query'] ?? [], $screenSize, 'products');
        $limit = $pagination['limit'];
        $page = $pagination['page'];
        $offset = ($page - 1) * $limit;

        $categoryId = isset($request['query']['category_id']) ? (int)$request['query']['category_id'] : null;

        if ($categoryId) {
            $sql = "SELECT p.product_id, p.product_code, p.product_name, p.description, p.price, p.cost, p.status,
                    c.category_name
                    FROM products p
                    LEFT JOIN categories c ON p.category_id = c.category_id
                    WHERE p.category_id = ?
                    ORDER BY p.product_name
                    LIMIT ? OFFSET ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$categoryId, $limit, $offset]);
        } else {
            $sql = "SELECT p.product_id, p.product_code, p.product_name, p.description, p.price, p.cost, p.status,
                    c.category_name
                    FROM products p
                    LEFT JOIN categories c ON p.category_id = c.category_id
                    ORDER BY p.product_name
                    LIMIT ? OFFSET ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$limit, $offset]);
        }

        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Apply screen size filtering
        $result = [
            'success' => true,
            'data' => $products,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'screen_size' => $screenSize
            ]
        ];

        $result = \ScreenSizeHelper::applyScreenSizeFilter($result, $screenSize, 'products');

        Response::json($result);
    }
}
