<?php

namespace App\Modules\Marketing\Models;

use App\Core\BaseModel;

class SocialMediaPost extends BaseModel
{
    protected $table = 'social_media_posts';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'post_title',
        'post_content',
        'platform',
        'media_urls',
        'scheduled_date',
        'posted_date',
        'post_status',
        'likes',
        'comments',
        'shares',
        'views',
        'engagement_rate',
        'external_post_id',
        'external_post_url',
        'created_by'
    ];

    /**
     * Get by restaurant
     */
    public function getByRestaurant($restaurantId, $platform, $status)
    {
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($platform) {
            $where .= " AND platform = ?";
            $params[] = $platform;
        }
        
        if ($status) {
            $where .= " AND post_status = ?";
            $params[] = $status;
        }
        
        $sql = "SELECT smp.*, u.username as created_by_name 
                FROM {$this->table} smp
                LEFT JOIN users u ON smp.created_by = u.id
                {$where}
                ORDER BY smp.scheduled_date DESC, smp.created_at DESC";
        
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
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE restaurant_id = ? AND post_status = ?";
        $result = $this->db->query($sql, [$restaurantId, $status])->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Get scheduled posts
     */
    public function getScheduled($restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND post_status = 'scheduled' ORDER BY scheduled_date ASC";
        return $this->db->query($sql, [$restaurantId])->fetchAll();
    }

    /**
     * Update metrics
     */
    public function updateMetrics($id, $likes, $comments, $shares, $views)
    {
        $post = $this->findById($id, 0);
        
        if (!$post) {
            return false;
        }
        
        $totalEngagement = $likes + $comments + $shares;
        $engagementRate = $views > 0 ? ($totalEngagement / $views) * 100 : 0;
        
        return $this->update($id, [
            'likes' => $likes,
            'comments' => $comments,
            'shares' => $shares,
            'views' => $views,
            'engagement_rate' => $engagementRate
        ]);
    }

    /**
     * Get by platform
     */
    public function getByPlatform($restaurantId, $platform, $limit = 20)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND platform = ? ORDER BY posted_date DESC LIMIT ?";
        return $this->db->query($sql, [$restaurantId, $platform, $limit])->fetchAll();
    }
}
