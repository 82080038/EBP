<?php

namespace App\Modules\Supplier\Services;

use App\Modules\Supplier\Models\Supplier;
use App\Modules\Supplier\Models\SupplierProduct;
use App\Modules\Supplier\Models\SupplierContract;
use App\Modules\Supplier\Models\SupplierPerformance;
use App\Core\Database;

class SupplierService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get suppliers
     */
    public function getSuppliers($restaurantId, $type, $isActive, $page, $limit)
    {
        $supplierModel = new Supplier();
        return $supplierModel->getPaginated($restaurantId, $type, $isActive, $page, $limit);
    }

    /**
     * Get single supplier
     */
    public function getSupplier($id, $restaurantId)
    {
        $supplierModel = new Supplier();
        $supplier = $supplierModel->findById($id, $restaurantId);
        
        if ($supplier) {
            // Get products
            $productModel = new SupplierProduct();
            $supplier['products'] = $productModel->getBySupplier($id, $restaurantId);
            
            // Get contracts
            $contractModel = new SupplierContract();
            $supplier['contracts'] = $contractModel->getBySupplier($id, $restaurantId);
            
            // Get latest performance
            $performanceModel = new SupplierPerformance();
            $supplier['performance'] = $performanceModel->getLatest($id, $restaurantId);
        }
        
        return $supplier;
    }

    /**
     * Create supplier
     */
    public function createSupplier($restaurantId, $data)
    {
        $supplierModel = new Supplier();
        
        $supplierData = [
            'restaurant_id' => $restaurantId,
            'supplier_name' => $data->supplier_name,
            'supplier_type' => $data->supplier_type,
            'contact_person' => $data->contact_person,
            'email' => $data->email,
            'phone' => $data->phone,
            'address_line1' => $data->address_line1,
            'address_line2' => $data->address_line2 ?? null,
            'city' => $data->city,
            'state' => $data->state ?? null,
            'postal_code' => $data->postal_code,
            'country' => $data->country ?? 'Indonesia',
            'tax_id' => $data->tax_id ?? null,
            'business_license' => $data->business_license ?? null,
            'payment_terms' => $data->payment_terms ?? 30,
            'payment_method' => $data->payment_method ?? 'bank_transfer',
            'bank_account' => $data->bank_account ?? null,
            'is_active' => true,
            'is_preferred' => $data->is_preferred ?? false,
            'notes' => $data->notes ?? null
        ];
        
        $supplierId = $supplierModel->create($supplierData);
        
        if (!$supplierId) {
            return ['success' => false, 'message' => 'Failed to create supplier'];
        }
        
        return ['success' => true, 'message' => 'Supplier created', 'supplier_id' => $supplierId];
    }

    /**
     * Update supplier
     */
    public function updateSupplier($id, $restaurantId, $data)
    {
        $supplierModel = new Supplier();
        $supplier = $supplierModel->findById($id, $restaurantId);
        
        if (!$supplier) {
            return ['success' => false, 'message' => 'Supplier not found'];
        }
        
        $updateData = [];
        
        if (isset($data->supplier_name)) {
            $updateData['supplier_name'] = $data->supplier_name;
        }
        if (isset($data->contact_person)) {
            $updateData['contact_person'] = $data->contact_person;
        }
        if (isset($data->email)) {
            $updateData['email'] = $data->email;
        }
        if (isset($data->phone)) {
            $updateData['phone'] = $data->phone;
        }
        if (isset($data->is_active)) {
            $updateData['is_active'] = $data->is_active;
        }
        if (isset($data->is_preferred)) {
            $updateData['is_preferred'] = $data->is_preferred;
        }
        
        $updated = $supplierModel->update($id, $updateData);
        
        if (!$updated) {
            return ['success' => false, 'message' => 'Failed to update supplier'];
        }
        
        return ['success' => true, 'message' => 'Supplier updated'];
    }

    /**
     * Get supplier products
     */
    public function getSupplierProducts($supplierId, $restaurantId)
    {
        $productModel = new SupplierProduct();
        return $productModel->getBySupplier($supplierId, $restaurantId);
    }

    /**
     * Add supplier product
     */
    public function addSupplierProduct($supplierId, $restaurantId, $data)
    {
        $productModel = new SupplierProduct();
        
        $productData = [
            'restaurant_id' => $restaurantId,
            'supplier_id' => $supplierId,
            'inventory_item_id' => $data->inventory_item_id,
            'supplier_sku' => $data->supplier_sku ?? null,
            'product_name' => $data->product_name,
            'unit_price' => $data->unit_price,
            'currency' => $data->currency ?? 'IDR',
            'minimum_order_quantity' => $data->minimum_order_quantity ?? 1.00,
            'lead_time_days' => $data->lead_time_days ?? 7,
            'is_primary' => $data->is_primary ?? false,
            'is_active' => true
        ];
        
        $productId = $productModel->create($productData);
        
        if (!$productId) {
            return ['success' => false, 'message' => 'Failed to add product'];
        }
        
        return ['success' => true, 'message' => 'Product added', 'product_id' => $productId];
    }

    /**
     * Get supplier contracts
     */
    public function getSupplierContracts($supplierId, $restaurantId)
    {
        $contractModel = new SupplierContract();
        return $contractModel->getBySupplier($supplierId, $restaurantId);
    }

    /**
     * Create supplier contract
     */
    public function createSupplierContract($supplierId, $restaurantId, $userId, $data)
    {
        $contractModel = new SupplierContract();
        
        $contractData = [
            'restaurant_id' => $restaurantId,
            'supplier_id' => $supplierId,
            'contract_name' => $data->contract_name,
            'contract_type' => $data->contract_type,
            'start_date' => $data->start_date,
            'end_date' => $data->end_date ?? null,
            'contract_terms' => $data->contract_terms,
            'payment_terms' => $data->payment_terms ?? 30,
            'contract_value' => $data->contract_value ?? null,
            'contract_status' => 'draft',
            'contract_document_url' => $data->contract_document_url ?? null,
            'created_by' => $userId
        ];
        
        $contractId = $contractModel->create($contractData);
        
        if (!$contractId) {
            return ['success' => false, 'message' => 'Failed to create contract'];
        }
        
        return ['success' => true, 'message' => 'Contract created', 'contract_id' => $contractId];
    }

    /**
     * Get supplier performance
     */
    public function getSupplierPerformance($supplierId, $restaurantId)
    {
        $performanceModel = new SupplierPerformance();
        return $performanceModel->getBySupplier($supplierId, $restaurantId);
    }

    /**
     * Rate supplier
     */
    public function rateSupplier($supplierId, $restaurantId, $userId, $data)
    {
        $sql = "INSERT INTO supplier_ratings (restaurant_id, supplier_id, rating, rating_category, comment, rated_by, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        
        $inserted = $this->db->query($sql, [
            $restaurantId,
            $supplierId,
            $data->rating,
            $data->rating_category,
            $data->comment ?? null,
            $userId
        ]);
        
        if (!$inserted) {
            return ['success' => false, 'message' => 'Failed to rate supplier'];
        }
        
        return ['success' => true, 'message' => 'Rating submitted'];
    }

    /**
     * Get statistics
     */
    public function getStatistics($restaurantId)
    {
        $supplierModel = new Supplier();
        $contractModel = new SupplierContract();
        
        // Total suppliers
        $totalSuppliers = $supplierModel->countByRestaurant($restaurantId);
        
        // Active suppliers
        $activeSuppliers = $supplierModel->countActive($restaurantId);
        
        // Preferred suppliers
        $preferredSuppliers = $supplierModel->countPreferred($restaurantId);
        
        // Active contracts
        $activeContracts = $contractModel->countActive($restaurantId);
        
        return [
            'total_suppliers' => $totalSuppliers,
            'active_suppliers' => $activeSuppliers,
            'preferred_suppliers' => $preferredSuppliers,
            'active_contracts' => $activeContracts
        ];
    }
}
