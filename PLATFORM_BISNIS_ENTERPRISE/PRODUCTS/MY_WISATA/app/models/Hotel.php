<?php
/**
 * MyWisata Application - Hotel Model
 * 
 * Handles hotel related database operations.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-01
 */

class Hotel extends Model {
    
    /**
     * Table name
     */
    protected $table = 'hotels';
    
    /**
     * Get all hotels with filters
     * 
     * @param array $filters Optional filters
     * @return array
     */
    public function getAllWithFilters($filters = []) {
        $where = ['1=1'];
        $params = [];
        
        if (!empty($filters['city'])) {
            $where[] = "city LIKE :city";
            $params['city'] = "%{$filters['city']}%";
        }
        
        if (!empty($filters['is_approved'])) {
            $where[] = "is_approved = :is_approved";
            $params['is_approved'] = $filters['is_approved'];
        }
        
        if (!empty($filters['search'])) {
            $where[] = "(h.name LIKE :search_name OR h.description LIKE :search_desc)";
            $params['search_name'] = "%{$filters['search']}%";
            $params['search_desc'] = "%{$filters['search']}%";
        }
        
        $whereClause = implode(' AND ', $where);
        
        $sql = "SELECT h.*, 
                (SELECT AVG(rating) FROM reviews WHERE reviewable_type = 'hotel' AND reviewable_id = h.id) as rating_avg,
                (SELECT COUNT(*) FROM reviews WHERE reviewable_type = 'hotel' AND reviewable_id = h.id) as review_count
                FROM {$this->table} h 
                WHERE {$whereClause} 
                ORDER BY h.name";
        
        return $this->db->query($sql, $params)->fetchAll();
    }
    
    /**
     * Get hotel by ID
     * 
     * @param int $id Hotel ID
     * @return array|false
     */
    public function findById($id) {
        $sql = "SELECT h.*, 
                (SELECT AVG(rating) FROM reviews WHERE reviewable_type = 'hotel' AND reviewable_id = h.id) as rating_avg,
                (SELECT COUNT(*) FROM reviews WHERE reviewable_type = 'hotel' AND reviewable_id = h.id) as review_count
                FROM {$this->table} h 
                WHERE h.id = :id";
        
        return $this->db->query($sql, ['id' => $id])->fetch();
    }
    
    /**
     * Get hotel rooms
     * 
     * @param int $hotelId Hotel ID
     * @return array
     */
    public function getRooms($hotelId) {
        $sql = "SELECT * FROM hotel_rooms WHERE hotel_id = :hotel_id AND is_active = 1";
        return $this->db->query($sql, ['hotel_id' => $hotelId])->fetchAll();
    }
    
    /**
     * Get hotel reviews
     * 
     * @param int $hotelId Hotel ID
     * @param int $limit Optional limit
     * @return array
     */
    public function getReviews($hotelId, $limit = null) {
        $sql = "SELECT r.*, u.name as user_name 
                FROM reviews r 
                LEFT JOIN users u ON r.user_id = u.id 
                WHERE r.reviewable_type = 'hotel' AND r.reviewable_id = :hotel_id 
                ORDER BY r.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        return $this->db->query($sql, ['hotel_id' => $hotelId])->fetchAll();
    }
    
    /**
     * Add review
     * 
     * @param array $data Review data
     * @return bool
     */
    public function addReview($data) {
        $sql = "INSERT INTO reviews 
                (reviewable_type, reviewable_id, user_id, rating, comment, created_at)
                VALUES 
                ('hotel', :hotel_id, :user_id, :rating, :comment, NOW())";
        
        return $this->db->query($sql, $data);
    }
}
