<?php
/**
 * MyWisata Application - Favorite Model
 * 
 * Handles user favorites.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-01
 */

class Favorite extends Model {
    
    /**
     * Table name
     */
    protected $table = 'user_favorites';
    
    /**
     * Add to favorites
     * 
     * @param int $userId User ID
     * @param string $itemType Item type (destination, hotel, restaurant, event)
     * @param int $itemId Item ID
     * @return bool
     */
    public function add($userId, $itemType, $itemId) {
        $sql = "INSERT INTO {$this->table} (user_id, item_type, item_id, created_at)
                VALUES (:user_id, :item_type, :item_id, NOW())
                ON DUPLICATE KEY UPDATE created_at = NOW()";
        
        return $this->db->query($sql, [
            'user_id' => $userId,
            'item_type' => $itemType,
            'item_id' => $itemId
        ]);
    }
    
    /**
     * Remove from favorites
     * 
     * @param int $userId User ID
     * @param string $itemType Item type
     * @param int $itemId Item ID
     * @return bool
     */
    public function remove($userId, $itemType, $itemId) {
        $sql = "DELETE FROM {$this->table} 
                WHERE user_id = :user_id AND item_type = :item_type AND item_id = :item_id";
        
        return $this->db->query($sql, [
            'user_id' => $userId,
            'item_type' => $itemType,
            'item_id' => $itemId
        ]);
    }
    
    /**
     * Get user favorites
     * 
     * @param int $userId User ID
     * @param string $itemType Optional item type filter
     * @return array
     */
    public function getUserFavorites($userId, $itemType = null) {
        $where = "user_id = :user_id";
        $params = ['user_id' => $userId];
        
        if ($itemType) {
            $where .= " AND item_type = :item_type";
            $params['item_type'] = $itemType;
        }
        
        $sql = "SELECT * FROM {$this->table} WHERE {$where} ORDER BY created_at DESC";
        
        return $this->db->query($sql, $params)->fetchAll();
    }
    
    /**
     * Check if item is favorited
     * 
     * @param int $userId User ID
     * @param string $itemType Item type
     * @param int $itemId Item ID
     * @return bool
     */
    public function isFavorited($userId, $itemType, $itemId) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                WHERE user_id = :user_id AND item_type = :item_type AND item_id = :item_id";
        
        $result = $this->db->query($sql, [
            'user_id' => $userId,
            'item_type' => $itemType,
            'item_id' => $itemId
        ])->fetch();
        
        return $result['count'] > 0;
    }
}
