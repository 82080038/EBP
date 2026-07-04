<?php

namespace App\Modules\Payment\Models;

use App\Core\BaseModel;

class Tip extends BaseModel
{
    protected $table = 'tips';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'order_id',
        'payment_id',
        'tip_amount',
        'tip_type',
        'tip_percentage',
        'distribution_method',
        'distribution_config',
        'staff_id'
    ];

    /**
     * Get by order ID
     */
    public function getByOrderId($orderId)
    {
        $sql = "SELECT t.*, u.username as staff_name 
                FROM {$this->table} t
                LEFT JOIN users u ON t.staff_id = u.id
                WHERE t.order_id = ?";
        return $this->db->query($sql, [$orderId])->fetchAll();
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
     * Get by restaurant
     */
    public function getByRestaurant($restaurantId, $dateFrom = null, $dateTo = null)
    {
        $params = [$restaurantId];
        $where = "WHERE t.restaurant_id = ?";
        
        if ($dateFrom) {
            $where .= " AND t.created_at >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND t.created_at <= ?";
            $params[] = $dateTo;
        }
        
        $sql = "SELECT t.*, o.order_number, u.username as staff_name 
                FROM {$this->table} t
                LEFT JOIN orders o ON t.order_id = o.id
                LEFT JOIN users u ON t.staff_id = u.id
                {$where}
                ORDER BY t.created_at DESC";
        
        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Get by staff ID
     */
    public function getByStaffId($staffId, $dateFrom = null, $dateTo = null)
    {
        $params = [$staffId];
        $where = "WHERE staff_id = ?";
        
        if ($dateFrom) {
            $where .= " AND created_at >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND created_at <= ?";
            $params[] = $dateTo;
        }
        
        $sql = "SELECT * FROM {$this->table} {$where} ORDER BY created_at DESC";
        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Sum tips by staff
     */
    public function sumByStaff($restaurantId, $dateFrom = null, $dateTo = null)
    {
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($dateFrom) {
            $where .= " AND created_at >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND created_at <= ?";
            $params[] = $dateTo;
        }
        
        $sql = "SELECT staff_id, u.username, SUM(tip_amount) as total_tips, COUNT(*) as tip_count 
                FROM {$this->table} t
                LEFT JOIN users u ON t.staff_id = u.id
                {$where}
                GROUP BY staff_id, u.username
                ORDER BY total_tips DESC";
        
        return $this->db->query($sql, $params)->fetchAll();
    }
}
