<?php

namespace App\Modules\Marketing\Models;

use App\Core\BaseModel;

class MarketingCampaign extends BaseModel
{
    protected $table = 'marketing_campaigns';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'campaign_name',
        'campaign_description',
        'campaign_type',
        'start_date',
        'end_date',
        'budget_amount',
        'actual_spent',
        'marketing_channels',
        'target_audience',
        'target_segments',
        'campaign_status',
        'impressions',
        'clicks',
        'conversions',
        'conversion_rate',
        'revenue_generated',
        'roi_percentage',
        'created_by',
        'managed_by'
    ];

    /**
     * Get by restaurant
     */
    public function getByRestaurant($restaurantId, $status, $type)
    {
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($status) {
            $where .= " AND campaign_status = ?";
            $params[] = $status;
        }
        
        if ($type) {
            $where .= " AND campaign_type = ?";
            $params[] = $type;
        }
        
        $sql = "SELECT mc.*, u1.username as created_by_name, u2.username as managed_by_name 
                FROM {$this->table} mc
                LEFT JOIN users u1 ON mc.created_by = u1.id
                LEFT JOIN users u2 ON mc.managed_by = u2.id
                {$where}
                ORDER BY mc.start_date DESC";
        
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
     * Count by status
     */
    public function countByStatus($restaurantId, $status)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE restaurant_id = ? AND campaign_status = ?";
        $result = $this->db->query($sql, [$restaurantId, $status])->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Get active campaigns
     */
    public function getActive($restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND campaign_status = 'active' ORDER BY end_date ASC";
        return $this->db->query($sql, [$restaurantId])->fetchAll();
    }

    /**
     * Update metrics
     */
    public function updateMetrics($id, $impressions, $clicks, $conversions, $revenue)
    {
        $campaign = $this->findById($id, 0);
        
        if (!$campaign) {
            return false;
        }
        
        $conversionRate = $impressions > 0 ? ($conversions / $impressions) * 100 : 0;
        $roi = $campaign['actual_spent'] > 0 ? (($revenue - $campaign['actual_spent']) / $campaign['actual_spent']) * 100 : 0;
        
        return $this->update($id, [
            'impressions' => $campaign['impressions'] + $impressions,
            'clicks' => $campaign['clicks'] + $clicks,
            'conversions' => $campaign['conversions'] + $conversions,
            'conversion_rate' => $conversionRate,
            'revenue_generated' => $campaign['revenue_generated'] + $revenue,
            'roi_percentage' => $roi
        ]);
    }
}
