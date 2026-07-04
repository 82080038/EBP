<?php

namespace App\Modules\Franchise\Models;

use App\Core\BaseModel;

class FranchisePerformance extends BaseModel
{
    protected $table = 'franchise_performance';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'franchisee_id',
        'evaluation_period_start',
        'evaluation_period_end',
        'total_revenue',
        'gross_margin',
        'net_profit',
        'customer_satisfaction_score',
        'food_quality_score',
        'service_quality_score',
        'brand_compliance_score',
        'operational_compliance_score',
        'overall_score',
        'performance_rating'
    ];

    /**
     * Get by restaurant
     */
    public function getByRestaurant($restaurantId, $franchiseeId)
    {
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($franchiseeId) {
            $where .= " AND franchisee_id = ?";
            $params[] = $franchiseeId;
        }
        
        $sql = "SELECT fp.*, f.franchisee_name 
                FROM {$this->table} fp
                LEFT JOIN franchisees f ON fp.franchisee_id = f.id
                {$where}
                ORDER BY fp.evaluation_period_start DESC";
        
        return $this->db->query($sql, $params)->fetchAll();
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
     * Get latest by franchisee
     */
    public function getLatestByFranchisee($franchiseeId, $restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE franchisee_id = ? AND restaurant_id = ? ORDER BY evaluation_period_start DESC LIMIT 1";
        $result = $this->db->query($sql, [$franchiseeId, $restaurantId])->fetch();
        return $result ?: null;
    }

    /**
     * Get top performers
     */
    public function getTopPerformers($restaurantId, $limit = 10)
    {
        $sql = "SELECT fp.*, f.franchisee_name 
                FROM {$this->table} fp
                LEFT JOIN franchisees f ON fp.franchisee_id = f.id
                WHERE fp.restaurant_id = ?
                ORDER BY fp.overall_score DESC
                LIMIT ?";
        return $this->db->query($sql, [$restaurantId, $limit])->fetchAll();
    }

    /**
     * Get by rating
     */
    public function getByRating($restaurantId, $rating)
    {
        $sql = "SELECT fp.*, f.franchisee_name 
                FROM {$this->table} fp
                LEFT JOIN franchisees f ON fp.franchisee_id = f.id
                WHERE fp.restaurant_id = ? AND fp.performance_rating = ?
                ORDER BY fp.overall_score DESC";
        return $this->db->query($sql, [$restaurantId, $rating])->fetchAll();
    }
}
