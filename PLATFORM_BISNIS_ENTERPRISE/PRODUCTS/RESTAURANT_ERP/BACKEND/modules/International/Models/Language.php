<?php

namespace App\Modules\International\Models;

use App\Core\BaseModel;

class Language extends BaseModel
{
    protected $table = 'languages';
    protected $primaryKey = 'id';
    protected $fillable = [
        'language_code',
        'language_name',
        'native_name',
        'text_direction',
        'is_active'
    ];

    /**
     * Get active languages
     */
    public function getActive()
    {
        $sql = "SELECT * FROM {$this->table} WHERE is_active = TRUE ORDER BY language_name ASC";
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Find by code
     */
    public function findByCode($languageCode)
    {
        $sql = "SELECT * FROM {$this->table} WHERE language_code = ?";
        $result = $this->db->query($sql, [$languageCode])->fetch();
        return $result ?: null;
    }
}
