<?php

namespace App\Modules\Order\Models;

use App\Core\BaseModel;

class Order extends BaseModel
{
    protected $table = 'orders';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'table_id',
        'order_number',
        'order_type',
        'order_status',
        'order_channel',
        'customer_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'customer_address',
        'order_date',
        'estimated_time',
        'actual_time',
        'confirmed_at',
        'started_at',
        'ready_at',
        'served_at',
        'completed_at',
        'cancelled_at',
        'subtotal',
        'tax_amount',
        'service_charge',
        'discount_amount',
        'delivery_fee',
        'total_amount',
        'paid_amount',
        'payment_status',
        'payment_method',
        'payment_reference',
        'created_by',
        'confirmed_by',
        'served_by',
        'special_instructions',
        'internal_notes',
        'cancellation_reason',
        'external_order_id',
        'external_source'
    ];

    /**
     * Get paginated orders
     */
    public function getPaginated($restaurantId, $status, $tableId, $dateFrom, $dateTo, $page, $limit)
    {
        $offset = ($page - 1) * $limit;
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($status) {
            $where .= " AND order_status = ?";
            $params[] = $status;
        }
        
        if ($tableId) {
            $where .= " AND table_id = ?";
            $params[] = $tableId;
        }
        
        if ($dateFrom) {
            $where .= " AND order_date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND order_date <= ?";
            $params[] = $dateTo;
        }
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} {$where}";
        $totalResult = $this->db->query($countSql, $params)->fetch();
        $total = $totalResult['total'] ?? 0;
        
        // Get data
        $sql = "SELECT o.*, t.table_number, t.table_name 
                FROM {$this->table} o
                LEFT JOIN tables t ON o.table_id = t.id
                {$where}
                ORDER BY o.order_date DESC
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
        $sql = "SELECT o.*, t.table_number, t.table_name 
                FROM {$this->table} o
                LEFT JOIN tables t ON o.table_id = t.id
                WHERE o.id = ? AND o.restaurant_id = ?";
        $result = $this->db->query($sql, [$id, $restaurantId])->fetch();
        return $result ?: null;
    }

    /**
     * Find by order number
     */
    public function findByOrderNumber($orderNumber, $restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE order_number = ? AND restaurant_id = ?";
        $result = $this->db->query($sql, [$orderNumber, $restaurantId])->fetch();
        return $result ?: null;
    }

    /**
     * Get by status
     */
    public function getByStatus($restaurantId, $status)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND order_status = ? ORDER BY order_date DESC";
        return $this->db->query($sql, [$restaurantId, $status])->fetchAll();
    }

    /**
     * Get by table
     */
    public function getByTable($restaurantId, $tableId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND table_id = ? AND order_status != 'completed' ORDER BY order_date DESC";
        return $this->db->query($sql, [$restaurantId, $tableId])->fetchAll();
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
}
