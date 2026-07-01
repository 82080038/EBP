<?php

require_once __DIR__ . '/../../../config/database.php';

class SupplierRepository
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function create($data)
    {
        $sql = "INSERT INTO suppliers (tenant_id, supplier_code, supplier_name, contact_person, phone, email, address, city, province, postal_code, country, tax_id, payment_terms, credit_limit, lead_time_days, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['supplier_code'],
            $data['supplier_name'],
            $data['contact_person'] ?? null,
            $data['phone'] ?? null,
            $data['email'] ?? null,
            $data['address'] ?? null,
            $data['city'] ?? null,
            $data['province'] ?? null,
            $data['postal_code'] ?? null,
            $data['country'] ?? 'Indonesia',
            $data['tax_id'] ?? null,
            $data['payment_terms'] ?? null,
            $data['credit_limit'] ?? 0,
            $data['lead_time_days'] ?? 7,
            $data['status'] ?? 'ACTIVE',
            $data['notes'] ?? null
        ]);
        return $this->db->lastInsertId();
    }

    public function getByTenant($tenantId)
    {
        $sql = "SELECT * FROM suppliers WHERE tenant_id = ? AND status = 'ACTIVE' AND deleted_at IS NULL ORDER BY supplier_name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($supplierId)
    {
        $sql = "SELECT * FROM suppliers WHERE supplier_id = ? AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$supplierId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($supplierId, $data)
    {
        $setClauses = [];
        $values = [];
        
        foreach ($data as $field => $value) {
            $setClauses[] = "$field = ?";
            $values[] = $value;
        }
        
        $values[] = $supplierId;
        
        $sql = "UPDATE suppliers SET " . implode(', ', $setClauses) . " WHERE supplier_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);
    }

    public function delete($supplierId)
    {
        $sql = "UPDATE suppliers SET status = 'INACTIVE', deleted_at = NOW() WHERE supplier_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$supplierId]);
    }
}
