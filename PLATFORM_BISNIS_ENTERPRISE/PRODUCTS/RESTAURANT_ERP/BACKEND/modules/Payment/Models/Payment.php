<?php

namespace App\Modules\Payment\Models;

use App\Core\BaseModel;

class Payment extends BaseModel
{
    protected $table = 'payments';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'order_id',
        'payment_number',
        'payment_method',
        'payment_status',
        'amount',
        'currency',
        'payment_gateway',
        'gateway_transaction_id',
        'gateway_response',
        'card_last_four',
        'card_brand',
        'e_wallet_provider',
        'e_wallet_phone',
        'bank_name',
        'account_number',
        'account_name',
        'voucher_code',
        'voucher_amount',
        'processed_at',
        'completed_at',
        'failed_at',
        'processed_by',
        'notes',
        'failure_reason'
    ];

    /**
     * Get paginated payments
     */
    public function getPaginated($restaurantId, $orderId, $status, $method, $dateFrom, $dateTo, $page, $limit)
    {
        $offset = ($page - 1) * $limit;
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($orderId) {
            $where .= " AND order_id = ?";
            $params[] = $orderId;
        }
        
        if ($status) {
            $where .= " AND payment_status = ?";
            $params[] = $status;
        }
        
        if ($method) {
            $where .= " AND payment_method = ?";
            $params[] = $method;
        }
        
        if ($dateFrom) {
            $where .= " AND created_at >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND created_at <= ?";
            $params[] = $dateTo;
        }
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} {$where}";
        $totalResult = $this->db->query($countSql, $params)->fetch();
        $total = $totalResult['total'] ?? 0;
        
        // Get data
        $sql = "SELECT p.*, o.order_number 
                FROM {$this->table} p
                LEFT JOIN orders o ON p.order_id = o.id
                {$where}
                ORDER BY p.created_at DESC
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
        $sql = "SELECT p.*, o.order_number 
                FROM {$this->table} p
                LEFT JOIN orders o ON p.order_id = o.id
                WHERE p.id = ? AND p.restaurant_id = ?";
        $result = $this->db->query($sql, [$id, $restaurantId])->fetch();
        return $result ?: null;
    }

    /**
     * Find by order ID
     */
    public function findByOrderId($orderId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE order_id = ? ORDER BY created_at DESC";
        return $this->db->query($sql, [$orderId])->fetchAll();
    }

    /**
     * Get by status
     */
    public function getByStatus($restaurantId, $status)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND payment_status = ? ORDER BY created_at DESC";
        return $this->db->query($sql, [$restaurantId, $status])->fetchAll();
    }

    /**
     * Count by status
     */
    public function countByStatus($restaurantId, $status)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE restaurant_id = ? AND payment_status = ?";
        $result = $this->db->query($sql, [$restaurantId, $status])->fetch();
        return $result['count'] ?? 0;
    }
}
