<?php

if (!class_exists('SupplierRepository')) {
    require_once __DIR__ . '/../Repositories/SupplierRepository.php';
}
if (!class_exists('database')) {
    require_once __DIR__ . '/../../../config/database.php';
}

class SupplierService
{
    private $repository;
    private $db;

    public function __construct()
    {
        $this->repository = new SupplierRepository();
        $database = new Database();
        $this->db = $database->connect();
    }

    public function createSupplier($data, $tenantId)
    {
        try {
            // Validate required fields
            if (empty($data['supplier_code']) || empty($data['supplier_name'])) {
                return [
                    'success' => false,
                    'message' => 'Supplier code and name are required'
                ];
            }

            $data['tenant_id'] = $tenantId;
            $result = $this->repository->create($data);
            
            return [
                'success' => true,
                'message' => 'Supplier created successfully',
                'supplier_id' => $result
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create supplier: ' . $e->getMessage()
            ];
        }
    }

    public function getSuppliers($tenantId)
    {
        try {
            $suppliers = $this->repository->getByTenant($tenantId);
            
            return [
                'success' => true,
                'message' => 'Suppliers retrieved successfully',
                'data' => $suppliers
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get suppliers: ' . $e->getMessage()
            ];
        }
    }

    public function updateSupplier($supplierId, $data, $tenantId)
    {
        try {
            // Check if supplier belongs to tenant
            $stmt = $this->db->prepare("SELECT supplier_id FROM suppliers WHERE supplier_id = ? AND tenant_id = ?");
            $stmt->execute([$supplierId, $tenantId]);
            if (!$stmt->fetch()) {
                return [
                    'success' => false,
                    'message' => 'Supplier not found or does not belong to tenant'
                ];
            }

            $this->repository->update($supplierId, $data);
            
            return [
                'success' => true,
                'message' => 'Supplier updated successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to update supplier: ' . $e->getMessage()
            ];
        }
    }

    public function deleteSupplier($supplierId, $tenantId)
    {
        try {
            // Check if supplier belongs to tenant
            $stmt = $this->db->prepare("SELECT supplier_id FROM suppliers WHERE supplier_id = ? AND tenant_id = ?");
            $stmt->execute([$supplierId, $tenantId]);
            if (!$stmt->fetch()) {
                return [
                    'success' => false,
                    'message' => 'Supplier not found or does not belong to tenant'
                ];
            }

            $this->repository->delete($supplierId);
            
            return [
                'success' => true,
                'message' => 'Supplier deleted successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to delete supplier: ' . $e->getMessage()
            ];
        }
    }
}
