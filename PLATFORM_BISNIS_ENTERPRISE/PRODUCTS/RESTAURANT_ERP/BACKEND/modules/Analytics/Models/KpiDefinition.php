<?php

namespace App\Modules\Analytics\Models;

use App\Core\BaseModel;

class KpiDefinition extends BaseModel
{
    protected $table = 'kpi_definitions';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'kpi_code',
        'kpi_name',
        'kpi_description',
        'kpi_type',
        'calculation_formula',
        'data_source_table',
        'data_source_field',
        'target_value',
        'target_comparison',
        'unit',
        'decimal_places',
        'icon',
        'color',
        'is_active'
    ];

    /**
     * Get by restaurant
     */
    public function getByRestaurant($restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? ORDER BY kpi_code ASC";
        return $this->db->query($sql, [$restaurantId])->fetchAll();
    }

    /**
     * Get active
     */
    public function getActive($restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND is_active = TRUE ORDER BY kpi_code ASC";
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
     * Find by code
     */
    public function findByCode($kpiCode, $restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE kpi_code = ? AND restaurant_id = ?";
        $result = $this->db->query($sql, [$kpiCode, $restaurantId])->fetch();
        return $result ?: null;
    }

    /**
     * Get by type
     */
    public function getByType($restaurantId, $kpiType)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND kpi_type = ? AND is_active = TRUE";
        return $this->db->query($sql, [$restaurantId, $kpiType])->fetchAll();
    }
}
