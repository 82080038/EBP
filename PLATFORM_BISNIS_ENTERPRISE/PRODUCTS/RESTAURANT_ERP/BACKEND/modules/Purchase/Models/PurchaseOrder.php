<?php

namespace App\Modules\Purchase\Models;

use App\Core\BaseModel;

class PurchaseOrder extends BaseModel
{
    protected $table = 'purchase_orders';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'supplier_id',
        'order_number',
        'order_type',
        'order_date',
        'expected_delivery_date',
        'actual_delivery_date',
        'subtotal',
        'tax_amount',
        'shipping_cost',
        'discount_amount',
        'total_amount',
        'order_status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'delivery_address',
        'delivery_instructions',
        'internal_notes',
        'supplier_notes',
        'created_by',
        'modified_by'
    ];

    /**
     * Get paginated purchase orders
     */
    public function getPaginated($restaurantId, $status, $supplierId, $dateFrom, $dateTo, $page, $limit)
    {
        $offset = ($page - 1) * $limit;
        $params = [$restaurantId];
        $where = "WHERE po.restaurant_id = ?";
        
        if ($status) {
            $where .= " AND po.order_status = ?";
            $params[] = $status;
        }
        
        if ($supplierId) {
            $where .= " AND po.supplier_id = ?";
            $params[] = $supplierId;
        }
        
        if ($dateFrom) {
            $where .= " AND po.order_date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND po.order_date <= ?";
            $params[] = $dateTo;
        }
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} po {$where}";
        $totalResult = $this->db->query($countSql, $params)->fetch();
        $total = $totalResult['total'] ?? 0;
        
        // Get data
        $sql = "SELECT po.*, s.supplier_name, u1.username as created_by_name, u2.username as modified_by_name 
                FROM {$this->table} po
                LEFT JOIN suppliers s ON po.supplier_id = s.id
                LEFT JOIN users u1 ON po.created_by = u1.id
                LEFT JOIN users u2 ON po.modified_by = u2.id
                {$where}
                ORDER BY po.order_date DESC, po.created_at DESC
                LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $data = $this->db->query($sql, $params)->fetchAll();
        
        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit)
        ];
    }

    /**
     * Find by ID
     */
    public function findById($id, $restaurantId)
    {
        $sql = "SELECT po.*, s.supplier_name, s.contact_person, s.email, s.phone 
                FROM {$this->table} po
                LEFT JOIN suppliers s ON po.supplier_id = s.id
                WHERE po.id = ? AND po.restaurant_id = ?";
        $result = $this->db->query($sql, [$id, $restaurantId])->fetch();
        return $result ?: null;
    }

    /**
     * Find by number
     */
    public function findByNumber($orderNumber, $restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE order_number = ? AND restaurant_id = ?";
        $result = $this->db->query($sql, [$orderNumber, $restaurantId])->fetch();
        return $result ?: null;
    }

    /**
     * Get items
     */
    public function getItems($purchaseOrderId, $restaurantId)
    {
        $sql = "SELECT poi.*, ii.item_name, ii.item_code, ii.unit_of_measure 
                FROM purchase_order_items poi
                LEFT JOIN inventory_items ii ON poi.inventory_item_id = ii.id
                WHERE poi.purchase_order_id = ? AND poi.restaurant_id = ?
                ORDER BY poi.id ASC";
        return $this->db->query($sql, [$purchaseOrderId, $restaurantId])->fetchAll();
    }

    /**
     * Get history
     */
    public function getHistory($purchaseOrderId, $restaurantId)
    {
        $sql = "SELECT poh.*, u.username as performed_by_name 
                FROM purchase_order_history poh
                LEFT JOIN users u ON poh.performed_by = u.id
                WHERE poh.purchase_order_id = ? AND poh.restaurant_id = ?
                ORDER BY poh.performed_at DESC";
        return $this->db->query($sql, [$purchaseOrderId, $restaurantId])->fetchAll();
    }

    /**
     * Count by restaurant
     */
    public function countByRestaurant($restaurantId)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE restaurant_id = ?";
        $result = $this->db->query($sql, [$restaurantId])->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Count by status
     */
    public function countByStatus($restaurantId, $status)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE restaurant_id = ? AND order_status = ?";
        $result = $this->db->query($sql, [$restaurantId, $status])->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Get total value
     */
    public function getTotalValue($restaurantId)
    {
        $sql = "SELECT SUM(total_amount) as total FROM {$this->table} WHERE restaurant_id = ? AND order_status IN ('approved', 'processing', 'shipped', 'received', 'completed')";
        $result = $this->db->query($sql, [$restaurantId])->fetch();
        return $result['total'] ?? 0;
    }

    /**
     * Get by supplier
     */
    public function getBySupplier($supplierId, $restaurantId, $limit = 20)
    {
        $sql = "SELECT * FROM {$this->table} WHERE supplier_id = ? AND restaurant_id = ? ORDER BY order_date DESC LIMIT ?";
        return $this->db->query($sql, [$supplierId, $restaurantId, $limit])->fetchAll();
    }
}
