<?php

namespace App\Modules\Language\Models;

use App\Core\BaseModel;

class Language extends BaseModel
{
    protected $table = 'languages';
    protected $primaryKey = 'id';
    protected $fillable = [
        'language_code',
        'language_name',
        'native_name',
        'is_active',
        'is_default',
        'sort_order'
    ];

    /**
     * Get active languages
     */
    public function getActive()
    {
        $sql = "SELECT * FROM {$this->table} WHERE is_active = TRUE ORDER BY sort_order ASC";
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

    /**
     * Get default language
     */
    public function getDefault()
    {
        $sql = "SELECT * FROM {$this->table} WHERE is_default = TRUE LIMIT 1";
        $result = $this->db->query($sql)->fetch();
        return $result ?: null;
    }
}
