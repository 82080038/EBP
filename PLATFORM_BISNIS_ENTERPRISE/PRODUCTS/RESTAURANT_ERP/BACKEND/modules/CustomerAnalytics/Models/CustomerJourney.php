<?php

namespace App\Modules\CustomerAnalytics\Models;

use App\Core\BaseModel;

class CustomerJourney extends BaseModel
{
    protected $table = 'customer_journey';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'customer_id',
        'journey_stage',
        'stage_entered_at',
        'stage_duration_days',
        'touchpoint_count',
        'last_touchpoint',
        'conversion_rate'
    ];

    /**
     * Get by customer
     */
    public function getByCustomer($customerId, $restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE customer_id = ? AND restaurant_id = ? ORDER BY stage_entered_at DESC";
        return $this->db->query($sql, [$customerId, $restaurantId])->fetchAll();
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
     * Get current stage
     */
    public function getCurrentStage($customerId, $restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE customer_id = ? AND restaurant_id = ? ORDER BY stage_entered_at DESC LIMIT 1";
        $result = $this->db->query($sql, [$customerId, $restaurantId])->fetch();
        return $result ?: null;
    }

    /**
     * Get by stage
     */
    public function getByStage($restaurantId, $journeyStage)
    {
        $sql = "SELECT cj.*, c.first_name, c.last_name 
                FROM {$this->table} cj
                LEFT JOIN customers c ON cj.customer_id = c.id
                WHERE cj.restaurant_id = ? AND cj.journey_stage = ?
                ORDER BY cj.stage_entered_at DESC";
        return $this->db->query($sql, [$restaurantId, $journeyStage])->fetchAll();
    }

    /**
     * Count by stage
     */
    public function countByStage($restaurantId, $journeyStage)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE restaurant_id = ? AND journey_stage = ?";
        $result = $this->db->query($sql, [$restaurantId, $journeyStage])->fetch();
        return $result['count'] ?? 0;
    }
}
