<?php

// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';

class SimpleSupplierController
{
    // Simple endpoint to get suppliers without middleware
    public function getSuppliers($request = null)
    {
        $database = new Database();
        $db = $database->connect();

        $sql = "SELECT s.supplier_id, s.supplier_name, s.contact_person, s.phone, s.email, s.address, s.status
                FROM suppliers s
                ORDER BY s.supplier_name ASC
                LIMIT 50";

        $stmt = $db->prepare($sql);
        $stmt->execute();
        $suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        Response::success($suppliers);
    }
}
