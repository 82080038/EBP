<?php

namespace App\Modules\Supplier\Models;

use App\Core\BaseModel;

class SupplierProduct extends BaseModel
{
    protected $table = 'supplier_products';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'supplier_id',
        'inventory_item_id',
        'supplier_sku',
        'product_name',
        'unit_price',
        'currency',
        'minimum_order_quantity',
        'lead_time_days',
        'is_primary',
        'is_active'
    ];

    /**
     * Get by supplier
     */
    public function getBySupplier($supplierId, $restaurantId)
    {
        $sql = "SELECT sp.*, ii.item_name, ii.item_code, ii.unit_of_measure 
                FROM {$this->table} sp
                LEFT JOIN inventory_items ii ON sp.inventory_item_id = ii.id
                WHERE sp.supplier_id = ? AND sp.restaurant_id = ? AND sp.is_active = TRUE
                ORDER BY sp.is_primary DESC, sp.product_name ASC";
        return $this->db->query($sql, [$supplierId, $restaurantId])->fetchAll();
    }

    /**
     * Find by ID
     */
    public function findById($id, $restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? AND restaurant_id = ?";
        $result = $this->db->query($sql, [$id, $restaurantId])->fetch();
        return $result ?: null;
    }

    /**
     * Get by inventory item
     */
    public function getByInventoryItem($inventoryItemId, $restaurantId)
    {
        $sql = "SELECT sp.*, s.supplier_name 
                FROM {$this->table} sp
                LEFT JOIN suppliers s ON sp.supplier_id = s.id
                WHERE sp.inventory_item_id = ? AND sp.restaurant_id = ? AND sp.is_active = TRUE
                ORDER BY sp.is_primary DESC, sp.unit_price ASC";
        return $this->db->query($sql, [$inventoryItemId, $restaurantId])->fetchAll();
    }

    /**
     * Get primary supplier for item
     */
    public function getPrimarySupplier($inventoryItemId, $restaurantId)
    {
        $sql = "SELECT sp.*, s.supplier_name 
                FROM {$this->table} sp
                LEFT JOIN suppliers s ON sp.supplier_id = s.id
                WHERE sp.inventory_item_id = ? AND sp.restaurant_id = ? AND sp.is_primary = TRUE AND sp.is_active = TRUE
                LIMIT 1";
        $result = $this->db->query($sql, [$inventoryItemId, $restaurantId])->fetch();
        return $result ?: null;
    }
}
