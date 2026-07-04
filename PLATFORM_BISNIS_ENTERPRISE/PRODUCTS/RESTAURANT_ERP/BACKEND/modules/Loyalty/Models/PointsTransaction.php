<?php

namespace App\Modules\Loyalty\Models;

use App\Core\BaseModel;

class PointsTransaction extends BaseModel
{
    protected $table = 'points_transactions';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'customer_loyalty_id',
        'customer_id',
        'transaction_type',
        'points_amount',
        'reference_type',
        'reference_id',
        'reference_number',
        'balance_before',
        'balance_after',
        'expires_at',
        'created_by',
        'notes'
    ];

    /**
     * Get paginated transactions
     */
    public function getPaginated($restaurantId, $customerId, $transactionType, $page, $limit)
    {
        $offset = ($page - 1) * $limit;
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($customerId) {
            $where .= " AND customer_id = ?";
            $params[] = $customerId;
        }
        
        if ($transactionType) {
            $where .= " AND transaction_type = ?";
            $params[] = $transactionType;
        }
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} {$where}";
        $totalResult = $this->db->query($countSql, $params)->fetch();
        $total = $totalResult['total'] ?? 0;
        
        // Get data
        $sql = "SELECT pt.*, c.first_name, c.last_name, u.username as created_by_name 
                FROM {$this->table} pt
                LEFT JOIN customers c ON pt.customer_id = c.id
                LEFT JOIN users u ON pt.created_by = u.id
                {$where}
                ORDER BY pt.created_at DESC
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
     * Get by customer
     */
    public function getByCustomer($customerId, $limit = 50)
    {
        $sql = "SELECT * FROM {$this->table} WHERE customer_id = ? ORDER BY created_at DESC LIMIT ?";
        return $this->db->query($sql, [$customerId, $limit])->fetchAll();
    }

    /**
     * Sum by type
     */
    public function sumByType($restaurantId, $transactionType)
    {
        $sql = "SELECT SUM(points_amount) as total FROM {$this->table} WHERE restaurant_id = ? AND transaction_type = ?";
        $result = $this->db->query($sql, [$restaurantId, $transactionType])->fetch();
        return $result['total'] ?? 0;
    }

    /**
     * Get expiring points
     */
    public function getExpiring($restaurantId, $days = 30)
    {
        $sql = "SELECT pt.*, c.first_name, c.last_name 
                FROM {$this->table} pt
                LEFT JOIN customers c ON pt.customer_id = c.id
                WHERE pt.restaurant_id = ? 
                AND pt.transaction_type = 'earned'
                AND pt.expires_at BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL ? DAY)
                ORDER BY pt.expires_at ASC";
        return $this->db->query($sql, [$restaurantId, $days])->fetchAll();
    }
}
