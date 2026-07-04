<?php

namespace App\Modules\Loyalty\Models;

use App\Core\BaseModel;

class RewardRedemption extends BaseModel
{
    protected $table = 'reward_redemptions';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'customer_id',
        'reward_id',
        'points_transaction_id',
        'redemption_code',
        'points_used',
        'order_id',
        'redemption_status',
        'redeemed_at',
        'applied_at',
        'expired_at',
        'redeemed_by',
        'applied_by',
        'notes'
    ];

    /**
     * Get paginated redemptions
     */
    public function getPaginated($restaurantId, $customerId, $status, $page, $limit)
    {
        $offset = ($page - 1) * $limit;
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($customerId) {
            $where .= " AND customer_id = ?";
            $params[] = $customerId;
        }
        
        if ($status) {
            $where .= " AND redemption_status = ?";
            $params[] = $status;
        }
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} {$where}";
        $totalResult = $this->db->query($countSql, $params)->fetch();
        $total = $totalResult['total'] ?? 0;
        
        // Get data
        $sql = "SELECT rr.*, r.reward_name, c.first_name, c.last_name, u1.username as redeemed_by_name, u2.username as applied_by_name 
                FROM {$this->table} rr
                LEFT JOIN rewards r ON rr.reward_id = r.id
                LEFT JOIN customers c ON rr.customer_id = c.id
                LEFT JOIN users u1 ON rr.redeemed_by = u1.id
                LEFT JOIN users u2 ON rr.applied_by = u2.id
                {$where}
                ORDER BY rr.redeemed_at DESC
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
        $sql = "SELECT * FROM {$this->table} WHERE id = ? AND restaurant_id = ?";
        $result = $this->db->query($sql, [$id, $restaurantId])->fetch();
        return $result ?: null;
    }

    /**
     * Find by redemption code
     */
    public function findByCode($redemptionCode, $restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE redemption_code = ? AND restaurant_id = ?";
        $result = $this->db->query($sql, [$redemptionCode, $restaurantId])->fetch();
        return $result ?: null;
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
     * Get by customer
     */
    public function getByCustomer($customerId)
    {
        $sql = "SELECT rr.*, r.reward_name 
                FROM {$this->table} rr
                LEFT JOIN rewards r ON rr.reward_id = r.id
                WHERE rr.customer_id = ? 
                ORDER BY rr.redeemed_at DESC";
        return $this->db->query($sql, [$customerId])->fetchAll();
    }
}
