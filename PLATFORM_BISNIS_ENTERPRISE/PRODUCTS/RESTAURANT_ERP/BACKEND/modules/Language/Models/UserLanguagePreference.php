<?php

namespace App\Modules\Language\Models;

use App\Core\BaseModel;

class UserLanguagePreference extends BaseModel
{
    protected $table = 'user_language_preferences';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'language_code',
        'is_primary'
    ];

    /**
     * Get by user
     */
    public function getByUser($userId)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE user_id = ? AND is_primary = TRUE 
                LIMIT 1";
        $result = $this->db->query($sql, [$userId])->fetch();
        return $result ?: null;
    }

    /**
     * Get all user preferences
     */
    public function getAllByUser($userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = ? ORDER BY is_primary DESC";
        return $this->db->query($sql, [$userId])->fetchAll();
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
