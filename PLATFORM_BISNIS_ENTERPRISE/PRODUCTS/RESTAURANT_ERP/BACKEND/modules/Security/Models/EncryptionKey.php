<?php

namespace App\Modules\Security\Models;

use App\Core\BaseModel;

class EncryptionKey extends BaseModel
{
    protected $table = 'encryption_keys';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'key_name',
        'key_type',
        'key_purpose',
        'key_value_encrypted',
        'key_iv_encrypted',
        'key_algorithm',
        'key_size',
        'key_version',
        'valid_from',
        'valid_until',
        'is_active',
        'last_rotated_at',
        'rotation_frequency_days',
        'next_rotation_date',
        'created_by'
    ];

    /**
     * Get by restaurant
     */
    public function getByRestaurant($restaurantId)
    {
        $sql = "SELECT ek.*, u.username as created_by_name 
                FROM {$this->table} ek
                LEFT JOIN users u ON ek.created_by = u.id
                WHERE ek.restaurant_id = ? 
                ORDER BY ek.created_at DESC";
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
     * Get keys requiring rotation
     */
    public function getKeysRequiringRotation()
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE is_active = TRUE 
                AND next_rotation_date <= CURDATE()";
        return $this->db->query($sql)->fetchAll();
    }
}
