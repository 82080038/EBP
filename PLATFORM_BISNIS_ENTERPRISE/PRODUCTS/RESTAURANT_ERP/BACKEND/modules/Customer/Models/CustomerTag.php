<?php

namespace App\Modules\Customer\Models;

use App\Core\BaseModel;

class CustomerTag extends BaseModel
{
    protected $table = 'customer_tags';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'tag_name',
        'tag_color',
        'tag_description',
        'sort_order',
        'is_active'
    ];

    /**
     * Get by restaurant
     */
    public function getByRestaurant($restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND is_active = TRUE ORDER BY sort_order ASC, tag_name ASC";
        return $this->db->query($sql, [$restaurantId])->fetchAll();
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
     * Count by restaurant
     */
    public function countByRestaurant($restaurantId)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE restaurant_id = ? AND is_active = TRUE";
        $result = $this->db->query($sql, [$restaurantId])->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Get by customer
     */
    public function getByCustomer($customerId)
    {
        $sql = "SELECT ct.* FROM {$this->table} ct
                INNER JOIN customer_tag_assignments cta ON ct.id = cta.tag_id
                WHERE cta.customer_id = ? AND ct.is_active = TRUE
                ORDER BY ct.sort_order ASC";
        return $this->db->query($sql, [$customerId])->fetchAll();
    }
}
