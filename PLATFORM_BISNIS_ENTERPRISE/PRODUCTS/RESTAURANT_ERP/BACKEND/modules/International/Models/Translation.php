<?php

namespace App\Modules\International\Models;

use App\Core\BaseModel;

class Translation extends BaseModel
{
    protected $table = 'translations';
    protected $primaryKey = 'id';
    protected $fillable = [
        'translation_key_id',
        'language_id',
        'translated_value',
        'is_approved',
        'translated_by'
    ];

    /**
     * Get by language
     */
    public function getByLanguage($languageCode, $category)
    {
        $languageModel = new Language();
        $language = $languageModel->findByCode($languageCode);
        
        if (!$language) {
            return [];
        }
        
        $params = [$language['id']];
        $sql = "SELECT t.*, tk.key_name, tk.key_category, tk.default_value 
                FROM {$this->table} t
                LEFT JOIN translation_keys tk ON t.translation_key_id = tk.id
                WHERE t.language_id = ?";
        
        if ($category) {
            $sql .= " AND tk.key_category = ?";
            $params[] = $category;
        }
        
        $sql .= " AND t.is_approved = TRUE ORDER BY tk.key_category, tk.key_name";
        
        return $this->db->query($sql, $params)->fetchAll();
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
     * Get by key and language
     */
    public function getByKeyAndLanguage($translationKeyId, $languageId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE translation_key_id = ? AND language_id = ?";
        $result = $this->db->query($sql, [$translationKeyId, $languageId])->fetch();
        return $result ?: null;
    }

    /**
     * Get pending translations
     */
    public function getPending($languageId)
    {
        $sql = "SELECT t.*, tk.key_name, tk.key_category, tk.default_value 
                FROM {$this->table} t
                LEFT JOIN translation_keys tk ON t.translation_key_id = tk.id
                WHERE t.language_id = ? AND t.is_approved = FALSE
                ORDER BY t.created_at DESC";
        return $this->db->query($sql, [$languageId])->fetchAll();
    }

    /**
     * Approve translation
     */
    public function approve($id)
    {
        return $this->update($id, ['is_approved' => TRUE]);
    }
}
