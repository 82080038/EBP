<?php

namespace App\Modules\Payment\Models;

use App\Core\BaseModel;

class PaymentMethod extends BaseModel
{
    protected $table = 'payment_methods';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'method_name',
        'method_type',
        'method_code',
        'gateway_config',
        'is_active',
        'is_default',
        'display_order',
        'icon_url'
    ];

    /**
     * Get by restaurant
     */
    public function getByRestaurant($restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? ORDER BY display_order ASC, method_name ASC";
        return $this->db->query($sql, [$restaurantId])->fetchAll();
    }

    /**
     * Get active methods
     */
    public function getActive($restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND is_active = TRUE ORDER BY display_order ASC";
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
     * Find by code
     */
    public function findByCode($restaurantId, $methodCode)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND method_code = ?";
        $result = $this->db->query($sql, [$restaurantId, $methodCode])->fetch();
        return $result ?: null;
    }

    /**
     * Get default method
     */
    public function getDefault($restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND is_default = TRUE LIMIT 1";
        $result = $this->db->query($sql, [$restaurantId])->fetch();
        return $result ?: null;
    }
}
