<?php

// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';

class SimpleMenuController
{
    // Simple endpoint to get categories without complex middleware
    public function getCategories($request = null)
    {
        $database = new Database();
        $db = $database->connect();

        $sql = "SELECT category_id, category_code, category_name, description, status
                FROM menu_categories
                ORDER BY category_name";

        $stmt = $db->prepare($sql);
        $stmt->execute();
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        Response::success($categories);
    }

    // Simple endpoint to get products without complex middleware
    public function getProducts($request = null)
    {
        $database = new Database();
        $db = $database->connect();

        $categoryId = isset($request['query']['category_id']) ? (int)$request['query']['category_id'] : null;

        if ($categoryId) {
            $sql = "SELECT p.product_id, p.product_code, p.product_name, p.description, p.price, p.cost, p.status,
                    c.category_name
                    FROM menu_products p
                    LEFT JOIN menu_categories c ON p.category_id = c.category_id
                    WHERE p.category_id = ?
                    ORDER BY p.product_name";
            $stmt = $db->prepare($sql);
            $stmt->execute([$categoryId]);
        } else {
            $sql = "SELECT p.product_id, p.product_code, p.product_name, p.description, p.price, p.cost, p.status,
                    c.category_name
                    FROM menu_products p
                    LEFT JOIN menu_categories c ON p.category_id = c.category_id
                    ORDER BY p.product_name";
            $stmt = $db->prepare($sql);
            $stmt->execute();
        }

        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        Response::success($products);
    }
}
