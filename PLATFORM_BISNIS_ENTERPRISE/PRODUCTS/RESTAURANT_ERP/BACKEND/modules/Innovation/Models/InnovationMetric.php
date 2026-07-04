<?php

namespace App\Modules\Innovation\Models;

use App\Core\BaseModel;

class InnovationMetric extends BaseModel
{
    protected $table = 'innovation_metrics';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'metric_date',
        'metric_type',
        'total_ideas_submitted',
        'ideas_approved',
        'ideas_implemented',
        'implementation_rate',
        'active_projects',
        'completed_projects',
        'project_success_rate',
        'total_investment',
        'realized_savings',
        'roi_percentage',
        'customer_satisfaction_impact',
        'operational_efficiency_impact',
        'revenue_impact'
    ];

    /**
     * Get by restaurant
     */
    public function getByRestaurant($restaurantId, $metricType, $dateFrom, $dateTo, $limit)
    {
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($metricType) {
            $where .= " AND metric_type = ?";
            $params[] = $metricType;
        }
        
        if ($dateFrom) {
            $where .= " AND metric_date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND metric_date <= ?";
            $params[] = $dateTo;
        }
        
        $sql = "SELECT * FROM {$this->table} {$where} ORDER BY metric_date DESC LIMIT ?";
        $params[] = $limit;
        
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
     * Get latest
     */
    public function getLatest($restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? ORDER BY metric_date DESC LIMIT 1";
        $result = $this->db->query($sql, [$restaurantId])->fetch();
        return $result ?: null;
    }

    /**
     * Get by date
     */
    public function getByDate($restaurantId, $date)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND metric_date = ?";
        $result = $this->db->query($sql, [$restaurantId, $date])->fetch();
        return $result ?: null;
    }

    /**
     * Calculate implementation rate
     */
    public function calculateImplementationRate($restaurantId, $date)
    {
        $ideaModel = new InnovationIdea();
        $totalIdeas = $ideaModel->countByRestaurant($restaurantId);
        $implementedIdeas = $ideaModel->countByStatus($restaurantId, 'implemented');
        
        return $totalIdeas > 0 ? ($implementedIdeas / $totalIdeas) * 100 : 0;
    }

    /**
     * Calculate project success rate
     */
    public function calculateProjectSuccessRate($restaurantId, $date)
    {
        $projectModel = new InnovationProject();
        $totalProjects = $projectModel->countByRestaurant($restaurantId);
        $completedProjects = $projectModel->countByStatus($restaurantId, 'completed');
        
        return $totalProjects > 0 ? ($completedProjects / $totalProjects) * 100 : 0;
    }
}
