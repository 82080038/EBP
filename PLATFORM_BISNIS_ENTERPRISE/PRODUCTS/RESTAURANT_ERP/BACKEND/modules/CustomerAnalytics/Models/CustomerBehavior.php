<?php

namespace App\Modules\CustomerAnalytics\Models;

use App\Core\BaseModel;

class CustomerBehavior extends BaseModel
{
    protected $table = 'customer_behavior';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'customer_id',
        'total_orders',
        'total_spend',
        'average_order_value',
        'days_since_last_visit',
        'average_days_between_visits',
        'visit_frequency',
        'preferred_order_type',
        'preferred_time_slot',
        'loyalty_score',
        'churn_risk',
        'customer_lifetime_value',
        'predicted_lifetime_value'
    ];

    /**
     * Get by customer
     */
    public function getByCustomer($customerId, $restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE customer_id = ? AND restaurant_id = ?";
        $result = $this->db->query($sql, [$customerId, $restaurantId])->fetch();
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
     * Count by restaurant
     */
    public function countByRestaurant($restaurantId)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE restaurant_id = ?";
        $result = $this->db->query($sql, [$restaurantId])->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Count by lifetime value
     */
    public function countByLifetimeValue($restaurantId, $minValue)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE restaurant_id = ? AND customer_lifetime_value >= ?";
        $result = $this->db->query($sql, [$restaurantId, $minValue])->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Count by churn risk
     */
    public function countByChurnRisk($restaurantId, $riskLevel)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE restaurant_id = ? AND churn_risk = ?";
        $result = $this->db->query($sql, [$restaurantId, $riskLevel])->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Get average lifetime value
     */
    public function getAverageLifetimeValue($restaurantId)
    {
        $sql = "SELECT AVG(customer_lifetime_value) as avg_value FROM {$this->table} WHERE restaurant_id = ?";
        $result = $this->db->query($sql, [$restaurantId])->fetch();
        return $result['avg_value'] ?? 0;
    }

    /**
     * Get top customers by lifetime value
     */
    public function getTopByLifetimeValue($restaurantId, $limit = 10)
    {
        $sql = "SELECT cb.*, c.first_name, c.last_name 
                FROM {$this->table} cb
                LEFT JOIN customers c ON cb.customer_id = c.id
                WHERE cb.restaurant_id = ?
                ORDER BY cb.customer_lifetime_value DESC
                LIMIT ?";
        return $this->db->query($sql, [$restaurantId, $limit])->fetchAll();
    }
}
