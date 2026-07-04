<?php

namespace App\Modules\Franchise\Models;

use App\Core\BaseModel;

class Franchisee extends BaseModel
{
    protected $table = 'franchisees';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'franchisee_name',
        'franchisee_code',
        'contact_person',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'business_name',
        'tax_id',
        'business_license',
        'franchisee_status',
        'notes',
        'assigned_manager'
    ];

    /**
     * Get by restaurant
     */
    public function getByRestaurant($restaurantId, $status)
    {
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($status) {
            $where .= " AND franchisee_status = ?";
            $params[] = $status;
        }
        
        $sql = "SELECT f.*, u.username as assigned_manager_name 
                FROM {$this->table} f
                LEFT JOIN users u ON f.assigned_manager = u.id
                {$where}
                ORDER BY f.franchisee_name ASC";
        
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
     * Find by code
     */
    public function findByCode($franchiseeCode, $restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE franchisee_code = ? AND restaurant_id = ?";
        $result = $this->db->query($sql, [$franchiseeCode, $restaurantId])->fetch();
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
     * Count by status
     */
    public function countByStatus($restaurantId, $status)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE restaurant_id = ? AND franchisee_status = ?";
        $result = $this->db->query($sql, [$restaurantId, $status])->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Get active
     */
    public function getActive($restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND franchisee_status = 'active' ORDER BY franchisee_name ASC";
        return $this->db->query($sql, [$restaurantId])->fetchAll();
    }
}
