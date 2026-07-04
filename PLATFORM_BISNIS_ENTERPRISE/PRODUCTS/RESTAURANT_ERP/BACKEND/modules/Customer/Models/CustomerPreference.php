<?php

namespace App\Modules\Customer\Models;

use App\Core\BaseModel;

class CustomerPreference extends BaseModel
{
    protected $table = 'customer_preferences';
    protected $primaryKey = 'id';
    protected $fillable = [
        'customer_id',
        'restaurant_id',
        'preferred_table_type',
        'preferred_area',
        'meal_type_preference',
        'spice_level',
        'service_level',
        'allergies',
        'special_requests'
    ];

    /**
     * Get by customer
     */
    public function getByCustomer($customerId, $restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE customer_id = ? AND restaurant_id = ?";
        $result = $this->db->query($sql, [$customerId, $restaurantId])->fetch();
        return $result ?: null;
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
     * Update or create
     */
    public function updateOrCreate($customerId, $restaurantId, $data)
    {
        $existing = $this->getByCustomer($customerId, $restaurantId);
        
        if ($existing) {
            return $this->update($existing['id'], $data);
        } else {
            $data['customer_id'] = $customerId;
            $data['restaurant_id'] = $restaurantId;
            return $this->create($data);
        }
    }
}
