<?php
/**
 * MyWisata Application - Search Helper
 * 
 * Handles search functionality across multiple entities.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-01
 */

class Search {
    
    /**
     * Search destinations
     * 
     * @param string $query Search query
     * @param array $filters Optional filters
     * @return array
     */
    public static function searchDestinations($query, $filters = []) {
        $db = Database::getInstance();
        
        $where = "(d.name LIKE :query OR d.description LIKE :query OR d.city LIKE :query)";
        $params = ['query' => "%{$query}%"];
        
        if (!empty($filters['category_id'])) {
            $where .= " AND d.category_id = :category_id";
            $params['category_id'] = $filters['category_id'];
        }
        
        if (!empty($filters['city'])) {
            $where .= " AND d.city = :city";
            $params['city'] = $filters['city'];
        }
        
        $sql = "SELECT d.*, c.name as category_name,
                (SELECT AVG(rating) FROM destination_reviews WHERE destination_id = d.id) as rating_avg
                FROM destinations d 
                LEFT JOIN destination_categories c ON d.category_id = c.id 
                WHERE {$where} AND d.is_active = 1
                ORDER BY d.rating_avg DESC
                LIMIT 20";
        
        return $db->query($sql, $params)->fetchAll();
    }
    
    /**
     * Search tour guides
     * 
     * @param string $query Search query
     * @param array $filters Optional filters
     * @return array
     */
    public static function searchTourGuides($query, $filters = []) {
        $db = Database::getInstance();
        
        $where = "(tg.name LIKE :query OR tg.bio LIKE :query OR tg.city LIKE :query)";
        $params = ['query' => "%{$query}%"];
        
        if (!empty($filters['city'])) {
            $where .= " AND tg.city = :city";
            $params['city'] = $filters['city'];
        }
        
        if (!empty($filters['language'])) {
            $where .= " AND EXISTS (SELECT 1 FROM guide_languages gl WHERE gl.guide_id = tg.id AND gl.language_id = :language_id)";
            $params['language_id'] = $filters['language'];
        }
        
        if (!empty($filters['specialization'])) {
            $where .= " AND EXISTS (SELECT 1 FROM guide_specializations gs WHERE gs.guide_id = tg.id AND gs.specialization_id = :specialization_id)";
            $params['specialization_id'] = $filters['specialization'];
        }
        
        $sql = "SELECT tg.*, u.name, u.email, u.phone
                FROM tour_guides tg 
                LEFT JOIN users u ON tg.user_id = u.id 
                WHERE {$where} AND tg.is_verified = 1 AND tg.is_available = 1
                ORDER BY tg.rating_avg DESC
                LIMIT 20";
        
        return $db->query($sql, $params)->fetchAll();
    }
    
    /**
     * Search hotels
     * 
     * @param string $query Search query
     * @param array $filters Optional filters
     * @return array
     */
    public static function searchHotels($query, $filters = []) {
        $db = Database::getInstance();
        
        $where = "(h.name LIKE :query OR h.description LIKE :query OR h.city LIKE :query)";
        $params = ['query' => "%{$query}%"];
        
        if (!empty($filters['city'])) {
            $where .= " AND h.city = :city";
            $params['city'] = $filters['city'];
        }
        
        if (!empty($filters['star_rating'])) {
            $where .= " AND h.star_rating = :star_rating";
            $params['star_rating'] = $filters['star_rating'];
        }
        
        $sql = "SELECT h.*, 
                (SELECT AVG(rating) FROM hotel_reviews WHERE hotel_id = h.id) as rating_avg
                FROM hotels h 
                WHERE {$where} AND h.is_approved = 1
                ORDER BY h.rating_avg DESC
                LIMIT 20";
        
        return $db->query($sql, $params)->fetchAll();
    }
    
    /**
     * Search restaurants
     * 
     * @param string $query Search query
     * @param array $filters Optional filters
     * @return array
     */
    public static function searchRestaurants($query, $filters = []) {
        $db = Database::getInstance();
        
        $where = "(r.name LIKE :query OR r.description LIKE :query OR r.city LIKE :query)";
        $params = ['query' => "%{$query}%"];
        
        if (!empty($filters['city'])) {
            $where .= " AND r.city = :city";
            $params['city'] = $filters['city'];
        }
        
        if (!empty($filters['cuisine_type'])) {
            $where .= " AND r.cuisine_type = :cuisine_type";
            $params['cuisine_type'] = $filters['cuisine_type'];
        }
        
        $sql = "SELECT r.*, 
                (SELECT AVG(rating) FROM restaurant_reviews WHERE restaurant_id = r.id) as rating_avg
                FROM restaurants r 
                WHERE {$where} AND r.is_approved = 1
                ORDER BY r.rating_avg DESC
                LIMIT 20";
        
        return $db->query($sql, $params)->fetchAll();
    }
    
    /**
     * Global search across all entities
     * 
     * @param string $query Search query
     * @return array
     */
    public static function globalSearch($query) {
        return [
            'destinations' => self::searchDestinations($query),
            'tour_guides' => self::searchTourGuides($query),
            'hotels' => self::searchHotels($query),
            'restaurants' => self::searchRestaurants($query),
            'events' => self::searchEvents($query)
        ];
    }
    
    /**
     * Search events
     * 
     * @param string $query Search query
     * @param array $filters Optional filters
     * @return array
     */
    public static function searchEvents($query, $filters = []) {
        $db = Database::getInstance();
        
        $where = "(e.name LIKE :query OR e.description LIKE :query OR e.city LIKE :query)";
        $params = ['query' => "%{$query}%"];
        
        if (!empty($filters['city'])) {
            $where .= " AND e.city = :city";
            $params['city'] = $filters['city'];
        }
        
        if (!empty($filters['category'])) {
            $where .= " AND e.category = :category";
            $params['category'] = $filters['category'];
        }
        
        $sql = "SELECT e.*, 
                (SELECT AVG(rating) FROM event_reviews WHERE event_id = e.id) as rating_avg
                FROM events e 
                WHERE {$where} AND e.is_approved = 1 AND e.event_date >= CURDATE()
                ORDER BY e.event_date ASC
                LIMIT 20";
        
        return $db->query($sql, $params)->fetchAll();
    }
}
