<?php

namespace App\Modules\Customer\Models;

use App\Core\BaseModel;

class CustomerNote extends BaseModel
{
    protected $table = 'customer_notes';
    protected $primaryKey = 'id';
    protected $fillable = [
        'customer_id',
        'restaurant_id',
        'note_type',
        'note_text',
        'created_by',
        'is_internal'
    ];

    /**
     * Get by customer
     */
    public function getByCustomer($customerId, $restaurantId)
    {
        $sql = "SELECT cn.*, u.username as created_by_name 
                FROM {$this->table} cn
                LEFT JOIN users u ON cn.created_by = u.id
                WHERE cn.customer_id = ? AND cn.restaurant_id = ?
                ORDER BY cn.created_at DESC";
        return $this->db->query($sql, [$customerId, $restaurantId])->fetchAll();
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
    public function getByRestaurant($restaurantId, $noteType = null)
    {
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($noteType) {
            $where .= " AND note_type = ?";
            $params[] = $noteType;
        }
        
        $sql = "SELECT cn.*, c.first_name, c.last_name, u.username as created_by_name 
                FROM {$this->table} cn
                LEFT JOIN customers c ON cn.customer_id = c.id
                LEFT JOIN users u ON cn.created_by = u.id
                {$where}
                ORDER BY cn.created_at DESC";
        
        return $this->db->query($sql, $params)->fetchAll();
    }
}
