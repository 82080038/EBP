<?php

// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';

class SimpleTableController
{
    // Simple endpoint to get tables without complex middleware
    public function getTables($request = null)
    {
        $database = new Database();
        $db = $database->connect();

        $sql = "SELECT table_id, table_number, table_name, capacity, status
                FROM tables
                ORDER BY table_number";

        $stmt = $db->prepare($sql);
        $stmt->execute();
        $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);

        Response::success($tables);
    }
}
