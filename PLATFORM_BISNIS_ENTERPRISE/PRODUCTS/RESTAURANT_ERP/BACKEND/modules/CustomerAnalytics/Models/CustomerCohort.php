<?php

namespace App\Modules\CustomerAnalytics\Models;

use App\Core\BaseModel;

class CustomerCohort extends BaseModel
{
    protected $table = 'customer_cohorts';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'cohort_name',
        'cohort_description',
        'cohort_type',
        'cohort_criteria',
        'cohort_size',
        'retention_rate',
        'average_lifetime_value',
        'cohort_start_date',
        'cohort_end_date',
        'is_active'
    ];

    /**
     * Get by restaurant
     */
    public function getByRestaurant($restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? ORDER BY cohort_start_date DESC";
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
     * Get active cohorts
     */
    public function getActive($restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND is_active = TRUE ORDER BY cohort_start_date DESC";
        return $this->db->query($sql, [$restaurantId])->fetchAll();
    }

    /**
     * Get by type
     */
    public function getByType($restaurantId, $cohortType)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND cohort_type = ? ORDER BY cohort_start_date DESC";
        return $this->db->query($sql, [$restaurantId, $cohortType])->fetchAll();
    }

    /**
     * Count by restaurant
     */
    public function countByRestaurant($restaurantId)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE restaurant_id = ?";
        $result = $this->db->query($sql, [$restaurantId])->fetch();
        return $result['count'] ?? 0;
    }
}
