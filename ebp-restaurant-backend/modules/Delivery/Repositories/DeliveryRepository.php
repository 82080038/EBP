<?php

require_once __DIR__ . '/../../../config/database.php';

class DeliveryRepository
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function create($data)
    {
        $sql = "INSERT INTO delivery_orders (tenant_id, branch_id, order_id, driver_id, delivery_type, external_order_id, customer_name, customer_phone, delivery_address, delivery_lat, delivery_lng, estimated_distance_km, estimated_time_minutes, delivery_fee, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['branch_id'],
            $data['order_id'],
            $data['driver_id'] ?? null,
            $data['delivery_type'],
            $data['external_order_id'],
            $data['customer_name'],
            $data['customer_phone'],
            $data['delivery_address'],
            $data['delivery_lat'],
            $data['delivery_lng'],
            $data['estimated_distance_km'],
            $data['estimated_time_minutes'],
            $data['delivery_fee'],
            $data['status']
        ]);
        return $this->db->lastInsertId();
    }

    public function getByTenant($tenantId, $branchId = null)
    {
        if ($branchId) {
            $sql = "SELECT do.*, d.driver_name FROM delivery_orders do LEFT JOIN delivery_drivers d ON do.driver_id = d.driver_id WHERE do.tenant_id = ? AND do.branch_id = ? AND do.deleted_at IS NULL ORDER BY do.created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$tenantId, $branchId]);
        } else {
            $sql = "SELECT do.*, d.driver_name FROM delivery_orders do LEFT JOIN delivery_drivers d ON do.driver_id = d.driver_id WHERE do.tenant_id = ? AND do.deleted_at IS NULL ORDER BY do.created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$tenantId]);
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update($deliveryOrderId, $data)
    {
        $setClauses = [];
        $values = [];
        
        foreach ($data as $field => $value) {
            $setClauses[] = "$field = ?";
            $values[] = $value;
        }
        
        $values[] = $deliveryOrderId;
        
        $sql = "UPDATE delivery_orders SET " . implode(', ', $setClauses) . " WHERE delivery_order_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);
    }
}
