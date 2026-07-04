<?php

namespace App\Modules\Customer\Models;

use App\Core\BaseModel;

class CustomerVisit extends BaseModel
{
    protected $table = 'customer_visits';
    protected $primaryKey = 'id';
    protected $fillable = [
        'customer_id',
        'restaurant_id',
        'visit_date',
        'party_size',
        'table_id',
        'order_id',
        'order_count',
        'total_amount',
        'served_by',
        'rating'
    ];

    /**
     * Get paginated visits
     */
    public function getPaginated($customerId, $restaurantId, $page, $limit)
    {
        $offset = ($page - 1) * $limit;
        $params = [$customerId, $restaurantId];
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} WHERE customer_id = ? AND restaurant_id = ?";
        $totalResult = $this->db->query($countSql, $params)->fetch();
        $total = $totalResult['total'] ?? 0;
        
        // Get data
        $sql = "SELECT cv.*, t.table_number, u.username as served_by_name 
                FROM {$this->table} cv
                LEFT JOIN tables t ON cv.table_id = t.id
                LEFT JOIN users u ON cv.served_by = u.id
                WHERE cv.customer_id = ? AND cv.restaurant_id = ?
                ORDER BY cv.visit_date DESC
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
     * Get summary
     */
    public function getSummary($customerId, $restaurantId)
    {
        $sql = "SELECT 
                    COUNT(*) as total_visits,
                    SUM(total_amount) as total_spent,
                    AVG(total_amount) as average_spent,
                    MAX(visit_date) as last_visit,
                    MIN(visit_date) as first_visit
                FROM {$this->table}
                WHERE customer_id = ? AND restaurant_id = ?";
        $result = $this->db->query($sql, [$customerId, $restaurantId])->fetch();
        return $result ?: null;
    }

    /**
     * Count by month
     */
    public function countByMonth($restaurantId)
    {
        $sql = "SELECT COUNT(*) as count 
                FROM {$this->table} 
                WHERE restaurant_id = ? 
                AND YEAR(visit_date) = YEAR(CURDATE()) 
                AND MONTH(visit_date) = MONTH(CURDATE())";
        $result = $this->db->query($sql, [$restaurantId])->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Get average value
     */
    public function getAverageValue($restaurantId)
    {
        $sql = "SELECT AVG(total_amount) as avg_value 
                FROM {$this->table} 
                WHERE restaurant_id = ? 
                AND visit_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        $result = $this->db->query($sql, [$restaurantId])->fetch();
        return $result['avg_value'] ?? 0;
    }

    /**
     * Get by order ID
     */
    public function getByOrderId($orderId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE order_id = ?";
        $result = $this->db->query($sql, [$orderId])->fetch();
        return $result ?: null;
    }
}
