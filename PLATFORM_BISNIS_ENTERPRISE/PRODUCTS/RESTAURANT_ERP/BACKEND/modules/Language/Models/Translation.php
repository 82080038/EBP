<?php

namespace App\Modules\Language\Models;

use App\Core\BaseModel;

class Translation extends BaseModel
{
    protected $table = 'translations';
    protected $primaryKey = 'id';
    protected $fillable = [
        'language_code',
        'translation_key',
        'translation_value',
        'context',
        'is_active'
    ];

    /**
     * Get by language
     */
    public function getByLanguage($languageCode, $context = null)
    {
        $params = [$languageCode];
        $where = "WHERE language_code = ? AND is_active = TRUE";
        
        if ($context) {
            $where .= " AND context = ?";
            $params[] = $context;
        }
        
        $sql = "SELECT * FROM {$this->table} {$where} ORDER BY translation_key ASC";
        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Get all translations
     */
    public function getAll($context = null)
    {
        $params = [];
        $where = "WHERE is_active = TRUE";
        
        if ($context) {
            $where .= " AND context = ?";
            $params[] = $context;
        }
        
        $sql = "SELECT * FROM {$this->table} {$where} ORDER BY language_code, translation_key ASC";
        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Find by key and language
     */
    public function findByKeyAndLanguage($key, $languageCode, $context = null)
    {
        $params = [$key, $languageCode];
        $where = "WHERE translation_key = ? AND language_code = ? AND is_active = TRUE";
        
        if ($context) {
            $where .= " AND context = ?";
            $params[] = $context;
        }
        
        $sql = "SELECT * FROM {$this->table} {$where} LIMIT 1";
        $result = $this->db->query($sql, $params)->fetch();
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
     * Get by context
     */
    public function getByContext($context)
    {
        $sql = "SELECT * FROM {$this->table} WHERE context = ? AND is_active = TRUE ORDER BY translation_key ASC";
        return $this->db->query($sql, [$context])->fetchAll();
    }
}
