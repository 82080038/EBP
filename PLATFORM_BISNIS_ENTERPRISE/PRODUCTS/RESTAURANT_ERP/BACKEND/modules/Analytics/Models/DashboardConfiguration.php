<?php

namespace App\Modules\Analytics\Models;

use App\Core\BaseModel;

class DashboardConfiguration extends BaseModel
{
    protected $table = 'dashboard_configurations';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'user_id',
        'dashboard_name',
        'dashboard_description',
        'layout_config',
        'is_default',
        'is_public',
        'is_active'
    ];

    /**
     * Get by restaurant
     */
    public function getByRestaurant($restaurantId, $userId = null)
    {
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ? AND is_active = TRUE";
        
        if ($userId) {
            $where .= " AND (user_id = ? OR is_public = TRUE)";
            $params[] = $userId;
        }
        
        $sql = "SELECT dc.*, u.username as created_by_name 
                FROM {$this->table} dc
                LEFT JOIN users u ON dc.user_id = u.id
                {$where}
                ORDER BY dc.is_default DESC, dc.created_at DESC";
        
        return $this->db->query($sql, $params)->fetchAll();
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
     * Get default dashboard
     */
    public function getDefault($restaurantId, $userId)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE restaurant_id = ? AND is_default = TRUE AND is_active = TRUE
                LIMIT 1";
        $result = $this->db->query($sql, [$restaurantId])->fetch();
        
        if (!$result) {
            // Get user's first dashboard
            $sql = "SELECT * FROM {$this->table} 
                    WHERE restaurant_id = ? AND user_id = ? AND is_active = TRUE
                    ORDER BY created_at ASC
                    LIMIT 1";
            $result = $this->db->query($sql, [$restaurantId, $userId])->fetch();
        }
        
        return $result ?: null;
    }
}
