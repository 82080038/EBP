<?php

namespace App\Modules\International\Models;

use App\Core\BaseModel;

class Country extends BaseModel
{
    protected $table = 'countries';
    protected $primaryKey = 'id';
    protected $fillable = [
        'country_code',
        'country_name',
        'currency_id',
        'default_language_id',
        'region',
        'vat_rate',
        'tax_id_required',
        'is_active'
    ];

    /**
     * Get active countries
     */
    public function getActive()
    {
        $sql = "SELECT c.*, cur.currency_code, cur.currency_symbol, lang.language_code 
                FROM {$this->table} c
                LEFT JOIN currencies cur ON c.currency_id = cur.id
                LEFT JOIN languages lang ON c.default_language_id = lang.id
                WHERE c.is_active = TRUE 
                ORDER BY c.country_name ASC";
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Find by code
     */
    public function findByCode($countryCode)
    {
        $sql = "SELECT * FROM {$this->table} WHERE country_code = ?";
        $result = $this->db->query($sql, [$countryCode])->fetch();
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
}
