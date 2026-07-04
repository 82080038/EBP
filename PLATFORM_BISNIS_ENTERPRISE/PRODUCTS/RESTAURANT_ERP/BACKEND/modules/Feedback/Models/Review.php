<?php

namespace App\Modules\Feedback\Models;

use App\Core\BaseModel;

class Review extends BaseModel
{
    protected $table = 'reviews';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'customer_id',
        'order_id',
        'rating',
        'title',
        'review_text',
        'review_source',
        'external_review_id',
        'external_source',
        'review_status',
        'is_public',
        'is_verified'
    ];

    /**
     * Get paginated reviews
     */
    public function getPaginated($restaurantId, $status, $rating, $source, $page, $limit)
    {
        $offset = ($page - 1) * $limit;
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($status) {
            $where .= " AND review_status = ?";
            $params[] = $status;
        }
        
        if ($rating) {
            $where .= " AND rating = ?";
            $params[] = $rating;
        }
        
        if ($source) {
            $where .= " AND review_source = ?";
            $params[] = $source;
        }
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} {$where}";
        $totalResult = $this->db->query($countSql, $params)->fetch();
        $total = $totalResult['total'] ?? 0;
        
        // Get data
        $sql = "SELECT r.*, c.first_name, c.last_name, o.order_number 
                FROM {$this->table} r
                LEFT JOIN customers c ON r.customer_id = c.id
                LEFT JOIN orders o ON r.order_id = o.id
                {$where}
                ORDER BY r.created_at DESC
                LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $data = $this->db->query($sql, $params)->fetchAll();
        
        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit)
        ];
    }

    /**
     * Find by ID
     */
    public function findById($id, $restaurantId)
    {
        $sql = "SELECT r.*, c.first_name, c.last_name, o.order_number 
                FROM {$this->table} r
                LEFT JOIN customers c ON r.customer_id = c.id
                LEFT JOIN orders o ON r.order_id = o.id
                WHERE r.id = ? AND r.restaurant_id = ?";
        $result = $this->db->query($sql, [$id, $restaurantId])->fetch();
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
     * Count by status
     */
    public function countByStatus($restaurantId, $status)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE restaurant_id = ? AND review_status = ?";
        $result = $this->db->query($sql, [$restaurantId, $status])->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Get average rating
     */
    public function getAverageRating($restaurantId)
    {
        $sql = "SELECT AVG(rating) as avg_rating FROM {$this->table} WHERE restaurant_id = ? AND review_status = 'approved'";
        $result = $this->db->query($sql, [$restaurantId])->fetch();
        return $result['avg_rating'] ?? 0;
    }

    /**
     * Get rating distribution
     */
    public function getRatingDistribution($restaurantId)
    {
        $sql = "SELECT rating, COUNT(*) as count 
                FROM {$this->table} 
                WHERE restaurant_id = ? AND review_status = 'approved'
                GROUP BY rating 
                ORDER BY rating DESC";
        return $this->db->query($sql, [$restaurantId])->fetchAll();
    }
}
