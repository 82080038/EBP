<?php

namespace App\Modules\Payment\Models;

use App\Core\BaseModel;

class PaymentRefund extends BaseModel
{
    protected $table = 'payment_refunds';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'payment_id',
        'order_id',
        'refund_number',
        'refund_amount',
        'refund_reason',
        'refund_status',
        'gateway_refund_id',
        'gateway_response',
        'requested_at',
        'processed_at',
        'requested_by',
        'processed_by',
        'notes'
    ];

    /**
     * Get paginated refunds
     */
    public function getPaginated($restaurantId, $status, $page, $limit)
    {
        $offset = ($page - 1) * $limit;
        $params = [$restaurantId];
        $where = "WHERE pr.restaurant_id = ?";
        
        if ($status) {
            $where .= " AND pr.refund_status = ?";
            $params[] = $status;
        }
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} pr {$where}";
        $totalResult = $this->db->query($countSql, $params)->fetch();
        $total = $totalResult['total'] ?? 0;
        
        // Get data
        $sql = "SELECT pr.*, p.payment_number, p.payment_method, o.order_number 
                FROM {$this->table} pr
                LEFT JOIN payments p ON pr.payment_id = p.id
                LEFT JOIN orders o ON pr.order_id = o.id
                {$where}
                ORDER BY pr.requested_at DESC
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
    public function findById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $result = $this->db->query($sql, [$id])->fetch();
        return $result ?: null;
    }

    /**
     * Get by payment ID
     */
    public function getByPaymentId($paymentId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE payment_id = ? ORDER BY requested_at DESC";
        return $this->db->query($sql, [$paymentId])->fetchAll();
    }

    /**
     * Get by order ID
     */
    public function getByOrderId($orderId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE order_id = ? ORDER BY requested_at DESC";
        return $this->db->query($sql, [$orderId])->fetchAll();
    }

    /**
     * Count by status
     */
    public function countByStatus($restaurantId, $status)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE restaurant_id = ? AND refund_status = ?";
        $result = $this->db->query($sql, [$restaurantId, $status])->fetch();
        return $result['count'] ?? 0;
    }
}
