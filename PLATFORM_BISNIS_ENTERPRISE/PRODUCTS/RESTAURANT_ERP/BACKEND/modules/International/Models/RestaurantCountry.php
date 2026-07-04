<?php

namespace App\Modules\International\Models;

use App\Core\BaseModel;

class RestaurantCountry extends BaseModel
{
    protected $table = 'restaurant_countries';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'country_id',
        'is_primary',
        'local_currency_id',
        'local_language_id',
        'tax_registration_number',
        'vat_number',
        'legal_entity_name',
        'business_address',
        'is_active'
    ];

    /**
     * Get by restaurant
     */
    public function getByRestaurant($restaurantId)
    {
        $sql = "SELECT rc.*, c.country_name, c.country_code, cur.currency_code, cur.currency_symbol, lang.language_code 
                FROM {$this->table} rc
                LEFT JOIN countries c ON rc.country_id = c.id
                LEFT JOIN currencies cur ON rc.local_currency_id = cur.id
                LEFT JOIN languages lang ON rc.local_language_id = lang.id
                WHERE rc.restaurant_id = ? AND rc.is_active = TRUE
                ORDER BY rc.is_primary DESC, c.country_name ASC";
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
     * Get primary country
     */
    public function getPrimary($restaurantId)
    {
        $sql = "SELECT rc.*, c.country_name, c.country_code, cur.currency_code, cur.currency_symbol, lang.language_code 
                FROM {$this->table} rc
                LEFT JOIN countries c ON rc.country_id = c.id
                LEFT JOIN currencies cur ON rc.local_currency_id = cur.id
                LEFT JOIN languages lang ON rc.local_language_id = lang.id
                WHERE rc.restaurant_id = ? AND rc.is_primary = TRUE AND rc.is_active = TRUE
                LIMIT 1";
        $result = $this->db->query($sql, [$restaurantId])->fetch();
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
     * Set as primary
     */
    public function setAsPrimary($id, $restaurantId)
    {
        // Remove primary from all
        $this->db->query("UPDATE {$this->table} SET is_primary = FALSE WHERE restaurant_id = ?", [$restaurantId]);
        
        // Set new primary
        return $this->update($id, ['is_primary' => TRUE]);
    }
}
