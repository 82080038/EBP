<?php

namespace App\Modules\Language\Models;

use App\Core\BaseModel;

class RestaurantLanguageSetting extends BaseModel
{
    protected $table = 'restaurant_language_settings';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'language_code',
        'is_primary',
        'is_enabled'
    ];

    /**
     * Get by restaurant
     */
    public function getByRestaurant($restaurantId)
    {
        $sql = "SELECT rls.*, l.language_name, l.native_name 
                FROM {$this->table} rls
                LEFT JOIN languages l ON rls.language_code = l.language_code
                WHERE rls.restaurant_id = ? 
                ORDER BY rls.is_primary DESC, l.sort_order ASC";
        return $this->db->query($sql, [$restaurantId])->fetchAll();
    }

    /**
     * Find by restaurant and language
     */
    public function findByRestaurantAndLanguage($restaurantId, $languageCode)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE restaurant_id = ? AND language_code = ?";
        $result = $this->db->query($sql, [$restaurantId, $languageCode])->fetch();
        return $result ?: null;
    }

    /**
     * Get primary language for restaurant
     */
    public function getPrimaryLanguage($restaurantId)
    {
        $sql = "SELECT rls.*, l.language_name, l.native_name 
                FROM {$this->table} rls
                LEFT JOIN languages l ON rls.language_code = l.language_code
                WHERE rls.restaurant_id = ? AND rls.is_primary = TRUE 
                LIMIT 1";
        $result = $this->db->query($sql, [$restaurantId])->fetch();
        return $result ?: null;
    }

    /**
     * Get enabled languages for restaurant
     */
    public function getEnabledLanguages($restaurantId)
    {
        $sql = "SELECT rls.*, l.language_name, l.native_name 
                FROM {$this->table} rls
                LEFT JOIN languages l ON rls.language_code = l.language_code
                WHERE rls.restaurant_id = ? AND rls.is_enabled = TRUE 
                ORDER BY rls.is_primary DESC, l.sort_order ASC";
        return $this->db->query($sql, [$restaurantId])->fetchAll();
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
}
