<?php

namespace App\Modules\International\Models;

use App\Core\BaseModel;

class Currency extends BaseModel
{
    protected $table = 'currencies';
    protected $primaryKey = 'id';
    protected $fillable = [
        'currency_code',
        'currency_name',
        'currency_symbol',
        'exchange_rate',
        'base_currency',
        'is_active'
    ];

    /**
     * Get active currencies
     */
    public function getActive()
    {
        $sql = "SELECT * FROM {$this->table} WHERE is_active = TRUE ORDER BY currency_name ASC";
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Find by code
     */
    public function findByCode($currencyCode)
    {
        $sql = "SELECT * FROM {$this->table} WHERE currency_code = ?";
        $result = $this->db->query($sql, [$currencyCode])->fetch();
        return $result ?: null;
    }

    /**
     * Get exchange rate
     */
    public function getExchangeRate($currencyCode)
    {
        $currency = $this->findByCode($currencyCode);
        return $currency ? $currency['exchange_rate'] : null;
    }

    /**
     * Update exchange rate
     */
    public function updateExchangeRate($currencyCode, $newRate)
    {
        return $this->db->query("UPDATE {$this->table} SET exchange_rate = ? WHERE currency_code = ?", [$newRate, $currencyCode]);
    }
}
