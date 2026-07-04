<?php

namespace App\Modules\Feedback\Models;

use App\Core\BaseModel;

class ReviewResponse extends BaseModel
{
    protected $table = 'review_responses';
    protected $primaryKey = 'id';
    protected $fillable = [
        'review_id',
        'restaurant_id',
        'response_text',
        'responded_by',
        'responded_at'
    ];

    /**
     * Get by review ID
     */
    public function getByReviewId($reviewId)
    {
        $sql = "SELECT rr.*, u.username as responded_by_name 
                FROM {$this->table} rr
                LEFT JOIN users u ON rr.responded_by = u.id
                WHERE rr.review_id = ?
                ORDER BY rr.responded_at DESC
                LIMIT 1";
        $result = $this->db->query($sql, [$reviewId])->fetch();
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
     * Get by restaurant
     */
    public function getByRestaurant($restaurantId, $page = 1, $limit = 20)
    {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT rr.*, r.rating, r.title, c.first_name, c.last_name, u.username as responded_by_name 
                FROM {$this->table} rr
                LEFT JOIN reviews r ON rr.review_id = r.id
                LEFT JOIN customers c ON r.customer_id = c.id
                LEFT JOIN users u ON rr.responded_by = u.id
                WHERE rr.restaurant_id = ?
                ORDER BY rr.responded_at DESC
                LIMIT ? OFFSET ?";
        
        return $this->db->query($sql, [$restaurantId, $limit, $offset])->fetchAll();
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
