<?php

// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';

class SimpleEmployeeController
{
    // Simple endpoint to get employees without middleware
    public function getEmployees($request = null)
    {
        $database = new Database();
        $db = $database->connect();

        $sql = "SELECT e.employee_id, e.employee_name, e.position, e.phone, e.email, e.salary, e.start_date, e.status
                FROM employees e
                ORDER BY e.employee_name ASC
                LIMIT 50";

        $stmt = $db->prepare($sql);
        $stmt->execute();
        $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

        Response::success($employees);
    }
}
